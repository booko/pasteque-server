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

namespace BaseCashRegisters;

$message = null;
$error = null;
$srv = new \Pasteque\CashRegistersService();

if (isset($_GET['delete-cashreg'])) {
    if ($srv->delete($_GET['delete-cashreg'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$cashRegs = $srv->getAll();
?>
<h1><?php \pi18n("Cash registers", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<?php \Pasteque\tpl_btn('btn', \Pasteque\get_module_url_action(PLUGIN_NAME, "cashregister_edit"),
        \i18n('New cash register', PLUGIN_NAME), 'img/btn_add.png');?>

<p><?php \pi18n("%d cash registers", PLUGIN_NAME, count($cashRegs)); ?></p>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php \pi18n("CashRegister.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($cashRegs as $cashReg) { ?>
		<tr>
			<td><?php echo $cashReg->label; ?></td>
			<td class="edition">
                    <?php \Pasteque\tpl_btn('btn-edition', \Pasteque\get_module_url_action(
                            PLUGIN_NAME, 'cashregister_edit', array("id" => $cashReg->id)), "",
                            'img/edit.png', \i18n('Edit'), \i18n('Edit'));
                    ?>
                    <?php \Pasteque\tpl_btn('btn-delete', \Pasteque\get_current_url() . "&delete-cashreg=" . $cashReg->id, "",
                            'img/delete.png', \i18n('Delete'), \i18n('Delete'), true);
                    ?>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
