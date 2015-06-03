<?php
//    Pastèque API
//
//    Copyright (C) 2015 Scil (http://scil.coop)
//    Cédric Houbart
//
//    This file is part of Pastèque
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

class PaymentMode {

    /** Requires a customer */
    const CUST_ASSIGNED = 1;
    /** Uses customer's debt (includes CUST_ASSIGNED) */
    const CUST_DEBT = 3; // 2 + PaymentMode::CUST_ASSIGNED
    /** Uses customer's prepaid (includes CUST_ASSIGNED) */
    const CUST_PREPAID = 5; // 4 + PaymentMode::CUST_ASSIGNED;

    public $id;
    public $code;
    public $label;
    public $backLabel;
    public $flags;
    public $hasImage;
    /** Rules define how exceedent must be treated. It's an array
     * of PaymentModeRule ordered by minVal ASC. */
    public $rules;
    /** Optional pre-defined values to pick. */
    public $values;
    public $active;
    /** If system, it cannot be deleted */
    public $system;
    /** Display order */
    public $dispOrder;

    static function __build($id, $code, $label, $backLabel, $flags, $hasImage,
            $rules, $values, $active, $system, $dispOrder) {
        $paymentMode = new PaymentMode($code, $label, $backLabel, $flags,
                $hasImage, $rules, $values, $active, $system, $dispOrder);
        $paymentMode->id = $id;
        return $paymentMode;
    }

    function __construct($code, $label, $backLabel, $flags, $hasImage, $rules,
            $values, $active, $system, $dispOrder) {
        $this->code = $code;
        $this->label = $label;
        $this->backLabel = $backLabel;
        $this->flags = $flags;
        $this->hasImage = $hasImage;
        $this->rules = $rules;
        $this->values = $values;
        $this->active = $active;
        $this->system = $system;
        $this->dispOrder = $dispOrder;
    }
}

class PaymentModeReturn {

    const PARENT_ID = "parent";

    public $minVal;
    /** In which PaymentMode the exceedent is given back. May be null.
     * When creating a new PaymentMode, use the special value PARENT_ID
     * to reference the not currently set PaymentMode id. */
    public $modeId;

    public function __construct($minVal, $modeId) {
        $this->minVal = $minVal;
        $this->modeId = $modeId;
    }
}

/** Predifined values to pick for PaymentModes */
class PaymentModeValue {

    public $value;
    public $resource;
    public $dispOrder;

    public static function __build($id, $value, $resource, $dispOrder) {
        $pmVal = new PaymentModeValue($value, $resource, $dispOrder);
        $pmVal->id = $id;
        return $pmVal;
    }

    public function __construct($value, $resource, $dispOrder) {
        $this->value = $value;
        $this->resource = $resource;
        $this->dispOrder = $dispOrder;
    }
}
