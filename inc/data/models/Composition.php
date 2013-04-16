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

class CompositionGroup {

    public $id;
    public $label;
    public $product_ids;

    static function __build($id, $label, $product_ids = NULL) {
        $grp = new CompositionGroup($label, $product_ids);
        $grp->id = $id;
        return $grp;
    }

    function __construct($label, $product_ids = NULL) {
        $this->label = $label;
        if ($product_ids !== NULL) {
            $this->product_ids = $product_ids;
        } else {
            $this->product_ids = array();
        }
    }

    function addProduct($product_id) {
        $this->product_ids[] = $product_id;
    }
}

class Composition {

    public $product_id;
    public $groups;

    static function __build($product_id, $groups = NULL) {
        $cmp = new Composition($groups);
        $cmp->product_id = $product_id;
        return $cmp;
    }

    function __construct($groups = NULL) {
        if ($groups !== NULL) {
            $this->groups = $groups;
        } else {
            $this->groups = array();
        }
    }

    function addGroup($group) {
        $this->groups[] = $group;
    }
}

?>
