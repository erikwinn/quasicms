<div class="QFileInputDlg">
<h1>Upload a File</h1>
<p>Please select a File to upload.
</p>
    <?php $_CONTROL->ctlFileInput->RenderWithError(); ?>
    <br />
    <?php $_CONTROL->lblErrorMessage->Render(); ?>
<p>
	<?php $_CONTROL->btnUpload->Render(); ?>
	<?php $_CONTROL->btnCancel->Render(); ?>
    <?php $_CONTROL->objSpinner->Render(); ?>
</p>
</div>