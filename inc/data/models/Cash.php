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

class Cash {

    public $id;
    public $host;
    public $sequence;
    /** Open date as timestamp, may be null */
    public $startDate;
    /** Close date as timestamp, may be null */
    public $endDate;

    static function __build($id, $host, $sequence, $startDate, $endDate) {
        $cash = new Cash($host, $sequence, $startDate, $endDate);
        $cash->id = $id;
        return $cash;
    }

    function __construct($host, $sequence, $startDate, $endDate) {
        $this->host = $host;
        $this->sequence = $sequence;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    function isClosed() {
        return $this->endDate != null;
    }

    function isOpened() {
        return $this->startDate != null;
    }
}

?>
