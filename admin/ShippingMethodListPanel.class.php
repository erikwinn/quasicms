<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the ShippingMethod class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of ShippingMethod objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this ShippingMethodListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class ShippingMethodListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list ShippingMethods
		public $dtgShippingMethods;

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
			$this->Template = 'ShippingMethodListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgShippingMethods = new ShippingMethodDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgShippingMethods->CssClass = 'datagrid';
			$this->dtgShippingMethods->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgShippingMethods->Paginator = new QPaginator($this->dtgShippingMethods);
			$this->dtgShippingMethods->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgShippingMethods->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for shipping_method's properties, or you
			// can traverse down QQN::shipping_method() to display fields that are down the hierarchy)
			$this->dtgShippingMethods->MetaAddColumn('Id');
			$this->dtgShippingMethods->MetaAddColumn('Title');
			$this->dtgShippingMethods->MetaAddColumn('Carrier');
			$this->dtgShippingMethods->MetaAddColumn('ServiceType');
			$this->dtgShippingMethods->MetaAddColumn('ClassName');
			$this->dtgShippingMethods->MetaAddColumn('TransitTime');
			$this->dtgShippingMethods->MetaAddColumn('Description');
			$this->dtgShippingMethods->MetaAddColumn('Active');
			$this->dtgShippingMethods->MetaAddColumn('IsInternational');
			$this->dtgShippingMethods->MetaAddColumn('TestMode');
			$this->dtgShippingMethods->MetaAddColumn('SortOrder');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('ShippingMethod');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new ShippingMethodEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new ShippingMethodEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>