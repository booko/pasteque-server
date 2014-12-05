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
    /** If cs, the payment is included in comsolidated sales, otherwise not
     * (for example free) */
    public $cs;
    /** Display order */
    public $dispOrder;

    static function __build($id, $code, $label, $flags, $hasImage, $rules,
            $values, $active, $system, $cs, $dispOrder) {
        $paymentMode = new PaymentMode($code, $label, $flags, $hasImage, $rules,
                $values, $active, $system, $cs, $dispOrder);
        $paymentMode->id = $id;
        return $paymentMode;
    }

    function __construct($code, $label, $flags, $hasImage, $rules, $values,
            $active, $system, $cs, $dispOrder) {
        $this->code = $code;
        $this->label = $label;
        $this->flags = $flags;
        $this->hasImage = $hasImage;
        $this->rules = $rules;
        $this->values = $values;
        $this->active = $active;
        $this->system = $system;
        $this->cs = $cs;
        $this->dispOrder = $dispOrder;
    }
}

class PaymentModeRule {

   /** Rule to convert exceedent to credit note
     * (add a negative payment with code "credit_note") */
    const CREDIT_NOTE = "note";
    /** Rule to give back exceedent (add a negative payment of the same type) */
    const GIVE_BACK = "give_back";
    /** Rule to add exceedent to customer's prepaid */
    const PREPAID = "prepaid";
    /** Rule to cover customer's debt */
    const DEBT = "debt";

    public $minVal;
    public $rule;

    public function __construct($minVal, $rule) {
        $this->minVal = $minVal;
        $this->rule = $rule;
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
