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

class CashMovementsServiceTest extends \PHPUnit_Framework_TestCase {

    private $cashId;
    private $currencyId;

    protected function setUp() {
        $locSrv = new LocationsService();
        $location = new Location("Location");
        $location->id = $locSrv->create($location);
        $cashRegSrv = new CashRegistersService();
        $cashReg = new CashRegister("CashReg", $location->id, 1);
        $cashRegId = $cashRegSrv->create($cashReg);
        // Create a cash session
        $srv = new CashesService();
        $cash = $srv->add($cashRegId);
        $this->cashId = $cash->id;
        $srv = new CurrenciesService();
        $eur = new Currency("Eur", "€", ",", ".", "#,##0.00$", 1, true, false);
        $this->currencyId = $srv->create($eur);
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM PAYMENTS") === false
                || $pdo->exec("DELETE FROM RECEIPTS") === false
                || $pdo->exec("DELETE FROM CLOSEDCASH") === false
                || $pdo->exec("DELETE FROM CASHREGISTERS") === false
                || $pdo->exec("DELETE FROM LOCATIONS") === false
                || $pdo->exec("DELETE FROM CURRENCIES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testCreate() {
        $db = DB::get();
        $srv = new CashMovementsService();
        $mvt = new CashMovement($this->cashId, $db->readDate("2014-01-03 00:00:00"),
                CashMovement::TYPE_CASHIN, 10.0, $this->currencyId, 12, "note");
        $id = $srv->create($mvt);
        $this->assertNotEquals(false, $id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM RECEIPTS WHERE ID = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "No receipt found");
        $this->assertEquals($mvt->date, $db->readDate($row['DATENEW']),
                "Date mismatch");
        $this->assertEquals($mvt->cashId, $row['MONEY'],
                "Cash session id mismatch");
        $stmtP = $pdo->prepare("SELECT * FROM PAYMENTS WHERE ID = :id");
        $stmtP->bindParam(":id", $id);
        $stmtP->execute();
        $row = $stmtP->fetch();
        $this->assertNotEquals(false, $row, "No payment found");
        $this->assertEquals($id, $row['RECEIPT'], "Receipt id mismatch");
        $this->assertEquals($mvt->type, $row['PAYMENT'],
                "Payment type mismatch");
        $this->assertEquals($mvt->amount, $row['TOTAL'], "Amount mismatch");
        $this->assertEquals($mvt->currencyId, $row['CURRENCY'],
                "Currency id mismatch");
        $this->assertEquals($mvt->currencyAmount, $row['TOTALCURRENCY'],
                "Currency amount mismatch");
        $this->assertEquals($mvt->note, $row['NOTE'], "Note mismatch");
    }

    public function testGet() {
        $this->markTestIncomplete();
    }

    public function testGetAll() {
        $this->markTestIncomplete();
    }
}