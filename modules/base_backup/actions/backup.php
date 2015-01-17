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

$user_id = \Pasteque\get_user_id();
$dbhost = \Pasteque\get_db_host($user_id);
$dbuser = \Pasteque\get_db_user($user_id);
$dbpasswd = \Pasteque\get_db_password($user_id);
$database = \Pasteque\get_db_name($user_id);
$dbport = \Pasteque\get_db_port($user_id);

$cmd = "mysqldump -u " . $dbuser . " --password=" . $dbpasswd . " " . $database ." --port=" . $dbport;

?><textarea style="width:100%;height:80em;"><?php
system($cmd);
?></textarea><?php
