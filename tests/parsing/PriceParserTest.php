<?php

//    Pasteque server testing
//
//    Copyright (C) 2015 Scil (http://scil.coop)
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


namespace Pasteque\Tests\Parsing;

require_once(dirname(dirname(__FILE__)) . "/common_load.php");

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class PriceParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testOk()
    {
        $this->assertTrue(true);
    }
    
    public function testPriceWithDot() 
    {
        $this->assertSame(10.1, \Pasteque\Parsing\parsePrice('10.1'));
        $this->assertSame(10.1, \Pasteque\Parsing\parsePrice(10.1));
    }
    
    public function testPriceWithComma() 
    {
        $this->assertSame(10.1, \Pasteque\Parsing\parsePrice('10,1'));
    }
    
    /**
     * @expectedException Pasteque\Parsing\ParsingException
     */
    public function testInvalidString()
    {
        \Pasteque\Parsing\parsePrice('I am a string');
    }
    
}
