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

namespace WordPressDB {
    require_once(\Pasteque\PT::$ABSPATH . "/core_modules/tools/wp_preprocessing.php");
    require_once(dirname(__FILE__) . "/config.php");
    \WordPress\loadWP($config['wordpress_base_path']);
    $data = NULL;
    function getInfo($uid) {
        if ($data == NULL) {
            $wpdb = $GLOBALS['wpdb'];
            global $config;
            $sql = $wpdb->prepare('SELECT * FROM ' . $config['wordpress_table']
                    . ' WHERE user_id = %s', $uid);
            $data = $wpdb->get_row($sql, ARRAY_A, 0);
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
        return \WordPressDB\host($user_id);
    }

    function get_db_port($user_id) {
        return \WordPressDB\port($user_id);
    }

    function get_db_name($user_id) {
        return \WordPressDB\name($user_id);
    }

    function get_db_user($user_id) {
//        echo \WordPressDB\user($user_id);
//        echo $user_id;
        return \WordPressDB\user($user_id);
    }

    function get_db_password($user_id) {
        return \WordPressDB\passwd($user_id);
    }
}
?>
