<?php
namespace ProductCompositions;

function init() {
    global $MENU;

    $MENU->registerModuleEntry("catalog", PLUGIN_NAME, "menu_compositions.png", "Compositions", "composition");
    \Pasteque\register_i18n(PLUGIN_NAME);

}
\Pasteque\hook("module_load", __NAMESPACE__ . "\init");
?>
