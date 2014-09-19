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

class ProductsAPITest extends \PHPUnit_Framework_TestCase {

    const API = "ProductsAPI";
    private $products;

    protected function setUp() {
        $this->products = array();
        $taxCat = new TaxCat("Tax");
        $tax = new Tax(null, "Tax", stdtimefstr("2001-01-01 00:00:00"),
                0.1);
        $taxCat->addTax($tax);
        $taxCat->id = TaxesService::createCat($taxCat);
        $cat = new Category(null, "Category", false, 1);
        $cat->id = CategoriesService::createCat($cat);
        $prd = new Product("REF", "product", 1.0, $cat->id, 1,
                $taxCat->id, true, true, 0.3, null, "12345", false, true, 0.2);
        $prd->id = ProductsService::create($prd);
        $this->products[] = $prd;
        $prd2 = new Product("REF2", "product2", 2.0, $cat->id, 3, $taxCat->id,
                true, false);
        $prd2->id = ProductsService::create($prd2);
        $this->products[] = $prd2;
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM PRODUCTS_CAT") === false
                || $pdo->exec("DELETE FROM PRODUCTS") === false
                || $pdo->exec("DELETE FROM CATEGORIES") === false
                || $pdo->exec("DELETE FROM TAXES") === false
                || $pdo->exec("DELETE FROM TAXCATEGORIES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testGet() {
        $prd = $this->products[0];
        $broker = new APIBroker(ProductsAPITest::API);
        $result = $broker->run("get", array("id" => $prd->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($prd->id, $content->id, "Id mismatch");
        $this->assertEquals($prd->reference, $content->reference,
                "Reference mismatch");
        $this->assertEquals($prd->label, $prd->label, "Label mismatch");
        $this->assertEquals($prd->priceBuy, $content->priceBuy,
                "Price buy mismatch");
        $this->assertEquals($prd->priceSell, $content->priceSell,
                "Price sell mismatch");
        $this->assertEquals($prd->visible, $content->visible,
                "Active mismatch");
        $this->assertEquals($prd->scaled, $content->scaled,
                "Scaled mismatch");
        $this->assertEquals($prd->categoryId, $content->categoryId,
                "Category id mismatch");
        $this->assertEquals($prd->dispOrder, $content->dispOrder,
                "Display order mismatch");
        $this->assertEquals($prd->taxCatId, $content->taxCatId,
                "Tax category id mismatch");
        $this->assertEquals($prd->attributeSetId, $content->attributeSetId,
                "Attributes set id mismatch");
        $this->assertEquals($prd->hasImage, $content->hasImage,
                "Image mismatch");
        $this->assertEquals($prd->discountEnabled, $content->discountEnabled,
                "Automatic discount switch mismatch");
        $this->assertEquals($prd->discountRate, $content->discountRate,
                "Automatic discount rate mismatch");
    }

    public function testGetAll() {
        $broker = new APIBroker(ProductsAPITest::API);
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