//used when we add Place or add Floor
var indexFloor = -1; //used for change listFloor when we add a floor
var idTmp = -1;
var currentFloorId;

/* Add a new floor and show it */
function newFloor() {
    var name = $("#addFloorBtn").val();
    var img = null;

    var floorId = "" + idTmp--;

    if (addFloorData(floorId, name, img)) {
        //modify the list item selected by the new floor and show it
        $("#listFloor option").eq(indexFloor).prop("selected", true);
        showFloor(floorId);
    }
}

/** add to the floor data the floor if the name is not empty
 * if issue was occured return false*/
function addFloorData(floorId, name, img) {
    if (name != null && name != "") {
        if (addFloor(floorId, name, img)) {
            // add the floor to the list of floors
            var res = "<option value=" + floorId + ">" + name + "</option>";
            $('#listFloor').append(res);
            indexFloor++;
            return true;
        }
    }
    return false;
}

/** delete all place contain in current floor and delete it
 * if the floor isn't the last floor*/
function deleteFloor() {
    //Delete all place contain in the floor
    var places = getFloor(currentFloorId).place;
    for (var idPlace in places) {
        deletePlace(idPlace);
    }

    if (indexFloor != 0) {
        // set status floor in DELETE
        setEtatFloor(currentFloorId, false);

        // the floor is not insert in BDD yet so delete immediatly
        if (currentFloorId.charAt(0) == "-") {
            delFloor(currentFloorId);
        }
        //remove the floor from list of floor:
        $("#listFloor option[value=" + currentFloorId + "]").remove();
        indexFloor--;
    }

    showFloor();
}

/** Write the html code of the floor get by jquery the floor div in html
 * if idFloor not set get the floor selected in the list of floor
 * affect the currentFloorId whith the idFloor */
function showFloor(idFloor) {
    if (!idFloor) {
        var idFloor = $('#listFloor').val();
    }
    currentFloorId = idFloor;
    //reset the div
    var floorHtmlContainer = $("#floorDivContainer").empty();
    //no floors set
    if (!currentFloorId) {
        return;
    }
    var floorHtml = "<div id='floorDiv' class='floor'></div>";
    floorHtmlContainer.append(floorHtml);

    //get the floor data:
    var fl = getFloor(idFloor);
    //SET PLACE
    if (fl.status != DEL) {
        //get the place data and add them in div
        var places = fl.place;

        for (var id in places) {
            if (places[id].status != DEL) {
                showPlace(id, places[id].name, places[id].x, places[id].y);
            }
        }
    }

}

function editFloorName() {
    var input = $("#formUp input").eq(0); // the first input
    if (input.val().length > 0) {
        // edit only name
        editFloor(currentFloorId, input.val(), "");
        // change value of list floor
        $("#listFloor option[value=" + currentFloorId + "]").html(input.val());
        // clean input
        input.val('');
    }
}

/** Get value of input text in html do nothing if currentFloorId is not set
 * else add new place on data at the currentFloor at position (0,0)
 * finaly showPlace */
function newPlace() {
    var input = $("#addPlaceBtn");
    var name = input.val();

    if (name.length != 0) {
        var id = "" + idTmp--;
        addPlaceData(id, name, 0, 0, currentFloorId);
        showPlace(id, name,0, 0);
        input.remove();
    } else {
        showFloor(currentFloorId);
    }
}

/** create an input text in currentFloor */
function addPlaceInput() {
    // if id exist
    if($("#addPlaceBtn").length) {
        return;
    }
    var res = "<form action='javascript:newPlace()' >"
           + "<input type='text' id='addPlaceBtn'/>";

    $("#floorDiv").append(res);
    $("#floorDiv #addPlaceBtn").focus();
}

/** Add data place to the floorId
 * return false if a issue was occured*/
function addPlaceData(id, name, x, y, floorId) {
    if (name != "") {
        //floor was changed
        setEtatFloor(floorId, true);
        var place = addPlace(id, name, x, y, floorId);
        if (place) {
            return true
        }
    }
    return false;
}

/** Write the html code of the place in the divFloor*/
function showPlace(placeId, name, x, y) {
    var placeToAdd = "<div id='pl_" + placeId +"' class='place'>"
            //+ ">"
            + "<p onDblClick='beEditablePlace(\"" + placeId + "\")'>"
            + name + "</p></div>";

    $("#floorDiv").append(placeToAdd);

    $("#pl_" + placeId).click(function() {
        move(this.id);
    });

    $("#pl_" + placeId).draggable({ 'containment': 'parent' });

    $("#pl_" + placeId).css({'left' : x + 'px', 'top': y + 'px'});
}

function beEditablePlace(placeId) {
    var divToEdit = $("#pl_" + placeId);
    var namePlace = $("#pl_" + placeId + " p").html();

    var form = "<form id='formEditPlace' action='javascript:editPlaceName(\"" + placeId + "\")'></form>";
    divToEdit.html(form + "\n");

    var res = "<input type='text'>";
    var delButton = "<input type='button' onclick='deletePlace(\"" + placeId + "\")'/>";
    $("#pl_" + placeId +" #formEditPlace").html(res + " " + delButton);

    // affect the oldname place in input text and focus on
    $("#pl_" + placeId +" #formEditPlace input").eq(0).focus().val(namePlace);
}

/** get the contenu of the input text in div id : placeId
 * and edit the place corresponding
 * notice: to do check if the value of input text contain malicious code */
function editPlaceName(placeId) {
    var divToChange = $("#pl_" + placeId);
    newName = $("#pl_" + placeId + " input").val();
    divToChange.empty();
    var oldPlace = getPlace(placeId, currentFloorId);

    var res = "<p onDblClick='beEditablePlace(\"" + placeId + "\")'>";
    // affect new name if the new name is not empty
    if (newName.length != 0) {
        editPlace(placeId, newName, oldPlace.x, oldPlace.y, currentFloorId);
        res +=  newName ;
    } else {
        res += oldPlace.name;
    }
    divToChange.html(res + "</p");
}

/** Remove the place whith placeId in the current floor do nothing if
 * currentFloorId is not set*/
function deletePlace(placeId) {
    if (placeId.charAt(0) != "-") {
        // the place will be delete by php at the next save change
        setEtatPlace(currentFloorId, placeId, false);
    } else {
        //the place was created but not insert into BDD
        delPlace(currentFloorId, placeId);
    }
    showFloor(currentFloorId);
}

/** Move the place on the currentFloor */
function move(divId) {
    div = $("#" + divId);
    idPlace = divId.substr(3);
    offset = div.offset();
    osf = $("#floorDiv").offset();

    setEtatPlace(currentFloorId, idPlace, true);
    name = getPlace(idPlace, currentFloorId).name;
    //absolute position of placeDiv - absolute position of floorDiv
    editPlace(idPlace, name, offset.left - osf.left, offset.top - osf.top, currentFloorId);
}

/** Move all data on the correct input at the format:
 * all floor separte whith "|"
 * all place separate whith "#"
 * statusFloor; idFloor; name; img;
 *           statusPlace, nameP, x, y, idFloor#
 *           statusPlace, nameP, x, y, idFloor#
 *           statusPlace, nameP, x, y, idFloor
 * | statusFloor; idFloor; name; img;
 *           statusPlace, nameP, x, y, idFloor#
 * | statusFloor; idFloor; name; img;
 * etc … */
function save() {
    var floors = getAllFloor();
    var res = "";
    var floorSep = "|";
    var placeSep = "#";

    for (var idF in floors) {
        var placeAdd = false;
        res += floorToString(idF, floors);

        var places = floors[idF].place;
        for (var idP in places) {
            if (idP < 0 || places[idP].status != "") {
                placeAdd = true;
                res += placeToString(idP, places);
                res += placeSep;
            }
        }
        if (placeAdd) {//remove the last placeSep
            res = res.substr(0, res.length-1);
        }
        res += floorSep;
    }
    //remove the last floorSep
    res = res.substr(0, res.length-1);
    $("#input").append(input);
    $("#inputData").val(res);
    return true;
}

/** return a string represent a place all value separate whith "," */
function placeToString(placeId, place) {
    var res = place[placeId].status + ","
            + placeId + ","
            + place[placeId].name + ","
            + place[placeId].x + ","
            + place[placeId].y + ","
            + place[placeId].floorId;
    return res;
}

/*return a string represent a floor all value separate whith ";" */
function floorToString(floorId, ff) {
    var res = ff[floorId].status + ";"
            + floorId + ";"
            + ff[floorId].name + ";"
            + ff[floorId].img + ";"; // place for attribut place[]
    return res;
}
