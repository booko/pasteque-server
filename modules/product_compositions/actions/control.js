var currentSg = null;
var currentCmp = null; //used for know if composition is set
var idTmp = -1; // used for new subgroup
var indexSg = -1; //used for listSg To changed and delet subgroup

var ERR_COMPOSITION_UNDEFINED = "";
var ERR_COMPOSITION_NAME_EMPTY = "";
var ERR_COMPOSITION_NAME = "";
var ERR_COMPOSITION_REF_EMPTY = "";
var ERR_COMPOSITION_REF = "";

var ERR_SUBGROUP_NAME_EMPTY = "";
var ERR_SUBGROUP_NAME = "";
var ERR_SUBGROUP_UNDEFINED = "";

Subgroup = function(id, label, dispOrder, image) {
    this.id = id;
    this.label = label;
    this.dispOrder = dispOrder;
    this.image = image;
    this.prodIds = new Array();
};

Subgroup.prototype.addProduct = function(prodId) {
    this.prodIds.push(prodId);
};
Subgroup.prototype.removeProduct = function(prodId) {
    for (var i = 0; i < this.prodIds.length; i++) {
        if (this.prodIds[i] == prodId) {
            this.prodIds.splice(i, 1);
            return;
        }
    }
}

Product = function(id, label) {
    this.id = id;
    this.label = label;
};

/** The array of subgroups set on the page.
 * It will be sent to the server on send */
var subgroups = new Array();
/** List of products id => label for client-side manipulations */
var products = new Array();

function registerProduct(id, label) {
    var p = new Product(id, label);
    products[id] = p;
};

function getSubgroup(id) {
    for (var i = 0; i < subgroups.length; i++) {
        if (subgroups[i].id == id) {
            return subgroups[i];
        }
    }
    return null;
};

function deleteSubgroup(groupId) {
    for (var i = 0; i < subgroups.length; i++) {
        if (subgroups[i].id == groupId) {
            subgroups.splice(i, 1);
            return;
        }
    }
};

/* get all value in input subgroup and add to modele */
function addSubgroup(label, dispOrder) {
    var id = idTmp--;
    var img = "null";
    if (!dispOrder) {
        dispOrder = 0;
    }
    var subgroup = new Subgroup(id, label, dispOrder, null);
    subgroups.push(subgroup);
    var grpSelect = jQuery("#listSubGr");
    var option = "<option value=" + id + ">" + label + "</option>";
    grpSelect.append(option);
    return id;
};

/** Button create a subgroup */
function newSubgroup() {
    var id = addSubgroup("", "");
    jQuery("#listSubGr").val(id);
    showSubgroup();
};

/** show the current subgroup */
function showSubgroup() {
    var currentSubgroupId = jQuery("#listSubGr").val();
    var div = jQuery('#product-sub-container');
    div.empty();
    var subgroup = getSubgroup(currentSubgroupId);
    jQuery("#edit-sgName").val(subgroup.label);
    jQuery("#edit-sgOrder").val(subgroup.dispOrder);
    for (var i = 0; i < subgroup.prodIds.length; i++) {
        showSgPrd(subgroup.prodIds[i]);
    }
};

/** used for edit the name of subgroup or the order of subgroups */
function editSubgroup() {
    var currentSubgroupId = jQuery("#listSubGr").val();
    var subgroup = getSubgroup(currentSubgroupId);
    var newOrder = $("#edit-sgOrder").val();
    var newName = $("#edit-sgName").val();
    subgroup.label = newName;
    subgroup.dispOrder = newOrder;
    jQuery("#listSubGr option[value=" + currentSubgroupId + "]").html(newName);
};


/** Check if product exist into current subgroup.
 * @param id Id of product
 * @return true if product is already added
 * */
function checkPrdAdded(id) {
    var currentSubgroupId = jQuery("#listSubGr").val();
    var subgrp = getSubgroup(currentSubgroupId);
    for (var i = 0; i < subgrp.prodIds.length; i++) {
        if (subgrp.prodIds[i] == id) {
            return true;
        }
    }
    return false;
};

function addProduct(groupId, idPrd) {
    var subgroup = getSubgroup(groupId);
    subgroup.addProduct(idPrd);
    return true;
};
function removeProduct(groupId, idPrd) {
    var subgroup = getSubgroup(groupId);
    subgroup.removeProduct(idPrd);
}

/** Product clicked in catalog */
function productPicked(idPrd) {
    if (checkPrdAdded(idPrd)) {
        return;
    }
    var currentSubgroupId = jQuery("#listSubGr").val();
    addProduct(currentSubgroupId, idPrd);
    showSgPrd(idPrd);
}

/** Delete button clicked */
function delProduct(idPrd) {
    var currentSubgroupId = jQuery("#listSubGr").val();
    removeProduct(currentSubgroupId, idPrd);
    jQuery("#productSg-" + idPrd).remove();
}
/** Delete subgroup clicked */
function delSubgroup() {
    var currentSubgroupId = jQuery("#listSubGr").val();
    deleteSubgroup(currentSubgroupId);
    jQuery("#listSubGr option[value=" + currentSubgroupId + "]").remove();
    if (subgroups.length == 0) {
        newSubgroup();
    }
    showSubgroup();
}

/** Add a product into container of product by subgroup */
function showSgPrd(idPrd) {
    var div = jQuery('#product-sub-container');
    var prd = products[idPrd];
    var res = "<div id='productSg-" + idPrd + "' class='catalog-product'>"
            + "<img src=\"?p=img&w=product&id=" + idPrd + "\" onload=\"javascript:centerImage('#productSg-" + idPrd + "');\">"
            + "<p class=\"catalog-label\">" + prd.label + "</p>"
            + "<input type='button' onclick='delProduct(\"" + idPrd + "\")'/>";
            + "</div>";
    div.append(res);
    jQuery("#productSg-" + idPrd)
        .children("img").css({'left': '16px', 'top': '16px'});
};


function save() {
    var stringjs = JSON.stringify(subgroups);
    jQuery("#subgroupData").val(stringjs);
};

/** Callback of form send */
function submitData() {
    save();
    return true;
};
