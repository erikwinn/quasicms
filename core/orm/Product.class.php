<?php
	require(__DATAGEN_CLASSES__ . '/ProductGen.class.php');

	/**
	 * The Product class defined here contains any
	 * customized code for the Product class in the
	 * Object Relational Model.  It represents the "product" table 
	 * in the database, and extends from the code generated abstract ProductGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */
	class Product extends ProductGen {
		/**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objProduct->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
			return sprintf('%s',  $this->Model);
		}

        public function InsertWithId()
        {
            $objDatabase = Product::GetDatabase();
            
            $strQuery = 'INSERT INTO `product` (
                        `id`,
                        `manufacturer_id`,
                        `supplier_id`,
                        `name`,
                        `model`,
                        `short_description`,
                        `long_description`,
                        `msrp`,
                        `wholesale_price`,
                        `retail_price`,
                        `cost`,
                        `weight`,
                        `height`,
                        `width`,
                        `depth`,
                        `is_virtual`,
                        `type_id`,
                        `status_id`,
                        `view_count`,
                        `user_permissions_id`,
                        `public_permissions_id`,
                        `group_permissions_id`';
            if( '' != $this->strCreationDate )
                $strQuery .= ',`creation_date`';
            $strQuery .= ') VALUES (
                        ' . $objDatabase->SqlVariable($this->intId) . ',
                        ' . $objDatabase->SqlVariable($this->intManufacturerId) . ',
                        ' . $objDatabase->SqlVariable($this->intSupplierId) . ',
                        ' . $objDatabase->SqlVariable($this->strName) . ',
                        ' . $objDatabase->SqlVariable($this->strModel) . ',
                        ' . $objDatabase->SqlVariable($this->strShortDescription) . ',
                        ' . $objDatabase->SqlVariable($this->strLongDescription) . ',
                        ' . $objDatabase->SqlVariable($this->fltMsrp) . ',
                        ' . $objDatabase->SqlVariable($this->fltWholesalePrice) . ',
                        ' . $objDatabase->SqlVariable($this->fltRetailPrice) . ',
                        ' . $objDatabase->SqlVariable($this->fltCost) . ',
                        ' . $objDatabase->SqlVariable($this->fltWeight) . ',
                        ' . $objDatabase->SqlVariable($this->fltHeight) . ',
                        ' . $objDatabase->SqlVariable($this->fltWidth) . ',
                        ' . $objDatabase->SqlVariable($this->fltDepth) . ',
                        ' . $objDatabase->SqlVariable($this->blnIsVirtual) . ',
                        ' . $objDatabase->SqlVariable($this->intTypeId) . ',
                        ' . $objDatabase->SqlVariable($this->intStatusId) . ',
                        ' . $objDatabase->SqlVariable($this->intViewCount) . ',
                        ' . $objDatabase->SqlVariable($this->intUserPermissionsId) . ',
                        ' . $objDatabase->SqlVariable($this->intPublicPermissionsId) . ',
                        ' . $objDatabase->SqlVariable($this->intGroupPermissionsId) ;
                if( '' != $this->strCreationDate )
                    $strQuery .= ', ' . $objDatabase->SqlVariable($this->strCreationDate);
                $strQuery .= ' )';
            try{
                $objDatabase->NonQuery($strQuery);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
             $this->__blnRestored = true;                   
        }
                
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'RetailPrice':
                    return number_format($this->fltRetailPrice, 2);
                
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
                case 'Id':
                    try {
                        return ($this->intId = QType::Cast($mixValue, QType::Integer));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'CreationDate':
                    try {
                        return ($this->strCreationDate = QType::Cast($mixValue, QType::String));
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
			// This will return an array of Product objects
			return Product::QueryArray(
				QQ::AndCondition(
					QQ::Equal(QQN::Product()->Param1, $strParam1),
					QQ::GreaterThan(QQN::Product()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a single Product object
			return Product::QuerySingle(
				QQ::AndCondition(
					QQ::Equal(QQN::Product()->Param1, $strParam1),
					QQ::GreaterThan(QQN::Product()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function CountBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a count of Product objects
			return Product::QueryCount(
				QQ::AndCondition(
					QQ::Equal(QQN::Product()->Param1, $strParam1),
					QQ::Equal(QQN::Product()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses) {
			// Performing the load manually (instead of using Qcodo Query)

			// Get the Database Object for this Class
			$objDatabase = Product::GetDatabase();

			// Properly Escape All Input Parameters using Database->SqlVariable()
			$strParam1 = $objDatabase->SqlVariable($strParam1);
			$intParam2 = $objDatabase->SqlVariable($intParam2);

			// Setup the SQL Query
			$strQuery = sprintf('
				SELECT
					`product`.*
				FROM
					`product` AS `product`
				WHERE
					param_1 = %s AND
					param_2 < %s',
				$strParam1, $intParam2);

			// Perform the Query and Instantiate the Result
			$objDbResult = $objDatabase->Query($strQuery);
			return Product::InstantiateDbResult($objDbResult);
		}
*/

	}
?>