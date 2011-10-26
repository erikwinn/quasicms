    <div id="formActions">
        <div id="save"><?php $_CONTROL->btnSave->Render(); ?></div>
        <div id="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
        <div id="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
    </div>
   <br />
   <br />
   <hr>
	<div id="formControls">
    <table width="100%">
        <tr>
            <td width="50%" valign="top">   
                <hr> <strong>     General Information: </strong> <br />
                <?php $_CONTROL->lblId->RenderWithName(); ?>

                <?php $_CONTROL->lblAccount->RenderWithName(); ?>

                <?php $_CONTROL->lstStatus->RenderWithName(); ?>
                
                <?php $_CONTROL->lblCreationDate->RenderWithName(); ?>

                <?php $_CONTROL->lblLastModificationDate->RenderWithName(); ?>

                <?php $_CONTROL->lblCompletionDate->RenderWithName(); ?>
                
                <?php $_CONTROL->lstShippingMethod->RenderWithName(); ?>

                <?php $_CONTROL->lstPaymentMethod->RenderWithName(); ?>
                
                <?php $_CONTROL->lblOrderTotal->RenderWithName(); ?>
            </td>
            <td>        
                <hr> <strong>     Notes: </strong> <br />
                <?php $_CONTROL->txtNotes->Render(); ?>
            </td>
        </tr>
        <tr>
            <td>
                <hr> <strong>     Order Items: </strong> <br />
                <?php
//                    if( $_CONTROL->dtgOrderItems->TotalItemCount )
                        $_CONTROL->dtgOrderItems->Render();
                ?>
            </td>
        </tr>
        <tr>
            <td>
               <hr> <strong>     Charges: </strong> <br />
        		<?php $_CONTROL->txtShippingCost->RenderWithName(); ?>

		        <?php $_CONTROL->txtProductTotalCost->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingCharged->RenderWithName(); ?>

                <?php $_CONTROL->txtHandlingCharged->RenderWithName(); ?>

                <?php $_CONTROL->txtTax->RenderWithName(); ?>

                <?php $_CONTROL->txtProductTotalCharged->RenderWithName(); ?>
            </td>
        </tr>
        <tr>
            <td>
              
                <hr><strong>Shipping Address:</strong> <br />

                <?php $_CONTROL->txtShippingNamePrefix->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingFirstName->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingMiddleName->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingLastName->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingNameSuffix->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingStreet1->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingStreet2->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingSuburb->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingCounty->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingCity->RenderWithName(); ?>

                <?php $_CONTROL->lstShippingZone->RenderWithName(); ?>

                <?php $_CONTROL->lstShippingCountry->RenderWithName(); ?>

                <?php $_CONTROL->txtShippingPostalCode->RenderWithName(); ?>
            </td>
            <td>
                <hr><strong> Billing Address:</strong> <br />
                <?php $_CONTROL->txtBillingNamePrefix->RenderWithName(); ?>

                <?php $_CONTROL->txtBillingFirstName->RenderWithName(); ?>

                <?php $_CONTROL->txtBillingMiddleName->RenderWithName(); ?>

                <?php $_CONTROL->txtBillingLastName->RenderWithName(); ?>

                <?php $_CONTROL->txtBillingNameSuffix->RenderWithName(); ?>

                <?php $_CONTROL->txtBillingStreet1->RenderWithName(); ?>

                <?php $_CONTROL->txtBillingStreet2->RenderWithName(); ?>

                <?php $_CONTROL->txtBillingSuburb->RenderWithName(); ?>

                <?php $_CONTROL->txtBillingCounty->RenderWithName(); ?>

                <?php $_CONTROL->txtBillingCity->RenderWithName(); ?>

                <?php $_CONTROL->lstBillingZone->RenderWithName(); ?>

                <?php $_CONTROL->lstBillingCountry->RenderWithName(); ?>

                <?php $_CONTROL->txtBillingPostalCode->RenderWithName(); ?>
            </td>
        </tr>
    </table>    
</div>
