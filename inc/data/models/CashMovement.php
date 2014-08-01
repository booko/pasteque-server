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

/** Cash in/out. It has its own payment type */
class CashMovement extends Payment {

    const TYPE_CASHIN = "cashin";
    const TYPE_CASHOUT = "cashout";

    public $cashId;
    public $receiptId;
    public $date;
    public $note;

    static function __build($cashId, $rcptId, $date, $pmtId, $type, $amount,
            $currencyId, $currencyAmount, $note) {
        $mvt = new CashMovement($cashId, $date, $type, $amount, $currencyId,
                $currencyAmount, $note);
        $mvt->id = $pmtId;
        $mvt->receiptId = $rcptId;
    }

    function __construct($cashId, $date, $type, $amount, $currencyId,
            $currencyAmount, $note) {
        parent::__construct($type, $amount, $currencyId, $currencyAmount);
        $this->cashId = $cashId;
        $this->date = $date;
        $this->note = $note;
    }
}