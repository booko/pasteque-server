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

namespace StockMultilocations;

$message = null;
$error = null;
$srv = new \Pasteque\LocationsService();
if (isset($_POST['id']) && isset($_POST['label'])) {
    // Update location
    $location = \Pasteque\Location::__build($_POST['id'], $_POST['label']);
    if ($srv->update($location)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['label'])) {
    // New location
    $location = new \Pasteque\Location($_POST['label']);
    $id = $srv->create($location);
    if ($id !== false) {
        $message = \i18n("Location saved. <a href=\"%s\">Go to the location page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'location_edit', array('id' => $id)));
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$location = null;
if (isset($_GET['id'])) {
    $location = $srv->get($_GET['id']);
}
?>
<h1><?php \pi18n("Edit a location", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post" enctype="multipart/form-data">
    <?php \Pasteque\form_hidden("edit", $location, "id"); ?>
	<?php \Pasteque\form_input("edit", "Location", $location, "label", "string", array("required" => true)); ?>

	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($location !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'locations'); ?>" method="post">
    <?php \Pasteque\form_delete("location", $location->id); ?>
</form>
<?php } ?>
