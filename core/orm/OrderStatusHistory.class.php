<?php
	require(__DATAGEN_CLASSES__ . '/OrderStatusHistoryGen.class.php');

	/**
	 * The OrderStatusHistory class defined here contains any
	 * customized code for the OrderStatusHistory class in the
	 * Object Relational Model.  It represents the "order_status_history" table 
	 * in the database, and extends from the code generated abstract OrderStatusHistoryGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */
	class OrderStatusHistory extends OrderStatusHistoryGen {
		/**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objOrderStatusHistory->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
			return sprintf('Order Status: %s - %s',  $this->strDate,  $this->intOrderId);
		}

        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Status':
                    return ($this->intStatusId ? OrderStatusType::ToString($this->intStatusId) : null);

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
                case 'Date':
                    try {
                        return ($this->strDate = QType::Cast($mixValue, QType::String));
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
        /**
         * Insert this OrderStatusHistory - this is to support setting the Date timestamp
         * @return void
         */
        public function InsertDated()
        {
            $objDatabase = OrderStatusHistory::GetDatabase();

            $strQuery = 'INSERT INTO `order_status_history` (
                            `order_id`,
                            `date`,
                            `notes`,
                            `status_id`
                        ) VALUES (
                            ' . $objDatabase->SqlVariable($this->intOrderId) . ',
                            ' . $objDatabase->SqlVariable($this->strDate) . ',
                            ' . $objDatabase->SqlVariable($this->strNotes) . ',
                            ' . $objDatabase->SqlVariable($this->intStatusId) . '
                        )';
                    
            try {
                    $objDatabase->NonQuery($strQuery);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->intId = OrderStatusHistory::GetDatabase()->InsertId('order_status_history', 'id');
            $this->__blnRestored = true;
            $this->Reload();
        }
		// Override or Create New Load/Count methods
		// (For obvious reasons, these methods are commented out...
		// but feel free to use these as a starting point)
/*
		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return an array of OrderStatusHistory objects
			return OrderStatusHistory::QueryArray(
				QQ::AndCondition(
					QQ::Equal(QQN::OrderStatusHistory()->Param1, $strParam1),
					QQ::GreaterThan(QQN::OrderStatusHistory()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a single OrderStatusHistory object
			return OrderStatusHistory::QuerySingle(
				QQ::AndCondition(
					QQ::Equal(QQN::OrderStatusHistory()->Param1, $strParam1),
					QQ::GreaterThan(QQN::OrderStatusHistory()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function CountBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a count of OrderStatusHistory objects
			return OrderStatusHistory::QueryCount(
				QQ::AndCondition(
					QQ::Equal(QQN::OrderStatusHistory()->Param1, $strParam1),
					QQ::Equal(QQN::OrderStatusHistory()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses) {
			// Performing the load manually (instead of using Qcodo Query)

			// Get the Database Object for this Class
			$objDatabase = OrderStatusHistory::GetDatabase();

			// Properly Escape All Input Parameters using Database->SqlVariable()
			$strParam1 = $objDatabase->SqlVariable($strParam1);
			$intParam2 = $objDatabase->SqlVariable($intParam2);

			// Setup the SQL Query
			$strQuery = sprintf('
				SELECT
					`order_status_history`.*
				FROM
					`order_status_history` AS `order_status_history`
				WHERE
					param_1 = %s AND
					param_2 < %s',
				$strParam1, $intParam2);

			// Perform the Query and Instantiate the Result
			$objDbResult = $objDatabase->Query($strQuery);
			return OrderStatusHistory::InstantiateDbResult($objDbResult);
		}
*/




		// Override or Create New Properties and Variables
		// For performance reasons, these variables and __set and __get override methods
		// are commented out.  But if you wish to implement or override any
		// of the data generated properties, please feel free to uncomment them.
/*
		protected $strSomeNewProperty;


*/
	}
?>