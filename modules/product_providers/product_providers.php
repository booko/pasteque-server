<?php
//    Pastèque Web back office, Products module
//
//    Copyright (C) 2015 Scil (http://scil.coop)
//    Philippe Pary
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

namespace ProductProviders;

function init() {
    global $MENU;
    $MENU->addSection("catalog", "Catalog", PLUGIN_NAME);
    $MENU->registerModuleEntry("catalog", PLUGIN_NAME, "menu_category.png", "Providers", "providers");
    $MENU->addSection("sales", "Sales", PLUGIN_NAME);
    $MENU->registerModuleReport("sales", PLUGIN_NAME, "menu_product_sales.png", "Sales by provider", "sales_by_provider_report");
    \Pasteque\register_i18n(PLUGIN_NAME);
}
\Pasteque\hook("module_load", __NAMESPACE__ . "\init");

?>

