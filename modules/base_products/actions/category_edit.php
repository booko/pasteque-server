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

// category_edit action

namespace BaseProducts;

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
    $parent_id = NULL;
    if ($_POST['parentId'] !== "") {
        $parent_id = $_POST['parentId'];
    }
    $dispOrder = 0;
    if ($_POST['dispOrder'] !== "") {
        $dispOrder = intval($_POST['dispOrder']);
    }
    $cat = \Pasteque\Category::__build($_POST['id'], $parent_id,
            $_POST['label'], $img !== null, $dispOrder);
    if (\Pasteque\CategoriesService::updateCat($cat, $img)) {
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
    $parent_id = NULL;
    if ($_POST['parentId'] !== "") {
        $parent_id = $_POST['parentId'];
    }
    $dispOrder = 0;
    if ($_POST['dispOrder'] !== "") {
        $dispOrder = intval($_POST['dispOrder']);
    }
    $cat = new \Pasteque\Category($parent_id, $_POST['label'], $img, $dispOrder);
    $id = \Pasteque\CategoriesService::createCat($cat, $img);
    if ($id !== FALSE) {
        $message = \i18n("Category saved. <a href=\"%s\">Go to the category page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'category_edit', array('id' => $id)));
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$category = NULL;
if (isset($_GET['id'])) {
    $category = \Pasteque\CategoriesService::get($_GET['id']);
}
?>

<div class="blc_ti">
    <h1><?php \pi18n("Edit a category", PLUGIN_NAME); ?></h1>
</div>

<div class="container_scroll">
    <div class="stick_row stickem-container">
        <div id="content_liste" class="grid_9">
            <div class="blc_content">

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post" enctype="multipart/form-data">
    <?php \Pasteque\form_hidden("edit", $category, "id"); ?>
	<?php \Pasteque\form_input("edit", "Category", $category, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "Category", $category, "parentId", "pick", array("model" => "Category", "nullable" => TRUE)); ?>
	<?php \Pasteque\form_input("edit", "Category", $category, "dispOrder", "numeric"); ?>
	<div class="row">
		<label for="image"><?php \pi18n("Image", PLUGIN_NAME); ?></label>
		<div style="display:inline-block">
			<input type="hidden" id="clearImage" name="clearImage" value="0" />
		<?php if ($category !== null && $category->hasImage) { ?>
			<img id="img" class="image-preview" src="?<?php echo \Pasteque\PT::URL_ACTION_PARAM; ?>=img&w=category&id=<?php echo $category->id; ?>" />
			<a class="btn" id="clear" href="" onClick="javascript:clearImage(); return false;"><?php \pi18n("Delete"); ?></a>
			<a class="btn" style="display:none" id="restore" href="" onClick="javascript:restoreImage(); return false;"><?php \pi18n("Restore"); ?></a><br />
		<?php } ?>
			<input type="file" name="image" />
		</div>
	</div>

	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($category !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'categories'); ?>" method="post">
    <?php \Pasteque\form_delete("cat", $category->id); ?>
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
            </div>
        </div>
            <div id="sidebar_menu" class="grid_3 stickem">
                <div class="blc_content">
                <div class="edito"></div>
            </div>
        </div>
    </div>
</div>
