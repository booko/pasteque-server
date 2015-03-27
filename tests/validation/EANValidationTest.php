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
 * Test the validation of EAN-13 code
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class EANValidationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validEANProvider
     */
    public function testValidEAN($ean)
    {
        $this->assertTrue(\Pasteque\Validation\validateEAN($ean));
    }
    
    /**
     * return valid EAN code extracted from ean-search.org
     */
    public function validEANProvider()
    {
        return array(
           ['5413635006500'], ['9782212137354'], ['5425016570340'],
           ['5000009913016'], ['0236542358242'], ['7311271077190'],
           ['3268840001008'], ['4042588001105'], ['0885746108841'],
           ['3590800001095'], ['3561302508320'], ['5412038620009'] 
        );
    }
    
    /**
     * @expectedException \Pasteque\Validation\ValidationException
     */
    public function testInvalidEAN()
    {
        \Pasteque\Validation\validateEAN('4000009913016');
    }
    
    /**
     * @expectedException \Pasteque\Validation\ValidationException
     */
    public function testInvalidLengthEAN()
    {
        \Pasteque\Validation\validateEAN('40000099130165');
    }
    
    /**
     * @expectedException \Pasteque\Validation\ValidationException
     */
    public function testNotFullNumberEAN()
    {
        \Pasteque\Validation\validateEAN('a012345678912');
    }
}
