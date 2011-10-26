<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("CHECKOUTEDITMODULE.CLASS.PHP")){
define("CHECKOUTEDITMODULE.CLASS.PHP",1);

/**
* Class CheckOutEditModule - a module providing the modiflable display of order information
*
* This class displays the list of items in an Order with modiflable quantity fields, and two
* address fields (shipping and billing) that may also be modified. It also displays the totals
* for the Order including an estimated shipping charge based on the default shipping address
* and the cheapest method available for that address.
* 
* This class is displayed by the CheckOutModule for the first part of the check out process. It
* presents the user with a view of the items in the Order, Addresses, and a selection of shipping
* and payment methods. Each of these may be modified here.
* 
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: CheckOutEditModule.class.php 286 2008-10-10 23:33:36Z erikwinn $
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
 class CheckOutEditModule extends QPanel
 {
        /**
        * ContentBlock contains the data and main functions for the check out module.
        * @var CheckOutModule - both the DOM parent object and the control block passed to submodules
        */
        protected $objControlBlock;
        protected $objShippingAddress;
        protected $objBillingAddress;
        /**
        * This module shows a panel containing the items on the order with modifiable quantity fields
        * and a remove button.
        * @var CheckOutItemListModule - lists order items
        */
        public $objCheckOutItemListModule;
        /**
        * @var CheckOutTotalsView - module to display shipping, handling and total price for order
        */
        public $objCheckOutTotalsView;
        /**
        * This module shows a panel containing the address for the order
        * @var AddressView
        */
        public $objShippingAddressView;
        public $objBillingAddressView;
        /**
        * This module shows a panel for editting the addresses for the order. It is called with a parameter
        * to determine which of the addresses to edit (Billing | Shipping)
        * @var AddressView
        */
        public $objAddressEditPanel;
        
        /**
        * @var QButton control buttons to edit the address fields
        */
        public $btnChangeShipping;
        public $btnChangeBilling;
        
        /**
        * This is here only because technically PayPal requires that you have two points of entry into their
        * scheme .. one is supposed to be on the "shopping cart page" - that is a pain to set up and putting
        * it here makes more sense as we want to offer shipping options. Besides, I don't like the idea
        * of sending the customer to approve a payment amount and _then_ adding shipping - too sleazy
        * for my troublesome sense of ethics ...
        * @var QImageButton button to show on the first panel to support PayPal Express Checkout
        */
        public $btnPayPalExpressButton;
        
        /**
        * Module constructor
        * NOTE: This module ignores the required extra parameters ..
        *@param ContentBlock - parent controller object.
        *@param mixed - extra parameters, ignored
        */
        public function __construct( CheckOutModule $objControlBlock, $mixParameters=null)
        {
            
            try {
                parent::__construct($objControlBlock);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->objControlBlock =& $objControlBlock;
            $this->objShippingAddress =& $this->objControlBlock->objShippingAddress;
            $this->objBillingAddress =& $this->objControlBlock->objBillingAddress;
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/CheckOutEditModule.tpl.php';
            
            $this->objCheckOutItemListModule = new CheckOutItemListModule($this, $objControlBlock);
            $this->objCheckOutItemListModule->initItemList($objControlBlock->aryOrderItems);
            
            $this->objShippingAddressView = new AddressView($this,
                                                                                              $this->objShippingAddress->Id,
                                                                                              'ShippingAddress: ',
                                                                                              'ShippingAddressView'
                                                                                              );
            $this->objBillingAddressView = new AddressView($this,
                                                                                            $this->objBillingAddress->Id,
                                                                                            'BillingAddress: ',
                                                                                            'BillingAddressView'
                                                                                            );

            $this->objShippingAddressView->AutoRenderChildren = true;
            $this->objBillingAddressView->AutoRenderChildren = true;
            
            $this->objCheckOutTotalsView = new CheckOutTotalsView($this);
            // grab shipping charges from shipping module if possible..
            if( $this->objControlBlock->ShippingModule instanceof ShippingModule
                && $this->objControlBlock->ShippingMethod instanceof ShippingMethod)
                $this->objCheckOutTotalsView->ShippingCharge = $this->objShippingModule->SelectedMethod->Rate;
            else
                $this->objCheckOutTotalsView->ShippingCharge = 0;                
            /// @todo make handling charge configurable.
            $this->objCheckOutTotalsView->HandlingCharge = 10.0;            
            $this->objCheckOutTotalsView->TotalItemsCharge = $this->objCheckOutItemListModule->ItemsTotalPrice;
            
            $this->objCheckOutTotalsView->RefreshTotal();
            
            $this->btnChangeShipping = new QButton($this->objShippingAddressView);
            $this->btnChangeShipping->Text = Quasi::Translate('Change');
            if(IndexPage::$blnAjaxOk)
                $this->btnChangeShipping->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnChangeAddress_Click'));
            else
                $this->btnChangeShipping->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnChangeAddress_Click'));
            $this->btnChangeShipping->ActionParameter = 'Shipping';
            $this->btnChangeShipping->CausesValidation = $this;
            
            $this->btnChangeBilling = new QButton($this->objBillingAddressView);
            $this->btnChangeBilling->Text = Quasi::Translate('Change');
            if(IndexPage::$blnAjaxOk)
                $this->btnChangeBilling->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnChangeAddress_Click'));
            else
                $this->btnChangeBilling->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnChangeAddress_Click'));
            $this->btnChangeBilling->ActionParameter = 'Billing';
            $this->btnChangeBilling->CausesValidation = $this;

        }
        
        public function btnChangeAddress_Click($strFormId, $strControlId, $strParameters)
        {
            $aryParameters = explode(',',$strParameters);
            switch($aryParameters[0])
            {
                case 'Billing':
                    $this->ShowAddressEditPanel($this->objBillingAddress->Id);
                    break;
                case 'Shipping':
                    $this->ShowAddressEditPanel($this->objShippingAddress->Id);
                    break;
                case 'New':
                    $this->ShowAddressEditPanel();
                    break;
                default:
                    throw new QCallerException('Unknown Address change - ' . $aryParameters[0] );
            }
        }
        /**
        * Shows the panel to the selected shipping address, hiding all the others.
        */
        public function ShowAddressEditPanel($intAddressId=null)
        {
            $this->objCheckOutItemListModule->Visible = false;
            $this->objShippingAddressView->Visible = false;
            $this->objBillingAddressView->Visible = false;
            $this->objControlBlock->btnContinue->Visible = false;
            $this->objControlBlock->ShippingModule->Visible = false;
            $this->objAddressEditPanel = new AccountAddressEditPanel($this, $this, 'CloseAddressEditPanel', $intAddressId );
            //set a template that doesn't show the delete button ..
            $this->objAddressEditPanel->Template = __QUASI_CORE_TEMPLATES__ .  '/CheckOutAddressEditPanel.tpl.php';
            $this->objAddressEditPanel->Visible = true;            
        }
        /**
        * Closes the address editting panel, updates the addresses and shows the CheckOutEditModule again.
        *@todo  implement reloading the address data, for now its just a brute force kludge to update the page.
        */
        public function CloseAddressEditPanel($blnUpdatesMade)
        {
            Quasi::Redirect(__QUASI_SUBDIRECTORY__ . '/index.php/CheckOut');
/*
            $this->objAddressEditPanel->Visible = false;
            // update the addresses            
            $this->objShippingAddressView = new AddressView($this, $this->objShippingAddress->Id, 'ShippingAddress: ', 'ShippingAddressView' );
            $this->objBillingAddressView = new AddressView($this, $this->objBillingAddress->Id, 'BillingAddress: ', 'BillingAddressView' );
            $this->objShippingAddressView->Visible = true;
            $this->objBillingAddressView->Visible = true;
            $this->objCheckOutItemListModule->Visible = true;
*/
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
        public function __get($strName)
        {
            switch ($strName)
            {
                //Note: this (like all __get magic) returns a copy ..)
                case 'ItemListModule':
                    return $this->objCheckOutItemListModule ;
                case 'ShoppingCart':
                    return $this->objShoppingCart ;
                case 'ShippingAddress':
                    return $this->objShippingAddress ;
                case 'BillingAddress':
                    return $this->objBillingAddress ;
                case 'Account':
                    return IndexPage::$objAccount ;
                case 'TotalItemsCharge':
                    return $this->objCheckOutTotalsView->ShippingCharge ;
                case 'ShippingCharge':
                    return $this->objCheckOutTotalsView->ShippingCharge ;
                case 'HandlingCharge':
                    return $this->objCheckOutTotalsView->HandlingCharge ;
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
                case 'ShippingCharge':
                    return ($this->objCheckOutTotalsView->ShippingCharge = $mixValue);
                case 'TotalItemsCharge':
                    return ($this->objCheckOutTotalsView->TotalItemsCharge = $mixValue);

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