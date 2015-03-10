<?php
//    Pastèque Web back office, Users module
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

namespace BaseStocks;

$sql = "SELECT REFERENCE as Reference, "
            . "STOCKCURRENT.UNITS as Quantity "
        . " FROM PRODUCTS "
        . " LEFT JOIN STOCKLEVEL ON PRODUCTS.ID = STOCKLEVEL.PRODUCT "
        . " LEFT JOIN STOCKCURRENT ON PRODUCTS.ID = STOCKCURRENT.PRODUCT "
        . " WHERE PRODUCTS.DELETED = 0 "
        . " ORDER BY PRODUCTS.NAME";

$fields = array("Quantity","Reference");
$headers = $fields;

$report = new \Pasteque\Report(PLUGIN_NAME, "stockcurrent_export",
        \i18n("Export stock level", PLUGIN_NAME),
        $sql, $headers, $fields);

\Pasteque\register_report($report);