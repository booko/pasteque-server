<?php
//    Pastèque Web back office, WordPress ident module
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

namespace WordPressID {
    require_once(\Pasteque\PT::$ABSPATH . "/core_modules/tools/wp_preprocessing.php");
    require_once(dirname(__FILE__) . "/config.php");
    \WordPress\loadWP($config['wordpress_base_path']);
    $api_user = NULL;

    function logged_in() {
        return is_user_logged_in();
    }
    function log($user, $password) {
        $creds = array();
        $creds['user_login'] = $user;
        $creds['user_password'] = $password;
        $creds['remember'] = FALSE;
        $user = wp_signon($creds, FALSE);
        if (!is_wp_error($user)) {
            global $api_user;
            $api_user = $user;
            return TRUE;
        } else {
            return FALSE;
        }
    }
    function show_login() {
        auth_redirect();
    }
    function get_user_id() {
        global $api_user;
        if ($api_user !== NULL) {
            return $api_user->ID;
        } else {
            return get_current_user_id();
        }
    }

    function logout() {
        wp_logout();
    }
}

namespace Pasteque {
    function is_user_logged_in() {
        if(\WordPressID\logged_in() != true) {
            return api_user_login();
        }
        else {
            return true;
        }
    }
    function api_user_login() {
        $user = null;
        $pwd = null;
        if (isset($_POST['login'])) {
            $user = $_POST['login'];
        } else if (isset($_GET['login'])) {
            $user = $_GET['login'];
        }
        if (isset($_POST['password'])) {
            $pwd = $_POST['password'];
        } else if (isset($_GET['password'])) {
            $pwd = $_GET['password'];
        }
        return \WordPressID\log($user, $pwd);
    }
    function show_login_page() {
        return \WordPressID\show_login();
    }

    function get_user_id() {
        return \WordPressID\get_user_id();
    }

    function can_logout() {
        return true;
    }
    function logout() {
        \WordPressID\logout();
        header('Location: ' . get_site_url());
    }
}
