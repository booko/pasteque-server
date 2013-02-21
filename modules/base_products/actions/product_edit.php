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
    if (isset($_POST['reference']) && isset($_POST['label'])
            && isset($_POST['selltax']) && isset($_POST['category'])
            && isset($_POST['tax_cat'])) {
        $cat = \Pasteque\Category::__build($_POST['category'], NULL, "dummy");
        $taxCat = \Pasteque\TaxesService::get($_POST['tax_cat']);
        $taxRate = $taxCat->getCurrentTax()->rate;
        $sell = $_POST['selltax'] / (1 + $taxRate);
        $prd = \Pasteque\Product::__build($_POST['id'], $_POST['reference'], $_POST['label'], $sell, $cat, $taxCat,
                TRUE, FALSE, NULL, NULL, NULL);
        \Pasteque\ProductsService::update($prd);
    }
} else if (isset($_POST['reference'])) {
    if (isset($_POST['reference']) && isset($_POST['label'])
            && isset($_POST['selltax']) && isset($_POST['category'])
            && isset($_POST['tax_cat'])) {
        $cat = \Pasteque\Category::__build($_POST['category'], NULL, "dummy");
        $taxCat = \Pasteque\TaxesService::get($_POST['tax_cat']);
        $taxRate = $taxCat->getCurrentTax()->rate;
        $sell = $_POST['selltax'] / (1 + $taxRate);
        $prd = new \Pasteque\Product($_POST['reference'], $_POST['label'], $sell, $cat, $taxCat,
                TRUE, FALSE, NULL, NULL, NULL);
        \Pasteque\ProductsService::create($prd);
    }
}

$product = NULL;
$vatprice = "";
if (isset($_GET['id'])) {
    $product = \Pasteque\ProductsService::get($_GET['id']);
    $tax = $product->tax_cat->getCurrentTax();
    $vatprice = $product->price_sell * (1 + $tax->rate);
}
$taxes = \Pasteque\TaxesService::getAll();
$categories = \Pasteque\CategoriesService::getAll();
?>
<h1><?php \pi18n("Edit a product", PLUGIN_NAME); ?></h1>

<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("edit", $product, "id"); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "reference", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "tax_cat", "pick", array("model" => "TaxCategory")); ?>
	<label for="sell"><?php \pi18n("Sell price + taxes", PLUGIN_NAME); ?></label><input id="sell" type="numeric" name="selltax" value="<?php echo $vatprice; ?>" />
	<?php \Pasteque\form_input("edit", "Product", $product, "category", "pick", array("model" => "Category")); ?>
	
	<?php \Pasteque\form_send(); ?>
</form>
