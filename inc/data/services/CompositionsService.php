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

class CompositionsService {

    private static function buildDBCompo($db_compo) {
        $compo = Composition::__build($db_compo['COMPOSITION']);
        return $compo;
    }

    private static function buildDBGrp($db_grp, $pdo) {
        $grp = CompositionGroup::__build($db_grp['ID'], $db_grp['NAME']);
        $stmt = $pdo->prepare("SELECT * FROM SUBGROUPS_PROD WHERE "
                . "SUBGROUP = :id ORDER BY PRODUCT ASC");
        $stmt->execute(array(':id' => $db_grp['ID']));
        while ($db_component = $stmt->fetch()) {
            $grp->addProduct($db_component['PRODUCT']);
        }
        return $grp;
    }

    static function getAll() {
        $compos = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM SUBGROUPS ORDER BY COMPOSITION, DISPORDER";
        $current_compo = NULL;
        $compo = NULL;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($db_grp = $stmt->fetch()) {
            if ($db_grp['COMPOSITION'] !== $current_compo) {
                if ($compo !== NULL) {
                    $compos[] = $compo;
                }
                $compo = CompositionsService::buildDBCompo($db_grp);
                $current_compo = $db_grp['COMPOSITION'];
            }
            $grp = CompositionsService::buildDBGrp($db_grp, $pdo);
            $compo->addGroup($grp);
        }
        if ($compo !== NULL) {
            $compos[] = $compo;
        }
        return $compos;
    }

}

?>
