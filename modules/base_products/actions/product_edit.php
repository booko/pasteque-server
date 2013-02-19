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

if (isset($_POST['ref'])) {
    $def = \Pasteque\ModelFactory::get("product");
    if ($def->checkForm($_POST)) {
        if (isset($_POST['id'])) {
            \Pasteque\ModelService::update("product", $_POST);
        } else {
            \Pasteque\ModelService::create("product", $_POST);
        }
    }
}

$product = NULL;
if (isset($_GET['id'])) {
    $product = \Pasteque\ModelService::get("product", $_GET['id']);
}
?>
<h1><?php \pi18n("Edit a product", PLUGIN_NAME); ?></h1>

<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("edit", $product, "id"); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "ref", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "name", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "taxcat_id", "pick", array("model" => "taxcategory")); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "pricesell", "numeric", array("required" => true)); ?>
	<label for="sell"><?php \pi18n("Sell price + taxes", PLUGIN_NAME); ?></label><input id="sell" type="numeric" name="selltax" />
	<h2><?php \pi18n("Categories", PLUGIN_NAME); ?></h2>
	<?php \Pasteque\form_input("edit", "Product", $product, "category_ids", "pick_multiple", array("model" => "category")); ?>
	
	<?php \Pasteque\form_send(); ?>
</form>
