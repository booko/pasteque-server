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

class TicketsAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'getShared':
            return isset($this->params['id']);
        case 'getAllShared':
            return true;
        case 'delShared':
            return isset($this->params['id']);
        case 'share':
            return isset($this->params['ticket']);
        case 'save':
            return (isset($this->params['ticket'])
                            || isset($this->params['tickets']))
                    && isset($this->params['cashId']);
        case 'getOpen':
            return true;
        }
        return false;
    }

    protected function proceed() {
        switch ($this->action) {
        case 'getShared':
            $this->succeed(TicketsService::getSharedTicket($this->params['id']));
            break;
        case 'getAllShared':
            $this->succeed(TicketsService::getAllSharedTickets());
            break;
        case 'delShared':
            $this->succeed(TicketsService::deleteSharedTicket($this->params['id']));
            break;
        case 'share':
            $json = json_decode($this->params['ticket']);
            $id = null;
            if (property_exists($json, 'id')) {
                $id = $json->id;
            }
            $ticket = null;
            if ($id !== null) {
                $ticket = SharedTicket::__build($id, $json->label,
                        $json->data);
                $this->succeed(TicketsService::updateSharedTicket($ticket));
            } else {
                $ticket = new SharedTicket($json->label, $json->data);
                $this->succeed(TicketsService::createSharedTicket($ticket));
            }
            break;
        case 'getOpen':
            $this->succeed(TicketsService::getOpen());
            break;
        case 'save':
            // Receive ticket data as json
            // Convert single ticket to array for consistency
            if (isset($this->params['tickets'])) {
                $json = json_decode($this->params['tickets']);
            } else {
                $json = array(json_decode($this->params['ticket']));
            }
            $cashId = $this->params['cashId'];
            // Check location existence if there is one
            $locSrv = new LocationsService();
            if (isset($this->params['locationId'])) {
                $location = $locSrv->get($this->params['locationId']);
                if ($location === null) {
                    $this->fail(APIError::$ERR_GENERIC);
                    break;
                }
                $locationId = $this->params['locationId'];
            } else {                
                $locations = $locSrv->getAll();
                if (count($locations) === 0) {
                    $this->fail(APIError::$ERR_GENERIC);
                    break;
                }
                $locationId = $locations[0]->id;
            }
            // Register tickets
            $ticketsCount = count($json);
            $successes = 0;
            $pdo = PDOBuilder::getPDO();
            $pdo->beginTransaction();
            foreach ($json as $jsonTkt) {
                if ($jsonTkt === null) {
                    break;
                }
                $userId = $jsonTkt->userId;
                $customerId = $jsonTkt->customerId;
                $date = $jsonTkt->date;
                $tktType = $jsonTkt->type;
                $custCount = $jsonTkt->custCount;
                $tariffAreaId = $jsonTkt->tariffAreaId;
                $discountRate = $jsonTkt->discountRate;
                $discountProfileId = $jsonTkt->discountProfileId;
                // Get lines
                $lines = array();
                foreach ($jsonTkt->lines as $jsLine) {
                    // Get line info
                    $number = $jsLine->dispOrder;
                    $productId = $jsLine->productId;
                    $quantity = $jsLine->quantity;
                    $price = $jsLine->price;
                    $taxId = $jsLine->taxId;
                    $lineDiscountRate = $jsLine->discountRate;
                    if ($jsLine->attributes !== null) {
                        $jsAttr = $jsLine->attributes;
                        $attrSetId = $jsAttr->attributeSetId;
                        $values = $jsAttr->values;
                        $desc = "";
                        foreach ($values as $val) {
                            $desc .= $val->value . ", ";
                        }
                        $desc = substr($desc, 0, -2);
                        $attrs = new AttributeSetInstance($attrSetId, $desc);
                        foreach ($values as $val) {
                            $attrVal = new AttributeInstance(null,
                                    $val->id, $val->value);
                            $attrs->addAttrInst($attrVal);
                        }
                        $attrsId = TicketsService::createAttrSetInst($attrs);
                        
                        if ($attrsId === false) {
                            // Fail, will check line count to continue
                            break;
                        }
                    } else {
                        $attrsId = null;
                    }
                    $product = ProductsService::get($productId);
                    $tax = TaxesService::getTax($taxId);
                    if ($product == null || $tax == null) {
                        break;
                    }
                    $newLine = new TicketLine($number, $product,
                            $attrsId, $quantity, $price, $tax,
                            $lineDiscountRate);
                    $lines[] = $newLine;
                }
                if (count($lines) != count($jsonTkt->lines)) {
                    break;
                }
                // Get payments
                $payments = array();
                foreach ($jsonTkt->payments as $jspay) {
                    $type = $jspay->type;
                    $amount = $jspay->amount;
                    if (!isset($jspay->currencyId)) {
                        $currSrv = new CurrenciesService();
                        $currencyId = $currSrv->getDefault()->id;
                        $currencyAmount = $amount;
                    } else {
                        $currencyId = $jspay->currencyId;
                        $currencyAmount = $jspay->currencyAmount;
                    }
                    $payment = new Payment($type, $amount, $currencyId,
                            $currencyAmount);
                    $payments[] = $payment;
                }
                $ticket = new Ticket($tktType, $userId, $date, $lines,
                        $payments, $cashId, $customerId, $custCount,
                        $tariffAreaId, $discountRate, $discountProfileId);
                if (TicketsService::save($ticket, $locationId)) {
                    $successes++;
                } else {
                    break;
                }
            }
            // Check if all tickets were saved, if not rollback and error
            $ret = ($successes == $ticketsCount);
            if ($ret === true) {
                $pdo->commit();
                $this->succeed($ret);
            } else {
                $pdo->rollback();
                $this->fail(APIError::$ERR_GENERIC);
            }
            break;
        }
    }
}