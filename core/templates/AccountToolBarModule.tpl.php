<div id="ToolBarInner">
<?php
    if(null != $_CONTROL->Account)
    {
        print '<div class="twisty">';
        $_CONTROL->btnToggleMenu->Render();
        print 'My Account </div>';
        $_CONTROL->pnlMenuBody->Render();
        $_CONTROL->objDefaultWaitIcon->Render();
        print '<div id="ToolBarBody">';
            $_CONTROL->pnlList->Render();
            $_CONTROL->pnlEdit->Render();
        print '</div>';
    }        
?>

</div>
