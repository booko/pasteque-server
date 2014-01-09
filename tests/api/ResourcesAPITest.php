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

class ResourcesAPITest extends \PHPUnit_Framework_TestCase {

    const API = "ResourcesAPI";

    public static function setUpBeforeClass() {
        // Install empty database
        Installer::install(null);
    }

    public static function tearDownAfterClass() {
        // Erase database
        dropDatabase();
    }

    public function testGet() {
        $broker = new APIBroker(ResourcesAPITest::API);
        $result = $broker->run("get", array("label" => "Window.Logo"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals("9", $content->id, "Id mismatch");
        $this->assertEquals("Window.Logo", $content->label, "Label mismatch");
        $this->assertEquals(1, $content->type, "Type mismatch");
        $this->assertNotNull($content->content, "Content is null");
        $this->markTestIncomplete("Test content");
    }

}