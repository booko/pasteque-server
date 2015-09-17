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
if (isset($_POST['toggle-paymentmode'])) {
    if ($modeSrv->toggle($_POST['toggle-paymentmode'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$paymentModes = $modeSrv->getAll();
?>
<h1><?php \pi18n("Payment modes", PLUGIN_NAME); ?></h1>

<br />

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php \pi18n("PaymentMode.code"); ?></th>
			<th><?php \pi18n("PaymentMode.label"); ?></th>
			<th><?php \pi18n("PaymentMode.backLabel"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($paymentModes as $paymentMode) {
    if ($paymentMode->system) {
        continue;
    }
?>
	<tr>
		<td><?php echo $paymentMode->code; ?></td>
		<td><?php echo $paymentMode->label; ?></td>
		<td><?php echo $paymentMode->backLabel; ?></td>
		<td class="edition">
                    <form action="<?php echo \Pasteque\get_current_url(); ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="toggle-paymentmode" value="<?php echo $paymentMode->id; ?>" />
<?php
    $action = \i18n("Enable");
    if ($paymentMode->active === true)
        $action = \i18n("Disable");
?>
                        <input type="submit" value="<?php echo $action; ?>" />
                    </form>
                </td>
	</tr>
<?php
}
?>
	</tbody>
</table>

