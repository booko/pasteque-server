<?php
//    Pastèque Web back office, Stocks module
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

namespace BaseStocks;

$products = \Pasteque\ProductsService::getAll();
$stocks = \Pasteque\StocksService::getQties();
$levels = \Pasteque\StocksService::getLevels();
?>
<h1><?php \pi18n("Inventory", PLUGIN_NAME); ?></h1>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th></th>
			<th><?php \pi18n("Product.reference"); ?></th>
			<th><?php \pi18n("Product.label"); ?></th>
			<th><?php \pi18n("Quantity"); ?></th>
			<th><?php \pi18n("Security threshold", PLUGIN_NAME); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$par = FALSE;
foreach ($products as $product) {
$par = !$par;
$qty = isset($stocks[$product->id]) ? $stocks[$product->id] : 0;
$security = isset($levels[$product->id]) ? $levels[$product->id]->security : NULL;
$max = isset($levels[$product->id]) ? $levels[$product->id]->max : NULL;
$class = "";
$help = "";
if ($security !== NULL && $qty < $security) {
    $class=" warn-level";
    $help = ' title="' . \i18n("Stock is below security level!", PLUGIN_NAME) . '"';
}
if ($qty < 0 || ($max !== NULL && $qty > $max)) {
    $class=" alert-level";
    $help = ' title="' . \i18n("Stock is negative!", PLUGIN_NAME) . '"';
}
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
	    <td><img class="thumbnail" src="?<?php echo \Pasteque\URL_ACTION_PARAM; ?>=img&w=product&id=<?php echo $product->id; ?>" />
		<td><?php echo $product->reference; ?></td>
		<td><?php echo $product->label; ?></td>
		<td class="numeric<?php echo $class; ?>"<?php echo $help; ?>><?php echo $qty; ?></td>
		<td class="numeric"><?php echo $security === NULL ? \i18n("Undefined") : $security; ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>
