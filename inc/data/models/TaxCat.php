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

class TaxCat {

    public $id;
    public $name;
    public $taxes;
    
    static function __build($id, $name) {
        $taxcat = new TaxCat($name);
        $taxcat->id = $id;
        return $taxcat;
    }
    
    function __construct($name) {
        $this->name = $name;
        $this->taxes = array();
    }

    function __form($f) {
        if (!isset($f['name'])) {
            return NULL;
        }
        if (isset($f['id'])) {
            return TaxCat::__build($f['id'], $f['name']);
        } else {
            return new TaxCat($f['name']);
        }
    }

    function addTax($tax) {
        $this->taxes[] = $tax;
    }

    function getId() {
        return $this->id;
    }

    function getCurrentTax() {
        $current = null;
        $now = time();
        foreach ($this->taxes as $tax) {
            if ($current == null || ($tax->start_date <= $now
                    && $tax->start_date > $current->start_date)) {
                $current = $tax;
            }
        }
        return $current;
    }
}
