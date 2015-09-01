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

$sql = "SELECT CASHREGISTERS.NAME, CLOSEDCASH.DATESTART, "
        . "CLOSEDCASH.DATEEND, TICKETS.TICKETID, RECEIPTS.DATENEW, "
        . "PRODUCTS.NAME AS PRD_NAME, CATEGORIES.NAME AS CAT_NAME, "
        . "TICKETLINES.UNITS, (TICKETLINES.PRICE * TICKETLINES.UNITS * (1 - TICKETLINES.DISCOUNTRATE)) AS SELL "
        . "FROM TICKETS "
        . "LEFT JOIN RECEIPTS ON TICKETS.ID = RECEIPTS.ID "
        . "LEFT JOIN CLOSEDCASH ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
        . "LEFT JOIN CASHREGISTERS ON CLOSEDCASH.CASHREGISTER_ID = CASHREGISTERS.ID "
        . "LEFT JOIN TICKETLINES ON TICKETLINES.TICKET = TICKETS.ID "
        . "LEFT JOIN PRODUCTS ON TICKETLINES.PRODUCT = PRODUCTS.ID "
        . "LEFT JOIN CATEGORIES ON PRODUCTS.CATEGORY = CATEGORIES.ID "
        . "WHERE CLOSEDCASH.DATESTART > :start AND CLOSEDCASH.DATEEND < :stop "
        . "ORDER BY CLOSEDCASH.DATESTART DESC, TICKETS.TICKETID DESC, "
        . "TICKETLINES.LINE DESC";
$fields = array("NAME", "DATESTART", "DATEEND", "TICKETID", "DATENEW",
        "PRD_NAME", "CAT_NAME", "UNITS", "SELL");
$headers = array(\i18n("CashRegister.label"), \i18n("Session.openDate"),
        \i18n("Session.closeDate"), \i18n("Ticket.number"),
        \i18n("Ticket.date"), \i18n("Product name", PLUGIN_NAME),
        \i18n("Category name", PLUGIN_NAME),
        \i18n("Units", PLUGIN_NAME), \i18n("Sell", PLUGIN_NAME));

$report = new \Pasteque\Report(PLUGIN_NAME, "sales_report",
        \i18n("Sales report", PLUGIN_NAME),
        $sql, $headers, $fields);

$report->addInput("start", \i18n("Session.openDate"), \Pasteque\DB::DATE);
$report->setDefaultInput("start", time() - (time() % 86400) - 86400);
$report->addInput("stop", \i18n("Session.closeDate"), \Pasteque\DB::DATE);
$report->setDefaultinput("stop", time() - (time() % 86400) + 86400);

$report->addFilter("DATESTART", "\Pasteque\stdtimefstr");
$report->addFilter("DATESTART", "\i18nDatetime");
$report->addFilter("DATEEND", "\Pasteque\stdtimefstr");
$report->addFilter("DATEEND", "\i18nDatetime");
$report->addFilter("DATENEW", "\Pasteque\stdtimefstr");
$report->addFilter("DATENEW", "\i18nDatetime");
$report->setVisualFilter("SELL", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("SELL", "\i18nFlt", \Pasteque\Report::DISP_CSV);

\Pasteque\register_report($report);
