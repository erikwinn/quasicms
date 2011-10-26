<div id="PaymentModule">
<?php
if($_CONTROL->HasActiveMethods)
{
    print '<div class="heading">' . Quasi::Translate('Billing Address') . ': </div>';
    
    $_CONTROL->objAddressSelectionModule->Render();
    
    print '<div class="spacer"></div><hr> <div class="spacer"></div>';
    
    print '<div class="heading">' . Quasi::Translate('Payment Method') . ': </div>';
    print '<div class="PaymentRadioButtons"> ';

    if( $_CONTROL->ShowCCInput )
    {
        print '<div class="spacer"></div>';
        
        foreach($_CONTROL->aryPaymentMethodViews as $objMethodView)
        {
            if($objMethodView->PaymentMethod->RequiresCcNumber)
            {
                print '<div class="spacer"></div>';
                $objMethodView->Render();
                print '<div class="spacer"></div>';
            }
        }
        ?>
        
        <table class="CCInput">
            <tr>
                <td colspan="2"><?php $_CONTROL->txtCCNumber->RenderWithName(); ?></td>
            </tr>
            <tr>
                <td><div class="renderWithName"><?php print Quasi::Translate('Expiration Date') . ': '; ?></div></td>
                <td>
                    <?php
                        $_CONTROL->lstCCExpirationMonth->RenderWithError();
                        $_CONTROL->lstCCExpirationYear->RenderWithError();
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="2"><?php $_CONTROL->txtCCVNumber->RenderWithName(); ?></td>
            </tr>
        </table>

        <?php        
    }
    
    foreach($_CONTROL->aryPaymentMethodViews as $objMethodView)
    {
        if( ! $objMethodView->PaymentMethod->RequiresCcNumber)
        {     
                print '<div class="spacer"></div>';
                print '<div class="spacer"></div>';
                $objMethodView->Render();
                print '<div class="spacer"></div>';
        }     
    }
    print '</div>';
    
}
?>
</div>    
