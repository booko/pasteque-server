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

$sql = "SELECT AVERAGE.HOST, AVERAGE.DATESTART, AVERAGE.DATEEND, "
        . "AVERAGE.TICKETS, AVERAGE.AVERAGE, "
        . "REALCS.TICKETAMOUNT AS REALCS, THEOCS.AMOUNT AS THEOCS, THEOCS.SUBAMOUNT AS THEOSCS "
        . "FROM "
        . ""
        . "(SELECT LIST.MONEY, HOST, DATESTART, DATEEND, COUNT(LIST.TICKET) AS TICKETS, AVG(LIST.TICKETAMOUNT) AS AVERAGE "
        . "FROM "
        . "(SELECT CLOSEDCASH.MONEY, HOST, DATESTART, DATEEND, "
        . "SUM(PAYMENTS.TOTAL) AS TICKETAMOUNT, RECEIPTS.ID AS TICKET "
        . "FROM PAYMENTS "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.ID = PAYMENTS.RECEIPT "
        . "LEFT JOIN CLOSEDCASH ON CLOSEDCASH.MONEY = RECEIPTS.MONEY "
        . "GROUP BY TICKET) "
        . "AS LIST "
        . "GROUP BY LIST.MONEY "
        . ") AS AVERAGE "
        . ""
        . "LEFT JOIN "
        . "(SELECT CLOSEDCASH.MONEY, HOST, DATESTART, DATEEND, SUM(PAYMENTS.TOTAL) AS TICKETAMOUNT "
        . "FROM PAYMENTS "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.ID = PAYMENTS.RECEIPT "
        . "LEFT JOIN CLOSEDCASH ON CLOSEDCASH.MONEY = RECEIPTS.MONEY "
        . "WHERE PAYMENTS.PAYMENT IN ('cash', 'magcard', 'cheque', 'paperin') "
        . "GROUP BY CLOSEDCASH.MONEY) AS REALCS "
        . "ON AVERAGE.MONEY = REALCS.MONEY "
        . ""
        . "LEFT JOIN "
        . "(SELECT CLOSEDCASH.MONEY, HOST, DATESTART, DATEEND, "
        . "SUM(TICKETLINES.PRICE * (1 + TAXES.RATE) * TICKETLINES.UNITS) AS AMOUNT, "
        . "SUM(TICKETLINES.PRICE * TICKETLINES.UNITS) AS SUBAMOUNT "
        . "FROM TICKETLINES "
        . "LEFT JOIN TAXES ON TICKETLINES.TAXID = TAXES.ID "
        . "LEFT JOIN RECEIPTS ON TICKETLINES.TICKET = RECEIPTS.ID "
        . "LEFT JOIN CLOSEDCASH ON CLOSEDCASH.MONEY = RECEIPTS.MONEY "
        . "LEFT JOIN PRODUCTS ON TICKETLINES.PRODUCT = PRODUCTS.ID "
        . "WHERE PRODUCTS.CATEGORY != '-1' "
        . "GROUP BY CLOSEDCASH.MONEY) AS THEOCS "
        . "ON AVERAGE.MONEY = THEOCS.MONEY "
        . ""
        . "WHERE AVERAGE.DATESTART > :start AND AVERAGE.DATEEND < :stop "
        . "ORDER BY HOST ASC, DATESTART ASC";
$fields = array("HOST", "DATESTART", "DATEEND", "TICKETS", "AVERAGE",
       "REALCS", "THEOCS", "THEOSCS");
$headers = array(\i18n("Session.host"), \i18n("Session.openDate"),
        \i18n("Session.closeDate"), \i18n("Tickets", PLUGIN_NAME),
        \i18n("Average", PLUGIN_NAME), \i18n("Real CS", PLUGIN_NAME),
        \i18n("Theo CS", PLUGIN_NAME), \i18n("Theo SCS", PLUGIN_NAME));
$report = new \Pasteque\Report($sql, $headers, $fields);
$report->setParam(":start", $start);
$report->setParam(":stop", $stop);
$report->addFilter("DATESTART", "\Pasteque\stdtimefstr");
$report->addFilter("DATESTART", "\i18nDatetime");
$report->addFilter("DATEEND", "\Pasteque\stdtimefstr");
$report->addFilter("DATEEND", "\i18nDatetime");
$report->addFilter("AVERAGE", "\i18nCurr");
$report->addFilter("REALCS", "\i18nCurr");
$report->addFilter("THEOCS", "\i18nCurr");
$report->addFilter("THEOSCS", "\i18nCurr");

\Pasteque\register_report(PLUGIN_NAME, "cs_report", $report);
?>
