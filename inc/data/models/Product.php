<?php
//    Pastèque Web back office
//
//    Copyright (C) 2013 Scil (http://scil.coop)
//
//    This file is part of Pastèque.
//
//    Pastèque is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Pastèque is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Pastèque.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque;

class Product {

    public $id;
    public $ref;
    public $name;
    public $price_sell;
    public $tax_cat_id;
    public $category_ids;

    static function __build($id, $ref, $name, $price_sell, $tax_cat_id, $cat_ids) {
        $prd = new Product($ref, $name, $price_sell, $tax_cat_id, $cat_ids);
        $prd->id = $id;
        return $prd;
    }

    function __construct($ref, $name, $price_sell, $tax_cat_id, $cat_ids) {
        $this->ref = $ref;
        $this->name = $name;
        $this->price_sell = $price_sell;
        $this->tax_cat_id = $tax_cat_id;
        $this->category_ids = $cat_ids;
    }

    static function __form($f) {
        if (!isset($f['ref']) || !isset($f['name'])
                || !isset($f['price_sell']) || !isset($f['tax_cat_id'])) {
            return NULL;
        }
        $cat_ids = array();
        if (isset($f['category_ids'])) {
            foreach($f['category_ids'] as $cat) {
                $cat_ids[] = $cat;
            }
        }
        if (isset($f['id'])) {
            $prd = Product::__build($f['id'], $f['ref'], $f['name'],
                    $f['price_sell'], $f['tax_cat_id'], $cat_ids);
        } else {
            $prd = new Product($f['ref'], $f['name'], $f['price_sell'],
                    $f['tax_cat_id'], $cat_ids);
        }
        return $prd;
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
