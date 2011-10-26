<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("CREATEACCOUNTMODULE.CLASS.PHP")){
define("CREATEACCOUNTMODULE.CLASS.PHP",1);

/**
* Class CreateAccountModule - a registration page module
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* This module provides a center page block with a registration page.
* On success the user is redirected to his/her UserPage.
*  On failure, the entry fields are redrawn and an error message 
* is displayed next to any fields that are incorrect or required.
*
* The module stores the Account, Person, Address in the respective tables
* - all of which are associated with the Person. An Account can have _one_
* and only one Person, a Person may have many Persons and Addresses
* which will be associated with the original Person for the Account.
* 
* $Id: CreateAccountModule.class.php 524 2009-05-12 16:23:30Z bbearnes $
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

 class CreateAccountModule extends QPanel
 {
        protected $objParentObject;

/** ******************************
* Section: Local object instances
******************************** */
        protected $objAccount;
        protected $objPerson;
        protected $objAddress;
       
/** ******************************
* Section: Object MetaControls
******************************** */
        protected $mctAccount;
        protected $mctPerson;
        protected $mctAddress;
    
/** ******************************
* Section: Control fields
******************************** */
        // Controls for Account's Data Fields
        public $txtUsername;
        public $txtPassword;
        public $txtPassword2;
        
        // Controls for Person's Data Fields
        public $txtFirstName;
        public $txtMiddleName;
        public $txtLastName;
        public $txtNamePrefix;
        public $txtNameSuffix;
        public $txtCompanyName;

        // Controls for Address's Data Fields
        public $txtTitle;
        public $txtStreet1;
        public $txtStreet2;
        public $txtSuburb;
        public $txtCity;
        public $txtCounty;
        public $lstZone;
        public $lstCountry;
        public $txtPostalCode;
        public $lstAddressType;
        public $txtEmailAddress;
        public $txtPhoneNumber;

        // Action Button Controls
        public $btnSave;
        public $btnReset;
        public $btnCancel;
       
        /**
        * Module constructor
        * NOTE: This module ignores the required extra parameters ..
        *@param ContentBlockView - parent controller object.
        *@param mixed - extra parameters, ignored
        */
        public function __construct( ContentBlockView $objParentObject, $mixParameters=null)
        {
            //Parent should always be a ContentBlockView
            $this->objParentObject =& $objParentObject;
            
            //make sure momma can do this ..
            try {
                parent::__construct($this->objParentObject/*, $this->strCssId*/);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            // We'll let the MetaControls create the new objects - this gives us a nice connection
            // to relevant fields and saving methods immediately.
            $this->mctAccount = AccountMetaControl::Create($this);
            $this->mctPerson = PersonMetaControl::Create($this);
            $this->mctAddress = AddressMetaControl::Create($this);

            // Use Account MetaControl's methods to create qcontrols based on Account's data fields
            $this->txtUsername = $this->mctAccount->txtUsername_Create('username', true);
            $this->txtUsername->Required = true;
            $this->txtUsername->Name = Quasi::Translate('Login Name');
            $this->txtPassword = $this->mctAccount->txtPassword_Create('password');
            $this->txtPassword->TextMode = QTextMode::Password;
            $this->txtPassword->Required = true;
            $this->txtPassword2 = new QTextBox($this, 'password2');
            $this->txtPassword2->TextMode = QTextMode::Password;
            $this->txtPassword2->Name = Quasi::Translate('Confirm Password');
            $this->txtPassword2->Required = true;
            //Person MetaControl methods
            $this->txtNamePrefix = $this->mctPerson->txtNamePrefix_Create();
            $this->txtNamePrefix->Name = Quasi::Translate('Mr., Ms., Dr., etc. ');
            $this->txtFirstName = $this->mctPerson->txtFirstName_Create();
            $this->txtFirstName->Required = true;
            $this->txtMiddleName = $this->mctPerson->txtMiddleName_Create();
            $this->txtLastName = $this->mctPerson->txtLastName_Create();
            $this->txtLastName->Required = true;
            $this->txtNameSuffix = $this->mctPerson->txtNameSuffix_Create();
            $this->txtNameSuffix->Name = Quasi::Translate('Jr., Sr., PhD, etc. ');
            $this->txtCompanyName = $this->mctPerson->txtCompanyName_Create();
            $this->txtEmailAddress = $this->mctPerson->txtEmailAddress_Create();
            $this->txtEmailAddress->Required = true;
            $this->txtEmailAddress->Name =Quasi::Translate('Email Address');
            $this->txtPhoneNumber = $this->mctPerson->txtPhoneNumber_Create();
            $this->txtPhoneNumber->Name =Quasi::Translate('Phone Number');
            // Address MetaControl methods
            $this->txtTitle = $this->mctAddress->txtTitle_Create();
            $this->txtTitle->Name = 'Address Title: ';
            $this->txtStreet1 = $this->mctAddress->txtStreet1_Create();
            $this->txtStreet1->Name = 'Street :';
            $this->txtStreet1->Required = true;
            
            $this->txtStreet2 = $this->mctAddress->txtStreet2_Create();
            $this->txtStreet2->Name = 'Street 2 or Apt.#:';
            
            $this->txtSuburb = $this->mctAddress->txtSuburb_Create();
            $this->txtSuburb->Name = 'Suburb :';
                    
            $this->txtCity = $this->mctAddress->txtCity_Create();
            $this->txtCity->Name = 'City :';
            $this->txtCity->Required = true;
            
            $this->txtCounty = $this->mctAddress->txtCounty_Create();
            $this->txtCounty->Name = 'County/District :';
            
            $this->lstZone = $this->mctAddress->lstZone_Create();
            $this->lstZone->Name = 'State/Province :';
            
            $this->lstCountry = $this->mctAddress->lstCountry_Create();
            $this->lstCountry->Name = 'Country :';
            $this->lstCountry->Required = true;
            
            $this->txtPostalCode = $this->mctAddress->txtPostalCode_Create();
            $this->txtPostalCode->Name = 'Zip/Postal Code :';
            
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/CreateAccountModule.tpl.php';
            // Create Buttons and Actions on this Form
            $this->btnSave = new QButton($this,'createAccountSaveButton');
            $this->btnSave->Text = Quasi::Translate('Register');
            if(IndexPage::$blnAjaxOk)
                $this->btnSave->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnSave_Click'));
            else
                $this->btnSave->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnSave_Click'));
            $this->btnSave->CausesValidation = true;

            $this->btnCancel = new QButton($this,'createAccountCancelButton');
            $this->btnCancel->Text = Quasi::Translate('Cancel');
            if(IndexPage::$blnAjaxOk)
                $this->btnCancel->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCancel_Click'));
            else
                $this->btnCancel->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnCancel_Click'));

            $this->btnReset = new QButton($this,'createAccountResetButton');
            $this->btnReset->Text = Quasi::Translate('Reset');
            if(IndexPage::$blnAjaxOk)
                $this->btnReset->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnReset_Click'));
            else
                $this->btnReset->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnReset_Click'));

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
            $this->objAccount = $this->mctAccount->Account;
            $this->objPerson = $this->mctPerson->Person;
            $this->objAddress = $this->mctAddress->Address;

            //Account Metacontrol will encrypt password ..
            $this->objAccount->Password = $this->txtPassword->Text;
            
            //This will give us an Id number for the person, which the Account needs for creation.
            $this->mctPerson->SavePerson();

            //Make the appropriate types and  foriegn keys ..
            $this->objAccount->PersonId = $this->objPerson->Id;
            $this->objAddress->PersonId = $this->objPerson->Id;

            $this->objAddress->TypeId = AddressType::Primary;

            // go ..
            $this->mctAccount->SaveAccount();
            $this->mctAddress->SaveAddress();
            $this->mctPerson->SavePerson();
            
            $this->objAccount->Online = true;
            $this->objAccount->LastLogin = date("Y-m-d H:i:s");
            $this->objAccount->UpdateLoginState();
            $_SESSION["AccountLogin"] = serialize($this->objAccount);
            
            if(Quasi::IsBrowser( QBrowserType::Opera ))
            {
        	ob_clean();
        	header('Location: http://www.batchpcb.com/index.php/AccountHome/PcbDesign');
        	exit;
            }
            
            Quasi::Redirect(__QUASI_SUBDIRECTORY__ . LOGIN_REDIRECT );
        }

        public function btnReset_Click($strFormId, $strControlId, $strParameter)
        {
            $this->mctAccount->Refresh();
            $this->mctPerson->Refresh();
            $this->mctAddress->Refresh();
        }

        public function btnCancel_Click($strFormId, $strControlId, $strParameter)
        {
                Quasi::Redirect( __QUASI_SUBDIRECTORY__ . '/index.php/Home' );
        }
    }//end class 
}//end define
?>