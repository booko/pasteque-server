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
    var_dump($pdo->errorInfo());
    // Load country data
    $country = $_POST['install'];
    $country = str_replace("..", "", $country);
    $cfile = ABSPATH . "/install/database/data_" . $country . ".sql";
    $pdo->query(\file_get_contents($cfile));
    var_dump($pdo->errorInfo());
}

function show_install() {
    tpl_open();
?><h1><?php \pi18n("Installation"); ?></h1>
<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<label for="install"><?php \pi18n("Pays"); ?>
	<select id="install" name="install">
		<option value="france">France</option>
	</select>
	<?php \Pasteque\form_send(); ?>
</form>
<?php
    tpl_close();
}

$pdo = PDOBuilder::getPDO();
$sql = "SELECT VERSION FROM APPLICATIONS WHERE ID = \"postech\"";
$stmt = $pdo->prepare($sql);
$stmt->execute();
if ($stmt->fetch()) {
    // Check version
} else {
    // Install
    show_install();
    die();
}

?>
