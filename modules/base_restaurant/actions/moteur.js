var EDIT = "EDIT";
var NEW = "NEW";
var DEL = "DELETE";
var tmpId = -1;

Floor = function(id, label, state) {
    this.id = id;
    this.label = label;
    this.places = new Array();
    this.status = state;
    
}
Floor.prototype.addPlace = function(place) {
    this.places.push(place);
}
Floor.prototype.registerPlace = function(id, label, x, y) {
    var place = new Place(id, label, x, y, EDIT);
    this.places.push(place);
}

Place = function(id, label, x, y, state) {
    this.id = id;
    this.label = label;
    this.x = x;
    this.y = y;
    this.status = state;
}

/* Used to manage floor or place */
var floors = new Array();
var selectedPlace = null;

/** Add a floor on the array of floors 
 * return the floor created */
function registerFloor(id, name) {
    var floor = new Floor(id, name, EDIT);
    floors.push(floor);
    jQuery("#listFloors").append("<option value=\"" + id + "\">" + name + "</option>");
    return floor;
}

function newFloor() {
    var id = tmpId--;
    var floor = new Floor(id, "", NEW);
    floors.push(floor);
    jQuery("#listFloors").append("<option value=\"" + id + "\"></option>");
    jQuery("#listFloors").val(id);
    showFloor();
}

function getCurrentFloor() {
    var floorId = jQuery("#listFloors").val();
    for (var i = 0; i < floors.length; i++) {
        if (floors[i].id == floorId) {
            return floors[i];
        }
    }
    return null;
}

function selectPlace(id) {
    if (id == null) {
        if (selectedPlace != null) {
            jQuery("#place-" + selectedPlace.id).css({"background-color": "#fff"});
        }
        selectedPlace = null;
        jQuery("#placeLabel").val("");
        jQuery("#placeLabel").prop("disabled", true);
        return;
    }
    var floor = getCurrentFloor();
    for (var i = 0; i < floor.places.length; i++) {
        if (floor.places[i].id == id) {
            if (selectedPlace != null) {
                jQuery("#place-" + selectedPlace.id).css({"background-color": "#fff"});
            }
            selectedPlace = floor.places[i];
            jQuery("#place-" + id).css({"background-color": "#bbf"});
            jQuery("#placeLabel").val(selectedPlace.label);
            jQuery("#placeLabel").prop("disabled", false);
            return;
        }
    }
}

function deleteCurrentFloor() {
    var floorId = jQuery("#listFloors").val();
    for (var i = 0; i < floors.length; i++) {
        if (floors[i].id == floorId) {
            var floor = floors[i];
            if (floor.status == NEW) {
                floors.splice(i, 1);
            } else {
                floor.status = DEL;
            }
        }
        jQuery("#listFloors option[value=" + floorId + "]").remove();
    }
    var noLeft = true;
    for (var i = 0; i < floors.length; i++) {
        if (floors[i].status != DEL) {
            noLeft = false;
            break;
        }
    }
    if (noLeft) {
        newFloor();
    }
    showFloor();
}

function updateFloor() {
    var label = jQuery("#floorLabel").val();
    var floor = getCurrentFloor();
    floor.label = label;
    jQuery("#listFloors option[value=" + floor.id + "]").html(floor.label);
}


function newPlace() {
    var floor = getCurrentFloor();
    var place = new Place(tmpId--, newPlaceLabel, 60, 50, NEW);
    floor.addPlace(place);
    showPlace(place);
}

function updatePlaceLabel() {
    selectedPlace.label = jQuery("#placeLabel").val();
    jQuery("#place-" + selectedPlace.id + " p").html(selectedPlace.label);
}
function updatePlacePos() {
    var div = jQuery("#place-" + selectedPlace.id);
    offset = div.offset();
    osf = jQuery("#floorDiv").offset();
    var width = div.width() / 2 - 1;
    var height = div.height() / 2 - 1;
    var x = offset.left - osf.left + width;
    var y = offset.top - osf.top + height;
    selectedPlace.x = x;
    selectedPlace.y = y;
}

function deletePlace() {
    if (selectedPlace.status == NEW) {
        var floor = getCurrentFloor();
        for (var i = 0; i < floor.places.length; i++) {
            if (floor.places[i].id == selectedPlace.id) {
                floor.places.splice(i, 1);
            }
        }
    } else {
        selectedPlace.status = DEL;
    }
    jQuery("#place-" + selectedPlace.id).remove();
    selectPlace(null);
}

/** Write the html code of the floor get by jquery the floor div in html
 * if idFloor not set get the floor selected in the list of floor
 * affect the currentFloorId whith the idFloor */
function showFloor() {
    var floor = getCurrentFloor();
    var floorHtmlContainer = jQuery("#floorDivContainer").empty();
    var floorHtml = "<div id=\"floorDiv\" class=\"floor\"></div>";
    floorHtmlContainer.append(floorHtml);

    for (var i = 0; i < floor.places.length; i++) {
        var place = floor.places[i];
        if (place.status != DEL) {
            showPlace(place);
        }
    }
    jQuery("#floorLabel").val(floor.label);
}

/** Write the html code of the place in the divFloor*/
function showPlace(place) {
    var placeToAdd = "<div id=\"place-" + place.id +"\" class=\"place\">"
            + "<p onClick='selectPlace(\"" + place.id + "\")'>"
            + place.label + "</p></div>";

    jQuery("#floorDiv").append(placeToAdd);
    var divPlace = jQuery("#place-" + place.id);
    var posX = place.x - divPlace.width() / 2 - 1;
    var posY = place.y - divPlace.height() / 2 - 1;
    divPlace.css({"left": posX + "px", "top": posY + "px"});
    var scope = place;
    divPlace.mousedown(function() {selectPlace(scope.id); });
    divPlace.click(function() {updatePlacePos();})
            .draggable({ 'containment': '#floorDiv' })
            .css({'left' : posX + 'px', 'top': posY + 'px'});
    selectPlace(null);
}

function save() {
    jQuery("#floorData").val(JSON.stringify(floors));
    return true;
}
