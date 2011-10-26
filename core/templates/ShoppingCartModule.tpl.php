<div id="CartBoxInner">
<?php
    if(null != $_CONTROL->Account)
    {
        $_CONTROL->pnlHeader->Render();
        $_CONTROL->pnlItemList->Render();
    }
?>

</div>
