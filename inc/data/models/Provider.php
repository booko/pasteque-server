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

class Provider {

    public $id;
    public $parentId;
    public $label;
    public $hasImage;
    public $firstName;
    public $lastName;
    public $email;
    public $phone1;
    public $phone2;
    public $website;
    public $fax;
    public $addr1;
    public $addr2;
    public $zipCode;
    public $city;
    public $region;
    public $country;
    public $notes;
    public $visible;
    public $dispOrder;

    static function __build($id, $label, $hasImage,
            $firstName, $lastName, $email, $phone1, $phone2, $website,
            $fax, $addr1, $addr2, $zipCode, $city, $region, $country,
            $notes, $visible, $order) {
        $prov = new Provider($label, $hasImage,
            $firstName, $lastName, $email, $phone1, $phone2, $website,
            $fax, $addr1, $addr2, $zipCode, $city, $region, $country,
            $notes, $visible, $order);
        $prov->id = $id;
        return $prov;
    }

    function __construct($label, $hasImage,
            $firstName, $lastName, $email, $phone1, $phone2, $website,
            $fax, $addr1, $addr2, $zipCode, $city, $region, $country,
            $notes, $visible, $order) {
        $this->label = $label;
        $this->hasImage = (bool) $hasImage;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone1 = $phone1;
        $this->phone2 = $phone2;
        $this->website = $website;
        $this->fax = $fax;
        $this->addr1 = $addr1;
        $this->addr2 = $addr2;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->region = $region;
        $this->country = $country;
        $this->notes = $notes;
        $this->visible = $visible;
        $this->dispOrder = (int) $order;
    }

}

?>
