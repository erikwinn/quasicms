<div class="AddressListPanel">    

    <p>Below is a list of your addresses. Click the address name link to edit the address.</p>

    <?php
    if(IndexPage::$objAccount)
    {
        $_CONTROL->dtgAddresses->Render();
        $_CONTROL->btnCreateNew->Render();
        print '<div class="spacer"></div>' . "\n";
    }
    ?>
</div>
