<?php
//    Pastèque Web back office, Products module
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

// products action

namespace BaseProducts;

if (isset($_POST['id'])) {
    $edit = \Pasteque\Product::__form($_POST);
    if ($edit !== NULL) {
        \Pasteque\ProductsService::update($edit);
    }
} else if (isset($_POST['ref'])) {
    $new = \Pasteque\Product::__form($_POST);
    if ($new !== NULL) {
        \Pasteque\ProductsService::create($new);
    }
}

$product = NULL;
if (isset($_GET['id'])) {
    $product = \Pasteque\ProductsService::get($_GET['id']);
}
$taxes = \Pasteque\TaxesService::getAll();
$categories = \Pasteque\CategoriesService::getAll();
?>
<h1><?php \pi18n("Edit a product", PLUGIN_NAME); ?></h1>

<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("edit", $product, "id"); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "reference", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "tax_cat_id", "pick", array("model" => "TaxCategory")); ?>
	<label for="sell"><?php \pi18n("Sell price + taxes", PLUGIN_NAME); ?></label><input id="sell" type="numeric" name="selltax" />
	<?php \Pasteque\form_input("edit", "Product", $product, "category_ids", "pick", array("model" => "Category")); ?>
	
	<?php \Pasteque\form_send(); ?>
</form>
