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

class DB {

    const BOOL = 1;
    const TIME = 2;
    const BIN  = 3;

    private $type;

    public function __construct($type) {
        $this->type = $type;
    }

    public static function get() {
        return new DB(get_db_type(get_user_id()));
    }

    public function readBool($val) {
        switch ($this->type) {
        case 'mysql':
            return (ord($val) == 1);
        case 'postgresql':
            return ($val == "t");
        }
    }

    public function boolVal($val) {
        switch ($this->type) {
        case 'mysql':
            if ($val) {
                return true;
            } else {
                return false;
            }
        case 'postgresql':
            if ($val) {
                return "t";
            } else {
                return "f";
            }
        }
    }

    public function true() {
        switch ($this->type) {
        case 'mysql':
            return 1;
        case 'postgresql':
            return "'t'";
        }
    }

    public function false() {
        switch ($this->type) {
        case 'mysql':
            return 0;
        case 'postgresql':
            return "'f'";
        }
    }

    public function readBin($val) {
        switch ($this->type) {
        case 'mysql':
            return $val;
        case 'postgresql':
            return fgets($val);
        }
    }
}