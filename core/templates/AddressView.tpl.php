<div class="AddressView">	

    <?php
        print '<div class"heading">';
        $_CONTROL->lblAddressName->Render();
        print '</div>';
        
        print '<div class="spacer"></div>';
        
        if( '' != $_CONTROL->lblTitle->Text )
            $_CONTROL->lblTitle->Render();
        if( '' != $_CONTROL->lblPersonId->Text )
            $_CONTROL->lblPersonId->Render();
        if( '' != $_CONTROL->lblStreet1->Text )
            $_CONTROL->lblStreet1->Render();
        if( '' != $_CONTROL->lblStreet2->Text )
            $_CONTROL->lblStreet2->Render();
        if( '' != $_CONTROL->lblSuburb->Text )
            $_CONTROL->lblSuburb->Render();
        if( '' != $_CONTROL->lblCity->Text )
            $_CONTROL->lblCity->Render();
        if( '' != $_CONTROL->lblCounty->Text )
            $_CONTROL->lblCounty->Render();
        if( '' != $_CONTROL->lblZoneId->Text )
            $_CONTROL->lblZoneId->Render();
        if( '' != $_CONTROL->lblCountryId->Text )
            $_CONTROL->lblCountryId->Render();
        if( '' != $_CONTROL->lblPostalCode->Text )
            $_CONTROL->lblPostalCode->Render();
        if( '' != $_CONTROL->lblTypeId->Text )
            $_CONTROL->lblTypeId->Render();
        
        print '<div class="spacer"></div>';

    ?>
        
</div>