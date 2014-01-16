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

    public $id;
    public $ticketId;
    public $cashId;
    public $type;
    public $userId;
    /** Payment date, as timestamp */
    public $date;
    public $lines;
    public $payments;
    public $customerId;
    public $custCount;
    public $tariffAreaId;
    public $discountRate;
    public $discountProfileId;

    static function __build($id, $ticketId, $type, $userId, $date, $lines,
            $payments, $cashId, $customerId = null, $custCount = null,
            $tariffAreaId = null, $discountRate = 0.0,
            $discountProfileId = null) {
        $tkt = new Ticket($type, $label, $userId, $date, $lines, $payments,
                $cashId, $customerId, $custCount, $tariffAreaId, $discountRate,
                $discountProfileId);
        $tkt->id = $id;
        $tkt->ticketId = $ticketId;
        return $tkt;
    }

    function __construct($type, $userId, $date, $lines, $payments,
            $cashId, $customerId = null, $custCount = null,
            $tariffAreaId = null, $discountRate = 0.0,
            $discountProfileId = null) {
        $this->type = $type;
        $this->userId = $userId;
        $this->date = $date;
        $this->lines = $lines;
        $this->payments = $payments;
        $this->cashId = $cashId;
        $this->customerId = $customerId;
        $this->custCount = $custCount;
        $this->tariffAreaId = $tariffAreaId;
        $this->discountRate = $discountRate;
        $this->discountProfileId = $discountProfileId;
    }

    function getTaxAmounts() {
        $amounts = array();
        foreach ($this->lines as $line) {
            if (isset($amounts[$line->taxId])) {
                $amounts[$line->taxId] += $line->getSubtotal($this->discountRate);
            } else {
                $amounts[$line->taxId] = $line->getSubtotal($this->discountRate);
            }
        }
        $ta = array();
        foreach ($amounts as $taxId => $base) {
            $ta[] = new TaxAmount($taxId, $base);
        }
        return $ta;
    }
}