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

class SharedTicket {

    public $id;
    public $label;
    public $customerId;
    public $custCount;
    public $tariffAreaId;
    public $discountProfileId;
    public $discountRate;
    public $lines;

    static function __build($id, $label, $customerId, $custCount, $tariffAreaId,
            $discountProfileId, $discountRate) {
        $ticket = new SharedTicket($label, $customerId, $custCount,
                $tariffAreaId, $discountProfileId, $discountRate);
        $ticket->id = $id;
        return $ticket;
    }

    public function __construct($label, $customerId, $custCount, $tariffAreaId,
            $discountProfileId, $discountRate) {
        $this->label = $label;
        $this->customerId = $customerId;
        $this->custCount = $custCount;
        $this->tariffAreaId = $tariffAreaId;
        $this->discountProfileId = $discountProfileId;
        $this->discountRate = $discountRate;
        $this->lines = array();
    }

    public function addProduct($sharedTicketLine) {
        $this->lines[] = $sharedTicketLine;
    }
}

class SharedTicketLines {

    public $id;
    public $sharedTicketId;
    public $dispOrder;
    public $productId;
    public $taxId;
    public $quantity;
    public $discountRate;
    public $price;
    public $attributes;

    public function __build($id, $sharedTicketId, $dispOrder, $productId,
            $taxId, $quantity, $discountRate, $price, $attributes) {
        $ticketLine = new SharedTicketLines($sharedTicketId, $dispOrder,
                $productId, $taxId, $quantity, $discountRate, $price,
                $attributes);
        $ticketLine->id = $id;
        return $ticketLine;
    }

    public function __construct($sharedTicketId, $dispOrder, $productId,
            $taxId, $quantity, $discountRate, $price, $attributes) {
        $this->sharedTicketId = $sharedTicketId;
        $this->dispOrder = $dispOrder;
        $this->productId = $productId;
        $this->taxId = $taxId;
        $this->quantity = $quantity;
        $this->discountRate = $discountRate;
        $this->price = $price;
        $this->attributes = $attributes;
    }
}