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

class CashTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    protected function tearDown() {
    }

    public static function tearDownAfterClass() {
    }

    public function testConstructEmpty() {
        $cash = new Cash("Host", 2, null, null, null, null);
        $this->assertEquals("Host", $cash->host, "Host assignment failed");
        $this->assertEquals(2, $cash->sequence, "Sequence assignment failed");
        $this->assertNull($cash->openDate, "Open date assignment failed");
        $this->assertNull($cash->closeDate, "Close date assignment failed");
        $this->assertNull($cash->openCash, "Open cash assignment failed");
        $this->assertNull($cash->closeCash, "Close cash assignment failed");
        $this->assertFalse($cash->isOpened(), "Open state check failed");
        $this->assertFalse($cash->isClosed(), "Close state check failed");
    }

    /** @depends testConstructEmpty */
    public function testConstructOpened() {
        $cash = new Cash("Host", 2, stdtimefstr("1900-01-01 00:00:00"), null,
                10.0, null);
        $this->assertTrue($cash->isOpened(), "Open state check failed");
        $this->assertFalse($cash->isClosed(), "Close state check failed");
        $this->assertEquals(10.0, $cash->openCash, "Open cash mismatch");
        $this->assertNull($cash->closeCash, "Close cash mismatch");
    }

    /** @depends testConstructEmpty */
    public function testConstructClosed() {
        $cash = new Cash("Host", 2, stdtimefstr("1900-01-01 00:00:00"),
                stdtimefstr("1900-01-02 00:00:00"), 10.0, 12.0);
        $this->assertTrue($cash->isOpened(), "Open state check failed");
        $this->assertTrue($cash->isClosed(), "Close state check failed");
        $this->assertEquals(10.0, $cash->openCash, "Open cash mismatch");
        $this->assertEquals(12.0, $cash->closeCash, "Close cash mismatch");
    }

}