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

/** Redirect page content to an action */
function redirectAction($path) {
    if (!file_exists(PT::$ABSPATH . "/" . $path . ".php")) {
        tpl_404();
        return;
    }
    require_once(PT::$ABSPATH . "/" . $path . ".php");
}

function redirect_report($module, $name) {
    if (!file_exists(PT::$ABSPATH . "/modules/" . $module . "/reports/" . $name . ".php")) {
        $ret = array("error" => "No such seport");
        echo json_encode($ret);
        return;
    }
    require_once(PT::$ABSPATH . "/modules/" . $module . "/reports/" . $name . ".php");
}

/** Redirect page to the given one in url */
function url_content() {
    if (!isset($_GET[PT::URL_ACTION_PARAM]) && !isset($_GET[PT::URL_REPORT_PARAM])) {
        $action = "home";
        redirectAction($action);
    } else if (isset($_GET[PT::URL_ACTION_PARAM])) {
        $action = $_GET[PT::URL_ACTION_PARAM];
        $action = str_replace("..", "", $action);
        redirectAction($action);
    } else if (isset($_GET[PT::URL_REPORT_PARAM])) {
        $report = $_GET[PT::URL_REPORT_PARAM];
        $report = str_replace("..", "", $report);
        redirectReport($report);
    }
    else {
        $action = "home";
    }
}
/** Redirect to the given report data */
function report_content($module, $name) {
    redirect_report($module, $name);
}
function print_content($module, $name) {
    if (!file_exists(PT::$ABSPATH . "/modules/" . $module . "/print/" . $name . ".php")) {
        $ret = array("error" => "No such print");
        echo json_encode($ret);
        return;
    }
    require_once(PT::$ABSPATH . "/modules/" . $module . "/print/" . $name . ".php");
}

function get_url_action($action) {
    return "./?" . PT::URL_ACTION_PARAM . "=" . $action;
}
function get_url_report($report) {
    return "./?" . PT::URL_REPORT_PARAM . "=" . $report;
}

function get_module_url_action($module, $action, $params = array()) {
    $url = "./?" . PT::URL_ACTION_PARAM . "=" . get_module_action($module, $action);
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
function get_module_report($module, $report) {
    return "modules/" . $module . "/reports/" . $report;
}

function get_report_url($module, $report_name, $type = "csv") {
    return "./?" . PT::URL_ACTION_PARAM . "=report&w=" . $type . "&m=" . $module
            . "&n=" . $report_name;
} 

function get_template_url() {
    global $config;
    return "templates/" . $config['template'] . "/";
}
