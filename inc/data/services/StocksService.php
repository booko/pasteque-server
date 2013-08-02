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
        $lvl = StockLevel::__build($db_lvl['ID'], $db_lvl['PRODUCT'],
                $db_lvl['LOCATION'], $db_lvl['STOCKSECURITY'],
                $db_lvl['STOCKMAXIMUM']);
        return $lvl;
    }

    static function getLocationId($locationName) {
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM LOCATIONS WHERE NAME LIKE :loc";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":loc", $locationName);
        $stmt->execute();
        if ($row = $stmt->fetch()) {
            return $row['ID'];
        } else {
            return NULL;
        }
    }

    static function getQties($warehouseId = NULL) {
        $qties = array();
        $pdo = PDOBuilder::getPDO();
        if ($warehouseId === NULL) {
            $sql = "SELECT PRODUCT, SUM(UNITS) FROM STOCKCURRENT "
                    . "GROUP BY PRODUCT";
            $stmt = $pdo->prepare($sql);
        } else {
            $sql = "SELECT PRODUCT, SUM(UNITS) FROM STOCKCURRENT "
                    . "WHERE LOCATION = :loc GROUP BY PRODUCT";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':loc', $warehouseId);
        }
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $qties[$row[0]] = floatval($row[1]);
        }
        return $qties;
    }

    static function getQty($productId, $warehouseId = NULL) {
        $pdo = PDOBuilder::getPDO();
        if ($warehouseID === NULL) {
            $stmt = $pdo->prepare("SELECT SUM(UNITS) FROM STOCKCURRENT WHERE "
                    . "PRODUCT = :id GROUP BY PRODUCT");
            $stmt->bindParam(":id", $productId);
            $stmt->execute();
            $res = $stmt->fetchAll();
            return floatval($res[0]);
        } else {
            $stmt = $pdo->prepare("SELECT UNITS FROM STOCKCURRENT WHERE "
                    . "PRODUCT = :id AND LOCATION = :loc");
            $stmt->bindParam(":id", $productId);
            $stmt->bindParam(":loc", $warehouseId);
            $stmt->execute();
            $ret = $stmt->fetchAll();
            foreach ($ret as $key => $val) {
                $ret[$key] = floatval($ret[$key]);
            }
        }
    }

    static function getLevels($warehouseId = NULL) {
        $pdo = PDOBuilder::getPDO();
        $lvls = array();
        if ($warehouseId === NULL) {
            $sql = "SELECT * FROM STOCKLEVEL";
            $stmt = $pdo->prepare($sql);
        } else {
            $sql = "SELECT * FROM STOCKLEVEL WHERE LOCATION = :loc";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':loc', $warehouseId);
        }
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $lvl = StocksService::buildDBLevel($row);
            $lvls[$row['PRODUCT']] = $lvl;
        }
        return $lvls;
    }

    static function getLevel($productId, $warehouseId = NULL) {
        $pdo = PDOBuilder::getPDO();
        $lvl = array();
        if ($warehouseId === NULL) {
            $sql = "SELECT * FROM STOCKLEVEL WHERE PRODUCT = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":id", $productId);
        } else {
            $sql = "SELECT * FROM STOCKLEVEL WHERE PRODUCT = :id "
                    . "AND LOCATION = :loc";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":id", $productId);
            $stmt->bindParam(":loc", $warehouseId);
        }
        $stmt->execute();
        if ($row = $stmt->fetch()) {
            $lvl = StocksService::buildDBLevel($row);
            return $lvl;
        }
        return NULL;
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

    static function addMove($move) {
        $pdo = PDOBuilder::getPDO();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        $qty = StockMove::isIn($move->reason)
                ? $move->quantity : $move->quantity * -1;
        // Update STOCKCURRENT
        $stockSql = "UPDATE STOCKCURRENT SET UNITS = (UNITS + :qty) "
                . "WHERE LOCATION = :loc AND PRODUCT = :prd "
                . "AND ATTRIBUTESETINSTANCE_ID IS NULL";
        $stockStmt = $pdo->prepare($stockSql);
        $stockStmt->bindParam(":qty", $qty);
        $stockStmt->bindParam(":loc", $move->location);
        $stockStmt->bindParam(":prd", $move->product_id);
        $exec = $stockStmt->execute();
        if ($exec !== FALSE && $stockStmt->rowcount() == 0) {
            // Unable to update, insert
            $stockSql = "INSERT INTO STOCKCURRENT (LOCATION, PRODUCT, "
                    . "ATTRIBUTESETINSTANCE_ID, UNITS) "
                    . "VALUES (:loc, :prd, NULL, :qty)";
            $stockStmt = $pdo->prepare($stockSql);
            $stockStmt->bindParam(":qty", $qty);
            $stockStmt->bindParam(":loc", $move->location);
            $stockStmt->bindParam(":prd", $move->product_id);
            $stockStmt->execute();
        }
        if ($stockStmt->rowcount() == 0) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return FALSE;
        }
        // Update STOCKDIARY
        $id = md5(time() . rand());
        $diarySql = "INSERT INTO STOCKDIARY (ID, DATENEW, REASON, LOCATION, "
                . "PRODUCT, ATTRIBUTESETINSTANCE_ID, UNITS, PRICE) "
                . "VALUES (:id, :date, :reason, :loc, :prd, NULL, :qty, :price)";
        $diaryStmt = $pdo->prepare($diarySql);
        $diaryStmt->bindParam(":id", $id);
        $diaryStmt->bindParam(":date", $move->date);
        $diaryStmt->bindParam(":reason", $move->reason);
        $diaryStmt->bindParam(":loc", $move->location);
        $diaryStmt->bindParam(":prd", $move->product_id);
        $diaryStmt->bindParam(":qty", $qty);
        $diaryStmt->bindValue(":price", 0.0);
        if ($diaryStmt->execute()) {
            if ($newTransaction) {
                $pdo->commit();
            }
            return TRUE;
        } else {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return FALSE;
        }
    }
}

?>