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

class CurrenciesServiceTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        // Install empty database
        Installer::install(null);
    }

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM CURRENCIES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    public static function tearDownAfterClass() {
        // Erase database
        dropDatabase();
    }

    private function checkEquality($ref, $read) {
        $this->assertEquals($ref->id, $read->id, "Id mismatch");
        $this->assertEquals($ref->label, $read->label, "Label mismatch");
        $this->assertEquals($ref->symbol, $read->symbol, "Symbol mismatch");
        $this->assertEquals($ref->decimalSeparator, $read->decimalSeparator,
                "Decimal separator mismatch");
        $this->assertEquals($ref->thousandsSeparator,
                $read->thousandsSeparator, "Thousands separator mismatch");
        $this->assertEquals($ref->format, $read->format, "Format mismatch");
        $this->assertEquals($ref->rate, $read->rate, "Rate mismatch");
        $this->assertEquals($ref->isMain, $read->isMain, "Main mismatch");
        $this->assertEquals($ref->isActive, $read->isActive, "Active mismatch");
    }

    public function testCreate() {
        $eur = new Currency("Eur", "€", ",", ".", "#,##0.00$", 1, true, false);
        $srv = new CurrenciesService();
        $eur->id = $srv->create($eur);
        $this->assertNotEquals(false, $eur->id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = "SELECT * FROM CURRENCIES";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertNotEquals(false, $row, "Nothing found");
        $this->assertEquals($eur->id, $row['ID'], "Id mismatch");
        $this->assertEquals($eur->label, $row['NAME'], "Label mismatch");
        $this->assertEquals($eur->symbol, $row['SYMBOL'], "Symbol mismatch");
        $this->assertEquals($eur->decimalSeparator, $row['DECIMALSEP'],
                "Decimal separator mismatch");
        $this->assertEquals($eur->thousandsSeparator,
                $row['THOUSANDSSEP'], "Thousands separator mismatch");
        $this->assertEquals($eur->format, $row['FORMAT'], "Format mismatch");
        $this->assertEquals($eur->rate, $row['RATE'], "Rate mismatch");
        $this->assertEquals($eur->isMain, $db->readBool($row['MAIN']),
                "Main mismatch");
        $this->assertEquals($eur->isActive, $db->readBool($row['ACTIVE']),
                "Active mismatch");
    }

    /** @depends testCreate */
    public function testRead() {
        $eur = new Currency("Eur", "€", ",", ".", "#,##0.00$", 1, true, true);
        $dol = new Currency("Doll", "$", ".", ",", "$0#,##0.00", 1.25, false,
                true);
        $yen = new Currency("Yen", "Y", " ", " ", "#$", 120, false, false);
        $srv = new CurrenciesService();
        $eur->id = $srv->create($eur);
        $dol->id = $srv->create($dol);
        $yen->id = $srv->create($yen);
        $readEur = $srv->get($eur->id);
        $readDol = $srv->get($dol->id);
        $readYen = $srv->get($yen->id);
        $this->assertNotNull($readEur, "Eur not found");
        $this->assertNotNull($readDol, "Dol not found");
        $this->assertNotNull($readYen, "Yen not found");
        $this->checkEquality($eur, $readEur);
        $this->checkEquality($dol, $readDol);
        $this->checkEquality($yen, $readYen);
    }

    public function testReadInexistent() {
        $srv = new CurrenciesService();
        $read = $srv->get(0);
        $this->assertEquals(null, $read);
    }

    /** @depends testCreate
     * @depends testRead
     */
    public function testUpdate() {
        $srv = new CurrenciesService();
        $eur = new Currency("Eur", "€", ",", ".", "#,##0.00$", 1, true, true);
        $eur->id = $srv->create($eur);
        $eur->label = "Euro";
        $eur->symbol = "E";
        $eur->decimalSeparator = " ";
        $eur->thousandsSeparator = "_";
        $eur->format = "$#,##0";
        $eur->rate = 1.1;
        $eur->isActive = false;
        $this->assertTrue($srv->update($eur));
        $read = $srv->get($eur->id);
        $this->checkEquality($eur, $read);
    }

    /** @depends testUpdate */
    public function testUpdateMain() {
        $srv = new CurrenciesService();
        $eur = new Currency("Eur", "€", ",", ".", "#,##0.00$", 1, true, true);
        $eur->id = $srv->create($eur);
        $yen = new Currency("Yen", "Y", " ", " ", "#$", 120, false, false);
        $yen->id = $srv->create($yen);
        $yen->isMain = true;
        $this->assertTrue($srv->update($yen));
        $eurRead = $srv->get($eur->id);
        $yenRead = $srv->get($yen->id);
        $this->assertTrue($yenRead->isMain, "Main not set");
        $this->assertFalse($eurRead->isMain, "Old main not unset");
    }

    public function testUpdateInexistent() {
        $this->markTestIncomplete();
    }

    /** @depends testCreate */
    public function testDelete() {
        $this->markTestIncomplete();
    }

}