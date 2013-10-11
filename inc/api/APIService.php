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

abstract class APIService {

    protected $action;
    protected $params;
    protected $result;

    public function __construct($action, $params) {
        $this->action = $action;
        $this->params = $params;
    }

    /** Set the result to a reject (should be called in check()). */
    protected function reject($reason) {
        $this->result = APIResult::reject($reason);
    }
    /** Set the result to a failure (should be called in run()). */
    protected function fail($reason) {
        $this->result = APIResult::error($reason);
    }
    /** Set the result to a succes (should be called in run())). */
    protected function succeed($result) {
        $this->result = APIResult::success($result);
    }

    /** Check before runnig.
     * @return True if inputs are OK, false otherwise. */
    protected abstract function check();

    /** Run the service and set result. */
    protected abstract function proceed();

    public function run() {
        if (!$this->check()) {
            $this->reject(APIError::$REJ_WRONG_PARAMS);
        } else {
            $this->proceed();
        }
    }
    /** Get the final result. */
    public function getResult() {
        return $this->result;
    }
}
