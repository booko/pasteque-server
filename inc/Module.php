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

/** Definition of a module */
class Module {

    /** List all modules. Return an array of dir names. */
    public static function listAll() {
        $dir = PT::$ABSPATH . "/modules";
        $list = scandir($dir);
        $dot = array_search(".", $list);
        if ($dot !== false) {
            array_splice($list, $dot, 1);
        }
        $dotdot = array_search("..", $list);
        if ($dotdot !== false) {
            array_splice($list, $dotdot, 1);
        }
        return $list; // TODO: this assumes all files are module directories
    }

    /** List all basic modules. Return an array of dir names. */
    public static function listBase() {
        $dir = PT::$ABSPATH . "/modules";
        $list = scandir($dir);
        $base = array();
        foreach ($list as $module) {
            if (substr($module, 0, 4) == "base") {
                $base[] = $module;
            }
        }
        return $base;
    }

    private $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }
}
