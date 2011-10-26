<?php

    /* $Id: PageView.tpl.php 197 2008-09-19 22:11:27Z erikwinn $ */

    if($_CONTROL->HasHeader)
    {
        print '<div id="PageHeader">' . "\n";    
        if($_CONTROL->HeaderContentBlocks)
        {
            foreach( $_CONTROL->aryHeaderContentBlocks as $childBlockView )
                if($childBlockView instanceof ContentBlockView)
                    $childBlockView->Render();
        }
        print '</div><!-- end PageHeader  -->' . "\n";
    }
    
    if($_CONTROL->HasLeftColumn)
    {
        print '<div id="LeftPanel">' . "\n";
        if($_CONTROL->LeftPanelContentBlocks)
        {
            foreach( $_CONTROL->aryLeftPanelContentBlocks as $childBlockView )
                if($childBlockView instanceof ContentBlockView)
                    $childBlockView->Render();
        }
        print '</div><!-- end LeftPanel  -->' . "\n";
    }
    
   // We assume at least the center panel exists ..
        print '<div id="CenterPanel">' . "\n";
        if($_CONTROL->CenterPanelContentBlocks)
        {
            foreach( $_CONTROL->aryCenterPanelContentBlocks as $childBlockView )
                if($childBlockView instanceof ContentBlockView)
                    $childBlockView->Render();
        }
        print '</div><!-- end CenterPanel  -->' . "\n";
    
    
    if($_CONTROL->HasRightColumn)
    {
        print '<div id="RightPanel">' . "\n";
        if($_CONTROL->RightPanelContentBlocks)
        {
            foreach( $_CONTROL->aryRightPanelContentBlocks as $childBlockView )
                if($childBlockView instanceof ContentBlockView)
                    $childBlockView->Render();
        }
        print '</div><!-- end RightPanel  -->' . "\n";
    }

    if($_CONTROL->HasFooter)
    {
        print '<div id="PageFooter">' . "\n";
        if($_CONTROL->aryFooterContentBlocks)
        {
            foreach( $_CONTROL->aryFooterContentBlocks as $childBlockView )
                if($childBlockView instanceof ContentBlockView )
                    $childBlockView->Render();
        }
        print '</div><!-- end PageFooter  --> ' . "\n";
    }

    // Now for all the free radicals .. these are only constrained by the index.tpl.php PageContainer div
    if($_CONTROL->ExtraContentBlocks)
    {
        foreach( $_CONTROL->aryExtraContentBlocks as $childBlockView )
            if($childBlockView instanceof ContentBlockView )
                $childBlockView->Render();
    }

?>
