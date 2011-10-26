<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/
if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("PAYPALNVPACTION.CLASS.PHP")){
define("PAYPALNVPACTION.CLASS.PHP",1);

/**
* Class PayPalNVPAction - PayPal  NVP API  action
*
* This class provides an interface to the PayPal NVP API.
*
* @todo - meaningful comments .. we do a bunch of stuff here:
*  connect, redirect, handle returns, save transaction ..etc.
*   
* 
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: PayPalNVPAction.class.php 451 2008-12-22 21:47:41Z erikwinn $
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

    class PayPalNVPAction extends PaymentActionBase
    {
        /**
        * @var array Name - Value pairs with which to construct GET query strings
        */
        protected $aryRequestValues = array(
        'USER' => '',
        'PWD' => '',
        'PAYERID' => '',
        'VERSION' => PAYPAL_NVP_VERSION,
        'PAYMENTACTION' => 'Sale', //Sale | Autorization | Order
        'METHOD' => '', //API method - REQUIRED
        'TOKEN' => '',     //transaction token, returned in response - OPTIONAL/required with get/doExpressCheckout ..
        'AMT' => '',           // total purchase amount, including tax and shipping - REQUIRED
        'CURRENCYCODE' => 'USD', //AUD, CAD, CHF, CZK, DKK, EUR, GBP, HKD, HUF, JPY, NOK, NZD, PLN, SEK, SGD
        'RETURNURL' => '', //URL to which customer is returned after paying - REQUIRED
        'CANCELURL' => '', //URL to which customer is returned if they cancel - REQUIRED
        'NOTIFYURL' => '', //URL for receiving Instant Payment Notification - optional
        'IPADDRESS' => '', // Local ServerName 
        'MAXAMT' => '', // The expected maximum total amount of the complete order - OPTIONAL
        'DESC' => '', // Description of purchase - OPTIONAL
        'CUSTOM' => '', // Custom data, whatever you like, returned by GetExpressCheckoutDetails - OPTIONAL
        'INVNUM' => '', // Invoice or Order number returned by DoExpressCheckoutPayment - OPTIONAL
        'REQCONFIRMSHIPPING' => '', //Require PayPal to confirm customers address (filed at PayPal) - OPTIONAL
        'NOSHIPPING' => '', //If true (1), PayPal is to display no shipping address info - OPTIONAL
        'ALLOWNOTE' => '', //If true (1) user can add a note returned by GetExpressCheckoutDetails - OPTIONAL
        'ADDRESSOVERRIDE' => '', //If true (1), PayPal displays address sent with request - OPTIONAL
        'LOCALECODE' => '', //Display PayPal pages using this locale (AU, FR, DE, GB, IT, ES, US) -  OPTIONAL
        'PAGESTYLE' => '', //page style from the Profile subtab of the My Account tab - OPTIONAL
        'HDRIMG' => '', // (https) URL for the image to appear at the top left of the payment page. - OPTIONAL
        'HDRBORDERCOLOR' => '', //Sets the border color around the header of the payment page.- OPTIONAL
        'HDRBACKCOLOR' => '', // Sets the background color for the header of the payment page - OPTIONAL
        'PAYFLOWCOLOR' => '', // Sets the background color for the payment page - OPTIONAL
        'EMAIL' => '', // Email address of the buyer to prefill field at PayPal - OPTIONAL
        'LANDINGPAGE' => '', //Type of PayPal page to display ("Billing" for non-PayPal account else "Login") - OPTIONAL
        'SHIPTONAME' => '', // (Customer name associated with shipping address - REQUIRED
        'SHIPTOSTREET' => '', //First street address - REQUIRED
        'SHIPTOSTREET2' => '', //Second street address - OPTIONAL
        'SHIPTOCITY' => '', // Name of city. -REQUIRED
        'SHIPTOSTATE' => '', // Name of state. -REQUIRED
        'SHIPTOZIP' => '', // Postal code. -REQUIRED
        'SHIPTOCOUNTRY' => '', // Country. -REQUIRED
        'ITEMAMT' => '', //Sum of cost of all items in this order, REQUIRED if you use L_AMTn or shipping etc. ...
        'SHIPPINGAMT' => '', //Total shipping costs for this order - optional.
        'HANDLINGAMT' => '', //Total handling costs for this order - optional
        'TAXAMT' => '', //Sum of tax for all items in this order - optional
        );

        /**
        * 
        * @var array Response values will be stored here 
        */
        protected $aryResponseValues;
        /**
        * PayPal transaction ORM object for logging transactions ..
        * @var PaypalTransaction - represents the paypal_transaction table ..
        */
        protected $objPaypalTransaction;
        
        /**
        * The following are base strings for constructing multiple item requests, eg. L_NAME0, L_NAME1, etc...
        * All are optional.
        */
        protected $strItemName = 'L_NAME';
        protected $strItemDescBase = 'L_DESC';
        protected $strItemAmountBase = 'L_AMT';
        protected $strItemQuantityBase = 'L_QTY';
        protected $strItemNumberBase = 'L_NUMBER';
        protected $strItemTaxBase = 'L_TAXAMT';

        protected $blnShowShippingAddress = false;
        
        /**
        * PayPalNVPAction Constructor
        *
        * This sets various defaults specific to the NVP API
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
            
            $this->blnTestMode = $objOrder->PaymentMethod->TestMode;
            if($this->TestMode)
            {
                $this->strRedirectDomainName = PAYPAL_REDIRECT_TESTURL;
                $this->aryRequestValues['USER'] = PAYPAL_NVP_TESTUSERNAME;
                $this->aryRequestValues['PWD'] = PAYPAL_NVP_TESTPASSWORD;
                if('' != PAYPAL_NVP_TESTSIGNATURE)
                {
                    $this->aryRequestValues['SIGNATURE'] = PAYPAL_NVP_TESTSIGNATURE;
                    $this->strRemoteDomainName = PAYPAL_NVP_TESTURL;
                }
                else
                {
                    $this->UseCurl = true;
                    $this->UseSslCertificate = true;
                    $this->strRemoteDomainName = PAYPAL_NVP_CURL_TESTURL;
                    $this->strSslCertificateUri = PAYPAL_CERT_TESTPATH;
                }
            }
            else
            /// FIXME: put these somewhere safer and load it .. currently in config file!
            {
                $this->strRedirectDomainName = PAYPAL_REDIRECT_URL;
                $this->aryRequestValues['USER'] = PAYPAL_NVP_USERNAME;
                $this->aryRequestValues['PWD'] = PAYPAL_NVP_PASSWORD;
                if('' != PAYPAL_NVP_TESTSIGNATURE)
                {
                    $this->aryRequestValues['SIGNATURE'] = PAYPAL_NVP_SIGNATURE;
                    $this->strRemoteDomainName = PAYPAL_NVP_URL;
                }
                else
                {
                    $this->UseCurl = true;
                    $this->UseSslCertificate = true;
                    $this->strRemoteDomainName = PAYPAL_NVP_CURL_URL;
                    $this->strSslCertificateUri = PAYPAL_CERT_PATH;
                }
            }
            
            $this->strRemoteCgiUrl = '/nvp';
            $this->strRequestType = 'POST';

            $this->aryRequestValues['IPADDRESS'] = Quasi::$ServerName;
            
            //unused ..
            $this->strTemplateUri = __QUASI_CORE_TEMPLATES__ . '/PayPalNVPAction.tpl.php';
        }
                
       /**
        * The createRequest functions are handled by separate functions which create requests
        * by stage as they may occur before and after a customer is redirected to PayPal and may
        * therefor be two separate requests. 
        */        
        protected function createPOSTRequest(){}
        protected function createGETRequest(){}
        
        /**
        * Performs any preparation steps prior to submitting an actual payment.
        * Eg. We submit a call to the SetExpressCheckoutDetails API here and set up
        * the values for the transaction. If successful, aryResponseValues['TOKEN']
        * will contain the identifier for the transaction.
        *@return bool true on success  
        */        
        public function PreProcess(){}
        /**
        *@return bool true on success
        */        
        public function Process(){}
        /**
        * Performs any steps necessary after submitting an actual payment. For example
        * the PayPal Express Checkout redirects the user here ..
        *@return bool true on success  
        */        
        public function PostProcess(){}
        /**
        * Parses the direct API response from PayPal into an array of values. This also inserts an entry
        * into the paypal_transaction table and initializes the PaypalTransaction object to which other
        * functions may refer for information returned concerning the transaction.
        *
        *@todo
        *   - handle errors gracefully !!
        *   - L_ERRORCODE0=81100&L_SHORTMESSAGE0=Missing%20Parameter&L_LONGMESSAGE0
        *       errornumber: 10415 - transaction already completed for token
        *  
        *   - optionally save address values from PP and use address confirmation ... this ain't gonna be
        *     soon since it requires a whole reworking of the scheme to put shipping options after redirect
        *     and adding the shipping charge.. ick, i have pp slime on my keyboard ..
        *@return boolean true if the response was successfully parsed.
        */        
        protected function handleResponse()
        {
            $this->aryResponseValues = array();
            $strResponseRaw = $this->strResponse;
            
            $this->strResponse = urldecode($this->strResponse);
            $pos = strpos($this->strResponse, "TOKEN=" );
            if( false === $pos )
            {
                $this->HasErrors = true;
                return false;
            }
            $this->strResponse = substr( $this->strResponse, $pos);
            
            //split up the string and store the values in a map ..
            $aryTokens = explode('&', $this->strResponse );
            foreach($aryTokens as $strToken)
            {
                $aryTemp = explode('=', $strToken);
                $this->aryResponseValues[$aryTemp[0]] = $aryTemp[1];
            }                
            if( empty($this->aryResponseValues) )
            {
                $this->HasErrors = true;
                $this->strErrors .= 'Response: ' . $strResponseRaw;
                return false;
            }
            
            //initialize a transaction logging object ..
            $this->objPaypalTransaction = new PaypalTransaction();
            $this->objPaypalTransaction->OrderId = $this->objOrder->Id;
            $this->objPaypalTransaction->PaymentMethodId = $this->objOrder->PaymentMethodId;
            $this->objPaypalTransaction->ApiAction = $this->aryRequestValues['METHOD'];
            $this->objPaypalTransaction->ApiVersion = $this->aryResponseValues['VERSION'];
            $this->objPaypalTransaction->CorrelationId = $this->aryResponseValues['CORRELATIONID'];
            $this->objPaypalTransaction->AckReturned = $this->aryResponseValues['ACK'];
            //clean up the timestamp .. note: the settor converts this to a QDateTime 
            $strDateTime = str_replace('T',' ', $this->aryResponseValues['TIMESTAMP']);
            $this->objPaypalTransaction->TimeStamp = $strDateTime;
            
            $this->checkAckReturned();
            
            if($this->HasErrors)
            {
                foreach($this->aryResponseValues as $strName => $strValue)
                    if( false !== strpos( $strName, 'L_ERRORCODE') || false !== strpos( $strName, 'MESSAGE') )
                        $this->strErrors .= '<br />' . $strName . ': ' . $strValue;
                
                $this->objPaypalTransaction->Messages = $this->strErrors;
                $this->objPaypalTransaction->Save();
                return false;
            }
                        
            //server transaction ok, finish with the payment ..
            switch(strtoupper($this->objPaypalTransaction->ApiAction))
            {
                case 'DOEXPRESSCHECKOUTPAYMENT':
                    $this->objPaypalTransaction->PaymentStatus = $this->aryResponseValues['PAYMENTSTATUS'];
                    $this->objPaypalTransaction->PpToken = $this->aryResponseValues['TOKEN'];
                    break;
                case 'GETEXPRESSCHECKOUTDETAILS':
                    $this->objPaypalTransaction->PayerId = $this->aryResponseValues['PAYERID'];
                    $this->objPaypalTransaction->PayerStatus = $this->aryResponseValues['PAYERSTATUS'];
                case 'SETEXPRESSCHECKOUT':
                    $this->objPaypalTransaction->PpToken = $this->aryResponseValues['TOKEN'];
                    break;
                default:
                    //unsupported method ..
            }            
            $this->objPaypalTransaction->Save();
            return true;
        }
        protected function checkAckReturned()
        {
            $strAck =$this->objPaypalTransaction->AckReturned ;
            if( '' == $strAck)
                $this->HasErrors = true;
            else
            {
                switch( strtoupper( $strAck ))
                {
                    case 'SUCCESS':
                    case 'SUCCESSWITHWARNING':
                        $this->HasErrors = false;
                        break;
                    case 'FAILURE':
                    case 'FAILUREWITHWARNING':
                        $this->HasErrors = true;
                        break;
                    default:
                        //error ..                    
                        $this->HasErrors = true;
                }
            }        
            return ! $this->HasErrors;
        }
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'PaypalTransaction':
                    return $this->objPaypalTransaction;
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