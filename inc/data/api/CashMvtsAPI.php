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

class CashMvtsAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'move':
            return $this->isParamSet('cashId') && $this->isParamSet("date")
                    && $this->isParamSet("payment")
                    && $this->isParamSet("note");
        }
        return false;
    }

    protected function proceed() {
        switch ($this->action) {
        case 'move':
            $cashId = $this->getParam("cashId");
            $date = $this->getParam("date");
            $note = $this->getParam("note");
            $json = json_decode($this->getParam("payment"));
            $move = new CashMovement($cashId, $date, $json->type, $json->amount,
                    $json->currencyId, $json->currencyAmount, $note);
            $srv = new CashMovementsService();
            $id = $srv->create($move);
            if ($id !== false) {
                $this->succeed($id);
            } else {
                $this->fail(APIError::$ERR_GENERIC);
            }
            break;
        }
    }
}