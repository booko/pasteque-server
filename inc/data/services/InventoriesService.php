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

class InventoriesService extends AbstractService {

    protected static $dbTable = "STOCK_INVENTORY";
    protected static $dbIdField = "ID";
    protected static $fieldMapping = array(
            "ID" => "id",
            "DATE" => array("type" => DB::DATE, "attr" => "date"),
            "LOCATION_ID" => "locationId",
    );

    protected function build($dbInv, $pdo = null) {
        $db = DB::get();
        $inv = Inventory::__build($dbInv['ID'], $db->readDate($dbInv['DATE']),
                $dbInv['LOCATION_ID'], $dbInv['PRODUCT_ID'],
                $dbInv['ATTRSETINST_ID'], $dbInv['QTY'], $dbInv['LOSTQTY'],
                $dbInv['DEFECTQTY'], $dbInv['MISSINGQTY'], $dbInv['UNITVALUE']);
        $stmt = $pdo->prepare("SELECT * FROM STOCK_INVENTORYCONTENT WHERE "
                . "INVENTORY_ID = :id");
        $stmt->bindParam(":id", $dbInv['ID']);
        $stmt->execute();
        while($row = $stmt->fetch()) {
            $item = new InventoryItem($row['PRODUCT_ID'],
                    $row['ATTRSETINST_ID'], $row['QTY'], $row['LOSTQTY'],
                    $row['DEFECTQTY'], $row['MISSINGQTY'], $row['UNITVALUE']);
            $inv->addItem($item);
        }
        return $inv;
    }

    /** Create an inventory. If date, missingQty or unitValue is null they are
     * computed from current stock and current date. */
    public function create($inventory) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        if ($inventory->date === null) {
            // Set date to now
            $inventory->date = time();
        }
        // Insert inventory
        $invStmt = $pdo->prepare("INSERT INTO STOCK_INVENTORY (DATE, "
                . "LOCATION_ID) VALUES (:date, :locId)");
        $invStmt->bindParam(":date", $db->dateVal($inventory->date));
        $invStmt->bindParam(":locId", $inventory->locationId);
        if ($invStmt->execute() !== false) {
            $id = $pdo->lastInsertId(static::$dbTable . "_"
                    . static::$dbIdField . "_seq");
        } else {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Parse and insert items
        $stmt = $pdo->prepare("INSERT INTO STOCK_INVENTORYITEM (INVENTORY_ID, "
                . "PRODUCT_ID, ATTRSETINST_ID, QTY, LOSTQTY, DEFECTQTY, "
                . "MISSINGQTY, UNITVALUE) VALUES "
                . "(:id, :prdId, :attrId, :qty, :lostQty, :defectQty, "
                . ":missingQty, :unitValue)");
        $stmt->bindParam(":id", $id);
        foreach ($inventory->items as $item) {
            if ($item->missingQty === null) {
                // Check for missing count on current stock
                $lvl = StocksService::getLevel($item->productId,
                        $inventory->locationId, $item->attrSetInstId);
                if ($lvl != null) {
                    $qty = $lvl->qty;
                    $invQty = $item->qty + $item->lostQty
                            + $item->defectQty;
                    $item->missingQty = $qty - $invQty;
                } else {
                    $item->missingQty = 0;
                }
            }
            if ($item->unitValue === null) {
                // Compute average value
                $sql = "SELECT REASON, UNITS, PRICE FROM STOCKDIARY "
                        . "WHERE LOCATION = :loc AND PRODUCT = :prd";
                if ($item->attrSetInstId !== null) {
                    $sql .= " AND ATTRIBUTESETINSTANCE_ID = :attr";
                } else {
                    $sql .= " AND ATTRIBUTESETINSTANCE_ID IS NULL";
                }
                $sql .= " ORDER BY DATENEW DESC";
                $stmtVal = $pdo->prepare($sql);
                $stmtVal->bindParam(":loc", $item->locationId);
                $stmtVal->bindParam(":prd", $item->productId);
                if ($item->attrSetInstId !== null) {
                    $stmtVal->bindParam(":attr", $item->attrSetInstId);
                }
                $stmtVal->execute();
                $units = 0;
                $expectedUnits = $item->getTotalQty();
                $price = 0.0;
                while ($row = $stmtVal->fetch() && $units != $expectedUnits) {
                    $units += $row['UNITS'];
                    if ($row['UNITS'] > 0) {
                        $price += $row['PRICE']; 
                    } else {
                        $price -= $row['PRICE'];
                    }
                }
                if ($units != 0) {
                    $item->unitValue = $price / $units;
                } else {
                    $item->unitValue = 0;
                }
            }
            // Insert
            $stmt->bindParam(":prdId", $item->productId);
            $stmt->bindParam(":attrId", $item->attrSetInstId);
            $stmt->bindParam(":qty", $item->qty);
            $stmt->bindParam(":lostQty", $item->lostQty);
            $stmt->bindParam(":defectQty", $item->defectQty);
            $stmt->bindParam(":missingQty", $item->missingQty);
            $stmt->bindParam(":unitValue", $item->unitValue);
            if ($stmt->execute() === false) {
                var_dump($stmt->errorInfo());
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

}