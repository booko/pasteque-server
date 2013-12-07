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

namespace BaseUsers;

$srv = new \Pasteque\RolesService();
if (isset($_POST['delete-role'])) {
    $srv->delete($_POST['delete-role']);
}

$roles = $srv->getAll();
?>
<h1><?php \pi18n("Users", PLUGIN_NAME); ?></h1>

<p><a class="btn" href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'role_edit'); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/btn_add.png" /><?php \pi18n("Add a role", PLUGIN_NAME); ?></a></p>

<table cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th><?php \pi18n("Role.name"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($roles as $role) {
?>
	<tr>
		<td><?php echo $role->name; ?></td>
		<td class="edition">
			<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'role_edit', array('id' => $role->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("role", $role->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
}
?>
	</tbody>
</table>
