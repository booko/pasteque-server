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

function jsonify($key, $value) {
    // TODO: escape data
    if (\is_string($value)) {
        return '"' . $key . '": "' . $value . '"';
    } else if ($value === null) {
        return '"' . $key . '": null';
    } else {
        return '"' . $key . '": ' . $value;
    }
}

function init_catalog($jsName, $containerId, $selectCallback,
        $categories, $products) {
    echo '<script type="text/javascript" src="inc/catalog.js"></script>';
    echo '<script type="text/javascript">';
    echo "var $jsName = new Catalog(\"$containerId\", \"$selectCallback\");\n";
    echo "jQuery(document).ready(function() {\n";
    echo "var html = \"<div class=\\\"catalog-categories-container\\\"></div>\";\n";
    echo "html += \"<div class=\\\"catalog-products-container\\\"></div>\";\n";
    echo "jQuery(\"#$containerId\").html(html);\n";
    foreach ($categories as $cat) {
        echo $jsName . ".createCategory(\"" . $jsName . "\", \"" . $cat->id . "\""
                . ", \"" . $cat->label . "\");\n";
    }
    foreach ($products as $product) {
        $taxCat = TaxesService::get($product->taxCatId);
        $tax = $taxCat->getCurrentTax();
        $vatPrice = $product->priceSell * (1 + $tax->rate);
        $prd = '{' . jsonify("id", $product->id) . ', '
                . jsonify("label", $product->label) . ', '
                . jsonify("reference", $product->reference) . ', '
                . jsonify("img", "?" . URL_ACTION_PARAM . "=img&w=product&id=" . $product->id) . ', '
                . jsonify("buy", $product->priceBuy) . ', '
                . jsonify("sell", $product->priceSell) . ', '
                . jsonify("vatSell", $vatPrice)
                . '}';
        echo $jsName . ".addProductToCat(\"" . $product->id . "\", \"" . $product->categoryId . "\");\n";
        echo $jsName . ".addProduct(" . $prd .");\n";
    }
    if (count($categories) > 0) {
        echo $jsName . ".changeCategory(\"" . $categories[0]->id . "\");\n";
    }
    echo "});\n</script>";
}
