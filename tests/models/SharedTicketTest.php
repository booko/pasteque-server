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

class SharedTicketTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    protected function tearDown() {
    }

    public static function tearDownAfterClass() {
    }

    public function testConstruct() {
        $tkt = new SharedTicket("Label", 0xae0c98);
        $this->assertEquals("Label", $tkt->label);
        $this->assertEquals(0xae0c98, $tkt->data);
    }

    public function testBuild() {
        $tkt = SharedTicket::__build(2, "Label", 0xae0c98);
        $this->assertEquals(2, $tkt->id);
        $this->assertEquals("Label", $tkt->label);
        $this->assertEquals(0xae0c98, $tkt->data);
    }
}