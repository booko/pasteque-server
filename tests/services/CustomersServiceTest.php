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

class CustomersServiceTest extends \PHPUnit_Framework_TestCase {

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM CUSTOMERS") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testCreate() {
        $srv = new CustomersService();
        $cust = new Customer(1, "Cust", "It's me", "card", null, null, 12.0,
                10.0, 5.0, stdtimefstr("2012-01-01 00:00:00"), "It's", "me",
                "itsme@me.me", "012345", "23456", "11111", "Address1",
                "Address2", "59000", "City", "Region", "France", "Note", true);
        $id = $srv->create($cust);
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = "SELECT * FROM CUSTOMERS";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $id, "Create failed");
        $this->assertEquals($id, $row['ID'], "Id mismatch");
        $this->assertEquals(1, $row['TAXID'], "Number mismatch");
        $this->assertEquals("Cust", $row['SEARCHKEY'], "Key mismatch");
        $this->assertEquals("It's me", $row['NAME'], "Display name mismatch");
        $this->assertEquals("card", $row['CARD'], "Card mismatch");
        $this->assertEquals(null, $row['TAXCATEGORY'], "Tax cat mismatch");
        $this->assertEquals(null, $row['DISCOUNTPROFILE_ID'],
                "Discount profile id mismatch");
        $this->assertEquals(12.0, $row['PREPAID'], "Prepaid mismatch");
        $this->assertEquals(10.0, $row['MAXDEBT'], "Max debt mismatch");
        $this->assertEquals(5.0, $row['CURDEBT'], "Current debt mismatch");
        $this->assertEquals("2012-01-01 00:00:00", $row['CURDATE'],
                "Debt date mismatch");
        $this->assertEquals("It's", $row['FIRSTNAME'], "First name mismatch");
        $this->assertEquals("me", $row['LASTNAME'], "Last name mismatch");
        $this->assertEquals("itsme@me.me", $row['EMAIL'], "Email mismatch");
        $this->assertEquals("012345", $row['PHONE'], "Phone mismatch");
        $this->assertEquals("23456", $row['PHONE2'], "Phone 2 mismatch");
        $this->assertEquals("11111", $row['FAX'], "Fax mismatch");
        $this->assertEquals("Address1", $row['ADDRESS'], "Address mismatch");
        $this->assertEquals("Address2", $row['ADDRESS2'], "Address 2 mismatch");
        $this->assertEquals("59000", $row['POSTAL'], "Zipcode mismatch");
        $this->assertEquals("City", $row['CITY'], "City mismatch");
        $this->assertEquals("Region", $row['REGION'], "Region mismatch");
        $this->assertEquals("France", $row['COUNTRY'], "Country mismatch");
        $this->assertEquals("Note", $row['NOTES'], "Note mismatch");
        $this->assertEquals(true, $db->readBool($row['VISIBLE']),
                "Visible mismatch");
    }

    /** @depends testCreate */
    public function testRead() {
        $srv = new CustomersService();
        $cust = new Customer(1, "Cust", "It's me", "card", null, null, 12.0,
                10.0, 5.0, stdtimefstr("2012-01-01 00:00:00"), "It's", "me",
                "itsme@me.me", "012345", "23456", "11111", "Address1",
                "Address2", "59000", "City", "Region", "France", "Note", true);
        $id = $srv->create($cust);
        $read = $srv->get($id);
        $this->assertNotEquals(false, $read);
        $this->assertEquals($id, $read->id);
        $this->markTestIncomplete("Check all fields");
    }

    public function testReadInexistent() {
        $srv = new CustomersService();
        $read = $srv->get(0);
        $this->assertEquals(false, $read);
    }

    public function testGetAllEmpty() {
        $srv = new CustomersService();
        $read = $srv->getAll();
        $this->assertTrue(is_array($read), "Get all list is not an array");
        $this->assertEquals(0, count($read), "Get all list is not empty");
    }

    public function testGetAll() {
        $this->markTestIncomplete();
    }

    public function testGetAllHidden() {
        $this->markTestIncomplete();
    }

    /** @depends testCreate */
    public function testUpdate() {
        $this->markTestIncomplete();
    }

    public function testUpdateInexistent() {
        $this->markTestIncomplete();
    }

    /** @depends testUpdate */
    public function testAddPrepaid() {
        $this->markTestIncomplete();
    }

    /** @depends testUpdate */
    public function testAddDebt() {
        $this->markTestIncomplete();
    }

    /** @depends testUpdate */
    public function testRecoverDebt() {
        $this->markTestIncomplete();
    }

    /** @depends testCreate
     * @depends testReadInexistent
     */
    public function testDelete() {
        $srv = new CustomersService();
        $cust = new Customer(1, "Cust", "It's me", "card", null, null, 12.0,
                10.0, 5.0, stdtimefstr("2012-01-01 00:00:00"), "It's", "me",
                "itsme@me.me", "012345", "23456", "11111", "Address1",
                "Address2", "59000", "City", "Region", "France", "Note", true);
        $id = $srv->create($cust);
        $this->assertTrue($srv->delete($id));
        $read = $srv->get($id);
        $this->assertNull($srv->get($id));
    }

    public function testDeleteInexistent() {
        // TODO: is this behaviour a feature?
        $srv = new CustomersService();
        $this->assertTrue($srv->delete(0));
    }
}