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

namespace BaseSales;

function init() {
    global $MENU;
    $MENU->addSection("sales", "Sales", PLUGIN_NAME);

    $MENU->registerModuleReport("sales", PLUGIN_NAME, "menu_sales.png", "CS report", "cs_report");
    $MENU->registerModuleReport("sales", PLUGIN_NAME, "menu_product_sales.png", "Sales by product", "sales_by_product_report");
    $MENU->registerModuleReport("sales", PLUGIN_NAME, "menu_product_sales.png", "Sales by provider", "sales_by_provider_report");
    $MENU->registerModuleReport("sales", PLUGIN_NAME, "menu_sales_details.png", "Sales report", "sales_report");
    $MENU->registerModuleReport("sales", PLUGIN_NAME, "menu_tax.png", "Taxes report", "taxes_report");
    \Pasteque\register_i18n(PLUGIN_NAME);
}
\Pasteque\hook("module_load", __NAMESPACE__ . "\init");
