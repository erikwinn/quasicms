<?php
	require(__DATAGEN_CLASSES__ . '/ContentBlockGen.class.php');

	/**
	 * The ContentBlock class defined here contains any
	 * customized code for the ContentBlock class in the
	 * Object Relational Model.  It represents the "content_block" table 
	 * in the database, and extends from the code generated abstract ContentBlockGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */

	class ContentBlock extends ContentBlockGen {
		/**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objContentBlock->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
			return sprintf('%s',  $this->Name);
		}
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Location':
                    return ($this->LocationId) ? BlockLocationType::$NameArray[$this->LocationId] : null;
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
//              case 'SomeNewProperty':
//                  try {
//                      return ($this->strSomeNewProperty = QType::Cast($mixValue, QType::String));
//                  } catch (QInvalidCastException $objExc) {
//                      $objExc->IncrementOffset();
//                      throw $objExc;
//                  }

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
			// This will return an array of ContentBlock objects
			return ContentBlock::QueryArray(
				QQ::AndCondition(
					QQ::Equal(QQN::ContentBlock()->Param1, $strParam1),
					QQ::GreaterThan(QQN::ContentBlock()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a single ContentBlock object
			return ContentBlock::QuerySingle(
				QQ::AndCondition(
					QQ::Equal(QQN::ContentBlock()->Param1, $strParam1),
					QQ::GreaterThan(QQN::ContentBlock()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function CountBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a count of ContentBlock objects
			return ContentBlock::QueryCount(
				QQ::AndCondition(
					QQ::Equal(QQN::ContentBlock()->Param1, $strParam1),
					QQ::Equal(QQN::ContentBlock()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses) {
			// Performing the load manually (instead of using Qcodo Query)

			// Get the Database Object for this Class
			$objDatabase = ContentBlock::GetDatabase();

			// Properly Escape All Input Parameters using Database->SqlVariable()
			$strParam1 = $objDatabase->SqlVariable($strParam1);
			$intParam2 = $objDatabase->SqlVariable($intParam2);

			// Setup the SQL Query
			$strQuery = sprintf('
				SELECT
					`content_block`.*
				FROM
					`content_block` AS `content_block`
				WHERE
					param_1 = %s AND
					param_2 < %s',
				$strParam1, $intParam2);

			// Perform the Query and Instantiate the Result
			$objDbResult = $objDatabase->Query($strQuery);
			return ContentBlock::InstantiateDbResult($objDbResult);
		}
*/

	}
?>