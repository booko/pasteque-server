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

// Cash session request
$sqls[] = "SELECT "
        . "CLOSEDCASH.HOST, CLOSEDCASH.MONEY, CLOSEDCASH.DATESTART, "
        . "CLOSEDCASH.DATEEND, COUNT(RECEIPTS.ID) AS TICKETS, "
        . "SUM(TICKETLINES.PRICE * TICKETLINES.UNITS) AS SALES "
        . "FROM CLOSEDCASH "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
        . "LEFT JOIN TICKETLINES ON TICKETLINES.TICKET = RECEIPTS.ID "
        . "WHERE CLOSEDCASH.DATESTART > :start AND CLOSEDCASH.DATEEND <= :stop "
        . "GROUP BY MONEY, HOST, DATESTART, DATEEND "
        . "ORDER BY CLOSEDCASH.DATESTART DESC";

// Payments request
$sqls[] = "SELECT "
        . "CLOSEDCASH.MONEY, PAYMENTS.PAYMENT AS __KEY__, "
        . "SUM(PAYMENTS.TOTAL) AS __VALUE__ "
        . "FROM "
        . "CLOSEDCASH "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
        . "LEFT JOIN PAYMENTS ON PAYMENTS.RECEIPT = RECEIPTS.ID "
        . "WHERE CLOSEDCASH.DATESTART > :start AND CLOSEDCASH.DATEEND <= :stop "
        . "GROUP BY MONEY, __KEY__ "
        . "ORDER BY CLOSEDCASH.DATESTART DESC";

// Taxes request
$sqls[] = "SELECT "
        . "CLOSEDCASH.MONEY, TAXES.NAME as __KEY__, "
        . "SUM(TAXLINES.AMOUNT) AS __VALUE__ "
        . "FROM CLOSEDCASH "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
        . "LEFT JOIN TAXLINES ON TAXLINES.RECEIPT = RECEIPTS.ID "
        . "LEFT JOIN TAXES ON TAXLINES.TAXID = TAXES.ID "
        . "WHERE CLOSEDCASH.DATESTART > :start AND CLOSEDCASH.DATEEND < :stop "
        . "GROUP BY CLOSEDCASH.MONEY, TAXES.NAME "
        . "ORDER BY CLOSEDCASH.DATESTART DESC";

$fields = array("HOST", "DATESTART", "DATEEND", "TICKETS", "SALES");
$mergeFields = array("MONEY");
$headers = array(
        \i18n("Session.host"),
        \i18n("Session.openDate"),
        \i18n("Session.closeDate"),
        \i18n("Tickets", PLUGIN_NAME),
        \i18n("Sales", PLUGIN_NAME),
);
$report = new \Pasteque\MergedReport($sqls, $headers, $fields, $mergeFields);
$report->setParam(":start", $start);
$report->setParam(":stop", $stop);

$report->addFilter("DATESTART", "\Pasteque\stdtimefstr");
$report->addFilter("DATESTART", "\i18nDatetime");
$report->addFilter("DATEEND", "\Pasteque\stdtimefstr");
$report->addFilter("DATEEND", "\i18nDatetime");
$report->addFilter("SALES", "\i18nCurr");
$report->addMergedFilter(0, "\i18nCurr");
$report->addMergedHeaderFilter(0, "\i18n");
$report->addMergedFilter(1, "\i18nCurr");
\Pasteque\register_report(PLUGIN_NAME, "ztickets", $report);
?>
