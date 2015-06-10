<?php
//    Pastèque API
//
//    Copyright (C) 2012-2015 Scil (http://scil.coop)
//    Cédric Houbart, Philippe Pary
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

class Category {

    public $id;
    public $reference;
    public $parentId;
    public $label;
    public $hasImage;
    public $dispOrder;

    static function __build($id, $reference="", $parentId, $label, $hasImage, $order) {
        $cat = new Category($reference, $parentId, $label, $hasImage, $order);
        $cat->id = $id;
        return $cat;
    }

    function __construct($reference="", $parentId, $label, $hasImage, $order) {
        $this->parentId = $parentId;
        $this->reference = $reference;
        $this->label = $label;
        $this->hasImage = (bool) $hasImage;
        $this->dispOrder = (int) $order;
    }

}

?>
