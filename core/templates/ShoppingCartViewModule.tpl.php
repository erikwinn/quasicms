<div id="CartViewInner">
    <div class="CheckoutHeading">
        &nbsp;&nbsp;&nbsp; Shopping Cart
    </div>
    
<?php if( ! $_CONTROL->LoggedIn )
                print '<div class="info">' . Quasi::Translate('We are sorry, you must be logged in to view the cart.')
                            . '</div>';
            else
            {
?>

    <div class="ProductItemList">
        <table>
            <thead>
                <tr>
                    <th><?php print Quasi::Translate('Name'); ?></th>
                    <th><?php print Quasi::Translate('Image'); ?></th>
                    <th><?php print Quasi::Translate('Dimensions'); ?></th>
                    <th><?php print Quasi::Translate('Remove'); ?></th>
                    <th><?php print Quasi::Translate('Item Price'); ?></th>
                    <th><?php print Quasi::Translate('Quantity'); ?></th>
                    <th><?php print Quasi::Translate('Item Total'); ?></th>
                </tr>
            </thead>
            <tbody>
            <!--  Item rows ..    -->
            <?php
                if(null != $_CONTROL->ShoppingCart)
                {
                    $blnAlternate = false;
                    foreach($_CONTROL->aryShoppingCartItemViews as $objItemView)
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
                }
            ?>                
            </tbody>
        </table>        
    </div>
        <?php $_CONTROL->objOrderTotalsView->Render(); ?>
    <div class="spacer"></div>
        <?php $_CONTROL->lblMessage->Render(); ?>
    <hr>
    <div class="formActions">
        <div class="save"><?php $_CONTROL->btnSave->Render(); ?></div>
        <div class="checkout"><?php $_CONTROL->btnCheckOut->Render(); ?></div>
    </div>
    <div class="spacer"></div>
    <div id="ProgressBar">
        <?php  $_CONTROL->lblProgressBar->Render(); ?>
    </div>

<?php } //close else ?>
    
</div>
