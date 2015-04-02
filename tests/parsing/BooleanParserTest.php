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

namespace Pasteque\Tests\Parsing;

require_once(dirname(dirname(__FILE__)) . "/common_load.php");

/**
 * Description of BooleanParserTest
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class BooleanParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseBoolean()
    {
        $this->assertSame(true, \Pasteque\Parsing\parseBoolean(true));
        $this->assertSame(false, \Pasteque\Parsing\parseBoolean(false));
    }
    
    public function testParseInt()
    {
        $this->assertSame(True, \Pasteque\Parsing\parseBoolean(1));
        $this->assertSame(False, \Pasteque\Parsing\parseBoolean(0));
    }
    
    public function testParseString()
    {
        $this->assertSame(True, \Pasteque\Parsing\parseBoolean('true'));
        $this->assertSame(False, \Pasteque\Parsing\parseBoolean('false'));
        $this->assertSame(True, \Pasteque\Parsing\parseBoolean('TRUE'));
        $this->assertSame(False, \Pasteque\Parsing\parseBoolean('FALSE'));
        $this->assertSame(True, \Pasteque\Parsing\parseBoolean('True'));
        $this->assertSame(False, \Pasteque\Parsing\parseBoolean('False'));
        $this->assertSame(True, \Pasteque\Parsing\parseBoolean('1'));
        $this->assertSame(False, \Pasteque\Parsing\parseBoolean('0'));
    }
    
    /**
     * 
     * @param mixed $value the value to test
     * @dataProvider provideIncorrectValues
     * @expectedException \Pasteque\Parsing\ParsingException
     */
    public function testIncorrectValues($value)
    {
        \Pasteque\Parsing\parseBoolean($value);
    }
    
    public function provideIncorrectValues()
    {
        return array(
           [2], [3], [-1], [-2], //incorrect int
           ['abcde'], ['Les chèvres aiment les orties'], //incorrect strings
           [1.2], [4.5], //incorrect float
        );
    }
}
