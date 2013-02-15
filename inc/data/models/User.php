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

class User {

    public $id;
    public $name;
    public $permissions;

    static function __build($id, $name, $permissions) {
        $user = new User($name, $permissions);
        $user->id = $id;
        return $user;
    }

    function __construct($name, $permissions) {
        $this->name = $name;
        $this->permissions = $permissions;
    }

    static function __form($f) {
        if (!isset($f['name'])) {
            return NULL;
        }
        $permissions = array();
        if (isset($f['permissions'])) {
            foreach ($f['permissions'] as $perm) {
                $permissions[] = $perm;
            }
        }
        if (isset($f['id'])) {
            return User::__build($f['id'], $f['name'], $permissions);
        } else {
            return new User($f['name'], $permissions);
        }
    }

    function hasPermission($permission) {
        return (array_search($permission, $this->permissions) !== FALSE);
    }

}

?>
