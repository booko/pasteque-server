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

class DiscountProfilesServiceTest extends \PHPUnit_Framework_TestCase {

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM DISCOUNTPROFILES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    private function checkEquality($ref, $read) {
        $this->assertEquals($ref->id, $read->id, "Id mismatch");
        $this->assertEquals($ref->label, $read->label, "Label mismatch");
        $this->assertEquals($ref->rate, $read->rate, "Rate mismatch");
    }

    public function testCreate() {
        $prof = new DiscountProfile("Profile", 0.1);
        $srv = new DiscountProfilesService();
        $prof->id = $srv->create($prof);
        $this->assertNotEquals(false, $prof->id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM DISCOUNTPROFILES";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "Nothing found");
        $this->assertEquals($prof->id, $row['ID'], "Id mismatch");
        $this->assertEquals($prof->label, $row['NAME'], "Label mismatch");
        $this->assertEquals($prof->rate, $row['RATE'], "Rate mismatch");
    }

    /** @depends testCreate */
    public function testRead() {
        $prof = new DiscountProfile("Profile", 0.1);
        $srv = new DiscountProfilesService();
        $prof->id = $srv->create($prof);
        $read = $srv->get($prof->id);
        $this->assertNotNull($read, "Nothing found");
        $this->checkEquality($prof, $read);
    }

    public function testReadInexistent() {
        $srv = new DiscountProfilesService();
        $read = $srv->get(0);
        $this->assertEquals(null, $read);
    }

    /** @depends testCreate
     * @depends testRead
     */
    public function testUpdate() {
        $prof = new DiscountProfile("Profile", 0.1);
        $srv = new DiscountProfilesService();
        $prof->id = $srv->create($prof);
        $prof->label = "Edited";
        $prof->rate = 0.5;
        $this->assertTrue($srv->update($prof), "Update failed");
        $read = $srv->get($prof->id);
        $this->checkEquality($prof, $read);
    }

    public function testUpdateInexistent() {
        $this->markTestIncomplete();
    }

    /** @depends testCreate
     * @depends testRead
     */
    public function testDelete() {
        $prof = new DiscountProfile("Profile", 0.1);
        $srv = new DiscountProfilesService();
        $prof->id = $srv->create($prof);
        $this->assertTrue($srv->delete($prof->id), "Delete failed");
        $this->assertNull($srv->get($prof->id), "Profile is still there");
    }

}