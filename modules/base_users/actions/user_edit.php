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

// category_edit action

namespace BaseUsers;

if (isset($_POST['id'])) {
    $edit = \Pasteque\User::__form($_POST);
    if ($edit !== NULL) {
        \Pasteque\UsersService::update($edit);
    }
} else if (isset($_POST['name'])) {
    $new = \Pasteque\User::__form($_POST);
    if ($new !== NULL) {
        \Pasteque\UsersService::create($new);
    }
}

$user = NULL;
if (isset($_GET['id'])) {
    $user = \Pasteque\UsersService::get($_GET['id']);
}
$permissions = \Pasteque\UsersService::getPermissions();
?>
<h1><?php \pi18n("Edit an user", PLUGIN_NAME); ?></h1>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("edit", $user, "id"); ?>
	<?php \Pasteque\form_input("edit", "User", $user, "name", "string", array("required" => true)); ?>
	<?php \Pasteque\form_send(); ?>
    <h2><?php \pi18n("Permissions", PLUGIN_NAME); ?></h2>
    <?php foreach ($permissions as $perm) { ?>
    <?php $checked = (isset($user) && $user->hasPermission($perm)) ? ' checked="true"' : ""; ?>
    <label for="perm-<?php echo $perm; ?>"><?php echo $perm; ?></label>
    <input id="perm-<?php echo $perm; ?>" type="checkbox" <?php echo $checked; ?> name="permissions[]" value="<?php echo $perm; ?>">
    <?php } ?>
</form>
<?php if ($user !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'users'); ?>" method="post">
	<?php \Pasteque\form_delete("user", $user->id); ?>
</form>
<?php } ?>

