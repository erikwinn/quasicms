	<div class="ContentBlockInner">

<?php
        
        if($_CONTROL->ShowTitle)
            $_CONTROL->TitlePanel->Render();
            
        if($_CONTROL->ShowDescription)
            $_CONTROL->DescriptionPanel->Render();

        // Modules are rendered first, and one module alone without content is recommended.
        if( $_CONTROL->HasModules )
            foreach( $_CONTROL->aryModuleViews as $moduleView)
                $moduleView->Render();
                
        // Then menus - recommendation as above ..
        if($_CONTROL->HasMenus)
            foreach( $_CONTROL->aryMenuViews as $menuView )
                $menuView->Render();

        // Note that child blocks are rendered after top level content for the block
        if($_CONTROL->HasContentItems)
            foreach( $_CONTROL->aryContentItemViews as $contentItemView )
                $contentItemView->Render();


        if($_CONTROL->HasContentBlocks)
            foreach( $_CONTROL->aryChildContentBlockViews as $childBlockView )
                $childBlockView->Render();
        
?>

        <div class="spacer"></div>

	</div>

