<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("ACCOUNTMANAGERMODULE.CLASS.PHP")){
define("ACCOUNTMANAGERMODULE.CLASS.PHP",1);

/**
* Class AccountManagerModule - A managing module for submodules that provide user
* management account functions
*
*  This module provides the center or main panel for a Member's home
* page, to which they are directed immediately after login or registration.
* It should be assigned to a content block which is assigned only to
* the Account home page. It performs one basic function: load the requested
* account module according to the Request URL string page parameters.
*
*  The Account page has a module that parses the PageParameters to determine which Account module
* to load. Eg. for Addresses/ it will acivate the AccountAddressesModule. This module in turn contains
* an instance of AccountAddressEditPanel and AccountAddressListPanel which it will manage. For a
* parameter like Addresses/2 it will go directly to the edit panel for that address but signalling the sub
* module. By default, the sub modules will show a list page if given no parameters. Each sub-module is
* a Page request, the actions within each of these are ajax calls.
* 
*   The default Account modules managed by the AccountManagerModule are:
*     - AccountAddressModule : addresses  viewing and management
*     - AccountOrderModule : orders viewing and management
*     - AccountSettingsModule: General account settings, includes the following two modules
*     - AccountInfoModule : to change email address, avatar or personal name.
*     - AccountPasswordModule : change password and username
*
*@todo 
*     - AccountProfileModule : public profile information
*
*
* NOTE: The Account modules managed by this class do not need to be entered in the
* modules table in the database - instead, they are  loaded dynamically using the autoloader
* according to the request page parameters in the URL
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: AccountManagerModule.class.php 267 2008-10-07 19:17:26Z erikwinn $
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

    class AccountManagerModule extends QPanel
    {
        /**    
        *@var Account objAccount - local reference to the Account object
        */
        private $objAccount;       
        /**
        * @var Module objModule - local reference or instance of the module ORM object
        */
        protected $objModule;
        /**
        * @var ContentBlockView objContentBlock - the content block to which this module is assigned
        */
        protected $objContentBlock;        
        /**
        * Module constructor
        *@param ContentBlockView - objContentBlock parent controller object.
        *@param object objModule - the module displayed
        */
        public function __construct( ContentBlockView $objContentBlock, $objModule)
        {
            //Parent should always be a ContentBlockView
            $this->objContentBlock =& $objContentBlock;
            $this->objModule =& $objModule;

            try {
                parent::__construct($this->objContentBlock);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
            throw $objExc;
            }
            $this->AutoRenderChildren = true;
//            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/AccountManagerModule.tpl.php';

            //Make sure we have logged in, otherwise we don't display anything  ..
            $this->objAccount =& IndexPage::$objAccount;
            if($this->objAccount instanceof Account)
                $this->loadModule();
            else
                $this->Text = Quasi::Translate('We are sorry - you must be logged in to access your account settings.');
        }
        /**
        * This is the main action for the manager, it parses the URL string and loads the
        * appropriate module. The URL must name the module class.
        */
        private function loadModule()
        {
            $aryParameters = explode('/', IndexPage::$strPageParameters);
            $strModuleName = array_shift($aryParameters);
            if(empty($strModuleName))
                $strModuleName = 'Address';
                
            $strClassName = 'Account' . $strModuleName . 'Module';
            if(class_exists($strClassName) )
                new $strClassName($this, $aryParameters);
            else
                new AccountAddressModule($this, $aryParameters);
        }
        /**
         * Unused.
         */
        public function Validate()
        {
            $blnToReturn = true;
            // validate input here
            return $blnToReturn;
        }
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Account':
                    return $this->objAccount ;
                case 'ClassName':
                    return $this->objModule->ClassName ;
                case 'Module':
                    return $this->objModule ;
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