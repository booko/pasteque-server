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

class ProductsAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'get':
            return isset($this->params['id']) || isset($this->params['code'])
                    || isset($this->params['reference']);
        case 'getAll':
            return true;
        case 'getCategory':
            return isset($this->params['id']);
        }
    }

    protected function proceed() {
        switch ($this->action) {
        case 'get':
            if (isset($this->params['id'])) {
                $this->succeed(ProductsService::get($this->params['id']));
            } else if (isset($this->params['reference'])) {
                $this->succeed(
                        ProductsService::getByRef($this->params['reference']));
            } else {
                $this->succeed(
                        ProductsService::getByCode($this->params['code']));
            }
            break;
        case 'getAll':
            if (isset($this->params['all']) && in_array($this->params['all'],array(true, 1))) {
                $this->succeed(ProductsService::getAll(true));
            }
            else {
                $this->succeed(ProductsService::getAll());
            }
            break;
        case 'getCategory':
            $this->succeed(ProductsService::getByCategory($this->params['id']));
            break;
        }
    }
}

?>
