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
    $reportRun = $report->run();
    $output = fopen("php://output", "rb+");

    if(!$report->isGrouping()) {
        $line = $report->headers;
        fputcsv($output, $line);
        while ($line = $reportRun->fetch() ) {
            $data = array();
            foreach ($report->fields as $field) {
                $data = init_data($data,$line,$field);
            }
            fputcsv($output, $data);
        }
    } else {
        while ($line = $reportRun->fetch()) {
        $data = array();
            if( $reportRun->isGroupEnd()) {
                if($report->hasSubtotals()) {
                    write_subtotals($output, $report, $reportRun);
                }
                fputcsv($output, array());
            }

            if( $reportRun->isGroupStart() ) {
                fputcsv($output, array($reportRun->getCurrentGroup()));
                fputcsv($output, $report->headers);
            }

            foreach($report->fields as $field) {
                $data = init_data($data, $line, $field);
            }
            fputcsv($output,$data);
            unset($data);
        }
        if($report->hasSubtotals()) {
            write_subtotals($output, $report, $reportRun);
            fputcsv($output,array());
        }

        if($report->hasTotals()) {
            fputcsv($output, array(\i18n("Total")));
            fputcsv($output, $report->headers);
            fputcsv($output, totals($report,$reportRun));
        }
    }
}


function init_data($data, $line, $field) {
     if( isset($line[$field])) {
        $data[] = $line[$field];
     } else {
        $data[] = "";
     }
     return $data;
}

function write_subtotals($output,$report,$run){
    $data= array();
    foreach($report->fields as $field) {
        $data = init_data($data,$run->subtotals, $field);
    }
    fputcsv($output,array(\i18n("Subtotal")));
    fputcsv($output, $data);

}

function totals($report,$run){
    $data = array();
    foreach($report->fields as $field){
        $data = init_data($data,$run->totals, $field);
    }
    return $data;
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

