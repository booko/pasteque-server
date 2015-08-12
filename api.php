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

// API is the entry point for all API calls.
namespace Pasteque;

require_once(__DIR__ . "/inc/constants.php");
PT::$ABSPATH = __DIR__; // Base path. Also to check if a call
                         // originates from api.php
// Load
require_once(PT::$ABSPATH . "/inc/load.php");
require_once(PT::$ABSPATH . "/inc/load_api.php");

if (isset($_GET[PT::URL_ACTION_PARAM])) {
    if($config['debug'] === true) {
        trigger_error(serialize($_GET));
    }
    $api = $_GET[PT::URL_ACTION_PARAM];
    $params = $_GET;
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
    } else {
        $action = null;
    }
} else {
    if (isset($_POST[PT::URL_ACTION_PARAM])) {
        if($config['debug'] === true) {
            trigger_error(serialize($_POST));
        }
        $api = $_POST[PT::URL_ACTION_PARAM];
        $params = $_POST;
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
        } else {
            $action = null;
        }
    } else {
        $api = null;
    }
}

$broker = new APIBroker($api);
$result = $broker->run($action, $params);

if ($api == "ImagesAPI") {
    // Special case of images api with binary data
    header("Cache-Control: max-age=864000");
    echo($result);
} else {
    header("Content type: application/json");
    echo json_encode($result);
}

?>
