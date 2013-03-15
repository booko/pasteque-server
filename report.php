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

if (@constant("\Pasteque\ABSPATH") === NULL) {
    die();
}

function report_csv($module, $name) {
    $report = get_report($module, $name);
    if ($report === NULL) {
        die();
    }
    $report->run();
    $output = fopen("php://output", "rb+");
    $line = $report->headers;
    fputcsv($output, $line);
    while ($line = $report->fetch()) {
        $data = array();
        foreach ($report->fields as $field) {
            $data[] = $line[$field];
        }
        fputcsv($output, $data);
    }
}

switch ($_GET['w']) {
case 'csv':
    header("Content-type: text/csv");
    report_csv($_GET['m'], $_GET['n']);
    break;
case 'display':
default:
    // TODO: auto format display (input and result)
    break;
}
?>
