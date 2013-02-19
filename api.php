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

const ABSPATH = __DIR__; // Base path. Also to check if a call
                         // originates from api.php
// Load
require_once(ABSPATH . "/inc/load.php");

// Check user authentication
if (!is_user_logged_in()) {
    $ret = array("error" => "Not logged");
    return json_encode($ret);
} else {
    require_once(ABSPATH . "/inc/load_logged.php");
    $ret = NULL;
    if (!isset($_GET[URL_ACTION_PARAM]) || !isset($_GET[URL_API_PARAM])) {
        $ret = array("error" => "No such API");
        echo json_encode($ret);
        return;
    } else {
        $action = $_GET[URL_ACTION_PARAM];
        $modelName = $_GET[URL_API_PARAM];
        $def = ModelFactory::get($modelName);
        if ($def == NULL) {
            $ret = array("error" => "No such API");
            echo json_encode($ret);
            return;
        }
        switch ($action) {
        case 'search':
            $data = ModelService::search($modelName);
            $ret = $data->fetchAll();
            break;
        case 'get':
            if (!isset($_GET['id'])) {
                $ret = FALSE;
                break;
            }
            $data = ModelService::get($modelName, $_GET['id']);
            if (is_array($_GET['id'])) {
                $ret = $data->fetchAll();
            } else {
                $ret = $data;
            }
            break;
        case 'create':
            if (!$def->checkForm($_GET)) {
                $ret = FALSE;
                break;
            }
            $ret = ModelService::create($modelName, $_GET);
            break;
        case 'update':
            if (!$def->checkForm($_GET) || !isset($_GET['id'])) {
                $ret = FALSE;
                break;
            }
            $ret = ModelService::update($modelName, $_GET);
            break;
        case 'delete':
            if (!isset($_GET['id'])) {
                $ret = FALSE;
                break;
            }
            $ret = ModelService::delete($modelName, $_GET['id']);
            break;
        default:
            $ret = array("error" => "No such API");
        }
    }
    echo json_encode($ret);
}
