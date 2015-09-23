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

if (isset($_POST['submit_request'])) {
    $fields = array('first_name', 'last_name', 'email', 'accept_contract', 'login', 'password', 'confirm_password');
    $errors = array();
    foreach ($fields as &$field) {
        if (!isset($_POST[$field]) or $_POST[$field] == "") {
            array_push($errors, \i18n("Missing required field $field"));
        }
    }
    if ($_POST['password'] != $_POST['confirm_password']) {
        array_push($errors, \i18n("Password confirmation incorrect"));
    }
    $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
    if ($email == NULL) {
        array_push($errors, \i18n("Invalid email"));
    }

    if (count($errors) == 0) {
        $firstName = $_POST["first_name"];
        $lastName = $_POST["last_name"];
        $login = $_POST["login"];
        
        // TODO : insert data in database
        $password = $_POST["password"];
        $dbh = \Pasteque\get_local_auth_database();
        $extraFields = '';
        $extraPlaceholders = '';
        if ($_POST["website"]) {
            $extraFields .= ', website';
            $extraPlaceholders .= ', :website';
        }
        $query = "INSERT INTO pasteque_users (creation_date, user_id, first_name, last_name, email, password, newsletter, coffee_shop, token $extraFields) VALUES (now(), :login, :first_name, :last_name, :email, :password, :newsletter, :coffee_shop, :token $extraPlaceholders);";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':login', $login);
        $stmt->bindValue(':first_name', $firstName);
        $stmt->bindValue(':last_name', $lastName);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', \Pasteque\local_hash_password($password));
        if ($_POST["subscribe_newsletter"]) {
            $stmt->bindValue(':newsletter', 1);
        } else {
            $stmt->bindValue(':newsletter', 0);
        }
        if ($_POST["usage_coffeeshop"]) {
            $stmt->bindValue(':coffee_shop', 1);
        } else {
            $stmt->bindValue(':coffee_shop', 0);
        }

        // Generate token and push to placeholder :token
        $token = sha1(time() . $login . $email . rand());
        $stmt->bindValue(":token", $token);

        // Optional placeholders
        if ($_POST["website"]) {
            $stmt->bindValue(":website", $_POST["website"]);
        }

        if (!$stmt->execute()) {
            $error = \i18n("Failed to register user");
            print_r($stmt->errorInfo());
            print "$query<br />\n";
        } else {
            // TODO : send mail
            if (!mail($email, \i18n("Pasteque registration"), \i18n("Hello, bla bla bla, go there, bla bla, token ! $token !"))) {
                $error = \i18n("Failed to send the registration email");
            } else {
                $message = \i18n("A confirmation email has been sent to your address - $token !");
            }
        }
    } else {
        $error = implode("<br />\n", $errors);
    }
}

?>
<h1><?php \pi18n("Register a new account", PLUGIN_NAME); ?></h1>

<br />

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" method="POST" action="<?php echo \Pasteque\get_current_url(); ?>">
    <input type="hidden" name="submit_request" value="1" />
    <label for="login"><?php \pi18n("login", PLUGIN_NAME); ?></label> <input type="text" name="login" /><br />
    <label for="first_name"><?php \pi18n("first_name", PLUGIN_NAME); ?></label> <input type="text" name="first_name" /><br />
    <label for="last_name"><?php \pi18n("last_name", PLUGIN_NAME); ?></label> <input type="text" name="last_name" /><br />
    <label for="email"><?php \pi18n("email", PLUGIN_NAME); ?></label> <input type="text" name="email" /><br />
    <label for="website"><?php \pi18n("website", PLUGIN_NAME); ?></label> <input type="text" name="website" /><br />
    <input type="checkbox" style="width:auto; margin: 5px; margin-left: 27%;" name="accept_contract"><?php \pi18n("contract", PLUGIN_NAME); ?></input><br />
    <input type="checkbox" style="width:auto; margin: 5px; margin-left: 27%;" name="usage_coffeeshop"><?php \pi18n("usage_coffeeshop", PLUGIN_NAME); ?></input><br />
    <input type="checkbox" style="width:auto; margin: 5px; margin-left: 27%;" name="subscribe_newsletter"><?php \pi18n("subscribe_newsletter", PLUGIN_NAME); ?></input><br />
    <label for="password"><?php \pi18n("password", PLUGIN_NAME); ?></label> <input type="password" name="password" /><br />
    <label for="confirm_password"><?php \pi18n("confirm_password", PLUGIN_NAME); ?></label> <input type="password" name="confirm_password" /><br />
    <input style="margin:15px; width:auto; margin-left: 27%;" type="submit" />
</form>

