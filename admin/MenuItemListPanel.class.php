<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the MenuItem class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of MenuItem objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this MenuItemListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class MenuItemListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list MenuItems
		public $dtgMenuItems;

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
			$this->Template = 'MenuItemListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgMenuItems = new MenuItemDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgMenuItems->CssClass = 'datagrid';
			$this->dtgMenuItems->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgMenuItems->Paginator = new QPaginator($this->dtgMenuItems);
			$this->dtgMenuItems->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgMenuItems->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for menu_item's properties, or you
			// can traverse down QQN::menu_item() to display fields that are down the hierarchy)
			$this->dtgMenuItems->MetaAddColumn('Id');
			$this->dtgMenuItems->MetaAddColumn('Name');
			$this->dtgMenuItems->MetaAddColumn('CssClass');
			$this->dtgMenuItems->MetaAddColumn('Label');
			$this->dtgMenuItems->MetaAddColumn('Uri');
			$this->dtgMenuItems->MetaAddColumn('IsLocal');
			$this->dtgMenuItems->MetaAddColumn('IsSsl');
			$this->dtgMenuItems->MetaAddColumn('SortOrder');
			$this->dtgMenuItems->MetaAddTypeColumn('PublicPermissionsId', 'PermissionType');
			$this->dtgMenuItems->MetaAddTypeColumn('UserPermissionsId', 'PermissionType');
			$this->dtgMenuItems->MetaAddTypeColumn('GroupPermissionsId', 'PermissionType');
			$this->dtgMenuItems->MetaAddTypeColumn('StatusId', 'MenuStatusType');
			$this->dtgMenuItems->MetaAddTypeColumn('TypeId', 'MenuItemType');
			$this->dtgMenuItems->MetaAddColumn(QQN::MenuItem()->Page);

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('MenuItem');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new MenuItemEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new MenuItemEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>