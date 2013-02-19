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

const ATTRDEF_STRING = "str";
const ATTRDEF_INT = "int";
const ATTRDEF_DOUBLE = "double";
const ATTRDEF_DATE = "date";
const ATTRDEF_SINGLEREL = "srel"; // one relationnal model
const ATTRDEF_MULTREL = "mrel"; // multiple relational model (array)

class AttributeDef {

    protected $name;
    protected $type;
    protected $required;
    protected $relModel;

    public function __construct($name, $type, $args = array()) {
        $this->name = $name;
        $this->type = $type;
        foreach($args as $key => $value) {
            switch ($key) {
            case 'required':
                $this->required = $value;
                break;
            case 'model': // Model for rel field
                $this->relModel = $value;
                break;
            }
        }
    }

    public function getName() { return $this->name; }
    public function getType() { return $this->type; }
    public function isRequired() { return $this->required; }
    public function getRelModel() { return $this->relModel; }
}

class ExtAttributeDef extends AttributeDef {

    protected $table;

    public function __construct($name, $type, $table, $args = array()) {
        parent::__construct($name, $type, $args);
        $this->table = $table;
    }

    public function getTable() { return $this->table; }
}

/** This is the base class for business models.
 * Id and name are mandatory attributes.
 */
class ModelDef {

    private $className;
    private $attributes;
    private $extAttr;
    /** Multi rel attributes */
    private $mrelAttr;

    public function __construct($className) {
        $this->className = $className;
        $this->attributes = array();
        $this->extAttr = array();
        $this->mrelAttr = array();
    }

    public function addAttribute($name, $type, $args = array()) {
        $attribute = new AttributeDef($name, $type, $args);
        if ($attribute->getType() == ATTRDEF_MULTREL) {
            $this->mrelAttr[] = $attribute;
        } else {
            $this->attributes[] = $attribute;
        }
    }

    public function addExtAttr($name, $type, $table, $args = array()) {
        $extAttribute = new ExtAttribute($name, $type, $table, $args);
        if ($extAttribute->getType() == ATTRDEF_MULTREL) {
            $this->mrelAttr[] = $extAttribute;
            return;
        }
        $table = $extAttribute->getTable();
        if (!isset($this->extAttrs[$table])) {
            $this->extAttr[$table] = array();
        }
        $this->extAttr[$table][] = $extAttribute;
    }

    /** Check an array of data to be used as a model instance. */
    public function checkForm(&$f) {
        foreach ($this->attributes as $attribute) {
            if ($attribute->isRequired() && !isset($f[$attribute->getName()])) {
                return FALSE;
            }
        }
        // Remove the dummy form entry for MULTREL attributes
        // Must include it to send the value for an empty array
        foreach ($this->mrelAttr as $attribute) {
            if (isset($f[$attribute->getName()])) {
                $idx = array_search("dummy", $f[$attribute->getName()]);
                if ($idx !== FALSE) {
                    array_splice($f[$attribute->getName()], $idx, 1);
                }
            }
        }
        return TRUE;
    }

    public function getName() { return $this->className; }
    public function getAttrs() { return $this->attributes; }
    public function getExtTables() { return array_keys($this->extAttr); }
    public function getExtAttrs($table) { return $this->extAttr[$table]; }
    public function getMultirelAttrs() { return $this->mrelAttr; }
}

class MRel {
    public static function table($modelDef, $attr) {
        return $modelDef->getName() . "_" . $attr->getRelModel();
    }
    public static function srcField($modelDef) {
        return $modelDef->getName() . "_id";
    }
    public static function destField($attr) {
        return $attr->getRelModel() . "_id";
    }
}

class ModelFactory {

    private static $defs = array();

    /** Register a new model definition.
     * @param $modelName {string} The name of the model.
     * @param $baseTable {string} The table in the database to hold data.
     * @return NULL if already registered, the ModelDef if success.
     */
    public static function register($modelName) {
        if (!isset(ModelFactory::$defs[$modelName])) {
            $def = new ModelDef($modelName);
            ModelFactory::$defs[$modelName] = $def;
            return $def;
        }
        return NULL;
    }

    public static function get($modelName) {
        if (isset(ModelFactory::$defs[$modelName])) {
            return ModelFactory::$defs[$modelName];
        }
        return NULL;
    }
}
