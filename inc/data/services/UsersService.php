<?php
//    POS-Tech API
//
//    Copyright (C) 2012 Scil (http://scil.coop)
//
//    This file is part of POS-Tech.
//
//    POS-Tech is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    POS-Tech is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with POS-Tech.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque;

class UsersService {

    private static function buildDBUser($db_user) {
        $role = RolesService::get($db_user['ROLE']);
        $user = User::__build($db_user['ID'], $db_user['NAME'],
                              $db_user['APPPASSWORD'], $role);
        return $user;
    }


    static function getAll() {
        $users = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM PEOPLE";
        foreach ($pdo->query($sql) as $db_user) {
            $user = UsersService::buildDBUser($db_user, $pdo);
            $users[] = $user;
        }
        return $users;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PEOPLE WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                return UsersService::buildDBUser($row, $pdo);
            }
        }
        return null;
    }

    static function update($user) {
        if ($user->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE PEOPLE SET NAME = :name, ROLE = :role";
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $user->name, \PDO::PARAM_STR);
        $stmt->bindParam(":role", $user->role->id, \PDO::PARAM_STR);
        $stmt->bindParam(":id", $user->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    static function create($user) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $sql = "INSERT INTO PEOPLE (ID, NAME, APPPASSWORD, CARD, ROLE, VISIBLE, IMAGE";
        $sql .= ") VALUES (:id, :name, NULL, NULL, :role, 1, NULL)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $user->name, \PDO::PARAM_STR);
        $stmt->bindParam(":role", $user->role->id, \PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($stmt->execute() !== FALSE) {
            return $id;
        } else {
            return FALSE;
        }
    }

    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $sql = "DELETE FROM PEOPLE WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>
