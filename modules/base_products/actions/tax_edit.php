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

// tax_edit action

namespace BaseProducts;

$message = null;
$error = null;
// Check saves
if (isset($_POST['id'])) {
    // Update tax category
    $taxCat = \Pasteque\TaxCat::__build($_POST['id'], $_POST['label']);
    // rebuild tax rates
    $taxValues = array();
    foreach ($_POST as $key => $value) {
        if (strpos($key, "label-") === 0) {
            if (substr($key, 6) != "new") {
                $taxValues[substr($key, 6)]['label'] = $value;
            }
        } else if (strpos($key, "rate-") === 0) {
            if (substr($key, 5) != "new") {
                $taxValues[substr($key, 5)]['rate'] = floatval($value);
            }
        } else if (strpos($key, "startDate-") === 0) {
            if (substr($key, 10) != "new") {
                $taxValues[substr($key, 10)]['startDate'] = \i18nRevDate($value);
            }
        }
    }
    foreach ($taxValues as $id => $data) {
        $tax = \Pasteque\Tax::__build($id, $taxCat->id, $data['label'],
                $data['startDate'], floatval($data['rate']));
        $taxCat->addTax($tax);
    }
    // new tax rate?
    if (isset($_POST['label-new']) && $_POST['label-new'] != "" && isset($_POST['rate-new']) && $_POST['rate-new'] != "") {
        if (!isset($_POST['startDate-new']) || $_POST['startDate-new'] == "") {
            $start = \time();
        } else {
            $start = \i18nRevDate($_POST['startDate-new']);
        }
        $tax = new \Pasteque\Tax($_POST['id'], $_POST['label-new'],
                $start, floatval($_POST['rate-new']));
        $taxCat->addTax($tax);
    }
    // Update
    $taxCatId = \Pasteque\TaxesService::updateCat($taxCat);
    if ($taxCatId === false) {
        $error = \i18n("Unable to save tax.", PLUGIN_NAME);
    }
    if ($error === null) {
        $message = \i18n("Tax saved", PLUGIN_NAME);
    }
} else if (isset($_POST['label'])) {
    // Create tax category
    if (!isset($_POST['new-startDate'])) {
        $start = \time();
    } else {
        $start = \i18nRevDate($_POST['new-startDate']);
    }
    $taxCat = new \Pasteque\TaxCat($_POST['label']);
    $tax = new \Pasteque\Tax(null, $_POST['label-new'],
            $start, floatval($_POST['rate-new']));
    $taxCat->addTax($tax);
    $taxCatId = \Pasteque\TaxesService::createCat($taxCat);
    if ($taxCatId === false) {
        $error = \i18n("Unable to save tax.", PLUGIN_NAME);
    } else {
        $message = \i18n("Tax saved", PLUGIN_NAME);
    }
}

$taxCat = null;
if (isset($_GET['id'])) {
    $taxCat = \Pasteque\TaxesService::get($_GET['id']);
}
?>
<h1><?php \pi18n("Edit tax", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<!-- Tax category edit -->
<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<fieldset>
		<legend><?php \pi18n("TaxCategory"); ?></legend>
		<?php \Pasteque\form_hidden("edit", $taxCat, "id"); ?>
		<?php \Pasteque\form_input("edit", "TaxCat", $taxCat, "label", "string", array("required" => true)); ?>
	</fieldset>
	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th><?php \pi18n("Tax.label"); ?></th>
				<th><?php \pi18n("Tax.rate"); ?></th>
				<th><?php \pi18n("Tax.startDate"); ?></th>
			</tr>
		</thead>
		<tbody id="list">
	<?php if ($taxCat !== null) { foreach ($taxCat->taxes as $tax) { ?>
		<tr>
			<td><?php \Pasteque\form_input($tax->id, "Tax", $tax, "label", "string", array("required" => true, "nolabel" => true, "nameid" => true)); ?></td>
			<td><?php \Pasteque\form_input($tax->id, "Tax", $tax, "rate", "float", array("required" => true, "nolabel" => true, "nameid" => true)); ?></td>
			<td><?php \Pasteque\form_input($tax->id, "Tax", $tax, "startDate", "date", array("required" => true, "nolabel" => true, "nameid" => true)); ?></td>
		</tr>
	<?php } }?>
		<tr>
			<td><?php \Pasteque\form_input("new", "Tax", null, "label", "string", array("nolabel" => true, "nameid" => true)); ?></td>
			<td><?php \Pasteque\form_input("new", "Tax", null, "rate", "float", array("nolabel" => true, "nameid" => true)); ?></td>
			<td><?php \Pasteque\form_input("new", "Tax", null, "startDate", "date", array("nolabel" => true, "nameid" => true)); ?></td>
		</tr>
		</tbody>
	</table>

	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
	
</form>
<?php if ($taxCat !== null) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'taxes'); ?>" method="post">
	<?php \Pasteque\form_delete("taxcat", $taxCat->id); ?>
</form>
<?php } ?>
