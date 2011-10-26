<?php
	require(__DATAGEN_CLASSES__ . '/ContentItemGen.class.php');

	/**
	 * The ContentItem class defined here contains any
	 * customized code for the ContentItem class in the
	 * Object Relational Model.  It represents the "content_item" table 
	 * in the database, and extends from the code generated abstract ContentItemGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */
	class ContentItem extends ContentItemGen {
		/**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
			return sprintf('%s',  $this->Name );
		}


		// Override or Create New Load/Count methods
		// (For obvious reasons, these methods are commented out...
		// but feel free to use these as a starting point)
/*
		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return an array of ContentItem objects
			return ContentItem::QueryArray(
				QQ::AndCondition(
					QQ::Equal(QQN::ContentItem()->Param1, $strParam1),
					QQ::GreaterThan(QQN::ContentItem()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a single ContentItem object
			return ContentItem::QuerySingle(
				QQ::AndCondition(
					QQ::Equal(QQN::ContentItem()->Param1, $strParam1),
					QQ::GreaterThan(QQN::ContentItem()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function CountBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a count of ContentItem objects
			return ContentItem::QueryCount(
				QQ::AndCondition(
					QQ::Equal(QQN::ContentItem()->Param1, $strParam1),
					QQ::Equal(QQN::ContentItem()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses) {
			// Performing the load manually (instead of using Qcodo Query)

			// Get the Database Object for this Class
			$objDatabase = ContentItem::GetDatabase();

			// Properly Escape All Input Parameters using Database->SqlVariable()
			$strParam1 = $objDatabase->SqlVariable($strParam1);
			$intParam2 = $objDatabase->SqlVariable($intParam2);

			// Setup the SQL Query
			$strQuery = sprintf('
				SELECT
					`content_item`.*
				FROM
					`content_item` AS `content_item`
				WHERE
					param_1 = %s AND
					param_2 < %s',
				$strParam1, $intParam2);

			// Perform the Query and Instantiate the Result
			$objDbResult = $objDatabase->Query($strQuery);
			return ContentItem::InstantiateDbResult($objDbResult);
		}
*/

		// Override or Create New Properties and Variables
		// For performance reasons, these variables and __set and __get override methods
		// are commented out.  But if you wish to implement or override any
		// of the data generated properties, please feel free to uncomment them.

		protected $strSomeNewProperty;

		public function __get($strName) {
            switch ($strName)
            {
                case 'Type':
                    return ($this->TypeId) ? ContentType::$NameArray[$this->TypeId] : null;
                case 'ShowTitle':
                    return $this->blnShowTitle ;
                case 'ShowDescription':
                    return $this->blnShowDescription ;
                case 'ShowCreator':
                    return $this->blnShowCreator ;
                case 'ShowCreationDate':
                    return $this->blnShowCreationDate ;
                case 'ShowLastModification':
                    return $this->blnShowLastModification ;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
		}

/*
		public function __set($strName, $mixValue) {
			switch ($strName) {
				case 'SomeNewProperty':
					try {
						return ($this->strSomeNewProperty = QType::Cast($mixValue, QType::String));
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
*/
	}
?>