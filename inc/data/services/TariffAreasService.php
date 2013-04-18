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

class TariffAreasService {

    private static function buildDBArea($db_area, $pdo) {
        $area = TariffArea::__build($db_area['ID'], $db_area['NAME']);
        $stmt = $pdo->prepare("SELECT * FROM TARIFFAREAS_PROD "
                . "WHERE TARIFFID = :id");
        $stmt->bindParam(":id", $area->id);
        $stmt->execute();
        while ($db_price = $stmt->fetch()) {
            $area->addPrice($db_price['PRODUCTID'],
                    floatval($db_price['PRICESELL']));
        }
        return $area;
    }

    private static function buildDBGrp($db_grp, $pdo) {
        $grp = CompositionGroup::__build($db_grp['ID'], $db_grp['NAME']);
        $stmt = $pdo->prepare("SELECT * FROM SUBGROUPS_PROD WHERE "
                . "SUBGROUP = :id");
        $stmt->execute(array(':id' => $db_grp['ID']));
        while ($db_component = $stmt->fetch()) {
            $grp->addProduct($db_component['PRODUCT']);
        }
        return $grp;
    }

    static function getAll() {
        $areas = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM TARIFFAREAS ORDER BY NAME";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($db_area = $stmt->fetch()) {
            $area = TariffAreasService::buildDBArea($db_area, $pdo);
            $areas[] = $area;
        }
        return $areas;
    }

}

?>
