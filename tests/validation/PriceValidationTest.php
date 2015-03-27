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
 * test the function of price validation
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class PriceValidationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Pasteque\Validation\ValidationException
     */
    public function testNegativePrice()
    {
        \Pasteque\Validation\validatePrice(-1);
    }
    
    public function testValidPrice()
    {
        $this->assertTrue(\Pasteque\Validation\validatePrice(10));
        $this->assertTrue(\Pasteque\Validation\validatePrice(0));
    }
    
    /**
     * @expectedException \Pasteque\Validation\ValidationException
     */
    public function testStringPrice()
    {
        \Pasteque\Validation\validatePrice('not a price');
    }
}
