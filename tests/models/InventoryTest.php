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

class InventoryTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    protected function tearDown() {
    }

    public static function tearDownAfterClass() {
    }

    public function testConstruct() {
        $inv = new Inventory(1000, 1);
        $this->assertEquals(1000, $inv->date);
        $this->assertEquals(1, $inv->locationId);
        $this->assertTrue(is_array($inv->items));
        $this->assertEquals(0, count($inv->items));
    }

    public function testConstructItem() {
        $item = new InventoryItem(1000, "abc", null, 1, 2, 3, 4, 10);
        $this->assertEquals(1000, $item->inventoryId);
        $this->assertEquals("abc", $item->productId);
        $this->assertEquals(null, $item->attrSetInstId);
        $this->assertEquals(1, $item->qty);
        $this->assertEquals(2, $item->lostQty);
        $this->assertEquals(3, $item->defectQty);
        $this->assertEquals(4, $item->missingQty);
        $this->assertEquals(10, $item->unitValue);
    }

    /** @depends testConstruct
     * @depends testConstructItem */
    public function testAddItem() {
        $inv = new Inventory(1000, 1);
        $inv->addItem(new InventoryItem(1000, "abc", null, 1, 2, 3, 4, 10));
        $this->assertEquals(1, count($inv->items));
        $item = $inv->items[0];
        $this->assertEquals("abc", $item->productId);
        $this->assertEquals(null, $item->attrSetInstId);
        $this->assertEquals(1, $item->qty);
        $this->assertEquals(2, $item->lostQty);
        $this->assertEquals(3, $item->defectQty);
        $this->assertEquals(4, $item->missingQty);
        $this->assertEquals(10, $item->unitValue);
    }

    /** @depends testConstruct */
    public function testBuild() {
        $inv = Inventory::__build(1, 1000, 1);
        $this->assertEquals(1, $inv->id);
        $this->assertEquals(1000, $inv->date);
        $this->assertEquals(1, $inv->locationId);
    }

}