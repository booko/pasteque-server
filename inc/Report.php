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

class Report {

    const TOTAL_SUM = "sum";
    const TOTAL_AVG = "average";

    private $sql;
    public $headers;
    public $fields;
    private $params;
    private $filters;
    private $grouping;
    private $subtotals;
    private $totals;

    public function __construct($sql, $headers, $fields) {
        $this->sql = $sql;
        $this->headers = $headers;
        $this->fields = $fields;
        $this->params = array();
        $this->filters = array();
        $this->grouping = NULL;
        $this->subtotals = array();
        $this->totals = array();
    }

    public function setParam($param, $value, $type = \PDO::PARAM_STR) {
        $this->params[$param] = array("value" => $value, "type" => $type);
    }

    public function getParams() {
        return $this->params;
    }

    public function run() {
        return new ReportRun($this);
    }

    public function getSql() {
        return $this->sql;
    }

    public function addFilter($field, $function) {
        if (!isset($this->filters[$field])) {
            $this->filters[$field] = array();
        }
        $this->filters[$field][] = $function;
    }
    public function getFilters() {
        return $this->filters;
    }

    public function setGrouping($field) {
        $this->grouping = $field;
    }
    public function isGrouping() {
        return $this->grouping !== NULL;
    }
    public function getGrouping() {
        return $this->grouping;
    }

    public function addSubtotal($field, $type) {
        if ($type === NULL && isset($this->subtotals[$field])) {
            unset($this->subtotals[$field]);
        } else {
            $this->subtotals[$field] = $type;
        }
    }

    public function getSubtotals() {
        return $this->subtotals;
    }
    public function hasSubtotals() {
        return count($this->subtotals) > 0;
    }

    public function addTotal($field, $type) {
        if ($type === NULL && isset($this->totals[$field])) {
            unset($this->totals[$field]);
        } else {
            $this->totals[$field] = $type;
        }
    }

    public function getTotals() {
        return $this->totals;
    }
    public function hasTotals() {
        return count($this->totals) > 0;
    }
}

class ReportRun {

    private $report;
    public $subtotals;
    public $totals;
    private $tmpSubtotals;
    private $tmpTotals;
    private $groupRowCount;
    private $totalRowCount;
    private $currentGroup;
    private $groupStart;
    private $groupStop;

    public function __construct($report) {
        $this->report = $report;
        $pdo = PDOBuilder::getPDO();
        $this->stmt = $pdo->prepare($report->getSql());
        foreach ($report->getParams() as $key => $param) {
            $this->stmt->bindValue($key, $param['value'], $param['type']);
        }
        $this->groupRowCount = 0;
        $this->totalRowCount = 0;
        $this->resetTmpSubtotals();
        $this->tmpTotals = array();
        $this->totals = array();
        $this->subtotals = array();
        foreach ($this->report->getTotals() as $field => $type) {
            $this->tmpTotals[$field] = 0;
        }
        $this->currentGroup = NULL;
        $this->stmt->execute();
    }

    private function resetTmpSubtotals() {
        $this->tmpSubtotals = array();
        foreach ($this->report->getSubtotals() as $field => $type) {
            $this->tmpSubtotals[$field] = 0;
        }
    }

    private function computeTotals($source, $tmp, $count) {
        $dest = array();
        foreach ($source as $field => $type) {
            switch ($type) {
            case Report::TOTAL_SUM:
                $dest[$field] = $tmp[$field];
                break;
            case Report::TOTAL_AVG:
                $dest[$field] = $tmp[$field] / $count;
                break;
            }
        }
        return $dest;
    }

    private function applyFilters($values) {
        if (!is_array($values)) {
            return $values;
        }
        $ret = array();
        foreach ($values as $field => $value) {
            $ret[$field] = $value;
        }
        foreach ($this->report->getFilters() as $field => $filters) {
            if (isset($ret[$field])) {
                foreach ($filters as $filter) {
                    $val = $filter($ret[$field]);
                    $ret[$field] = $val;
                }
            }
        }
        return $ret;
    }

    public function fetch() {
        $values = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        // Check for group change
        if ($this->report->isGrouping() && $values !== FALSE) {
            $group = $this->report->getGrouping();
            if ($this->currentGroup === NULL) {
                // First row
                $this->currentGroup = $values[$group];
                $this->groupStop = FALSE;
                $this->groupStart = TRUE;
            } else {
                if ($this->currentGroup != $values[$group]) {
                    // Group changed, set subtotals and reinit counts
                    $this->currentGroup = $values[$group];
                    $this->groupStop = TRUE;
                    $this->groupStart = TRUE;
                    $this->subtotals = $this->computeTotals($this->report->getSubtotals(),
                            $this->tmpSubtotals, $this->groupRowCount);
                    $this->subtotals = $this->applyFilters($this->subtotals);
                    $this->resetTmpSubTotals();
                    $this->groupRowCount = 0; // will be incremented to 1
                } else {
                    $this->groupStop = FALSE;
                    $this->groupStart = FALSE;
                }
            }
        }
        // Add values
        if ($values !== FALSE) {
            $this->groupRowCount++;
            $this->totalRowCount++;
            foreach ($this->report->getTotals() as $field => $type) {
                if (isset($values[$field])) {
                    $this->tmpTotals[$field] += $values[$field];
                }
            }
            if ($this->report->isGrouping()) {
                foreach ($this->report->getSubtotals() as $field => $type) {
                    $this->tmpSubtotals[$field] += $values[$field];
                }
            }
        } else {
            // End, set totals and last group subtotals
            $this->subtotals = $this->computeTotals($this->report->getSubtotals(),
                    $this->tmpSubtotals, $this->groupRowCount);
            $this->subtotals = $this->applyFilters($this->subtotals);
            $this->totals = $this->computeTotals($this->report->getTotals(),
                    $this->tmpTotals, $this->totalRowCount);
            $this->totals = $this->applyFilters($this->totals);
            $this->groupStop = TRUE;
        }
        // Apply filters
        $values = $this->applyFilters($values);
        return $values;
    }

    /** The group has changed. Check $subtotals for group total. */
    public function isGroupEnd() {
        return $this->groupStop;
    }

    public function isGroupStart() {
        return $this->groupStart;
    }
    public function getCurrentGroup() {
        return $this->currentGroup;
    }
}

$REPORTS = array();

function register_report($module, $name, $report) {
    global $REPORTS;
    $REPORTS[$module . ":" . $name] = $report;
}
function get_report($module, $name) {
    report_content($module, $name);
    global $REPORTS;
    if (isset($REPORTS[$module . ":" . $name])) {
        return $REPORTS[$module . ":" . $name];
    } else {
        return NULL;
    }
}
?>
