<?php
//    Pastèque API
//
//    Copyright (C) 2015 Scil (http://scil.coop)
//    Cédric Houbart, Philippe Pary
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
        case 'search':
            return ($this->isParamSet("ticketId")
                    || $this->isParamSet("ticketType")
                    || $this->isParamSet("cashId")
                    || $this->isParamSet("dateStart")
                    || $this->isParamSet("dateStop")
                    || $this->isParamSet("customerId")
                    || $this->isParamSet("userId")
                    || $this->isParamSet("limit"));
        case 'delete':
            return $this->isParamSet("id");
        }
        return false;
    }

    protected function proceed() {
        switch ($this->action) {
        case 'getShared':
            $tkt = TicketsService::getSharedTicket($this->params['id']);
            $this->succeed($tkt);
            break;
        case 'getAllShared':
            $tkts = TicketsService::getAllSharedTickets();
            $this->succeed($tkts);
            break;
        case 'delShared':
            $this->succeed(TicketsService::deleteSharedTicket($this->params['id']));
            break;
        case 'share':
            $json = json_decode($this->params['ticket']);
            $ticket = SharedTicket::__build($json->id, $json->label,
                    $json->customerId, $json->custCount, $json->tariffAreaId,
                    $json->discountProfileId, $json->discountRate);
            $lines = array();
            foreach ($json->lines as $jsLine) {
                // Get line info
                $tktLine = new SharedTicketLines($ticket->id,
                        $jsLine->dispOrder, $jsLine->productId, $jsLine->taxId,
                        $jsLine->quantity, $jsLine->discountRate,
                        $jsLine->price, $jsLine->attributes);
                $lines[] = $tktLine;
            }
            if (TicketsService::createSharedTicket($ticket, $lines) === false) {
                $this->succeed(TicketsService::updateSharedTicket($ticket,
                                $lines));
            } else {
                $this->succeed(true);
            }
            break;
        case 'getOpen':
            $this->succeed(TicketsService::getOpen());
            break;
        case 'search':
            $this->succeed(TicketsService::search($this->getParam("ticketId"),
                    $this->getParam("ticketType"), $this->getParam("cashId"),
                    $this->getParam("dateStart"), $this->getParam("dateStop"),
                    $this->getParam("customerId"), $this->getParam("userId"),
                    $this->getParam("limit")));
            break;
        case 'delete':
            if (!TicketsService::delete($this->getParam("id"))) {
                $this->fail(APIError::$ERR_GENERIC);
            } else {
                $this->succeed(true);
            }
            break;
        case 'save':
            // Receive ticket data as json
            // Convert single ticket to array for consistency
            if (isset($this->params['tickets'])) {
                $json = json_decode($this->params['tickets']);
            } else {
                $json = array(json_decode($this->params['ticket']));
            }
            // Get location from cash register
            $cashId = $this->params['cashId'];
            $cashSrv = new CashesService();
            $cash = $cashSrv->get($cashId);
            if ($cash === null) {
                $this->fail(new APIError("Unknown cash session"));
                break;
            }
            $cashRegSrv = new CashRegistersService();
            $cashReg = $cashRegSrv->get($cash->cashRegisterId);
            if ($cashReg === null) {
                $this->fail(new APIError("Cash register not found"));
                break;
            }
            $locationId = $cashReg->locationId;
            if ($locationId === null) {
                $this->fail(new APIError("Location not set"));
                break;
            }
            // Register tickets
            $ticketsCount = count($json);
            $successes = 0;
            $pdo = PDOBuilder::getPDO();
            if (!$pdo->beginTransaction()) {
                $this->fail(APIError::$ERR_GENERIC);
                break;
            }
            foreach ($json as $jsonTkt) {
                if ($jsonTkt === null) {
                    $this->fail(new APIError("Unable to decode ticket"));
                    break;
                }
                $ticketId = $jsonTkt->ticketId;
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
                    if (property_exists($jsLine, "productId")) {
                        $productId = $jsLine->productId;
                    } else {
                        $productId = null;
                    }
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
                            $this->fail(new APIError("Unknown attributes"));
                            break;
                        }
                    } else {
                        $attrsId = null;
                    }
                    $product = ProductsService::get($productId);
                    if ($product === null) {
                        $product = new Product($productId,$productId,$productId,null,null,null,$taxId,true,false);
                    }
                    $tax = TaxesService::getTax($taxId);
                    if ($tax == null) {
                        $this->fail(new APIError("Unknown tax"));
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
                    if (!property_exists($jspay, "currencyId")
                            || $jspay->currencyId === null) {
                        $currSrv = new CurrenciesService();
                        $currencyId = $currSrv->getDefault()->id;
                        $currencyAmount = $amount;
                    } else {
                        $currencyId = $jspay->currencyId;
                        $currencyAmount = $jspay->currencyAmount;
                    }
                    $backType = null;
                    $backAmount = null;
                    if (property_exists($jspay, "back")
                            && $jspay->back !== null) {
                        $backType = $jspay->back->type;
                        $backAmount = $jspay->back->amount;
                    }
                    $payment = new Payment($type, $amount, $currencyId,
                            $currencyAmount, $backType, $backAmount);
                    $payments[] = $payment;
                }
                $ticket = new Ticket($tktType, $userId, $date, $lines,
                        $payments, $cashId, $customerId, $custCount,
                        $tariffAreaId, $discountRate, $discountProfileId);
                $ticket->ticketId = $ticketId;
                if (isset($jsonTkt->id)) {
                    // Ticket edit
                    $ticket->id = $jsonTkt->id;
                    //Check if cash is still opened
                    $oldTicket = TicketsService::get($id);
                    if($oldTicket != null) {
                        $cashSrv = new CashesService();
                        $cash = $cashSrv->get($oldTicket->cashId);
                        if ($cash->isClosed()) {
                            $this->fail(new APIError("Cannot edit a ticket from "
                                            . "a closed cash"));
                            break;
                        }
                        // Delete the old ticket and recreate
                        if (TicketsService::delete($oldTicket->id)
                                && TicketsService::save($ticket, $locationId)) {
                            $successes++;
                        } else {
                            $this->fail(new APIError("Unable to edit ticket"));
                            break;
                        }
                    }
                } else {
                    // New ticket
                    if (TicketsService::save($ticket)) {
                        $successes++;
                    } else {
                        $this->fail(new APIError("Unable to save ticket"));
                        break;
                    }
                }
            }
            // Check if all tickets were saved, if not rollback and error
            $ret = ($successes == $ticketsCount);
            if ($ret === true) {
                if ($pdo->commit()) {
                    $ticketId++;
                    $cashRegSrv->setNextTicketId($ticketId,$cash->cashRegisterId);
                    $this->succeed(array("saved" => $ticketsCount));
                } else {
                    $this->fail(APIError::$ERR_GENERIC);
                }
            } else {
                $pdo->rollback();
                if ($this->result === null) {
                    $this->fail(APIError::$ERR_GENERIC);
                }
            }
            break;
        }
    }
}
