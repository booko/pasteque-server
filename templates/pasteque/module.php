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

function tpl_open() {
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php \pi18n("Pastèque"); ?></title>
	<link rel="icon" type="image/png" href="templates/pt2.0/img/icon.png" />
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_url(); ?>/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_url(); ?>/catalog.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_url(); ?>/stock.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_url(); ?>/js/pepper-grinder/jquery-ui-1.10.4.custom.css" />
	<script type="text/javascript" src="<?php echo get_template_url(); ?>/js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="<?php echo get_template_url(); ?>/js/jquery-ui-1.10.4.custom.min.js"></script>
	<script type="text/javascript" src="<?php echo get_template_url(); ?>/js/jquery-tablesorter.min.js"></script>
	<script type="text/javascript" src="?<?php echo \Pasteque\PT::URL_ACTION_PARAM; ?>=img&w=js&id=js/pasteque.js.php"></script>
</head>
<body>
<div id="header">
	<div id="version"><a href="" onclick="showAbout();return false;"><?php echo \i18n("About"); ?></a></div>
</div>
<?php tpl_menu(); ?>

<div id="content">
<?php
}

function tpl_close() {
?>
	<div style="clear:both"></div>
</div>
<div id="footer"><?php \pi18n("Copyright"); ?></div>
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
    echo "<div id=\"logo\"><a href=\"" . get_url_action("home") . "\"><img src=\"" . get_template_url() . "img/logo.png\" /></a></div>\n";
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
            switch ($entry->getType()) {
            case MenuEntry::ACTION:
                $url = get_url_action($entry->getAction());
                break;
            case MenuEntry::REPORT:
                $url = get_report_url($entry->getNameDomain(),
                        $entry->getAction(), 'display');
                break;
            }
            echo "<a $style href=\"" . $url . "\">" . __($entry->getName(), $entry->getNameDomain()) . "</a></li>\n";
        }
        echo "\t\t</ul>\n";
        echo "\t</div>\n";
    }
    echo "</div>";
}

function __tpl_report_title($report) {
    echo "<h1>" . $report->getTitle() . "</h1>\n";
}

function __tpl_report_input($report, $values) {
    // Export button
    echo "<div id=\"btn\"><a class=\"btn\" href=\""
            . \Pasteque\get_report_url($report->getDomain(), $report->getId());
    foreach($report->getParams() as $param) {
        echo "&" . $param['param'] . "=" . $values[$param['param']];
    }
    echo "\">" . \i18n("Export") . "</a></div>\n";
    echo "<br />\n";
    if(is_array($report->getParams()) && sizeof($report->getParams()) > 0) {
        // Input form
        echo "<form class=\"edit\" action=\"" . \Pasteque\get_current_url() . "\" "
                . "method=\"post\">";
        foreach($report->getParams() as $param) {
            $id = $param['param'];
            echo "<div class=\"row\">";
            if($param['label'] != null && $param['label'] != "" && $param['type'] != "hidden")
                echo "<label for=\"" . $id . "\">" . $param['label'] . "</label>";
            switch ($param['type']) {
            case DB::DATE:
                $value = \i18nDate($values[$id]);
                echo "<input type=\"text\" name=\"" . $id . "\" id=\"" . $id
                        . "\" class=\"dateinput\" value=\"" . $value . "\" />";
                break;
            case 'hidden':
                $value = $values[$param['param']];
                echo "<input type=\"hidden\" name=\"" . $id . "\" id=\"" . $id
                        . "\" value=\"" . $value . "\" />";
                break;
            default:
                $value = $values[$param['param']];
                echo "<input type=\"text\" name=\"" . $id . "\" id=\"" . $id
                        . "\" value=\"" . $value . "\" />";
                break;
            }
            echo "</div>\n";
        }
        // Send
        echo "<div class=\"row actions\">" . \Pasteque\form_send() . "</div>\n";
        echo "</form>\n";
    }
}

function __tpl_chart($headers,$datasets) {
    echo "<script>\n";
    echo "\tvar data = {\n";
    echo "\t\tlabels: [";
    foreach ($headers as $header) {
        echo "\"".$header."\",";
    }
    echo "],\n";
    echo "\t\tdatasets: [\n";
    foreach ($datasets as $dataset) {
        echo "\t\t\t{\n";
        echo "\t\t\tlabel: \"".$dataset->title."\"\n";
        echo "\t\t\tdata:";
        for($i=0;$i<sizeof($dataset->data)-1;$i++) {
            echo $dataset->data[$i].",";
        }
        echo $dataset->data[sizeof($dataset->data)]."]\n";
        echo "\t\t\t},";
    }
    echo "};\n";
    echo "</script>";
}

function __tpl_report_header($report) {
    echo "<table id=\"".$report->getId()."\" class=\"report\" cellspacing=\"0\" cellpadding=\"0\">\n";
    echo "\t<thead>\n";
    echo "\t\t<tr>\n";
    foreach ($report->getHeaders() as $header) {
        echo "\t\t\t<th>" . $header . "</th>\n";
    }
    echo "\t\t</tr>\n";
    echo "\t<thead>\n";
    echo "\t<tbody>\n";
}
function __tpl_report_footer($report) {
    echo "\t</tbody>\n";
    echo "</table>\n";
    echo "<script>\$(function(){\$(\"#";
    echo $report->getId();
    echo "\").tablesorter();});</script>";
}
function __tpl_report_line($report, $line, $par) {
    echo "\t\t<tr class=\"row-" . ($par ? 'par' : 'odd') . "\">\n";
    foreach ($report->getFields() as $field) {
        if (isset($line[$field])) {
            echo "\t\t\t<td>" . $report->applyVisualFilter($field, $line)
                    . "</td>\n";
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
    echo "\t\t\t<td colspan=\"" . count($report->getHeaders()) . "\">" . \i18n("Subtotal") . "</td>\n";
    echo "\t\t</tr>\n";
    echo "\t\t<tr class=\"row-par\">\n";
    $subtotals = $run->getSubtotals();
    foreach ($report->getFields() as $field) {
        if (isset($subtotals[$field])) {
            echo "\t\t\t<td>" . $report->applyVisualFilter($field, $subtotals[$field]) . "</td>\n";
        } else {
            echo "\t\t\t<td></td>\n";
        }
    }
    echo "\t\t</tr>\n";
}

function __tpl_total_header($report, $run) {
    echo "<table class=\"report report-total\" cellspacing=\"0\" cellpadding=\"0\">\n";
    echo "\t<thead>\n";
    echo "\t\t<tr>\n";
    $totals = $report->getTotals();
    $headers = $report->getHeaders();
    $cmp = 0;
    foreach ($report->getFields() as $field) {
        if (isset($totals[$field])) {
            echo "\t\t\t<td>";
            if ($totals[$field] === \Pasteque\Report::TOTAL_AVG) {
                 echo \i18n("Average") . "<br/>";
            }
            echo $headers[$cmp]. "</td>\n";
        } else {
            echo "\t\t\t<td></td>\n";
        }
        $cmp++;
    }
    echo "\t\t</tr>\n";
    echo "\t<thead>\n";
    echo "\t<tbody>\n";
}

function __tpl_report_totals($report, $run) {
    echo "<h2>" . \i18n("Total") . "</h2>\n";
    __tpl_total_header($report, $run);
    echo "\t\t<tr class=\"row-par\">\n";
    $totals = $run->getTotals();
    foreach ($report->getFields() as $field) {
        if (isset($totals[$field])) {
            echo "\t\t\t<td>" . $report->applyVisualFilter($field, $totals[$field]) . "</td>\n";
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
    // Read values
    $values = array();
    foreach ($report->getParams() as $param) {
        $id = $param['param'];
        if (isset($_POST[$id]) || isset($_GET[$id])) {
            if (isset($_POST[$id])) {
                $val = $_POST[$id];
            } else {
                $val = $_GET[$id];
            }
            $db = DB::get();
            switch ($param['type']) {
            case DB::DATE:
                // Revert the i18n input to timestamp
                $values[$id] = \i18nRevDate($val);
                break;
            default:
                $values[$id] = $val;
                break;
            }
        } else {
            $default = $report->getDefault($id);
            if ($default !== null) {
                $values[$id] = $default;
            } else {
                // TODO: error
            }
        }
    }
    // Display
    __tpl_report_title($report);
    __tpl_report_input($report, $values);
    $run = $report->run($values);
    $par = FALSE;
    if ($run->isEmpty()) {
        echo "<div class=\"information\">" . \i18n("No result") . "</div>";
        return;
    }
    if (!$report->isGrouping()) {
        __tpl_report_header($report);
        while ($row = $run->fetch()) {
            $par = !$par;
            __tpl_report_line($report, $row, $par);
        }
        __tpl_report_footer($report);
        if ($report->hasTotals()) {
            __tpl_report_totals($report, $run);
        }
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

/** Display a chart.
 * @param $chart chart data, as given by get_chart
 */
function tpl_chart($chart) {
    // Read values
    $values = array();
    foreach ($chart->getParams() as $param) {
        $id = $param['param'];
        if (isset($_POST[$id]) || isset($_GET[$id])) {
            if (isset($_POST[$id])) {
                $val = $_POST[$id];
            } else {
                $val = $_GET[$id];
            }
            $db = DB::get();
            switch ($param['type']) {
            case DB::DATE:
                // Revert the i18n input to timestamp
                $values[$id] = \i18nRevDate($val);
                break;
            default:
                $values[$id] = $val;
                break;
            }
        } else {
            $default = $chart->getDefault($id);
            if ($default !== null) {
                $values[$id] = $default;
            } else {
                // TODO: error
            }
        }
    }
    // Display
    __tpl_chart_title($chart);
    __tpl_chart_input($chart, $values);
    $run = $chart->run($values);
    $par = FALSE;
    if ($run->isEmpty()) {
        echo "<div class=\"information\">" . \i18n("No result") . "</div>";
        return;
    }
    __tpl_chart($chart->getHeaders(),$chart->getDatasets());
}

function __tpl_pagination_url($range,$start=0) {
    $url = \Pasteque\get_current_url();
    $url = preg_replace("/start=(\d+)/","start=".$start,$url);
    $url = preg_replace("/offset=(\d+)/","range=".$range,$url);
    if($url == \Pasteque\get_current_url()) {
        $url .= "&start=".$start."&range=".$range;
    }
    return $url;
}

function tpl_pagination($total,$range,$start=0) {
    if($range>=$total) {
        return;
    }
    echo "<div class=\"pagination\">";
    echo "<a href=\"".\Pasteque\get_current_url()."&range=all\">".\i18n('all')."</a>";
    if(isset($_GET["start"]) && $_GET["start"] != 0) {
        $url = __tpl_pagination_url($range,$_GET["start"]-$range);
        echo "<a class=\"prev_page\" href=\"".$url."\">«</a>";
    }
    for($i=0;$i<ceil($total/$range);$i++) {
        echo "<a";
        if($i*$range== $_GET["start"]) {
            echo " class=\"current_page\"";
        }
        $url = __tpl_pagination_url($range,$i*$range);
        echo " href=\"".$url."\">".$i."</a>";
    }
    if(isset($_GET["start"]) && $_GET["start"] < ($total-$range)) {
        $url = __tpl_pagination_url($range,$_GET["start"]+$range);
        echo "<a class=\"next_page\" href=\"".$url."\">»</a>";
    }
    echo "</div>\n";
}

function tpl_btn($class, $href, $label, $image_btn, $alt = NULL, $title = NULL, $confirm = false) {
    $btn = "<a class=\"btn " . $class . "\" href=\"" . $href . "\"";
    if($confirm) {
             $btn .= " onclick=\"return confirm('" . \i18n('confirm') ."');return false;\"";
    }
    $btn .= ">";
    $btn .= "<img src=\"" .\Pasteque\get_template_url() . "" . $image_btn . "\"";
    if (isset($alt)) {
         $btn .= " alt =\"" . $alt . "\"";
    }
    if (isset($title)) {
        $btn .= " title =\"" . $title . "\"";
    }
    $btn .= "/>&nbsp;";
    $btn .= $label . "</a>";
    echo $btn;
}

function tpl_js_btn($class, $onclick, $label, $id = NULL, $image_btn = NULL, $alt = NULL, $title = NULL) {
    $btn = "<a class=\"" . $class . "\" onclick=\"javascript:" . $onclick . ";return false;\"";
    if (isset($id)) {
        $btn .= "id=\"" . $id . "\" ";
    }
    $btn .= ">";
    if (isset($image_btn)) {
        $btn .= "<img src=\"" .\Pasteque\get_template_url() . "img/" . $image_btn . "\"";

        if (isset($alt)) {
            $btn .= " alt =\"" . $alt . "\"";
        }
        if (isset($title)) {
            $btn .= " title =\"" . $title . "\"";
        }
        $btn .= "/>";
    }
    $btn .= $label . "</a>";
    echo $btn;
}

function tpl_form($type,$key,$data) {
    $form = "<form action=\"".\Pasteque\url_content()."\" method=\"get\">\n";
    switch($type) {
        case 'select':
            $form .= "<select name=\"".$key."\" onchange=\"this.form.submit();\">\n";
            foreach($data as $d) {
                switch($key) {
                    case 'category':
                        $form .= "\t<option value=\"".$d->id."\"";
                        if($_GET[$key] == $d->id) {
                            $form .= " selected";
                        }
                        $form .= ">".$d->label."</option>\n";
                        break;
                    case 'customer':
                        $form .= "\t<option value=\"".$d->id."\"";
                        if($_GET[$key] == $d->id) {
                            $form .= " selected";
                        }
                        $form .= ">".$d->dispName."</option>\n";
                        break;
                    default:
                        $form .= "\t<option value=\"".$d->id."\"";
                        if($_GET[$key] == $d->id) {
                            $form .= " selected";
                        }
                        $form .= ">".$d->label."</option>\n";
                        break;
                }
            }
            $form .= "</select>\n";
            break;
    }
    $form .= "<input type=\"hidden\" name=\"p\" value=\"".$_GET[PT::URL_ACTION_PARAM]."\" />\n";
    $form .= "</form>\n";
    echo $form;
}
