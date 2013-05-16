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

class CategoriesService {

    private static function buildDBCat($db_cat) {
        return Category::__build($db_cat['ID'], $db_cat['PARENTID'],
                                 $db_cat['NAME'], $db_cat['IMAGE']);
    }

    static function getAll() {
        $cats = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM CATEGORIES ORDER BY DISPORDER ASC, NAME ASC";
        foreach ($pdo->query($sql) as $db_cat) {
            $cat = CategoriesService::buildDBCat($db_cat);
            $cats[] = $cat;
        }
        return $cats;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM CATEGORIES WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                $cat = CategoriesService::buildDBCat($row);
                return $cat;
            }
        }
        return null;
    }

    static function updateCat($cat) {
        if ($cat->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE CATEGORIES SET NAME = :name, PARENTID = :pid";
        if ($cat->image !== "") {
            $sql .= ", IMAGE = :img";
        }
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $cat->label, \PDO::PARAM_STR);
        $stmt->bindParam(":pid", $cat->parent_id, \PDO::PARAM_INT);
        $stmt->bindParam(":id", $cat->id, \PDO::PARAM_INT);
        if ($cat->image !== "") {
            $stmt->bindParam(":img", $cat->image, \PDO::PARAM_LOB);
        }
        return $stmt->execute();
    }

    static function createCat($cat) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $sql = "INSERT INTO CATEGORIES (ID, NAME, PARENTID";
        if ($cat->image !== "") {
            $sql .= ", IMAGE";
        }
        $sql .= ") VALUES (:id, :name, :pid";
        if ($cat->image !== "") {
            $sql .= ", :img";
        }
        $sql .= ")";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $cat->label, \PDO::PARAM_STR);
        $stmt->bindParam(":pid", $cat->parent_id, \PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($cat->image !== "") {
            $stmt->bindParam(":img", $cat->image, \PDO::PARAM_LOB);
        }
        if ($stmt->execute() !== FALSE) {
            return $id;
        } else {
            return FALSE;
        }
    }

    static function deleteCat($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare('DELETE FROM CATEGORIES WHERE ID = :id');
        return $stmt->execute(array(':id' => $id));
    }

}

?>
