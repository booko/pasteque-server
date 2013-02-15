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

class Tax {

    public $id;
    public $tax_cat_id;
    public $name;
    /** Timestamp */
    public $start_date;
    public $rate;

    static function __build($id, $tax_cat_id, $start_date, $rate) {
        $tax = new Tax($tax_cat_id, $start_date, $rate);
        $tax->id = $id;
        return $tax;
    }

    function __construct($tax_cat_id, $start_date, $rate) {
        $this->tax_cat_id = $tax_cat_id;
        $this->name = $rate;
        if (!preg_match('%^\\d*$%', $start_date) && !is_int($start_date)) {
            $start_date = strtotime($start_date);
        }
        $this->start_date = $start_date;
        $this->rate = $rate;
    }

    static function __form($f) {
        if (!isset($f['rate']) || !isset($f['start_date'])
                || !isset($f['tax_cat_id'])) {
            return NULL;
        }
        if (isset($f['id'])) {
            return Tax::__build($f['id'], $f['tax_cat_id'], $f['start_date'],
                    $f['rate']);
        } else {
            return new Tax($f['tax_cat_id'], $f['start_date'], $f['rate']);
        }
    }

    /** Check if this tax is valid at a given date.
     * @param $date (optional) The date as timestamp (default now)
     */
    function isValid($date = null) {
        if ($date === null) {
            $date = time();
        }
        return $this->start_date < $date;
    }
}
