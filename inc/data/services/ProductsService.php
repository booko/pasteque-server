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

    private static function buildDBPrd($dpPrd, $pdo) {
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS_CAT WHERE PRODUCT = :id");
        $stmt->execute(array(':id' => $dpPrd['ID']));
        $prdCat = $stmt->fetch();
        $visible = ($prdCat !== false);
        $dispOrder = null;
        if ($visible) {
            $dispOrder = $prdCat['CATORDER'];
        }
        return Product::__build($dpPrd['ID'], $dpPrd['REFERENCE'],
                $dpPrd['NAME'], $dpPrd['PRICESELL'], $dpPrd['CATEGORY'],
                $dpPrd['PROVIDER'],
                $dispOrder, $dpPrd['TAXCAT'], $visible,
                $db->readBool($dpPrd['ISSCALE']), $dpPrd['PRICEBUY'],
                $dpPrd['ATTRIBUTESET_ID'], $dpPrd['CODE'],
                $dpPrd['IMAGE'] !== null,
                $db->readBool($dpPrd['DISCOUNTENABLED']),
                $dpPrd['DISCOUNTRATE']);
    }

    // Retrieves the number of products
    static function getTotal($include_hidden = false) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = NULL;
        if ($include_hidden) {
            $sql = "SELECT COUNT(*) AS TOTAL FROM PRODUCTS LEFT JOIN PRODUCTS_CAT ON "
                    . "PRODUCTS_CAT.PRODUCT = PRODUCTS.ID "
                    . "WHERE DELETED = " . $db->false();
        } else {
            $sql = "SELECT COUNT(*) AS TOTAL FROM PRODUCTS, PRODUCTS_CAT WHERE "
                    . "PRODUCTS.ID = PRODUCTS_CAT.PRODUCT AND DELETED = "
                    . $db->false();
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    static function getTotalByCategory($categoryId, $include_hidden = false) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = NULL;
        if ($include_hidden) {
            $sql = "SELECT COUNT(*) AS TOTAL FROM PRODUCTS LEFT JOIN PRODUCTS_CAT ON "
                    . "PRODUCTS_CAT.PRODUCT = PRODUCTS.ID "
                    . "WHERE DELETED = " . $db->false();
        } else {
            $sql = "SELECT COUNT(*) AS TOTAL FROM PRODUCTS, PRODUCTS_CAT WHERE "
                    . "PRODUCTS.ID = PRODUCTS_CAT.PRODUCT AND DELETED = "
                    . $db->false();
        }
        $sql .= " AND CATEGORY = '".$categoryId."'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    static function getAll($include_hidden = false) {
        $prds = array();
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = NULL;
        if ($include_hidden) {
            $sql = "SELECT * FROM PRODUCTS LEFT JOIN PRODUCTS_CAT ON "
                    . "PRODUCTS_CAT.PRODUCT = PRODUCTS.ID";
        } else {
            $sql = "SELECT * FROM PRODUCTS LEFT JOIN PRODUCTS_CAT ON "
                    . "PRODUCTS.ID = PRODUCTS_CAT.PRODUCT WHERE DELETED = "
                    . $db->false();
        }
        $sql .= " ORDER BY CATORDER, NAME";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($dpPrd = $stmt->fetch()) {
            $prd = ProductsService::buildDBPrd($dpPrd, $pdo);
            $prds[] = $prd;
        }
        return $prds;
    }

    static function getRange($range,$start=0,$include_hidden=false) {
        $prds = array();
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = NULL;
        if ($include_hidden) {
            $sql = "SELECT PRODUCTS.*, PRODUCTS_CAT.* FROM CATEGORIES, PRODUCTS  "
                    . "LEFT JOIN PRODUCTS_CAT ON "
                    . "PRODUCTS_CAT.PRODUCT = PRODUCTS.ID "
                    . "WHERE 1=1";
        } else {
            $sql = "SELECT PRODUCTS.*, PRODUCTS_CAT.* FROM CATEGORIES, PRODUCTS  "
                    . "LEFT JOIN PRODUCTS_CAT ON "
                    . "PRODUCTS_CAT.PRODUCT = PRODUCTS.ID "
                    . "WHERE DELETED = " . $db->false();
        }
        $sql .= " AND PRODUCTS.CATEGORY = CATEGORIES.ID ";
        $sql .= " ORDER BY DELETED ASC, CATEGORIES.DISPORDER, CATEGORY, CATORDER, PRODUCTS.NAME";
        $sql .= " LIMIT ".$range." OFFSET ".$start;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($dpPrd = $stmt->fetch()) {
            $prd = ProductsService::buildDBPrd($dpPrd, $pdo);
            $prds[] = $prd;
        }
        return $prds;
    }

    static function getPrepaidIds() {
        $ids = array();
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $sql = "SELECT ID FROM PRODUCTS WHERE CATEGORY = :cat AND DELETED = "
                . $db->false() . ";";
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

    static function getByCode($code) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS "
            . "WHERE PRODUCTS.CODE = :code");
        $stmt->bindParam(":code", $code, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                $prd = ProductsService::buildDBPrd($row, $pdo);
                return $prd;
            }
        }
        return null;
    }

    static function getByCategory($categoryId) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS, PRODUCTS_CAT WHERE "
                . "PRODUCTS.ID = PRODUCTS_CAT.PRODUCT AND DELETED = "
                . $db->false() . " AND PRODUCTS.CATEGORY = :cat "
                . "ORDER BY CATORDER, NAME");
        $stmt->bindParam(":cat", $categoryId, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            $prds = array();
            while ($row = $stmt->fetch()) {
                $prd = ProductsService::buildDBPrd($row, $pdo);
                $prds[] = $prd;
            }
            return $prds;
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


    static function getImage($id) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("SELECT IMAGE FROM PRODUCTS WHERE ID = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return $db->readBin($row['IMAGE']);
            }
        }
        return null;
    }

    /** Update a product. $prd->id must be set. Set $image to "" (default)
     * to keep the actual image */
    static function update($prd, $image = "") {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $code = "";
        if ($prd->barcode != null) {
            $code = $prd->barcode;
        }
        $sql = "UPDATE PRODUCTS SET REFERENCE = :ref, CODE = :code, "
                . "NAME = :name, PRICEBUY = :buy, PRICESELL = :sell, "
                . "CATEGORY = :cat, PROVIDER = :prov, TAXCAT = :tax, "
                . "ATTRIBUTESET_ID = :attr, "
                . "ISSCALE = :scale, DISCOUNTENABLED = :discountEnabled, "
                . "DISCOUNTRATE = :discountRate";
        if ($image !== "") {
            $sql .= ", IMAGE = :img";
        }
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":ref", $prd->reference, \PDO::PARAM_STR);
        $stmt->bindParam(":code", $code, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $prd->label, \PDO::PARAM_STR);
        if ($prd->priceBuy === null || $prd->priceBuy === "") {
            $stmt->bindParam(":buy", $prd->priceBuy, \PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":buy", $prd->priceBuy, \PDO::PARAM_STR);
        }
        $stmt->bindParam(":sell", $prd->priceSell, \PDO::PARAM_STR);
        $stmt->bindParam(":cat", $prd->categoryId, \PDO::PARAM_INT);
        if ($prd->providerId === null || $prd->providerId === "") {
            $stmt->bindParam(":prov", $prd->providerId, \PDO::PARAM_NULL);
        }
        else {
            $stmt->bindParam(":prov", $prd->providerId, \PDO::PARAM_STR);
        }
        $stmt->bindParam(":tax", $prd->taxCatId, \PDO::PARAM_INT);
        $stmt->bindParam(":attr", $prd->attributeSetId, \PDO::PARAM_INT);
        $stmt->bindParam(":scale", $db->boolVal($prd->scaled));
        $stmt->bindParam(":id", $prd->id, \PDO::PARAM_INT);
        $stmt->bindParam(":discountEnabled",
                $db->boolVal($prd->discountEnabled));
        if ($prd->discountRate === null || $prd->discountRate === "") {
            $stmt->bindValue(":discountRate", 0.0);
        } else {
            $stmt->bindParam(":discountRate", $prd->discountRate);
        }
        if ($image !== "") {
            $stmt->bindParam(":img", $image, \PDO::PARAM_LOB);
        }
        $vsql = "DELETE FROM PRODUCTS_CAT WHERE PRODUCT = :id";
        $vstmt = $pdo->prepare($vsql);
        $vstmt->bindParam(":id", $prd->id, \PDO::PARAM_STR);
        $vstmt->execute();
        if ($prd->visible == 1 || $prd->visible == TRUE) {
            $vsql = "INSERT INTO PRODUCTS_CAT (PRODUCT, CATORDER) VALUES "
                    . "(:id, :dispOrder)";
            $vstmt = $pdo->prepare($vsql);
            $vstmt->bindParam(":id", $prd->id, \PDO::PARAM_STR);
            $vstmt->bindParam(":dispOrder", $prd->dispOrder, \PDO::PARAM_INT);
            $vstmt->execute();
        }
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
        }
    }

    /** Create a product and return its id. */
    static function create($prd, $image = null) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $id = md5(time() . rand());
        $code = "";
        if ($prd->barcode != null) {
            $code = $prd->barcode;
        }
        $sql = "INSERT INTO PRODUCTS (ID, REFERENCE, CODE, NAME, "
                . "PRICEBUY, PRICESELL, CATEGORY, PROVIDER, TAXCAT, "
                . "ATTRIBUTESET_ID, ISSCALE, DISCOUNTENABLED, DISCOUNTRATE, "
                . "IMAGE) VALUES (:id, :ref, :code, :name, :buy, :sell, :cat, :prov, "
                . ":tax, :attr, :scale, :discEnabled, :discRate, :img)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":ref", $prd->reference, \PDO::PARAM_STR);
        $stmt->bindParam(":code", $code, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $prd->label, \PDO::PARAM_STR);
        if ($prd->priceBuy === null || $prd->priceBuy === "") {
            $stmt->bindParam(":buy", $prd->priceBuy, \PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":buy", $prd->priceBuy, \PDO::PARAM_STR);
        }
        $stmt->bindParam(":sell", $prd->priceSell, \PDO::PARAM_STR);
        $stmt->bindParam(":cat", $prd->categoryId, \PDO::PARAM_INT);
        $stmt->bindParam(":prov", $prd->providerId, \PDO::PARAM_INT);
        $stmt->bindParam(":tax", $prd->taxCatId, \PDO::PARAM_INT);
        $stmt->bindParam(":attr", $prd->attributeSetId, \PDO::PARAM_INT);
        $stmt->bindParam(":scale", $db->boolVal($prd->scaled));
        $stmt->bindParam(":discEnabled", $db->boolVal($prd->discountEnabled));
        if ($prd->discountRate === null || $prd->discountRate === "") {
            $stmt->bindValue(":discRate", 0.0);
        } else {
            $stmt->bindParam(":discRate", $prd->discountRate);
        }
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        $stmt->bindParam(":img", $image, \PDO::PARAM_LOB);
        if (!$stmt->execute()) {
            return false;
        }
        if ($prd->visible == true) {
            $catstmt = $pdo->prepare("INSERT INTO PRODUCTS_CAT (PRODUCT, "
                    . "CATORDER) "
                    . "VALUES (:id, :dispOrder)");
            $catstmt->bindParam(":id", $id);
            $catstmt->bindParam(":dispOrder", $prd->dispOrder);
            $catstmt->execute();
        }
        return $id;
    }
    
    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        // Delete references to the product
        $stmtcat = $pdo->prepare("DELETE FROM PRODUCTS_CAT WHERE PRODUCT = :id");
        $stmtcat->execute(array(":id" => $id));
        $stmtstk = $pdo->prepare("DELETE FROM STOCKLEVEL WHERE PRODUCT = :id");
        $stmtstk->execute(array(":id" => $id));
        $stmtstk2 = $pdo->prepare("DELETE FROM STOCKCURRENT WHERE PRODUCT = :id");
        $stmtstk2->execute(array(":id" => $id));
        $stmtta = $pdo->prepare("DELETE FROM TARIFFAREAS_PROD "
                . "WHERE PRODUCTID = :id");
        $stmtta->execute(array(":id" => $id));
        $stmtcmp = $pdo->prepare("DELETE FROM SUBGROUPS_PROD "
                . "WHERE PRODUCT = :id");
        $stmtcmp->execute(array(":id" => $id));
        // Update reference with garbage to break unicity constraint
        $garbage = "_deleted_" . \md5(\time());
        $stmt = $pdo->prepare("UPDATE PRODUCTS SET DELETED = " . $db->true()
                . ", REFERENCE = " . $db->concat("REFERENCE", ":garbage") . ", "
               . "NAME = " . $db->concat("NAME", ":garbage")
                . " WHERE ID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':garbage', $garbage);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
