<?php ?>
	<div class="OrderStatusHistory">
		<?php $_CONTROL->lblOrder->RenderWithName(); ?>

		<?php $_CONTROL->lblDate->RenderWithName(); ?>

		<?php $_CONTROL->lblNotes->RenderWithName(); ?>

		<?php $_CONTROL->lblStatus->RenderWithName(); ?>

	</div>

	<div class="formActions">
		<div id="save"><?php $_CONTROL->btnSave->Render(); ?></div>
		<div id="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
		<div id="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
	</div>
