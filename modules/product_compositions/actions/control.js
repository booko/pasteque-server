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

$("#edit-reference").change(function() {
    checkReference();
});

$("#edit-label").change(function()  {
    checkNameCompo();
});

/** Check reference and check name of the composition
 * @return true if name and reference are correct.*/
function checkCompo() {
    return checkReference() && checkNameCompo();
}

/*Show an error's message if a composition's reference are already used
 *  by an other product or are empty and replace the old value
 *  of the reference in input
 * @param ref: reference of the composition
 * @return true if name and ref not used.*/
function checkReference() {
    var ref = $("#edit-reference").val();
    var cmp = getCmp();
    var oldRef = cmp.reference;
    var refOk = true;
    if (ref.length == 0) {
        showPopup(ERR_COMPOSITION_REF_EMPTY);
        refOk = false;
    }
    for(prd in products) {
        if (ref != oldRef && products[prd].reference == ref) {
            showPopup(ERR_COMPOSITION_REF);
            refOk = false;
            break;
        }
    }
    if (!refOk) {
        $("#edit-reference").val(oldRef);
        return false;
    }
    return true;
}

/*Show an error's message if a composition's name are already used
 *  by an other product or are empty  and replace the old value
 * of the name in input
 * @param name: label of the composition
 * @return true if name and ref not used.*/
function checkNameCompo(name) {
    var name = $("#edit-label").val();
    var cmp = getCmp();
    var oldName = cmp.label;
    var nameOk = true;

    if (name.length == 0) {
        showPopup(ERR_COMPOSITION_NAME_EMPTY);
        nameOk = false;
    }
    for(prd in products) {
        if (name != oldName && products[prd].label == name) {
                showPopup(ERR_COMPOSITION_NAME);
                nameOk = false;
        }
    }
    if (!nameOk) {
        $("#edit-label").val(oldName);
        return false;
    }
    return true;
}
function addDataCmp(id, reference, label, order, visible, priceSell, priceBuy, 
        priceSellAndTaxes, tva, barcode, discountEnabled, discountRate,
        image, status) {
    //var status = NEW;
    if (!image) {
        image = "null";
    }

    addCmpModel("" + id , reference, label, barcode, tva, priceSell,
            priceBuy, order, visible, discountEnabled, discountRate,
            image, status);
}

/** Add value in view and add to model*/
function getViewData() {
    var reference = $("#edit-reference").val();
    var label = $("#edit-label").val();
    
    var order = $("#edit-disp_order").val();
    if (!order) {
        order = 0;
    }
    var visible = $("#edit-visible").val();

    var priceSell =  $("#sell").val();
    var priceBuy = $("#edit-price_buy").val();
    var priceSellandTaxes = $("#sellvat").val();

    var tva = $("#edit-tax_cat").val();
    var barcode =  $("#barcode").val();

    var image = "null";

    var discountEnabled = $("#edit-discount_enabled").val();
    var discountRate = $("#edit-discount_rate").val();

    if (checkCompo()) {
        addDataCmp(getCmp().id, reference, label, order, visible, priceSell,
                priceBuy, "0", tva, barcode, discountEnabled,
                discountRate, image);
        return true;
    }
    return false;
}

/* get all value in input and add to the modele */
function addCmp() {
    getViewData();
    var composition = getCmp();
    composition['id'] = "" + idTmp--;
    composition['subGroups'] = Array();
    composition['status'] = NEW;
    //clear listSubGr and container of product
    $("#listSubGr option").remove();
    indexSg = -1;
    $("#product-sub-container").html('');
    currentCmp = composition.id;
    showSubgroup();
}

/** move the data in model to the view */
function showCmp() {
    var composition = getCmp();
    $("#edit-reference").val(composition['reference']);
    $("#edit-label").val(composition['label']);
    
    $("#edit-disp_order").val(composition['order']);
    //$("#edit-visible").val();

    $("#sell").val(composition['price_sell']);
    $("#edit-price_buy").val(composition['price_buy']);
    updateSellVatPrice();

   // $("#edit-tax_cat").val(composition['tax_category']);
    $("#barcode").val(composition['barcode']);

    //$("#edit-discount_enabled").val(composition['discount_enabled']);
    $("#edit-discount_rate").val(composition['discount_rate']);
    currentCmp = composition['id'];
    showSubgroup();
    updateBarcode();
}



function checkCurrentSg() {
    if (!currentSg) {
        showPopup(ERR_SUBGROUP_UNDEFINED);
        return false;
    }
    return true;
}

/** If name isn't empty and if name not affect at other subgroup
 * show popup error message corresponding and return false,
 * else return true */
function checkNameSG(name) {
    if (!name) {
        showPopup(ERR_SUBGROUP_NAME_EMPTY);
        return false;
    }
    // name of subgroup are stocked in listbox
    for (var i = 0; i <= indexSg; i++) {
        var subgroupName = $("#listSubGr option").eq(i).html();
        if (name == subgroupName) {
            showPopup(ERR_SUBGROUP_NAME);
            return false;
        }
    }
    return true;
}

/* get all value in input subgroup and add to modele */
function addSubGroup() {
    if (!currentCmp) {
        showPopup(ERR_COMPOSITION_UNDEFINED);
        return;
    }
    var id = idTmp--;
    var name = $("#edit-sgName").val();
    var img = "null";
    var dispOrder = $("#edit-sgOrder").val();
    if (!dispOrder) {
        dispOrder = 0;
    }
    if (checkNameSG(name)) {
        addDataSg(id, name, img, dispOrder, NEW);
        currentSg = id;
        showSubgroup(id);
        $("#listSubGr option").eq(indexSg).prop("selected", true);
    }
    $("#edit-sgName").val('');
}

/* add the subgroup into model and add it into list of subgroup */
function addDataSg(id, name, image, dispOrder, status) {
    if (!image) {
        image = "null";
    }
    addSubGroupModel(id, name, image, dispOrder, status);
    addOngletSg(id, name);
    showCmp();
}

/** add item to the list of subgroup*/
function addOngletSg(id, name) {
    var onglet = $("#listSubGr");
    var res = "<option value=" + id + ">" + name + "</option>";
    onglet.append(res);
    indexSg++;
}

/** show the subgroup in the page
 *@param idSubgroup id of the subgroup*/
function showSubgroup(idSubgroup) {
    if (!idSubgroup) {
        var idSubgroup = $("#listSubGr").val();
    }
    currentSg = idSubgroup;
    if(!currentSg) {
        return;
    }
    var div = $('#product-sub-container');
    div.empty();
    var subgroup = getSg(currentSg);
    $("#edit-sgOrder").val(subgroup.dispOrder);
    var products = getAllproduct(currentSg);
    for (var i = 0; i < products.length; i++) {
        if (products[i].status != DEL) {
            showSgPrd(products[i].id, products[i].name);
        }
    }
}

/**delete current subgroup */
function delSubgroup() {
    if (!currentSg) {
        showPopup(ERR_SUBGROUP_UNDEFINED);
        return;
    }
    //remove all products contain in the current Subgroup
    deleteSubgroup(currentSg);
    showSubgroup();
}

/** delete subgroup and all product contains if the group isn't the last
 * group */
function deleteSubgroup(id) {
    var products = getAllproduct(id);
    var sizePrd = products.length;
    var newPrd = Array();
    var i = 0;
    // create a new array contain products not new
    // do that because delProduct change length of the array products
    for (var j = 0; j < products.length; j++) {
        if (products[j].status != NEW) {
            newPrd.push(products[j]);
        }
    }
    // set statut of the product on DEL
    for (var i = 0; i < newPrd.length; i++) {
        delProduct(newPrd[i].id);
    }
    // change old array by new
    getSg(id).product = newPrd;
    //not delete the subgroup if it's the last
    if (indexSg > 0) {
        var subgroup = getSg(currentSg);
        if (subgroup.status != NEW) {
            subgroup.status = DEL;
        } else {
            delSg(currentSg);
        }
        indexSg--;
        $("#listSubGr option[value=" + currentSg +  "]").remove();
    }
}

/** used for edit the name of subgroup or the order of subgroups */
function editSubGroup() {
    if (!currentSg) {
        showPopup(ERR_SUBGROUP_UNDEFINED);
        return;
    }
    var subgroup = getSg(currentSg);
    var newOrder = $("#edit-sgOrder").val();
    var newName = $("#edit-sgNewName").val();
    var change = false;

    if (newName.length != 0 && checkNameSG(newName)) {
            $("#listSubGr option[value=" + subgroup.id + "]").html(newName);
            subgroup.name = newName;
            $("#edit-sgNewName").val('');
            change = true;
    }

    /** same input for edit subgroup order and add subgroup order
     * when the input text for #edit-sgName is empty the new value of
     * order will be affect of the current groupe*/
    if ($("#edit-sgName").val().length == 0) {
        if (newOrder !== subgroup.dispOrder) {
            subgroup.dispOrder = newOrder;
            change = true;
        }
    }

    //set status group if subgroup isn't new
    if (change) {
        if (subgroup.status != NEW) {
            subgroup.status = EDIT;
        }
    }
    showSubgroup();
}


/** Check if product exist into current subgroup.
 * @param id Id of product
 * @return true if product exist false else.
 * */
function checkPrdExist(id) {
    return getPrd(currentSg, id);
}

/** Add new product into current subgroup if product not contain into
 * show error message if current subgroup undefined
 * @param idPrd Id of product
 * @return true if product is add to current subgroup*/
function addProduct(idPrd) {
    if (!checkCurrentSg()) {
        return false;
    }
    if (!checkPrdExist(idPrd)) {
        var name = $('#product-' + idPrd + " p").text();
        addDataSgPrd(currentSg, idPrd, name, NEW);
        showSgPrd(idPrd, name);
    } else { //product re insert after being delete
        prd = getPrd(currentSg, idPrd);
        if (prd.status != NEW) {
            getPrd(currentSg, idPrd).status = "0";
            showSubgroup(currentSg);
        }
    }
    return true;
    }

/** Add a product into container of product by subgroup */
function showSgPrd(idPrd, name) {
    var div = $('#product-sub-container');
    var res = "<a id='productSg-" + idPrd + "' class='catalog-product'>"
            + "<img src='?p=img&w=product&id=39608bd4-f07a-490d-802d-1e080ad3caa6'>"
            + "<p>" + name + "</p>"
            + "</a>";
    var buttonDell = "<input type='button' onclick='delProduct(\"" + idPrd + "\")'/>";
    div.append(res + " " +  buttonDell);
    $("#productSg-" + idPrd)
        .children("img").css({'left': '16px', 'top': '16px'});
}

/* get all value add in sg and add to modele */
function addDataSgPrd(idSg, idPrd, name, status) {
    addProductModel(idSg, idPrd, name, status);
}

/**delet product from data if product start by "-" remove product from */
function delProduct(id) {
    var prd = getPrd(currentSg, id);
    if (prd.status != NEW) {
        prd.status = DEL;
    } else {
        delPrd(currentSg, id);
    }
    showSubgroup(currentSg);
}


function save() {
    id = getCmp().id ;
    if (id.charAt(0) != '-') {
        data['status'] = EDIT;
    }
    var stringjs = JSON.stringify(data);
    $("#inputData").val(stringjs);
}

function submitData() {
    var cmp = getCmp();
    dataOk = getViewData();
    if (dataOk) {
        save();
        return true;
    }
    return false;
}
