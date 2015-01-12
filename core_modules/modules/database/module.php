<?php
//    Pastèque Web back office, Database module manager
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

namespace DatabaseMM {
    require_once(dirname(__FILE__) . "/config.php");
    $data = NULL;
    function getModules($user_id) {
        global $data, $config;
        if ($data == NULL) {
            $pdo = \Pasteque\PDOBuilder::getPDO();
            $stmt = $pdo->prepare("SELECT modules FROM " . $config['table'] .
                " WHERE user_id = :id");
            $stmt->bindParam(":id", $user_id, \PDO::PARAM_INT);
            $stmt->execute();
            if ($row = $stmt->fetch()) {
                $modules = $row['MODULES'];
                $data = explode(",", $modules);
            }
        }
        foreach ($data as $module) {
            if ($module == "all") {
                return \Pasteque\Module::listAll();
            } else if ($module == "base") {
                return \Pasteque\Module::listBase();
            }
        }
        return $data;
    }
}

namespace Pasteque {
    function get_loaded_modules($user_id) {
        return \DatabaseMM\getModules($user_id);
    }
}
?>
