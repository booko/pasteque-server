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

class Payment {

    public $id;
    public $type;
    public $amount;
    public $currencyId;
    public $currencyAmount;

    static function __build($id, $type, $amount, $currencyId, $currencyAmount) {
        $payment = new Payment($type, $amount, $currencyId, $currencyAmount);
        $payment->id = $id;
        return $payment;
    }

    function __construct($type, $amount, $currencyId, $currencyAmount) {
        $this->type = $type;
        $this->amount = $amount;
        $this->currencyId = $currencyId;
        $this->currencyAmount = $currencyAmount;
    }
}