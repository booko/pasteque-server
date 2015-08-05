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

class ProviderTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    protected function tearDown() {
    }

    public static function tearDownAfterClass() {
    }

    public function testConstruct() {
        $prov = new Provider("Provider", true, "First", "Last", "email",
                "phone1", "phone2", "website", "fax", "addr1", "addr2", "zip",
                "city", "region", "country", "note", true, 1);
        $this->assertEquals("Provider", $prov->label);
        $this->assertEquals(true, $prov->hasImage);
        $this->assertEquals("First", $prov->firstName);
        $this->assertEquals("Last", $prov->lastName);
        $this->assertEquals("email", $prov->email);
        $this->assertEquals("phone1", $prov->phone1);
        $this->assertEquals("phone2", $prov->phone2);
        $this->assertEquals("website", $prov->website);
        $this->assertEquals("fax", $prov->fax);
        $this->assertEquals("addr1", $prov->addr1);
        $this->assertEquals("addr2", $prov->addr2);
        $this->assertEquals("zip", $prov->zipCode);
        $this->assertEquals("city", $prov->city);
        $this->assertEquals("region", $prov->region);
        $this->assertEquals("country", $prov->country);
        $this->assertEquals("note", $prov->notes);
        $this->assertEquals(true, $prov->visible);
        $this->assertEquals(1, $prov->dispOrder);
    }

}