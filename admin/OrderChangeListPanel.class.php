<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the OrderChange class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of OrderChange objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this OrderChangeListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class OrderChangeListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list OrderChanges
		public $dtgOrderChanges;

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
			$this->Template = 'OrderChangeListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgOrderChanges = new OrderChangeDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgOrderChanges->CssClass = 'datagrid';
			$this->dtgOrderChanges->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgOrderChanges->Paginator = new QPaginator($this->dtgOrderChanges);
			$this->dtgOrderChanges->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgOrderChanges->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for order_change's properties, or you
			// can traverse down QQN::order_change() to display fields that are down the hierarchy)
			$this->dtgOrderChanges->MetaAddColumn(QQN::OrderChange()->Order);
			$this->dtgOrderChanges->MetaAddColumn('Date');
			$this->dtgOrderChanges->MetaAddColumn('Notes');
			$this->dtgOrderChanges->MetaAddColumn('Value');
			$this->dtgOrderChanges->MetaAddTypeColumn('TypeId', 'OrderChangeType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('OrderChange');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new OrderChangeEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0], $strParameterArray[1]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new OrderChangeEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>