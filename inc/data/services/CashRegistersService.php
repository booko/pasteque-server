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

class CashRegistersService extends AbstractService {

    protected static $dbTable = "CASHREGISTERS";
    protected static $dbIdField = "ID";
    protected static $fieldMapping = array(
            "ID" => "id",
            "NAME" => "label",
            "LOCATION_ID" => "locationId",
            "NEXTTICKETID" => "nextTicketId"
    );

    protected function build($row, $pdo = null) {
        return CashRegister::__build($row["ID"], $row["NAME"],
                $row["LOCATION_ID"], $row["NEXTTICKETID"]);
    }

    public function getFromCashId($cashId) {
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT CR.ID, CR.NAME, CR.LOCATION_ID, CR.NEXTTICKETID "
                . "FROM CASHREGISTERS AS CR, CLOSEDCASH "
                . "WHERE CLOSEDCASH.CASHREGISTER_ID = CR.ID "
                . "AND CLOSEDCASH.MONEY = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $cashId);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row !== false) {
            return $this->build($row, $pdo);
        }
    }

    public function incrementNextTicketId($cashId) {
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE CASHREGISTERS SET NEXTTICKETID = (NEXTTICKETID + 1) "
                . "WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $cashId);
        $stmt->execute();
    }
}
