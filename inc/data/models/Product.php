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

class Product {

    public $id;
    public $reference;
    public $barcode;
    public $label;
    public $priceBuy;
    public $priceSell;
    public $visible;
    public $scaled;
    public $categoryId;
    public $providerId;
    public $dispOrder;
    public $taxCatId;
    public $attributeSetId;
    public $hasImage;
    public $discountEnabled;
    public $discountRate;

    static function __build($id, $ref, $label, $priceSell, $categoryId, $providerId,
            $dispOrder, $taxCatId, $visible, $scaled, $priceBuy = null,
            $attributeSetId = null, $barcode = null, $hasImage = false,
            $discountEnabled = false, $discountRate = 0.0) {
        $prd = new Product($ref, $label, $priceSell, $categoryId, $providerId,
                $dispOrder,
                $taxCatId, $visible, $scaled, $priceBuy,
                $attributeSetId, $barcode, $hasImage,
                $discountEnabled, $discountRate);
        $prd->id = $id;
        return $prd;
    }

    function __construct($ref, $label, $priceSell, $categoryId, $providerId,
            $dispOrder,
            $taxCatId, $visible, $scaled, $priceBuy = null,
            $attributeSetId = null, $barcode = null, $hasImage = false,
            $discountEnabled = false, $discountRate = 0.0) {
        $this->reference = $ref;
        $this->label = $label;
        $this->priceSell = $priceSell;
        $this->visible = $visible;
        $this->scaled = $scaled;
        $this->barcode = $barcode;
        $this->priceBuy = $priceBuy;
        $this->categoryId = $categoryId;
        $this->providerId = $providerId;
        $this->dispOrder = $dispOrder;
        $this->taxCatId = $taxCatId;
        $this->attributeSetId = $attributeSetId;
        $this->hasImage = $hasImage;
        $this->discountEnabled = $discountEnabled;
        $this->discountRate = $discountRate;
    }

    function getTotalPrice() {
        $taxCat = TaxesService::get($this->taxCatId);
        $currentTax = $this->taxCat->getCurrentTax();
        if ($currentTax != null) {
            return $this->priceSell * (1 + $currentTax->rate);
        } else {
            return $this->priceSell;
        }
    }

    function getMargin() {
        if ($this->priceBuy !== null) {
            return $this->priceSell / $this->priceBuy;
        } else {
            return null;
        }
    }
}

?>
