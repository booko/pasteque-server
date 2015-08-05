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

class CurrenciesService extends AbstractService {

    protected static $dbTable = "CURRENCIES";
    protected static $dbIdField = "ID";
    protected static $fieldMapping = array(
            "ID" => "id",
            "NAME" => "label",
            "SYMBOL" => "symbol",
            "DECIMALSEP" => "decimalSeparator",
            "THOUSANDSSEP" => "thousandsSeparator",
            "FORMAT" => "format",
            "RATE" => "rate",
            "MAIN" => array("attr" =>"isMain", "type" => DB::BOOL),
            "ACTIVE" => array("attr" => "isActive", "type" => DB::BOOL),
    );

    protected function build($row, $pdo = null) {
        $db = DB::get();
        return Currency::__build($row["ID"], $row["NAME"], $row["SYMBOL"],
                $row["DECIMALSEP"], $row["THOUSANDSSEP"], $row["FORMAT"],
                $row["RATE"], $db->readBool($row["MAIN"]),
                $db->readBool($row["ACTIVE"]));
    }

    public function getDefault() {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT * from CURRENCIES WHERE "
                . "MAIN = " . $db->true());
        $stmt->execute();
        if ($row = $stmt->fetch()) {
            return $this->build($row, $pdo);
        }
        return null;
    }

    public function update($model) {
        if ($model->isMain) {
            $pdo = PDOBuilder::getPDO();
            $db = DB::get();
            $stmt = $pdo->prepare("UPDATE CURRENCIES SET MAIN = "
                    . $db->false());
            $stmt->execute();
        }
        return parent::update($model);
    }
}
