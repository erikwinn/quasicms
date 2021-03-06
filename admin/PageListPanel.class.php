<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the Page class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of Page objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this PageListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class PageListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list Pages
		public $dtgPages;

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
			$this->Template = 'PageListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgPages = new PageDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgPages->CssClass = 'datagrid';
			$this->dtgPages->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgPages->Paginator = new QPaginator($this->dtgPages);
			$this->dtgPages->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgPages->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for page's properties, or you
			// can traverse down QQN::page() to display fields that are down the hierarchy)
			$this->dtgPages->MetaAddColumn('Id');
			$this->dtgPages->MetaAddColumn('CreationDate');
			$this->dtgPages->MetaAddColumn('LastModification');
			$this->dtgPages->MetaAddColumn('Name');
			$this->dtgPages->MetaAddColumn('Title');
			$this->dtgPages->MetaAddColumn('Uri');
			$this->dtgPages->MetaAddColumn('HasHeader');
			$this->dtgPages->MetaAddColumn('HasLeftColumn');
			$this->dtgPages->MetaAddColumn('HasRightColumn');
			$this->dtgPages->MetaAddColumn('HasFooter');
			$this->dtgPages->MetaAddTypeColumn('PublicPermissionsId', 'PermissionType');
			$this->dtgPages->MetaAddTypeColumn('UserPermissionsId', 'PermissionType');
			$this->dtgPages->MetaAddTypeColumn('GroupPermissionsId', 'PermissionType');
			$this->dtgPages->MetaAddTypeColumn('TypeId', 'PageType');
			$this->dtgPages->MetaAddTypeColumn('DocTypeId', 'PageDocType');
			$this->dtgPages->MetaAddTypeColumn('StatusId', 'PageStatusType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('Page');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new PageEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new PageEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>