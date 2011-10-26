<div id="CheckOutModule">
    
    <?php $_CONTROL->pnlHeading->Render();?>

    <div class="spacer"></div>    

    <?php        
        if($_CONTROL->pnlCurrentPanel)
        {            
            if($_CONTROL->objOrderTotalsView)
            {
                $_CONTROL->objOrderTotalsView->Render();
                print '<div class="spacer"></div>';
                print '<hr>';
            }                            
            $_CONTROL->pnlCurrentPanel->Render();
            if ( ! $_CONTROL->CartEmpty )
            {
                ?>
                
                    <hr>
                    <div class="formActions">
                        <div class="button"><?php $_CONTROL->btnBack->Render(); ?></div>
                        <div class="button"><?php $_CONTROL->btnCancel->Render(); ?></div>
                        <div class="button"><?php $_CONTROL->btnContinue->Render(); ?></div>
                    </div>
                
                <?php
            }
        }
    ?>
    
    <div class="spacer"></div>

    <div id="ProgressBar">
        <?php  $_CONTROL->lblProgressBar->Render(); ?>
    </div>
  
</div>
