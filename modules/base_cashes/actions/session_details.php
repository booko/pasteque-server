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

$crSrv = new \Pasteque\CashRegistersService();
$cashRegister = $crSrv->get($session->cashRegisterId);

if ($session->isClosed()) {
    $title = \i18n("Closed session", PLUGIN_NAME);
} else {
    $title = \i18n("Active session", PLUGIN_NAME);
}
?>



<!-- start bloc titre -->
<div class="blc_ti">
<h1><?php echo($title); ?></h1>
</div>
<!-- end bloc titre -->

<!-- start container scroll -->
            <div class="container_scroll">
            
            	<div class="stick_row stickem-container">
                    
                    <!-- start colonne contenu -->
                    <div id="content_liste" class="grid_9">
                    
                        <div class="blc_content">
                        
                        
                        
                        
<table cellpadding="0" cellspacing="0">
	<thead>
		<th colspan="2"><?php \pi18n("Session"); ?></th>
	</thead>
	<tbody>
		<tr>
			<td><?php \pi18n("CashRegister.label"); ?></td>
			<td><?php echo($cashRegister->label); ?></td>
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
foreach ($zticket->payments as $payment) {
    $currency = $currSrv->get($payment->currencyId);
    if ($currency->isMain) {
        $amount = \i18nCurr($payment->amount);
    } else {
        $amount = $currency->format($payment->currencyAmount) . " ("
                . \i18nCurr($payment->amount) . ")";
    }
?>
		<tr>
			<td><?php \pi18n($payment->type); ?></td>
			<td class="numeric"><?php echo $amount; ?></td>
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


</div></div>
                    <!-- end colonne contenu -->
                    
                    <!-- start sidebar menu -->
                    <div id="sidebar_menu" class="grid_3 stickem">
                    
                        <div class="blc_content">
                            
                            <!-- start texte editorial -->
                            <div class="edito"><!-- zone_edito --></div>
                            <!-- end texte editorial -->
                            
                            
                        </div>
                        
                    </div>
                    <!-- end sidebar menu -->
                    
        		</div>
                
        	</div>
            <!-- end container scroll -->
