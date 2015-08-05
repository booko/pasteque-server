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

class CashesServiceTest extends \PHPUnit_Framework_TestCase {

    private $cashRegisterId;
    private $cashRegisterId2;

    protected function setUp() {
        $srv = new LocationsService();
        $location = new Location("Location");
        $location->id = $srv->create($location);
        $srv = new CashRegistersService();
        $cashReg = new CashRegister("CashReg", $location->id, 1);
        $this->cashRegisterId = $srv->create($cashReg);
        $cashReg2 = new CashRegister("CashReg2", $location->id, 1);
        $this->cashRegisterId2 = $srv->create($cashReg2);
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM CLOSEDCASH") === false
                || $pdo->exec("DELETE FROM CASHREGISTERS") === false
                || $pdo->exec("DELETE FROM LOCATIONS") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public function testAdd() {
        $srv = new CashesService();
        $cash = $srv->add($this->cashRegisterId);
        $this->assertNotNull($cash, "Created cash is null");
        $this->assertNotNull($cash->id, "Id not set");
        $this->assertEquals($this->cashRegisterId, $cash->cashRegisterId,
                "Cash register id mismatch");
        $this->assertEquals(1, $cash->sequence, "Sequence doesn't start at 1");
        $this->assertNull($cash->openDate, "Created cash is opened");
        $this->assertNull($cash->closeDate, "Created cash is closed");
        $this->assertNull($cash->openCash, "Open cash mismatch");
        $this->assertNull($cash->closeCash, "Close cash mismatch");
        $this->assertNull($cash->expectedCash, "Expected cash mismatch");
        $cash2 = $srv->add($this->cashRegisterId);
        $this->assertNotNull($cash2, "Created cash is null");
        $this->assertNotNull($cash2->id, "Id not set");
        $this->assertEquals($this->cashRegisterId, $cash2->cashRegisterId,
                "Cash register id mismatch");
        $this->assertEquals(2, $cash2->sequence, "Sequence increment failed");
        $this->assertNull($cash2->openDate, "Created cash is opened");
        $this->assertNull($cash2->closeDate, "Created cash is closed");
        $this->assertNull($cash2->openCash, "Open cash mismatch");
        $this->assertNull($cash2->closeCash, "Close cash mismatch");
        $this->assertNull($cash2->expectedCash, "Expected cash mismatch");
        $cash3 = $srv->add($this->cashRegisterId2);
        $this->assertNotNull($cash3, "Created cash is null");
        $this->assertNotNull($cash3->id, "Id not set");
        $this->assertEquals($this->cashRegisterId2, $cash3->cashRegisterId,
                "Cash register id 2 mismatch");
        $this->assertEquals(1, $cash3->sequence, "Bad sequence");
        $this->assertNull($cash3->openDate, "Created cash is opened");
        $this->assertNull($cash3->closeDate, "Created cash is closed");
        $this->assertNull($cash3->openCash, "Open cash mismatch");
        $this->assertNull($cash3->closeCash, "Close cash mismatch");
        $this->assertNull($cash3->expectedCash, "Expected cash mismatch");
    }

    public function testGetHostEmpty() {
        $srv = new CashesService();
        $cash = $srv->getCashRegister(1293819);
        $this->assertNull($cash, "Cash shouldn't exist");
    }

    /** @depends testAdd */
    public function testGetHostCreated() {
        $srv = new CashesService();
        $cash = $srv->add($this->cashRegisterId);
        $read = $srv->getCashRegister($this->cashRegisterId);
        $this->assertNotNull($read, "Created cash not found");
        $this->assertEquals($cash->cashRegisterId, $read->cashRegisterId,
                "Cash register id was modified");
        $this->assertEquals($cash->sequence, $read->sequence,
                "Sequence was modified");
        $this->assertEquals($cash->openDate, $read->openDate,
                "Open date was modified");
        $this->assertEquals($cash->closeDate, $read->closeDate,
                "Close date was modified");
        $this->assertEquals($cash->openCash, $read->openCash,
                "Open cash was modified");
        $this->assertEquals($cash->closeCash, $read->closeCash,
                "Close cash was modified");
        $this->assertEquals($cash->expectedCash, $read->expectedCash,
                "Expected cash was modified");
    }

    public function testGetEmpty() {
        $srv = new CashesService();
        $cash = $srv->get(null);
        $this->assertNull($cash, "There shouldn't be anything with null id");
        $cash = $srv->get("junk");
        $this->assertNull($cash, "Junk id returned something");
    }

    /** @depends testAdd */
    public function testGet() {
        $srv = new CashesService();
        $cash = $srv->add($this->cashRegisterId);
        $read = $srv->get($cash->id);
        $this->assertNotNull($read, "Created cash not found");
        $this->assertEquals($cash->cashRegisterId, $read->cashRegisterId,
                "Cash register id was modified");
        $this->assertEquals($cash->sequence, $read->sequence,
                "Sequence was modified");
        $this->assertEquals($cash->openDate, $read->openDate,
                "Open date was modified");
        $this->assertEquals($cash->closeDate, $read->closeDate,
                "Close date was modified");
        $this->assertEquals($cash->openCash, $read->openCash,
                "Open cash was modified");
        $this->assertEquals($cash->closeCash, $read->closeCash,
                "Close cash was modified");
        $this->assertEquals($cash->expectedCash, $read->expectedCash,
                "Expected cash was modified");
    }

    /** @depends testAdd
     * @depends testGet
     */
    public function testUpdate() {
        $srv = new CashesService();
        $cash = $srv->add($this->cashRegisterId);
        // Edit open date
        $cash->openDate = stdtimefstr("2000-02-02 02:02:02");
        $cash->openCash = 10.0;
        $srv->update($cash);
        $read = $srv->get($cash->id);
        $this->assertNotNull($read, "Created cash not found");
        $this->assertEquals($cash->id, $read->id, "Id was modified");
        $this->assertEquals($cash->cashRegisterId, $read->cashRegisterId,
                "Cash register id was modified");
        $this->assertEquals($cash->sequence, $read->sequence,
                "Sequence was modified");
        $this->assertEquals($cash->openDate, $read->openDate,
                "Open date Mismatch");
        $this->assertEquals($cash->closeDate, $read->closeDate,
                "Close date was modified");
        $this->assertEquals($cash->openCash, $read->openCash,
                "Open cash mismatch");
        $this->assertEquals($cash->closeCash, $read->closeCash,
                "Close cash was modified");
        $this->assertEquals($cash->expectedCash, $read->expectedCash,
                "Expected cash was modified");
        // Edit close date
        $cash->closeDate = stdtimefstr("2000-02-03 02:02:02");
        $cash->closeCash = 12.0;
        $cash->expectedCash = 25.0;
        $srv->update($cash);
        $read = $srv->get($cash->id);
        $this->assertNotNull($read, "Created cash not found");
        $this->assertEquals($cash->id, $read->id, "Id was modified");
        $this->assertEquals($cash->cashRegisterId, $read->cashRegisterId,
                "Cash register id was modified");
        $this->assertEquals($cash->sequence, $read->sequence,
                "Sequence was modified");
        $this->assertEquals($cash->openDate, $read->openDate,
                "Open date was modified");
        $this->assertEquals($cash->closeDate, $read->closeDate,
                "Close date was modified");
        $this->assertEquals($cash->openCash, $read->openCash,
                "Open cash was modified");
        $this->assertEquals($cash->closeCash, $read->closeCash,
                "Close cash mismatch");
        $this->assertEquals($cash->expectedCash, $read->expectedCash,
                "Expected cash was modified");
        // Edit open and close date
        $cash->openDate = stdtimefstr("2001-02-02 03:03:03");
        $cash->closeDate = stdtimefstr("2001-02-03 03:03:03");
        $cash->openCash = 9.0;
        $cash->closeCash = 9.1;
        $cash->expectedCash = 9.2;
        $srv->update($cash);
        $read = $srv->get($cash->id);
        $this->assertNotNull($read, "Created cash not found");
        $this->assertEquals($cash->id, $read->id, "Id was modified");
        $this->assertEquals($cash->cashRegisterId, $read->cashRegisterId,
                "Cash register id was modified");
        $this->assertEquals($cash->sequence, $read->sequence,
                "Sequence was modified");
        $this->assertEquals($cash->openDate, $read->openDate,
                "Open date mismatch");
        $this->assertEquals($cash->closeDate, $read->closeDate,
                "Close date mismatch");
        $this->assertEquals($cash->openCash, $read->openCash,
                "Open cash mismatch");
        $this->assertEquals($cash->closeCash, $read->closeCash,
                "Close cash mismatch");
        $this->assertEquals($cash->expectedCash, $read->expectedCash,
                "Expected cash was modified");
    }

}