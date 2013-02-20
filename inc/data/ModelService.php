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

/** Results of a search to fetch. */
class ModelFetch {

    private $modelName;
    private $stmt;
    private $fields;

    public function __construct($modelName, $stmt, $fields) {
        $this->modelName = $modelName;
        $this->stmt = $stmt;
        $this->fields = $fields;
    }

    public function fetch() {
        $def = ModelFactory::get($this->modelName);
        $pdo = PDOBuilder::getPDO();
        $data = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        if ($data === FALSE) {
            return NULL;
        }
        // Multirel fields
        foreach ($def->getMultirelAttrs() as $attr) {
            if ($this->fields !== NULL
                    && array_search($attr->getName(), $this->fields) === FALSE) {
                // Not requested
                continue;
            }
            $table = MRel::table($def, $attr);
            $rel = $attr->getRelModel();
            $srcField = MRel::srcField($def);
            $dstField = MRel::destField($attr);
            $rsql = "SELECT " . $rel . ".id as id, " . $rel . ".name as name "
                    . "FROM " . $table . ", " . $rel . " WHERE "
                    . $table . "." . $srcField . " = " . intval($data['id'])
                    . " AND " . $table . "." . $dstField . " = " . $rel . ".id";
            $relData = $pdo->query($rsql);
            if ($relData !== FALSE) {
                $data[$attr->getName()] = $relData->fetchAll(\PDO::FETCH_ASSOC);
            }
        }
        // Singlerel fields
        foreach ($def->getAttrs() as $attr) {
            if ($attr->getType() == ATTRDEF_SINGLEREL) {
                $name = $attr->getName();
                $data[$name] = array('id' => $data[$name],
                        'name' => $data[$name . "_name"]);
                unset($data[$name . "_name"]);
            }
        }
        return $data;
    }

    public function fetchAll() {
        $ret = array();
        while ($data = $this->fetch()) {
            $ret[] = $data;
        }
        return $ret;
    }
}

class ModelService {

    public static function search($name, $fields = NULL, $args = NULL,
            $start = 0, $limit = NULL) {
        $def = ModelFactory::get($name);
        if ($def == NULL) {
            return NULL;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT " . ModelService::selectFields($def, $fields);
        $sql .= " FROM " . ModelService::selectTables($def, $fields);
        // Search
        if ($args !== NULL) {
            $sql .= " WHERE ";
            for ($i = 0; $i < count($args); $i += 3) {
                $sql .= $args[$i] . " " . $args[$i + 1] . " " . $args[$i + 2];
                $sql .= " AND ";
            }
            $sql = substr($sql, 0, -5);
        }
        // Limit
        if ($limit != NULL) {
            $sql .= "LIMIT " . intval($start) . "," . intval($limit);
        }
        $data = $pdo->query($sql);
        if ($data !== FALSE) {
            return new ModelFetch($name, $data, $fields);
        } else {
            return NULL;
        }
    }

    /** Get a single or multiple data.
     * @param $name {string} Model name
     * @param $id {mixed} int or array, ids to get
     * @param $fields {array} Fields to get
     */
    public static function get($name, $id, $fields = NULL) {
        $def = ModelFactory::get($name);
        if ($def == NULL) {
            return NULL;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT ";
        // Fields
        $sql .= ModelService::selectFields($def, $fields);
        // Tables
        $sql .= " FROM " . $def->getName();
        foreach ($def->getExtTables() as $table) {
            $sql .= "LEFT JOIN " . $table . " ON " . $table . ".rel_id = "
                    . $def->getName() . ".id ";
        }
        // Where
        if (is_array($id)) {
            $sql .= " WHERE " . $def->getName() . ".id IN (";
            foreach ($id as $i) {
                $sql .= $i . ", ";
            }
            $sql = substr($sql, 0, -2);
            $sql .= ")";
        } else {
            $sql .= " WHERE " . $def->getName() . ".id = " . intval($id);
        }
        $fetch = $pdo->query($sql);
        if ($fetch !== FALSE) {
            $data = new ModelFetch($name, $fetch, $fields);
            if (is_array($id)) {
                return $data;
            } else {
                return $data->fetch();
            }
        } else {
            return NULL;
        }
    }

    public static function delete($name, $id) {
        $def = ModelFactory::get($name);
        if ($def == NULL) {
            return NULL;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "DELETE FROM " . $def->getName() . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function update($name, $values) {
        $def = ModelFactory::get($name);
        if ($def == NULL) {
            return NULL;
        }
        $pdo = PDOBuilder::getPDO();
        // Update main table
        $attrs = array();
        $sql = "UPDATE " . $def->getName() . " SET ";
        foreach ($def->getAttrs() as $attr) {
            // Check attribute in values to build sql
            if (isset($values[$attr->getName()])) {
                $sql .= $attr->getName() . " = :" . $attr->getName() . ", ";
                $attrs[] = $attr;
            }
        }
        $sql = substr($sql, 0, -2);
        $sql .= " WHERE id = :id";
        // Bind params
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $values['id'], \PDO::PARAM_INT);
        foreach ($attrs as $attr) {
            $stmt->bindParam(':' . $attr->getName(), $values[$attr->getName()],
                    ModelService::PDOType($attr->getType()));
        }
        $stmt->execute();
        // Update extension tables
        foreach ($def->getExtTables() as $extTable) {
            $extSql = "UPDATE " . $extTable . " SET ";
            $extAttrs = array();
            foreach ($def->getExtAttrs($extTable) as $extAttr) {
                $extName = $extAttr->getName();
                if (isset($values[$extName])) {
                    $extSql = $extName . " = :" . $extName . ", ";
                    $extAttrs[] = $extAttr;
                }
            }
            $extSql = substr($extSql, 0, -2);
            $extSql = " WHERE rel_id = :id";
            $extStmt = $pdo->prepare($extSql);
            $extStmt->bindParam(":id", $values['id'], \PDO::PARAM_INT);
            foreach ($extAttrs as $extAttr) {
                $extName = $extAttr->getName();
                $extStmt->bindParam(':' . $extName, $values[$extName],
                        ModelService::PDOType($extAttr->getType()));
            }
            $extStmt->execute();
        }
        // Update multi rel tables
        foreach ($def->getMultirelAttrs() as $attr) {
            $relName = $attr->getName();
            if (isset($values[$relName])) {
                $table = MRel::table($def, $attr);
                $srcField = MRel::srcField($def);
                $dstField = MRel::destField($attr);
                // Delete old values
                $delete = "DELETE FROM " . $table . " WHERE " . $srcField
                        . " = :id";
                $delStmt = $pdo->prepare($delete);
                $delStmt->bindParam(":id", $values['id']);
                $delStmt->execute();
                // Insert new ones
                if (count($values[$relName]) == 0) {
                    continue;
                }
                $insert = "INSERT INTO " . $table . " (" . $srcField . ", "
                        . $dstField . ") VALUES ";
                for ($i = 0; $i < count($values[$relName]); $i++) {
                    $insert .= "(:src, :dst$i), ";
                }
                $insert = substr($insert, 0, -2);
                $insStmt = $pdo->prepare($insert);
                $insStmt->bindParam(":src", $values['id'], \PDO::PARAM_INT);
                for ($i = 0; $i < count($values[$relName]); $i++) {
                    $insStmt->bindParam(":dst$i", $values[$relName][$i],
                            \PDO::PARAM_INT);
                }
                $insStmt->execute();
            }
        }
    }

    public static function create($name, $values) {
        $def = ModelFactory::get($name);
        if ($def == NULL) {
            return NULL;
        }
        $pdo = PDOBuilder::getPDO();
        // Insert main table
        $attrs = array();
        $sql = "INSERT INTO " . $def->getName() . " (name";
        foreach ($def->getAttrs() as $attr) {
            // Check attribute in values to build sql
            if (isset($values[$attr->getName()])) {
                $sql .= ", " . $attr->getName();
                $attrs[] = $attr;
            }
        }
        $sql .= ") VALUES (:name";
        foreach ($attrs as $attr) {
            $sql .= ", :" . $attr->getName();
        }
        $sql .= ")";
        // Bind params
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $values['name'], \PDO::PARAM_STR);
        foreach ($attrs as $attr) {
            $stmt->bindParam(':' . $attr->getName(), $values[$attr->getName()],
                    ModelService::PDOType($attr->getType()));
        }
        $stmt->execute();
        $id = $pdo->lastInsertId();
        // Insert extension tables
        foreach ($def->getExtTables() as $extTable) {
            $extSql = "INSERT INTO " . $extTable . " (rel_id";
            $extAttrs = array();
            foreach ($def->getExtAttrs($extTable) as $extAttr) {
                $extName = $extAttr->getName();
                if (isset($values[$extName])) {
                    $extSql .= ", :" . $extName;
                    $extAttrs[] = $extAttr;
                }
            }
            $extSql .= ") VALUES (:rel_id, ";
            foreach ($extAttrs as $attr) {
                $sql .= ", :" . $attr->getName;
            }
            $extSql .= ")";
            $extStmt = $pdo->prepare($extSql);
            $extStmt->bindParam(":rel_id", $id, \PDO::PARAM_INT);
            foreach ($extAttrs as $extAttr) {
                $extName = $extAttr->getName();
                $extStmt->bindParam(':' . $extName, $values[$extName],
                        ModelService::PDOType($extAttr->getType()));
            }
            $extStmt->execute();
        }
        // INSERT multi rel tables
        foreach ($def->getMultirelAttrs() as $attr) {
            $relName = $attr->getName();
            if (isset($values[$relName])) {
                $table = MRel::table($def, $attr);
                $srcField = MRel::srcField($def);
                $dstField = MRel::destField($attr);
                if (count($values[$relName]) == 0) {
                    continue;
                }
                $insert = "INSERT INTO " . $table . " (" . $srcField . ", "
                        . $dstField . ") VALUES ";
                for ($i = 0; $i < count($values[$relName]); $i++) {
                    $insert .= "(:src, :dst$i), ";
                }
                $insert = substr($insert, 0, -2);
                $insStmt = $pdo->prepare($insert);
                $insStmt->bindParam(":src", $id, \PDO::PARAM_INT);
                for ($i = 0; $i < count($values[$relName]); $i++) {
                    $insStmt->bindParam(":dst$i", $values[$relName][$i],
                            \PDO::PARAM_INT);
                }
                $insStmt->execute();
            }
        }
        return TRUE;
    }

    private static function PDOType($attrType) {
        switch ($attrType) {
            case ATTRDEF_INT:
            case ATTRDEF_SINGLEREL:
                return \PDO::PARAM_INT;
                break;
            case ATTRDEF_STRING:
            case ATTRDEF_DOUBLE:
            case ATTRDEF_DATE:
                return \PDO::PARAM_STR;
                break;
            default:
                return \PDO::PARAM_STR;
            }
    }

    private static function selectFields($def, $fields) {
        if ($fields == NULL) {
            $selfTbl = $def->getName() . ".";
            $sql = $selfTbl . "id, " . $selfTbl . "name, ";
            foreach ($def->getAttrs() as $attr) {
                $sql .= $selfTbl . ModelService::attrField($attr);
                $sql .= ", ";
            }
            $sql = substr($sql, 0, -2);
        } else {
            $sql = "";
            foreach ($fields as $field) {
                $sql .= $field . ", ";
            }
            $sql = substr($field, 0, -1);
        }
        return $sql;
    }

    private static function attrField($attr) {
        switch ($attr->getType()) {
            case ATTRDEF_INT:
            case ATTRDEF_STRING:
            case ATTRDEF_DOUBLE:
            case ATTRDEF_DATE:
                return $attr->getName();
            case ATTRDEF_SINGLEREL:
                // Use model_rel as table name for self relationship
                return $attr->getName() . ", " . $attr->getRelModel()
                        . "_rel.name as " . $attr->getName() . "_name";
            default:
                return $attr->getName();
        }
    }

    private static function selectTables($def, $fields) {
        $sql = $def->getName();
        foreach ($def->getAttrs() as $attr) {
            if ($attr->getType() == ATTRDEF_SINGLEREL) {
                $selfTbl = $def->getName() . ".";
                // Use model_rel as table name for self relationship
                $sql .= " LEFT JOIN " . $attr->getRelModel() . " "
                        . $attr->getRelModel() . "_rel ON "
                        . $selfTbl . $attr->getName() . " = "
                        . $attr->getRelModel() . "_rel.id";
            }
        }
        foreach ($def->getExtTables() as $table) {
            $sql .= " LEFT JOIN " . $table . " ON " . $table . ".rel_id = "
                    . $def->getName() . ".id ";
        }
        return $sql;
    }
}
