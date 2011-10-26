<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("LOGINMODULE.CLASS.PHP")){
define("LOGINMODULE.CLASS.PHP",1);

/**
* Class LoginModule - a login block
* This class provides a basic login module that may be assigned (once per page)
* to any content block. It validates that the fields are filled in, checks the account
* table for the username and performs a match on the password. It supports both
* Quasi native and OsCommerce encryption schemes (checking for both).
*
* On successful login the module will display the username and the length of time
* that they have been logged in as well as a shopping cart status if the shopping
* cart module is enabled. The user is redirected to a configurable landing page
* (defaulting to "AccountHome").On failure, the entry fields are redrawn and an
* error message is displayed.
*
* This module also provides a link to a registration page and a password retrieval
* page - these should exist in the page table and contain modules for each of these
* functions (CreateAccountModule and LostPasswordModule).
*
* The module stores an Account object in serialized form in $_SESSION['AccountLogin']
*  if login succeeds. Session timeout as configured for PHP determines the idle timeout.
*
*@todo
*   - more elegant error messages, its a bit messy ..
*   - create an enable/disable interface that will ensure that the registration and lost
*     password pages and modules exist.
*   - allow disabling individual parts, esp. shopping cart status
*   - when accepting an old oscommerce style password, transform to new style ..
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: LoginModule.class.php 516 2009-03-19 20:14:17Z erikwinn $
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


 class LoginModule extends QPanel 
 {
        // Our control block
        protected $objControlBlock;
        //Local reference to the Account object
        protected $objAccount;

        //shows "you are signed in as .."
        public $lblSignedInAs;
        //shows the login duration, just for fun ..
        public $lblLoginSpan;
        // Shopping cart status display ..
        public $lblShoppingCartStatus;
        
        //Input Controls 
        public $txtUsername;
        public $txtPassword;
        // Button Controls
        public $btnLogin;
        public $btnLogout;

        /**
        * LoginModule constructor
        * NOTE: This module ignores the required extra parameters ..
        *@param ContentBlockView - parent controller object.
        *@param mixed - extra parameters, ignored
        */
        public function __construct( ContentBlockView $objControlBlock, $mixParameters=null)
        {
            //Parent should always be a ContentBlockView
            $this->objControlBlock =& $objControlBlock;
            
            try {
                parent::__construct($this->objControlBlock);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            
            $this->HtmlEntities = false;
            
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/LoginModule.tpl.php';

            $this->objAccount =& IndexPage::$objAccount;
            //if not logged in, set up the login fields
            if( ! $this->objAccount )
            {
                $this->txtUsername = new QTextBox($this, 'username');
                $this->txtUsername->Required = true;
                $this->txtUsername->TabIndex = 1;
//                $this->txtUsername->Name = 'Username: ';                
                $this->txtUsername->Text = 'username';
                $this->txtUsername->AddAction(new QFocusEvent(), new QJavaScriptAction( 'clearText(this)'));

                $this->txtPassword = new QTextBox($this, 'password');
                $this->txtPassword->TextMode = QTextMode::Password;
                $this->txtPassword->Required = true;
                $this->txtPassword->Text = 'password';
                $this->txtPassword->AddAction(new QFocusEvent(), new QJavaScriptAction( 'clearText(this)'));
                if(IndexPage::$blnAjaxOk)
                    $this->txtPassword->AddAction(new QEnterKeyEvent(), new QAjaxControlAction($this, 'btnLogin_Click'));
                else
                    $this->txtPassword->AddAction(new QEnterKeyEvent(), new QServerControlAction($this, 'btnLogin_Click'));
                $this->txtPassword->CausesValidation = $this;
                $this->txtPassword->TabIndex = 2;

                // Create Buttons and Actions
                $this->btnLogin = new QButton($this,"LoginButton");
                $this->btnLogin->Text = Quasi::Translate('Login');
                if(IndexPage::$blnAjaxOk)
                    $this->btnLogin->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnLogin_Click'));
                else
                    $this->btnLogin->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnLogin_Click'));
                if(IndexPage::$blnAjaxOk)
                    $this->btnLogin->AddAction(new QEnterKeyEvent(), new QAjaxControlAction($this, 'btnLogin_Click'));
                else
                    $this->btnLogin->AddAction(new QEnterKeyEvent(), new QServerControlAction($this, 'btnLogin_Click'));
                $this->btnLogin->CausesValidation = $this;
                $this->btnLogin->TabIndex = 3;

            }//otherwise, set up various logged in info ..
            else
            {
                $this->btnLogout = new QButton($this, "LogoutButton");
                $this->btnLogout->Text = Quasi::Translate('Logout');
/* eh, this may be causing a bug
                if(IndexPage::$blnAjaxOk)
                    $this->btnLogout->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnLogout_Click'));
                else
*/
                $this->btnLogout->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnLogout_Click'));
                $this->lblSignedInAs_Create();
                $this->lblLoginSpan_Create();
                $this->lblShoppingCartStatus_Create();
            }
        }

        /**
        * Quasi supports importing user accounts from OsCommerce - this function checks an OSC style
        * encryption.
        *@param string strInput - the input string
        *@param string strStored - the stored encrypted string
        *@return bool true if valid
        */
        private function checkOSCPassword($strInput, $strStored)
        {
            // split apart the hash / salt
            $aryStack = explode(':', $strStored);

            if (sizeof($aryStack) != 2)
                return false;

            if (md5($aryStack[1] . $strInput) == $aryStack[0])
                return true;
            return false;
        }

        /**
        * Quasi native encrypted password check.
        * @todo - improve the encryption scheme
        *@param string strInput - the input string
        *@param string strStored - the stored encrypted string
        *@return bool true if valid
        */
        private function checkQuasiPassword($strInput, $strStored)
        {
            if( sha1( $strInput ) == $strStored)
                return true;
            return false;
        }
        
        /**
         * This Function is called when the login button is clicked - it checks the login data
         * returning false if it fails. Failure results in redrawing the form with error messages.
         */
        public function Validate()
        {
            $blnPassed = false;
            $blnValidPassword = true;
            $this->objAccount = Account::LoadByUsername($this->txtUsername->Text);

            if(! $this->objAccount || $this->objAccount->StatusId != AccountStatusType::Active)
            {
                $this->objAccount = null;
                unset($_SESSION["AccountLogin"]);
                $this->txtUsername->Warning = Quasi::Translate('Incorrect');
                return $blnPassed;
            }
            
            $strInput = $this->txtPassword->Text;
            $strStored =  $this->objAccount->Password;
            if(! $this->objAccount->ValidPassword)
                $blnValidPassword = false;
            elseif( $this->checkQuasiPassword($strInput, $strStored))
                $blnPassed = true;
            elseif($this->checkOSCPassword($strInput, $strStored))
                $blnPassed = true;
                
            if( ! $blnPassed)
            {
                $this->objAccount = null;
                unset($_SESSION["AccountLogin"]);
                if($blnValidPassword)
                    $this->txtPassword->Warning = Quasi::Translate('Incorrect');
                else
                    $this->txtPassword->Warning .= Quasi::Translate('Not Valid');
            }
            return $blnPassed;
        }

        /**
        * This function sets the SESSION['AccountLogin'] and updates the login state and count.
        * If this is a onetime password, it sets valid_password false - the user must save a new
        * password to reset this.
        */
        public function btnLogin_Click($strFormId, $strControlId, $strParameter)
        {
            $this->objAccount->Online = true;
            $this->objAccount->LoginCount += 1;
            $this->objAccount->LastLogin = date("Y-m-d H:i:s");
            $this->objAccount->UpdateLoginState();
            $_SESSION["AccountLogin"] = serialize($this->objAccount);

            if( $this->objAccount->OnetimePassword )
            {
                $this->objAccount->ValidPassword = false;
                $this->objAccount->Save();
                Quasi::Redirect( __QUASI_SUBDIRECTORY__ . '/index.php/AccountHome/Settings/Password' );
            }
            
            Quasi::Redirect( __QUASI_SUBDIRECTORY__ . LOGIN_REDIRECT );
/*            header( 'Location: http://' . $_SERVER['SERVER_NAME'] . __QUASI_SUBDIRECTORY__ . LOGIN_REDIRECT );
            exit;*/
        }
        /**
        * This function logs out the user and unsets the session variable for the account.
        * @todo - FIXME error on logout if session has timed out already ..
        */
        public function btnLogout_Click($strFormId, $strControlId, $strParameter)
        {            
            if( session_is_registered( 'AccountLogin' ) )
            {
                $this->objAccount = unserialize($_SESSION["AccountLogin"]);
                if($this->objAccount instanceof Account)
                {
                    $this->objAccount->Online = false;
                    $this->objAccount->UpdateLoginState();
                    $this->objAccount = null;
                }                
                session_unregister( 'AccountLogin' );
            }
            Quasi::Redirect(__QUASI_SUBDIRECTORY__ . '/index.php/Home');
        }
        /**
        * This a little label indicating who the user is logged it as
        */
        public function lblSignedInAs_Create()
        {
            $this->lblSignedInAs = new QLabel($this, "SignedInAs");
            $this->lblSignedInAs->HtmlEntities = false;
            
            if($this->Account instanceof Account )
            {
                $txt = Quasi::Translate('You are signed in as') . ':<br>&nbsp;&nbsp;<strong>' . $this->objAccount->Username . '</strong>';
                $this->lblSignedInAs->Text = $txt;
            }
        }
        /**
        * Here we create a little label indicating how long the user has been logged in.
        * Note: This is only updated on a full page load.
        */
        public function lblLoginSpan_Create()
        {
            $this->lblLoginSpan = new QLabel($this, "LoginSpan");
            if($this->Account instanceof Account )
            {
//                $dttLoginTime = QDateTime::FromTimestamp(strtotime( $this->Account->LastLogin) );
                $dttLoginTime = new QDateTime( $this->Account->LastLogin );
//                exit(var_dump($dttLoginTime));
                $txt = 'Logged in ' . $dttLoginTime->Age;
                $this->lblLoginSpan->Text = $txt;
            }
        }
        
        /**
        * This creates a small display of the shopping cart status
        * Note: This is only updated on a full page load.
        */
        public function lblShoppingCartStatus_Create()
        {
            $this->lblShoppingCartStatus = new QLabel($this, 'ShoppingCartStatus');
            $this->lblShoppingCartStatus->HtmlEntities = false; 
            $strText = '<img src="' . __QUASI_CORE_IMAGES__ . '/default_shopping_cart_icon.gif"> &nbsp;&nbsp;';
            if(IndexPage::$objShoppingCart instanceof ShoppingCart )
            {
                // $strText .= Quasi::Translate('You have ');
                $intItemCount = ShoppingCartItem::CountByShoppingCartId(IndexPage::$objShoppingCart->Id);
                if($intItemCount > 0)
                {
                    $strText .= $intItemCount . ' ';
                    if($intItemCount > 1)
                         $strText .= Quasi::Translate('items') . ' ';
                    else
                         $strText .= Quasi::Translate('item') . ' ';
                }
                else
                    $strText .= Quasi::Translate('No items') . ' ';
                    
                $strText .= Quasi::Translate('in your') . ' ';
                
                if($intItemCount)
                    $strText .= '<a href="http://'  . Quasi::$ServerName . __QUASI_SUBDIRECTORY__ . '/index.php/ShoppingCart' . '">'
                            . Quasi::Translate('cart') . '</a>' . '.';
                else
                    $strText .= Quasi::Translate('cart') . '.';
            }

            $this->lblShoppingCartStatus->Text = $strText;
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
