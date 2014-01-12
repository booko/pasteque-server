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

class CategoriesServiceTest extends \PHPUnit_Framework_TestCase {

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        $child = "DELETE FROM CATEGORIES WHERE PARENTID IS NOT NULL";
        if ($pdo->exec($child) === false
                || $pdo->exec("DELETE FROM CATEGORIES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testCreate() {
        $category = new Category(null, "Test", true, 1);
        $id = CategoriesService::createCat($category, 0xaa);
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = "SELECT * FROM CATEGORIES";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $id, "Create failed");
        $this->assertEquals($id, $row['ID'], "Inconsistent returned id");
        $this->assertEquals("Test", $row['NAME'],
                "Inconsistent label after create");
        $this->assertEquals(null, $row['PARENTID'],
                "Inconsistent parent id after create");
        $this->assertEquals(0xaa, $db->readBin($row['IMAGE']),
                "Inconsistent image after create");
        $this->assertEquals(1, $row['DISPORDER'],
                "Inconsistent display order after create");
    }

    /** @depends testCreate */
    public function testGetImage() {
        $category = new Category(null, "Test", true, 1);
        $id = CategoriesService::createCat($category, 0xaa);
        $img = CategoriesService::getImage($id);
        $this->assertEquals(0xaa, $img);
    }

    /** @depends testCreate */
    public function testRead() {
        $category = new Category(null, "Test", 0xaa, 1);
        $id = CategoriesService::createCat($category);
        $read = CategoriesService::get($id);
        $this->assertEquals($id, $read->id);
        $this->assertEquals($category->label, $read->label);
        $this->assertEquals($category->parentId, $read->parentId);
        $this->assertEquals($category->dispOrder, $read->dispOrder);
    }

    public function testReadInexistent() {
        $read = CategoriesService::get(0);
        $this->assertEquals(false, $read);
    }

    /** @depends testCreate */
    public function testUpdate() {
        $category = new Category(null, "Test", true, 1);
        $category2 = new Category(null, "Parent", null, 0);
        $id = CategoriesService::createCat($category, 0xaa);
        $id2 = CategoriesService::createCat($category2);
        $category->id = $id;
        $category->label = "Updated";
        $category->dispOrder = 3;
        $category->parentId = $id2;
        $ret = CategoriesService::updateCat($category, 0xbb);
        $this->assertNotEquals(false, $ret, "Update failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = "SELECT * FROM CATEGORIES WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id);
        $this->assertNotEquals(false, $stmt->execute(), "Query failed");
        $row = $stmt->fetch();
        $this->assertEquals($id, $row['ID'], "Id was modified");
        $this->assertEquals("Updated", $row['NAME'], "Label update failed");
        $this->assertEquals($id2, $row['PARENTID'], "Parent id update failed");
        $this->assertEquals(0xbb, $db->readBin($row['IMAGE']),
                "Image update failed");
        $this->assertEquals(3, $row['DISPORDER'], "Display order update failed");
    }

    public function testUpdateInexistent() {
        $category = Category::__build(0, null, "Test", 0xaa, 1);
        $this->assertFalse(CategoriesService::updateCat($category));
    }

    /** @depends testUpdate */
    public function testUpdateKeepImg() {
        $category = new Category(null, "Test", true, 1);
        $category2 = new Category(null, "Parent", null, 0);
        $id = CategoriesService::createCat($category, 0xaa);
        $id2 = CategoriesService::createCat($category2);
        $category->id = $id;
        $category->label = "Updated";
        $category->image = "";
        $category->dispOrder = 3;
        $category->parentId = $id2;
        $ret = CategoriesService::updateCat($category);
        $this->assertNotEquals(false, $ret, "Update failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = "SELECT * FROM CATEGORIES WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id);
        $this->assertNotEquals(false, $stmt->execute(), "Query failed");
        $row = $stmt->fetch();
        $this->assertEquals($id, $row['ID'], "Id was modified");
        $this->assertEquals("Updated", $row['NAME'], "Label update failed");
        $this->assertEquals($id2, $row['PARENTID'], "Parent id update failed");
        $this->assertEquals(0xaa, $db->readBin($row['IMAGE']),
                "Image keeping failed");
        $this->assertEquals(3, $row['DISPORDER'], "Display order update failed");
    }

    /** @depends testCreate
     * @depends testReadInexistent
     */
    public function testDelete() {
        $category = new Category(null, "Test", true, 1);
        $id = CategoriesService::createCat($category, 0xaa);
        $this->assertTrue(CategoriesService::deleteCat($id));
        $read = CategoriesService::get($id);
        $this->assertNull(CategoriesService::get($id));
    }

    public function testDeleteInexistent() {
        // TODO: is this behaviour a feature?
        $this->assertTrue(CategoriesService::deleteCat(0));
    }
}