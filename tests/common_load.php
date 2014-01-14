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

// This is the common file to include to load stuff on

namespace Pasteque;

define("ABSPATH", dirname(__DIR__));
$altConfigFile = "tests/config.php";
require_once(ABSPATH . "/inc/load.php");
require_once(ABSPATH . "/inc/load_logged.php");
require_once(ABSPATH . "/inc/load_api.php");

function dropDatabase() {
    global $config;
    $pdo = PDOBuilder::getPdo();
    if ($config['db_type'] == "mysql") {
        $sqls = array("DROP TABLE APPLICATIONS;", "DROP TABLE ROLES;",
                "DROP TABLE PEOPLE;", "DROP TABLE RESOURCES;",
                "DROP TABLE TAXCUSTCATEGORIES;", "DROP TABLE CUSTOMERS;",
                "DROP TABLE CATEGORIES;", "DROP TABLE TAXCATEGORIES;",
                "DROP TABLE TAXES;", "DROP TABLE ATTRIBUTE;",
                "DROP TABLE ATTRIBUTEVALUE;", "DROP TABLE ATTRIBUTESET;",
                "DROP TABLE ATTRIBUTEUSE;", "DROP TABLE ATTRIBUTESETINSTANCE;",
                "DROP TABLE ATTRIBUTEINSTANCE;", "DROP TABLE PRODUCTS;",
                "DROP TABLE PRODUCTS_CAT;", "DROP TABLE PRODUCTS_COM;",
                "DROP TABLE SUBGROUPS;", "DROP TABLE SUBGROUPS_PROD;",
                "DROP TABLE TARIFFAREAS;", "DROP TABLE TARIFFAREAS_PROD;",
                "DROP TABLE LOCATIONS;",  "DROP TABLE STOCKDIARY;",
                "DROP TABLE STOCKLEVEL;", "DROP TABLE STOCKCURRENT;",
                "DROP TABLE CURRENCIES;", "DROP TABLE CLOSEDCASH;",
                "DROP TABLE RECEIPTS;", "DROP TABLE TICKETS;",
                "DROP TABLE TICKETSNUM;", "DROP TABLE TICKETSNUM_REFUND;",
                "DROP TABLE TICKETSNUM_PAYMENT;", "DROP TABLE TICKETLINES;",
                "DROP TABLE PAYMENTS;", "DROP TABLE TAXLINES;",
                "DROP TABLE FLOORS;", "DROP TABLE PLACES;",
                "DROP TABLE RESERVATIONS;", "DROP TABLE RESERVATION_CUSTOMERS;",
                "DROP TABLE THIRDPARTIES;", "DROP TABLE SHAREDTICKETS;");
    } else if ($config['db_type'] == "postgresql") {
        $sqls = array("DROP TABLE APPLICATIONS;", "DROP TABLE ROLES;",
                "DROP TABLE PEOPLE;", "DROP TABLE RESOURCES;",
                "DROP TABLE TAXCUSTCATEGORIES;", "DROP TABLE CUSTOMERS;",
                "DROP TABLE CATEGORIES;", "DROP TABLE TAXCATEGORIES;",
                "DROP TABLE TAXES;", "DROP TABLE ATTRIBUTE;",
                "DROP TABLE ATTRIBUTEVALUE;", "DROP TABLE ATTRIBUTESET;",
                "DROP TABLE ATTRIBUTEUSE;", "DROP TABLE ATTRIBUTESETINSTANCE;",
                "DROP TABLE ATTRIBUTEINSTANCE;", "DROP TABLE PRODUCTS;",
                "DROP TABLE PRODUCTS_CAT;", "DROP TABLE PRODUCTS_COM;",
                "DROP SEQUENCE SUBGROUPS_ID_SEQ", "DROP TABLE SUBGROUPS;",
                "DROP TABLE SUBGROUPS_PROD;",
                "DROP SEQUENCE TARIFFAREAS_ID_SEQ CASCADE;",
                "DROP TABLE TARIFFAREAS;", "DROP TABLE TARIFFAREAS_PROD;",
                "DROP TABLE LOCATIONS;",  "DROP TABLE STOCKDIARY;",
                "DROP TABLE STOCKLEVEL;", "DROP TABLE STOCKCURRENT;",
                "DROP SEQUENCE CURRENCIES_ID_SEQ CASCADE;",
                "DROP TABLE CURRENCIES;", "DROP TABLE CLOSEDCASH;",
                "DROP TABLE RECEIPTS;", "DROP TABLE TICKETS;",
                "DROP SEQUENCE TICKETSNUM;", "DROP SEQUENCE TICKETSNUM_REFUND;",
                "DROP SEQUENCE TICKETSNUM_PAYMENT;", "DROP TABLE TICKETLINES;",
                "DROP TABLE PAYMENTS;", "DROP TABLE TAXLINES;",
                "DROP TABLE FLOORS;", "DROP TABLE PLACES;",
                "DROP TABLE RESERVATIONS;", "DROP TABLE RESERVATION_CUSTOMERS;",
                "DROP TABLE THIRDPARTIES;", "DROP TABLE SHAREDTICKETS;");
    }
    for ($i = count($sqls) - 1; $i >= 0; $i--) {
        if ($pdo->exec($sqls[$i]) === false) {
            echo("[ERROR] Could not execute " . $sqls[$i] . "\n");
        }
    }
}
?>
