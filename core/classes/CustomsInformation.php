<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/
if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("CLASSNAME.CLASS.PHP")){
define("CLASSNAME.CLASS.PHP",1);

/**
* Class ClassName
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: CustomsInformation.php 357 2008-11-22 02:51:12Z erikwinn $
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

    class ClassName /*extends QControl*/
    {
        /**
        *@var Member member object
        */
        protected $objMember;
        /**
        *@var string Errors
        */
        protected $strErrors;
        
        /**
        * ClassName Constructor
        *
        * @param string thing name ..
        */
        public function __construct($thing)
        {
    
        }
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Member':
                    return $this->objMember ;
                case 'Errors':
                    return $this->objErrors ;
                default:
                    throw new QCallerException('Payment Action - Access Unknown property: ' . $strName);
                
/*   if subclass:                 try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }*/
            }
        }
        
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
                case 'Member':
                    try {
                        return ($this->objMember = QType::Cast($mixValue, 'Member' ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Errors':
                    try {
                        return ($this->strErrors .= "\n" . QType::Cast($mixValue, QType::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                default:
                        throw new QCallerException('Payment Action - Set Unknown property: ' . $strName);
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