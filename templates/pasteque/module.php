<?php
//    Pastèque Web back office, Default template
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

function tpl_open() {
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php \pi18n("Pastèque"); ?></title>
	<link rel="stylesheet" type="text/css" href="templates/pasteque/style.css" />
</head>
<body>
<?php tpl_menu(); ?>
<div class="content">
<?php
}

function tpl_close() {
?>
</div>
</body>
</html><?php
}

function tpl_404() {
?>	<h1>ERREUR 404</h1>
<?php
}

function tpl_menu() {
    global $MENU;
    echo "<div id=\"menu-container\">\n";
    echo "\t<img src=\"" . get_template_url() . "img/logo.png" . "\" />\n";
    foreach ($MENU->getSections() as $section) {
        echo "\t<div class=\"menu-section\">\n";
        echo "\t\t<div class=\"menu-section-title\">";
        \pi18n($section->getName(), $section->getNameDomain());
        echo "</div>\n";
        echo "\t\t<ul class=\"menu\">\n";
        foreach ($section->getEntries() as $entry) {
            echo "\t\t\t<li>";
            if ($entry->getImg() !== NULL && $entry->getImg() != "") {
                echo "<img src=\"" . get_template_url() . "img/" . $entry->getImg() . "\" width=\"22\" height=\"22\" />";
            } else {
                echo "<img src=\"" . get_template_url() . "img/menu_default.png\" width=\"22\" height=\"22\" />";
            }
            echo "<a href=\"" . get_url_action($entry->getAction()) . "\">" . __($entry->getName(), $entry->getNameDomain()) . "</a></li>\n";
        }
        echo "\t\t</ul>\n";
        echo "\t</div>\n";
    }
    echo "</div>";
}

?>
