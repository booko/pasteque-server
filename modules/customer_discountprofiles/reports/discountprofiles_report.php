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

namespace CustomerDiscountProfiles;

$sql = "SELECT DISCOUNTPROFILES.NAME, TICKETS.DISCOUNTRATE, "
        . "COUNT(TICKETS.ID) AS TICKETS, "
        . "SUM(TICKETLINES.PRICE * TICKETLINES.UNITS) AS BASE, "
        . "SUM(TICKETLINES.PRICE * TICKETLINES.UNITS * (1 + TAXES.RATE)) AS BASETAX, "
        . "(SUM(TICKETLINES.PRICE * TICKETLINES.UNITS) * TICKETS.DISCOUNTRATE) "
        . "AS DISCOUNT, "
        . "(SUM(TICKETLINES.PRICE * TICKETLINES.UNITS * (1 + TAXES.RATE)) * TICKETS.DISCOUNTRATE) AS DISCOUNTTAX "
        . "FROM RECEIPTS "
        . "LEFT JOIN TICKETS ON TICKETS.ID = RECEIPTS.ID "
        . "LEFT JOIN TICKETLINES ON TICKETLINES.TICKET = TICKETS.ID "
        . "LEFT JOIN TAXES ON TICKETLINES.TAXID = TAXES.ID "
        . "LEFT JOIN DISCOUNTPROFILES ON TICKETS.DISCOUNTPROFILE_ID = DISCOUNTPROFILES.ID "
        . "WHERE RECEIPTS.DATENEW > :start AND RECEIPTS.DATENEW < :stop "
        . "AND TICKETS.DISCOUNTRATE != 0 "
        . "GROUP BY DISCOUNTPROFILES.NAME, TICKETS.DISCOUNTRATE;";
$fields = array("NAME", "DISCOUNTRATE", "TICKETS", "BASE", "BASETAX",
        "DISCOUNT", "DISCOUNTTAX");
$headers = array(\i18n("DiscountProfile"), \i18n("DiscountProfile.rate"),
        \i18n("Tickets count", PLUGIN_NAME), \i18n("Base", PLUGIN_NAME),
        \i18n("Taxed base", PLUGIN_NAME), \i18n("Amount", PLUGIN_NAME),
        \i18n("Taxed amount", PLUGIN_NAME));

$report = new \Pasteque\Report(PLUGIN_NAME, "discountprofiles_report",
        \i18n("Discounts by profile", PLUGIN_NAME),
        $sql, $headers, $fields);

$report->addInput("start", \i18n("Start date"), \Pasteque\DB::DATE);
$report->setDefaultInput("start", time() - (time() % 86400) - 7 * 86400);
$report->addInput("stop", \i18n("Stop date"), \Pasteque\DB::DATE);
$report->setDefaultinput("stop", time() - (time() % 86400) + 86400);

$report->addFilter("DATENEW", "\Pasteque\stdtimefstr");
$report->addFilter("DATENEW", "\i18nDatetime");
$report->setVisualFilter("BASE", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("BASE", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->setVisualFilter("BASETAX", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("BASETAX", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->setVisualFilter("DISCOUNT", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("DISCOUNT", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->setVisualFilter("DISCOUNTTAX", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("DISCOUNTTAX", "\i18nFlt", \Pasteque\Report::DISP_CSV);

\Pasteque\register_report($report);