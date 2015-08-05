<?php
//    Pastèque Web back office, Static database module
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

/* This module uses the global config file to define database configuration.
 * It is the same as static database module but allows an alternative config not
 * to mess up development with testing.
 * See tests/config-sample.php */

function get_db_type($user_id) {
    global $config;
    return $config['db_type'];
}
function get_db_host($user_id) {
    global $config;
    return $config['db_host'];
}
function get_db_port($user_id) {
    global $config;
    return $config['db_port'];
}
function get_db_name($user_id) {
    global $config;
    return $config['db_name'];
}
function get_db_user($user_id) {
    global $config;
    return $config['db_user'];
}
function get_db_password($user_id) {
    global $config;
    return $config['db_password'];
}
?>
