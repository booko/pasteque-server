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

$message = null;
$error = null;
$srv = new \Pasteque\CashesService();

$startStr = isset($_POST['start']) ? $_POST['start'] : \i18nDate(time() - 604800);
$stopStr = isset($_POST['stop']) ? $_POST['stop'] : \i18nDate(time());

$report = \Pasteque\get_report(PLUGIN_NAME, "ztickets");
?>
<h1><?php \pi18n("Z tickets", PLUGIN_NAME); ?></h1>

<p><a class="btn" href="<?php echo \Pasteque\get_report_url(PLUGIN_NAME, 'ztickets'); ?>&start=<?php echo $startStr; ?>&stop=<?php echo $stopStr; ?>"><?php \pi18n("Export"); ?></a></p>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<div class="row">
		<label for="start"><?php \pi18n("Session.openDate"); ?></label>
		<input type="date" name="start" id="start" value="<?php echo $startStr; ?>" />
	</div>
	<div class="row">
		<label for="stop"><?php \pi18n("Session.closeDate"); ?></label>
		<input type="date" name="stop" id="stop" value="<?php echo $stopStr; ?>" />
	</div>
	<div class="row actions">
		<?php \Pasteque\form_send(); ?>
	</div>
</form>

<?php \Pasteque\tpl_report($report); ?>
