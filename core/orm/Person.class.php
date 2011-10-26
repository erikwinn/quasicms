<?php
	require(__DATAGEN_CLASSES__ . '/PersonGen.class.php');

	/**
	 * The Person class defined here contains any
	 * customized code for the Person class in the
	 * Object Relational Model.  It represents the "person" table 
	 * in the database, and extends from the code generated abstract PersonGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */
	class Person extends PersonGen {
        
        /**
         * Protected member variable that maps to the database PK Identity column person.id
         * @var integer intId
         */
        protected $intId;
        const IdDefault = null;


        /**
         * Protected member variable that maps to the database column person.address_id
         * @var integer intAddressId
         */
        protected $intAddressId;
        const AddressIdDefault = 1;

        /**
         * Protected member variable that maps to the database column person.is_virtual
         * @var boolean blnIsVirtual
         */
        protected $blnIsVirtual;
        const IsVirtualDefault = false;

   
		/**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objPerson->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
            return  $this->FirstName . ' ' . $this->LastName;
		}

        public function __get($strName)
        {
            switch ($strName)
            {
                case 'FullName':
                    return  $this->FirstName . ' ' . $this->LastName;
                case 'ProperName':
                    $strToReturn = '';
                    if('' != $this->NamePrefix )
                        $strToReturn .= $this->NamePrefix . ' ';
                    $strToReturn .= $this->FirstName . ' ';
                    if('' != $this->MiddleName )
                        $strToReturn .= $this->MiddleName . ' ';
                    $strToReturn .= $this->LastName. ' ';
                    if('' != $this->NameSuffix )
                        $strToReturn .= $this->NameSuffix . ' ';
                    return $strToReturn;
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
			// This will return an array of Person objects
			return Person::QueryArray(
				QQ::AndCondition(
					QQ::Equal(QQN::Person()->Param1, $strParam1),
					QQ::GreaterThan(QQN::Person()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a single Person object
			return Person::QuerySingle(
				QQ::AndCondition(
					QQ::Equal(QQN::Person()->Param1, $strParam1),
					QQ::GreaterThan(QQN::Person()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function CountBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a count of Person objects
			return Person::QueryCount(
				QQ::AndCondition(
					QQ::Equal(QQN::Person()->Param1, $strParam1),
					QQ::Equal(QQN::Person()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses) {
			// Performing the load manually (instead of using Qcodo Query)

			// Get the Database Object for this Class
			$objDatabase = Person::GetDatabase();

			// Properly Escape All Input Parameters using Database->SqlVariable()
			$strParam1 = $objDatabase->SqlVariable($strParam1);
			$intParam2 = $objDatabase->SqlVariable($intParam2);

			// Setup the SQL Query
			$strQuery = sprintf('
				SELECT
					`person`.*
				FROM
					`person` AS `person`
				WHERE
					param_1 = %s AND
					param_2 < %s',
				$strParam1, $intParam2);

			// Perform the Query and Instantiate the Result
			$objDbResult = $objDatabase->Query($strQuery);
			return Person::InstantiateDbResult($objDbResult);
		}
*/


	}
?>