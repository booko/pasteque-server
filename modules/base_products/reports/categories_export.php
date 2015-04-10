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

namespace BaseProducts;

$sql = "SELECT a.NAME AS NAME, p.NAME AS PARENT_NAME, a.DISPORDER AS DISPORDER"
        . " FROM `CATEGORIES` a LEFT JOIN `CATEGORIES` p ON a.`PARENTID` = p.`ID`";

$fields = array('NAME', 'PARENT_NAME', 'DISPORDER');
$headers = array(\i18n("Category.label"), 
    \i18n("Category.parent"),
    \i18n("Category.dispOrder"));

$report = new \Pasteque\Report(PLUGIN_NAME, "categories_export",
        \i18n("Export categories", PLUGIN_NAME),
        $sql, $headers, $fields);

\Pasteque\register_report($report);

