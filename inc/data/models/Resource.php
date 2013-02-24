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

class Resource {

	const TYPE_TEXT = 0;
	const TYPE_IMAGE = 1;
	const TYPE_BIN = 2;

    public $id;
    public $name;
    public $type;
    public $content;

    static function __build($id, $name, $type, $content) {
        $res = new Resource($name, $type, $content);
        $res->id = $id;
        return $res;
    }

    function __construct($name, $type, $content) {
        $this->name = $name;
        $this->type = $type;
        $this->content = $content;
    }
}

?>
