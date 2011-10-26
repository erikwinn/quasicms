<?php
	require(__DATAGEN_CLASSES__ . '/CountryTypeGen.class.php');

	/**
	 * The CountryType class defined here contains any
	 * customized code for the CountryType enumerated type. 
	 * 
	 * It represents the enumerated values found in the "country_type" table in the database,
	 * and extends from the code generated abstract CountryTypeGen
	 * class, which contains all the values extracted from the database.
	 * 
	 * Type classes which are generally used to attach a type to data object.
	 * However, they may be used as simple database indepedant enumerated type.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 */
	abstract class CountryType extends CountryTypeGen
    {
        public static function GetId($strName)
        {
            $intToReturn = self::World;
            //look for exact match
            foreach( self::$NameArray as $intId => $m_Name )
                if( strtolower( $m_Name) == strtolower( $strName ) )
                {
                    $intToReturn = $intId;
                    break;
                }
             //check for abbreviations ..
             if($intToReturn == self::World)
                foreach( CountryType::$ExtraColumnValuesArray as $intId => $aryInfo )
                {
                    if( strtolower($strName) == strtolower($aryInfo['IsoCode2'])
                        || strtolower($strName) == strtolower($aryInfo['IsoCode3']) )
                    {
                        $intToReturn = $intId;
                        break;
                    }
                }
             //look for partial match ..
             if($intToReturn == self::World)
                foreach( self::$NameArray as $intId => $m_Name )
                    if( false !== stripos( $m_Name, $strName ) )
                    {
                        $intToReturn = $intId;
                        break;
                    }
            return $intToReturn;
        }       
	}
?>