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

class ResourcesService {

    private static function buildDBRes($db_res) {
        $res = Resource::__build($db_res['ID'], $db_res['NAME'],
                $db_res['RESTYPE'], $db_res['CONTENT']);
        return $res;
    }


    static function getAll() {
        $res = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM RESOURCES";
        foreach ($pdo->query($sql) as $db_res) {
            $r = ResourcesService::buildDBRes($db_res);
            $res[] = $r;
        }
        return $res;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM RESOURCES WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                return ResourcesService::buildDBRes($row);
            }
        }
        return NULL;
    }

    static function update($res) {
        if ($res->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE RESOURCES SET NAME = :name, RESTYPE = :type, CONTENT = :content";
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $res->name, \PDO::PARAM_STR);
        $stmt->bindParam(":type", $res->type, \PDO::PARAM_INT);
        $stmt->bindParam(":content", $res->content, \PDO::PARAM_LOB);
        $stmt->bindParam(":id", $res->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    static function create($res) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $sql = "INSERT INTO RESOURCES (ID, NAME, RESTYPE, CONTENT";
        $sql .= ") VALUES (:id, :name, :type, :content)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $res->name, \PDO::PARAM_STR);
        $stmt->bindParam(":type", $res->type, \PDO::PARAM_INT);
        $stmt->bindParam(":content", $res->content, \PDO::PARAM_LOB);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($stmt->execute() !== FALSE) {
            return $id;
        } else {
            return FALSE;
        }
    }

    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $sql = "DELETE FROM RESOURCES WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>
