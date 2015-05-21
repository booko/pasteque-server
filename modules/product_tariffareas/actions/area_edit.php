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

namespace ProductTariffAreas;

$message = null;
$error = null;
$srv = new \Pasteque\TariffAreasService();

if (isset($_POST['id'])) {
    // Edit the area
    $area = \Pasteque\TariffArea::__build($_POST['id'], $_POST['label'],
            $_POST['dispOrder'], $_POST['notes']);
    foreach ($_POST as $key => $value) {
        if (strpos($key, "price-") === 0) {
            $productId = substr($key, 6);
            $product = \Pasteque\ProductsService::get($productId);
            $taxCat = \Pasteque\TaxesService::get($product->taxCatId);
            $tax = $taxCat->getCurrentTax();
            $price = $value / (1 + $tax->rate);
            $area->addPrice($productId, $price);
        }
    }
    if ($srv->update($area)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['label'])) {
    $area = new \Pasteque\TariffArea($_POST['label'],
            $_POST['dispOrder'],$_POST['notes']);
    foreach ($_POST as $key => $value) {
        if (strpos($key, "price-") === 0) {
            $productId = substr($key, 6);
            $product = \Pasteque\ProductsService::get($productId);
            $taxCat = \Pasteque\TaxesService::get($product->taxCatId);
            $tax = $taxCat->getCurrentTax();
            $price = $value / (1 + $tax->rate);
            $area->addPrice($productId, $price);
        }
    }
    if ($srv->create($area)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
}


$area = null;
if (isset($_GET['id'])) {
    $area = $srv->get($_GET['id']);
}
$categories = \Pasteque\CategoriesService::getAll();
$products = \Pasteque\ProductsService::getAll(true);

?>

<!-- start bloc titre -->
<div class="blc_ti">
<h1><?php \pi18n("Tariff area", PLUGIN_NAME); ?></h1>
</div>
<!-- end bloc titre -->

<!-- start container scroll -->
<div class="container_scroll">
            
            	<div class="stick_row stickem-container">
                    
                    <!-- start colonne contenu -->
                    <div id="content_liste" class="grid_9">
                    
                        <div class="blc_content">


<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" id="edit" method="post">
	<?php \Pasteque\form_hidden("edit", $area, "id"); ?>
	<?php \Pasteque\form_input("edit", "TariffArea", $area, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "TariffArea", $area, "dispOrder", "numeric", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "TariffArea", $area, "notes", "text"); ?>

    <div id="catalog-picker"></div>

	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th></th>
				<th><?php \pi18n("Product.reference"); ?></th>
				<th><?php \pi18n("Product.label"); ?></th>
				<th><?php \pi18n("Price", PLUGIN_NAME); ?></th>
                                <th><?php \pi18n("Area price", PLUGIN_NAME); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody id="list">
		</tbody>
	</table>

	<div class="row actions">
		<?php \Pasteque\form_send(); ?>
	</div>

</form>

<?php \Pasteque\init_catalog_old("catalog", "catalog-picker", "addProduct",
        $categories, $products); ?>
<script type="text/javascript">

	addProduct = function(productId) {
		var product = catalog.products[productId];
		initProduct(productId, product['vatSell']);
	}

	deleteLine = function(productId) {
		jQuery("#line-" + productId).detach();
	}

    initProduct = function(productId, price) {
    	var product = catalog.products[productId];
		if (jQuery("#line-" + productId).length > 0) {
			// Already there
			return;
		} else {
			// Add line
			var html = "<tr id=\"line-" + product['id'] + "\">\n";
			html += "<td><img class=\"thumbnail\" src=\"" + product['img'] + "\" /></td>\n";
			html += "<td>" + product['reference'] + "</td>\n";
			html += "<td>" + product['label'] + "</td>\n";
			html += "<td>" + product['vatSell'] + "</td>\n";
			html += "<td class=\"price-cell\"><input class=\"price\" id=\"line-" + product['id'] + "\" type=\"numeric\" name=\"price-" + product['id'] + "\" value=\"" + price + "\" />\n";
			html += "<td><a class=\"btn-delete\" href=\"\" onClick=\"javascript:deleteLine('" + product['id'] + "');return false;\"><img alt=\"<?php \pi18n("Delete"); ?>\" src=\"<?php echo get_template_url(); ?>/img/delete.png\" /></a></td>\n";
			html += "</tr>\n"; 
			jQuery("#list").append(html);
		}
    }

    jQuery(document).ready(function() {
<?php
if ($area !== null) {
    foreach ($area->getPrices() as $price) {
        $product = \Pasteque\ProductsService::get($price->productId);
        $taxCat = \Pasteque\TaxesService::get($product->taxCatId);
        $tax = $taxCat->getCurrentTax();
        $vatPrice = $price->price * (1 + $tax->rate);
        //echo "\t\tinitProduct(\"" . $price->productId . "\", " . $vatPrice . ");\n";
        echo "\t\taddProduct('" . $price->productId . "');\n";
    }
} ?>
    });
</script>
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
