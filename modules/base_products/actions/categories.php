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

// categories action

namespace BaseProducts;

$message = NULL;
$error = NULL;
if (isset($_POST['delete-cat'])) {
    if (\Pasteque\CategoriesService::deleteCat($_POST['delete-cat'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
        $error .= " " . \i18n("Only empty category can be deleted", PLUGIN_NAME);
    }
}

$categories = \Pasteque\CategoriesService::getAll();
?>
<h1><?php \pi18n("Categories", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<?php \Pasteque\tpl_btn('btn', \Pasteque\get_module_url_action(PLUGIN_NAME, "category_edit"),
        \i18n('Add a category', PLUGIN_NAME), 'img/btn_add.png');?>
<?php \Pasteque\tpl_btn('btn', \Pasteque\get_module_url_action(PLUGIN_NAME, "categoriesManagement"),
        \i18n('Import categories', PLUGIN_NAME), 'img/btn_add.png');?>


<p><?php \pi18n("%d categories", PLUGIN_NAME, count($categories)); ?></p>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th></th>
			<th><?php \pi18n("Category.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
function printCategory($printCategory, $level) {
        if ($printCategory->hasImage) {
            $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=category&id=" . $printCategory->id;
        } else {
            $imgSrc = \Pasteque\PT::URL_ACTION_PARAM . "=img&w=category";
        }
        ?>
                <tr>
                        <td>
                        <?php
                        for($i=0;$i<$level;$i++) {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                        }
                        ?>
                        <img class="thumbnail" src="?<?php echo $imgSrc ?>" />
                        <td><?php echo $printCategory->label; ?></td>
                        <td class="edition">
                    <?php \Pasteque\tpl_btn("edition", \Pasteque\get_module_url_action(PLUGIN_NAME,
                            'category_edit', array("id" => $printCategory->id)), "",
                            'img/edit.png', \i18n('Edit'), \i18n('Edit'));
                    ?>
                                <form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("cat", $printCategory->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
                        </td>
                </tr>
        <?php
        $categories = \Pasteque\CategoriesService::getChildren($printCategory->id);
        $level++;
        foreach($categories as $childCategory) {
            printCategory($childCategory, $level);
        }
}

foreach ($categories as $category) {
    if($category->parentId == "") {
        printCategory($category, 0); // we start with root categories. As the function is recursive, we don’t need more than this :-)
    }
}
?>
	</tbody>
</table>
<?php
if (count($categories) == 0) {
?>
<div class="alert"><?php \pi18n("No category found", PLUGIN_NAME); ?></div>
<?php
}
?>
