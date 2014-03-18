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

$ALL_PERMS = array(
"fr.pasteque.pos.sales.JPanelTicketSales",
"fr.pasteque.pos.sales.JPanelTicketEdits",
"fr.pasteque.pos.customers.CustomersPayment",
"fr.pasteque.pos.panels.JPanelPayments",
"fr.pasteque.pos.panels.JPanelCloseMoney",
"sales.EditLines",
"sales.EditTicket",
"sales.RefundTicket",
"sales.PrintTicket",
"sales.Total",
"sales.ChangeTaxOptions",
"payment.cash",
"payment.cheque",
"payment.paper",
"payment.magcard",
"payment.free",
"payment.debt",
"refund.cash",
"refund.cheque",
"refund.paper",
"refund.magcard",
"Menu.ChangePassword",
"Menu.BackOffice",
"fr.pasteque.pos.panels.JPanelPrinter",
"fr.pasteque.pos.config.JPanelConfiguration",
"button.print",
"button.opendrawer",
"button.openmoney",
);

$message = null;
$error = null;
$srv = new \Pasteque\RolesService();
if (isset($_POST['id'])) {
    if (isset($_POST['name'])) {
        $permissions = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<permissions>\n";
        if (isset($_POST['permissions'])) {
            foreach($_POST['permissions'] as $perm) {
                $permissions .= "    <class name=\"" . $perm . "\"/>\n";
            }
        }
        $permissions .= "</permissions>";
        $role = \Pasteque\Role::__build($_POST['id'], $_POST['name'], $permissions);
        if ($srv->update($role)) {
            $message = \i18n("Changes saved");
        } else {
            $error = \i18n("Unable to save changes");
        }
    }
} else if (isset($_POST['name'])) {
    if (isset($_POST['name'])) {
        $permissions = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<permissions>\n";
        if (isset($_POST['permissions'])) {
            foreach($_POST['permissions'] as $perm) {
                $permissions .= "    <class name=\"" . $perm . "\"/>\n";
            }
        }
        $permissions .= "</permissions>";
        $role = new \Pasteque\Role($_POST['name'], $permissions);
        $id = $srv->create($role);
        if ($id !== FALSE) {
            $message = \i18n("Role saved. <a href=\"%s\">Go to the role page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'role_edit', array('id' => $id)));
        } else {
            $error = \i18n("Unable to save changes");
        }
    }
}

$role = null;
if (isset($_GET['id'])) {
    $role = $srv->get($_GET['id']);
}
?>
<h1><?php \pi18n("Edit a role", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("edit", $role, "id"); ?>
	<?php \Pasteque\form_input("edit", "Role", $role, "name", "string", array("required" => true)); ?>
	<h2><?php \pi18n("Permissions", PLUGIN_NAME); ?></h2>
    <?php foreach ($ALL_PERMS as $perm) { ?>
    <?php $checked = (isset($role) && $role->hasPermission($perm)) ? ' checked="true"' : ""; ?>
    <div class="row">
    	<input id="perm-<?php echo \Pasteque\esc_attr($perm); ?>" type="checkbox" <?php echo $checked; ?> name="permissions[]" value="<?php echo \Pasteque\esc_attr($perm); ?>">
		<label for="perm-<?php echo \Pasteque\esc_attr($perm); ?>"><?php echo \pi18n($perm, PLUGIN_NAME); ?></label>
	</div>
    <?php } ?>	
    <div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($role !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'roles'); ?>" method="post">
	<?php \Pasteque\form_delete("role", $role->id); ?>
</form>
<?php } ?>
