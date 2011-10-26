<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("BLOGMODULE.CLASS.PHP")){
define("BLOGMODULE.CLASS.PHP",1);

/**
* Class BlogModule - provides module that loads content items of type "BlogPost"
* 
* To use this module, assign it to a content block and create content items that have the
* content type set to BlogPost. This module will display the most recent 10 posts sorted
* by date descending.
*
* This class can also be used independently by instantiating the class. It accepts optional
* parameters for the type of content item to display and the number of items.
*
*@author Erik Winn <erikwinnmail@yahoo.com>
* 
* $Id: BlogModule.class.php 517 2009-03-24 17:59:23Z erikwinn $
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


 class BlogModule extends QPanel
 {
        /**
        * @var ContentBlockView objContentBlock - the content block to which this module is assigned
        */
        protected $objContentBlock;        
        /**
        * @var array aryContentItems ContentItems to be displayed
        */
        protected $aryContentItems;
        /**
        * @var array aryContentItemViews ContentItemViews to be displayed
        */
        public $aryContentItemViews;
        /**
        * Module constructor
        * NOTE: When loaded as a module registered in the database, the parameters will be
        * a reference to the Module ORM object.
        *@param ContentBlock - parent controller object.
        *@param mixed - extra parameters for the displayed module
        *@param integer - optional content type to display
        *@param integer - optional number of posts to display
        */
        public function __construct( ContentBlockView $objContentBlock,
                                                     $mixParameters = null,
                                                     $intContentType=ContentType::BlogPost,
                                                     $intLimit=10)
        {
            //Parent should always be a ContentBlockView
            $this->objContentBlock =& $objContentBlock;
            
            try {
                parent::__construct($this->objContentBlock);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/BlogModule.tpl.php';

            $objConditions = QQ::AndCondition(
                                        QQ::Equal(QQN::ContentItem()->TypeId, $intContentType),
                                        QQ::Equal(QQN::ContentItem()->StatusId, ContentStatusType::Published)
                                                                     );
            $aryClauses = QQ::Clause(
                    QQ::OrderBy(QQN::ContentItem()->CreationDate, false),
                    QQ::LimitInfo($intLimit));
            
            $this->aryContentItems =  ContentItem::QueryArray($objConditions, $aryClauses);
            
            foreach ( $this->aryContentItems as $objContentItem )
            {
                $objContentItemView = new ContentItemView( $this, $objContentItem );
                $objContentItemView->AddCssClass($objContentItem->Type);
                $this->aryContentItemViews[] = $objContentItemView;
            }
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
/*                case 'SomeClass':
                    return $this->objSomeClass ;*/
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
//                 case 'SomeClass':
//                     try {
//                         return ($this->objSomeClass = QType::Cast($mixValue, 'SomeClass' ));
//                     } catch (QInvalidCastException $objExc) {
//                         $objExc->IncrementOffset();
//                         throw $objExc;
//                     }

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