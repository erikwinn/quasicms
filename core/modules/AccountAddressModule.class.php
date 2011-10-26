<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("ACCOUNTADDRESSMODULE.CLASS.PHP")){
define("ACCOUNTADDRESSMODULE.CLASS.PHP",1);
 


/**
* Class AccountAddressModule - view/manage orders for a user account
* This class is a manager module; it creates a panel for a list of account addresses
* and/or a panel to edit or create an address. 
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: AccountAddressModule.class.php 109 2008-09-03 17:38:39Z erikwinn $
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
 
    class AccountAddressModule extends ListModuleBase
    {
        private $intAddressId;
        /**
        * Note: the parameter array is derived from the request url string by AccountManagerModule.
        * This array is passed by default to Account function modules, in this case it contains only
        * one optional element - an address id.
        *
        * Module constructor
        *@param ContentBlock - parent controller object.
        *@param array - aryParameters, should contain one element with an address id or be empty
        */
        public function __construct( $objParentObject, $aryParameters)
        {
           $this->objParentObject =& $objParentObject;
            if(!empty($aryParameters))
                $this->intAddressId = $aryParameters[0];
            
            try {
                parent::__construct($this->objParentObject);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->AutoRenderChildren = true;
//            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/AccountAddressModule.tpl.php';
            
            if($this->objAccount instanceof Account)
                $this->InitPanels();
        }
        protected function InitPanels()
        {
            ///@todo  parse the parameters to accept going directly to edit a specific address ..
            //if($this->intAddressId) ...
            
            // Get rid of all child controls for list and edit panels - not sure we need to do this here, remove?
            $this->pnlListView->RemoveChildControls(true);
            $this->pnlItemView->RemoveChildControls(true);
            $this->pnlItemView->Visible = false;
            $objNewPanel = new AccountAddressListPanel($this->pnlListView,
                                                                                    $this,
                                                                                    'ShowItemPanel',
                                                                                    'CloseItemPanel',
                                                                                    $this->Account->Id);
            $this->pnlListView->Visible = true;
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
        
        public function __get($strName)
        {
            switch ($strName)
            {
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