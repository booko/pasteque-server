<?php
namespace BaseComposition;

function init() {
    global $MENU;
    $MENU->addSection("composition", "Composition", PLUGIN_NAME);

    $MENU->registerModuleEntry("composition", PLUGIN_NAME, "menu_resources.png", "composition", "composition");
    \Pasteque\register_i18n(PLUGIN_NAME);

}
\Pasteque\hook("module_load", __NAMESPACE__ . "\init");
?>