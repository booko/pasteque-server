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

namespace BaseCustomers;

$message = NULL;
$error = NULL;
if (isset($_POST['id']) && isset($_POST['label'])) {
    $tax_cat_id = NULL;
    if ($_POST['tax_cat_id'] !== "") {
        $tax_cat_id = $_POST['tax_cat_id'];
    }
    $custTax = \Pasteque\CustTaxCat::__build($_POST['id'], $_POST['label'], $tax_cat_id);
    if (\Pasteque\CustTaxCatsService::update($custTax)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['label'])) {
    $tax_cat_id = NULL;
    if ($_POST['tax_cat_id'] !== "") {
        $tax_cat_id = $_POST['tax_cat_id'];
    }
    $custTax = new \Pasteque\CustTaxCat($_POST['label'], $tax_cat_id);
    $id = \Pasteque\CustTaxCatsService::create($custTax);
    if ($id !== FALSE) {
        $message = \i18n("Tax saved. <a href=\"%s\">Go to the tax page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'cust_tax_edit', array('id' => $id)));
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$custTax = NULL;
if (isset($_GET['id'])) {
    $custTax = \Pasteque\CustTaxCatsService::get($_GET['id']);
}
?>
<h1><?php \pi18n("Edit a customer tax", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<?php \Pasteque\form_hidden("edit", $custTax, "id"); ?>
	<?php \Pasteque\form_input("edit", "CustTaxCat", $custTax, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "CustTaxCat", $custTax, "tax_cat_id", "pick", array("model" => "Tax", "nullable" => true)); ?>
	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($custTax !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'cust_taxes'); ?>" method="post">
	<?php \Pasteque\form_delete("custtax", $custTax->id); ?>
</form>
<?php } ?>