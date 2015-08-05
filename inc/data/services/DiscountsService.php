<?php
//    Pastèque API
//
//    Copyright (C) 2015 Scil (http://scil.coop)
//    Philippe Pary
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

class DiscountsService {

    private static function buildDBDis($db_dis) {
        return Discount::__build($db_dis['ID'], $db_dis['LABEL'], $db_dis['STARTDATE'],
                $db_dis['ENDDATE'], $db_dis['RATE'], $db_dis['BARCODE'],
                $db_dis['BARCODETYPE'],$db_dis['DISPORDER']);
    }

    static function getAll() {
        $diss = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM DISCOUNTS";
        $sql .= " ORDER BY DISPORDER ASC, LABEL ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($db_dis = $stmt->fetch()) {
            $dis = DiscountsService::buildDBDis($db_dis);
            $diss[] = $dis;
        }
        return $diss;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM DISCOUNTS WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                $dis = DiscountsService::buildDBDis($row);
                return $dis;
            }
        }
        return null;
    }

    static function updateDis($dis, $image = "") {
        if ($dis->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE DISCOUNTS SET LABEL = :label, STARTDATE = :startDate, "
                . "ENDDATE = :endDate, RATE = :rate, BARCODE = :barcode, "
                . "BARCODETYPE = :barcodeType, DISPORDER = :order";
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":label", $dis->label);
        $stmt->bindParam(":startDate", $dis->startDate);
        $stmt->bindParam(":endDate", $dis->endDate);
        $stmt->bindParam(":rate", $dis->rate);
        $stmt->bindParam(":barcode", $dis->barcode);
        $stmt->bindParam(":barcodeType", $dis->barcodeType);
        $stmt->bindParam(":order", $dis->dispOrder);
        $stmt->bindParam(":id", $dis->id);
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
        }
    }

    static function createDis($dis, $image = null) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $sql = "INSERT INTO DISCOUNTS (ID, LABEL, STARTDATE, ENDDATE, "
                . "RATE, BARCODE, BARCODETYPE, DISPORDER) "
                . "VALUES (:id, :label, :startDate, :endDate, :rate, "
                . ":barcode, :barcodeType, :order)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":label", $dis->label);
        $stmt->bindParam(":startDate", $dis->startDate);
        $stmt->bindParam(":endDate", $dis->endDate);
        $stmt->bindParam(":rate", $dis->rate);
        $stmt->bindParam(":barcode", $dis->barcode);
        $stmt->bindParam(":barcodeType", $dis->barcodeType);
        $stmt->bindParam(":order", $dis->dispOrder);
        $stmt->bindParam(":id", $dis->id);
        if ($stmt->execute() !== false) {
            return $id;
        } else {
            return false;
        }
    }

    static function deleteDis($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare('DELETE FROM DISCOUNTS WHERE ID = :id');
        return $stmt->execute(array(':id' => $id));
    }

}
