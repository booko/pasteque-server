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
$zticket = \Pasteque\CashesService::getZTicket($sessId);


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
			<td><?php echo($zticket->ticketCount); ?></td>
		</tr>
		<tr>
			<td><?php \pi18n("Consolidated sales", PLUGIN_NAME); ?></td>
			<td><?php \pi18nCurr($zticket->cs); ?></td>
		</tr>
	</tbody>
</table>

<table cellpadding="0" cellspacing="0">
	<thead>
		<th colspan="2"><?php \pi18n("Payments", PLUGIN_NAME); ?></th>
	</thead>
	<tbody>
<?php $currSrv = new \Pasteque\CurrenciesService();
foreach ($zticket->payments as $payment) { ?>
		<tr>
			<td><?php \pi18n($payment['code'], PLUGIN_NAME); ?></td>
			<td class="numeric"><?php echo $currSrv->get($payment['currencyId'])->format($payment['amount']); ?></td>
		</tr>
<?php } ?>
</table>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3"><?php \pi18n("Taxes", PLUGIN_NAME); ?></th>
		</tr>
		<tr>
			<td></td>
			<td><?php \pi18n("Base", PLUGIN_NAME); ?></td>
			<td><?php \pi18n("Amount", PLUGIN_NAME); ?></td>
	</thead>
	<tbody>
<?php foreach ($zticket->taxes as $tax) { ?>
		<tr>
			<td><?php echo \Pasteque\TaxesService::getTax($tax['id'])->label; ?></td>
			<td class="numeric"><?php \pi18nCurr($tax['base']); ?></td>
			<td class="numeric"><?php \pi18nCurr($tax['amount']); ?></td>
		</tr>
<?php } ?>
</table>

<table cellpadding="0" cellspacing="0">
	<thead>
		<th colspan="2"><?php \pi18n("Sales by category", PLUGIN_NAME); ?></th>
	</thead>
	<tbody>
<?php foreach ($zticket->catSales as $cat) { ?>
		<tr>
			<td><?php \pi18n(\Pasteque\CategoriesService::get($cat['id'])->label, PLUGIN_NAME); ?></td>
			<td class="numeric"><?php \pi18nCurr($cat['amount']); ?></td>
		</tr>
<?php } ?>
</table>
