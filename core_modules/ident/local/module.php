<?php
//    Pastèque Web back office, local ident module
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

namespace Pasteque {
    require_once(dirname(__FILE__) . "/config.php");

    function is_user_logged_in() {
        session_start();
	if (isset($_SESSION["user"])) {
		return true;
	}
        if (isset($_POST['login']) and isset($_POST['password'])) {
            return api_user_login();
        }

	return false;
    }

    function api_user_login() {
        $user = null;
        $pwd = null;
        if (isset($_POST['login'])) {
            $user = $_POST['login'];
        }
        if (isset($_POST['password'])) {
            $pwd = $_POST['password'];
        }

	// Check in database...
        // Get the main database
        $dbh_ident = get_local_auth_database();
        $stmt = $dbh_ident->prepare("SELECT * FROM pasteque_users WHERE can_login AND user_id = :user_id");
        $stmt->bindParam(':user_id', $user, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) != 1) {
            // Bouh, invalid user
echo "hooo le pas beau";
            return false;
        }
        $userDbData = $result[0];
        
        require_once 'PasswordHash.php';
        $hasher = new \PasswordHash(8, TRUE);
        if ($hasher->CheckPassword($pwd, $userDbData['password'])) {
            session_start();
            $_SESSION["user"] = $userDbData['user_id'];
            return true;
        } else {
            echo "Vilain ! C'est pas ça le mot de passe... essaye chaussette ?";
            return false;
        }
    }

    function get_local_auth_database() {
        global $config;
        return new \PDO($config['local_ident_db_dsn'], $config['local_ident_db_username'], $config['local_ident_db_password']);
    }

    function show_login_page() {
	// Pasteque login page ?
        echo "Haaaaaaa je sais pas faire !\n";
        echo "<form method=\"POST\"><label for=\"login\">Login :</label><input name=\"login\" type=\"text\" /><br /><label for=\"password\">Password :</label><input name=\"password\" type=\"password\" /><br /><input type=\"submit\" /></form>";
    }

    function get_user_id() {
	return $_SESSION["user"];
    }

    function can_logout() {
        return true;
    }

    function logout() {
	session_unset();
	session_destroy();
        // Just for fun
        //header("Location: ");
        header('Location: .');
    }
}

