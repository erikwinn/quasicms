<?php
if(!defined('QUASICMS') ) die("No quasi.");

    if (!defined("ACCOUNTPASSWORDEDITPANEL.CLASS.PHP")){
define("ACCOUNTPASSWORDEDITPANEL.CLASS.PHP",1);

	/**
	 * AccountPasswordEditPanel provides a panel in which the user may
     * change their password and username.   
    *    
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: AccountPasswordEditPanel.class.php 286 2008-10-10 23:33:36Z erikwinn $
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
	class AccountPasswordEditPanel extends QPanel
    {
		// Local instance of the AccountMetaControl
		protected $objAccount;
        protected $objControlBlock;

		// Controls for Account's Data Fields
		public $txtUsername;
		public $txtPassword;
        public $txtPassword2;

		// Buttons
		public $btnSave;
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
            $this->objControlBlock = $objControlBlock;
            
            $this->objAccount =& IndexPage::$objAccount;
            
			$this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/AccountPasswordEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

            $this->txtUsername = new QTextBox($this, 'username');
            $this->txtUsername->Text = $this->objAccount->Username;
            $this->txtUsername->Required = true;
            $this->txtUsername->MaxLength = Account::UsernameMaxLength;
            $this->txtUsername->Name = QApplication::Translate('Login Name');
            
            $this->txtPassword = new QTextBox($this, 'password');
            $this->txtPassword->TextMode = QTextMode::Password;
            $this->txtPassword->Name = QApplication::Translate('New Password');
            $this->txtPassword->Required = true;
            $this->txtPassword2 = new QTextBox($this, 'password2');
            $this->txtPassword2->TextMode = QTextMode::Password;
            $this->txtPassword2->Name = QApplication::Translate('Confirm Password');
            $this->txtPassword2->Required = true;
			
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

		}
        public function Validate()
        {
            $blnToReturn = true;
            if($this->txtPassword->Text !== $this->txtPassword2->Text )
            {
                $this->txtPassword->Warning = 'Passwords do not match!';
                $blnToReturn = false;
            }

            return $blnToReturn;
        }

		public function btnSave_Click($strFormId, $strControlId, $strParameter)
        {
            $this->objAccount->Username = $this->txtUsername->Text;
            
            //paranoid about PHP array/object handling now .. assign first, _then manipulate.
            $strPassword = $this->txtPassword->Text;
            $this->objAccount->Password = sha1($strPassword);
            $this->objAccount->OnetimePassword = false;
            $this->objAccount->ValidPassword = true;
			$this->objAccount->Save();
			
            $this->CloseSelf(true);
		}

		public function btnCancel_Click($strFormId, $strControlId, $strParameter)
        {
			$this->CloseSelf(false);
		}

		protected function CloseSelf($blnChangesMade)
        {
			$strMethod = $this->strClosePanelMethod;
			$this->objControlBlock->$strMethod($blnChangesMade);
		}
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Account':
                    return $this->objAccount ;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
        
	}//end class
 }//end define
?>