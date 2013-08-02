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

/** This is the basis for services. It defines standard procedures for simple
 * models available for override for more complex ones. */
abstract class AbstractService {

    protected static $dbTable;
    protected static $dbIdField;
    /** Associative array with db fields as key and model fields as values.
     * It must contains $dbIdField in keys.
     */
    protected static $fieldMapping;

    /** Build a model from raw database line. */
    protected abstract function build($dbRow, $pdo = null);

    /** Convert a model object to raw associative array for database.
     * The keys of the array are the same as the keys found in $fieldMapping.
     */
    protected function unbuild($model) {
        $ret = array();
        foreach (static::$fieldMapping as $field => $value) {
            $ret[$field] = $model->{$value};
        }
        return $ret;
    }

    /** Insert a new model in database. */
    public function create($model) {
        $dbData = static::unbuild($model);
        $pdo = PDOBuilder::getPDO();
        // Get all fields except id field
        $dbFields = array_keys(static::$fieldMapping); // Copy
        $idIndex = array_search(static::$dbIdField, $dbFields);
        if ($idIndex !== -1) {
            array_splice($dbFields, $idIndex, 1);
        }
        // Prepare sql query
        $sql = "INSERT INTO " . static::$dbTable . " ("
                . implode($dbFields, ", ") . ") VALUES (";
        // Set :field for each field for values and bind values for PDO
        foreach ($dbFields as $field) {
            $sql .= ":" . $field . ", ";
        }
        $sql = substr($sql, 0, -2);
        $sql .= ")";
        // Assign values to sql
        $stmt = $pdo->prepare($sql);
        foreach ($dbFields as $field) {
            $stmt->bindValue(":" . $field, $dbData[$field]);
            var_dump("bound " . $dbData[$field] . " to " . $field);
        }
        // RUN!
        if ($stmt->execute()) {
            return $pdo->lastInsertId();
        } else {
            var_dump($stmt->errorInfo());
            return false;
        }
    }

    /** Update a model. Returns true if success, false otherwise. */
    public function update($model) {
        $dbData = static::unbuild($model);
        var_dump($dbData);
        $pdo = PDOBuilder::getPDO();
        // Get all fields except id field
        $dbFields = array_keys(static::$fieldMapping); // Copy
        $idIndex = array_search(static::$dbIdField, $dbFields);
        if ($idIndex !== -1) {
            array_splice($dbFields, $idIndex, 1);
        }
        // Prepare sql query
        $sql = "UPDATE " . static::$dbTable . " SET ";
        foreach ($dbFields as $field) {
            $sql .= $field . " = :" . $field . ", ";
        }
        $sql = substr($sql, 0, -2);
        $sql .= " WHERE " . static::$dbIdField . " = :_id_";
        // Assign values to sql
        $stmt = $pdo->prepare($sql);
        foreach ($dbFields as $field) {
            $stmt->bindValue(":" . $field, $dbData[$field]);
        }
        $stmt->bindValue(":_id_", $dbData[static::$dbIdField]);
        var_dump("bound " . $dbData[static::$dbIdField] . " to : " . static::$dbIdField);
        // RUN!
        if ($stmt->execute()) {
            return true;
        } else {
            var_dump($stmt->errorInfo());
            return false;
        }
    }

    public function getAll() {
        $pdo = PDOBuilder::getPDO();
        var_dump(static::$dbTable);
        $stmt = $pdo->prepare("SELECT * FROM " . static::$dbTable);
        if ($stmt->execute()) {
            $ret = array();
            while ($row = $stmt->fetch()) {
                $ret[] = static::build($row, $pdo);
            }
            return $ret;
        } else {
            return false;
        }
    }

    public function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM " . static::$dbTable . " WHERE "
                . static::$dbIdField . " = :_id_");
        $stmt->bindParam(":_id_", $id);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return static::build($row, $pdo);
            }
        } else {
            return false;
        }
    }

    public function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM " . static::$dbTable. " WHERE "
                . static::idDbField . " = :_id_");
        $stmt->bindParam(":_id_", $id);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}

?>