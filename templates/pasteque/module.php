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
	
	<script src="<?php echo get_template_url(); ?>/js/jquery_1.8.1.js"></script>
	<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script> -->

    <!-- fancybox -->
	<script type="text/javascript" src="<?php echo get_template_url(); ?>/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
    <script type="text/javascript" src="<?php echo get_template_url(); ?>/fancybox/jquery.easing-1.4.pack.js"></script>
    <script type="text/javascript" src="<?php echo get_template_url(); ?>/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>

    <!-- sticky nav -->
    <script>
    $(function() {

        // grab the initial top offset of the navigation
        var sticky_navigation_offset_top = $('#sticky_navigation').offset().top;

        // our function that decides weather the navigation bar should have "fixed" css position or not.
        var sticky_navigation = function(){
            var scroll_top = $(window).scrollTop(); // our current vertical position from the top

            // if we've scrolled more than the navigation, change its position to fixed to stick to top, otherwise change it back to relative
            if (scroll_top > sticky_navigation_offset_top) {
                $('#sticky_navigation').css({ 'position': 'fixed', 'top':0, 'left':0 });
            } else {
                $('#sticky_navigation').css({ 'position': 'relative' });
            }
        };

        // run our function on load
        sticky_navigation();

        // and run it again every time you scroll
        $(window).scroll(function() {
             sticky_navigation();
        });

        // NOT required:
        // for this demo disable all links that point to "#"
        $('a[href="#"]').click(function(event){
            event.preventDefault();
        });

    });
    </script>

    <!-- script scroll id -->
    <script language="javascript">
    $(document).ready(function(){
        $('a[href^="#"]').on('click',function (e) {
            e.preventDefault();

            var target = this.hash,
            $target = $(target);

            $('html, body').stop().animate({
                'scrollTop': $target.offset().top
            }, 900, 'swing', function () {
                window.location.hash = target;
            });
        });
    });
    </script>

    <!-- script adapt.js responsive -->
    <script language="javascript">
    // Edit to suit your needs.
    var ADAPT_CONFIG = {
      // Where is your CSS?
      path: '<?php echo get_template_url(); ?>/css/',

      // false = Only run once, when page first loads.
      // true = Change on window resize and page tilt.
      dynamic: true,

      // First range entry is the minimum.
      // Last range entry is the maximum.
      // Separate ranges by "to" keyword.
      range: [
        '0px    to 760px  = mobile.min.css',
        '760px  to 980px  = 720.min.css',
        '980px  to 1280px = 960.min.css',
        '1280px           = 1200.min.css'
      ]
    };


    </script>
    <script src="<?php echo get_template_url(); ?>/js/adapt.min.js"></script>

	<!-- menu mobile -->
    <link type="text/css" rel="stylesheet" href="<?php echo get_template_url(); ?>/css/jquery.mmenu.all.css" />
    <!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script> -->
    <script type="text/javascript" src="<?php echo get_template_url(); ?>/js/jquery.mmenu.min.all.js"></script>
    <script type="text/javascript">
        $(function() {
            $('nav#nav_main__mobile').mmenu();
        });
    </script>

	<link href="<?php echo get_template_url(); ?>/css/styles_admin.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="<?php echo get_template_url(); ?>/fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />

</head>
<body>

<!-- START tpl open -->
<div id="page" class="">

	<!-- start navigation mobile -->
	<?php tpl_menu_mobile() ?>
    <nav id="nav_main__mobile">
    	<?php tpl_menu(); ?>
    </nav>
    <!-- end navigation mobile -->


	<!-- start navigation PC -->
    <div id="nav_pc">

        <!-- start bande header -->
        <header>
            <div class="container_12">

                <!-- logo -->
                <div class="logo"><img src="<?php echo get_template_url(); ?>/imgs/logo_worldline.png" width="247" height="69" alt="Worldline E-payment services" /></div>

            </div>
        </header>
        <!-- end bande header -->
		
        <div id="sticky_navigation_wrapper">
            <div id="sticky_navigation">

				<!-- start navigation standart -->
				<nav id="nav_main">
					<div class="container_12">
						<?php tpl_menu(); ?>
                	</div>
                </nav>
                <!-- end navigation standart -->

        	</div>
        </div>

	</div>
    <!-- end navigation PC -->



	<!-- start content -->
	<div id="content" class="container_12">
	<!-- START tpl close -->
<?php
}

function tpl_close() {
?>
    </div>
    <!-- end content -->
    <div class="clear"></div>


    <!-- start bande footer -->
    <footer>
    	<div class="container_12">
    		<div class="grid_12">
			<div id="version">© Scil pour Worldline 2014 - <a href="" onclick="showAbout();return false;"><?php echo \i18n("About"); ?></a></div>
		</div>
    	</div>
    </footer>
    <div id="logout"><a href="/awl/?p=logout">Déconnexion</a></div>

</div>
<!-- END tpl close -->

<!-- <script src="<?php echo get_template_url(); ?>/js/jquery_1.8.1.js"></script> -->
<!-- <script src="http://davist11.github.io/jQuery-Stickem/jquery.js"></script>   -->
<script src="<?php echo get_template_url(); ?>/js/jquery.stickem.js"></script>
<script>
		$(document).ready(function() {
			$('.container_scroll').stickem();
		});
</script>


</body>
</html><?php
}

function tpl_404() {
?>	

<!-- start bloc titre -->
<div class="blc_ti">
<h1>ERREUR 404</h1>
</div>
<!-- end bloc titre -->

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

function tpl_menu_mobile() {
    global $MENU;
    echo '<div id="nav_mobile">
			<a href="#nav_main__mobile" class="bt_nav_mobile">Navigation</a>
        	<div class="logo_mobile"><img src="';
    echo get_template_url();
    echo '/imgs/logo_worldline_mobile.png" width="230" height="70" alt="Worldline E-payment services" /></div>
	</div>
	
	
	
	';
}

function tpl_menu() {

    global $MENU;

	
	// ----------------------> gérer l'affichage du menu reglage
    echo '
				<!-- start navigation standart -->



                        <ul id="nav_services">';
						
							// ----------------------> gérer les affichage de nom de class sur les boutons
							// ----------------------> gérer l'affichage de la class "activ" sur le li en cours de consultation pour afficher le sous menu
						
    foreach ($MENU->getSections() as $section) {
        echo "\t<li class=\"" . strtolower($section->getName());
        if ($section->isActive()) {
            echo " activ";
        }
        echo "\">\n";
        echo "\t\t<a class=\"" . strtolower($section->getName()) . "\"><span>";
        \pi18n($section->getName(), $section->getNameDomain());
        echo "</span></a>\n";
        echo "\t\t<div class=\"ssmenu\"><ul class=\"ul_ssmenu\">\n";
        foreach ($section->getEntries() as $entry) {
            echo "\t\t\t<li>";
            if ($entry->getImg() !== NULL && $entry->getImg() != "") {
                $img = get_template_url() . "img/" . $entry->getImg();
            } else {
                $img = get_template_url() . "img/menu_default.png";
            }
            // $style = "style=\"background-image:url('$img');\"";
            switch ($entry->getType()) {
            case MenuEntry::ACTION:
                $url = get_url_action($entry->getAction());
                break;
            case MenuEntry::REPORT:
                $url = get_report_url($entry->getNameDomain(),
                        $entry->getAction(), 'display');
                break;
            }
            if($entry->isActive())
                $activ=" activ";
            else
                $activ="";
            echo "<a class=\"" . strtolower($section->getName()) . "_"
                    . strtolower($entry->getActionName()) . "".$activ
                    . "\" $style href=\"" . $url . "\"><span>"
                    . __($entry->getName(), $entry->getNameDomain())
                    . "</span></a></li>\n";
        }
        echo "\t\t<li class=\"clear\"></li></ul></div>\n";
        echo "\t</li>\n";
    }
    echo '</ul>
                        <div class="clear"></div>


                <!-- end navigation standart -->
	';
}

function __tpl_report_title($report) {
    echo '
	<!-- start bloc titre -->
    <div class="blc_ti">
	<h1>' . $report->getTitle() . '</h1>
	';
}

function __tpl_report_input($report, $values) {

	// Export button
    echo '
	<ul class="bt_fonction">
    <li>
		<a class="bt_export transition btn" href="'
            . \Pasteque\get_report_url($report->getDomain(), $report->getId());
		foreach($report->getParams() as $param) {
			echo "&" . $param['param'] . "=" . $values[$param['param']];
		}
    	echo '">' . \i18n("Export") . '</a>
		
	</li></ul>
	</div>
    <!-- end bloc titre -->';
	
	

	// Input form
    echo "<div class=\"blc_content\"><form class=\"edit\" action=\"" . \Pasteque\get_current_url() . "\" "
            . "method=\"post\">";
    foreach($report->getParams() as $param) {
        $id = $param['param'];
        echo "<div class=\"row\">";
        echo "<label for=\"" . $id . "\">" . $param['label'] . "</label>";
        switch ($param['type']) {
        case DB::DATE:
            $value = \i18nDate($values[$id]);
            echo "<input type=\"text\" name=\"" . $id . "\" id=\"" . $id
                    . "\" class=\"dateinput\" value=\"" . $value . "\" />";
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
    echo "</form></div>\n";
}

function __tpl_report_header($report) {
    echo "<table class=\"report\" cellspacing=\"0\" cellpadding=\"0\">\n";
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
    echo "\t\t\t<th class=\"footer\" colspan=\"" . count($report->getHeaders()) . "\">" . \i18n("Subtotal") . "</th>\n";
    echo "\t\t</tr>\n";
    echo "\t\t<tr class=\"row-par\">\n";
    $subtotals = $run->getSubtotals();
    foreach ($report->getFields() as $field) {
        if (isset($subtotals[$field])) {
            echo "\t\t\t<td class=\"footer\">" . $subtotals[$field] . "</td>\n";
        } else {
            echo "\t\t\t<td class=\"footer\"></td>\n";
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
            echo "\t\t\t<td>" . $totals[$field] . "</td>\n";
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

function tpl_btn($class, $href, $label, $image_btn, $alt = NULL, $title = NULL) {
	$btn = '<a class="transition ' . $class . '" href="' . $href . '">'.
    $btn .= $label . "</a>";
    echo $btn;
}

function tpl_js_btn($class, $onclick, $label, $id = NULL, $image_btn = NULL, $alt = NULL, $title = NULL) {
    $btn = "<a class=\"but_2 " . $class . "\" onclick=\"javascript:" . $onclick . ";return false;\"";
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
