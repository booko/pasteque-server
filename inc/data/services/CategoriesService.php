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
                $db_cat['NAME'], $db_cat['IMAGE'] !== null,
                $db_cat['DISPORDER']);
    }

    static function getAll() {
        $cats = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM CATEGORIES ORDER BY DISPORDER ASC, NAME ASC";
        $data = $pdo->query($sql);
        if ($data !== FALSE) {
            foreach ($pdo->query($sql) as $db_cat) {
                $cat = CategoriesService::buildDBCat($db_cat);
                $cats[] = $cat;
            }
        }
        return $cats;
    }

    static function getChildren($parentId) {
        $cats = array();
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM CATEGORIES "
                . "WHERE PARENTID = :pid "
                . "ORDER BY DISPORDER ASC, NAME ASC");
        $stmt->bindParam(":pid", $parentId);
        if ($stmt->execute() !== false) {
            while ($row = $stmt->fetch()) {
                $cat = CategoriesService::buildDBCat($row);
                $cats[] = $cat;
            }
        }
        return $cats;
    }

    static function getByName($name) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM CATEGORIES WHERE NAME = :name");
        $stmt->bindParam(":name", $name, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                $cat = CategoriesService::buildDBCat($row);
                return $cat;
            }
        }
        return null;
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
    
    static function getImage($id) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT IMAGE FROM CATEGORIES WHERE ID = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return $db->readBin($row['IMAGE']);
            }
        }
        return null;
    }

    static function updateCat($cat, $image = "") {
        if ($cat->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE CATEGORIES SET NAME = :name, PARENTID = :pid, "
                . "DISPORDER = :order";
        if ($image !== "") {
            $sql .= ", IMAGE = :img";
        }
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $cat->label);
        $stmt->bindParam(":pid", $cat->parentId);
        $stmt->bindParam(":id", $cat->id);
        $stmt->bindParam(":order", $cat->dispOrder);
        if ($image !== "") {
            $stmt->bindParam(":img", $image, \PDO::PARAM_LOB);
        }
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
        }
    }

    static function createCat($cat, $image = null) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $sql = "INSERT INTO CATEGORIES (ID, NAME, PARENTID, DISPORDER, IMAGE) "
                . "VALUES (:id, :name, :pid, :order, :img)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $cat->label);
        $stmt->bindParam(":pid", $cat->parentId);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":order", $cat->dispOrder);
        $stmt->bindParam(":img", $image, \PDO::PARAM_LOB);
        if ($stmt->execute() !== false) {
            return $id;
        } else {
            return false;
        }
    }

    static function deleteCat($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare('DELETE FROM CATEGORIES WHERE ID = :id');
        return $stmt->execute(array(':id' => $id));
    }

}