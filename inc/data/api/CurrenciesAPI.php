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

class CurrenciesAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'get':
            return isset($this->params['id']);
        case 'getMain':
            return true;
        case 'getAll':
            return true;
        }
        return false;
    }

    protected function proceed() {
        $srv = new CurrenciesService();
        switch ($this->action) {
        case 'get':
            $this->succeed($srv->get($this->params['id']));
            break;
        case 'getMain':
            $main = $srv->getDefault();
            $this->succeed($main);
            break;
        case 'getAll':
            $this->succeed($srv->getAll());
            break;
        }
    }

}