<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/
if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("CUSTOMSINFORMATION.CLASS.PHP")){
define("CUSTOMSINFORMATION.CLASS.PHP",1);

/**
* Class CustomsInformation - container for information in a customs declaration for a line item
* 
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: CustomsInformation.class.php 354 2008-11-21 05:42:08Z erikwinn $
*@version 0.1
*
*@copyright (C) 2008 by Erik Winn
*@license GPL v.2

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111 USA

*
*@package Quasi
* @subpackage CMS
*/

    class CustomsInformation 
    {
        /**
        *@var string strDescription - describes the Item
        */
        protected $strDescription;
        /**
        *@var integer intQuantity ..
        */
        protected $intQuantity;
        /**
        *@var float fltWeight - item weight in ounces (aggregate).
        */
        protected $fltWeight;
        /**
        *@var float fltValue - normally the final sale price of the item (aggregate)
        */
        protected $fltValue;
        /**
        * Defaults to the store address ..
        *@var string strOriginCountry - the country of origin for the shipment
        */
        protected $strOriginCountry = STORE_COUNTRY;
                
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Description':
                    return $this->strDescription ;
                case 'OriginCountry':
                    return $this->strOriginCountry ;
                case 'Quantity':
                    return $this->intQuantity ;
                case 'Weight':
                    return $this->fltWeight ;
                case 'Value':
                    return $this->fltValue ;
                default:
                    throw new QCallerException('CustomsInformation::__get() Unknown property: ' . $strName);                
            }
        }
        
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
                case 'Description':
                    try {
                        return ($this->strDescription = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'OriginCountry':
                    try {
                        return ($this->strOriginCountry = QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Quantity':
                    try {
                        return ($this->intQuantity = QType::Cast($mixValue, QType::Integer ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Value':
                    try {
                        return ($this->fltValue = QType::Cast($mixValue, QType::Float ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Weight':
                    try {
                        return ($this->fltWeight = QType::Cast($mixValue, QType::Float ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                default:
                        throw new QCallerException('CustomsInformation::__get() Unknown property: ' . $strName);
/*                    try {
                        return (parent::__set($strName, $mixValue));
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }*/
            }
        }
    
    }//end class
}//end define

?>