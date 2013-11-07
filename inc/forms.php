<?php
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

namespace Pasteque;

function form_hidden($form_id, $object, $field) {
    if ($object != NULL && isset($object->{$field})) {
        echo '<input type="hidden" name="' . $field . '" value="'
                . $object->{$field} . "\"/>\n";
    }
}
function form_value_hidden($form_id, $name, $value) {
    echo '<input type="hidden" name="' . $name . '" value="' . $value . "\"/>\n";
}

function form_input($form_id, $class, $object, $field, $type, $args = array()) {
    echo "<div class=\"row\">\n";
    if ($type != "pick_multiple") {
        echo '<label for="' . $form_id . '-' . $field . '">';
        $fieldLabel = $field;
        if (substr($field, -2) == "Id") {
            $fieldLabel = substr($field, 0, -2);
        }
        echo \i18n($class . "." . $fieldLabel);
        echo "</label>\n";
    }
    $required = "";
    if (isset($args['required']) && $args['required']) {
        $required = ' required="true"';
    }
    switch ($type) {
    case 'string':
        echo '<input id="' . $form_id . '-' . $field . '" type="text" name="'
                . $field . '"';
        if ($object != NULL) {
            echo ' value="' . $object->{$field} . '"';
        }
        echo "$required />\n";
        break;
    case 'text':
        echo '<textarea id="' . $form_id . '-' . $field . '" name="' . $field
                . '">';
        if ($object != NULL) {
            echo $object->{$field};
        }
        echo '</textarea>';
        break;
    case 'numeric':
        echo '<input id="' . $form_id . '-' . $field . '" type="numeric" name="'
                . $field . '"';
        if ($object != NULL) {
            echo ' value="' . $object->{$field} . '"';
        }
        echo "$required />\n";
        break;
    case 'boolean':
        echo '<input id="' . $form_id . '-' . $field
            . '" type="checkbox" name="' . $field . '"';
        if ($object != NULL) {
            if ($object->{$field}){
                echo ' checked="checked"';
            }
        } else {
            if (!isset($args['default']) || $args['default'] == TRUE) {
                echo ' checked="checked"';
            }
        }
        echo " />\n";
        break;
    case 'float':
        if (!isset($args['step'])) {
            $step = 0.01;
        } else {
            $step = $args['step'];
        }
        echo '<input id="' . $form_id . '-' . $field
                . '" type="number" step="' . $step . '" min="0.00" name="' . $field . '"';
        if ($object != NULL) {
            echo ' value="' . $object->{$field} . '"';
        }
        echo "$required />\n";
        break;
    case 'date':
        echo '<input id="' . $form_id . '-' . $field
                . '" type="date" name="' . $field . '"';
        if ($object != NULL) {
            echo ' value="' . strftime("%Y-%m-%d", $object->{$field}) . '"';
        }
        echo "$required />\n";
        break;    
    case 'pick':
        $model = $args['model'];
        switch ($model) {
        case 'Category':
            $data = CategoriesService::getAll();
            break;
        case 'TaxCategory':
            $data = TaxesService::getAll();
            break;
        case 'Tax':
            $cats = TaxesService::getAll();
            $data = array();
            foreach ($cats as $cat) {
                $data[] = $cat->getCurrentTax();
            }
            break;
        case 'CustTaxCat':
            $data = CustTaxCatsService::getAll();
            break;
        case 'Role':
            $data = RolesService::getAll();
            break;
        }
        echo '<select id="' . $form_id . '-' . $field . '" name="' . $field . '">';
        if (isset($args['nullable']) && $args['nullable']) {
            echo '<option value=""></option>';
        }
        foreach ($data as $r) {
            $selected = "";
            if ($object != NULL && ($object->{$field} == $r->id
                    || (is_object($object->{$field}) && $object->{$field}->id == $r->id))) {
                $selected = ' selected="true"';
            }
            echo '<option value="' . $r->id . '"' . $selected . '>'
                    . $r->label . '</option>';
        }
        echo "</select>\n";
        break;
    case 'pick_multiple':
        $model = $args['model'];
        switch ($model) {
        case 'Category':
            $data = CategoriesService::getAll();
            break;
        }
        foreach ($data as $r) {
            $selected = "";
            if ($object != NULL
                    && (array_search($r->id, $object->{$field}) !== FALSE)) {
                $selected = ' checked="true"';
            }
            $id = $form_id . "-" . $field . "-" .$r->id;
            echo '<label for="' . $id . '">' . $r->label . '</label>';
            echo '<input id="' . $id . '" type="checkbox" name="' . $field
                    . '[]" value="' . $r->id . '"' . $selected . "/>\n";
        }
        break;
    }
    echo "</div>";
}

function form_send() {
    echo '<button class="btn-send" type="submit">' . \i18n('Send') . '</button>';
}
function form_save() {
    echo '<button class="btn-send" type="submit">' . \i18n('Save') . '</button>';
}
function form_delete($what, $id, $img_src = NULL) {
    echo '<input type="hidden" name="delete-' . $what . '" value="' . $id . '" />';
    if ($img_src == NULL) {
        echo '<button type="submit">' . \i18n('Delete') . '</button>';
    } else {
        echo '<button type="submit"><img src="' . $img_src . '" alt="' . \i18n('Delete') . '" title="' . \i18n('Delete') . '" /></button>';
    }
}
