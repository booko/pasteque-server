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

class TaxesAPITest extends \PHPUnit_Framework_TestCase {

    const API = "TaxesAPI";
    private $taxes;

    protected function setUp() {
        $this->taxes = array();
        $taxCat1 = new TaxCat("Standard");
        $taxCat1->id = TaxesService::createCat($taxCat1);
        $taxCat2 = new TaxCat("Reduced");
        $taxCat2->id = TaxesService::createCat($taxCat2);
        $tax1 = new Tax($taxCat1->id, "20%", stdtimefstr("2014-01-01 00:00:00"),
                0.2);
        $tax1->id = TaxesService::createTax($tax1);
        $taxCat1->addTax($tax1);
        $tax2 = new Tax($taxCat2->id, "10%", stdtimefstr("2014-01-01 00:00:00"),
                0.1);
        $tax2->id = TaxesService::createTax($tax2);
        $taxCat2->addTax($tax2);
        $tax3 = new Tax($taxCat1->id, "Old", stdtimefstr("2000-01-01 00:00:00"),
                0.22);
        $tax3->id = TaxesService::createTax($tax3);
        $taxCat1->addTax($tax3);
        $this->taxes[] = $taxCat1;
        $this->taxes[] = $taxCat2;
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM TAXES") === false
                || $pdo->exec("DELETE FROM TAXCATEGORIES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testGet() {
        $taxCat = $this->taxes[0];
        $broker = new APIBroker(TaxesAPITest::API);
        $result = $broker->run("get", array("id" => $taxCat->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($taxCat->id, $content->id, "Id mismatch");
        $this->assertEquals($taxCat->label, $content->label,
                "Name mismatch");
        $this->assertTrue(is_array($content->taxes), "Taxes is not an array");
        $this->assertEquals(2, count($content->taxes), "Taxes count mismatch");
        $tax = $content->taxes[0];
        $this->assertNotNull($tax->id, "Tax id is null");
        $this->assertEquals($taxCat->id, $tax->taxCatId, "Tax cat id mismatch");
        $this->assertNotNull($tax->startDate, "Start date is null");
        $this->assertNotNull($tax->rate, "Tax rate is null");
        $this->markTestIncomplete("Check tax values");
    }

    public function testGetAll() {
        $broker = new APIBroker(TaxesAPITest::API);
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