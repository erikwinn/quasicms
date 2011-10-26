	<div class="BlogModuleInner">

<?php
        if(is_array($_CONTROL->aryContentItemViews))
            foreach( $_CONTROL->aryContentItemViews as $contentItemView )
                $contentItemView->Render();
?>

        <div class="spacer"></div>

	</div>

