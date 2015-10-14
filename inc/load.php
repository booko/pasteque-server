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

/** Check a core module readability. Returns the module file to import if
 * success. Die on error.
 */
function _check_core_module($type) {
    global $config;
    if (!isset($config["core_$type"])) {
        echo("No $type module set");
        die();
    }
    $name = $config["core_$type"];
    $file = PT::$ABSPATH . "/core_modules/$type/" . $name . "/module.php";
    if (!file_exists($file)) {
        echo("$name module not found");
        die();
    }
    return $file;
}

// Check dependencies
$dependencies = array("intl", "gd");
foreach ($dependencies as $dependency) {
    if (!\extension_loaded($dependency)) {
        echo("Missing PHP module: " . $dependency);
        die();
    }
}

// Load configuration file
if (isset($altConfigFile)) {
    define("CFG_FILE", PT::$ABSPATH . "/" . $altConfigFile);
} else {
    define ("CFG_FILE", PT::$ABSPATH . "/config.php");
}
if (!file_exists(CFG_FILE) || !is_readable(CFG_FILE)) {
    echo("No config file");
    die();
}
require_once(CFG_FILE);

// Load static tools
require_once(PT::$ABSPATH . "/inc/date_utils.php");
require_once(PT::$ABSPATH . "/inc/url_broker.php");
require_once(PT::$ABSPATH . "/inc/i18n.php");
require_once(PT::$ABSPATH . "/inc/i18n_aliases.php");
require_once(PT::$ABSPATH . "/inc/Report.php");
require_once(PT::$ABSPATH . "/inc/hooks.php");
require_once(PT::$ABSPATH . "/inc/forms.php");
require_once(PT::$ABSPATH . "/inc/PDOBuilder.php");
require_once(PT::$ABSPATH . "/inc/DB.php");
require_once(PT::$ABSPATH . "/inc/Installer.php");
require_once(PT::$ABSPATH . "/inc/images.php");
require_once(PT::$ABSPATH . "/inc/Module.php");

// Load core modules
require_once(_check_core_module('ident'));
require_once(_check_core_module('database'));
require_once(_check_core_module('modules'));

require_once(PT::$ABSPATH . "/inc/Menu.php"); // Menu requires login module

// Load data
require_once(PT::$ABSPATH . "/inc/data/models/Attribute.php");
require_once(PT::$ABSPATH . "/inc/data/models/CashRegister.php");
require_once(PT::$ABSPATH . "/inc/data/models/Cash.php");
require_once(PT::$ABSPATH . "/inc/data/models/ZTicket.php");
require_once(PT::$ABSPATH . "/inc/data/models/Category.php");
require_once(PT::$ABSPATH . "/inc/data/models/Provider.php");
require_once(PT::$ABSPATH . "/inc/data/models/Floor.php");
require_once(PT::$ABSPATH . "/inc/data/models/PaymentMode.php");
require_once(PT::$ABSPATH . "/inc/data/models/Payment.php");
require_once(PT::$ABSPATH . "/inc/data/models/Place.php");
require_once(PT::$ABSPATH . "/inc/data/models/Product.php");
require_once(PT::$ABSPATH . "/inc/data/models/Composition.php");
require_once(PT::$ABSPATH . "/inc/data/models/TariffArea.php");
require_once(PT::$ABSPATH . "/inc/data/models/Tax.php");
require_once(PT::$ABSPATH . "/inc/data/models/TaxAmount.php");
require_once(PT::$ABSPATH . "/inc/data/models/TaxCat.php");
require_once(PT::$ABSPATH . "/inc/data/models/SharedTicket.php");
require_once(PT::$ABSPATH . "/inc/data/models/Ticket.php");
require_once(PT::$ABSPATH . "/inc/data/models/TicketLine.php");
require_once(PT::$ABSPATH . "/inc/data/models/CashMovement.php");
require_once(PT::$ABSPATH . "/inc/data/models/DiscountProfile.php");
require_once(PT::$ABSPATH . "/inc/data/models/Discount.php");
require_once(PT::$ABSPATH . "/inc/data/models/Customer.php");
require_once(PT::$ABSPATH . "/inc/data/models/CustTaxCat.php");
require_once(PT::$ABSPATH . "/inc/data/models/User.php");
require_once(PT::$ABSPATH . "/inc/data/models/Role.php");
require_once(PT::$ABSPATH . "/inc/data/models/Location.php");
require_once(PT::$ABSPATH . "/inc/data/models/Inventory.php");
require_once(PT::$ABSPATH . "/inc/data/models/Stock.php");
require_once(PT::$ABSPATH . "/inc/data/models/Resource.php");
require_once(PT::$ABSPATH . "/inc/data/models/Currency.php");
require_once(PT::$ABSPATH . "/inc/data/services/AbstractService.php");
require_once(PT::$ABSPATH . "/inc/data/services/AttributesService.php");
require_once(PT::$ABSPATH . "/inc/data/services/CashesService.php");
require_once(PT::$ABSPATH . "/inc/data/services/CategoriesService.php");
require_once(PT::$ABSPATH . "/inc/data/services/ProvidersService.php");
require_once(PT::$ABSPATH . "/inc/data/services/PlacesService.php");
require_once(PT::$ABSPATH . "/inc/data/services/PaymentModesService.php");
require_once(PT::$ABSPATH . "/inc/data/services/ProductsService.php");
require_once(PT::$ABSPATH . "/inc/data/services/CompositionsService.php");
require_once(PT::$ABSPATH . "/inc/data/services/TariffAreasService.php");
require_once(PT::$ABSPATH . "/inc/data/services/TaxesService.php");
require_once(PT::$ABSPATH . "/inc/data/services/TicketsService.php");
require_once(PT::$ABSPATH . "/inc/data/services/CashMovementsService.php");
require_once(PT::$ABSPATH . "/inc/data/services/DiscountProfilesService.php");
require_once(PT::$ABSPATH . "/inc/data/services/DiscountsService.php");
require_once(PT::$ABSPATH . "/inc/data/services/CustomersService.php");
require_once(PT::$ABSPATH . "/inc/data/services/CustTaxCatsService.php");
require_once(PT::$ABSPATH . "/inc/data/services/UsersService.php");
require_once(PT::$ABSPATH . "/inc/data/services/RolesService.php");
require_once(PT::$ABSPATH . "/inc/data/services/LocationsService.php");
require_once(PT::$ABSPATH . "/inc/data/services/StocksService.php");
require_once(PT::$ABSPATH . "/inc/data/services/ResourcesService.php");
require_once(PT::$ABSPATH . "/inc/data/services/CurrenciesService.php");
require_once(PT::$ABSPATH . "/inc/data/services/CashRegistersService.php");
require_once(PT::$ABSPATH . "/inc/data/services/InventoriesService.php");
require_once(PT::$ABSPATH . "/inc/data/validation/load.php");
require_once(PT::$ABSPATH . "/inc/data/validation/validators/price.php");
require_once(PT::$ABSPATH . "/inc/data/parsing/load.php");
require_once(PT::$ABSPATH . "/inc/Csv.php");
require_once(PT::$ABSPATH . "/inc/catalog.php");

load_base_i18n(detect_preferred_language());

call_hooks("core_ready");
