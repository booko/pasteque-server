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

class TariffArea {

    public $id;
    public $label;
    public $dispOrder;
    public $prices;
    public $notes;

    static function __build($id, $label, $dispOrder, $notes="") {
        $area = new TariffArea($label, $dispOrder, $notes);
        $area->id = $id;
        return $area;
    }

    function __construct($label, $dispOrder, $notes="") {
        $this->label = $label;
        $this->dispOrder = $dispOrder;
        $this->notes = $notes;
        $this->prices = array();
    }

    function addPrice($productId, $price) {
        $this->prices[] = new TariffAreaPrice($productId, $price);
    }

    function getPrice($productId) {
        foreach ($this->prices as $price) {
            if ($price->productId == $productId) {
                return $price->price;
            }
        }
        return null;
    }

    function getPrices() {
        return $this->prices;
    }

    function getNotes() {
        return $this->notes;
    }
}

class TariffAreaPrice {

    public $productId;
    public $price;

    public function __construct($prdId, $price) {
        $this->productId = $prdId;
        $this->price = $price;
    }
}
