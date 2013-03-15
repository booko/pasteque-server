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

class CustTaxCatsService {

    private static function buildDBCustTaxCat($db_taxcat) {
        $taxcat = CustTaxCat::__build($db_taxcat['ID'], $db_taxcat['NAME'],
                $db_taxcat['TID']);
        return $taxcat;
    }

    static function getAll() {
        $taxcats = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT tc.ID as ID, tc.NAME as NAME, t.ID as TID FROM TAXCUSTCATEGORIES as tc "
                . "LEFT JOIN TAXES AS t ON t.CUSTCATEGORY = tc.ID";
        foreach ($pdo->query($sql) as $db_taxcat) {
            $taxcat = CustTaxCatsService::buildDBCustTaxCat($db_taxcat);
            $taxcats[] = $taxcat;
        }
        return $taxcats;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT tc.ID as ID, tc.NAME as NAME, t.ID as TID FROM TAXCUSTCATEGORIES as tc "
                . "LEFT JOIN TAXES AS t ON t.CUSTCATEGORY = tc.ID "
                . "WHERE tc.ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                return CustTaxCatsService::buildDBCustTaxCat($row);
            }
        }
        return NULL;
    }

    static function update($cat) {
        if ($cat->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare('UPDATE TAXCUSTCATEGORIES SET NAME = :name '
                              . 'WHERE ID = :id');
        $stmt->execute(array(':name' => $cat->label, ':id' => $cat->id));
        $stmtTaxN = $pdo->prepare("UPDATE TAXES SET CUSTCATEGORY = NULL "
                . "WHERE CUSTCATEGORY = :id");
        $ret = $stmtTaxN->execute(array(':id' => $cat->id));
        if ($cat->tax_cat_id !== NULL) {
            $stmtTax = $pdo->prepare("UPDATE TAXES SET CUSTCATEGORY = :id "
                    . "WHERE ID = :tid");
            $ret = $stmtTax->execute(array(':id' => $cat->id,
                    ':tid' => $cat->tax_cat_id));
        }
        return $ret;
    }

    static function create($cat) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $stmt = $pdo->prepare("INSERT INTO TAXCUSTCATEGORIES (ID, NAME) VALUES "
                . "(:id, :name)");
        $ret = $stmt->execute(array(':name' => $cat->label, ':id' => $id));
        if ($cat->tax_cat_id !== NULL) {
            $stmt = $pdo->prepare("UPDATE TAXES SET CUSTCATEGORY = :id "
                    . "WHERE ID = :tid");
            $ret = $stmt->execute(array(':id' => $id,
                    ':tid' => $cat->tax_cat_id));
        }
        if ($ret !== FALSE) {
            return $id;
        }
        return FALSE;
    }

    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("UPDATE TAXES SET CUSTCATEGORY = NULL "
                . "WHERE CUSTCATEGORY = :id");
        $stmt->execute(array(":id" => $id));
        $stmt = $pdo->prepare('DELETE FROM TAXCUSTCATEGORIES WHERE ID = :id');
        return $stmt->execute(array(':id' => $id));
    }

}

?>