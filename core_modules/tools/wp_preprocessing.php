<?php
//    Pastèque Web back office, WordPress ident module
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

// This file contains functions to neutralize some WordPress side effects
// on loading
namespace WordPress;

function loadWP($base_path) {
    // Save timezone
    $timezone = date_default_timezone_get();
    // Load WordPress
    if (substr($base_path, -1) == "/") {
        require_once($base_path . "wp-load.php");
    } else {
        require_once($base_path . "/wp-load.php");
    }
    // Restore timezone
    date_default_timezone_set($timezone);
    // Activate hooks
    setHooks();
}

function disableQuoteEscape() {
    // Dirty hack to disable WordPress magic_quotes
    if (!get_magic_quotes_gpc()) {
        $_POST = array_map('stripslashes_deep', $_POST);
        $_GET = array_map('stripslashes_deep', $_GET);
        $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
        $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    }
}

$hooked = false;
function setHooks() {
    global $hooked;
    if (!$hooked) {
        \Pasteque\hook("core_ready", "\WordPress\disableQuoteEscape");
    }
    $hooked = true;
}

?>
