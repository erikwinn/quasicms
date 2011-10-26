<div id="AddressSelectionModuleInner">

        <table>
		
        <tr><td>
                <?php $_CONTROL->objWaitIcon->Render(); ?>
        </td></tr>
<!-- Editable display -->
<?php if( $_CONTROL->Modifiable ) { ?>

        <tr><td>
            <?php
                    $_CONTROL->lstMyPeople->Render();
  //              $_CONTROL->btnAddPerson->Render();
            ?>
        </td></tr>

        <tr><td><?php $_CONTROL->txtStreet1->RenderWithName(); ?></td></tr>

        <tr><td><?php $_CONTROL->txtStreet2->RenderWithName(); ?></td></tr>

        <tr><td><?php $_CONTROL->txtSuburb->RenderWithName(); ?></td></tr>

        <tr><td><?php $_CONTROL->txtCity->RenderWithName(); ?></td></tr>

        <tr><td><?php $_CONTROL->txtCounty->RenderWithName(); ?></td></tr>

        <tr><td><?php $_CONTROL->lstZone->RenderWithName(); ?></td></tr>

        <tr><td><?php $_CONTROL->lstCountry->RenderWithName(); ?></td></tr>

        <tr><td><?php $_CONTROL->txtPostalCode->RenderWithName(); ?></td></tr>

        <tr><td><?php $_CONTROL->lstType->RenderWithName(); ?></td></tr>

 </table>

    <div class="formActions">
        <table width="100%"><tr><td>
        <div class="save"><?php $_CONTROL->btnSave->Render(); ?></div>
        <div class="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
        </td></tr></table>
    </div>

 <!-- Passive Display ..  -->
<?php } else { ?>
        
        <tr><td>  <?php $_CONTROL->lstMyAddresses->Render(); ?> </td></tr>
        
        <tr><td><?php $_CONTROL->lblPersonId->Render(); ?></td></tr>

        <tr><td><?php $_CONTROL->lblStreet1->Render(); ?></td></tr>

        <?php if( '' != $_CONTROL->lblStreet2->Text ) { ?>
            <tr><td><?php $_CONTROL->lblStreet2->Render(); ?></td></tr>
        <?php } ?>
        
        <?php if( '' != $_CONTROL->lblSuburb->Text ) { ?>
            <tr><td><?php  $_CONTROL->lblSuburb->Render(); ?></td></tr>
        <?php } ?>

        <tr><td><?php $_CONTROL->lblCity->Render(); ?></td></tr>

        <?php if( '' != $_CONTROL->lblCounty->Text )  { ?>
        <tr><td><?php $_CONTROL->lblCounty->Render(); ?></td></tr>
        <?php } ?>

        <tr><td><?php $_CONTROL->lblZoneId->Render(); ?></td></tr>

        <tr><td><?php $_CONTROL->lblCountryId->Render(); ?></td></tr>

        <tr><td><?php $_CONTROL->lblPostalCode->Render(); ?></td></tr>

 </table>
    
<?php } ?>
    
</div>