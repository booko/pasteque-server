<?php
//    Pastèque Web back office, Restaurant module
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

$DELETE = "DELETE";
$error_mess = array();

if (isset($_POST['inputData'])) {
    /* input-data is a string representing a array who contain an other array
     * all rows of the first array are separate by "|" the value of these rows
     * are separate by ";" the values are : status id; name; image; array
     * all rows of the second array are separate by "#" the values of these rows
     * are separate by "," the values are status, id, name, x, y, idFloor
     */

    // get the row floor
    $floors = split("\|", $_POST["inputData"]);
    for ($cmp = 0; $cmp < count($floors); $cmp++) {
        //get the value of row floor
        $floors[$cmp] = split("\;", $floors[$cmp]);
        //get the row place
        $floors[$cmp][4] = split("\#", $floors[$cmp][4]);
        //get the value of place
        for ($cmp2 = 0; $cmp2 < count($floors[$cmp][4]); $cmp2++) {
            $floors[$cmp][4][$cmp2] = split("\,", $floors[$cmp][4][$cmp2]);
        }
    }

    // traitement of the data:
    $keyFloor = array("status", "id", "name", "img", "place");
    for ($i = 0; $i < count($floors); $i++) {
        $data = setKey($keyFloor, $floors[$i]);
        // for delete image floor
        if ($data['img'] === "null") {
            $data['img'] = NULL;
        }

        $floor = \Pasteque\PlacesService::getFloor($data['id']);

        $newFloor = \Pasteque\Floor::__build($data['id'], $data['name'],
                $data['img']);

        // FLOOR EXIST idFloor >= 0 because there are not id negative in BDD
        if ($floor !== NULL) {
            // DELETE THE FLOOR
            if ($data['status'] == $DELETE) {
                // delete places contains in the floor:
                managePlace($data['place']);

                if (!\Pasteque\PlacesService::deleteFloor($data['id'])) {
                    $error_mess[] .= \i18n("Impossible to delete floor: %s.",
                            PLUGIN_NAME, $floor->label);
                }
                // UPDATE THE FLOOR
            } else {
                if (\Pasteque\PlacesService::updateFloor($newFloor)) {
                    managePlace($data['place']);
                } else {
                    $error_mess[] .= \i18n("Impossible to update floor: %s.",
                            PLUGIN_NAME, $newFloor->label);
                }
            }
            // CREATE FLOOR
        } else {
            $floorId = \Pasteque\PlacesService::createFloor($newFloor);
            if ($floorId != NULL) {
                managePlace($data['place'], $floorId);
            } else {
                $error_mess[] .= \i18n("Impossible to create floor: %s.",
                        PLUGIN_NAME, $newFloor->label);
            }
        }
    }
    \Pasteque\tpl_msg_box(NULL, $error_mess);
}

/** check if the place exist into base if the place exist:
 * delete the place if the status is DELETE update else;
 * if the place not exist create it */
function managePlace($arrayPlaces, $floorId = NULL) {
    $keyPlace = array("status", "id", "name", "x", "y", "idFloor");
    $error_mess = array();

    for ($i = 0; $i < count($arrayPlaces); $i++) {
        if (count($arrayPlaces[$i]) < 2) {
            return;
        }
        $data = setKey($keyPlace, $arrayPlaces[$i]);

        $place = \Pasteque\PlacesService::getPlace($data['id']);
        $placeId = $data['id'];

        $new_place = \Pasteque\Place::__build($placeId, $data['name'],
                $data['x'], $data['y'], $data['idFloor']);

        // PLACE EXIST
        if ($place) {
            // DELETE PLACE
            if ($data['status'] == "DELETE") {

                if (!\Pasteque\PlacesService::deletePlace($placeId)) {
                    $error_mess[] .= \i18n("Impossible to delete place: %s.",
                            PLUGIN_NAME, $data['name']);
                }
                // UPDATE PLACE
            } else {
                if (!\Pasteque\PlacesService::updatePlace($new_place)) {
                    $error_mess[] .= \i18n("Impossible to update place: %s.",
                            PLUGIN_NAME, $place->label);
                }
            }
            // CREATE PLACE
        } else {
            // if the id floor start with "-" use the param $floorId
            if ($data['idFloor']{0} == "-" ) {
                $new_place->floor = $floorId;
            }
            if (!\Pasteque\PlacesService::createPlace($new_place)) {
                $error_mess[] .= \i18n("Impossible to create place: %s.",
                        PLUGIN_NAME, $data['name']);
            }
        }
    }
    \Pasteque\tpl_msg_box(NULL, $error_mess);
}

/** return an associative array with key associated at the correct values
 * in $array*/
function setKey($key, $array) {
    $new_array = array_fill_keys($key, NULL);
    $cmp = 0;

    foreach ($key as $field) {
        $result = $array[$cmp];
        if (!is_array($result)) {
            $result = escapeshellcmd($result);
        }
        $new_array[$field] = $result;
        $cmp++;
    }
    return $new_array;
}

?>
