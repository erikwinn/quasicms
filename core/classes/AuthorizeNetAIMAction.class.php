<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/
if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("AUTHORIZENETAIMACTION.CLASS.PHP")){
define("AUTHORIZENETAIMACTION.CLASS.PHP",1);

/**
* Class AuthorizeNetAIMAction - Authorize.net AIM payment action
*
* This class provides an interface to the Authorize.net AIM API. It sends credit card and
* order information (via SSL) to the API server and handles the response. It will set
* Approved = true on success and store any message returned. If the transaction is
* not approved the order will be deleted and error messages are available for display.
* Additionally, the error code and error reason code will be stored. If the transaction is
* approved the order status will be updated, order_status_history and order_totals will
* be inserted and a confirmation email sent to the customer in the base class.
*
*@todo
*   - deal with "Transaction Id"? Might log them like PayPal ..
*   - handle errors better, messages - eg. what failed ..
*   - implement address verification check - ie. add ship_to_* fields and handle response
*   - add optional customer email notification (from authorize)?
*   - add "Send Shipping address .." option?
* 
* 
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: AuthorizeNetAIMAction.class.php 458 2008-12-23 20:12:46Z erikwinn $
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

    class AuthorizeNetAIMAction extends PaymentActionBase
    {
        /**
        * @var array Name - Value pairs with which to construct AIM POST string
        */
        protected $aryRequestValues = array(
            'x_login'                => '',
            'x_tran_key'          => '',
            'x_version'             => '3.1',
            'x_delim_char'       => '|',
            'x_delim_data'       => 'TRUE',
            'x_url'                   => 'FALSE',
            'x_type'                 => 'AUTH_CAPTURE',
            'x_method'            => 'CC',
            'x_relay_response' => 'FALSE',
            'x_card_num'        => '4242424242424242',
            'x_exp_date'          => '1209',
            'x_card_code'        => '', //CCV no.
            'x_description'      => 'Recycled Toner Cartridges',
            'x_amount'           => '12.23',
            'x_first_name'       => 'Charles D.',
            'x_last_name'        => 'Gaulle',
            'x_address'            => '342 N. Main Street #150',
            'x_city'                  => 'Ft. Worth',
            'x_state'                => 'TX',
            'x_country'           => 'USA',
            'x_zip'                   => '12345',
            'x_email'                   => 'FALSE',
            'x_email_customer'   => '', //TRUE | FALSE
            'x_email_header'       => '',
            'x_email_footer'         => '',
            'x_cust_id'                   => '',
            'x_customer_ip'           => '',
            'x_invoice_num'    => '',
        );
        
        /**
        * 
        * @var array Response values will be stored here 
        */
        protected $aryResponseValues;
        
        protected $intResponseCode ;
        protected $intResponseReasonCode ;
        protected $strResponseReasonText ;
        protected $strAVSResponse ;
//        protected $strInvoiceNumber ;

        protected $blnSendShippingAddress = false;
        
        /**
        * AuthorizeNetAIMAction Constructor
        * This sets various defaults specific to the Authorize.net API
        *
        * @param Order objOrder - the Order to process
        */
        public function __construct(Order $objOrder)
        {
            //Note: fixme - i don't think we will get a QCallerException here ..
            try {
                parent::__construct($objOrder);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->blnTestMode = $objOrder->PaymentMethod->TestMode;
            if($this->blnTestMode)
            {
                $this->strRemoteDomainName = AUTHORIZENET_AIM_TESTURL;
                $this->strRemoteAccountId = AUTHORIZENET_AIM_TESTUSERNAME;
                $this->strTransactionKey = AUTHORIZENET_AIM_TESTTRANSACTIONKEY;
            }
            else
            {
                $this->strRemoteDomainName = AUTHORIZENET_AIM_URL;
                /// FIXME: put these somewhere safer and load it .. currently in config file!
                $this->strRemoteAccountId = AUTHORIZENET_AIM_USERNAME;
                $this->strTransactionKey = AUTHORIZENET_AIM_TRANSACTIONKEY;
            }
            
            $this->strRemoteCgiUrl = '/gateway/transact.dll';
            $this->strRequestType = 'POST';
             
            $this->strTemplateUri = __QUASI_CORE_TEMPLATES__ . '/AuthorizeNetAIMAction.tpl.php';            
        }
        
        /**
        * Performs any preparation steps prior to submitting an actual payment.
        *@return bool true on success
        */        
        public function PreProcess()
        {
            $this->createRequest();
             return ! $this->HasErrors;
        }
        /**
        * Performs the actual payment submission
        *@return bool true on success
        */        
        public function Process()
        {
            $this->submitRequest();
            return ! $this->HasErrors;
        }
        /**
        * Performs any steps necessary after submitting an actual payment.
        * Updating order_status_history, order totals and confirmation email
        * is also called here on approval (these actions actually performed in
        * completeOrder ..)
        *@return bool true on success  
        */        
        public function PostProcess()
        {
            $this->handleResponse();
            if($this->blnApproved)
                $this->completeOrder();
            return ! $this->HasErrors;
        }
        /**
        * Parses the response from the payment service provider into an array
        * for convenience. It also sets the Response codes, messages, and blnApproved.
        * On failure or error messages will be in strErrors.
        */        
        protected function handleResponse()
        {
            $objAuthNetTransaction = new AuthorizeNetTransaction();
            $objAuthNetTransaction->OrderId = $this->objOrder->Id;
            //default to an error ..
            $objAuthNetTransaction->ResponseCode = 3;
            $objAuthNetTransaction->ResponseReasonCode = 555;
            $objAuthNetTransaction->ResponseReasonText = 'Unknown Server Error';
            
            $this->aryResponseValues = array();
            $pos = strpos( $this->strResponse,'|' );
            if(false === $pos)
            {
                $this->intResponseCode = 3;
                $this->intResponseReasonCode = 555;
            }
            else
            {
                //remove everything except the integer at the end just before the pipe (the "ResponseCode")..
                $strTemp = substr($this->strResponse, $pos - 1);
                $this->aryResponseValues = explode('|', $strTemp );
                $objAuthNetTransaction->TransactionId = $this->aryResponseValues[ AuthorizeNetTransaction::TransactionIdIdx ];
                $objAuthNetTransaction->TransactionType = $this->aryResponseValues[ AuthorizeNetTransaction::TransactionTypeIdx ];
                $objAuthNetTransaction->ResponseCode = $this->aryResponseValues[ AuthorizeNetTransaction::ResponseCodeIdx ];
                $objAuthNetTransaction->ResponseSubcode = $this->aryResponseValues[ AuthorizeNetTransaction::ResponseSubcodeIdx ];
                $objAuthNetTransaction->ResponseReasonCode = $this->aryResponseValues[ AuthorizeNetTransaction::ResponseReasonCodeIdx ];
                $objAuthNetTransaction->ResponseReasonText = $this->aryResponseValues[ AuthorizeNetTransaction::ResponseReasonTextIdx ];
                $objAuthNetTransaction->AuthorizationCode = $this->aryResponseValues[ AuthorizeNetTransaction::AuthorizationCodeIdx ];
                $objAuthNetTransaction->AvsResponseCode = $this->aryResponseValues[ AuthorizeNetTransaction::AVSResponseIdx ];
                $objAuthNetTransaction->CcvResponseCode = $this->aryResponseValues[ AuthorizeNetTransaction::CCVResponseIdx ];
                $objAuthNetTransaction->CavResponseCode = $this->aryResponseValues[ AuthorizeNetTransaction::CAVResponseIdx ];
                $objAuthNetTransaction->Amount = $this->aryResponseValues[ AuthorizeNetTransaction::AmountIdx ];
                
                $this->intResponseCode = $objAuthNetTransaction->ResponseCode;
                $this->intResponseReasonCode = $objAuthNetTransaction->ResponseReasonCode;
                $this->strResponseReasonText = $objAuthNetTransaction->ResponseReasonText;
                $this->strAVSResponse = $objAuthNetTransaction->AvsResponseCode;                
            }
            
            $objAuthNetTransaction->Save();
            
            switch($this->intResponseCode)
            {
                case '1': //Approved
                    $this->blnApproved = true;
                    $this->strStatusText = $this->strResponseReasonText;
                    $this->blnHasErrors = false;
                    break;
                case '2': //Declined
                    $this->blnApproved = false;
                    $this->strStatusText = $this->strResponseReasonText;
                    $this->blnHasErrors = false;
                    $this->objOrder->Delete();
                    break;
                case '3': //Error
                    $this->strErrors = $this->strResponseReasonText;
/*                                                  . '<br />Response code: ' . $this->intResponseCode
                                                  .'<br />Response: ' . $this->strResponse;*/
                    switch($this->intResponseReasonCode)
                    {
                        case '103':
                            $this->strErrors .= '<br />Valid Fingerprint, Transaction Key or Password Required. ';
                            break;
                        case '555':
                            $this->strErrors .= '<br /> Invalid server response. ';
                            break;
                         default:
                            $this->strErrors .= '<br />Unknown internal error. ';
                    }
                    $this->blnApproved = false;
                    $this->blnHasErrors = true;
                    $this->objOrder->Delete();
                    break;
                case '4': //Held for review
                             ///@todo  handle held for review payments (authnet)
                    $this->blnApproved = false;
                    $this->blnHasErrors = false;
                    break;
                default:
                    $this->blnApproved = false;
                    $this->blnHasErrors = true;
                    $this->strErrors = $this->strResponseReasonText
                                                  . '<br />Unknown Response code: ' . $this->intResponseCode
                                                  .'<br />Response: ' . $this->strResponse;
//                    $this->objOrder->Delete();
            }
            
        }
        /**
        * Creates GET query string for the transaction appropriate to the provider API, storing
        * the result in strGETRequest.
        * NOTE: Currently unused - we send a POST ..
        */        
        protected function createGETRequest()
        {
            $this->initMerchantFields();
            $this->initTransactionFields();
            $this->initOrderFields();
            $this->initCustomerFields();
            foreach( $this->aryRequestValues as $strName => $strValue )
                if('' != $strValue )
                    $this->strGETRequest .= $strName . '=' . urlencode($strValue) . '&';
            $this->strGETRequest = rtrim($this->strGETRequest,'&');
            if('' != $this->strGETRequest )
                $this->blnHasErrors = false;
        }

        protected function createPOSTRequest()
        {
            $this->initMerchantFields();
            $this->initTransactionFields();
            $this->initOrderFields();
            $this->initCustomerFields();
            foreach( $this->aryRequestValues as $strName => $strValue )
                if('' != $strValue )
                    $this->strPOSTRequest .= $strName . '=' . urlencode($strValue) . '&';
            $this->strPOSTRequest = rtrim($this->strPOSTRequest,'&');
            if('' != $this->strPOSTRequest )
                $this->blnHasErrors = false;
        }
        protected function initMerchantFields()
        {
            $this->aryRequestValues['x_login'] = $this->strRemoteAccountId;
            $this->aryRequestValues['x_tran_key'] = $this->strTransactionKey;
        }
        protected function initTransactionFields($strTransactionId='')
        {
/* unused - todo: .. not sure what to do with this yet.
            if('' != $strTransactionId)
                $this->aryRequestValues['x_trans_id'] = $strTransactionId;
            else
                $this->aryRequestValues['x_trans_id'] = $this->strTransactionId;
            $this->aryRequestValues['x_auth_code'] = $this->
*/
            $this->aryRequestValues['x_amount'] = $this->fltTotalPrice;
            $this->aryRequestValues['x_card_num'] = $this->strCCNumber;
            $this->aryRequestValues['x_exp_date'] = $this->strCCExpirationMonth . $this->strCCExpirationYear;
            
            //Note - this should be checked on input, this is for testing - remove conditional ..
            if( '' != $this->strCCVNumber )
                $this->aryRequestValues['x_card_code'] = $this->strCCVNumber;
        }
        protected function initOrderFields()
        {
            $this->aryRequestValues['x_invoice_num'] = $this->objOrder->Id;
            $this->aryRequestValues['x_description'] = DEFAULT_ORDER_DESCRIPTION;
            //todo: maybe itemized list here ..
            //foreach (orderitems) $this->aryRequestValues['x_line_item'] ...etc.
        }
        protected function initCustomerFields()
        {

            $this->aryRequestValues['x_customer_ip'] = $_SERVER['REMOTE_ADDR'];
            $this->aryRequestValues['x_cust_id'] = $this->objOrder->Account->Id;
            $this->aryRequestValues['x_email'] = $this->objOrder->Account->Person->EmailAddress;
            $this->initAddressFields();
            
/* todo: optionally have authorize send a confirmation email ..
            $this->aryRequestValues['x_email_customer'] = '';
            $this->aryRequestValues['x_email_header'] = '';
            $this->aryRequestValues['x_email_footer'] = '';
*/
        }
        protected function initAddressFields()
        {
            //todo: if($this->SendShippingAddress)
            
            //We must concatenate all the name fields into first or last name ..
            if( '' != $this->objOrder->BillingNamePrefix )
            {
                $this->aryRequestValues['x_first_name'] = $this->objOrder->BillingNamePrefix . ' ';
                $this->aryRequestValues['x_first_name'] .= $this->objOrder->BillingFirstName;
            }
            else
                $this->aryRequestValues['x_first_name'] = $this->objOrder->BillingFirstName;
            
            if( '' != $this->objOrder->BillingMiddleName )
                $this->aryRequestValues['x_first_name'] .= ' ' . $this->objOrder->BillingMiddleName;
                
            $this->aryRequestValues['x_last_name'] = ' ' . $this->objOrder->BillingLastName;

            if( '' != $this->objOrder->BillingNameSuffix )
                $this->aryRequestValues['x_last_name'] .= ' ' . $this->objOrder->BillingNameSuffix;

            $this->aryRequestValues['x_address'] = $this->objOrder->BillingStreet1;

            //ALERT: There is no field for Street2, County or Suburb so we put them all in address .. this may
            // cause problems ..
            if( '' != $this->objOrder->BillingStreet2 )
                $this->aryRequestValues['x_address'] .= ', ' . $this->objOrder->BillingStreet2;
            if( '' != $this->objOrder->BillingSuburb )
                $this->aryRequestValues['x_address'] .= ', ' . $this->objOrder->BillingSuburb;
            if( '' != $this->objOrder->BillingCounty )
                $this->aryRequestValues['x_address'] .= ', ' . $this->objOrder->BillingCounty;

            $this->aryRequestValues['x_city'] = $this->objOrder->BillingCity;
            $this->aryRequestValues['x_state'] = $this->objOrder->BillingState;
            $this->aryRequestValues['x_country'] = $this->objOrder->BillingCountry;
            $this->aryRequestValues['x_zip'] = $this->objOrder->BillingPostalCode;
        }
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'SendShippingAddress':
                    return $this->blnSendShippingAddress;
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
                case 'SendShippingAddress':
                    try {
                        $this->blnSendShippingAddress = QType::Cast($mixValue, QType::Boolean );
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