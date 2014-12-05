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

class PaymentModeTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    protected function tearDown() {
    }

    public static function tearDownAfterClass() {
    }

    public function testConstruct() {
        $rules = array(new PaymentModeRule(0.0, PaymentModeRule::CREDIT_NOTE),
                new PaymentModeRule(1.0, PaymentModeRule::GIVE_BACK));
        $values = array(new PaymentModeValue(10, "label_10", 1),
                new PaymentModeValue(20, "label_20", 2));
        $mode = new PaymentMode("code", "label" , PaymentMode::CUST_ASSIGNED,
                false, $rules, $values, true, false, true, 1);
        $this->assertEquals("code", $mode->code);
        $this->assertEquals("label", $mode->label);
        $this->assertEquals(PaymentMode::CUST_ASSIGNED, $mode->flags);
        $this->assertEquals(false, $mode->hasImage);
        $this->assertEquals(true, $mode->active);
        $this->assertEquals(false, $mode->system);
        $this->assertEquals(true, $mode->cs);
        $this->assertEquals(1, $mode->dispOrder);
        $this->assertTrue(is_array($mode->rules));
        $this->assertEquals(2, count($mode->rules));
        $this->assertEquals(0.0, $mode->rules[0]->minVal);
        $this->assertEquals(PaymentModeRule::CREDIT_NOTE,
                $mode->rules[0]->rule);
        $this->assertEquals(1.0, $mode->rules[1]->minVal);
        $this->assertEquals(PaymentModeRule::GIVE_BACK, $mode->rules[1]->rule);
        $this->assertTrue(is_array($mode->values));
        $this->assertEquals(2, count($mode->values));
        $this->assertEquals(10, $mode->values[0]->value);
        $this->assertEquals("label_10", $mode->values[0]->resource);
        $this->assertEquals(1, $mode->values[0]->dispOrder);
        $this->assertEquals(20, $mode->values[1]->value);
        $this->assertEquals("label_20", $mode->values[1]->resource);
        $this->assertEquals(2, $mode->values[1]->dispOrder);
    }

    /** @depends testConstruct */
    public function testBuild() {
        $rules = array(new PaymentModeRule(0.0, PaymentModeRule::CREDIT_NOTE),
                new PaymentModeRule(1.0, PaymentModeRule::GIVE_BACK));
        $values = array(new PaymentModeValue(10, "label_10", 1),
                new PaymentModeValue(20, "label_20", 2));
        $mode = PaymentMode::__build(1, "code", "label",
                PaymentMode::CUST_ASSIGNED, false, $rules, $values, true, false,
                true, 1);
        $this->assertEquals(1, $mode->id);
        $this->assertEquals("code", $mode->code);
    }

}