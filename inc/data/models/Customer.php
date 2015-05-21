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

    /** Minimal size of card number */
    const CARD_SIZE = 7;
    /** Barcode prefix for customer cards */
    const CARD_PREFIX = "c";

    public $id;
    public $number;
    public $key;
    public $dispName;
    public $card;
    public $custTaxId;
    public $discountProfileId;
    public $tariffAreaId;
    public $prepaid;
    public $maxDebt;
    public $currDebt;
    public $debtDate;
    public $firstName;
    public $lastName;
    public $email;
    public $phone1;
    public $phone2;
    public $fax;
    public $addr1;
    public $addr2;
    public $zipCode;
    public $city;
    public $region;
    public $country;
    public $note;
    public $visible;
    public $expireDate;

    static function __build($id, $number, $key, $dispName, $card, $custTaxId,
            $discountProfileId, $tariffAreaId, $prepaid, $maxDebt, $currDebt,
            $debtDate, $firstName, $lastName, $email, $phone1, $phone2, $fax,
            $addr1, $addr2, $zipCode, $city, $region, $country, $note, $visible,
            $expireDate) {
        $cust = new Customer($number, $key, $dispName, $card, $custTaxId,
                $discountProfileId, $tariffAreaId, $prepaid, $maxDebt, $currDebt,
                $debtDate, $firstName, $lastName, $email, $phone1, $phone2, $fax,
                $addr1, $addr2, $zipCode, $city, $region, $country, $note, $visible,
                $expireDate);
        $cust->id = $id;
        return $cust;
    }

    function __construct($number, $key, $dispName, $card, $custTaxId,
            $discountProfileId, $tariffAreaId, $prepaid, $maxDebt, $currDebt,
            $debtDate, $firstName, $lastName, $email, $phone1, $phone2, $fax,
            $addr1, $addr2, $zipCode, $city, $region, $country, $note, $visible,
            $expireDate) {
        $this->number = $number;
        $this->key = $key;
        $this->dispName = $dispName;
        $this->card = $card;
        $this->custTaxId = $custTaxId;
        $this->discountProfileId = $discountProfileId;
        $this->tariffAreaId = $tariffAreaId;
        $this->prepaid = $prepaid;
        $this->maxDebt = $maxDebt;
        $this->currDebt = $currDebt;
        $this->debtDate = $debtDate;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone1 = $phone1;
        $this->phone2 = $phone2;
        $this->fax = $fax;
        $this->addr1 = $addr1;
        $this->addr2 = $addr2;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->region = $region;
        $this->country = $country;
        $this->note = $note;
        if($visible == "") {
            $visible = 1;
        }
        $this->visible = $visible;
        $this->expireDate = $expireDate;
    }

}
