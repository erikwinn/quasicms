<?php
	require(__DATAGEN_CLASSES__ . '/OrderChangeTypeGen.class.php');

	/**
	 * The OrderChangeType class defined here contains any
	 * customized code for the OrderChangeType enumerated type. 
	 * 
	 * It represents the enumerated values found in the "order_change_type" table in the database,
	 * and extends from the code generated abstract OrderChangeTypeGen
	 * class, which contains all the values extracted from the database.
	 * 
	 * Type classes which are generally used to attach a type to data object.
	 * However, they may be used as simple database indepedant enumerated type.
	 * 
	 * @package Quasi
	 * @subpackage DataObjects
	 */
	abstract class OrderChangeType extends OrderChangeTypeGen {
	}
?>