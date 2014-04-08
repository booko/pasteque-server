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

class CashesAPITest extends \PHPUnit_Framework_TestCase {

    private $cashRegisterId;

    protected function setUp() {
        $srv = new LocationsService();
        $location = new Location("Location");
        $location->id = $srv->create($location);
        $srv = new CashRegistersService();
        $cashReg = new CashRegister("CashReg", $location->id, 1);
        $this->cashRegisterId = $srv->create($cashReg);
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM CLOSEDCASH") === false
                || $pdo->exec("DELETE FROM CASHREGISTERS") === false
                || $pdo->exec("DELETE FROM LOCATIONS") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testGetById() {
        $broker = new APIBroker("CashesAPI");
        $srv = new CashesService();
        // Init cash
        $cash = $srv->add($this->cashRegisterId);
        $cash->openDate = stdtimefstr("2002-02-02 02:02:02");
        $cash->closeDate = stdtimefstr("2002-02-03 03:03:03");
        $cash->openCash = 10.0;
        $cash->closeCash = 12.0;
        $cash->expectedCash = 25.0;
        $srv->update($cash);
        // Get it through API
        $result = $broker->run("get", array("id" => $cash->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($cash->id, $content->id, "Id mismatch");
        $this->assertEquals($cash->cashRegisterId, $content->cashRegisterId,
                "Cash register id mismatch");
        $this->assertEquals($cash->sequence, $content->sequence,
                "Sequence mismatch");
        $this->assertEquals($cash->openDate, $content->openDate,
                "Open date mismatch");
        $this->assertEquals($cash->closeDate, $content->closeDate,
                "Close date mismatch");
        $this->assertEquals($cash->openCash, $content->openCash,
                "Open cash mismatch");
        $this->assertEquals($cash->closeCash, $content->closeCash,
                "Close cash mismatch");
        $this->assertEquals($cash->expectedCash, $content->expectedCash,
                "Expected cash mismatch");
    }

    public function testGetOpenedByCashRegister() {
        $broker = new APIBroker("CashesAPI");
        $srv = new CashesService();
        // Init cash
        $cash = $srv->add($this->cashRegisterId);
        $cash->openDate = stdtimefstr("2002-02-02 02:02:02");
        $cash->openCash = 9.0;
        $srv->update($cash);
        // Get it through API
        $result = $broker->run("get",
                array("cashRegisterId" => $cash->cashRegisterId));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($cash->id, $content->id, "Id mismatch");
        $this->assertEquals($cash->cashRegisterId, $content->cashRegisterId,
                "Cash register id mismatch");
        $this->assertEquals($cash->sequence, $content->sequence,
                "Sequence mismatch");
        $this->assertEquals($cash->openDate, $content->openDate,
                "Open date mismatch");
        $this->assertEquals($cash->openCash, $content->openCash,
                "Open cash mismatch");
        $this->assertEquals($cash->closeCash, $content->closeCash,
                "Close cash mismatch");
        $this->assertEquals($cash->expectedCash, $content->expectedCash,
                "Expected cash mismatch");
    }

    public function testGetClosedByCashRegister() {
        $broker = new APIBroker("CashesAPI");
        $srv = new CashesService();
        // Init cash
        $cash = $srv->add($this->cashRegisterId);
        $cash->openDate = stdtimefstr("2002-02-02 02:02:02");
        $cash->closeDate = stdtimefstr("2002-02-03 03:03:03");
        $cash->openCash = 8.0;
        $cash->closeCash = 8.2;
        $srv->update($cash);
        // Get it through API
        $result = $broker->run("get",
                array("cashRegisterId" => $cash->cashRegisterId));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNull($content, "Content is not null");
    }

    public function testGetInexistentId() {
        $broker = new APIBroker("CashesAPI");
        $result = $broker->run("get", array("id" => "junk"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNull($content, "Content is not null");
    }

    public function testGetInexistentCashRegister() {
        $broker = new APIBroker("CashesAPI");
        $result = $broker->run("get", array("cashRegisterId" => "junk"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNull($content, "Content is not null");
    }

    public function testUpdateCreate() {
        $broker = new APIBroker("CashesAPI");
        $cash = new Cash($this->cashRegisterId, 1,
                stdtimefstr("2002-02-02 02:02:02"),
                stdtimefstr("2002-02-03 03:03:03"), 7.0, 15.0, 17.0);
        $result = $broker->run("update", array("cash" => json_encode($cash)));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertNotNull($content->id, "Id not created");
        $this->assertEquals($cash->cashRegisterId, $content->cashRegisterId,
                "Cash register id mismatch");
        $this->assertEquals($cash->sequence, $content->sequence,
                "Sequence mismatch");
        $this->assertEquals($cash->openDate, $content->openDate,
                "Open date mismatch");
        $this->assertEquals($cash->closeDate, $content->closeDate,
                "Close date mismatch");
        $this->assertEquals($cash->openCash, $content->openCash,
                "Open cash mismatch");
        $this->assertEquals($cash->closeCash, $content->closeCash,
                "Close cash mismatch");
        $this->assertEquals($cash->expectedCash, $content->expectedCash,
                "Expected cash mismatch");
    }

    public function testUpdate() {
        $broker = new APIBroker("CashesAPI");
        $srv = new CashesService();
        $cash = $srv->add($this->cashRegisterId);
        $cash->openDate = stdtimefstr("2002-02-02 02:02:02");
        $cash->closeDate = stdtimefstr("2002-02-03 03:03:03");
        $cash->openCash = 1.0;
        $cash->closeCash = 13.0;
        $cash->expectedCash = 15.0;
        $result = $broker->run("update", array("cash" => json_encode($cash)));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($cash->id, $content->id, "Id mismatch");
        $this->assertEquals($cash->cashRegisterId, $content->cashRegisterId,
                "Cash register id mismatch");
        $this->assertEquals($cash->sequence, $content->sequence,
                "Sequence mismatch");
        $this->assertEquals($cash->openDate, $content->openDate,
                "Open date mismatch");
        $this->assertEquals($cash->closeDate, $content->closeDate,
                "Close date mismatch");
        $this->assertEquals($cash->openCash, $content->openCash,
                "Open cash mismatch");
        $this->assertEquals($cash->closeCash, $content->closeCash,
                "Close cash mismatch");
        $this->assertEquals($cash->expectedCash, $content->expectedCash,
                "Expected cash mismatch");
    }
}