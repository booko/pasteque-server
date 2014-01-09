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

class CurrenciesAPITest extends \PHPUnit_Framework_TestCase {

    const API = "CurrenciesAPI";
    private $currencies;

    public static function setUpBeforeClass() {
        // Install empty database
        Installer::install(null);
    }

    protected function setUp() {
        $this->currencies = array();
        $srv = new CurrenciesService();
        $euro = new Currency("Euro", "€", ",", ".", "#,##0.00$", 1, true, true);
        $doll = new Currency("Dollar", "$", ".", ",", "$0#,##0.00", 1.25, false,
                true);
        $yen = new Currency("Yen", "Y", " ", " ", "#$", 120, false, false);
        $euro->id = $srv->create($euro);
        $doll->id = $srv->create($doll);
        $yen->id = $srv->create($yen);
        $this->currencies[] = $euro;
        $this->currencies[] = $doll;
        $this->currencies[] = $yen;
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

    public function testGet() {
        $curr = $this->currencies[0];
        $broker = new APIBroker(CurrenciesAPITest::API);
        $result = $broker->run("get", array("id" => $curr->id));
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($curr->id, $content->id, "Id mismatch");
        $this->assertEquals($curr->label, $content->label,
                "Name mismatch");
        $this->assertEquals($curr->symbol, $content->symbol, "Symbol mismatch");
        $this->assertEquals($curr->decimalSeparator, $content->decimalSeparator,
                "Decimal separator mismatch");
        $this->assertEquals($curr->thousandsSeparator,
                $content->thousandsSeparator, "Thousands separator mismatch");
        $this->assertEquals($curr->format, $content->format, "Format mismatch");
        $this->assertEquals($curr->rate, $content->rate, "Rate mismatch");
        $this->assertEquals($curr->isMain, $content->isMain, "Main mismatch");
        $this->assertEquals($curr->isActive, $content->isActive,
                "Active mismatch");
    }

    public function testGetMain() {
        $curr = $this->currencies[0];
        $broker = new APIBroker(CurrenciesAPITest::API);
        $result = $broker->run("getMain", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertEquals($curr->id, $content->id, "Id mismatch");
        $this->assertEquals($curr->label, $content->label,
                "Name mismatch");
        $this->assertEquals($curr->symbol, $content->symbol, "Symbol mismatch");
        $this->assertEquals($curr->decimalSeparator, $content->decimalSeparator,
                "Decimal separator mismatch");
        $this->assertEquals($curr->thousandsSeparator,
                $content->thousandsSeparator, "Thousands separator mismatch");
        $this->assertEquals($curr->format, $content->format, "Format mismatch");
        $this->assertEquals($curr->rate, $content->rate, "Rate mismatch");
        $this->assertEquals($curr->isMain, $content->isMain, "Main mismatch");
        $this->assertEquals($curr->isActive, $content->isActive,
                "Active mismatch");
    }

    public function testGetAll() {
        $broker = new APIBroker(CurrenciesAPITest::API);
        $result = $broker->run("getAll", null);
        $this->assertEquals(APIResult::STATUS_CALL_OK, $result->status,
                "Result status check failed");
        $content = $result->content;
        $this->assertNotNull($content, "Content is null");
        $this->assertTrue(is_array($content), "Content is not an array");
        $this->assertEquals(3, count($content), "Content size mismatch");
        $this->markTestIncomplete("Check content");
    }
}
?>