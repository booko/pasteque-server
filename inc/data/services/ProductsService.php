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

class ProductsService {

    private static function buildDBLightPrd($db_prd, $pdo) {
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS_CAT WHERE PRODUCT = :id");
        $stmt->execute(array(':id' => $db_prd['ID']));
        $visible = ($stmt->fetch() !== false);
        return ProductLight::__build($db_prd['ID'], $db_prd['REFERENCE'],
                $db_prd['NAME'], $db_prd['PRICESELL'], $visible,
                ord($db_prd['ISSCALE']) == 1, $db_prd['CODE'],
                $db_prd['PRICEBUY'], ord($db_prd['DISCOUNTENABLED']),
                $db_prd['DISCOUNTRATE']);
    }

    private static function buildDBPrd($db_prd, $pdo) {
        $cat = CategoriesService::get($db_prd['CATEGORY']);
        $tax_cat = TaxesService::get($db_prd['TAXCAT']);
        $attr = AttributesService::get($db_prd['ATTRIBUTES']);
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS_CAT WHERE PRODUCT = :id");
        $stmt->execute(array(':id' => $db_prd['ID']));
        $prd_cat = $stmt->fetch();
        $visible = ($prd_cat !== false);
        $disp_order = NULL;
        if ($visible) {
            $disp_order = $prd_cat['CATORDER'];
        }
        return Product::__build($db_prd['ID'], $db_prd['REFERENCE'],
                $db_prd['NAME'], $db_prd['PRICESELL'], $cat, $disp_order, 
                $tax_cat, $visible, ord($db_prd['ISSCALE']) == 1,
                $db_prd['PRICEBUY'], $attr, $db_prd['CODE'], $db_prd['IMAGE'],
                ord($db_prd['DISCOUNTENABLED']), $db_prd['DISCOUNTRATE']);
    }

    static function getAll($full = FALSE, $include_hidden = FALSE) {
        $prds = array();
        $pdo = PDOBuilder::getPDO();
        $sql = NULL;
        if ($include_hidden) {
            $sql = "SELECT * FROM PRODUCTS LEFT JOIN PRODUCTS_CAT ON "
                    . "PRODUCTS_CAT.PRODUCT = PRODUCTS.ID "
                    . "WHERE DELETED = 0 ORDER BY CATORDER";
        } else {
            $sql = "SELECT * FROM PRODUCTS, PRODUCTS_CAT WHERE "
                    . "PRODUCTS.ID = PRODUCTS_CAT.PRODUCT AND DELETED = 0 "
                    . "ORDER BY CATORDER";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($db_prd = $stmt->fetch()) {
            if ($full) {
                $prd = ProductsService::buildDBPrd($db_prd, $pdo);
            } else {
                $prd = ProductsService::buildDBLightPrd($db_prd, $pdo);
            }
            $prds[] = $prd;
        }
        return $prds;
    }

    static function getPrepaidIds() {
        $ids = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT ID FROM PRODUCTS WHERE CATEGORY = :cat AND DELETED = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":cat", '-1');
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $ids[] = $row['ID'];
        }
        return $ids;
    }

    static function getByRef($ref) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS "
            . "WHERE PRODUCTS.REFERENCE = :ref");
        $stmt->bindParam(":ref", $ref, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                $prd = ProductsService::buildDBPrd($row, $pdo);
                return $prd;
            }
        }
        return null;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS LEFT JOIN PRODUCTS_CAT "
                . "ON PRODUCTS_CAT.PRODUCT = PRODUCTS.ID WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                $prd = ProductsService::buildDBPrd($row, $pdo);
                return $prd;
            }
        }
        return null;
    }

    static function update($prd) {
        $pdo = PDOBuilder::getPDO();
        $attr_id = null;
        if ($prd->attributes_set != null) {
            $attr_id = $prd->attributes_set->id;
        }
        $code = "";
        if ($prd->barcode != null) {
            $code = $prd->barcode;
        }
        $sql = "UPDATE PRODUCTS SET REFERENCE = :ref, CODE = :code, "
                . "NAME = :name, PRICEBUY = :buy, PRICESELL = :sell, "
                . "CATEGORY = :cat, TAXCAT = :tax, ATTRIBUTESET_ID = :attr, "
                . "ISSCALE = :scale, DISCOUNTENABLED = :discount_enabled, "
                . "DISCOUNTRATE = :discount_rate";
        if ($prd->image !== "") {
            $sql .= ", IMAGE = :img";
        }
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":ref", $prd->reference, \PDO::PARAM_STR);
        $stmt->bindParam(":code", $code, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $prd->label, \PDO::PARAM_STR);
        $stmt->bindParam(":buy", $prd->price_buy, \PDO::PARAM_STR);
        $stmt->bindParam(":sell", $prd->price_sell, \PDO::PARAM_STR);
        $stmt->bindParam(":cat", $prd->category->id, \PDO::PARAM_INT);
        $stmt->bindParam(":tax", $prd->tax_cat->id, \PDO::PARAM_INT);
        $stmt->bindParam(":attr", $attr_id, \PDO::PARAM_INT);
        $stmt->bindParam(":scale", $prd->scaled, \PDO::PARAM_INT);
        $stmt->bindParam(":id", $prd->id, \PDO::PARAM_INT);
        $stmt->bindParam(":discount_enabled", $prd->discount_enabled, \PDO::PARAM_INT);
        $stmt->bindParam(":discount_rate", $prd->discount_rate);
        if ($prd->image !== "") {
            $stmt->bindParam(":img", $prd->image, \PDO::PARAM_LOB);
        }
        $vsql = "DELETE FROM PRODUCTS_CAT WHERE PRODUCT = :id";
        $vstmt = $pdo->prepare($vsql);
        $vstmt->bindParam(":id", $prd->id, \PDO::PARAM_STR);
        $vstmt->execute();
        if ($prd->visible == 1 || $prd->visible == TRUE) {
            $vsql = "INSERT INTO PRODUCTS_CAT (PRODUCT, CATORDER) VALUES "
                    . "(:id, :disp_order)";
            $vstmt = $pdo->prepare($vsql);
            $vstmt->bindParam(":id", $prd->id, \PDO::PARAM_STR);
            $vstmt->bindParam(":disp_order", $prd->disp_order, \PDO::PARAM_INT);
            $vstmt->execute();
        }
        return $stmt->execute();
    }
    
    static function create($prd) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $attr_id = null;
        if ($prd->attributes_set != null) {
            $attr_id = $prd->attributes_set->id;
        }
        $code = "";
        if ($prd->barcode != null) {
            $code = $prd->barcode;
        }
        $sql = "INSERT INTO PRODUCTS (ID, REFERENCE, CODE, NAME, "
                . "PRICEBUY, PRICESELL, CATEGORY, TAXCAT, "
                . "ATTRIBUTESET_ID, ISSCALE, DISCOUNTENABLED, DISCOUNTRATE";
        if ($prd->image !== "") {
            $sql .= ", IMAGE";
        }
        $sql .= ") VALUES (:id, :ref, :code, :name, :buy, :sell, :cat, "
                . ":tax, :attr, :scale, :disc_enabled, :disc_rate";
        if ($prd->image !== "") {
            $sql .= ", :img";
        }
        $sql .= ")";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":ref", $prd->reference, \PDO::PARAM_STR);
        $stmt->bindParam(":code", $code, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $prd->label, \PDO::PARAM_STR);
        $stmt->bindParam(":buy", $prd->price_buy, \PDO::PARAM_STR);
        $stmt->bindParam(":sell", $prd->price_sell, \PDO::PARAM_STR);
        $stmt->bindParam(":cat", $prd->category->id, \PDO::PARAM_INT);
        $stmt->bindParam(":tax", $prd->tax_cat->id, \PDO::PARAM_INT);
        $stmt->bindParam(":attr", $attr_id, \PDO::PARAM_INT);
        $stmt->bindParam(":scale", $prd->scaled, \PDO::PARAM_INT);
        $stmt->bindParam(":disc_enabled", $prd->discount_enabled, \PDO::PARAM_INT);
        $stmt->bindParam(":disc_rate", $prd->discount_rate);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($prd->image !== "") {
            $stmt->bindParam(":img", $prd->image, \PDO::PARAM_LOB);
        }
        if (!$stmt->execute()) {
            return FALSE;
        }
        if ($prd->visible == 1 || $prd->visible == TRUE) {
            $catstmt = $pdo->prepare("INSERT INTO PRODUCTS_CAT (PRODUCT, CATORDER) "
                    . "VALUES (:id, :disp_order)");
            $catstmt->bindParam(":id", $id);
            $catstmt->bindParam(":disp_order", $prd->disp_order);
            $catstmt->execute();
        }
        return $id;
    }
    
    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $stmtcat = $pdo->prepare("DELETE FROM PRODUCTS_CAT WHERE PRODUCT = :id");
        $stmtcat->execute(array(":id" => $id));
        $stmtstk = $pdo->prepare("DELETE FROM STOCKLEVEL WHERE PRODUCT = :id");
        $stmtstk->execute(array(":id" => $id));
        // Update reference with garbage to break unicity constraint
        $garbage = "_deleted_" . \md5(\time());
        $stmt = $pdo->prepare("UPDATE PRODUCTS SET DELETED = 1, "
               . "REFERENCE = concat(REFERENCE, :garbage) WHERE ID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':garbage', $garbage);
        return $stmt->execute();
    }
}

?>
