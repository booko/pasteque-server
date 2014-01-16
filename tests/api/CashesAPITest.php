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

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM CLOSEDCASH") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testGetById() {
        $broker = new APIBroker("CashesAPI");
        $srv = new CashesService();
        // Init cash
        $cash = $srv->add("Host");
        $cash->openDate = stdtimefstr("2002-02-02 02:02:02");
        $cash->closeDate = stdtimefstr("2002-02-03 03:03:03");
        $srv->update($cash);
        // Get it through API
        $result = $broker->run("get", array("id" => $cash->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($cash->id, $content->id, "Id mismatch");
        $this->assertEquals($cash->host, $content->host, "Host mismatch");
        $this->assertEquals($cash->sequence, $content->sequence,
                "Sequence mismatch");
        $this->assertEquals($cash->openDate, $content->openDate,
                "Open date mismatch");
        $this->assertEquals($cash->closeDate, $content->closeDate,
                "Close date mismatch");
    }

    public function testGetOpenedByHost() {
        $broker = new APIBroker("CashesAPI");
        $srv = new CashesService();
        // Init cash
        $cash = $srv->add("Host");
        $cash->openDate = stdtimefstr("2002-02-02 02:02:02");
        $srv->update($cash);
        // Get it through API
        $result = $broker->run("get", array("host" => $cash->host));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($cash->id, $content->id, "Id mismatch");
        $this->assertEquals($cash->host, $content->host, "Host mismatch");
        $this->assertEquals($cash->sequence, $content->sequence,
                "Sequence mismatch");
        $this->assertEquals($cash->openDate, $content->openDate,
                "Open date mismatch");
    }

    public function testGetClosedByHost() {
        $broker = new APIBroker("CashesAPI");
        $srv = new CashesService();
        // Init cash
        $cash = $srv->add("Host");
        $cash->openDate = stdtimefstr("2002-02-02 02:02:02");
        $cash->closeDate = stdtimefstr("2002-02-03 03:03:03");
        $srv->update($cash);
        // Get it through API
        $result = $broker->run("get", array("host" => $cash->host));
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

    public function testGetInexistentHost() {
        $broker = new APIBroker("CashesAPI");
        $result = $broker->run("get", array("host" => "junk"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNull($content, "Content is not null");
    }

    public function testUpdateCreate() {
        $broker = new APIBroker("CashesAPI");
        $cash = new Cash("Host", 1, stdtimefstr("2002-02-02 02:02:02"),
                stdtimefstr("2002-02-03 03:03:03"));
        $result = $broker->run("update", array("cash" => json_encode($cash)));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertNotNull($content->id, "Id not created");
        $this->assertEquals($cash->host, $content->host, "Host mismatch");
        $this->assertEquals($cash->sequence, $content->sequence,
                "Sequence mismatch");
        $this->assertEquals($cash->openDate, $content->openDate,
                "Open date mismatch");
        $this->assertEquals($cash->closeDate, $content->closeDate,
                "Close date mismatch");
    }

    public function testUpdate() {
        $broker = new APIBroker("CashesAPI");
        $srv = new CashesService();
        $cash = $srv->add("Host");
       $cash->openDate = stdtimefstr("2002-02-02 02:02:02");
        $cash->closeDate = stdtimefstr("2002-02-03 03:03:03");
        $result = $broker->run("update", array("cash" => json_encode($cash)));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($cash->id, $content->id, "Id mismatch");
        $this->assertEquals($cash->host, $content->host, "Host mismatch");
        $this->assertEquals($cash->sequence, $content->sequence,
                "Sequence mismatch");
        $this->assertEquals($cash->openDate, $content->openDate,
                "Open date mismatch");
        $this->assertEquals($cash->closeDate, $content->closeDate,
                "Close date mismatch");
    }
}