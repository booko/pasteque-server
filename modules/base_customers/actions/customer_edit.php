<?php
//    Pastèque Web back office, Users module
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

// category_edit action

namespace BaseCustomers;

$message = null;
$error = null;
$discounts = false;
$modules = \Pasteque\get_loaded_modules(\Pasteque\get_user_id());
if (in_array("customer_discountprofiles", $modules)) {
    $discounts = true;
}

if (isset($_POST['id']) && isset($_POST['dispName'])) {
    $visible = isset($_POST['visible']) ? 1 : 0;
    $taxCatId = NULL;
    if (isset($_POST['custTaxId']) && $_POST['custTaxId'] != "") {
        $taxCatId = $_POST['custTaxId'];
    }
    $currDebt = NULL;
    if (isset($_POST['currDebt']) && $_POST['currDebt'] != "") {
        $currDebt = $_POST['currDebt'];
    }
    $debtDate = NULL;
    if (isset($_POST['debtDate']) && $_POST['debtDate'] != "") {
        $debtDate = $_POST['debtDate'];
        $debtDate = \i18nRevDateTime($debtDate);
        $debtDate = \Pasteque\stdstrftime($debtDate);
    }
    $discountProfileId = null;
    if ($discounts && $_POST['discountProfileId'] !== "") {
        $discountProfileId = $_POST['discountProfileId'];
    }
    $cust = \Pasteque\Customer::__build($_POST['id'], $_POST['number'], $_POST['key'],
            $_POST['dispName'], $_POST['card'], $taxCatId, $discountProfileId,
            $_POST['prepaid'], $_POST['maxDebt'], $currDebt, $debtDate,
            $_POST['firstName'], $_POST['lastName'], $_POST['email'],
            $_POST['phone1'], $_POST['phone2'], $_POST['fax'], $_POST['addr1'],
            $_POST['addr2'], $_POST['zipCode'], $_POST['city'],
            $_POST['region'], $_POST['country'], $_POST['note'], $visible);
    if (\Pasteque\CustomersService::update($cust)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['dispName'])) {
    $visible = isset($_POST['visible']) ? 1 : 0;
    $taxCatId = null;
    if (isset($_POST['custTaxId']) && $_POST['custTaxId'] != "") {
        $taxCatId = $_POST['custTaxId'];
    }
    $discountProfileId = null;
    if ($discounts && $_POST['discountProfileId'] !== "") {
        $discountProfileId = $_POST['discountProfileId'];
    }
    $cust = new \Pasteque\Customer($_POST['number'], $_POST['key'],
            $_POST['dispName'], $_POST['card'], $taxCatId, $discountProfileId,
            $_POST['prepaid'], $_POST['maxDebt'], null, null,
            $_POST['firstName'], $_POST['lastName'], $_POST['email'],
            $_POST['phone1'], $_POST['phone2'], $_POST['fax'], $_POST['addr1'],
            $_POST['addr2'], $_POST['zipCode'], $_POST['city'],
            $_POST['region'], $_POST['country'], $_POST['note'], $visible);
    $id = \Pasteque\CustomersService::create($cust);
    if ($id !== false) {
        $message = \i18n("Customer saved. <a href=\"%s\">Go to the customer page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'customer_edit', array('id' => $id)));
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$cust = null;
$currDebt = "";
$str_debtDate = "";
if (isset($_GET['id'])) {
    $cust = \Pasteque\CustomersService::get($_GET['id']);
    $currDebt = $cust->currDebt;
    if ($cust->debtDate !== null) {
        $str_debtDate = \Pasteque\stdtimefstr($cust->debtDate);
        $str_debtDate = \i18nDatetime($str_debtDate);
    }
}
?>
<h1><?php \pi18n("Edit a customer", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<?php if ($cust !== null) { ?>
<p><a class="btn" href="<?php echo \Pasteque\get_report_url(PLUGIN_NAME, 'customers_diary', 'display'); ?>&id=<?php echo $cust->id; ?>"><?php \pi18n("Customer's diary", PLUGIN_NAME); ?></a></p>
<?php } ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("edit", $cust, "id"); ?>
    <fieldset>
	<legend><?php \pi18n("Keys", PLUGIN_NAME); ?></legend>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "number", "numeric", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "key", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "dispName", "string", array("required" => true)); ?>
	<div class="row">
		<label for="card"><?php \pi18n("Customer.card"); ?></label>
		<div style="display:inline-block; max-width:65%;">
			<img id="barcodeImg" src="" />
			<input id="barcode" type="text" readonly="true" name="card" <?php if ($cust != NULL) echo 'value="' . $cust->card . '"'; ?> />
			<a class="btn" href="" onClick="javascript:generateCard(); return false;"><?php \pi18n("Generate"); ?></a>
		</div>
	</div>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "visible", "boolean"); ?>
	</fieldset>
	<fieldset>
	<legend><?php \pi18n("Debt", PLUGIN_NAME); ?></legend>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "prepaid", "numeric"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "maxDebt", "numeric"); ?>
	<div class="row">
		<label for="currDebt"><?php \pi18n("Customer.currDebt"); ?></label>
		<input id="currDebt" name="currDebt" type="numeric" readonly="true" value="<?php echo $currDebt; ?>" />
	</div>
	<div class="row">
		<label for="date"><?php \pi18n("Customer.debtDate"); ?></label>
		<input id="date" name="debtDate" type="date" readonly="true" value="<?php echo $str_debtDate; ?>" />
	</div>
	</fieldset>
	<fieldset>
	<legend><?php \pi18n("Miscellaneous", PLUGIN_NAME); ?></legend>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "note", "text"); ?>
    <?php \Pasteque\form_input("edit", "Customer", $cust, "custTaxId", "pick", array("model" => "CustTaxCat", "nullable" => true)); ?>
    <?php if ($discounts) { \Pasteque\form_input("edit", "Customer", $cust, "discountProfileId", "pick", array("model" => "DiscountProfile", "nullable" => true)); } ?>
	</fieldset>
	<fieldset>
	<legend><?php \pi18n("Personnal data", PLUGIN_NAME); ?></legend>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "firstName", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "lastName", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "email", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "phone1", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "phone2", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "fax", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "addr1", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "addr2", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "zipCode", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "city", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "region", "string"); ?>
	<?php \Pasteque\form_input("edit", "Customer", $cust, "country", "string"); ?>
	</fieldset>

	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($cust !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'customers'); ?>" method="post">
	<?php \Pasteque\form_delete("customer", $cust->id); ?>
</form>
<?php } ?>

<script type="text/javascript">
	updateBarcode = function() {
		var barcode = jQuery("#barcode").val();
		var src = "?<?php echo \Pasteque\PT::URL_ACTION_PARAM; ?>=img&w=custcard&code=" + barcode;
		jQuery("#barcodeImg").attr("src", src);
	}
	updateBarcode();
	generateCard = function() {
		var code = new Array();
		for (var i = 0; i < 12; i++) {
			var num = Math.floor(Math.random() * 10);
			code.push(num);
		}
		var barcode = "c" + code.join("");
		jQuery("#barcode").val(barcode);
		updateBarcode();
	}
</script>
