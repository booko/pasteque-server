<?php

namespace BaseProducts;

// open csv return null if the file selected had not extension "csv"
// or user not selected file
function init_csv() {

    if ($_FILES['csv']['tmp_name'] === NULL) {
        return NULL;
    }
    $ext = strchr($_FILES['csv']['type'], "/");
    $ext = strtolower($ext);

    if($ext !== "/csv") {
        return NULL;
    }

    $key = array('Designation', 'Parent', 'Ordre');

    $csv = new \Pasteque\Csv($_FILES['csv']['tmp_name'], $key);

    if (!$csv->open()) {
        return $csv;
    }

    return $csv;
}
function import_csv($csv) {

    $error_mess = array();
    $update = 0;
    $create = 0;
    $error=0;

    while ($tab = $csv->readLine()) {
        $parentOk = false;
        if ($tab['Parent'] !== NULL) {
            $parent = \Pasteque\CategoriesService::getByName($tab['Parent']);
            $image = NULL;
            if ($parent) {
                $parentOk = true;
                $tab['Parent'] = $parent->id;
            }
        } else {
            // Category isn't subCategory
            $parentOk = true;
        }

        if ($parentOk) {
            $cat = new \Pasteque\Category($tab['Parent'], $tab['Designation'],
                $image, $tab['Ordre']);

            $category_exist = \Pasteque\CategoriesService::getByName($cat->label);
            //UPDATE category
            if ($category_exist) {
                $cat->id = $category_exist->id;
                if (\Pasteque\CategoriesService::updateCat($cat)) {
                    $update++;
                } else {
                    $error++;
                    $error_mess[] = \i18n("On line %d: Cannot update category: '%s'", PLUGIN_NAME,
                            $csv->getCurrentLineNumber(), $tab['Designation']);
                }
            //CREATE category
            } else {
                $id = \Pasteque\CategoriesService::createCat($cat);
                if ($id) {
                    $create++;
                } else {
                    $error++;
                    $error_mess[] = \i18n("On line %d: Cannot create category: '%s'", PLUGIN_NAME,
                            $csv->getCurrentLineNumber(), $tab['Designation']);
                }
            }
        } else {
                $error++;
                $error_mess[] = \i18n("On line %d: Category parent doesn't exist",
                        PLUGIN_NAME, $csv->getCurrentLineNumber());
        }
    }

    $message = \i18n("%d line(s) inserted, %d line(s) modified, %d error(s)",
            PLUGIN_NAME, $create, $update, $error );

    $csv->close();
    \Pasteque\tpl_msg_box($message, $error_mess);
}
?>

<?php
if (isset($_FILES['csv'])) {
    $dateStr = isset($_POST['date']) ? $_POST['date'] : \i18nDate(time());
    $dateStr = \i18nRevDate($dateStr);
    $date = \Pasteque\stdstrftime($dateStr);

    $csv = init_csv();
    if ($csv === NULL) {
        \Pasteque\tpl_msg_box(NULL, \i18n("Selected file empty or bad format", PLUGIN_NAME));
    } else if (!$csv->isOpen()) {
        $err = array();
        foreach ($csv->getErrors() as $mess) {
            $err[] = \i18n($mess);
        }
        \Pasteque\tpl_msg_box(NULL, $err);
    } else {
        import_csv($csv, $date);
    }
}
?>

<h1><?php \pi18n("Import category from csv file", PLUGIN_NAME); ?></h1>
<form class="edit" method="post" action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'categoriesManagement');?>" enctype="multipart/form-data">
    <div class="row">
        <label for='csv' >
            <?php \pi18n("File", PLUGIN_NAME) ?>:
        </label>
            <input type="file" name="csv">
    </div>
    <div class="row actions">
        <button class="btn-send" type="submit" id="envoyer" name="envoyer" >
            <?php \pi18n("send", PLUGIN_NAME)?>
        </button>
    </div>
</form>