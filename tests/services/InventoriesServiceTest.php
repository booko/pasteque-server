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

class InventoriesServiceTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
        // Products
        $taxCat = new TaxCat("Tax");
        $tax = new Tax(null, "Tax", stdtimefstr("2001-01-01 00:00:00"),
                0.1);
        $taxCat->addTax($tax);
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
        if ($pdo->exec("DELETE FROM STOCK_INVENTORYITEM") === false
                || $pdo->exec("DELETE FROM STOCK_INVENTORY") === false
                || $pdo->exec("DELETE FROM STOCKDIARY") === false
                || $pdo->exec("DELETE FROM STOCKCURRENT") === false
                || $pdo->exec("DELETE FROM LOCATIONS") === false
                || $pdo->exec("DELETE FROM PRODUCTS_CAT") === false
                || $pdo->exec("DELETE FROM PRODUCTS") === false
                || $pdo->exec("DELETE FROM CATEGORIES") === false
                || $pdo->exec("DELETE FROM TAXES") === false
                || $pdo->exec("DELETE FROM TAXCATEGORIES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    private function checkInventory($inventory, $qty, $lost, $defect,
            $missing, $unitValue) {
        $this->assertEquals($qty, $inventory->qty, "Quantity mismatch");
        $this->assertEquals($lost, $inventory->lostQty, "Lost mismatch");
        $this->assertEquals($defect, $inventory->defectQty, "Defect mismatch");
        $this->assertEquals($missing, $inventery->missingQty,
                "Missing mismatch");
        $this->assertEquals($unitValue, $inventory->unitValue,
                "Unit value mismatch");
    }

    public function testCreateFull() {
        $item = new InventoryItem(null, $this->products[0]->id, null,
                1, 2, 3, 4, 5);
        $inv = new Inventory(stdtimefstr("2001-01-01 00:00:00"),
                $this->locations[0]->id);
        $inv->addItem($item);
        $srv = new InventoriesService();
        $id = $srv->create($inv);
        $this->assertNotEquals(false, $id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM STOCK_INVENTORY");
        $this->assertNotEquals(false, $stmt->execute(), "Query failed");
        if ($row = $stmt->fetch()) {
            $this->assertEquals($id, $row['ID'], "Id mismatch");
            $this->assertEquals($inv->locationId, $row['LOCATION_ID'],
                    "Location id mismatch");
            $this->assertEquals($inv->date, $db->readDate($row['DATE']),
                    "Date mismatch");
        } else {
            $this->assertTrue(false, "No inventory found after creation");
        }
        $stmt = $pdo->prepare("SELECT * FROM STOCK_INVENTORYITEM");
        $this->assertNotEquals(false, $stmt->execute(), "Query failed");
        if ($row = $stmt->fetch()) {
            $this->assertEquals($id, $row['INVENTORY_ID'], "Id mismatch");
            $this->assertEquals($item->productId, $row['PRODUCT_ID'],
                    "Product id mismatch");
            $this->assertEquals($item->attrSetInstId, $row['ATTRSETINST_ID'],
                    "Attribute set instance id mismatch");
            $this->assertEquals($item->qty, $row['QTY'],
                    "Quantity mismatch");
            $this->assertEquals($item->lostQty, $row['LOSTQTY'],
                    "Lost quantity mismatch");
            $this->assertEquals($item->defectQty, $row['DEFECTQTY'],
                    "Defect quantity mismatch");
            $this->assertEquals($item->missingQty, $row['MISSINGQTY'],
                    "Missing quantity mismatch");
            $this->assertEquals($item->unitValue, $row['UNITVALUE'],
                    "Unit value mismatch");
        } else {
            $this->assertTrue(false, "No inventory item found after creation");
        }
    }

    /** @depends testCreateFull */
    public function testCreateGuessMissing() {
        $move = new StockMove(stdtimefstr("2014-01-01 00:00:00"),
                StockMove::REASON_IN_BUY, $this->products[0]->id,
                $this->locations[0]->id, null,
                10, 1);
        $move->id = StocksService::addMove($move);
        $item = new InventoryItem(null, $this->products[0]->id, null,
                1, 2, 3, null, 5);
        $inv = new Inventory(stdtimefstr("2001-01-01 00:00:00"),
                $this->locations[0]->id);
        $inv->addItem($item);
        $srv = new InventoriesService();
        $id = $srv->create($inv);
        $this->assertNotEquals(false, $id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM STOCK_INVENTORYITEM");
        $this->assertNotEquals(false, $stmt->execute(), "Query failed");
        if ($row = $stmt->fetch()) {
            $this->assertEquals(4, $row["MISSINGQTY"]);
            $this->markTestIncomplete("Check unit value");
        } else {
            $this->assertTrue(false, "No inventory item found after creation");
        }
    }

    /** @depends testCreateFull */
    public function testCreateGuessMissingNoStock() {
        $item = new InventoryItem(null, $this->products[0]->id, null,
                1, 2, 3, null, 5);
        $inv = new Inventory(stdtimefstr("2001-01-01 00:00:00"),
                $this->locations[0]->id);
        $inv->addItem($item);
        $srv = new InventoriesService();
        $id = $srv->create($inv);
        $this->assertNotEquals(false, $id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM STOCK_INVENTORYITEM");
        $this->assertNotEquals(false, $stmt->execute(), "Query failed");
        if ($row = $stmt->fetch()) {
            $this->assertEquals(0, $row["MISSINGQTY"]);
            $this->markTestIncomplete("Check unit value");
        } else {
            $this->assertTrue(false, "No inventory item found after creation");
        }
    }
}