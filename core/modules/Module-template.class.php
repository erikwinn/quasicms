<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("someMODULE.CLASS.PHP")){
define("someMODULE.CLASS.PHP",1);

/**
* Class SomeModule - provides modifiable display of data 
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: Module-template.class.php 290 2008-10-12 02:22:54Z erikwinn $
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
* @subpackage Modules
*/


 class SomeModule extends QPanel
 {
        /**
        * @var ContentBlockView objContentBlock - the content block to which this module is assigned
        */
        protected $objContentBlock;        
        /**
        * @var SomeClass objSomeClass - local reference or instance of some relevant object ..
        */
        protected $objSomeClass;
        /**
        * Module constructor
        * NOTE: When loaded as a module registered in the database, the parameters will be
        * a reference to the Module ORM object.
        *@param ContentBlock - parent controller object.
        *@param mixed - extra parameters for the displayed module
        */
        public function __construct( ContentBlockView $objContentBlock, $mixParameters=null)
        {
            //Parent should always be a ContentBlockView
            $this->objContentBlock =& $objContentBlock;
            
            try {
                parent::__construct($this->objContentBlock);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/SomeModule.tpl.php';
        }
        /**
         * This Function is called when any input is sent - on failure the
         * fields are redrawn with optional error messages.
         */
        public function Validate()
        {
            $blnToReturn = true;
            // validate input here
            return $blnToReturn;
        }

        /**
        * Event Handling
        */
        public function btnDoSomething_Click($strFormId, $strControlId, $strParameter)
        {
            Quasi::Redirect(__QUASI_SUBDIRECTORY__ . '/index.php/Home');
        }

        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'SomeClass':
                    return $this->objSomeClass ;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
                case 'SomeClass':
                    try {
                        return ($this->objSomeClass = QType::Cast($mixValue, 'SomeClass' ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                default:
                    try {
                        return (parent::__set($strName, $mixValue));
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
        
  }//end class
}//end define
?>