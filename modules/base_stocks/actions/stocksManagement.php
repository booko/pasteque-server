<?php

namespace BaseStocks;

/** open csv return null if the file selected had not extension "csv"
 * or user not selected file */
function init_csv() {

    if ($_FILES['csv']['tmp_name'] === NULL) {
        return NULL;
    }
    $ext = strchr($_FILES['csv']['type'], "/");
    $ext = strtolower($ext);

    if ($ext !== "/csv") {
        return NULL;
    }

    $key = array('Quantity', 'Reference', 'Location');

    $csv = new \Pasteque\Csv($_FILES['csv']['tmp_name'], $key);
    if (! $csv->open()) {
        return $csv;
    }

    //manage empty string
    $csv->addFilter("Quantity", "0");

    return $csv;
}

function import_csv($csv, $date) {
    $error_mess = array();
    $move = 0;
    $error = 0;

    while ($tab = $csv->readLine()) {

        $reason = $_POST['reason'];
        $productOk = false;
        $quantityOk = false;
        $locationOk = false;
        $errMove = false;

        // check value of tab
        $product = \Pasteque\ProductsService::getByRef($tab['Reference']);
        if ($product !== NULL) {
            $productOk = true;
            $tab['Reference'] = $product->id;
        }
        $location = \Pasteque\StocksService::getLocationId($tab['Location']);
        $tab['Location'] = $location;
        if ($location !== NULL) {
            $locationOk = true;
        }
        if ($tab['Quantity'] === "0" || intval($tab['Quantity']) !== 0) {
            $quantityOk = true;
        }

        if ($productOk && $locationOk && $quantityOk) {
            $stock = new \Pasteque\StockMove($date, $reason, $tab['Location'],
                    $tab['Reference'], $tab['Quantity']);
            if (\Pasteque\StocksService::addMove($stock)) {
                $move++;
            } else {
                $errMove = true;
            }
        }

        //manage errors
        if (!$productOk) {
            $error_mess[] = \i18n("On line %d: Product not define or invalid",
                    PLUGIN_NAME, $csv->getCurrentLineNumber());
        }
        if (!$locationOk) {
            $error_mess[] = \i18n("On line %d: Location not define or invalid",
                    PLUGIN_NAME, $csv->getCurrentLineNumber());
        }
        if (!$quantityOk) {
            $error_mess[] = \i18n("On line %d: Quantity not define or invalid",
                    PLUGIN_NAME, $csv->getCurrentLineNumber());
        }
        if ($errMove) {
            $error_mess[] = \i18n("On line %d: exeptionnal error",
                    PLUGIN_NAME, $csv->getCurrentLineNumber());
        }
        if (!($productOk && $locationOk &&  $quantityOk && $errMove)) {
            $error++;
        }
    }
    $csv->close();
    $message = \i18n("%d move(s), %d error(s)", PLUGIN_NAME, $move, $error);

\Pasteque\tpl_msg_box($message, $error_mess);

}
?>

<?php
if (isset($_FILES['csv'])) {
    $dateStr = isset($_POST['date']) ? $_POST['date'] : \i18nDate(time());
    $dateStr = \i18nRevDate($dateStr);
    $date = \Pasteque\stdstrftime($dateStr);

    $csv = init_csv();
    if ($csv === NULL) {
        \Pasteque\tpl_msg_box(NULL, \i18n("Selected file empty or bad format", PLUGIN_NAME));
    } else if (!$csv->isOpen()) {
        $err = array();
        foreach ($csv->getErrors() as $mess) {
            $err[] = \i18n($mess);
        }
        \Pasteque\tpl_msg_box(NULL, $err);
    } else {
        import_csv($csv, $date);
    }
}
?>

<h1><?php \pi18n("Import stock moves from csv", PLUGIN_NAME); ?></h1>
<?php     $dateStr = isset($_POST['date']) ? $_POST['date'] : \i18nDate(time()); ?>
<form method="post" class="edit" action="<?php echo \Pasteque\get_current_url();?>" enctype="multipart/form-data">
        <div class="row">
                <?php \pi18n("Fichier", PLUGIN_NAME);?> : <input type="file" name="csv">
        </div>
        <div class="row">
                <label for="reason"><?php \pi18n("Operation", PLUGIN_NAME);?></label>
                <select id="reason" name="reason">
                    <option value="<?php echo \Pasteque\StockMove::REASON_IN_BUY; ?>"><?php \pi18n("Input (buy)", PLUGIN_NAME); ?></option>
                    <option value="<?php echo \Pasteque\StockMove::REASON_OUT_SELL; ?>"><?php \pi18n("Output (sell)", PLUGIN_NAME); ?></option>
                    <option value="<?php echo \Pasteque\StockMove::REASON_OUT_BACK; ?>"><?php \pi18n("Output (return to supplyer)", PLUGIN_NAME); ?></option>
                </select>
        </div>
        <div class="row">
                <label for="date"><?php \pi18n("Date", PLUGIN_NAME); ?></label>
                <input type="date" name="date" id="date" value="<?php echo $dateStr; ?>" />
        </div>
        <div class="row actions">
            <button type="submit" class="btn-send" name="envoyer">
                <?php echo \i18n("Send", PLUGIN_NAME) ?>
            </button>
        </div>
</form>
