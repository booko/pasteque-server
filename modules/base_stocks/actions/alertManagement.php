<?php

//    Pastèque Web back office, Stocks module
//
//    Copyright (C) 2015 Scil (http://scil.coop)
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

// https://my.pasteque.coop/awl-julien/?p=modules/base_stocks/actions/alertManagement
namespace BaseStocks;


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

// open csv return null if the file selected had not extension "csv"
// or user not selected file
function init_csv() {
    if ($_FILES['csv']['tmp_name'] === NULL) {
        return NULL;
    }
    $ext = strchr($_FILES['csv']['type'], "/");
    $ext = strtolower($ext);

    if($ext !== "/csv" && $ext !== "/plain") {
        return NULL;
    }

    $key = array(
        \i18n("Location.label"),
        \i18n("Product.reference"),
        \i18n("Quantity"),
        \i18n("QuantityMin"),
        \i18n("QuantityMax")
    );
    
    $optionKey = array(\i18n("Product.label"),);

    //$optionKey = array('price_buy', 'visible', 'scaled', 'disp_order',
    //        'discount_rate', 'discount_enabled', 'stock_min', 'stock_max');

    $csv = new \Pasteque\Csv($_FILES['csv']['tmp_name'], $key, $optionKey,
            PLUGIN_NAME);
    if (!$csv->open()) {
        return $csv;
    }

    //manage empty string
//    $csv->setEmptyStringValue("visible", true);
//    $csv->setEmptyStringValue("scaled", false);
//    $csv->setEmptyStringValue("disp_order", null);
    return $csv;
}

function import_csv($csv) {
    $error = 0;
    $create = 0;
    $update = 0;
    $error_mess = array();

    while ($tab = $csv->readLine()) {
        //init optionnal values
        $AllKeyPossible = array_merge($csv->getKeys(), $csv->getOptionalKeys());
        $tab = initArray($AllKeyPossible, $tab);
        
        //validation
        $line_errors = validateLine($tab, $csv->getCurrentLineNumber());
        if (count($line_errors) > 0) {
            $error_mess = array_merge($error_mess, $line_errors);
            $error ++;
            continue;
        }
        
        
        //get the product
        $product = \Pasteque\ProductsService::getByRef($tab[\i18n("Product.reference")]);
        //if the product does not exist, pass...
        if ($product === NULL) {
            $error_mess[] = \i18n("The product with reference '%s' does not exists at line %d", 
                    PLUGIN_NAME, $tab[\i18n("Product.reference")], 
                    $csv->getCurrentLineNumber());
            $error++;
        }
        
        //get the location
        //TODO in case of multilocation, get the location
        $locationId = 0;
        
        //try to get a stock level
        $stockLevel = \Pasteque\StocksService::getLevel($product->id, $locationId);
        if ($stockLevel->id === NULL) {
            //create a new stockLevel
            $stockLevel = new \Pasteque\StockLevel( 
                    $product->id, $locationId, NULL, 
                    $tab[\i18n("QuantityMin")], 
                    $tab[\i18n("QuantityMax")]);
            $result = \Pasteque\StocksService::createLevel($stockLevel);
            if ($result !== FALSE) {
                $create++;
            }
        } else {
            $stockLevel->max = $tab[\i18n("QuantityMax")];
            $stockLevel->security = $tab[\i18n("QuantityMin")];
            $result = \Pasteque\StocksService::updateLevel($stockLevel);
            if ($result === true) {
                $update ++;
            }
            
        }
        
    }

    $message = \i18n("%d line(s) inserted, %d line(s) modified, %d error(s)",
            PLUGIN_NAME, $create, $update, $error);
    return array($message, $error_mess);
}

function validateLine($tab, $lineNumber) {
    $error_mess = array();
    //if both are empty
    if ($tab[\i18n("QuantityMin")] === NULL && $tab[\i18n("QuantityMax")] === NULL) {
        $error_mess[] = \i18n("Line %d ignored because QuantityMin and QuantityMax are empty", 
                PLUGIN_NAME, $lineNumber);
        return $error_mess;
    }
    //if no numeric
    if (is_numeric($tab[\i18n("QuantityMin")]) === FALSE 
            && trim($tab[\i18n("QuantityMin")]) != '' ) {
        $error_mess[] = \i18n("Line %d: QuantityMin is not numeric", PLUGIN_NAME,
                $lineNumber);
    }
    if (is_numeric($tab[\i18n("QuantityMax")]) == FALSE
            && trim($tab[\i18n("QuantityMax")]) != '') {
        $error_mess[] = \i18n("Line %d: QuantityMax is not numeric", PLUGIN_NAME,
                $lineNumber);
    }
    
    //at this point, we must return if one of them are not numeric:
    if (count($error_mess) > 0){
        return $error_mess;
    }
    
    //if max < min 
    if ($tab[\i18n("QuantityMin")] >= $tab[\i18n("QuantityMax")]
            && trim($tab[\i18n("QuantityMax")]) != '') {
        $error_mess[] = \i18n("Line %d: QuantityMin must be lower than QuantityMax", 
                PLUGIN_NAME, $lineNumber);
    }
    
    return $error_mess;
    
}

// return an array whith all key set
function initArray($key, $tab) {
    $array = array_fill_keys($key, NULL);
    //set QuantityMax and QuantityMin to NULL if empty
    $tab[\i18n("QuantityMax")] = (trim($tab[\i18n("QuantityMax")]) === '') ?
            NULL : $tab[\i18n("QuantityMax")];
    $tab[\i18n("QuantityMin")] = (trim($tab[\i18n("QuantityMin")]) === '') ?
            NULL : $tab[\i18n("QuantityMin")];

    foreach ($tab as $field => $value) {
        $array[$field] = $value;
    }
    return $array;
}


?>
<div class="container_scroll">
    <div class="stick_row stickem-container">
        <div id="content_liste" class="grid_9">
            <div class="blc_content">
                <div class="blc_ti">
                    <h1><?php \pi18n("Import alerts from csv file", PLUGIN_NAME); ?></h1>
                </div>
                <?php \Pasteque\tpl_msg_box($message, $error); ?>
                <form class="edit" method="post" action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'alertManagement');?>" enctype="multipart/form-data">
                    <div class="row">
                        <label for='csv' >
                            <?php \pi18n("File", PLUGIN_NAME) ?>:
                        </label>
                            <input type="file" name="csv">
                    </div>
                    <div class="row actions">
                        <button class="btn-send" type="submit" id="<?php \pi18n("send", PLUGIN_NAME)?>" name="<?php \pi18n("send", PLUGIN_NAME)?>" >
                            <?php \pi18n("send", PLUGIN_NAME)?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div id="sidebar_menu" class="grid_3 stickem">
            <div class="blc_content visible">
                <ul id="menu_site">
                    <li>
                        <a href="#">Accès rapide</a>
                        <ul>
                            <li>&nbsp;</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>