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

GET(id)
Get cash by id, no matter it's state.

UPDATE(cash)
When client sends a cash, it may have an id or not. If the id is present the
cash is updated. If not a new cash is created. In all cases return the cash.

*/

class CashesAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'get':
            return isset($this->params['host']) || isset($this->params['id']);
        case 'update':
            return isset($this->params['cash']);
        case 'zticket':
            return isset($this->params['id']);
        }
        return false;
    }

    /** Run the service and set result. */
    protected function proceed() {
        $srv = new CashesService();
        switch ($this->action) {
        case 'get':
            if (isset($this->params['id'])) {
                $ret = $srv->get($this->params['id']);
            } else {
                $ret = $srv->getHost($this->params['host']);
                if ($ret === null || $ret->isClosed()) {
                    $ret = null;
                }
            }
            $this->succeed($ret);
            break;
        case 'zticket':
            $ret = $srv->getZTicket($this->params['id']);
            $this->succeed($ret);
            break;
        case 'update':
            $json = json_decode($this->params['cash']);
            $open = null;
            $id = null;
            if (property_exists($json, 'id')) {
                $id = $json->id;
            }
            if (property_exists($json, 'openDate')) {
                $open = $json->openDate;
            }
            $close = null;
            if (property_exists($json, 'closeDate')) {
                $close = $json->closeDate;
            }
            $host = $json->host;
            $sequenc = null;
            if (property_exists($json, 'sequence')) {
                $sequence = $json->sequence;
            }
            if ($id !== null) {
                // Update an existing cash
                $cash = Cash::__build($id, $host, $sequence, $open, $close);
                if ($srv->update($cash)) {
                    $this->succeed($cash);
                } else {
                    $this->fail(APIError::$ERR_GENERIC);
                }
            } else {
                // Create a cash and update with given data
                if ($srv->add($host)) {
                    $cash = $srv->getHost($host);
                    $cash->openDate = $open;
                    $cash->closeDate = $close;
                    if ($srv->update($cash)) {
                        $this->succeed($cash);
                    } else {
                        $this->fail(APIError::$ERR_GENERIC);
                    }
                } else {
                    $this->fail(APIError::$ERR_GENERIC);
                }
            }
            break;
        }
    }
}
