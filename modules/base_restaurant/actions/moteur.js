var floor = new Array();
/*used for manage floor or place */

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
    //check if the floor id set yet in the floor
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

/** set etat of floor whith id if bool true set EDIT else set DEL*/
function setEtatFloor(id, bool) {
    if (bool) {
        floor[id].status = EDIT;
    } else {
        floor[id].status = DEL;
    }
}

/** set etat of floor whith id if bool true set EDIT else set DEL*/
function setEtatPlace(idFloor, idPlace, bool) {
    if (bool) {
        floor[idFloor].place[idPlace].status = EDIT;
    } else {
        floor[idFloor].place[idPlace].status = DEL;
    }
}

/** Remove element whith id in floor*/
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

/** remove element whith placeId in the floor whith floorId*/
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
