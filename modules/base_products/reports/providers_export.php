<?php

//    Pastèque Web back office, Users module
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

namespace BaseProducts;

$sql = "SELECT `NAME`, `DISPORDER` FROM `PROVIDERS` ";

$fields = array('NAME', 'DISPORDER');
$headers = array(\i18n("Provider.label"), 
    \i18n("Provider.dispOrder"));

$report = new \Pasteque\Report(PLUGIN_NAME, "providers_export",
        \i18n("Export providers", PLUGIN_NAME),
        $sql, $headers, $fields);

\Pasteque\register_report($report);
