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

$sql = "SELECT "
        . "CASHREGISTERS.NAME AS CASHREGISTER, "
        . "TAXES.NAME, SUM(TAXLINES.BASE) AS BASE, "
        . "SUM(TAXLINES.AMOUNT) AS AMOUNT, "
        . "DATESTART, DATEEND "
        . "FROM RECEIPTS "
        . "LEFT JOIN CLOSEDCASH ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
        . "LEFT JOIN TAXLINES ON TAXLINES.RECEIPT = RECEIPTS.ID "
        . "LEFT JOIN TAXES ON TAXLINES.TAXID = TAXES.ID "
        . "LEFT JOIN CASHREGISTERS ON CLOSEDCASH.CASHREGISTER_ID = CASHREGISTERS.ID "
        . "WHERE CLOSEDCASH.DATESTART > :start AND CLOSEDCASH.DATEEND < :stop "
        . "GROUP BY CLOSEDCASH.MONEY, CASHREGISTERS.NAME, TAXES.NAME "
        . "ORDER BY CLOSEDCASH.DATESTART ASC, TAXES.NAME ASC";

$fields = array("CASHREGISTER", "DATESTART", "DATEEND", "NAME", "BASE", "AMOUNT");

$headers = array(
        \i18n("CashRegister.label"),
        \i18n("Session.openDate"),
        \i18n("Session.closeDate"),
        \i18n("Tax name", PLUGIN_NAME),
        \i18n("Tax base", PLUGIN_NAME),
        \i18n("Tax amount", PLUGIN_NAME)
        );

$report = new \Pasteque\Report(PLUGIN_NAME, "taxes_report",
        \i18n("Taxes report", PLUGIN_NAME),
        $sql, $headers, $fields);

$report->addInput("start", \i18n("Session.openDate"), \Pasteque\DB::DATE);
$report->setDefaultInput("start", time() - (time() % 86400) - 30 * 86400);
$report->addInput("stop", \i18n("Session.closeDate"), \Pasteque\DB::DATE);
$report->setDefaultinput("stop", time() - (time() % 86400) + 86400);

$report->setGrouping("CASHREGISTER");
$report->addSubtotal("BASE", \Pasteque\Report::TOTAL_SUM);
$report->addTotal("BASE", \Pasteque\Report::TOTAL_SUM);
$report->addSubtotal("AMOUNT", \Pasteque\Report::TOTAL_SUM);
$report->addTotal("AMOUNT", \Pasteque\Report::TOTAL_SUM);
$report->addFilter("DATESTART", "\Pasteque\stdtimefstr");
$report->addFilter("DATESTART", "\i18nDatetime");
$report->addFilter("DATEEND", "\Pasteque\stdtimefstr");
$report->addFilter("DATEEND", "\i18nDatetime");
$report->setVisualFilter("BASE", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("BASE", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->setVisualFilter("AMOUNT", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->setVisualFilter("AMOUNT", "\i18nFlt", \Pasteque\Report::DISP_CSV);
$report->SetVisualFilter("BASE", "\i18nCurr", \Pasteque\Report::DISP_USER);
$report->SetVisualFilter("BASE", "\i18nFlt", \Pasteque\Report::DISP_CSV);

\Pasteque\register_report($report);
