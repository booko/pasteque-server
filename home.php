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

require_once("lib/simplepie_1.3.1.compiled.php");
?>

<h1><?php \pi18n("Main title"); ?></h1>

<p><?php \pi18n("Introduction"); ?></p>

<h2><?php \pi18n("News"); ?></h2>
<?php

$url = "http://communaute.pasteque.coop/feed/";

$feed = new \SimplePie();
$feed->set_feed_url($url);
$feed->init();

$start  = 0;
$length = 3;

foreach($feed->get_items($start,$length) as $key=>$item) {
        echo "<div class=\"news_item\">\n";
        echo "<h3><a href=\"" . $item->get_link() . "\">" . $item->get_title() . "</a></h3>\n";
        echo " <p><small>".$item->get_date("d-m-Y")."</small><br>\n";
        echo $item->get_content()."</p>\n";
        echo "</div>\n";
}
?>
