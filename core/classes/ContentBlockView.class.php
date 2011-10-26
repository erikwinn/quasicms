<?php

if(!defined('QUASICMS') ) die("No quasi.");
     

/**
    * Class ContentBlockView - selects and renders items in a content block.
    *
    * This is the View class for display functionality of the ContentBlock class.
    *  It provides a div based area for content with hierarchy, css id and class
    * and a relationship to the basic areas provided by PageView. It is to a
    * ContentBlock that a ContentItem, MenuItem or ActionItem is assigned. This
    * class will render any child ContentBlocks and all associated Items. These
    * associations may configured via the QuasiCMS administrative interface and
    * will then automatically be reflected in the associated PageView display.
    *
    * This class is created by PageView which passes a reference to the main parent
    * object and the ContentBlock object from the content_block table in the database.
    *
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: ContentBlockView.class.php 127 2008-09-08 18:32:18Z erikwinn $
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
    
    class ContentBlockView extends QPanel
    {
        protected $objParentObject;
        
        protected $objContentBlock;

        ///Arrays of child blocks and content items and menus managed
        public $aryChildContentBlockViews = null;
        public $aryContentItemViews = null;
        public $aryMenuViews = null;
        public $aryModules = null;
        public $aryModuleViews = null;

        /// Metacontrol to handle title and description - maybe drop in favor of panels to optimize later.
        protected $mctContentBlock;
        
        /// Controls that allow the viewing of ContentBlock's individual data fields
        protected $pnlTitle;
        protected $pnlDescription;
        
		/// This ContentBlock's CSS id
        protected $strCssId;
       
		public function __construct($objParentObject, $objContentBlock, $strCssId)
        {
            //Parent must always be a child of QForm or Qcontrol
            $this->objParentObject = $objParentObject;
            $this->strCssId = $strCssId;
			$this->objContentBlock = $objContentBlock;
            
			try {
				parent::__construct($this->objParentObject, $this->strCssId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
            
            $this->initModuleViews();
            $this->initContentBlockViews();

/*An idea:

            if(!$this->mctContentBlock || !$this->objContentBlock  )
                $this->Template = 'BasicContentBlock.tpl.php';
            else    
                switch( $this->objContentBlock->Type )
                {
                    case 'Menu':
                        $this->Template = 'MenuContentBlock.tpl.php';
                        break;
                    case 'MenuItem':
                        $this->Template = 'MenuItemContentBlock.tpl.php';
                        break;
                    case 'Header':
                        $this->Template = 'HeaderContentBlock.tpl.php';
                        break;
                    case 'RightPanel':
                        $this->Template = 'RightPanelContentBlock.tpl.php';
                        break;
                    case 'LeftPanel':
                        $this->Template = 'LeftPanelContentBlock.tpl.php';
                        break;
                    case 'CenterPanel':
                        $this->Template = 'CenterPanelContentBlock.tpl.php';
                        break;
                    case 'Footer':
                        $this->Template = 'FooterContentBlock.tpl.php';
                        break;
                    case 'BlockHeader':
                        $this->Template = 'BlockHeaderContentBlock.tpl.php';
                        break;
                    case 'BlockFooter':
                        $this->Template = 'BlockFooterContentBlock.tpl.php';
                        break;
                    default:
                        $this->Template = 'BasicContentBlock.tpl.php';
                }*/
                
		}
        /**
        *  This function handles rendering of "passive" content blocks. It is invoked if the
        * object passed to the constructor is of type ContentBlock. Passive content blocks
        * contain only data to be displayed and do not have any (overt) action controls.
        */
        protected function initContentBlockViews()
        {
            ///@todo  drop the meta control to optimize later ..
            $this->mctContentBlock =  new ContentBlockMetaControl($this, $this->objContentBlock);

            $this->pnlTitle = $this->mctContentBlock->pnlTitle_Create($this->CssId );
            $this->pnlTitle->CssClass = 'ContentBlockTitle';
            $this->pnlDescription = $this->mctContentBlock->pnlDescription_Create($this->CssId);
            $this->pnlDescription->CssClass = 'ContentBlockDescription';
            // Setup the Template
            $this->Template = __QUASI_CORE_TEMPLATES__ . '/ContentBlockView.tpl.php';
            
            foreach ( $this->objContentBlock->GetChildContentBlockArray(
                                                    QQ::Clause (QQ::OrderBy(QQN::ContentBlock()->SortOrder) )
                                                                ) as $childContentBlock )
            {
                $this->aryChildContentBlockViews[] = new ContentBlockView( $this, $childContentBlock, null);
            }
            
            foreach ( $this->objContentBlock->GetContentItemArray(
                                                    QQ::Clause (QQ::OrderBy(QQN::ContentItem()->SortOrder) )
                                                                ) as $objContentItem )
            {
                $objContentItemView = new ContentItemView( $this, $objContentItem );
                $objContentItemView->CssClass = $objContentItem->Type;
                $this->aryContentItemViews[] = $objContentItemView;
            }
            foreach ( $this->objContentBlock->GetMenuArray(
                                                    QQ::Clause (QQ::OrderBy(QQN::Menu()->SortOrder) )
                                                                ) as $objMenu )
            {
                $objMenuView = new MenuView( $this, $objMenu );
                $objMenuView->CssClass = $objMenu->Type;
                $this->aryMenuViews[] = $objMenuView;
            }
            return true;
        }
        
        protected function initModuleViews()
        {
            $this->aryModules = $this->objContentBlock->GetModuleArray();
            if(!$this->aryModules)
                return false; 
            foreach($this->aryModules as $objModule)
            {
               $strModuleClassName = $objModule->ClassName;
                try{
                     $objModuleView = new $strModuleClassName($this, $objModule);
                } catch (QCallerException $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
                $this->aryModuleViews[] = $objModuleView;
                
                ///@todo - add module to the global list
/*                if(!IndexPage::$MainWindow->GetActiveModule($strModuleClassName) )
                    IndexPage::$MainWindow->AddActiveModule ( $objModuleView );*/
            }
        }
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'HasModules':
                    return ! empty($this->aryModuleViews);
                case 'HasMenus':
                    return ! empty($this->aryMenuViews);
                case 'HasContentItems':
                    return ! empty($this->aryContentItemViews);
                case 'HasContentBlocks':
                    return ! empty($this->aryChildContentBlockViews);
                case 'Title':
                    return $this->objContentBlock->Title ;
                case 'Description':
                    return $this->objContentBlock->Description ;
                case 'ShowTitle':
                    return $this->objContentBlock->ShowTitle ;
                case 'ShowDescription':
                    return $this->objContentBlock->ShowDescription ;
                case 'CssId':
                    return $this->strCssId ;
                case 'TitlePanel':
                    return $this->pnlTitle ;
                case 'DescriptionPanel':
                    return $this->pnlDescription ;
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
             case 'Title':
                 try {
                     return ($this->objContentBlock->Title = QType::Cast($mixValue, QType::String));
                 } catch (QInvalidCastException $objExc) {
                     $objExc->IncrementOffset();
                     throw $objExc;
                 }

             case 'Description':
                 try {
                     return ($this->objContentBlock->Description = QType::Cast($mixValue, QType::String));
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