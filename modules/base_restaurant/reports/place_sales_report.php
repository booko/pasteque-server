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

$sql = "SELECT TICKETS.CUSTCOUNT, "
        . "MIN(RECEIPTS.DATENEW) AS STARTDATE, "
        . "MAX(RECEIPTS.DATENEW) AS ENDDATE, COUNT(TICKETS.TICKETID) AS COUNT, "
        . "(COUNT(TICKETS.TICKETID) / TICKETS.CUSTCOUNT) AS TABLES ,"
        . "AVG(TAXLINES.BASE) AS AVGSUBPRICE, "
        . "AVG(TAXLINES.BASE + TAXLINES.AMOUNT) AS AVGPRICE "
        . "FROM TICKETS, RECEIPTS, TAXLINES WHERE RECEIPTS.ID = TICKETS.ID "
        . "AND RECEIPTS.ID = TAXLINES.RECEIPT "
        . "AND RECEIPTS.DATENEW > :start "
        . "AND RECEIPTS.DATENEW < :stop "
        . "GROUP BY TICKETS.CUSTCOUNT "
        . "ORDER BY TICKETS.CUSTCOUNT";

$fields = array("CUSTCOUNT", "STARTDATE", "ENDDATE", "COUNT", "TABLES",
        "AVGSUBPRICE", "AVGPRICE");
$headers = array(\i18n("Custcount", PLUGIN_NAME),
        \i18n("Session.openDate"), \i18n("Session.closeDate"),
        \i18n("Number", PLUGIN_NAME), \i18n("Tables", PLUGIN_NAME),
        \i18n("Average price w/o tax", PLUGIN_NAME),
        \i18n("Average price", PLUGIN_NAME)
        );

$report = new \Pasteque\Report(PLUGIN_NAME, "place_sales_report",
        \i18n("Place sales", PLUGIN_NAME),
        $sql, $headers, $fields);

$report->addInput("start", \i18n("Session.openDate"), \Pasteque\DB::DATE);
$report->setDefaultInput("start", time() - (time() % 86400) - 7 * 86400);
$report->addInput("stop", \i18n("Session.closeDate"), \Pasteque\DB::DATE);
$report->setDefaultinput("stop", time() - (time() % 86400) + 86400);

$report->addFilter("DATESTART", "\Pasteque\stdtimefstr");
$report->addFilter("DATESTART", "\i18nDatetime");
$report->addFilter("DATEEND", "\Pasteque\stdtimefstr");
$report->addFilter("DATEEND", "\i18nDatetime");
$report->setVisualFilter("AVGSUBPRICE", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("AVGSUBPRICE", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->setVisualFilter("AVGPRICE", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("AVGPRICE", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->setVisualFilter("TABLES", "\i18nInt", \Pasteque\Report::DISP_CSV | \Pasteque\Report::DISP_USER);

$report->addTotal("TABLES", \Pasteque\Report::TOTAL_SUM);
// sum of count / sum of tables
$report->addTotal("CUSTCOUNT", \Pasteque\Report::TOTAL_SUM);
$report->addTotal("CUSTCOUNT", \Pasteque\Report::TOTAL_AVG);
$report->addPonderate("CUSTCOUNT", "TABLES"); // COUNT = SUM(TABLE * CUSTCOUNT)

$report->addTotal("AVGSUBPRICE", \Pasteque\Report::TOTAL_AVG);
$report->addTotal("AVGPRICE", \Pasteque\Report::TOTAL_AVG);

\Pasteque\register_report($report);