<?php
    
    /** Load in the QCodo framework
    * @package QCodo
    */
    require('../prepend.inc.php');
    /** Load in the QCodeGen Class
    * @package QCodo
    */
    require(__QCODO__ . '/codegen/QCodeGen.class.php');

    // Security check for ALLOW_REMOTE_ADMIN
    // To allow access REGARDLESS of ALLOW_REMOTE_ADMIN, simply remove the line below
//    QApplication::CheckRemoteAdmin();

    /**
    * Class CodeGenUI - this class provides a more flexible way to run the code generator. It is like codegen.php
    * but allows you to select settings and the list of tables for which to generate ORM classes.
    * Basically, this class loads the standard codegen_settings.xml, saves a temporary copy and gives
    * the filename to QCodeGen::Run.
    *
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    * $Id: codegen_ui.php 522 2009-04-02 18:14:53Z erikwinn $
    *@version 0.1
    * @package QCodo
    */
    class CodeGenUI extends QForm
    {
        ///@var QPanel - the panel containing controls for selecting settings
		public $pnlSettings;
        ///@var QButton - triggers code generation
        public $btnRun;
        ///@var QListBox - a list of tables for which to generate code
        public $lstTables;
        ///@var QCheckBox - check to support old style manual queries
        public $chkManualQuerySupport;
        ///@var QTextBox - type table suffix
        public $txtTypeTableIdentifier;
        ///@var QTextBox - association table suffix
        public $txtAssociationTableIdentifier;
        ///@var QTextBox - optional table prefix to remove 
        public $txtStripFromTableName;
        ///@var QCheckBox - check to preserve previously generated QQN file
        public $chkSaveORM;
        
        ///@var string - comma delimited list of tables to generate
        private $strIncludeList;
        ///@var DOMDocument - contains the codegen_settings.xml XML
		private $objSettingsDom;
        ///@var string - file name for the QCodo standard codegen_settings.xml; adjust to fit your set up.
        private $strSettingsTpl;
        ///@var string - file name for the temporary settings XML; adjust to fit your set up.
        private $strMySettingsFile = '/tmp/code_generator_settings.xml';
        ///@var boolean - whether to preserve the old QQN.class.php
        private $blnSaveORM;
        ///@var array - contains names of ORM files to save and replace
        private $aryORMFiles;

        ///Runs last .. replace old ORM if desired
        protected function Form_Exit()
        {
            if($this->blnSaveORM)
            {
                foreach($this->aryORMFiles as $strFileName => $strTempFileName)
                    if(file_exists($strTempFileName) )
                        copy($strTempFileName, $strFileName);
            }
        }
        ///Runs once, first time the page is accessed (in a session)
        protected function Form_Create()
        {
            $this->strSettingsTpl = __DOCROOT__ . __DEVTOOLS__ . '/codegen_settings.xml';
//            $this->strTemplate = 'codegen_ui.tpl.php';            
            if( !is_file($this->strSettingsTpl) )
                throw new QCallerException('Settings template missing: ' . $this->strSettingsTpl);

            //Set up array of ORM classes to preserve ..
            $strBaseDir = __DATAGEN_CLASSES__;
            $this->aryORMFiles = array(
                        $strBaseDir . '/_type_class_paths.inc.php' => $strBaseDir . '/_type_class_paths.inc.php-gentmp',
                        $strBaseDir . '/_class_paths.inc.php' => $strBaseDir . '/_class_paths.inc.php-gentmp',
                        $strBaseDir . '/QQN.class.php' => $strBaseDir . '/QQN.class.php-gentmp',
                        $strBaseDir . '/QMetaDataBase.class.php' => $strBaseDir . '/QMetaDataBase.class.php-gentmp',
                                                            );
            $this->objSettingsDom = new DOMDocument();
            $this->objSettingsDom->load( $this->strSettingsTpl );
                        
            $this->pnlSettings = new QPanel($this);
            //Note that we are setting a template for the child panel here:
            $this->pnlSettings->Template = 'codegen_ui_settings.tpl.php';

            $this->initTableList();

            //get the other settings in the standard file ..
            $objTypeTableIdentifierNode = $this->objSettingsDom->getElementsByTagName('typeTableIdentifier')->item(0);
            $objAssociationTableIdentifierNode = $this->objSettingsDom->getElementsByTagName('associationTableIdentifier')->item(0);
            $objManualQueryNode = $this->objSettingsDom->getElementsByTagName('manualQuery')->item(0);
            $objStripFromTableName= $this->objSettingsDom->getElementsByTagName('stripFromTableName')->item(0);
            
            $this->chkManualQuerySupport = new QCheckBox($this->pnlSettings);
            $this->chkManualQuerySupport->Name = 'Support (old) Manual Queries';
            $this->chkManualQuerySupport->Checked =  ( 'true' == $objManualQueryNode->getAttribute('support') );

            $this->txtAssociationTableIdentifier = new QTextBox($this->pnlSettings);
            $this->txtAssociationTableIdentifier->Name = 'Association table suffix';
            $this->txtAssociationTableIdentifier->Text = $objAssociationTableIdentifierNode->getAttribute('suffix');
            
            $this->txtTypeTableIdentifier = new QTextBox($this->pnlSettings);
            $this->txtTypeTableIdentifier->Name = 'Type table suffix';
            $this->txtTypeTableIdentifier->Text = $objTypeTableIdentifierNode->getAttribute('suffix');
                       
            $this->txtStripFromTableName = new QTextBox($this->pnlSettings);
            $this->txtStripFromTableName->Name = 'Remove table prefix';
            $this->txtStripFromTableName->Text = $objStripFromTableName->getAttribute('prefix');
            
            $this->chkSaveORM = new QCheckBox($this->pnlSettings);
            $this->chkSaveORM->Name = 'Preserve ORM (Recommended)';
            $this->chkSaveORM->Checked =  true;
            
            $this->btnRun = new QButton($this);
            $this->btnRun->Text = 'Run Generator';
            $this->btnRun->AddAction(new QClickEvent(), new QServerAction('btnRun_Click'));
		}
        /**
        * Creates a list box with a list of the possible tables for which to generate ORM code
        *
        * @todo - suppport multiple databases ..
        */
        private function initTableList()
        {
            $this->lstTables = new QListBox($this->pnlSettings);
            $this->lstTables->Name = 'Select tables';
            $this->lstTables->SelectionMode = QSelectionMode::Multiple;
            
            $objDbi = QApplication::$Database[1];
            $aryTables = $objDbi->GetTables();
            $intSize = 0;
            foreach($aryTables as $strTableName)
            {
                $intSize += 1;
                $this->lstTables->AddItem(new QListItem($strTableName, $strTableName));
            }
            $this->lstTables->Rows = $intSize > 10 ? 10: $intSize;
        }
        
        /**
        * This is called when the user clicks "Run Generator". It collects the values set in the upper
        * half of the page, writes a temporary XML doc for CodeGen settings and runs QCodeGen::Run
        * using the temporary settings.
        * Parameters are ignored.
        */
		protected function btnRun_Click($strFormId, $strControlId, $strParameter)
        {
            $this->objSettingsDom->load( $this->strSettingsTpl );
            $this->strIncludeList = '';
            $arySelectedItems = $this->lstTables->SelectedItems;
            if(!$arySelectedItems)
                return;
            foreach( $arySelectedItems as $objListItem)
            {
                if('' != $this->strIncludeList)
                    $this->strIncludeList .= ',';
                $this->strIncludeList .= $objListItem->Value;
            }
            if('' == $this->strIncludeList)
                return;
            
            //first, set up the table selection ..
            $objExcludesNode = $this->objSettingsDom->getElementsByTagName('excludeTables')->item(0);
            $objIncludesNode = $this->objSettingsDom->getElementsByTagName('includeTables')->item(0);
            $objExcludesNode->setAttribute('pattern','[0-9a-zA-Z_]*');
            $objIncludesNode->setAttribute('list', $this->strIncludeList);

            //extra settings ..
            $objManualQueryNode = $this->objSettingsDom->getElementsByTagName('manualQuery')->item(0);
            $objTypeTableIdentifierNode = $this->objSettingsDom->getElementsByTagName('typeTableIdentifier')->item(0);
            $objAssociationTableIdentifierNode = $this->objSettingsDom->getElementsByTagName('associationTableIdentifier')->item(0);
            $objStripFromTableName= $this->objSettingsDom->getElementsByTagName('stripFromTableName')->item(0);
            
            $objManualQueryNode->setAttribute('support', $this->chkManualQuerySupport->Checked ? 'true' : 'false');
            $objAssociationTableIdentifierNode->setAttribute('suffix', trim($this->txtTypeTableIdentifier->Text));
            $objTypeTableIdentifierNode->setAttribute('suffix', trim($this->txtTypeTableIdentifier->Text));
            $objStripFromTableName->setAttribute('suffix', trim($this->txtStripFromTableName->Text));
            
            $this->objSettingsDom->save( $this->strMySettingsFile );

            //By default QCodeGen creates new ORM files - save these and replace if desired ..
            $this->blnSaveORM = $this->chkSaveORM->Checked;
            if($this->blnSaveORM)
            {
                foreach($this->aryORMFiles as $strFileName => $strTempFileName)
                    if(file_exists($strFileName) )
                        copy($strFileName, $strTempFileName);
            }
            
            QCodeGen::Run($this->strMySettingsFile);		
        }
        
        /**
        * Convenience function for formatting output - pretty prints the given text
        *@param string strText - text to format and print to screen
        */
        protected function DisplayMonospacedText($strText)
        {
            $strText = QApplication::HtmlEntities($strText);
            $strText = str_replace('    ', '    ', $strText);
            $strText = str_replace(' ', '&nbsp;', $strText);
            $strText = str_replace("\r", '', $strText);
            $strText = str_replace("\n", '<br/>', $strText);

            _p($strText, false);
        }
	}

	CodeGenUI::Run('CodeGenUI');
?>