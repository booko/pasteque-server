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

namespace CustomerDiscountProfiles;

$message = null;
$error = null;
$srv = new \Pasteque\DiscountProfilesService();

if (isset($_POST['delete-profile'])) {
    if ($srv->delete($_POST['delete-profile'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$profiles = $srv->getAll();
?>
<h1><?php \pi18n("Discount profiles", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_btn('btn', \Pasteque\get_module_url_action(PLUGIN_NAME, "discountprofile_edit"),
        \i18n('New discount profile', PLUGIN_NAME), 'img/btn_add.png');?>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php \pi18n("DiscountProfile.label"); ?></th>
			<th><?php \pi18n("DiscountProfile.rate"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($profiles as $profile) { ?>
		<tr>
			<td><?php echo $profile->label; ?></td>
			<td><?php echo $profile->rate; ?></td>
			<td class="edition">
				<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'discountprofile_edit', array('id' => $profile->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
