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
    const DATE = 4;

    private $type;

    public function __construct($type) {
        $this->type = $type;
    }

    public static function get() {
        return new DB(get_db_type(get_user_id()));
    }

    public function getType() {
        return $this->type;
    }

    public function readBool($val) {
        if ($val === null) {
            return null;
        }
        switch ($this->type) {
        case 'mysql':
            // Happy PHP consistency!
            return ((ord($val) == 1) || ($val == "1"));
        case 'postgresql':
            return ($val == "t");
        }
    }

    public function boolVal($val) {
        if ($val === null) {
            return null;
        }
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
        if ($val === null) {
            return null;
        }
        switch ($this->type) {
        case 'mysql':
            return $val;
        case 'postgresql':
            $data = fread($val, 2048);
            while (!feof($val)) {
                $data .= fread($val, 2048);
            }
            return $data;
        }
    }

    public function readDate($val) {
        if ($val !== null) {
            return stdtimefstr($val);
        } else {
            return null;
        }
    }

    public function dateVal($val) {
        if ($val !== null) {
            return stdstrftime($val);
        } else {
            return null;
        }
    }

    public function concat($a, $b) {
        switch ($this->type) {
        case 'mysql':
            return "concat(" . $a . ", " . $b . ")";
            break;
        case 'postgresql':
            return $a . "||" . $b;
            break;
        }
    }
}