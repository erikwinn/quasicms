<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("PRODUCTDISPLAYMODULE.CLASS.PHP")){
define("PRODUCTDISPLAYMODULE.CLASS.PHP",1);

 
/**
* Class ProductDisplayModule - A managing module for views of products by list and by item
* 
*
*  This module provides the center or main panel for display of products in the database. It
* utilizes two main panels to do this, one a list of products and one an individual product view.
* See ListModuleBase for more on the internals.
*
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: ProductDisplayModule.class.php 410 2008-12-09 20:43:20Z erikwinn $
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
 
 class ProductDisplayModule extends ListModuleBase
 {
        private $strViewMode = 'None';
        private $intProductId = null;        
        private $intProductCount = 0;
        private $objProductListView = null;
        private $objProductItemView = null;
        /**
        * Module constructor
        * NOTE: This module ignores the required extra parameters ..
        *@param ContentBlock - parent controller object.
        *@param mixed - extra parameters, ignored
        */
        public function __construct( ContentBlockView $objParentObject, $mixParameters=null)
        {
            //Parent should always be a ContentBlockView
            $this->objParentObject =& $objParentObject;
            
            try {
                parent::__construct($this->objParentObject);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->AutoRenderChildren = true;
//            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/ProductDisplayModule.tpl.php';
            
            $this->countProducts();
            $this->createDisplay();
        }
        
        private function countProducts()
        {
            ///@todo  implement display by Model for pretty urls ..
            ///@todo  check permissions here - stop on failure. redirect or limit the count to permissable ...
            
            $this->intProductCount = 0;
            
            $strParams = urldecode(IndexPage::$strPageParameters);
            $aryParameters = explode('/', $strParams);
            //really we are only concerned with the last parameter as this is the
            // category of items to be listed ..
            $strParam = array_pop($aryParameters);
            if(! $strParam)
            {
                $this->intProductCount = Product::CountAll();
                $this->strViewMode = 'List';
            }
            //last part may also be a product id
            elseif( is_numeric($strParam) )
            {
                $objProduct = Product::Load($strParam);
                if( $objProduct instanceof Product)
                {
                    $this->intProductId = $strParam;
                    $this->intProductCount = 1;
                    $this->strViewMode = 'Item';
                }
                else
                {
                    $this->intProductCount = 0;
                    $this->intProductId = null;
                }
            }
            //otherwise, it must be a category ..
            else
            {
                $objCategory = ProductCategory::LoadByName($strParam);
                if($objCategory)
                    $this->intProductCount = Product::CountByProductCategoryAsCategory( $objCategory->Id );
            }
        }
        private function createDisplay()
        {
            if( $this->intProductCount > 1 )
            {
                $this->strViewMode = 'List';
                if(!$this->objProductListView)
                    $this->objProductListView = new ProductListView( $this->pnlListView, $this, 'ShowItemPanel', 'CloseItemPanel'  );
                $this->pnlListView->Visible = true;
            }
            else
            {
                $this->strViewMode = 'Item';
                new ProductItemView( $this->pnlItemView, $this, 'CloseItemPanel', $this->intProductId );
                $this->pnlItemView->Visible = true;
            }
        }
        
        //Overrides the parent to ensure that the list view is populated
        public function CloseItemPanel($blnUpdatesMade)
        {
            if(!$this->objProductListView)
                $this->objProductListView = new ProductListView( $this->pnlListView, $this, 'ShowItemPanel', 'CloseItemPanel'  );
            parent::CloseItemPanel($blnUpdatesMade);            
        }
        
        public function __get($strName)
        {
            switch ($strName)
            {
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