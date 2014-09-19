<?php
//    Pastèque Web back office, Stocks module
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

namespace BaseStocks;

$message = null;
$error = null;
$modules = \Pasteque\get_loaded_modules(\Pasteque\get_user_id());
$multilocations = false;
$locSrv = new \Pasteque\LocationsService();
$locations = $locSrv->getAll();
$defaultLocationId = $locations[0]->id;
if (in_array("stock_multilocations", $modules)) {
    $multilocations = true;
}

$dateStr = isset($_POST['date']) ? $_POST['date'] : \i18nDate(time());
$time = \i18nRevDate($dateStr);
if (isset($_POST['reason']) && !isset($_POST['sendCsv'])) {
    $reason = $_POST['reason'];
    if ($multilocations) {
        $locationId = $_POST['location'];
    } else {
        $locationId = $defaultLocationId;
    }
    foreach ($_POST as $key => $value) {
        if (strpos($key, "qty-") === 0) {
            $productId = substr($key, 4);
            $product = \Pasteque\ProductsService::get($productId);
            switch ($reason) {
            case \Pasteque\StockMove::REASON_OUT_SELL:
            case \Pasteque\StockMove::REASON_IN_REFUND:
                $price = $product->priceSell;
                break;
            case \Pasteque\StockMove::REASON_IN_BUY:
            case \Pasteque\StockMove::REASON_OUT_BACK:
            case \Pasteque\StockMove::REASON_IN_MOVEMENT:
            case \Pasteque\StockMove::REASON_OUT_REFUND:
           	case \Pasteque\StockMove::REASON_OUT_MOVEMENT:
           	case \Pasteque\StockMove::REASON_RESET:
                if ($product->priceBuy !== null) {
                    $price = $product->priceBuy;
                } else {
                    $price = 0.0;
                }
                break;
            case \Pasteque\StockMove::REASON_TRANSFERT:
                $price = 0.0;
                break;
            }
            $qty = $value;
            if ($reason == \Pasteque\StockMove::REASON_TRANSFERT) {
                $destId = $_POST['destination'];
                $move = new \Pasteque\StockMove($time,
                        \Pasteque\StockMove::REASON_OUT_MOVEMENT, $productId,
                        $locationId, null, $qty, $price);
                $move2 = new \Pasteque\StockMove($time,
                        \Pasteque\StockMove::REASON_IN_MOVEMENT, $productId,
                        $destId, null, $qty, $price);
                if (\Pasteque\StocksService::addMove($move)
                        && \Pasteque\StocksService::addMove($move2)) {
                    $message = \i18n("Changes saved");
                } else {
                    $error = \i18n("Unable to save changes");
                }
            } else if ($reason == \Pasteque\StockMove::REASON_RESET) {
                $level = \Pasteque\StocksService::getLevel($productId,
                        $locationId, null);
                $move = new \Pasteque\StockMove($time, $reason, $productId,
                    $locationId, null, -$level->qty, $price);
                $move2 = new \Pasteque\StockMove($time, $reason, $productId,
                    $locationId, null, $qty, $price);
                if (\Pasteque\StocksService::addMove($move)
                        && \Pasteque\StocksService::addMove($move2)) {
                    $message = \i18n("Changes saved");
                } else {
                    $error = \i18n("Unable to save changes");
                }
            } else {
                $move = new \Pasteque\StockMove($time, $reason, $productId,
                        $locationId, null, $qty, $price);
                if (\Pasteque\StocksService::addMove($move)) {
                    $message = \i18n("Changes saved");
                } else {
                    $error = \i18n("Unable to save changes");
                }
            }
        }
    }
} else if (isset($_POST['sendCsv'])) {
    $key = array('Quantity', 'Reference');

    $csv = new \Pasteque\Csv($_FILES['csv']['tmp_name'], $key, array(),
            PLUGIN_NAME);
    if (!$csv->open()) {
        $error = $csv->getErrors();
    } else {
        //manage empty string
        $csv->setEmptyStringValue("Quantity", "0");
        echo "<script type=\"text/javascript\">\n";
        echo "jQuery(document).ready(function() {\n";
        while ($tab = $csv->readLine()) {
            $productOk = false;
            $quantityOk = false;
            $product = \Pasteque\ProductsService::getByRef($tab['Reference']);
            if ($product !== null) {
                $productOk = true;
            } else {
                if ($error === null) {
                    $error = array();
                }
                $error[] = \i18n("Unable to find product %s", PLUGIN_NAME, $tab['Reference']);
            }
            if ($tab['Quantity'] === "0" || intval($tab['Quantity']) !== 0) {
                $quantityOk = true;
            } else {
                if ($error === null) {
                    $error = array();
                }
                $error[] = \i18n("Undefined quantity for product %s", PLUGIN_NAME, $tab['Reference']);
            }
            if ($productOk && $quantityOk) {
                echo "setProduct(\"" . \Pasteque\esc_js($product->id) . "\", \""
                        . \Pasteque\esc_js($product->reference) . "\", "
                        . ($product->hasImage ? "1" : "0") . ", \""
                        . \Pasteque\esc_js($product->label) . "\", "
                        . $tab['Quantity'] . ");\n";
            }
        }
        echo "});\n";
        echo "</script>\n\n";
        $csv->close();
    }
}

$categories = \Pasteque\CategoriesService::getAll();
$products = \Pasteque\ProductsService::getAll(TRUE);

$locNames = array();
$locIds = array();
foreach ($locations as $location) {
    $locNames[] = $location->label;
    $locIds[] = $location->id;
}
$reasonIds = array(\Pasteque\StockMove::REASON_IN_BUY,
        \Pasteque\StockMove::REASON_OUT_SELL,
        \Pasteque\StockMove::REASON_OUT_BACK,
        \Pasteque\StockMove::REASON_TRANSFERT,
        \Pasteque\StockMove::REASON_RESET);
$reasonNames = array(\i18n("Buy", PLUGIN_NAME),
        \i18n("Sell", PLUGIN_NAME),
        \i18n("Return to supplier", PLUGIN_NAME),
        \i18n("Transfert", PLUGIN_NAME),
        \i18n("Reset", PLUGIN_NAME));
if (!$multilocations) {
    array_splice($reasonIds, 3, 1);
    array_splice($reasonNames, 3, 1);
}
?>
<h1><?php \pi18n("Stock move", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" id="move" method="post" enctype="multipart/form-data">
	<?php if ($multilocations) { \Pasteque\form_select("location", \i18n("Location"), $locIds, $locNames, null); }?>
	<?php \Pasteque\form_select("reason", \i18n("Operation", PLUGIN_NAME), $reasonIds, $reasonNames, null); ?>
	<?php if ($multilocations) { \Pasteque\form_select("destination", \i18n("Destination"), $locIds, $locNames, null); }?>
	<div class="row">
		<label for="date"><?php \pi18n("Date", PLUGIN_NAME); ?></label>
		<input type="text" class="dateinput" name="date" id="date" value="<?php echo $dateStr; ?>" />
	</div>

	<div id="catalog-picker"></div>

    <div class="row">
        <label for="file"><?php \pi18n("Load csv file", PLUGIN_NAME);?></label>
        <input id="file" type="file" name="csv">
    </div>
    <div class="row actions">
        <button class="btn-send" type="submit" name="sendCsv"><?php \pi18n("Load", PLUGIN_NAME); ?></button>
    </div>

	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th></th>
				<th><?php \pi18n("Product.reference"); ?></th>
				<th><?php \pi18n("Product.label"); ?></th>
				<th><?php \pi18n("Quantity"); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody id="list">
		</tbody>
	</table>

	<div class="row actions">
		<?php \Pasteque\form_send(); ?>
	</div>

</form>

<?php \Pasteque\init_catalog("catalog", "catalog-picker", "addProduct",
        $categories, $products); ?>
<script type="text/javascript">
	addProduct = function(productId) {
		var product = catalog.getProduct(productId);
		if (jQuery("#line-" + productId).length > 0) {
			// Add quantity to existing line
			var qty = jQuery("#line-" + productId + "-qty");
			var currVal = qty.val();
			qty.val(parseInt(currVal) + 1);
		} else {
			// Add line
			var src = "?p=img&w=product";
			if (product['hasImage']) {
			    src += "&id=" + product['id'];
			}
			var html = "<tr id=\"line-" + product['id'] + "\">\n";
			html += "<td><img class=\"thumbnail\" src=\"" + src + "\" /></td>\n";
			html += "<td>" + product['reference'] + "</td>\n";
			html += "<td>" + product['label'] + "</td>\n";
			html += "<td class=\"qty-cell\"><input class=\"qty\" id=\"line-" + product['id'] + "-qty\" type=\"numeric\" name=\"qty-" + product['id'] + "\" value=\"1\" />\n";
			html += "<td><a class=\"btn-delete\" href=\"\" onClick=\"javascript:deleteLine('" + product['id'] + "');return false;\"><?php \pi18n("Delete"); ?></a></td>\n";
			html += "</tr>\n"; 
			jQuery("#list").append(html);
		}
	}

	/** Set a new line with given quantity. Use only at start. */
	setProduct = function(productId, productRef, hasImage, productLabel, qty) {
		var src = "?p=img&w=product";
		if (hasImage == 1) {
		    src += "&id=" + productId;
		}
		var html = "<tr id=\"line-" + productId + "\">\n";
		html += "<td><img class=\"thumbnail\" src=\"" + src + "\" /></td>\n";
		html += "<td>" + productRef + "</td>\n";
		html += "<td>" + productLabel + "</td>\n";
		html += "<td class=\"qty-cell\"><input class=\"qty\" id=\"line-" + productId + "-qty\" type=\"numeric\" name=\"qty-" + productId + "\" value=\"" + qty + "\" />\n";
		html += "<td><a class=\"btn-delete\" href=\"\" onClick=\"javascript:deleteLine('" + productId + "');return false;\"><?php \pi18n("Delete"); ?></a></td>\n";
		html += "</tr>\n";
		jQuery("#list").append(html);
    }

	deleteLine = function(productId) {
		jQuery("#line-" + productId).detach();
	}

<?php if ($multilocations) { ?>
    reasonChange = function() {
        var reason = jQuery("#reason").val();
        if (reason == <?php echo \Pasteque\StockMove::REASON_TRANSFERT; ?>) {
            jQuery("#destination").prop("disabled", false);
        } else {
            jQuery("#destination").prop("disabled", true);
        }
    }
    jQuery("#reason").change(function() { reasonChange(); });
    reasonChange();
<?php } ?>
</script>
