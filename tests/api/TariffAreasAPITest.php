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

class TariffAreasAPITest extends \PHPUnit_Framework_TestCase {

    const API = "TariffAreasAPI";
    private $areas;

    protected function setUp() {
        $this->areas = array();
        $taxCat = new TaxCat("Tax");
        $tax = new Tax(null, "Tax", stdtimefstr("2001-01-01 00:00:00"),
                0.1);
        $taxCat->addTax($tax);
        $taxCat->id = TaxesService::createCat($taxCat);
        $cat = new Category(null, "Category", false, 1);
        $cat->id = CategoriesService::createCat($cat);
        $prd = new Product("REF", "product", 1.0, $cat->id, null, 1,
                $taxCat->id, true, false);
        $prd->id = ProductsService::create($prd);
        $area = new TariffArea("Area", 1);
        $area->addPrice($prd->id, 0.8);
        $srv = new TariffAreasService();
        $area->id = $srv->create($area);
        $area2 = new TariffArea("Area51", 51);
        $area2->id = $srv->create($area2);
        $this->areas[] = $area;
        $this->areas[] = $area2;
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM TARIFFAREAS_PROD") === false
                || $pdo->exec("DELETE FROM TARIFFAREAS") === false
                || $pdo->exec("DELETE FROM PRODUCTS_CAT") === false
                || $pdo->exec("DELETE FROM PRODUCTS") === false
                || $pdo->exec("DELETE FROM CATEGORIES") === false
                || $pdo->exec("DELETE FROM TAXES") === false
                || $pdo->exec("DELETE FROM TAXCATEGORIES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testGet() {
        $area = $this->areas[0];
        $broker = new APIBroker(TariffAreasAPITest::API);
        $result = $broker->run("get", array("id" => $area->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($area->id, $content->id, "Id mismatch");
        $this->assertEquals($area->label, $content->label,
                "Name mismatch");
        $this->assertEquals($area->dispOrder, $content->dispOrder,
                "Display order mismatch");
        $this->assertTrue(is_array($content->prices), "Prices is not an array");
        $this->assertEquals(1, count($content->prices), "Prices count mismatch");
        $price = $content->prices[0];
        $this->assertNotNull($price->productId, "Product id is null");
        $this->assertNotNull($price->price, "Price is null");
        $this->markTestIncomplete("Check price values");
    }

    public function testGetAll() {
        $broker = new APIBroker(TariffAreasAPITest::API);
        $result = $broker->run("getAll", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(2, count($content), "Content size mismatch");
        $this->markTestIncomplete("Check content");
    }
}