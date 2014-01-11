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

class StockMove {
    // Reasion constants, see com.openbravo.pos.inventory.MovementReason
    // to add missing ones
    const REASON_IN_BUY = 1;
    const REASON_OUT_SELL = -1;
    const REASON_OUT_BACK = -3;

    /** Check if the move in in or out. */
    public static function isIn($reason) {
        return $reason == StockMove::REASON_IN_BUY;
    }

    public $date;
    public $reason;
    public $locationId;
    public $productId;
    public $attrSetInstId;
    public $qty;
    public $price;

    public function __construct($date, $reason, $productId, $locationId,
            $attrSetInstId, $qty, $price) {
        $this->date = $date;
        $this->reason = $reason;
        $this->locationId = $locationId;
        $this->productId = $productId;
        $this->attrSetInstId = $attrSetInstId;
        $this->qty = $qty;
        $this->price = $price;
    }
}

class StockLevel {
    public $id;
    public $productId;
    public $locationId;
    public $attrSetInstId;
    public $security;
    public $max;
    public $qty;

    public static function __build($id, $productId, $locationId, $attrSetInstId,
            $security, $max, $qty) {
        $stock = new StockLevel($productId, $locationId, $attrSetInstId,
                $security, $max, $qty);
        $stock->id = $id;
        return $stock;
    }

    /** Create a stock level. As quantities are set through stock moves
     * leave $qty to null to create security and max levels. In that case
     * also set $attrSetInstId to null as it is ignored.
     */
    public function __construct($productId, $locationId, $attrSetInstId,
            $security, $max, $qty = null) {
        $this->productId = $productId;
        $this->locationId = $locationId;
        $this->attrSetInstId = $attrSetInstId;
        if ($security !== NULL) {
            $this->security = floatval($security);
        } else {
            $this->security = NULL;
        }
        if ($max !== NULL) {
            $this->max = floatval($max);
        } else {
            $this->max = NULL;
        }
        $this->qty = $qty;
    }
}