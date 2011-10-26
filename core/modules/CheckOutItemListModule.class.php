<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("CHECKOUTITEMLISTMODULE.CLASS.PHP")){
define("CHECKOUTITEMLISTMODULE.CLASS.PHP",1);
     

/** Class CheckOutItemListModule - provides display/modification of the list of items in an order
    *
    *  CheckOutItemListModule is a center page module displayed on the Checkout page.
    * It shows a detailed list of the items in an Order with quantity modification fields.
    * 
    * NOTE: You must call initItemList() to initialize the list and the totals - this allows for AJAX refreshing
    * between panels when quantities are modified.
    *
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: CheckOutItemListModule.class.php 197 2008-09-19 22:11:27Z erikwinn $
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
    * @subpackage Views
    *
    */
	
    class CheckOutItemListModule extends QPanel
    {
        protected $objControlBlock;
        protected $objCheckOutEditModule;
        
        protected $fltItemsTotalPrice;
        /**
        * @var array CheckOutItems - a list of products as cart items.
        */
        public $aryCheckOutItemViews;

        protected $blnModifiable;
                
        /**
        *@param QPanel objParentObject a reference to the CheckOutEditModule, DOM parent
        *@param QPanel objControlBlock a reference to the main CheckOutModule
        *@param bool blnModifiable - if true the quantity is modifiable
        */
		public function __construct($objParentObject, $objControlBlock, $blnModifiable=true)
        {

            try {
				parent::__construct($objParentObject);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
            // a reference to the main CheckOutModule
            $this->objControlBlock =& $objControlBlock;
            // a reference to the immediate parent
            $this->objCheckOutEditModule =& $objParentObject;
            //lets avoid foreach complaint in template if there are no items ..
            $this->aryCheckOutItemViews = array();
            $this->blnModifiable = $blnModifiable;         
                                    
            $this->Template = __QUASI_CORE_TEMPLATES__ . '/CheckOutItemListModule.tpl.php';
            $this->fltItemsTotalPrice = 0;
            
		}
        
        /**
        * This function initializes the item list display and and the total price. It may be called to
        * refresh the list and totals at any time after instantiation.
        *@param array aryOrderItems - an array of OrderItems from which to create a list view
        */
        public function initItemList($aryOrderItems)
        {
            $this->fltItemsTotalPrice = 0;
            $this->aryCheckOutItemViews = array();
            //construct the list of items
            foreach($aryOrderItems as $objOrderItem)
            {
                $objItemView = new CheckOutItemView( $this, $objOrderItem, $this->blnModifiable );
                $this->fltItemsTotalPrice += $objItemView->ItemTotal;
                $this->aryCheckOutItemViews[] = $objItemView;
            }
        }      
        public function RefreshTotalPrice()
        {
            $this->fltItemsTotalPrice = 0;
            foreach($this->aryCheckOutItemViews as $objItemView)
                $this->fltItemsTotalPrice += $objItemView->ItemTotal;
            if($this->objCheckOutEditModule instanceof CheckOutEditModule)
                $this->objCheckOutEditModule->TotalItemsCharge = $this->fltItemsTotalPrice;
        }
              
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'ItemsTotalPrice':
                    return $this->fltItemsTotalPrice ;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
        
	}// end class
 }//end define shield   
?>