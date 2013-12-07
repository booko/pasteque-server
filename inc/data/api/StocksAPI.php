<?php
//    POS-Tech API
//
//    Copyright (C) 2012 Scil (http://scil.coop)
//
//    This file is part of POS-Tech.
//
//    POS-Tech is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    POS-Tech is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with POS-Tech.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque;

class StocksAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'getAll':
            return isset($this->params['location']);
        }
        return false;
    }

    protected function proceed() {
        switch ($this->action) {
        case 'getAll':
            $location = NULL;
            if (isset($this->params['location'])) {
                $location = $this->params['location'];
                $location = StocksService::getLocationId($location);
                if ($location === NULL) {
                    $this->fail("unknown location");
                    return;
                }
            }
            $stocks = StocksService::getQties($location);
            $levels = StocksService::getLevels($location);
            $ret = array();
            foreach ($stocks as $prd => $qty) {
                $stock = new \StdClass();
                $stock->product_id = $prd;
                $stock->qty = $qty;
                $found = FALSE;
                foreach ($levels as $level) {
                    if ($level->product_id == $prd) {
                        $stock->security = $level->security;
                        $stock->max = $level->max;
                        $found = TRUE;
                        break;
                    }
                }
                if (!$found) {
                    $stock->security = NULL;
                    $stock->max = NULL;
                }
                $ret[] = $stock;
            }
            $this->succeed($ret);
            break;
        }
    }
}

?>
