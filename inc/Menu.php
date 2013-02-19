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

class MenuEntry {
    private $nameDomain;
    private $name;
    private $action;

    public function __construct($name, $action, $domain = NULL) {
        $this->name = $name;
        $this->action = $action;
        $this->nameDomain = $domain;
    }

    public function getName() { return $this->name; }
    public function getNameDomain() { return $this->nameDomain; }
    public function getAction() { return $this->action; }
}

class MenuSection {
    private $nameDomain;
    private $name;
    private $entries;

    public function __construct($name, $domain = NULL) {
        $this->name = $name;
        $this->nameDomain = $domain;
        $this->entries = array();
    }

    public function addEntry($menuEntry) {
        $this->entries[] = $menuEntry;
    }

    public function getName() { return $this->name; }
    public function getNameDomain() { return $this->nameDomain; }
    public function getEntries() { return $this->entries; }
}

class Menu {

    private $sections;

    public function __construct() {
        $this->sections = array();
        $this->addSection("general", "General");
        $entry = new MenuEntry("Home", "home");
        $this->addEntry("general", $entry);
    }

    public function addSection($id, $name, $nameDomain = NULL) {
        if (!isset($this->sections[$id])) {
            $this->sections[$id] = new MenuSection($name, $nameDomain);
            return TRUE;
        }
        return FALSE;
    }

    public function addEntry($sectionId, $entry) {
        if (isset($this->sections[$sectionId])) {
            $this->sections[$sectionId]->addEntry($entry);
            return TRUE;
        }
        return FALSE;
    }

    public function registerModuleEntry($sectionId, $module_name, $name,
            $action) {
        $entry = new MenuEntry($name, get_module_action($module_name, $action),
                $module_name);
        return $this->addEntry($sectionId, $entry);
    }

    public function getSections() {
        return $this->sections;
    }
}

$MENU = new Menu();
?>
