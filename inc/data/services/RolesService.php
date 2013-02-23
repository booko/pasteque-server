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

class RolesService {

    private static function buildDBRole($db_role) {
        $role = Role::__build($db_role['ID'], $db_role['NAME'],
                              $db_role['PERMISSIONS']);
        return $role;
    }


    static function getAll() {
        $roles = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM ROLES";
        foreach ($pdo->query($sql) as $db_role) {
            $role = RolesService::buildDBRole($db_role, $pdo);
            $roles[] = $role;
        }
        return $roles;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM ROLES WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                return RolesService::buildDBRole($row, $pdo);
            }
        }
        return null;
    }

    static function update($role) {
        if ($role->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE ROLES SET NAME = :name, PERMISSIONS = :permissions";
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $role->name, \PDO::PARAM_STR);
        $stmt->bindParam(":permissions", $role->permissions, \PDO::PARAM_LOB);
        $stmt->bindParam(":id", $role->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    static function create($role) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $sql = "INSERT INTO ROLES (ID, NAME, PERMISSIONS";
        $sql .= ") VALUES (:id, :name, :permissions)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $role->name, \PDO::PARAM_STR);
        $stmt->bindParam(":permissions", $role->permissions, \PDO::PARAM_LOB);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($stmt->execute() !== FALSE) {
            return $id;
        } else {
            return FALSE;
        }
    }

    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $sql = "DELETE FROM ROLES WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>
