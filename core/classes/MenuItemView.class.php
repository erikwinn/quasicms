<?php
/**
    *  This is the MenuItemView class for the display functionality
    * of the MenuItem class. It determines the type of menu to create
    * based on flags in MenuItem, creating local soft hrefs (href#target)
    * or external links. The rendering is based on <li> for CSS styling
    * when there are lists or optionally a simple div with an anchor. The label
    * may be text or an img src (CSS images are prefered). A div may be
    * used for stand alone links that are not part of a menu
    *
    *  The CSS Id is the Name column in the menu_item table, class may
    * be set in the administration as well - each class will be added, a default
    * class of Menu or MenuItem will be applied in any case.
    *
    *  The constructor may be passed either a MenuItem or a MenuView -
    * for MenuView GetControlHtml will make a li or a div in which to create
    * the children of the menu (NOTE: this is unimplemented ..) 
    *
    *@todo
    *   - implement Menu child items
    *   - check permissions for the item ..
    *   - (maybe) add js actions to simply change the CenterPanel of a PageView
    *   without a total reload of the page. Ie. pass a callback that can hide/show
    *   areas.
    *
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: MenuItemView.class.php 426 2008-12-14 04:18:10Z erikwinn $
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
    * @package Quasi
    * @subpackage Views
    *
    */
     
	
    class MenuItemView extends QControl
    {
        /**		
        * Local reference to the Parent object: MenuItem or a Menu normally
        * @var QPanel objParentObject
        */
        protected $objControlBlock;
        
        protected $mixMenuItem;

        private $_objMenuItem = null;
        private $_objMenu = null;
        
        // The presented item - may contain text or an image
        protected $strLabel = '';
        protected $strHref = '';
        protected $strImgSrc = null;
        
		// This MenuItem's CSS
        protected $strCssId = '';
        protected $strCssclass = '';

        // State flags
        protected $intLevel = 1;
        protected $blnEnabled = true;
        protected $blnUseDivs = false;
        protected $blnUseSpans = false;
        protected $blnUseImgSrc = false;
        protected $isLocal = true;
        
        public function ParsePostData() {}
        
        public function Validate() {return true;}
        /**
        * MenuItemView constructor
        * This constructor will accept any QPanel as the control block, this is normally a MenuView.
        * The second parameter may be either a Menu or a MenuItem.
        *
        *@param QPanel objControlBlock - the DOM parent, should be a ContentBlockView or a MenuView
        *@param QPanel mixMenuItem - the item to display, a Menu or a MenuItem 
        */
		public function __construct($objControlBlock, $mixMenuItem)
        {
            //Parent should always be a ContentBlockView or a MenuView
            $this->objControlBlock = $objControlBlock;
            $this->strCssId = preg_replace('/\s/', '',$mixMenuItem->Name);
            
			try {
				parent::__construct($this->objControlBlock, $this->strCssId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
            if(  $mixMenuItem instanceof MenuItem )
            {
                $this->_objMenuItem = $mixMenuItem;
                if('/'. $this->MenuItem->Uri == Quasi::$PathInfo)
                    $this->AddCssClass("currentlink");
                    
            }
            elseif ( $mixMenuItem instanceof Menu )
            {
                $this->AddCssClass("Menu");
                $this->_objMenu = $mixMenuItem;
            }
            
            if($mixMenuItem->CssClass)
                $this->AddCssClass($mixMenuItem->CssClass);

            $this->AddCssClass($mixMenuItem->Type);
                        
            $this->strHref = $this->CreateHref();
		}
        
        /**
         * Creates the HTML for this MenuItem - returns address to be wrapped
         * @todo
         *  - be more intelligent here, perhaps support odd number ports, possibly
         * omit http* and servername for local items.
         *  - There is also an odd behaviour in QCodo - it gets confused when switching
         * between ssl and plain connections .. fixme.
         *  - Possibly allow for document.location if js is available 
         * 
         * @return string
         */
        public function CreateHref()
        {
            //we are a menu .. return.
            if($this->Menu )
                return '';
                
            if($this->MenuItem->IsSsl)
                $strReturn = 'https://';
            else
                $strReturn = 'http://';

            if($this->MenuItem->IsLocal)
                $strReturn .=  Quasi::$ServerName . __QUASI_SUBDIRECTORY__ . '/index.php/';
            $strReturn .= $this->MenuItem->Uri;
            return $strReturn;
        }
        /**
         * Get the HTML for this MenuItem - returns the anchor wrapped in li or div
         * @return string
         */
        public function GetControlHtml()
        {
            if($this->Menu)
                $object = $this->Menu;
            else
                $object = $this->MenuItem;
            
            $strAttributes = $this->GetAttributes();
            
            $strStyle = $this->GetStyleAttributes();
            if ( '' != $strStyle)
                $strStyle = 'style="' . $strStyle . '"';

            if($object instanceof Menu)
                return sprintf(' %s', 'Submenus TBD-FIXME' );
/*            if($this->blnUseDivs)
                return sprintf('<div id="%s" %s%s><a href="%s"> %s</a></div>',
                        $this->strCssId,  $strAttributes, $strStyle, $this->Href, $object->Label);
/*            return sprintf('<li id="%s" %s%s><a href="%s"> %s</a></li>',
                        $this->strCssId,  $strAttributes, $strStyle, $this->Href, $object->Label);*/
            return sprintf('<a %s %s href="%s"> %s</a>',
                        $strAttributes, $strStyle, $this->Href, $object->Label);
        }

        public function __get($strName)
        {
            switch ($strName)
            {
                case 'UseDivs':
                    return $this->blnUseDivs ;
                case 'CssId':
                    return $this->strCssId ;
                case 'Cssclass':
                    return $this->strCssclass ;
                case 'Href':
                    return $this->strHref ;
                case 'Level':
                    return $this->intLevel ;
                case 'Menu':
                    return $this->_objMenu;
                case 'MenuItem':
                    return $this->_objMenuItem;
                case 'Label':
                case 'Title':
                    if($this->mixMenuItem instanceof Menu )
                        return $this->_objMenu->Title ;
                    else
                        return $this->_objMenuItem->Label ;
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
                case 'Level':
                    try {
                        return ($this->intLevel = QType::Cast($mixValue, QType::Integer));
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
	}
?>