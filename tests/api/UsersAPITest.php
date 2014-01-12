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

class UsersAPITest extends \PHPUnit_Framework_TestCase {

    const API = "UsersAPI";

    protected function tearDown() {
    }

    public function testGet() {
        $broker = new APIBroker(UsersAPITest::API);
        $result = $broker->run("get", array("id" => "0"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals("0", $content->id, "Id mismatch");
        $this->assertEquals("Administrator", $content->name, "Name mismatch");
        $this->assertEquals(null, $content->password, "Password mismatch");
        $this->assertEquals("0", $content->roleId, "Role id mismatch");
        $this->assertEquals(true, $content->visible, "Active mismatch");
        $this->assertEquals(false, $content->hasImage, "Image mismatch");
        $this->assertEquals(null, $content->card, "Card mismatch");
        $this->markTestIncomplete("Test with non-default values");
    }

    public function testGetAll() {
        $broker = new APIBroker(UsersAPITest::API);
        $result = $broker->run("getAll", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(3, count($content), "Content size mismatch");
        $this->markTestIncomplete("Check content");
    }
}