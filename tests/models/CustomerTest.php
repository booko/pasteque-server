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

class CustomerTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    protected function tearDown() {
    }

    public static function tearDownAfterClass() {
    }

    public function testConstructEmpty() {
        $cust = new Customer(1, "Cust", "It's me", "card", 0, 1, 12.0, 10.0,
                5.0, stdtimefstr("2012-01-01 00:00:00"), "It's", "me",
                "itsme@me.me", "012345", "23456", "11111", "Address1",
                "Address2", "59000", "City", "Region", "France", "Note", true);
        $this->assertEquals(1, $cust->number);
        $this->assertEquals("Cust", $cust->key);
        $this->assertEquals("It's me", $cust->dispName);
        $this->assertEquals("card", $cust->card);
        $this->assertEquals(0, $cust->custTaxId);
        $this->assertEquals(1, $cust->discountProfileId);
        $this->assertEquals(12.0, $cust->prepaid);
        $this->assertEquals(10.0, $cust->maxDebt);
        $this->assertEquals(5.0, $cust->currDebt);
        $this->assertEquals(stdtimefstr("2012-01-01 00:00:00"),
                $cust->debtDate);
        $this->assertEquals("It's", $cust->firstName);
        $this->assertEquals("me", $cust->lastName);
        $this->assertEquals("itsme@me.me", $cust->email);
        $this->assertEquals("012345", $cust->phone1);
        $this->assertEquals("23456", $cust->phone2);
        $this->assertEquals("11111", $cust->fax);
        $this->assertEquals("Address1", $cust->addr1);
        $this->assertEquals("Address2", $cust->addr2);
        $this->assertEquals("59000", $cust->zipCode);
        $this->assertEquals("City", $cust->city);
        $this->assertEquals("Region", $cust->region);
        $this->assertEquals("France", $cust->country);
        $this->assertEquals("Note", $cust->note);
        $this->assertTrue($cust->visible);
    }

}