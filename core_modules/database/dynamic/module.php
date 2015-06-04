<?php
//    Pastèque Web back office, Static database module
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

namespace DynamicDB {
    // Remember, this cache dies at the end of each HTTP request, no panic
    $data = NULL;
    function getInfo($uid) {
        global $data;
        if ($data == NULL) {
            $dbh = \Pasteque\get_local_auth_database();
            $stmt = $dbh->prepare('SELECT * FROM pasteque_databases WHERE user_id = (SELECT id FROM pasteque_users WHERE user_id = :user_id)');
            $stmt->bindParam(':user_id', $uid);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if (count($result) != 1) {
                die("hard - $uid");
            }
            $data = $result[0];
        }
        return $data;
    }
    
    function host($uid) { $info = getInfo($uid); return $info['host']; }
    function port($uid) { $info = getInfo($uid); return $info['port']; }
    function name($uid) { $info = getInfo($uid); return $info['database']; }
    function user($uid) { $info = getInfo($uid); return $info['user']; }
    function passwd($uid) { $info = getInfo($uid); return $info['password']; }
}

namespace Pasteque {
    function get_db_type($user_id) {
        return "mysql";
    }
    function get_db_host($user_id) {
        return \DynamicDB\host($user_id);
    }

    function get_db_port($user_id) {
        return \DynamicDB\port($user_id);
    }

    function get_db_name($user_id) {
        return \DynamicDB\name($user_id);
    }

    function get_db_user($user_id) {
        return \DynamicDB\user($user_id);
    }

    function get_db_password($user_id) {
        return \DynamicDB\passwd($user_id);
    }
}
?>
