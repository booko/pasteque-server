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

// categories action

namespace BaseProducts;

if (isset($_POST['delete-cat'])) {
    \Pasteque\CategoriesService::deleteCat($_POST['delete-cat']);
}

$categories = \Pasteque\CategoriesService::getAll();
?>
<h1><?php \pi18n("Categories", PLUGIN_NAME); ?></h1>

<p><a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'category_edit'); ?>" class="btn btn-primary"><?php \pi18n("Add a category", PLUGIN_NAME); ?></a></p>

<p><?php \pi18n("%d categories", PLUGIN_NAME, count($categories)); ?></p>

<table class="table table-bordered table-striped table-condensed table-hover">
	<thead>
		<tr>
			<th><?php \pi18n("Category.name"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($categories as $category) {
?>
	<tr>
		<td><?php echo $category->name; ?></td>
		<td class="edition">
			<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'category_edit', array('id' => $category->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("cat", $category->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
}
?>
	</tbody>
</table>
<?php
if (count($categories) == 0) {
?>
<div class="alert"><?php \pi18n("No category found", PLUGIN_NAME); ?></div>
<?php
}
?>
