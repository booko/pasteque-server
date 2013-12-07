<?php
//    Pastèque Web back office
//
//    Copyright (C) 2013 Scil (http://scil.coop)
//
//    This file is part of Pastèque.
//
//    Pastèque is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Pastèque is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Pastèque.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque;

class TaxesService {

    private static function buildDBTaxCat($db_taxcat, $pdo) {
        $taxcat = TaxCat::__build($db_taxcat['id'], $db_taxcat['name']);
        $sqltax = 'SELECT * FROM taxes WHERE taxcategory_id = "' . $db_taxcat['id'] . '" ORDER BY validfrom DESC';
        foreach ($pdo->query($sqltax) as $db_tax) {
            $tax = TaxesService::buildDBTax($db_tax);
            $taxcat->addTax($tax);
        }
        return $taxcat;
    }

    private static function buildDBTax($db_tax) {
        $tax = Tax::__build($db_tax['id'], $db_tax['taxcategory_id'],
                            $db_tax['validfrom'], $db_tax['rate']);
        return $tax;
    }

    static function getAll() {
        $taxcats = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM taxcategories";
        foreach ($pdo->query($sql) as $db_taxcat) {
            $taxcat = TaxesService::buildDBTaxCat($db_taxcat, $pdo);
            $taxcats[] = $taxcat;
        }
        return $taxcats;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM taxcategories WHERE id = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return TaxesService::buildDBTaxCat($row, $pdo);
            }
        }
        return null;
    }

    static function updateCat($cat) {
        if ($cat->getId() == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare('UPDATE taxcategories SET name = :name '
                              . 'WHERE id = :id');
        $stmt->bindParam(":id", $cat->id, \PDO::PARAM_INT);
        $stmt->bindParam(":name", $cat->name, \PDO::PARAM_STR);
        return $stmt->execute();
    }

    static function createCat($cat) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $stmt = $pdo->prepare('INSERT INTO taxcategories (id, name) VALUES '
                              . '(:id, :name)');
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        $stmt->bindParam(":name", $cat->name, \PDO::PARAM_STR);
        return $stmt->execute();
    }

    static function deleteCat($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare('DELETE FROM taxcategories WHERE id = :id');
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    static function getTax($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM taxes WHERE id = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return TaxesService::buildDBTax($row, $pdo);
            }
        }
        return null;
    }

    static function getTaxes($tax_cat_id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM taxes WHERE taxcategory_id = :id "
                . "ORDER BY validfrom DESC");
        $stmt->bindParam(":id", $tax_cat_id, \PDO::PARAM_INT);
        $taxes = array();
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()) {
                $taxes[] = TaxesService::buildDBTax($row, $pdo);
            }
        }
        return $taxes;
    }

    static function updateTax($tax) {
        if ($tax->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare('UPDATE taxes SET validfrom = :valid, '
                              . 'taxcategory_id = :cat, rate = :rate '
                              . 'WHERE id = :id');
        $date = strftime("%Y-%m-%d %H:%M:%S", $tax->start_date);
        $stmt->bindParam(":id", $tax->id, \PDO::PARAM_INT);
        $stmt->bindParam(":cat", $tax->tax_cat_id, \PDO::PARAM_INT);
        $stmt->bindParam(":rate", $tax->rate, \PDO::PARAM_STR);
        $stmt->bindParam(":valid", $date, \PDO::PARAM_STR);
        return $stmt->execute();
    }

    static function createTax($tax) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $stmt = $pdo->prepare('INSERT INTO taxes (validfrom, '
                              . 'taxcategory_id, rate) VALUES '
                              . '(:valid, :cat, :rate)');
        $date = strftime("%Y-%m-%d %H:%M:%S", $tax->start_date);
        $stmt->bindParam(":cat", $tax->tax_cat_id, \PDO::PARAM_INT);
        $stmt->bindParam(":rate", $tax->rate, \PDO::PARAM_STR);
        $stmt->bindParam(":valid", $date, \PDO::PARAM_STR);
        return $stmt->execute();
    }

    static function deleteTax($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM taxes WHERE id = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>
