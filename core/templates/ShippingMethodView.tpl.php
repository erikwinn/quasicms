<div class="ShippingMethodView">
    <?php
            $_CONTROL->ctlRadioButton->Render();
            if($_CONTROL->ShowDescription)
                $_CONTROL->lblDescription->Render();
            if($_CONTROL->ShowTransitTime)
                $_CONTROL->lblTransitTime->Render();
            if($_CONTROL->ShowRate)
                $_CONTROL->lblRate->Render();
    ?>
    <div class="spacer"></div>
</div>