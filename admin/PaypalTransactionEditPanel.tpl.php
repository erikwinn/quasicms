<?php
	// This is the HTML template include file (.tpl.php) for paypal_transactionEditPanel.
	// Remember that this is a DRAFT.  It is MEANT to be altered/modified.
	// Be sure to move this out of the drafts/dashboard subdirectory before modifying to ensure that subsequent 
	// code re-generations do not overwrite your changes.
?>
	<div id="formControls">
		<?php $_CONTROL->lblId->RenderWithName(); ?>

		<?php $_CONTROL->lstOrder->RenderWithName(); ?>

		<?php $_CONTROL->txtCorrelationId->RenderWithName(); ?>

		<?php $_CONTROL->txtTransactionId->RenderWithName(); ?>

		<?php $_CONTROL->txtPpToken->RenderWithName(); ?>

		<?php $_CONTROL->txtPayerId->RenderWithName(); ?>

		<?php $_CONTROL->txtPayerStatus->RenderWithName(); ?>

		<?php $_CONTROL->txtPaymentStatus->RenderWithName(); ?>

		<?php $_CONTROL->txtAckReturned->RenderWithName(); ?>

		<?php $_CONTROL->txtApiAction->RenderWithName(); ?>

		<?php $_CONTROL->calTimeStamp->RenderWithName(); ?>

		<?php $_CONTROL->txtApiVersion->RenderWithName(); ?>

		<?php $_CONTROL->txtMessages->RenderWithName(); ?>

		<?php $_CONTROL->txtAmount->RenderWithName(); ?>

		<?php $_CONTROL->txtPpFee->RenderWithName(); ?>

		<?php $_CONTROL->lstPaymentMethod->RenderWithName(); ?>

	</div>

	<div id="formActions">
		<div id="save"><?php $_CONTROL->btnSave->Render(); ?></div>
		<div id="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
		<div id="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
	</div>
