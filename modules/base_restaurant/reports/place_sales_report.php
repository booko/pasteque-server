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

namespace BaseRestaurant;

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

$sql = "SELECT TICKETS.CUSTCOUNT, "
        . "MIN(RECEIPTS.DATENEW) AS STARTDATE, "
        . "MAX(RECEIPTS.DATENEW) AS ENDDATE, COUNT(TICKETS.TICKETID) AS COUNT, "
        . "(COUNT(TICKETS.TICKETID) / TICKETS.CUSTCOUNT) AS TABLES ,"
        . "AVG(TAXLINES.BASE + TAXLINES.AMOUNT) AS AVGPRICE "
        . "FROM TICKETS, RECEIPTS, TAXLINES WHERE RECEIPTS.ID = TICKETS.ID "
        . "AND RECEIPTS.ID = TAXLINES.RECEIPT "
        . "AND RECEIPTS.DATENEW > :start "
        . "AND RECEIPTS.DATENEW < :stop "
        . "GROUP BY TICKETS.CUSTCOUNT "
        . "ORDER BY TICKETS.CUSTCOUNT";

$fields = array("CUSTCOUNT", "STARTDATE", "ENDDATE", "COUNT", "TABLES", "AVGPRICE");
$headers = array(\i18n("Custcount", PLUGIN_NAME),
        \i18n("Session.openDate"), \i18n("Session.closeDate"),
        \i18n("Number", PLUGIN_NAME), \i18n("Tables", PLUGIN_NAME),
        \i18n("Average price", PLUGIN_NAME)
        );

$report = new \Pasteque\Report($sql, $headers, $fields);
$report->setParam(":start", $start);
$report->setParam(":stop", $stop);
$report->addFilter("DATESTART", "\Pasteque\stdtimefstr");
$report->addFilter("DATESTART", "\i18nDatetime");
$report->addFilter("DATEEND", "\Pasteque\stdtimefstr");
$report->addFilter("DATEEND", "\i18nDatetime");
$report->addFilter("AVGPRICE", "\i18nCurr");
$report->addFilter("TABLES", "\i18nInt");

$report->addTotal("TABLES", \Pasteque\Report::TOTAL_SUM);
// sum of count / sum of tables
$report->addTotal("CUSTCOUNT", \Pasteque\Report::TOTAL_SUM);
$report->addTotal("CUSTCOUNT", \Pasteque\Report::TOTAL_AVG);
$report->addPonderate("CUSTCOUNT", "TABLES"); // COUNT = SUM(TABLE * CUSTCOUNT)

$report->addTotal("AVGPRICE", \Pasteque\Report::TOTAL_AVG);

\Pasteque\register_report(PLUGIN_NAME, "place_sales_report", $report);
?>
