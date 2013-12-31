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

class AttributesService {

    private static function buildSet($dbSet, $pdo) {
        $set = AttributeSet::__build($dbSet['ID'], $dbSet['NAME']);
        $stmt = $pdo->prepare("SELECT * FROM ATTRIBUTEUSE "
                . "WHERE ATTRIBUTESET_ID = :id");
        $stmt->bindParam(":id", $set->id);
        $stmt->execute();
        while ($dbUse = $stmt->fetch()) {
            $stmtAttr = $pdo->prepare("SELECT * FROM ATTRIBUTE "
                    . "WHERE ID = :id");
            $stmtAttr->bindParam(":id", $dbUse['ATTRIBUTE_ID']);
            $stmtAttr->execute();
            while ($dbAttr = $stmtAttr->fetch()) {
                $attribute = AttributesService::buildDBAttr($dbAttr, $pdo);
                $set->addAttribute($attribute, $dbUse['LINENO']);
            }
        }
        return $set;
    }

    private static function buildDBAttr($db_attr, $pdo) {
        $attr = Attribute::__build($db_attr['ID'], $db_attr['NAME']);
        $valstmt = $pdo->prepare("SELECT * FROM ATTRIBUTEVALUE WHERE "
                                 . "ATTRIBUTE_ID = :id ORDER BY VALUE");
        $valstmt->execute(array(':id' => $db_attr['ID']));
        while ($db_val = $valstmt->fetch()) {
            $val = AttributeValue::__build($db_val['ID'], $db_val['VALUE']);
            $attr->addValue($val);
        }
        return $attr;
    }

    static function getAll() {
        $pdo = PDOBuilder::getPDO();
        $attrs = array();
        $sql = "SELECT * FROM ATTRIBUTESET";
        foreach ($pdo->query($sql) as $dbSet) {
            $attr = AttributesService::buildSet($dbSet, $pdo);
            $attrs[] = $attr;
        }
        return $attrs;
    }

    static function getAllAttrs() {
        $pdo = PDOBuilder::getPDO();
        $attrs = array();
        $sql = "SELECT * FROM ATTRIBUTE";
        foreach ($pdo->query($sql) as $dbAttr) {
            $attr = AttributesService::buildDBAttr($dbAttr, $pdo);
            $attrs[] = $attr;
        }
        return $attrs;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM ATTRIBUTESET WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id)) !== false) {
            if ($row = $stmt->fetch()) {
                return AttributesService::buildSet($row, $pdo);
            }
        }
        return null;
    }

    static function createSet($set) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $stmt = $pdo->prepare("INSERT INTO ATTRIBUTESET (ID, NAME) VALUES "
                              . "(:id, :name)");
        if ($stmt->execute(array(':id' => $id, ':name' => $set->label))) {
            $set->id = $id;
            $stmtUse = $pdo->prepare("INSERT INTO ATTRIBUTEUSE "
                    . "(ID, ATTRIBUTESET_ID, ATTRIBUTE_ID) VALUES "
                    . "(:id, :setId, :attrId)");
            foreach ($set->attributes as $attr) {
                $stmtUse->bindParam(":id", md5(time() . rand()));
                $stmtUse->bindParam(":setId", $set->id);
                $stmtUse->bindParam(":attrId", $attr->id);
                $stmtUse->execute();
            }
            return true;
        } else {
            return false;
        }
    }

    static function updateSet($set) {
        if ($set->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("UPDATE ATTRIBUTESET SET NAME = :name "
                . "WHERE ID = :id");
        $stmt->bindParam(":id", $set->id);
        $stmt->bindParam(":name", $set->label);
        $stmtDel = $pdo->prepare("DELETE FROM ATTRIBUTEUSE "
                . "WHERE ATTRIBUTESET_ID = :id");
        $stmtDel->bindParam(":id", $set->id);
        $stmtDel->execute();
        $stmtUse = $pdo->prepare("INSERT INTO ATTRIBUTEUSE "
                . "(ID, ATTRIBUTESET_ID, ATTRIBUTE_ID) VALUES "
                . "(:id, :setId, :attrId)");
        foreach ($set->attributes as $attr) {
            $stmtUse->bindParam(":id", md5(time() . rand()));
            $stmtUse->bindParam(":setId", $set->id);
            $stmtUse->bindParam(":attrId", $attr->id);
            $stmtUse->execute();
        }
        return $stmt->execute();
    }

    static function deleteSet($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM ATTRIBUTESET WHERE ID = :id");
        return $stmt->execute(array(':id' => $id));    
    }

    static function getAttr($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM ATTRIBUTE WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id)) !== false) {
            if ($row = $stmt->fetch()) {
                return AttributesService::buildDBAttr($row, $pdo);
            }
        }
        return null;
    }

    static function createAttribute($attr) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $stmt = $pdo->prepare("INSERT INTO ATTRIBUTE (ID, NAME) VALUES "
                              . "(:id, :name)");
        if ($stmt->execute(array(':id' => $id, ':name' => $attr->label))) {
            $attr->id = $id;
            return true;
        } else {
            return false;
        }
    }

    static function deleteAttribute($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM ATTRIBUTE WHERE ID = :id");
        return $stmt->execute(array(':id' => $id));
    }

    static function updateAttribute($attr) {
        if ($attr->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("UPDATE ATTRIBUTE SET NAME = :name WHERE ID = :id");
        return $stmt->execute(array(':id' => $attr->id, ':name' => $attr->label));
    }

    static function createValue($value, $attr_id) {
        $id = md5(time() . rand());
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO ATTRIBUTEVALUE "
                              . "(ID, VALUE, ATTRIBUTE_ID) VALUES "
                              . "(:id, :value, :attr_id)");
        return $stmt->execute(array(':id' => $id, ':value' => $value->label,
                                    ':attr_id' => $attr_id));
    }

    static function deleteValue($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM ATTRIBUTEVALUE WHERE ID = :id");
        return $stmt->execute(array(':id' => $id));
    }

    static function updateValue($val) {
        if ($val->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("UPDATE ATTRIBUTEVALUE SET VALUE = :value "
                              . "WHERE ID = :id");
        return $stmt->execute(array(':id' => $val->id, ':value' => $val->label));
    }
}

?>