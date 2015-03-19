<?php
//    Pastèque Web back office, Users module
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

namespace BaseCashRegisters;

$message = null;
$error = null;
$srv = new \Pasteque\CashRegistersService();

if (isset($_POST['delete-cashreg'])) {
    if ($srv->delete($_POST['delete-cashreg'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$cashRegs = $srv->getAll();
?>

<!-- start bloc titre -->
<div class="blc_ti">
<h1><?php \pi18n("Cash registers", PLUGIN_NAME); ?></h1>


<ul class="bt_fonction">
	<li><?php \Pasteque\tpl_btn('btn bt_add', \Pasteque\get_module_url_action(PLUGIN_NAME, "cashregister_edit"),
        \i18n('New cash register', PLUGIN_NAME), 'img/btn_add.png');?></li>
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
			<th><?php \pi18n("CashRegister.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($cashRegs as $cashReg) { ?>
		<tr>
			<td><?php echo $cashReg->label; ?></td>
			<td class="edition">
				<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'cashregister_edit', array('id' => $cashReg->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
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