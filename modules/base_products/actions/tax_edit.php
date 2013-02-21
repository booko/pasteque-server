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

// Check saves
if (isset($_POST['name'])) {
    // Tax cat
    $cat = \Pasteque\TaxCat::__form($_POST);
    if (isset($_POST['id'])) {
        \Pasteque\TaxesService::updateCat($cat);
    } else {
        \Pasteque\TaxesService::createCat($cat);
    }
} else if (isset($_POST['rate'])) {
    // Tax rate
    $rate = \Pasteque\Tax::__form($_POST);
    if (isset($_POST['id'])) {
        \Pasteque\TaxesService::updateTax($rate);
    } else {
        \Pasteque\TaxesService::createTax($rate);
    }
} else if (isset($_POST['delete-tax'])) {
    \Pasteque\TaxesService::deleteTax($_POST['delete-tax']);
} else if (isset($_POST['delete-taxcat'])) {
}

$tax_cat = NULL;
if (isset($_GET['id'])) {
    $tax_cat = \Pasteque\TaxesService::get($_GET['id']);
}
?>
<h1><?php \pi18n("Edit tax", PLUGIN_NAME); ?></h1>

<!-- Tax category edit -->
<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("edit", $tax_cat, "id"); ?>
	<?php \Pasteque\form_input("edit", "TaxCat", $tax_cat, "name", "string", array("required" => true)); ?>
	<?php \Pasteque\form_send(); ?>
</form>
<?php if ($tax_cat !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'taxes'); ?>" method="post">
	<?php \Pasteque\form_delete("taxcat", $tax_cat->id); ?>
</form>
<?php } ?>

<?php if ($tax_cat->taxes !== NULL) { ?>
<!-- Tax rates -->
<h2><?php \pi18n("Rates", PLUGIN_NAME); ?></h2>
<?php foreach ($tax_cat->taxes as $tax) { ?>
<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("rate$tax->id", $tax, "id"); ?>
    <?php \Pasteque\form_hidden("rate$tax->id", $tax, "tax_cat_id"); ?>
    <?php \Pasteque\form_input("rate$tax->id", "Tax", $tax, "rate", "float", array("required" => true, "step" => 0.001)); ?>
    <?php \Pasteque\form_input("rate$tax->id", "Tax", $tax, "start_date", "date", array("required" => true)); ?>
    <?php \Pasteque\form_send(); ?>
</form>
<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<?php \Pasteque\form_delete("tax", $tax->id); ?>
</form>
<?php } ?>
<?php } ?>

<!-- New rate -->
<?php if ($tax_cat !== NULL) { ?>
<h2><?php \pi18n("New tax rate", PLUGIN_NAME); ?></h2>
<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_value_hidden("new_rate", "tax_cat_id", $tax_cat->id); ?>
	<?php \Pasteque\form_input("new_rate", "Tax", NULL, "rate", "float", array("required" => true)); ?>
	<?php \Pasteque\form_input("new_rate", "Tax", NULL, "start_date", "date", array("required" => true)); ?>
	<?php \Pasteque\form_send(); ?>
</form>
<?php } ?>
