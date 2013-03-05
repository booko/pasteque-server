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

namespace {
    function i18n($label, $module = NULL) {
        $args = func_get_args();
        $args = array_slice($args, 2);
        return \Pasteque\__($label, $module, $args);
    }

    function pi18n($label, $module = NULL) {
        $args = func_get_args();
        $args = array_slice($args, 2);
        echo \Pasteque\__($label, $module, $args);
    }

    function i18nDate($timestamp) {
        return \Pasteque\__d($timestamp);
    }
    function i18nDatetime($timestamp) {
        return \Pasteque\__dt($timestamp);
    }
    function i18nRevDate($date) {
        return \Pasteque\__rd($date);
    }

    function pi18nDate($timestamp) {
        echo \Pasteque\__d($timestamp);
    }
    function pi18nDatetime($timestamp) {
        echo \Pasteque\__dt($timestamp);
    }
}
