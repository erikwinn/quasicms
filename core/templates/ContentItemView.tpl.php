	<div class="ContentItemInner">

<?php
        if($_CONTROL->ShowTitle)
            $_CONTROL->Title->Render();
        if($_CONTROL->ShowDescription)
            $_CONTROL->Description->Render();
        if($_CONTROL->ShowCreator)
            $_CONTROL->Creator->RenderWithName();
        if($_CONTROL->ShowCreationDate)
            $_CONTROL->CreationDate->RenderWithName();
        if($_CONTROL->ShowLastModification)
            $_CONTROL->LastModification->RenderWithName();
        print '<div class="spacer"></div>';
        $_CONTROL->Text->Render();

?>

	</div>

