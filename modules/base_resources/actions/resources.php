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
if (isset($_GET['delete-res'])) {
    $resSrv->delete($_GET['delete-res']);
}

$resources = $resSrv->getAll();
?>

<h1><?php \pi18n("Resources", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_btn('btn-add', \Pasteque\get_module_url_action(PLUGIN_NAME, "resource_edit"),
        \i18n('Add a resource', PLUGIN_NAME), 'img/btn_add.png');?>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<p><?php \pi18n("%d resources", PLUGIN_NAME, count($resources)); ?></p>

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
                    <?php \Pasteque\tpl_btn('btn-edition', \Pasteque\get_module_url_action(
                            PLUGIN_NAME, 'resource_edit', array("id" => $res->id)), "",
                            'img/edit.png', \i18n('Edit'), \i18n('Edit'));
                    ?>
                    <?php \Pasteque\tpl_btn('btn-delete', \Pasteque\get_current_url() . "&delete-resource=" . $res->id, "",
                            'img/delete.png', \i18n('Delete'), \i18n('Delete'), true);
                    ?>
		</td>
	</tr>
<?php
}
?>
	</tbody>
</table>
