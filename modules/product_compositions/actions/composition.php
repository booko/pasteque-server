<?php
namespace ProductCompositions;

$message = NULL;
$error = NULL;
if (isset($_POST['delete-comp'])) {
    if (\Pasteque\CompositionsService::delete($_POST['delete-comp'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$compositions = \Pasteque\CompositionsService::getAll();
?>

<!-- start bloc titre -->
<div class="blc_ti">
<h1><?php \pi18n("Compositions", PLUGIN_NAME); ?></h1>
<span class="nb_article"><?php \pi18n("%d compositions", PLUGIN_NAME, count($compositions)); ?></span>
<?php \Pasteque\tpl_msg_box($message, $error); ?>


<ul class="bt_fonction">
	<li><?php \Pasteque\tpl_btn('btn bt_add', \Pasteque\get_module_url_action(PLUGIN_NAME, "composition_edit"),
        \i18n('Add composition', PLUGIN_NAME), 'img/btn_add.png');?></li>
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
			<th></th>
			<th><?php \pi18n("Composition.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
$par = FALSE;
foreach ($compositions as $composition) {
$par = !$par;
if ($composition->hasImage) {
    $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=product&id=" . $composition->id;
} else {
    $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=product";
}
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
		<td><img class="thumbnail" src="?<?php echo $imgSrc ?>" />
		<td><?php echo $composition->label; ?></td>
		<td class="edition">
            <?php \Pasteque\tpl_btn("edition", \Pasteque\get_module_url_action(PLUGIN_NAME,
                    'composition_edit', array("productId" => $composition->id)), "",
                    'img/edit.png', \i18n('Edit'), \i18n('Edit'));
            ?>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("comp", $composition->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
}
?>
	</tbody>
</table>
<?php
if (count($compositions) == 0) {
?>
<div class="alert"><?php \pi18n("No category found", PLUGIN_NAME); ?></div>
<?php
}
?>

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
