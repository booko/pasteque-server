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

class CustomersAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'get':
            return isset($this->params['id']);
        case 'getAll':
            return true;
        case 'addPrepaid':
            return isset($this->params['id'], $this->params['amount']);
        case 'getTop':
            return true;
        case 'save':
            return $this->isParamSet('customer')
                    || $this->isParamSet('customers');
        }
        return false;
    }

    protected function proceed() {
        $srv = new CustomersService();
        switch ($this->action) {
        case 'get':
            $this->succeed($srv->get($this->params['id']));
            break;
        case 'getAll':
            $this->succeed($srv->getAll());
            break;
        case 'addPrepaid':
            $this->succeed($srv->addPrepaid($this->params['id'],
                    $this->params['amount']));
            break;
        case 'getTop':
            $this->succeed($srv->getTop($this->getParam("limit")));
            break;
        case 'save':
            // Convert single customer to array for consistency
            if ($this->isParamSet('customers')) {
                $json = json_decode($this->params['customers']);
            } else {
                $json = array(json_decode($this->params['customer']));
            }
            $srv = new CustomersService();
            $result = array();
            // Begin transaction
            $pdo = PDOBuilder::getPDO();
            if (!$pdo->beginTransaction()) {
                $this->fail(APIError::$ERR_GENERIC);
                break;
            }
            foreach ($json as $customer) {
                if (isset($customer->id) && $customer->id !== null) {
                    // Edit
                    if (!$srv->update($customer)) {
                        // Error, rollback
                        $pdo->rollback();
                        $this->fail(APIError::$ERR_GENERIC);
                        return;
                    } else {
                        $result[] = $customer->id;
                    }
                } else {
                    // Create
                    $id = $srv->create($customer);
                    if ($id !== false) {
                        $result[] = $id;
                    } else {
                        // Error, rollback
                        $pdo->rollback();
                        $this->fail(APIError::$ERR_GENERIC);
                        return;
                    }
                }
            }
            // Success, commit
            if ($pdo->commit()) {
                $this->succeed(array("saved" => $result));
            } else {
                $this->fail(APIError::$ERR_GENERIC);
            }
            break;
        }
    }
}