<?php
//    Pastèque Web back office
//
//    Copyright (C) 2015 Scil (http://scil.coop)
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

namespace BaseBackup;

?><h1><?php \pi18n("Backup", PLUGIN_NAME); ?></h1>
<div class="hint"><?php \pi18n("Backup_help", PLUGIN_NAME); ?></div><?php

$user_id        = \Pasteque\get_user_id();
$dbhost = \Pasteque\get_db_host($user_id);
$dbuser = \Pasteque\get_db_user($user_id);
$dbpasswd       = \Pasteque\get_db_password($user_id);
$database       = \Pasteque\get_db_name($user_id);
$dbport = \Pasteque\get_db_port($user_id);

// Looking for an existing file (we do only allow one dump per day)
$dir = opendir("cache");
while(($f = readdir($dir)) != false) {
    if(preg_match("/pasteque-" . date("Ymd") . "-" . $database . "-.*.sql.gz/",$f) == 1) {
        $filename = "cache/" . $f;
        if(time() - filemtime($filename) > 86400) {
            $cmd = "rm cache/" . $filename;
            exec($cmd);
            $cmd = "mysqldump -u " . $dbuser . " --password=" . $dbpasswd . " " . $database ." --port=" . $dbport . "|gzip -c > " . $filename;
            exec($cmd);
        }
        break;
    }
}
// We didn’t find a file, we generate one with a little obfuscation for security
if($filename == "") {
    $filename       = "cache/pasteque-" . date("Ymd") . "-" . $database . "-" . md5(time() . rand(0,getrandmax())) . ".sql.gz";
    $cmd = "mysqldump -u " . $dbuser . " --password=" . $dbpasswd . " " . $database ." --port=" . $dbport . "|gzip -c > " . $filename;
    exec($cmd);

}
if(file_exists($filename)) {
    \Pasteque\tpl_btn('btn bt_export ', $filename,
        \i18n('Download backup', PLUGIN_NAME), 'img/btn_add.png');
}
