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
    public $price_buy;
    public $price_sell;
    public $visible;
    public $scaled;

    static function __build($id, $ref, $label, $price_sell, $visible, $scaled,
                         $barcode = null, $price_buy = null) {
        $prd = new ProductLight($ref, $label, $price_sell, $visible, $scaled,
                                $barcode, $price_buy);
        $prd->id = $id;
        return $prd;
    }

    function __construct($ref, $label, $price_sell, $visible, $scaled,
                         $barcode = null, $price_buy = null) {
        $this->reference = $ref;
        $this->label = $label;
        $this->price_sell = $price_sell;
        $this->visible = $visible;
        $this->scaled = $scaled;
        $this->barcode = $barcode;
        $this->price_buy = $price_buy;
    }
}

class Product {

    public $id;
    public $reference;
    public $barcode;
    public $label;
    public $price_buy;
    public $price_sell;
    public $visible;
    public $scaled;
    public $category;
    public $disp_order;
    public $tax_cat;
    public $attributes_set;
    /** Contains the binary of the image. NULL if not any.
     * For the services set this value to "" keep data unchanged.
     */
    public $image;

    static function __build($id, $ref, $label, $price_sell, $category, $disp_order,
                            $tax_cat, $visible, $scaled, $price_buy = null,
                            $attributes_set = null, $barcode = null, $image) {
        $prd = new Product($ref, $label, $price_sell, $category, $disp_order,
                            $tax_cat, $visible, $scaled, $price_buy,
                            $attributes_set, $barcode, $image);
        $prd->id = $id;
        return $prd;
    }

    function __construct($ref, $label, $price_sell, $category, $disp_order,
                         $tax_cat, $visible, $scaled, $price_buy = null,
                         $attributes_set = null, $barcode = null, $image = null) {
        $this->reference = $ref;
        $this->label = $label;
        $this->price_sell = $price_sell;
        $this->visible = $visible;
        $this->scaled = $scaled;
        $this->barcode = $barcode;
        $this->price_buy = $price_buy;
        $this->category = $category;
        $this->disp_order = $disp_order;
        $this->tax_cat = $tax_cat;
        $this->attributes_set = $attributes_set;
        $this->image = $image;
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
