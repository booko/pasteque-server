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

if (isset($_POST['delete-user'])) {
    \Pasteque\UsersService::delete($_POST['delete-user']);
}

$users = \Pasteque\UsersService::getAll();
?>
<h1><?php \pi18n("Users", PLUGIN_NAME); ?></h1>

<p><a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'user_edit'); ?>"><?php \pi18n("Add an user", PLUGIN_NAME); ?></a></p>

<p><?php \pi18n("%d users", PLUGIN_NAME, count($users)); ?></p>

<table>
	<thead>
		<tr>
			<th><?php \pi18n("User.name"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($users as $user) {
?>
	<tr>
		<td><?php echo $user->name; ?></td>
		<td class="edition">
			<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'user_edit', array('id' => $user->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("user", $user->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
}
?>
	</tbody>
</table>
