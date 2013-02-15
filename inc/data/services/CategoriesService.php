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

class CategoriesService {

    private static function buildDBCat($db_cat) {
        return Category::__build($db_cat['id'], $db_cat['parent_id'],
                                 $db_cat['name']);
    }

    static function getAll() {
        $cats = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM categories";
        foreach ($pdo->query($sql) as $db_cat) {
            $cat = CategoriesService::buildDBCat($db_cat);
            $cats[] = $cat;
        }
        return $cats;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($stmt->execute()) {
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
        $stmt = $pdo->prepare('UPDATE categories SET name = :name, '
                . 'parent_id = :pid WHERE id = :id');
        $stmt->bindParam(":name", $cat->label, \PDO::PARAM_STR);
        $stmt->bindParam(":pid", $cat->parent_id, \PDO::PARAM_INT);
        $stmt->bindParam(":id", $cat->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    static function createCat($cat) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $stmt = $pdo->prepare('INSERT INTO categories (id, name, parent_id) '
                . 'VALUES (:id, :name, :pid)');
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        $stmt->bindParam(":name", $cat->label, \PDO::PARAM_STR);
        $stmt->bindParam(":pid", $cat->parent_id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    static function deleteCat($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

}

?>
