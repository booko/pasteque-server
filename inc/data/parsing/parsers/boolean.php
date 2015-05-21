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

function parseBoolean($bool)
{
    if (is_bool($bool)) {
        return $bool;
    }
    if (is_null($bool)) {
        return false;
    }
    if (is_string($bool)) {
        switch($bool) {
            case 'true':
            case 'True':
            case 'TRUE':
            case '1':
                return true;
            case 'false':
            case 'FALSE':
            case 'False':
            case '0':
                return false;
            default:
                throw new ParsingException('Fail to convert '.$bool.
                      ' to boolean', $bool, "Impossible to parse boolean '%s'");
        }
    }
    if (is_int($bool)) {
        switch($bool) {
            case 1: return true;
            case 0: return false;
            default: throw new ParsingException('Fail to convert int '.$bool.
                  ' to boolean', $bool, "Impossible to parse boolean '%s'");
        }
    }
    
    throw new ParsingException('The type of expected boolean '.$bool.
          ' is not handled', $bool, "Impossible to parse boolean '%s'");
}
