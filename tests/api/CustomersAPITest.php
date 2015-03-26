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

class CustomersAPITest extends \PHPUnit_Framework_TestCase {

    const API = "CustomersAPI";

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM CUSTOMERS") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    private function createCust($parentId, $label, $image, $dispOrder) {
        $this->markTestIncomplete();
    }

    private function checkCustEquality($expected, $read) {
        $this->assertEquals($expected->id, $read->id, "Id mismatch");
        $this->assertEquals($expected->number, $read->number,
                "Number mismatch");
        $this->assertEquals($expected->key, $read->key, "Key mismatch");
        $this->assertEquals($expected->dispName, $read->dispName,
                "Display name mismatch");
        $this->assertEquals($expected->card, $read->card, "Card mismatch");
        $this->assertEquals($expected->custTaxId, $read->custTaxId,
                "Tax cat mismatch");
        $this->assertEquals($expected->discountProfileId,
                $read->discountProfileId, "Discount profile id mismatch");
        $this->assertEquals($expected->prepaid, $read->prepaid,
                "Prepaid mismatch");
        $this->assertEquals($expected->maxDebt, $read->maxDebt,
                "Max debt mismatch");
        $this->assertEquals($expected->currDebt, $read->currDebt,
                "Current debt mismatch");
        $this->assertEquals($expected->debtDate, $read->debtDate,
                "Debt date mismatch");
        $this->assertEquals($expected->firstName, $read->firstName,
                "First name mismatch");
        $this->assertEquals($expected->lastName, $read->lastName,
                "Last name mismatch");
        $this->assertEquals($expected->email, $read->email, "Email mismatch");
        $this->assertEquals($expected->phone1, $read->phone1, "Phone mismatch");
        $this->assertEquals($expected->phone2, $read->phone2,
                "Phone 2 mismatch");
        $this->assertEquals($expected->fax, $read->fax, "Fax mismatch");
        $this->assertEquals($expected->addr1 ,$read->addr1, "Address mismatch");
        $this->assertEquals($expected->addr2, $read->addr2,
                "Address 2 mismatch");
        $this->assertEquals($expected->zipCode, $read->zipCode,
                "Zipcode mismatch");
        $this->assertEquals($expected->city, $read->city, "City mismatch");
        $this->assertEquals($expected->region, $read->region,
                "Region mismatch");
        $this->assertEquals($expected->country, $read->country,
                "Country mismatch");
        $this->assertEquals($expected->note, $read->note, "Note mismatch");
        // TODO postgresql boolean
        $this->assertEquals($expected->visible, $read->visible,
                "Visible mismatch");

    }

    public function testGetInexistent() {
        $broker = new APIBroker(CustomersAPITest::API);
        $result = $broker->run("get", array("id" => "junk"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNull($content, "Junk id returned something");
    }

    public function testGet() {
        $broker = new APIBroker(CustomersAPITest::API);
        $srv = new CustomersService();
        $cust = new Customer(1, "Cust", "It's me", "card", null, null, 12.0,
                10.0, 5.0, stdtimefstr("2012-01-01 00:00:00"), "It's", "me",
                "itsme@me.me", "012345", "23456", "11111", "Address1",
                "Address2", "59000", "City", "Region", "France", "Note", true);
        $id = $srv->create($cust);
        $cust->id = $id;
        $result = $broker->run("get", array("id" => $id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Customer not found");
        $this->checkCustEquality($cust, $content);
    }

    public function testGetAllEmpty() {
        $broker = new APIBroker(CustomersAPITest::API);
        $result = $broker->run("getAll", array());
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(0, count($content), "Empty getAll returned something");
    }

    public function testGetAll() {
        $this->markTestIncomplete();
    }

    public function testAddPrepaid() {
        $this->markTestIncomplete();
    }

    public function testCreate() {
        $broker = new APIBroker(CustomersAPITest::API);
        $srv = new CustomersService();
        $cust = new Customer(null, "Cust", "It's me", "card", null, null, 12.0,
                10.0, 5.0, stdtimefstr("2012-01-01 00:00:00"), "It's", "me",
                "itsme@me.me", "012345", "23456", "11111", "Address1",
                "Address2", "59000", "City", "Region", "France", "Note", true);
        unset($cust->id);
        $result = $broker->run("save", array("customer" => json_encode($cust)));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $id = $content['saved'][0];
        $cust->id = $id;
        $this->assertNotNull($content, "Result not found");
        $read = $srv->get($id);
        $this->checkCustEquality($cust, $read);
    }

    public function testCreateMultiple() {
        $this->markTestIncomplete();
    }
}