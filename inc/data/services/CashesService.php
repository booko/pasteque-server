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

class CashesService extends AbstractService {

    protected static $dbTable = "CLOSEDCASH";
    protected static $dbIdField = "MONEY";
    protected static $fieldMapping = array(
            "id" => "MONEY",
            "host" => "HOST",
            "sequence" => "HOSTSEQUENCE",
            "openDate" => "DATESTART",
            "closeDate" => "DATEEND"
    );

    protected function build($db_cash, $pdo = null) {
        $cash = Cash::__build($db_cash['MONEY'], $db_cash['HOST'],
                              $db_cash['HOSTSEQUENCE'],
                              stdtimefstr($db_cash['DATESTART']),
                              stdtimefstr($db_cash['DATEEND']));
        if (isset($db_cash['TKTS'])) {
            $cash->tickets = $db_cash['TKTS'];
        }
        if (isset($db_cash['TOTAL'])) {
            $cash->total = $db_cash['TOTAL'];
        }
        return $cash;
    }

    private function getLastSequence($host, $pdo) {
        $stmt = $pdo->prepare("SELECT max(HOSTSEQUENCE) FROM CLOSEDCASH WHERE "
                              . "HOST = :host");
        $stmt->execute(array(':host' => $host));
        if ($data = $stmt->fetch()) {
            return $data[0];
        } else {
            return 0;
        }
    }

    public function getAll() {
        $cashes = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT CLOSEDCASH.MONEY, CLOSEDCASH.HOST, "
                . "CLOSEDCASH.HOSTSEQUENCE, CLOSEDCASH.DATESTART, "
                . "CLOSEDCASH.DATEEND, "
                . "COUNT(DISTINCT(RECEIPTS.ID)) as TKTS, "
                . "SUM(PAYMENTS.TOTAL) AS TOTAL "
                . "FROM CLOSEDCASH "
                . "LEFT JOIN RECEIPTS ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
                . "LEFT JOIN PAYMENTS ON PAYMENTS.RECEIPT = RECEIPTS.ID "
                . "GROUP BY CLOSEDCASH.MONEY "
                . "ORDER BY DATESTART DESC";
        foreach ($pdo->query($sql) as $db_cash) {
            $cash = $this->build($db_cash);
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

    public function getHost($host) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM CLOSEDCASH WHERE HOST = :host "
                              . "ORDER BY HOSTSEQUENCE DESC");
        if ($stmt->execute(array(':host' => $host))) {
            if ($row = $stmt->fetch()) {
                return $this->build($row, $pdo);
            }
        }
        return null;
    }

    public function update($cash) {
        $pdo = PDOBuilder::getPDO();
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $startParam = ($cash->isOpened()) ? ':start' : 'NULL';
        $endParam = ($cash->isClosed()) ? ':end' : 'NULL';
        $stmt = $pdo->prepare("UPDATE CLOSEDCASH SET DATESTART = $startParam, "
                              . "DATEEND = $endParam WHERE MONEY = :id");
        $stmt->bindParam(':id', $cash->id);
        if ($cash->isOpened()) {
            $open = stdstrftime($cash->openDate);
            $stmt->bindParam(':start', $open, \PDO::PARAM_INT);
        }
        if ($cash->isClosed()) {
            $close = stdstrftime($cash->closeDate);
            $stmt->bindParam(':end', $close, \PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    /** Create a new cash for the given host and return it.
     * Returns null in case of error.
     */
    public function add($host) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $stmt = $pdo->prepare("INSERT INTO CLOSEDCASH (MONEY, HOST, "
                              . "HOSTSEQUENCE) VALUES (:id, :host, :sequence)");
        $sequence = CashesService::getLastSequence($host, $pdo) + 1;
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':host', $host);
        $stmt->bindParam(':sequence', $sequence);
        if ($stmt->execute() !== false) {
            return $this->get($id);
        } else {
            return null;
        }
    }
}

?>