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

    private static $error = Array();

    private static function buildDBCompo($db_compo) {
        $prd = ProductsService::get($db_compo['COMPOSITION']);
        $compo = Composition::__build($prd->id, $prd->reference, $prd->label, 
                $prd->priceSell, $prd->categoryId, $prd->dispOrder,
                $prd->taxCatId, $prd->visible, $prd->scaled, $prd->priceBuy,
                $prd->attributesSet, $prd->barcode, $prd->image,
                $prd->discountEnabled, $prd->discountRate);
        $subgrpSrv = new SubgroupsService($compo->id);
        $compo->groups = $subgrpSrv->getAll();
        return $compo;
    }

    /** Return an array of composition */
    static function getAll() {
        $compos = array();
        $pdo = PDOBuilder::getPDO();
        $comp = null;
        $current_compo = null;
        $sql = "SELECT * FROM SUBGROUPS GROUP BY COMPOSITION";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        while ($db_grp = $stmt->fetch()) {
            $comp = CompositionsService::buildDBCompo($db_grp);
            $compos[] = $comp;
        }
        return $compos;
    }

    static function get($id) {
        $prd = \Pasteque\ProductsService::get($id);
        if (!$prd) {
            return NULL;
        }
        $compo = Composition::__build($prd->id, $prd->reference, $prd->label,
                $prd->priceSell, $prd->categoryId, $prd->dispOrder,
                $prd->taxCatId, $prd->visible, false, $prd->priceBuy,
                NULL, $prd->barcode, $prd->image,
                $prd->discountEnabled, $prd->discountRate);
        $subgrpSrv = new SubGroupsService($compo->id);
        $compo->groups = $subgrpSrv->getAll();
        return $compo;
    }

    static function delete($id) {
        $subgrpSrv = new SubgroupsService($id);
        $subgroups = $subgrpSrv->getAll();
        foreach($subgroups as $subgroup) {
            if (!$subgrpSrv->delete($subgroup->id)) {
                return false;
            }
        }
        return ProductsService::delete($id);
    }

    static function maj($array) {
        $error = Array(); // clear error
        $pdo = PDOBuilder::getPDO();
        if (count($array) == 0) {
            $error[] = Array("ERR_COMPOSITION_UNDEFINED", "");
            return NULL;
        }
        $newTransaction = !$pdo->inTransaction();
        if ($newTransaction) {
            $pdo->beginTransaction();
        }   
        $compo = CompositionsService::manageCompo($array);
        if ($compo) {
            if ($newTransaction) {
                $pdo->commit();
            }
            return $compo;
        } else {
            if ($newTransaction) {
                $pdo->rollback();
            }
            return NULL;
        }
    }

    static function errorInfo() {
        return self::$error;
    }

    private static function manageCompo($data) {
        $image = NULL;
        if ($data->image !== "null") {
            $image = $data->image;
        }

        $category = CategoriesService::getByName($data->category);
        $tax_cat = TaxesService::get($data->tax_category);

        $compo = Composition::__build($data->id, $data->reference, $data->label,
                $data->price_sell, $category, $data->order,
                $tax_cat, $data->visible, FALSE, $data->price_buy,
                NULL, $data->barcode, $image, $data->discount_enabled,
                $data->discount_rate);
        switch($data->status) {
            case 'NEW':
                $compo->id = \Pasteque\ProductsService::create($compo);
                $data->id = $compo->id;
                if (!$compo->id) {
                    self::$error[] =  array("ERR_CREATE_COMPOSITION", $compo->label);
                    return NULL;
                }
                break;
            default:
                if (!\Pasteque\ProductsService::update($compo)) {
                    self::$error[] = Array("ERR_UPDATE_COMPOSITION", $compo->label);
                    return NULL;
                }
        }

        $compo->groups = CompositionsService::manageAllSG($compo->id, $data->subGroups);
        if ($compo->groups === NULL) {
            return NULL;
        }
        return $compo;
    }

    /** do manageSubgroup of all Subgroup contain in array
     * @param idCompo an id of composition
     * @dataSubGroup an jsonObject representing Subgroup
     * @return NULL if any error occured else return an array contain 
     * object Subgroups or empty if there are no subgroups */
    private static function manageAllSG($idCompo, $dataSubGroups) {
        $groups = Array();
        for ($i = 0; $i < count($dataSubGroups); $i++) {
            $subgroup = CompositionsService::manageSubgroups($dataSubGroups[$i], $idCompo);
            if ($subgroup === NULL) {
                return NULL;
            }
            if ($dataSubGroups[$i]->status != 'DEL') {
                $groups[] = $subgroup;
            }
        }
        return $groups;
    }

    /** Create/Update/Delet Subgroups into BDD
     * if any error occured return NULL and
     * @subGroup an array data  representing the subgroup
     * @idCompo l'id of the composition
     * @return an object SubGroups */
    private static function manageSubgroups($dataSG, $idCompo) {
        $image = NULL;
        $subgrpSrv = new SubgroupsService($idCompo);
        if ($dataSG->image !== "null") {
            $image = $dataSG->image;
        }
        $subgroup = \Pasteque\SubGroups::__build($dataSG->id, $idCompo,
                $dataSG->name, $dataSG->dispOrder, Array(), $image);
        switch ($dataSG->status) {
            case 'DEL':
                if (count($dataSG->product) > 0) {
                    if (CompositionsService::manageAllSGProducts(
                            $subgroup->id, $dataSG->product) === NULL) {
                        return NULL;
                    }
                }
                if (!$subgrpSrv->delete($subgroup->id)) {
                    self::$error[] = array("ERR_DELETE_SUBGROUP %s", $subgroup->label);
                    return NULL;
                }
                break;
            case 'NEW':
                $subgroup->id = $subgrpSrv->create($subgroup);
                if (!$dataSG->id) {
                    self::$error[] = array("ERR_CREATE_SUBGROUP", $subgroup->name);
                    return NULL;
                }
                break;
            default:
                if (!$subgrpSrv->update($subgroup)) {
                    self::$error[] =  array("ERR_UPDATE_SUBGROUP", $subgroup->name);
                    return false;
                }
        }
        if (count($dataSG->product) > 0) {
            $subgroup->groups = CompositionsService::manageAllSGProducts($subgroup->id, $dataSG->product);

            if ($subgroup->groups === NULL) {
                return NULL;
            }
        }
        return $subgroup;
    }


    private static function manageAllSGProducts($idSubgroup, $products) {
        $groups = Array();
        foreach ($products as $product) {
            $prd = CompositionsService::manageSubgroups_prod($product, $idSubgroup);
            if ($prd === NULL) {
                return NULL;
            }
            if ($product->status != 'DEL') {
                $groups[] = $prd;
            }

        }
        return $groups;
    }

    /** delete or add product to the subgroup
     * @prod an array representing subgroup product
     * @idSubgroup the id of subgroup who contain products*/
    private static function manageSubgroups_prod($prod, $idSubgroup) {
        $subgrpPrdSrv = new SubgroupsProdsService($prod->id);
        $prd = SubGroupsProduct::__build(
                $prod->id, $idSubgroup, $prod->name,
                $prod->dispOrder);

        switch ($prod->status) {
            case 'DEL':
                if(!$subgrpPrdSrv->delete($prd->product)) {
                    self::$error[] =  array("ERR_DELETE_SUBGROUP_PROD", $prod->name);
                    return NULL;
                }
                break;
            case 'NEW':
               if (!$subgrpPrdSrv->create($prd)) {
                    self::$error[] =  array("ERR_CREATE_SUBGROUP_PROD", $prod->name);
                    return NULL;
                }
        }
        return $prd;
    }
}

class SubgroupsService extends AbstractService {

    protected static $dbTable = "SUBGROUPS";

    protected static $dbIdField = "ID";

    protected static $fieldMapping = array(
            "ID" => "id",
            "COMPOSITION" => "composition",
            "NAME" => "label",
            "IMAGE" => "image",
            "DISPORDER" => "dispOrder"
    );

    private $compositionId;

    public function __construct($compositionId) {
        $this->compositionId = $compositionId;
    }

    /** Construct an object SubGroup whith groups set*/
    protected function build($row, $pdo = null) {
            $subgroup = SubGroups::__build($row["ID"], $row["COMPOSITION"], $row["NAME"],
                    $row["DISPORDER"],NULL , $row["IMAGE"]);
            $subgrpPrdSrv = new SubgroupsProdsService($row['ID']);
            $subgroup->groups = $subgrpPrdSrv->getAll();
            return $subgroup;
    }

    /** get all subgroups of a composition
     * @id an id of composition
     * @return an array of SubGroups*/
    public function getAll() {
        $compo = Array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM SUBGROUPS WHERE COMPOSITION = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $this->compositionId, \PDO::PARAM_STR);

        $res = $stmt->execute();
        if ($res) {
            while ($db_component = $stmt->fetch()) {
                $subgroup = $this->get($db_component['ID']);
                $compo[] = $subgroup;
            }
        }
        return $compo;
    }
}

class SubgroupsProdsService extends AbstractService {

    protected static $dbTable = "SUBGROUPS_PROD";
    protected static $dbIdField = "PRODUCT";
    protected static $fieldMapping = array(
        "SUBGROUP" => "subgroup",
        "PRODUCT" => "product",
        "DISPORDER" => "dispOrder"
    );

    private $compositionId;

    public function __construct($compositionId) {
        $this->compositionId = $compositionId;
    }

    protected function build($row, $pdo = null) {
        $label = ProductsService::get($row['PRODUCT'])->label;
        return SubGroupsProduct::__build($row["PRODUCT"],
            $row["SUBGROUP"], $label, $row["DISPORDER"]);
    }

    public function create($model) {
        $dbData = static::unbuild($model);
        $pdo = PDOBuilder::getPDO();

        $dbFields = array_keys(static::$fieldMapping); // Copy
        $idIndex = array_search(static::$dbIdField, $dbFields);

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
            $stmt->bindValue(":" . $field, $dbData[$field]);
        }
        // RUN!
        if($stmt->execute()) { return true; } 
        return false;
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
        $stmt->bindParam(":id", $this->compositionId, \PDO::PARAM_STR);

        $stmt->execute();

        while ($db_component = $stmt->fetch()) {
            $product = $this->build($db_component);
            $grp[] = $product;
        }
        return $grp;
    }
}
?>
