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

class PaymentModesServiceTest extends \PHPUnit_Framework_TestCase {

    protected function tearDown() {
        // Restore database in its empty state
        $pdo = PDOBuilder::getPDO();
        if ($pdo->exec("DELETE FROM PAYMENTMODES_RULES") === false
                || $pdo->exec("DELETE FROM PAYMENTMODES_VALUES") === false
                || $pdo->exec("DELETE FROM PAYMENTMODES") === false) {
            echo("[ERROR] Unable to restore db\n");
        }
    }

    private function checkEquality($ref, $read) {
        $this->assertEquals($ref->id, $read->id);
        $this->assertEquals($ref->code, $read->code);
        $this->assertEquals($ref->label, $read->label);
        $this->assertEquals($ref->flags, $read->flags);
        $this->assertEquals($ref->hasImage, $read->hasImage);
        $this->assertEquals($ref->active, $read->active);
        $this->assertEquals($ref->system, $read->system);
        $this->assertEquals($ref->cs, $read->cs);
        $this->assertEquals($ref->dispOrder, $read->dispOrder);
        // Check rules
        $this->assertEquals(count($ref->rules), count($read->rules));
        for ($i = 0; $i < count($ref->rules); $i++) {
            $refRule = $ref->rules[$i];
            $readRule = $read->rules[$i];
            $this->assertEquals($refRule->minVal, $readRule->minVal);
            $this->assertEquals($refRule->rule, $readRule->rule);
        }
        // Check values
        $this->assertEquals(count($ref->values), count($read->values));
        for ($i = 0; $i < count($ref->values); $i++) {
            $refVal = $ref->values[$i];
            $readVal = $read->values[$i];
            $this->assertEquals($refVal->value, $readVal->value);
            $this->assertEquals($refVal->resource, $readVal->resource);
            $this->assertEquals($refVal->dispOrder, $readVal->dispOrder);
        }
    }

    public function testCreate() {
        $rules = array(new PaymentModeRule(0.0, PaymentModeRule::CREDIT_NOTE),
                new PaymentModeRule(1.0, PaymentModeRule::GIVE_BACK));
        $values = array(new PaymentModeValue(10, "label_10", 1),
                new PaymentModeValue(20, "label_20", 2));
        $mode = new PaymentMode("code", "label" , PaymentMode::CUST_ASSIGNED,
                false, $rules, $values, true, false, true, 1);
        $srv = new PaymentModesService();
        $mode->id = $srv->create($mode);
        $this->assertNotEquals(false, $mode->id, "Creation failed");
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM PAYMENTMODES";
        $stmt = $pdo->prepare($sql);
        $this->assertNotEquals($stmt->execute(), false, "Query failed");
        $row = $stmt->fetch();
        $db = DB::get();
        $this->assertNotEquals(false, $row, "Nothing found");
        $this->assertEquals($mode->id, $row['ID'], "Id mismatch");
        $this->assertEquals($mode->code, $row['CODE'], "Code mismatch");
        $this->assertEquals($mode->label, $row['NAME'], "Label mismatch");
        $this->assertEquals($mode->flags, $row['FLAGS'], "Flags mismatch");
        $this->assertEquals($mode->active, $db->readBool($row['ACTIVE']),
                "Active mismatch");
        $this->assertEquals($mode->system, $db->readBool($row['SYSTEM']),
                "System mismatch");
        $this->assertEquals($mode->cs, $db->readBool($row['CS']),
                "CS mismatch");
        $this->assertEquals($mode->dispOrder, $row['DISPORDER'],
                "Order mismatch");
        $stmtRules = $pdo->prepare("SELECT * FROM PAYMENTMODES_RULES "
                . "ORDER BY MIN ASC");
        $this->assertNotEquals(false, $stmtRules->execute(), "Query failed");
        $rulesCount = 0;
        while ($row = $stmtRules->fetch()) {
            $this->assertEquals($rules[$rulesCount]->minVal, $row['MIN'],
                    "Rule min val mismatch");
            $this->assertEquals($rules[$rulesCount]->rule, $row['RULE'],
                    "Rule mismatch");
            $rulesCount++;
            if ($rulesCount > count($rules)) {
                $this->assertTrue(false, "Unknown rule");
            }
        }
        $this->assertEquals(count($rules), $rulesCount,
                "Rules count mismatch");        
    }

    /** @depends testCreate */
    public function testRead() {
        $rules = array(new PaymentModeRule(0.0, PaymentModeRule::CREDIT_NOTE),
                new PaymentModeRule(1.0, PaymentModeRule::GIVE_BACK));
        $values = array(new PaymentModeValue(10, "label_10", 1),
                new PaymentModeValue(20, "label_20", 2));
        $mode = new PaymentMode("code", "label" , PaymentMode::CUST_ASSIGNED,
                false, $rules, $values, true, false, true, 1);
        $srv = new PaymentModesService();
        $mode->id = $srv->create($mode);
        $read = $srv->get($mode->id);
        $this->assertNotNull($read, "Nothing found");
        $this->checkEquality($mode, $read);
    }

    public function testReadInexistent() {
        $srv = new PaymentModesService();
        $read = $srv->get(0);
        $this->assertEquals(null, $read);
    }

    /** @depends testCreate
     * @depends testRead
     */
    public function testUpdate() {
        $rules = array(new PaymentModeRule(0.0, PaymentModeRule::CREDIT_NOTE),
                new PaymentModeRule(1.0, PaymentModeRule::GIVE_BACK));
        $values = array(new PaymentModeValue(10, "label_10", 1),
                new PaymentModeValue(20, "label_20", 2));
        $mode = new PaymentMode("code", "label" , PaymentMode::CUST_ASSIGNED,
                false, $rules, $values, true, false, true, 1);
        $srv = new PaymentModesService();
        $mode->id = $srv->create($mode);
        $mode->label = "Edited";
        $mode->cs = false;
        $this->assertTrue($srv->update($mode), "Update failed");
        $read = $srv->get($mode->id);
        $this->checkEquality($mode, $read);
    }

    public function testUpdateInexistent() {
        $this->markTestIncomplete();
    }

    /** @depends testCreate
     * @depends testRead
     */
    public function testDelete() {
        $rules = array(new PaymentModeRule(0.0, PaymentModeRule::CREDIT_NOTE),
                new PaymentModeRule(1.0, PaymentModeRule::GIVE_BACK));
        $values = array(new PaymentModeValue(10, "label_10", 1),
                new PaymentModeValue(20, "label_20", 2));
        $mode = new PaymentMode("code", "label" , PaymentMode::CUST_ASSIGNED,
                false, $rules, $values, true, false, true, 1);
        $srv = new PaymentModesService();
        $mode->id = $srv->create($mode);
        $this->assertTrue($srv->delete($mode->id), "Delete failed");
        $this->assertNull($srv->get($mode->id), "Profile is still there");
    }

}