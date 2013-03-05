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

class StocksService {

    private static function buildDBLevel($db_lvl) {
        $lvl = StockLevel::__build($db_ldl['ID'], $db_lvl['PRODUCT'],
                $db_lvl['LOCATION'], $db_lvl['STOCKSECURITY'],
                $db_lvl['STOCKMAXIMUM']);
        return $lvl;
    }


    static function getQties($warehouseId = NULL) {
        $qties = array();
        $pdo = PDOBuilder::getPDO();
        if ($warehouseId === NULL) {
            $sql = "SELECT PRODUCT, SUM(UNITS) FROM STOCKCURRENT "
                    . "GROUP BY PRODUCT";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $qties[$row[0]] = $row[1];
            }
            return $qties;
        } else {
            // TODO: multiple warehouses
            return NULL;
        }
    }

    static function getQty($productId, $warehouseId = NULL) {
        $pdo = PDOBuilder::getPDO();
        if ($warehouseID === NULL) {
            $stmt = $pdo->prepare("SELECT SUM(UNITS) FROM STOCKCURRENT WHERE "
                    . "PRODUCT = :id GROUP BY PRODUCT");
            $stmt->bindParam(":id", $productId);
            $stmt->execute();
            $res = $stmt->fetchAll();
            return $res[0];
        } else {
            $stmt = $pdo->prepare("SELECT UNITS FROM STOCKCURRENT WHERE "
                    . "PRODUCT = :id AND LOCATION = :loc");
            $stmt->bindParam(":id", $productId);
            $stmt->bindParam(":loc", $warehouseId);
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    static function getLevels($warehouseId = NULL) {
        $pdo = PDOBuilder::getPDO();
        $lvls = array();
        if ($warehouseId === NULL) {
            $sql = "SELECT * FROM STOCKLEVEL";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                $lvl = StocksService::buildDBLevel($row);
                $lvls[$row['PRODUCT']] = $lvl;
            }
            return $lvls;
        }
        // TODO: multiple warehouses
    }

    static function getLevel($productId, $warehouseId = NULL) {
        $pdo = PDOBuilder::getPDO();
        $lvl = array();
        if ($warehouseId === NULL) {
            $sql = "SELECT * FROM STOCKLEVEL WHERE PRODUCT = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":id", $productId);
            $stmt->execute();
            if ($row = $stmt->fetch()) {
                $lvl = StocksService::buildDBLevel($row);
                return $lvl;
            }
            return NULL;
        }
        // TODO: multiple warehouses
    }

    static function createLevel($level) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $stmt = $pdo->prepare("INSERT INTO STOCKLEVEL (ID,PRODUCT, LOCATION, "
                . "STOCKSECURITY, STOCKMAXIMUM) VALUES (:id, :prd, :loc, :sec, "
                . ":max)");
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":prd", $level->product);
        $stmt->bindValue(":loc", "0");
        $stmt->bindParam(":sec", $level->security);
        $stmt->bindParam(":max", $level->max);
        if ($stmt->execute()) {
            return $id;
        } else {
            return FALSE;
        }
    }

    static function updateLevel($level) {
        if (!isset($level->id)) {
            return FALSE;
        }
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("UPDATE STOCKLEVEL SET STOCKSECURITY = :sec, "
                . "STOCKMAXIMUM = :max WHERE ID = :id");
        $stmt->bindParam(":id", $level->id);
        $stmt->bindParam(":sec", $level->security);
        $stmt->bindParam(":max", $level->max);
        return $stmt->execute();
    }
}

?>
