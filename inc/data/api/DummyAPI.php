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

/** This is a "do nothing" API for automatic testing */
class DummyAPI extends APIService {

    protected function check() {
        return $this->action == "succeed" || $this->action == "fail"
                || $this->action == "param";
    }
    protected function proceed() {
        switch ($this->action) {
        case "param":
            switch ($this->params['result']) {
            case "succeed":
                $this->succeed("I'm Dummy!");
                break;
            case "fail":
                $this->fail("I'm Dummy!");
                break;
            }
            break;
        case "succeed":
            $this->succeed("I'm Dummy!");
            break;
        case "fail":
            $this->fail("I'm Dummy!");
            break;
        }
    }
}

?>
