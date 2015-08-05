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

class DiscountProfilesAPITest extends \PHPUnit_Framework_TestCase {

    const API = "DiscountProfilesAPI";

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

    public function testGet() {
        $prof = new DiscountProfile("Profile", 0.1);
        $srv = new DiscountProfilesService();
        $prof->id = $srv->create($prof);
        $broker = new APIBroker(DiscountProfilesAPITest::API);
        $result = $broker->run("get", array("id" => $prof->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->checkEquality($prof, $content);
    }

    public function testGetAll() {
        $prof = new DiscountProfile("Profile", 0.1);
        $srv = new DiscountProfilesService();
        $prof->id = $srv->create($prof);
        $prof2 = new DiscountProfile("Profile2", 0.5);
        $prof2->id = $srv->create($prof2);
        $broker = new APIBroker(DiscountProfilesAPITest::API);
        $result = $broker->run("getAll", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(2, count($content), "Content size mismatch");
        $toCheck = array($prof, $prof2);
        $count = 0;
        foreach ($content as $rprof) {
            $ref = null;
            $count++;
            if ($rprof->id == $prof->id) {
                $ref = $prof;
            } else if ($rprof->id == $prof2->id) {
                $ref = $prof2;
            }
            $this->assertNotNull($ref, "Unknown profile");
            $this->checkEquality($ref, $rprof);
            for ($i = 0; $i < count($toCheck); $i++) {
                $t = $toCheck[$i];
                if ($t->id == $ref->id) {
                    array_splice($toCheck, $i, 1);
                    break;
                }
            }
        }
        $this->assertEquals(0, count($toCheck), "Duplicated profiles");
    }
}