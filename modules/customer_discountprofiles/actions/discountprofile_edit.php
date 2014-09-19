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

namespace CustomerDiscountProfiles;

$message = null;
$error = null;
$srv = new \Pasteque\DiscountProfilesService();
if (isset($_POST['id']) && isset($_POST['label'])) {
    // Update profile
    $profile = \Pasteque\DiscountProfile::__build($_POST['id'], $_POST['label'],
            $_POST['rate']);
    if ($srv->update($profile)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['label'])) {
    // New profile
    $profile = new \Pasteque\DiscountProfile($_POST['label'], $_POST['rate']);
    $id = $srv->create($profile);
    if ($id !== false) {
        $message = \i18n("Discount profile saved. <a href=\"%s\">Go to the profile page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'discountprofile_edit', array('id' => $id)));
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$profile = null;
if (isset($_GET['id'])) {
    $profile = $srv->get($_GET['id']);
}
?>

<!-- start bloc titre -->
<div class="blc_ti">
<h1><?php \pi18n("Edit a profile", PLUGIN_NAME); ?></h1>
</div>
<!-- end bloc titre -->

<!-- start container scroll -->
<div class="container_scroll">
            
            	<div class="stick_row stickem-container">
                    
                    <!-- start colonne contenu -->
                    <div id="content_liste" class="grid_9">
                    
                        <div class="blc_content">



<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post" enctype="multipart/form-data">
    <?php \Pasteque\form_hidden("edit", $profile, "id"); ?>
	<?php \Pasteque\form_input("edit", "DiscountProfile", $profile, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "DiscountProfile", $profile, "rate", "float", array("required" => true)); ?>

	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($profile !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'discountprofiles'); ?>" method="post">
    <?php \Pasteque\form_delete("profile", $profile->id); ?>
</form>
<?php } ?>
</div></div>
                    <!-- end colonne contenu -->
                    
                    <!-- start sidebar menu -->
                    <div id="sidebar_menu" class="grid_3 stickem">
                    
                        <div class="blc_content">
                            
                            <!-- start texte editorial -->
                            <div class="edito"><!-- zone_edito --></div>
                            <!-- end texte editorial -->
                            
                            
                        </div>
                        
                    </div>
                    <!-- end sidebar menu -->
                    
        		</div>
                
        	</div>
            <!-- end container scroll -->