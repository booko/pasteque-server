<?php
//    Pastèque API
//
//    Copyright (C) 2012-2015 Scil (http://scil.coop)
//    Cédrci Houbart, Philippe Pary
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

    public function updateCat($cashReg) {
        if ($cashReg->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE CASHREGISTERS SET NAME = :name, LOCATION_ID = :locId "
                . "WHERE ID = :id"; // Don't update nextTicketId
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $cashReg->label);
        $stmt->bindParam(":locId", $cashReg->locationId);
        $stmt->bindParam(":id", $cashReg->id);
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
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

    public function setNextTicketId($nextTicketId,$cashId) {
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE CASHREGISTERS SET NEXTTICKETID =  :nextTicketId "
                . "WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":nextTicketId", $nextTicketId);
        $stmt->bindParam(":id", $cashId);
        $stmt->execute();
    }
}
