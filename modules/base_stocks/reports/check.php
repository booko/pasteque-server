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

class StockCheckReport implements \Pasteque\ReportInterface {

    public function __construct() {
        $this->headers = array(\i18n("Product.reference"),
                \i18n("Counted stock", PLUGIN_NAME),
                \i18n("Actual stock", PLUGIN_NAME),
                \i18n("Difference", PLUGIN_NAME));
        $this->fields = array("ref", "counted", "actual", "diff");
    }

    public function run($values) {
        return new StockCheckRun($this, $values);
    }
    public function isGrouping() {
        return false;
    }
    public function getHeaders() {
        return $this->headers;
    }
    /** Get the array of field names. */
    public function getFields() {
        return $this->fields;
    }
    public function hasSubtotals() {
        return false;
    }
    public function getSubtotals() {
        return null;
    }
    public function hasTotals() {
        return false;
    }
    public function getTotals() {
        return null;
    }
    public function getTitle() {
        return \i18n("Stock check", PLUGIN_NAME);
    }
    public function getDomain() {
        return PLUGIN_NAME;
    }
    public function getId() {
        return "check";
    }

}

class StockCheckRun implements \Pasteque\ReportRunInterface {
    private $data;
    private $i;

    public function __construct($report, $values) {
        $this->data = array();
        $this->i = 0;
        $countedStock = array();
        if (!isset($values['location'])) {
            $locSrv = new \Pasteque\LocationsService();
            $locations = $locSrv->getAll();
            $defaultLocationId = $locations[0]->id;
            $values['location'] = $defaultLocationId;
        }
        foreach ($values as $key => $value) {
            if (strpos($key, "qty-") === 0) {
                $productId = substr($key, 4);
                $qty = $value;
                $countedStock[$productId] = $qty;
            }
        }
        $categories = \Pasteque\CategoriesService::getAll();
        $products = \Pasteque\ProductsService::getAll(TRUE);

        $prdCat = array();
        // Build listing by categories
        foreach ($products as $product) {
            if ($product->categoryId !== \Pasteque\CompositionsService::CAT_ID) {
                $prdCat[$product->categoryId][] = $product;
            }
        }
        // Get stock to compare with counted stock
        $levels = array();
        $rawLevels = \Pasteque\StocksService::getLevels($values['location']);
        foreach ($rawLevels as $level) {
            $levels[$level->productId] = $level;
        }
        foreach ($categories as $category) {
            if (isset($prdCat[$category->id])) {
                foreach ($prdCat[$category->id] as $product) {
                    $counted = 0;
                    if (isset($countedStock[$product->id])) {
                        $counted = $countedStock[$product->id];
                    }
                    $actual = 0;
                    if (isset($levels[$product->id])) {
                        $actual = $levels[$product->id]->qty;
                    }
                    if ($counted !== $actual) {
                        $this->data[] = array("ref" => $product->reference,
                                "counted" => $counted,
                                "actual" => $actual,
                                "diff" => $counted - $actual,
                                        );
                    }
                }
            }
        }
    }

    public function fetch() {
        if ($this->i < count($this->data)) {
            $line = $this->data[$this->i];
            $this->i++;
            return $line;
        } else {
            return false;
        }
    }
    public function isGroupStart() {
        return false;
    }
    public function isGroupEnd() {
        return false;
    }
    public function getCurrentGroup() {
        return null;
    }
    public function getSubtotals() {
        return null;
    }
    public function getTotals() {
        return null;
    }
}

$report = new StockCheckReport();
\Pasteque\register_report($report);