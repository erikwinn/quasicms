<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("CHECKOUTREVIEWMODULE.CLASS.PHP")){
define("CHECKOUTREVIEWMODULE.CLASS.PHP",1);

/**
* Class CheckOutReviewModule - provides display of order information for review during checkout
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: CheckOutReviewModule.class.php 457 2008-12-23 20:11:54Z erikwinn $
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

 class CheckOutReviewModule extends QPanel
 {
        /**
        *@var CheckOutModule objControlBlock - the main control block for the check out module
        */
        protected $objControlBlock;
        /**
        *@var Order objOrder - local reference to the order
        */
        protected $objOrder;
        /**
        * @var array CheckOutItems - a list of products as cart items.
        */
        public $aryCheckOutItemViews;
        /**
        * @var OrderTotalsView - module to display shipping, handling and total price for order
        */
        public $objOrderTotalsView;
        /**
        * @var AddressView objShippingAddressView - display for the shipping address
        */
        public $objShippingAddressView;
        /**
        * @var AddressView objBillingAddressView - display for the billing address
        */
        public $objBillingAddressView;
        /**
        * @var QPanel pnlPaymentMethod - panel to display information about the selected method
        */
        public $pnlPaymentMethod;
        /**
        * @var QPanel pnlShippinggMethod - panel to display information about the selected method
        */
        public $pnlShippingMethod;
        
        
        ///Buttons attached to the address and payment views for callback ..
        public $btnEditShippingAddress;
        public $btnEditBillingAddress;
        public $btnEditShippingMethod;
        public $btnEditPaymentMethod;
        /**
        * Module constructor
        *@param QPanel pnlParentObject - the DOM parent
        *@param CheckOutModule  objControlBlock - parent controller module.
        *@param Order objOrder - the Order being reviewed.
        */
        public function __construct( QPanel $pnlParentObject, $objControlBlock, Order $objOrder)
        {
        
            try {
                parent::__construct($pnlParentObject, 'CheckOutReviewModule');
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->objOrder =& $objOrder;
            
            $this->objControlBlock =& $objControlBlock;
            
            $this->AutoRenderChildren = true;
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/CheckOutReviewModule.tpl.php';
            $this->init();
            
        }
         /**
        * This function is called when the user clicks "Edit" by the Shipping address, it returns the user to the
        * Shipping information panel.
        *@param string strFormId - a string representation of the CSS Id for the main form
        *@param string strControlId - a string representation of the CSS Id for the control calling this function
        *@param string strParameter - a string containing optionally set parameters 
        */               
        public function btnEditShippingAddress_Click($strFormId, $strControlId, $strParameter)
        {
            $this->objControlBlock->GoBack(CheckOutStage::Shipping);
        }
         /**
        * This function is called when the user clicks "Edit" by the Billing address, it returns the user to the
        * Billing information panel.
        *@param string strFormId - a string representation of the CSS Id for the main form
        *@param string strControlId - a string representation of the CSS Id for the control calling this function
        *@param string strParameter - a string containing optionally set parameters 
        */               
        public function btnEditBillingAddress_Click($strFormId, $strControlId, $strParameter)
        {
            $this->objControlBlock->GoBack(CheckOutStage::Payment);
        }
        protected function init()
        {
            $this->aryCheckOutItemViews = array();
            //construct the list of items
            $aryOrderItems = $this->objOrder->GetNewOrderItemsArray();
            foreach( $aryOrderItems as $objOrderItem)
            {
                $objItemView = new CheckOutItemView( $this, $objOrderItem, false );
                $this->aryCheckOutItemViews[] = $objItemView;
            }

            $this->objOrderTotalsView = new OrderTotalsView($this, $this->objOrder, false);
            
            $this->objShippingAddressView = new AddressView($this,
                                                            $this->objOrder->ShippingAddressId,
                                                            'ShippingAddress: ');
            $this->objShippingAddressView->CssClass = 'ShippingAddressReview';
            $this->objShippingAddressView->AutoRenderChildren = true;
            
            $this->objBillingAddressView = new AddressView($this,
                                                           $this->objOrder->BillingAddressId,
                                                           'BillingAddress: ');
            $this->objBillingAddressView->CssClass = 'BillingAddressReview';
            $this->objBillingAddressView->AutoRenderChildren = true;
            
            $this->pnlPaymentMethod_Create();
            $this->pnlShippingMethod_Create();
            
            $this->btnEditShippingAddress = new QButton($this->objShippingAddressView);
            $this->btnEditShippingAddress->Text = Quasi::Translate('Edit');
            if(IndexPage::$blnAjaxOk)
                $this->btnEditShippingAddress->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnEditShippingAddress_Click'));
            else
                $this->btnEditShippingAddress->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnEditShippingAddress_Click'));
            
            $this->btnEditBillingAddress = new QButton($this->objBillingAddressView);
            $this->btnEditBillingAddress->Text = Quasi::Translate('Edit');
            if(IndexPage::$blnAjaxOk)
                $this->btnEditBillingAddress->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnEditBillingAddress_Click'));
            else                        
                $this->btnEditBillingAddress->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnEditBillingAddress_Click'));

        }
        public function RefreshView($objOrder)
        {
            $this->RemoveChildControls(true);
            $this->objOrder = $objOrder;
            $this->init();
        }
        protected function pnlPaymentMethod_Create()
        {
            if($this->objControlBlock->PaymentMethod instanceof PaymentMethod)
            {
                $objPaymentMethod = $this->objControlBlock->PaymentMethod;
                
                $this->pnlPaymentMethod = new QPanel($this);
                $this->pnlPaymentMethod->HtmlEntities = false;            
                $this->pnlPaymentMethod->CssClass = 'PaymentMethodReview';
                $this->pnlPaymentMethod->AutoRenderChildren = true;
                
                $strText =  '<div class="heading">' . Quasi::Translate('Payment Method') . ':</div>'
                                .  sprintf( '<div class="heading"> %s </div> <br /> %s ',
                                                                        $objPaymentMethod->Title,
                                                                        $objPaymentMethod->Description
                                                                    );
                $this->pnlPaymentMethod->Text = $strText;
            
                $this->btnEditPaymentMethod = new QButton($this->pnlPaymentMethod);
                $this->btnEditPaymentMethod->Text = Quasi::Translate('Change');
                if(IndexPage::$blnAjaxOk)
                    $this->btnEditPaymentMethod->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnEditBillingAddress_Click'));
                else
                    $this->btnEditPaymentMethod->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnEditBillingAddress_Click'));
            }
        }
        protected function pnlShippingMethod_Create()
        {
            if($this->objControlBlock->ShippingMethod instanceof ShippingMethod)
            {
                $objShippingMethod = $this->objControlBlock->ShippingMethod;
                
                $this->pnlShippingMethod = new QPanel($this);
                $this->pnlShippingMethod->HtmlEntities = false;
                $this->pnlShippingMethod->CssClass = 'ShippingMethodReview';
                $this->pnlShippingMethod->AutoRenderChildren = true;
                
                $strText = '<div class="heading">' . Quasi::Translate('Shipping Method') . ':</div>'
                                .  sprintf( '<div class="heading"> %s </div> <br /> %s ',
                                                                        $objShippingMethod->Title,
                                                                        $objShippingMethod->Description
                                                                    );
                $this->pnlShippingMethod->Text = $strText;
                
                $this->btnEditShippingMethod = new QButton($this->pnlShippingMethod);
                $this->btnEditShippingMethod->Text = Quasi::Translate('Change');
                if(IndexPage::$blnAjaxOk)
                    $this->btnEditShippingMethod->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnEditShippingAddress_Click'));
                else
                    $this->btnEditShippingMethod->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnEditShippingAddress_Click'));
            }
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
                case 'Order':
                    return $this->objOrder ;
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
                case 'Order':
                    try {
                        return ($this->objOrder = QType::Cast($mixValue, 'Order' ));
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