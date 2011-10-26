<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the PaymentMethod class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of PaymentMethod objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this PaymentMethodListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class PaymentMethodListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list PaymentMethods
		public $dtgPaymentMethods;

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
			$this->Template = 'PaymentMethodListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgPaymentMethods = new PaymentMethodDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgPaymentMethods->CssClass = 'datagrid';
			$this->dtgPaymentMethods->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgPaymentMethods->Paginator = new QPaginator($this->dtgPaymentMethods);
			$this->dtgPaymentMethods->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgPaymentMethods->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for payment_method's properties, or you
			// can traverse down QQN::payment_method() to display fields that are down the hierarchy)
			$this->dtgPaymentMethods->MetaAddColumn('Id');
			$this->dtgPaymentMethods->MetaAddColumn('Title');
			$this->dtgPaymentMethods->MetaAddColumn('ServiceProvider');
			$this->dtgPaymentMethods->MetaAddColumn('ServiceType');
			$this->dtgPaymentMethods->MetaAddColumn('ActionClassName');
			$this->dtgPaymentMethods->MetaAddColumn('Description');
			$this->dtgPaymentMethods->MetaAddColumn('ImageUri');
			$this->dtgPaymentMethods->MetaAddColumn('Active');
			$this->dtgPaymentMethods->MetaAddColumn('RequiresCcNumber');
			$this->dtgPaymentMethods->MetaAddColumn('SaveCcNumber');
			$this->dtgPaymentMethods->MetaAddColumn('TestMode');
			$this->dtgPaymentMethods->MetaAddColumn('SortOrder');
			$this->dtgPaymentMethods->MetaAddTypeColumn('PaymentTypeId', 'PaymentType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('PaymentMethod');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new PaymentMethodEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new PaymentMethodEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>