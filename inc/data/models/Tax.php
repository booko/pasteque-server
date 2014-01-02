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

class Tax {

    public $id;
    public $taxCatId;
    public $label;
    public $startDate;
    public $rate;

    static function __build($id, $taxCatId, $label, $startDate, $rate) {
        $tax = new Tax($taxCatId, $label, $startDate, $rate);
        $tax->id = $id;
        return $tax;
    }

    function __construct($taxCatId, $label, $startDate, $rate) {
        $this->taxCatId = $taxCatId;
        $this->label = $label;
        if (!preg_match('%^\\d*$%', $startDate) && !is_int($startDate)) {
            $startDate = strtotime($startDate);
        }
        $this->startDate = $startDate;
        $this->rate = $rate;
    }
    
    /** Check if this tax is valid at a given date.
     * @param $date (optional) The date as timestamp (default now)
     */
    function isValid($date = null) {
        if ($date === null) {
            $date = time();
        }
        return $this->startDate <= $date;
    }
}
