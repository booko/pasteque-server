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

abstract class AbsReport {
    public abstract function run();
    public abstract function fetch();
}

class Report extends AbsReport {

    private $sql;
    private $params;
    private $filters;

    public function __construct($sql) {
        $this->sql = $sql;
        $this->params = array();
        $this->filters = array();
    }

    public function setParam($param, $value, $type = \PDO::PARAM_STR) {
        $this->params[$param] = array("value" => $value, "type" => $type);
    }

    public function run() {
        $pdo = PDOBuilder::getPDO();
        $this->stmt = $pdo->prepare($this->sql);
        foreach ($this->params as $key => $param) {
            $this->stmt->bindValue($key, $param['value'], $param['type']);
        }
        return $this->stmt->execute();
    }

    public function fetch() {
        $values = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        foreach($this->filters as $field => $filters) {
            if (isset($values[$field])) {
                foreach ($filters as $filter) {
                    $val = $filter($values[$field]);
                    $values[$field] = $val;
                }
            }
        }
        return $values;
    }

    public function addFilter($field, $function) {
        if (!isset($this->filters[$field])) {
            $this->filters[$field] = array();
        }
        $this->filters[$field][] = $function;
    }
}

$REPORTS = array();

function register_report($name, $report, $fields, $headers) {
    global $REPORTS;
    $REPORTS[$name] = array("report" => $report, "fields" => $fields,
            "headers" => $headers);
}
function get_report($name) {
    global $REPORTS;
    if (isset($REPORTS[$name])) {
        return $REPORTS[$name];
    } else {
        return NULL;
    }
}
?>