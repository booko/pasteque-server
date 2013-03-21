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
    public $location;
    public $product_id;
    public $quantity;

    public function __construct($date, $reason, $location, $product_id, $qty) {
        $this->date = $date;
        $this->reason = $reason;
        $this->location = $location;
        $this->product_id = $product_id;
        $this->quantity = $qty;
    }
}

class StockLevel {
    public $id;
    public $product_id;
    public $location;
    public $security;
    public $max;

    public static function __build($id, $product_id, $location, $security, $max) {
        $stock = new StockLevel($product_id, $location, $security, $max);
        $stock->id = $id;
        return $stock;
    }

    public function __construct($product_id, $location, $security, $max) {
        $this->product_id = $product_id;
        $this->location = $location;
        $this->security = intval($security);
        $this->max = intval($max);
    }
}
