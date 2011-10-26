<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the Menu class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of Menu objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this MenuListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class MenuListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list Menus
		public $dtgMenus;

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
			$this->Template = 'MenuListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgMenus = new MenuDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgMenus->CssClass = 'datagrid';
			$this->dtgMenus->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgMenus->Paginator = new QPaginator($this->dtgMenus);
			$this->dtgMenus->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgMenus->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for menu's properties, or you
			// can traverse down QQN::menu() to display fields that are down the hierarchy)
			$this->dtgMenus->MetaAddColumn('Id');
			$this->dtgMenus->MetaAddColumn('Name');
			$this->dtgMenus->MetaAddColumn('Title');
			$this->dtgMenus->MetaAddColumn('CssClass');
			$this->dtgMenus->MetaAddColumn('SortOrder');
			$this->dtgMenus->MetaAddColumn('ShowTitle');
			$this->dtgMenus->MetaAddColumn('MenuItemId');
			$this->dtgMenus->MetaAddTypeColumn('PublicPermissionsId', 'PermissionType');
			$this->dtgMenus->MetaAddTypeColumn('UserPermissionsId', 'PermissionType');
			$this->dtgMenus->MetaAddTypeColumn('GroupPermissionsId', 'PermissionType');
			$this->dtgMenus->MetaAddTypeColumn('StatusId', 'MenuStatusType');
			$this->dtgMenus->MetaAddTypeColumn('TypeId', 'MenuType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('Menu');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new MenuEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new MenuEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>