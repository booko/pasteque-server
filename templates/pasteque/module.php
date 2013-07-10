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
	<link rel="stylesheet" type="text/css" href="templates/pasteque/catalog.css" />
	<link rel="stylesheet" type="text/css" href="templates/pasteque/stock.css" />	
	<script type="text/javascript" src="templates/pasteque/js/jquery-1.9.1.min.js"></script>
</head>
<body>
<?php tpl_menu(); ?>	<div id="header">
		
    	<div id="version"><?php echo \pi18n("Version.Codename") . " " . \Pasteque\VERSION; ?></div>
	</div>

<div class="content">
<?php
}

function tpl_close() {
?>
<div style="clear:both" />
</div>
</body>
</html><?php
}

function tpl_404() {
?>	<h1>ERREUR 404</h1>
<?php
}

function tpl_msg_box($info, $error) {
    if ($info !== NULL) {
        if (is_array($info)) {
            $mess_i = "";
            foreach ($info as $m_info) {
                $mess_i .= $m_info . "<br/>";
            }
            $info = $mess_i;
        }
        if ($info != "") {
            echo "<div class=\"message\">" . $info . "</div>\n";
        }
    }
    if ($error !== NULL) {
        if (is_array($error)) {
            $mess_e = "";
            foreach ($error as $m_err) {
                $mess_e .= $m_err . "<br/>";
            }
            $error = $mess_e;
        }
        if($error != "") {
            echo "<div class=\"error\">" . $error . "</div>\n";
        }
    }
}

function tpl_menu() {
    global $MENU;
    echo "<div id=\"menu-container\">\n";
    echo "<div id=\"logo\"><img src=\"" . get_template_url() . "img/logo.png\" /></div>\n";
    foreach ($MENU->getSections() as $section) {
        echo "\t<div class=\"menu-section\">\n";
        echo "\t\t<div class=\"menu-section-title\">";
        \pi18n($section->getName(), $section->getNameDomain());
        echo "</div>\n";
        echo "\t\t<ul class=\"menu\">\n";
        foreach ($section->getEntries() as $entry) {
            echo "\t\t\t<li>";
            if ($entry->getImg() !== NULL && $entry->getImg() != "") {
                $img = get_template_url() . "img/" . $entry->getImg();
            } else {
                $img = get_template_url() . "img/menu_default.png";
            }
            $style = "style=\"background-image:url('$img');\"";
            echo "<a $style href=\"" . get_url_action($entry->getAction()) . "\">" . __($entry->getName(), $entry->getNameDomain()) . "</a></li>\n";
        }
        echo "\t\t</ul>\n";
        echo "\t</div>\n";
    }
    echo "</div>";
}

function __tpl_report_header($report) {
    echo "<table cellspacing=\"0\" cellpadding=\"0\">\n";
    echo "\t<thead>\n";
    echo "\t\t<tr>\n";
    foreach ($report->headers as $header) {
        echo "\t\t\t<th>" . $header . "</th>\n";
    }
    echo "\t\t</tr>\n";
    echo "\t<thead>\n";
    echo "\t<tbody>\n";
}
function __tpl_report_footer($report) {
    echo "\t</tbody>\n";
    echo "</table>\n";
}
function __tpl_report_line($report, $line, $par) {
    echo "\t\t<tr class=\"row-" . ($par ? 'par' : 'odd') . "\">\n";
    foreach ($report->fields as $field) {
        if(isset($line[$field])) {
            echo "\t\t\t<td>" . $line[$field] . "</td>\n";
        } else {
            echo "\t\t\t<td></td>\n";
        }
    }
    echo "\t\t</tr>\n";
}
function __tpl_group_header($report, $run) {
    echo "<h2>" . $run->getCurrentGroup() . "</h2>\n";
}
function __tpl_group_footer($report, $run) {
    echo "\t\t<tr>\n";
    echo "\t\t\t<td colspan=\"" . count($report->headers) . "\">" . \i18n("Subtotal") . "</td>\n";
    echo "\t\t</tr>\n";
    echo "\t\t<tr class=\"row-par\">\n";
    foreach ($report->fields as $field) {
        if (isset($run->subtotals[$field])) {
            echo "\t\t\t<td>" . $run->subtotals[$field] . "</td>\n";
        } else {
            echo "\t\t\t<td></td>\n";
        }
    }
    echo "\t\t</tr>\n";
}
function __tpl_report_totals($report, $run) {
    echo "<h2>" . \i18n("Total") . "</h2>\n";
    __tpl_report_header($report);
    echo "\t\t<tr class=\"row-par\">\n";
    foreach ($report->fields as $field) {
        if (isset($run->totals[$field])) {
            echo "\t\t\t<td>" . $run->totals[$field] . "</td>\n";
        } else {
            echo "\t\t\t<td></td>\n";
        }
    }
    echo "\t\t</tr>\n";
    __tpl_report_footer($report);
}
/** Display a report.
 * @param $report Report data, as given by get_report
 */
function tpl_report($report) {
    $run = $report->run();
    $par = FALSE;
    if ($run->isEmpty()) {
        echo "<p>" . \i18n("No result") . "</p>";
        return;
    }
    if (!$report->isGrouping()) {
        __tpl_report_header($report);
        while ($row = $run->fetch()) {
            $par = !$par;
            __tpl_report_line($report, $row, $par);
        }
        __tpl_report_footer($report);
    } else {
        while ($row = $run->fetch()) {
            $par = !$par;
            if ($run->isGroupEnd()) {
                if ($report->hasSubtotals()) {
                    __tpl_group_footer($report, $run);
                }
                __tpl_report_footer($report);
            }
            if ($run->isGroupStart()) {
                __tpl_group_header($report, $run);
                __tpl_report_header($report);
            }
            __tpl_report_line($report, $row, $par);
        }
        if ($report->hasSubtotals()) {
            __tpl_group_footer($report, $run);
        }
        __tpl_report_footer($report);
        if ($report->hasTotals()) {
            __tpl_report_totals($report, $run);
        }
    }
}


function tpl_btn($class, $href, $label, $image_btn, $alt = NULL, $title = NULL) {
    $btn = "<a class=" . $class . " href=\"" . $href . "\">"
            . "<img src=\"" .\Pasteque\get_template_url() . "" . $image_btn . "\"";
    if (isset($alt)) {
         $btn .= " alt =\"" . $alt . "\"";
    }
    if (isset($title)) {
        $btn .= " title =\"" . $title . "\"";
    }
    $btn .= "/>";
    $btn .= $label . "</a>";
    echo $btn;
}
?>

