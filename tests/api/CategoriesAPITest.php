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

class CategoriesAPITest extends \PHPUnit_Framework_TestCase {

    const API = "CategoriesAPI";

    public static function setUpBeforeClass() {
        // Install empty database
        Installer::install(null);
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        $sql = "DELETE FROM CATEGORIES WHERE PARENTID IS NOT NULL";
        if ($pdo->exec($sql) === false) {
            echo("[ERROR] Unable to restore db\n");
        } else {
            $sql2 = "DELETE FROM CATEGORIES";
            if ($pdo->exec($sql2) === false) {
                echo("[ERROR] Unable to restore db\n");
            }
        }
    }

    public static function tearDownAfterClass() {
        // Erase database
        dropDatabase();
    }

    private function createCat($parentId, $label, $image, $dispOrder) {
        $cat = new Category($parentId, $label, $image, $dispOrder);
        $srv = new CategoriesService();
        $id = $srv->createCat($cat);
        $cat->id = $id;
        return $cat;
    }

    private function checkCatEquality($expected, $read) {
        $this->assertEquals($expected->id, $read->id, "Id mismatch");
        $this->assertEquals($expected->parentId, $read->parentId,
                "Parent id mismatch");
        $this->assertEquals($expected->label, $read->label, "Label mismatch");
        $this->assertEquals($expected->dispOrder, $read->dispOrder,
                "Display order mismatch");
        // TODO: what about images?
    }

    public function testGetInexistent() {
        $broker = new APIBroker(CategoriesAPITest::API);
        $result = $broker->run("get", array("id" => "junk"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNull($content, "Junk id returned something");
    }

    public function testGet() {
        $broker = new APIBroker(CategoriesAPITest::API);
        $cat = $this->createCat(null, "Category", null, 1);
        $result = $broker->run("get", array("id" => $cat->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Category not found");
        $this->checkCatEquality($cat, $content);
    }

    public function testGetChildrenInexistent() {
        $broker = new APIBroker(CategoriesAPITest::API);
        $result = $broker->run("getChildren", array("parentId" => "junk"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(0, count($content), "Content is not empty");
    }

    public function testGetChildrenOne() {
        $broker = new APIBroker(CategoriesAPITest::API);
        $parent = $this->createCat(null, "Parent", null, 1);
        $child = $this->createCat($parent->id, "Child", null, 2);
        $result = $broker->run("getChildren", array("parentId" => $parent->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(1, count($content), "Content size mismatch");
        $readChild = $content[0];
        $this->checkCatEquality($child, $readChild);
    }

    public function testGetChildrenMultiple() {
        $broker = new APIBroker(CategoriesAPITest::API);
        $parent = $this->createCat(null, "Parent", null, 1);
        $child1 = $this->createCat($parent->id, "Child", null, 2);
        $child2 = $this->createCat($parent->id, "Child2", null, 3);
        $child3 = $this->createCat($parent->id, "Child3", null, 4);
        $result = $broker->run("getChildren", array("parentId" => $parent->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(3, count($content), "Content size mismatch");
        $this->assertNotNull($content[0], "Children is null");
        $this->checkCatEquality($child1, $content[0]);
        $this->assertNotNull($content[1], "Children is null");
        $this->checkCatEquality($child2, $content[1]);
        $this->assertNotNull($content[2], "Children is null");
        $this->checkCatEquality($child3, $content[2]);
    }

    public function testGetAll() {
        $this->markTestIncomplete("What is the most logical output?");
    }
}
?>