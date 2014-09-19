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

namespace BaseCashRegisters;

$message = null;
$error = null;
$srv = new \Pasteque\CashRegistersService();
if (isset($_POST['id']) && isset($_POST['label'])) {
    // Update cash register
    $cashReg = \Pasteque\CashRegister::__build($_POST['id'], $_POST['label'],
            $_POST['locationId']);
    if ($srv->update($cashReg)) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
} else if (isset($_POST['label'])) {
    // New cash register
    $cashReg = new \Pasteque\CashRegister($_POST['label'],
            $_POST['locationId']);
    $id = $srv->create($cashReg);
    if ($id !== false) {
        $message = \i18n("Cash register saved. <a href=\"%s\">Go to the cash register page</a>.", PLUGIN_NAME, \Pasteque\get_module_url_action(PLUGIN_NAME, 'cashregister_edit', array('id' => $id)));
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$cashReg = null;
if (isset($_GET['id'])) {
    $cashReg = $srv->get($_GET['id']);
}
?>

<!-- start bloc titre -->
<div class="blc_ti">
<h1><?php \pi18n("Edit a cash register", PLUGIN_NAME); ?></h1>
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
    <?php \Pasteque\form_hidden("edit", $cashReg, "id"); ?>
	<?php \Pasteque\form_input("edit", "CashRegister", $cashReg, "label", "string", array("required" => true)); ?>
	<?php \Pasteque\form_input("edit", "CashRegister", $cashReg, "locationId", "pick", array("model" => "Location")); ?>

	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
</form>
<?php if ($cashReg !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'cashregisters'); ?>" method="post">
    <?php \Pasteque\form_delete("cashreg", $cashReg->id); ?>
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