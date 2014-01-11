<?php
//    Pasteque server testing
//
//    Copyright (C) 2012 Scil (http://scil.coop)
//
//    This file is part of Pasteque.
//
//    Pasteque is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Pasteque is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Pasteque.  If not, see <http://www.gnu.org/licenses/>.
namespace Pasteque;

require_once(dirname(dirname(__FILE__)) . "/common_load.php");

class CurrencyTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    protected function tearDown() {
    }

    public static function tearDownAfterClass() {
    }

    public function testConstructEmpty() {
        $curr = new Currency("Test", "$", ".", ",", "$##0.00", 1.2, true, true);
        $this->assertEquals("Test", $curr->label);
        $this->assertEquals("$", $curr->symbol);
        $this->assertEquals(".", $curr->decimalSeparator);
        $this->assertEquals(",", $curr->thousandsSeparator);
        $this->assertEquals("$##0.00", $curr->format);
        $this->assertEquals(1.2, $curr->rate);
        $this->assertTrue($curr->isMain);
        $this->assertTrue($curr->isActive);
    }

    /** @depends testConstructEmpty */
    public function testIntegerFormat() {
        $curr = new Currency("Test", "$", ".", ",", "#", 1.2, true, true);
        $this->assertEquals("1", $curr->format(1), "Integer format failed");
        $this->assertEquals("1", $curr->format(1.1),
                "Integer floor rounding format failed");
        $this->assertEquals("2", $curr->format(1.9),
                "Integer ceil rounding format failed");
    }

    /** @depends testConstructEmpty
     * @depends testIntegerFormat
     */
    public function testIntegerSymbolFormat() {
        $curr = new Currency("Test", "$", ".", ",", "$#", 1.2, true, true);
        $this->assertEquals("$1", $curr->format(1), "Dollar symbol failed");
        $curr->symbol = "€";
        $this->assertEquals("€1", $curr->format(1), "Euro symbol failed");
        $curr->format = "#0€";
        $this->assertEquals("1€", $curr->format(1), "Post Euro symbol failed");
    }


    /** @depends testConstructEmpty */
    public function testDecimalSeparatorFormat() {
        $curr = new Currency("Test", "$", ".", ",", "##0.#", 1.2, true, true);
        $this->assertEquals("1.1", $curr->format(1.1),
                "Dot decimal separator failed");
        $this->assertEquals("1.00", $curr->format(1), "Integer decimal failed");
        $curr->decimalSeparator = ",";
        $this->assertEquals("1,1", $curr->format(1.1),
                "Coma decimal separator failed");
    }

    /** @depends testConstructEmpty
     * @depends testDecimalSeparatorFormat
     */
    public function testFixedDecimalDigitsFormat() {
        $curr = new Currency("Test", "$", ".", ",", "#.00", 1.2, true, true);
        $this->assertEquals("1.10", $curr->format(1.1), "Right padding failed");
        $this->assertEquals("1.10", $curr->format(1.10111),
                "Floor rounding failed");
        $this->assertEquals("1.10", $curr->format(1.0999),
                "Ceil rounding failed");
        $curr->format = "#.000";
        $this->assertEquals("1.000", $curr->format(1), "Right padding failed");
        $this->assertEquals("1.001", $curr->format(1.001231),
                "Floor rounding failed");
        $this->assertEquals("1.001", $curr->format(1.0009),
                "Ceil rounding failed");
    }

    /** @depends testConstructEmpty
     * @depends testDecimalSeparatorFormat
     */
    public function testDecimalSymbolFormat() {
        $curr = new Currency("Test", "$", ".", ",", "$#.00", 1.2, true, true);
        $this->assertEquals("$1.99", $curr->format(1.99),
                "Decimal format failed");
        $this->assertEquals("$1.99", $curr->format(1.99132312),
                "Decimal floor rounding failed");
        $this->assertEquals("$2.00", $curr->format(1.9999999),
                "Decimal ceil rounding failed");
        $this->assertEquals("$1.00", $curr->format(1),
                "Decimal padding failed");
    }

    /** @depends testConstructEmpty */
    public function testThousandsSeparatorFormat() {
        $curr = new Currency("Test", "$", ".", ",", "#,##0", 1.2, true, true);
        $this->assertEquals("1,000", $curr->format(1000),
                "Coma thousands separator failed");
        $curr->thousandsSeparator = " ";
        $this->assertEquals("1 000", $curr->format(1000),
                "Space thousands separator failed");
    }

    /** @depends testConstructEmpty
     * @depends testDecimalSymbolFormat
     * @depends testDecimalSeparatorFormat
     * @depends testThousandsSeparatorFormat
     */
    public function testRealFormat() {
        $curr = new Currency("Test", "$", ".", ",", "$ #,##0.00", 1.2, true, true);
        $this->assertEquals("$ 1,234.99", $curr->format(1234.99));
        $this->assertEquals("$ 11,234.99", $curr->format(11234.99));
        $this->assertEquals("$ 10.50", $curr->format(10.5));
        $this->assertEquals("$ 0.00", $curr->format(0));
        $curr = new Currency("Test", "€", ",", " ", "#,##0.00 $", 1.2,
                true, true);
        $this->assertEquals("1 234,99 €", $curr->format(1234.99));
        $this->assertEquals("11 234,99 €", $curr->format(11234.99));
        $this->assertEquals("10,50 €", $curr->format(10.5));
        $this->assertEquals("0,00 €", $curr->format(0));
    }
}