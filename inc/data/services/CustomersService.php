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

class CustomersService {

    private static function buildDBCustomer($db_cust) {
        $cust = Customer::__build($db_cust['ID'], $db_cust['TAXID'], $db_cust['SEARCHKEY'],
                $db_cust['NAME'], $db_cust['CARD'], $db_cust['TAXCATEGORY'],
                $db_cust['MAXDEBT'], $db_cust['CURDEBT'], $db_cust['CURDATE'],
                $db_cust['FIRSTNAME'], $db_cust['LASTNAME'], $db_cust['EMAIL'],
                $db_cust['PHONE'], $db_cust['PHONE2'], $db_cust['FAX'],
                $db_cust['ADDRESS'], $db_cust['ADDRESS2'], $db_cust['POSTAL'],
                $db_cust['CITY'], $db_cust['REGION'], $db_cust['COUNTRY'],
                $db_cust['NOTES'], $db_cust['VISIBLE']);
        return $cust;
    }


    static function getAll($include_hidden = FALSE) {
        $customers = array();
        $pdo = PDOBuilder::getPDO();
        $sql = NULL;
        if ($include_hidden) {
            $sql = "SELECT * FROM CUSTOMERS";
        } else {
            $sql = "SELECT * FORM CUSTOMERS WHERE VISIBLE = 1";
        }
        foreach ($pdo->query($sql) as $db_cust) {
            $cust = CustomersService::buildDBCustomer($db_cust);
            $customers[] = $cust;
        }
        return $customers;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM CUSTOMERS WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                return CustomersService::buildDBCustomer($row);
            }
        }
        return NULL;
    }

    static function update($cust) {
        if ($cust->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE CUSTOMERS SET SEARCHKEY = :key, TAXID = :num, "
                . "NAME = :name, TAXCATEGORY = :tax_id, CARD = :card, "
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
        $stmt->bindParam(":name", $cust->disp_name, \PDO::PARAM_STR);
        $stmt->bindParam(":tax_id", $cust->tax_id, \PDO::PARAM_INT);
        $stmt->bindParam(":card", $cust->card, \PDO::PARAM_STR);
        $stmt->bindParam(":max_debt", $cust->max_debt, \PDO::PARAM_STR);
        $stmt->bindParam(":addr", $cust->addr1, \PDO::PARAM_STR);
        $stmt->bindParam(":addr2", $cust->addr2, \PDO::PARAM_STR);
        $stmt->bindParam(":zip", $cust->zip_code, \PDO::PARAM_STR);
        $stmt->bindParam(":city", $cust->city, \PDO::PARAM_STR);
        $stmt->bindParam(":region", $cust->region, \PDO::PARAM_STR);
        $stmt->bindParam(":country", $cust->country, \PDO::PARAM_STR);
        $stmt->bindParam(":first_name", $cust->first_name, \PDO::PARAM_STR);
        $stmt->bindParam(":last_name", $cust->last_name, \PDO::PARAM_STR);
        $stmt->bindParam(":email", $cust->email, \PDO::PARAM_STR);
        $stmt->bindParam(":phone", $cust->phone1, \PDO::PARAM_STR);
        $stmt->bindParam(":phone2", $cust->phone2, \PDO::PARAM_STR);
        $stmt->bindParam(":fax", $cust->fax, \PDO::PARAM_STR);
        $stmt->bindParam(":note", $cust->note, \PDO::PARAM_STR);
        $stmt->bindParam(":visible", $cust->visible, \PDO::PARAM_INT);
        $stmt->bindParam(":date", $cust->debt_date, \PDO::PARAM_STR);
        $stmt->bindParam(":debt", $cust->curr_debt, \PDO::PARAM_STR);
        $stmt->bindParam(":id", $cust->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    static function create($cust) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $sql = "INSERT INTO CUSTOMERS (ID, SEARCHKEY, TAXID, NAME, TAXCATEGORY, "
                . "CARD, MAXDEBT, ADDRESS, ADDRESS2, POSTAL, CITY, REGION, "
                . "COUNTRY, FIRSTNAME, LASTNAME, EMAIL, PHONE, PHONE2, FAX, "
                . "NOTES, VISIBLE, CURDATE, CURDEBT) VALUES ("
                . ":id, :key, :num, :name, :tax_id, :card, :max_debt, :addr, "
                . ":addr2, :zip, :city, :region, :country, :first_name, "
                . ":last_name, :email, :phone, :phone2, :fax, :note, :visible, "
                . ":date, :debt)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id, \PDO::PARAM_STR);
        $stmt->bindParam(":key", $cust->key, \PDO::PARAM_STR);
        $stmt->bindParam(":num", $cust->number, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $cust->disp_name, \PDO::PARAM_STR);
        $stmt->bindParam(":tax_id", $cust->tax_id, \PDO::PARAM_INT);
        $stmt->bindParam(":card", $cust->card, \PDO::PARAM_STR);
        $stmt->bindParam(":max_debt", $cust->max_debt, \PDO::PARAM_STR);
        $stmt->bindParam(":addr", $cust->addr1, \PDO::PARAM_STR);
        $stmt->bindParam(":addr2", $cust->addr2, \PDO::PARAM_STR);
        $stmt->bindParam(":zip", $cust->zip_code, \PDO::PARAM_STR);
        $stmt->bindParam(":city", $cust->city, \PDO::PARAM_STR);
        $stmt->bindParam(":region", $cust->region, \PDO::PARAM_STR);
        $stmt->bindParam(":country", $cust->country, \PDO::PARAM_STR);
        $stmt->bindParam(":first_name", $cust->first_name, \PDO::PARAM_STR);
        $stmt->bindParam(":last_name", $cust->last_name, \PDO::PARAM_STR);
        $stmt->bindParam(":email", $cust->email, \PDO::PARAM_STR);
        $stmt->bindParam(":phone", $cust->phone1, \PDO::PARAM_STR);
        $stmt->bindParam(":phone2", $cust->phone2, \PDO::PARAM_STR);
        $stmt->bindParam(":fax", $cust->fax, \PDO::PARAM_STR);
        $stmt->bindParam(":note", $cust->note, \PDO::PARAM_STR);
        $stmt->bindParam(":visible", $cust->visible, \PDO::PARAM_INT);
        $stmt->bindParam(":date", $cust->debt_date, \PDO::PARAM_STR);
        $stmt->bindParam(":debt", $cust->curr_debt, \PDO::PARAM_STR);
        if ($stmt->execute() !== FALSE) {
            return $id;
        } else {
            return FALSE;
        }
    }

    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $sql = "DELETE FROM CUSTOMERS WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>
