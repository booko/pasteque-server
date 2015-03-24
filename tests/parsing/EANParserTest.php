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
 * test the function to parse EAN code
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class EANParserTest extends \PHPUnit_Framework_TestCase
{
    public function testThirteenNumbers()
    {
        $ean1 = \Pasteque\Parsing\parseEAN(1234567890123);
        $ean2 = \Pasteque\Parsing\parseEAN('1234567890123');
        
        $this->assertSame('1234567890123', $ean1);
        $this->assertSame('1234567890123', $ean2);
    }
    
    public function testTwelveLengthNumbers()
    {
        $this->assertSame('0123456789012',
              \Pasteque\Parsing\parseEAN(123456789012));
        $this->assertSame('0123456789012',
              \Pasteque\Parsing\parseEAN('123456789012'));
    }
    
    /**
     * @expectedException \Pasteque\Parsing\ParsingException
     */
    public function testParsingTooLongString()
    {
        \Pasteque\Parsing\parseEAN('12345678901234');
    }
    
    /**
     * @expectedException \Pasteque\Parsing\ParsingException
     */
    public function testParsingTooLongInt()
    {
        \Pasteque\Parsing\parseEAN(12345678901234);
    }
    
    /**
     * @expectedException \Pasteque\Parsing\ParsingException
     */
    public function testParsingTooShortString()
    {
        \Pasteque\Parsing\parseEAN('12345678901');
    }
    
    /**
     * @expectedException \Pasteque\Parsing\ParsingException
     */
    public function testParsingTooShortInt()
    {
        \Pasteque\Parsing\parseEAN(12345678901);
    }
}
