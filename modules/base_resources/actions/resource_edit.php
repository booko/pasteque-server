<?php
//    Pastèque Web back office, Resources module
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

namespace BaseResources;

$message = NULL;
$error = NULL;
if (isset($_POST['id'])) {
    if ($_FILES['file']['tmp_name'] !== "") {
        $content = file_get_contents($_FILES['file']['tmp_name']);
    } else if ($_POST['type'] == \Pasteque\Resource::TYPE_TEXT) {
        $content = $_POST['content'];
    }
    $res = \Pasteque\Resource::__build($_POST['id'], $_POST['name'],
            $_POST['type'], $content);
    if (\Pasteque\ResourcesService::update($res)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['name'])) {
    if ($_FILES['file']['tmp_name'] !== "") {
        $content = file_get_contents($_FILES['file']['tmp_name']);
    } else if ($_POST['type'] == \Pasteque\Resource::TYPE_TEXT) {
        $content = $_GET['text'];
    }
    $res = new \Pasteque\Resource($_POST['name'],
            $_POST['type'], $content);
    if (\Pasteque\ResourcesService::create($res)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$resource = NULL;
$txtContent = "";
$imgContent = "";
if (isset($_GET['id'])) {
    $resource = \Pasteque\ResourcesService::get($_GET['id']);

    switch ($resource->type) {
    case \Pasteque\Resource::TYPE_TEXT:
        $txtContent = $resource->content;
        break;
    case \Pasteque\Resource::TYPE_IMAGE:
        $imgContent = \Pasteque\URL_ACTION_PARAM . "=img&w=resource&id=" . $resource->id;
        break;
    }
}
?>

<h1><?php \pi18n("Resources", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form id="form" class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post" enctype="multipart/form-data">
	<?php \Pasteque\form_hidden("edit", $resource, "id"); ?>
	<div class="row">
		<?php \Pasteque\form_input("edit", "Resource", $resource, "name", "string", array("required" => true)); ?>
	</div>
	<div id="selector">
		<div class="row">
			<select id="type-selector">
				<option selected="true" value="<?php echo \Pasteque\Resource::TYPE_TEXT; ?>"><?php \pi18n("Text", PLUGIN_NAME); ?></option>
				<option value="<?php echo \Pasteque\Resource::TYPE_IMAGE; ?>"><?php \pi18n("Image", PLUGIN_NAME); ?></option>
			</select>
		</div>
		<div id="selector-btn" class="row actions">
			<a href="" class="btn" onClick="javascript:selected();return false;"><?php \pi18n("OK"); ?></a>
		</div>
	</div>
	<div id="editor">
		<textarea cols="80" rows="30" id="text" name="content"><?php echo $txtContent; ?></textarea>
		<img id="preview" name="image" src="?<?php echo $imgContent; ?>" /><br />
		<input type="file" name="file" />
		<div class="row actions">
			<?php \Pasteque\form_save(); ?>
		</div>
	</div>
</form>

<script type="text/javascript">
	typed = function(type) {
		jQuery("#form").append('<input type="hidden" name="type" value="' + type + '" />');
		switch (parseInt(type)) {
		case <?php echo \Pasteque\Resource::TYPE_TEXT; ?>:
			jQuery("#text").show();
			jQuery("#preview").hide();
			break;
		case <?php echo \Pasteque\Resource::TYPE_IMAGE; ?>:
			jQuery("#text").hide();
			jQuery("#preview").show();
			break;
		}
		jQuery("#editor").show();
	}

<?php if ($resource === NULL) { ?>
	jQuery("#editor").hide();
<?php } else { ?>
	jQuery("#selector").hide();
	typed(<?php echo $resource->type; ?>);
<?php } ?>
	selected = function() {
		jQuery("#selector").hide();
		typed(jQuery("#type-selector").val());
	}
</script>
