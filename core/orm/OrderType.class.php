<?php
    require(__DATAGEN_CLASSES__ . '/OrderTypeGen.class.php');

    /**
     * The OrderType class defined here contains any
     * customized code for the OrderType enumerated type. 
     * 
     * It represents the enumerated values found in the "order_type" table in the database,
     * and extends from the code generated abstract OrderTypeGen
     * class, which contains all the values extracted from the database.
     * 
     * Type classes which are generally used to attach a type to data object.
     * However, they may be used as simple database indepedant enumerated type.
     * 
     * @package Quasi
     * @subpackage DataObjects
     */
    abstract class OrderType extends OrderTypeGen {
    }
?>