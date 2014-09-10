<?php
namespace ModulePayment;

function init() {
    global $MENU;

    $MENU->registerModuleEntry("admin", PLUGIN_NAME, "menu_modules.png", "Modules", "modules");
    \Pasteque\register_i18n(PLUGIN_NAME);

}
\Pasteque\hook("module_load", __NAMESPACE__ . "\init");

