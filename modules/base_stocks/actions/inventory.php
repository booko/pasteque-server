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

$modules = \Pasteque\get_loaded_modules(\Pasteque\get_user_id());
$multilocations = false;
$defaultLocationId = null;
if (in_array("stock_multilocations", $modules)) {
    $multilocations = true;
}

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
$categories = \Pasteque\CategoriesService::getAll();
$prdCat = array();
// Link products to categories and don't track compositions
foreach ($products as $product) {
    if ($product->categoryId !== \Pasteque\CompositionsService::CAT_ID) {
        $prdCat[$product->categoryId][] = $product;
    }
}
$levels = \Pasteque\StocksService::getLevels($currLocation);
$prdLevel = array();
foreach ($levels as $level) {
    $prdLevel[$level->productId] = $level;
}
?>

<!-- start bloc titre -->
<div class="blc_ti">
<h1><?php \pi18n("Inventory", PLUGIN_NAME); ?></h1>
</div>
<!-- end bloc titre -->

<!-- start container scroll -->
<div class="container_scroll">
        <div class="stick_row stickem-container">
                    <!-- start colonne contenu -->
                    <div id="content_liste" class="grid_9">
                        <div class="blc_content">


<?php if ($multilocations) {
    // Location picker ?>
<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<div class="row">
		<?php \Pasteque\form_select("location", \i18n("Location"), $locIds, $locNames, $currLocation); ?>
	</div>
	<div class="row actions">
		<?php \Pasteque\form_send(); ?>
	</div>
</form>
<?php
}

$par = false;
foreach ($categories as $category) {
    if (isset($prdCat[$category->id])) {
        // Category header ?>
<h3><?php echo \Pasteque\esc_html($category->label); ?></h3>
<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th></th>
			<th><?php \pi18n("Product.reference"); ?></th>
			<th><?php \pi18n("Product.label"); ?></th>
			<th><?php \pi18n("Quantity"); ?></th>
			<th><?php \pi18n("Stock.SellValue"); ?></th>
			<th><?php \pi18n("Stock.BuyValue"); ?></th>
			<th><?php \pi18n("QuantityMin"); ?></th>
			<th><?php \pi18n("QuantityMax"); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
        foreach ($prdCat[$category->id] as $product) {
            if (!isset($prdLevel[$product->id])) {
                continue;
            }
            // Level lines
            $par = !$par;
            $prdRef = "";
            $prdLabel = "";
            $imgSrc = "";
            $prdSellPrice = 0;
            $prdBuyPrice = 0;
            $level = $prdLevel[$product->id];
            if ($product->hasImage) {
                $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=product&id=" . $product->id;
            } else {
                $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=product";
            }
            $prdLabel = $product->label;
            $prdRef = $product->reference;
            $prdSellPrice = $product->priceSell;
            $prdBuyPrice = $product->priceBuy;
            $security = $level->security;
            $max = $level->max;
            $qty = $level->qty !== null ? $level->qty : 0;
            $class = "";
            $help = "";
            if ($security !== null && $qty < $security) {
                $class=" warn-level";
                $help = ' title="' . \Pasteque\esc_attr(\i18n("Stock is below security level!", PLUGIN_NAME)) . '"';
            }
            if ($qty < 0) {
                $class=" alert-level";
                $help = ' title="' . \Pasteque\esc_attr(\i18n("Stock is negative!", PLUGIN_NAME)) . '"';
            } else if ($max !== NULL && $qty > $max) {
                $class=" alert-level";
                $help = ' title="' . \Pasteque\esc_attr(\i18n("Overstock!", PLUGIN_NAME)) . '"';
            }
            if (!isset($security)) {
                $security = \i18n("Undefined");
            }
            if (!isset($max)) {
                $max = \i18n("Undefined");
            }
            ?>
		<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
                 <td><img class="thumbnail" src="?<?php echo \Pasteque\esc_attr($imgSrc); ?>" />
                 <td><?php echo \Pasteque\esc_html($prdRef); ?></td>
                 <td><?php echo \Pasteque\esc_html($prdLabel); ?></td>
                 <td class="numeric<?php echo $class; ?>"<?php echo $help; ?>><?php echo \Pasteque\esc_html($qty); ?></td>
                 <td><?php echo \Pasteque\esc_html(\i18nCurr($prdSellPrice*$qty)); ?></td>
                 <td><?php echo \Pasteque\esc_html(\i18nCurr($prdBuyPrice*$qty)); ?></td>
                 <td><?php echo \Pasteque\esc_html($security); ?></td>
                 <td><?php echo \Pasteque\esc_html($max); ?></td>
		</tr>
<?php
        } ?>
	</tbody>
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
