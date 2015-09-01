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

namespace BaseSales;

$sqls = array();
$sqls[] = "SELECT AVERAGE.NAME, AVERAGE.DATESTART, AVERAGE.DATEEND, "
        . "AVERAGE.TICKETS, AVERAGE.AVERAGE, "
        . "REALCS.TICKETAMOUNT AS REALCS, THEOCS.AMOUNT AS THEOCS, THEOCS.SUBAMOUNT AS THEOSCS "
        . "FROM "
        . ""
        . "(SELECT LIST.MONEY, NAME, DATESTART, DATEEND, COUNT(LIST.TICKET) AS TICKETS, AVG(LIST.TICKETAMOUNT) AS AVERAGE "
        . "FROM "
        . "(SELECT CLOSEDCASH.MONEY, NAME, DATESTART, DATEEND, "
        . "SUM(PAYMENTS.TOTAL) AS TICKETAMOUNT, RECEIPTS.ID AS TICKET "
        . "FROM PAYMENTS "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.ID = PAYMENTS.RECEIPT "
        . "LEFT JOIN CLOSEDCASH ON CLOSEDCASH.MONEY = RECEIPTS.MONEY "
        . "LEFT JOIN CASHREGISTERS ON CLOSEDCASH.CASHREGISTER_ID = CASHREGISTERS.ID "
        . "GROUP BY TICKET, CLOSEDCASH.MONEY, NAME, DATESTART, DATEEND) "
        . "AS LIST "
        . "GROUP BY LIST.MONEY, LIST.NAME, LIST.DATESTART, LIST.DATEEND "
        . ") AS AVERAGE "
        . ""
        . "LEFT JOIN "
        . "(SELECT CLOSEDCASH.MONEY, NAME, DATESTART, DATEEND, SUM(PAYMENTS.TOTAL) AS TICKETAMOUNT "
        . "FROM PAYMENTS "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.ID = PAYMENTS.RECEIPT "
        . "LEFT JOIN CLOSEDCASH ON CLOSEDCASH.MONEY = RECEIPTS.MONEY "
        . "LEFT JOIN CASHREGISTERS ON CLOSEDCASH.CASHREGISTER_ID = CASHREGISTERS.ID "
        . "WHERE PAYMENTS.PAYMENT IN ('cash', 'magcard', 'cheque', 'paperin') "
        . "GROUP BY CLOSEDCASH.MONEY, NAME, DATESTART, DATEEND) AS REALCS "
        . "ON AVERAGE.MONEY = REALCS.MONEY "
        . ""
        . "LEFT JOIN "
        . "(SELECT CLOSEDCASH.MONEY, CASHREGISTERS.NAME, DATESTART, DATEEND, "
        . "SUM(TICKETLINES.PRICE * (1 + TAXES.RATE) * TICKETLINES.UNITS * (1 - TICKETLINES.DISCOUNTRATE)) AS AMOUNT, "
        . "SUM(TICKETLINES.PRICE * TICKETLINES.UNITS * (1 - TICKETLINES.DISCOUNTRATE)) AS SUBAMOUNT "
        . "FROM TICKETLINES "
        . "LEFT JOIN TAXES ON TICKETLINES.TAXID = TAXES.ID "
        . "LEFT JOIN RECEIPTS ON TICKETLINES.TICKET = RECEIPTS.ID "
        . "LEFT JOIN CLOSEDCASH ON CLOSEDCASH.MONEY = RECEIPTS.MONEY "
        . "LEFT JOIN CASHREGISTERS ON CLOSEDCASH.CASHREGISTER_ID = CASHREGISTERS.ID "
        . "LEFT JOIN PRODUCTS ON TICKETLINES.PRODUCT = PRODUCTS.ID "
        . "WHERE PRODUCTS.CATEGORY != '-1' "
        . "GROUP BY CLOSEDCASH.MONEY, CASHREGISTERS.NAME) AS THEOCS "
        . "ON AVERAGE.MONEY = THEOCS.MONEY "
        . ""
        . "WHERE AVERAGE.DATESTART > :start AND AVERAGE.DATEEND < :stop "
        . "ORDER BY NAME ASC, DATESTART ASC";

$sqls[] = "SELECT CASHREGISTERS.NAME, CLOSEDCASH.DATESTART, "
        . "CLOSEDCASH.DATEEND,"
        . "TAXES.NAME as __KEY__, SUM(TAXLINES.AMOUNT) AS __VALUE__ "
        . "FROM CLOSEDCASH "
        . "LEFT JOIN CASHREGISTERS ON CLOSEDCASH.CASHREGISTER_ID = CASHREGISTERS.ID "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
        . "LEFT JOIN TICKETS ON TICKETS.ID = RECEIPTS.ID "
        . "LEFT JOIN TAXLINES ON TAXLINES.RECEIPT = TICKETS.ID "
        . "LEFT JOIN TAXES ON TAXLINES.TAXID = TAXES.ID "
        . "WHERE CLOSEDCASH.DATESTART > :start AND CLOSEDCASH.DATEEND < :stop "
        . "GROUP BY CLOSEDCASH.MONEY, CASHREGISTERS.NAME, "
        . "CLOSEDCASH.DATESTART, CLOSEDCASH.DATEEND, __KEY__ "
        . "ORDER BY CASHREGISTERS.NAME ASC, CLOSEDCASH.DATESTART ASC";

$fields = array("NAME", "DATESTART", "DATEEND", "TICKETS", "AVERAGE",
       "REALCS", "THEOCS", "THEOSCS");
$mergeFields = array("NAME","DATESTART","DATEEND");

$headers = array(
        \i18n("CashRegister.label"), \i18n("Session.openDate"),
        \i18n("Session.closeDate"), \i18n("Tickets", PLUGIN_NAME),
        \i18n("Average", PLUGIN_NAME), \i18n("Real CS", PLUGIN_NAME),
        \i18n("Theo CS", PLUGIN_NAME), \i18n("Theo SCS", PLUGIN_NAME));

$report = new \Pasteque\MergedReport(PLUGIN_NAME, "cs_report",
        \i18n("Consolidated sales report", PLUGIN_NAME),
        $sqls, $headers, $fields, $mergeFields);

$report->addInput("start", \i18n("Session.openDate"), \Pasteque\DB::DATE);
$report->setDefaultInput("start", time() - (time() % 86400) - 7 * 86400);
$report->addInput("stop", \i18n("Session.closeDate"), \Pasteque\DB::DATE);
$report->setDefaultinput("stop", time() - (time() % 86400) + 86400);

$report->setGrouping("NAME");
$report->addSubtotal("AVERAGE", \Pasteque\Report::TOTAL_AVG);
$report->addSubtotal("REALCS", \Pasteque\Report::TOTAL_SUM);
$report->addSubtotal("THEOCS", \Pasteque\Report::TOTAL_SUM);
$report->addSubtotal("THEOSCS", \Pasteque\Report::TOTAL_SUM);
$report->addSubtotal("TICKETS", \Pasteque\Report::TOTAL_SUM);
$report->addMergedSubtotal(0, \Pasteque\Report::TOTAL_SUM);
$report->addTotal("AVERAGE", \Pasteque\Report::TOTAL_AVG);
$report->addTotal("REALCS", \Pasteque\Report::TOTAL_SUM);
$report->addTotal("THEOCS", \Pasteque\Report::TOTAL_SUM);
$report->addTotal("THEOSCS", \Pasteque\Report::TOTAL_SUM);
$report->addTotal("TICKETS", \Pasteque\Report::TOTAL_SUM);
$report->addMergedTotal(0, \Pasteque\Report::TOTAL_SUM);
$report->addFilter("DATESTART", "\Pasteque\stdtimefstr");
$report->addFilter("DATESTART", "\i18nDatetime");
$report->addFilter("DATEEND", "\Pasteque\stdtimefstr");
$report->addFilter("DATEEND", "\i18nDatetime");
$report->setVisualFilter("AVERAGE", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("AVERAGE", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->setVisualFilter("REALCS", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("REALCS", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->setVisualFilter("THEOCS", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("THEOCS", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->setVisualFilter("THEOSCS", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("THEOSCS", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->SetMergedVisualFilter(0, "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->SetMergedVisualFilter(0, "\i18nFlt", \Pasteque\Report::DISP_CSV);

\Pasteque\register_report($report);
