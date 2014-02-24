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

class CompositionsAPITest extends \PHPUnit_Framework_TestCase {

    const API = "CompositionsAPI";
    private $products;
    private $compositions;

    protected function setUp() {
        $this->products = array();
        $this->compositions = array();
        // Setup tax and categories
        $taxCat = new TaxCat("Tax");
        $taxCat->id = TaxesService::createCat($taxCat);
        $pdo = PDOBuilder::getPDO();
        $id = CompositionsService::CAT_ID;
        $catCmp = new Category(null, "Compositions", false, 1);
        $sql = "INSERT INTO CATEGORIES (ID, NAME, PARENTID, DISPORDER, IMAGE) "
                . "VALUES (:id, :name, :pid, :order, null)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $catCmp->label, \PDO::PARAM_STR);
        $stmt->bindParam(":pid", $catCmp->parentId, \PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        $stmt->bindParam(":order", $catCmp->dispOrder, \PDO::PARAM_INT);
        $stmt->execute();
        $cat = new Category(null, "Category", false, 2);
        $cat->id = CategoriesService::createCat($cat);
        // Set up products
        $prd = new Product("REF", "product", 1.0, $cat->id, 1,
                $taxCat->id, true, true, 0.3, null, "12345", false, true, 0.2);
        $prd->id = ProductsService::create($prd, null);
        $this->products[] = $prd;
        $prd2 = new Product("REF2", "product2", 2.0, $cat->id, 3, $taxCat->id,
                true, false);
        $prd2->id = ProductsService::create($prd2, null);
        $this->products[] = $prd2;

        $cmp = new Composition("CMP", "composition", 1.0, $id, 1,
                $taxCat->id, true, true, 0.3, null, "12345", false, true, 0.2);
        $subgrp = new Subgroup(null, "Subgroup", 1, false);
        $subgrp->addProduct(new SubgroupProduct($prd->id, null, 1));
        $subgrp->addProduct(new SubgroupProduct($prd2->id, null, 2));
        $cmp->addGroup($subgrp);
        $cmp->id = CompositionsService::create($cmp, null, null);
        $this->compositions[] = $cmp;
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM SUBGROUPS_PROD") === false
                || $pdo->exec("DELETE FROM SUBGROUPS") === false
                || $pdo->exec("DELETE FROM PRODUCTS_CAT") === false
                || $pdo->exec("DELETE FROM PRODUCTS") === false
                || $pdo->exec("DELETE FROM CATEGORIES") === false
                || $pdo->exec("DELETE FROM TAXCATEGORIES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    private function checkEquality($ref, $read) {
        $this->assertEquals($ref->reference, $read->reference,
                "Reference mismatch");
        $this->assertEquals($ref->label, $ref->label, "Label mismatch");
        $this->assertEquals($ref->priceBuy, $read->priceBuy,
                "Price buy mismatch");
        $this->assertEquals($ref->priceSell, $read->priceSell,
                "Price sell mismatch");
        $this->assertEquals($ref->visible, $read->visible,
                "Active mismatch");
        $this->assertEquals($ref->scaled, $read->scaled,
                "Scaled mismatch");
        $this->assertEquals($ref->categoryId, $read->categoryId,
                "Category id mismatch");
        $this->assertEquals($ref->dispOrder, $read->dispOrder,
                "Display order mismatch");
        $this->assertEquals($ref->taxCatId, $read->taxCatId,
                "Tax category id mismatch");
        $this->assertEquals($ref->attributeSetId, $read->attributeSetId,
                "Attributes set id mismatch");
        $this->assertEquals($ref->hasImage, $read->hasImage,
                "Image mismatch");
        $this->assertEquals($ref->discountEnabled, $read->discountEnabled,
                "Automatic discount switch mismatch");
        $this->assertEquals($ref->discountRate, $read->discountRate,
                "Automatic discount rate mismatch");
        $this->assertTrue(is_array($read->groups), "Groups is not an array");
        $this->assertEquals(count($ref->groups), count($read->groups),
                "Groups number mismatch");
        foreach ($read->groups as $group) {
            $refGrp = null;
            foreach ($ref->groups as $rgrp) {
                if ($group->id == $rgrp->id) {
                    $refGrp = $rgrp;
                    break;
                }
            }
            $this->assertNotNull($refGrp, "Unknown group");
            $this->assertEquals($refGrp->label, $group->label,
                    "Group label mismatch");
            $this->assertEquals($refGrp->dispOrder, $group->dispOrder,
                    "Group display order mismatch");
            $this->assertEquals($refGrp->hasImage, $group->hasImage,
                    "Group has image mismatch");
            $this->assertTrue(is_array($group->choices),
                    "Subgroup products is not an array");
            $this->assertEquals(count($refGrp->choices),
                    count($group->choices),
                    "Subgroup products number mismatch");
            foreach ($group->choices as $prd) {
                $refPrd = null;
                foreach ($refGrp->choices as $rprd) {
                    if ($prd->productId == $rprd->productId) {
                        $refPrd = $rprd;
                        break;
                    }
                }
                $this->assertNotNull($refPrd, "Unknown product");
                $this->assertEquals($refPrd->dispOrder, $prd->dispOrder,
                        "Product display order mismatch");
            }
        }
    }

    public function testGet() {
        $cmp = $this->compositions[0];
        $broker = new APIBroker(CompositionsAPITest::API);
        $result = $broker->run("get", array("id" => $cmp->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->checkEquality($cmp, $content);
    }

    public function testGetAll() {
        $broker = new APIBroker(CompositionsAPITest::API);
        $result = $broker->run("getAll", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(count($this->compositions), count($content),
                "Content size mismatch");
        foreach ($content as $cmp) {
            $refCmp = null;
            foreach ($this->compositions as $rcmp) {
                if ($cmp->id == $rcmp->id) {
                    $refCmp = $cmp;
                    break;
                }
            }
            $this->assertNotNull($refCmp, "Unknown composition");
            $this->checkEquality($refCmp, $cmp);
        }
    }
}