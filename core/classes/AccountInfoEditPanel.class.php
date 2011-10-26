<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("ACCOUNTINFOEDITPANEL.CLASS.PHP")){
define("ACCOUNTINFOEDITPANEL.CLASS.PHP",1);

/**
* Class AccountInfoEditPanel - provides modifiable display of Account data
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: AccountInfoEditPanel.class.php 286 2008-10-10 23:33:36Z erikwinn $
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
*@package Quasi
* @subpackage Modules
*/


    class AccountInfoEditPanel extends QPanel
    {
        /**
        * @var ContentBlockView objControlBlock - usually the AccountManagerModule
        */
        protected $objControlBlock;        
        /**
        * @var Account objAccount - local reference or instance of some relevant object ..
        */
        protected $objAccount;
        
        /**
        * @var PersonMetaControl mctPerson - meta control for Person data fields
        */
        protected $mctPerson;
        
        public $txtNamePrefix;
        public $txtFirstName;
        public $txtMiddleName;
        public $txtLastName;
        public $txtNameSuffix;
        public $txtNickName;
        public $txtEmailAddress;
        public $txtPhoneNumber;
//        public $txtAvatarUri;
        public $txtCompanyName;

        public $btnChangePassword;
        public $btnSave;
        
        /**
        * Module constructor
        * NOTE: This module ignores the required extra parameters ..
        *@param QPanel - parent controller object, usually AccountManagerModule.
        *@param mixed - extra parameters, ignored
        */
        public function __construct( QPanel $objControlBlock, $mixParameters=null)
        {
            //Parent should always be a ContentBlockView
            $this->objControlBlock =& $objControlBlock;
            
            try {
                parent::__construct($this->objControlBlock);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/AccountInfoEditPanel.tpl.php';
            
            $this->objAccount =& IndexPage::$objAccount;
                        
            $this->mctPerson = PersonMetaControl::Create($this, $this->objAccount->PersonId);
                                                   
            $this->txtNamePrefix = $this->mctPerson->txtNamePrefix_Create();
            $this->txtFirstName = $this->mctPerson->txtFirstName_Create();
            $this->txtMiddleName = $this->mctPerson->txtMiddleName_Create();
            $this->txtLastName = $this->mctPerson->txtLastName_Create();
            $this->txtNameSuffix = $this->mctPerson->txtNameSuffix_Create();
            $this->txtNickName = $this->mctPerson->txtNickName_Create();
            $this->txtEmailAddress = $this->mctPerson->txtEmailAddress_Create();
            $this->txtPhoneNumber = $this->mctPerson->txtPhoneNumber_Create();
//            $this->txtAvatarUri = $this->mctPerson->txtAvatarUri_Create();
            $this->txtCompanyName = $this->mctPerson->txtCompanyName_Create();
            
            $this->btnSave = new QButton($this);
            $this->btnSave->Text = QApplication::Translate('Save');
            if(IndexPage::$blnAjaxOk)
                $this->btnSave->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnSave_Click'));
            else
                $this->btnSave->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnSave_Click'));
            $this->btnSave->CausesValidation = $this;

            $this->btnChangePassword = new QButton($this);
            $this->btnChangePassword->Text = QApplication::Translate('Change Password or Username');
            if(IndexPage::$blnAjaxOk)
                $this->btnChangePassword->AddAction(new QClickEvent(), new QAjaxControlAction($this->objControlBlock, 'btnChangePassword_Click'));
            else
                $this->btnChangePassword->AddAction(new QClickEvent(), new QServerControlAction($this->objControlBlock, 'btnChangePassword_Click'));
            
        }
        /**
         * This Function is called when any input is sent - on failure the
         * fields are redrawn with optional error messages.
         */
        public function Validate()
        {
            $blnToReturn = true;
            // validate input here
            return $blnToReturn;
        }
        
        public function btnSave_Click($strFormId, $strControlId, $strParameter)
        {
            $this->mctPerson->SavePerson();
            if( $this->objControlBlock->lblMessage instanceof QLabel )
                $this->objControlBlock->lblMessage->Text = Quasi::Translate('Settings Saved') . '!';
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
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
                case 'Account':
                    try {
                        return ($this->objAccount = QType::Cast($mixValue, 'Account' ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                default:
                    try {
                        return (parent::__set($strName, $mixValue));
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }        
    }//end class
}//end define
?>