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

if (isset($_POST['install'])) {
    $country = $_POST['install'];
    $country = str_replace("..", "", $country);
    Installer::install($country);
} else if (isset($_POST['update'])) {
    $country = $_POST['country'];
    $country = str_replace("..", "", $country);
    Installer::upgrade($country);
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

$dbVer = Installer::getVersion();
switch (Installer::checkVersion($dbVer)) {
case Installer::NEED_DB_UPGRADE:
    show_update($dbVer);
    die();
case Installer::NEED_DB_DOWNGRADE:
    show_dowgrade($dbVer);
    die();
case Installer::DB_NOT_INSTALLED:
    show_install();
    die();
}

?>
