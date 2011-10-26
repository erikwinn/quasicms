
<?php
        print '<td>';
        $_CONTROL->lblProductName->Render();
        print '</td><td>';
        $_CONTROL->lblItemPrice->Render();
        print '</td><td>';
        if( $_CONTROL->Modifiable )
            $_CONTROL->txtQuantity->RenderWithError();
        else
            $_CONTROL->lblQuantity->Render();
        print '</td><td>';
        $_CONTROL->lblTotalPrice->Render();
        print '</td>';
?>


