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

class Installer {
    const DB_NOT_INSTALLED = 0;
    const OK = 1;
    const NEED_DB_UPGRADE = 2;
    const NEED_DB_DOWNGRADE = 3;

    static function install($country) {
        $pdo = PDOBuilder::getPDO();
        $file = ABSPATH . "/install/database/create.sql";
        $pdo->query(\file_get_contents($file));
        // Load country data
        if ($country !== null) {
            $cfile = ABSPATH . "/install/database/data_" . $country . ".sql";
            $pdo->query(\file_get_contents($cfile));
        }
    }

    /** Upgrade database from given version to the latest. */
    static function upgrade($country, $version = null) {
        if ($version === null) {
            $version = Installer::getVersion();
        }
        while ($version != DB_VERSION) {
            $pdo = PDOBuilder::getPDO();
            // Load generic sql update for current version
            $file = ABSPATH . "/install/database/upgrade-" . $version . ".sql";
            $pdo->query(\file_get_contents($file));
            // Check for localized update data for current version
            $file = ABSPATH . "/install/database/upgrade-" . $version . "_" . $country . ".sql";
            if (\file_exists($file)) {
                $pdo->query(\file_get_contents($file));
            }
            $version++;
        }
    }

    static function getVersion() {
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT VERSION FROM APPLICATIONS WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id", "pasteque");
        $stmt->execute();
        $data = $stmt->fetch();
        if ($data !== false) {
            return $data['VERSION'];
        } else {
            return null;
        }
    }

    static function checkVersion($dbVer = null) {
        if ($dbVer === null) {
            $dbVer = Installer::getVersion();
        }
        if ($dbVer !== null) {
            if (intval($dbVer) < intval(DB_VERSION)) {
                return Installer::NEED_DB_UPGRADE;
            } else if (intval($dbVer) > intval(DB_VERSION)) {
                return Installer::NEED_DB_DOWNGRADE;
            } else {
                return Installer::OK;
            }
        } else {
            return Installer::DB_NOT_INSTALLED;
        }
    }
}
