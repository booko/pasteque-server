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

$HOOKS = array();

function hook($hook_name, $function_name, $args = array()) {
    global $HOOKS;
    if (!isset($HOOKS[$hook_name])) {
        $HOOKS[$hook_name] = array();
    }
    $HOOKS[$hook_name][] = array($function_name, $args);
}

function call_hooks($hook_name) {
    global $HOOKS;
    if (!isset($HOOKS[$hook_name])) {
        return;
    }
    foreach ($HOOKS[$hook_name] as $hook) {
        if (function_exists($hook[0])) {
            $hook[0]($hook[1]);
        }
    }
}
?>
