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

class UsersService {

    private static function buildDBUser($db_user, $pdo) {
        $stmt = $pdo->prepare("SELECT p.id FROM permissions as p, "
                . "user_permission as up "
                . "WHERE up.permission_id = p.id AND up.user_id = :user");
        $stmt->bindParam(":user", $db_user['id'], \PDO::PARAM_INT);
        $permissions = array();
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()) {
                $permissions[] = $row['id'];
            }
        }
        $user = User::__build($db_user['id'], $db_user['name'], $permissions);
        return $user;
    }


    static function getAll() {
        $users = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM users";
        foreach ($pdo->query($sql) as $db_user) {
            $user = UsersService::buildDBUser($db_user, $pdo);
            $users[] = $user;
        }
        return $users;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return UsersService::buildDBUser($row, $pdo);
            }
        }
        return NULL;
    }

    static function create($user) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO users (name) "
                . "VALUES (:name)");
        $stmt->bindParam(":name", $user->name, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            $id = $pdo->lastInsertId();
            foreach ($user->permissions as $perm) {
                $add = $pdo->prepare("INSERT INTO user_permission (user_id, "
                        . "permission_id) VALUES (:uid, :pid)");
                $add->bindParam(":uid", $id, \PDO::PARAM_INT);
                $add->bindParam(":pid", $perm, \PDO::PARAM_STR);
                $add->execute();
            }
            return TRUE;
        }
        return FALSE;
    }

    static function update($user) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("UPDATE users SET name = :name "
                . "WHERE id = :id");
        $stmt->bindParam(":id", $user->id, \PDO::PARAM_INT);
        $stmt->bindParam(":name", $user->name, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            $del = $pdo->prepare("DELETE FROM user_permission WHERE user_id = :id");
            $del->bindParam(":id", $user->id, \PDO::PARAM_INT);
            $del->execute();
            foreach ($user->permissions as $perm) {
                $add = $pdo->prepare("INSERT INTO user_permission (user_id, "
                        . "permission_id) VALUES (:uid, :pid)");
                $add->bindParam(":uid", $user->id, \PDO::PARAM_INT);
                $add->bindParam(":pid", $perm, \PDO::PARAM_STR);
                $add->execute();
            }
            return TRUE;
        }
        return FALSE;
    }

    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    static function getPermissions() {
        $permissions = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM permissions";
        foreach ($pdo->query($sql) as $perm) {
            $permissions[] = $perm['id'];
        }
        return $permissions;
    }
}

?>
