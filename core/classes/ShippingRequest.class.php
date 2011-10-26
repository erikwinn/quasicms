<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/

if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("SHIPPINGREQUESTBASE.CLASS.PHP")){
define("SHIPPINGREQUESTBASE.CLASS.PHP",1);

/**
* Class ShippingRequestType - enumerator class for types of shipping requests
*@package Quasi
* @subpackage Classes
*/
class ShippingRequestType
{
    ///@var const - a request for a rate
    const Rate = 1;
    ///@var const - a request for a rate availability
    const Availability = 2;
    ///@var const - a request for a printable label image
    const  Label = 3;
    ///@var const - a request for the status of the account
    const  AccountStatus = 4;
    ///@var const - a request to make a credit payment
    const  CreditAccount = 5;
                
    public static function ToString($intFlag)
    {
        switch($intFlag)
        {
            case ShippingRequestType::Label:
                return 'LabelRequest';
            case ShippingRequestType::Availability:
                return 'AvailabilityRequest';
            case ShippingRequestType::Rate:
                return 'RateRequest';
            case ShippingRequestType::AccountStatus:
                return 'AccountStatusRequest';
            case ShippingRequestType::CreditAccount:
                return 'CreditAccountRequest';
            default:
                return 'None';
        }
    }
}

/**
* Class ExtraDocumentImage - convenience class for extra customs image documents
*@package Quasi
* @subpackage Classes
*/
class ExtraDocumentImage
{
    public $Copies;
    public $Image;
    public $Type;
    public function __construct($mixImage, $strType, $intCopies=1)
    {
        $this->Image = $mixImage;
        $this->Type = $strType;
        $this->Copies = $intCopies;
    }
}

/**
* Class ShippingRequest - base class for performing shipping requests via Shipping API web services
*
* This class provides an interface to shipping requests. Abstract methods MUST be implemented by
* subclasses, others may be overridden as needed.  Subclasses are instantiated by a call to
* ShippingMethod::GetRequest($strRequestType). The primary function of this base class is to associate
* the request with the ShippingMethod
* 
* This class also connects to the specified server via the methods provided by extending WebServiceRequest.
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: ShippingRequest.class.php 502 2009-02-10 22:09:53Z erikwinn $
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

 abstract class ShippingRequest extends WebServiceRequest
 {
        /**
        *@var ShippingMethod method that created this calculator
        */
        protected $objShippingMethod;
        /**
        *@var ShippingRequestType - the type of request to create
        */
        protected $intShippingRequestType;
        /**
        * An array of CustomsInformation objects containing data for customs
        * declarations
        *@var array aryCustomsInformation
        */
        protected $aryCustomsInformation = null;
        /**
        * An array of extra document images for customs declarations
        *@var array aryExtraDocumentImages
        */
        protected $aryExtraDocumentImages;
        /**
        *@var resource objShippingLabelImage - the shipping label image as GD image resource
        */
        protected $objShippingLabelImage;
        /**
        *@var array aryCustomsFormImages - image(s) for printing out customs forms ..
        */
        protected $aryCustomsFormImages;
        
        /**
        *@var boolean blnIsAvailable - flag indicating if the method is avaiable for the address in Order ..
        */
        protected $blnIsAvailable;
                    
        /**
        * ShippingRequest Constructor
        *
        * This sets some defaults for the shipping requests.
        *
        *NOTE: It is assumed that the shipping origin is the same as the store address.
        *@todo  Support remote shipping options .. 
        *
        * @param ShippingMethod objShippingMethod - the method to be used for the request
        */
        public function __construct(ShippingMethod $objShippingMethod)
        {
            $this->objShippingMethod =& $objShippingMethod;
            $this->blnTestMode = $objShippingMethod->TestMode;           
        }
        /**
        * Connects to web service and submits the request. Note that
        * this function merely constructs a request URL from internal variables
        * that are set in createRequest, it may therefor contain a GET query
        * string or a POST depending on the subclass requirements.
        * Note: If the request transaction succeeds we also call handleResponse here.
        *@return boolean true on success
        */
        protected function submitRequest()
        {
            if(! parent::submitRequest())
                return false;
            if(! $this->handleResponse())
                return false;
            return true;
        }
        /**
        * This function attempts to create a request string for the method of the given ShippingRequestType.
        * The WebRequestType (POST, GET, etc ..) may also be optionally set here.
        * 
        * @param ShippingRequestType intShippingRequestType - the type of shipping request object to create
        * @param WebRequestType intWebRequestType - the type of web request to submit
        */
        protected function createRequest($intShippingRequestType, $intWebRequestType = WebRequestType::POST)
        {
            $this->intShippingRequestType = $intShippingRequestType;
            parent::createRequest($intWebRequestType);
            
/*            switch($intShippingRequestType)
            {
                case ShippingRequestType::Label:
                    return $this->createLabelRequest();
                    break;
                case ShippingRequestType::Availability:
                    return $this->createAvailabilityRequest();
                    break;
                case ShippingRequestType::Rate:
                    return $this->createRateRequest();
                    break;
                case ShippingRequestType::AccountStatus:
                    return $this->createAccountStatusRequest();
                    break;
                case ShippingRequestType::CreditAccount:
                    return $this->createCreditAccountRequest();
                    break;
                default:
                    throw new QCallerException('Shipping request type unsupported: ' . $intRequestType );
            }*/
        }
        /**
        * This function attempts to create a POST request string for the method of a given ShippingRequestType.
        */
        protected function createPOSTRequest()
        {            
            switch($this->intShippingRequestType)
            {
                case ShippingRequestType::Label:
                    return $this->createLabelRequest();
                    break;
                case ShippingRequestType::Availability:
                    return $this->createAvailabilityRequest();
                    break;
                case ShippingRequestType::Rate:
                    return $this->createRateRequest();
                    break;
                case ShippingRequestType::AccountStatus:
                    return $this->createAccountStatusRequest();
                    break;
                case ShippingRequestType::CreditAccount:
                    return $this->createCreditAccountRequest();
                    break;
                default:
                    throw new QCallerException('Shipping request type unsupported: ' . $this->intRequestType );
            }
        }
        /**
        * This function attempts to create a GET request string for the method of a given ShippingRequestType.
        */
        protected function createGETRequest()
        {            
            switch($this->intShippingRequestType)
            {
                case ShippingRequestType::Label:
                    return $this->createLabelRequest();
                    break;
                case ShippingRequestType::Availability:
                    return $this->createAvailabilityRequest();
                    break;
                case ShippingRequestType::Rate:
                    return $this->createRateRequest();
                    break;
                case ShippingRequestType::AccountStatus:
                    return $this->createAccountStatusRequest();
                    break;
                case ShippingRequestType::CreditAccount:
                    return $this->createCreditAccountRequest();
                    break;
                default:
                    throw new QCallerException('Shipping request type unsupported: ' . $this->intRequestType );
            }
        }
        
        /**
        * This function handles the response string for the method of a given ShippingRequestType.
        */
        protected function handleResponse()
        {            
            switch($this->intShippingRequestType)
            {
                case ShippingRequestType::Label:
                    return $this->handleLabelResponse();
                    break;
                case ShippingRequestType::Availability:
                    return $this->handleAvailabilityResponse();
                    break;
                case ShippingRequestType::Rate:
                    return $this->handleRateResponse();
                    break;
                case ShippingRequestType::AccountStatus:
                    return $this->handleAccountStatusResponse();
                    break;
                case ShippingRequestType::CreditAccount:
                    return $this->handleCreditAccountResponse();
                    break;
                default:
                    throw new QCallerException('Shipping request type unsupported: ' . $this->intRequestType );
            }
        }
        protected function initCustomsInformationArray()
        {
            $this->aryCustomsInformation = array();
            $aryOrderItems = OrderItem::LoadArrayByOrderId($this->objShippingMethod->Order->Id);
            foreach($aryOrderItems as $objOrderItem)
            {
                $objCustomsInfo = new CustomsInformation();
                $objCustomsInfo->Quantity = $objOrderItem->Quantity;
                $objCustomsInfo->Description = $objOrderItem->Product->Name;
                $objCustomsInfo->Weight = $objOrderItem->Product->Weight * $objOrderItem->Quantity;
                $objCustomsInfo->Value = $objOrderItem->Product->RetailPrice * $objOrderItem->Quantity;
                $this->aryCustomsInformation[] = $objCustomsInfo;
            }
        }
        /**
        * Returns a rate for this method to the order address
        *@return float containing the rate for the order address
        */
        abstract public function GetRate();
        /**
        * Returns a shipping label image suitable for printing
        *@return string containing the image code
        */
        abstract public function GetLabel();
        /**
        * Returns an account status report
        *@return string containing the status report
        */
        abstract public function GetAccountStatus();
        /**
        * Returns whether this method is available for the order address
        *@return boolean true if method is available
        */
        abstract public function GetAvailability();
        /**
        * Submits an account credit payment
        */
//        abstract public function CreditAccount();

        //Request string creators
        /**
        * Creates a rate request 
        */
        abstract protected function createRateRequest();
        /**
        * Creates a label image request
        */
        abstract protected function createLabelRequest();
        /**
        * Creates an account status request
        */
        abstract protected function createAccountStatusRequest();
        /**
        * Creates a request submitting an account credit payment 
        */
        abstract protected function createCreditAccountRequest();
        /**
        * Creates a method available request
        */
        abstract protected function createAvailabilityRequest();

        //Response handlers
        /**
        * Creates a rate request 
        */
        abstract protected function handleRateResponse();
        /**
        * Creates a label image request
        */
        abstract protected function handleLabelResponse();
        /**
        * Creates an account status request
        */
        abstract protected function handleAccountStatusResponse();
        /**
        * Creates a request submitting an account credit payment 
        */
        abstract protected function handleCreditAccountResponse();
        /**
        * Creates a method available request
        */
        abstract protected function handleAvailabilityResponse();

        ///Gettors
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'ShippingLabelImage':
                    return $this->objShippingLabelImage;
                case 'ExtraDocumentImages':
                    return (array) $this->aryExtraDocumentImages;
                case 'CustomsFormImages':
                    return (array) $this->aryCustomsFormImages;
                case 'IsAvailable':
                    return $this->blnIsAvailable;
                case 'Order':
                    return $this->objShippingMethod->Order ;
                case 'Carrier':
                    return $this->objShippingMethod->Carrier ;
                case 'ServiceType':
                    return $this->objShippingMethod->ServiceType ;
                case 'Rate':
                    return $this->objShippingMethod->Rate ;
                case 'TotalValue':
                    return $this->objShippingMethod->Order->ProductTotalCharged;
                case 'Pounds':
                    return $this->objShippingMethod->Pounds ;
                case 'Ounces':
                    return $this->objShippingMethod->Ounces ;
                case 'Container':
                    return $this->objShippingMethod->Container ;
                case 'IsMachinable':
                    return $this->objShippingMethod->IsMachinable ;
                case 'OriginZip':
                    return $this->objShippingMethod->OriginZip ;
                case 'DestinationZip':
                    return $this->objShippingMethod->DestinationZip ;
                case 'DestinationCountryId':
                    return $this->objShippingMethod->DestinationCountryId ;
                case 'OriginCountryId':
                    return $this->objShippingMethod->OriginCountryId ;
                case 'OriginStateId':
                case 'OriginZoneId':
                    return $this->objShippingMethod->OriginZoneId ;
                case 'DestinationStateId':
                case 'DestinationZoneId':
                    return $this->objShippingMethod->DestinationZoneId ;
                ///string representation of country and state names ..
                case 'DestinationCountry':
                    return $this->objShippingMethod->DestinationCountry ;
                case 'OriginCountry':
                    return $this->objShippingMethod->OriginCountry ;
                case 'OriginState':
                case 'OriginZone':
                    return $this->objShippingMethod->OriginZone ;
                case 'DestinationState':
                case 'DestinationZone':
                    return $this->objShippingMethod->DestinationZone ;
                ///string representation of country and state ISO 2 letter codes ..
                case 'DestinationCountryCode':
                    return $this->objShippingMethod->DestinationCountryCode ;
                case 'OriginCountryCode':
                    return $this->objShippingMethod->OriginCountryCode ;
                case 'OriginStateCode':
                case 'OriginZoneCode':
                    return $this->objShippingMethod->OriginZoneCode ;
                case 'DestinationStateCode':
                case 'DestinationZoneCode':
                    return $this->objShippingMethod->DestinationZoneCode ;
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
                case 'DestinationZip':
                        return ($this->objShippingMethod->DestinationZip = $mixValue);
                case 'OriginZip':
                        return ($this->objShippingMethod->OriginZip = $mixValue);                
                case 'DestinationCountryId':
                        return ($this->objShippingMethod->DestinationCountryId = $mixValue);
                case 'OriginCountryId':
                        return ($this->objShippingMethod->OriginCountryId = $mixValue);
                case 'Container':
                        return ($this->objShippingMethod->Container = $mixValue);
                case 'Pounds':
                        return ($this->objShippingMethod->Pounds = $mixValue);
                case 'Rate':
                        return ($this->objShippingMethod->Rate = $mixValue);
                case 'Ounces':
                        return ($this->objShippingMethod->Ounces = $mixValue);
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