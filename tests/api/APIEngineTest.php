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

class APIEngineTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    protected function tearDown() {
    }

    public static function tearDownAfterClass() {
    }

    public function testConstructEmpty() {
        $broker = new APIBroker("api");
        $this->assertEquals("api", $broker->getAPIName());
    }

    public function testAPIResult() {
        $result = APIResult::success("content");
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Success result status failed");
        $this->assertEquals("content", $result->content,
                "Success result content failed");
        $result = APIResult::reject("content");
        $this->assertEquals(APIResult::STATUS_CALL_REJECTED, $result->status,
                "Rejected result status failed");
        $this->assertEquals("content", $result->content,
                "Rejected result content failed");
        $result = APIResult::fail("content");
        $this->assertEquals(APIResult::STATUS_CALL_ERROR, $result->status,
                "Fail result status failed");
        $this->assertEquals("content", $result->content,
                "Fail result content failed");
    }

    /** @depends testConstructEmpty
     * @depends testAPIResult
     */
    public function testNullAPI() {
        $broker = new APIBroker(null);
        $result = $broker->run("action", null);
        $this->assertEquals(APIResult::STATUS_CALL_REJECTED, $result->status,
                "Rejected status failed");
        $this->assertEquals(APIError::$REJ_WRONG_API, $result->content,
                "Rejected content failed");
    }

    /** @depends testConstructEmpty
     * @depends testAPIResult
     */
    public function testInexistentAPI() {
        $broker = new APIBroker("You_won_t_find_me");
        $result = $broker->run("action", null);
        $this->assertEquals(APIResult::STATUS_CALL_REJECTED, $result->status,
                "Rejected status failed");
        $this->assertEquals(APIError::$REJ_WRONG_API, $result->content,
                "Rejected content failed");        
    }

    /** @depends testConstructEmpty
     * @depends testAPIResult
     */
    public function testDummyReject() {
        $broker = new APIBroker("DummyAPI");
        $result = $broker->run("Reject me", null);
        $this->assertEquals(APIResult::STATUS_CALL_REJECTED, $result->status,
                "Rejected status failed");
        $this->assertEquals(APIError::$REJ_WRONG_PARAMS, $result->content,
                "Rejected content failed");
    }

    /** @depends testConstructEmpty
     * @depends testAPIResult
     */
    public function testDummySuccess() {
        $broker = new APIBroker("DummyAPI");
        $result = $broker->run("succeed", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Succeed status failed");
        $this->assertEquals("I'm Dummy!", $result->content,
                "Succeed content failed");
    }

    /** @depends testConstructEmpty
     * @depends testAPIResult
     */
    public function testDummyFail() {
        $broker = new APIBroker("DummyAPI");
        $result = $broker->run("fail", null);
        $this->assertEquals(APIResult::STATUS_CALL_ERROR, $result->status,
                "Fail status failed");
        $this->assertEquals("I'm Dummy!", $result->content,
                "Fail content failed");
    }

    /** @depends testConstructEmpty
     * @depends testAPIResult
     */
    public function testDummySuccessParam() {
        $broker = new APIBroker("DummyAPI");
        $result = $broker->run("param", array("result" => "succeed"));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Succeed status failed");
        $this->assertEquals("I'm Dummy!", $result->content,
                "Succeed content failed");
    }

    /** @depends testConstructEmpty
     * @depends testAPIResult
     */
    public function testDummyFailParam() {
        $broker = new APIBroker("DummyAPI");
        $result = $broker->run("param", array("result" => "fail"));
        $this->assertEquals(APIResult::STATUS_CALL_ERROR, $result->status,
                "Fail status failed");
        $this->assertEquals("I'm Dummy!", $result->content,
                "Fail content failed");
    }

}
?>