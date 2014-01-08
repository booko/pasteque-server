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
            "PREPAID" => "prepaid",
            "MAXDEBT" => "maxDebt",
            "CURDEBT" => "currDebt",
            "CURDATE" => "debtDate",
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
            "VISIBLE" => "visible"
    );

    protected function build($row, $pdo = null) {
        $cust = Customer::__build($row['ID'], $row['TAXID'], $row['SEARCHKEY'],
                $row['NAME'], $row['CARD'], $row['TAXCATEGORY'],
                $row['PREPAID'],
                $row['MAXDEBT'], $row['CURDEBT'], stdtimefstr($row['CURDATE']),
                $row['FIRSTNAME'], $row['LASTNAME'], $row['EMAIL'],
                $row['PHONE'], $row['PHONE2'], $row['FAX'],
                $row['ADDRESS'], $row['ADDRESS2'], $row['POSTAL'],
                $row['CITY'], $row['REGION'], $row['COUNTRY'],
                $row['NOTES'], ord($row['VISIBLE']) == 1);
        return $cust;
    }

    function getAll($include_hidden = false) {
        $customers = array();
        $pdo = PDOBuilder::getPDO();
        $sql = null;
        if ($include_hidden) {
            $sql = "SELECT * FROM CUSTOMERS";
        } else {
            $sql = "SELECT * FROM CUSTOMERS WHERE VISIBLE = 1";
        }
        foreach ($pdo->query($sql) as $dbCust) {
            $cust = $this->build($dbCust);
            $customers[] = $cust;
        }
        return $customers;
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

    function update($cust) {
        if ($cust->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE CUSTOMERS SET SEARCHKEY = :key, TAXID = :num, "
                . "NAME = :name, TAXCATEGORY = :tax_id, CARD = :card, "
                . "PREPAID = :prepaid, "
                . "MAXDEBT = :max_debt, ADDRESS = :addr, ADDRESS2 = :addr2, "
                . "POSTAL = :zip, CITY = :city, REGION = :region, "
                . "COUNTRY = :country, FIRSTNAME = :first_name, "
                . "LASTNAME = :last_name, EMAIL = :email, PHONE = :phone, "
                . "PHONE2 = :phone2, FAX = :fax, NOTES = :note, "
                . "VISIBLE = :visible, CURDATE = :date, CURDEBT = :debt";
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":key", $cust->key, \PDO::PARAM_STR);
        $stmt->bindParam(":num", $cust->number, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $cust->dispName, \PDO::PARAM_STR);
        $stmt->bindParam(":tax_id", $cust->custTaxId, \PDO::PARAM_STR);
        $stmt->bindParam(":card", $cust->card, \PDO::PARAM_STR);
        $stmt->bindParam(":prepaid", $cust->prepaid, \PDO::PARAM_STR);
        $stmt->bindParam(":max_debt", $cust->maxDebt, \PDO::PARAM_STR);
        $stmt->bindParam(":addr", $cust->addr1, \PDO::PARAM_STR);
        $stmt->bindParam(":addr2", $cust->addr2, \PDO::PARAM_STR);
        $stmt->bindParam(":zip", $cust->zipCode, \PDO::PARAM_STR);
        $stmt->bindParam(":city", $cust->city, \PDO::PARAM_STR);
        $stmt->bindParam(":region", $cust->region, \PDO::PARAM_STR);
        $stmt->bindParam(":country", $cust->country, \PDO::PARAM_STR);
        $stmt->bindParam(":first_name", $cust->firstName, \PDO::PARAM_STR);
        $stmt->bindParam(":last_name", $cust->lastName, \PDO::PARAM_STR);
        $stmt->bindParam(":email", $cust->email, \PDO::PARAM_STR);
        $stmt->bindParam(":phone", $cust->phone1, \PDO::PARAM_STR);
        $stmt->bindParam(":phone2", $cust->phone2, \PDO::PARAM_STR);
        $stmt->bindParam(":fax", $cust->fax, \PDO::PARAM_STR);
        $stmt->bindParam(":note", $cust->note, \PDO::PARAM_STR);
        $stmt->bindParam(":visible", $cust->visible, \PDO::PARAM_INT);
        $stmt->bindParam(":date", $cust->debtDate, \PDO::PARAM_STR);
        $stmt->bindParam(":debt", stdstrftime($cust->currDebt));
        $stmt->bindParam(":id", $cust->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    function create($cust) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $sql = "INSERT INTO CUSTOMERS (ID, SEARCHKEY, TAXID, NAME, TAXCATEGORY, "
                . "CARD, PREPAID, MAXDEBT, ADDRESS, ADDRESS2, POSTAL, CITY, REGION, "
                . "COUNTRY, FIRSTNAME, LASTNAME, EMAIL, PHONE, PHONE2, FAX, "
                . "NOTES, VISIBLE, CURDATE, CURDEBT) VALUES ("
                . ":id, :key, :num, :name, :tax_id, :card, :prepaid, :max_debt, :addr, "
                . ":addr2, :zip, :city, :region, :country, :first_name, "
                . ":last_name, :email, :phone, :phone2, :fax, :note, :visible, "
                . ":date, :debt)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id, \PDO::PARAM_STR);
        $stmt->bindParam(":key", $cust->key, \PDO::PARAM_STR);
        $stmt->bindParam(":num", $cust->number, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $cust->dispName, \PDO::PARAM_STR);
        $stmt->bindParam(":tax_id", $cust->custTaxId, \PDO::PARAM_STR);
        $stmt->bindParam(":card", $cust->card, \PDO::PARAM_STR);
        $stmt->bindParam(":prepaid", $cust->prepaid, \PDO::PARAM_STR);
        $stmt->bindParam(":max_debt", $cust->maxDebt, \PDO::PARAM_STR);
        $stmt->bindParam(":addr", $cust->addr1, \PDO::PARAM_STR);
        $stmt->bindParam(":addr2", $cust->addr2, \PDO::PARAM_STR);
        $stmt->bindParam(":zip", $cust->zipCode, \PDO::PARAM_STR);
        $stmt->bindParam(":city", $cust->city, \PDO::PARAM_STR);
        $stmt->bindParam(":region", $cust->region, \PDO::PARAM_STR);
        $stmt->bindParam(":country", $cust->country, \PDO::PARAM_STR);
        $stmt->bindParam(":first_name", $cust->firstName, \PDO::PARAM_STR);
        $stmt->bindParam(":last_name", $cust->lastName, \PDO::PARAM_STR);
        $stmt->bindParam(":email", $cust->email, \PDO::PARAM_STR);
        $stmt->bindParam(":phone", $cust->phone1, \PDO::PARAM_STR);
        $stmt->bindParam(":phone2", $cust->phone2, \PDO::PARAM_STR);
        $stmt->bindParam(":fax", $cust->fax, \PDO::PARAM_STR);
        $stmt->bindParam(":note", $cust->note, \PDO::PARAM_STR);
        $stmt->bindParam(":visible", $cust->visible, \PDO::PARAM_INT);
        $stmt->bindParam(":date", stdstrftime($cust->debtDate));
        $stmt->bindParam(":debt", $cust->currDebt, \PDO::PARAM_STR);
        if ($stmt->execute() !== FALSE) {
            return $id;
        } else {
            return FALSE;
        }
    }

    function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $sql = "DELETE FROM CUSTOMERS WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>