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
    showPopup('<p><img id="about-icon" src="<?php echo get_template_url() . "img/icon.png"; ?>" /> Pastèque <?php echo \i18n("Version.Codename"); ?> <?php echo VERSION ?></p>');
}