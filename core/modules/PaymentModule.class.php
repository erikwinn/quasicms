<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("PAYMENTMODULE.CLASS.PHP")){
define("PAYMENTMODULE.CLASS.PHP",1);

/**
* Class PaymentModule - a module to manage PaymentMethods
*
* This class iterates through the list of PaymentMethods in the payment_methods table
* and for each that is flagged active it creates a QRadioButton to be rendered in the template.
* When the button is clicked, it sets objSelectedMethod to the selected payment method.
* When the 'Purchase' button is clicked the btn_Purchase function is called; this calls three
* functions that a PaymentAction is required to implement:
*   - PreProcess: perform any actions to set up the transaction
*   - Process: perform the actual transaction, ie. connect to server and make a request in most cases.
*   - PostProcess: perform any validation checks and update the order_status_history table in most cases.
*
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: PaymentModule.class.php 500 2009-02-06 21:44:47Z erikwinn $
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


 class PaymentModule extends QPanel
 {
        
        /**
        *@var CheckOutModule a reference to the control block - expected to be CheckOutModule
        */
        protected $objControlBlock;
        /**
        *@var Order a reference to the current order
        */
        protected $objOrder;
        /**
        *@var Address a reference to the current order
        */
        protected $objAddress;
        /**
        *@var array PaymentMethods flagged active in database
        */
        protected $aryPaymentMethods;
        /**
        *@var PaymentMethod a reference to the method chosen by the user (or the default)
        */
        protected $objSelectedMethod;
        /**
        *@var PaymentAction the action for the selected payment method
        */
        protected $objPaymentAction=null;
        /**
        *@var string strDefaultServiceProvider - the default payment gateway
        */
        protected $strDefaultServiceProvider = DEFAULT_PAYMENT_PROVIDER;
        /**
        *@var string strDefaultServiceType - the default payment type for the default service
        */
        protected $strDefaultServiceType = DEFAULT_PAYMENT_SERVICE;

        /**
        *@var array aryYears - years for CC expiration selection 
        */
        protected $aryYears = array(
            '2008',
            '2009',
            '2010',
            '2011',
            '2012',
            '2013',
            '2014',
            '2015',
            '2016',
            '2017',
            '2018',
        );
        /**
        *@var array aryMonths - months for CC expiration selection
        */
        protected $aryMonths = array(
            '01',
            '02',
            '03',
            '04',
            '05',
            '06',
            '07',
            '08',
            '09',
            '10',
            '11',
            '12',
        );
        
        protected $strErrors;
                
        protected $fltPaymentTotal;
        
        protected $blnHasActiveMethods = true;
        
        /**
        *@var bool blnShowCCInput - if true display Credit card input fields
        */
        protected $blnShowCCInput = false;        
        /**
        *@var integer intPreviousAddressId - the id of the address before a selection signal.
        */
        protected $intPreviousAddressId;

        /// Display objects ..
        /**
        *@var array PaymentMethodViews for the selection of PaymentMethods
        */
        public $aryPaymentMethodViews;
        /**
        *@var QButton Button to complete the purchase - triggers a payment action
        */
        public $btnPurchase;
        /**
        * @todo  subclass for validation ..QCCTextBox?
        *@var QTextBox
        */
        public $txtCCNumber;
        /**
        *@var QListBox
        */
        public $lstCCExpirationYear;
        /**
        *@var QListBox
        */
        public $lstCCExpirationMonth;
        /**
        * @todo  implement CCV Check ..
        *@var QTextBox
        */
        public $txtCCVNumber;
        /**
        *@var AddressSelectionModule objAddressSelectionModule - handles selecting the billing address
        */
        public $objAddressSelectionModule;
        
        /**
        * Module constructor
        *@param ContentBlock - parent controller object.
        *@param Order - the order for which we are paying.
        */
        public function __construct(QPanel $objControlBlock, $objOrder )
        {
            
            try {
                parent::__construct($objControlBlock);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->objOrder =& $objOrder;
            $this->objControlBlock =& $objControlBlock;
            
            $this->AutoRenderChildren = true;
            
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/PaymentModule.tpl.php';
            
            $this->aryPaymentMethods = PaymentMethod::QueryArray(
                                                                QQ::Equal(QQN::PaymentMethod()->Active, true),
                                                                QQ::OrderBy(QQN::PaymentMethod()->SortOrder, false)
                                                                );
            
            if(!empty( $this->aryPaymentMethods ))
            {            
                $this->objAddressSelectionModule = new AddressSelectionModule($this,
                                                                                                                         'SelectAddress',
                                                                                                                         $this->objOrder->BillingAddressId
                                                                                                                         );
                $this->objAddress = $this->objAddressSelectionModule->Address;
                $this->createInputOptions();
            }
            else
                $this->blnHasActiveMethods = false;
                            
        }
        /**
        *This creates the method views for the available payment methods and the input fields for
        * credit card numbers (if needed)
        */
        protected function createInputOptions()
        {
            $this->blnShowCCInput = false;
            
            foreach($this->aryPaymentMethods as &$objPaymentMethod)
            {
                
                $objPaymentMethodView = new PaymentMethodView($this, $objPaymentMethod);
                
                if( $objPaymentMethod->ServiceProvider == $this->DefaultServiceProvider
                    && $objPaymentMethod->ServiceType == $this->DefaultServiceType )
                {
                    $objPaymentMethodView->Checked = true;
                    $this->objSelectedMethod =& $objPaymentMethod;
                    $this->objOrder->PaymentMethodId = $objPaymentMethod->Id;
                }

                if( $objPaymentMethod->RequiresCcNumber )
                    $this->blnShowCCInput = true;
                    
                $this->aryPaymentMethodViews[] = $objPaymentMethodView;
            }
            
            //credit card input fields
            if($this->blnShowCCInput)
            {
                $this->txtCCNumber = new QTextBox($this);
                $this->txtCCNumber->CssClass = 'CCNumber';
                $this->txtCCNumber->Required = true;
                $this->txtCCNumber->Name = Quasi::Translate('Card Number') . ': ';
                $this->txtCCNumber->Text = '';
                $this->txtCCVNumber = new QTextBox($this);
                $this->txtCCVNumber->CssClass = 'CCVNumber';
                $this->txtCCVNumber->Required = true;
                $this->txtCCVNumber->Name = Quasi::Translate('CCV Number') . ': ';
                
                $this->lstCCExpirationYear = new QListBox($this);
                foreach($this->aryYears as $strYear)
                    $this->lstCCExpirationYear->AddItem($strYear, $strYear);
                $this->lstCCExpirationYear->Required = true;
                
                $this->lstCCExpirationMonth = new QListBox($this);
                foreach($this->aryMonths as $strMonth)
                    $this->lstCCExpirationMonth->AddItem($strMonth,$strMonth);
                $this->lstCCExpirationMonth->Required = true;
            }
        }
        /**
        * Sets the selected payment method, called when the user selects/changes a payment
        * method radio button.
        */
        public function SelectMethod($intMethodId)
        {
            foreach($this->aryPaymentMethods as &$objMethod )
                if($intMethodId == $objMethod->Id)
                {
                    $this->objSelectedMethod =& $objMethod;
                    $this->objOrder->PaymentMethodId = $objMethod->Id;
                    break;
                }
        }
        
        public function SelectAddress($intAddressId, $strParameter=null)
        {
            if( is_numeric($intAddressId) )
            {
                $this->intPreviousAddressId = $intAddressId;            
                $this->objOrder->SetBillingAddress($this->objAddressSelectionModule->Address);
                $this->objAddress = $this->objAddressSelectionModule->Address;
            }

            $this->objAddressSelectionModule->RemoveChildControls(true);
            $this->RemoveChildControl($this->objAddressSelectionModule->ControlId, false);
            
            if( 'Edit' == $strParameter )
                $this->objAddressSelectionModule = new AddressSelectionModule($this, 'SelectAddress', $intAddressId, true);
            elseif( 'New' == $strParameter )
                $this->objAddressSelectionModule = new AddressSelectionModule($this, 'SelectAddress', null, true);
            else//Note: includes Save and Cancel ..
            {
                if($intAddressId)
                    $this->objAddressSelectionModule = new AddressSelectionModule($this, 'SelectAddress', $intAddressId);
                else
                    $this->objAddressSelectionModule = new AddressSelectionModule($this, 'SelectAddress', $this->intPreviousAddressId);
            }
            
            $this->objAddressSelectionModule->Visible = true;
            $this->AddChildControl($this->objAddressSelectionModule);
                                    
        }

        public function Validate(){ return true;}
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'SelectedMethod':
                    return $this->objSelectedMethod ;
                case 'Address':
                    return $this->objAddress ;
                case 'HasActiveMethods':
                    return $this->blnHasActiveMethods ;
                case 'ShowCCInput':
                    return $this->blnShowCCInput ;
                case 'DefaultServiceProvider':
                    return $this->strDefaultServiceProvider ;
                case 'DefaultServiceType':
                    return $this->strDefaultServiceType ;
                case 'Errors':
                    return $this->strErrors ;
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
                case 'SelectedMethod':
                    try {
                        return ($this->objSelectedMethod = QType::Cast($mixValue, 'PaymentMethod' ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'DefaultServiceProvider':
                    try {
                        return ($this->strDefaultServiceProvider = QType::Cast($mixValue, Qtype::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'DefaultServiceType':
                    try {
                        return ($this->strDefaultServiceType = QType::Cast($mixValue, Qtype::String ));
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