<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/

if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("USPSREQUEST.CLASS.PHP")){
define("USPSREQUEST.CLASS.PHP",1);


/**
* Class USPSRequest - provides shipping requests for USPS via web services
* Note: This class provides access to the EndiciaRequest as a child request object for
* some services.
*
*Service must be Express, First Class, Priority, Parcel, Library, BPM, Media or ALL for domestic (in US)
* 
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: USPSRequest.class.php 502 2009-02-10 22:09:53Z erikwinn $
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

 class USPSRequest  extends ShippingRequest
 {
        /**
        *@var string Indicates enumeration value for size (REGULAR, LARGE, OVERSIZE)
        */
        protected $strSize = "REGULAR";
        /**
        *@var string Indicates enumeration value for  FIRST CLASS "type" [LETTER | FLAT | PARCEL]
        */
        protected $strFirstClassMailType = "FLAT";
        /**
        *@var ShippingRequest
        */
        protected $objEndiciaRequest;
        
        /**
        * USPSRequest Constructor
        *
        * @param ShippingMethod objShippingMethod - the method for which to obtain estimate
        */
        public function __construct(ShippingMethod $objShippingMethod)
        {
            
            try {
                parent::__construct($objShippingMethod);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            ///@todo  this is currently defined in quasi_config - fixme!!
            $this->RemoteAccountId = USPS_USERID;
            $this->RemotePassword = USPS_PASSWORD;
                        
            if($this->blnTestMode)
            {
                $this->strRemoteDomainName = 'testing.shippingapis.com';
                $this->strRemoteCgiUrl = '/ShippingAPITest.dll?';
            }
            else
            {
                $this->strRemoteDomainName = 'production.shippingapis.com';
                $this->strRemoteCgiUrl = '/ShippingAPI.dll?';
            }
            $this->UseSsl = false;
        }
        //Public interface ..
        /**
        * Returns a shipping label for this method to the order address
        * Note that we use the Endicia label server for this - it must be enabled
        * and you must have a configured account with Endicia for this to work!
        *@return resource gd image object containing label image
        */
        public function GetLabel()
        {
            $this->objEndiciaRequest = new EndiciaRequest($this->objShippingMethod);
            $this->objShippingLabelImage = $this->objEndiciaRequest->GetLabel();
            $this->blnHasErrors = $this->objEndiciaRequest->HasErrors;
            $this->strErrors = $this->objEndiciaRequest->Errors;
            $this->aryExtraDocumentImages = $this->objEndiciaRequest->ExtraDocumentImages;
            $this->aryCustomsFormImages = $this->objEndiciaRequest->CustomsFormImages;
            return $this->objShippingLabelImage;
        }
        /**
        * Returns a shipping rate for the order for this method
        *@return image object containing the image code
        */
        public function GetRate()
        {
            $this->createRequest(ShippingRequestType::Rate, WebRequestType::GET);
            $this->submitRequest();
            return $this->Rate;
        }
        
        //Request string creators
        /**
        * Creates a rate request - if the shipping is international this call createIntlRateRequest instead ..
        */
        protected function createRateRequest()
        {
            if($this->Order->IsInternational)
                return $this->createIntlRateRequest();
                
            $this->strApiName = 'API=RateV3';
            if($this->blnTestMode)
            {
                $this->OriginZip = '10022';
                $this->DestinationZip = '20008';
                $this->Ounces = 5;
                $this->Pounds = 10;
                $this->Container = 'Flat Rate Box';
            }

            $this->strRequest = '&XML=';
            $strXml = '<RateV3Request USERID="' .$this->strRemoteAccountId . '">';
            $strXml .= '<Package ID="0">';
            $strXml .= '<Service>' . $this->ServiceType . '</Service>';
            $strXml .= '<FirstClassMailType>' . $this->strFirstClassMailType . '</FirstClassMailType>';
            $strXml .= '<ZipOrigination>' . $this->OriginZip . '</ZipOrigination>';
            $strXml .= '<ZipDestination>' . $this->DestinationZip . '</ZipDestination>';
            $strXml .= '<Pounds>' . $this->Pounds . '</Pounds>';
            $strXml .= '<Ounces>' . round($this->Ounces, 2) . '</Ounces>';
            $strXml .= '<Container>' . $this->Container .'</Container>';
            $strXml .= '<Size>' . $this->strSize . '</Size>';
            $strXml .= '<Machinable>' . ($this->IsMachinable ? 'True' : 'False') . '</Machinable>';
            $strXml .= '</Package></RateV3Request>';
            $strXml = urlencode($strXml);
            $this->strRequest .= $strXml;
        }
        /**
        * Creates an international shipping rate request 
        */
        protected function createIntlRateRequest()
        {        
            //USPS testing servers do not support international - force to production ..               
            $this->strRemoteDomainName = 'production.shippingapis.com';
            $this->strRemoteCgiUrl = '/ShippingAPI.dll?';
            
            //neato - USPS has decided that it prefers "Great Britain" and not "United Kingdom" ..
            $strCountry = strtoupper($this->DestinationCountry);
            if($strCountry == "UNITED KINGDOM")
                $strCountry = 'GREAT BRITAIN';
                   
            $this->strApiName = 'API=IntlRate';
            $this->strRequest = '&XML=';
            $strXml = '<IntlRateRequest USERID="' . $this->strRemoteAccountId . '">';
            $strXml .= '<Package ID="0">';
            $strXml .= '<Pounds>' . $this->Pounds . '</Pounds>';
            $strXml .= '<Ounces>' . round($this->Ounces, 2) . '</Ounces>';
            $strXml .= '<MailType>Package</MailType>';
            $strXml .= '<Country>' . $strCountry . '</Country>';
            $strXml .= '</Package></IntlRateRequest>';
            $strXml = urlencode($strXml);
            $this->strRequest .= $strXml;
        }
        //Response handlers
        /**
        * Handles a rate request response
        */
        protected function handleRateResponse()
        {
            if ($this->objShippingMethod->Order->IsInternational)
                $objDomDoc = $this->getDomDocument('IntlRateResponse');
            else
                $objDomDoc = $this->getDomDocument('RateV3Response');
            if($objDomDoc)
            {
                $strErrorMessage = $this->requestErrors($objDomDoc);
                if($strErrorMessage)
                {
                    $this->blnIsAvailable = false;
                    $this->blnHasErrors = true;
                    $this->strErrors =  $strErrorMessage;
                    $this->Rate = 0;
                } else {
                    $this->blnIsAvailable = true;

                    if( $this->objShippingMethod->Order->IsInternational)
                        return $this->handleIntlRateResponse($objDomDoc);
                        
                    $nodeList = $objDomDoc->getElementsByTagName('Postage');                    
                    $nodeList = $nodeList->item(0)->getElementsByTagName('Rate');
                    
                    if($nodeList->length > 0)
                        $this->Rate = $nodeList->item(0)->nodeValue;
                    else
                        $this->Rate = 0;
                }
             } else {
                $this->blnIsAvailable = false;
                $this->HasErrors = true;
                $this->Errors = 'Unknown USPS error ..Request:' . $this->strRequest . ' Response:'  . $this->strResponse;
                $this->Rate = 0;
            }
        }
        /**
        * Handles an International rate request response.
        * USPS returns multiple rates for some areas (eg. Australia) and we need to parse the DOM to
        * return the correct rate for the method (service type).
        * @todo - more sophisticated and configurable matching by package type; currently we mostly grab the first match ..
        * Here is a typical response for the Service nodes:
        
<Service id="4"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>79.95</Postage><SvcCommitments>1 - 3 Days</SvcCommitments>\
<SvcDescription>Global Express Guaranteed</SvcDescription><MaxDimensions>Max. length 46", width 35", height 46" and max. length plus girth 108"</MaxDimensions><MaxWeight>70</MaxWeight>
</Service>
<Service id="6"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>79.95</Postage><SvcCommitments>1 - 3 Days</SvcCommitments>
<SvcDescription>Global Express Guaranteed Non-Document Rectangular</SvcDescription><MaxDimensions>Max. length 46", width 35", height 46" and max. length plus girth 108"</MaxDimensions><MaxWeight>70</MaxWeight>
</Service>
<Service id="7"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>79.95</Postage><SvcCommitments>1 - 3 Days</SvcCommitments>
<SvcDescription>Global Express Guaranteed Non-Document Non-Rectangular</SvcDescription><MaxDimensions>Max. length 46", width 35", height 46" and max. length plus girth 108"</MaxDimensions><MaxWeight>70</MaxWeight>
</Service>
<Service id="12"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>79.95</Postage><SvcCommitments>1 - 3 Days</SvcCommitments>
<SvcDescription>USPS GXG Envelopes</SvcDescription><MaxDimensions>Cardboard envelope has a dimension of 9 1/2" X 12 1/2" and GXG tyvek envelope has a dimension of 12 1/2" X 15 1/2"</MaxDimensions><MaxWeight>70</MaxWeight>
</Service>
<Service id="1"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>25.95</Postage><SvcCommitments>8 Days</SvcCommitments>
<SvcDescription>Express Mail International (EMS)</SvcDescription><MaxDimensions>Max.length 36", max. length plus girth 79"</MaxDimensions><MaxWeight>70</MaxWeight>
</Service>
<Service id="10"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>25.95</Postage><SvcCommitments>8 Days</SvcCommitments>
<SvcDescription>Express Mail International (EMS) Flat-Rate Envelope</SvcDescription><MaxDimensions>9 1/2" X 12 1/2"</MaxDimensions><MaxWeight>70</MaxWeight>
</Service>
<Service id="2"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>21.50</Postage><SvcCommitments>6 - 10 Days</SvcCommitments>
<SvcDescription>Priority Mail International</SvcDescription><MaxDimensions>Max. length 42", Max length plus girth combined 79"</MaxDimensions><MaxWeight>70</MaxWeight>
</Service>
<Service id="8"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>11.95</Postage><SvcCommitments>6 - 10 Days</SvcCommitments>
<SvcDescription>Priority Mail International Flat-Rate Envelope</SvcDescription><MaxDimensions>USPS-supplied Priority Mail flat-rate envelope 9 1/2" x 12 1/2." Maximum weight 4 pounds.</MaxDimensions><MaxWeight>4</MaxWeight>
</Service>
<Service id="9"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>38.95</Postage><SvcCommitments>6 - 10 Days</SvcCommitments>
<SvcDescription>Priority Mail International Flat-Rate Box</SvcDescription><MaxDimensions>USPS-supplied Priority Mail flat-rate box. Maximum weight 20 pounds.</MaxDimensions><MaxWeight>20</MaxWeight>
</Service>
<Service id="11"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>49.95</Postage><SvcCommitments>6 - 10 Days</SvcCommitments>
<SvcDescription>Priority Mail International Large Flat-Rate Box</SvcDescription><MaxDimensions>USPS-supplied Priority Mail Large flat-rate box. Maximum weight 20 pounds.</MaxDimensions><MaxWeight>20</MaxWeight>
</Service>
<Service id="13"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>0.94</Postage><SvcCommitments>Varies</SvcCommitments>
<SvcDescription>First Class Mail International Letters</SvcDescription><MaxDimensions>Max. length 11.5", height 6 1/8" or more than 1/4" thick</MaxDimensions><MaxWeight>0.2188</MaxWeight>
</Service>
<Postage>1.20</Postage><SvcCommitments>Varies</SvcCommitments>
<SvcDescription>First Class Mail International Large Envelope</SvcDescription><MaxDimensions>Max. length 15", height 12 or more than 3/4" thick</MaxDimensions><MaxWeight>4</MaxWeight>
</Service>
<Service id="15"><Pounds>0</Pounds><Ounces>0.07</Ounces><MailType>Package</MailType><Country>ROMANIA</Country>
<Postage>1.40</Postage><SvcCommitments>Varies</SvcCommitments>
<SvcDescription>First Class Mail International Package</SvcDescription><MaxDimensions>Max. length 24", max length, height and depth (thickness) combined 36"</MaxDimensions><MaxWeight>4</MaxWeight>
</Service>
        *
        */
        protected function handleIntlRateResponse($objDomDoc)
        {
            $this->Rate = 0;
            $nodeList = $objDomDoc->getElementsByTagName('Service');
            if($nodeList->length > 0)
            {
                foreach($nodeList as $objNode)
                {
                    $nodeSvcDescription = $objNode->getElementsByTagName('SvcDescription');
                    $nodePostage = $objNode->getElementsByTagName('Postage');
                    if($nodeSvcDescription->length > 0)
                    {
                        $strSvcDesc = $nodeSvcDescription->item(0)->nodeValue;
                        if(false !== stripos($strSvcDesc , $this->ServiceType  ))
                        {
                        //these are examples - not written in stone (they just happen to work for me now ..):
                            if('First Class Mail International' == $this->ServiceType )
                            {
                                if(false === stripos($strSvcDesc , 'Large Envelope' ))
                                    continue;
                                else
                                {
                                    $this->Rate = $nodePostage->item(0)->nodeValue;
                                    break;
                                }
                            }
                            if('Priority Mail International' == $this->ServiceType )
                            {
                                if(false === stripos($strSvcDesc , 'Flat-Rate Envelope' ))
                                    continue;
                                else
                                {
                                    $this->Rate = $nodePostage->item(0)->nodeValue;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        /**
        * Utility function to check for request errors - returns either a string containing
        * server error messages or false if there were none.
        *@param DOMDocument objDomDoc - the server response ..
        *@return string | boolean error messages or false if request succeeded.
        */
        private function requestErrors($objDomDoc)
        {
            $mixToReturn = false;
            $nodeListErrors = $objDomDoc->getElementsByTagName('Error');
            if( $nodeListErrors->length > 0 )
            {
                $this->blnHasErrors = true;
                $mixToReturn =  'Request: ' . $this->strRequest;
                $nodeListErrorMessages = $objDomDoc->getElementsByTagName('Description');
                if( $nodeListErrorMessages->length)
                    $mixToReturn .= ' Message: ' . $nodeListErrorMessages->item(0)->nodeValue;
            }
            return $mixToReturn;
        }

/*************************************************************************************/
///@todo - implement me:        
        /**
        * Returns an account status report
        *@return string containing the status report
        */
        public function GetAccountStatus()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Returns whether this method is available for the order address
        *@return boolean true if method is available
        */
        public function GetAvailability()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Submits an account credit payment
        *@return boolean true on success
        */
        public function CreditAccount()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        
        //Request string creators
        /**
        * Creates a method available request
        */
        protected function createAvailabilityRequest()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Creates a label printing request
        */
        protected function createLabelRequest()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Creates a request submitting an account credit payment
        */
        protected function createCreditAccountRequest()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Creates an account status request
        */
        protected function createAccountStatusRequest()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        
        /**
        * Handles an account status request
        */
        protected function handleAccountStatusResponse()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Handles a request submitting an account credit payment
        */
        protected function handleCreditAccountResponse()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Handles a method available request
        */
        protected function handleAvailabilityResponse()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Handles a label request
        */
        protected function handleLabelResponse()
        {
            throw new QCallerException(sprintf('USPSRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }

/*************************************************************************************/

  }//end class
}//end define

?>