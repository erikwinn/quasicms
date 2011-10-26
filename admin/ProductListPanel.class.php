<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the Product class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of Product objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this ProductListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class ProductListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list Products
		public $dtgProducts;

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
			$this->Template = 'ProductListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgProducts = new ProductDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgProducts->CssClass = 'datagrid';
			$this->dtgProducts->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgProducts->Paginator = new QPaginator($this->dtgProducts);
			$this->dtgProducts->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgProducts->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for product's properties, or you
			// can traverse down QQN::product() to display fields that are down the hierarchy)
			$this->dtgProducts->MetaAddColumn('Id');
			$this->dtgProducts->MetaAddColumn(QQN::Product()->Manufacturer);
			$this->dtgProducts->MetaAddColumn(QQN::Product()->Supplier);
			$this->dtgProducts->MetaAddColumn('CreationDate');
			$this->dtgProducts->MetaAddColumn('Name');
			$this->dtgProducts->MetaAddColumn('Model');
			$this->dtgProducts->MetaAddColumn('ShortDescription');
			$this->dtgProducts->MetaAddColumn('LongDescription');
			$this->dtgProducts->MetaAddColumn('Msrp');
			$this->dtgProducts->MetaAddColumn('WholesalePrice');
			$this->dtgProducts->MetaAddColumn('RetailPrice');
			$this->dtgProducts->MetaAddColumn('Cost');
			$this->dtgProducts->MetaAddColumn('Weight');
			$this->dtgProducts->MetaAddColumn('Height');
			$this->dtgProducts->MetaAddColumn('Width');
			$this->dtgProducts->MetaAddColumn('Depth');
			$this->dtgProducts->MetaAddColumn('IsVirtual');
			$this->dtgProducts->MetaAddTypeColumn('TypeId', 'ProductType');
			$this->dtgProducts->MetaAddTypeColumn('StatusId', 'ProductStatusType');
			$this->dtgProducts->MetaAddColumn('ViewCount');
			$this->dtgProducts->MetaAddTypeColumn('UserPermissionsId', 'PermissionType');
			$this->dtgProducts->MetaAddTypeColumn('PublicPermissionsId', 'PermissionType');
			$this->dtgProducts->MetaAddTypeColumn('GroupPermissionsId', 'PermissionType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('Product');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new ProductEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new ProductEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>