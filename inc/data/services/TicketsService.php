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

    private static function buildTicket($row, $pdo) {
        $db = DB::get();
        $id = $row['ID'];
        // Get lines
        $lines = array();
        $lineSql = "SELECT * FROM TICKETLINES WHERE TICKET = :id "
                . "ORDER BY LINE";
        $lineStmt = $pdo->prepare($lineSql);
        $lineStmt->bindParam(":id", $id);
        $lineStmt->execute();
        while ($rowLine = $lineStmt->fetch()) {
            $product = ProductsService::get($rowLine['PRODUCT']);
            $tax = TaxesService::getTax($rowLine['TAXID']);
            $line = new TicketLine($rowLine['LINE'], $product,
                    $rowLine['ATTRIBUTESETINSTANCE_ID'], $rowLine['UNITS'],
                    $rowLine['PRICE'], $tax, $rowLine['DISCOUNTRATE']);
            $lines[] = $line;
        }
        // Get payments
        $payments = array();
        $paySql = "SELECT * FROM PAYMENTS WHERE RECEIPT = :id";
        $payStmt = $pdo->prepare($paySql);
        $payStmt->bindParam(":id", $id);
        $payStmt->execute();
        while ($rowPay = $payStmt->fetch()) {
            $pay = Payment::__build($rowPay['ID'], $rowPay['PAYMENT'],
                    $rowPay['TOTAL'], $rowPay['CURRENCY'],
                    $rowPay['TOTALCURRENCY']);
            $payments[] = $pay;
        }
        // Build ticket
        $tkt = Ticket::__build($row['ID'], $row['TICKETID'],
                $row['TICKETTYPE'], $row['PERSON'],
                $db->readDate($row['DATENEW']), $lines, $payments,
                $row['MONEY'], $row['CUSTOMER'],
                $row['CUSTCOUNT'], $row['TARIFFAREA'],
                $row['DISCOUNTRATE'], $row['DISCOUNTPROFILE_ID']);
        return $tkt;
    }

    private static function buildSharedTicket($dbRow, $pdo) {
        $db = DB::get();
        return SharedTicket::__build($dbRow['ID'], $dbRow['NAME'],
                $db->readBin($dbRow['CONTENT']));
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT T.ID, T.TICKETID, T.TICKETTYPE, T.PERSON, T.CUSTOMER, "
                . "T.STATUS, T.CUSTCOUNT, T.TARIFFAREA, T.DISCOUNTRATE, "
                . "T.DISCOUNTPROFILE_ID, RECEIPTS.DATENEW, RECEIPTS.MONEY "
                . "FROM TICKETS AS T, RECEIPTS "
                . "WHERE RECEIPTS.ID = T.ID AND T.ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        if ($row = $stmt->fetch()) {
            return TicketsService::buildTicket($row, $pdo);
        } else {
            return null;
        }
    }

    static function getOpen() {
        $tickets = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT T.ID, T.TICKETID, T.TICKETTYPE, T.PERSON, T.CUSTOMER, "
                . "T.STATUS, T.CUSTCOUNT, T.TARIFFAREA, T.DISCOUNTRATE, "
                . "T.DISCOUNTPROFILE_ID, RECEIPTS.DATENEW, "
                . "CLOSEDCASH.MONEY "
                . "FROM TICKETS AS T, RECEIPTS, CLOSEDCASH "
                . "WHERE CLOSEDCASH.DATEEND IS NULL "
                . "AND CLOSEDCASH.MONEY = RECEIPTS.MONEY "
                . "AND RECEIPTS.ID = T.ID "
                . "ORDER BY T.TICKETID DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $ticket = TicketsService::buildTicket($row, $pdo);
            $tickets[] = $ticket;
        }
        return $tickets;
    }

    static function search($ticketId, $ticketType, $cashId, $dateStart,
            $dateStop, $customerId, $userId) {
        $tickets = array();
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = "SELECT T.ID, T.TICKETID, T.TICKETTYPE, T.PERSON, T.CUSTOMER, "
                . "T.STATUS, T.CUSTCOUNT, T.TARIFFAREA, T.DISCOUNTRATE, "
                . "T.DISCOUNTPROFILE_ID, RECEIPTS.DATENEW, "
                . "CLOSEDCASH.MONEY "
                . "FROM TICKETS AS T, RECEIPTS, CLOSEDCASH "
                . "WHERE CLOSEDCASH.MONEY = RECEIPTS.MONEY "
                . "AND RECEIPTS.ID = T.ID";
        $conds = array();
        if ($ticketId !== null) {
            $conds[] = "TICKETID = :ticketId";
        }
        if ($ticketType !== null) {
            $conds[] = "TICKETTYPE = :ticketType";
        }
        if ($cashId !== null) {
            $conds[] = "MONEY = :cashId";
        }
        if ($dateStart !== null) {
            $conds[] = "DATESTART >= :dateStart";
        }
        if ($dateStop !== null) {
            $conds[] = "DATEEND <= :dateStop";
        }
        if ($customerId !== null) {
            $conds[] = "CUSTOMER = :custId";
        }
        if ($userId !== null) {
            $conds[] = "PERSON = :userId";
        }
        if (count($conds) > 0) {
            $sql .= " AND " . implode(" AND ", $conds);
        }
        $sql .= " ORDER BY T.TICKETID DESC";
        $stmt = $pdo->prepare($sql);
        if ($ticketId !== null) {
            $stmt->bindParam(":ticketId", $ticketId);
        }
        if ($ticketType !== null) {
            $stmt->bindParam(":ticketType", $ticketType);
        }
        if ($cashId !== null) {
            $stmt->bindParam(":cashId", $cashId);
        }
        if ($dateStart !== null) {
            $stmt->bindParam(":dateStart", $db->dateVal($dateStart));
        }
        if ($dateStop !== null) {
            $stmt->bindParam(":dateStop", $db->dateVal($dateStop));
        }
        if ($customerId !== null) {
            $stmt->bindParam(":custId", $customerId);
        }
        if ($userId !== null) {
            $stmt->bindParam(":userId", $userId);
        }
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $ticket = TicketsService::buildTicket($row, $pdo);
            $tickets[] = $ticket;
        }
        return $tickets;
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
        if ($ticket->ticketId === null) {
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
                $stmtNumInc->bindValue(":id", $nextNum + 1);
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
            $ticket->ticketId = $nextNum;
        }
        //  Insert ticket
        $discountRate = $ticket->discountRate;
        $stmtTkt = $pdo->prepare("INSERT INTO TICKETS (ID, TICKETID, "
                . "TICKETTYPE, PERSON, CUSTOMER, CUSTCOUNT, TARIFFAREA, "
                . "DISCOUNTRATE, DISCOUNTPROFILE_ID) VALUES "
                . "(:id, :tktId, :tktType, :person, :cust, :custcount, :taId, "
                . ":discRate, :discProfId)");
        $stmtTkt->bindParam(':id', $id, \PDO::PARAM_STR);
        $stmtTkt->bindParam(':tktId', $ticket->ticketId, \PDO::PARAM_INT);
        $stmtTkt->bindParam(":tktType", $ticket->type);
        $stmtTkt->bindParam(':person', $ticket->userId);
        $stmtTkt->bindParam(':cust', $ticket->customerId);
        $stmtTkt->bindParam(":custcount", $ticket->custCount);
        $stmtTkt->bindParam(":taId", $ticket->tariffAreaId);
        $stmtTkt->bindParam(":discRate", $ticket->discountRate);
        $stmtTkt->bindParam(":discProfId", $ticket->discountProfileId);
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
                . "DISCOUNTRATE, ATTRIBUTES) VALUES (:id, :line, :prdId, "
                . ":attrSetInstId, :qty, :price, :taxId, :discRate, :attrs)");
        foreach ($ticket->lines as $line) {
            $fullDiscount = $discountRate + $line->discountRate;
            $discountPrice = $line->price * (1.0 - $fullDiscount);
            $stmtLines->bindParam(":id", $id);
            $stmtLines->bindParam(":line", $line->dispOrder);
            $stmtLines->bindParam(":prdId", $line->productId);
            $stmtLines->bindParam(":attrSetInstId", $line->attrSetInstId);
            $stmtLines->bindParam(":qty", $line->quantity);
            $stmtLines->bindParam(":price", $line->price);
            $stmtLines->bindParam(":taxId", $line->taxId);
            $stmtLines->bindParam(":discRate", $line->discountRate);
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
                    $line->quantity, $discountPrice);
            if (StocksService::addMove($move) === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            // Check prepayment refill
            // Refill is not affected by discount
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

    static function delete($ticket, $locationId = "0") {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        // Delete ticket lines
        // Also check for prepayments refill
        $stmtLines = $pdo->prepare("DELETE FROM TICKETLINES "
                . "WHERE TICKET = :id");
        $stmtLines->bindParam(":id", $ticket->id);
        foreach ($ticket->lines as $line) {
            // Update stock
            $discountRate = $ticket->discountRate;
            $fullDiscount = $discountRate + $line->discountRate;
            $discountPrice = $line->price * (1.0 - $fullDiscount);
            $move = new StockMove($ticket->date, StockMove::REASON_IN_REFUND,
                    $line->productId, $locationId, $line->attrSetInstId,
                    $line->quantity, $discountPrice);
            if (StocksService::addMove($move) === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            // Check prepayment refill
            // Refill is not affected by discount
            $prepaidIds = ProductsService::getPrepaidIds();
            if ($ticket->customerId !== null
                    && in_array($line->productId, $prepaidIds)) {
                $custSrv = new CustomersService();
                $ok = $custSrv->addPrepaid($ticket->customerId,
                        -$line->price * $line->quantity);
                if ($ok === false) {
                    if ($newTransaction) {
                        $pdo->rollback();
                    }
                    return false;
                }
            }
        }
        if ($stmtLines->execute() === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Delete payments
        // Also check for prepayment debit
        $stmtPay = $pdo->prepare("DELETE FROM PAYMENTS WHERE RECEIPT = :id");
        $stmtPay->bindParam(":id", $ticket->id);
        foreach ($ticket->payments as $payment) {
            if ($payment->type == 'prepaid') {
                $custSrv = new CustomersService();
                $ok = $custSrv->addPrepaid($ticket->customerId,
                        $payment->amount);
                if ($ok === false) {
                    if ($newTransaction) {
                        $pdo->rollback();
                    }
                    return false;
                }
            }
        }
        if ($stmtPay->execute() === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Delete taxlines
        $stmtTax = $pdo->prepare("DELETE FROM TAXLINES WHERE RECEIPT = :id");
        $stmtTax->bindParam(":id", $ticket->id);
        if ($stmtTax->execute() === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        //  Delete ticket
        $discountRate = $ticket->discountRate;
        $stmtTkt = $pdo->prepare("DELETE FROM TICKETS WHERE ID = :id");
        $stmtTkt->bindParam(':id', $ticket->id);
        if ($stmtTkt->execute() === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Delete receipt
        $stmtRcpt = $pdo->prepare("DELETE FROM RECEIPTS WHERE ID = :id");
        $stmtRcpt->bindParam(":id", $ticket->id);
        if ($stmtRcpt->execute() === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        if ($newTransaction) {
            $pdo->commit();
        }
        return true;
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

    static function getSharedTicket($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM SHAREDTICKETS "
                . "WHERE ID = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        if ($row = $stmt->fetch()) {
            return TicketsService::buildSharedTicket($row, $pdo);
        }
        return null;
    }

    static function getAllSharedTickets() {
        $pdo = PDOBuilder::getPDO();
        $tkts = array();
        $stmt = $pdo->prepare("SELECT * FROM SHAREDTICKETS");
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $tkts[] = TicketsService::buildSharedTicket($row, $pdo);
        }
        return $tkts;
    }

    static function deleteSharedTicket($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM SHAREDTICKETS WHERE ID = :id");
        $stmt->bindParam(":id", $id);
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
        }
    }

    /** Create a shared ticket, its id is always set. */
    static function createSharedTicket($ticket) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO SHAREDTICKETS (ID, NAME, CONTENT) "
                . "VALUES (:id, :label, :data)");
        $stmt->bindParam(":id", $ticket->id);
        $stmt->bindParam(":label", $ticket->label);
        $stmt->bindParam(":data", $ticket->data, \PDO::PARAM_LOB);
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
        }
    }

    static function updateSharedTicket($ticket) {
        if ($ticket->id === null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("UPDATE SHAREDTICKETS SET NAME = :lbl, "
                . "CONTENT = :content WHERE ID = :id");
        $stmt->bindParam(":id", $ticket->id);
        $stmt->bindParam(":lbl", $ticket->label);
        $stmt->bindParam(":content", $ticket->data, \PDO::PARAM_LOB);
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
        }
    }

}