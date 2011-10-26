
<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/
if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("PAYMENTACTION.CLASS.PHP")){
define("PAYMENTACTION.CLASS.PHP",1);

/**
* Class PaymentActionBase - base class for classes that perform payment actions with a web service
*
* This class provides the basic actions and properties for all of the PaymentAction classes. This includes
* making the connection to the payment service provider, sending the request in either GET or POST (usually
* in XML ..) and accepting the response from the server. The response is stored in strResponse regardless
* of format.
*
* Subclasses are responsible for formatting the request. Users of the subclasses are responsible for initializing
* the required properties.
*
* Subclasses must implement these methods:
*   - PreProcess: perform any actions to set up the transaction
*   - Process: perform the actual transaction, ie. connect to server and make a request in most cases.
*   - PostProcess: perform any validation checks and update the order_status_history table in most cases.
*     PayPal Express Checkout redirects the user to the PayPal site at this point and order_status
*     is set to "Pending" (??), there is a special return page for this kind of case that will complete the
*     order_status update (??).
*
* See the PaymentModule class documentation and documentation for the PaymentAction subclasses
* for more details.
*
*@todo
*   - port payment actions to use WebServiceRequest .. and this class should go away ..
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: PaymentActionBase.class.php 497 2009-01-26 20:56:28Z erikwinn $
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

abstract class PaymentActionBase
 {
        /**
        *@var Order  local reference to the Order being processed
        */
        protected $objOrder;
        /**
        *@var string Order id (aka Invoice number)
        */
        protected $strOrderId;
        /**
        * In some cases (eg. Authorize.net) this is returned by the provider, in others
        * we may provide it for tracking the transaction.
        *@var string Transaction identifier
        */
        protected $strTransactionId;
        /**
        * In some cases (eg. Authorize.net) this is used in place of RemotePassword
        * - it is provided merely for literate purposes.
        *@var string Transaction Key - aka Password ..
        */
        protected $strTransactionKey;
        /**
        *@var string Username, login or account id for the service
        */
        protected $strRemoteAccountId;
        /**
        *@var string password for the service
        */
        protected $strRemotePassword;
        /**
        * Note: this is currently unused, the Quasi built in PaymentModule displays the payment
        * action selection as QRadioButtons ..
        *@var string the template file to use for this method
        */
        protected $strTemplateUri;
        /**
        *@var string the FQD for the payment service provider API server
        */
        protected $strRemoteDomainName;
        /**
        * Note: This must contain the separater character that follows the script name, eg. '?' or '&'
        * if the request is of type GET
        *@var string the URL portion after the domain name leading to API script
        */
        protected $strRemoteCgiUrl;
        /**
        * This is the URL to which a customer is redirected to make a payment (eg. www.paypal.com ..)
        *@var string the FQD for the payment service provider redirect target
        */
        protected $strRedirectDomainName;
        /** 
        * Note: This must contain the separater character that follows the script name, eg. '?' or '&'
        * if the request is of type GET
        *@var string the URL portion after the domain name leading to redirect script
        */
        protected $strRedirectCgiUrl;
        /**
        *@var string storage for response from service
        */
        protected $strResponse;
        /**
        *@var string storage for POST request to service
        */
        protected $strPOSTRequest;
        /**
        *@var string query string appended to CGI URL for a GET request
        */
        protected $strGETRequest;
        /**
        *@var string The type of request to be made (GET | POST ) 
        */
        protected $strRequestType;
        /**
        *@var boolean True if we should use CURL to connect to the provider
        */
        protected $blnUseCurl = false;
        /**
        *@var boolean True if we should use SSL to connect to the provider
        */
        protected $blnUseSsl = true;
        /**
        *@var boolean True if we should use SSL certificate to authenticate ourselves ..
        */
        protected $blnUseSslCertificate = false;
        /**
        *@var string full path to our SSL certificate 
        */
        protected $strSslCertificateUri;
        /**
        *@var integer Port number to use for the connection (80, 443)
        */
        protected $intPort = 443;
        /**
        *@var integer Connection time out in seconds
        */
        protected $intTimeOut = 60;
        /**
        *@var boolean True if there were errors or if the transaction/connection failed for any reason
        */
        protected $blnHasErrors;
        /**
        *@var string Errors 
        */
        protected $strErrors;
        /**
        * This contains a string which either confirms the approval of a payment or gives a reason
        * for its failure.
        *@var string status explanation text from the transaction
        */
        protected $strStatusText;
        /**
        *@var boolean True if the transaction was approved 
        */
        protected $blnApproved = false;
        /**
        * NOTE: You must explicitly set this to disable testing mode ..
        *@var boolean True for testing (and by default)
        */
        protected $blnTestMode = true;

        /**
        *@var float total charges for items
        */
        protected $fltSubTotal;
        /**
        *@var float Charges for shipping
        */
        protected $fltShippingCharge;
        /**
        *@var float Charges for handling
        */
        protected $fltHandlingCharge;
        /**
        *@var float Taxes
        */
        protected $fltTax;
        
        /**
        *@var string CC for customer
        */
        protected $strCCNumber;
        /**
        *@var string CC expiration data for customer
        */
        protected $strCCExpirationYear;
        protected $strCCExpirationMonth;
        /**
        *@var string CCV number for customer
        */
        protected $strCCVNumber;
        
        /**
        *@var float Total of all charges, items, shipping, tax and handling
        */
        protected $fltTotalPrice;
        
        /**
        * PaymentActionBase Constructor
        *
        * @param Order objOrder - the order for which we are attempting to pay..
        */
        public function __construct(Order $objOrder)
        {
            $this->objOrder =& $objOrder;
            $this->fltHandlingCharge = $objOrder->HandlingCharged;
            $this->fltShippingCharge = $objOrder->ShippingCharged;
            $this->fltTax = $objOrder->Tax;

            $this->fltTotalPrice = $objOrder->ProductTotalCharged
                                           + $objOrder->HandlingCharged
                                           + $objOrder->ShippingCharged
                                           + $objOrder->Tax;
            
        }
        /**
        * Performs any preparation steps prior to submitting an actual payment. For example
        * the PayPal NVP (Express Checkout) calls the SetExpressCheckoutDetails API here
        *@return bool true on success  
        */        
        abstract public function PreProcess();
        /**
        * Performs any steps necessary after submitting an actual payment. For example
        * the PayPal NVP (Express Checkout) calls the GetExpressCheckoutDetails API here.
        * Updating order_status_history is also performed here.
        *@return bool true on success  
        */        
        abstract public function PostProcess();
        /**
        * Performs the actual payment submission - in some cases this is a request/response
        * routine and in some (eg. PayPal), this simply redirects the user to the provider site.
        *@return bool true on success  
        */        
        abstract public function Process();
        /**
        * Parses the response from the payment service provider
        */        
        abstract protected function handleResponse();
        /**
        * Creates GET query string for the transaction appropriate to the provider API, storing
        * the result in strGETRequest.
        */        
        abstract protected function createGETRequest();
        /**
        * Creates GET query string for the transaction appropriate to the provider API, storing
        * the result in strGETRequest.
        */        
        abstract protected function createPOSTRequest();
        
        /**
        * This function directs the call to the appropriate creation function and returns
        * a string containing either a query string for a GET or a content string for a POST.
        *
        * A RequestType may be provided to override a default as an alternative to setting
        * it explicitly. Note that this will set the RequestType for the object.
        *
        *@param string strRequestType - you may provide the RequestType 
        */
        protected function createRequest($strRequestType=null)
        {
            if(null != $strRequestType)
                $this->strRequestType = $strRequestType;
                
            switch($this->strRequestType)
            {
                case 'GET':
                    $this->createGETRequest();
                    return $this->strGETRequest;
                    break;
                case 'POST':
                    $this->createPOSTRequest();
                    return $this->strPOSTRequest;
                    break;
                case 'SOAP':
                default:
                    throw new QCallerException('PaymentAction - Unsupported RequestType: ' . $this->strRequestType);
            }
        }
         /**
        * Connects to payment service and submits the request. Note that
        * this function merely constructs a request URL from internal variables
        * that are set in createRequest, it may therefor contain a GET query
        * string or not depending on the subclass requirements.
        *
        *@return bool true on success
        */
        protected function submitRequest()
        {
            if($this->UseCurl)
                return $this->submitCurlRequest();
                
            if($this->UseSsl)
                $strProtocol = 'ssl://';
            else
                $strProtocol = 'http://';

            //attempt to connect ..
            $fp = fsockopen($strProtocol . $this->strRemoteDomainName,
                                        $this->intPort,
                                        $intError,
                                        $strError,
                                        $this->intTimeOut
                                      );
                                        
            //did we connect?                            
            if (!$fp)
            {
/*                $this->blnHasErrors = true;
                return 0;*/
                throw new QCallerException("Payment Action base: Connection request failed: $strError ($intError) ");
            }
            else
            {
                //construct the request ..
                switch( $this->strRequestType )
                {
                    case 'GET':
                        $out = "GET " . $this->strRemoteCgiUrl . $this->strGETRequest . " HTTP/1.1\r\n";
                        $out .= "Host:" . $this->strRemoteDomainName . "\r\n";
                        $out .= "User-Agent: QuasiCMS " . QUASI_VERSION . "\r\n";
                        $out .= "Connection: Close\r\n\r\n";
                        break;
                    case 'POST':
                        $out = "POST " . $this->strRemoteCgiUrl . " HTTP/1.1\r\n";
                        $out .= "Host:" . $this->strRemoteDomainName . "\r\n";
                        $out .= "User-Agent: QuasiCMS " . QUASI_VERSION . "\r\n";
//                        $out .= "MIME-Version: 1.0\r\n";
                        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
//                        $out .= "Accept: text/xml\r\n";
                        $out .= "Content-length: " . strlen($this->strPOSTRequest) . "\r\n";
                        $out .= "Cache-Control: no-cache\r\n";
                        $out .= "Connection: Close\r\n\r\n";
                        $out .= $this->strPOSTRequest . "\r\n\r\n";
                        break;
                    default:
                        throw new QCallerException('WebService RequestType unsupported: ' . $this->RequestType);
                }
                //send the request 
                fwrite($fp, $out );
                
                $this->strResponse = '';
                //store the response
                while ( !feof($fp) ) 
                    $this->strResponse .= fgets($fp, 128);
                $this->strResponse .= $out;
                fclose($fp);
            }
            return true;
        }
        /**
        * This is an alternate request submission method using CURL
        * @return bool true on sucess
        */
        protected function submitCurlRequest()
        {
            if($this->UseSsl)
                $strProtocol = 'https://';
            else
                $strProtocol = 'http://';
                
            $objCurlHandle = curl_init();
            curl_setopt($objCurlHandle, CURLOPT_USERAGENT, 'QuasiCMS ' . QUASI_VERSION);
            curl_setopt($objCurlHandle, CURLOPT_TIMEOUT, $this->intTimeOut);
            curl_setopt ($objCurlHandle, CURLOPT_RETURNTRANSFER, 1);
            
            switch( $this->strRequestType )
            {
                case 'GET':
                    $strUri = $strProtocol . $this->strRemoteDomainName . $this->strRemoteCgiUrl . $this->strGETRequest;
                    curl_setopt($objCurlHandle, CURLOPT_URL, $strUri);
                    break;
                case 'POST':
                    $strUri = $strProtocol . $this->strRemoteDomainName . $this->strRemoteCgiUrl;
                    curl_setopt($objCurlHandle, CURLOPT_POST, 1);
                    curl_setopt($objCurlHandle, CURLOPT_URL, $strUri);
                    curl_setopt($objCurlHandle, CURLOPT_POSTFIELDS, $this->strPOSTRequest);
                    break;
                default:
                    throw new QCallerException('WebService RequestType unsupported: ' . $this->RequestType);
            }
            if($this->UseSsl)
            {
                curl_setopt($objCurlHandle, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($objCurlHandle, CURLOPT_SSL_VERIFYPEER, 0);
            }
            if($this->UseSslCertificate)
                curl_setopt($objCurlHandle, CURLOPT_SSLCERT,$this->strSslCertificateUri);

            $this->strResponse = curl_exec($objCurlHandle);
            if(false === $this->strResponse )
            {
                $this->HasErrors = true;
                $this->strErrors .= curl_error($objCurlHandle);
                return false;
            }            
            curl_close($objCurlHandle);
        }
        /**
         *This function sets the order status for an order when payment is approved.
         * It also inserts a new order_status_history and clears the shopping cart. The confirmation
         * email is handled by Order when the status is set.
         */
        public function completeOrder()
        {                
/* this is now done with __set magic in Order and the notes seem superflous, disabled pending removal           
            $objOrderStatusHistory = new OrderStatusHistory();
            $objOrderStatusHistory->OrderId = $this->objOrder->Id;
            $objOrderStatusHistory->StatusId = OrderStatusType::Paid;
            $objOrderStatusHistory->Notes = $this->objOrder->Notes;
            $objOrderStatusHistory->Save();
*/
            // Order can send its own email confirmations ..
            $this->objOrder->SetStatus(OrderStatusType::Paid);

            IndexPage::$objShoppingCart->DeleteAllShoppingCartItems();
        }
                
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'TotalPrice':
                    return $this->fltTotalPrice;
                case 'StatusText':
                    return $this->strStatusText;
                case 'TemplateUri':
                    return $this->strTemplateUri ;
                case 'Order':
                    return $this->objOrder ;
                case 'OrderId':
                    return $this->objOrder->Id ;
                case 'RemotePassword':
                    return $this->strRemotePassword ;
                case 'RemoteAccountId':
                    return $this->strRemoteAccountId ;
                case 'RemoteCgiUrl':
                    return $this->strRemoteCgiUrl ;
                case 'RemoteDomainName':
                    return $this->strRemoteDomainName ;
                case 'Errors':
                    return $this->strErrors ;
                case 'Approved':
                    return $this->blnApproved ;
                case 'HasErrors':
                    return $this->blnHasErrors ;
                case 'UseCurl':
                    return $this->blnUseSsl ;
                case 'UseSsl':
                    return $this->blnUseSsl ;
                case 'UseSslCertificate':
                    return $this->blnUseSslCertificate ;
                case 'SslCertificateUri':
                    return $this->strSslCertificateUri;
                case 'TestMode':
                    return $this->blnTestMode ;
                case 'CCNumber':
                    return $this->strCCNumber ;
                case 'CCExpirationYear':
                    return $this->strCCExpirationYear ;
                case 'CCExpirationMonth':
                    return $this->strCCExpirationMonth ;
                case 'CCVNumber':
                    return $this->strCCVNumber ;
                default:
                    throw new QCallerException('Payment Action - Access Unknown property: ' . $strName);
            }
        }
        
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
                case 'TemplateUri':
                    try {
                        return ($this->strTemplateUri = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'TransactionId':
                    try {
                        return ($this->strTransactionId = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'OrderId':
                    try {
                        return ($this->strOrderId = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'RemoteDomainName':
                    try {
                        return ($this->strRemoteDomainName = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'RemoteCgiUrl':
                    try {
                        return ($this->strRemoteCgiUrl = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'RemoteAccountId':
                    try {
                        return ($this->strRemoteAccountId = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'RemotePassword':
                    try {
                        return ($this->strRemotePassword = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Approved':
                    try {
                        return ($this->blnApproved = QType::Cast($mixValue, QType::Boolean ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Errors':
                    try {
                        return ($this->strErrors = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'HasErrors':
                    try {
                        return ($this->blnHasErrors = QType::Cast($mixValue, QType::Boolean ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'UseCurl':
                    try {
                        return ($this->blnUseCurl = QType::Cast($mixValue, QType::Boolean ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'UseSsl':
                    try {
                        return ($this->blnUseSsl = QType::Cast($mixValue, QType::Boolean ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'UseSslCertificate':
                    try {
                        return ($this->blnUseSslCertificate = QType::Cast($mixValue, QType::Boolean ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'SslCertificateUri':
                    try {
                        return ($this->strSslCertificateUri = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'TestMode':
                    try {
                        return ($this->blnTestMode = QType::Cast($mixValue, QType::Boolean ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Port':
                    try {
                        return ($this->intPort = QType::Cast($mixValue, QType::Integer ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'TimeOut':
                    try {
                        return ($this->intTimeOut = QType::Cast($mixValue, QType::Integer ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'CCNumber':
                    try {
                        return ($this->strCCNumber = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'CCExpirationYear':
                    try {
                        return ($this->strCCExpirationYear = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'CCExpirationMonth':
                    try {
                        return ($this->strCCExpirationMonth = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'CCVNumber':
                    try {
                        return ($this->strCCVNumber = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                default:
                        throw new QCallerException('Payment Action - Set Unknown property: ' . $strName);
            }
        }
        
    }//end class
}//end define

?>