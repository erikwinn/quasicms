<?php
	require(__DATAGEN_CLASSES__ . '/AddressGen.class.php');

	/**
	 * The Address class defined here contains any
	 * customized code for the Address class in the
	 * Object Relational Model.  It represents the "address" table 
	 * in the database, and extends from the code generated abstract AddressGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 * 
	 * @package Quasi Application
	 * @subpackage ORM
	 * 
	 */
	class Address extends AddressGen {
        /**
         * Protected member variable that maps to the database column address.zone_id
         * @var integer intZoneId
         */
        protected $intZoneId = 13;
        const ZoneIdDefault = 13;


        /**
         * Protected member variable that maps to the database column address.country_id
         * @var integer intCountryId
         */
        protected $intCountryId = 223;
        const CountryIdDefault = 223;

        /**
         * Protected member variable that maps to the database column address.is_current
         * @var boolean blnIsCurrent
         */
        protected $blnIsCurrent = true;
        const IsCurrentDefault = true;

        /**
         * Protected member variable that maps to the database column address.type_id
         * @var integer intTypeId
         */
        protected $intTypeId =1;
        const TypeIdDefault = 1;
		
        /**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objAddress->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString()
        {
                if(isset($this->Title))
                    return $this->Title;
                else
                    return "Primary address";
        }

        ///gettors
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Country':
                    return ($this->CountryId) ? CountryType::$NameArray[$this->CountryId] : null;
                case 'State':
                case 'Zone':
                    return ($this->ZoneId) ? ZoneType::$NameArray[$this->ZoneId] : null;
                case 'Type':
                    return ($this->TypeId) ? AddressType::$NameArray[$this->TypeId] : null;

                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }

        ///settors
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
                //experimental - not really used ..
                case 'Country':
                    if(! is_string($mixValue) )
                        throw new QCallerException('Set Address country - value must be a string.');
                    
                    $intCountryId = 0;
                    
                    foreach( CountryType::$NameArray as $intId => $strName )
                        if( $strName === $mixValue )
                            $intCountryId = $intId;
                            
                    if( 0 == $intCountryId)
                        throw new QCallerException('Set Address country - unknown country:' . $mixValue);
                    
                    try {
                        return ($this->CountryId = QType::Cast($intCountryId, QType::String));
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
                    

		// Override or Create New Load/Count methods
		// (For obvious reasons, these methods are commented out...
		// but feel free to use these as a starting point)
/*
		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return an array of Address objects
			return Address::QueryArray(
				QQ::AndCondition(
					QQ::Equal(QQN::Address()->Param1, $strParam1),
					QQ::GreaterThan(QQN::Address()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a single Address object
			return Address::QuerySingle(
				QQ::AndCondition(
					QQ::Equal(QQN::Address()->Param1, $strParam1),
					QQ::GreaterThan(QQN::Address()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function CountBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a count of Address objects
			return Address::QueryCount(
				QQ::AndCondition(
					QQ::Equal(QQN::Address()->Param1, $strParam1),
					QQ::Equal(QQN::Address()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses) {
			// Performing the load manually (instead of using Qcodo Query)

			// Get the Database Object for this Class
			$objDatabase = Address::GetDatabase();

			// Properly Escape All Input Parameters using Database->SqlVariable()
			$strParam1 = $objDatabase->SqlVariable($strParam1);
			$intParam2 = $objDatabase->SqlVariable($intParam2);

			// Setup the SQL Query
			$strQuery = sprintf('
				SELECT
					`address`.*
				FROM
					`address` AS `address`
				WHERE
					param_1 = %s AND
					param_2 < %s',
				$strParam1, $intParam2);

			// Perform the Query and Instantiate the Result
			$objDbResult = $objDatabase->Query($strQuery);
			return Address::InstantiateDbResult($objDbResult);
		}
*/

	}
?>