<?php
	require(__DATAGEN_CLASSES__ . '/ZoneTypeGen.class.php');

	/**
	 * The ZoneType class defined here contains any
	 * customized code for the ZoneType enumerated type. 
	 * 
	 * It represents the enumerated values found in the "zone_type" table in the database,
	 * and extends from the code generated abstract ZoneTypeGen
	 * class, which contains all the values extracted from the database.
	 * 
	 * Type classes which are generally used to attach a type to data object.
	 * However, they may be used as simple database indepedant enumerated type.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 */
	abstract class ZoneType extends ZoneTypeGen
    {
        
        /**
        * Returns an array of Zones for a given country id or an empty array if none match the country
        * @param integer intCountryId
        * @return array - an array of zone ids 
        */
        public static function GetNameArrayByCountryId($intCountryId)
        {
            $aryZonesToReturn = array();
            foreach(self::$ExtraColumnValuesArray as $intZoneId => $aryValues)
                if($intCountryId == $aryValues['CountryId'])
                    $aryZonesToReturn[$intZoneId] = self::$NameArray[$intZoneId];
            return $aryZonesToReturn;
        }

        /**
        * Return the id for a Zone by its name
        * @param string strName - name for the zone
        * @return integer - the id of the given zone or ZoneType::NoZone 
        */
        public static function GetId($strName)
        {
            $intToReturn = self::NoZone;
            if(empty($strName))
                return $intToReturn;
                
            //look for exact match
            foreach( self::$NameArray as $intId => $m_Name )
                if( strtolower( $m_Name) == strtolower( $strName ) )
                {
                    $intToReturn = $intId;
                    break;
                }
             //check for abbreviations ..
             if($intToReturn == self::NoZone)
                foreach( ZoneType::$ExtraColumnValuesArray as $intId => $aryInfo )
                {
                    $strCode = $aryInfo['Code'];
                    if( strtolower( $strCode) == strtolower( $strName ) )
                        {
                            $intToReturn = $intId;
                            break;
                        }
                }
             //look for partial match ..
             if($intToReturn == self::NoZone)
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