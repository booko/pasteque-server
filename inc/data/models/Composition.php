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

class Composition extends Product {

    public $groups;

    function addGroup($group) {
        $this->groups[] = $group;
    }

    static function __build($id, $ref, $label, $priceSell, $categoryId, $providerId,
            $dispOrder, $taxCatId, $visible, $scaled, $priceBuy = null,
            $attributeSetId = null, $barcode = null, $hasImage = false,
            $discountEnabled = false, $discountRate = 0.0) {
        $compo = new Composition($ref, $label, $priceSell, $categoryId, $providerId,
                $dispOrder,
                $taxCatId, $visible, $scaled, $priceBuy,
                $attributeSetId, $barcode, $hasImage,
                $discountEnabled, $discountRate);
        $compo->id = $id;
        return $compo;
    }

    function __construct($ref, $label, $priceSell, $categoryId, $providerId,
            $dispOrder, $taxCatId, $visible, $scaled, $priceBuy,
            $attributeSetId, $barcode, $hasImage, $discountEnabled,
            $discountRate) {
        parent::__construct($ref, $label, $priceSell, $categoryId, $providerId,
                $dispOrder, $taxCatId, $visible, $scaled, $priceBuy,
                $attributeSetId, $barcode, $hasImage, $discountEnabled,
                $discountRate);
        $this->groups = array();
    }

}

class SubGroup {
    public $id;
    public $compositionId;
    public $label;
    public $hasImage;
    public $dispOrder;
    public $choices;

    static function __build($id, $compositionId, $label,
            $dispOrder = null, $hasImage = false) {
        $sbg = new SubGroup($compositionId, $label,
                $dispOrder, $hasImage);
        $sbg->id = $id;
        return $sbg;
    }

    function __construct($compositionId, $label, $dispOrder, $hasImage) {
        $this->compositionId = $compositionId;
        $this->label = $label;
        $this->hasImage = $hasImage;
        $this->dispOrder = $dispOrder;
        $this->choices = array();
    }

    function addProduct($subgroupProd) {
        $this->choices[] = $subgroupProd;
    }
}

class SubGroupProduct {
    public $groupId;
    public $productId;
    public $dispOrder;

    static function __build($productId, $groupId, $dispOrder = 0) {
        $sbgp = new SubGroupProduct($productId, $groupId, $dispOrder);
        return $sbgp;
    }

    function __construct($productId, $groupId, $dispOrder = 0) {
        $this->productId = $productId;
        $this->groupId = $groupId;
        $this->dispOrder = $dispOrder;
    }
}
