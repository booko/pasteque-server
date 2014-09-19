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

require_once("url_broker.php");

class MenuEntry {

    const ACTION = 1;
    const REPORT = 2;

    private $type;
    private $nameDomain;
    private $img;
    private $name;
    private $action;

    public function __construct($type, $name, $img, $action, $domain = NULL) {
        $this->type = $type;
        $this->img = $img;
        $this->name = $name;
        $this->action = $action;
        $this->nameDomain = $domain;
    }

    public function getImg() { return $this->img; }
    public function getName() { return $this->name; }
    public function getNameDomain() { return $this->nameDomain; }
    public function getAction() { return $this->action; }
    // Used to get the last param of action for css purposes
    public function getActionName() { 
        $substr=explode("/",$this->action);
	return $substr[sizeof($substr)-1];
    }
    // Used to know if current entry for css purposes
    public function isActive() { 
		switch($this->getType()) {
			case MenuEntry::REPORT:
				$url = get_report_url($this->getNameDomain(),$this->getAction(),'display');
				break;
			case MenuEntry::ACTION:
				$url = get_url_action($this->getAction());
				break;
		}
		if(get_current_url() == $url)
			return true;
		return false;
    }
    public function getType() { return $this->type; }
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
    // Used to know if current entry for css purposes
    public function isActive() { 
	foreach($this->entries as $entry) {
		switch($entry->getType()) {
			case MenuEntry::REPORT:
				$url = get_report_url($entry->getNameDomain(),$entry->getAction(),'display');
				break;
			case MenuEntry::ACTION:
				$url = get_url_action($entry->getAction());
				break;
		}
		if(get_current_url() == $url)
			return true;
	}
	return false;
    }
}

class Menu {

    private $sections;

    public function __construct() {
        $this->sections = array();
        $this->addSection("general", "General");
        $entry = new MenuEntry(MenuEntry::ACTION, "Home", "menu_home.png",
                "home");
        $this->addEntry("general", $entry);
        if (can_logout()) {
            $logout = new MenuEntry(MenuEntry::ACTION, "Logout",
                    "menu_logout.png", "logout");
            $this->addENtry("general", $logout);
        }
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

    public function registerModuleEntry($sectionId, $moduleName, $img, $name,
            $action) {
        $entry = new MenuEntry(MenuEntry::ACTION, $name, $img,
                get_module_action($moduleName, $action), $moduleName);
        return $this->addEntry($sectionId, $entry);
    }
    public function registerModuleReport($sectionId, $moduleName, $img, $name,
            $target) {
        $entry = new MenuEntry(MenuEntry::REPORT, $name, $img,
                $target, $moduleName);
        return $this->addEntry($sectionId, $entry);
    }

    public function getSections() {
        return $this->sections;
    }
}

global $MENU;
$MENU = new Menu();
