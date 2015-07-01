<?php
//    Pastèque API
//
//    Copyright (C) 2015 Scil (http://scil.coop)
//    Philippe Pary (philippe@scil.coop)
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

namespace Pasteque;

/** This is a "do nothing" API for automatic testing */
class DiscountsAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'get':
            return isset($this->params['id']);
        case 'getAll':
            return true;
        }
        return false;
    }
    protected function proceed() {
        switch ($this->action) {
        case 'get':
            $this->succeed(DiscountsService::get($this->params['id']));
            break;
        case 'getAll':
            $this->succeed(DiscountsService::getAll());
            break;
        }
    }
}

?>
