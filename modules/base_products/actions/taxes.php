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

// List all tax categories

namespace BaseProducts;

$message = NULL;
$error = NULL;
if (isset($_GET['delete-taxcat'])) {
    if (\Pasteque\TaxesService::deleteCat($_GET['delete-taxcat'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to delete tax. Tax cannot be deleted when in use.", PLUGIN_NAME);
    }
}

$taxes = \Pasteque\TaxesService::getAll();
?>

<!-- start bloc titre -->
<div class="blc_ti">
<h1><?php \pi18n("Taxes", PLUGIN_NAME); ?></h1>


<?php \Pasteque\tpl_msg_box($message, $error); ?>

	<ul class="bt_fonction">
    	<li><a class="bt_add transition" href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'tax_edit'); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/btn_add.png" /><?php \pi18n("Add a tax", PLUGIN_NAME); ?></a></li>
    </ul>
                
</div>
<!-- end bloc titre -->

<!-- start container scroll -->
<div class="container_scroll">
            
            	<div class="stick_row stickem-container">
                    
                    <!-- start colonne contenu -->
                    <div id="content_liste" class="grid_9">
                    
                        <div class="blc_content">


<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php \pi18n("TaxCat.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
$par = FALSE;
foreach ($taxes as $tax) {
$par = !$par;
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
		<td><?php echo $tax->label; ?></td>
		<td class="edition">
                    <?php \Pasteque\tpl_btn('btn-edition', \Pasteque\get_module_url_action(
                            PLUGIN_NAME, 'tax_edit', array("id" => $tax->id)), "",
                            'img/edit.png', \i18n('Edit'), \i18n('Edit'));
                    ?>
                    <?php \Pasteque\tpl_btn('btn-delete', \Pasteque\get_current_url() . "&delete-taxcat=" . $tax->id, "",
                            'img/delete.png', \i18n('Delete'), \i18n('Delete'), true);
                    ?>
		</td>
	</tr>
<?php
}
?>
	</tbody>
</table>
<?php
if (count($tax) == 0) {
?>
<div class="alert"><?php \pi18n("No tax found", PLUGIN_NAME); ?></div>
<?php
}
?>
<<<<<<< HEAD

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
=======
>>>>>>> 6.0
