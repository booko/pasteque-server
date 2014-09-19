<?php namespace Pasteque; ?>
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
function showPopup(content) {
    var popup = '<div id="popup-container"><div id="popup">' + content + '<p id="popup-close"><a class="btn" onclick="javascript:closePopup();return false;"><?php echo \i18n("Close"); ?></a></div></div>';
    $("body").append(popup);
}

function closePopup() {
	$("#popup-container").remove();
}

function showAbout() {
    showPopup('<p><img id="about-icon" src="<?php echo get_template_url() . "img/icon.png"; ?>" /> Pastèque <?php echo \i18n("Version.Codename"); ?> <?php echo PT::VERSION ?></p>');
}

$(function() {
<?php
$dateFormat = i18n("date");
$jsDateFormat = str_replace(array("%Y", "%m", "%d"),
        array("yy", "mm", "dd"),$dateFormat);
?>
    $( ".dateinput" ).datepicker({ dateFormat: "<?php echo $jsDateFormat; ?>", buttonImage: "<?php echo get_template_url(); ?>/img/calendar.png", showOn: "both" });
    $( ".dateinput" ).datepicker({ dateFormat: "<?php echo $jsDateFormat; ?>", buttonImage: "<?php echo get_template_url(); ?>/img/calendar.png", showOn: "both" });
});

var menuHidden = false;
var menuWidth = "";
function toggleMenu() {
    if (menuHidden) {
        $("#menu-container").show();
        $("#content").css("margin-left", menuWidth + "px");
    } else {
        menuWidth = $("#menu-container").outerWidth(true);
        $("#menu-container").hide();
        $("#content").css("margin-left", "0px");
    }
    menuHidden = !menuHidden;
}
