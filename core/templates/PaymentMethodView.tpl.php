<div class="PaymentMethodView">
    <?php
            $_CONTROL->ctlRadioButton->Render();
            $_CONTROL->lblTitle->Render();
            if($_CONTROL->ShowImage)
                $_CONTROL->pnlImage->Render();
            print '<div class="spacer"></div>';
            if($_CONTROL->ShowDescription)
                $_CONTROL->lblDescription->Render();
    ?>
</div>