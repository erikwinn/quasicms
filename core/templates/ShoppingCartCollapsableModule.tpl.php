<div id="CartBoxCollapsableInner">
<?php
    if(null != $_CONTROL->Account)
    {
        print '<div class="twisty">';
        $_CONTROL->Header->Render();
        print 'Shopping Cart </div>';
        print '<div id="CartBoxBody">';
        $_CONTROL->Body->Render();
//        $_CONTROL->DefaultWaitIcon->Render();
        print '</div>';
    }        
?>

</div>
