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

class TariffAreasService extends AbstractService {

    protected static $dbTable = "TARIFFAREAS";
    protected static $dbIdField = "ID";
    protected static $fieldMapping = array(
            "ID" => "id",
            "NAME" => "label",
            "TARIFFORDER" => "dispOrder"
    );

    protected function build($dbArea, $pdo = null) {
        $area = TariffArea::__build($dbArea['ID'], $dbArea['NAME'], $dbArea['TARIFFORDER']);
        $stmt = $pdo->prepare("SELECT * FROM TARIFFAREAS_PROD "
                . "WHERE TARIFFID = :id");
        $stmt->bindParam(":id", $area->id);
        $stmt->execute();
        while ($dbPrice = $stmt->fetch()) {
            $area->addPrice($dbPrice['PRODUCTID'],
                    floatval($dbPrice['PRICESELL']));
        }
        return $area;
    }

    public function getAll() {
        $areas = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM TARIFFAREAS ORDER BY TARIFFORDER";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($dbArea = $stmt->fetch()) {
            $area = $this->build($dbArea, $pdo);
            $areas[] = $area;
        }
        return $areas;
    }

    private function insertAreaPrices($id, $area) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO TARIFFAREAS_PROD "
                . "(TARIFFID, PRODUCTID, PRICESELL) "
                . "VALUES (:id, :pid, :price)");
        foreach ($area->getPrices() as $price) {
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":pid", $price->productId);
            $stmt->bindParam(":price", $price->price);
            $stmt->execute();
        }
    }

    public function create($area) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO TARIFFAREAS "
                . "(NAME, TARIFFORDER) "
                . "VALUES (:label, :dispOrder)");
        $stmt->bindParam(":label", $area->label);
        $stmt->bindParam(":dispOrder", $area->dispOrder);
        if ($stmt->execute() !== false) {
            $id = $pdo->lastInsertId();
        } else {
            return false;
        }
        $this->insertAreaPrices($id, $area);
        return $id;
    }

    public function update($area) {
        if ($area->id == null) {
            return false;
        }
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("UPDATE TARIFFAREAS SET NAME = :label, "
                . "TARIFFORDER = :dispOrder WHERE ID = :id");
        $stmt->bindParam(":id", $area->id);
        $stmt->bindParam(":label", $area->label);
        $stmt->bindParam(":dispOrder", $area->dispOrder);
        if ($stmt->execute() === false) {
            return false;
        }
        $del = $pdo->prepare("DELETE FROM TARIFFAREAS_PROD "
                . "WHERE TARIFFID = :id");
        $del->bindParam(":id", $area->id);
        $del->execute();
        $this->insertAreaPrices($area->id, $area);
        return true;
    }

    public function delete($areaId) {
        $pdo = PDOBuilder::getPDO();
        $del = $pdo->prepare("DELETE FROM TARIFFAREAS_PROD "
                . "WHERE TARIFFID = :id");
        $del->bindParam(":id", $areaId);
        $del->execute();
        return parent::delete($areaId);
    }
}