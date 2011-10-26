<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("SHOPPINGCARTMODULE.CLASS.PHP")){
define("SHOPPINGCARTMODULE.CLASS.PHP",1);

/**
* Class ShoppingCartModule - mini shopping cart display 
*@author Erik Winn <erikwinnmail@yahoo.com>
*
*  This module provides the mini cart view for items in the shopping cart.
* It is created by default for any user that signs in. An account has one
* shopping cart, when checkout occurs the items are removed from the
* cart when the order is created.
*
* The mini view is a small box containing a hyperlinked list of the items
* in the cart, clicking on an item redirects the user to the product page, clicking
* on the title header ("Shopping Cart") redirects the user to a full cart view page.
*
*@todo
*   - add total price (incl. shipping) estimates
* 
* $Id: ShoppingCartModule.class.php 286 2008-10-10 23:33:36Z erikwinn $
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

 class ShoppingCartModule extends QPanel
 {
        /**
        *@var ContentBlockView - objControlBlock the controlling block for the module
        */
        protected $objControlBlock;
        /**
        * @var Module objModule - local reference or instance of the module ORM object
        */
        protected $objModule;
        /**
        * @var Account objAccount - local reference to the current user's account object
        */
        protected $objAccount;
        /**
        * @var ShoppingCart objShoppingCart - local reference to the current user's shopping cart object
        */
        protected $objShoppingCart;
        /**
        * @var integer intItemCount - number of (line) items in the cart
        */
        protected $intItemCount = 0;
        /**
        * @var QPanel pnlHeader - the title box for the cart
        */
        public $pnlHeader;
        /**
        * @var QPanel pnlItemList - the panel containing the list of items
        */
        public $pnlItemList;
        
        /**
        * Module constructor
        *@param ContentBlockView - objContentBlock parent controller object.
        *@param Module objModule - the ORM object of the module displayed
        */
        public function __construct( ContentBlockView $objControlBlock, $objModule)
        {
            
            $this->objControlBlock =& $objControlBlock;
            $this->objModule =& $objModule;
            
            try {
                parent::__construct($this->objControlBlock, 'CartModule', true);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/ShoppingCartModule.tpl.php';

            $this->objAccount =& IndexPage::$objAccount;
            $this->objShoppingCart =& IndexPage::$objShoppingCart;

            $this->pnlHeader = new QPanel($this, 'CartBoxHeader');
            $this->pnlHeader->Text = Quasi::Translate('Shopping Cart');
            if(IndexPage::$blnAjaxOk)
                $this->pnlHeader->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pnlHeader_Click'));
            else
                $this->pnlHeader->AddAction(new QClickEvent(), new QServerControlAction($this, 'pnlHeader_Click'));
            
            $this->pnlItemList = new QPanel($this, 'CartBoxList');
            $this->pnlItemList->AutoRenderChildren = true;
            
            if($this->objAccount instanceof Account)
                $this->init();
        }

        protected function init()
        {
                $this->intItemCount = ShoppingCartItem::CountByShoppingCartId($this->objShoppingCart->Id);
                if($this->intItemCount > 0)
                    $this->CreateItemList();
                else
                    $this->pnlItemList->Text = Quasi::Translate('There are no items in your cart') . '.';
        }
        
        protected function CreateItemList()
        {
            foreach( $this->objShoppingCart->GetShoppingCartItemArray() as $objItem )
            {
                $objProduct = Product::Load($objItem->ProductId);
                $intQuantity = $objItem->Quantity;
                $pnlCartItem = new QPanel($this->pnlItemList);
                
                $href = __QUASI_SUBDIRECTORY__ . '/index.php/Products/' . $objProduct->Id ;
                $pnlCartItem->Text =  '<a href="' . $href . '">' . $objProduct->Model . " ({$intQuantity}) </a> ";
                $pnlCartItem->CssClass = 'ShoppingCartItem';

/* another way to do this
                $pnlCartItem->Text = $intQuantity . ' ' . $objProduct->Model;
                $pnlCartItem->ActionParameter = $objProduct->Id;
                $pnlCartItem->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'CartItem_Click'));
*/                
            }
        }
        public function RefreshCart()
        {
            if($this->objShoppingCart instanceof ShoppingCart)
            {
                $this->MarkAsModified();
                $this->RemoveChildControls(true);
                $this->init();
            }
        }
        /**
        * This function is unused - it responds to the example alternate link shown above, currently disabled.
        */
        public function CartItem_Click($strFormId, $strControlId, $intProductId)
        {
            Qapplication::Redirect( __QUASI_SUBDIRECTORY__ . '/index.php/Products/' . $intProductId );
        }

        /**
        * This function is unused - it responds to a hypothetical "Check Out" button .. currently disabled.
        */
        public function btnCheckOut_Click($strFormId, $strControlId, $strParameter)
        {
            Quasi::Redirect(__QUASI_SUBDIRECTORY__ . '/index.php/CheckOut');
        }

        /**
        * This function responds to a click on the cart box header, redirecting the user to the
        * full shopping cart view page
        */
        public function pnlHeader_Click($strFormId, $strControlId, $strParameter)
        {
            Quasi::Redirect(__QUASI_SUBDIRECTORY__ . '/index.php/ShoppingCart');
        }

        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Module':
                    return $this->objModule ;
                case 'Account':
                    return $this->objAccount ;
                case 'ShoppingCart':
                    return $this->objShoppingCart ;
                case 'ItemCount':
                    return $this->intItemCount ;
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
                case 'ShoppingCart':
                    try {
                        return ($this->objShoppingCart = QType::Cast($mixValue, 'ShoppingCart' ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                case 'ItemCount':
                    try {
                        return ($this->intItemCount = QType::Cast($mixValue, Qtype::Integer ));
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
