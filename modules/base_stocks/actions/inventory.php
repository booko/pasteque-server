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
$levels = \Pasteque\StocksService::getLevels($currLocation);
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


<?php if ($multilocations) { ?>
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
$imgSrc = "";
foreach ($products as $product) {
    if ($product->id == $level->productId) {
        $prdLabel = $product->label;
        $prdRef = $product->reference;
        if ($product->hasImage) {
            $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=product&id=" . $product->id;
        } else {
            $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=product";
        }
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
	    <td><img class="thumbnail" src="?<?php echo $imgSrc ?>" />
		<td><?php echo $prdRef; ?></td>
		<td><?php echo $prdLabel; ?></td>
		<td class="numeric<?php echo $class; ?>"<?php echo $help; ?>><?php echo $qty; ?></td>
	</tr>
<?php
}
?>
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