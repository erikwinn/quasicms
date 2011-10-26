<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>Qcodo Development Framework - Code Generator</title>
        <style type="text/css">@import url("<?php _p(__VIRTUAL_DIRECTORY__ . __CSS_ASSETS__); ?>/styles.css");</style>
        <style>
            body { font-family: 'Arial' 'Helvetica' 'sans-serif'; font-size: 14px; margin: 0px; background-color: white;}
            a:link, a:visited { text-decoration: none; }
            a:hover { text-decoration: underline; }

            .page { padding: 10px; }

            .headingLeft {
                background-color: #446644;
                color: #ffffff;
                padding: 10px 0px 10px 10px;
                font-family: 'Verdana' 'Arial' 'Helvetica' 'sans-serif';
                font-size: 18px;
                font-weight: bold;
                width: 70%;
                vertical-align: bottom;
            }
            .headingLeftSmall { font-size: 10px; }
            .headingRight {
                background-color: #446644;
                color: #ffffff;
                padding: 0px 10px 10px 10px;
                font-family: 'Verdana' 'Arial' 'Helvetica' 'sans-serif';
                font-size: 10px;
                width: 30%;
                vertical-align: bottom;
                text-align: right;
            }
            .title { font-family: 'Verdana' 'Arial' 'Helvetica' 'sans-serif'; font-size: 19px; font-style: italic; color: #330055; }

            .code { background-color: #f4eeff; padding: 10px 10px 10px 10px; margin-left: 50px; font-size: 11px; font-family: 'Lucida Console' 'Courier New' 'Courier' 'monospaced';}
            .code_title { font-family: 'Verdana' 'Arial' 'Helvetica' 'sans-serif'; font-size: 12px; font-weight: bold; }
        </style>
    </head>
    <body>

        <table border="0" cellspacing="0" width="100%">
            <tr>
                <td nowrap="nowrap" class="headingLeft"><span class="headingLeftSmall">Qcodo Development Framework <?php _p(QCODO_VERSION); ?><br /></span>Code Generator</div></td>
                <td nowrap="nowrap" class="headingRight">
                    <b>PHP Version:</b> <?php _p(PHP_VERSION); ?>;&nbsp;&nbsp;<b>Zend Engine Version:</b> <?php _p(zend_version()); ?>;&nbsp;&nbsp;<b>Qcodo Version:</b> <?php _p(QCODO_VERSION); ?><br />
                    <?php if (array_key_exists('OS', $_SERVER)) printf('<b>Operating System:</b> %s;&nbsp;&nbsp;', $_SERVER['OS']); ?><b>Application:</b> <?php _p($_SERVER['SERVER_SOFTWARE']); ?>;&nbsp;&nbsp;<b>Server Name:</b> <?php _p($_SERVER['SERVER_NAME']); ?><br />
                    <b>Code Generated:</b> <?php _p(date('l, F j Y, g:i:s A')); ?>
                </td>
            </tr>
        </table>

<p><?php $this->RenderBegin();?></p>
<p><?php $this->pnlSettings->Render(); ?></p>
<p><?php $this->btnRun->Render(); ?></p>
 <?php if(is_array(QCodeGen::$CodeGenArray)) { ?>
        <div class="page">
            <?php if ($strErrors = QCodeGen::$RootErrors) { ?>
                <p><b>The following root errors were reported:</b></p>
                <div class="code"><?php $this->DisplayMonospacedText($strErrors); ?></div>
                <p></p>
            <?php } else { ?>
                <p><b>CodeGen Settings (as evaluated from <?php _p(QCodeGen::$SettingsFilePath); ?>):</b></p>
                <div class="code"><?php $this->DisplayMonospacedText(QCodeGen::GetSettingsXml()); ?></div>
                <p></p>
            <?php } ?>

            <?php if(is_array(QCodeGen::$CodeGenArray)) foreach (QCodeGen::$CodeGenArray as $objCodeGen) { ?>
                <p><b><?php _p($objCodeGen->GetTitle()); ?></b></p>
                <div class="code"><span class="code_title"><?php _p($objCodeGen->GetReportLabel()); ?></span><br/><br/>
                    <?php $this->DisplayMonospacedText($objCodeGen->GenerateAll()); ?>
                    <?php if ($strErrors = $objCodeGen->Errors) { ?>
                        <p class="code_title">The following errors were reported:</p>
                        <?php $this->DisplayMonospacedText($objCodeGen->Errors); ?>
                    <?php } ?>
                </div><p></p>
            <?php } ?>
            
            <?php foreach (QCodeGen::GenerateAggregate() as $strMessage) { ?>
                <p><b><?php _p($strMessage); ?></b></p>
            <?php } ?>

        </div>              
<?php } ?>    
<?php $this->RenderEnd(); ?>

</body></html>