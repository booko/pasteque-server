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

$message = NULL;
$error = NULL;

$pdo = \Pasteque\PDOBuilder::getPDO();

$sessId = $_GET['id'];
$session = \Pasteque\CashesService::get($sessId);

$cs = "";
$csSql = "SELECT SUM(PAYMENTS.TOTAL) AS CS "
        . "FROM PAYMENTS, RECEIPTS "
        . "WHERE PAYMENTS.RECEIPT = RECEIPTS.ID AND RECEIPTS.MONEY = :id";
$csStmt = $pdo->prepare($csSql);
$csStmt->bindParam(":id", $sessId);
$csStmt->execute();
if ($row = $csStmt->fetch()) {
    $cs = $row['CS'];
}
$payments = array();
$pmtsSql = "SELECT PAYMENTS.PAYMENT AS TYPE, SUM(PAYMENTS.TOTAL) AS TOTAL "
        . "FROM PAYMENTS, RECEIPTS "
        . "WHERE PAYMENTS.RECEIPT = RECEIPTS.ID AND RECEIPTS.MONEY = :id "
        . "GROUP BY PAYMENTS.PAYMENT";
$pmtsStmt = $pdo->prepare($pmtsSql);
$pmtsStmt->bindParam(":id", $sessId);
$pmtsStmt->execute();
while ($row = $pmtsStmt->fetch()) {
    $payments[] = $row;
}

$ticketsCount = 0;
$sales = 0;
$custs = 0;
$glbSql = "SELECT COUNT(DISTINCT RECEIPTS.ID) AS TKTS, "
        . "SUM(TICKETLINES.UNITS * TICKETLINES.PRICE) AS SALES, "
        . "SUM(TICKETS.CUSTCOUNT) AS CUSTCOUNT "
        . "FROM RECEIPTS, TICKETS, TICKETLINES "
        . "WHERE RECEIPTS.ID = TICKETLINES.TICKET "
        . "AND RECEIPTS.ID = TICKETS.ID "
        . "AND RECEIPTS.MONEY = :id";
$glbStmt = $pdo->prepare($glbSql);
$glbStmt->bindParam(":id", $sessId);
$glbStmt->execute();
if ($row = $glbStmt->fetch()) {
    $ticketsCount = $row['TKTS'];
    $sales = $row['SALES'];
    $custs = $row['CUSTCOUNT'];
}

$catSales = array();
$catSql = "SELECT SUM(TICKETLINES.UNITS * TICKETLINES.PRICE) AS SUM, "
        . "CATEGORIES.NAME AS CAT "
        . "FROM RECEIPTS, TICKETS, TICKETLINES, PRODUCTS, CATEGORIES "
        . "WHERE RECEIPTS.ID = TICKETLINES.TICKET "
        . "AND RECEIPTS.ID = TICKETS.ID "
        . "AND TICKETLINES.PRODUCT = PRODUCTS.ID "
        . "AND PRODUCTS.CATEGORY = CATEGORIES.ID "
        . "AND RECEIPTS.MONEY = :id "
        . "GROUP BY CATEGORIES.NAME";
$catStmt = $pdo->prepare($catSql);
$catStmt->bindParam(":id", $sessId);
$catStmt->execute();
while ($row = $catStmt->fetch()) {
    $catSales[] = $row;
}

$totalTaxes = 0;
$ttaxSql = "SELECT SUM(TAXLINES.AMOUNT) AS TOTAL "
        . "FROM RECEIPTS, TAXLINES WHERE RECEIPTS.ID = TAXLINES.RECEIPT "
        . "AND RECEIPTS.MONEY = :id";
$ttaxStmt = $pdo->prepare($ttaxSql);
$ttaxStmt->bindParam(":id", $sessId);
$ttaxStmt->execute();
if ($row = $ttaxStmt->fetch()) {
    $totalTaxes = $row['TOTAL'];
}

$taxes = array();
$taxSql = "SELECT TAXCATEGORIES.NAME AS TAX, SUM(TAXLINES.AMOUNT) AS SUM "
        . "FROM RECEIPTS, TAXLINES, TAXES, TAXCATEGORIES "
        . "WHERE RECEIPTS.ID = TAXLINES.RECEIPT AND "
        . "TAXLINES.TAXID = TAXES.ID AND "
        . "TAXES.CATEGORY = TAXCATEGORIES.ID "
        . "AND RECEIPTS.MONEY = :id "
        . "GROUP BY TAXCATEGORIES.NAME";
$taxStmt = $pdo->prepare($taxSql);
$taxStmt->bindParam(":id", $sessId);
$taxStmt->execute();
while ($row = $taxStmt->fetch()) {
    $taxes[] = $row;
}

if ($session->isClosed()) {
    $title = \i18n("Closed session", PLUGIN_NAME);
} else {
    $title = \i18n("Active session", PLUGIN_NAME);
}
?>

<h1><?php echo($title); ?></h1>

<table cellpadding="0" cellspacing="0">
	<thead>
		<th colspan="2"><?php \pi18n("Session"); ?></th>
	</thead>
	<tbody>
		<tr>
			<td><?php \pi18n("Session.host"); ?></td>
			<td><?php echo($session->host); ?></td>
		</tr>
		<tr>
			<td><?php \pi18n("Session.openDate"); ?></td>
			<td><?php \pi18nDateTime($session->openDate); ?></td>
		</tr>
<?php if ($session->isClosed()) { ?>
		<tr>
			<td><?php \pi18n("Session.closeDate"); ?></td>
			<td><?php \pi18nDateTime($session->closeDate); ?></td>
		</tr>
<?php } ?>
		<tr>
			<td><?php \pi18n("Tickets", PLUGIN_NAME); ?></td>
			<td><?php echo($ticketsCount); ?></td>
		</tr>
		<tr>
			<td><?php \pi18n("Consolidated sales", PLUGIN_NAME); ?></td>
			<td><?php \pi18nCurr($cs); ?></td>
		</tr>
	</tbody>
</table>

<table cellpadding="0" cellspacing="0">
	<thead>
		<th colspan="2"><?php \pi18n("Payments", PLUGIN_NAME); ?></th>
	</thead>
	<tbody>
<?php foreach ($payments as $payment) { ?>
		<tr>
			<td><?php \pi18n($payment['TYPE'], PLUGIN_NAME); ?></td>
			<td class="numeric"><?php \pi18nCurr($payment['TOTAL']); ?></td>
		</tr>
<?php } ?>
</table>

<table cellpadding="0" cellspacing="0">
	<thead>
		<th colspan="2"><?php \pi18n("Taxes", PLUGIN_NAME); ?></th>
	</thead>
	<tbody>
<?php foreach ($taxes as $tax) { ?>
		<tr>
			<td><?php echo $tax['TAX']; ?></td>
			<td class="numeric"><?php \pi18nCurr($tax['SUM']); ?></td>
		</tr>
<?php } ?>
</table>

<table cellpadding="0" cellspacing="0">
	<thead>
		<th colspan="2"><?php \pi18n("Sales by category", PLUGIN_NAME); ?></th>
	</thead>
	<tbody>
<?php foreach ($catSales as $cat) { ?>
		<tr>
			<td><?php \pi18n($cat['CAT'], PLUGIN_NAME); ?></td>
			<td class="numeric"><?php \pi18nCurr($cat['SUM']); ?></td>
		</tr>
<?php } ?>
</table>
