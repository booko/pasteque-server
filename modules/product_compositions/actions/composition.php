<?php
namespace ProductCompositions;

$message = NULL;
$error = NULL;
if (isset($_POST['delete-comp'])) {
    if (\Pasteque\CompositionsService::delete($_POST['delete-comp'])) {
        $message = \i18n("Changes saved");
    } else {
        $error = \i18n("Unable to save changes");
    }
}

$compositions = \Pasteque\CompositionsService::getAll();
?>
<h1><?php \pi18n("Composition", PLUGIN_NAME); ?></h1>

<?php \Pasteque\tpl_msg_box($message, $error); ?>

<?php \Pasteque\tpl_btn('btn', \Pasteque\get_module_url_action(PLUGIN_NAME, "composition_edit"),
        \i18n('Add a composition', PLUGIN_NAME), 'img/btn_add.png');?>


<p><?php \pi18n("%d composition", PLUGIN_NAME, count($compositions)); ?></p>

<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th></th>
			<th><?php \pi18n("Composition.label"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
$par = FALSE;
foreach ($compositions as $composition) {
$par = !$par;
?>
	<tr class="row-<?php echo $par ? 'par' : 'odd'; ?>">
		<td><img class="thumbnail" src="?<?php echo \Pasteque\URL_ACTION_PARAM; ?>=img&w=category&product_id=<?php echo $composition->id; ?>" />
		<td><?php echo $composition->label; ?></td>
		<td class="edition">
            <?php \Pasteque\tpl_btn("edition", \Pasteque\get_module_url_action(PLUGIN_NAME,
                    'composition_edit', array("product_id" => $composition->id)), "",
                    'img/edit.png', \i18n('Edit'), \i18n('Edit'));
            ?>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("comp", $composition->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
}
?>
	</tbody>
</table>
<?php
if (count($compositions) == 0) {
?>
<div class="alert"><?php \pi18n("No category found", PLUGIN_NAME); ?></div>
<?php
}
?>
