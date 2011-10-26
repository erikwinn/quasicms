    <div class="PrintAction button">
        <a href="javascript:window.print()">
            <?php print Quasi::Translate('Print'); ?>
        </a>
    </div>

    <div id="ConfirmationMessage">
        <?php  $_CONTROL->lblMessage->Render(); ?>
    </div>

    <div class="spacer"></div>
    <hr>
    <div class="spacer"></div>

    <?php  print '<div class="heading">' . Quasi::Translate('Order Summary') . ':</div>'; ?>

    <div class="spacer"></div>
        <div class="CheckOutItemList">
        <table>
            <thead>
                <tr>
<!--    the extra div is to fix a QCodo quirk ..              -->
                <div></div>
                    <th><?php print Quasi::Translate('Product'); ?></th>
                    <th><?php print Quasi::Translate('Item Price'); ?></th>
                    <th><?php print Quasi::Translate('Quantity'); ?></th>
                    <th><?php print Quasi::Translate('Item Total'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    //a quickie fake datagrid list .. Note: the CheckOutItemViews are in <TD>s
                    ///@todo - change this to a smart datagrid, configurable  ..
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


