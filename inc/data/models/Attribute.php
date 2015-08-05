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

class Attribute {

    public $id;
    public $label;
    public $values;
    public $dispOrder;

    static function __build($id, $label, $dispOrder) {
        $attr = new Attribute($label, $dispOrder);
        $attr->id = $id;
        return $attr;
    }

    function __construct($label, $dispOrder) {
        $this->label = $label;
        $this->values = array();
        $this->dispOrder = $dispOrder;
    }

    function addValue($value) {
        $this->values[] = $value;
    }
}

class AttributeValue {

    public $id;
    public $value;

    static function __build($id, $value) {
        $val = new AttributeValue($value);
        $val->id = $id;
        return $val;
    }

    function __construct($value) {
        $this->value = $value;
    }
}

class AttributeSet {

    public $id;
    public $label;
    public $attributes;

    public function __construct($label) {
        $this->label = $label;
        $this->attributes = array();
    }

    public function __build($id, $label) {
        $set = new AttributeSet($label);
        $set->id = $id;
        return $set;
    }

    public function addAttribute($attr) {
        $this->attributes[] = $attr;
    }
}

class AttributeSetInstance {

    public $id;
    public $attrSetId;
    public $value;
    public $attrInsts;

    public static function __build($id, $attrSetId, $value) {
        $attrInst = new AttributeSetInstance($attrSetId, $value);
        $attrInst->id = $id;
        return $attrInst;
    }

    public function __construct($attrSetId, $value) {
        $this->attrSetId = $attrSetId;
        $this->value = $value;
        $this->attrInsts = array();
    }

    public function addAttrInst($attrInst) {
        $this->attrInsts[] = $attrInst;
    }
}

class AttributeInstance {

    public $id;
    public $attrSetInstId;
    public $attrId;
    public $value;

    public function __construct($attrSetInstId, $attrId, $value) {
        $this->attrSetInstId = $attrSetInstId;
        $this->attrId = $attrId;
        $this->value = $value;
    }
}