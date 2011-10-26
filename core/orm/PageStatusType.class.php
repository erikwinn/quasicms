<?php
	require(__DATAGEN_CLASSES__ . '/PageStatusTypeGen.class.php');

	/**
	 * The PageStatusType class defined here contains any
	 * customized code for the PageStatusType enumerated type. 
	 * 
	 * It represents the enumerated values found in the "page_status_type" table in the database,
	 * and extends from the code generated abstract PageStatusTypeGen
	 * class, which contains all the values extracted from the database.
	 * 
	 * Type classes which are generally used to attach a type to data object.
	 * However, they may be used as simple database indepedant enumerated type.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 */
	abstract class PageStatusType extends PageStatusTypeGen {
	}
?>