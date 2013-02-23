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

// products action

namespace BaseProducts;

if (isset($_POST['delete-product'])) {
    \Pasteque\ProductsService::delete($_POST['delete-product']);
}

$products = \Pasteque\ProductsService::getAll();
?>
<h1><?php \pi18n("Products", PLUGIN_NAME); ?></h1>

<p><a class="btn" href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'product_edit'); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/btn_add.png" /><?php \pi18n("Add a product", PLUGIN_NAME); ?></a></p>

<p><?php \pi18n("%d products", PLUGIN_NAME, count($products)); ?></p>

<h2><?php \pi18n("Catalog", PLUGIN_NAME); ?></h2>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th></th>
			<th><?php \pi18n("Product.reference"); ?></th>
			<th><?php \pi18n("Product.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
$par = FALSE;
$archive = FALSE;
foreach ($products as $product) {
if ($product->visible) {
$par = !$par;
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
	    <td><img class="thumbnail" src="?<?php echo \Pasteque\URL_ACTION_PARAM; ?>=img&w=product&id=<?php echo $product->id; ?>" />
		<td><?php echo $product->reference; ?></td>
		<td><?php echo $product->label; ?></td>
		<td class="edition">
			<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'product_edit', array('id' => $product->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("product", $product->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
} else { $archive = TRUE; }
}
?>
	</tbody>
</table>

<?php if ($archive) { ?>
<h2><?php \pi18n("Archived", PLUGIN_NAME); ?></h2>
<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th></th>
			<th><?php \pi18n("Product.reference"); ?></th>
			<th><?php \pi18n("Product.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
$par = FALSE;
foreach ($products as $product) {
if (!$product->visible) {
$par = !$par;
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
	    <td><img class="thumbnail" src="?<?php echo \Pasteque\URL_ACTION_PARAM; ?>=img&w=product&id=<?php echo $product->id; ?>" />
		<td><?php echo $product->reference; ?></td>
		<td><?php echo $product->label; ?></td>
		<td class="edition">
			<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'product_edit', array('id' => $product->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("product", $product->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
}
}
?>
	</tbody>
</table>
<?php } // archive end ?>

<?php
if (count($products) == 0) {
?>
<div class="alert"><?php \pi18n("No product found", PLUGIN_NAME); ?></div>
<?php
}
?>
