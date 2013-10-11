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

/* Cash API specification

GET(host)
When client request a new cash, the server check for an active cash for
requested host. If found return it. Otherwise return NULL.

UPDATE(cash)
When client sends a cash, it may have an id or not. If the id is present the
cash is updated. If not a new cash is created for the host and it's id is
returned.

*/

class CashesAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'get':
            return isset($this->params['host']);
        case 'update':
            return isset($this->params['cash']);
        }
        return false;
    }

    /** Run the service and set result. */
    protected function proceed() {
        switch ($this->action) {
        case 'get':
            $ret = CashesService::getHost($_GET['host']);
            if ($ret === null || $ret->isClosed()) {
                $ret = null;
            }
            $this->succeed($ret);
            break;
        case 'update':
            $json = json_decode($params['cash']);
            $open = null;
            if (property_exists($json, 'openDate')) {
                $open = $json->openDate;
            }
            $close = null;
            if (property_exists($json, 'closeDate')) {
                $close = $json->closeDate;
            }
            $host = $json->host;
            if ($json->id !== null) {
                // Update an existing cash
                $cash = Cash::__build($json->id, $host, -1, $open, $close);
                if (CachesService::update($cash)) {
                    $ret = $cash;
                } else {
                    $ret = array("error" => "Server error");
                }
            } else {
                $cash = CashesService::add($host);
                $cash->openDate = $open;
                $cash->closeDate = $close;
                CashesService::update($cash);
                $ret = $cash;
            }
            $ret = array();
            $ret['result'] = CashesService::update($cash);
            $lastCash = CashesService::getHost($host);
            if ($lastCash != null && $lastCash->isClosed()) {
                if (CashesService::add($host)) {
                    $newCash = CashesService::getHost($host);
                } else {
                    $newCash = null;
                }
                $ret['cash'] = $newCash;
            } else {
                $ret['cash'] = $lastCash;
            }
            break;
        }
    }
}

?>
