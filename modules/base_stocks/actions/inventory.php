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

$locSrv = new \Pasteque\LocationsService();
$locations = $locSrv->getAll();
$locNames = array();
$locIds = array();
foreach ($locations as $location) {
    $locNames[] = $location->label;
    $locIds[] = $location->id;
}
$currLocation = null;
if (isset($_POST['location'])) {
    $currLocation = $_POST['location'];
} else {
    $currLocation = $locations[0]->id;
}
$products = \Pasteque\ProductsService::getAll(true);
$levels = \Pasteque\StocksService::getLevels($currLocation);
?>
<h1><?php \pi18n("Inventory", PLUGIN_NAME); ?></h1>

<?php if (count($locations) > 1) { ?>
<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<div class="row">
		<?php \Pasteque\form_select("location", \i18n("Location"), $locIds, $locNames, $currLocation); ?>
	</div>
	<div class="row actions">
		<?php \Pasteque\form_send(); ?>
	</div>
</form>
<?php } ?>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th></th>
			<th><?php \pi18n("Product.reference"); ?></th>
			<th><?php \pi18n("Product.label"); ?></th>
			<th><?php \pi18n("Quantity"); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$par = FALSE;
foreach ($levels as $level) {
$par = !$par;
$prdRef = "";
$prdLabel = "";
foreach ($products as $product) {
    if ($product->id == $level->productId) {
        $prdLabel = $product->label;
        $prdRef = $product->reference;
        break;
    }
}
$security = $level->security;
$max = $level->max;
$qty = $level->qty !== null ? $level->qty : 0;
$class = "";
$help = "";
if ($security !== NULL && $qty < $security) {
    $class=" warn-level";
    $help = ' title="' . \i18n("Stock is below security level!", PLUGIN_NAME) . '"';
}
if ($qty < 0) {
    $class=" alert-level";
    $help = ' title="' . \i18n("Stock is negative!", PLUGIN_NAME) . '"';
} else if ($max !== NULL && $qty > $max) {
    $class=" alert-level";
    $help = ' title="' . \i18n("Overstock!", PLUGIN_NAME) . '"';
}
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
	    <td><img class="thumbnail" src="?<?php echo \Pasteque\PT::URL_ACTION_PARAM; ?>=img&w=product&id=<?php echo $level->productId; ?>" />
		<td><?php echo $prdRef; ?></td>
		<td><?php echo $prdLabel; ?></td>
		<td class="numeric<?php echo $class; ?>"<?php echo $help; ?>><?php echo $qty; ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>
