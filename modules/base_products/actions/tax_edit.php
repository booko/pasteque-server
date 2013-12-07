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

$message = NULL;
$error = NULL;
// Check saves
if (isset($_POST['label'])) {
    if (isset($_POST['label']) && isset($_POST['rate'])) {
        $rate = $_POST['rate'];
        if (isset($_POST['id'])) {
            $cat = \Pasteque\TaxCat::__build($_POST['id'], $_POST['label']);
            \Pasteque\TaxesService::updateCat($cat);
            $tax = new \Pasteque\Tax($cat->id, $cat->label, time(), $rate);
            if (\Pasteque\TaxesService::createTax($tax)) {
                $message = \i18n("Changes saved");
            } else {
                $error = \i18n("Unable to save changes");
            }
        } else {
            $cat = new \Pasteque\TaxCat($_POST['label']);
            $id = \Pasteque\TaxesService::createCat($cat);
            $tax = new \Pasteque\Tax($id, $cat->label, time(), $rate);
            if (\Pasteque\TaxesService::createTax($tax)) {
                $message = \i18n("Changes saved");
            } else {
                $error = \i18n("Unable to save changes");
            }
        }
    }
}

$tax_cat = NULL;
$tax = NULL;
if (isset($_GET['id'])) {
    $tax_cat = \Pasteque\TaxesService::get($_GET['id']);
    $tax = $tax_cat->getCurrentTax();
}
?>
<h1><?php \pi18n("Edit tax", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<!-- Tax category edit -->
<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("edit", $tax_cat, "id"); ?>
	<?php \Pasteque\form_input("edit", "TaxCat", $tax_cat, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Tax", $tax, "rate", "float", array("required" => true)); ?>
	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($tax_cat !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'taxes'); ?>" method="post">
	<?php \Pasteque\form_delete("taxcat", $tax_cat->id); ?>
</form>
<?php } ?>