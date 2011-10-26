	<div id="formControls">
		<?php $_CONTROL->lblProduct->RenderWithName(); ?>

		<?php $_CONTROL->lblOrder->RenderWithName(); ?>

		<?php $_CONTROL->txtQuantity->RenderWithName(); ?>

	</div>

	<div id="formActions">
		<div id="save"><?php $_CONTROL->btnSave->Render(); ?></div>
		<div id="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
		<div id="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
	</div>
