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

// provider_edit action

namespace ProductProviders;

$message = NULL;
$error = NULL;
if (isset($_POST['id']) && isset($_POST['label'])) {
    if ($_FILES['image']['tmp_name'] !== "") {
        $output = $_FILES['image']['tmp_name'] . "thumb";
        \Pasteque\img_thumbnail($_FILES['image']['tmp_name'], $output);
        $img = file_get_contents($output);
    } else if ($_POST['clearImage']) {
        $img = NULL;
    } else {
        $img = "";
    }
    $dispOrder = 0;
    if ($_POST['dispOrder'] !== "") {
        $dispOrder = intval($_POST['dispOrder']);
    }
    $prov = \Pasteque\provider::__build($_POST['id'], $_POST['label'], $img !== null,
            $_POST['firstName'], $_POST['lastName'], $_POST['email'],
            $_POST['phone1'], $_POST['phone2'], $_POST['website'], $_POST['fax'],
            $_POST['addr1'],  $_POST['addr2'], $_POST['zipCode'], $_POST['city'],
            $_POST['region'], $_POST['country'], $_POST['notes'], $_POST['visible'],
            $dispOrder);
    if (\Pasteque\providersService::updateprov($prov, $img)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['label'])) {
    if ($_FILES['image']['tmp_name'] !== "") {
        $img = file_get_contents($_FILES['image']['tmp_name']);
    } else {
        $img = NULL;
    }
    $dispOrder = 0;
    if ($_POST['dispOrder'] !== "") {
        $dispOrder = intval($_POST['dispOrder']);
    }

    $prov = new \Pasteque\Provider($_POST['label'], $img !== null, 
            $_POST['firstName'], $_POST['lastName'], $_POST['email'],
            $_POST['phone1'], $_POST['phone2'], $_POST['website'], $_POST['fax'],
            $_POST['addr1'],  $_POST['addr2'], $_POST['zipCode'], $_POST['city'],
            $_POST['region'], $_POST['country'], $_POST['notes'], $_POST['visible'],
            $dispOrder);
    $id = \Pasteque\providersService::createprov($prov, $img);
    if ($id !== FALSE) {
        $message = \i18n("provider saved. <a href=\"%s\">Go to the provider page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'provider_edit', array('id' => $id)));
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$provider = NULL;
if (isset($_GET['id'])) {
    $provider = \Pasteque\providersService::get($_GET['id']);
}
?>
<h1><?php \pi18n("Edit a provider", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post" enctype="multipart/form-data">
    <?php \Pasteque\form_hidden("edit", $provider, "id"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "dispOrder", "numeric"); ?>
	<div class="row">
		<label for="image"><?php \pi18n("Image", PLUGIN_NAME); ?></label>
		<div style="display:inline-block">
			<input type="hidden" id="clearImage" name="clearImage" value="0" />
		<?php if ($provider !== null && $provider->hasImage) { ?>
			<img id="img" class="image-preview" src="?<?php echo \Pasteque\PT::URL_ACTION_PARAM; ?>=img&w=provider&id=<?php echo $provider->id; ?>" />
			<a class="btn" id="clear" href="" onClick="javascript:clearImage(); return false;"><?php \pi18n("Delete"); ?></a>
			<a class="btn" style="display:none" id="restore" href="" onClick="javascript:restoreImage(); return false;"><?php \pi18n("Restore"); ?></a><br />
		<?php } ?>
			<input type="file" name="image" />
		</div>
	</div>
	<legend><?php \pi18n("Contact data", PLUGIN_NAME); ?></legend>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "firstName", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "lastName", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "email", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "phone1", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "phone2", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "website", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "fax", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "addr1", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "addr2", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "zipCode", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "city", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "region", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "country", "string"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "notes", "text"); ?>
	<?php \Pasteque\form_input("edit", "Provider", $provider, "visible", "boolean"); ?>
	</fieldset>

	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($provider !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'providers'); ?>" method="post">
    <?php \Pasteque\form_delete("prov", $provider->id); ?>
</form>
<?php } ?>

<script type="text/javascript">
	clearImage = function() {
		jQuery("#img").hide();
		jQuery("#clear").hide();
		jQuery("#restore").show();
		jQuery("#clearImage").val(1);
	}
	restoreImage = function() {
		jQuery("#img").show();
		jQuery("#clear").show();
		jQuery("#restore").hide();
		jQuery("#clearImage").val(0);
	}	
</script>
