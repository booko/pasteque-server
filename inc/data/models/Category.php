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

class Category {

    public $id;
    public $parent_id;
    public $name;

    static function __build($id, $parent_id, $name) {
        $cat = new Category($parent_id, $name);
        $cat->id = $id;
        return $cat;
    }

    function __construct($parent_id, $name) {
        $this->parent_id = $parent_id;
        $this->name = $name;
    }

    function __form($f) {
        if (!isset($f['name'])) {
            return NULL;
        }
        $parent = NULL;
        if (isset($f['parent_id']) && $f['parent_id'] != "") {
            $parent = $f['parent_id'];
        }
        if (isset($f['id'])) {
            $cat = Category::__build($f['id'], $parent, $f['name']);
        } else {
            $cat = new Category($parent, $f['name']);
        }
        return $cat;
    }
}

?>
