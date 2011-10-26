<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/

if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("ENDICIAREQUESTBASE.CLASS.PHP")){
define("ENDICIAREQUESTBASE.CLASS.PHP",1);

/**
* Class EndiciaRequest -  class for performing shipping requests via Endicia API web services
*
* This class provides an interface to shipping requests. Abstract methods MUST be implemented by
* subclasses, others may be overridden as needed.  Subclasses are instantiated by a call to
* ShippingMethod::GetRequest($strRequestType). The primary function of this base class is to associate
* the request with the ShippingMethod
* 
* This class also connects to the specified server via the methods provided by extending WebServiceRequest.
*
*@todo
*   - implement the other Get methods, only label requests are finished!
*   - 
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: EndiciaRequest.class.php 517 2009-03-24 17:59:23Z erikwinn $
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

 class EndiciaRequest extends ShippingRequest
 {
        /**
        * Assigned by Endicia - identifies the system making the request.
        *However, any string (strlen > 0 && < 50) will do as it is not checked
        *@var string strRequesterId
        */
        protected $strRequesterId;
        /**
        *@var string strRequestId
        */
        protected $strRequestId;
        /**
        * Endicia prefers the weight in ounces .. (0000.0)
        *@var float fltTotalWeightOz
        */
        protected $fltTotalWeightOz;
        /**
        * Type of label to return [Default, CertifiedMail, DestinationConfirm, International]
        * "Default" creates based on mail class. Note: if you want PDF images returned you
        * must set this to International for international labels.
        *@var string strLabelType
        */
        protected $strLabelType = 'Default';
        /**
        * The format for the returned image - this is the default for international using
        * LabelType "Default" is GIF so this is the best default for us.
        *  [EPL2, GIF,  JPEG, PDF, PNG, ZPLII ]
        *@var string strImageFormat
        */
        protected $strImageFormat = 'GIF';
        /**
        * [4X6 | 4X5 | 4X4.5 | 6X4 | 7X3 | Dmo30384 | EnvelopeSize10 | Mailer7X5 ]
        * The size for the returned image (inches)
        *@var string strLabelSize
        */
        protected $strLabelSize = '4X6';
        /**
        * Rotation for the returned image [None, Rotate90, Rotate180, Rotate270]
        *@var string strImageRotation
        */
        protected $strImageRotation = 'Rotate180';
        /**
        * Resolution for the returned image [150 | 203 | 300]
        *@var string strImageResolution
        */
        protected $strImageResolution = '203';
        /**
        * Endicia uses its own version of the USPS Service Types ..
        * For Domestic:
        * [Express, First, LibraryMail, MediaMail,ParcelPost, Priority]
        * For International:
        * [ExpressMailInternational, FirstClassMailInternational, PriorityMailInternational]
        *@var string strMailClass
        */
        protected $strMailClass;
        /**
        * Specifies nondelivery options for international labels and customs forms
        *@var string strNonDeliveryOption
        */
        protected $strNonDeliveryOption;
        /**
        * Unique identifier for the end user printing the label
        *@var string strPartnerCustomerId
        */
        protected $strPartnerCustomerId;
        /**
        * Unique identifier for the transaction (eg. invoice or order id)
        *@var string strPartnerTransactionId
        */
        protected $strPartnerTransactionId;
        /**
        * Name of the sender, required for international shipping and must contain
        * at least two words. This defaults to STORE_OWNER
        *@var string strFromName
        */
        protected $strFromName;
        /**
        * Name of the sender company
        * at least two words. This defaults to STORE_NAME
        *@var string strFromCompany
        */
        protected $strFromCompany;
        /**
        * Name of the sender city
        * at least two words. This defaults to STORE_CITY
        *@var string strFromCity
        */
        protected $strFromCity;
        /**
        * Line one of return address ..
        *@var string strReturnAddress1
        */
        protected $strReturnAddress1;
        /**
        * Line two of return address ..
        *@var string strReturnAddress2
        */
        protected $strReturnAddress2;
        /**
        * The total value of the shipment
        *@var float fltTotalValue
        */
        protected $fltTotalValue;
        
        /**
        * EndiciaRequest Constructor - sets defaults for this request method ..
        *
        * @param ShippingMethod objShippingMethod - the method to be used for the request
        */
        public function __construct(ShippingMethod $objShippingMethod)
        {
            parent::__construct($objShippingMethod);

            if($objShippingMethod->TestMode)
            {
                $this->strRemoteAccountId = ENDICIA_TESTACCOUNT_ID;            
                $this->strRemotePassword = ENDICIA_TESTPASSWORD;
                $this->strRequestId = ENDICIA_TESTREQUEST_ID;
                $this->strRequesterId = ENDICIA_TESTREQUESTER_ID;
                $this->strRemoteDomainName = ENDICIA_TESTDOMAIN;
            } else {
                $this->strRemoteAccountId = ENDICIA_ACCOUNT_ID;            
                $this->strRemotePassword = ENDICIA_PASSWORD;
                $this->strRequestId = ENDICIA_REQUEST_ID;
                $this->strRequesterId = ENDICIA_REQUESTER_ID;
                $this->strRemoteDomainName = ENDICIA_DOMAIN;
            }
            
            $this->strRemoteCgiUrl = '/LabelService/EwsLabelService.asmx/';
            
            //combine weight as endicia accepts only ounces
            $this->fltTotalWeightOz = round( ($objShippingMethod->Ounces + ($objShippingMethod->Pounds * 16)), 2);
            //and the minimum is one.
            if($this->fltTotalWeightOz < 1)
                $this->fltTotalWeightOz = 1;
            
            $this->fltTotalValue = $objShippingMethod->Order->ProductTotalCharged;

            $this->strPartnerTransactionId = 'BPCB' . $objShippingMethod->Order->Id;
            $this->strPartnerCustomerId = STORE_NAME;
            $this->strFromCompany = STORE_NAME;
            $this->strReturnAddress1 = STORE_ADDRESS1;
            $this->strReturnAddress2 = STORE_ADDRESS2;
            $this->strFromCity = STORE_CITY;
            $this->strFromName = STORE_OWNER;
            $aryFromName = explode( ' ', $this->strFromName );
            if(count( $aryFromName ) < 2 )
                  throw new Exception('EndiciaRequest: FromName (STORE_OWNER) must have at least 2 words!');

            if($this->objShippingMethod->Order->IsInternational)
            {
                $this->strLabelType = 'International';
//                $this->strImageFormat = 'GIF';
            }
                
            //translate USPS service types - ugly as these change .. careful.
            ///@todo - fill out the rest here .. library, parcel, etc .. no time ..
            if( 'FIRST CLASS' == $objShippingMethod->ServiceType )
                $this->strMailClass = 'First';
            elseif( 'PRIORITY' == $objShippingMethod->ServiceType )
                $this->strMailClass = 'Priority';
            elseif( 'EXPRESS' == $objShippingMethod->ServiceType )
                $this->strMailClass = 'Express';
            elseif( 'Express Mail International' == $objShippingMethod->ServiceType )
                $this->strMailClass = 'ExpressMailInternational';
            elseif( 'First Class Mail International' == $objShippingMethod->ServiceType )
                $this->strMailClass = 'FirstClassMailInternational';
            elseif( 'Priority Mail International' == $objShippingMethod->ServiceType )
                $this->strMailClass = 'PriorityMailInternational';
            else // assume we are using the Endicia ShippingMethod natively
                $this->strMailClass = $objShippingMethod->ServiceType;

        }
        /**
        * Returns a shipping label image suitable for printing
        *@return image object containing the image code
        */
        public function GetLabel()
        {
            $this->createRequest(ShippingRequestType::Label, WebRequestType::POST);
            $this->submitRequest();
            return $this->objShippingLabelImage;
        }
        /**
        * Creates a label image request
        */
        protected function createLabelRequest()
        {
            $this->strApiName = 'GetPostageLabelXML';
            
            if('Express Mail International' == $this->ServiceType
                   || 'Priority Mail International' == $this->ServiceType )
                $this->strImageRotation ='Rotate90';
            
            $strXML = 'labelRequestXML=';
            $strXML .= sprintf('<LabelRequest Test="%s" LabelType="%s" LabelSize="%s" LabelFormat="%s" ImageRotation="%s" ImageResolution="%s">',
                            $this->Test,
                            $this->strLabelType,
                            $this->strLabelSize,
                            $this->strImageFormat,
                            $this->strImageRotation,
                            $this->strImageResolution
                          );
            $strXML .= '<RequesterID>' . $this->strRequesterId . '</RequesterID>';
            $strXML .= '<AccountID>' . $this->RemoteAccountId . '</AccountID>';
            $strXML .= '<PassPhrase>' . $this->RemotePassword . '</PassPhrase>';
            $strXML .= '<MailClass>' . $this->strMailClass . '</MailClass>';
            $strXML .= '<WeightOz>' . $this->fltTotalWeightOz. '</WeightOz>';
            if($this->fltTotalValue)
                $strXML .= '<Value>' . $this->fltTotalValue . '</Value>';                
            $strXML .= '<OriginCountry>' . $this->OriginCountry . '</OriginCountry>';
            ///@todo -- make this smarter ..
            $strXML .= '<Description>Consumer Goods</Description>';
            
            $strXML .= '<PartnerCustomerID>' . $this->strPartnerCustomerId . '</PartnerCustomerID>';
            $strXML .= '<PartnerTransactionID>' . $this->strPartnerTransactionId . '</PartnerTransactionID>';
            
            $strXML .= $this->formatAddress($this->objShippingMethod->Order);

            $strXML .= '<FromCompany>' . $this->strFromCompany . '</FromCompany>';
            $strXML .= '<FromName>' . $this->strFromName . '</FromName>';
            $strXML .= '<ReturnAddress1>' . $this->strReturnAddress1 . '</ReturnAddress1>';
            $strXML .= '<ReturnAddress2>' . $this->strReturnAddress2 . '</ReturnAddress2>';
            $strXML .= '<FromCity>' . $this->strFromCity . '</FromCity>';
            $strXML .= '<FromState>' . $this->OriginStateCode . '</FromState>';
            $strXML .= '<FromPostalCode>' . substr($this->OriginZip, 0, 5) . '</FromPostalCode>';
            $strPhone = preg_replace( '/[\- ]/', '', STORE_PHONE  );
            $strXML .= '<FromPhone>' . $strPhone . '</FromPhone>';
            if($this->objShippingMethod->Order->IsInternational)
            {
                $strXML .= '<FromCountry>' . $this->OriginCountry . '</FromCountry>';
                $this->initCustomsInformationArray();
                foreach($this->aryCustomsInformation as $intIndex => $objInfo)
                {
                    $intIdx = $intIndex + 1;
                    $strXML .= '<CustomsDescription' . $intIdx . '>' . $objInfo->Description. '</CustomsDescription' . $intIdx . '>';
                    $strXML .= '<CustomsQuantity' . $intIdx . '>' . $objInfo->Quantity . '</CustomsQuantity' . $intIdx . '>';
                    $strXML .= '<CustomsWeight' . $intIdx . '>' . ceil($objInfo->Weight) . '</CustomsWeight' . $intIdx . '>';
                    $strXML .= '<CustomsValue' . $intIdx . '>' . $objInfo->Value . '</CustomsValue' . $intIdx . '>';
                    $strXML .= '<CustomsCountry' . $intIdx . '>' . $objInfo->OriginCountry . '</CustomsCountry' . $intIdx . '>';
                }
            }           
            $strXML .= '</LabelRequest>';

            $this->strRequest = $strXML;
        }
        /**
        * Creates an account status request
        */
        protected function createAccountStatusRequest()
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );

            $this->strApiName = 'GetAccountStatusXML';
            
            $strXML  = 'accountStatusRequestXML=';
            $strXML .= '<AccountStatusRequest Test="' . $this->Test . '">';
            $strXML .= '<RequesterID>' . $this->RequesterId . '</RequesterID>';
            $strXML .= '<RequestID>' . $this->RequestId . '</RequestID>';
            $strXML .= '<CertifiedIntermediary>';
            $strXML .= '<AccountID>' . $this->RemoteAccountId. '</AccountID>';
            $strXML .= '<PassPhrase>' . $this->RemotePassword . '</PassPhrase>';
            $strXML .= '</CertifiedIntermediary>';
            $strXML .= '</AccountStatusRequest>';

            $this->strRequest = $strXML;
        }
        /**
        * Creates a request submitting an account credit payment 
        */
        protected function createCreditAccountRequest($fltAmount)
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s UNTESTED DO NOT USE! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
                                                                        
            $this->strApiName = 'BuyPostageXML';

            if( ! is_numeric($fltAmount) || $fltAmount < 10 || $fltAmount >= 100000)
                    throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s - Param: %s ' .
                                                                    'Postage amount must be a number between 10 and 99999.99.',
                                                                    ShippingRequestType::ToString($this->ShippingRequestType),
                                                                    $fltAmount ));

            $strXML  = 'recreditRequestXML=';
            $strXML .= '<RecreditRequest Test="' . $this->Test . '">';
            $strXML .= '<RequesterID>' . $this->RequesterId . '</RequesterID>';
            $strXML .= '<RequestID>' . $this->RequestId . '</RequestID>';
            $strXML .= '<CertifiedIntermediary>';
            $strXML .= '<AccountID>' . $this->RemoteAccountId . '</AccountID>';
            $strXML .= '<PassPhrase>' . $this->RemotePassword . '</PassPhrase>';
            $strXML .= '</CertifiedIntermediary>';
            $strXML .= '<RecreditAmount>' . round($fltAmount, 2) . '</RecreditAmount>';
            $strXML .= '</RecreditRequest>';

            $this->strRequest = $strXML;
        }
        
        //Response handlers
        /**
        * Handles a rate request
        */
        protected function handleRateResponse()
        {
            $objDomDoc = $this->getDomDocument('RateRequestResponse');
            if($objDomDoc)
            {
                $strErrorMessage = $this->requestErrors($objDomDoc);
                if($strErrorMessage)
                {
                    $this->blnHasErrors = true;
                    $this->strErrors =  $strErrorMessage;
                    $this->fltRate = null;
                }
                else 
                    $this->fltRate = $objDomDoc->getElementsByTagName('NetCharge')->item(0)->nodeValue;
             } else {
//                    die($this->strResponse);
                $this->HasErrors = true;
                $this->ErrorMessages = 'Unknown Endicia error ..';
                $this->fltRate = 0;
            }            
        }
        /**
        * Handles a label image request response
        * This function handles the entire label request which includes extracting the tracking number
        * and other information from the response XML. 
        */
        protected function handleLabelResponse()
        {
            $objDomDoc = $this->getDomDocument('LabelRequestResponse');
            if($objDomDoc)
            {                
                $strErrorMessage = $this->requestErrors($objDomDoc);
                if($strErrorMessage)
                {
                    $this->blnHasErrors = true;
                    $this->strErrors =  $strErrorMessage;
                    $this->objShippingLabelImage = null;
                } else {
                    $objImageNodeList = $objDomDoc->getElementsByTagName('Base64LabelImage');
                    //if nothing, try the other tag - International is usually in there ..
                    if($objImageNodeList->length <= 0) 
                      $objImageNodeList = $objDomDoc->getElementsByTagName('Image');
                    //still nothing? ruh roh ..
                    if($objImageNodeList->length <= 0)
                        throw new Exception('EndiciaRequest: No label image returned for Order ' . $this->Order->Id);
                        
                    $this->objShippingLabelImage = base64_decode( $objImageNodeList->item(0)->nodeValue );
                    
                    if(!$this->objShippingLabelImage)
                        throw new Exception('EndiciaRequest: Empty image!');
                        
                    if($this->Order->IsInternational)
                    {//get customs forms if available ..
                        if($objImageNodeList->length > 1)
                            for($intIdx = 1; $intIdx < $objImageNodeList->length; ++$intIdx)
                                $this->aryCustomsFormImages[] = base64_decode($objImageNodeList->item($intIdx)->nodeValue);
                    }
                    
                    //try to save the tracking number ..
                    $objTrackingNumberNodeList = $objDomDoc->getElementsByTagName('TrackingNumber');
                    if($objTrackingNumberNodeList->length > 0)
                    {
                        $strTrackingNumber = $objTrackingNumberNodeList->item(0)->nodeValue;
                        if(!empty($strTrackingNumber))
                        {
                            if(! TrackingNumber::LoadByOrderIdNumber($this->objShippingMethod->OrderId, $strTrackingNumber ))
                            {
                                $objTrackingNumber = new TrackingNumber();
                                $objTrackingNumber->OrderId = $this->objShippingMethod->OrderId;
                                $objTrackingNumber->Number = $strTrackingNumber;
                                $objTrackingNumber->Save();
                            }
                        }
                    }
                    $objFinalPriceNodeList = $objDomDoc->getElementsByTagName('FinalPostage');
                    if($objFinalPriceNodeList->length > 0)
                    {
                        $strFinalPrice = $objFinalPriceNodeList->item(0)->nodeValue;
                        if(!empty($strFinalPrice))
                        {
                            $this->objShippingMethod->Order->ShippingCost = $strFinalPrice;
                            $this->objShippingMethod->Order->Save(false,true);
                        }
                    }
                }
            } else {
//                    die($this->strResponse);
                $this->blnHasErrors = true;
                $this->strErrors = 'Unknown Endicia error ..Request:' . $this->strRequest . ' Response:'  . $this->strResponse;
                $this->objShippingLabelImage = 0;
            }

        }
        
        /**
        * Utility function to format the address for a label or rate request, creates a
        * string containing the XML tags for the address
        *@return string
        */
        private function formatAddress($objOrder)
        {
            $strToReturn = '';
            $intAddressLine = 1;
            
            $strToReturn .= '<ToName>' . $objOrder->FullShippingName . '</ToName>';
            
            if($objOrder->ShippingCompany)
                $strToReturn .= '<ToCompany>' . $objOrder->ShippingCompany . '</ToCompany>';

            //Endicia allows 4 Address lines so we have to be clever here ..
            $strToReturn .= '<ToAddress' . $intAddressLine . '>' . $objOrder->ShippingStreet1 . '</ToAddress' . $intAddressLine . '>';
            ++$intAddressLine;
            if($objOrder->ShippingStreet2)
            {//line 2
                $strToReturn .= '<ToAddress' . $intAddressLine . '>' . $objOrder->ShippingStreet2 . '</ToAddress' . $intAddressLine . '>';
                ++$intAddressLine;
            }
              
            if($objOrder->ShippingSuburb)
            {//line 2 or 3 ..
                $strToReturn .= '<ToAddress' . $intAddressLine . '>' . $objOrder->ShippingSuburb . '</ToAddress' . $intAddressLine . '>';
                ++$intAddressLine;
            }
            //line 2, 3 or 4 ..
            if($objOrder->ShippingCounty)
            {//line
                $strToReturn .= '<ToAddress' . $intAddressLine . '>' . $objOrder->ShippingCounty . '</ToAddress' . $intAddressLine . '>';
                ++$intAddressLine;
            }
            
            $strToReturn .= '<ToCity>' . $objOrder->ShippingCity . '</ToCity>';
            if( ZoneType::NoZone != $this->objShippingMethod->DestinationStateId )
                $strToReturn .= '<ToState>' . $this->objShippingMethod->DestinationStateCode . '</ToState>';
            //truncate zip else endicia complains ..
            $strToReturn .= '<ToPostalCode>' . substr( trim($this->objShippingMethod->DestinationZip), 0, 5) . '</ToPostalCode>';
            $strToReturn .= '<ToCountry>' . $this->objShippingMethod->DestinationCountry . '</ToCountry>';
            $strPhoneNumber = trim($objOrder->Account->Person->PhoneNumber);
            if( !empty($strPhoneNumber) && 'N/A' != $strPhoneNumber)
                $strPhoneNumber = preg_replace('/[^\d]/', '', $strPhoneNumber);
            else
                $strPhoneNumber = '8091236354';
           $strToReturn .= '<ToPhone>' . $strPhoneNumber . '</ToPhone>';
                
            return $strToReturn;
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
            
            $nodeListStatus = $objDomDoc->getElementsByTagName('Status');
            //Status is zero on success ..
            if($nodeListStatus->item(0)->nodeValue > 0)
            {
                $nodeListErrors = $objDomDoc->getElementsByTagName('ErrorMessage');
                $this->blnHasErrors = true;
                $mixToReturn =  'Request: ' . $this->strRequest;
                if( $nodeListErrors->length)
                    $mixToReturn .= ' Message: ' . $nodeListErrors->item(0)->nodeValue;
                else
                    $mixToReturn .= ' ... Endicia had no comment. ';
            }
            return $mixToReturn;
        }

        ///Gettors
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Test':
                    return $this->TestMode ? 'YES' : 'NO';
                case 'Carrier':
                    return $this->objShippingMethod->Carrier ;
                default:
                     try {
                        return parent::__get($strName);
                     } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                     }
            }
        }
        
        ///Settors
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
               case 'FromName':
                    try {
                        return ($this->strFromDate = QType::Cast($mixValue, QType::String));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
               case 'Container':
                        return ($this->objShippingMethod->Container = $mixValue);
                default:
                    try {
                        return (parent::__set($strName, $mixValue));
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
        
/*************************************************************************************/
///@todo - implement the other Get methods ..:        
        /**
        * Returns a rate for this method to the order address
        *@return float containing the rate for the order address
        */
        public function GetRate()
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Returns an account status report
        *@return string containing the status report
        */
        public function GetAccountStatus()
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Returns whether this method is available for the order address
        *@return boolean true if method is available
        */
        public function GetAvailability()
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Submits an account credit payment
        *@return boolean true on success
        */
        public function CreditAccount()
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        
        //Request string creators
        /**
        * Creates a rate request 
        */
        protected function createRateRequest()
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Creates a method available request
        */
        protected function createAvailabilityRequest()
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        
        /**
        * Handles an account status request
        */
        protected function handleAccountStatusResponse()
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Handles a request submitting an account credit payment
        */
        protected function handleCreditAccountResponse()
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Handles a method available request
        */
        protected function handleAvailabilityResponse()
        {
            throw new QCallerException(sprintf('EndiciaRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
/*************************************************************************************/
      
  }//end class
}//end define

?>