<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the ContentItem class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of ContentItem objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this ContentItemListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class ContentItemListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list ContentItems
		public $dtgContentItems;

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
			$this->Template = 'ContentItemListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgContentItems = new ContentItemDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgContentItems->CssClass = 'datagrid';
			$this->dtgContentItems->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgContentItems->Paginator = new QPaginator($this->dtgContentItems);
			$this->dtgContentItems->ItemsPerPage = 20;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgContentItems->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for content_item's properties, or you
			// can traverse down QQN::content_item() to display fields that are down the hierarchy)
			$this->dtgContentItems->MetaAddColumn('Id');
			$this->dtgContentItems->MetaAddColumn('Name');
			$this->dtgContentItems->MetaAddColumn('Cssclass');
			$this->dtgContentItems->MetaAddColumn('Title');
			$this->dtgContentItems->MetaAddColumn('Description');
/*			$this->dtgContentItems->MetaAddColumn('Text');
			$this->dtgContentItems->MetaAddColumn('SortOrder');
			$this->dtgContentItems->MetaAddColumn('ShowTitle');
			$this->dtgContentItems->MetaAddColumn('ShowDescription');
			$this->dtgContentItems->MetaAddColumn('ShowCreator');
			$this->dtgContentItems->MetaAddColumn('ShowCreationDate');
			$this->dtgContentItems->MetaAddColumn('ShowLastModification');*/
			$this->dtgContentItems->MetaAddColumn(QQN::ContentItem()->Creator);
//			$this->dtgContentItems->MetaAddColumn('CopyrightNotice');
			$this->dtgContentItems->MetaAddColumn('CreationDate');
			$this->dtgContentItems->MetaAddColumn('LastModification');
/*			$this->dtgContentItems->MetaAddTypeColumn('PublicPermissionsId', 'PermissionType');
			$this->dtgContentItems->MetaAddTypeColumn('UserPermissionsId', 'PermissionType');
			$this->dtgContentItems->MetaAddTypeColumn('GroupPermissionsId', 'PermissionType');*/
			$this->dtgContentItems->MetaAddTypeColumn('TypeId', 'ContentType');
			$this->dtgContentItems->MetaAddTypeColumn('StatusId', 'ContentStatusType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('ContentItem');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new ContentItemEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new ContentItemEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>