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

$sql = "SELECT PRODUCTS.REFERENCE as reference, "
            . "PRODUCTS.NAME AS label, "
            . "PRODUCTS.CODE AS barcode, "
            . "PRODUCTS.PRICEBUY AS price_buy, "
            . "PRODUCTS.ISSCALE+0 AS scaled, "
            . "PRODUCTS.DISCOUNTENABLED+0 AS discount_enabled, "
            . "PRODUCTS.DISCOUNTRATE AS discount_rate, "
            . "ROUND(PRODUCTS.PRICESELL*(1+TAXES.RATE),2) AS sellVat, "
            . "CATEGORIES.NAME AS category, "
            . "PROVIDERS.NAME AS provider, "
            . "TAXCATEGORIES.NAME AS tax_cat, "
            . "STOCKLEVEL.STOCKSECURITY as stock_min, "
            . "STOCKLEVEL.STOCKMAXIMUM as stock_max, "
            . "STOCKCURRENT.UNITS as stock_current, "
            . "PRODUCTS_CAT.CATORDER as disp_order"
        . " FROM PRODUCTS "
        . " LEFT JOIN CATEGORIES ON CATEGORIES.ID = PRODUCTS.CATEGORY "
        . " LEFT JOIN PROVIDERS ON PROVIDERS.ID = PRODUCTS.PROVIDER "
        . " LEFT JOIN PRODUCTS_CAT ON PRODUCTS_CAT.PRODUCT = PRODUCTS.ID "
        . " LEFT JOIN TAXCATEGORIES ON PRODUCTS.TAXCAT = TAXCATEGORIES.ID "
        . " LEFT JOIN TAXES ON TAXCATEGORIES.ID = TAXES.CATEGORY "
        . " LEFT JOIN STOCKLEVEL ON PRODUCTS.ID = STOCKLEVEL.PRODUCT "
        . " LEFT JOIN STOCKCURRENT ON PRODUCTS.ID = STOCKCURRENT.PRODUCT "
        . " ORDER BY TAXES.VALIDFROM DESC, PRODUCTS.NAME";

$fields = array("label","reference","sellVat","tax_cat",
   "category","provider","barcode","price_buy","scaled",
   "disp_order","discount_enabled","discount_rate",
   "stock_min","stock_max", "stock_current");
$headers = $fields;

$report = new \Pasteque\Report(PLUGIN_NAME, "products_export",
        \i18n("Export products", PLUGIN_NAME),
        $sql, $headers, $fields);

\Pasteque\register_report($report);
