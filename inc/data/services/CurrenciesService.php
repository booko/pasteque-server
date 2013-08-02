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
            "MAIN" => "main",
            "ACTIVE" => "active"
    );

    protected function build($row, $pdo = null) {
        return Currency::__build($row["ID"], $row["NAME"], $row["SYMBOL"],
                $row["DECIMALSEP"], $row["THOUSANDSSEP"], $row["FORMAT"],
                $row["RATE"], $row["MAIN"], ord($row["ACTIVE"]));
    }
}