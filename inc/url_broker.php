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

if (@constant("\Pasteque\ABSPATH") === NULL) {
    die();
}

function redirect($path) {
    if (!file_exists(ABSPATH . "/" . $path . ".php")) {
        tpl_404();
        return;
    }
    require_once(ABSPATH . "/" . $path . ".php");
}

/** Redirect page to the given one in url */
function url_content() {
    if (!isset($_GET[URL_ACTION_PARAM])) {
        $action = "home";
    } else {
        $action = $_GET[URL_ACTION_PARAM];
    }
    $action = str_replace("..", "", $action);
    redirect($action);
}

function get_url_action($action) {
    return "./?" . URL_ACTION_PARAM . "=" . $action;
}

function get_module_url_action($module, $action, $params = array()) {
    $url = "./?" . URL_ACTION_PARAM . "=" . get_module_action($module, $action);
    foreach ($params as $key => $value) {
        $url .= "&" . $key . "=" . $value;
    }
    return $url;
    // TODO: escape all of this
}

function get_current_url() {
    $url = "./?";
    foreach ($_GET as $key => $value) {
        $url .= $key . "=" . $value . "&";
    }
    return substr($url, 0, -1);
}

function get_module_action($module, $action) {
    return "modules/" . $module . "/actions/" . $action;
}

function get_template_url() {
    global $config;
    return "templates/" . $config['template'] . "/";
}
?>
