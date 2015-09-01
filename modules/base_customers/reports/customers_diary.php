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

namespace BaseCustomers;

$sql = "SELECT RECEIPTS.DATENEW, TICKETS.TICKETID, "
        . "PRODUCTS.NAME AS PNAME, TICKETS.TICKETTYPE, "
        . "SUM(TICKETLINES.UNITS) AS UNITS, "
        . "ROUND(SUM(TICKETLINES.UNITS * TICKETLINES.PRICE*(1-TICKETLINES.DISCOUNTRATE)),2) AS SUBTOTAL, "
        . "ROUND(SUM(TICKETLINES.UNITS * TICKETLINES.PRICE)*(1+TAXES.RATE)*(1-TICKETLINES.DISCOUNTRATE),2) AS TOTAL, "
        . "ROUND(SUM(TICKETLINES.UNITS * TICKETLINES.PRICE)*(TAXES.RATE)*(1-TICKETLINES.DISCOUNTRATE),2) AS TAXESTOTAL, "
        . "TAXCATEGORIES.NAME AS TAXNAME "
        . "FROM RECEIPTS, CUSTOMERS, TICKETS, TICKETLINES "
        . "LEFT JOIN PRODUCTS ON TICKETLINES.PRODUCT = PRODUCTS.ID "
        . "LEFT JOIN TAXCATEGORIES ON TICKETLINES.TAXID = TAXCATEGORIES.ID "
        . "LEFT JOIN TAXES ON TAXCATEGORIES.ID = TAXES.CATEGORY "
        . "WHERE CUSTOMERS.ID = TICKETS.CUSTOMER "
        . "AND RECEIPTS.ID = TICKETS.ID "
        . "AND TICKETS.ID = TICKETLINES.TICKET "
        . "AND RECEIPTS.DATENEW > :start AND RECEIPTS.DATENEW < :stop "
        . "AND CUSTOMERS.ID = :id "
        . "GROUP BY RECEIPTS.DATENEW, TICKETS.TICKETID, PRODUCTS.NAME, "
        . "TICKETS.TICKETTYPE "
        . "ORDER BY PRODUCTS.NAME, RECEIPTS.DATENEW";

$fields = array("PNAME", "DATENEW", "TICKETID", "UNITS", "SUBTOTAL","TAXNAME","TAXESTOTAL","TOTAL");
$headers = array(\i18n("Product.label"), \i18n("Date"), \i18n("Ticket number"),
        \i18n("Quantity"), \i18n("Subtotal"), \i18n("TaxRate"),
        \i18n("TaxTotal"), \i18n("Total"));

$report = new \Pasteque\Report(PLUGIN_NAME, "customers_diary",
        \i18n("Customer's diary", PLUGIN_NAME),
        $sql, $headers, $fields);

$report->addInput("start", \i18n("Session.openDate"), \Pasteque\DB::DATE);
$report->setDefaultInput("start", time() - 604800);
$report->addInput("stop", \i18n("Session.closeDate"), \Pasteque\DB::DATE);
$report->setDefaultInput("stop", time());
$report->addInput("id", "", "hidden");

$report->addFilter("DATENEW", "\Pasteque\stdtimefstr");
$report->addFilter("DATENEW", "\i18nDatetime");
$report->addFilter("SUBTOTAL", "\i18nCurr");
$report->addFilter("TAXESTOTAL", "\i18nCurr");
$report->addFilter("TOTAL", "\i18nCurr");

\Pasteque\register_report($report);
