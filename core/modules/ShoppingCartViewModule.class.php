<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("SHOPPINGCARTVIEWMODULE.CLASS.PHP")){
define("SHOPPINGCARTVIEWMODULE.CLASS.PHP",1);

/** Class ShoppingCartViewModule - provides display/modification of the list of items in an order
    *
    *  ShoppingCartViewModule is a center page module displayed on the ShoppingCart page.
    * It shows a detailed list of the items in an Order with quantity modification fields and a
    * button to go directly to the CheckOut page.
    * 
    *
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: ShoppingCartViewModule.class.php 462 2008-12-30 17:14:49Z erikwinn $
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
        
    class ShoppingCartViewModule extends QPanel
    {
        /**
        * @var ContentBlock objParentObject - the DOM parent,
        */
        protected $objParentObject;
        /**
        * @var Order objOrder - order created from cart items for account,
        */
        protected $objOrder;
        /**
        * @var ShoppingCart objShoppingCart - local reference to the current user's cart
        */
        protected $objShoppingCart;
        /**
        * @var float fltItemsTotalPrice - the total of all the line items in the cart, aka subtotal
        */
        protected $fltItemsTotalPrice;
        /**
        * @var boolean blnLoggedIn - indicate if the user is logged in.
        */
        protected $blnLoggedIn = false;
        /**
        * @var array ShoppingCartItems - a list of products as cart item Views.
        */
        public $aryShoppingCartItemViews = array();
        /**
        * @var OrderTotalsView objOrderTotalsView - panel that displays the order summary ..
        */
        public $objOrderTotalsView;
        
        ///Controls ..
        /**
        * @var QLabel lblMessage - a text label to relay messages to the user
        */
        public $lblMessage;
        /**
        * @var QLabel lblProgressBar - a progress bar depicting the first (Shopping cart) stage of checkout
         */
        public $lblProgressBar;
        
        public $btnSave;
        public $btnCheckOut;
                
        public function __construct( ContentBlockView $objParentObject, $intShoppingCartId=null)
        {
            $this->objParentObject =& $objParentObject;
            $this->objShoppingCart =& IndexPage::$objShoppingCart;
            try {
                parent::__construct($this->objParentObject);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }                                   
            $this->Template = __QUASI_CORE_TEMPLATES__ . '/ShoppingCartViewModule.tpl.php';
                        
            // if not logged in show nothing ..
            if( ! IndexPage::$objAccount instanceof Account )
                return;
            else
                $this->blnLoggedIn = true;
                
            $this->aryShoppingCartItemViews = array();
            
            $this->fltItemsTotalPrice = 0;
            if($this->objShoppingCart instanceof ShoppingCart )
            {
                foreach ( $this->objShoppingCart->GetShoppingCartItemArray() as $objShoppingCartItem )
                {
                    $objItemView = new ShoppingCartItemView( $this, $objShoppingCartItem );
                    $this->aryShoppingCartItemViews[] = $objItemView;
                }                
                $this->objOrder = $this->objShoppingCart->CreateNewOrder(true);
                if($this->objOrder instanceof Order)
                    $this->objOrderTotalsView = new OrderTotalsView($this, $this->objOrder);
            }

            $this->lblMessage = new QLabel($this);
            $this->lblProgressBar = new QLabel($this);
            $this->lblProgressBar->HtmlEntities = false;
            $this->lblProgressBar->CssClass = 'ProgressBarShoppingCart';

            $this->lblProgressBar->Text = sprintf('<span class="heading">%s</span><span class="label">%s</span>
                                                                       <span class="label">%s</span><span class="label">%s</span>
                                                                       <span class="label">%s</span><span class="label">%s</span>',
                                                                        STORE_NAME . ' ' . Quasi::Translate('Checkout Process') . ':',
                                                                        Quasi::Translate('cart'),
                                                                        Quasi::Translate('shipping'),
                                                                        Quasi::Translate('payment'),
                                                                        Quasi::Translate('review order'),
                                                                        Quasi::Translate('receipt'));

            $this->btnSave = new QButton($this);
            $this->btnSave->Text = Quasi::Translate('Update');
            if(IndexPage::$blnAjaxOk)
                $this->btnSave->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnSave_Click'));
            else
                $this->btnSave->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnSave_Click'));
            $this->btnSave->CausesValidation = QCausesValidation::SiblingsAndChildren;
            
            $this->btnCheckOut = new QLabel($this);
            $this->btnCheckOut->AddCssClass('button');
            $this->btnCheckOut->HtmlEntities = false;
            $this->btnCheckOut->Text = '<a href="https://' . Quasi::$ServerName . __QUASI_SUBDIRECTORY__ . '/index.php/CheckOut">'
                                                             .  Quasi::Translate('CheckOut') . '</a>';

/* yes, it would be nice to make sure that we saved, but IE cannot redirect correctly to https 
so we must use a hard link until somebody figures out a way around this.
            $this->btnCheckOut->Text = Quasi::Translate('CheckOut');
            if(IndexPage::$blnAjaxOk)
                $this->btnCheckOut->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCheckOut_Click'));
            else
                $this->btnCheckOut->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnCheckOut_Click'));
*/
            
        }
        
        public function RefreshTotals()
        {
            if( ! $this->objOrder instanceof Order )
                return;
                
            $this->fltItemsTotalPrice = 0;
            
            foreach($this->aryShoppingCartItemViews as $objItemView)
                $this->fltItemsTotalPrice += $objItemView->ItemTotal;
                
            $this->objOrder->ProductTotalCharged = $this->fltItemsTotalPrice;
            $this->objOrderTotalsView->SetTotals($this->objOrder);
        }
        
        public function btnSave_Click($strFormId, $strControlId, $strParameter)
        {
            foreach($this->aryShoppingCartItemViews as &$objItemView)
            {
                
                $objItemView->ShoppingCartItem->Quantity = $objItemView->Quantity;
                if($objItemView->Quantity <= 0)
                    $objItemView->ShoppingCartItem->Delete();
                else
                    $objItemView->ShoppingCartItem->Save();
            }
            $this->objShoppingCart->Reload();
            $this->RefreshTotals();
            $this->lblMessage->Text = Quasi::Translate('Shopping Cart Saved') . '!';
        }
        
       //Note: unused due to IE ssl redirect incompetence ..
        public function btnCheckOut_Click($strFormId, $strControlId, $strParameter)
        {
            foreach($this->aryShoppingCartItemViews as &$objItemView)
            {
                $objItemView->ShoppingCartItem->Quantity = $objItemView->Quantity;
                $objItemView->ShoppingCartItem->Save();
            }
            Quasi::Redirect('https://' . Quasi::$ServerName . __QUASI_SUBDIRECTORY__ . '/index.php/CheckOut');
        }
                
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'ShoppingCart':
                    return $this->objShoppingCart ;
                case 'ItemsTotalPrice':
                    return $this->fltItemsTotalPrice ;
                case 'Tax':
                    return $this->objOrder->Tax;
                case 'LoggedIn':
                    return $this->blnLoggedIn;
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
                case 'ShoppingCart':
                    try {
                        return ($this->objShoppingCart = QType::Cast($mixValue, 'ShoppingCart'));
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
    }// end class
 }//end define shield   
?>
