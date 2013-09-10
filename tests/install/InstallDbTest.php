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

class InstallDbTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {}

    protected function tearDown() {
        // Erase database
        dropDatabase();
    }

    public function testInstallFrance() {
        Installer::install("france");
        $pdo = PDOBuilder::getPDO();
        $this->assertEquals(Installer::getVersion(), DB_VERSION,
                "Version doesn't match");
        // Check data insert
        $sql = "SELECT * FROM PLACES WHERE ID = 10";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertEquals($row['NAME'], "Table 10",
                "Country data failed to be inserted");
    }

    public function testInstallBelgique() {
        Installer::install("belgique");
        $pdo = PDOBuilder::getPDO();
        $this->assertEquals(Installer::getVersion(), DB_VERSION,
                "Version doesn't match");
        // Check data insert
        $sql = "SELECT * FROM PLACES WHERE ID = 10";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertEquals($row['NAME'], "Table 10",
                "Country data failed to be inserted");
    }

    public function testInstallUnitedKingdom() {
        Installer::install("united_kingdom");
        $pdo = PDOBuilder::getPDO();
        $this->assertEquals(Installer::getVersion(), DB_VERSION,
                "Version doesn't match");
        // Check data insert
        $sql = "SELECT * FROM PLACES WHERE ID = 10";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $this->assertEquals($row['NAME'], "Table 10",
                "Country data failed to be inserted");
    }

}
?>
