<?php
	require(__DATAGEN_CLASSES__ . '/AccountTypeGen.class.php');

	/**
	 * The AccountType class defined here contains any
	 * customized code for the AccountType enumerated type. 
	 * 
	 * It represents the enumerated values found in the "account_type" table in the database,
	 * and extends from the code generated abstract AccountTypeGen
	 * class, which contains all the values extracted from the database.
	 * 
	 * Type classes which are generally used to attach a type to data object.
	 * However, they may be used as simple database indepedant enumerated type.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 */
	abstract class AccountType extends AccountTypeGen {
	}
?>