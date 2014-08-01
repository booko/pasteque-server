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

class ZTicket {

    public $cashId;
    public $openCash;
    public $closeCash;
    public $ticketCount;
    /** Count of customers, may be null */
    public $custCount;
    public $paymentCount;
    public $cs;
    /** Array of payments */
    public $payments;
    /** Array of taxes {id, base, amount} */
    public $taxes;
    /** Array of categories {id, amount} */
    public $catSales;

    function __construct($cashId, $openCash, $closeCash, $ticketCount, $cs,
            $paymentCount, $payments, $taxes, $categories, $custCount) {
        $this->cashId = $cashId;
        $this->openCash = $openCash;
        $this->closeCash = $closeCash;
        $this->ticketCount = (int) $ticketCount;
        $this->cs = $cs;
        $this->paymentCount = $paymentCount;
        $this->payments = $payments;
        $this->taxes = $taxes;
        $this->catSales = $categories;
        $this->custCount = $custCount;
    }

}