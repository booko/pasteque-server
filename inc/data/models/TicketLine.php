<?php
//    POS-Tech API
//
//    Copyright (C) 2012 Scil (http://scil.coop)
//
//    This file is part of POS-Tech.
//
//    POS-Tech is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    POS-Tech is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with POS-Tech.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque;

class TicketLine {

    public $dispOrder;
    public $productId;
    public $attrSetInstId;
    public $quantity;
    /** Price without tax and befor applying discount */
    public $price;
    public $taxId;
    public $discountRate;
    /** XML attribute field */
    public $attributes;

    /** Constructor must take full product and tax objects to build
     * xml attributes. Only the id is then kept.
     */
    function __construct($line, $product, $attrSetInstId, $quantity, $price,
            $tax, $discountRate = 0.0) {
        $this->dispOrder = $line;
        $this->productId = $product->id;
        $this->attrSetInstId = $attrSetInstId;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->taxId = $tax->id;
        $this->discountRate = $discountRate;
        $this->createAttributes($product, $tax);
    }

    function getSubtotal($extDiscountRate) {
        $fullDiscount = $this->discountRate + $extDiscountRate;
        return $this->price * (1.0 - $fullDiscount) * $this->quantity;
    }

    /** Build xml attributes from line data. See TicketLineInfo constructors. */
    private function createAttributes($product, $tax) {
        // Set xml
        $domimpl = new \DOMImplementation();
        $doctype = $domimpl->createDocumentType('properties', null,
                                                "http://java.sun.com/dtd/properties.dtd");
        $attrs = $domimpl->createDocument(null, null, $doctype);
        $attrs->encoding = "UTF-8";
        $attrs->version = "1.0";
        $attrs->standalone = false;
        // Add root properties element
        $properties = $attrs->createElement("properties");
        $attrs->appendChild($properties);
        // Add comment element
        $comment = $attrs->createElement("comment");
        $comment->appendChild($attrs->createTextNode("POS-Tech")); // This is actually the application name
        $properties->appendChild($comment);
        // Add some product keys
        $entry = $attrs->createElement("entry");
        $key = $attrs->createAttribute("key");
        $key->appendChild($attrs->createTextNode("product.taxcategoryid"));
        $entry->appendChild($key);
        $entry->appendChild($attrs->createTextNode($tax->taxCatId));
        $properties->appendChild($entry);
        $entry = $attrs->createElement("entry");
        $key = $attrs->createAttribute("key");
        $key->appendChild($attrs->createTextNode("product.com"));
        $entry->appendChild($key);
        $entry->appendChild($attrs->createTextNode("false")); // TODO add iscom field
        $properties->appendChild($entry);
        $entry = $attrs->createElement("entry");
        $key = $attrs->createAttribute("key");
        $key->appendChild($attrs->createTextNode("product.categoryid"));
        $entry->appendChild($key);
        $entry->appendChild($attrs->createTextNode($product->categoryId));
        $properties->appendChild($entry);
        $entry = $attrs->createElement("entry");
        $key = $attrs->createAttribute("key");
        $key->appendChild($attrs->createTextNode("product.scale"));
        $entry->appendChild($key);
        $entry->appendChild($attrs->createTextNode(strval($product->scaled)?"true":"false"));
        $properties->appendChild($entry);
        $entry = $attrs->createElement("entry");
        $key = $attrs->createAttribute("key");
        $key->appendChild($attrs->createTextNode("product.name"));
        $entry->appendChild($key);
        $entry->appendChild($attrs->createTextNode($product->label));
        $properties->appendChild($entry);
        // Save all this stuff
        $this->attributes = $attrs->saveXML();
    }

}