<div id="LostPasswordModuleInner">

    <p>
       <?php $_CONTROL->lblInstructions->Render();?>
       <?php $_CONTROL->txtUserName->RenderWithError();?>
    </p>

    <p>
        <?php $_CONTROL->btnSubmit->Render(); ?>
    </p>
    
    <p>
        <?php $_CONTROL->lblMessage->Render(); ?>
    </p>    

</div>
