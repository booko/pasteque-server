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

    $key = array('reference', 'barcode', 'label', 'price_buy', 'price_sell',
            'category', 'tax_cat');
    $optionKey = array('visible', 'scaled', 'disp_order', 'discount_rate',
            'discount_enabled');
    $optionKey = array();

    $csv = new \Pasteque\Csv($_FILES['csv']['tmp_name'], $key, $optionKey);
    if (!$csv->open()) {
        return $csv;
    }

    //manage empty string
    $csv->addFilter("visible", true);
    $csv->addFilter("scaled", false);
    $csv->addFilter("disp_order", "0");
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
        $tax_cat = \Pasteque\Taxesservice::getByName($tab['tax_cat']);

        if ($tax_cat && $category) {
            $prod = new \Pasteque\Product($tab['reference'], $tab['label'],
                    $tab['price_sell'], $category, $tab['disp_order'],
                    $tax_cat, $tab['visible'], $tab['scaled']);

            // manage optional value may be present in $tab
            $prod = manage_header_option($prod, $tab);
            $product_exist= \Pasteque\ProductsService::getByRef($prod->reference);

            //UPDATE product
            if ($product_exist !== NULL ) {
                $prod->id = $product_exist->id;
                $prod = mergeProduct($product_exist, $prod);

                //if update imposible an is occurred
                if (!\Pasteque\ProductsService::update($prod)) {
                   $error++;
                   $error_mess[] = \i18n("On line %d: Cannot update product: '%s'", PLUGIN_NAME,
                            $csv->getCurrentLineNumber(), $tab['label']);
                } else {
                    // update stock_curr and stock_diary
                    manage_stock_level($prod->id, $tab, FALSE);
                    $update++;
                }

            //CREATE product
            } else {
                    $id = \Pasteque\ProductsService::create($prod);
                    if ($id) {
                        //create stock_curr and stock diary
                        manage_stock_level($id, $tab, TRUE);
                        $create++;
                    } else {
                        $error++;
                        $error_mess[] = \i18n("On line %d: Cannot create product: '%s'", PLUGIN_NAME,
                                 $csv->getCurrentLineNumber(), $tab['label']);
                    }
            }

        // category or tax_category doesn't exist
        } else {
            $error++;
            if (!$category) {
                $error_mess[] = \i18n("On line %d category: '%s' doesn't exist", PLUGIN_NAME,
                        $csv->getCurrentLineNumber(), $tab['category']);
            }
            if (!$tax_cat) {
                $error_mess[] = \i18n("On line %d: Tax category: '%s' doesn't exist", PLUGIN_NAME,
                        $csv->getCurrentLineNumber(), $tab['tax_cat']);
            }
        }
    }

    $message = \i18n("%d line(s) inserted, %d line(s) modified, %d error(s)", PLUGIN_NAME,
            $create, $update, $error);
    \Pasteque\tpl_msg_box($message, $error_mess);
}


// add to product values not obligatory may be present in array
function manage_header_option($product, $array) {
    if (isset($array['barcode'])) {
        $product->barcode = $array['barcode'];
    }
    if (isset($array['price_buy'])) {
        $product->price_buy = $array['price_buy'];
    }
    if (isset($array['discount_enabled'])) {
        $product->discount_enabled = $array['discount_enabled'];
    }
    if (isset($array['discount_rate'])) {
        $product->discount_rate = $array['discount_rate'];
    }
    if (isset($array['attributes_set'])) {
        $product->attributes_set = $array['attributes_set'];
    }
    return $product;
}

/** Manage stockDiary and stockCurr whith id and location by default:"Principal"
 * check if fields 'stock_min' and 'stock_max' are set in array
 * if $create is true create a new entry in stockDiary and stockCurr in BDD
 * else update stockDiarry and  stockCurr.
 */
function manage_stock_level($id, $array, $create) {
    $stockLevel = new \Pasteque\StockLevel($id, "Principal", NULL, NULL);

    if (isset($array['stock_min'])) {
        $stockLevel->security = $array['stock_min'];
    }
    if (isset($array['stock_max'])) {
        $stockLevel->max = $array['stock_max'];
    }

    if ($create) {
        \Pasteque\StocksService::createLevel($stockLevel);

    } else {
        \Pasteque\StocksService::updateLevel($stockLevel);
    }

    return $stockLevel;
}

/* merge the old field values of product to new product
 * if the fields corresponding are not set */
function mergeProduct($old, $new) {
    if (!isset($new->barcode)) {
        $new->barcode = $old->barcode;
    }
    if (!isset($new->price_buy)) {
        $new->price_buy = $old->price_buy;
    }
    if (!isset($new->image)) {
        $new->image = $old->image;
    }
    if (!isset($new->discount_enabled)) {
        $new->discount_enabled = $old->discount_enabled;
    }
    if (!isset($new->discount_rate)) {
        $new->discount_rate = $old->discount_rate;
    }
    if (!isset($new->attributes_set)) {
        $new->attributes_set = $old->attributes_set;
    }
    return $new;
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


<h1><?php \pi18n("Import products from csv file", PLUGIN_NAME); ?></h1>
<form method="post" action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'productsManagement');?>" enctype="multipart/form-data">
        <?php \pi18n("File", PLUGIN_NAME) ?>: <input type="file" name="csv">
        <input type="submit" name="envoyer" value=<?php \pi18n("send", PLUGIN_NAME)?>>
</form>