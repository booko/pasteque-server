<?php
//    Pastèque Web back office, Payment modes module
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

namespace BaseAccounts;

$message = NULL;
$error = NULL;

if (isset($_GET['token'])) {
    $dbh = \Pasteque\get_local_auth_database();
    $stmt = $dbh->prepare("SELECT id, user_id FROM pasteque_users WHERE token = :token");
    $stmt->bindValue(":token", $_GET["token"]);
    if (!$stmt->execute()) {
        $error = \i18n("Internal error");
    } else {
        $results = $stmt->fetchall();
        if (count($results) != 1) {
            $error = \i18n("Failed to find the requested user");
        } else {
            $result = $results[0];
            $user_id = $result["id"];
            $user_login = $result["user_id"];
            
            // Start preparing account
            if (system("/usr/local/bin/prepare_pasteque_account $user_id") != 0) {
                $error = \i18n("Failed to prepare the pasteque account, please contact our support");
            } else {
                $stmt = $dbh->prepare("UPDATE pasteque_users SET token = NULL, validation_date = NOW(), can_login = 1 WHERE id = :id AND token = :token;");
                $stmt->bindValue(":token", $_GET["token"]);
                $stmt->bindValue(":id", $user_id);
                if (!$stmt->execute()) {
                    $error = \i18n("Failed to validate your account, please contact our support");
                } else {
                    $message = \i18n("Your account is ready, you can login now");
                }
            }
        }
    }
} else {
    $error = \i18n("Missing parameter");
}


?>
<h1><?php \pi18n("Register a new account", PLUGIN_NAME); ?></h1>

<br />

<?php \Pasteque\tpl_msg_box($message, $error); ?>

