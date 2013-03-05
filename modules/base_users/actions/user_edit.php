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

$message = NULL;
$error = NULL;
if (isset($_POST['id']) && isset($_POST['name'])) {
    $role = \Pasteque\RolesService::get($_POST['role']);
    $user = \Pasteque\User::__build($_POST['id'], $_POST['name'], "", $role);
    if (\Pasteque\UsersService::update($user)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['name'])) {
    $role = \Pasteque\RolesService::get($_POST['role']);
    $user = new \Pasteque\User($_POST['name'], NULL, $role);
    $id = \Pasteque\UsersService::create($user);
    if ($id !== FALSE) {
        $message = \i18n("User saved. <a href=\"%s\">Go to the user page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'user_edit', array('id' => $id)));
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$user = NULL;
if (isset($_GET['id'])) {
    $user = \Pasteque\UsersService::get($_GET['id']);
}
?>
<h1><?php \pi18n("Edit an user", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("edit", $user, "id"); ?>
	<?php \Pasteque\form_input("edit", "User", $user, "name", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "User", $user, "role", "pick", array("model" => "Role")); ?>
	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($user !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'users'); ?>" method="post">
	<?php \Pasteque\form_delete("user", $user->id); ?>
</form>
<?php } ?>

