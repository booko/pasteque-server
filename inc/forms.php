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

/** Escape data to be used inside html attribute */
function esc_attr($value) {
    return htmlspecialchars($value);
}
/** Escape data to be used in html content */
function esc_html($value) {
    return htmlspecialchars($value, ENT_NOQUOTES);
}
/** Escape a JS variable to be enclosed in double quotes */
function esc_js($value) {
    return addslashes($value);
}

function form_hidden($form_id, $object, $field) {
    if ($object != NULL && isset($object->{$field})) {
        echo '<input type="hidden" name="' . esc_attr($field) . '" value="'
                . esc_attr($object->{$field}) . "\"/>\n";
    }
}
function form_value_hidden($form_id, $name, $value) {
    echo '<input type="hidden" name="' . esc_attr($name)
            . '" value="' . esc_attr($value) . "\"/>\n";
}

function form_input($form_id, $class, $object, $field, $type, $args = array()) {
    if (!isset($args['nolabel']) || $args['nolabel'] === false) {
        echo "<div class=\"row\">\n";
    }
    if (isset($args['nameid']) && $args['nameid'] == true) {
        $name = $field . "-" . $form_id;
    } else {
        $name = $field;
    }
    if (isset($args['array']) && $args['array'] == true) {
        $name = $name . "[]";
    }
    if ($type != "pick_multiple") {
        if (!isset($args['nolabel']) || $args['nolabel'] === false) {
            echo '<label for="' . esc_attr($form_id . '-' . $field) . '">';
            $fieldLabel = $field;
            if (substr($field, -2) == "Id") {
                $fieldLabel = substr($field, 0, -2);
            }
            echo esc_html(\i18n($class . "." . $fieldLabel));
            echo "</label>\n";
        }
    }
    $required = "";
    if (isset($args['required']) && $args['required']) {
        $required = ' required="true"';
    }
    switch ($type) {
    case 'string':
        echo '<input id="' . esc_attr($form_id . '-' . $field)
                . '" type="text" name="' . esc_attr($name) . '"';
        if ($object != NULL) {
            echo ' value="' . esc_attr($object->{$field}) . '"';
        }
        echo "$required />\n";
        break;
    case 'text':
        echo '<textarea id="' . esc_attr($form_id . '-' . $field)
                . '" name="' . esc_attr($name) . '">';
        if ($object != NULL) {
            echo esc_html($object->{$field});
        }
        echo '</textarea>';
        break;
    case 'numeric':
        echo '<input id="' . esc_attr($form_id . '-' . $field)
                . '" type="numeric" name="' . esc_attr($name) . '"';
        if ($object != NULL) {
            echo ' value="' . esc_attr($object->{$field}) . '"';
        }
        echo "$required />\n";
        break;
    case 'boolean':
        echo '<input id="' . esc_attr($form_id . '-' . $field)
                . '" type="checkbox" name="' . esc_attr($name) . '"';
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
        echo '<input id="' . esc_attr($form_id . '-' . $field)
                . '" type="number" step="' . esc_attr($step)
                . '" min="0.00" name="' . esc_attr($name) . '"';
        if ($object != NULL) {
            echo ' value="' . esc_attr($object->{$field}) . '"';
        }
        echo "$required />\n";
        break;
    case 'date':
        // Class dateinput will be catched to show js date picker
        echo '<input id="' . esc_attr($form_id . '-' . $field)
                . '" type="text" class="dateinput" name="' . esc_attr($name) . '"';
        if ($object !== null) {
            if (isset($args['dataformat'])) {
                if ($args['dataformat'] == 'standard') {
                    $timestamp = stdtimefstr($object->{$field});
                } else {
                    $timestamp = timefstr($args['dataformat'],
                            $object->{$field});
                }
            } else {
                $timestamp = $object->{$field};
            } 
            echo ' value="' . esc_attr(\i18nDate($timestamp)) . '"';
        }
        echo "$required />\n";
        break;    
    case 'pick':
        $model = $args['model'];
        $data = $args['data'];
        if ($model !== null) {
            switch ($model) {
            case 'Category':
                $data = CategoriesService::getAll(false);
                break;
            case 'Provider':
                $data = ProvidersService::getAll();
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
            case 'Attribute':
                $data = AttributesService::getAllAttrs();
                break;
            case 'AttributeSet':
                $data = AttributesService::getAll();
                break;
            case 'Location':
                $locSrv = new LocationsService();
                $data = $locSrv->getAll();
                break;
            case 'DiscountProfile':
                $profSrv = new DiscountProfilesService();
                $data = $profSrv->getAll();
                break;
            case 'TariffArea':
                $areaSrv = new TariffAreasService();
                $data = $areaSrv->getAll();
                break;
            }
        }
        echo '<select id="' . esc_attr($form_id . '-' . $field)
                . '" name="' . esc_attr($name) . '">';
        if (isset($args['nullable']) && $args['nullable']) {
            echo '<option value=""></option>';
        }
        foreach ($data as $r) {
            $selected = "";
            $r_id = $r->id;
            $r_label = $r->label;
            if ($model == null) {
                $r_id = $r['id'];
                $r_label = $r['label'];
            }
            if ($object != NULL && ($object->{$field} == $r_id
                    || (is_object($object->{$field}) && $object->{$field}->id == $r_id))) {
                $selected = ' selected="true"';
            }
            echo '<option value="' . esc_attr($r_id) . '"' . $selected . '>'
                    . esc_html($r_label) . '</option>';
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
            echo '<label for="' . esc_attr($id) . '">' . esc_html($r->label) . '</label>';
            echo '<input id="' . esc_attr($id) . '" type="checkbox" name="'
                    . esc_attr($name) . '[]" value="' . esc_attr($r->id) . '"'
                    . $selected . "/>\n";
        }
        break;
    }
    if (!isset($args['nolabel']) || $args['nolabel'] === false) {
        echo "</div>";
    }
}

/** Create a select with given labels. For relation in a model use form_input
 * with type pick */
function form_select($id, $label, $values, $labels, $currentValue) {
    echo "<div class=\"row\">\n";
    echo "<label for=\"" . esc_attr($id) ."\">" . esc_html($label) . "</label>";
    echo "<select id=\"" . esc_attr($id) . "\" name=\"" . esc_attr($id) . "\">>";
    for ($i = 0; $i < count($values); $i++) {
        $selected = "";
        if ($values[$i] === $currentValue) {
            $selected = ' selected="true"';
        }
        echo '<option value="' . esc_attr($values[$i]) . '"' . $selected . '>'
                . esc_html($labels[$i]) . '</option>';
    }
    echo "</select>";
    echo "</div>";
}

function form_send() {
    echo '<button class="btn-send" type="submit">' . \i18n('Send') . '</button>';
}
function form_save() {
    echo '<button class="btn-send" type="submit">' . \i18n('Save') . '</button>';
}
function form_delete($what, $id, $img_src = NULL) {
    echo '<input type="hidden" name="delete-' . esc_attr($what)
            . '" value="' . esc_attr($id) . '" />';
    if ($img_src == NULL) {
        echo '<button onclick="return confirm(\'' . \i18n('confirm deletion') . '\');return false;" type="submit"><a class="btn btn-delete">' . \i18n('Delete') . '</a></button>';
    } else {
        echo '<button onclick="return confirm(\'' . \i18n('confirm deletion') . '\');return false;" type="submit"><a class="btn btn-delete"><img src="' . esc_attr($img_src)
                . '" alt="' . esc_attr(\i18n('Delete'))
                . '" title="' . esc_attr(\i18n('Delete')) . '" /></a></button>';
    }
}
