<?php
//    Pastèque Web back office, Products module
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

function init() {
    // Register models
    $productDef = \Pasteque\ModelFactory::register("product");
    $productDef->addAttribute("ref", \Pasteque\ATTRDEF_STRING,
            array("required" => TRUE));
    $productDef->addAttribute("pricesell", \Pasteque\ATTRDEF_DOUBLE,
            array("required" => TRUE, "readonly" => TRUE));
    $productDef->addAttribute("taxcat_id", \Pasteque\ATTRDEF_SINGLEREL,
            array("model" => "taxcategory", "onset" => "product_updateprice"));
    $productDef->addAttribute("category_ids", \Pasteque\ATTRDEF_MULTREL,
            array("model" => "category"));

    $taxcatDef = \Pasteque\ModelFactory::register("taxcategory");
    $taxDef = \Pasteque\ModelFactory::register("tax");
    $taxDef->addAttribute("rate", \Pasteque\ATTRDEF_DOUBLE,
            array("required" => TRUE));
    $taxDef->addAttribute("validfrom", \Pasteque\ATTRDEF_DATE);
    $taxDef->addAttribute("taxcat_id", \Pasteque\ATTRDEF_SINGLEREL,
            array("model" => "taxcat"));
    
    $categoryDef = \Pasteque\ModelFactory::register("category");
    $categoryDef->addAttribute("parent_id", \Pasteque\ATTRDEF_SINGLEREL,
            array("model" => "category"));

    // Register menu
    global $MENU;
    $MENU->addSection("catalog", "Catalog", PLUGIN_NAME);
    $MENU->registerModuleEntry("catalog", PLUGIN_NAME, "Taxes", "taxes");
    $MENU->registerModuleEntry("catalog", PLUGIN_NAME, "Categories", "categories");
    $MENU->registerModuleEntry("catalog", PLUGIN_NAME, "Products", "products");
    \Pasteque\register_i18n(PLUGIN_NAME);
}
\Pasteque\hook("module_load", __NAMESPACE__ . "\init");

?>
