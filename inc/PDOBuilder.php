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

class PDOBuilder {

    /** Get PDO from the loaded database core module */
    public static function getPDO() {
        $uid = get_user_id();
        switch (get_db_type($uid)) {
        case 'mysql':
            $dsn = "mysql:dbname=" . get_db_name($uid) . ";host="
                   . get_db_host($uid) . ";port=" . get_db_port($uid);
            try {
                return new \PDO($dsn, get_db_user($uid), get_db_password($uid),
                               array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
            } catch (\PDOException $e) {
                die("Connexion error " . $e);
            }
        default:
            die("Config error");
        }
    }

}

?>
