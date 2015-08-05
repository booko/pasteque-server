<?php
namespace ProductTariffAreas;

// open csv return null if the file selected had not extension "csv"
// or user not selected file
function init_csv() {

    if ($_FILES['csv']['tmp_name'] === NULL) {
        return NULL;
    }
    $ext = strchr($_FILES['csv']['type'], "/");
    $ext = strtolower($ext);

    if($ext !== "/csv") {
        return NULL;
    }

    $key = array('area', 'reference', 'sellVat');

    $csv = new \Pasteque\Csv($_FILES['csv']['tmp_name'], $key, array(),
            PLUGIN_NAME);
    if (!$csv->open()) {
        return $csv;
    }

    return $csv;
}

/** Main csv import function given a csv object */
function import_csv($csv) {

    $error = 0;
    $creations = 0;
    $updates = 0;
    $error_mess = array();
    $srv = new \Pasteque\TariffAreasService();
    $allAreas = $srv->getAll();
    $taxCats = \Pasteque\TaxesService::getAll();
    $areas = array();
    $areasRef = array();

    while ($tab = $csv->readLine()) {
        //check
        $area = null;
        $ref = null;
        if (isset($areas[$tab['area']])) {
            $area = $areas[$tab['area']];
            $ref = $areasRef[$tab['area']];
        } else {
            foreach ($allAreas as $a) {
                if ($a->label == $tab['area']) {
                    // Set reference for update and recreate a new to
                    // delete non imported prices
                    $areasRef[$tab['area']] = $a;
                    $area = \Pasteque\TariffArea::__build($a->id, $a->label,
                            $a->dispOrder);
                    $areas[$tab['area']] = $area;
                    break;
                }
            }
        }
        // Create area if not found
        if ($area === null) {
            $area = new \Pasteque\TariffArea($tab['area'], 100);
            $area->id = $srv->create($area);
            if ($area->id === false) {
                $error++;
                $error_mess[] = \i18n("Line %d: Unable to create area %s",
                        PLUGIN_NAME, $csv->getCurrentLineNumber(),
                        $tab['area']);
                continue;
            }
            $areas[$tab['area']] = $area;
            $areasRef[$tab['area']] = null;
        }

        $product = \Pasteque\ProductsService::getByRef($tab['reference']);
        if ($product === null ) {
            $error++;
            $error_mess[] = \i18n("Line %d: '%s' doesn't exist", PLUGIN_NAME,
                        $csv->getCurrentLineNumber(), $tab['reference']);
            continue;
        }
        $taxCat = null;
        foreach ($taxCats as $tc) {
            if ($tc->id == $product->taxCatId) {
                $taxCat = $tc;
                break;
            }
        }
        // Check if price should be updated
        $priceFound = false;
        $sellPrice = $tab['sellVat'] / ( 1 + $taxCat->getCurrentTax()->rate);
        if ($ref !== null) {
            foreach ($ref->getPrices() as $price) {
                if ($price->productId == $product->id) {
                    // Update
                    $area->addPrice($product->id, $sellPrice);
                    $priceFound = true;
                    $updates++;
                    break;
                }
            }
        }
        if (!$priceFound) {
            // Add a price
            $area->addPrice($product->id, $sellPrice);
            $creations++;
        }
    }
    // Update data
    foreach ($areas as $name => $area) {
        if ($srv->update($area) === false) {
            $error++;
            $error_message[] = \i18n("Unable to save tariff area %d",
                 PLUGIN_NAME, $name);
        }
    }

    $message = \i18n("%d line(s) inserted, %d line(s) modified, %d error(s)",
            PLUGIN_NAME, $creations, $updates, $error);
    return array($message, $error_mess);
}

$error = null;
$message = null;
if (isset($_FILES['csv'])) {
    $csv = init_csv();
    if ($csv === NULL) {
        $error = \i18n("Selected file empty or bad format", PLUGIN_NAME);
    } else if (!$csv->isOpen()) {
        $err = array();
        foreach ($csv->getErrors() as $mess) {
            $err[] = \i18n($mess);
        }
        if (count($err) > 0) {
            $error = $err;
        }
    } else {
        $msgs = import_csv($csv);
        $message = $msgs[0];
        $error = $msgs[1];
    }
}
?>

<h1><?php \pi18n("Import areas from csv file", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" method="post" action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'areas_import');?>" enctype="multipart/form-data">
    <div class="row">
        <label for='csv' >
            <?php \pi18n("File", PLUGIN_NAME) ?>:
        </label>
            <input type="file" name="csv">
    </div>
    <div class="row actions">
        <button class="btn-send" type="submit" id="envoyer" name="envoyer" >
            <?php \pi18n("Send")?>
        </button>
    </div>
</form>
