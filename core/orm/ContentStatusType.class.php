<?php
	require(__DATAGEN_CLASSES__ . '/ContentStatusTypeGen.class.php');

	/**
	 * The ContentStatusType class defined here contains any
	 * customized code for the ContentStatusType enumerated type. 
	 * 
	 * It represents the enumerated values found in the "content_status_type" table in the database,
	 * and extends from the code generated abstract ContentStatusTypeGen
	 * class, which contains all the values extracted from the database.
	 * 
	 * Type classes which are generally used to attach a type to data object.
	 * However, they may be used as simple database indepedant enumerated type.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 */
	abstract class ContentStatusType extends ContentStatusTypeGen {
	}
?>