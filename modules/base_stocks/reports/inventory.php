<?php
//    Pastèque Web back office
//
//    Copyright (C) 2015 Scil (http://scil.coop)
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

namespace BaseStocks;

$sql = "SELECT "
    . "LOCATIONS.NAME AS NAME, PRODUCTS.NAME AS PRODUCTNAME, STOCKCURRENT.UNITS, STOCKLEVEL.STOCKSECURITY, STOCKLEVEL.STOCKMAXIMUM "
    . "FROM STOCKCURRENT "
    . "LEFT JOIN PRODUCTS ON STOCKCURRENT.PRODUCT = PRODUCTS.ID "
    . "LEFT JOIN STOCKLEVEL ON STOCKCURRENT.PRODUCT = STOCKLEVEL.PRODUCT "
    . "LEFT JOIN LOCATIONS ON LOCATIONS.ID = STOCKCURRENT.LOCATION "
    . "ORDER BY STOCKLEVEL.LOCATION";

$fields = array("NAME","PRODUCTNAME","UNITS","STOCKSECURITY","STOCKMAXIMUM");
$headers = array(
    \i18n("Location.label"),
    \i18n("Product.label"),
    \i18n("Quantity"),
    \i18n("QuantityMin"),
    \i18n("QuantityMax")
);

$report = new \Pasteque\Report(PLUGIN_NAME, "inventory",
    \i18n("Stock.InventoryReport"),
    $sql, $headers, $fields);

\Pasteque\register_report($report);
