<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("ACCOUNTSETTINGSMODULE.CLASS.PHP")){
define("ACCOUNTSETTINGSMODULE.CLASS.PHP",1);

/**
* Class AccountSettingsModule - provides modifiable display of Account data
* This module contains two panels, one for changing the password and one
* for editing personal information.
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: AccountSettingsModule.class.php 237 2008-10-01 19:44:17Z erikwinn $
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


 class AccountSettingsModule extends QPanel
 {
        /**
        * @var ContentBlockView objControlBlock - usually the AccountManagerModule
        */
        protected $objControlBlock;        
        /**
        * @var Account objAccount - local reference or instance of the account
        */
        protected $objAccount;                
        /**
        * @var AccountPasswordEditPanel pnlPasswordEdit - panel for changing password and username
        */
        public $pnlPasswordEdit;
        /**
        * @var AccountInfoEditPanel pnlInfoEdit - panel for changing general account information
        */
        public $pnlInfoEdit;
        /**
        * @var QLabel lblMessage - let the user know they have saved ..
        */
        public $lblMessage;
        
        /**
        * Module constructor
        * 
        *@param QPanel objControlBlock - parent controller object, usually AccountManagerModule.
        *@param array aryParameters - extra parameters, if present should be "Password" to indicate password edit
        */
        public function __construct( QPanel $objControlBlock, $aryParameters=null)
        {
            //Parent should always be a ContentBlockView
            $this->objControlBlock =& $objControlBlock;
            
            try {
                parent::__construct($this->objControlBlock);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
//            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/AccountSettingsModule.tpl.php';
            $this->AutoRenderChildren = true;

            $this->lblMessage = new QLabel($this);
            $this->lblMessage->HtmlEntities = false; 
            
            $this->objAccount =& IndexPage::$objAccount;
            if( null != $aryParameters && 'Password' == $aryParameters[0])
            {
                $this->pnlPasswordEdit = new AccountPasswordEditPanel($this, $this, 'ClosePasswordPanel');
                $this->pnlPasswordEdit->Visible = true;
            }
            else
            {
                $this->pnlInfoEdit = new AccountInfoEditPanel($this, 'pnlInfoEdit');
                $this->pnlInfoEdit->Visible = true;
            }           
            
        }
        /**
        * Unused
         */
        public function Validate()
        {
            $blnToReturn = true;
            // validate input here
            return $blnToReturn;
        }

        /**
        * This function closes the password edit panel (by removing all child controls) and shows
        * the general info editting panel again. It is called from an action in the password edit panel
        */
        public function ClosePasswordPanel($blnUpdatesMade)
        {
            $this->RemoveChildControls(true);
            $this->pnlInfoEdit = new AccountInfoEditPanel($this, 'pnlInfoEdit');
            $this->pnlInfoEdit->Visible = true;
            $this->lblMessage->Text = Quasi::Translate('Password Saved') .'!';
        }
        
        /**
        * This function closes the general information edit panel (by removing all child controls) and shows
        * the password edit panel when the user selects the "Change password" button
        */
         public function btnChangePassword_Click($strFormId, $strControlId, $strParameter)
        {
            $this->RemoveChildControls(true);
            $this->pnlPasswordEdit = new AccountPasswordEditPanel($this, $this, 'ClosePasswordPanel');
            $this->pnlPasswordEdit->Visible = true;
        }

        ///gettors
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