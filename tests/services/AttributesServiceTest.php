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

class AttributesServiceTest extends \PHPUnit_Framework_TestCase {

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

    public function testCreateAttribute() {
        $attr = new Attribute("attr", 0);
        $id = AttributesService::createAttribute($attr);
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM ATTRIBUTE";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $id, "Create failed");
        $this->assertEquals($id, $row['ID'], "Id mismatch");
        $this->assertEquals($attr->label, $row['NAME'], "Name mismatch");
    }

    /** @depends testCreateAttribute */
    public function testReadAttribute() {
        $attr = new Attribute("attr", 0);
        $id = AttributesService::createAttribute($attr);
        $read = AttributesService::getAttribute($id);
        $this->assertNotNull($read);
        $this->assertEquals($id, $read->id);
        $this->assertEquals($attr->label, $read->label);
    }

    public function testReadInexistentAttribute() {
        $read = AttributesService::getAttribute("junk");
        $this->assertEquals(null, $read);
    }

    /** @depends testReadAttribute */
    public function testUpdateAttribute() {
        $attr = new Attribute("attr", 0);
        $id = AttributesService::createAttribute($attr);
        $attr->id = $id;
        $attr->label = "updated";
        $this->assertTrue(AttributesService::updateAttribute($attr),
                "Update failed");
        $read = AttributesService::getAttribute($id);
        $this->assertEquals("updated", $read->label);
    }

    /** @depends testReadAttribute
     * @depends testReadInexistentAttribute
     */
    public function testDeleteAttribute() {
        $attr = new Attribute("attr", 0);
        $id = AttributesService::createAttribute($attr);
        $attr->id = $id;
        $this->assertTrue(AttributesService::deleteAttribute($id));
        $this->assertNull(AttributesService::getAttribute($id));
    }

    /** @depends testCreateAttribute */
    public function testCreateValue() {
        $attr = new Attribute("attr", 0);
        $id = AttributesService::createAttribute($attr);
        $attr->id = $id;
        $val = new AttributeValue("value");
        $valId = AttributesService::createValue($val, $id);
        $this->assertNotEquals(false, $valId, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM ATTRIBUTEVALUE";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertEquals($valId, $row['ID'], "Id mismatch");
        $this->assertEquals($val->value, $row['VALUE'], "Value mismatch");
        $this->assertEquals($id, $row['ATTRIBUTE_ID'], "Attribute id mismatch");
    }

    /** @depends testCreateValue */
    public function testDeleteValue() {
        $attr = new Attribute("attr", 0);
        $id = AttributesService::createAttribute($attr);
        $attr->id = $id;
        $val = new AttributeValue("value");
        $valId = AttributesService::createValue($val, $id);
        $this->assertTrue(AttributesService::deleteValue($valId),
                "Delete failed");
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM ATTRIBUTEVALUE";
        $stmt = $pdo->prepare($sql);
        $row = $stmt->fetch();
        $this->assertFalse($row, "Something was found after delete");
    }

    /** @depends testCreateValue */
    public function testUpdateValue() {
        $attr = new Attribute("attr", 0);
        $id = AttributesService::createAttribute($attr);
        $attr->id = $id;
        $val = new AttributeValue("value");
        $valId = AttributesService::createValue($val, $id);
        $val->id = $valId;
        $val->value = "updated";
        $this->assertTrue(AttributesService::updateValue($val),
                "Update failed");
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM ATTRIBUTEVALUE";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $this->assertEquals("updated", $row['VALUE'], "Value mismatch");
    }

    public function testCreateEmptySet() {
        $set = new AttributeSet("set");
        $id = AttributesService::createSet($set);
        $this->assertNotEquals(false, $id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM ATTRIBUTESET";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $this->assertEquals($id, $row['ID'], "Id mismatch");
        $this->assertEquals($set->label, $row['NAME'], "Label mismatch");
    }

    public function testReadInexistentSet() {
        $this->assertNull(AttributesService::get("junk"),
                "Something was found with junk id");
    }

    /** @depends testCreateEmptySet */
    public function testReadEmptySet() {
        $set = new AttributeSet("set");
        $id = AttributesService::createSet($set);
        $set->id = $id;
        $read = AttributesService::get($id);
        $this->assertNotNull($read, "Nothing found");
        $this->assertEquals($set->id, $read->id, "Id mismatch");
        $this->assertEquals($set->label, $read->label, "Label mismatch");
    }

    /** @depends testCreateEmptySet
     * @depends testCreateAttribute
     * @depends testCreateValue
     */
    public function testCreateSet() {
        $set = new AttributeSet("set");
        $attr = new Attribute("attr", 1);
        $attrId = AttributesService::createAttribute($attr);
        $attr->id = $attrId;
        $set->addAttribute($attr);
        $id = AttributesService::createSet($set);
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM ATTRIBUTEUSE";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "No use found");
        $this->assertEquals($id, $row['ATTRIBUTESET_ID'],
                "Attribute set id mismatch");
        $this->assertEquals($attrId, $row['ATTRIBUTE_ID'],
                "Attribude id mismatch");
        $this->assertEquals(1, $row['LINENO'], "Display order mismatch");
    }

    /** @depends testCreateSet
     * @depends testReadEmptySet
     */
    public function testReadSet() {
        $set = new AttributeSet("set");
        $attr = new Attribute("attr", 1);
        $attrId = AttributesService::createAttribute($attr);
        $attr->id = $attrId;
        $set->addAttribute($attr);
        $id = AttributesService::createSet($set);
        $read = AttributesService::get($id);
        $this->assertNotNull($read, "Nothing found");
        $this->assertEquals(1, count($read->attributes));
        $this->assertEquals($attr->id, $read->attributes[0]->id,
                "Attribute id mismatch");
        $this->assertEquals($attr->label, $read->attributes[0]->label,
                "Attribute label mismatch");
        $this->assertEquals($attr->dispOrder, $read->attributes[0]->dispOrder,
                "Display order mismatch");
    }

    /** @depends testCreateSet
     */
    public function testDeleteSet() {
        $set = new AttributeSet("set");
        $attr = new Attribute("attr", 1);
        $attrId = AttributesService::createAttribute($attr);
        $attr->id = $attrId;
        $set->addAttribute($attr);
        $id = AttributesService::createSet($set);
        $this->assertTrue(AttributesService::deleteSet($id),
                "Delete failed");
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM ATTRIBUTEUSE";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $this->assertFalse($row,
                "Something was found in ATTRIBUTEUSE after delete");
        $sql = "SELECT * FROM ATTRIBUTESET";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $this->assertFalse($row,
                "Something was found in ATTRIBUTESET after delete");
    }

    /** @depends testCreateSet
     * @depends testReadSet
     */
    public function testUpdateSet() {
        $this->markTestIncomplete();
    }
}