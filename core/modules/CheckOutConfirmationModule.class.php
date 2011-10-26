<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("CHECKOUTCONFIRMATIONMODULE.CLASS.PHP")){
define("CHECKOUTCONFIRMATIONMODULE.CLASS.PHP",1);

/**
* Class CheckOutConfirmationModule - provides display of order information for review during checkout
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: CheckOutConfirmationModule.class.php 272 2008-10-08 15:40:08Z erikwinn $
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

 class CheckOutConfirmationModule extends QPanel
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
        /**
        * Note that this is initialized by CheckOutModule based on payment status
        * @var QLabel lblMessage - used to display the message of confirmed or declined payment.
        */
        public $lblMessage;
        
        /**
        * Module constructor
        *@param QPanel pnlParentObject - the DOM parent
        *@param CheckOutModule  objControlBlock - parent controller module.
        *@param Order objOrder - the Order being reviewed.
        */
        public function __construct( QPanel $pnlParentObject, $objControlBlock, Order $objOrder)
        {
        
            try {
                parent::__construct($pnlParentObject, 'CheckOutConfirmationModule');
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->objControlBlock =& $objControlBlock;
            $this->objOrder =& $objOrder;
                        
            $this->AutoRenderChildren = true;
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/CheckOutConfirmationModule.tpl.php';
            
            $this->lblMessage = new QLabel($this);
            $this->lblMessage->HtmlEntities = false; 

            $this->init();
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
            
            if($this->objControlBlock->PaymentMethod instanceof PaymentMethod)
            {
                $objPaymentMethod = $this->objControlBlock->PaymentMethod;
                
                $this->pnlPaymentMethod = new QPanel($this);
                $this->pnlPaymentMethod->HtmlEntities = false;            
                $this->pnlPaymentMethod->CssClass = 'PaymentMethodReview';
                $this->pnlPaymentMethod->AutoRenderChildren = true;
                
                $strText =  '<div class="heading">' . Quasi::Translate('Payment Method') . ':</div>';
                $strText .= sprintf( '<div class="heading"> %s </div> <br /> %s ',
                                                                        $objPaymentMethod->Title,
                                                                        $objPaymentMethod->Description );
                $this->pnlPaymentMethod->Text = $strText;            
            }
            
            if($this->objControlBlock->ShippingMethod instanceof ShippingMethod)
            {
                $objShippingMethod = $this->objControlBlock->ShippingMethod;
                
                $this->pnlShippingMethod = new QPanel($this);
                $this->pnlShippingMethod->HtmlEntities = false;
                $this->pnlShippingMethod->CssClass = 'ShippingMethodReview';
                $this->pnlShippingMethod->AutoRenderChildren = true;
                
                $strText = '<div class="heading">' . Quasi::Translate('Shipping Method') . ': </div>';
                $strText .= sprintf( '<div class="heading"> %s </div> <br /> %s ',
                                                                        $objShippingMethod->Title,
                                                                        $objShippingMethod->Description );
                $this->pnlShippingMethod->Text = $strText;                
            }
        }
        
//        public function Validate(){ return true;}
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Order':
                    return $this->objOrder ;
                case 'Message':
                    return $this->lblMessage->Text ;
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
                case 'Message':
                    try {
                        return ($this->lblMessage->Text = QType::Cast($mixValue, QType::String ));
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