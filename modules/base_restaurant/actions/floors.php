<?php
//    Pastèque Web back office, Products module
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

namespace BaseRestaurant;

require_once('maj.php');
?>

<h1><?php \pi18n("Floors configuration", PLUGIN_NAME);?></h1>

<form id="formUp" action="javascript:editFloorName()">
    <select id="listFloor" onchange="showFloor()">
    </select>
    <input type="text" placeholder="<?php pi18n("Rename floor", PLUGIN_NAME);?>"/>
    <input type="button" onclick="deleteFloor()" value="<?php pi18n("Delete floor",PLUGIN_NAME); ?>">
    <input type="button" id="btnPlace" value="<?php pi18n("Add a place", PLUGIN_NAME);?>" onclick="addPlaceInput()">
</form>

<div id="floorDivContainer">
</div>


<form id="input" method="post" action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'floors');?>">
    <input id="addFloorBtn" type="text"/>
    <input type="button" id="btnFloor" value="<?php \pi18n("Add a floor", PLUGIN_NAME);?>" onclick="newFloor()"/>

    <input id="inputData" name="inputData" type="text" style="display:none">
    <input type="submit" onclick="save()" value="<?php pi18n("Save changes", PLUGIN_NAME)?>">
</form>

<script src="<?php echo \Pasteque\get_module_action(PLUGIN_NAME, "jquery-ui-1.10.3.custom.js")?>" type="text/javascript"></script>
<script src="<?php echo \Pasteque\get_module_action(PLUGIN_NAME, "moteur.js")?>" type="text/javascript"></script>
<script src="<?php echo \Pasteque\get_module_action(PLUGIN_NAME, "graph.js")?>" type="text/javascript"></script>

<script type="text/javascript">
</script>

<?php
// insert all floors and all places:
echo "<script type='text/javascript'>";
$floors = \Pasteque\PlacesService::getAllFloors();
foreach ($floors as $floor) {
    echo "addFloorData('" . $floor->id . "', '" . $floor->name . "', '"
            . $floor->image . "');\n";
    $places = \Pasteque\PlacesService::getAllPlacesByFloorId($floor->id);
    foreach ($places as $place) {
        echo "addPlaceData('" . $place->id . "', '" . $place->name
                . "', '" . $place->x . "', '" . $place->y
                . "', '" . $floor->id . "');\n";
    }
}
// show the first floor
echo " showFloor('" . $floors[0]->id ."');";
echo "</script>";
?>