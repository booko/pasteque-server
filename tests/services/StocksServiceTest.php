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

class StocksServiceTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
        // Products
        $taxCat = new TaxCat("Tax");
        $taxCat->id = TaxesService::createCat($taxCat);
        $cat = new Category(null, "Category", false, 1);
        $cat->id = CategoriesService::createCat($cat);
        $prd1 = new Product("REF", "product", 1.0, $cat->id, 1,
                $taxCat->id, true, true, 0.3, null, "12345", false, true, 0.2);
        $prd1->id = ProductsService::create($prd1);
        $prd2 = new Product("REF2", "product2", 2.0, $cat->id, 3, $taxCat->id,
                true, false);
        $prd2->id = ProductsService::create($prd2);
        $this->products = array($prd1, $prd2);
        // Locations
        $locSrv = new LocationsService();
        $loc1 = new Location("Location1");
        $loc1->id = $locSrv->create($loc1);
        $loc2 = new Location("Location2");
        $loc2->id = $locSrv->create($loc2);
        $this->locations = array($loc1, $loc2);

    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM STOCKDIARY") === false
                || $pdo->exec("DELETE FROM STOCKCURRENT") === false
                || $pdo->exec("DELETE FROM STOCKLEVEL") === false
                || $pdo->exec("DELETE FROM LOCATIONS") === false
                || $pdo->exec("DELETE FROM PRODUCTS_CAT") === false
                || $pdo->exec("DELETE FROM PRODUCTS") === false
                || $pdo->exec("DELETE FROM CATEGORIES") === false
                || $pdo->exec("DELETE FROM TAXCATEGORIES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    /** Check equality of two levels for security and max levels */
    private function checkLevelEquality($level, $ref) {
        $this->assertEquals($ref->id, $level->id, "Id mismatch");
        $this->assertEquals($ref->productId, $level->productId,
                "Product id mismatch");
        $this->assertEquals($ref->locationId, $level->locationId,
                "LocationId mismatch");
        $this->assertEquals($ref->security, $level->security,
                "Security level mismatch");
        $this->assertEquals($ref->max, $level->max, "Maximum level mismatch");
    }

    /** Full level equality check */
    private function checkEquality($level, $ref) {
        $this->checkLevelEquality($level, $ref);
        $this->assertEquals($ref->attrSetInstId, $level->attrSetInstId,
                "Attribute set instance id mismatch");
        $this->assertEquals($ref->qty, $level->qty, "Quantity mismatch");
    }

    public function testCreate() {
        $level = new StockLevel($this->products[0]->id, $this->locations[0]->id,
                null, 1.2, 15.6, 10);
        $level->id = StocksService::createLevel($level);
        $this->assertNotEquals(false, $level->id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM STOCKLEVEL");
        $this->assertNotEquals(false, $stmt->execute(), "Query failed");
        if ($row = $stmt->fetch()) {
            $this->assertEquals($level->id, $row['ID'], "Id mismatch");
            $this->assertEquals($level->locationId, $row['LOCATION'],
                    "Location id mismatch");
            $this->assertEquals($level->productId, $row['PRODUCT'],
                    "Product id mismatch");
            $this->assertEquals($level->security, $row['STOCKSECURITY'],
                    "Security level mismatch");
            $this->assertEquals($level->max, $row['STOCKMAXIMUM'],
                    "Maximum level mismatch");
        } else {
            $this->assertTrue(false, "Nothing found after creation");
        }
    }

    /** @depends testCreate*/
    public function testReadLevel() {
        $level = new StockLevel($this->products[0]->id, $this->locations[0]->id,
                null, 1.2, 15.6, 10);
        $level->id = StocksService::createLevel($level);
        $read = StocksService::getLevel($this->products[0]->id,
                $this->locations[0]->id, null);
        $this->assertNotNull($read, "Nothing found");
        $this->checkLevelEquality($read, $level);
    }

    /** @depends testCreate */
    public function testReadLevelInexistentAttribute() {
        $level = new StockLevel($this->products[0]->id, $this->locations[0]->id,
                null, 1.2, 15.6, 10);
        $level->id = StocksService::createLevel($level);
        $read = StocksService::getLevel($this->products[0]->id,
                $this->locations[0]->id, "junk");
        $this->assertNotNull($read, "Nothing found");
        $this->checkLevelEquality($read, $level);
    }

    /** @depends testCreate */
    public function testReadInexistent() {
        $level = new StockLevel($this->products[0]->id, $this->locations[0]->id,
                null, 1.2, 15.6, 10);
        $level->id = StocksService::createLevel($level);
        $read = StocksService::getLevel($this->products[0]->id, "junk", null);
        $this->assertEquals(null, $read,
                "Found something with junk location id");
        $read = StocksService::getLevel("junk", $this->locations[0]->id, null);
        $this->assertEquals(null, $read,
                "Found something with junk product id");
        $read = StocksService::getLevel("junk", "junk", null);
        $this->assertEquals(null, $read,
                "Found something with junk product and location ids");
    }

    /** @depends testCreate */
    public function testReadLevels() {
        $level1 = new StockLevel($this->products[0]->id,
                $this->locations[0]->id, null, 1.2, 15.6, 10);
        $level1->id = StocksService::createLevel($level1);
        $level2 = new StockLevel($this->products[1]->id,
                $this->locations[0]->id, null, null, 10, 8.5);
        $level2->id = StocksService::createLevel($level2);
        $read = StocksService::getLevels($this->locations[0]->id);
        $this->assertNotNull($read, "Nothing found");
        $this->assertTrue(is_array($read), "Content is not an array");
        $this->assertEquals(2, count($read), "Content size mismatch");
        foreach ($read as $lvl) {
            if ($lvl->id == $level1->id) {
                $ref = $level1;
            } else if ($lvl->id == $level2->id) {
                $ref = $level2;
            } else {
                $this->assertTrue(false, "Unknown level id returned");
            }
            $this->checkLevelEquality($lvl, $ref);
        }
    }

    /** @depends testCreate */
    public function testReadEmptyLevels() {
        $level1 = new StockLevel($this->products[0]->id,
                $this->locations[0]->id, null, 1.2, 15.6, 10);
        $level1->id = StocksService::createLevel($level1);
        $level2 = new StockLevel($this->products[1]->id,
                $this->locations[0]->id, null, null, 10, 8.5);
        $level2->id = StocksService::createLevel($level2);
        $read = StocksService::getLevels($this->locations[1]->id);
        $this->assertNotNull($read, "Nothing found");
        $this->assertTrue(is_array($read), "Content is not an array");
        $this->assertEquals(0, count($read), "Result should be empty");
    }

    /** @depends testCreate */
    public function testReadInexistentLevels() {
        $level1 = new StockLevel($this->products[0]->id,
                $this->locations[0]->id, null, 1.2, 15.6, 10);
        $level1->id = StocksService::createLevel($level1);
        $level2 = new StockLevel($this->products[1]->id,
                $this->locations[0]->id, null, null, 10, 8.5);
        $level2->id = StocksService::createLevel($level2);
        $read = StocksService::getLevels("junk");
        $this->assertNull($read, "Junk id returned something");
    }

    /** @depends testCreate */
    public function testUpdate() {
        $this->markTestIncomplete();
    }

    public function testUpdateInexistent() {
        $this->markTestIncomplete();
    }

    /** @depends testCreate
     * @depends testReadInexistent
     */
    public function testDeleteLevel() {
        $this->markTestIncomplete();
    }

    /** @depends testCreate */
    public function testCreateMoveBuy() {
        $level = new StockLevel($this->products[0]->id, $this->locations[0]->id,
                null, 1.2, 15.6, 10);
        $level->id = StocksService::createLevel($level);
        $move = new StockMove(stdtimefstr("2014-01-01 00:00:00"),
                StockMove::REASON_IN_BUY, $level->productId,
                $level->locationId, $level->attrSetInstId,
                3.5, 10);
        $move->id = StocksService::addMove($move);
        $this->assertNotEquals(false, $move->id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM STOCKCURRENT");
        $this->assertNotEquals(false, $stmt->execute(), "Query failed");
        if ($row = $stmt->fetch()) {
            $this->assertEquals($move->locationId, $row['LOCATION'],
                    "Location id mismatch");
            $this->assertEquals($move->productId, $row['PRODUCT'],
                    "Product id mismatch");
            $this->assertEquals($move->attrSetInstId,
                    $row['ATTRIBUTESETINSTANCE_ID'],
                    "Attribute set instance id mismatch");
            $this->assertEquals($move->qty, $row['UNITS'],
                    "Quantity mismatch");
        } else {
            $this->assertTrue(false, "Nothing found after creation");
        }
    }

    /** @depends testCreateMoveBuy
     * @depends testReadLevel
     */
    public function testReadQty() {
        $level = new StockLevel($this->products[0]->id, $this->locations[0]->id,
                null, 1.2, 15.6, 3.5); // Quantity is set matching to move
        $level->id = StocksService::createLevel($level);
        $move = new StockMove(stdtimefstr("2014-01-01 00:00:00"),
                StockMove::REASON_IN_BUY, $level->productId,
                $level->locationId, $level->attrSetInstId,
                $level->qty, 10);
        $move->id = StocksService::addMove($move);
        $read = StocksService::getLevel($move->productId, $move->locationId,
                $move->attrSetInstId);
        $this->checkEquality($read, $level);
    }

    /** @depends testReadQty */
    public function testReadQtyAttr() {
        $this->markTestIncomplete("Check quantity with attribute set");
    }

    public function testReadQtyBadAttr() {
        $this->markTestIncomplete("Check that security and max are returned "
                . "and qty is null with bad attribute set");
    }

    /** @depends testReadQty
     * @depends testReadLevels
     * Test reading levels with stock defined but no level.
     */
    public function testReadEmptyLevel() {
        $this->markTestIncomplete();
    }

    /** @depends testReadQty
     * @depends testReadLevels
     * Test reading stock with stock defined but with no level.
     */
    public function testReadEmptyStock() {
        $this->markTestIncomplete();
    }
}