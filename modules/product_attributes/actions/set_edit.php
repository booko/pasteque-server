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

// tax_edit action

namespace ProductAttributes;

$message = null;
$error = null;
// Check saves
if (isset($_POST['id'])) {
    // Update attribute
    $set = \Pasteque\AttributeSet::__build($_POST['id'], $_POST['label']);
    foreach ($_POST['id-attr'] as $attrId) {
        if ($attrId !== null && $attrId !== "") {
            $attr = \Pasteque\Attribute::__build($attrId, "unused", null);
            $set->addAttribute($attr, null);
        }
    }
    \Pasteque\AttributesService::updateSet($set);
} else if (isset($_POST['label'])) {
    // Create attribute
    $set = new \Pasteque\AttributeSet($_POST['label']);
    foreach ($_POST['id-attr'] as $attrId) {
        if ($attrId !== null && $attrId !== "") {
            $attr = \Pasteque\Attribute::__build($attrId, "unused", null);
            $set->addAttribute($attr, null);
        }
    }
    \Pasteque\AttributesService::createSet($set);
}

$set = null;
if (isset($_GET['id'])) {
    $set = \Pasteque\AttributesService::get($_GET['id']);
}
?>
<h1><?php \pi18n("Edit attribute set", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<!-- Attribute edit -->
<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<fieldset>
		<legend><?php \pi18n("Attribute set", PLUGIN_NAME); ?></legend>
		<?php \Pasteque\form_hidden("edit", $set, "id"); ?>
		<?php \Pasteque\form_input("edit", "AttributeSet", $set, "label", "string", array("required" => true)); ?>
	</fieldset>
	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th><?php \pi18n("AttributeSet.label"); ?></th>
			</tr>
		</thead>
		<tbody id="list">
	<?php if ($set !== null) { foreach ($set->attributes as $value) { ?>
		<tr id="line-<?php echo $value->id; ?>">
			<td><?php \Pasteque\form_input("attr", "Attribute", $value, "id", "pick", array("model" => "Attribute", "nullable" => true, "nolabel" => true, "array" => true, "nameid" => true)); ?></td>
		</tr>
	<?php } } ?>
	<?php for ($i = 0; $i < 5; $i++) { ?>
		<tr>
			<td><?php \Pasteque\form_input("attr", "Attribute", null, "id", "pick", array("model" => "Attribute", "nullable" => true, "nolabel" => true, "array" => true, "nameid" => true)); ?></td>
		</tr>
	<?php } ?>
		</tbody>
	</table>

	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
	
</form>
<?php if ($set !== null) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'sets'); ?>" method="post">
	<?php \Pasteque\form_delete("set", $set->id); ?>
</form>
<?php } ?>
