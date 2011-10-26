<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php _p(QApplication::$EncodingType); ?>" />
<title>Quasi CMS Installation</title>
<link rel="stylesheet" type="text/css" href="core/assets/css/quasi.css">
</head><body>
<center>
<div style="width:600px; color:white; font-weight:bold">
<h1>Quasi CMS Installation</h1>

<?php $this->RenderBegin(); ?>
    <?php if (! $this->blnFinished) { ?>
<p style="text-align: left; font-weight:normal">
<strong>Note: </strong>If you have not created a database for Quasi yet and you have administrative access
 (meaning you have permission to create databases) you can use the "Create Database" section
 below. Otherwise, you must create a database first and enter the correct information here. The
 database must have read/write permissions for the Quasi user that you enter.</p>
    <p><?php $this->txtDatabaseAdapter->RenderWithName(); ?></p>
    <p><?php $this->txtDatabaseServer->RenderWithName(); ?></p>
    <p><?php $this->txtDatabaseName->RenderWithName(); ?></p>
    <p><?php $this->txtDatabaseUser->RenderWithName(); ?></p>
    <p><?php $this->txtDatabasePassword->RenderWithName(); ?></p>
    <p><?php $this->chkInstallExampleData->RenderWithName(); ?></p>
    <p><?php $this->chkCreateDatabase->RenderWithName(); ?></p>
    <p><?php $this->txtDatabaseAdminUser->RenderWithName(); ?></p>
    <p><?php $this->txtDatabaseAdminPassword->RenderWithName(); ?></p>
    <?php } ?>
    <p><?php $this->lblMessage->RenderWithName(); ?></p>
	<p><?php $this->btnSaveConfig->Render(); ?></p>
<?php $this->RenderEnd(); ?>
</div>
</center>

</body></html>