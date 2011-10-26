<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("LOSTPASSWORDMODULE.CLASS.PHP")){
define("LOSTPASSWORDMODULE.CLASS.PHP",1);

/**
* Class LostPasswordModule - provides a module to retrieve lost passwords
* This module will prompt the user for a username or an email address and attempt
* to retrieve the corresponding Account. If successful, it will create a onetime password
* and send it to the email address for the Person (account.person_id). account.onetime_password
* will be set true. The user is sent directly to change the password on login if onetime is true.
* If onetime is true at logout, valid_password is set to false, which will trigger failure and a
* redirect to this module if a second login is attempted - this is to enforce that the user reset
* the password after retrieval.
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: LostPasswordModule.class.php 286 2008-10-10 23:33:36Z erikwinn $
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


 class LostPasswordModule extends QPanel
 {
        /**
        * @var ContentBlockView objContentBlock - the content block to which this module is assigned
        */
        protected $objContentBlock;        
        /**
        * @var Account objAccount - local  instance of the Account
        */
        protected $objAccount = null;
        /**
        * @var Person objPerson - local  instance of the Person
        */
        protected $objPerson = null;
        /**
        * Note: this will accept a username or an email address, an account will be retrieved for either
        * if possible.
        * @var QTextBox txtUserName - input for password retrieval
        */
        public $txtUserName;

        public $lblInstructions;
        public $lblMessage;
        public $btnSubmit;
        
        /**
        * Module constructor
        * NOTE: This module ignores the required extra parameters ..
        *@param ContentBlock - parent controller object.
        *@param mixed - extra parameters, ignored
        */
        public function __construct( ContentBlockView $objContentBlock, $mixParameters=null)
        {
            //Parent should always be a ContentBlockView
            $this->objContentBlock =& $objContentBlock;
            
            try {
                parent::__construct($this->objContentBlock);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/LostPasswordModule.tpl.php';
            $this->txtUserName = new QTextBox($this);
            $this->txtUserName->Name = Quasi::Translate('Username or Email'); 
            $this->txtUserName->Required = true;
            
            $this->lblMessage = new QLabel($this);
            $this->lblMessage->HtmlEntities = false;
            
            $this->lblInstructions = new QLabel($this);
            $this->lblInstructions->HtmlEntities = false;
            $this->lblInstructions->Text = Quasi::Translate('Please enter your username or primary email address') .':<br />';
            $this->btnSubmit = new QButton($this);
            $this->btnSubmit->Text = QApplication::Translate('Submit');
            if(IndexPage::$blnAjaxOk)
                $this->btnSubmit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnSubmit_Click'));
            else
                $this->btnSubmit->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnSubmit_Click'));
            $this->btnSubmit->CausesValidation = $this;
                        
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

        public function btnSubmit_Click($strFormId, $strControlId, $strParameter)
        {
            $strInput = $this->txtUserName->Text;
            $this->objAccount = Account::LoadByUsername($strInput);
            if( ! $this->objAccount instanceof Account )
            {
                $aryPersons = Person::LoadArrayByEmailAddress($strInput);
                foreach($aryPersons as $objPerson)
                {
                    $this->objAccount = Account::LoadByPersonId($objPerson->Id);
                    if( $this->objAccount instanceof Account  )
                    {
                        $this->objPerson = $objPerson;
                        break;
                    }
                }
            }
            
            //Still no good? sorry ..
            if( ! $this->objAccount instanceof Account )
            {
                $strMessage = Quasi::Translate('I am sorry, I can not find an account for this username or email') . '! <br />'
                . Quasi::Translate('Please contact support at') . Quasi::$SupportEmailLink . Quasi::Translate('for further assistance') . '.' ;
            }
            else
            {
                $strMessage = Quasi::Translate('Thank You ') . $this->objAccount->Name . '! <br />'
                . Quasi::Translate('You will receive an email in a few minutes containing a onetime password to use to login and reset your password.');

                $this->lblInstructions->Visible = false;
                $this->txtUserName->Visible = false;
                $this->btnSubmit->Visible = false;
                $this->setRandomPassword();
            }           

            $this->lblMessage->Text =  $strMessage;
                      
        }
        private function setRandomPassword()
        {
            $strPassword = self::CreatePassword();
            $this->objAccount->Password = sha1($strPassword);
            $this->objAccount->OnetimePassword = true;
            $this->objAccount->ValidPassword = true;
            $this->objAccount->Save();
            
            if(null == $this->objPerson)
                $this->objPerson = Person::LoadById( $this->objAccount->PersonId );
                
            $strEmailText = Quasi::Translate('Hi ') . $this->objAccount->Name . ", \n"
                . Quasi::Translate('  Here is a temporary password you can use to log in to your account ') . ". \n\n"
                . Quasi::Translate(' Username') . ': ' . $this->objAccount->Username . " \n"
                . Quasi::Translate(' Password') . ': ' . $strPassword . " \n\n"
                . Quasi::Translate('PLEASE NOTE: This password can only be used once. You MUST RESET YOUR PASSWORD after logging in!')
                . Quasi::Translate('Warm Regards') . ", \n\n" . STORE_NAME . Quasi::Translate('Support Team') . "\n"
                . STORE_EMAIL_ADDRESS . "\n .\n";
                
            $objEmail = new QEmailMessage();
            $objEmail->From = STORE_NAME . ' <' . STORE_EMAIL_ADDRESS . '>';
            $objEmail->Subject = STORE_NAME . ' Important Information ';
            $objEmail->To = $this->objPerson->FullName . ' <' . $this->objPerson->EmailAddress . '>';
            $objEmail->Body = $strEmailText;
             
            QEmailServer::Send($objEmail);
        }
        public static function CreatePassword($intLength = 8, $blnHard = true) 
        {         
            if($blnHard)
                $strChars = "0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            else
                $strChars = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            
            $strToReturn  = '';
            $intCtr = 0;
            
            $intSelectionLength = strlen($strChars) - 1;
            while ($intCtr < $intLength) 
            {
                $strChar = substr($strChars, rand(0, $intSelectionLength), 1);
                if (false === strpos($strToReturn, $strChar)) 
                {
                    $strToReturn .= $strChar;
                    $intCtr++;
                }
            }            
            return $strToReturn;
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
