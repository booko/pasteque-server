<?php
//    Pastèque API
//
//    Copyright (C) 2012-2015 Scil (http://scil.coop)
//    Cédric Houbart, Philippe Pary
//
//    This file is part of Pastèque.
//
//    Pastèque is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Pastèque is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Pastèque.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque;

class CashesService extends AbstractService {

    protected static $dbTable = "CLOSEDCASH";
    protected static $dbIdField = "MONEY";
    protected static $fieldMapping = array(
            "MONEY" => "id",
            "CASHREGISTER_ID" => "cashRegisterId",
            "HOSTSEQUENCE" => "sequence",
            "DATESTART" => "openDate",
            "DATEEND" => "closeDate",
            "OPENCASH" => "openCash",
            "CLOSECASH" => "closeCash",
            "EXPECTEDCASH" => "expectedCash"
    );

    protected function build($dbCash, $pdo = null) {
        $db = DB::get();
        $cash = Cash::__build($dbCash['MONEY'], $dbCash['CASHREGISTER_ID'],
                $dbCash['HOSTSEQUENCE'],
                $db->readDate($dbCash['DATESTART']),
                $db->readDate($dbCash['DATEEND']),
                $dbCash['OPENCASH'], $dbCash['CLOSECASH'],
                $dbCash['EXPECTEDCASH']);
        if (isset($dbCash['TKTS'])) {
            $cash->tickets = $dbCash['TKTS'];
        }
        if (isset($dbCash['TOTAL'])) {
            $cash->total = $dbCash['TOTAL'];
        }
        return $cash;
    }

    private function getLastSequence($cashRegisterId, $pdo) {
        $stmt = $pdo->prepare("SELECT max(HOSTSEQUENCE) FROM CLOSEDCASH WHERE "
                              . "CASHREGISTER_ID = :crId");
        $stmt->execute(array(':crId' => $cashRegisterId));
        if ($data = $stmt->fetch()) {
            return $data[0];
        } else {
            return 0;
        }
    }

    public function getAll() {
        $cashes = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT CLOSEDCASH.MONEY, CLOSEDCASH.CASHREGISTER_ID, "
                . "CLOSEDCASH.HOSTSEQUENCE, CLOSEDCASH.DATESTART, "
                . "CLOSEDCASH.DATEEND, "
                . "CLOSEDCASH.OPENCASH, CLOSEDCASH.CLOSECASH, "
                . "CLOSEDCASH.EXPECTEDCASH, "
                . "COUNT(DISTINCT(RECEIPTS.ID)) as TKTS, "
                . "SUM(PAYMENTS.TOTAL) AS TOTAL "
                . "FROM CLOSEDCASH "
                . "LEFT JOIN RECEIPTS ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
                . "LEFT JOIN PAYMENTS ON PAYMENTS.RECEIPT = RECEIPTS.ID "
                . "GROUP BY CLOSEDCASH.MONEY "
                . "ORDER BY DATESTART DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $cash = $this->build($row);
            $cashes[] = $cash;
        }
        return $cashes;
    }

    public function getRunning() {
        $cashes = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM CLOSEDCASH WHERE DATESTART NOT NULL AND DATEEND "
                . "IS NULL";
        foreach ($pdo->query($sql) as $db_cash) {
            $cash = CashesService::buildDBCash($db_cash);
            $cashes[] = $cash;
        }
        return $cashes;
    }

    public function getCashRegister($cashRegisterId) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM CLOSEDCASH "
                . "WHERE CASHREGISTER_ID = :crId "
                . "ORDER BY HOSTSEQUENCE DESC");
        if ($stmt->execute(array(':crId' => $cashRegisterId))) {
            if ($row = $stmt->fetch()) {
                return $this->build($row, $pdo);
            }
        }
        return null;
    }

    /** Update open and end date for a cash. */
    public function update($cash) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $startParam = ($cash->isOpened()) ? ':start' : 'NULL';
        $endParam = ($cash->isClosed()) ? ':end' : 'NULL';
        $openCashParam = $cash->openCash !== null ? ':openCash' : 'NULL';
        $closeCashParam = $cash->closeCash !== null ? ':closeCash' : 'NULL';
        $exptCashParam = $cash->expectedCash !== null ? ':exptCash' : 'NULL';
        $stmt = $pdo->prepare("UPDATE CLOSEDCASH SET DATESTART = $startParam, "
                . "DATEEND = $endParam, "
                . "OPENCASH = $openCashParam, CLOSECASH = $closeCashParam, "
                . "EXPECTEDCASH = $exptCashParam "
                . "WHERE MONEY = :id");
        $stmt->bindParam(':id', $cash->id);
        if ($cash->isOpened()) {
            $stmt->bindParam(':start', $db->dateVal($cash->openDate));
        }
        if ($cash->isClosed()) {
            $stmt->bindParam(':end', $db->dateVal($cash->closeDate));
        }
        if ($cash->openCash !== null) {
            $stmt->bindParam(':openCash', $cash->openCash);
        }
        if ($cash->closeCash !== null) {
            $stmt->bindParam(':closeCash', $cash->closeCash);
        }
        if ($cash->expectedCash !== null) {
            $stmt->bindParam(':exptCash', $cash->expectedCash);
        }
        return $stmt->execute();
    }

    /** Create a new cash for the given host and return it.
     * Returns null in case of error.
     */
    public function add($cashRegisterId) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $stmt = $pdo->prepare("INSERT INTO CLOSEDCASH (MONEY, CASHREGISTER_ID, "
                              . "HOSTSEQUENCE) VALUES (:id, :crId, :sequence)");
        $sequence = CashesService::getLastSequence($cashRegisterId, $pdo) + 1;
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':crId', $cashRegisterId);
        $stmt->bindParam(':sequence', $sequence);
        if ($stmt->execute() !== false) {
            return $this->get($id);
        } else {
            return null;
        }
    }

    public function getZTicket($cashId) {
        $pdo = PDOBuilder::getPDO();
        // Get open/close cash fund
        $openCash = null;
        $closeCash = null;
        $cashSql = "SELECT OPENCASH, CLOSECASH FROM CLOSEDCASH "
                . "WHERE MONEY = :id";
        $cashStmt = $pdo->prepare($cashSql);
        $cashStmt->bindParam(":id", $cashId);
        $cashStmt->execute();
        if ($row = $cashStmt->fetch()) {
            $openCash = $row['OPENCASH'];
            $closeCash = $row['CLOSECASH'];
        }
        // Get tickets, cs and customers
        $ticketCount = 0;
        $sales = 0;
        $custCount = null;
        $paymentCount = 0;
        $glbSql = "SELECT COUNT(DISTINCT RECEIPTS.ID) AS TKTS, "
                . "SUM(TICKETLINES.UNITS * TICKETLINES.PRICE) AS SALES, "
                . "SUM(TICKETS.CUSTCOUNT) AS CUSTCOUNT "
                . "FROM RECEIPTS, TICKETS, TICKETLINES "
                . "WHERE RECEIPTS.ID = TICKETLINES.TICKET "
                . "AND RECEIPTS.ID = TICKETS.ID "
                . "AND RECEIPTS.MONEY = :id";
        $glbStmt = $pdo->prepare($glbSql);
        $glbStmt->bindParam(":id", $cashId);
        $glbStmt->execute();
        if ($row = $glbStmt->fetch()) {
            $ticketCount = $row['TKTS'];
            if ($row['SALES'] !== null) {
                $sales = $row['SALES'];
            }
            $custCount = $row['CUSTCOUNT'];
        } else {
            return null;
        }
        // Get consolidated payments by mode
        $payments = array();
        $pmtsSql = "SELECT PAYMENTS.PAYMENT AS TYPE, "
                . "PAYMENTS.CURRENCY AS CURRENCYID, "
                . "SUM(PAYMENTS.TOTAL) AS TOTAL, "
                . "COUNT(PAYMENTS.ID) AS COUNT, "
                . "SUM(PAYMENTS.TOTALCURRENCY) AS TOTALCURRENCY "
                . "FROM PAYMENTS, RECEIPTS "
                . "WHERE PAYMENTS.RECEIPT = RECEIPTS.ID "
                . "AND RECEIPTS.MONEY = :id "
                . "GROUP BY PAYMENTS.PAYMENT, PAYMENTS.CURRENCY";
        $pmtsStmt = $pdo->prepare($pmtsSql);
        $pmtsStmt->bindParam(":id", $cashId);
        $pmtsStmt->execute();
        while ($row = $pmtsStmt->fetch()) {
            $paymentCount += $row['COUNT'];
            $payments[] = new Payment($row['TYPE'], $row['TOTAL'],
                    $row['CURRENCYID'], $row['TOTALCURRENCY']);
        }
        // Get taxes
        $taxes = array();
        $taxSql = "SELECT TAXES.ID AS TAXID, SUM(TAXLINES.BASE) AS BASE, "
                . "SUM(TAXLINES.AMOUNT) AS AMOUNT "
                . "FROM RECEIPTS, TAXLINES, TAXES, TAXCATEGORIES "
                . "WHERE RECEIPTS.ID = TAXLINES.RECEIPT AND "
                . "TAXLINES.TAXID = TAXES.ID AND "
                . "TAXES.CATEGORY = TAXCATEGORIES.ID "
                . "AND RECEIPTS.MONEY = :id "
                . "GROUP BY TAXES.ID";
        $taxStmt = $pdo->prepare($taxSql);
        $taxStmt->bindParam(":id", $cashId);
        $taxStmt->execute();
        while ($row = $taxStmt->fetch()) {
            $taxes[] = array("id" => $row['TAXID'], "base" => $row['BASE'],
                    "amount" => $row['AMOUNT']);
        }
        // Get categories
        $catSales = array();
        $catSql = "SELECT SUM(TICKETLINES.UNITS * TICKETLINES.PRICE) AS SUM, "
                . "CATEGORIES.ID AS CATID "
                . "FROM RECEIPTS, TICKETS, TICKETLINES, PRODUCTS, CATEGORIES "
                . "WHERE RECEIPTS.ID = TICKETLINES.TICKET "
                . "AND RECEIPTS.ID = TICKETS.ID "
                . "AND TICKETLINES.PRODUCT = PRODUCTS.ID "
                . "AND PRODUCTS.CATEGORY = CATEGORIES.ID "
                . "AND RECEIPTS.MONEY = :id "
                . "GROUP BY CATEGORIES.ID";
        $catStmt = $pdo->prepare($catSql);
        $catStmt->bindParam(":id", $cashId);
        $catStmt->execute();
        while ($row = $catStmt->fetch()) {
            $catSales[] = array("id" => $row['CATID'], "amount" => $row['SUM']);
        }
        return new ZTicket($cashId, $openCash, $closeCash, $ticketCount,
                $sales, $paymentCount, $payments, $taxes, $catSales,
                $custCount);
    }
}
