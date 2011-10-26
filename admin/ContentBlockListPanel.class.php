<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the ContentBlock class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of ContentBlock objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this ContentBlockListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class ContentBlockListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list ContentBlocks
		public $dtgContentBlocks;

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
			$this->Template = 'ContentBlockListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgContentBlocks = new ContentBlockDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgContentBlocks->CssClass = 'datagrid';
			$this->dtgContentBlocks->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgContentBlocks->Paginator = new QPaginator($this->dtgContentBlocks);
			$this->dtgContentBlocks->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgContentBlocks->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for content_block's properties, or you
			// can traverse down QQN::content_block() to display fields that are down the hierarchy)
			$this->dtgContentBlocks->MetaAddColumn('Id');
			$this->dtgContentBlocks->MetaAddColumn('Name');
			$this->dtgContentBlocks->MetaAddColumn('Cssclass');
			$this->dtgContentBlocks->MetaAddColumn('Title');
			$this->dtgContentBlocks->MetaAddColumn('Description');
			$this->dtgContentBlocks->MetaAddColumn('ShowTitle');
			$this->dtgContentBlocks->MetaAddColumn('ShowDescription');
			$this->dtgContentBlocks->MetaAddColumn('Collapsable');
			$this->dtgContentBlocks->MetaAddColumn('SortOrder');
			$this->dtgContentBlocks->MetaAddColumn(QQN::ContentBlock()->ParentContentBlock);
			$this->dtgContentBlocks->MetaAddTypeColumn('LocationId', 'BlockLocationType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('ContentBlock');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new ContentBlockEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new ContentBlockEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>