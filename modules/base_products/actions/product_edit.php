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

$message = NULL;
$error = NULL;
if (isset($_POST['id'])) {
    if (isset($_POST['reference']) && isset($_POST['label'])
            && isset($_POST['realsell']) && isset($_POST['category'])
            && isset($_POST['tax_cat'])) {
        $cat = \Pasteque\Category::__build($_POST['category'], NULL, "dummy", NULL);
        $taxCat = \Pasteque\TaxesService::get($_POST['tax_cat']);
        $taxRate = $taxCat->getCurrentTax()->rate;
        if ($_FILES['image']['tmp_name'] !== "") {
            $img = file_get_contents($_FILES['image']['tmp_name']);
        } else if ($_POST['clearImage']) {
            $img = NULL;
        } else {
            $img = "";
        }
        $prd = \Pasteque\Product::__build($_POST['id'], $_POST['reference'], $_POST['label'], $_POST['realsell'], $cat, $taxCat,
                FALSE, FALSE, $_POST['price_buy'], NULL, NULL, $img);
        if (\Pasteque\ProductsService::update($prd)) {
            $message = \i18n("Changes saved");
        } else {
            $error = \i18n("Unable to save changes");
        }
    }
} else if (isset($_POST['reference'])) {
    if (isset($_POST['reference']) && isset($_POST['label'])
            && isset($_POST['realsell']) && isset($_POST['category'])
            && isset($_POST['tax_cat'])) {
        $cat = \Pasteque\Category::__build($_POST['category'], NULL, "dummy", NULL);
        $taxCat = \Pasteque\TaxesService::get($_POST['tax_cat']);
        $taxRate = $taxCat->getCurrentTax()->rate;
        if ($_FILES['image']['tmp_name'] !== "") {
            $img = file_get_contents($_FILES['image']['tmp_name']);
        } else {
            $img = NULL;
        }
        $prd = new \Pasteque\Product($_POST['reference'], $_POST['label'], $_POST['realsell'], $cat, $taxCat,
                FALSE, FALSE, $_POST['price_buy'], NULL, NULL, $img);
        $id = \Pasteque\ProductsService::create($prd);
        if ($id !== FALSE) {
            $message = \i18n("Product saved. <a href=\"%s\">Go to the product page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'product_edit', array('id' => $id)));
        } else {
            $error = \i18n("Unable to save changes");
        }
    }
}

$product = NULL;
$vatprice = "";
$price = "";
if (isset($_GET['id'])) {
    $product = \Pasteque\ProductsService::get($_GET['id']);
    $tax = $product->tax_cat->getCurrentTax();
    $vatprice = $product->price_sell * (1 + $tax->rate);
    $price = sprintf("%.2f", $product->price_sell);
}
$taxes = \Pasteque\TaxesService::getAll();
$categories = \Pasteque\CategoriesService::getAll();
?>
<h1><?php \pi18n("Edit a product", PLUGIN_NAME); ?></h1>

<?php if ($message !== NULL) {
    echo "<div class=\"message\">" . $message . "</div>\n";
}
if ($error !== NULL) {
    echo "<div class=\"error\">" . $error . "</div>\n";
}
?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post" enctype="multipart/form-data">
    <?php \Pasteque\form_hidden("edit", $product, "id"); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "reference", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "category", "pick", array("model" => "Category")); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "tax_cat", "pick", array("model" => "TaxCategory")); ?>
	<div class="row">
		<label for="sellvat"><?php \pi18n("Sell price + taxes", PLUGIN_NAME); ?></label>
		<input id="sellvat" type="numeric" name="selltax" value="<?php echo $vatprice; ?>" />
	</div>
	<div class="row">
		<label for="sell"><?php \pi18n("Sell price", PLUGIN_NAME); ?></label>
		<input type="hidden" id="realsell" name="realsell" <?php if ($product != NULL) echo 'value=' . $product->price_sell; ?> />
		<input id="sell" type="numeric" name="sell" value="<?php echo $price; ?>" />
	</div>
	<?php \Pasteque\form_input("edit", "Product", $product, "price_buy", "numeric"); ?>
	<div class="row">
		<label for="margin"><?php \pi18n("Margin", PLUGIN_NAME); ?></label>
		<input id="margin" type="numeric" disabled="true" />
	</div>
	<div class="row">
		<label for="image"><?php \pi18n("Image", PLUGIN_NAME); ?></label>
		<div style="display:inline-block">
			<input type="hidden" id="clearImage" name="clearImage" value="0" />
		<?php if ($product !== NULL && $product->image !== NULL) { ?>
			<img id="img" class="image-preview" src="?<?php echo \Pasteque\URL_ACTION_PARAM; ?>=img&w=product&id=<?php echo $product->id; ?>" />
			<a id="clear" href="" onClick="javascript:clearImage(); return false;"><?php \pi18n("Delete"); ?></a>
			<a style="display:none" id="restore" href="" onClick="javascript:restoreImage(); return false;"><?php \pi18n("Restore"); ?></a><br />
		<?php } ?>
			<input type="file" name="image" />
		</div>
	
	<div class="row actions">
		<?php \Pasteque\form_send(); ?>
	</div>
</form>

<script type="text/javascript">
	var tax_rates = new Array();
<?php foreach ($taxes as $tax) {
	echo "\ttax_rates['" . $tax->id . "'] = " . $tax->getCurrentTax()->rate . ",\n";
} ?>

	updateSellPrice = function() {
		var sellvat = jQuery("#sellvat").val();
		var rate = tax_rates[jQuery("#edit-tax_cat").val()];
		var sell = sellvat / (1 + rate);
		jQuery("#realsell").val(sell);
		jQuery("#sell").val(sell.toFixed(2));
		updateMargin();
	}
	updateSellVatPrice = function() {
		// Update sellvat price
		var sell = jQuery("#sell").val();
		var rate = tax_rates[jQuery("#edit-tax_cat").val()];
		var sellvat = sell * (1 + rate);
		// Round to 2 decimals and refresh sell price to avoid unrounded payments
		sellvat = sellvat.toFixed(2);
		jQuery("#sellvat").val(sellvat);
		updateSellPrice();
		updateMargin();
	}
	updateMargin = function() {
		var sell = jQuery("#realsell").val();
		var buy = jQuery("#edit-price_buy").val();
		var ratio = sell / buy - 1;
		var margin = (ratio * 100).toFixed(2) + "%";
		var rate = (sell / buy).toFixed(2);
		jQuery("#margin").val(margin + "\t\t" + rate);
	}
	updateMargin();

	jQuery("#sellvat").change(updateSellPrice);
	jQuery("#edit-tax_cat").change(updateSellPrice);
	jQuery("#sell").change(updateSellVatPrice);
	jQuery("#edit-price_buy").change(updateMargin);

	clearImage = function() {
		jQuery("#img").hide();
		jQuery("#clear").hide();
		jQuery("#restore").show();
		jQuery("#clearImage").val(1);
	}
	restoreImage = function() {
		jQuery("#img").show();
		jQuery("#clear").show();
		jQuery("#restore").hide();
		jQuery("#clearImage").val(0);
	}	
</script>
