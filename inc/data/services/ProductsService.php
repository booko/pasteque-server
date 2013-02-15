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

class ProductsService {

    private static function buildDBPrd($db_prd, $pdo) {
        $cat = array();
        $stmt = $pdo->prepare("SELECT category_id FROM product_category "
                . "WHERE product_id = :id");
        $stmt->bindParam(":id", $db_prd['id'], \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $cat[] = $row['category_id'];
        }
        return Product::__build($db_prd['id'], $db_prd['ref'],
                                $db_prd['name'], $db_prd['pricesell'],
                                $db_prd['taxcat_id'], $cat);
    }

    static function getAll() {
        $prds = array();
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM products");
        $stmt->execute();
        while ($db_prd = $stmt->fetch()) {
            $prd = ProductsService::buildDBPrd($db_prd, $pdo);
            $prds[] = $prd;
        }
        return $prds;
    }

    
    static function search($where = '', $groupby = '',$orderby='',$limit='',$having='',$full = false) {
        $prds = array();
        $pdo = PDOBuilder::getPDO();
		$supplement_req="";
		if(!empty($where)) $supplement_req.=" WHERE ".$where;
		if(!empty($groupby)) $supplement_req.=" GROUP BY ".$groupby;
		if(!empty($orderby)) $supplement_req.=" ORDER BY ".$orderby;
		if(!empty($limit)) $supplement_req.=" LIMIT ".$limit;
		if(!empty($having)) $supplement_req.=" HAVING ".$having;
		$stmt = $pdo->prepare("SELECT * FROM PRODUCTS".$supplement_req);
        $stmt->execute();
        while ($db_prd = $stmt->fetch()) {
            if ($full) {
                $prd = ProductsService::buildDBPrd($db_prd, $pdo);
            } else {
                $prd = ProductsService::buildDBLightPrd($db_prd, $pdo);
            }
            $prds[] = $prd;
        }
        return $prds;
    }

    
    static function getCount($where = '') {
        $prds = array();
        $pdo = PDOBuilder::getPDO();
		$supplement_req="";
		if(!empty($where)) $supplement_req.=" WHERE ".$where;
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM PRODUCTS".$supplement_req);
        $stmt->execute();
        if ($db_prd = $stmt->fetch()) {
			return $db_prd[0];
        }
		return 0;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE ID = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                $prd = ProductsService::buildDBPrd($row, $pdo);
                return $prd;
            }
        }
        return NULL;
    }

    static function getImage($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            if ( $row = $stmt->fetch()) {
                return $row['image'];
            }
        }
       return NULL;
    }

	static function setImage($id,$fichier) {
        $pdo = PDOBuilder::getPDO();
        $image = file_get_contents($fichier);
        $stmt = $pdo->prepare("UPDATE PRODUCTS SET image = :image  WHERE ID = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':image', $image, PDO::PARAM_LOB);
 //       var_dump($stmt->query(array(':id' => $id,':image'=>$image)));
       $stmt->execute(array(':id' => $id,':image'=>$image));
    }


    static function update($prd) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("UPDATE products SET ref = :ref, "
                . "name = :name, pricesell = :sell, taxcat_id = :tax "
                . "WHERE id = :id");
        $stmt->bindParam(":id", $prd->id, \PDO::PARAM_INT);
        $stmt->bindParam(":name", $prd->name, \PDO::PARAM_STR);
        $stmt->bindParam(":ref", $prd->ref, \PDO::PARAM_STR);
        $stmt->bindParam(":sell", $prd->price_sell, \PDO::PARAM_STR);
        $stmt->bindParam(":tax", $prd->tax_cat_id, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            $del = $pdo->prepare("DELETE FROM product_category WHERE product_id = :id");
            $del->bindParam(":id", $prd->id, \PDO::PARAM_INT);
            $del->execute();
            foreach ($prd->category_ids as $cat) {
                $add = $pdo->prepare("INSERT INTO product_category (product_id, "
                        . "category_id) VALUES (:pid, :cid)");
                $add->bindParam(":pid", $prd->id, \PDO::PARAM_INT);
                $add->bindParam(":cid", $cat, \PDO::PARAM_INT);
                $add->execute();
            }
        }
    }
    
    static function create($prd) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO products (ref, name, "
                . "pricesell, taxcat_id) VALUES "
                . "(:ref, :name, :sell, :tax)");
        $stmt->bindParam(":name", $prd->name, \PDO::PARAM_STR);
        $stmt->bindParam(":ref", $prd->ref, \PDO::PARAM_STR);
        $stmt->bindParam(":sell", $prd->price_sell, \PDO::PARAM_STR);
        $stmt->bindParam(":tax", $prd->tax_cat_id, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            foreach ($prd->category_ids as $cat) {
                $add = $pdo->prepare("INSERT INTO product_category (product_id, "
                        . "category_id) VALUES (:pid, :cid)");
                $add->bindParam(":pid", $prd->id, \PDO::PARAM_INT);
                $add->bindParam(":cid", $cat, \PDO::PARAM_INT);
                $add->execute();
            }
        }
    }
    
    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>
