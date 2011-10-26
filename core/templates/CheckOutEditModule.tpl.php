<div id="CheckOutEditModule">
        <?php
            $_CONTROL->objCheckOutItemListModule->Render();
            $_CONTROL->objCheckOutTotalsView->Render();
        ?>
        <div class="spacer"></div>
        <?php $_CONTROL->objShippingAddressView->Render(); ?>
        <?php $_CONTROL->objBillingAddressView->Render(); ?>
        <div class="spacer"></div>
</div>
