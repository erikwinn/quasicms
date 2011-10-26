<?php
	// This is the HTML template include file (.tpl.php) for content_blockEditPanel.
	// Remember that this is a DRAFT.  It is MEANT to be altered/modified.
	// Be sure to move this out of the drafts/dashboard subdirectory before modifying to ensure that subsequent 
	// code re-generations do not overwrite your changes.
?>
	<div id="formControls">
		<?php $_CONTROL->lblId->RenderWithName(); ?>

		<?php $_CONTROL->txtName->RenderWithName(); ?>

		<?php $_CONTROL->txtCssclass->RenderWithName(); ?>

		<?php $_CONTROL->txtTitle->RenderWithName(); ?>

		<?php $_CONTROL->txtDescription->RenderWithName(); ?>

		<?php $_CONTROL->chkShowTitle->RenderWithName(); ?>

		<?php $_CONTROL->chkShowDescription->RenderWithName(); ?>

		<?php $_CONTROL->chkCollapsable->RenderWithName(); ?>

		<?php $_CONTROL->txtSortOrder->RenderWithName(); ?>

		<?php $_CONTROL->lstParentContentBlock->RenderWithName(); ?>

		<?php $_CONTROL->lstLocation->RenderWithName(); ?>

		<?php $_CONTROL->lstPages->RenderWithName(true, "Rows=7"); ?>

		<?php $_CONTROL->lstContentItems->RenderWithName(true, "Rows=7"); ?>

		<?php $_CONTROL->lstMenus->RenderWithName(true, "Rows=7"); ?>

	</div>

	<div id="formActions">
		<div id="save"><?php $_CONTROL->btnSave->Render(); ?></div>
		<div id="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
		<div id="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
	</div>
