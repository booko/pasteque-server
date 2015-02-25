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

class TariffAreasServiceTest extends \PHPUnit_Framework_TestCase {

    private $prdId;

    protected function setUp() {
        $taxCat = new TaxCat("Tax");
        $tax = new Tax(null, "Tax", stdtimefstr("2001-01-01 00:00:00"),
                0.1);
        $taxCat->addTax($tax);
        $taxCat->id = TaxesService::createCat($taxCat);
        $cat = new Category(null, "Category", false, 1);
        $cat->id = CategoriesService::createCat($cat);
        $prd = new Product("REF", "product", 1.0, $cat->id, null, 1,
                $taxCat->id, true, false);
        $this->prdId = ProductsService::create($prd);
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

    public function testCreateEmpty() {
        $srv = new TariffAreasService();
        $area = new TariffArea("area", 1);
        $id = $srv->create($area);
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM TARIFFAREAS";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $id, "Create failed");
        $this->assertEquals($id, $row['ID'], "Inconsistent returned id");
        $this->assertEquals($area->label, $row['NAME'],
                "Inconsistent label after create");
        $this->assertEquals($area->dispOrder, $row['TARIFFORDER'],
                "Inconsistent display order after create");
    }

    /** @depends testCreateEmpty */
    public function testCreate() {
        $srv = new TariffAreasService();
        $area = new TariffArea("area", 1);
        $area->addPrice($this->prdId, 0.8);
        $id = $srv->create($area);
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM TARIFFAREAS_PROD";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "Nothing found after creation");
        $this->assertNotEquals(false, $id, "Create failed");
        $this->assertEquals($id, $row['TARIFFID'], "Id mismatch");
        $this->assertEquals($this->prdId, $row['PRODUCTID'],
                "Product id mismatch");
        $this->assertEquals(0.8, $row['PRICESELL'], "Price mismatch");
    }

    /** @depends testCreate */
    public function testRead() {
        $srv = new TariffAreasService();
        $area = new TariffArea("area", 1);
        $area->addPrice($this->prdId, 0.8);
        $area->id = $srv->create($area);
        $read = $srv->get($area->id);
        $this->assertEquals($area->id, $read->id);
        $this->assertEquals($area->label, $read->label);
        $this->assertEquals(1, count($read->prices), "Prices count mismatch");
        $price = $read->prices[0];
        $this->assertEquals($price->productId, $this->prdId,
                "Product id mismatch");
        $this->assertEquals(0.8, $price->price, "Price mismatch");
    }

    public function testReadInexistent() {
        $srv = new TariffAreasService();
        $read = $srv->get(0);
        $this->assertEquals(null, $read);
    }

    /** @depends testCreate */
    public function testUpdate() {
        $srv = new TariffAreasService();
        $area = new TariffArea("area", 1);
        $area->addPrice($this->prdId, 0.8);
        $area->id = $srv->create($area);
        $areaUpd = new TariffArea("updated", 3);
        $areaUpd->id = $area->id;
        $areaUpd->addPrice($this->prdId, 0.6);
        $this->assertNotEquals(false, $srv->update($areaUpd), "Update failed");
        $read = $srv->get($area->id);
        $this->assertEquals($areaUpd->id, $read->id);
        $this->assertEquals($areaUpd->label, $read->label);
        $this->assertEquals(1, count($read->prices), "Prices count mismatch");
        $price = $read->prices[0];
        $this->assertEquals($price->productId, $this->prdId,
                "Product id mismatch");
        $this->assertEquals(0.6, $price->price, "Price mismatch");
    }

    /** @depends testCreate */
    public function testUpdateDeletePrice() {
        $srv = new TariffAreasService();
        $area = new TariffArea("area", 1);
        $area->addPrice($this->prdId, 0.8);
        $area->id = $srv->create($area);
        $areaUpd = new TariffArea("updated", 3);
        $areaUpd->id = $area->id;
        $this->assertNotEquals(false, $srv->update($areaUpd), "Update failed");
        $read = $srv->get($area->id);
        $this->assertEquals($areaUpd->id, $read->id);
        $this->assertEquals($areaUpd->label, $read->label);
        $this->assertEquals(0, count($read->prices), "Prices count mismatch");
    }
 
    /** @depends testCreate */
    public function testDelete() {
        $srv = new TariffAreasService();
        $area = new TariffArea("area", 1);
        $area->addPrice($this->prdId, 0.8);
        $area->id = $srv->create($area);        
        $this->assertTrue($srv->delete($area->id));
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM TARIFFAREAS_PROD";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $this->assertFalse($stmt->fetch(),
                "Something was found in TARIFFAREAS_PROD after deletion");
        $sql = "SELECT * FROM TARIFFAREAS";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $this->assertFalse($stmt->fetch(),
                "Something was found in TARIFFAREAS after deletion");

    }

}