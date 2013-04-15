<?php
//    POS-Tech API
//
//    Copyright (C) 2012 Scil (http://scil.coop)
//
//    This file is part of POS-Tech.
//
//    POS-Tech is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    POS-Tech is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with POS-Tech.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque;

$action = $_GET['action'];
$ret = NULL;

switch ($action) {
case 'getAll':
    $location = NULL;
    if (isset($_GET['location'])) {
        $location = $_GET['location'];
        $location = StocksService::getLocationId($location);
        if ($location === NULL) {
            $ret = "ERROR: unknown location";
            break;
        }
    }
    $stocks = StocksService::getQties($location);
    $levels = StocksService::getLevels($location);
    $ret = array();
    foreach ($stocks as $prd => $qty) {
        $stock = new \StdClass();
        $stock->product_id = $prd;
        $stock->qty = $qty;
        $found = FALSE;
        foreach ($levels as $level) {
            if ($level->product_id == $prd) {
                $stock->security = $level->security;
                $stock->max = $level->max;
                $found = TRUE;
                break;
            }
        }
        if (!$found) {
            $stock->security = NULL;
            $stock->max = NULL;
        }
        $ret[] = $stock;
    }
    break;
}

echo(json_encode($ret));

?>
