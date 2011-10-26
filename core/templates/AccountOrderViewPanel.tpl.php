<div class="AccountOrderView">
    <table>
        <tr>
            <td>
                <?php print Quasi::Translate('Order Number') . ': '; ?>
            </td>
            <td>
                <?php print  $_CONTROL->objOrder->Id; ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php print Quasi::Translate('Order Status') . ': '; ?>
            </td>
            <td>
                <?php print OrderStatusType::ToString($_CONTROL->objOrder->StatusId); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php print Quasi::Translate('Created On') . ': '; ?>
            </td>
            <td>
                <?php print $_CONTROL->objOrder->CreationDate; ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php print Quasi::Translate('Last Changed') . ': '; ?>
            </td>
            <td>
                <?php print $_CONTROL->objOrder->LastModificationDate; ?>
            </td>
        </tr>
        
        <?php if('0000-00-00 00:00:00' != $_CONTROL->objOrder->CompletionDate)  { ?>
        <tr>
            <td>
                <?php print Quasi::Translate('Completed On') . ': '; ?>
            </td>
            <td>
                <?php print $_CONTROL->objOrder->CompletionDate; ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php print Quasi::Translate('Tracking Numbers') . ': '; ?>
            </td>
            <td>
                <?php
                    if('' != $_CONTROL->strTrackingNumbers)
                        print $_CONTROL->strTrackingNumbers;
                    else
                        print Quasi::Translate('No Tracking Number available');
                ?>
            </td>
        </tr>
        <?php } ?>


    </table>
        
    <div class="spacer"></div>
    <hr>
    <div class="spacer"></div>
    <div class="OrderHistory">
        <div class="heading">
            <?php print Quasi::Translate('Order History') . ': ';?>
        </div>
        <div class="spacer"></div> 
            <?php $_CONTROL->dtgOrderStatusHistory->Render(); ?>
    </div>
    <div class="spacer"></div>
    <hr>
    <div class="spacer"></div>
    
    <div class="ProductItemList">
        <div class="heading">
            <?php print Quasi::Translate('Order Items') . ': ';?>
        </div>
            <?php $_CONTROL->dtgOrderItems->Render();?>
    </div>

    <div class="spacer"></div>
    <?php $_CONTROL->objOrderTotalsView->Render(); ?>
    
    <div class="spacer"></div>
    <hr>
    <div class="spacer"></div>

    <?php $_CONTROL->objBillingAddressView->Render(); ?>

    <?php if($_CONTROL->pnlPaymentMethod)
    {
        $_CONTROL->pnlPaymentMethod->Render();
    }
    ?>

    <div class="spacer"></div>
    <hr>
    <div class="spacer"></div>

    <?php $_CONTROL->objShippingAddressView->Render(); ?>

    <?php if($_CONTROL->pnlShippingMethod)
    {
        $_CONTROL->pnlShippingMethod->Render();
    }
    ?>

    <div class="spacer"></div>
    <hr>
    <div class="spacer"></div>
        

	<div class="formActions">
		<div class="cancel"><?php $_CONTROL->btnBack->Render(); ?></div>
	</div>
</div>
 