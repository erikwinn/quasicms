<div class="AccountInfoEditPanel">
	
        <?php $_CONTROL->btnChangePassword->Render(); ?>
    <div class="spacer"></div>
    <hr>
    <div class="spacer"></div>
    <div class="heading">
        <?php print Quasi::Translate('Personal Information') . ':'; ?>
    </div>
    
    <div class="spacer"></div>
    <div class="spacer"></div>
    
    <div class="formControls">

		<?php $_CONTROL->txtNamePrefix->RenderWithName(); ?>

		<?php $_CONTROL->txtFirstName->RenderWithName(); ?>

		<?php $_CONTROL->txtMiddleName->RenderWithName(); ?>

		<?php $_CONTROL->txtLastName->RenderWithName(); ?>

		<?php $_CONTROL->txtNameSuffix->RenderWithName(); ?>

		<?php $_CONTROL->txtNickName->RenderWithName(); ?>

		<?php $_CONTROL->txtEmailAddress->RenderWithName(); ?>

		<?php $_CONTROL->txtPhoneNumber->RenderWithName(); ?>

		<?php $_CONTROL->txtCompanyName->RenderWithName(); ?>


	</div>
    <div class="spacer"></div>

	<div class="formActions">
		<div class="save"><?php $_CONTROL->btnSave->Render(); ?></div>
	</div>
</div>