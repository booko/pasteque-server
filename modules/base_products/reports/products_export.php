<?php
//    Pastèque Web back office, Users module
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

namespace BaseProducts;

$sql = "SELECT REFERENCE as reference, PRODUCTS.NAME AS label, CODE AS barcode, PRICEBUY AS price_buy, ISSCALE AS scaled, DISCOUNTENABLED AS discount_enabled, DISCOUNTRATE AS discount_rate, ROUND(PRODUCTS.PRICESELL*(1+TAXES.RATE),2) AS sellVat, "
        . " CATEGORIES.NAME AS category, TAXCATEGORIES.NAME AS tax_cat "
        . " FROM PRODUCTS "
        . " LEFT JOIN CATEGORIES ON CATEGORIES.ID = PRODUCTS.CATEGORY "
        . " LEFT JOIN PRODUCTS_CAT ON PRODUCTS_CAT.PRODUCT = PRODUCTS.ID "
        . " LEFT JOIN TAXCATEGORIES ON PRODUCTS.TAXCAT = TAXCATEGORIES.ID "
        . " LEFT JOIN TAXES ON TAXCATEGORIES.ID = TAXES.CATEGORY "
        . " WHERE PRODUCTS.DELETED = 0 "
        . " ORDER BY PRODUCTS.NAME";

$fields = array("label","reference","sellVat","tax_cat","category","barcode","price_buy","scaled","disp_order","discount_enabled","discount_rate");
$headers = $fields;

$report = new \Pasteque\Report(PLUGIN_NAME, "products_export",
        \i18n("Export products", PLUGIN_NAME),
        $sql, $headers, $fields);

\Pasteque\register_report($report);
