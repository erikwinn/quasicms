<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the ContentCategory class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of ContentCategory objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this ContentCategoryListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class ContentCategoryListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list ContentCategories
		public $dtgContentCategories;

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
			$this->Template = 'ContentCategoryListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgContentCategories = new ContentCategoryDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgContentCategories->CssClass = 'datagrid';
			$this->dtgContentCategories->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgContentCategories->Paginator = new QPaginator($this->dtgContentCategories);
			$this->dtgContentCategories->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgContentCategories->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for content_category's properties, or you
			// can traverse down QQN::content_category() to display fields that are down the hierarchy)
			$this->dtgContentCategories->MetaAddColumn('Id');
			$this->dtgContentCategories->MetaAddColumn('Name');
			$this->dtgContentCategories->MetaAddColumn('Title');
			$this->dtgContentCategories->MetaAddColumn('Description');
			$this->dtgContentCategories->MetaAddColumn('ImageUri');
			$this->dtgContentCategories->MetaAddColumn(QQN::ContentCategory()->ParentContentCategory);
			$this->dtgContentCategories->MetaAddTypeColumn('PublicPermissionsId', 'PermissionType');
			$this->dtgContentCategories->MetaAddTypeColumn('UserPermissionsId', 'PermissionType');
			$this->dtgContentCategories->MetaAddTypeColumn('GroupPermissionsId', 'PermissionType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('ContentCategory');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new ContentCategoryEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new ContentCategoryEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>