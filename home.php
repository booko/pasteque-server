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

<div class="twitter-feed">
    <a class="twitter-timeline"  href="https://twitter.com/pastequepos" data-widget-id="584374065407885312">Tweets de @pastequepos</a>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</div>

<h1><?php \pi18n("Main title"); ?></h1>

<p><?php \pi18n("Introduction"); ?></p>

<h2><?php \pi18n("Download"); ?></h2>
    <p><?php \pi18n("Download instructions"); ?></p>

<h2><?php \pi18n("Documentation"); ?></h2>
    <p><?php \pi18n("Documentation instructions"); ?></p>
