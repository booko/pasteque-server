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

class TaxesTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    protected function tearDown() {
    }

    public static function tearDownAfterClass() {
    }

    public function testConstructEmpty() {
        $tax = new Tax("Cat", "Label", stdtimefstr("2001-01-01 00:00:00"), 0.2);
        $this->assertEquals("Cat", $tax->taxCatId);
        $this->assertEquals("Label", $tax->label);
        $this->assertEquals(stdtimefstr("2001-01-01 00:00:00"),
                $tax->startDate);
        $this->assertEquals(0.2, $tax->rate);
    }

    /** @depends testConstructEmpty */
    public function testValidity() {
        $tax = new Tax("Cat", "Label", stdtimefstr("2001-01-01 00:00:00"), 0.2);
        $this->assertTrue($tax->isValid(stdtimefstr("2001-01-01 00:00:00")),
                "Tax recognised invalid at change date");
        $this->assertTrue($tax->isValid(stdtimefstr("2001-01-02 00:00:00")),
                "Tax recognised invalid after change date");
        $this->assertFalse($tax->isValid(stdtimefstr("2000-01-01 00:00:00")),
                "Tax recognised valid before change date");
    }

    public function testCreateEmptyCat() {
        $cat = new TaxCat("Label");
        $this->assertEquals("Label", $cat->label);
    }

    /** @depends testCreateEmptyCat
     * @depends testConstructEmpty
     */
    public function testCurrentTax() {
        $cat = new TaxCat("Category");
        $taxA = new Tax("id", "TaxA", stdtimefstr("2001-02-01 00:00:00"), 0.1);
        $taxB = new Tax("id", "TaxB", stdtimefstr("2001-03-01 00:00:00"), 0.2);
        $cat->addTax($taxA);
        $cat->addTax($taxB);
        $current = $cat->getCurrentTax(stdtimefstr("2001-05-01 00:00:00"));
        $this->assertEquals("TaxB", $current->label,
                "Unable to find latest tax");
        $current = $cat->getCurrentTax(stdtimefstr("2001-02-03 00:00:00"));
        $this->assertEquals("TaxA", $current->label,
                "Unable to find first tax");
        $current = $cat->getCurrentTax(stdtimefstr("2001-02-01 00:00:00"));
        $this->assertEquals("TaxA", $current->label,
                "First tax activation failed");
        $current = $cat->getCurrentTax(stdtimefstr("2001-03-01 00:00:00"));
        $this->assertEquals("TaxB", $current->label,
                "Last tax switch failed");
        $current = $cat->getCurrentTax(stdtimefstr("2001-01-01 00:00:00"));
        $this->assertEquals(null, $current,
                "Found something where nothing was defined");
    }
}

?>