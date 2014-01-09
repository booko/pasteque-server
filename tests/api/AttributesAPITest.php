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

class AttributesAPITest extends \PHPUnit_Framework_TestCase {

    const API = "AttributesAPI";

    public static function setUpBeforeClass() {
        // Install empty database
        Installer::install(null);
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM ATTRIBUTEUSE") === false
                || $pdo->exec("DELETE FROM ATTRIBUTESET") === false
                || $pdo->exec("DELETE FROM ATTRIBUTEVALUE") === false
                || $pdo->exec("DELETE FROM ATTRIBUTE") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public static function tearDownAfterClass() {
        // Erase database
        dropDatabase();
    }

    public function testGet() {
        $broker = new APIBroker(AttributesAPITest::API);
        // Init set
        $set = new AttributeSet("set");
        $attr = new Attribute("attr", 1);
        $attr->id = AttributesService::createAttribute($attr);
        $val1 = new AttributeValue("value1");
        $val2 = new AttributeValue("value2");
        $val1->id = AttributesService::createValue($val1, $attr->id);
        $val2->id = AttributesService::createValue($val2, $attr->id);
        $attr->addValue($val1);
        $attr->addValue($val2);
        $set->addAttribute($attr);
        $set->id = AttributesService::createSet($set);
        // Get it through API
        $result = $broker->run("get", array("id" => $set->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($set->id, $content->id, "Set id mismatch");
        $this->assertEquals($set->label, $content->label, "Set label mismatch");
        $this->assertTrue(is_array($set->attributes),
                "Attributes is not an array");
        $this->assertEquals(1, count($set->attributes),
                "Attribute count mismatch");
        $readAttr = $content->attributes[0];
        $this->assertEquals($attr->id, $readAttr->id, "Attribute id mismatch");
        $this->assertEquals($attr->label, $readAttr->label,
                "Attribute label mismatch");
        $this->assertEquals($attr->dispOrder, $readAttr->dispOrder,
                "Attribute display order mismatch");
        $this->assertTrue(is_array($readAttr->values),
                "Values is not an array");
        $this->assertEquals(2, count($readAttr->values));
        $this->markTestIncomplete("Check values (order issue?)");
    }

    public function testGetAll() {
        $broker = new APIBroker(AttributesAPITest::API);
        // Get it through API
        $result = $broker->run("getAll", array());
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->markTestIncomplete("Check content");
    }
}