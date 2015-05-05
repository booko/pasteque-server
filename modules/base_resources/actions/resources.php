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

$resSrv = new \Pasteque\ResourcesService();
if (isset($_POST['delete-res'])) {
    $resSrv->delete($_POST['delete-res']);
}

$resources = $resSrv->getAll();
?>

<!-- start bloc titre -->
<div class="blc_ti">

<h1><?php \pi18n("Resources", PLUGIN_NAME); ?></h1>
<ul class="bt_fonction">
    <li>
        <a class="btn bt_add" href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'resource_edit'); ?>">
            <img src="<?php echo \Pasteque\get_template_url(); ?>img/btn_add.png" /><?php \pi18n("Add a resource", PLUGIN_NAME); ?>
        </a>
    </li>
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
			<th><?php \pi18n("Resource.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<ul class="resources-list">
<?php
$par = FALSE;
foreach ($resources as $res) {
    $par = !$par; ?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
		<td><?php echo $res->label; ?></td>
		<td class="edition">
			<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'resource_edit', array('id' => $res->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("res", $res->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
}
?>
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
