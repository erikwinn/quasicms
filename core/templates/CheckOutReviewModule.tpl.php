<div id="CheckOutReviewModule">
        
        <?php
            print '<div class="heading">' . Quasi::Translate('Order Summary') .
                ': <a href="http://' . Quasi::$ServerName . __QUASI_SUBDIRECTORY__ . '/index.php/ShoppingCart"> '
                 . Quasi::Translate('Change Items') . '</a></div>';
        ?>
        <div class="CheckOutItemList">
        <table>
            <thead>
                <tr><div></div>
                    <th><?php print Quasi::Translate('Product'); ?></th>
                    <th><?php print Quasi::Translate('Item Price'); ?></th>
                    <th><?php print Quasi::Translate('Quantity'); ?></th>
                    <th><?php print Quasi::Translate('Item Total'); ?></th>
                </tr>
            </thead>
            <tbody>
        <?php
                    $blnAlternate = false;
                    foreach($_CONTROL->aryCheckOutItemViews as $objItemView)
                    {
                        if($blnAlternate)
                        {
                            print '<tr class="alternate">';
                            $blnAlternate = false;
                        }
                        else
                        {
                            print '<tr>';
                            $blnAlternate = true;
                        }
                        $objItemView->Render();
                        print "</tr> \n";
                    }           
        ?>
            </tbody>
        </table>
        </div>

        <?php $_CONTROL->objOrderTotalsView->Render(); ?>
        
        <div class="spacer"></div>
        <hr>
        <div class="spacer"></div>
        
        <?php $_CONTROL->objBillingAddressView->Render(); ?>
        
        <?php if($_CONTROL->pnlPaymentMethod)
        {
            $_CONTROL->pnlPaymentMethod->Render();
//            print '<div class="spacer"></div><hr><div class="spacer"></div>';
        } ?>
        
        <div class="spacer"></div>
        <hr>
        <div class="spacer"></div>
        
        <?php $_CONTROL->objShippingAddressView->Render(); ?>

        <?php if($_CONTROL->pnlShippingMethod)
        {
            $_CONTROL->pnlShippingMethod->Render();
        } ?>
        
        <div class="spacer"></div>
        <hr>
        <div class="spacer"></div>
        
        <div class="warning">
            <?php
                print  '<strong>' . Quasi::Translate('NOTE') . ': </strong>';
                print Quasi::Translate('Once an order is submitted we are unable to alter the contents.'
                       . ' Please verify that you have placed all the items that you wish to order in your cart.');
            ?>
        </div>
        
        <div class="spacer"></div>

</div>
