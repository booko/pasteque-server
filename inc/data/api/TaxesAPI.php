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

$action = $_GET['action'];
$ret = NULL;

switch ($action) {
case 'get':
    if (!isset($_GET['id'])) {
        $ret = FALSE;
        break;
    }
    $ret = TaxesService::get($_GET['id']);
    break;
case 'getAll':
    $ret = TaxesService::getAll();
    break;
case 'updateCat':
    $json = json_decode($_POST['taxcat'], true);
    $cat = Category::__form($json);
    if ($cat == NULL) {
        $ret = FALSE;
        break;
    }
    $ret = TaxesService::updateCat($cat);
    break;
case 'createCat':
    $json = json_decode($_POST['taxcat'], true);
    $cat = Category::__form($json);
    if ($cat == NULL) {
        $ret = FALSE;
        break;
    }
    $ret = TaxesService::createCat($cat);
    break;
case 'deleteCat':
    if (!isset($_GET['id'])) {
        $ret = FALSE;
        break;
    }
    $id = $_GET['id'];
    $ret = TaxesService::deleteCat($id);
    break;
case 'updateTax':
    $json = json_decode($_POST['tax']);
    $tax = Tax::__form($json);
    if ($tax == NULL) {
        $ret = FALSE;
        break;
    }
    $ret = TaxesService::updateTax($tax);
    break;
case 'createTax':
    $json = json_decode($_POST['tax']);
    $tax = Tax::__form($json);
    if ($tax == NULL) {
        $ret = FALSE;
        break;
    }
    $ret = TaxesService::createTax($tax);
    break;
case 'deleteTax':
    if (!isset($_GET['id'])) {
        $ret = FALSE;
        break;
    }
    $ret = TaxesService::deleteTax($_GET['id']);
    break;
}

echo(json_encode($ret));

?>
