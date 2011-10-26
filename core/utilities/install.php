<?php
	/**
	 * This is the installation system for Quasi CMS.
	 */

	// Include to load Quasi and Qcodo configuration ..
    require('core/Quasi.class.php');

	class InstallationForm extends QForm
    {
        protected $strQCodoConfigFile;
        protected $strQuasiConfigFile;
        protected $txtDatabaseAdapter;
        protected $txtDatabaseServer;
        protected $txtDatabaseName;
        protected $txtDatabaseUser;
        protected $txtDatabasePassword;
        protected $txtDatabaseAdminUser;
        protected $txtDatabaseAdminPassword;
        protected $chkCreateDatabase;
        protected $chkInstallExampleData;
		
        protected $blnFinished = false;
        
        protected $lblMessage;
		protected $btnSaveConfig;

        protected $strErrors;
		protected function Form_Create()
        {
            $this->strQCodoConfigFile = __QCODO_ROOT__ . '/includes/configuration.inc.php';
            $this->strQuasiConfigFile = __QUASI_CORE__ . '/quasi_config.php';
            $this->txtDatabaseAdapter = new QTextBox($this);
            $this->txtDatabaseAdapter->Text = 'MySql5';
            $this->txtDatabaseAdapter->Name = Quasi::Translate('Database Adapter (Required)') . ':';
            
            $this->txtDatabaseServer = new QTextBox($this);
            $this->txtDatabaseServer->Text = 'localhost';
            $this->txtDatabaseServer->Name = Quasi::Translate('Database Server (Required)') . ':';
            
            $this->txtDatabaseName = new QTextBox($this);
            $this->txtDatabaseName->Text = 'quasicmstest';
            $this->txtDatabaseName->Name = Quasi::Translate('Database (Required)') . ':';

            $this->txtDatabaseUser = new QTextBox($this);
            $this->txtDatabaseUser->Text = 'quasidbutest';
            $this->txtDatabaseUser->Name = Quasi::Translate('Database User Name (Required)') . ':';

            $this->txtDatabasePassword = new QTextBox($this);
            $this->txtDatabasePassword->Text = 'quasidbptest';
            $this->txtDatabasePassword->Name = Quasi::Translate('Database Password (Required)') . ':';

            $this->txtDatabaseAdminPassword = new QTextBox($this);
            $this->txtDatabaseAdminPassword->Text = '';
            $this->txtDatabaseAdminPassword->Name = Quasi::Translate('Database Admin Password') . ':';
            
            $this->txtDatabaseAdminUser = new QTextBox($this);
            $this->txtDatabaseAdminUser->Text = 'root';
            $this->txtDatabaseAdminUser->Name = Quasi::Translate('Database Admin Username') . ':';

            $this->lblMessage = new QLabel($this);
            $this->lblMessage->Text = '';
            $this->lblMessage->HtmlEntities = false;
            
            $this->chkInstallExampleData = new QCheckBox($this);
            $this->chkInstallExampleData->Name = Quasi::Translate('Install Example Data? (Recommended for new users.) ') . ':';
            $this->chkInstallExampleData->Checked = true;
            $this->chkInstallExampleData->Width = '1em';
            
            $this->chkCreateDatabase = new QCheckBox($this);
            $this->chkCreateDatabase->Name = Quasi::Translate('Create Database ? (Requires Admin username and password) ') . ':';
            $this->chkCreateDatabase->Width = '1em';
            

            $this->btnSaveConfig = new QButton($this);
            $this->btnSaveConfig->Text = 'Continue';
            $this->btnSaveConfig->AddAction(new QClickEvent(), new QServerAction('btnSaveConfig_Click'));
		}

		protected function btnSaveConfig_Click($strFormId, $strControlId, $strParameter)
        {
            if($this->blnFinished)
                Quasi::Redirect(__QUASI_SUBDIRECTORY__ . '/index.php/Home');
                
            $strOutFile = $this->strQCodoConfigFile;
            if($this->chkCreateDatabase->Checked )
                if(!$this->createDatabase())
                    die($this->strErrors);
                
            if(!$this->installDatabase())
                    die($this->strErrors);
    
            $this->writeConfigFile($strOutFile);
			$this->lblMessage->Text =
                '<h3>Configuration saved!</h3> <p style=" color:red; font-weight:bold;">'
                . '<strong>Security Warning:</strong> You must change the permissions on '
                . $strOutFile . ' <i>Immediately!!</i></p>'
                . '<p>On Linux/Unix use this command: <br /><pre><code> chmod go-w '
                . $strOutFile . '</code></pre></p><p style=" color:red; font-weight:bold;">'
                . '<strong>Security Warning:</strong> You must also remove the install.php links '
                . ' <i>Immediately!!</i></p>'
                . '<p>On Linux/Unix use this command: <br /><pre><code> rm '
                .  __QUASI_ROOT__ . '/install* </code></pre></p>';
                $this->blnFinished = true;
        }
        protected function installDatabase()
        {
            if($this->chkInstallExampleData->Checked )
                $strSql = __QUASI_CORE__ . '/quasi-with-data.sql';
            else
                $strSql = __QUASI_CORE__ . '/quasi.sql';
            $strCommand = 'mysql -u ' . $this->txtDatabaseUser->Text
                                        . ' -h ' . $this->txtDatabaseServer->Text
                                        . ' -p' . $this->txtDatabasePassword->Text
                                        . ' ' . $this->txtDatabaseName->Text . ' < ' . $strSql;
            $aryErrors = array();
            $intRetval = 0;
            $blnFork =  exec($strCommand, $aryErrors, $intRetval);
            if( false === $blnFork || 0 != $intRetval )
            {
                $this->strErrors .= 'Failed to install data - command: ' . $strCommand;
                foreach($aryErrors as $strError)
                    $this->strErrors .= $strError;
                return false;
            }
            return true;
        }
        protected function createDatabase()
        {
            $strCommand = 'mysql -u ' . $this->txtDatabaseAdminUser->Text
                                        . ' -h ' . $this->txtDatabaseServer->Text
                                        . " -p'" . $this->txtDatabaseAdminPassword->Text
                                        . "' -e ' CREATE DATABASE " . $this->txtDatabaseName->Text . ';'
                                        . ' GRANT ALL ON ' . $this->txtDatabaseName->Text . '.* TO '
                                        . $this->txtDatabaseUser->Text . ' IDENTIFIED BY "'
                                        . $this->txtDatabasePassword->Text . '" ;\' ' ;
            $aryErrors = array();
            $intRetval = 0;
            $blnFork =  exec($strCommand, $aryErrors, $intRetval);
            if( false === $blnFork || 0 != $intRetval )
            {
                $this->strErrors .= 'Failed to create database - command: ' . $strCommand;
                foreach($aryErrors as $strError)
                    $this->strErrors .= $strError;
                return false;
            }
            return true;
        }
        
        protected function writeConfigFile($strOutFile)
        {
            $strInFile = $this->strQCodoConfigFile . '.example';
            $aryValues = array(
                "/'adapter' => '.*'/" => "'adapter' => '" . $this->txtDatabaseAdapter->Text ."'",
                "/'server' => '.*'/"  => "'server' => '" . $this->txtDatabaseServer->Text ."'",
                "/'database' => '.*'/" => "'database' => '" . $this->txtDatabaseName->Text ."'",
                "/'username' => '.*'/" => "'username' => '" . $this->txtDatabaseUser->Text ."'",
                "/'password' => '.*'/" => "'password' => '" . $this->txtDatabasePassword->Text ."'",
            );
            
            $aryFile = file($strInFile);
            foreach($aryValues as $strName => $strValue)
                $aryFile = preg_replace( $strName, $strValue, $aryFile );
            $fp = fopen($strOutFile, 'w' );
            foreach($aryFile as $strLine)
                fwrite( $fp, $strLine );
            fclose($fp);
        }      
	}

	InstallationForm::Run('InstallationForm');
?>