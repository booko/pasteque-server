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

    private static function buildDBTkt($dbTkt, $pdo) {
        return Ticket::__build($dbTkt['ID'], $dbTkt['TICKETTYPE'],
                $dbTkt['TICKETID'], $dbTkt['PERSON'], $dbTkt['DATENEW'],
                array(), array(), $dbTkt['MONEY'], $dbTkt['CUSTOMER'],
                $dbTkt['CUSTCOUNT'], $dbTkt['TARIFFAREA']);
    }

    static function save($ticket, $locationId = "0") {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        $id = md5(time() . rand());
        $stmtRcpt = $pdo->prepare("INSERT INTO RECEIPTS	(ID, MONEY, DATENEW) "
                . "VALUES (:id, :money, :date)");
        $stmtRcpt->bindParam(":id", $id);
        $stmtRcpt->bindParam(":money", $ticket->cashId);
        $stmtRcpt->bindParam(":date", $db->dateVal($ticket->date));
        if ($stmtRcpt->execute() === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Get next ticket number
        switch ($ticket->type) {
        case Ticket::TYPE_REFUND:
            $ticketNumTable = "TICKETSNUM_REFUND";
            break;
        case Ticket::TYPE_PAYMENT:
            $ticketNumTable = "TICKETSNUM_PAYMENT";
        case Ticket::TYPE_SELL:
        default:
            $ticketNumTable = "TICKETSNUM";
            break;
        }
        switch ($db->getType()) {
        case 'mysql':
            // Get ticket number
            $stmtNum = $pdo->prepare("SELECT ID FROM " . $ticketNumTable);
            if ($stmtNum->execute() === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            $nextNum = $stmtNum->fetchColumn(0);
            // Increment next ticket number
            $stmtNumInc = $pdo->prepare("UPDATE " . $ticketNumTable
                    . " SET ID = :id");
            $stmtNumInc->bindParam(":id", $nextNum + 1);
            if ($stmtNumInc->execute() === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            break;
        case 'postgresql':
            $stmtNum = $pdo->prepare("SELECT nextval('"
                    . $ticketNumTable . "')");
            if ($stmtNum->execute() === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            $nextNum = $stmtNum->fetchColumn(0);
            break;
        }
        //  Insert ticket
        $stmtTkt = $pdo->prepare("INSERT INTO TICKETS (ID, TICKETID, "
                . "TICKETTYPE, PERSON, CUSTOMER, CUSTCOUNT, TARIFFAREA) VALUES "
                . "(:id, :tktId, :tktType, :person, :cust, :custcount, :taId)");
        $stmtTkt->bindParam(':id', $id, \PDO::PARAM_STR);
        $stmtTkt->bindParam(':tktId', $nextNum, \PDO::PARAM_INT);
        $stmtTkt->bindParam(":tktType", $ticket->type);
        $stmtTkt->bindParam(':person', $ticket->userId);
        $stmtTkt->bindParam(':cust', $ticket->customerId);
        $stmtTkt->bindParam(":custcount", $ticket->custCount);
        $stmtTkt->bindParam(":taId", $ticket->tariffAreaId);
        if ($stmtTkt->execute() === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Insert ticket lines
        // Also check for prepayments refill
        $stmtLines = $pdo->prepare("INSERT INTO TICKETLINES (TICKET, LINE, "
                . "PRODUCT, ATTRIBUTESETINSTANCE_ID, UNITS, PRICE, TAXID, "
                . "ATTRIBUTES) VALUES (:id, :line, :prdId, :attrSetInstId, "
                .":qty, :price, :taxId, :attrs)");
        foreach ($ticket->lines as $line) {
            $stmtLines->bindParam(":id", $id);
            $stmtLines->bindParam(":line", $line->dispOrder);
            $stmtLines->bindParam(":prdId", $line->productId);
            $stmtLines->bindParam(":attrSetInstId", $line->attrSetInstId);
            $stmtLines->bindParam(":qty", $line->quantity);
            $stmtLines->bindParam(":price", $line->price);
            $stmtLines->bindParam(":taxId", $line->taxId);
            $stmtLines->bindParam(":attrs", $line->attributes);
            if ($stmtLines->execute() === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            // Update stock
            $move = new StockMove($ticket->date, StockMove::REASON_OUT_SELL,
                    $line->productId, $locationId, $line->attrSetInstId,
                    $line->quantity, $line->price);
            if (StocksService::addMove($move) === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            // Check prepayment refill
            $prepaidIds = ProductsService::getPrepaidIds();
            if ($ticket->customerId !== null
                    && in_array($line->productId, $prepaidIds)) {
                $custSrv = new CustomersService();
                $ok = $custSrv->addPrepaid($ticket->customerId,
                        $line->price * $line->quantity);
                if ($ok === false) {
                    if ($newTransaction) {
                        $pdo->rollback();
                    }
                    return false;
                }
            }
        }
        // Insert payments
        // Also check for prepayment debit
        $stmtPay = $pdo->prepare("INSERT INTO PAYMENTS (ID, RECEIPT, PAYMENT, "
                . "TOTAL, CURRENCY, TOTALCURRENCY) VALUES (:id, :rcptId, "
                . ":type, :amount, :currId, :currAmount)");
        foreach ($ticket->payments as $payment) {
            $paymentId = md5(time() . rand());
            $stmtPay->bindParam(":id", $paymentId);
            $stmtPay->bindParam(":rcptId", $id);
            $stmtPay->bindParam(":type", $payment->type);
            $stmtPay->bindParam(":amount", $payment->amount);
            $stmtPay->bindParam(":currId", $payment->currencyId);
            $stmtPay->bindParam(":currAmount", $payment->currencyAmount);
            if ($stmtPay->execute() === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            if ($payment->type == 'prepaid') {
                $custSrv = new CustomersService();
                $ok = $custSrv->addPrepaid($ticket->customerId,
                        $payment->amount * -1);
                if ($ok === false) {
                    if ($newTransaction) {
                        $pdo->rollback();
                    }
                    return false;
                }
            }
        }
        // Insert taxlines
        $stmtTax = $pdo->prepare("INSERT INTO TAXLINES (ID, RECEIPT, TAXID, "
                                 . "BASE, AMOUNT)  VALUES (:id, :rcptId, "
                                 . ":taxId, :base, :amount)");
        foreach ($ticket->getTaxAmounts() as $ta) {
            $taxId = md5(time() . rand());
            $stmtTax->bindParam(":id", $taxId);
            $stmtTax->bindParam(":rcptId", $id);
            $stmtTax->bindParam(":taxId", $ta->taxId);
            $stmtTax->bindParam(":base", $ta->base);
            $stmtTax->bindParam(":amount", $ta->getAmount());
            if ($stmtTax->execute() === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
        }
        if ($newTransaction) {
            $pdo->commit();
        }
        return $id;
    }

    static function createAttrSetInst($attrs) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        $id = md5(time() . rand());
        $stmt = $pdo->prepare("INSERT INTO ATTRIBUTESETINSTANCE (ID, "
                . "ATTRIBUTESET_ID, DESCRIPTION) VALUES (:id, :setId, :val)");
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":setId", $attrs->attrSetId);
        $stmt->bindParam(":val", $attrs->value);
        if ($stmt->execute() !== false) {
            $attrId = md5(time() . rand());
            $stmtAttr = $pdo->prepare("INSERT INTO ATTRIBUTEINSTANCE "
                    . "(ID, ATTRIBUTESETINSTANCE_ID, ATTRIBUTE_ID, VALUE) "
                    . " VALUES (:id, :attrSetInstId, :attrId, :val)");
            foreach ($attrs->attrInsts as $inst) {
                $stmtAttr->bindParam(":id", $attrId);
                $stmtAttr->bindParam(":attrSetInstId", $id);
                $stmtAttr->bindParam(":attrId", $inst->attrId);
                $stmtAttr->bindParam(":val", $inst->value);
                if ($stmtAttr->execute() === false) {
                    if ($newTransaction) {
                        $pdo->rollback();
                    }
                    return false;
                }
            }
            if ($newTransaction) {
                $pdo->commit();
            }
            return $id;
        } else {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
    }
}