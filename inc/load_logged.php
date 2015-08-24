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

// Load template
if(!isset($config['template'])) {
    die("No template given");
}
$tmpl_file = PT::$ABSPATH . "/templates/" . $config['template'] . "/module.php";
if (!file_exists($tmpl_file) || !is_readable($tmpl_file)) {
    die("Template not found");
}
require_once($tmpl_file);
// Load modules
$modules = get_loaded_modules(get_user_id());
foreach ($modules as $module) {
    $module_file = PT::$ABSPATH . "/modules/" . $module . "/module.php";
    if (file_exists($module_file)) {
        require_once($module_file);
    }
}

call_hooks("module_load");

load_modules_i18n(detect_preferred_language());

?>
