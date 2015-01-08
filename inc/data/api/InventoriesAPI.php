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

class InventoriesAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'save':
            return $this->isParamSet("inventory");
        }
        return false;
    }

    protected function proceed() {
        $srv = new InventoriesService();
        switch ($this->action) {
        case 'save':
            $jsInv = json_decode($this->getParam("inventory"));
            $id = null;
            if (!property_exists($jsInv, 'id')) {
                $jsInv->id = null;
            }
            $inv = Inventory::__build($jsInv->id, $jsInv->date,
                    $jsInv->locationId);
            foreach ($jsInv->items as $item) {
                if (!property_exists($item, 'missingQty')) {
                    $item->missingQty = null;
                }
                if (!property_exists($item, 'unitValue')) {
                    $item->unitValue = null;
                }
                if (!property_exists($item, "attrSetInstId")) {
                    $item->attrSetInstId = null;
                }
                $item = new InventoryItem($inv->id, $item->productId,
                        $item->attrSetInstId, $item->qty, $item->lostQty,
                        $item->defectQty, $item->missingQty, $item->unitValue);
                $inv->addItem($item);
            }
            $id = $srv->create($inv);
            if ($id !== false) {
                $this->succeed($id);
            } else {
                $this->fail(APIError::$ERR_GENERIC);
            }
            break;
        }
    }
}