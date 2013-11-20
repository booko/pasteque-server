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
"/com/openbravo/reports/closedpos.bs",
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
"fr.pasteque.pos.forms.MenuCustomers",
"fr.pasteque.pos.customers.CustomersPanel",
"/com/openbravo/reports/customers.bs",
"/com/openbravo/reports/customersb.bs",
"/com/openbravo/reports/customersdiary.bs",
"/com/openbravo/reports/salesbycustomer.bs",
"fr.pasteque.pos.inventory.TaxCustCategoriesPanel",
"fr.pasteque.pos.forms.MenuCatalogManagement",
"fr.pasteque.pos.inventory.ProductsPanel",
"fr.pasteque.pos.inventory.ProductsWarehousePanel",
"fr.pasteque.pos.inventory.CategoriesPanel",
"fr.pasteque.pos.inventory.AttributesPanel",
"fr.pasteque.pos.inventory.AttributeValuesPanel",
"fr.pasteque.pos.inventory.AttributeSetsPanel",
"fr.pasteque.pos.inventory.AttributeUsePanel",
"fr.pasteque.pos.forms.MenuTaxesManagement",
"fr.pasteque.pos.inventory.TaxPanel",
"fr.pasteque.pos.inventory.TaxCategoriesPanel",
"fr.pasteque.pos.forms.MenuStockManagement",
"fr.pasteque.pos.inventory.StockDiaryPanel",
"fr.pasteque.pos.inventory.StockManagement",
"fr.pasteque.pos.inventory.AuxiliarPanel",
"/com/openbravo/reports/products.bs",
"/com/openbravo/reports/productlabels.bs" ,    
"/com/openbravo/reports/productscatalog.bs",
"/com/openbravo/reports/inventory.bs",
"/com/openbravo/reports/inventoryb.bs",
"/com/openbravo/reports/inventorybroken.bs",
"/com/openbravo/reports/inventorylistdetail.bs",
"/com/openbravo/reports/inventorydiff.bs",
"/com/openbravo/reports/inventorydiffdetail.bs",
"fr.pasteque.pos.forms.MenuSalesManagement",
"/com/openbravo/reports/usersales.bs",
"/com/openbravo/reports/closedproducts.bs",
"/com/openbravo/reports/taxes.bs",
"/com/openbravo/reports/chartsales.bs",
"/com/openbravo/reports/productsales.bs",
"/com/openbravo/reports/placessales.bs",
"fr.pasteque.pos.forms.MenuMaintenance",
"fr.pasteque.pos.admin.PeoplePanel",
"fr.pasteque.pos.admin.RolesPanel",
"fr.pasteque.pos.admin.ResourcesPanel",
"fr.pasteque.pos.inventory.LocationsPanel",
"fr.pasteque.pos.mant.JPanelFloors",
"fr.pasteque.pos.mant.JPanelPlaces",
"/com/openbravo/reports/people.bs",
"fr.pasteque.possync.ProductsSyncCreate",
"fr.pasteque.possync.OrdersSyncCreate",
"Menu.ChangePassword",
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
    	<input id="perm-<?php echo $perm; ?>" type="checkbox" <?php echo $checked; ?> name="permissions[]" value="<?php echo $perm; ?>">
		<label for="perm-<?php echo $perm; ?>"><?php echo \pi18n($perm, PLUGIN_NAME); ?></label>		
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
