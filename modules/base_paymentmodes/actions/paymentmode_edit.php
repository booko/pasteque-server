<?php
//    Pastèque Web back office, Payment modes module
//
//    Copyright (C) 2015 Scil (http://scil.coop)
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

// categories action

namespace BasePaymentModes;

$message = NULL;
$error = NULL;
$modeSrv = new \Pasteque\PaymentModesService();

if (isset($_POST['id']) && isset($_POST['code'])) {
    $active = isset($_POST['active']) && $_POST['active'];
    $system = isset($_POST['system']) && $_POST['system'];
//   static function __build($id, $code, $label, $backLabel, $flags, $hasImage,    $rules, $values, $active, $system, $dispOrder) {

    $paymentMode = \Pasteque\PaymentMode::__build($_POST['id'], $_POST['code'],
            $_POST['label'], $_POST['backLabel'], 0 /* flags */, 0 /* hasImage */,
	    null /* rules */, null /* values */, $active, $system, $_POST["dispOrder"]);
    if ($modeSrv->update($paymentMode)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['code'])) {
    $active = isset($_POST['active']) && $_POST['active'];
    $system = isset($_POST['system']) && $_POST['system'];
    
    $paymentMode = new \Pasteque\PaymentMode($_POST['code'], 
            $_POST['label'], $_POST['backLabel'], 0 /* flags */, 0 /* hasImage */,
	    null /* rules */, null /* values */, $active, $system, $_POST["dispOrder"]);

    $id = $modeSrv->create($paymentMode);
    if ($id !== FALSE) {
        $message = \i18n("Payment mode saved. <a href=\"%s\">Go to the payment modes page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'payment_modes', array('id' => $id)));
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$paymentMode = NULL;
if (isset($_GET['id'])) {
    $paymentMode = $modeSrv->get($_GET['id']);
}
else {
    $paymentMode = new \Pasteque\PaymentMode();
}

$possiblePaymentCodes = array(
    array("id" => 'cash',           "label" => \i18n('Cash')),
    array("id" => 'magcard',        "label" => \i18n('Credit Card')),
    array("id" => 'cheque',         "label" => \i18n('Cheque'))
    array("id" => 'prepaid',        "label" => \i18n('Prepaid')),
    array("id" => 'paperin',        "label" => \i18n('Paper in')),
    array("id" => 'credit_note',    "label" => \i18n('Credit note')),
    array("id" => 'internet',       "label" => \i18n('Internet')),
    array("id" => 'debt',           "label" => \i18n('Debt')),
    array("id" => 'free',           "label" => \i18n('Free')),
);

?>
<h1><?php \pi18n("Edit a payment mode", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post" enctype="multipart/form-data">
    <?php \Pasteque\form_hidden("edit", $paymentMode, "id"); ?>
    <?php \Pasteque\form_input("edit", "Payment mode", $paymentMode, "code", "pick", array("required" => true, "data" => $possiblePaymentCodes)); ?>
    <?php \Pasteque\form_input("edit", "Payment mode", $paymentMode, "label", "string", array("required" => true)); ?>
    <?php \Pasteque\form_input("edit", "Payment mode", $paymentMode, "backLabel", "string", array("required" => true)); ?>
    <?php \Pasteque\form_input("edit", "Payment mode", $paymentMode, "active", "boolean"); ?>
    <?php \Pasteque\form_input("edit", "Payment mode", $paymentMode, "system", "boolean"); ?>
    <?php \Pasteque\form_input("edit", "Payment mode", $paymentMode, "dispOrder", "numeric"); ?>

    <div class="row actions">
        <?php \Pasteque\form_save(); ?>
    </div>
</form>
<?php if ($paymentMode->id !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'paymentmodes'); ?>" method="post">
    <?php \Pasteque\form_delete("cat", $paymentMode->id); ?>
</form>
<?php } ?>
