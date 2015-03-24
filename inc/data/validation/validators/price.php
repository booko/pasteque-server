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

namespace Pasteque\Validation;

/**
 * return true if price is valid, and throw a ValidationException
 * if price is not valid
 * 
 * @param float $price
 * @return boolean
 * @throws \Pasteque\Validators\ValidationException
 */
function validatePrice($price)
{
    if (!is_numeric($price) OR $price < 0) {
        throw new \Pasteque\Validation\ValidationException(
              'price is not valid', $price, "The price '%s' is not valid");
    }
    
    return true;
}

