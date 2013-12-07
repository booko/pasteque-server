<?php
namespace Pasteque;

class SubGroups {
    public $id;
    public $composition;
    public $label;
    public $image;
    public $dispOrder;
    public $groups;

    static function __build($id, $composition_id, $label,
            $dispOrder = 0, $groups = Array(), $image = NULL) {

        $sbg = new SubGroups($composition_id, $label,
                $dispOrder, $image, $groups);

        $sbg->id = $id;
        return $sbg;
    }

    function __construct($composition_id, $label, $dispOrder, $image, $groups = Array()) {
        $this->composition = $composition_id;
        $this->label = $label;
        $this->image = $image;
        $this->dispOrder = $dispOrder;
        $groups = $groups;
    }

    function addProduct($subgroupProd) {
        $this->groups[] = $subgroupProd;
    }
}

class SubGroupsProduct {
    public $subgroup;
    public $product;
    public $label;
    public $dispOrder;

    static function __build($product_id, $group_id, $label, $dispOrder = 0) {
        $sbgp = new SubGroupsProduct($product_id, $group_id, $label, 0);
        return $sbgp;
    }

    function __construct($product_id, $group_id, $label, $dispOrder = 0) {
        $this->product = $product_id;
        $this->subgroup = $group_id;
        $this->label = $label;
        $this->dispOrder = $dispOrder;
    }
}
?>
