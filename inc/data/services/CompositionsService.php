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

/** composition doesn't exist in BDD */
class CompositionsService {

    const CAT_ID = "0";
    private static $error = Array();

    private static function build($prd) {
        // Get the product
        $db = DB::get();
        $compo = Composition::__build($prd['ID'], $prd['REFERENCE'],
                $prd['NAME'], $prd['PRICESELL'], $prd['CATEGORY'],
                $dpPrd['PROVIDER'],
                $prd['CATORDER'], $prd['TAXCAT'], $prd['PRODUCT'] != null,
                $db->readBool($prd['ISSCALE']), $prd['PRICEBUY'],
                $prd['ATTRIBUTESET_ID'], $prd['CODE'], $prd['IMAGE'] !== null,
                $db->readBool($prd['DISCOUNTENABLED']),
                $prd['DISCOUNTRATE']);
        // Get the subgroups
        $subgrpSrv = new SubgroupsService($compo->id);
        $compo->groups = $subgrpSrv->getAll();
        return $compo;
    }

    /** Return an array of composition */
    static function getAll() {
        $compos = array();
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $comp = null;
        $sql = "SELECT * FROM PRODUCTS LEFT JOIN PRODUCTS_CAT ON PRODUCT = ID "
                . "WHERE CATEGORY = :cat AND DELETED = " . $db->false();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":cat", CompositionsService::CAT_ID);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $comp = CompositionsService::build($row);
            $compos[] = $comp;
        }
        return $compos;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $comp = null;
        $sql = "SELECT * FROM PRODUCTS LEFT JOIN PRODUCTS_CAT ON PRODUCT = ID "
                . "WHERE CATEGORY = :cat AND ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":cat", CompositionsService::CAT_ID);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        if ($row = $stmt->fetch()) {
            $comp = CompositionsService::build($row);
            return $comp;
        } else {
            return null;
        }
    }

    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        $subgrpSrv = new SubgroupsService($id);
        if (!$subgroups = $subgrpSrv->deleteAll()) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        if (!ProductsService::delete($id)) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        } else {
            if ($newTransaction) {
                $pdo->commit();
            }
            return true;
        }
    }

    static function update($composition, $img = "", $groupImgs) {
        if ($composition->id === null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        // Update product
        if (!ProductsService::update($composition, $img)) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Delete current subgroups
        $srv = new SubgroupsService($composition->id);
        if (!$srv->deleteAll()) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Create subgroups
        if (!$srv->create($composition->groups, $groupImgs)) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        } else {
            if ($newTransaction) {
                $pdo->commit();
            }
            return true;
        }
    }

    static function create($composition, $img, $groupImgs) {
        $pdo = PDOBuilder::getPDO();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        // Create product
        $id = ProductsService::create($composition, $img);
        if ($id === false) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        }
        // Create subgroups
        $srv = new SubgroupsService($id);
        if (!$srv->create($composition->groups, $groupImgs)) {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return false;
        } else {
            if ($newTransaction) {
                $pdo->commit();
            }
            return $id;
        }
    }

}

/** Service to manage subgroups. To be used only by CompositionsService. */
class SubgroupsService {

    private $compositionId;

    public function __construct($compositionId) {
        $this->compositionId = $compositionId;
    }

    protected function build($row) {
        $subgroup = SubGroup::__build($row["ID"], $row["COMPOSITION"],
                $row["NAME"], $row["DISPORDER"], $row["IMAGE"] !== null);
        $subgrpPrdSrv = new SubgroupProdsService($row['ID']);
        $subgroup->choices = $subgrpPrdSrv->getAll();
        return $subgroup;
    }

    /** Get all subgroups of the composition */
    public function getAll() {
        $subgrps = Array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM SUBGROUPS WHERE COMPOSITION = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $this->compositionId);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $subgroup = $this->build($row);
            $subgrps[] = $subgroup;
        }
        return $subgrps;
    }

    public function create($groups, $images) {
        $pdo = PDOBuilder::getPDO();
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }
        // Create subgroups
        $sql = "INSERT INTO SUBGROUPS (COMPOSITION, NAME, IMAGE, "
                ."DISPORDER) VALUES (:cmpId, :label, :img, :dispOrder)";
        $stmt = $pdo->prepare($sql);
        foreach($groups as $i => $group) {
            $stmt->bindParam(":cmpId", $this->compositionId);
            $stmt->bindParam(":label", $group->label);
            $stmt->bindParam(":dispOrder", $group->dispOrder);
            if ($images !== null) {
                $stmt->bindParam(":img", $images[$i]);
            } else {
                $stmt->bindValue(":img", null, \PDO::PARAM_NULL);
            }
            if ($stmt->execute() === false) {
                if ($newTransaction) {
                    $pdo->rollback();
                }
                return false;
            }
            $id = $pdo->lastInsertId("SUBGROUPS_ID_seq");
            $group->id = $id;
            // Insert subgroup prods
            foreach ($group->choices as $sgprd) {
                $srv = new SubgroupProdsService($id);
                $sgprd->groupId = $id;
                if (!$srv->create($sgprd)) {
                    if ($newTransaction) {
                        $pdo->rollback();
                    }
                    return false;
                }
            }
        }
        if ($newTransaction) {
            $pdo->commit();
        }
        return true;
    }

    public function deleteAll() {
        $pdo = PDOBuilder::getPDO();
        $sql = "DELETE FROM SUBGROUPS WHERE COMPOSITION = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $this->compositionId);
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
        }
    }
}

/** Service to manage SubgroupProds. Use only with CompositionsService. */
class SubgroupProdsService {

    private $groupId;

    public function __construct($groupId) {
        $this->groupId = $groupId;
    }

    protected function build($row) {
        return SubGroupProduct::__build($row["PRODUCT"],
            $row["SUBGROUP"], $row["DISPORDER"]);
    }

    public function create($model) {
        $pdo = PDOBuilder::getPDO();
        $sql = "INSERT INTO SUBGROUPS_PROD (SUBGROUP, PRODUCT, DISPORDER) "
                . "VALUES (:grp, :prd, :dispOrder)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":grp", $model->groupId);
        $stmt->bindParam(":prd", $model->productId);
        $stmt->bindParam(":dispOrder", $model->dispOrder);
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
        }
    }

    /** get all products containing in a subgroup
     * @id an id of subgroup
     * @return an array of SubGroupsProduct*/
    public function getAll() {
        $grp = Array();
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM SUBGROUPS_PROD WHERE "
                . "SUBGROUP = :id ORDER BY DISPORDER ASC, PRODUCT ASC"
        );
        $stmt->bindParam(":id", $this->groupId);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $product = $this->build($row);
            $grp[] = $product;
        }
        return $grp;
    }

    public function deleteAll() {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM SUBGROUPS_PROD WHERE "
                . "SUBGROUP = :id");
        $stmt->bindParam(":id", $this->groupId);
        if ($stmt->execute() !== false) {
            return true;
        } else {
            return false;
        }
    }
}
