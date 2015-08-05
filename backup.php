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

ob_clean();
$user_id = \Pasteque\get_user_id();
$dbhost = \Pasteque\get_db_host($user_id);
$dbuser = \Pasteque\get_db_user($user_id);
$dbpasswd = \Pasteque\get_db_password($user_id);
$database = \Pasteque\get_db_name($user_id);
$dbport = \Pasteque\get_db_port($user_id);


header("Content-type: application/x-gzip-compressed");
header("Content-Disposition: attachment; filename=pasteque-".date(Ymd)."-" . $database . "-.sql.gz");
$output = fopen("php://output", "rb+");
$cmd ="mysqldump -u " . $dbuser . " --password=" . $dbpasswd . " " . $database ." --port=" . $dbport . " | gzip -c";
fputs($output,system($cmd));
