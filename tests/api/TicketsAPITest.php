<?php
//    Pasteque server testing
//
//    Copyright (C) 2012 Scil (http://scil.coop)
//
//    This file is part of Pasteque.
//
//    Pasteque is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Pasteque is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Pasteque.  If not, see <http://www.gnu.org/licenses/>.
namespace Pasteque;

require_once(dirname(dirname(__FILE__)) . "/common_load.php");

class TicketsAPITest extends \PHPUnit_Framework_TestCase {

    const API = "TicketsAPI";

    private $cashId;
    private $locationId;
    private $jsTicket1;
    private $jsTicket2;

    protected function setUp() {
        // Attribute set
        $set = new AttributeSet("set");
        $attr = new Attribute("attr", 1);
        $val = new AttributeValue("value");
        $attr->id = AttributesService::createAttribute($attr);
        $val->id = AttributesService::createValue($val, $attr->id);
        $attr->addValue($val);
        $set->addAttribute($attr);
        $set->id = AttributesService::createSet($set);
        // Product, tax and category
        $taxCat = new TaxCat("Tax");
        $tax = new Tax(null, "Tax", stdtimefstr("2001-01-01 00:00:00"),
                0.1);
        $taxCat->addTax($tax);
        $taxCat->id = TaxesService::createCat($taxCat);
        $taxCat2 = new TaxCat("Tax2");
        $tax2 = new Tax(null, "Tax2",
                stdtimefstr("2001-01-01 00:00:00"), 0.2);
        $taxCat2->addTax($tax2);
        $taxCat2->id = TaxesService::createCat($taxCat2);
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO CATEGORIES (ID, NAME) "
                . "VALUES (:id, :name)");
        $stmt->execute(array(":id" => "-1", ":name" => "Refill"));
        $cat = new Category(null, "Category", false, 1);
        $cat->id = CategoriesService::createCat($cat);
        $prd = new Product("REF", "product", 1.0, $cat->id, 1,
                $taxCat->id, true, false, 0.5, $set->id);
        $prd->id = ProductsService::create($prd);
        $prd2 = new Product("REF2", "product2", 2.0, $cat->id, 1,
                $taxCat2->id, true, false, 0.5, null);
        $prd2->id = ProductsService::create($prd2);
        $prdRefill = new Product("REFILL", "Refill", 1.0, "-1", 1,
                $taxCat->id, true, false);
        $prdRefill->id = ProductsService::create($prdRefill);
        // Tariff area
        $srvArea = new TariffAreasService();
        $area = new TariffArea("area", 1);
        $area->addPrice($prd->id, 0.8);
        $area->id = $srvArea->create($area);
        // Customer
        $srvCust = new CustomersService();
        $cust = new Customer(1, "Cust", "It's me", "card", null, null, 50.0,
                10.0, 5.0, stdtimefstr("2012-01-01 00:00:00"), "It's", "me",
                "itsme@me.me", "012345", "23456", "11111", "Address1",
                "Address2", "59000", "City", "Region", "France", "Note", true);
        $cust->id = $srvCust->create($cust);
        // Location
        $locSrv = new LocationsService();
        $loc = new Location("Location");
        $loc->id = $locSrv->create($loc);
        $this->locationId = $loc->id;
        // Cash register
        $srvCashReg = new CashRegistersService();
        $cashReg = new CashRegister("Cash", $loc->id, 1);
        $cashReg->id = $srvCashReg->create($cashReg);
        // Cash
        $srvCash = new CashesService();
        $cash = $srvCash->add($cashReg->id);
        $cash->openDate = stdtimefstr("2000-02-02 02:02:02");
        $srvCash->update($cash);
        $this->cashId = $cash->id;
        // User
        $srvUsers = new UsersService();
        $user = new User("User", null, null, "0", true, false);
        $user->id = $srvUsers->create($user);
        // Currency
        $curr = new Currency("Eur", "€", ",", ".", "#,##0.00$", 1, true, false);
        $srvCurr = new CurrenciesService();
        $curr->id = $srvCurr->create($curr);
        // Discount profile
        $profSrv = new DiscountProfilesService();
        $prof = new DiscountProfile("Profile", 0.1);
        $prof->id = $profSrv->create($prof);
        // Ticket
        $tkt1 = array("date" => stdtimefstr("2012-01-01 00:00:00"),
                "userId" => $user->id, "customerId" => null,
                "type" => Ticket::TYPE_SELL, "custCount" => 3,
                "tariffAreaId" => null, "discountRate" => 0.0,
                "discountProfileId" => null,
                "payments" => array(array("type" => "cash", "amount" => 10,
                                "currencyId" => $curr->id,
                                "currencyAmount" => 12)),
                "lines" => array(array("dispOrder" => 1,
                                "productId" => $prd->id,
                                "taxId" => $tax->id,
                                "attributes" => null,
                                "quantity" => 1.0,
                                "price" => 10.0,
                                "discountRate" => 0.0)));
        $jsAttr = array("attributeSetId" => $set->id,
                "values" => array(array("id" => $attr->id,
                                "value" => "value")));
        $tkt2 = array("date" => stdtimefstr("2012-01-01 00:00:00"),
                "userId" => $user->id, "customerId" => null,
                "type" => Ticket::TYPE_SELL, "custCount" => 3,
                "tariffAreaId" => null, "discountRate" => 0.25,
                "discountProfileId" => $prof->id,
                "payments" => array(array("type" => "cash", "amount" => 10,
                                "currencyId" => $curr->id,
                                "currencyAmount" => 12)),
                "lines" => array(array("dispOrder" => 1,
                                "productId" => $prd->id,
                                "taxId" => $tax->id,
                                "attributes" => $jsAttr,
                                "quantity" => 1.0,
                                "price" => 10.0,
                                "discountRate" => 0.25)));
        $this->jsTicket1 = json_encode($tkt1);
        $this->jsTicket2 = json_encode($tkt2);
    }


    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM PAYMENTS") === false
                || $pdo->exec("DELETE FROM TAXLINES") === false
                || $pdo->exec("DELETE FROM TICKETLINES") === false
                || $pdo->exec("DELETE FROM TICKETS") === false
                || $pdo->exec("DELETE FROM RECEIPTS") === false
                || $pdo->exec("DELETE FROM CLOSEDCASH") === false
                || $pdo->exec("DELETE FROM CASHREGISTERS") === false
                || $pdo->exec("DELETE FROM TARIFFAREAS_PROD") === false
                || $pdo->exec("DELETE FROM TARIFFAREAS") === false
                || $pdo->exec("DELETE FROM STOCKDIARY") === false
                || $pdo->exec("DELETE FROM STOCKCURRENT") === false
                || $pdo->exec("DELETE FROM STOCKLEVEL") === false
                || $pdo->exec("DELETE FROM LOCATIONS") === false
                || $pdo->exec("DELETE FROM PRODUCTS_CAT") === false
                || $pdo->exec("DELETE FROM PRODUCTS") === false
                || $pdo->exec("DELETE FROM CATEGORIES") === false
                || $pdo->exec("DELETE FROM ATTRIBUTEINSTANCE") === false
                || $pdo->exec("DELETE FROM ATTRIBUTESETINSTANCE") === false
                || $pdo->exec("DELETE FROM ATTRIBUTEUSE") === false
                || $pdo->exec("DELETE FROM ATTRIBUTESET") === false
                || $pdo->exec("DELETE FROM ATTRIBUTEVALUE") === false
                || $pdo->exec("DELETE FROM ATTRIBUTE") === false
                || $pdo->exec("DELETE FROM TAXES") === false
                || $pdo->exec("DELETE FROM TAXCATEGORIES") === false
                || $pdo->exec("DELETE FROM PEOPLE") === false
                //|| $pdo->exec("DELETE FROM ROLES") === false
                || $pdo->exec("DELETE FROM CURRENCIES") === false
                || $pdo->exec("DELETE FROM CUSTOMERS") === false
                || $pdo->exec("DELETE FROM SHAREDTICKETS") === false
                || $pdo->exec("DELETE FROM DISCOUNTPROFILES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testSaveTicket() {
        $broker = new APIBroker(TicketsAPITest::API);
        $result = $broker->run("save", array("cashId" => $this->cashId, 
                        "locationId" => $this->locationId,
                        "ticket" => $this->jsTicket1));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertEquals(array("saved" => 1), $content, "Content mismatch");
    }

    public function testSaveDefaultLocation() {
        $broker = new APIBroker(TicketsAPITest::API);
        $result = $broker->run("save", array("cashId" => $this->cashId,
                        "ticket" => $this->jsTicket1));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertEquals(array("saved" => 1), $content, "Content mismatch");
    }

    /** @depends testSaveTicket */
    public function testSaveTicketAttr() {
        $broker = new APIBroker(TicketsAPITest::API);
        $result = $broker->run("save", array("cashId" => $this->cashId, 
                        "locationId" => $this->locationId,
                        "ticket" => $this->jsTicket2));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertEquals(array("saved" => 1), $content, "Content mismatch");
    }

    /** @depends testSaveTicketAttr */
    public function testSaveTickets() {
        $broker = new APIBroker(TicketsAPITest::API);
        $jsTickets = "[" . $this->jsTicket1 . ", " . $this->jsTicket2 . "]";
        $result = $broker->run("save", array("cashId" => $this->cashId, 
                        "locationId" => $this->locationId,
                        "tickets" => $jsTickets));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertEquals(array("saved" => 2), $content, "Content mismatch");
    }

    /** @depends testSaveTicket */
    public function testGetOpen() {
        $broker = new APIBroker(TicketsAPITest::API);
        $broker->run("save", array("cashId" => $this->cashId, 
                        "locationId" => $this->locationId,
                        "ticket" => $this->jsTicket2));
        $result = $broker->run("getOpen", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(1, count($content), "Content size mismatch");
        $tkt = $content[0];
        $this->assertNotNull($tkt, "Ticket is null");
        $this->assertEquals(1, count($tkt->lines), "Line count mismatch");
        $this->assertEquals(1, count($tkt->payments),
                "Payments count mismatch");
        $this->markTestIncomplete("Check other fields");
    }


    private function checkSharedTktEquality($ref, $read) {
        $this->assertEquals($ref->id, $read->id, "Id mismatch");
        $this->assertEquals($ref->label, $read->label, "Label mismatch");
        $this->assertEquals($ref->data, $read->data, "Data mismatch");
    }


    public function testShare() {
        $tkt = SharedTicket::__build("1", "Shared", \base64_encode(0xabcdef));
        $json = json_encode($tkt);
        $broker = new APIBroker(TicketsAPITest::API);
        $result = $broker->run("share", array("ticket" => $json));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue($content, "API call failed");
        $read = TicketsService::getSharedTicket($tkt->id);
        $read->data = \base64_encode($read->data); // encode for equality
        $this->checkSharedTktEquality($tkt, $read);
    }

    public function testGetShared() {
        $tkt = SharedTicket::__build("1", "Shared", 0xabcdef);
        TicketsService::createSharedTicket($tkt);
        $broker = new APIBroker(TicketsAPITest::API);
        $result = $broker->run("getShared", array("id" => $tkt->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $tkt->data = \base64_encode($tkt->data); // encode for checking
        $this->checkSharedTktEquality($tkt, $content);
    }

    public function testGetAllShared() {
        $tkt = SharedTicket::__build("1", "Shared", 0xabcdef);
        TicketsService::createSharedTicket($tkt);
        $tkt2 = SharedTicket::__build("2", "Shared2" ,0xbc98d32f);
        TicketsService::createSharedTicket($tkt2);
        $broker = new APIBroker(TicketsAPITest::API);
        $result = $broker->run("getAllShared", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(2, count($content), "Content size mismatch");
        $toCheck = array($tkt, $tkt2);
        $count = 0;
        foreach ($content as $rtkt) {
            $ref = null;
            $count++;
            if ($rtkt->id == $tkt->id) {
                $ref = $tkt;
            } else if ($rtkt->id == $tkt2->id) {
                $ref = $tkt2;
            }
            $this->assertNotNull($ref, "Unknown line");
            $ref->data = \base64_encode($ref->data); // encode for equality
            $this->checkSharedTktEquality($ref, $rtkt);
            for ($i = 0; $i < count($toCheck); $i++) {
                $t = $toCheck[$i];
                if ($t->id == $ref->id) {
                    array_splice($toCheck, $i, 1);
                    break;
                }
            }
        }
        $this->assertEquals(0, count($toCheck), "Duplicated shared tickets");
    }

    public function testDelShared() {
        $tkt = new SharedTicket("Shared", 0xabcdef);
        $tkt->id = TicketsService::createSharedTicket($tkt);
        $broker = new APIBroker(TicketsAPITest::API);
        $result = $broker->run("delShared", array("id" => $tkt->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertTrue($content, "Content is not true");
        $this->assertNull(TicketsService::getSharedTicket($tkt->id),
                "Shared ticket is still there");
    }
}