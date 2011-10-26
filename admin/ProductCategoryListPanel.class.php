<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the ProductCategory class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of ProductCategory objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this ProductCategoryListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class ProductCategoryListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list ProductCategories
		public $dtgProductCategories;

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
			$this->Template = 'ProductCategoryListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgProductCategories = new ProductCategoryDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgProductCategories->CssClass = 'datagrid';
			$this->dtgProductCategories->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgProductCategories->Paginator = new QPaginator($this->dtgProductCategories);
			$this->dtgProductCategories->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgProductCategories->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for product_category's properties, or you
			// can traverse down QQN::product_category() to display fields that are down the hierarchy)
			$this->dtgProductCategories->MetaAddColumn('Id');
			$this->dtgProductCategories->MetaAddColumn('Name');
			$this->dtgProductCategories->MetaAddColumn('Title');
			$this->dtgProductCategories->MetaAddColumn('Description');
			$this->dtgProductCategories->MetaAddColumn('ImageUri');
			$this->dtgProductCategories->MetaAddColumn(QQN::ProductCategory()->ParentProductCategory);
			$this->dtgProductCategories->MetaAddTypeColumn('PublicPermissionsId', 'PermissionType');
			$this->dtgProductCategories->MetaAddTypeColumn('UserPermissionsId', 'PermissionType');
			$this->dtgProductCategories->MetaAddTypeColumn('GroupPermissionsId', 'PermissionType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('ProductCategory');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new ProductCategoryEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new ProductCategoryEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>