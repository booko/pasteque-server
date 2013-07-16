<?php
//    Pastèque Web back office
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

namespace Pasteque;

if (@constant("\Pasteque\ABSPATH") === NULL) {
    die();
}

if (isset($_POST['install'])) {
    $pdo = PDOBuilder::getPDO();
    // Load generic sql data
    $file = ABSPATH . "/install/database/create.sql";
    $pdo->query(\file_get_contents($file));
    // Load country data
    $country = $_POST['install'];
    $country = str_replace("..", "", $country);
    $cfile = ABSPATH . "/install/database/data_" . $country . ".sql";
    $pdo->query(\file_get_contents($cfile));
} else if (isset($_POST['update'])) {
    $pdo = PDOBuilder::getPDO();
    // Load generic sql update for current version
    $version = $_POST['update'];
    $country = $_POST['country'];
    $file = ABSPATH . "/install/database/upgrade-" . $version . ".sql";
    $pdo->query(\file_get_contents($file));
    // Check for localized update data for current version
    $file = ABSPATH . "/install/database/upgrade-" . $version . "_" . $country . ".sql";
    if (\file_exists($file)) {
        $pdo->query(\file_get_contents($file));
    }
}

function show_install() {
    tpl_open();
?><h1><?php \pi18n("Installation"); ?></h1>
<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<label for="install"><?php \pi18n("Pays"); ?>
	<select id="install" name="install">
		<option value="belgique">Belgique</option>
		<option value="france">France</option>
		<option value="luxembourg">Luxembourg</option>
		<option value="united_kingdom">United Kingdom</option>
	</select>
	<?php \Pasteque\form_send(); ?>
</form>
<?php
    tpl_close();
}

function show_update($dbVer) {
    tpl_open();
?><h1><?php \pi18n("Update"); ?></h1>
<p><?php \pi18n("Update notice"); ?></p>
<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php form_value_hidden("update", "update", $dbVer); ?>
    <label for="country"><?php \pi18n("Pays"); ?>
	<select id="country" name="country">
		<option value="belgique">Belgique</option>
		<option value="france">France</option>
		<option value="luxembourg">Luxembourg</option>
		<option value="united_kingdom">United Kingdom</option>
	</select>
	<?php \Pasteque\form_send(); ?>
</form>
<?php
    tpl_close();
}

function show_downgrade($dbVer) {
    tpl_open();
?><h1>Incompatible version</h1>
<p>Please update your server.</p>
<?php
    tpl_close();
}

$pdo = PDOBuilder::getPDO();
$sql = "SELECT VERSION FROM APPLICATIONS WHERE ID = \"postech\"";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetch();
if ($data !== FALSE) {
    // Check version
    $dbVer = $data['VERSION'];
    if (intval($dbVer) < intval(DB_VERSION)) {
        // Need update
        show_update($dbVer);
    } else if (intval($dbVer) > intval(DB_VERSION)) {
        // Need downgrade
        show_dowgrade($dbVer);
    }
} else {
    // Install
    show_install();
    die();
}

?>
