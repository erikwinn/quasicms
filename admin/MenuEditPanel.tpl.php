<?php
	// This is the HTML template include file (.tpl.php) for menuEditPanel.
	// Remember that this is a DRAFT.  It is MEANT to be altered/modified.
	// Be sure to move this out of the drafts/dashboard subdirectory before modifying to ensure that subsequent 
	// code re-generations do not overwrite your changes.
?>
	<div id="formControls">
		<?php $_CONTROL->lblId->RenderWithName(); ?>

		<?php $_CONTROL->txtName->RenderWithName(); ?>

		<?php $_CONTROL->txtTitle->RenderWithName(); ?>

		<?php $_CONTROL->txtCssClass->RenderWithName(); ?>

		<?php $_CONTROL->txtSortOrder->RenderWithName(); ?>

		<?php $_CONTROL->chkShowTitle->RenderWithName(); ?>

		<?php $_CONTROL->txtMenuItemId->RenderWithName(); ?>

		<?php $_CONTROL->lstPublicPermissions->RenderWithName(); ?>

		<?php $_CONTROL->lstUserPermissions->RenderWithName(); ?>

		<?php $_CONTROL->lstGroupPermissions->RenderWithName(); ?>

		<?php $_CONTROL->lstStatus->RenderWithName(); ?>

		<?php $_CONTROL->lstType->RenderWithName(); ?>

		<?php $_CONTROL->lstContentBlocks->RenderWithName(true, "Rows=7"); ?>

		<?php $_CONTROL->lstMenuItemsAsItem->RenderWithName(true, "Rows=7"); ?>

	</div>

	<div id="formActions">
		<div id="save"><?php $_CONTROL->btnSave->Render(); ?></div>
		<div id="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
		<div id="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
	</div>
