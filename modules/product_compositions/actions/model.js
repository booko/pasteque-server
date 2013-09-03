var mod = {
        'id': '',
        'label': '',
        'reference': '',
        'barcode': '',
        'price_buy': '',
        'price_sell': '',
        'tax_category': '',
        'category': 'formules',
        'order': '',
        'discount_enabled': '',
        'discount_rate': '',
        'image': '',
        'status': '',
        'subGroups': []
}

var NEW = 'NEW';
var EDIT = 'EDIT';
var DEL = 'DEL';

// subGroup is an array of associatif array whith key idSubGroup and values are name of subgroup and image of subgroups
// product is an associatif array with key is idSubGroup and value are an array of idProduct;

var data = eval(mod);

function addCmpModel(id, reference, label, barcode, taxeCat, priceSell,priceBuy, order, visible, discountEnabled, discountRate, image) {
    data['id'] = id;
    data['label'] = label;
    data['reference'] = reference;
    data['barcode'] = barcode;
    data['tax_category'] = taxeCat;
    data['price_sell'] = priceSell;
    data['price_buy'] = priceBuy;
    data['order'] = order;
    data['visible'] = visible;
    data['discount_enabled'] = discountEnabled;
    data['discount_rate'] = discountRate;
    //check image
    data['image'] = image;
    //check status
}

/** create subgroup whith id: idSg */
function addSubGroupModel(idSg, name, image, dispOrder, status) {
    data['subGroups'].push(eval({
            'id': idSg,
            'name': name,
            'image': image,
            'product': [],
            'dispOrder': dispOrder,
            'status': status}));
}

/** add product into subgroup if subgroup exist */
function addProductModel(idSg, idProduct, nameP, status) {
    var subGroup = getSg(idSg);
    if (subGroup) {
        var res = { 'id':idProduct, 'name': nameP, 'dispOrder': '0', 'status': status};
        subGroup.product.push(eval(res));
    }
}
/** return index of the object whith propriete id = idSearch in array */
function indexOf(array, idSearch) {
    for (index in array) {
        if (array[index].id == idSearch) {
            return index;
        }
    }
    return -1;
}
function getCmp() {
    return data;
    }
/* return null if the subGroups whith id: idSg doesn't exist */
function getSg(idSg) {
    var index = indexOf(data.subGroups, idSg);
    if (index == -1) {
        return null;
    }
    return data.subGroups[index];
}

function getAllSg() {
    return data.subGroups;
}

/* return an empty array if the subGroups whith id: idSg doesn't exist */
function getAllproduct(idSg) {
    var res = getSg(idSg);
    if (res) {
        return res.product;
    }
    return new Array();
}

/** Return null if subgroup whith id:idSg doesn't exist
 * or idPrd doesn't exist */
function getPrd (idSg, idPrd) {
    var subG = getSg(idSg);
    if (!subG) {
        return null;
    }
    var index = indexOf(subG.product, idPrd);
    if (index == -1) {
        return null;
    }
    return subG.product[index];
}

// suppresion: essaiyer requete ajax pour la suppression de composition ??? a mettre dans le controleur
// set a new empty composition erase current composition: 
function delCmp() {
    data = eval(mod);
}

function delSg(idSg) {
    var res = getSg(idSg);
    if (res) {
        var index = data.subGroups.indexOf(res);
        data.subGroups.splice(index,1);
        return true;
    }
    return false;
}

function delPrd(idSg, idPrd) {
    var res = getPrd(idSg, idPrd);
    if (res) {
        var subGroup = getSg(idSg);
        var index = subGroup.product.indexOf(res);
        subGroup.product.splice(index, 1);
        return true;
    }
    return false;
}

