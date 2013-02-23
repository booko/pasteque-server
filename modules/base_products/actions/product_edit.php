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
        $scaled = isset($_POST['scaled']) ? 1 : 0;
        $visible = isset($_POST['visible']) ? 1 : 0;
        $prd = \Pasteque\Product::__build($_POST['id'], $_POST['reference'], $_POST['label'], $_POST['realsell'], $cat, $taxCat,
                $visible, $scaled, $_POST['price_buy'], NULL, $_POST['barcode'], $img);
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
        $scaled = isset($_POST['scaled']) ? 1 : 0;
        $visible = isset($_POST['visible']) ? 1 : 0;
        $prd = new \Pasteque\Product($_POST['reference'], $_POST['label'], $_POST['realsell'], $cat, $taxCat,
                $visible, $scaled, $_POST['price_buy'], NULL, $_POST['barcode'], $img);
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
	<fieldset>
	<legend><?php \pi18n("Display", PLUGIN_NAME); ?></legend>
	<?php \Pasteque\form_input("edit", "Product", $product, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "category", "pick", array("model" => "Category")); ?>
	<div class="row">
		<label for="image"><?php \pi18n("Image"); ?></label>
		<div style="display:inline-block">
			<input type="hidden" id="clearImage" name="clearImage" value="0" />
		<?php if ($product !== NULL && $product->image !== NULL) { ?>
			<img id="img" class="image-preview" src="?<?php echo \Pasteque\URL_ACTION_PARAM; ?>=img&w=product&id=<?php echo $product->id; ?>" />
			<a id="clear" href="" onClick="javascript:clearImage(); return false;"><?php \pi18n("Delete"); ?></a>
			<a style="display:none" id="restore" href="" onClick="javascript:restoreImage(); return false;"><?php \pi18n("Restore"); ?></a><br />
		<?php } ?>
			<input type="file" name="image" />
		</div>
	</div>
	<?php \Pasteque\form_input("edit", "Product", $product, "visible", "boolean"); ?>
	</fieldset>
	<fieldset>
	<legend><?php \pi18n("Price", PLUGIN_NAME); ?></legend>
	<?php \Pasteque\form_input("edit", "Product", $product, "scaled", "boolean"); ?>
	<?php \Pasteque\form_input("edit", "Product", $product, "tax_cat", "pick", array("model" => "TaxCategory")); ?>
	<div class="row">
		<label for="sellvat"><?php \pi18n("Sell price + taxes", PLUGIN_NAME); ?></label>
		<input id="sellvat" type="numeric" name="selltax" value="<?php echo $vatprice; ?>" />
	</div>
	<div class="row">
		<label for="sell"><?php \pi18n("Product.price_sell"); ?></label>
		<input type="hidden" id="realsell" name="realsell" <?php if ($product != NULL) echo 'value="' . $product->price_sell. '"'; ?> />
		<input id="sell" type="numeric" name="sell" value="<?php echo $price; ?>" />
	</div>
	<?php \Pasteque\form_input("edit", "Product", $product, "price_buy", "numeric"); ?>
	<div class="row">
		<label for="margin"><?php \pi18n("Margin", PLUGIN_NAME); ?></label>
		<input id="margin" type="numeric" disabled="true" />
	</div>
	</fieldset>
	<fieldset>
	<legend><?php \pi18n("Referencing", PLUGIN_NAME); ?></legend>
	<?php \Pasteque\form_input("edit", "Product", $product, "reference", "string", array("required" => true)); ?>
	<div class="row">
		<label for="barcode"><?php \pi18n("Product.barcode"); ?></label>
		<div style="display:inline-block; max-width:65%;">
			<img id="barcodeImg" src="" />
			<input id="barcode" type="text" name="barcode" <?php if ($product != NULL) echo 'value="' . $product->barcode . '"'; ?> />
			<a class="btn" href="" onClick="javascript:generateBarcode(); return false;"><?php \pi18n("Generate"); ?></a>
		</div>
	</div>
	</fieldset>
	
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

	updateBarcode = function() {
		var barcode = jQuery("#barcode").val();
		var src = "?<?php echo \Pasteque\URL_ACTION_PARAM; ?>=img&w=barcode&code=" + barcode;
		jQuery("#barcodeImg").attr("src", src);
	}
	updateBarcode();

	jQuery("#barcode").change(updateBarcode);

	generateBarcode = function() {
		var first = Math.floor(Math.random() * 9) + 1;
		var code = new Array();
		code.push(first);
		for (var i = 0; i < 11; i++) {
			var num = Math.floor(Math.random() * 10);
			code.push(num);
		}
		var checksum = 0;
		for (var i = 0; i < code.length; i++) {
			var weight = 1;
			if (i % 2 == 1) {
				weight = 3;
			}
			checksum = checksum + weight * code[i];
		}
		checksum = checksum % 10;
		if (checksum != 0) {
			checksum = 10 - checksum;
		}
		code.push(checksum);
		var barcode = code.join("");
		jQuery("#barcode").val(barcode);
		updateBarcode();
	}
</script>
