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

class UsersService extends AbstractService {

    protected static $dbTable = "PEOPLE";
    protected static $dbIdField = "ID";
    protected static $fieldMapping = array(
            "ID" => "id",
            "NAME" => "name",
            "ROLE" => "roleId",
            "APPPASSWORD" => "password",
            "CARD" => "card",
            "VISIBLE" => array("type" => DB::BOOL, "attr" => "visible"),
    );

    protected function build($dbUser, $pdo = null) {
        $db = DB::get();
        $user = User::__build($dbUser['ID'], $dbUser['NAME'],
                $dbUser['APPPASSWORD'], $dbUser['CARD'], $dbUser['ROLE'],
                $db->readBool($dbUser['VISIBLE']), $dbUser['IMAGE'] != null);
        return $user;
    }

    public function create($user) {
        $pdo = PDOBuilder::getPDO();
        $db = DB::get();
        $stmt = $pdo->prepare("INSERT INTO PEOPLE (ID, NAME, APPPASSWORD, "
                . "CARD, ROLE, VISIBLE) VALUES "
                . "(:id, :label, :pwd, :card, :roleId, :vis)");
        $id = md5(time() . rand());
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":label", $user->name);
        $stmt->bindParam(":pwd", $user->password);
        $stmt->bindParam(":card", $user->card);
        $stmt->bindParam(":roleId", $user->roleId);
        $stmt->bindParam(":vis", $db->boolVal($user->visible));
        if ($stmt->execute() !== false) {
            return $id;
        } else {
            return false;
        }
    }
}