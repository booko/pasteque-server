<?php
//    Pastèque Web back office, Currencies module
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

namespace BaseCurrencies;

$message = NULL;
$error = NULL;
$currSrv = new \Pasteque\CurrenciesService();
if (isset($_POST['id']) && isset($_POST['label'])) {
    $curr = \Pasteque\Currency::__build($_POST['id'], $_POST['label'],
            $_POST['symbol'], $_POST['decimalSeparator'],
            $_POST['thousandsSeparator'], $_POST['format'], $_POST['rate'],
            $_POST['isMain'], $_POST['isActive']);
    if ($currSrv->update($curr)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['label'])) {
    $curr = new \Pasteque\Currency($_POST['label'], $_POST['symbol'],
            $_POST['decimalSeparator'], $_POST['thousandsSeparator'],
            $_POST['format'], $_POST['rate'], $_POST['isMain'],
            $_POST['isActive']);
    $id = $currSrv->create($curr);
    if ($id !== FALSE) {
        $message = \i18n("Currency saved. <a href=\"%s\">Go to the currecy page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'currency_edit', array('id' => $id)));
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$currency = NULL;
if (isset($_GET['id'])) {
    $currency = $currSrv->get($_GET['id']);
}
?>
<h1><?php \pi18n("Edit a currency", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post" enctype="multipart/form-data">
    <?php \Pasteque\form_hidden("edit", $currency, "id"); ?>
	<?php \Pasteque\form_input("edit", "Currency", $currency, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Currency", $currency, "rate", "numeric", array()); ?>
	<?php \Pasteque\form_input("edit", "Currency", $currency, "symbol", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Currency", $currency, "decimalSeparator", "string"); ?>
	<?php \Pasteque\form_input("edit", "Currency", $currency, "thousandsSeparator", "string"); ?>
	<?php \Pasteque\form_input("edit", "Currency", $currency, "format", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Currency", $currency, "isMain", "boolean"); ?>
	<?php \Pasteque\form_input("edit", "Currency", $currency, "isActive", "boolean"); ?>

	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($currency !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'currencies'); ?>" method="post">
    <?php \Pasteque\form_delete("cat", $currency->id); ?>
</form>
<?php } ?>
