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

//////////////////
//  Debug mode  //
//////////////////

$config['debug'] = false;

//////////////////
// Core modules //
//////////////////
// Don't forget to set the configuration file in each module if it has one.
// The values must match a directory in the core module directory
// I.e. core_ident must match a directory under core_modules/ident/

$config['core_ident'] = "openbar";
$config['core_database'] = "static";
$config['core_modules'] = "static";

// Template
// Must match a directory in templates/
$config['template'] = "pasteque";

// Thumbnail size in pixels
$config['thumb_width'] = 128;
$config['thumb_height'] = 128;

// Paypal config (for module payment)
$config['pp_sandbox_id'] = "";
$config['pp_user_id'] = "";
$config['pp_email'] = "";
$config['pp_sandbox_email'] = "";
$config['pp_sandbox'] = true;

$config['pp_modules'] = [
    array("module" => "product_barcodes", "price" => "1.00"),
];
$config['mandatory_modules'] = [
    "base_products",
    "base_sales",
    "modules_management",
];
$config['free_modules'] = [
    "base_restaurant",
    "base_cashes",
    "base_resources",
    "base_stocks",
    "base_users",
    "product_compositions",
    "product_discounts",
];

function getConfig() {
    global $config;
    return $config;
}
