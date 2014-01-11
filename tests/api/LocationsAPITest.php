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

class LocationsAPITest extends \PHPUnit_Framework_TestCase {

    const API = "LocationsAPI";
    private $locations;

    public static function setUpBeforeClass() {
        // Install empty database
        Installer::install(null);
    }

    protected function setUp() {
        $this->locations = array();
        $srv = new LocationsService();
        $loc1 = new Location("Location1");
        $loc1->id = $srv->create($loc1);
        $loc2 = new Location("Location2");
        $loc2->id = $srv->create($loc2);
        $this->locations[] = $loc1;
        $this->locations[] = $loc2;
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM LOCATIONS") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public static function tearDownAfterClass() {
        // Erase database
        dropDatabase();
    }

    public function testGet() {
        $broker = new APIBroker(LocationsAPITest::API);
        $result = $broker->run("get", array("id" => $this->locations[0]->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $loc = $this->locations[0];
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($loc->id, $content->id, "Id mismatch");
        $this->assertEquals($loc->label, $content->label, "Label mismatch");
    }

    public function testGetInexistent() {
        $broker = new APIBroker(LocationsAPITest::API);
        $result = $broker->run("get", array("id" => "junk"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNull($content, "Something was found with junk id");
    }

    public function testGetAll() {
        $broker = new APIBroker(LocationsAPITest::API);
        $result = $broker->run("getAll", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(2, count($content), "Content size mismatch");
        foreach($content as $loc) {
            if ($loc->id == $this->locations[0]->id) {
                $ref = $this->locations[0];
            } else if ($loc->id == $this->locations[1]->id) {
                $ref = $this->locations[1];
            } else {
                $this->assertTrue(false, "Unknown location id returned");
            }
            $this->assertEquals($loc->id, $ref->id, "Id mismatch");
            $this->assertEquals($loc->label, $ref->label, "Label mismatch");
        }
    }
}