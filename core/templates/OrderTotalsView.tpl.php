<div class="OrderTotalsView">
    
    <?php
        if( $_CONTROL->ShowTitle)
            print '<div class="heading">' . Quasi::Translate('Order Summary') . ':</div>';
    ?>
            
    <div class="OrderTotalsList">
    <table>
    <?php
        print '<tr><td>' . Quasi::Translate('Subtotal') . '</td><td>';
        $_CONTROL->lblSubTotal->Render();
        print "</td></tr>\n";
        if( '' != $_CONTROL->lblShipping->Text )
        {
            print '<tr><td>' . Quasi::Translate('Shipping') . '</td><td>';
            $_CONTROL->lblShipping->Render();
            print "</td></tr>\n";
        }
        if( '' != $_CONTROL->lblHandling->Text )
        {
            print '<tr><td>' . Quasi::Translate('Handling') . '</td><td>';
            $_CONTROL->lblHandling->Render();
        }
            if( '' != $_CONTROL->lblTax->Text )
        {
            print '<tr><td>' . Quasi::Translate('Tax') . '</td><td>';
            $_CONTROL->lblTax->Render();
            print "</td></tr>\n";
       }
        print '<tr><td colspan="2"><hr></td></tr>';
        
        print '<tr><td><strong>' . Quasi::Translate('Grand Total') . '</strong></td><td>';
        $_CONTROL->lblGrandTotal->Render();
        print "</td></tr>\n";
    ?>
    </table>
    </div>
</div>

