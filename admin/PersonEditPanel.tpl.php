<?php
	// This is the HTML template include file (.tpl.php) for personEditPanel.
	// Remember that this is a DRAFT.  It is MEANT to be altered/modified.
	// Be sure to move this out of the drafts/dashboard subdirectory before modifying to ensure that subsequent 
	// code re-generations do not overwrite your changes.
?>
	<div id="formControls">
		<?php $_CONTROL->lblId->RenderWithName(); ?>

		<?php $_CONTROL->txtNamePrefix->RenderWithName(); ?>

		<?php $_CONTROL->txtFirstName->RenderWithName(); ?>

		<?php $_CONTROL->txtMiddleName->RenderWithName(); ?>

		<?php $_CONTROL->txtLastName->RenderWithName(); ?>

		<?php $_CONTROL->txtNameSuffix->RenderWithName(); ?>

		<?php $_CONTROL->txtNickName->RenderWithName(); ?>

		<?php $_CONTROL->txtEmailAddress->RenderWithName(); ?>

		<?php $_CONTROL->txtPhoneNumber->RenderWithName(); ?>

		<?php $_CONTROL->txtAvatarUri->RenderWithName(); ?>

		<?php $_CONTROL->txtCompanyName->RenderWithName(); ?>

		<?php $_CONTROL->lstOwnerPerson->RenderWithName(); ?>

		<?php $_CONTROL->chkIsVirtual->RenderWithName(); ?>

		<?php $_CONTROL->lstAccount->RenderWithName(); ?>

		<?php $_CONTROL->lstUsergroups->RenderWithName(true, "Rows=7"); ?>

	</div>

	<div id="formActions">
		<div id="save"><?php $_CONTROL->btnSave->Render(); ?></div>
		<div id="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
		<div id="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
	</div>
