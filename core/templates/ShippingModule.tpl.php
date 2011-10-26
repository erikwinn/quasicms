<div id="ShippingModule">

<?php if($_CONTROL->HasActiveMethods)
            { 
                print '<div class="heading">' . Quasi::Translate('Shipping Address') . ': </div>';
    
                $_CONTROL->objAddressSelectionModule->Render();
                        
                print '<div class="heading">' . Quasi::Translate('Shipping Options') . ': </div>';
                
                if( is_array($_CONTROL->aryShippingProviders ) )
                    foreach($_CONTROL->aryShippingProviders as $strTitle => $aryShippingMethodViews)
                    {
                        print '<div class="ShippingProviderTitle">' . $strTitle . "</div>\n";
                        foreach($aryShippingMethodViews as $objShippingMethodView)
                            $objShippingMethodView->Render();
                        print '<div class="spacer"></div>';
                    }

            }
            print '<hr>';
            print '<div class="heading">' . Quasi::Translate('Add comments about your order') . ': </div>';
            $_CONTROL->txtNotes->Render();
                        
?>

</div>