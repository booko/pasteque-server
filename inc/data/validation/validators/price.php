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

namespace Pasteque\Validators;

/**
 * Convert a string or a number to a float representing price
 * 
 * @param mixed $price
 * @return float
 */
function parsePrice($price)
{
    $stripped = str_replace(',', '.', $price);
    if(!is_numeric($stripped)) {
        throw new ParsingException("The string $price is not numeric");
    }
    return (float) $stripped;
}

function validatePrice($price)
{
    
}

class ParsingException extends \Exception {
    
}