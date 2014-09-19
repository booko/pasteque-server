<?php
//    Pastèque Web back office, Currencies module
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

// categories action

namespace BaseCurrencies;

$message = NULL;
$error = NULL;
$currSrv = new \Pasteque\CurrenciesService();
if (isset($_POST['delete-currency'])) {
    if ($currSrv->delete($_POST['delete-currency'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$currencies = $currSrv->getAll();
?>

<!-- start bloc titre -->
<div class="blc_ti">
<h1><?php \pi18n("Currencies", PLUGIN_NAME); ?></h1>



<?php \Pasteque\tpl_msg_box($message, $error); ?>

<ul class="bt_fonction">
	<li><?php \Pasteque\tpl_btn('btn bt_add', \Pasteque\get_module_url_action(PLUGIN_NAME, "currency_edit"),
        \i18n('Add a currency', PLUGIN_NAME), 'img/btn_add.png');?></li>
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
			<th><?php \pi18n("Currency.label"); ?></th>
			<th><?php \pi18n("Currency.rate"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
$par = FALSE;
foreach ($currencies as $currency) {
$par = !$par;
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
		<td><?php echo $currency->label; ?></td>
		<td><?php
if ($currency->isMain) {
    \pi18n("Main", PLUGIN_NAME);
} else {
    echo($currency->rate);
} ?>
		<td class="edition">
            <?php \Pasteque\tpl_btn("edition", \Pasteque\get_module_url_action(PLUGIN_NAME,
                    'currency_edit', array("id" => $currency->id)), "",
                    'img/edit.png', \i18n('Edit'), \i18n('Edit'));
            ?>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("currency", $currency->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
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