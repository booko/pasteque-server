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

class ProvidersService {

    private static function buildDBprov($db_prov) {
        return Provider::__build($db_prov['ID'],
                $db_prov['NAME'], $db_prov['IMAGE'] !== null,
                $db_prov['FIRSTNAME'],$db_prov['LASTNAME'],$db_prov['EMAIL'],
                $db_prov['PHONE'],$db_prov['PHONE2'],$db_prov['WEBSITE'],
                $db_prov['FAX'],$db_prov['ADDRESS'],$db_prov['ADDRESS2'],
                $db_prov['POSTAL'],$db_prov['CITY'],$db_prov['REGION'],
                $db_prov['COUNTRY'],$db_prov['NOTES'],$db_prov['VISIBLE'],
                $db_prov['DISPORDER']);
    }

    static function getAll() {
        $provs = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM PROVIDERS ORDER BY DISPORDER ASC, NAME ASC";
        $data = $pdo->query($sql);
        if ($data !== FALSE) {
            foreach ($pdo->query($sql) as $db_prov) {
                $prov = ProvidersService::buildDBprov($db_prov);
                $provs[] = $prov;
            }
        }
        return $provs;
    }

    static function getByName($name) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PROVIDERS WHERE NAME = :name");
        $stmt->bindParam(":name", $name, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                $prov = ProvidersService::buildDBprov($row);
                return $prov;
            }
        }
        return null;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PROVIDERS WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                $prov = ProvidersService::buildDBprov($row);
                return $prov;
            }
        }
        return null;
    }

    static function getImage($id) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT IMAGE FROM PROVIDERS WHERE ID = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return $db->readBin($row['IMAGE']);
            }
        }
        return null;
    }

    static function updateprov($prov, $image = "") {
        if ($prov->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE PROVIDERS SET NAME = :name, "
                . "ADDRESS = :addr1, "
                . "ADDRESS2 = :addr2, "
                . "POSTAL = :zipCode, "
                . "CITY = :city, "
                . "REGION = :region, "
                . "COUNTRY = :country, "
                . "FIRSTNAME = :firstName, "
                . "LASTNAME = :lastName, "
                . "EMAIL = :email, "
                . "PHONE = :phone1, "
                . "PHONE2 = :phone2, "
                . "WEBSITE = :website, "
                . "FAX = :fax, "
                . "NOTES = :notes, "
                . "DISPORDER = :order, "
                . "VISIBLE = :visible";
        if ($image !== "") {
            $sql .= ", IMAGE = :img";
        }
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $prov->id);
        $stmt->bindParam(":name", $prov->label);
        $stmt->bindParam(":addr1",$prov->addr1);
        $stmt->bindParam(":addr2",$prov->addr2);
        $stmt->bindParam(":zipCode",$prov->zipCode);
        $stmt->bindParam(":city",$prov->city);
        $stmt->bindParam(":region",$prov->region);
        $stmt->bindParam(":country",$prov->country);
        $stmt->bindParam(":firstName",$prov->firstName);
        $stmt->bindParam(":lastName",$prov->lastName);
        $stmt->bindParam(":email",$prov->email);
        $stmt->bindParam(":phone1",$prov->phone1);
        $stmt->bindParam(":phone2",$prov->phone2);
        $stmt->bindParam(":website",$prov->website);
        $stmt->bindParam(":fax",$prov->fax);
        $stmt->bindParam(":notes",$prov->notes);
        $stmt->bindParam(":order", $prov->dispOrder);
        $stmt->bindParam(":visible",$prov->visible);
        if ($image !== "") {
            $stmt->bindParam(":img", $image, \PDO::PARAM_LOB);
        }
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
        }
    }

    static function createprov($prov, $image = null) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $sql = "INSERT INTO PROVIDERS (ID, NAME, IMAGE, ADDRESS, ADDRESS2, POSTAL, CITY, REGION, COUNTRY, FIRSTNAME, LASTNAME, EMAIL, PHONE, PHONE2, WEBSITE, FAX, NOTES, DISPORDER, VISIBLE) "
                . "VALUES (:id, :name, :img, :add1, :add2, :zipCode, :city, :region, :country, :firsName, :lastName, :email, :phone1, :phone2, :website, :fax, :notes, :order, :visible)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $prov->label);
        $stmt->bindParam(":add1",$prov->add1);
        $stmt->bindParam(":add2",$prov->add2);
        $stmt->bindParam(":zipCode",$prov->zipCode);
        $stmt->bindParam(":city",$prov->city);
        $stmt->bindParam(":region",$prov->region);
        $stmt->bindParam(":country",$prov->country);
        $stmt->bindParam(":firsName",$prov->firstName);
        $stmt->bindParam(":lastName",$prov->lastName);
        $stmt->bindParam(":email",$prov->email);
        $stmt->bindParam(":phone1",$prov->phone1);
        $stmt->bindParam(":phone2",$prov->phone2);
        $stmt->bindParam(":website",$prov->website);
        $stmt->bindParam(":fax",$prov->fax);
        $stmt->bindParam(":notes",$prov->notes);
        $stmt->bindParam(":visible",$prov->visible);
        $stmt->bindParam(":order", $prov->dispOrder);
        $stmt->bindParam(":img", $image, \PDO::PARAM_LOB);
        if ($stmt->execute() !== false) {
            return $id;
        } else {
            return false;
        }
    }

    static function deleteprov($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare('DELETE FROM PROVIDERS WHERE ID = :id');
        return $stmt->execute(array(':id' => $id));
    }

}
