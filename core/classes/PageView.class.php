<?php
if(!defined('QUASICMS') ) die("No quasi.");
if (!defined("PAGEVIEW.CLASS.PHP")){
define("PAGEVIEW.CLASS.PHP",1);

    /**
    * PageView - handles the content block placement for a single page in Quasi.
    * 
    * This class is the manager for placing content blocks associated with a Page.
    * It sets up the ContentBlocks according to some default areas as defined in the
    * block_location_type table in the database, These are currently hard coded to offer
    * a default generic layout with a header, two side bars, a center content area and
    * a footer. Extra divs are also provided for extra flexibility. All divs loaded by this
    * class are contained within the master container div (see the template).
    *
    * You can modify the default layout via the style sheet associated with each Page
    * - the style sheet to use for a page can be set via the Quasi CMS administrative interface.
    * It is also not difficult to change the default areas - simply edit the template and ensure
    * that the associated div CSS id names are in the block_location_type table and then
    * you can associate ContentBlocks with those areas (again through the admin interface).
    *
    *NOTE: One course of development would be to subclass this for page types. This
    * is already in the database, but it is not used yet - an architectural decision to be made ...
    *     
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: PageView.class.php 197 2008-09-19 22:11:27Z erikwinn $
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
    */
    class PageView extends QPanel
    {
        protected $objParentObject;
        protected $objPage;
        
        public $aryHeaderContentBlocks;
        public $aryRightPanelContentBlocks;
        public $aryCenterPanelContentBlocks;
        public $aryLeftPanelContentBlocks;
        public $aryFooterContentBlocks;
        public $aryExtraContentBlocks;

        public function __construct($objParentObject, $objPage)
        {
            ///@todo  We should have an ErrorPage in the page table with an Error ContentBlock and
            // ContentItem attached!   quasidb.sql should insert these by default on install.
            // Thought: just redirect to a static page here, or we need a class ErrorPage .. but, this
            // is an unlikely scenario anyway as IndexPage should handle this - I am really thinking
            // of new developers using this class wrongly, for now just go home ..
            if(! $objPage )
                $this->objPage = $this->objPage = Page::LoadByName('Home');
            else
                $this->objPage = $objPage;
            
            //To have any actions, Parent must be a QForm - QuasiCMS uses class IndexPage
            // as the master page (index.php) that takes all requests and instantiates pages
            $this->objParentObject = $objParentObject;

            try {
                parent::__construct($this->objParentObject);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            
            if( $this->objPage)
                foreach ( $this->objPage->GetContentBlockArray(
                                                        QQ::Clause (QQ::OrderBy(QQN::ContentBlock()->SortOrder) )
                                                                    ) as $objContentBlock )
                {
                    if(! $objContentBlock)
                        continue;
                    $strLocation = $objContentBlock->Location;
                    $strCssId = $strLocation . preg_replace('/\s/', '',$objContentBlock->Name);
                    $strCssClass = $strLocation . 'ContentBlock';
                    switch ($strLocation)
                    {
                        case 'PageHeader':
                            $objContentBlockView = new ContentBlockView( $this, $objContentBlock, $strCssId);
                            $this->aryHeaderContentBlocks[] = $objContentBlockView;
                            break;
                        case 'RightPanel':
                            $objContentBlockView = new ContentBlockView( $this, $objContentBlock, $strCssId);
                            $this->aryRightPanelContentBlocks[] = $objContentBlockView;
                            break;
                        case 'LeftPanel':
                            $objContentBlockView = new ContentBlockView( $this, $objContentBlock, $strCssId);
                            $this->aryLeftPanelContentBlocks[] = $objContentBlockView;
                            break;
                        case 'CenterPanel':
                            $objContentBlockView = new ContentBlockView( $this, $objContentBlock, $strCssId);
                            $this->aryCenterPanelContentBlocks[] = $objContentBlockView;
                            break;
                        case 'PageFooter':
                            $objContentBlockView = new ContentBlockView( $this, $objContentBlock, $strCssId);
                            $this->aryFooterContentBlocks[] = $objContentBlockView;
                            break;

                        default:
                            $objContentBlockView = new ContentBlockView( $this, $objContentBlock, $strCssId);
                            $this->aryExtraContentBlocks[] = $objContentBlockView;
                            break;
                    }

                    $objContentBlockView->CssClass = $strCssClass;
                    $objContentBlockView->Visible = true;
                }
            
            $this->Template = __QUASI_CORE_TEMPLATES__ . '/PageView.tpl.php';
            
        }
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'HeaderContentBlocks':
                    return $this->aryHeaderContentBlocks ;
                case 'LeftPanelContentBlocks':
                    return $this->aryLeftPanelContentBlocks ;
                case 'CenterPanelContentBlocks':
                    return $this->aryCenterPanelContentBlocks ;
                case 'RightPanelContentBlocks':
                    return $this->aryRightPanelContentBlocks ;
                case 'FooterContentBlocks':
                    return $this->aryFooterContentBlocks ;
                case 'ExtraContentBlocks':
                    return $this->aryExtraContentBlocks ;
                case 'HasHeader':
                    return $this->objPage->HasHeader ;
                case 'HasLeftColumn':
                    return $this->objPage->HasLeftColumn ;
                case 'HasRightColumn':
                    return $this->objPage->HasRightColumn ;
                case 'HasFooter':
                    return $this->objPage->HasFooter ;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
    }//end class
}//end define     
?>