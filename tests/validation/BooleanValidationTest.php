<?php

//    Pastèque Web back office
//
//    Copyright (C) 2015 Scil (http://scil.coop)
//
//    This file is part of Pastèque.
//
//    Pastèque is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Pastèque is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Pastèque.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque\Tests\Validation;


require_once(dirname(dirname(__FILE__)) . "/common_load.php");

/**
 * test the validation of boolean values
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class BooleanValidationTest extends \PHPUnit_Framework_TestCase
{
    public function testParseBoolean()
    {
        $this->assertSame(true, \Pasteque\Validation\validateBoolean(true));
        $this->assertSame(true, \Pasteque\Validation\validateBoolean(false));
    }
    
   /**
     * 
     * @param mixed $value the value to test
     * @dataProvider provideIncorrectValues
     * @expectedException \Pasteque\Validation\ValidationException
     */
    public function testIncorrectValues($value)
    {
        \Pasteque\Validation\validateBoolean($value);
    }
    
    public function provideIncorrectValues()
    {
        return array(
           [2], [3], [-1], [-2], //incorrect int
           ['abcde'], ['Les chèvres aiment les orties'], //incorrect strings
           [1.2], [4.5], //incorrect float
           ['true'], ['TRUE'], ['1'], //correct string but not parsed
           [1], [0], //correct int but should be parsed
        );
    }
}
