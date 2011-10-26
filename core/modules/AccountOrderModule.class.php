<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("ACCOUNTORDERMODULE.CLASS.PHP")){
define("ACCOUNTORDERMODULE.CLASS.PHP",1);


/**
* Class AccountOrderModule - view/manage orders for a user account
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: AccountOrderModule.class.php 275 2008-10-09 17:20:14Z erikwinn $
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
 
 class AccountOrderModule extends ListModuleBase
 {
        private $intOrderId;
        private $objOrderListView = null;
        private $objOrderItemView = null;
        /**
        * Module constructor
        *@param ContentBlockView - objParentObject parent controller object.
        *@param array - aryParameters, should contain one element with an order id or be empty
        */
        public function __construct( $objParentObject, $aryParameters)
        {
            $this->objParentObject =& $objParentObject;
            if(!empty($aryParameters))
                $this->intOrderId = array_pop($aryParameters);
            
            try {
                parent::__construct($this->objParentObject);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->AutoRenderChildren = true;
//            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/AccountOrderModule.tpl.php';
            
            if($this->objAccount instanceof Account)
                $this->InitPanels();
        }
        protected function InitPanels()
        {
/*            $this->pnlListView->RemoveChildControls(false);
            $this->pnlItemView->RemoveChildControls(false);*/
            $this->pnlItemView->Visible = false;
            $this->pnlListView->Visible = false;
            if($this->intOrderId)
            {
                $this->objOrderItemView = new AccountOrderViewPanel($this->pnlItemView, $this, 'CloseItemPanel', $this->intOrderId);
                $this->pnlItemView->Visible = true;
            }
            else
            {
                $this->objOrderListView = new AccountOrderListPanel($this->pnlListView, $this, 'ShowItemPanel', 'CloseItemPanel', $this->Account->Id);
                $this->pnlListView->Visible = true;
            }
        }
        
         //Overrides the parent to ensure that the list view is populated
        public function CloseItemPanel($blnUpdatesMade)
        {
            if(!$this->objOrderListView)
                $this->objOrderListView = new AccountOrderListPanel( $this->pnlListView, $this, 'ShowItemPanel', 'CloseItemPanel', $this->Account->Id );
            parent::CloseItemPanel($blnUpdatesMade);            
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