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

/** Light version without external references */
class ProductLight {

    public $id;
    public $reference;
    public $barcode;
    public $label;
    public $priceBuy;
    public $priceSell;
    public $visible;
    public $scaled;
    public $discountEnabled;
    public $discountRate;

    static function __build($id, $ref, $label, $priceSell, $visible, $scaled,
            $barcode = null, $priceBuy = null, $discountEnabled = FALSE,
            $discountRate = 0.0) {
        $prd = new ProductLight($ref, $label, $priceSell, $visible, $scaled,
                $barcode, $priceBuy, $discountEnabled, $discountRate);
        $prd->id = $id;
        return $prd;
    }

    function __construct($ref, $label, $priceSell, $visible, $scaled,
            $barcode = null, $priceBuy = null, $discountEnabled = FALSE,
            $discountRate = 0.0) {
        $this->reference = $ref;
        $this->label = $label;
        $this->priceSell = $priceSell;
        $this->visible = $visible;
        $this->scaled = $scaled;
        $this->barcode = $barcode;
        $this->priceBuy = $priceBuy;
        $this->discountEnabled = $discountEnabled;
        $this->discountRate = $discountRate;
    }
}

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
    public $dispOrder;
    public $taxCatId;
    public $attributesSet;
    /** Contains the binary of the image. NULL if not any.
     * For the services set this value to "" keep data unchanged.
     */
    public $image;
    public $discountEnabled;
    public $discountRate;

    static function __build($id, $ref, $label, $priceSell, $category,
            $dispOrder, $taxCatId, $visible, $scaled, $priceBuy = null,
            $attributesSet = null, $barcode = null, $image = NULL,
            $discountEnabled = false, $discountRate = 0.0) {
        $prd = new Product($ref, $label, $priceSell, $category, $dispOrder,
                $taxCatId, $visible, $scaled, $priceBuy,
                $attributesSet, $barcode, $image,
                $discountEnabled, $discountRate);
        $prd->id = $id;
        return $prd;
    }

    function __construct($ref, $label, $priceSell, $categoryId, $dispOrder,
            $taxCatId, $visible, $scaled, $priceBuy = null,
            $attributesSet = null, $barcode = null, $image = null,
            $discountEnabled = false, $discountRate = 0.0) {
        $this->reference = $ref;
        $this->label = $label;
        $this->priceSell = $priceSell;
        $this->visible = $visible;
        $this->scaled = $scaled;
        $this->barcode = $barcode;
        $this->priceBuy = $priceBuy;
        $this->categoryId = $categoryId;
        $this->dispOrder = $dispOrder;
        $this->taxCatId = $taxCatId;
        $this->attributesSet = $attributesSet;
        $this->image = $image;
        $this->discountEnabled = $discountEnabled;
        $this->discountRate = $discountRate;
    }

    function getTotalPrice() {
        $currentTax = $this->tax_cat->getCurrentTax();
        if ($currentTax != null) {
            return $this->price_sell * (1 + $currentTax->rate);
        } else {
            return $this->price_sell;
        }
    }

    function getMargin() {
        if ($this->price_buy !== null) {
            return $this->price_sell / $this->price_buy;
        } else {
            return null;
        }
    }
}

?>
