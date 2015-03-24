<?php
//    Pastèque Web back office, Product barcodes module
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

namespace ProductBarcodes;

$message = NULL;
$error = NULL;

$categories = \Pasteque\CategoriesService::getAll();
$allProducts = \Pasteque\ProductsService::getAll(TRUE);
$products = array();
foreach ($allProducts as $product) {
    if ($product->barcode !== NULL && $product->barcode != "") {
        $products[] = $product;
    }
}

?>

<!-- start bloc titre -->
<div class="blc_ti">
<h1><?php \pi18n("Tags", PLUGIN_NAME); ?></h1>
</div>
<!-- end bloc titre -->

<!-- start container scroll -->
<div class="container_scroll">
    <div class="stick_row stickem-container">
                    <!-- start colonne contenu -->
                    <div id="content_liste" class="grid_9">
                        <div class="blc_content">
<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="?<?php echo \Pasteque\PT::URL_ACTION_PARAM; ?>=print&w=pdf&m=<?php echo PLUGIN_NAME; ?>&n=tags" method="post">
    <div class="row">
        <label for="start_from"><?php \pi18n("Start from", PLUGIN_NAME); ?></label>
        <input type="numeric" name="start_from" id="start_from" value="1" />
    </div>
    <div class="row">
        <label for="format"><?php \pi18n("Format", PLUGIN_NAME); ?></label>
        <select name="format" id="format">
        <?php
            $dir = opendir("modules/product_barcodes/print/templates/");
            echo getcwd();
            while($f = readdir($dir)) {
                if($f != "." && $f != ".." && $f != "index.php") {
                    $name = substr($f,0,strpos($f,".php"));
                    echo "\t\t\t<option value=\"" . $name . "\">" . $name ."</option>\n";
                }
            }
        ?>
        </select>
    </div>

    <div id="catalog-picker"></div>

	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th></th>
				<th><?php \pi18n("Product.reference"); ?></th>
				<th><?php \pi18n("Product.label"); ?></th>
				<th><?php \pi18n("Quantity"); ?></th>
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

<?php \Pasteque\init_catalog("catalog", "catalog-picker", "addProduct",
        $categories, $products); ?>

<script type="text/javascript">

	addProduct = function(productId) {
		var product = catalog.products[productId];
		if (jQuery("#line-" + productId).length > 0) {
			// Add quantity to existing line
			var qty = jQuery("#line-" + productId + "-qty");
			var currVal = qty.val();
			qty.val(parseInt(currVal) + 1);
		} else {
			// Add line
			var html = "<tr id=\"line-" + product['id'] + "\">\n";
			html += "<td><img class=\"thumbnail\" src=\"" + product['img'] + "\" /></td>\n";
			html += "<td>" + product['reference'] + "</td>\n";
			html += "<td>" + product['label'] + "</td>\n";
			html += "<td class=\"qty-cell\"><input class=\"qty\" id=\"line-" + product['id'] + "-qty\" type=\"numeric\" name=\"qty-" + product['id'] + "\" value=\"1\" />\n";
			html += "<td><a class=\"btn-delete\" href=\"\" onClick=\"javascript:deleteLine('" + product['id'] + "');return false;\"><?php \pi18n("Delete"); ?></a></td>\n";
			html += "</tr>\n"; 
			jQuery("#list").append(html);
		}
	}

	deleteLine = function(productId) {
		jQuery("#line-" + productId).detach();
	}
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
