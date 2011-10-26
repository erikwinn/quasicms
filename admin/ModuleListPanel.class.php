<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the Module class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of Module objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this ModuleListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class ModuleListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list Modules
		public $dtgModules;

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
			$this->Template = 'ModuleListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgModules = new ModuleDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgModules->CssClass = 'datagrid';
			$this->dtgModules->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgModules->Paginator = new QPaginator($this->dtgModules);
			$this->dtgModules->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgModules->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for module's properties, or you
			// can traverse down QQN::module() to display fields that are down the hierarchy)
			$this->dtgModules->MetaAddColumn('Id');
			$this->dtgModules->MetaAddColumn('Name');
			$this->dtgModules->MetaAddColumn('Cssclass');
			$this->dtgModules->MetaAddColumn('Title');
			$this->dtgModules->MetaAddColumn('Description');
			$this->dtgModules->MetaAddColumn('ClassName');
			$this->dtgModules->MetaAddColumn('ShowTitle');
			$this->dtgModules->MetaAddColumn('ShowDescription');
			$this->dtgModules->MetaAddColumn(QQN::Module()->ContentBlock);
			$this->dtgModules->MetaAddColumn(QQN::Module()->ParentModule);
			$this->dtgModules->MetaAddTypeColumn('PublicPermissionsId', 'PermissionType');
			$this->dtgModules->MetaAddTypeColumn('UserPermissionsId', 'PermissionType');
			$this->dtgModules->MetaAddTypeColumn('GroupPermissionsId', 'PermissionType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('Module');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new ModuleEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new ModuleEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>