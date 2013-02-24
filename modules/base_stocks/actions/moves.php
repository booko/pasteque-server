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

$message = NULL;
$error = NULL;
if (isset($_POST['type'])) {
    $error = "Not supported yet";
}

$categories = \Pasteque\CategoriesService::getAll();
$products = \Pasteque\ProductsService::getAll(TRUE);

function catalog_category($category, $js) {
    echo "<a id=\"category-" . $category->id . "\" class=\"catalog-category\" onClick=\"javascript:" . $js . "return false;\">";
    echo "<img src=\"?" . \Pasteque\URL_ACTION_PARAM . "=img&w=category&id=" . $category->id . "\" />";
    echo "<p>" . $category->label . "</p>";
    echo "</a>";
}
?>
<h1><?php \pi18n("Stock move", PLUGIN_NAME); ?></h1>

<?php if ($message !== NULL) {
    echo "<div class=\"message\">" . $message . "</div>\n";
}
if ($error !== NULL) {
    echo "<div class=\"error\">" . $error . "</div>\n";
}
?>

<form class="edit" id="move" method="post">
	<div class="row">
		<label for="type"><?php \pi18n("Operation", PLUGIN_NAME); ?></label>
		<select id="type" name="type">
			<option name="input"><?php \pi18n("Input (buy)", PLUGIN_NAME); ?></option>
			<option name="output"><?php \pi18n("Output (sell)", PLUGIN_NAME); ?></option>
			<option name="return"><?php \pi18n("Output (return to supplyer)", PLUGIN_NAME); ?></option>
		</select>
	</div>

	<div class="catalog-categories-container">
<?php foreach ($categories as $category) {
	catalog_category($category, "changeCategory('" . $category->id . "');");
} ?>
	</div>

	<div id="products" class="catalog-products-container">
	</div>


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

<script type="text/javascript">
	centerImage = function(selector) {
		var container = jQuery(selector);
		var img = container.children("img");
		var containerWidth = parseInt(container.css('width'));
		var containerHeight = parseInt(container.css('height'));
		var imgWidth = parseInt(img.css('width'));
		var imgHeight = parseInt(img.css('height'));
		var hOffset = (containerWidth - imgWidth) / 2;
		var vOffset = (containerHeight - imgHeight) / 2;
		img.css("left", hOffset + "px");
		img.css("top", vOffset + "px");
	}

	jQuery().ready(function() {
<?php foreach ($categories as $category) {
	echo "\t\tcenterImage('#category-" . $category->id . "');\n";
} ?>
	});

	var productsByCategory = new Array();
	var products = new Array();

	addProductToCat = function(product, category) {
		if (typeof(productsByCategory[category]) != 'object') {
			productsByCategory[category] = new Array();
		}
		productsByCategory[category].push(product);
	}
<?php foreach ($products as $product) {
	echo "\taddProductToCat(\"" . $product->id . "\", \"" . $product->category->id . "\");\n";
	echo "\tproducts[\"" . $product->id . "\"] = {\"id\":\"" . $product->id . "\", \"label\": \"" . $product->label . "\", \"reference\": \"" . $product->reference . "\", \"img\": \"?" . \Pasteque\URL_ACTION_PARAM . "=img&w=product&id=" . $product->id . "\"};\n";
} ?>

	showProduct = function(productId) {
		var product = products[productId];
		html = "<a id=\"product-" + productId + "\"class=\"catalog-product\" onClick=\"javascript:addProduct('" + product['id'] + "');return false;\">";
		html += "<img src=\"" + product["img"] + "\" />";
		html += "<p>" + product['label'] + "</p>";
		html += "</a>";
		jQuery("#products").append(html);
		centerImage("#product-" + productId);
	}

	changeCategory = function(category) {
		jQuery("#products").html("");
		var prdCat = productsByCategory[category];
		for (var i = 0; i < prdCat.length; i++) {
			showProduct(prdCat[i]);
		}
	}

	addProduct = function(productId) {
		var product = products[productId];
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

<?php if (count($categories) > 0) {
	echo "\tchangeCategory(\"" . $categories[0]->id . "\");\n";
} ?>
</script>
