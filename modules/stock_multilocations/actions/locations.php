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

namespace StockMultilocations;

$message = null;
$error = null;
$srv = new \Pasteque\LocationsService();

if (isset($_POST['delete-location'])) {
    if ($srv->delete($_POST['delete-location'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to delete location. A location cannot be deleted when stock is assigned to it.", PLUGIN_NAME);
    }
}

$locations = $srv->getAll();
?>
<h1><?php \pi18n("Locations", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<?php \Pasteque\tpl_btn('btn', \Pasteque\get_module_url_action(PLUGIN_NAME, "location_edit"),
        \i18n('New location', PLUGIN_NAME), 'img/btn_add.png');?>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php \pi18n("Location.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($locations as $location) { ?>
		<tr>
			<td><?php echo $location->label; ?></td>
			<td class="edition">
				<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'location_edit', array('id' => $location->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
