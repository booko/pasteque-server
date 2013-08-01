<?php
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
                            PLUGIN_NAME, $floor->name);
                }
                // UPDATE THE FLOOR
            } else {
                if (\Pasteque\PlacesService::updateFloor($newFloor)) {
                    managePlace($data['place']);
                } else {
                    $error_mess[] .= \i18n("Impossible to update floor: %s.",
                            PLUGIN_NAME, $newFloor->name);
                }
            }
            // CREATE FLOOR
        } else {
            $floorId = \Pasteque\PlacesService::createFloor($newFloor);
            if ($floorId != NULL) {
                managePlace($data['place'], $floorId);
            } else {
                $error_mess[] .= \i18n("Impossible to create floor: %s.",
                        PLUGIN_NAME, $newFloor->name);
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
                            PLUGIN_NAME, $place->name);
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