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

    private static $pdo = null;
    private static $pdoUid = null;

    /** Get PDO from the loaded database core module */
    public static function getPDO($uid = null) {
        // Set uid to logged user
        if ($uid === null) {
            $uid = get_user_id();
        }
        // Return cached pdo if same uid
        if (PDOBuilder::$pdo !== null && $uid === PDOBuilder::$pdoUid) {
            return PDOBuilder::$pdo;
        }
        $dsn = null;
        switch (get_db_type($uid)) {
        case 'mysql':
            $dsn = "mysql:dbname=" . get_db_name($uid) . ";host="
                    . get_db_host($uid) . ";port=" . get_db_port($uid);
            $options = array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'');
            $attributes = array(\PDO::ATTR_CASE => \PDO::CASE_UPPER);
            break;
        case 'postgresql':
            $dsn = "pgsql:dbname=" . get_db_name($uid) . ";host="
                    . get_db_host($uid) . ";port=" . get_db_port($uid);
            $options = array();
            $attributes = array(\PDO::ATTR_CASE => \PDO::CASE_UPPER);
            break;
        default:
            die("Config error");
        }
        try {
            PDOBuilder::$pdo = new \PDO($dsn, get_db_user($uid),
                    get_db_password($uid), $options);
            foreach ($attributes as $key => $value) {
                PDOBuilder::$pdo->setAttribute($key, $value);
            }
            PDOBuilder::$pdoUid = $uid;
            return PDOBuilder::$pdo;
        } catch (\PDOException $e) {
            die("Connexion error " . $e);
        }
    }

}
