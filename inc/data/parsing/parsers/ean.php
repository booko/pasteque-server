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

namespace Pasteque\Parsing;

/**
 * try to interpret a string or a int as a EAN code
 * 
 * @param string|int $ean
 * @return string
 * @throws ParsingException
 */
function parseEAN($ean) {
    $string = (string) $ean;
    if (strlen($string) === 0) {
        return null;
    }
    if (strlen($string) === 12) {
        return '0'.$string;
    } elseif (strlen($string) === 13) {
        return $string;
    } else {
        throw new ParsingException('impossible to parse "$ean"', 
              $ean, "Impossible to read the ean code '%s'");
    }
}

