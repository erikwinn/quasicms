<?php

require(__INCLUDES__ . '/header.inc.php');

$gotoforms_link ='<a href="' . __VIRTUAL_DIRECTORY__  . __FORM_DRAFTS__ . '/index.php">';
                               
$this->RenderBegin();
?>
<div id="dashboardTitleBar">
     <div id="titleBar">
        <h2 id="right"><?php echo $gotoforms_link; _t('Go to Forms List'); ?> </a></h2>
        <br />      
        <h1>Quasi CMS Administration </h1>
        <h2><?php $this->pnlTitle->Render(); ?></h2>
    </div>

    <div id="dashboard">
        <div id="left">
            <p><strong>Select an Item</strong></p>
            
            <p><?php $this->pnlClassNames->Render(); ?></p>
            <p><?php $this->objDefaultWaitIcon->Render(); ?></p>
        </div>
        <div id="right">
            <?php $this->pnlList->Render(); ?>
            <?php $this->pnlEdit->Render(); ?>
        </div>
    </div>
 </div>   
<?php $this->RenderEnd(); ?>
<?php require(__INCLUDES__ . '/footer.inc.php'); ?>
