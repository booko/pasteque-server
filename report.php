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

function report_csv($module, $name, $values) {
    $report = get_report($module, $name);
    if ($report === NULL) {
        die();
    }
    $reportRun = $report->run($values);
    ob_clean();
    $output = fopen("php://output", "rb+");

    if (!$report->isGrouping()) {
        fputcsv($output,array("Pastèque"),";");
        $line = $report->getHeaders();
        fputcsv($output, $line,";");
        while ($line = $reportRun->fetch() ) {
            $data = array();
            foreach ($report->getFields() as $field) {
                $data = init_data($report, $data, $line, $field);
            }
            fputcsv($output, $data,";");
        }
    } else {
        while ($line = $reportRun->fetch()) {
        $data = array();
            if ($reportRun->isGroupEnd()) {
                if ($report->hasSubtotals()) {
                    write_subtotals($output, $report, $reportRun);
                }
                fputcsv($output, array(),";");
            }

            if ($reportRun->isGroupStart()) {
                fputcsv($output, array($reportRun->getCurrentGroup()),";");
                fputcsv($output, $report->getHeaders(),";");
            }

            foreach ($report->getFields() as $field) {
                $data = init_data($report, $data, $line, $field);
            }
            fputcsv($output, $data,";");
            unset($data);
        }
        if ($report->hasSubtotals()) {
            write_subtotals($output, $report, $reportRun);
            fputcsv($output, array(),";");
        }
    }
    if ($report->hasTotals()) {
        fputcsv($output, array(\i18n("Total")),";");
        fputcsv($output, totalHeader($report, $reportRun),";");
        fputcsv($output, totals($report, $reportRun),";");
    }
}


function init_data($report, $data, $line, $field) {
     $field = strtoupper($field);
     if (isset($line[$field])) {
         $data[] = $report->applyVisualFilter($field, $line, Report::DISP_CSV);
     } else {
         $data[] = "";
     }
     return $data;
}

function write_subtotals($output, $report, $run) {
    $data = array();
    foreach ($report->getFields() as $field) {
        $data = init_data($report, $data, $run->subtotals, $field);
    }
    fputcsv($output, array(\i18n("Subtotal")),";");
    fputcsv($output, $data,";");

}

function totalHeader($report, $run) {
    $totals = $report->getTotals();
    $headers = $report->getHeaders();
    $cmp = 0;
    $data = array();
    foreach ($report->getFields() as $field) {
        $txt = "";
        if (isset($totals[$field])) {
            if ($totals[$field] === \Pasteque\Report::TOTAL_AVG) {
                 $txt = \i18n("Average");
            }
            $txt .= " " . $headers[$cmp];
        }
        $data[] = $txt;
        $cmp++;
    }
    return $data;
}

function totals($report, $run) {
    $data = array();
    foreach ($report->getFields() as $field) {
        $data = init_data($report, $data, $run->getTotals(), $field);
    }
    return $data;
}

switch ($_GET['w']) {
case 'csv':
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=rapport.csv");
    $params = $_GET;
    unset($params['m']);
    unset($params['n']);
    report_csv($_GET['m'], $_GET['n'], $params);
    break;
case 'display':
default:
    $domain = $_GET['m'];
    $id = $_GET['n'];
    $report = get_report($domain, $id);
    tpl_open();
    tpl_report($report);
    tpl_close();
    break;
}
