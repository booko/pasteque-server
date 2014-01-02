<?php
namespace BaseProducts;

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

    $key = array('reference', 'barcode', 'label', 'sellVat',
            'category', 'tax_cat');

    $optionKey = array('price_buy', 'visible', 'scaled', 'disp_order',
            'discount_rate', 'discount_enabled', 'stock_min', 'stock_max');

    $csv = new \Pasteque\Csv($_FILES['csv']['tmp_name'], $key, $optionKey);
    if (!$csv->open()) {
        return $csv;
    }

    //manage empty string
    $csv->addFilter("visible", true);
    $csv->addFilter("scaled", false);
    $csv->addFilter("disp_order", null);
    return $csv;
}

// return an array whith all key set
function initArray($key, $tab) {
    $array = array_fill_keys($key, NULL);
    $array['visible'] = true;
    $array['scaled'] = false;
    $tab['disp_order'] = 0;

    foreach ($tab as $field => $value) {
        $array[$field] = $value;
    }
    return $array;
}
function import_csv($csv) {

    $error = 0;
    $create = 0;
    $update = 0;
    $error_mess = array();

    while ($tab = $csv->readLine()) {
        //init optionnal values
        $AllKeyPossible = array_merge($csv->getKey(), $csv->getOptionalKey());
        $tab = initArray($AllKeyPossible, $tab);

        //check
        $category = \Pasteque\CategoriesService::getByName($tab['category']);
        $tax_cat = \Pasteque\TaxesService::getByName($tab['tax_cat']);

        if ($tax_cat && $category) {
            $prod = readProductLine($tab, $category, $tax_cat);
            $product_exist = \Pasteque\ProductsService::getByRef($prod->reference);
            if ($product_exist !== null ) {
                // update product
                $prod->id = $product_exist->id;
                $prod = mergeProduct($product_exist, $prod);
                //if update imposible an is occurred
                if (!\Pasteque\ProductsService::update($prod)) {
                   $error++;
                   $error_mess[] = \i18n("On line %d: "
                           . "Cannot update product: '%s'", PLUGIN_NAME,
                            $csv->getCurrentLineNumber(), $tab['label']);
                } else {
                    // update stock_curr and stock_diary
                    manage_stock_level($prod->id, $tab, FALSE);
                    $update++;
                }

            } else {
                // create product
                $id = \Pasteque\ProductsService::create($prod);
                if ($id) {
                    //create stock_curr and stock diary
                    manage_stock_level($id, $tab, TRUE);
                    $create++;
                } else {
                    $error++;
                    $error_mess[] = \i18n("On line %d: "
                            . "Cannot create product: '%s'", PLUGIN_NAME,
                            $csv->getCurrentLineNumber(), $tab['label']);
                }
            }
        } else {
            // Missing category or tax category
            $error++;
            if (!$category) {
                $error_mess[] = \i18n("On line %d "
                        . "category: '%s' doesn't exist", PLUGIN_NAME,
                        $csv->getCurrentLineNumber(), $tab['category']);
            }
            if (!$tax_cat) {
                $error_mess[] = \i18n("On line %d: "
                        . "Tax category: '%s' doesn't exist", PLUGIN_NAME,
                        $csv->getCurrentLineNumber(), $tab['tax_cat']);
            }
        }
    }

    $message = \i18n("%d line(s) inserted, %d line(s) modified, %d error(s)",
            PLUGIN_NAME, $create, $update, $error);
    return array($message, $error_mess);
}

// add to product values not obligatory may be present in array
function readProductLine($line, $category, $taxCat) {
    $priceSell =  $line['sellVat'] / ( 1 + $taxCat->getCurrentTax()->rate);
    if (isset($line['visible'])) {
        $visible = $line['visible'];
    } else {
        $visible = true;
    }
    if (isset($line['scaled'])) {
        $scaled = $line['scaled'];
    } else {
        $scaled = false;
    }
    if (isset($line['disp_order'])) {
        $dispOrder = $line['disp_order'];
    } else {
        $dispOrder = null;
    }
    $product = new \Pasteque\Product($line['reference'], $line['label'],
            $priceSell, $category->id, $dispOrder,
            $taxCat, $visible, $scaled);
    if (isset($line['barcode'])) {
        $product->barcode = $line['barcode'];
    }
    if (isset($line['price_buy'])) {
        $product->priceBuy = $line['price_buy'];
    }
    if (isset($line['discount_enabled'])) {
        $product->discountEnabled = $line['discount_enabled'];
    }
    if (isset($line['discount_rate'])) {
        $product->discountRate = $line['discount_rate'];
    }
    // TODO: add support for attribute sets
    return $product;
}

/** Manage stockDiary and stockCurr whith id and location by default:"Principal"
 * check if fields 'stock_min' and 'stock_max' are set in array
 * if $create is true create a new entry in stockDiary and stockCurr in BDD
 * else update stockDiarry and  stockCurr.
 */
function manage_stock_level($id, $array) {
    $level = \Pasteque\StocksService::getLevel($id);
    $min = null;
    $max = null;
    if (isset($array['stock_min'])) {
        $min = $array['stock_min'];
    }
    if (isset($array['stock_max'])) {
        $max = $array['stock_max'];
    }
    if ($level !== null) {
        // Update existing level
        if ($min !== null) {
            $level->security = $min;
        }
        if ($max !== null) {
            $level->max = $max;
        }
        return \Pasteque\StocksService::updateLevel($level);
    } else {
        // Create a new level
        $level = new \Pasteque\StockLevel($id, "000", $min, $max);
        return \Pasteque\StocksService::createLevel($level);
    }
}

/* merge the old field values of product to new product
 * if the fields corresponding are not set */
function mergeProduct($old, $new) {
    if (!isset($new->barcode)) {
        $new->barcode = $old->barcode;
    }
    if (!isset($new->price_buy)) {
        $new->priceBuy = $old->priceBuy;
    }
    if (!isset($new->hasImage)) {
        $new->hasImage = $old->hasImage;
    }
    if (!isset($new->discountEnabled)) {
        $new->discountEnabled = $old->discountEnabled;
    }
    if (!isset($new->discountRate)) {
        $new->discountRate = $old->discountRate;
    }
    if (!isset($new->attributeSetId)) {
        $new->attributeSetId = $old->attributeSetId;
    }
    return $new;
}
?>

<?php
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


<h1><?php \pi18n("Import products from csv file", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" method="post" action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'productsManagement');?>" enctype="multipart/form-data">
    <div class="row">
        <label for='csv' >
            <?php \pi18n("File", PLUGIN_NAME) ?>:
        </label>
            <input type="file" name="csv">
    </div>
    <div class="row actions">
        <button class="btn-send" type="submit" id="envoyer" name="envoyer" >
            <?php \pi18n("send", PLUGIN_NAME)?>
        </button>
    </div>
</form>