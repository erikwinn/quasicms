<?php
	// This is the HTML template include file (.tpl.php) for shipping_methodEditPanel.
	// Remember that this is a DRAFT.  It is MEANT to be altered/modified.
	// Be sure to move this out of the drafts/dashboard subdirectory before modifying to ensure that subsequent 
	// code re-generations do not overwrite your changes.
?>
	<div id="formControls">
		<?php $_CONTROL->lblId->RenderWithName(); ?>

		<?php $_CONTROL->txtTitle->RenderWithName(); ?>

		<?php $_CONTROL->txtCarrier->RenderWithName(); ?>

		<?php $_CONTROL->txtServiceType->RenderWithName(); ?>

		<?php $_CONTROL->txtClassName->RenderWithName(); ?>

		<?php $_CONTROL->txtTransitTime->RenderWithName(); ?>

		<?php $_CONTROL->txtDescription->RenderWithName(); ?>

		<?php $_CONTROL->chkActive->RenderWithName(); ?>

		<?php $_CONTROL->chkIsInternational->RenderWithName(); ?>

		<?php $_CONTROL->chkTestMode->RenderWithName(); ?>

		<?php $_CONTROL->txtSortOrder->RenderWithName(); ?>

	</div>

	<div id="formActions">
		<div id="save"><?php $_CONTROL->btnSave->Render(); ?></div>
		<div id="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
		<div id="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
	</div>
