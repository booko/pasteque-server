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

class CashRegistersAPITest extends \PHPUnit_Framework_TestCase {

    private $location;

    protected function setUp() {
        $srv = new LocationsService();
        $location = new Location("Location");
        $location->id = $srv->create($location);
        $this->location = $location;
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM CASHREGISTERS") === false
                || $pdo->exec("DELETE FROM LOCATIONS") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testGetById() {
        $broker = new APIBroker("CashRegistersAPI");
        $srv = new CashRegistersService();
        // Init cash register
        $cashReg = new CashRegister("Cash", $this->location->id, 1);
        $id = $srv->create($cashReg);
        // Get it through API
        $result = $broker->run("get", array("id" => $id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($id, $content->id, "Id mismatch");
        $this->assertEquals($cashReg->label, $content->label, "Label mismatch");
        $this->assertEquals($cashReg->locationId, $content->locationId,
                "Location id mismatch");
    }

    public function testGetInexistentId() {
        $broker = new APIBroker("CashRegistersAPI");
        $result = $broker->run("get", array("id" => "junk"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNull($content, "Content is not null");
    }

}