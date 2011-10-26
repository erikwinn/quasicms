<?php
	require(__DATAGEN_CLASSES__ . '/PaypalTransactionGen.class.php');

	/**
	 * The PaypalTransaction class defined here contains any
	 * customized code for the PaypalTransaction class in the
	 * Object Relational Model.  It represents the "paypal_transaction" table 
	 * in the database, and extends from the code generated abstract PaypalTransactionGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */
	class PaypalTransaction extends PaypalTransactionGen {
		/**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objPaypalTransaction->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
			return sprintf('Transaction #%s',  $this->intId);
		}

//         protected $strSomeNewProperty;

        public function __get($strName)
        {
            switch ($strName)
            {
                case 'TimeStamp':
                    return $this->dttTimeStamp;

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
                case 'TimeStamp':
                    if(is_string($mixValue))
                        try {
                            return($this->dttTimeStamp = new QDateTime($mixValue));
                        } catch (QCallerException $objExc) {
                            $objExc->IncrementOffset();
                            throw $objExc;
                        }
                    else
                        try {
                            return ($this->dttTimeStamp = QType::Cast($mixValue, QType::DateTime));
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
			// This will return an array of PaypalTransaction objects
			return PaypalTransaction::QueryArray(
				QQ::AndCondition(
					QQ::Equal(QQN::PaypalTransaction()->Param1, $strParam1),
					QQ::GreaterThan(QQN::PaypalTransaction()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a single PaypalTransaction object
			return PaypalTransaction::QuerySingle(
				QQ::AndCondition(
					QQ::Equal(QQN::PaypalTransaction()->Param1, $strParam1),
					QQ::GreaterThan(QQN::PaypalTransaction()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function CountBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a count of PaypalTransaction objects
			return PaypalTransaction::QueryCount(
				QQ::AndCondition(
					QQ::Equal(QQN::PaypalTransaction()->Param1, $strParam1),
					QQ::Equal(QQN::PaypalTransaction()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses) {
			// Performing the load manually (instead of using Qcodo Query)

			// Get the Database Object for this Class
			$objDatabase = PaypalTransaction::GetDatabase();

			// Properly Escape All Input Parameters using Database->SqlVariable()
			$strParam1 = $objDatabase->SqlVariable($strParam1);
			$intParam2 = $objDatabase->SqlVariable($intParam2);

			// Setup the SQL Query
			$strQuery = sprintf('
				SELECT
					`paypal_transaction`.*
				FROM
					`paypal_transaction` AS `paypal_transaction`
				WHERE
					param_1 = %s AND
					param_2 < %s',
				$strParam1, $intParam2);

			// Perform the Query and Instantiate the Result
			$objDbResult = $objDatabase->Query($strQuery);
			return PaypalTransaction::InstantiateDbResult($objDbResult);
		}
*/

	}
?>