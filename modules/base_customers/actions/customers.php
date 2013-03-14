<?php
//    Pastèque Web back office, Customers module
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

namespace BaseCustomers;

if (isset($_POST['delete-customer'])) {
    \Pasteque\CustomersService::delete($_POST['delete-customer']);
}

$customers = \Pasteque\CustomersService::getAll(TRUE);
?>
<h1><?php \pi18n("Customers", PLUGIN_NAME); ?></h1>

<p><a class="btn" href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'customer_edit'); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/btn_add.png" /><?php \pi18n("Add a customer", PLUGIN_NAME); ?></a></p>

<p><?php \pi18n("%d customers", PLUGIN_NAME, count($customers)); ?></p>

<table cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th><?php \pi18n("Customer.number"); ?></th>
			<th><?php \pi18n("Customer.key"); ?></th>
			<th><?php \pi18n("Customer.disp_name"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($customers as $cust) {
?>
	<tr>
		<td><?php echo $cust->number; ?></td>
		<td><?php echo $cust->key; ?></td>
		<td><?php echo $cust->disp_name; ?></td>
		<td class="edition">
			<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'customer_edit', array('id' => $cust->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("customer", $cust->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
}
?>
	</tbody>
</table>
