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

class Attribute {

    public $id;
    public $label;
    public $values;

    static function __build($id, $label) {
        $attr = new Attribute($label);
        $attr->id = $id;
        return $attr;
    }

    function __construct($label) {
        $this->label = $label;
        $this->values = array();
    }

    function addValue($value) {
        $this->values[] = $value;
    }
}

class AttributeValue {

    public $id;
    public $label;

    static function __build($id, $label) {
        $val = new AttributeValue($label);
        $val->id = $id;
        return $val;
    }

    function __construct($label) {
        $this->label = $label;
    }
}
