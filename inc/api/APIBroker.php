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

/** Entry point for API calls */
class APIBroker {

    private $api;

    public function __construct($api) {
        $this->api = $api;
    }

    /** Run everything an return the result as APIResult. */
    public function run($action, $input) {
        // Check user login
        if (!api_user_login() && !is_user_logged_in()) {
            return APIResult::reject(APIError::$REJ_NOT_LOGGED);
        }
        // Check requested api, existing without illegal characters
        if ($this->api === null || $this->api == ""
                || strpos("..", $this->api) !== false
                || strpos("/", $this->api) !== false
                || !file_exists(PT::$ABSPATH . "/inc/data/api/"
                        . $this->api . ".php")) {
            return APIResult::reject(APIError::$REJ_WRONG_API);
        }
        // Run the api
        require_once(PT::$ABSPATH . "/inc/data/api/" . $this->api . ".php");
        $apiClass = new \ReflectionClass("\\Pasteque\\" . $this->api);
        $api = $apiClass->newInstance($action, $input);
        $api->run();
        return $api->getResult();
    }

    public function getAPIName() {
        return $this->api;
    }
}

?>
