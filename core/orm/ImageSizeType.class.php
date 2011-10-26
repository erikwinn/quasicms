<?php
	require(__DATAGEN_CLASSES__ . '/ImageSizeTypeGen.class.php');

	/**
	 * The ImageSizeType class defined here contains any
	 * customized code for the ImageSizeType enumerated type. 
	 * 
	 * It represents the enumerated values found in the "image_size_type" table in the database,
	 * and extends from the code generated abstract ImageSizeTypeGen
	 * class, which contains all the values extracted from the database.
	 * 
	 * Type classes which are generally used to attach a type to data object.
	 * However, they may be used as simple database indepedant enumerated type.
	 * 
	 * @package Quasi
	 * @subpackage DataObjects
	 */
	abstract class ImageSizeType extends ImageSizeTypeGen {
	}
?>