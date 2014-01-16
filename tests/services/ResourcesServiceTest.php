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

class ResourcesServiceTest extends \PHPUnit_Framework_TestCase {

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM RESOURCES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    private function checkEquality($ref, $read) {
        $this->assertEquals($ref->id, $read->id, "Id mismatch");
        $this->assertEquals($ref->label, $read->label, "Label mismatch");
        $this->assertEquals($ref->type, $read->type, "Type mismatch");
        $this->assertEquals($ref->content, $read->content, "Content mismatch");
    }

    public function testCreateText() {
        $res = new Resource("Test", Resource::TYPE_TEXT, "Resource content");
        $srv = new ResourcesService();
        $res->id = $srv->create($res);
        $this->assertNotEquals(false, $res->id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = "SELECT * FROM RESOURCES WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $res->id);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "Nothing found");
        $this->assertEquals($res->id, $row['ID'], "Id mismatch");
        $this->assertEquals($res->label, $row['NAME'], "Label mismatch");
        $this->assertEquals($res->type, $row['RESTYPE'], "Type mismatch");
        $this->assertEquals($res->content, $db->readBin($row['CONTENT']),
                "Content mismatch");
        $this->assertFalse($stmt->fetch(), "Too much resources matching");
    }

    public function testCreateImage() {
        $res = new Resource("Test", Resource::TYPE_IMAGE, 0xab93cd);
        $srv = new ResourcesService();
        $res->id = $srv->create($res);
        $this->assertNotEquals(false, $res->id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = "SELECT * FROM RESOURCES WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $res->id);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "Nothing found");
        $this->assertEquals($res->id, $row['ID'], "Id mismatch");
        $this->assertEquals($res->label, $row['NAME'], "Label mismatch");
        $this->assertEquals($res->type, $row['RESTYPE'], "Type mismatch");
        $this->assertEquals($res->content, $db->readBin($row['CONTENT']),
                "Content mismatch");
        $this->assertFalse($stmt->fetch(), "Too much resources matching");
    }

    public function testReadInexistent() {
        $srv = new ResourcesService();
        $read = $srv->get("junk");
        $this->assertEquals(null, $read);
    }

    /** @depends testCreateText */
    public function testReadText() {
        $res = new Resource("Test", Resource::TYPE_TEXT, "Resource content");
        $srv = new ResourcesService();
        $res->id = $srv->create($res);
        $read = $srv->get($res->id);
        $this->checkEquality($res, $read);
    }

    /** @depends testCreateImage */
    public function testReadImage() {
        $res = new Resource("Test", Resource::TYPE_TEXT, 0xab93cd);
        $srv = new ResourcesService();
        $res->id = $srv->create($res);
        $read = $srv->get($res->id);
        $this->checkEquality($res, $read);
    }
 
    /** @depends testCreateText */
    public function testSearchLabel() {
        $res = new Resource("Test", Resource::TYPE_TEXT, "Resource content");
        $srv = new ResourcesService();
        $res->id = $srv->create($res);
        $read = $srv->search(array(array("label", "=", $res->label)));
        $this->assertTrue(is_array($read), "Search failed");
        $this->assertEquals(1, count($read), "Search result count mismatch");
        $this->checkEquality($res, $read[0]);
    }

    /** @depends testCreateText
     * @depends testReadText
     */
    public function testUpdate() {
        $this->markTestIncomplete();
    }

    public function testUpdateInexistent() {
        $this->markTestIncomplete();
    }

    /** @depends testCreateText */
    public function testDelete() {
        $this->markTestIncomplete();
    }

}