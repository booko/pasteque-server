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

class PlacesService {

    private static function buildDBPlace($db_place) {
        $place = Place::__build($db_place['ID'], $db_place['NAME'],
                                $db_place['X'], $db_place['Y'],
                                $db_place['FLOOR']);
        return $place;
    }

    private static function buildDBFloor($dbFloor, $pdo) {
        $floor = Floor::__build($dbFloor['ID'], $dbFloor['NAME']);
        $stmt = $pdo->prepare("SELECT * FROM PLACES WHERE FLOOR = :id");
        $stmt->bindParam(":id", $dbFloor['ID']);
        $stmt->execute();
        while ($dbPlace = $stmt->fetch()) {
            $place = PlacesService::buildDBPlace($dbPlace);
            $floor->addPlace($place);
        }
        return $floor;
    }

    static function getAllFloors() {
        $floors = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM FLOORS";
        foreach ($pdo->query($sql) as $dbFloor) {
            $floor = PlacesService::buildDBFloor($dbFloor, $pdo);
            $floors[] = $floor;
        }
        return $floors;
    }

    static function getFloor($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM FLOORS WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                return PlacesService::buildDBFloor($row, $pdo);
            }
        }
        return null;
    }

    static function createFloor($floor, $img = null) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());

        $sql = "INSERT INTO FLOORS (ID, NAME, IMAGE) VALUES (:id, :name, :img)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $floor->label, \PDO::PARAM_STR);
        $stmt->bindParam(":img",$img);
        if (!$stmt->execute()) {
            return false;
        }
        return $id;
    }

    static function deleteFloor($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM FLOORS WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            return true;
        }
        return false;
    }

    static function updateFloor($floor, $image = "") {
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE FLOORS SET NAME = :name ";
        if ($image !== "") {
            $sql .= ", IMAGE = :image ";
        }
        $sql .= "WHERE ID = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":name", $floor->label, \PDO::PARAM_STR);
        if ($image !== "") {
            $stmt->bindParam(":image", $image);
        }
        $stmt->bindParam(":id", $floor->id, \PDO::PARAM_STR);

        return $stmt->execute();
    }

    static function getAllPlaces() {
        $place = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT * FROM PLACES";
        foreach ($pdo->query($sql) as $dbPlace) {
            $place = PlacesService::buildDBPlace($dbPlace, $pdo);
            $places[] = $place;
        }
        return $places;
    }

    static function getAllPlacesByFloorId($idFloor) {
        $places = array();
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PLACES WHERE FLOOR = :floor");
        $stmt->bindParam(":floor", $idFloor, \PDO::PARAM_STR);
        if ($stmt->execute(array(':floor' => $idFloor))) {
            while( $db_place = $stmt->fetch()) {
                $place = PlacesService::buildDBPlace($dbPlace, $pdo);
                $places[] = $place;
            }
        }
        return $places;
    }

    static function getPlace($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PLACES WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                return PlacesService::buildDBFloor($row, $pdo);
            }
        }
        return null;
    }

    static function deletePlace($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM PLACES WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            return true;
        }
        return false;
    }

    static function updatePlace($place) {
        $pdo = PDOBuilder::getPDO();
        $sql = "UPDATE PLACES SET NAME = :name, X = :x, Y = :y, FLOOR = :floor"
                . " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":name", $place->label, \PDO::PARAM_STR);
        $stmt->bindParam(":x", $place->x, \PDO::PARAM_INT);
        $stmt->bindParam(":y", $place->y, \PDO::PARAM_INT);
        $stmt->bindParam(":floor", $place->floorId, \PDO::PARAM_STR);
        $stmt->bindParam(":id", $place->id, \PDO::PARAM_STR);

        return $stmt->execute();
    }

    static function createPlace($place) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());

        $sql = "INSERT INTO PLACES (ID, NAME, X, Y, FLOOR) "
                . "VALUES(:id, :name, :x, :y, :floor)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":id", $id, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $place->label, \PDO::PARAM_STR);
        $stmt->bindParam(":x", $place->x, \PDO::PARAM_INT);
        $stmt->bindParam(":y", $place->y, \PDO::PARAM_INT);
        $stmt->bindParam(":floor", $place->floorId, \PDO::PARAM_STR);

        if (!$stmt->execute()) {
            return NULL;
        }
        return $id;
    }
}