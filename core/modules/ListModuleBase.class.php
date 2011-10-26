<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("LISTMODULEBASE.CLASS.PHP")){
define("LISTMODULEBASE.CLASS.PHP",1);

/**
* Class ListModuleBase - List and Item view panel manager
*@author Erik Winn <erikwinnmail@yahoo.com>
*
*  This module provides the base class for modules that display list and item
* views of objects. Based on the Dashboard, it provides two panels with callbacks
* for switching between panels, one panel is for displaying lists and one is for
* individual items which may have been selected from the list.
*
* The callback methods here only show or close the view panel - the list panel
* is shown by default if there are no page parameters in the URL. When the
* view panel is closed, the list panel is displayed after ensuring that it is populated
* with a list.
*
*  A reference to the Account object is also kept here to facilitate permissions
* checking on display of items.
*
* . New List modules can be created by extending this class and populating 
* the relevant panels.
* 
* 
* $Id: ListModuleBase.class.php 2 2008-07-31 18:55:50Z erikwinn $
*@version 0.1
*
*@copyright (C) 2008 by Erik Winn
*@license GPL v.2

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
*
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
 
 abstract class ListModuleBase extends QPanel
 {
        /**
        *@var ContentBlockView - the controlling block for the module
        */
        protected $objParentObject;
        //Local reference to the Account
        protected $objAccount;
        
        //Display panels
        public $pnlListView;
        public $pnlItemView;
        
        public $objDefaultWaitIcon;
        
        /**
        * Module constructor
        * NOTE: This module ignores the required extra parameters ..
        *@param ContentBlockView - parent controller object.
        *@param mixed - extra parameters, ignored
        */
        public function __construct($objParentObject)
        {
            
            $this->objParentObject =& $objParentObject;

            try {
                parent::__construct($this->objParentObject);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->objAccount =& IndexPage::$objAccount;
                                        
            $this->pnlListView = new QPanel($this, 'pnlListView');
            $this->pnlListView->AutoRenderChildren = true;

            $this->pnlItemView = new QPanel($this, 'pnlItemView');
            $this->pnlItemView->AutoRenderChildren = true;
            $this->pnlItemView->Visible = false;

            $this->objDefaultWaitIcon = new QWaitIcon($this);
        }
        
        public function Validate()
        {
            $blnToReturn = true;
            return $blnToReturn;
        }


/*      public function SetListPane(QPanel $objPanel) {
            $this->pnlListView->RemoveChildControls(true);
            $objPanel->SetParentControl($this->pnlListView);
        }*/

        public function CloseItemPanel($blnUpdatesMade)
        {
            $this->pnlItemView->RemoveChildControls(true);
            $this->pnlItemView->Visible = false;

            if ($blnUpdatesMade)
                $this->pnlListView->Refresh();
            $this->pnlListView->Visible = true;
        }

         public function ShowItemPanel(QPanel $objPanel = null)
        {
            $this->pnlListView->Visible = false;
            $this->pnlItemView->RemoveChildControls(true);
            if ($objPanel) {
                $objPanel->SetParentControl($this->pnlItemView);
                $this->pnlItemView->Visible = true;
            } else {
                $this->pnlItemView->Visible = false;
            }
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