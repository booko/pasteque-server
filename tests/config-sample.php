<?php
//    Pastèque Web back office, general configuration
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

global $config;
// Core modules may not be overriden by test cases
$config['core_ident'] = "openbar";
$config['core_database'] = "testing";
$config['core_modules'] = "testing";
$config['template'] = "pasteque";

// Database testing configuration, may be overriden by test cases
$config['db_type'] = "mysql";
$config['db_host'] = "localhost";
$config['db_port'] = "3306";
$config['db_name'] = "pasteque_testing";
$config['db_user'] = "root";
$config['db_password'] = "password";

// Module testing configuration, may be overriden by test cases
$config['modules'] = array();
