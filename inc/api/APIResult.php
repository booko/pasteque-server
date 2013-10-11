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

/** Representation of the result of a API call */
class APIResult {

    /** Call went well until the end, data is the result of the code. */
    const STATUS_CALL_OK = "ok";
    /** Call is not well formed (error before executing service),
     * data is an APIError. */
    const STATUS_CALL_REJECTED = "rej";
    /** Call returned an error (error during service), data is an APIError. */
    const STATUS_CALL_ERROR = "err";

    public $status;
    public $content;

    private function __construct($status, $content) {
        $this->status = $status;
        $this->content = $content;
    }

    public static function success($result) {
        return new APIResult(APIResult::STATUS_CALL_OK, $result);
    }
    public static function reject($reason) {
        return new APIResult(APIResult::STATUS_CALL_REJECTED, $reason);
    }
    public static function fail($err_code) {
        return new APIResult(APIResult::STATUS_CALL_ERROR, $result);
    }

}

class APIError {

    public static $REJ_NOT_LOGGED;
    public static $REJ_NO_ACTION;
    public static $REJ_WRONG_API;
    public static $REJ_WRONG_PARAMS;
    public static $ERR_GENERIC;
    public static function init() {
        APIError::$REJ_NOT_LOGGED = new APIError("Not logged");
        APIError::$REJ_NO_ACTION = new APIError("No action");
        APIError::$REJ_WRONG_API = new APIError("Wrong API");
        APIError::$REJ_WRONG_PARAMS = new APIError("Wrong parameters");
        APIError::$ERR_GENERIC = new APIError("Server error");
    }

    public $code;
    public $params;

    public function __construct($code, $params = null) {
        $this->code = $code;
        if ($params === null) {
            $this->params = array();
        }
        $this->params = $params;
    }

}
APIError::init();

?>
