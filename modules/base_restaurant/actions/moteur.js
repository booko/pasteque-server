/* Used to manage floor or place */
var floor = new Array();

//test();
var EDIT = "EDIT";
var DEL = "DELETE";

/* structure of floor[FloorId]
 *      name, img,place[idPlace]
 *          name, x, y, idF
 *          name, x, y, idF
 *          etc…
 */

/** Add a floor on the array of floors 
 * return the floor created */
function addFloor(id, name, img) {
    // check if the floor id set yet in the floor
    if (getFloor(id) == null) {
        floor[id] = new Array();
        floor[id]["name"] = name;
        floor[id]["img"] = img;
        floor[id]["place"] = new Array();
        floor[id]["status"] = "";
        return floor[id];
    }
    return false;
}

/** Set etat of floor whith id if bool true set EDIT else set DEL */
function setEtatFloor(id, bool) {
    if (bool) {
        floor[id].status = EDIT;
    } else {
        floor[id].status = DEL;
    }
}

/** Set etat of floor whith id if bool true set EDIT else set DEL */
function setEtatPlace(idFloor, idPlace, bool) {
    if (bool) {
        floor[idFloor].place[idPlace].status = EDIT;
    } else {
        floor[idFloor].place[idPlace].status = DEL;
    }
}

/** Remove element whith id in floor */
function delFloor(id) {
    delete floor[id];
}

/** Edit floor id, edit name,
 * change img if img not empty */
function editFloor(id, name, img) {
    floor[id].name = name;
    if (img.length != 0) {
        floor[id].img = img;
    } 
}

/** Remove element whith placeId in the floor whith floorId */
function delPlace(floorId, placeId) {
    delete floor[floorId].place[placeId];
}

/** Add a place on the array of place 
 * return the place created */
function addPlace(id, name, x, y, floorId) {
    var tmpFloor = getFloor(floorId);
    if (tmpFloor && getPlace(id, floorId) == null) {
        var place = tmpFloor.place;
        place[id] = new Array();
        place[id]["name"] = name;
        place[id]["x"] = x;
        place[id]["y"] = y;
        place[id]["floorId"] = floorId;
        place[id]["status"] = "";
        return place[id];
    }
    return false;
}

/** Edit the table whith id */
function editPlace(id, name, x, y, floorId) {
    var place = getPlace(id, floorId);
    if (place) {
        place.name = name;
        place.x = x;
        place.y = y;
        return true;
    }
    return false;
}

/** Return the table whith id of the current floor */
function getPlace(id, floorId) {
    var tmpFloor = getFloor(floorId);
    if (tmpFloor && tmpFloor.place[id]) {
        return floor[floorId].place[id];
    }
    return null;
}
/** Return the floor whith id */
function getFloor(floorId) {
    if (floor[floorId]) {
        return floor[floorId];
    }
    return null;
}
/** Return all floor */
function getAllFloor() {
    return floor;
}

/** return true if the string is a correct name for the place
 * a correct name is a string not used for an other place of all floor*/
function checkNamePlace(str) {
    for (var idF in floor) {
        for (idP in floor[idF].place) {
                if (str == floor[idF].place[idP].name) {
                    return false;
            }
        }
    }
    return true;
}

/** return true if the string is a correct name for a floor
 * a correct name is a string not used for an other floor*/
function checkNameFloor(str) {
    for (var idF in floor) {
        if (str == floor[idF].name) {
            return false;
        }
    }
    return true;
}
