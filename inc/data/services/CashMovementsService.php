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

class CashMovementsService {

    private function build($row, $pdo) {
        $mvt = CashMovement::__build($row['RECEIPTID'], $row['ID'],
                $row['PAYMENT'], $row['TOTAL'], $row['CURRENCY'],
                $row['TOTALCURRENCY'], $row['NOTE']);
        return $mvt;
    }

    public function get($rcptId) {
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT P.RECEIPT AS RECEIPTID, P.ID, P.PAYMENT, P.TOTAL, "
                . "P.CURRENCY, P.TOTALCURRENCY, P.NOTE "
                . "FROM RECEIPTS LEFT JOIN PAYMENTS AS P "
                . "ON P.RECEIPT = RECEIPT.ID"
                . "WHERE RECEIPTS.ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $rcptId);
        $stmt->execute();
        if ($row = $stmt->fetch()) {
            return $this->build($row, $pdo);
        } else {
            return null;
        }
    }

    public function getAll() {
        $tickets = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT P.RECEIPT AS RECEIPTID, P.ID, P.PAYMENT, P.TOTAL, "
                . "P.CURRENCY, P.TOTALCURRENCY, P.NOTE "
                . "FROM RECEIPTS LEFT JOIN PAYMENTS AS P "
                . "ON P.RECEIPT = RECEIPT.ID"
                . "WHERE RECEIPTS.ID = :id "
                . "AND P.PAYMENT IN (\"" . CashMovement::TYPE_CASHIN . "\", \""
                . CashMovement::TYPE_CASHOUT . "\")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $mvt = $this->build($row, $pdo);
            $mvts[] = $mvt;
        }
        return $mvts;
    }

    public function create($mvt) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        $id = md5(time() . rand());
        // Insert receipt
        $sql = "INSERT INTO RECEIPTS (ID, MONEY, DATENEW) "
                . "VALUES (:id, :money, :date)";
        $stmtRcpt = $pdo->prepare($sql);
        $stmtRcpt->bindValue(":id", $id);
        $stmtRcpt->bindValue(":money", $mvt->cashId);
        $stmtRcpt->bindValue(":date", $db->dateVal($mvt->date));
        if ($stmtRcpt->execute() === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Insert payment
        $stmtPay = $pdo->prepare("INSERT INTO PAYMENTS (ID, RECEIPT, PAYMENT, "
                . "TOTAL, CURRENCY, TOTALCURRENCY, NOTE) VALUES (:id, :rcptId, "
                . ":type, :amount, :currId, :currAmount, :note)");
        $stmtPay->bindParam(":id", $id);
        $stmtPay->bindParam(":rcptId", $id);
        $stmtPay->bindParam(":type", $mvt->type);
        $stmtPay->bindParam(":amount", $mvt->amount);
        $stmtPay->bindParam(":currId", $mvt->currencyId);
        $stmtPay->bindParam(":currAmount", $mvt->currencyAmount);
        $stmtPay->bindParam(":note", $mvt->note);
        if ($stmtPay->execute() === false) {
            var_dump($stmtPay->errorInfo());
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Finished
        if ($newTransaction) {
            $pdo->commit();
        }
        return $id;
    }
}