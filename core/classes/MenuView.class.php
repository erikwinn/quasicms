<?php

if(!defined('QUASICMS') ) die("No quasi.");

    
/**
    * This is the View class for display functionality of the Menu class.
    *  It provides a div based area for content with hierarchy, css id and class
    * and a relationship to the basic areas managed by ContentBlockView. It is to a
    * Menu that a MenuItem is assigned. This class will render any child Menus
    * and all associated Items.
    *  These associations may configured via the QuasiCMS Dashboard interface and
    * will then automatically be reflected in the associated ContentBlockView display.
    *    
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: MenuView.class.php 294 2008-10-13 22:29:36Z erikwinn $
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
     
    class MenuView extends QPanel
    {
		// Local instances of the Parent object, Menu and MenuItems
        protected $objParentObject;
        protected $objMenu;
        public $aryMenuItemViews;
        
        protected $strTitle;
        protected $intLevel = 0;

		// This Menu block's CSS id
        protected $strCssId;
        protected $strCssclass;
        
		public function __construct($objParentObject, Menu $objMenu/*, $strCssId*/)
        {
            //Parent must always be a ContentBlock or a MenuView
            $this->objParentObject = $objParentObject;
            $this->strCssId = preg_replace('/\s/', '',$objMenu->Name);
//            $this->strCssId = $strCssId;
			
			try {
				parent::__construct($this->objParentObject, $this->strCssId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
            if( !$objMenu )
                throw new QCallerException(sprintf("Menu %s created without a MenuItem!", $strCssId) );
           else
                $this->objMenu =  $objMenu;
                        
            $this->strTitle = $this->objMenu->Title;
            if($objMenu->CssClass)
                $this->AddCssClass($objMenu->CssClass);
                
            $this->AddCssClass($objMenu->Type);
            
            $this->Template = __QUASI_CORE_TEMPLATES__ . '/MenuView.tpl.php';

            $aryMenuItems = $this->objMenu->GetMenuItemAsItemArray( QQ::Clause( 
                                                        QQ::OrderBy(QQN::MenuItem()->SortOrder)
                                                                 ));
            foreach($aryMenuItems as $objMenuItem )
            {
                $objMenuItemView = new MenuItemView( $this, $objMenuItem );
                
                //Note: this will increment
                $objMenuItemView->Level = $this->Level + 1;
                
                $this->aryMenuItemViews[] = $objMenuItemView;
            }

/*            if(!$this->mctMenu || !$this->objMenu  )
                $this->Template = 'BasicMenu.tpl.php';
            else    
                switch( $this->objMenu->Type )
                {
                    case 'Menu':
                        $this->Template = 'MenuMenu.tpl.php';
                        break;
                    case 'MenuItem':
                        $this->Template = 'MenuItemMenu.tpl.php';
                        break;
                    case 'Header':
                        $this->Template = 'HeaderMenu.tpl.php';
                        break;
                    case 'RightPanel':
                        $this->Template = 'RightPanelMenu.tpl.php';
                        break;
                    case 'LeftPanel':
                        $this->Template = 'LeftPanelMenu.tpl.php';
                        break;
                    case 'CenterPanel':
                        $this->Template = 'CenterPanelMenu.tpl.php';
                        break;
                    case 'Footer':
                        $this->Template = 'FooterMenu.tpl.php';
                        break;
                    case 'BlockHeader':
                        $this->Template = 'BlockHeaderMenu.tpl.php';
                        break;
                    case 'BlockFooter':
                        $this->Template = 'BlockFooterMenu.tpl.php';
                        break;
                    default:
                        $this->Template = 'BasicMenu.tpl.php';
                }*/
                
		}
        protected function init()
        {            
        }
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Level':
                    return $this->intLevel ;
                case 'CssId':
                    return $this->strCssId ;
                case 'Title':
                    return $this->strTitle ;
                case 'ShowTitle':
                    return $this->objMenu->ShowTitle ;
                case 'Name':
                    return $this->objMenu->Name ;
                case 'MenuItemViews':
                    return $this->aryMenuItemViews ;
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