<?
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

namespace Pasteque {
    if (@constant("\Pasteque\ABSPATH") === NULL) {
        die();
    }
}

namespace WordPressDB {
    require_once(dirname(__FILE__) . "/config.php");
    $timezone = date_default_timezone_get();
    require_once($config['wordpress_base_path'] . "/wp-load.php");
    date_default_timezone_set($timezone);
    $data = NULL;
    function getInfo() {
        global $data;
        if ($data == NULL) {
            $wpdb = $GLOBALS['wpdb'];
            global $config;
            $sql = $wpdb->prepare('SELECT * FROM ' . $config['wordpress_table']
                    . ' WHERE user_id = %s', \Pasteque\get_user_id());
            $data = $wpdb->get_row($sql, ARRAY_A, 0);
        }
        return $data;
    }
    
    function host() { $info = getInfo(); return $info['host']; }
    function port() { $info = getInfo(); return $info['port']; }
    function name() { $info = getInfo(); return $info['database']; }
    function user() { $info = getInfo(); return $info['user']; }
    function passwd() { $info = getInfo(); return $info['password']; }
}

namespace Pasteque {
    function get_db_type($user_id) {
        return "mysql";
    }
    function get_db_host($user_id) {
        return \WordPressDB\host();
    }

    function get_db_port($user_id) {
        return \WordPressDB\port();
    }

    function get_db_name($user_id) {
        return \WordPressDB\name();
    }

    function get_db_user($user_id) {
        return \WordPressDB\user();
    }

    function get_db_password($user_id) {
        return \WordPressDB\passwd();
    }
}
