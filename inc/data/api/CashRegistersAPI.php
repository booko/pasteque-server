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

class CashRegistersAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'get':
            return isset($this->params['id']) || isset($this->params['label']);
        }
        return false;
    }

    /** Run the service and set result. */
    protected function proceed() {
        $srv = new CashRegistersService();
        switch ($this->action) {
        case 'get':
            if (isset($this->params['id'])) {
                $ret = $srv->get($this->params['id']);
                $this->succeed($ret);
            } else {
                $ret = $srv->search(
                        array(array("label", "=", $this->params['label'])));
                if (count($ret >= 1)) {
                    $this->succeed($ret[0]);
                } else {
                    $this->succeed(null);
                }
            }
            break;
        }
    }
}

?>
