<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the ShoppingCartItem class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of ShoppingCartItem objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this ShoppingCartItemListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class ShoppingCartItemListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list ShoppingCartItems
		public $dtgShoppingCartItems;

		// Other public QControls in this panel
		public $btnCreateNew;
		public $pxyEdit;

		// Callback Method Names
		protected $strSetEditPanelMethod;
		protected $strCloseEditPanelMethod;
		
		public function __construct($objParentObject, $strSetEditPanelMethod, $strCloseEditPanelMethod, $strControlId = null) {
			// Call the Parent
			try {
				parent::__construct($objParentObject, $strControlId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}

			// Record Method Callbacks
			$this->strSetEditPanelMethod = $strSetEditPanelMethod;
			$this->strCloseEditPanelMethod = $strCloseEditPanelMethod;

			// Setup the Template
			$this->Template = 'ShoppingCartItemListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgShoppingCartItems = new ShoppingCartItemDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgShoppingCartItems->CssClass = 'datagrid';
			$this->dtgShoppingCartItems->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgShoppingCartItems->Paginator = new QPaginator($this->dtgShoppingCartItems);
			$this->dtgShoppingCartItems->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgShoppingCartItems->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for shopping_cart_item's properties, or you
			// can traverse down QQN::shopping_cart_item() to display fields that are down the hierarchy)
			$this->dtgShoppingCartItems->MetaAddColumn(QQN::ShoppingCartItem()->ShoppingCart);
			$this->dtgShoppingCartItems->MetaAddColumn(QQN::ShoppingCartItem()->Product);
			$this->dtgShoppingCartItems->MetaAddColumn('Quantity');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('ShoppingCartItem');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new ShoppingCartItemEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0], $strParameterArray[1]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new ShoppingCartItemEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>