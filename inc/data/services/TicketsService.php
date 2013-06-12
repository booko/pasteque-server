<?php
//    POS-Tech API
//
//    Copyright (C) 2012 Scil (http://scil.coop)
//
//    This file is part of POS-Tech.
//
//    POS-Tech is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    POS-Tech is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with POS-Tech.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque;

class TicketsService {

    /** Build a full ticket from a light ticket */
    static function buildLight($ticketLight) {
        $cashier = UsersService::get($ticketLight->cashierId);
        $cash = CashesService::get($ticketLight->cashId);
        $customer = CustomersService::get($ticketLight->customerId);
        $lines = array();
        foreach ($ticketLight->linesLight as $lineLight) {
            $product = ProductsService::get($lineLight->productId);
            $tax = TaxesService::getTax($lineLight->taxId);
            $line = new TicketLine($lineLight->line, $product,
                    $lineLight->quantity, $lineLight->price, $tax);
            $lines[] = $line;
        }
        $ticket = new Ticket($ticketLight->label, $cashier, $ticketLight->date,
                $lines, $ticketLight->payments, $cash, $customer);
        return $ticket;
    }

    private static function buildDBTkt($db_tkt, $pdo) {
        // TODO: add references
        return Ticket::__build($db_tkt['ID'], $db_tkt['TICKETID'],
                $db_tkt['PERSON'], $db_tkt['DATENEW'], array(), array(),
                $db_tkt['MONEY']);
    }

    static function getBySession($sessId) {
        $tkts = array();
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM `TICKETS` LEFT JOIN RECEIPTS ON "
                . "TICKETS.ID = RECEIPTS.ID WHERE MONEY = :id");
        $stmt->bindParam(':id', $sessId);
        while ($db_tkt = $stmt->fetch()) {
            $tkt = TicketsService::buildDBTkt($db_tkt, $pdo);
            $tkts[] = $tkt;
        }
        return $tkts;
    }

    static function save($ticket, $location = "0") {
        $pdo = PDOBuilder::getPDO();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        $id = md5(time() . rand());
        $stmtRcpt = $pdo->prepare("INSERT INTO RECEIPTS	(ID, MONEY, DATENEW) "
                                  . "VALUES (:id, :money, :date)");
        $strdate = strftime("%Y-%m-%d %H:%M", $ticket->date);
        $ok = $stmtRcpt->execute(array(':id' => $id,
                                       ':money' => $ticket->cash->id,
                                       ':date' => $strdate));
        if ($ok === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Get next ticket number
        $stmtNum = $pdo->prepare("SELECT ID FROM TICKETSNUM");
        $ok = $stmtNum->execute();
        if ($ok === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        $nextNum = $stmtNum->fetchColumn(0);
        //  Insert ticket
        $stmtTkt = $pdo->prepare("INSERT INTO TICKETS (ID, TICKETID, PERSON, "
                . "CUSTOMER) VALUES (:id, :tktId, :person, :cust)");
        $cust = $ticket->customer === NULL ? NULL : $ticket->customer->id;
        $stmtTkt->bindParam(':id', $id, \PDO::PARAM_STR);
        $stmtTkt->bindParam(':tktId', $nextNum, \PDO::PARAM_INT);
        $stmtTkt->bindParam(':person', $ticket->cashier->id, \PDO::PARAM_STR);
        $stmtTkt->bindParam(':cust', $cust, \PDO::PARAM_STR);
        $ok = $stmtTkt->execute();
        if ($ok === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Increment next ticket number
        $stmtNumInc = $pdo->prepare("UPDATE TICKETSNUM SET ID = :id");
        $ok = $stmtNumInc->execute(array(':id' => $nextNum + 1));
        if ($ok === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Insert ticket lines
        // Also check for prepayments refill
        $stmtLines = $pdo->prepare("INSERT INTO TICKETLINES (TICKET, LINE, "
                                   . "PRODUCT, UNITS, "
                                   . "PRICE, TAXID, ATTRIBUTES) VALUES "
                                   . "(:id, :line, :product, :qty, :price, "
                                   . ":tax, :attrs)");
        $prepaidIds = ProductsService::getPrepaidIds();
        foreach ($ticket->lines as $line) {
            $ok = $stmtLines->execute(array(':id' => $id,
                                            ':line' => $line->line,
                                            ':product' => $line->product->id,
                                            ':qty' => $line->quantity,
                                            ':price' => $line->price,
                                            ':tax' => $line->tax->id,
                                            ':attrs' => $line->attributes));
            if ($ok === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            // Update stock
            $move = new StockMove($strdate, StockMove::REASON_OUT_SELL,
                    $location, $line->product->id, $line->quantity);
            if (!StocksService::addMove($move)) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            // Check prepayment
            if ($cust !== NULL && in_array($line->product->id, $prepaidIds)) {
                $ok = CustomersService::addPrepaid($cust,
                        $line->price * $line->quantity);
                if ($ok === FALSE) {
                    if ($newTransaction) {
                        $pdo->rollback();
                    }
                    return FALSE;
                }
            }
        }
        // Insert payments
        // Also check for prepayment debit
        $stmtPay = $pdo->prepare("INSERT INTO PAYMENTS (ID, RECEIPT, PAYMENT, "
                . "TOTAL, CURRENCY, TOTALCURRENCY) VALUES (:id, :rcptId, "
                . ":type, :amount, 1, :amount)");
        foreach ($ticket->payments as $payment) {
            $paymentId = md5(time() . rand());
            $ok = $stmtPay->execute(array(':id' => $paymentId,
                                          ':rcptId' => $id,
                                          ':type' => $payment->type,
                                          ':amount' => $payment->amount));
            if ($ok === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            if ($payment->type == 'prepaid') {
                $ok = CustomersService::addPrepaid($cust, $payment->amount * -1);
                if ($ok === FALSE) {
                    if ($newTransaction) {
                        $pdo->rollback();
                    }
                    return FALSE;
                }
            }
        }
        // Insert taxlines
        $stmtTax = $pdo->prepare("INSERT INTO TAXLINES (ID, RECEIPT, TAXID, "
                                 . "BASE, AMOUNT)  VALUES (:id, :rcptId, "
                                 . ":taxId, :base, :amount)");
        foreach ($ticket->getTaxAmounts() as $ta) {
            $taxId = md5(time() . rand());
            $ok = $stmtTax->execute(array(':id' => $taxId,
                                          ':rcptId' => $id,
                                          ':taxId' => $ta->tax->id,
                                          ':base' => $ta->base,
                                          ':amount' => $ta->getAmount()));
            if ($ok === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
        }
        if ($newTransaction) {
            $pdo->commit();
        }
        return true;
    }
}

?>
