//    Pastèque Web back office
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

// Do not create instances of Catalog. Instead use the php initializer.
Catalog = function(containerId, selectCallback) {
    this.products = new Array();
    this.productsByCategory = new Array();
    this.containerId = containerId;
    this.selectCallback = selectCallback;
    this.currentCategoryId = null;
}
/** Get label class according to its length */
Catalog.prototype.getLabelClass = function(label) {
    var className = "catalog-label";
    if (label.length > 16) {
        className += " catalog-label-verylong";
    } else if (label.length > 8) {
        className += " catalog-label-long";
    }
    return className;
}

Catalog.prototype.createCategory = function(catVar, id, label, hasImage) {
    var html = "<a class=\"category-" + id + " catalog-category\" onClick=\"javascript:" + catVar + ".changeCategory('" + id + "');return false;\">";
    if (hasImage) {
        src = "?p=img&w=category&id=" + id;
    } else {
        src = "?p=img&w=category";
    }
    html += "<img src=\"" + src + "\" onload=\"javascript:centerImage('.category-" + id + "');\" />";
    var className = this.getLabelClass(label);
    html += "<p class=\"" + className + "\">" + label + "</p></a>";
    jQuery("#" + this.containerId + " .catalog-categories-container").append(html);
}
/** Add a product to the catalog. Use full product object. */
Catalog.prototype.addProduct = function(product) {
    this.products[product['id']] = product;
}
Catalog.prototype.addProductToCat = function(product, category) {
    if (typeof(this.productsByCategory[category]) != 'object') {
        this.productsByCategory[category] = new Array();
    }
    this.productsByCategory[category].push(product);
}

Catalog.prototype.showProduct = function(productId) {
    var product = this.products[productId];
    html = "<div id=\"product-" + productId + "\"class=\"catalog-product\" onClick=\"javascript:" + this.selectCallback + "('" + product['id'] + "');\">";
    var src;
    if (product['hasImage']) {
        src = "?p=img&w=product&id=" + product['id'];
    } else {
        src = "?p=img&w=product";
    }
    html += "<img src=\"" + src + "\" onload=\"javascript:centerImage('#product-" + productId + "');\" />";
    var className = this.getLabelClass(product['label']);
    html += "<p class=\"" + className + "\">" + product['label'] + "</p>";
    html += "</div>";
    jQuery("#" + this.containerId + " .catalog-products-container").append(html);
    centerImage("#" + this.containerId + " .product-" + productId);
}

Catalog.prototype.changeCategory = function(category) {
    jQuery("#" + this.containerId + " .catalog-products-container").html("");
    var prdCat = this.productsByCategory[category];
    if (typeof prdCat == "undefined") {
        // Empty cat
        prdCat = new Array();
    }
    for (var i = 0; i < prdCat.length; i++) {
        this.showProduct(prdCat[i]);
    }
    this.currentCategoryId = category
}

Catalog.prototype.getProduct = function(productId) {
    return this.products[productId];
}

centerImage = function(selector) {
    var container = jQuery(selector);
    var img = container.children("img");
    var containerWidth = parseInt(container.css('width'));
    var containerHeight = parseInt(container.css('height'));
    var imgWidth = parseInt(img.css('width'));
    var imgHeight = parseInt(img.css('height'));
    var hOffset = (containerWidth - imgWidth) / 2;
    var vOffset = (containerHeight - imgHeight) / 2;
    img.css("left", hOffset + "px");
    img.css("top", vOffset + "px");
}
