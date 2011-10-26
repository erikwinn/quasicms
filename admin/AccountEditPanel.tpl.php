    <div id="formActions">
        <div id="save"><?php $_CONTROL->btnSave->Render(); ?></div>
        <div id="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
        <div id="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
    </div>
   <br />
   <br />
   <hr>
	<div id="formControls">
        <?php $_CONTROL->lblId->RenderWithName(); ?>
        <?php $_CONTROL->lblPerson->RenderWithName(); ?>

		<?php $_CONTROL->lblRegistrationDate->RenderWithName(); ?>

		<?php $_CONTROL->txtUsername->RenderWithName(); ?>

		<?php $_CONTROL->txtPassword->RenderWithName(); ?>

		<?php $_CONTROL->txtNotes->RenderWithName(); ?>

		<?php $_CONTROL->lblLastLogin->RenderWithName(); ?>

		<?php $_CONTROL->txtLoginCount->RenderWithName(); ?>

		<?php $_CONTROL->chkOnline->RenderWithName(); ?>

		<?php $_CONTROL->chkOnetimePassword->RenderWithName(); ?>

		<?php $_CONTROL->chkValidPassword->RenderWithName(); ?>

		<?php $_CONTROL->lstType->RenderWithName(); ?>

		<?php $_CONTROL->lstStatus->RenderWithName(); ?>

	</div>
 <hr>
    <strong>Orders: </strong>   
        <?php $_CONTROL->dtgOrders->Render(); ?>
   
