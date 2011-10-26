<?php
	require(__DATAGEN_CLASSES__ . '/ShippingMethodGen.class.php');

	/**
	 *  The ShippingMethod class defined here contains all the application
	 * context depedant code for the ShippingMethod ORM object.  It represents the
     * "shipping_method" table in the database, and extends from the code generated
     * abstract ShippingMethodGen. Additionally it contains data members and functions
     * to support individual order shipping.
     *
     * The actual ShippingRequest object appropriate to the specific method is instantiated
     * for WebEnabled methods according to the values for this method. If the method has
     * a class_name field set to "NoClass" a default rate is set (DEFAULT_SHIPPING_CHARGE)
     * and no Implementation is created (this is a "local pick up" option in the check out ).
     *
     * Users of this class should check the flag WebEnabled to determine if the Get*() methods
     * are of use - GetRate may be called regardless as it will simply return the default rate.
     *
     * Basic Usage:
     *  Get estimate - 
     *   $objMethod = ShippingMethod::Load($intId);
     *   $objMethod->Init($objOrder);
     *   $fltRate = $objMethod->GetRate();
     *   $strImage = $objMethod->GetLabel();
	 *
     * @todo - some of the way sizing is dealt with only works for thin things (eg. books, PCBs),
     *  and multiple packages are not handled. this needs work and perhaps some refactoring of design ..
     *   
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */
	class ShippingMethod extends ShippingMethodGen
    {
        /**
        * This is used to store a reference to the order being shipped
        * @var object objOrder
        */
        private $objOrder = null;
       /**
        * An array of CustomsInformation objects
        * @var array aryCustomsInformation
        */
        private $aryCustomsInformation;
       /**
        * This is used to store an instance of the class used to implement this method.
        * The implementation extends ShippingRequest and performs the actual requests. It may
        * also delegate tasks to other ShippingRequest classes as needed (eg. USPSRequest may
        * use EndiciaRequest to create a label ..)
        * @var object objImplementation - implementation class for method
        */
        private $objImplementation = null;                  
        /**
        * Flag for whether this module can make web requests - Local PickUp for example
        * does not
        * @var boolean blnWebEnabled
        */
        private $blnWebEnabled;
        /**
        * This is used by the ShippingModule to record the rate returned by the calculator for a particular
        * order address and weight.
        * @var float fltRate - the rate to be charged for this method and order
        */
        private $fltRate;
                
        ///Package data .. Most of this is set in Init(objOrder) from the data in the order.
        /**
        *@var string Used to indicate special containers - optional, defaults to empty string
        */
        protected $strContainer = '';
        /**
        *@var integer Weight in pounds
        */
        protected $intPounds=0;
        /**
        *@var float Weight in ounces
        */
        protected $fltOunces=5;
        /**
        * Corresponds to Z Axis
        *@var integer Height in inches
        */
        protected $intHeight;
        /**
        * Corresponds to Y Axis
        *@var integer Length in inches
        */
        protected $intLength;
        /**
        * Corresponds to X Axis
        *@var integer Width in inches
        */
        protected $intWidth;
        /**
        *@var integer Girth in inches
        */
        protected $intGirth;
        /**
        *@var bool True if the package may be machine processed
        *@warning - this should be configurable ..
        */
        protected $blnIsMachinable = true;
        
                 /// Routing data ..
        /**
        *@var integer the desination country
        */
        protected $intDestinationCountryId;
        /**
        *@var integer the origin country
        */
        protected $intOriginCountryId;
        /**
        *@var integer the desination state or province
        */
        protected $intDestinationZoneId;
        /**
        *@var integer the origin state or province
        */
        protected $intOriginZoneId;
        /**
        * This is used to store the destination postal code
        * @var string strDestinationZip
        */
        protected $strDestinationZip;
        /**
        * This is used to store the origin postal code
        * @var string strOriginZip
        */
        protected $strOriginZip;
                
        /**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objShippingMethod->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
			return sprintf('%s %s',  $this->Carrier, $this->ServiceType);
		}
        /**
        * This function initializes the shipping method with values for the specific order, eg. addresses
        * weight, dimensions, etc. For WebEnabled methods the Implementation is created here.
        *
        *@param Order objOrder - the order to be shipped ..
        *@return boolean - true if implementation exists
        */
        public function Init( Order $objOrder )
        {
            if( '' == $this->ClassName)
                throw new QCallerException(sprintf('Empty ShippingMethod Class for %s', $this->__toString() ) );
                
            //Some shipping methods do not require calculation, like in store PickUp ..
            if( false !== stripos( 'NoClass', $this->ClassName) )
                $this->blnWebEnabled = false;
            else
            {
                $strClassName = $this->ClassName . 'Request';
                if( class_exists($strClassName)  )
                {
                    $this->objImplementation = new $strClassName($this);
                    $this->blnWebEnabled = true;
                }
                else
                    throw new QCallerException(sprintf('ShippingMethod Class "%s" for "%s" was not found!', $strClassName, $this->__toString() ) );
            }
                
            $this->objOrder = $objOrder;
            
            $this->fltRate = DEFAULT_SHIPPING_RATE;
            $this->strOriginZip = STORE_POSTAL_CODE;
            
            ///@todo - fixme, configure Origin somehow, temporary fix ..
            $this->intOriginCountryId = 223; //usa ..
            $this->intOriginZoneId = 13; //colorado ..
            
            $this->strDestinationZip = substr($objOrder->ShippingPostalCode, 0, 5 );
            $this->intDestinationCountryId = $objOrder->ShippingCountryId;
            $this->intDestinationZoneId = $objOrder->ShippingZoneId;
            if(! $this->TestMode )
            {
                $this->Ounces = $objOrder->TotalOunces;
                $this->Pounds = $objOrder->TotalPounds;
                $this->Width = $objOrder->XAxisSize;
                $this->Length = $objOrder->YAxisSize;
                $this->Height = $objOrder->ZAxisSize;
            }
            return true;  
        }
                
        /**
        * This function returns a rate for the method. You must call Init(objOrder) before using this.
        * Note: if you define class_name as "NoClass" it will return the default (DEFAULT_SHIPPING_RATE)
        * This is to support methods that do not require calculation (eg. local pick up ..).
        * @return float rate for the method/order 
        */
        public function GetRate()
        {
            if( ! $this->blnWebEnabled)
                return $this->fltRate;
            if(!$this->objImplementation)
                throw new QCallerException('ShippingMethod::GetRate - Implementation uninitialized, you must call Init(objOrder) first!');
            try {
                $this->fltRate = $this->objImplementation->GetRate();
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }                
            return $this->fltRate;
        }
        /**
        * This function attempts to return a shipping label image for the method.
        * You must call Init(objOrder) before using this.
        * Note: if you define class_name as "NoClass" it will return null -
        * this is to support methods that do not require calculation (eg. local pick up ..).
        * @return  image object for the method/order (or null)
        */
        public function GetShippingLabel()
        {
            if('NoClass' == $this->ClassName )
                return null;
            if(!$this->objImplementation)
                throw new QCallerException('ShippingMethod::GetRate - Implementation uninitialized, you must call Init(objOrder) first!');
            try {
                return $this->objImplementation->GetLabel();
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }                
        }
                
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'WebEnabled':
                    return $this->blnWebEnabled;
                case 'CustomsFormImages':
                    if( false !== stripos( 'NoClass', $this->ClassName) )
                        return null;
                    if( isset($this->objImplementation) )
                        return $this->objImplementation->CustomsFormImages;
                    return null;
                case 'ExtraDocumentImages':
                    if( false !== stripos( 'NoClass', $this->ClassName) )
                        return null;
                    if( isset($this->objImplementation) )
                        return $this->objImplementation->ExtraDocumentImages;
                    return null;
                case 'IsAvailable':
                    if( false !== stripos( 'NoClass', $this->ClassName) )
                        return true;
                    if( isset($this->objImplementation) )
                        return $this->objImplementation->IsAvailable;
                    return false;
                case 'HasErrors':
                    if( isset($this->objImplementation) )
                        return $this->objImplementation->HasErrors;
                    return false;
                case 'Errors':
                    if( isset($this->objImplementation) )
                        return $this->objImplementation->Errors;
                    return '';
                case 'IsMachinable':
                        return $this->blnIsMachinable;
                case 'Order':
                    return $this->objOrder;
                case 'OrderId':
                    return $this->objOrder->Id;
                case 'Rate':
                    return $this->fltRate;
                case 'Ounces':
                    return $this->fltOunces;
                case 'Pounds':
                    return $this->intPounds;
                case 'DestinationZip':
                    return $this->strDestinationZip;
                case 'OriginZip':
                    return $this->strOriginZip;
                case 'Container':
                    return $this->strContainer;
                case 'DestinationCountryId':
                    return $this->intDestinationCountryId;
                case 'DestinationStateId':
                case 'DestinationProvinceId':
                case 'DestinationZoneId':
                    return $this->intDestinationZoneId;                
                case 'OriginCountryId':
                    return $this->intOriginCountryId;
                case 'OriginStateId':
                case 'OriginProvinceId':
                case 'OriginZoneId':
                    return $this->intOriginZoneId;                
                ///string representation of country and state names ..
                case 'DestinationCountry':
                    return ($this->DestinationCountryId) ? CountryType::$NameArray[$this->DestinationCountryId] : null;
                case 'DestinationState':
                case 'DestinationProvince':
                case 'DestinationZone':
                    return ($this->DestinationZoneId) ? ZoneType::$NameArray[$this->DestinationZoneId] : null;                
                case 'OriginCountry':
                    return ($this->OriginCountryId) ? CountryType::$NameArray[$this->OriginCountryId] : null;
                case 'OriginState':
                case 'OriginProvince':
                case 'OriginZone':
                    return ($this->OriginZoneId) ? ZoneType::$NameArray[$this->OriginZoneId] : null;                
                ///string representation of country and state ISO 2 letter codes ..
                case 'DestinationCountryCode':
                    return ($this->DestinationCountryId) ? CountryType::ToIsoCode2($this->DestinationCountryId) : null;
                case 'DestinationStateCode':
                case 'DestinationProvinceCode':
                case 'DestinationZoneCode':
                    return ($this->DestinationZoneId) ? ZoneType::ToCode($this->DestinationZoneId) : null;
                case 'OriginCountryCode':
                    return ($this->OriginCountryId) ? CountryType::ToIsoCode2($this->OriginCountryId) : null;
                case 'OriginStateCode':
                case 'OriginProvinceCode':
                case 'OriginZoneCode':
                    return ($this->OriginZoneId) ? ZoneType::ToCode($this->OriginZoneId) : null;

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
                case 'Rate':
                    try {
                        return ($this->fltRate = QType::Cast($mixValue, QType::Float));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Ounces':
                    try {
                        return ($this->fltOunces = QType::Cast($mixValue, QType::Float));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Pounds':
                    try {
                        return ($this->intPounds = QType::Cast($mixValue, QType::Integer));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'DestinationZip':
                    try {
                        return ($this->strDestinationZip = QType::Cast($mixValue, QType::String));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'OriginZip':
                    try {
                        return ($this->strOriginZip = QType::Cast($mixValue, QType::String));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'OriginCountryId':
                    try {
                        return ($this->intOriginCountryId = QType::Cast($mixValue, QType::Integer));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'DestinationCountryId':
                    try {
                        return ($this->intDestinationCountryId = QType::Cast($mixValue, QType::Integer));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'OriginStateId':
                case 'OriginProvinceId':
                case 'OriginZoneId':
                    try {
                        return ($this->intOriginZoneId = QType::Cast($mixValue, QType::Integer));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'DestinationStateId':
                case 'DestinationProvinceId':
                case 'DestinationZoneId':
                    try {
                        return ($this->intDestinationZoneId = QType::Cast($mixValue, QType::Integer));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Container':
                    try {
                        return ($this->strContainer = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
/*                case 'Size':
                    try {
                        return ($this->strSize = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }*/
                case 'Height':
                    try {
                        return ($this->intHeight = QType::Cast(ceil($mixValue), QType::Integer ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Width':
                    try {
                        return ($this->intWidth = QType::Cast(ceil($mixValue), QType::Integer ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Length':
                    try {
                        return ($this->intLength = QType::Cast(ceil($mixValue), QType::Integer ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Girth':
                    try {
                        return ($this->intGirth = QType::Cast(ceil($mixValue), QType::Integer ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Pounds':
                    try {
                        return ($this->intPounds = QType::Cast($mixValue, QType::Integer ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Ounces':
                    try {
                        return ($this->fltOunces = QType::Cast($mixValue, QType::Float ));
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
	}
?>