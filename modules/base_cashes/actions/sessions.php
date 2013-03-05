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

namespace BaseCashes;

$message = NULL;
$error = NULL;

$sessions = \Pasteque\CashesService::getAll();
?>
<h1><?php \pi18n("Sessions", PLUGIN_NAME); ?></h1>

<h2><?php \pi18n("Active sessions", PLUGIN_NAME); ?></h2>
<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php \pi18n("Session.host"); ?></th>
			<th><?php \pi18n("Session.openDate"); ?></th>
			<th><?php \pi18n("Session.tickets"); ?></th>
			<th><?php \pi18n("Session.total"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($sessions as $session) {
    if (!$session->isClosed()) { ?>
		<tr>
			<td><?php echo $session->host; ?></td>
			<td><?php \pi18nDatetime($session->openDate); ?></td>
			<td class="numeric"><?php echo $session->tickets; ?></td>
			<td class="numeric"><?php echo $session->total; ?></td>
			<td class="edition">
				<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'session_details', array('id' => $session->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			</td>
		</tr>
<?php } } ?>
	</tbody>
</table>

<h2><?php \pi18n("Closed sessions", PLUGIN_NAME); ?></h2>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php \pi18n("Session.host"); ?></th>
			<th><?php \pi18n("Session.openDate"); ?></th>
			<th><?php \pi18n("Session.closeDate"); ?></th>
			<th><?php \pi18n("Session.tickets"); ?></th>
			<th><?php \pi18n("Session.total"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($sessions as $session) {
    if ($session->isClosed()) { ?>
		<tr>
			<td><?php echo $session->host; ?></td>
			<td><?php \pi18nDatetime($session->openDate); ?></td>
			<td><?php \pi18nDatetime($session->closeDate); ?></td>
			<td class="numeric"><?php echo $session->tickets; ?></td>
			<td class="numeric"><?php echo $session->total; ?></td>
			<td class="edition">
				<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'session_details', array('id' => $session->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			</td>
		</tr>
<?php } } ?>
