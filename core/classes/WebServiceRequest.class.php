
<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/
if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("WEBSERVICEREQUEST.CLASS.PHP")){
define("WEBSERVICEREQUEST.CLASS.PHP",1);

/**
* Class  WebRequestType - enumerator class for types of shipping requests
*@package Quasi
* @subpackage Classes
*/
class WebRequestType
{
    ///@var const - a POST request
    const POST = 1;
    ///@var const - a GET request
    const GET = 2;
    ///@var const - a SOAP request
    const SOAP = 3;
}

/**
* Class WebServiceRequest - base class for classes that perform request actions with a web service
*
* This class provides the basic request actions and properties for all of the Shipping and Payment Action classes.
* This includes making the connection to the payment service provider, sending the request in either GET or
* POST (usually in XML ..) and accepting the response from the server.
* The request to send is stored in strRequest and the response is stored in strResponse regardless
* of format or request type. Subclasses are responsible for initilizing these priory to calling createRequest(), 
* formatting the request and for handling the response. The required properties (eg. RemoteDomainName,
* RemotePassword, etc ..) must be initilized by users of this or subclasses.
*
* Subclasses must implement these methods:
*   - createPostRequest: initializes strRequest with a formatted POST query (may return null if unused)
*   - createGetRequest: initializes strRequest with a formatted GET query (may return null if unused)
*   - handleResponse: parse strResponse for relevant data ..
*
* See the documentation for the Shipping and Payment Action subclasses
* for more details.
*
*@todo
*  - Support SOAP connections
*  - Support using CURL
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: WebServiceRequest.class.php 473 2009-01-13 17:10:15Z erikwinn $
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

abstract class WebServiceRequest
 {
        //////////// HTTP members:
        /**
        *@var string Username, login or account id for the service
        */
        protected $strRemoteAccountId;
        /**
        *@var string password for the service
        */
        protected $strRemotePassword;
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
        * This is appended to the CGI URL: www.foo.com/$CgiUrl . ApiName .. etc.  
        *@var string the name of the API call to use
        */
        protected $strApiName;
        /**
        *@var string storage for response from service
        */
        protected $strResponse;
        /**
        *@var string storage for the request to service
        */
        protected $strRequest;
        /**
        *@var string The type of request to be made (GET | POST | SOAP ) 
        */
        protected $intRequestType;
        /**
        *@var integer Port number to use for the connection (80, 443)
        */
        protected $intPort;
        /**
        *@var integer Connection time out in seconds
        */
        protected $intTimeOut = 90;

        //////////// SOAP members:
        /**
        * This is an array handed to the SOAP client - the values should match those
        * found in the WSDL 
        *@var array storage for the SOAP request to service
        */
        protected $arySoapRequest;
        /**
        * This is an array handed to the SOAP client - the values should match those
        * found in the WSDL 
        *@var mixed storage for the SOAP response
        */
        protected $mixSoapResponse;
        /**
        *@var string name of the SOAP function to call
        */
        protected $strSoapFunction;
        /**
        *@var string location of the WSDL to use
        */
        protected $strWsdlUri;
        
        //////////// Common members:
        /**
        *@var string Errors 
        */
        protected $strErrors;
        /**
        *@var boolean True if there were errors or if the transaction/connection failed for any reason
        */
        protected $blnHasErrors;
        /**
        *@var boolean True if we should use SSL to connect to the provider
        */
        protected $blnUseSsl = true;
        
        /**
        * NOTE: You must explicitly set this to disable testing mode ..
        *@var boolean True for testing (and by default)
        */
        protected $blnTestMode = true;
        
        /**
        * Parses the response from the web services provider
        */        
        abstract protected function handleResponse();
        /**
        * Creates GET query string for the transaction appropriate to the provider API, storing
        * the result in strRequest.
        */        
        abstract protected function createGETRequest();
        /**
        * Creates POST query string for the transaction appropriate to the provider API, storing
        * the result in strRequest.
        */        
        abstract protected function createPOSTRequest();
        
        /**
        * Connects to web SOAP service and submits the request.
        * This function merely passes the arySoapRequest to the SOAP client
        * and stores the result in mixSoapResponse.
        * Note: strWsdlUri, strSoapFunction, and arySoapRequest_must_ _all_
        * be set before calling this.
        *@return boolean true on success
        */
        protected function submitSoapRequest()
        {
            //Fedex Example code does this - not sure why or if it is really needed ..
            ini_set("soap.wsdl_cache_enabled", "0");

            $objClient = new SoapClient($this->strWsdlUri, array('trace' => 1));
//typical php - this does not work, __soapCall breaks the parameter array:
//            $this->mixSoapResponse = $objClient->__soapCall($this->strSoapFunction, $this->arySoapRequest);
// so we have to do this:
            $strFunctionName = $this->strSoapFunction;
            $this->mixSoapResponse = $objClient->$strFunctionName($this->arySoapRequest);
        }
        /**
        * Connects to web service and submits the request. Note that
        * this function merely constructs a request URL from internal variables
        * that are set in createRequest, it may therefor contain a GET query
        * string or a POST depending on the subclass requirements.
        *@return boolean true on success
        */
        protected function submitRequest()
        {
            if( WebRequestType::SOAP === $this->intRequestType)
                return $this->submitSoapRequest();
                
            $strProtocol = '';
            if($this->UseSsl)
            {
                $strProtocol = 'ssl://';
                $this->intPort = 443;
            }
            else
            {
//                $strProtocol = 'http://';
                $this->intPort = 80;
            }
            $strTarget = $strProtocol . $this->strRemoteDomainName;
            //attempt to connect ..
            @$fp = fsockopen($strTarget,
                                        $this->intPort,
                                        $intError,
                                        $strError,
                                        $this->intTimeOut
                                      );
                                        
            //did we connect?                            
            if (!$fp)
            {
                if($this->TestMode)
                    throw new Exception("Web Service request failed: $strError ($intError) ");
                else
                    return false;
            }
            else
            {
                // optionally add an API extension:
                if($this->strApiName)
                    $strUrl = $this->strRemoteCgiUrl . $this->strApiName;
                else
                    $strUrl = $this->strRemoteCgiUrl;
                //construct the request ..
                switch( $this->intRequestType )
                {
                    case WebRequestType::GET:
                        $out = "GET " . $strUrl . $this->strRequest . " HTTP/1.1\r\n";
                        $out .= "Host:" . $this->strRemoteDomainName . "\r\n";
                        $out .= "User-Agent: QuasiCMS " . QUASI_VERSION . "\r\n";
                        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
                        $out .= "Connection: Close\r\n\r\n";
                        break;
                    case WebRequestType::POST:
                        $out = "POST " . $strUrl . " HTTP/1.1\r\n";
                        $out .= "Host:" . $this->strRemoteDomainName . "\r\n";
                        $out .= "User-Agent: QuasiCMS " . QUASI_VERSION . "\r\n";
//                        $out .= "MIME-Version: 1.0\r\n";
                        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
//                        $out .= "Accept: text/xml\r\n";
                        $out .= "Content-length: " . strlen($this->strRequest) . "\r\n";
                        $out .= "Cache-Control: no-cache\r\n";
                        $out .= "Connection: Close\r\n\r\n";
                        $out .= $this->strRequest . "\r\n\r\n";
                        break;
                    default:
                        throw new Exception('WebService RequestType unsupported: ' . $this->intRequestType);
                }
                //send the request 
                fwrite($fp, $out );
                
                $this->strResponse = '';
                //store the response
                while ( !feof($fp) ) 
                    $this->strResponse .= fgets($fp, 128);
                $this->strResponse .= $out;
                fclose($fp);
                return true;
            }
        
        }
        /**
        * This function directs the call to the appropriate creation function and returns
        * a string containing either a query string for a GET or a content string for a POST.
        *
        * A WebRequestType may be provided to override a default as an alternative to setting
        * it explicitly. Note that this will set the RequestType for the object.
        *
        *@param string intRequestType - you may provide the RequestType
        */
        protected function createRequest($intWebRequestType=null)
        {
            if(null !== $intWebRequestType)
                $this->intRequestType = $intWebRequestType;
                
            switch($this->intRequestType)
            {
                case WebRequestType::GET:
                    $this->createGETRequest();
                    return $this->strRequest;
                    break;
                case WebRequestType::POST:
                    $this->createPOSTRequest();
                    return $this->strRequest;
                    break;
                case WebRequestType::SOAP:
                default:
                        throw new Exception('WebService RequestType unsupported: ' . $this->RequestType);
            }
        }
        /**
        * Utility function to extract a root XML node string by tag
        *
        *@param string text of the node name
        *@return null | DOMDocument - a DOMDocument containing the node or null on failure
        */
        protected function getDomDocument($strTag)
        {
            $strStartTag = '<' . $strTag . '>';
            $strEndTag = '</' . $strTag . '>';
            $intStartPos = strpos( $this->strResponse, $strStartTag );
            //no start? try leaving the start tag open ended to allow definitions ..
            if(false === $intStartPos)
            {
                $strStartTag = '<' . $strTag . ' ';
                $intStartPos = strpos( $this->strResponse, $strStartTag );
            }
            $intEndPos = strpos($this->strResponse, $strEndTag);
            $intLength = ($intEndPos + strlen($strEndTag)) - $intStartPos;
            if(false !== $intStartPos && false !== $intEndPos)
            {
                $objDomDoc = new DOMDocument();
                //don't let Domdoc complain about incorrect Endicia XML ..
                if( @$objDomDoc->loadXML(substr( $this->strResponse, $intStartPos, $intLength)) );
                    return $objDomDoc;
            }
            return null;
        }
                
        public function __get($strName)
        {
            switch ($strName)
            {
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
                case 'HasErrors':
                    return $this->blnHasErrors ;
                case 'UseSsl':
                    return $this->blnUseSsl ;
                case 'TestMode':
                    return $this->blnTestMode ;
                case 'TimeOut':
                    return $this->intTimeOut ;
                case 'Port':
                    return $this->intPort ;
                default:
                    throw new Exception('WebService Request - Unknown __get property: ' . $strName);
            }
        }
        
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
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
                case 'UseSsl':
                    try {
                        return ($this->blnUseSsl = QType::Cast($mixValue, QType::Boolean ));
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
                default:
                    throw new Exception('WebService Request - Unknown __set property: ' . $strName);
            }
        }
        
    }//end class
}//end define

?>