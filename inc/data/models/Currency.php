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

class Currency {

    public $id;
    public $label;
    public $symbol;
    public $decimalSeparator;
    public $thousandsSeparator;
    public $format;
    public $rate;
    public $isMain;
    public $isActive;

    static function __build($id, $label, $symbol, $decimalSeparator,
            $thousandsSeparator, $format, $rate, $main, $active) {
        $currency = new Currency($label, $symbol, $decimalSeparator,
                $thousandsSeparator, $format, $rate, $main, $active);
        $currency->id = $id;
        return $currency;
    }

    function __construct($label, $symbol, $decimalSeparator,
            $thousandsSeparator, $format, $rate, $main, $active) {
        $this->label = $label;
        $this->symbol = $symbol;
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandsSeparator = $thousandsSeparator;
        $this->format = $format;
        $this->rate = $rate;
        $this->isMain = $main;
        $this->isActive = $active;
    }

    public function format($amount) {
        $realFormat = str_replace("$", "¤", $this->format);
        $numFormatter = new \NumberFormatter(null,
                \NumberFormatter::PATTERN_DECIMAL, $realFormat);
        $numFormatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL,
                $this->symbol);
        $numFormatter->setSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL,
                $this->decimalSeparator);
        $numFormatter->setSymbol(\NumberFormatter::MONETARY_SEPARATOR_SYMBOL,
                $this->decimalSeparator);
        $numFormatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL,
                $this->thousandsSeparator);
        $numFormatter->setSymbol(\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, $this->thousandsSeparator);
        // PHP fix: number of decimal digits is forced without pattern analysis
        // when in currency mode (with currency symbol in format)
        if (strpos($realFormat, "¤") !== FALSE) {
            // Count number of 0 after decimal separator
            $parts = explode(".", $realFormat);
            $decimals = 0;
            if (count($parts) == 2) {
                $decimalPart = $parts[1];
                $decimals = count(explode("0", $decimalPart)) - 1;
            }
            $numFormatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS,
                    $decimals);
        }
        return $numFormatter->format($amount);
    }

}

?>