<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/

if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("FEDEXREQUEST.CLASS.PHP")){
define("FEDEXREQUEST.CLASS.PHP",1);

/**
* Class FedexRequest - provides services for Fedex Direct Connect
* This class implements the FEDEX SOAP API for web service requests. 
*
*     NOTE: ServiceType as configured in the database must be one of the following:
*    - EUROPE_FIRST_INTERNATIONAL_PRIORITY
*    - FEDEX_1_DAY_FREIGHT
*    - FEDEX_2_DAY
*    - FEDEX_2_DAY_FREIGHT
*    - FEDEX_3_DAY_FREIGHT
*    - FEDEX_EXPRESS_SAVER
*    - FEDEX_GROUND
*    - FIRST_OVERNIGHT
*    - GROUND_HOME_DELIVERY
*    - INTERNATIONAL_ECONOMY
*    - INTERNATIONAL_ECONOMY_FREIGHT
*    - INTERNATIONAL_FIRST
*    - INTERNATIONAL_PRIORITY
*    - INTERNATIONAL_PRIORITY_FREIGHT
*    - PRIORITY_OVERNIGHT
*    - STANDARD_OVERNIGHT
*
*@todo - document this class ..
* 
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: FedexRequest.class.php 517 2009-03-24 17:59:23Z erikwinn $
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

 class FedexRequest extends ShippingRequest
 {
        /**
        *     NOTE: LabelStockType must be one of the following:
        *    - PAPER_4X6
        *    - PAPER_4X8
        *    - PAPER_4X9
        *    - PAPER_7X4.75
        *    - PAPER_8.5X11_BOTTOM_HALF_LABEL
        *    - PAPER_8.5X11_TOP_HALF_LABEL
        *    - STOCK_4X6
        *    - STOCK_4X6.75_LEADING_DOC_TAB
        *    - STOCK_4X6.75_TRAILING_DOC_TAB
        *    - STOCK_4X8
        *    - STOCK_4X9_LEADING_DOC_TAB
        *    - STOCK_4X9_TRAILING_DOC_TAB
        * @var string
        */
        protected $strLabelStockType = 'PAPER_4X6';
        /**
        *     NOTE: LabelFormatType must be one of the following:
        *     COMMON2D
        *     LABEL_DATA_ONLY
        *@var string paper format for label
        */
        protected $strLabelFormatType = 'COMMON2D';
        /**
        *     NOTE: ImageType must be one of the following:
        *     DPL
        *     EPL2
        *     PDF
        *     PNG
        *     ZPLII
        *@var string image format for label
        */
        protected $strImageType = 'PNG';
        /**
        *@var string Account number provided by Fedex
        */
        protected $strAccountNumber;
        /**
        *@var string Meter number provided by Fedex
        */
        protected $strMeterNumber;
        /**
        *@var string strPayorType - who pays for the shipping
        */
        protected $strPayorType = 'SENDER';
        /**
        *     NOTE: DropoffType must be one of the following:
        *    - BUSINESS_SERVICE_CENTER
        *    - DROP_BOX
        *    - REGULAR_PICKUP
        *    - REQUEST_COURIER
        *    - STATION
        *@var string Drop off method
        */
        protected $strDropoffType = 'REGULAR_PICKUP';
        /**
        *     NOTE: PackagingType must be one of the following:
        *    - FEDEX_10KG_BOX
        *    - FEDEX_25KG_BOX
        *    - FEDEX_BOX
        *    - FEDEX_ENVELOPE
        *    - FEDEX_PAK
        *    - FEDEX_TUBE
        *    - YOUR_PACKAGING
        *@var string Packaging type
        */
        protected $strPackagingType = 'YOUR_PACKAGING';
        /**
        *@var string Indicates units of weight (LB | KG)
        */
        protected $strWeightUnits = 'LB';
        /**
        *@var string Indicates units of length (IN | CM)
        */
        protected $strLengthUnits = 'IN';
        /**
        *  FedEx combines units (eg. pounds and ounces ) into one figure ..
        *@var float fltWeight - weight of package in designated units ..
        */
        protected $fltWeight;

        /**
        * FedexRequest Constructor
        *
        * @param ShippingMethod objShippingMethod - the method for which to obtain estimate
        */
        public function __construct(ShippingMethod $objShippingMethod)
        {
            parent::__construct($objShippingMethod);

            //now unused ..
            $this->strRemoteCgiUrl = '/GatewayDC';
            
            if($objShippingMethod->TestMode)
            {///@todo  defined in config - fixme!
                $this->strRemoteDomainName = 'gatewaybeta.fedex.com';
                $this->strRemoteAccountId = FEDEX_TESTKEY;
                $this->strRemotePassword = FEDEX_TESTPASSWORD;
                $this->strAccountNumber = FEDEX_TESTACCOUNT_NUMBER;
                $this->strMeterNumber = FEDEX_TESTMETER_NUMBER;
                $this->strRemoteDomainName = 'gatewaybeta.fedex.com'; //unused ..
            }
            else
            {
                $this->strRemoteDomainName = 'gateway.fedex.com';
                $this->strRemoteAccountId = FEDEX_KEY;
                $this->strRemotePassword = FEDEX_PASSWORD;
                $this->strAccountNumber = FEDEX_ACCOUNT_NUMBER;
                $this->strMeterNumber = FEDEX_METER_NUMBER;
                $this->strRemoteDomainName = 'gateway.fedex.com'; //unused ..
            }
            
            $this->fltWeight = $objShippingMethod->Pounds;
            $this->fltWeight += $objShippingMethod->Ounces / 16;
        }
        //Public interface ..
        /**
        * Returns a shipping rate for the order for this method
        *@return image object containing the image code
        */
        public function GetRate()
        {
            $this->intShippingRequestType = ShippingRequestType::Rate;
            //Example code does this - not sure why or if it is needed ..
            ini_set("soap.wsdl_cache_enabled", "0");
            if($this->blnTestMode)
                $this->strWsdlUri = __QUASI_CORE__ . '/utilities/FDXRateService_v5_test.wsdl';
            else
                $this->strWsdlUri = __QUASI_CORE__ . '/utilities/FDXRateService_v5.wsdl';
            $this->strSoapFunction = 'getRates';
            $this->createSoapRateRequest();

            try{
                $this->submitSoapRequest();
            } catch (SoapFault $objFault) {
 //               exit(var_dump($objFault));
               throw new Exception($objFault->faultstring);
            }
            $this->handleRateResponse();
            return $this->Rate;
        }
       /**
        * Utility function to format the mult-dimensional array of RateRequest (labels) request data
        * to be passed on the the Soap client function.
        *@return array - suitable for passing to the Soap function for RateRequest (labels) requests
        */
        protected function createSoapRateRequest()
        {                        
            $arySoapParamsToReturn['WebAuthenticationDetail'] = $this->createWebAuthDetailArray();
            $arySoapParamsToReturn['ClientDetail'] = $this->createClientDetailArray();
            $arySoapParamsToReturn['TransactionDetail'] = $this->createTransactionDetailArray();
            $arySoapParamsToReturn['Version'] = $this->createVersionDetailArray('crs');
            
            $aryRequestedShipment = $this->createRequestedShipmentDetailArray();
            $aryRequestedShipment['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
            
            $arySoapParamsToReturn['RequestedShipment'] = $aryRequestedShipment;
            $this->arySoapRequest = $arySoapParamsToReturn;
        }
        /**
        * Parses the rate request SOAP response from FEDEX server
        * @todo - handle errors more elegantly, make more robust ..
        */
        protected function handleRateResponse()
        {
            $this->Rate = 0;
            //first check for Fedex errors
            if('FAILURE' == $this->mixSoapResponse->HighestSeverity
                || 'ERROR' == $this->mixSoapResponse->HighestSeverity )
            {
                $this->blnIsAvailable = false;
                $this->blnHasErrors = true;
                $mixNotifications = $this->mixSoapResponse->Notifications;   
                if(is_array($mixNotifications))
                    foreach($mixNotifications as $objNotification)
                        $this->strErrors .=  $objNotification->Severity . ':' . $objNotification->Message . "\n";
                else
                    $this->strErrors .= $mixNotifications->Severity . ' : ' .$mixNotifications->Message . "\n";
                                    
                //Service is not allowed to destination - eg. fedex_2_day to Australia .. not really
                // an error, just not available so reset HasErrors:
                if( false !== stripos( $this->strErrors, 'Service is not allowed' ) )
                   $this->blnHasErrors = false;
                                         
            } else {
                $this->blnIsAvailable = true;
                $mixDetails = $this->mixSoapResponse->RateReplyDetails->RatedShipmentDetails;
                if(is_array($mixDetails))
                    $this->Rate = $mixDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;
                else
                    $this->Rate = $mixDetails->ShipmentRateDetail->TotalNetCharge->Amount;
            }
            if($this->blnIsAvailable && !$this->Rate)
            {
                $this->blnIsAvailable = false;
                $this->HasErrors = true;
                $this->strErrors = 'Unknown Fedex Error.';
            }

            return $this->Rate;
        }
        /**
        * Returns a shipping label for this method to the order address
        * Note: this function uses the SOAP interface provided from Fedex - partially due to time constraints
        * and partially to remind me to reimplement these service request classes to use SOAP; all of the
        * web services seem to be providing WSDLs now and it is builtin to PHP so it makes sense. Later.
        * This quick example implements the "new" style Fedex API.
        *@return string containing label image or null on failure ..
        */
        public function GetLabel()
        {
            $this->intShippingRequestType = ShippingRequestType::Label;
            //Example code does this - not sure why or if it is needed ..
            ini_set("soap.wsdl_cache_enabled", "0");
            if($this->blnTestMode)
                $this->strWsdlUri = __QUASI_CORE__ . '/utilities/FDXShipService_v5_test.wsdl';
            else
                $this->strWsdlUri = __QUASI_CORE__ . '/utilities/FDXShipService_v5.wsdl';
            $this->strSoapFunction = 'processShipment';
            $this->createSoapShipRequest();

            try{
                $this->submitSoapRequest();
            } catch (SoapFault $objFault) {
//                exit(var_dump($objFault));
                throw new Exception($objFault->faultstring . ' - '
                                                  . $objFault->detail->fault->details->ValidationFailureDetail->message);
            }
            return $this->handleLabelResponse();
        }
        /**
        * Handles a label response returning the image. On failure the image is null and errors are
        * stored in strErrors. The image is the string containing the formatted image from Fedex - this
        * can be written directly to a file as eg. image.png or image.pdf ..
        * @return string containing the image code returned from Fedex
        */
        protected function handleLabelResponse()
        {            
            $this->objShippingLabelImage = null;            
            //first check for Fedex errors
            if('FAILURE' == $this->mixSoapResponse->HighestSeverity
                || 'ERROR' == $this->mixSoapResponse->HighestSeverity )
            {
                $this->blnHasErrors = true;
                //Note: this part is unclear - Notifications can be an array or object?? May not work correctly ..
                if(is_array($this->mixSoapResponse->Notifications))
                    foreach($this->mixSoapResponse->Notifications as $objNotification)
                        $this->strErrors .= $objNotification->Severity . ':' . $objNotification->Message . "\n";
                else
                    $this->strErrors .= $this->mixSoapResponse->Notifications->Severity
                                                    . ': ' . $this->mixSoapResponse->Notifications->Message . "\n";
            } else {
               $objPackageDetails = $this->mixSoapResponse->CompletedShipmentDetail->CompletedPackageDetails;
               $this->objShippingLabelImage = $objPackageDetails->Label->Parts->Image;
               $strFinalPrice = $this->mixSoapResponse->CompletedShipmentDetail->ShipmentRating;
                if(!empty($strFinalPrice))
                {
                    $this->objShippingMethod->Order->ShippingCost = $strFinalPrice;
                    $this->objShippingMethod->Order->Save(false,true);
                }
               
                if($this->Order->IsInternational)
                {
                    if(property_exists($objPackageDetails, 'PackageDocuments'))
                    {
                        $aryPackageDocuments = $objPackageDetails->PackageDocuments;
                        foreach($aryPackageDocuments as $objDocument)
                        {
                            $this->aryExtraDocumentImages[] = new ExtraDocumentImage(
                                                                                                    $objDocument->Parts->Image,
                                                                                                    $objDocument->Type,
                                                                                                    $objDocument->CopiesToPrint
                                                                                                      );
                        }
                    }
                }
                $strTrackingNumber = $objPackageDetails->TrackingId->TrackingNumber;
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
            return $this->objShippingLabelImage;
        }
       /**
        * Utility function to format the mult-dimensional array of ShipRequest (labels) request data
        * to be passed on the the Soap client function.
        *@return array - suitable for passing to the Soap function for ShipRequest (labels) requests
        */
        protected function createSoapShipRequest()
        {                        
            $arySoapParamsToReturn['WebAuthenticationDetail'] = $this->createWebAuthDetailArray();
            $arySoapParamsToReturn['ClientDetail'] = $this->createClientDetailArray();
            $arySoapParamsToReturn['TransactionDetail'] = $this->createTransactionDetailArray();
            $arySoapParamsToReturn['Version'] = $this->createVersionDetailArray('ship');
            
            $aryRequestedShipment = $this->createRequestedShipmentDetailArray();
            $aryRequestedShipment['LabelSpecification'] = array('LabelFormatType' => $this->strLabelFormatType,
                                                                                                'ImageType' => $this->strImageType,
                                                                                                'LabelStockType' => $this->strLabelStockType,
                                                                                               );
            $arySoapParamsToReturn['RequestedShipment'] = $aryRequestedShipment;
            $this->arySoapRequest = $arySoapParamsToReturn;
        }
        protected function createWebAuthDetailArray()
        {
            return array('UserCredential' => array('Key' => $this->strRemoteAccountId,
                                                                        'Password' => $this->strRemotePassword
                                                                        )
                                );
        }
        protected function createClientDetailArray()
        {
            return array('AccountNumber' => $this->strAccountNumber,
                                'MeterNumber' => $this->strMeterNumber
                                );
        }
        protected function createTransactionDetailArray()
        {
            return array('CustomerTransactionId' => STORE_NAME . ' Order ' . $this->Order->Id );
        }
        protected function createVersionDetailArray($strService)
        {
            return  array('ServiceId' => $strService, 'Major' => '5', 'Intermediate' => '0', 'Minor' => '0');
        }
        protected function createShipperDetailArray()
        {
            $strSenderCountryCode = CountryType::ToIsoCode2( $this->OriginCountryId);
            $arySenderStreets = array();
            if(STORE_ADDRESS1)
                $arySenderStreets[] = STORE_ADDRESS1;
            if(STORE_ADDRESS2)
                $arySenderStreets[] = STORE_ADDRESS2;
            if(! count($arySenderStreets))
                throw new Exception('FedexRequest: Shipper address must have at least one street line!');
            return array('Contact' => array('PersonName' => STORE_OWNER,
                                                            'CompanyName' => STORE_NAME,
                                                            'PhoneNumber' => STORE_PHONE,
                                                            ),
                                'Address' => array('StreetLines' =>$arySenderStreets,
                                                            'City' => STORE_CITY,
                                                            'StateOrProvinceCode' => STORE_STATE,
                                                            'PostalCode' => STORE_POSTAL_CODE,
                                                            'CountryCode' => $strSenderCountryCode,
                                                            ),
                                );
        }
        protected function createRecipientDetailArray()
        {
            $strRecipientName = $this->Order->FullShippingName;
            $strRecipientCompany  = $this->Order->ShippingCompany;
            $strRecipientCity  = $this->Order->ShippingCity;
            $strRecipientPhone  = $this->Order->Account->Person->PhoneNumber;
            $aryRecipientStreets = array();
            if($this->Order->ShippingStreet1)
                $aryRecipientStreets[] = $this->Order->ShippingStreet1;
            if($this->Order->ShippingStreet2)
                $aryRecipientStreets[] = $this->Order->ShippingStreet2;
            if(! count($aryRecipientStreets))
                throw new Exception('FedexRequest: Recipient address must have at least one street line!');
            $aryToReturn = array('Contact' => array('PersonName' => $strRecipientName,
                                                                            'CompanyName' => $strRecipientCompany,
                                                                            'PhoneNumber' => $strRecipientPhone,
                                                                            ),
                                                'Address' => array('StreetLines' => $aryRecipientStreets,
                                                                            'City' => $strRecipientCity,
                                                                            //Fedex barfs if the state or province is over 2 chars ..
                                                                            'StateOrProvinceCode' => substr($this->DestinationStateCode, 0, 2),
                                                                            'PostalCode' => $this->DestinationZip,
                                                                            'CountryCode' => $this->DestinationCountryCode,
                                                                            ),
                                                );
            return $aryToReturn;
        }
        protected function createRequestedShipmentDetailArray()
        {
            $strSenderCountryCode = CountryType::ToIsoCode2( $this->OriginCountryId);
            $aryToReturn = array( 'ShipTimestamp' => date('c'),
                                            'DropoffType' => $this->strDropoffType,
                                            'ServiceType' => $this->ServiceType,
                                            'PackagingType' => $this->strPackagingType,
                                            'TotalWeight' => array(
                                                                            'Value' => number_format($this->Weight, 1),
                                                                            'Units' => $this->strWeightUnits,
                                                                            ),
                                            'Shipper' => $this->createShipperDetailArray(),
                                            'Recipient' => $this->createRecipientDetailArray(),
                                            'ShippingChargesPayment' => array(
                                                                                    'PaymentType' => $this->strPayorType,
                                                                                    'Payor' => array('AccountNumber' => $this->strAccountNumber,
                                                                                                              'CountryCode' => $strSenderCountryCode,
                                                                                                             ),
                                                                                                    ),
                                            'RateRequestTypes' => array('ACCOUNT'), // valid values ACCOUNT and LIST
                                            'PackageCount' => 1,
                                            'RequestedPackages' => $this->createRequestedPackagesDetailArray(),
                                                );
                                                
            if($this->Order->IsInternational && ShippingRequestType::Label == $this->intShippingRequestType)
                $aryToReturn['InternationalDetail'] = $this->createInternationalDetailArray();
                
            return $aryToReturn;
        }
        protected function createInternationalDetailArray()
        {
            //start with "Contact" and "Address" populated ..
            $aryToReturn = $this->createShipperDetailArray();
            $aryToReturn['CustomsValue'] = array('Amount' => number_format($this->TotalValue, 2),
                                                                         'Currency' => 'USD',
                                                                         );
            $aryToReturn['DocumentContent'] = 'DOCUMENTS_ONLY';
            $aryToReturn['DutiesPayment'] = array('PaymentType' => 'SENDER',
                                                                          'Payor' => array('AccountNumber' => $this->strAccountNumber,
                                                                                                    'CountryCode' => $this->DestinationCountryCode,
                                                                                                   ),
                                                                          );
            $aryCommodities = array();
            foreach( OrderItem::LoadArrayByOrderId( $this->Order->Id ) as $objOrderItem )
            {
                $objProduct = $objOrderItem->Product;
                $fltWeight = $objProduct->Weight / 16;
                if('KG' == $this->strWeightUnits )
                    $fltWeight = $fltWeight / 2.2;
                $fltTotalAmount = $objProduct->RetailPrice * $objOrderItem->Quantity;
                $aryCommodities[] = array('NumberOfPieces' => $objOrderItem->Quantity,
                                                        'Description' => $objProduct->ShortDescription,
                                                        'CountryOfManufacture' => 'US',
                                                        'Weight' => array('Value' => $fltWeight,
                                                                                   'Units' => $this->strWeightUnits,
                                                                                   ),
                                                        'Quantity' => $objOrderItem->Quantity,
                                                        'QuantityUnits' => 'EA',
                                                        'UnitPrice' => array('Amount' => $objProduct->RetailPrice,
                                                                                       'Currency' => 'USD',
                                                                                      ),
                                                        'CustomsValue' => array('Amount' => $fltTotalAmount,
                                                                                                'Currency' => 'USD',
                                                                                               ),
                                                         );
            }
            if(empty($aryCommodities))
                throw new Exception('No order items for international Order #' . $this->Order->Id);
            $aryToReturn['Commodities'] = $aryCommodities;
            return $aryToReturn;
        }
       protected function createRequestedPackagesDetailArray()
       {
            return array('0' => array('SequenceNumber' => '1',
                                                      'Weight' => array(
                                                                                'Value' => number_format($this->Weight, 1),
                                                                                'Units' => $this->strWeightUnits,
                                                                                 ),
                                                    ),
                                 );
       }
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Weight':
                    return $this->fltWeight ;
                case 'WeightUnits':
                    return $this->strWeightUnits ;
                case 'LengthUnits':
                    return $this->strLengthUnits ;
                case 'MeterNumber':
                    return $this->strMeterNumber ;
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
                case 'Weight':
                    try {
                        return ($this->fltWeight = QType::Cast($mixValue, QType::Float ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'WeightUnits':
                    try {
                        return ($this->strWeightUnits = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'LengthUnits':
                    try {
                        return ($this->strLengthUnits = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'MeterNumber':
                    try {
                        return ($this->strMeterNumber = QType::Cast($mixValue, QType::String ));
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

/*************************************************************************************/
///@todo - implement me:        
        /**
        * Returns an account status report
        *@return string containing the status report
        */
        public function GetAccountStatus()
        {
            throw new QCallerException(sprintf('FEDEXRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Returns whether this method is available for the order address
        *@return boolean true if method is available
        */
        public function GetAvailability()
        {
            throw new QCallerException(sprintf('FEDEXRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Submits an account credit payment
        *@return boolean true on success
        */
        public function CreditAccount()
        {
            throw new QCallerException(sprintf('FEDEXRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        
        //Request string creators
        /**
        * Creates a method available request
        */
        protected function createAvailabilityRequest()
        {
            throw new QCallerException(sprintf('FEDEXRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Creates a label printing request
        */
        protected function createLabelRequest()
        {
            throw new QCallerException(sprintf('FEDEXRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Creates a request submitting an account credit payment
        */
        protected function createCreditAccountRequest()
        {
            throw new QCallerException(sprintf('FEDEXRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Creates an account status request
        */
        protected function createAccountStatusRequest()
        {
            throw new QCallerException(sprintf('FEDEXRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        
        /**
        * Handles an account status request
        */
        protected function handleAccountStatusResponse()
        {
            throw new QCallerException(sprintf('FEDEXRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Handles a request submitting an account credit payment
        */
        protected function handleCreditAccountResponse()
        {
            throw new QCallerException(sprintf('FEDEXRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }
        /**
        * Handles a method available request
        */
        protected function handleAvailabilityResponse()
        {
            throw new QCallerException(sprintf('FEDEXRequest: Shipping request type %s unsupported! ',
                                                                        ShippingRequestType::ToString($this->ShippingRequestType)) );
        }

/*************************************************************************************/

/***********************  Old XML POST version functions - DEPRECATED **********************************
 These are left as examples and in case some would prefer to use them (as PHP SOAP is a bit buggy ..)
        public function GetRate()
        {
            $this->createRequest(ShippingRequestType::Rate, WebRequestType::POST);
            $this->submitRequest();
            return $this->Rate;
        }*/
        
        /**
        * Creates the POST string for the rate request - DEPRECATED
        */
        protected function createRateRequest()
        {
            $this->strRequest = '';
            $blnAddOriginStateAndZip = ( CountryType::UnitedStates == $this->OriginCountryId
                                                        ||CountryType::Canada == $this->OriginCountryId );
            $blnAddDestinationStateAndZip = ( CountryType::UnitedStates == $this->DestinationCountryId
                                                        ||CountryType::Canada == $this->DestinationCountryId );

            $str = $this->createXMLOpenTags('FDXRateRequest');
            $str .= '<RequestHeader>';
//            $str .= '<CustomerTransactionIdentifier>Express Rate</CustomerTransactionIdentifier>';
            $str .= '<AccountNumber>' . $this->strAccountNumber . '</AccountNumber>';
            $str .= '<MeterNumber>' . $this->strMeterNumber . '</MeterNumber>';
            $str .= '<CarrierCode>' . $this->Carrier . '</CarrierCode>';
            $str .= '</RequestHeader>';
            
            $str .= '<DropoffType>' . $this->strDropoffType . '</DropoffType>';
            $str .= '<Service>' . $this->ServiceType . '</Service>';
            $str .= '<Packaging>'.$this->strPackagingType . '</Packaging>';
            $str .= '<WeightUnits>'. $this->strWeightUnits . '</WeightUnits>';
            $str .= '<Weight>' . number_format($this->Weight, 1) . '</Weight>';
            $str .= '<OriginAddress>';
            if($blnAddOriginStateAndZip)
            {
                $str .= '<StateOrProvinceCode>' . $this->OriginStateCode . '</StateOrProvinceCode>';
                $str .= '<PostalCode>' . $this->OriginZip.'</PostalCode>';
            }
            $str .= '<CountryCode>' . $this->OriginCountryCode . '</CountryCode>';
            $str .= '</OriginAddress>';
            $str .= '<DestinationAddress>';
            if($blnAddDestinationStateAndZip)
            {
                $str .= '<StateOrProvinceCode>' . $this->DestinationStateCode . '</StateOrProvinceCode>';
                $str .= '<PostalCode>' . $this->DestinationZip.'</PostalCode>';
            }
            $str .= '<CountryCode>' . $this->DestinationCountryCode . '</CountryCode>';
            $str .= '</DestinationAddress>';
            $str .= '<Payment>';
            $str .= '<PayorType>' . $this->strPayorType . '</PayorType>';
            $str .= '</Payment>';
            $str .= '<PackageCount>1</PackageCount>';
            $str .= '</FDXRateRequest>';
            
            $this->strRequest = $str;
        }
        /**
        * Parses the rate request response from FEDEX server - XML POST VERSION: DEPRECATED.
        * @todo - handle errors more elegantly, make more robust ..
        */
/*        protected function handleRateResponse()
        {
            $objDomDoc = $this->getDomDocument('FDXRateReply');
            if($objDomDoc)
            {
                $strErrorMessage = $this->requestErrors($objDomDoc);
                if($strErrorMessage)
                {
                    $this->blnHasErrors = true;
                    $this->strErrors =  $strErrorMessage;
                    $this->Rate = 0;
                } else {
                    $nodeList = $objDomDoc->getElementsByTagName('NetCharge');                    
                    if($nodeList->length > 0)
                        $this->Rate = $nodeList->item(0)->nodeValue;
                    else
                        $this->Rate = 0;
                }
             } else {
                $this->HasErrors = true;
                $this->Errors = 'Unknown FEDEX error ..Request:' . $this->strRequest . ' Response:'  . $this->strResponse;
                $this->Rate = 0;
            }
 //debugging:
//             if(!$this->Rate)
//             {
//                 $this->Errors = 'Unknown FEDEX error ..Request:' . $this->strRequest . ' Response:'  . $this->strResponse;
//                 die($this->Errors);
//             }

        }*/
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
                $nodeListErrorMessages = $objDomDoc->getElementsByTagName('Message');
                if( $nodeListErrorMessages->length)
                    $mixToReturn .= ' Message: ' . $nodeListErrorMessages->item(0)->nodeValue;
            }
            return $mixToReturn;
        }
        private function createXMLOpenTags($strApi)
        {
            $strToReturn = '<?xml version="1.0" encoding="UTF-8" ?>';
            $strToReturn .= sprintf('<%s xmlns:api="http://www.fedex.com/fsmapi" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="%s.xsd">',
                                    $strApi, $strApi);
            return $strToReturn;
        }
  
  }//end class
}//end define

?>