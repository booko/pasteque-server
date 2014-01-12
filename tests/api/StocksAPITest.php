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

class StocksAPITest extends \PHPUnit_Framework_TestCase {

    const API = "StocksAPI";
    private $products;
    private $locations;
    private $levels;

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
        // Stocks
        $lvl11 = new StockLevel($prd1->id, $loc1->id, null, null, 10);
        $lvl11->id = StocksService::createLevel($lvl11);
        $lvl12 = new StockLevel($prd1->id, $loc2->id, null, 5.2, null);
        $lvl12->id = StocksService::createLevel($lvl12);
        $lvl21 = new StockLevel($prd2->id, $loc1->id, null, 7, 20);
        $lvl21->id = StocksService::createLevel($lvl21);
        $move = new StockMove(stdtimefstr("2014-01-01 00:00:00"),
                StockMove::REASON_IN_BUY, $loc1->id, $prd1->id, null, 3.5, 2.1);
        StocksService::addMove($move);
        $this->levels = array($lvl11, $lvl12, $lvl21);
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

    public function testGetAll() {
        $broker = new APIBroker(StocksAPITest::API);
        $result = $broker->run("getAll",
                array("locationId" => $this->locations[0]->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(2, count($content), "Content size mismatch");
        foreach($content as $lvl) {
            if ($lvl->id == $this->levels[0]->id) {
                $ref = $this->levels[0];
            } else if ($lvl->id == $this->levels[2]->id) {
                $ref = $this->levels[2];
            } else {
                $this->assertTrue(false,
                        "Unknown level id returned: " . $lvl->id);
            }
            $this->assertEquals($lvl->productId, $ref->productId,
                    "Product id mismatch");
            $this->assertEquals($lvl->locationId, $ref->locationId,
                    "Location id mismatch");
            $this->assertEquals($lvl->attrSetInstId, $ref->attrSetInstId,
                    "Attribute set instance id mismatch");
            $this->assertEquals($lvl->security, $ref->security,
                    "Security level mismatch");
            $this->assertEquals($lvl->max, $ref->max,
                    "Maximum level mismatch");
            $this->assertEquals($lvl->qty, $ref->qty, "Quantity mismatch");
        }
    }

    public function testGetAllInexistent() {
        $broker = new APIBroker(StocksAPITest::API);
        $result = $broker->run("getAll", array("locationId" => "junk"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNull($content, "Content is null with junk id");
    }
}