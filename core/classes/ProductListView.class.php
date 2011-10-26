<?php
if(!defined('QUASICMS') ) die("No quasi.");
    
    /**
    * ProductListView - provides a panel for the display of a list of Products
    *    
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: ProductListView.class.php 290 2008-10-12 02:22:54Z erikwinn $
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
    * @subpackage Classes
     */
	class ProductListView extends QPanel
    {
        private $objControlBlock;
        private $objAccount;
                		
        // Local instance of the Meta DataGrid to list Products
		public $dtgProducts;

		public $pxtViewItem;
        public $pxyAddToCart;

		// Callback Method Names
		protected $strSetItemViewMethod;
		protected $strCloseItemViewMethod;
		
		public function __construct($objParentObject,
                                                      $objControlBlock,
                                                      $strSetItemViewMethod,
                                                      $strCloseItemViewMethod,
                                                      $strControlId = null)
        {
			// Call the Parent
			try {
				parent::__construct($objParentObject, $strControlId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
            
            $this->objControlBlock =& $objControlBlock;

            $this->objAccount = IndexPage::$objAccount;

			// Record Method Callbacks
			$this->strSetItemViewMethod = $strSetItemViewMethod;
			$this->strCloseItemViewMethod = $strCloseItemViewMethod;

			$this->Template =__QUASI_CORE_TEMPLATES__ . '/ProductListView.tpl.php';

			$this->dtgProducts = new ProductDataGrid($this);

			$this->dtgProducts->CssClass = 'datagrid';
			$this->dtgProducts->AlternateRowStyle->CssClass = 'alternate';

			$this->dtgProducts->Paginator = new QPaginator($this->dtgProducts);
			$this->dtgProducts->ItemsPerPage = 15;

			$this->pxtViewItem = new QControlProxy($this);
            if(IndexPage::$blnAjaxOk)
                $this->pxtViewItem->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxtViewItem_Click'));
            else
                $this->pxtViewItem->AddAction(new QClickEvent(), new QServerControlAction($this, 'pxtViewItem_Click'));
			$this->dtgProducts->MetaAddEditProxyColumn($this->pxtViewItem, 'View', 'View');
                    
			$this->dtgProducts->MetaAddColumn('Id');
			$this->dtgProducts->MetaAddColumn('Name');
			$this->dtgProducts->MetaAddColumn('Model');
			$this->dtgProducts->MetaAddColumn('RetailPrice');

/*other possible columns:
            $this->dtgProducts->MetaAddColumn(QQN::Product()->Manufacturer);
            $this->dtgProducts->MetaAddColumn(QQN::Product()->Supplier);
            $this->dtgProducts->MetaAddColumn('CreationDate');
            $this->dtgProducts->MetaAddColumn('ShortDescription');
            $this->dtgProducts->MetaAddColumn('LongDescription');
            $this->dtgProducts->MetaAddColumn('Msrp');
            $this->dtgProducts->MetaAddColumn('WholesalePrice');
			$this->dtgProducts->MetaAddColumn('Cost');
    		$this->dtgProducts->MetaAddColumn('Weight');
			$this->dtgProducts->MetaAddColumn('Height');
			$this->dtgProducts->MetaAddColumn('Width');
			$this->dtgProducts->MetaAddColumn('Depth');
			$this->dtgProducts->MetaAddColumn('IsVirtual');
			$this->dtgProducts->MetaAddTypeColumn('TypeId', 'ProductType');
*/            
            $this->pxyAddToCart = new QControlProxy($this);
            if(IndexPage::$blnAjaxOk)
                $this->pxyAddToCart->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyAddToCart_Click'));
            else
                $this->pxyAddToCart->AddAction(new QClickEvent(), new QServerControlAction($this, 'pxyAddToCart_Click'));
            $this->dtgProducts->MetaAddEditProxyColumn($this->pxyAddToCart, 'Add To Cart', '');
            
        }
        /**
        * This function accepts an Id for a Product item to view, called when user clicks "View"
        * in the list item.
        * The Product is displayed in a separate panel by the ControlBlock
        *
        *@param string strFormId - contains the CSS id of the main QForm (ie. IndexPage)
        *@param string strControlId - contains the CSS id of the calling control (ie. the datagrid )
        *@param integer intProductId - contains the id of the Product to add
        *@return void
        */
		public function pxtViewItem_Click($strFormId, $strControlId, $intProductId)
        {
			$objItemView = new ProductItemView($this,
                                                                        $this->objControlBlock,
                                                                        $this->strCloseItemViewMethod,
                                                                        $intProductId);

			$strMethodName = $this->strSetItemViewMethod;
			$this->objControlBlock->$strMethodName($objItemView);
		}
        /**
        * This function accepts an Id for a Product to add to the shopping cart, called when user clicks "Add to Cart"
        * in the list item.
        * The Product is then added to the cart as a ShoppingCartItem. If a ShoppingCartItem already exists
        * for this product, the quantity is incremented.
        *
        *
        *@param string strFormId - contains the CSS id of the main QForm (ie. IndexPage)
        *@param string strControlId - contains the CSS id of the calling control (ie. the datagrid )
        *@param integer intProductId - contains the id of the Product to add
        *@return void
        */
        public function pxyAddToCart_Click($strFormId, $strControlId, $intProductId)
        {
            if($intProductId && IndexPage::$objShoppingCart instanceof ShoppingCart )
                IndexPage::$objShoppingCart->AddItem($intProductId);
        }

	}
?>