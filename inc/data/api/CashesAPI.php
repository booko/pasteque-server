<?php
//    Pastèque Web back office
//
//    Copyright (C) 2013 Scil (http://scil.coop)
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

namespace Pasteque;

if (@constant("\Pasteque\ABSPATH") === NULL) {
    die();
}

$action = $_GET['action'];
$ret = NULL;

switch ($action) {
case 'get':
    if (!isset($_GET['host'])) {
        $ret = FALSE;
        break;
    }
    $ret = CashesService::getHost($_GET['host']);
    if ($ret == NULL || $ret->isClosed()) {
        // Create a new one
        if (CashesService::add($_GET['host'])) {
            $ret = CashesService::getHost($_GET['host']);
        }
    }
    break;
case 'update':
    $json = json_decode($_POST['cash'], true);
    $cash = Cash::__form($json);
    if ($cash == NULL) {
        $ret = FALSE;
        break;
    }
    $ret = array();
    $ret['result'] = CashesService::update($cash);
    $lastCash = CashesService::getHost($host);
    if ($lastCash != NULL && $lastCash->isClosed()) {
        if (CashesService::add($host)) {
            $newCash = CashesService::getHost($host);
        } else {
            $newCash = NULL;
        }
        $ret['cash'] = $newCash;
    } else {
        $ret['cash'] = $lastCash;
    }
    break;
}

echo(json_encode($ret));

?>
