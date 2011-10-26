<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/
if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("PAYPALEXPRESSCHECKOUTACTION.CLASS.PHP")){
define("PAYPALEXPRESSCHECKOUTACTION.CLASS.PHP",1);

/**
* Class PayPalExpressCheckoutAction - PayPal  Express Checkout payment action
*
* This class provides an implementation of the PayPal Express Checkout System including the
* two points of entry required. It can be activated either as a selected payment method on the
* checkout review page or by a click on the "Check out with PayPal" button.
*
* Express Checkout consists of 2 phases:
*   1. A request is sent to the server to set up the transaction details and a "token" is returned.
*       This token is stored in _SESSION['PayPalToken'] to be used in the second phase
*        Order status is left at pending and the customer is redirected to PayPal with the token as
*       part of the redirect URL
*   2. PayPal returns the customer to the PayPalExpressReturn page - this page must exist in
*       database and have in it the PayPalExpressReturnModule. This module will use this action
*       again, calling getExpressCheckoutDetails to complete the transaction. The function will
*       DoExpressCheckoutPayment
*
* Note that this payment action requires the existance of the return and cancel pages to which
* the customer is returned from PayPal - and that these pages must contain a content block with
* the PayPalExpressReturnModule to complete the process. Completion will consist of everything
* normally done in PaymentModule in btnPurchase_Click.
*
*@todo
*   - finish stage two, sending confirmation email on approval and completing the order
* 
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id$
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
* @subpackage Classes
*/

    class PayPalExpressCheckoutAction extends PayPalNVPAction
    {
        
        /**
        * PayPalExpressCheckoutAction Constructor
        *
        * This sets various defaults specific to the Express Checkout process
        *
        *@todo
        *   - safeguard the return URLs - send session id? or, encode account id and order id ..
        *
        * @param Order objOrder - the Order to process
        */
        public function __construct(Order $objOrder)
        {
            try {
                parent::__construct($objOrder);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            //the order id may be redundant - we already send INVNUM, this may go away ..
            $this->aryRequestValues['RETURNURL'] = 'http://' . Quasi::$ServerName .  __QUASI_SUBDIRECTORY__ 
                                            . '/index.php/PayPalExpressReturn?orderid=' . $this->objOrder->Id;
            $this->aryRequestValues['CANCELURL'] = 'http://' . Quasi::$ServerName .  __QUASI_SUBDIRECTORY__ 
                                            . '/index.php/PayPalExpressCancel?orderid=' . $this->objOrder->Id;
            
            $this->strRedirectCgiUrl = '/cgi-bin/webscr?';
                         
            $this->strTemplateUri = __QUASI_CORE_TEMPLATES__ . '/PayPalExpressCheckoutAction.tpl.php';
        }
       /**
        * The createRequest functions are handled by get/setExpressCheckoutDetails
        * as they occur before and after a customer is redirected to PayPal and are
        * therefor two separate requests. 
        */        
        protected function createPOSTRequest(){}
        protected function createGETRequest(){}
        
        /**
        * Performs any preparation steps prior to submitting an actual payment.
        * We submit a call to the SetExpressCheckoutDetails API here and set up
        * the values for the transaction. If successful, aryResponseValues['TOKEN']
        * will contain the identifier for the transaction.
        *@return bool true on success  
        */        
        public function PreProcess()
        {            
            $this->aryRequestValues['METHOD'] = 'SetExpressCheckout';
            $this->setExpressCheckoutDetails();
            return ! $this->HasErrors;
        }
        /**
        * 
        *@return bool true on success
        */        
        public function Process()
        {
            return ! $this->HasErrors;
        }
        /**
        * This function simply checks the return from setExpressCheckoutDetails and then
        * redirects the user to PayPal's site to complete the payment
        * Updating order_status_history is also performed here.
        *@return bool true on success  
        */        
        public function PostProcess()
        {
            $strRedirectTarget = $this->strRedirectDomainName . $this->strRedirectCgiUrl
                            . 'cmd=_express-checkout&token=' . $this->objPaypalTransaction->PpToken;
            Quasi::Redirect($strRedirectTarget );
        }
        
        /**
        * Creates POST string for the first half of the PayPal transaction, setting the checkout details
        * and storing the result in strPOSTRequest. Then a query is sent to NVP API server and the PayPal
        * "token" is recieved which will be used by getExpressCheckoutDetails to validate the payment.
        */        
        protected function setExpressCheckoutDetails()
        {
            $this->aryRequestValues['METHOD'] = 'SetExpressCheckout';

            //optionally send a shipping address to display ..
            if( $this->ShowShippingAddress )
                $this->initShippingDetails();
            else
                $this->aryRequestValues['NOSHIPPING'] = 1;

            $this->aryRequestValues['AMT'] = $this->fltTotalPrice;
            $this->aryRequestValues['ITEMAMT'] = $this->objOrder->ProductTotalCharged;
            $this->aryRequestValues['INVNUM'] = $this->objOrder->Id;
            $this->aryRequestValues['SHIPPINGAMT'] = $this->objOrder->ShippingCharged;
            $this->aryRequestValues['HANDLINGAMT'] = $this->objOrder->HandlingCharged;
            $this->aryRequestValues['TAXAMT'] = $this->objOrder->Tax;
            //truncated to fit PP specs in case of long store names ..
            $this->aryRequestValues['DESC'] = urlencode(substr(STORE_NAME . ' Order ' . $this->objOrder->Id, 0, 127));
            
            foreach( $this->aryRequestValues as $strName => $strValue )
                if('' != $strValue )
                    $this->strPOSTRequest .= '&' . $strName . '=' . $strValue;
            $this->submitRequest();
            
            $this->handleResponse();
/*            if( ! $this->submitRequest())
                throw new Exception('PayPal EC submit failed: ' . $this->strPOSTRequest);
            
            if( ! $this->handleResponse())
                throw new Exception('PayPal EC submit failed: ' . $this->strErrors
                                                 . ' <br /><br /> Response: ' . $this->strResponse);*/
        }
        /**
        * This function is called by PayPalExpressReturnModule when the buyer returns from PayPal.
        *Here we determine the payer id and status in preparation for completing the order ..
        */
        public function getExpressCheckoutDetails()
        {
            $this->strPOSTRequest = '';
            $this->aryRequestValues['METHOD'] = 'GetExpressCheckoutDetails';
            $this->aryRequestValues['TOKEN'] = $_GET['token'];
            // also adds default settings ..
            foreach( $this->aryRequestValues as $strName => $strValue )
                if('' != $strValue )
                    $this->strPOSTRequest .= '&' . $strName . '=' . $strValue;

            $this->submitRequest();
            $this->handleResponse();
            if( $this->HasErrors )
                return false;
            if( '' == $this->objPaypalTransaction->PayerId )
            {
                $this->HasErrors = true;
                $this->objPaypalTransaction->Messages .= '| Quasi PP Express: No PayerId returned! |';
                $this->objPaypalTransaction->Save();
                return false;
            }
            return true;
        }
        /**
        * This function overrides the PaymentActionBase completion - it is called by PayPalExpressReturnModule
        * when the buyer returns from PayPal.
        *@todo handle PENDINGREASON, REASONCODE, PAYMENTTYPE != INSTANT ..
        */
        public function doExpressCheckoutPayment()
        {
            $this->strPOSTRequest = '';
            $this->aryRequestValues['METHOD'] = 'DoExpressCheckoutPayment';
            $this->aryRequestValues['TOKEN'] = $this->objPaypalTransaction->PpToken;
            $this->aryRequestValues['PAYERID'] = $this->objPaypalTransaction->PayerId;
            $this->aryRequestValues['INVNUM'] = $this->objOrder->Id;
            $this->aryRequestValues['AMT'] = $this->fltTotalPrice;
            $this->aryRequestValues['ITEMAMT'] = $this->objOrder->ProductTotalCharged;
            $this->aryRequestValues['SHIPPINGAMT'] = $this->objOrder->ShippingCharged;
            $this->aryRequestValues['HANDLINGAMT'] = $this->objOrder->HandlingCharged;
            $this->aryRequestValues['TAXAMT'] = $this->objOrder->Tax;
            // also adds default settings ..
            foreach( $this->aryRequestValues as $strName => $strValue )
                if('' != $strValue )
                    $this->strPOSTRequest .= '&' . $strName . '=' . $strValue;

//            die($this->strPOSTRequest);
            
            $this->submitRequest();
            $this->handleResponse();
            if( $this->HasErrors )
                return false;
                
            // check for the payment status
            $strStatus = $this->objPaypalTransaction->PaymentStatus;
            if('' == $strStatus )
            {
                $this->HasErrors = true;
                $this->objPaypalTransaction->Messages .= '| Quasi PP Express: No Payment status returned! |';
                $this->objPaypalTransaction->Save();
                return false;
            }

///@todo handle PENDINGREASON, REASONCODE, PAYMENTTYPE != INSTANT ..
            switch( strtoupper($strStatus) )
            {
                case 'COMPLETED':
                    $this->blnApproved = true;
                    break;
                case 'PENDING':
                default:
                    $this->blnApproved = false;                    
            }
        }
        protected function initShippingDetails()
        {
            $this->aryRequestValues['NOSHIPPING'] = 0;
            $this->aryRequestValues['ADDRESSOVERRIDE'] = 1;
            
            //We must concatenate all the name fields into NAME ..
            if( '' != $this->objOrder->ShippingNamePrefix )
                $this->aryRequestValues['SHIPTONAME'] = urlencode($this->objOrder->ShippingNamePrefix . ' ');
            $this->aryRequestValues['SHIPTONAME'] .= urlencode($this->objOrder->ShippingFirstName);

            if( '' != $this->objOrder->ShippingMiddleName )
                $this->aryRequestValues['SHIPTONAME'] .= urlencode(' ' . $this->objOrder->ShippingMiddleName);
            $this->aryRequestValues['SHIPTONAME'] .= urlencode(' ' . $this->objOrder->ShippingLastName);

            if( '' != $this->objOrder->ShippingNameSuffix )
                $this->aryRequestValues['SHIPTONAME'] .= urlencode(' ' . $this->objOrder->ShippingNameSuffix);

            $this->aryRequestValues['SHIPTOSTREET'] = urlencode($this->objOrder->ShippingStreet1);

            if( '' != $this->objOrder->ShippingStreet2 )
                $this->aryRequestValues['SHIPTOSTREET2'] = urlencode($this->objOrder->ShippingStreet2);
            //PayPal offers no field for County or Suburb so we must put them in street2 ..
            if( '' != $this->objOrder->ShippingSuburb )
                $this->aryRequestValues['SHIPTOSTREET2'] .= urlencode(', ' . $this->objOrder->ShippingSuburb);
            if( '' != $this->objOrder->ShippingCounty )
                $this->aryRequestValues['SHIPTOSTREET2'] .= urlencode(', ' . $this->objOrder->ShippingCounty);

            $this->aryRequestValues['SHIPTOCITY'] = urlencode($this->objOrder->ShippingCity);
            $this->aryRequestValues['SHIPTOSTATE'] = urlencode($this->objOrder->ShippingState);
            $this->aryRequestValues['SHIPTOCOUNTRY'] = urlencode($this->objOrder->ShippingCountry);
            $this->aryRequestValues['SHIPTOZIP'] = urlencode($this->objOrder->ShippingPostalCode);
        }
        /**
        * Creates GET query string for the second half of the PayPal transaction. This function is called
        * by the PayPalExpressReturnModule only, which is effectively the return URL given to PayPal
        * in the first half of the transaction to which the user is redirected after paying. The "token"
        */
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'ShowShippingAddress':
                    return $this->blnShowShippingAddress;
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
                case 'ShowShippingAddress':
                    try {
                        $this->blnShowShippingAddress = QType::Cast($mixValue, QType::Boolean );
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                    //careful, its backwards ..
                    $this->aryRequestValues['NOSHIPPING'] = (true === $mixValue) ? 0 : 1;
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