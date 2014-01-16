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

class AttributeTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    protected function tearDown() {
    }

    public static function tearDownAfterClass() {
    }

    public function testConstruct() {
        $set = new AttributeSet("set");
        $this->assertEquals("set", $set->label);
        $this->assertTrue(is_array($set->attributes));
        $this->assertEquals(0, count($set->attributes));
        $attr = new Attribute("attr", 1);
        $set->addAttribute($attr);
        $this->assertEquals("attr", $attr->label);
        $this->assertTrue(is_array($attr->values));
        $this->assertEquals(0, count($attr->values));
        $this->assertEquals(1, $attr->dispOrder);
        $this->assertEquals(1, count($set->attributes));
        $val = new AttributeValue("Value");
        $this->assertEquals("Value", $val->value);
        $attr->addValue($val);
        $this->assertEquals(1, count($attr->values));
    }

}