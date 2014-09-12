<?php

namespace ModulePayment;

$message = null;
$error = null;
$modules = null;
$mandatoryModules = null;
$freeModules = null;
$activatedModules = \Pasteque\get_loaded_modules(\Pasteque\get_user_id());
$cfg = \getConfig();
if (!isset($cfg['pp_modules']) || !isset($cfg['mandatory_modules'])
        || !isset($cfg['free_modules'])) {
    $error = \i18n("Module list is not configured.", PLUGIN_NAME);
    $modules = array();
    $freeModules = array();
    $mandatoryModules = array("module_payment");
} else {
    $modules = $cfg['pp_modules'];
    $freeModules = $cfg['free_modules'];
    $mandatoryModules = $cfg['mandatory_modules'];
}

if (isset($_POST['modules']) && count($mandatoryModules) > 0) {
	$pdo = \Pasteque\PDOBuilder::getPDO();
	$paidModules = array();
	foreach ($modules as $module) {
        foreach ($activatedModules as $actMod) {
           if ($actMod == $module['module']) {
                $paidModules[] = $module['module'];
                break;
           }
        }
    }
	$allModules = array_merge($_POST['modules'], $paidModules);
	// TODO: this is not config agnostic for MODULES table
	$stmt = $pdo->prepare("update MODULES set modules = :mod where user_id = :id");
	$stmt->bindParam(":id", \Pasteque\get_user_id());
	$stmt->bindParam(":mod", implode(",", $allModules));
	if ($stmt->execute() !== false) {
	    $message = \i18n("Changes saved");
	    // Reload activated modules
	    $activatedModules = $allModules;
	} else {
        var_dump($stmt->errorInfo());
	    $error = \i18n("Unable to save changes");
	}
}

function displayFreeModule($module, $activatedModules) {
   $activated = false;
    foreach ($activatedModules as $actMod) {
        if ($actMod == $module) {
            $activated = true;
            break;
        }
    }
?>
<div class="row">
	<input type="checkbox" name="modules[]" id="module-<?php echo \Pasteque\esc_attr($module); ?>" value="<?php echo \Pasteque\esc_attr($module); ?>" <?php if ($activated) { echo ("checked=\"true\""); } ?> />
	<label for="module-<?php echo \Pasteque\esc_attr($module); ?>"><?php \pi18n($module, PLUGIN_NAME); ?></label>
</div>
<?php
}

function displayModule($module, $activatedModules, $pp_id, $sandbox) {
    $activated = false;
    foreach ($activatedModules as $actMod) {
        if ($actMod == $module['module']) {
            $activated = true;
            break;
        }
    }
    if (!$activated) {
        echo "<td>" . \i18n($module['module'], PLUGIN_NAME)
                . " (" . \i18nCurr($module['price']) . ")</td>\n";
        $host = $sandbox ? "www.sandbox.paypal.com" : "www.paypal.com";
        echo "<td>";
        echo "<form target=\"paypal\" action=\"https://" . $host . "/cgi-bin/webscr\" method=\"post\" >\n";
        echo "<input type=\"hidden\" name=\"cmd\" value=\"_cart\" />\n";
        echo "<input type=\"hidden\" name=\"business\" value=\"" . \Pasteque\esc_attr($pp_id) . "\" />\n";
        echo "<input type=\"hidden\" name=\"custom\" value=\"" . \Pasteque\esc_attr(\Pasteque\get_user_id()) . "\" />\n";
        echo "<input type=\"hidden\" name=\"lc\" value=\"FR\" />\n";
        echo "<input type=\"hidden\" name=\"item_name\" value=\"" . \Pasteque\esc_attr(\i18n($module['module'], PLUGIN_NAME)) . "\" />\n";
        echo "<input type=\"hidden\" name=\"item_number\" value=\"" . \Pasteque\esc_attr($module['module']) . "\" />\n";
        echo "<input type=\"hidden\" name=\"amount\" value=\"" . $module['price'] . "\" />\n";
        echo "<input type=\"hidden\" name=\"currency_code\" value=\"EUR\" />\n";
        echo "<input type=\"hidden\" name=\"button_subtype\" value=\"products\" />\n";
        echo "<input type=\"hidden\" name=\"no_note\" value=\"0\" />\n";
        echo "<input type=\"hidden\" name=\"cn\" value=\"Ajouter des instructions particulières pour le vendeur :\" />\n";
        echo "<input type=\"hidden\" name=\"no_shipping\" value=\"2\" />\n";
        echo "<input type=\"hidden\" name=\"tax_rate\" value=\"20.000\" />\n";
        echo "<input type=\"hidden\" name=\"add\" value=\"1\" />\n";
        echo "<input type=\"hidden\" name=\"bn\" value=\"PP-ShopCartBF:btn_cart_LG.gif:NonHosted\" />\n";
        echo "<input type=\"image\" src=\"https://" . $host . "/fr_FR/FR/i/btn/btn_cart_LG.gif\" border=\"0\" name=\"submit\" alt=\"PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !\" />\n";
        echo "<img alt=\"\" border=\"0\" src=\"https://" . $host . "/fr_FR/i/scr/pixel.gif\" width=\"1\" height=\"1\" />\n";
        echo "</form></td>\n";
    } else {
        echo "<td>" . \i18n($module['module'], PLUGIN_NAME) . "</td>\n";
        echo "<td>" . \i18n("activated", PLUGIN_NAME) . "</td>\n";
    }
}

?>

<h1><?php \pi18n("Modules", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<h2><?php \pi18n("Free modules", PLUGIN_NAME); ?></h2>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
<?php foreach ($mandatoryModules as $module) { ?>
	<input type="hidden" name="modules[]" value="<?php echo \Pasteque\esc_attr($module); ?>" />
<?php }  foreach($freeModules as $module) {
    displayFreeModule($module, $activatedModules);
} ?>
	<div class="row actions">
		<?php \Pasteque\form_save(); ?>
	</div>
    <div class="row"><?php \pi18n("Once modules are selected, they will be activated in a short moment.", PLUGIN_NAME); ?></div>
</form>

<h2><?php \pi18n("Buy modules", PLUGIN_NAME); ?></h2>

<div class="edit">
	<table cellpadding="0" cellspacing="0">
		<tbody id="list">
<?php
$pp_id = null;
if ($cfg['pp_sandbox']) {
    $pp_id = $cfg['pp_sandbox_id'];
} else {
    $pp_id = $cfg['pp_user_id'];
}

foreach ($modules as $module) {
    echo "\t\t\t<tr>\n";
    displayModule($module, $activatedModules, $pp_id, $cfg['pp_sandbox']);
    echo "\t\t\t</tr>\n";
?>
<?php } ?>
		</tbody>
	</table>

<form target="paypal" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" >
<input type="hidden" name="cmd" value="_cart" />
<input type="hidden" name="business" value="<?php echo \Pasteque\esc_attr($pp_id); ?>" />
<input type="hidden" name="display" value="1" />
<input type="image" src="https://www.sandbox.paypal.com/fr_FR/FR/i/btn/btn_viewcart_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !" />
<img alt="" border="0" src="https://www.sandbox.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1" />
</form>

<p><?php \pi18n("Once payment is validated, the modules will be activated in a short moment.", PLUGIN_NAME); ?></p>
