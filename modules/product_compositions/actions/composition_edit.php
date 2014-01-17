<?php
namespace ProductCompositions;

$error = null;
$message = null;
$modules = \Pasteque\get_loaded_modules(\Pasteque\get_user_id());
$discounts = false;
if (in_array("product_discounts", $modules)) {
    $discounts = true;
}

function parseSubgroups($data) {
    $jsSubgroups = json_decode($data);
    $subgroups = array();
    foreach ($jsSubgroups as $jsSubgroup) {
        $subgroup = new \Pasteque\Subgroup(null, $jsSubgroup->label,
            $jsSubgroup->dispOrder, false);
        foreach ($jsSubgroup->prodIds as $prdId) {
            $subgroupProd = new \Pasteque\SubgroupProduct($prdId, null);
            $subgroup->addProduct($subgroupProd);
        }
        $subgroups[] = $subgroup;
    }
    return $subgroups;
}

if (isset($_POST['id'])) {
    // Update composition
    $catId = \Pasteque\CompositionsService::CAT_ID;
    $dispOrder = $_POST['dispOrder'] == "" ? NULL : $_POST['dispOrder'];
    $taxCatId = $_POST['taxCatId'];
    if ($_FILES['image']['tmp_name'] !== "") {
        $img = file_get_contents($_FILES['image']['tmp_name']);
    } else if ($_POST['clearImage']) {
        $img = NULL;
    } else {
        $img = "";
    }
    $scaled = isset($_POST['scaled']) ? 1 : 0;
    $visible = isset($_POST['visible']) ? 1 : 0;
    $discountEnabled = FALSE;
    $discountRate = 0.0;
    if (isset($_POST['discountRate'])) {
        $discountEnabled = isset($_POST['discountEnabled']) ? 1 : 0;
        $discountRate = $_POST['discountRate'];
    }
    $cmp = \Pasteque\Composition::__build($_POST['id'], $_POST['reference'],
            $_POST['label'], $_POST['realsell'], $catId, $dispOrder,
            $taxCatId, $visible, $scaled, $_POST['priceBuy'], null,
            $_POST['barcode'], $img != null,
            $discountEnabled, $discountRate);
    $cmp->groups = parseSubgroups($_POST['subgroupData']);
    if (\Pasteque\CompositionsService::update($cmp, $img, null)) {
        $message = \i18n("Changes saved", PLUGIN_NAME);
    } else {
        $error = \i18n("Unable to save changes", PLUGIN_NAME);
    }
} else if (isset($_POST['reference'])) {
    // Create composition
    $catId = \Pasteque\CompositionsService::CAT_ID;
    $dispOrder = $_POST['dispOrder'] == "" ? NULL : $_POST['dispOrder'];
    $taxCatId = $_POST['taxCatId'];
    if ($_FILES['image']['tmp_name'] !== "") {
        $img = file_get_contents($_FILES['image']['tmp_name']);
    } else {
        $img = NULL;
    }
    $scaled = isset($_POST['scaled']) ? 1 : 0;
    $visible = isset($_POST['visible']) ? 1 : 0;
    $discountEnabled = FALSE;
    $discountRate = 0.0;
    if (isset($_POST['discountRate'])) {
        $discountEnabled = isset($_POST['discountEnabled']) ? 1 : 0;
        $discountRate = $_POST['discountRate'];
    }
    $cmp = new \Pasteque\Product($_POST['reference'], $_POST['label'],
            $_POST['realsell'], $catId, $dispOrder, $taxCatId,
            $visible, $scaled, $_POST['priceBuy'], null, $_POST['barcode'],
            $img !== null, $discountEnabled, $discountRate);
    $cmp->groups = parseSubgroups($_POST['subgroupData']);
    if (\Pasteque\CompositionsService::create($cmp, $img, null)) {
        $message = \i18n("Changes saved", PLUGIN_NAME);
    } else {
        $error = \i18n("Unable to save changes", PLUGIN_NAME);
    }
}

if (isset($_GET['productId'])) {
    $composition  = \Pasteque\CompositionsService::get($_GET['productId']);
    $taxCat = \Pasteque\TaxesService::get($composition->taxCatId);
    $tax = $taxCat->getCurrentTax();
    $vatprice = $composition->priceSell * (1 + $tax->rate);
    $price = sprintf("%.2f", $composition->priceSell);
} else {
    $vatprice = "";
    $price = "";
    $composition = NULL;
}

$categories = \Pasteque\CategoriesService::getAll();
$products = \Pasteque\ProductsService::getAll(true);
$taxes = \Pasteque\TaxesService::getAll();

?>

<h1><?php \pi18n('Composition edit',PLUGIN_NAME)?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<?php if (isset($composition)) { ?>
    <form method='post' action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'composition');?>">
        <input type="hidden" value="<?php echo $composition->id?>" name="delete-comp">
        <input type='submit' class='btn-delete' value='<?php \pi18n('Delete this composition',PLUGIN_NAME);?>'/>
    </form>
<?php } ?>

<form class="edit" id="data-compo" method="post" onsubmit="return submitData();" action="<?php echo \Pasteque\get_current_url();?>" enctype="multipart/form-data">
<div>
	<div id="composition" class="row">
	<fieldset>
        <legend>Composition</legend>
        <?php \Pasteque\form_hidden("edit", $composition, "id"); ?>
        <fieldset>
        <legend><?php \pi18n("Display", PLUGIN_NAME); ?></legend>
        <?php \Pasteque\form_input("edit", "Product", $composition, "label", "string", array("required" => true)); ?>
		<div class="row">
			<label for="image"><?php \pi18n("Image"); ?></label>
			<div style="display:inline-block">
				<input type="hidden" id="clearImage" name="clearImage" value="0" />
				<?php if ($composition !== null && $composition->hasImage === true) { ?>
				<img id="img" class="image-preview" src="?<?php echo \Pasteque\PT::URL_ACTION_PARAM; ?>=img&w=product&id=<?php echo $product->id; ?>" />
				<a class="btn" id="clear" href="" onClick="javascript:clearImage(); return false;"><?php \pi18n("Delete"); ?></a>
				<a class="btn" style="display:none" id="restore" href="" onClick="javascript:restoreImage(); return false;"><?php \pi18n("Restore"); ?></a><br />
				<?php } ?>
				<input id="image" type="file" name="image" />
			</div>
		</div>
	<?php \Pasteque\form_input("edit", "Product", $composition, "visible", "boolean"); ?>
        <?php \Pasteque\form_input("edit", "Product", $composition, "dispOrder", "numeric"); ?>
        </fieldset>
        <fieldset>
        <legend><?php \pi18n("Price", PLUGIN_NAME); ?></legend>
        <?php \Pasteque\form_input("edit", "Product", $composition, "taxCatId", "pick", array("model" => "TaxCategory")); ?>
        <div class="row">
            <label for="sellvat"><?php \pi18n("Sell price + taxes", PLUGIN_NAME); ?></label>
            <input id="sellvat" type="numeric" name="selltax" value="<?php echo $vatprice; ?>" />
        </div>
        <div class="row">
            <label for="sell"><?php \pi18n("Product.priceSell"); ?></label>
            <input type="hidden" id="realsell" name="realsell" <?php if ($composition != NULL) echo 'value="' . $composition->priceSell. '"'; ?> />
            <input id="sell" type="numeric" name="sell" value="<?php echo $price; ?>" />
        </div>
        <?php \Pasteque\form_input("edit", "Product", $composition, "priceBuy", "numeric"); ?>
        <div class="row">
            <label for="margin"><?php \pi18n("Margin", PLUGIN_NAME); ?></label>
            <input id="margin" type="numeric" disabled="true" />
        </div>
        <?php if ($discounts) {
            \Pasteque\form_input("edit", "Product", $composition, "discountEnabled", "boolean", array("default" => FALSE));
            \Pasteque\form_input("edit", "Product", $composition, "discountRate", "numeric");
            } ?>
        </fieldset>
        <fieldset>
            <legend><?php \pi18n("Referencing", PLUGIN_NAME); ?></legend>
            <?php \Pasteque\form_input("edit", "Product", $composition, "reference", "string", array("required" => true)); ?>
            <div class="row">
                <label for="barcode"><?php \pi18n("Product.barcode"); ?></label>
                <div style="display:inline-block; max-width:65%;">
                    <img id="barcodeImg" src="" />
                    <input id="barcode" type="text" name="barcode" <?php if ($composition != NULL) echo 'value="' . $composition->barcode . '"'; ?> />
                    <a class="btn" href="" onClick="javascript:generateBarcode(); return false;"><?php \pi18n("Generate"); ?></a>
                </div>
            </div>
        </fieldset>
    </fieldset>


    </div>
    <div id="subGroup">
    <fieldset>
    <legend><?php \pi18n('SubGroups',PLUGIN_NAME); ?></legend>
        <div class="row">
        	<label for="listSubGr"><?php \pi18n("SubGroups", PLUGIN_NAME); ?></label>
            <select id="listSubGr" onchange="showSubgroup()"></select>
        </div class="row">
        <div class="row">
            <label for="edit-sgName"><?php \pi18n('Subgroup.label'); ?></label>
            <input type="text" id="edit-sgName" onchange="javascript:editSubgroup();"/>
        </div>
        <div class="row">
            <label for="edit-sgOrder"><?php \pi18n('Subgroup.dispOrder'); ?></label>
            <input id="edit-sgOrder" type="numeric" name="dispOrder" onchange="javascript:editSubgroup();">
        </div>
        <div class="row actions">
            <?php \Pasteque\tpl_js_btn("btn", "newSubgroup()", \i18n("Add subgroup", PLUGIN_NAME));?>
            <?php \Pasteque\tpl_js_btn("btn-delete", "delSubgroup()", \i18n("Delete subgroup", PLUGIN_NAME));?>
        </div>


        <div>
            <div id="product-sub-container" class="product-container"></div>
        </div>
    </fieldset>
    </div>
    <!-- to change -->
    <div id="product" class="row">
        <fieldSet>
            <legend><?php \pi18n('Product',PLUGIN_NAME); ?></legend>
            <div id="catalog-picker"></div>
            <div class="row" id="btnAddAllPrd">
                <input type="button" onclick="javascript:addAllPrd()" value="<?php pi18n('Add all products of the category',PLUGIN_NAME)?>">
            </div>
        </fieldSet>
    </div>
</div>
<input type="hidden" name="subgroupData" id="subgroupData" />
        <?php \Pasteque\form_save();?>
</form>

<?php \Pasteque\init_catalog("catalog", "catalog-picker", "productPicked",
        $categories, $products); ?>

<script src="<?php echo \Pasteque\get_module_action(PLUGIN_NAME, "control.js")?>" type="text/javascript"></script>

<script type="text/javascript">

	var tax_rates = new Array();
<?php foreach ($taxes as $tax) {
	echo "\ttax_rates['" . $tax->id . "'] = " . $tax->getCurrentTax()->rate . ",\n";
} ?>


updateSellPrice = function() {
		var sellvat = jQuery("#sellvat").val();
		var rate = tax_rates[jQuery("#edit-taxCatId").val()];
		var sell = sellvat / (1 + rate);
		jQuery("#realsell").val(sell);
		jQuery("#sell").val(sell.toFixed(2));
		updateMargin();
	}
updateSellVatPrice = function() {
		// Update sellvat price
		var sell = jQuery("#sell").val();
		var rate = tax_rates[jQuery("#edit-taxCatId").val()];
		var sellvat = sell * (1 + rate);
		// Round to 2 decimals and refresh sell price to avoid unrounded payments
		sellvat = sellvat.toFixed(2);
		jQuery("#sellvat").val(sellvat);
		updateSellPrice();
		updateMargin();
	}
updateMargin = function() {
		var sell = jQuery("#realsell").val();
		var buy = jQuery("#edit-priceBuy").val();
		var ratio = sell / buy - 1;
		var margin = (ratio * 100).toFixed(2) + "%";
		var rate = (sell / buy).toFixed(2);
		jQuery("#margin").val(margin + "\t\t" + rate);
	}
	updateMargin();

    /**Replace ',' by '.' and call function 'fonction'
     * @param id the id of HTML input element
     * @param fonction the function after replace*/
    function changeVal(id, fonction) {
        var val = $("#" + id).val().replace(",", ".");
        jQuery(id).val(val);
        fonction();
    }

    jQuery("#sellvat").change(function() {changeVal(this.id, updateSellPrice)});

    jQuery("#edit-taxCatId").change(function() {changeVal(this.id, updateSellPrice)});

    jQuery("#sell").change(function() {changeVal(this.id, updateSellVatPrice);});

    jQuery("#edit-priceBuy").change(function() {changeVal(this.id, updateMargin);});

    jQuery("#edit-discountRate").change(function() {changeVal(this.id, updateMargin);});

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
		var src = "?<?php echo \Pasteque\PT::URL_ACTION_PARAM; ?>=img&w=barcode&code=" + barcode;
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

    /** Add all product contain in the category */
    addAllPrd = function() {
        var prdCat = catalog.productsByCategory[catalog.currentCategoryId];
            for (var i = 0; i < prdCat.length; i++) {
                productPicked(prdCat[i]);
            }
    }

</script>

<script type="text/javascript">
<?php
foreach ($products as $product) {
    echo("registerProduct(\"" . $product->id . "\", \"" . $product->label . "\");\n");
}
if ($composition !== null) {
    foreach($composition->groups as $group) {
        echo "var id = addSubgroup(\"" . $group->label . "\", " . $group->dispOrder . ");\n";
        foreach($group->subgroupProds as $prod) {
            echo "addProduct(id, \"" . $prod->productId . "\");";
        }
    }
    echo("showSubgroup();\n");
} else {
    echo("addSubgroup(\"\", \"\");\n");
    echo("showSubgroup();\n");
}
?>
</script>
