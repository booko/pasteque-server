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

namespace BaseCashes;

$startStr = NULL;
$stopStr = NULL;
if (isset($_GET['start']) || isset($_POST['start'])) {
    $startStr = isset($_GET['start']) ? $_GET['start'] : $_POST['start'];
} else {
    $startStr = \i18nDate(time() - 86400);
}
if (isset($_GET['stop']) || isset($_POST['stop'])) {
    $stopStr = isset($_GET['stop']) ? $_GET['stop'] : $_POST['stop'];
} else {
    $stopStr = \i18nDate(time());
}
// Set $start and $stop as timestamps
$startTime = \i18nRevDate($startStr);
$stopTime = \i18nRevDate($stopStr);
// Sql values
$start = \Pasteque\stdstrftime($startTime);
$stop = \Pasteque\stdstrftime($stopTime);

$sql = "SELECT "
        . "CLOSEDCASH.HOST, "
        . "CLOSEDCASH.MONEY, CLOSEDCASH.DATESTART, "
        . "CLOSEDCASH.DATEEND, PAYMENTS.PAYMENT, "
        . "SUM( PAYMENTS.TOTAL ) AS TOTAL "
        . "FROM "
        . "CLOSEDCASH, PAYMENTS, RECEIPTS "
        . "WHERE "
        . "CLOSEDCASH.MONEY = RECEIPTS.MONEY AND PAYMENTS.RECEIPT = RECEIPTS.ID AND "
        . "CLOSEDCASH.DATESTART > :start AND CLOSEDCASH.DATEEND <= :stop "
        . "GROUP BY "
        . "CLOSEDCASH.HOST, "
        . "PAYMENTS.PAYMENT "
        . "ORDER BY "
        . "CLOSEDCASH.HOST ";

$fields = array("HOST", "MONEY", "DATESTART", "DATEEND", "PAYMENT", "TOTAL");
$headers = array(
            \i18n("Session.host"),
            \i18n("Money", PLUGIN_NAME),
            \i18n("Session.openDate"),
            \i18n("Session.closeDate"),
            \i18n("Payments", PLUGIN_NAME),
            \i18n("Total", PLUGIN_NAME),
            );


$report = new \Pasteque\Report($sql, $headers, $fields);
$report->setGrouping("HOST");
$report->setParam(":start", $start);
$report->setParam(":stop", $stop);
$report->addSubtotal("TOTAL", \Pasteque\Report::TOTAL_SUM);
$report->addTotal("TOTAL", \Pasteque\Report::TOTAL_SUM);

$report->addFilter("DATESTART", "\Pasteque\stdtimefstr");
$report->addFilter("DATESTART", "\i18nDatetime");
$report->addFilter("DATEEND", "\Pasteque\stdtimefstr");
$report->addFilter("DATEEND", "\i18nDatetime");
$report->addFilter("TOTAL", "\i18nCurr");
$report->addFilter("PAYMENT", "\i18n");
\Pasteque\register_report(PLUGIN_NAME, "closedpos_report", $report);
?>
