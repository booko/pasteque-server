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

// providers action

namespace ProductProviders;

$message = NULL;
$error = NULL;
if (isset($_POST['delete-prov'])) {
    if (\Pasteque\providersService::deleteprov($_POST['delete-prov'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
        $error .= " " . \i18n("Only empty provider can be deleted", PLUGIN_NAME);
    }
}

$providers = \Pasteque\providersService::getAll();
?>
<h1><?php \pi18n("Providers", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<?php \Pasteque\tpl_btn('btn', \Pasteque\get_module_url_action(PLUGIN_NAME, "provider_edit"),
        \i18n('Add a provider', PLUGIN_NAME), 'img/btn_add.png');?>
<?php \Pasteque\tpl_btn('btn', \Pasteque\get_module_url_action(PLUGIN_NAME, "providersManagement"),
        \i18n('Import providers', PLUGIN_NAME), 'img/btn_add.png');?>


<p><?php \pi18n("%d providers", PLUGIN_NAME, count($providers)); ?></p>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th></th>
			<th><?php \pi18n("Provider.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
function printprovider($printprovider, $level, &$par) {
        $par = !$par;
        if ($printprovider->hasImage) {
            $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=provider&id=" . $printprovider->id;
        } else {
            $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=provider";
        }
        ?>
                <tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
                        <td>
                        <?php
                        for($i=0;$i<$level;$i++) {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                        }
                        ?>
                        <img class="thumbnail" src="?<?php echo $imgSrc ?>" />
                        <td><?php echo $printprovider->label; ?></td>
                        <td class="edition">
                    <?php \Pasteque\tpl_btn("edition", \Pasteque\get_module_url_action(PLUGIN_NAME,
                            'provider_edit', array("id" => $printprovider->id)), "",
                            'img/edit.png', \i18n('Edit'), \i18n('Edit'));
                    ?>
                                <form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("prov", $printprovider->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
                        </td>
                </tr>
        <?php
}

$par = false;
foreach ($providers as $provider) {
    printprovider($provider, 0, $par);
}
?>
	</tbody>
</table>
<?php
if (count($providers) == 0) {
?>
<div class="alert"><?php \pi18n("No provider found", PLUGIN_NAME); ?></div>
<?php
}
?>
