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

function validateEAN($ean)
{
    //test length
    if (strlen($ean) === 0) {
        return true;
    }
    if (strlen($ean) !== 13) {
        throw new ValidationException('EAN code is too long', $ean, "The EAN code '%s' is invalid");
    }

    //test is only digits
    if (\preg_match('/^[0-9]{13}$/', $ean) === 0) {
        throw new ValidationException('EAN code does contains non-number '
        . 'characters', $ean, "The EAN code '%s' is invalid");
    }

    //algorithm for EAN (https://fr.wikipedia.org/wiki/Code-barres_EAN#Cl.C3.A9_de_contr.C3.B4le)
    $numbers = str_split($ean);
    $even_sum = $numbers[0] + $numbers[2] + $numbers[4] + $numbers[6] + $numbers[8] + $numbers[10];
    $odd_sum = $numbers[1] + $numbers[3] + $numbers[5] + $numbers[7] + $numbers[9] + $numbers[11];
    $sum = $even_sum + $odd_sum * 3;
    $units = $sum % 10;
    $control = ($units === 0) ? 0 : 10 - $units;

    if ($control == $numbers[12]) {
        return true;
    } else {
        throw new ValidationException('EAN control code does not match. Expected '
        . $control . 'by calcul, matched ' . $numbers[12], $ean, "The EAN code '%s' is invalid");
    }
}
