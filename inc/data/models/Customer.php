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

class Customer {

    public $id;
    public $number;
    public $key;
    public $disp_name;
    public $card;
    public $cust_tax_id;
    public $prepaid;
    public $max_debt;
    public $curr_debt;
    public $debt_date;
    public $first_name;
    public $last_name;
    public $email;
    public $phone1;
    public $phone2;
    public $fax;
    public $addr1;
    public $addr2;
    public $zip_code;
    public $city;
    public $region;
    public $country;
    public $note;
    public $visible;

    static function __build($id, $number, $key, $disp_name, $card, $cust_tax_id,
            $prepaid, $max_debt, $curr_debt, $debt_date, $first_name, $last_name, $email,
            $phone1, $phone2, $fax, $addr1, $addr2, $zip_code, $city, $region,
            $country, $note, $visible) {
        $cust = new Customer($number, $key, $disp_name, $card, $cust_tax_id,
            $prepaid, $max_debt, $curr_debt, $debt_date, $first_name, $last_name, $email,
            $phone1, $phone2, $fax, $addr1, $addr2, $zip_code, $city, $region,
            $country, $note, $visible);
        $cust->id = $id;
        return $cust;
    }

    function __construct($number, $key, $disp_name, $card, $cust_tax_id, $prepaid,
            $max_debt, $curr_debt, $debt_date, $first_name, $last_name, $email,
            $phone1, $phone2, $fax, $addr1, $addr2, $zip_code, $city, $region,
            $country, $note, $visible) {
        $this->number = $number;
        $this->key = $key;
        $this->disp_name = $disp_name;
        $this->card = $card;
        $this->cust_tax_id = $cust_tax_id;
        $this->prepaid = $prepaid;
        $this->max_debt = $max_debt;
        $this->curr_debt = $curr_debt;
        $this->debt_date = $debt_date;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->phone1 = $phone1;
        $this->phone2 = $phone2;
        $this->fax = $fax;
        $this->addr1 = $addr1;
        $this->addr2 = $addr2;
        $this->zip_code = $zip_code;
        $this->city = $city;
        $this->region = $region;
        $this->country = $country;
        $this->note = $note;
        $this->visible = $visible;
    }

}
