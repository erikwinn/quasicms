<div class="PersonEditPanel">	
    <div class="formControls">

		<?php $_CONTROL->txtNamePrefix->RenderWithName(); ?>

		<?php $_CONTROL->txtFirstName->RenderWithName(); ?>

		<?php $_CONTROL->txtMiddleName->RenderWithName(); ?>

		<?php $_CONTROL->txtLastName->RenderWithName(); ?>

		<?php $_CONTROL->txtNameSuffix->RenderWithName(); ?>

		<?php $_CONTROL->txtNickName->RenderWithName(); ?>

		<?php $_CONTROL->txtEmailAddress->RenderWithName(); ?>

		<?php $_CONTROL->txtPhoneNumber->RenderWithName(); ?>

		<?php
        //$_CONTROL->txtAvatarUri->RenderWithName();
        ?>

		<?php $_CONTROL->txtCompanyName->RenderWithName(); ?>

		<?php
        //$_CONTROL->lstUsergroups->RenderWithName(true, "Rows=7");
        ?>

	</div>

	<div class="formActions">
		<div class="save"><?php $_CONTROL->btnSave->Render(); ?></div>
		<div class="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
		<div class="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
	</div>
</div>
