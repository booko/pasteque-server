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
    $attr = \Pasteque\Attribute::__build($_POST['id'], $_POST['label']);
    \Pasteque\AttributesService::updateAttribute($attr);
    // edit values
    $taxValues = array();
    foreach ($_POST as $key => $value) {
        if (strpos($key, "label-") === 0 && $key != "label-new") {
            $id = substr($key, 6);
            $val = \Pasteque\AttributeValue::__build($id, $value);
            \Pasteque\AttributesService::updateValue($val);
        }
    }
    if (isset($_POST['delete'])) {
        foreach ($_POST['delete'] as $del) {
            \Pasteque\AttributesService::deleteValue($del);
        }
    }
    // new values?
    foreach ($_POST['label-new'] as $newVal) {
        if ($newVal !== null && $newVal !== "") {
            $newValObj = new \Pasteque\AttributeValue($newVal);
            \Pasteque\AttributesService::createValue($newValObj, $_POST['id']);
        }
    }
} else if (isset($_POST['label'])) {
    // Create attribute
    $attr = new \Pasteque\Attribute($_POST['label']);
    \Pasteque\AttributesService::createAttribute($attr);
    foreach ($_POST['label-new'] as $newVal) {
        if ($newVal !== null && $newVal !== "") {
            $newValObj = new \Pasteque\AttributeValue($newVal);
            \Pasteque\AttributesService::createValue($newValObj, $attr->id);
        }
    }
}

$attribute = null;
if (isset($_GET['id'])) {
    $attribute = \Pasteque\AttributesService::getAttr($_GET['id']);
}
?>
<h1><?php \pi18n("Edit attribute", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<!-- Attribute edit -->
<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<fieldset>
		<legend><?php \pi18n("Attribute", PLUGIN_NAME); ?></legend>
		<?php \Pasteque\form_hidden("edit", $attribute, "id"); ?>
		<?php \Pasteque\form_input("edit", "Attribute", $attribute, "label", "string", array("required" => true)); ?>
	</fieldset>
	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th><?php \pi18n("AttributeValue.label"); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody id="list">
	<?php if ($attribute !== null) { foreach ($attribute->values as $value) { ?>
		<tr id="line-<?php echo $value->id; ?>">
			<td><?php \Pasteque\form_input($value->id, "AttributeValue", $value, "label", "string", array("required" => true, "nolabel" => true, "nameid" => true)); ?></td>
			<td><?php \Pasteque\tpl_js_btn("", "del('" . $value->id . "');", "", "", "delete.png"); ?></td>
		</tr>
	<?php } } ?>
	<?php for ($i = 0; $i < 5; $i++) { ?>
		<tr>
			<td><?php \Pasteque\form_input("new", "AttributeValue", null, "label", "string", array("nolabel" => true, "nameid" => true, "array" => true)); ?></td>
			<td></td>
		</tr>
	<?php } ?>
		</tbody>
	</table>

	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
	
</form>
<?php if ($attribute !== null) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'attributes'); ?>" method="post">
	<?php \Pasteque\form_delete("attribute", $attribute->id); ?>
</form>
<?php } ?>
<script type="text/javascript">
del = function(id) {
	jQuery("#line-" + id).remove();
	jQuery("form.edit").append("<input type=\"hidden\" name=\"delete[]\" value=\"" + id + "\" />");
}
</script>
