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
    public $customer_id;
    public $tariffarea_id;
    public $discount_profil_id;
    public $discount_rate;
    public $lines;

    static function __build($id, $label, $customer_id, $tariffarea_id,
            $discount_profil_id, $discount_rate) {
        $ticket = new SharedTicket($label, $customer_id, $tariffarea_id,
                $discount_profil_id, $discount_rate);
        $ticket->id = $id;
        return $ticket;
    }

    public function __construct($label, $customer_id, $tariffarea_id,
            $discount_profil_id, $discount_rate) {
        $this->label = $label;
        $this->customer_id = $customer_id;
        $this->tariffarea_id = $tariffarea_id;
        $this->discount_profil_id = $discount_profil_id;
        $this->discount_rate = $discount_rate;
        $this->lines = array();
    }

    public function addProduct($sharedTicketLine) {
        $this->lines[] = $sharedTicketLine;
    }
}

class SharedTicketLines {

    public $id;
    public $shared_ticket_id;
    public $line;
    public $product_id;
    public $quantity;
    public $discount_rate;
    public $price;
    public $attributes;

    public function __build($id, $shared_ticket_id, $line, $product_id,
            $quantity, $discount_rate, $price, $attributes) {
        $ticketLine = new SharedTicketLines($shared_ticket_id, $line,
                $product_id, $quantity, $discount_rate, $price, $attributes);
        $ticketLine->id = $id;
        return $ticketLine;
    }

    public function __construct($shared_ticket_id, $line, $product_id,
            $quantity, $discount_rate, $price, $attributes) {
        $this->shared_ticket_id = $shared_ticket_id;
        $this->line = $line;
        $this->product_id = $product_id;
        $this->quantity = $quantity;
        $this->discount_rate = $discount_rate;
        $this->price = $price;
        $this->attributes = $attributes;
    }
}