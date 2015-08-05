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

class ResourcesService extends AbstractService {

    protected static $dbTable = "RESOURCES";
    protected static $dbIdField = "ID";
    protected static $fieldMapping = array(
            "ID" => "id",
            "NAME" => "label",
            "RESTYPE" => "type",
            "CONTENT" => array("type" => DB::BIN, "attr" =>"content")
    );

    protected function build($dbRow, $pdo = null) {
        $db = DB::get();
        return Resource::__build($dbRow['ID'], $dbRow['NAME'],
                $dbRow['RESTYPE'], $db->readBin($dbRow['CONTENT']));
    }

    public function create($model) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO RESOURCES "
                . "(ID, NAME, RESTYPE, CONTENT) VALUES "
                . "(:id, :label, :type, :content)");
        $id = md5(time() . rand());
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":label", $model->label);
        $stmt->bindParam(":type", $model->type);
        $stmt->bindParam(":content", $model->content, \PDO::PARAM_LOB);
        if ($stmt->execute() !== false) {
            return $id;
        } else {
            return false;
        }
    }

    public function getAll() {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM RESOURCES ORDER BY NAME ASC");
        if($stmt->execute()) {
            $ret = array();
            while($row = $stmt->fetch()) {
                $ret[] = $this->build($row,$pdo);
            }
            return $ret;
        }
        return null;
    }

    public function getImage($label) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT CONTENT FROM RESOURCES WHERE NAME = :label");
        $stmt->bindParam(":label", $label, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return $db->readBin($row['CONTENT']);
            }
        }
        return null;
    }
}
