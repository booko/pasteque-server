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

class PlacesAPITest extends \PHPUnit_Framework_TestCase {

    const API = "PlacesAPI";
    private $floors;

    protected function setUp() {
        $this->floors = array();
        // Create some floors and places
        $floor1 = new Floor("Floor", false);
        $floor1->id = PlacesService::createFloor($floor1, null);
        $placeA = new Place("A", 5, 10, $floor1->id);
        $placeB = new Place("B", 30, 50, $floor1->id);
        $placeA->id = PlacesService::createPlace($placeA);
        $placeB->id = PlacesService::createPlace($placeB);
        $floor1->addPlace($placeA);
        $floor1->addPlace($placeB);
        $floor2 = new Floor("Rooftop", false);
        $floor2->id = PlacesService::createFloor($floor2, null);
        $placeC = new Place("C", 10, 20, $floor2->id);
        $placeD = new Place("D", 50, 15, $floor2->id);
        $placeC->id = PlacesService::createPlace($placeC);
        $placeD->id = PlacesService::createPlace($placeD);
        $floor2->addPlace($placeC);
        $floor2->addPlace($placeD);
        $this->floors[] = $floor1;
        $this->floors[] = $floor2;
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM PLACES") === false
                || $pdo->exec("DELETE FROM FLOORS") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testGet() {
        $broker = new APIBroker(PlacesAPITest::API);
        $result = $broker->run("get", array("id" => $this->floors[0]->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $floor = $this->floors[0];
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($floor->id, $content->id, "Id mismatch");
        $this->assertEquals($floor->label, $content->label,
                "Name mismatch");
        $this->assertTrue(is_array($content->places), "Places is not an array");
        $this->assertEquals(2, count($content->places),
                "Places count mismatch");
        $place = $content->places[0];
        $this->assertNotNull($place->id, "Place id is null");
        $this->assertNotNull($place->x, "Place x is null");
        $this->assertNotNull($place->y, "Place y is null");
        $this->assertEquals($floor->id, $place->floorId, "Floor id mismatch");
        $this->markTestIncomplete("Check place values");
    }

    public function testGetAll() {
        $broker = new APIBroker(PlacesAPITest::API);
        $result = $broker->run("getAll", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(2, count($content), "Content size mismatch");
        $this->markTestIncomplete("Check content");
    }
}