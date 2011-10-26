
<div class="MenuBlockInner">

    <ul>

<?php
        if($_CONTROL->ShowTitle)
           print '<div class="MenuTitle">' . $_CONTROL->Title . '</div>';

        if($_CONTROL->MenuItemViews)
            foreach( $_CONTROL->aryMenuItemViews as $MenuItemView )
            {
                if($MenuItemView->UseDivs)
                    $strTag = 'div';
                else
                    $strTag = 'li';
                
                print '<' . $strTag . ' ' . $MenuItemView->GetAttributes() . '>';
                print $MenuItemView->GetControlHtml();
                print '</' . $strTag . '>';
                
            }
?>

	</ul>
<!--  this pulls the div down below the list to make styling possible .. -->
<!--  <div class="spacer"></div>    -->
</div>
