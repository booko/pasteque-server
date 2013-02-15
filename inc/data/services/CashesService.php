<?php
//    Pastèque Web back office
//
//    Copyright (C) 2013 Scil (http://scil.coop)
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

class CashesService {

    private static function buildDBCash($db_cash) {
        $cash = Cash::__build($db_cash['id'], $db_cash['host'],
                $db_cash['sequence'], stdtimefstr($db_cash['start']),
                stdtimefstr($db_cash['end']));
        return $cash;
    }

    private static function getLastSequence($host, $pdo) {
        $stmt = $pdo->prepare("SELECT max(sequence) FROM cashsessions WHERE "
                . "HOST = :host");
        $stmt->bindParam(":host", $host, \PDO::PARAM_STR);
        $stmt->execute();
        if ($data = $stmt->fetch()) {
            return $data[0];
        } else {
            return NULL;
        }
    }

    static function getAll() {
        $cashes = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM cashsessions";
        foreach ($pdo->query($sql) as $db_cash) {
            $cash = CashesService::buildDBCash($db_cash);
            $cashes[] = $cash;
        }
        return $cashes;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM cashsessions WHERE id = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return CashesService::buildDBCash($row);
            }
        }
        return NULL;
    }

    static function getHost($host) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM cashsessions WHERE host = :host "
                . "ORDER BY sequence DESC LIMIT 1");
        $stmt->bindParam(":host", $host, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return CashesService::buildDBCash($row);
            }
        }
        return NULL;
    }

    static function update($cash) {
        $pdo = PDOBuilder::getPDO();
        $open = NULL;
        $close = NULL;
        if ($cash->isOpened()) {
            $open = stdstrftime($cash->openDate);
        }
        if ($cash->isClosed()) {
            $close = stdstrftime($cash->closeDate);
        }
        $stmt = $pdo->prepare("UPDATE cashsessions SET start = :start, "
                . "end = :end WHERE id = :id");
        $stmt->bindParam(":start", $open, \PDO::PARAM_INT);
        $stmt->bindParam(":end", $close, \PDO::PARAM_INT);
        $stmt->bindParam(':id', $cash->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    static function add($host) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO cashsessions (host, sequence) "
                . "VALUES (:host, :sequence)");
        $sequence = CashesService::getLastSequence($host, $pdo) + 1;
        $stmt->bindParam(":host", $host, \PDO::PARAM_STR);
        $stmt->bindParam(":sequence", $sequence, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>
