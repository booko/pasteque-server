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

namespace ProductTariffAreas;

$message = null;
$error = null;
$srv = new \Pasteque\TariffAreasService();
if (isset($_POST['delete-area'])) {
    if ($srv->delete($_POST['delete-area'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$areas = $srv->getAll();
?>
<h1><?php \pi18n("Tariff areas", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<?php \Pasteque\tpl_btn('btn', \Pasteque\get_module_url_action(PLUGIN_NAME, "area_edit"),
        \i18n('Add an area', PLUGIN_NAME), 'img/btn_add.png');?>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php \pi18n("TariffArea.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
$par = false;
foreach ($areas as $area) {
$par = !$par;
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
		<td><?php echo $area->label; ?></td>
		<td class="edition">
            <?php \Pasteque\tpl_btn("edition", \Pasteque\get_module_url_action(PLUGIN_NAME,
                    'area_edit', array("id" => $area->id)), "",
                    'img/edit.png', \i18n('Edit'), \i18n('Edit'));
            ?>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("area", $area->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
}
?>
	</tbody>
</table>
<?php
if (count($areas) == 0) {
?>
<div class="alert"><?php \pi18n("No area found", PLUGIN_NAME); ?></div>
<?php
}
?>
