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

class TicketsServiceTest extends \PHPUnit_Framework_TestCase {

    private $prdRefill;
    private $prd;
    private $prd2;
    private $tax;
    private $tax2;
    private $area;
    private $attrSet;
    private $attr;
    private $customer;
    private $cash;
    private $user;
    private $currency;
    private $location;
    private $discountProfile;

    protected function setUp() {
        // Attribute set
        $set = new AttributeSet("set");
        $attr = new Attribute("attr", 1);
        $val = new AttributeValue("value");
        $attr->id = AttributesService::createAttribute($attr);
        $val->id = AttributesService::createValue($val, $attr->id);
        $attr->addValue($val);
        $this->attr = $attr;
        $set->addAttribute($attr);
        $set->id = AttributesService::createSet($set);
        $this->attrSet = $set;
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
        $this->tax = $taxCat->taxes[0];
        $this->tax2 = $taxCat2->taxes[0];
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO CATEGORIES (ID, NAME) "
                . "VALUES (:id, :name)");
        $stmt->execute(array(":id" => "-1", ":name" => "Refill"));
        $cat = new Category(null, "Category", false, 1);
        $cat->id = CategoriesService::createCat($cat);
        $prd = new Product("REF", "product", 1.0, $cat->id, null, 1,
                $taxCat->id, true, false, 0.5, $set->id);
        $prd->id = ProductsService::create($prd);
        $this->prd = $prd;
        $prd2 = new Product("REF2", "product2", 2.0, $cat->id, null, 1,
                $taxCat2->id, true, false, 0.5, null);
        $prd2->id = ProductsService::create($prd2);
        $prdRefill = new Product("REFILL", "Refill", 1.0, "-1", null, 1,
                $taxCat->id, true, false);
        $prdRefill->id = ProductsService::create($prdRefill);
        $this->prd = $prd;
        $this->prd2 = $prd2;
        $this->prdRefill = $prdRefill;
        // Tariff area
        $srvArea = new TariffAreasService();
        $area = new TariffArea("area", 1);
        $area->addPrice($this->prd->id, 0.8);
        $area->id = $srvArea->create($area);
        $this->area = $area;
        // Discount profile
        $srvProfile = new DiscountProfilesService();
        $profile = new DiscountProfile("Discount profile", 1.0);
        $profile->id = $srvProfile->create($profile);
        $this->discountProfile = $profile;
        // Customer
        $srvCust = new CustomersService();
        $cust = new Customer(1, "Cust", "It's me", "card", null, null, 50.0,
                10.0, 5.0, stdtimefstr("2012-01-01 00:00:00"), "It's", "me",
                "itsme@me.me", "012345", "23456", "11111", "Address1",
                "Address2", "59000", "City", "Region", "France", "Note", true);
        $cust->id = $srvCust->create($cust);
        $this->customer = $cust;
        // Location
        $locSrv = new LocationsService();
        $loc = new Location("Location");
        $loc->id = $locSrv->create($loc);
        $this->location = $loc;
        // Cash
        $srvCashReg = new CashRegistersService();
        $cashReg = new CashRegister("CashReg", $loc->id, 1);
        $cashReg->id = $srvCashReg->create($cashReg);
        $srvCash = new CashesService();
        $cash = $srvCash->add($cashReg->id);
        $cash->openDate = stdtimefstr("2000-02-02 02:02:02");
        $srvCash->update($cash);
        $this->cash = $cash;
        // User
        $srvUsers = new UsersService();
        $user = new User("User", null, null, "0", true, false);
        $user->id = $srvUsers->create($user);
        $this->user = $user;
        // Currency
        $curr = new Currency("Eur", "€", ",", ".", "#,##0.00$", 1, true, false);
        $srvCurr = new CurrenciesService();
        $curr->id = $srvCurr->create($curr);
        $this->currency = $curr;
    }


    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM PAYMENTS") === false
                || $pdo->exec("DELETE FROM SHAREDTICKETLINES") === false
                || $pdo->exec("DELETE FROM SHAREDTICKETS") === false
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
                || $pdo->exec("DELETE FROM DISCOUNTPROFILES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    // Checking functions
    /////////////////////

    private function checkSaleEquality($tktId, $ref, $row) {
        $this->assertEquals($tktId, $row['TICKET'],
                "Ticket id mismatch in sale line");
        $this->assertEquals($ref->dispOrder, $row['LINE'],
                "Ticket line number mismatch");
        $this->assertEquals($ref->productId, $row['PRODUCT'],
                "Product id mismatch");
        $this->assertEquals($ref->attrSetInstId,
                $row['ATTRIBUTESETINSTANCE_ID'],
                "Attribute set instance id mismatch");
        $this->assertEquals($ref->quantity, $row['UNITS'],
                "Quantity mismatch");
        $this->assertEquals($ref->price, $row['PRICE'], "Price mismatch");
        $this->assertEquals($ref->taxId, $row['TAXID'],
                "Tax id mismatch in sale line");
        $this->assertEquals($ref->discountRate, $row['DISCOUNTRATE'],
                "Discount rate mismatch");
    }

    private function checkTaxEquality($tktId, $taxId, $base, $amount, $row) {
        $this->assertNotNull($row['ID'], "Tax line id is null");
        $this->assertEquals($tktId, $row['RECEIPT'],
                "Ticket id mismatch in tax line");
        $this->assertEquals($taxId, $row['TAXID'],
                "Tax id mismatch in tax line");
        $this->assertEquals($base, $row['BASE'], "Tax base amount mismatch");
        $this->assertEquals($amount, $row['AMOUNT'], "Tax amount mismatch");
    }

    private function checkPaymentEquality($tktId, $payment, $row) {
        $this->assertNotNull($row['ID'], "Payment id is null");
        $this->assertEquals($tktId, $row['RECEIPT'],
                "Ticket id mismatch in payment line");
        $this->assertEquals($payment->type, $row['PAYMENT'],
                "Payment type mismatch");
        $this->assertEquals($payment->amount, $row['TOTAL'],
                "Payment total mismatch");
        $this->assertEquals($payment->currencyId, $row['CURRENCY'],
                "Payment currency id mismatch");
        $this->assertEquals($payment->currencyAmount, $row['TOTALCURRENCY']);
    }

    private function checkTicketEquality($ref, $read) {
        $this->assertEquals($ref->id, $read->id, "Id mismatch");
        $this->assertEquals($ref->ticketId, $read->ticketId,
                "Ticket id mismatch");
        $this->assertEquals($ref->cashId, $read->cashId, "Cash id mismatch");
        $this->assertEquals($ref->type, $read->type, "Type mismatch");
        $this->assertEquals($ref->userId, $read->userId, "User id mismatch");
        $this->assertEquals($ref->date, $read->date, "Date mismatch");
        $this->assertEquals($ref->customerId, $read->customerId,
                "Customer id mismatch");
        $this->assertEquals($ref->tariffAreaId, $read->tariffAreaId,
                "Tariff area id mismatch");
        $this->assertEquals($ref->discountRate, $read->discountRate,
                "Discount rate mismatch");
        $this->assertEquals($ref->discountProfileId, $read->discountProfileId,
                "Discount profile id mismatch");
        $this->assertEquals(count($ref->lines), count($read->lines),
                "Line count mismatch");
        $this->assertEquals(count($ref->payments), count($read->payments),
                "Payment count mismatch");
        // Check lines
        // Check payments
        // TODO add content check
    }

    // Create ticket tests
    //////////////////////

    public function testCreateAttrSetInst() {
        $attrSetInst = new AttributeSetInstance($this->attrSet->id, "Value");
        $attrInst = new AttributeInstance(null, $this->attr->id, "AttrVal");
        $attrSetInst->addAttrInst($attrInst);
        $attrsId = TicketsService::createAttrSetInst($attrSetInst);
        $this->assertNotEquals(false, $attrsId, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        // Check attribute set instance
        $stmt = $pdo->prepare("SELECT * FROM ATTRIBUTESETINSTANCE");
        $this->assertNotEquals(false, $stmt->execute(),
                "Attribute set instance query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "Nothing found");
        $this->assertEquals($attrsId, $row['ID'], "Id mismatch");
        $this->assertEquals($this->attrSet->id, $row["ATTRIBUTESET_ID"],
                "Attribute set id mismatch");
        $this->assertEquals($attrSetInst->value, $row['DESCRIPTION'],
                "Value mismatch");
        $row = $stmt->fetch();
        $this->assertFalse($row, "Too much attribute set instances found");
        // Check attribute instance
        $stmt = $pdo->prepare("SELECT * FROM ATTRIBUTEINSTANCE");
        $this->assertNotEquals(false, $stmt->execute(),
                "Attribute instance Query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "Nothing found");
        $this->assertEquals($attrsId, $row['ATTRIBUTESETINSTANCE_ID'],
                "Attribute set instance id mismatch");
        $this->assertEquals($this->attr->id, $row["ATTRIBUTE_ID"],
                "Attribute id mismatch");
        $this->assertEquals($attrInst->value, $row['VALUE'],
                "Value mismatch");
        $row = $stmt->fetch();
        $this->assertFalse($row, "Too much attribute set instances found");
    }

    public function testSaveEmpty() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array(), array(),
                $this->cash->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmtRcpt = $pdo->prepare("SELECT * FROM RECEIPTS");
        $this->assertNotEquals($stmtRcpt->execute(), false,
                "Receipt query failed");
        $row = $stmtRcpt->fetch();
        $this->assertNotEquals(false, $row, "No receipt found");
        $this->assertEquals($id, $row['ID'], "Inconsistent receipt id");
        $this->assertEquals($this->cash->id, $row['MONEY'],
                "Inconsistent cash session id after create");
        $this->assertEquals($date, $db->readDate($row['DATENEW']),
                "Inconsistent date order after create");
        $stmtTkt = $pdo->prepare("SELECT * FROM TICKETS");
        $this->assertNotEquals($stmtTkt->execute(), false,
                "Receipt query failed");
        $row = $stmtTkt->fetch();
        $this->assertNotEquals(false, $row, "No receipt found");
        $this->assertEquals($id, $row['ID'], "Inconsistent ticket id");
        $this->assertEquals(Ticket::TYPE_SELL, $row['TICKETTYPE'],
                "Inconsistent ticket type");
        $this->assertEquals($this->user->id, $row['PERSON'],
                "Inconsistent user id");
        $this->assertNull($row['CUSTOMER'], "Customer id is not null");
        $this->assertNull($row['CUSTCOUNT'], "Customer count is not null");
        $this->assertNull($row['TARIFFAREA'], "Tariff area id is not null");
        $this->assertNull($row['DISCOUNTPROFILE_ID'],
                "Discount profile id is not null");
        $this->assertEquals(0.0, $row['DISCOUNTRATE'],
                "Discount rate mismatch");
        $stmtPmt = $pdo->prepare("SELECT * FROM PAYMENTS");
        $this->assertNotEquals($stmtPmt->execute(), false,
                "Payment query failed");
        $this->assertFalse($stmtPmt->fetch(), "Some payments found");
        $stmtTax = $pdo->prepare("SELECT * FROM TAXLINES");
        $this->assertNotEquals($stmtTax->execute(), false,
                "Tax query failed");
        $this->assertFalse($stmtTax->fetch(), "Some tax lines found");
        $stmtLine = $pdo->prepare("SELECT * FROM TICKETLINES");
        $this->assertNotEquals($stmtLine->execute(), false,
                "Line query failed");
        $this->assertFalse($stmtLine->fetch(), "Some ticket lines found");
        $row = $stmtTkt->fetch();
        $this->assertFalse($row, "Too much tickets found");
    }

    /** @depends testSaveEmpty */
    public function testSaveCustomer() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array(), array(),
                $this->cash->id, $this->customer->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmtTkt = $pdo->prepare("SELECT * FROM TICKETS");
        $this->assertNotEquals($stmtTkt->execute(), false,
                "Ticket query failed");
        $row = $stmtTkt->fetch();
        $this->assertEquals($this->customer->id, $row['CUSTOMER'],
                "Inconsistent customer id");
        $row = $stmtTkt->fetch();
        $this->assertFalse($row, "Too much tickets found");
    }

    /** @depends testSaveEmpty */
    public function testSaveTariffArea() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array(), array(),
                $this->cash->id, null, null, $this->area->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmtTkt = $pdo->prepare("SELECT * FROM TICKETS");
        $this->assertNotEquals($stmtTkt->execute(), false,
                "Ticket query failed");
        $row = $stmtTkt->fetch();
        $this->assertEquals($this->area->id, $row['TARIFFAREA'],
                "Inconsistent tariff area id");
        $row = $stmtTkt->fetch();
        $this->assertFalse($row, "Too much tickets found");
    }

    /** @depends testSaveEmpty */
    public function testSaveCustCount() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array(), array(),
                $this->cash->id, null, 3);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmtTkt = $pdo->prepare("SELECT * FROM TICKETS");
        $this->assertNotEquals($stmtTkt->execute(), false,
                "Ticket query failed");
        $row = $stmtTkt->fetch();
        $this->assertEquals(3, $row['CUSTCOUNT'],
                "Inconsistent customer count");
        $row = $stmtTkt->fetch();
        $this->assertFalse($row, "Too much tickets found");
    }

    public function testSaveDiscount() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array(), array(),
                $this->cash->id, null, null, null, 0.5,
                $this->discountProfile->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmtTkt = $pdo->prepare("SELECT * FROM TICKETS");
        $this->assertNotEquals($stmtTkt->execute(), false,
                "Ticket query failed");
        $row = $stmtTkt->fetch();
        $this->assertEquals(0.5, $row['DISCOUNTRATE'],
                "Inconsistent customer count");
        $this->assertEquals($this->discountProfile->id,
                $row['DISCOUNTPROFILE_ID'],
                "Discount profile id mismatch");
        $row = $stmtTkt->fetch();
        $this->assertFalse($row, "Too much tickets found");
    }

    /** @depends testSaveEmpty */
    public function testSaveLine() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 12, $this->tax);
        $payment = new Payment("cash", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id, null, 3);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        // Check sale lines
        $stmtLines = $pdo->prepare("SELECT * FROM TICKETLINES");
        $this->assertNotEquals($stmtLines->execute(), false,
                "Ticket query failed");
        $row = $stmtLines->fetch();
        $this->assertNotEquals(false, $row, "No ticket line found");
        $this->checkSaleEquality($id, $line, $row);
        $row = $stmtLines->fetch();
        $this->assertFalse($row, "Too much sale lines found");
        // Check tax lines
        $stmtTax = $pdo->prepare("SELECT * FROM TAXLINES");
        $this->assertNotEquals($stmtTax->execute(), false,
                "Tax lines query failed");
        $row = $stmtTax->fetch();
        $this->assertNotEquals(false, $row, "No tax line found");
        $this->checkTaxEquality($id, $this->tax->id, 12, 1.2, $row);
        $row = $stmtTax->fetch();
        $this->assertFalse($row, "Too much tax lines found");
        // Check payment lines
        $stmtPmt = $pdo->prepare("SELECT * FROM PAYMENTS");
        $this->assertNotEquals($stmtPmt->execute(), false,
                "Payment lines query failed");
        $row = $stmtPmt->fetch();
        $this->assertNotEquals(false, $row, "No payment line found");
        $this->checkPaymentEquality($id, $payment, $row);
        $row = $stmtPmt->fetch();
        $this->assertFalse($row, "Too much payment lines found");
        // Check stock
        $level = StocksService::getLevel($this->prd->id, $this->location->id,
                null);
        $this->assertEquals(-1, $level->qty);
    }

    /** @depends testSaveLine */
    public function testSaveBackPayment() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 10, $this->tax);
        $payment = new Payment("cash", 12, $this->currency->id, 14,
                "cashback", -2);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id, null, 3);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        // Check payment lines
        $stmtPmt = $pdo->prepare("SELECT * FROM PAYMENTS ORDER BY TOTAL DESC");
        $this->assertNotEquals($stmtPmt->execute(), false,
                "Payment lines query failed");
        $row = $stmtPmt->fetch();
        $this->assertNotEquals(false, $row, "No payment line found");
        $this->checkPaymentEquality($id, $payment, $row);
        $row = $stmtPmt->fetch();
        $refPmt = new Payment("cashback", -2, $this->currency->id, -2);
        $this->checkPaymentEquality($id, $refPmt, $row);
        $row = $stmtPmt->fetch();
        $this->assertFalse($row, "Too much payment lines found");
        // Check stock
        $level = StocksService::getLevel($this->prd->id, $this->location->id,
                null);
        $this->assertEquals(-1, $level->qty);
    }

    /** @depends testSaveLine */
    public function testSaveTktDiscountLine() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 12, $this->tax);
        $payment = new Payment("cash", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id, null, 3, null, 0.5,
                $this->discountProfile->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        // Check sale lines
        $stmtLines = $pdo->prepare("SELECT * FROM TICKETLINES");
        $this->assertNotEquals($stmtLines->execute(), false,
                "Ticket query failed");
        $row = $stmtLines->fetch();
        $this->assertNotEquals(false, $row, "No ticket line found");
        $this->checkSaleEquality($id, $line, $row);
        $row = $stmtLines->fetch();
        $this->assertFalse($row, "Too much sale lines found");
        // Check tax lines
        $stmtTax = $pdo->prepare("SELECT * FROM TAXLINES");
        $this->assertNotEquals($stmtTax->execute(), false,
                "Tax lines query failed");
        $row = $stmtTax->fetch();
        $this->assertNotEquals(false, $row, "No tax line found");
        $this->checkTaxEquality($id, $this->tax->id, 6, 0.6, $row);
        $row = $stmtTax->fetch();
        $this->assertFalse($row, "Too much tax lines found");
        // Check payment lines
        $stmtPmt = $pdo->prepare("SELECT * FROM PAYMENTS");
        $this->assertNotEquals($stmtPmt->execute(), false,
                "Payment lines query failed");
        $row = $stmtPmt->fetch();
        $this->assertNotEquals(false, $row, "No payment line found");
        $this->checkPaymentEquality($id, $payment, $row);
        $row = $stmtPmt->fetch();
        $this->assertFalse($row, "Too much payment lines found");
        // Check stock
        $level = StocksService::getLevel($this->prd->id, $this->location->id,
                null);
        $this->assertEquals(-1, $level->qty);
    }

    /** @depends testSaveLine */
    public function testSaveLineDiscountLine() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 12, $this->tax, 0.5);
        $payment = new Payment("cash", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        // Check sale lines
        $stmtLines = $pdo->prepare("SELECT * FROM TICKETLINES");
        $this->assertNotEquals($stmtLines->execute(), false,
                "Ticket query failed");
        $row = $stmtLines->fetch();
        $this->assertNotEquals(false, $row, "No ticket line found");
        $this->checkSaleEquality($id, $line, $row);
        $row = $stmtLines->fetch();
        $this->assertFalse($row, "Too much sale lines found");
        // Check tax lines
        $stmtTax = $pdo->prepare("SELECT * FROM TAXLINES");
        $this->assertNotEquals($stmtTax->execute(), false,
                "Tax lines query failed");
        $row = $stmtTax->fetch();
        $this->assertNotEquals(false, $row, "No tax line found");
        $this->checkTaxEquality($id, $this->tax->id, 6, 0.6, $row);
        $row = $stmtTax->fetch();
        $this->assertFalse($row, "Too much tax lines found");
        // Check payment lines
        $stmtPmt = $pdo->prepare("SELECT * FROM PAYMENTS");
        $this->assertNotEquals($stmtPmt->execute(), false,
                "Payment lines query failed");
        $row = $stmtPmt->fetch();
        $this->assertNotEquals(false, $row, "No payment line found");
        $this->checkPaymentEquality($id, $payment, $row);
        $row = $stmtPmt->fetch();
        $this->assertFalse($row, "Too much payment lines found");
        // Check stock
        $level = StocksService::getLevel($this->prd->id, $this->location->id,
                null);
        $this->assertEquals(-1, $level->qty);
    }

    /** @depends testSaveLine */
    public function testSaveBothDiscountLine() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 12, $this->tax, 0.25);
        $payment = new Payment("cash", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id, null, null, null, 0.25, null);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        // Check sale lines
        $stmtLines = $pdo->prepare("SELECT * FROM TICKETLINES");
        $this->assertNotEquals($stmtLines->execute(), false,
                "Ticket query failed");
        $row = $stmtLines->fetch();
        $this->assertNotEquals(false, $row, "No ticket line found");
        $this->checkSaleEquality($id, $line, $row);
        $row = $stmtLines->fetch();
        $this->assertFalse($row, "Too much sale lines found");
        // Check tax lines
        $stmtTax = $pdo->prepare("SELECT * FROM TAXLINES");
        $this->assertNotEquals($stmtTax->execute(), false,
                "Tax lines query failed");
        $row = $stmtTax->fetch();
        $this->assertNotEquals(false, $row, "No tax line found");
        $this->checkTaxEquality($id, $this->tax->id, 6, 0.6, $row);
        $row = $stmtTax->fetch();
        $this->assertFalse($row, "Too much tax lines found");
        // Check payment lines
        $stmtPmt = $pdo->prepare("SELECT * FROM PAYMENTS");
        $this->assertNotEquals($stmtPmt->execute(), false,
                "Payment lines query failed");
        $row = $stmtPmt->fetch();
        $this->assertNotEquals(false, $row, "No payment line found");
        $this->checkPaymentEquality($id, $payment, $row);
        $row = $stmtPmt->fetch();
        $this->assertFalse($row, "Too much payment lines found");
        // Check stock
        $level = StocksService::getLevel($this->prd->id, $this->location->id,
                null);
        $this->assertEquals(-1, $level->qty);
    }

    public function testSaveRefill() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prdRefill, null, 1, 10, $this->tax);
        $payment = new Payment("cash", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id,  $this->customer->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Creation failed");
        $custSrv = new CustomersService();
        $cust = $custSrv->get($this->customer->id);
        $this->assertEquals(60, $cust->prepaid, "Prepaid amount mismatch");
    }

    /** @depends testSaveRefill */
    public function testSaveDiscountRefill() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prdRefill, null, 1, 10, $this->tax,
                0.5);
        $payment = new Payment("cash", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id,  $this->customer->id, null, null, 0.2, null);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Creation failed");
        $custSrv = new CustomersService();
        $cust = $custSrv->get($this->customer->id);
        $this->assertEquals(60, $cust->prepaid, "Prepaid amount mismatch");
    }

    function testSavePrepaid() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 10, $this->tax);
        $payment = new Payment("prepaid", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id,  $this->customer->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Creation failed");
        $custSrv = new CustomersService();
        $cust = $custSrv->get($this->customer->id);
        $this->assertEquals(38, $cust->prepaid, "Prepaid amount mismatch");
    }

    function testSaveDebt() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 10, $this->tax);
        $payment = new Payment("debt", 3, $this->currency->id, 3);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id,  $this->customer->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Creation failed");
        $custSrv = new CustomersService();
        $cust = $custSrv->get($this->customer->id);
        $this->assertEquals(8, $cust->currDebt, "Debt amount mismatch");
    }

    /** @depends testSaveLine
     * @depends testCreateAttrSetInst
     */
    function testSaveLineAttribute() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $attrSetInst = new AttributeSetInstance($this->attrSet->id, "Value");
        $attrsId = TicketsService::createAttrSetInst($attrSetInst);
        $line = new TicketLine(1, $this->prd, $attrSetInst->id , 1, 12, $this->tax);
        $payment = new Payment("cash", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id, null, 3);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertNotEquals(false, $id, "Ticket save failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        // Check line
        $stmtLines = $pdo->prepare("SELECT * FROM TICKETLINES");
        $this->assertNotEquals($stmtLines->execute(), false,
                "Ticket query failed");
        $row = $stmtLines->fetch();
        $this->assertNotEquals(false, $row, "No ticket line found");
        $this->checkSaleEquality($id, $line, $row);
        $row = $stmtLines->fetch();
        $this->assertFalse($row, "Too much sale lines found");
        // Check stock
        $this->markTestIncomplete("Check stock with attribute");
    }

    /** @depends testSaveLine */
    function testSaveMultiLines() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $attrSetInst = new AttributeSetInstance($this->attrSet->id, "Value");
        $attrsId = TicketsService::createAttrSetInst($attrSetInst);
        $line1 = new TicketLine(1, $this->prd, $attrSetInst->id , 1, 12,
                $this->tax);
        $line2 = new TicketLine(2, $this->prd2, null, 2, 10, $this->tax2);
        $line3 = new TicketLine(3, $this->prd, null, 1.5, 10, $this->tax);
        $payment1 = new Payment("cash", 12, $this->currency->id, 14);
        $payment2 = new Payment("cheque", 25, $this->currency->id, 20);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line1, $line2, $line3),
                array($payment1, $payment2), $this->cash->id, null, null);
        $id = TicketsService::save($ticket, $this->location->id);
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        // Check sale lines
        $stmtLines = $pdo->prepare("SELECT * FROM TICKETLINES");
        $this->assertNotEquals($stmtLines->execute(), false,
                "Ticket query failed");
        $toCheck = array($line1, $line2, $line3);
        $count = 0;
        while ($row = $stmtLines->fetch()) {
            $ref = null;
            $count++;
            if ($row['LINE'] == 1) {
                $ref = $line1;
            } else if ($row['LINE'] == 2) {
                $ref = $line2;
            } else if ($row['LINE'] == 3) {
                $ref = $line3;
            }
            $this->assertNotNull($ref, "Unknown line");
            $this->checkSaleEquality($id, $ref, $row);
            for ($i = 0; $i < count($toCheck); $i++) {
                $l = $toCheck[$i];
                if ($l->dispOrder == $ref->dispOrder) {
                    array_splice($toCheck, $i, 1);
                    break;
                }
            }
        }
        $this->assertEquals(3, $count, "Sale line count mismatch");
        $this->assertEquals(0, count($toCheck), "Duplicated sale lines");
        // Check tax lines
        $stmtTax = $pdo->prepare("SELECT * FROM TAXLINES");
        $this->assertNotEquals($stmtTax->execute(), false,
                "Tax lines query failed");
        $toCheck = array($this->tax->id, $this->tax2->id);
        for ($i = 0; $i < 2; $i++) {
            $row = $stmtTax->fetch();
            $this->assertNotEquals(false, $row, "Not enough tax line found");
            $ref = null;
            if ($row['TAXID'] == $this->tax->id) {
                $ref = $this->tax->id;
                $this->checkTaxEquality($id, $ref, 27, 2.7, $row);
            } else if ($row['TAXID'] == $this->tax2->id) {
                $ref = $this->tax2->id;
                $this->checkTaxEquality($id, $ref, 20, 4, $row);
            }
            $this->assertNotNull($ref, "Unknown tax line");
            foreach ($toCheck as $j => $taxId) {
                if ($ref == $taxId) {
                    array_splice($toCheck, $j, 1);
                    break;
                }
            }
        }
        $this->assertEquals(0, count($toCheck), "Duplicated tax lines");
        $row = $stmtTax->fetch();
        $this->assertFalse($row, "Too much tax lines found");
        // Check payment lines
        $toCheck = array($payment1, $payment2);
        $stmtPmt = $pdo->prepare("SELECT * FROM PAYMENTS");
        $this->assertNotEquals($stmtPmt->execute(), false,
                "Payment lines query failed");
        $count = 0;
        while ($row = $stmtPmt->fetch()) {
            $ref = null;
            $count++;
            if ($row['PAYMENT'] == "cash") {
                $ref = $payment1;
            } else if ($row['PAYMENT'] == "cheque") {
                $ref = $payment2;
            }
            $this->assertNotNull($ref, "Unknown line");
            $this->checkPaymentEquality($id, $ref, $row);
            foreach ($toCheck as $i => $pmt) {
                if ($pmt->type == $ref->type) {
                    array_splice($toCheck, $i, 1);
                    break;
                }
            }
        }
        $this->assertEquals(2, $count, "Payment line count mismatch");
        $this->assertEquals(0, count($toCheck), "Duplicated payment lines");
        // Check stocks
        $level = StocksService::getLevel($this->prd->id, $this->location->id,
                null);
        $this->assertEquals(-1.5, $level->qty);
        $level = StocksService::getLevel($this->prd2->id, $this->location->id,
                null);
        $this->assertEquals(-2, $level->qty);
        $this->markTestIncomplete("Check stock level with attribute");
    }

    // Read ticket tests
    ////////////////////

    /** @depends testSaveLine */
    public function testGet() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 12, $this->tax);
        $payment = new Payment("cash", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id, null, 3);
        $ticket->id = TicketsService::save($ticket, $this->location->id);
        $read = TicketsService::get($ticket->id);
        $this->assertNotNull($read, "Nothing found");
        $this->checkTicketEquality($ticket, $read);
    }

    /** @depends testGet */
    public function testGetOpen() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 12, $this->tax);
        $payment = new Payment("cash", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id, null, 3);
        $ticket->id = TicketsService::save($ticket, $this->location->id);
        $tickets = TicketsService::getOpen();
        $this->assertNotEquals(false, $tickets, "Get open failed");
        $this->assertTrue(is_array($tickets), "Tickets is not an array");
        $this->assertEquals(1, count($tickets), "Tickets size mismatch");
        $toCheck = array($ticket);
        $count = 0;
        foreach ($tickets as $tkt) {
            $ref = null;
            $count++;
            if ($tkt->id == $ticket->id) {
                $ref = $ticket;
            }
            $this->assertNotNull($ref, "Unknown ticket");
            $this->checkTicketEquality($ref, $tkt);
            foreach ($toCheck as $i => $t) {
                if ($t->id == $ref->id) {
                    array_splice($toCheck, $i, 1);
                    break;
                }
            }
        }
        $this->assertEquals(0, count($toCheck), "Duplicated tickets");
    }

    // Delete ticket tests
    //////////////////////

    /** @depends testGet */
    public function testDelete() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array(), array(),
                $this->cash->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertTrue(TicketsService::delete($id), "Delete failed");
        $this->assertNull(TicketsService::get($id),
                "Ticket is still there after deletion");
    }

    /** @depends testGet
     * @depends testSavePrepaid
     */
    public function testDeletePrepaid() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 10, $this->tax);
        $payment = new Payment("prepaid", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id,  $this->customer->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertTrue(TicketsService::delete($id), "Delete failed");
        $this->assertNull(TicketsService::get($id),
                "Ticket is still there after deletion");
        $custSrv = new CustomersService();
        $cust = $custSrv->get($this->customer->id);
        $this->assertEquals($this->customer->prepaid, $cust->prepaid,
                "Prepaid payment is not cleared after ticket is deleted");
    }

    /** @depends testGet
     * @depends testSaveRefill
     */
    public function testDeleteRefill() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prdRefill, null, 1, 10, $this->tax);
        $payment = new Payment("cash", 12, $this->currency->id, 14);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id,  $this->customer->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertTrue(TicketsService::delete($id), "Delete failed");
        $this->assertNull(TicketsService::get($id),
                "Ticket is still there after deletion");
        $custSrv = new CustomersService();
        $cust = $custSrv->get($this->customer->id);
        $this->assertEquals($this->customer->prepaid, $cust->prepaid,
                "Prepaid refill is not cleared after ticket is deleted");
    }

    /** @depends testGet */
    public function testDeleteClosed() {
        $date = stdtimefstr("2013-01-01 00:00:00");
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array(), array(),
                $this->cash->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->cash->closeDate = stdtimefstr("2013-01-01 12:00:00");
        $cashSrv = new CashesService();
        $cashSrv->update($this->cash);
        $this->assertFalse(TicketsService::delete($id),
                "Closed ticket should not be deleted");
        $this->assertNotNull(TicketsService::get($id),
                "Ticket is not there anymore");
    }

    /** @depends testGet
     * @depends testSaveDebt
     */
    public function testDeleteDebt() {
        $currDebt = $this->customer->currDebt;
        $date = stdtimefstr("2013-01-01 00:00:00");
        $line = new TicketLine(1, $this->prd, null, 1, 10, $this->tax);
        $payment = new Payment("debt", 3, $this->currency->id, 3);
        $ticket = new Ticket(Ticket::TYPE_SELL, $this->user->id,
                $date, array($line), array($payment),
                $this->cash->id,  $this->customer->id);
        $id = TicketsService::save($ticket, $this->location->id);
        $this->assertTrue(TicketsService::delete($id), "Delete failed");
        $custSrv = new CustomersService();
        $cust = $custSrv->get($this->customer->id);
        $this->assertEquals($currDebt, $cust->currDebt,
                "Debt not cleared after ticket is deleted");
    }

    // Shared ticket tests
    //////////////////////

    private function checkSharedTktEquality($ref, $read) {
        $this->assertEquals($ref->id, $read->id, "Id mismatch");
        $this->assertEquals($ref->label, $read->label, "Label mismatch");
        $this->assertEquals($ref->customerId, $read->customerId,
                "Customer id mismatch");
        $this->assertEquals($ref->tariffAreaId, $read->tariffAreaId,
                "Tariff area id mismatch");
        $this->assertEquals($ref->discountProfileId, $read->discountProfileId,
                "Discount profile id mismatch");
        $this->assertEquals($ref->discountRate, $read->discountRate,
                "Discount rate id mismatch");
    }

    public function testCreateSharedTicket() {
        $tkt = SharedTicket::__build("1", "Label", $this->customer->id,
                $this->area->id, $this->discountProfile->id, 13.37);
        $this->assertTrue(TicketsService::createSharedTicket($tkt, array()),
                "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM SHAREDTICKETS");
        $this->assertNotEquals(false, $stmt->execute());
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "Nothing found");
        $this->assertEquals($tkt->id, $row['ID'], "Id mismatch");
        $this->assertEquals($tkt->label, $row['NAME'], "Label mismatch");
        $this->assertEquals($tkt->customerId, $row['CUSTOMER_ID'],
                "Customer id mismatch");
        $this->assertEquals($tkt->tariffAreaId, $row['TARIFFAREA_ID'],
                "Tariff area id mismatch");
        $this->assertEquals($tkt->discountProfileId,
                $row['DISCOUNTPROFILE_ID'], "Discount profile id mismatch");
        $this->assertEquals($tkt->discountRate, $row['DISCOUNTRATE'],
                "Discount rate mismatch");
    }

    private function createSharedTicketLines($sharedTicketId) {
        $ret = array();
        $ret[] = SharedTicketLines::__build(1, $sharedTicketId, 0,
                $this->prd->id, 1, 0.1, 10.34, null);
        $ret[] = SharedTicketLines::__build(2, $sharedTicketId, 0,
                $this->prd2->id, 1, 0.1, 10.34, null);
        return $ret;
    }

    /** @depends testCreateSharedTicket */
    public function testCreateSharedTicketWithLines() {
        $tkt = SharedTicket::__build("1", "Label", $this->customer->id,
                $this->area->id, $this->discountProfile->id, 13.37);
        $lines = $this->createSharedTicketLines($tkt->id);
        $this->assertTrue(TicketsService::createSharedTicket($tkt, $lines),
                "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM SHAREDTICKETS");
        $this->assertNotEquals(false, $stmt->execute());
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "Nothing found");
        $this->assertEquals($tkt->id, $row['ID'], "Id mismatch");
        $this->assertEquals($tkt->label, $row['NAME'], "Label mismatch");
        $this->assertEquals($tkt->customerId, $row['CUSTOMER_ID'],
                "Customer id mismatch");
        $this->assertEquals($tkt->tariffAreaId, $row['TARIFFAREA_ID'],
                "Tariff area id mismatch");
        $this->assertEquals($tkt->discountProfileId,
                $row['DISCOUNTPROFILE_ID'], "Discount profile id mismatch");
        $this->assertEquals($tkt->discountRate, $row['DISCOUNTRATE'],
                "Discount rate mismatch");
    }

    /** @depends testCreateSharedTicket */
    public function testGetSharedTicket() {
        $tkt = SharedTicket::__build("2", "Label2", $this->customer->id,
                $this->area->id, $this->discountProfile->id, 13.37);
        $this->assertTrue(TicketsService::createSharedTicket($tkt, array()),
                "Creation failed");
        $read = TicketsService::getSharedTicket($tkt->id);
        $this->assertNotNull($read, "Nothing found");
        $this->checkSharedTktEquality($tkt, $read);
    }

    public function testGetInexistentSharedTicket() {
        $this->assertNull(TicketsService::getSharedTicket("junk"),
                "Junk id returned something");
    }

    /** @depends testCreateSharedTicket */
    public function testGetAllSharedTickets() {
        $tkt = SharedTicket::__build("1", "Label", $this->customer->id,
                $this->area->id, $this->discountProfile->id, 13.37);
        TicketsService::createSharedTicket($tkt, array());
        $tkt2 = SharedTicket::__build("2", "Label2", $this->customer->id,
                $this->area->id, $this->discountProfile->id, 13.37);
        TicketsService::createSharedTicket($tkt2, array());
        $read = TicketsService::getAllSharedTickets($tkt->id);
        $this->assertNotNull($read, "Nothing found");
        $this->assertTrue(is_array($read), "Content is not an array");
        $this->assertEquals(2, count($read), "Content size mismatch");
        $toCheck = array($tkt, $tkt2);
        $count = 0;
        foreach ($read as $rtkt) {
            $ref = null;
            $count++;
            if ($rtkt->id == $tkt->id) {
                $ref = $tkt;
            } else if ($rtkt->id == $tkt2->id) {
                $ref = $tkt2;
            }
            $this->assertNotNull($ref, "Unknown line");
            $this->checkSharedTktEquality($ref, $rtkt);
            for ($i = 0; $i < count($toCheck); $i++) {
                $t = $toCheck[$i];
                if ($t->id == $ref->id) {
                    array_splice($toCheck, $i, 1);
                    break;
                }
            }
        }
        $this->assertEquals(2, $count, "Shared tickets count mismatch");
        $this->assertEquals(0, count($toCheck), "Duplicated shared tickets");
    }

    /** @depends testCreateSharedTicket
     * @depends testGetInexistentSharedTicket
     */
    public function testDeleteSharedTicket() {
        $tkt = SharedTicket::__build("1", "Label", $this->customer->id,
                $this->area->id, $this->discountProfile->id, 13.37);
        TicketsService::createSharedTicket($tkt, array());
        $this->assertTrue(TicketsService::deleteSharedTicket($tkt->id),
                "Delete failed");
        $this->assertNull(TicketsService::getSharedTicket($tkt->id),
                "Shared ticket is still there");
    }

    /** @depends testCreateSharedTicket
     * @depends testGetSharedTicket
     */
    public function testUpdateSharedTicket() {
        $tkt = SharedTicket::__build("1", "Label", $this->customer->id,
                $this->area->id, $this->discountProfile->id, 13.37);
        TicketsService::createSharedTicket($tkt, array());
        $tkt->label = "Edited";
        $tkt->data = 0x98bca8;
        $this->assertTrue(TicketsService::updateSharedTicket($tkt, array()),
                "Update failed");
        $read = TicketsService::getSharedTicket($tkt->id);
        $this->checkSharedTktEquality($tkt, $read);
    }

}