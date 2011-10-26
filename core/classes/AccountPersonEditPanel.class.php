<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("ACCOUNTPERSONEDITPANEL.CLASS.PHP")){
define("ACCOUNTPERSONEDITPANEL.CLASS.PHP",1);
    
    /**
    * AccountPersonEditPanel - provides a panel for user viewing and modification of a Person
    *    
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: AccountPersonEditPanel.class.php 286 2008-10-10 23:33:36Z erikwinn $
    *@version 0.1
    *
    *@copyright (C) 2008 by Erik Winn
    *@license GPL v.2

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111 USA

    *
    * @package Quasi
    * @subpackage Classes
    */
	class AccountPersonEditPanel extends QPanel
    {
		// Local instance of the PersonMetaControl
		protected $mctPerson;
        protected $objPerson;
        
        protected $objControlBlock;
		
        // Controls for Person's Data Fields
		public $txtNamePrefix;
		public $txtFirstName;
		public $txtMiddleName;
		public $txtLastName;
		public $txtNameSuffix;
		public $txtNickName;
		public $txtEmailAddress;
		public $txtPhoneNumber;
		public $txtAvatarUri;
		public $txtCompanyName;

		public $lstUsergroups;

		// Other Controls
		public $btnSave;
		public $btnDelete;
		public $btnCancel;

		// Callback
		protected $strClosePanelMethod;

		public function __construct($objParentObject,
                                                     $objControlBlock,
                                                     $strClosePanelMethod,
                                                     $intId = null,
                                                     $strControlId = null)
        {
			
			try {
				parent::__construct($objParentObject, $strControlId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
            
            $this->objControlBlock =& $objControlBlock;

			$this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/AccountPersonEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			$this->mctPerson = PersonMetaControl::Create($this, $intId);
            $this->objPerson = $this->mctPerson->Person;
            
			$this->txtNamePrefix = $this->mctPerson->txtNamePrefix_Create();
			$this->txtFirstName = $this->mctPerson->txtFirstName_Create();
			$this->txtMiddleName = $this->mctPerson->txtMiddleName_Create();
			$this->txtLastName = $this->mctPerson->txtLastName_Create();
			$this->txtNameSuffix = $this->mctPerson->txtNameSuffix_Create();
			$this->txtNickName = $this->mctPerson->txtNickName_Create();
			$this->txtEmailAddress = $this->mctPerson->txtEmailAddress_Create();
            $this->txtEmailAddress->Required = false;
                     
			$this->txtPhoneNumber = $this->mctPerson->txtPhoneNumber_Create();
			$this->txtAvatarUri = $this->mctPerson->txtAvatarUri_Create();
			$this->txtCompanyName = $this->mctPerson->txtCompanyName_Create();
			
            $this->lstUsergroups = $this->mctPerson->lstUsergroups_Create();

			$this->btnSave = new QButton($this);
			$this->btnSave->Text = QApplication::Translate('Save');
            if(IndexPage::$blnAjaxOk)
                $this->btnSave->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnSave_Click'));
            else
                $this->btnSave->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnSave_Click'));
			$this->btnSave->CausesValidation = $this;

			$this->btnCancel = new QButton($this);
			$this->btnCancel->Text = QApplication::Translate('Cancel');
            if(IndexPage::$blnAjaxOk)
                $this->btnCancel->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCancel_Click'));
            else
                $this->btnCancel->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnCancel_Click'));

			$this->btnDelete = new QButton($this);
			$this->btnDelete->Text = QApplication::Translate('Delete');
            $strDeleteMsg =  QApplication::Translate('Are you SURE you want to DELETE this Person') . '?';
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction($strDeleteMsg));
            if(IndexPage::$blnAjaxOk)
                $this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
            else
                $this->btnDelete->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctPerson->EditMode;
		}

		public function btnSave_Click($strFormId, $strControlId, $strParameter)
        {
            $this->objPerson->OwnerPersonId = $this->objControlBlock->Account->PersonId;
            $this->objPerson->IsVirtual = true;
            
            if( '' == $this->txtEmailAddress->Text )
                $this->txtEmailAddress->Text =  $this->objPerson->EmailAddress;
                
			$this->mctPerson->SavePerson();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter)
        {
			$this->mctPerson->DeletePerson();
			$this->CloseSelf(true);
		}

		public function btnCancel_Click($strFormId, $strControlId, $strParameter) {
			$this->CloseSelf(false);
		}

		// Close Myself and Call ClosePanelMethod Callback
		protected function CloseSelf($blnChangesMade) {
			$strMethod = $this->strClosePanelMethod;
			$this->objControlBlock->$strMethod($blnChangesMade);
		}
	}//end class   
 }//end define   
?>