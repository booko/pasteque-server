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

class Ticket {
    /** Sale ticket type */
    const TYPE_SELL = 0;
    /** Refund ticket type */
    const TYPE_REFUND = 1;
    /** Debt recovery ticket type */
    const TYPE_PAYMENT = 2;

    public $cashId;
    public $type;
    public $label;
    public $userId;
    /** Payment date, as timestamp */
    public $date;
    public $lines;
    public $payments;
    public $customerId;
    public $custCount;
    public $tariffAreaId;

    static function __build($id, $type, $label, $userId, $date, $lines,
            $payments, $cashId, $customerId = null, $custCount = null,
            $tariffAreaId = null) {
        $tkt = new Ticket($type, $label, $userId, $date, $lines, $payments,
                $cashId, $customerId, $custCount, $tariffAreaId);
        $tkt->id = $id;
        return $tkt;
    }

    function __construct($type, $label, $userId, $date, $lines, $payments,
            $cashId, $customerId = null, $custCount = null,
            $tariffAreaId = null) {
        $this->type = $type;
        $this->label = $label;
        $this->userId = $userId;
        $this->date = $date;
        $this->lines = $lines;
        $this->payments = $payments;
        $this->cashId = $cashId;
        $this->customerId = $customerId;
        $this->custCount = $custCount;
        $this->tariffAreaId = $tariffAreaId;
    }

    function getTaxAmounts() {
        $amounts = array();
        foreach ($this->lines as $line) {
            if (isset($amounts[$line->taxId])) {
                $amounts[$line->taxId] += $line->getSubtotal();
            } else {
                $amounts[$line->taxId] = $line->getSubtotal();
            }
        }
        $ta = array();
        foreach ($amounts as $taxId => $base) {
            $ta[] = new TaxAmount($taxId, $base);
        }
        return $ta;
    }
}