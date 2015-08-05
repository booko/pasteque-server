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

class CustomersService extends AbstractService {

    protected static $dbTable = "CUSTOMERS";
    protected static $dbIdField = "ID";
    protected static $fieldMapping = array(
            "ID" => "id",
            "TAXID" => "number",
            "SEARCHKEY" => "key",
            "NAME" => "dispName",
            "CARD" => "card",
            "TAXCATEGORY" => "custTaxId",
            "DISCOUNTPROFILE_ID" => "discountProfileId",
            "TARIFFAREA_ID" => "tariffAreaId",
            "PREPAID" => "prepaid",
            "MAXDEBT" => "maxDebt",
            "CURDEBT" => "currDebt",
            "CURDATE" => array("type" => DB::DATE, "attr" => "debtDate"),
            "FIRSTNAME" => "firstName",
            "LASTNAME" => "lastName",
            "EMAIL" => "email",
            "PHONE" => "phone1",
            "PHONE2" => "phone2",
            "FAX" => "fax",
            "ADDRESS" => "addr1",
            "ADDRESS2" => "addr2",
            "POSTAL" => "zipCode",
            "CITY" => "city",
            "REGION" => "region",
            "COUNTRY" => "country",
            "NOTES" => "note",
            "VISIBLE" => array("type" => DB::BOOL, "attr" => "visible"),
            "EXPIREDATE" => array("type" => DB::DATE, "attr" => "expireDate")
    );

    protected function build($row, $pdo = null) {
        $db = DB::get();
        $cust = Customer::__build($row['ID'], $row['TAXID'], $row['SEARCHKEY'],
                $row['NAME'], $row['CARD'], $row['TAXCATEGORY'],
                $row['DISCOUNTPROFILE_ID'], $row['TARIFFAREA_ID'],
                $row['PREPAID'], $row['MAXDEBT'],
                $row['CURDEBT'], $db->readDate($row['CURDATE']),
                $row['FIRSTNAME'], $row['LASTNAME'], $row['EMAIL'],
                $row['PHONE'], $row['PHONE2'], $row['FAX'],
                $row['ADDRESS'], $row['ADDRESS2'], $row['POSTAL'],
                $row['CITY'], $row['REGION'], $row['COUNTRY'],
                $row['NOTES'], $db->readBool($row['VISIBLE']),
                $db->readDate($row['EXPIREDATE']));
        return $cust;
    }

    function getAll($include_hidden = false) {
        $customers = array();
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = null;
        if ($include_hidden) {
            $sql = "SELECT * FROM CUSTOMERS";
        } else {
            $sql = "SELECT * FROM CUSTOMERS WHERE (EXPIREDATE IS NULL OR EXPIREDATE > NOW()) AND VISIBLE = " . $db->true();
        }
        $sql .= " ORDER BY NAME ASC";
        foreach ($pdo->query($sql) as $dbCust) {
            $cust = CustomersService::build($dbCust);
            $customers[] = $cust;
        }
        return $customers;
    }

    function getTop($limit = 10) {
        if ($limit === null) {
            $limit = 10;
        }
        $custIds = array();
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = "SELECT C.ID, COUNT(TICKETS.CUSTOMER) AS Top10 "
                . "FROM CUSTOMERS AS C "
                . "LEFT JOIN TICKETS ON TICKETS.CUSTOMER = C.ID "
                . "WHERE C.VISIBLE = " . $db->true() . " "
                . "AND (EXPIREDATE IS NULL OR EXPIREDATE > NOW()) "
                . "GROUP BY C.ID "
                . "ORDER BY Top10 DESC, C.NAME ASC "
                . "LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":limit", $limit, \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $custIds[] = $row['ID'];
        }
        return $custIds;
    }

    function addPrepaid($custId, $amount) {
        $cust = $this->get($custId);
        if ($cust !== null) {
            $cust->prepaid += $amount;
            $ret = $this->update($cust);
            return $ret;
        }
        return false;
    }

    /** Add debt and update debt date */
    function addDebt($custId, $amount, $date) {
        $cust = $this->get($custId);
        if ($cust !== null) {
            $cust->currDebt += $amount;
            $cust->debtDate = $date;
            $ret = $this->update($cust);
            return $ret;
        }
        return false;
    }
    /** Pay debt (use positive amount), if debt is 0 date is deleted. */
    function recoverDebt($custId, $amount) {
        $cust = $this->get($custId);
        if ($cust !== null) {
            $cust->currDebt -= $amount;
            if ($cust->currDebt == 0) {
                $cust->debtDate = null;
            }
            $ret = $this->update($cust);
            return $ret;
        }
        return false;
    }

    public function create($model) {
        // This is a copy of AbstractService->create but with explicit id
        $model->id = md5(time() . rand());
        // TODO: Move this when customer gets created
        if (!isset($model->visible) || $model->visible == null) {
            $model->visible = 1;
        }
        $dbData = static::unbuild($model);
        $pdo = PDOBuilder::getPDO();
        $dbFields = array_keys(static::$fieldMapping); // Copy
        // Prepare sql query
        $sql = "INSERT INTO " . static::$dbTable . " ("
                . implode($dbFields, ", ") . ") VALUES (";
        // Set :field for each field for values and bind values for PDO
        foreach ($dbFields as $field) {
            $sql .= ":" . $field . ", ";
        }
        $sql = substr($sql, 0, -2);
        $sql .= ")";
        // Assign values to sql
        $stmt = $pdo->prepare($sql);
        foreach ($dbFields as $field) {
            if ($dbData[$field] === null) {
                $stmt->bindValue(":" . $field, null, \PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(":" . $field, $dbData[$field]);
            }
        }
        // RUN!
        if ($stmt->execute()) {
            return $model->id;
        } else {
            return false;
        }
    }

    public function getNextNumber() {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT TAXID FROM CUSTOMERS");
        $stmt->execute();
        $maxNum = 0;
        while ($row = $stmt->fetch()) {
            $val = intval($row["TAXID"]);
            if ($maxNum < $val) {
                $maxNum = $val;
            }
        }
        return $maxNum + 1;
    }
}
