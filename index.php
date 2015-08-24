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
// Index is the entry point for everything.
namespace Pasteque;

require_once(__DIR__ . "/inc/constants.php");
PT::$ABSPATH = __DIR__; // Base path. Also to check if a call
                         // originates from index.php
// Load
require_once(PT::$ABSPATH . "/inc/load.php");

function index_run() {
    tpl_open();
    url_content();
    tpl_close();
}

// Check user authentication
if (!is_user_logged_in()) {
    show_login_page();
} else {
    require_once(PT::$ABSPATH . "/inc/load_logged.php");
    // Check install if not trying to logout
    if (!(isset($_GET[PT::URL_ACTION_PARAM])
                    && $_GET[PT::URL_ACTION_PARAM] == "logout")) {
        require_once(PT::$ABSPATH . "/install.php");
    }
    if (isset($_GET[PT::URL_ACTION_PARAM])) {
        switch($_GET[PT::URL_ACTION_PARAM]) {
        case "img":
            require_once(PT::$ABSPATH . "/dbimg.php");
            break;
        case "report":
            require_once(PT::$ABSPATH . "/report.php");
            break;
        case "backup":
            require_once(PT::$ABSPATH . "/backup.php");
            break;
        case "print":
            require_once(PT::$ABSPATH . "/print.php");
            break;
        case "logout":
            logout();
            break;
        default:
            index_run();
            break;
        }
    } else {
        index_run();
    }
}
