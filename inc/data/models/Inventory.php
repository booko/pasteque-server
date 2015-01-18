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

/** Snapshot of a stock at a given date */
class Inventory {

    public $id;
    public $date;
    public $locationId;

    public static function __build($id, $date, $locationId) {
        $inventory = new Inventory($date, $locationId);
        $inventory->id = $id;
        return $inventory;
    }

    public function __construct($date, $locationId) {
        $this->date = $date;
        $this->locationId = $locationId;
        $this->items = array();
    }

    public function addItem($inventoryItem) {
        $this->items[] = $inventoryItem;
    }

    /** Add 0 quantities to all on sales and missing products in inventory */
    public function fillZero() {
        $prds = ProductsService::getAll(false);
        foreach ($this->items as $item) {
            foreach ($prds as $i => $prd) {
                if ($prd->id == $item->productId) {
                    array_splice($prds, $i, 1);
                    break;
                }
            }
        }
        // Registered products removed, add 0 items
        foreach ($prds as $prd) {
            $this->addItem(new InventoryItem($this->id, $prd->id, null,
                            0, 0, 0, null, null));
        }
    }
}

class InventoryItem {

    public $inventoryId;
    public $productId;
    public $attrSetInstId;
    /** Useable quantity */
    public $qty;
    /** Quantity that cannot be sold and is lost */
    public $lostQty;
    public $defectQty;
    public $missingQty;
    /** Value of an unit in the inventory, average of full value by quantity */
    public $unitValue;

    public function __construct($inventoryId, $productId, $attrSetInstId,
            $qty, $lostQty, $defectQty, $missingQty, $unitValue) {
        $this->inventoryId = $inventoryId;
        $this->productId = $productId;
        $this->attrSetInstId = $attrSetInstId;
        $this->qty = $qty;
        $this->lostQty = $lostQty;
        $this->defectQty = $defectQty;
        $this->missingQty = $missingQty;
        $this->unitValue = $unitValue;
    }

    public function getTotalQty() {
        return $this->qty + $this->lostQty + $this->defectQty
                + $this->missingQty;
    }

}