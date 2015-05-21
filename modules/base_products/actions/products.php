<?php
//    Pastèque Web back office, Products module
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

// products action

namespace BaseProducts;

$message = null;
$error = null;
if (isset($_GET['delete-product'])) {
    if (\Pasteque\ProductsService::delete($_GET['delete-product'])) {
        $message = \i18n("Changes saved") ;
    } else {
        $message = "Le produit a été placé en archive (car déjà vendu ou en stock)";
    }
}

if(!isset($_GET["start"])) {
    $start = 0;
}
else {
    $start = $_GET["start"];
}
if(!isset($_GET["range"])) {
    $range = 50;
}
else {
    $range = $_GET["range"];
}
if(!isset($_GET["hidden"])) {
    $hidden = true;
}
else {
    $hidden = $_GET["hidden"];
}

if($range == "all") {
    $products = \Pasteque\ProductsService::getAll($hidden);
    $totalProducts = \Pasteque\ProductsService::getTotal($hidden);
}
else if(isset($_GET["category"])) {
    $products = \Pasteque\ProductsService::getByCategory($_GET["category"]);
    $totalProducts = \Pasteque\ProductsService::getTotalByCategory($_GET["category"],$hidden);
}
else {
    $products = \Pasteque\ProductsService::getRange($range,$start,$hidden);
    $totalProducts = \Pasteque\ProductsService::getTotal($hidden);
}
$categories = \Pasteque\CategoriesService::getAll();
$prdCat = array();
$archivesCat = array();
foreach ($products as $product) {
    if ($product->categoryId !== \Pasteque\CompositionsService::CAT_ID) {
        $prdCat[$product->categoryId][] = $product;
    }
    // Archive will be filled on display loop
}
?>

<!-- start bloc titre -->
<div class="blc_ti">
    <h1><?php \pi18n("Products", PLUGIN_NAME); ?></h1>
    <span class="nb_article"><?php \pi18n("%d products", PLUGIN_NAME, $totalProducts); ?></span>
    <?php \Pasteque\tpl_msg_box($message, $error); ?>
    <ul class="bt_fonction">
            <li><?php \Pasteque\tpl_btn('btn bt_add ', \Pasteque\get_module_url_action(PLUGIN_NAME, "product_edit"),
            \i18n('Add a product', PLUGIN_NAME), 'img/btn_add.png');?></li>
            <li><?php \Pasteque\tpl_btn('btn bt_import ', \Pasteque\get_module_url_action(PLUGIN_NAME, "productsManagement"),
            \i18n('Import products', PLUGIN_NAME), 'img/btn_add.png');?></li>
            <li><?php \Pasteque\tpl_btn('btn bt_export ', \Pasteque\get_report_url(PLUGIN_NAME, "products_export"),
            \i18n('Export products', PLUGIN_NAME), 'img/btn_add.png');?></li>
    </ul>
</div>

<!-- start container scroll -->
<div class="container_scroll">
                <div class="stick_row stickem-container">
                    <!-- start colonne contenu -->
                    <div id="content_liste" class="grid_9">
                        <div class="blc_content">
<?php \Pasteque\tpl_msg_box($message, $error); ?>

<?php \Pasteque\tpl_pagination($totalProducts,$range,$start); ?>

<?php
$par = false;
$archive = false;
foreach ($categories as $category) {
    if (isset($prdCat[$category->id])) { 

$anchor = \Pasteque\esc_html($category->label);
$anchor = str_replace(' ','',$anchor);
?>

<h2 id="<?php echo $anchor; ?>"><?php echo \Pasteque\esc_html($category->label); ?></h2>
<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th></th>
			<th><?php \pi18n("Product.reference"); ?></th>
			<th><?php \pi18n("Product.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
        foreach ($prdCat[$category->id] as $product) {
            if ($product->visible) {
                $par = !$par;
                if ($product->hasImage) {
                    $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=product&id=" . $product->id;
                } else {
                    $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=product";
                }
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
	    <td><img class="thumbnail" src="?<?php echo $imgSrc ?>" />
		<td><?php echo $product->reference; ?></td>
		<td><?php echo $product->label; ?></td>
		<td class="edition">
                    <?php \Pasteque\tpl_btn('btn-edition', \Pasteque\get_module_url_action(
                            PLUGIN_NAME, 'product_edit', array("id" => $product->id)), "",
                            'img/edit.png', \i18n('Edit'), \i18n('Edit'));
                    ?>
                    <?php \Pasteque\tpl_btn('btn-delete', \Pasteque\get_current_url() . "&delete-product=" . $product->id, "",
                            'img/delete.png', \i18n('Delete'), \i18n('Delete'), true);
                    ?>
		</td>
	</tr>
<?php
            } else {
                $archive = true;
                $archivesCat[$category->id][] = $product;
            }
        }
?>
	</tbody>
</table>
<?php
    }
}
?>


<?php if ($archive) {
foreach ($categories as $category) {
    if (isset($archivesCat[$category->id])) {
        $anchor = \Pasteque\esc_html($category->label);
        $anchor = str_replace(' ','',$anchor);
?>
<h2 id="<?php echo $anchor; ?>"><?php echo \Pasteque\esc_html($category->label); ?></h2>
<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th></th>
			<th><?php \pi18n("Product.reference"); ?></th>
			<th><?php \pi18n("Product.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
        $par = false;
        foreach ($archivesCat[$category->id] as $product) {
            if (!$product->visible) {
                $par = !$par;
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
	    <td><img class="thumbnail" src="?<?php echo \Pasteque\PT::URL_ACTION_PARAM; ?>=img&w=product&id=<?php echo $product->id; ?>" />
		<td><?php echo $product->reference; ?></td>
		<td><?php echo $product->label; ?></td>
		<td class="edition">
			<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'product_edit', array('id' => $product->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
		</td>
	</tr>
<?php
            }
        }
?>
	</tbody>
</table>
<?php
    }
}
} // archive end ?>

<?php
if (count($products) == 0) {
?>
<div class="alert"><?php \pi18n("No product found", PLUGIN_NAME);?></div>
<?php
}
?>


</div></div>
                    <!-- end colonne contenu -->
                    <!-- start sidebar menu -->
                    <div id="sidebar_menu" class="grid_3 stickem">
                        <div class="blc_content visible">
                            <ul id="menu_site">
                                <li>
                                    <a href="#">Accès rapide</a>
<?php
    \Pasteque\tpl_form('select', 'category', \Pasteque\CategoriesService::getAll());
?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- end sidebar menu -->
                </div>
            </div>
            <!-- end container scroll -->
