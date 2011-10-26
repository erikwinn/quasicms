<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the ShippingMethod class.  It uses the code-generated
	 * ShippingMethodMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a ShippingMethod columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both shipping_method_edit.php AND
	 * shipping_method_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class ShippingMethodEditPanel extends QPanel {
		// Local instance of the ShippingMethodMetaControl
		protected $mctShippingMethod;

		// Controls for ShippingMethod's Data Fields
		public $lblId;
		public $txtTitle;
		public $txtCarrier;
		public $txtServiceType;
		public $txtClassName;
		public $txtTransitTime;
		public $txtDescription;
		public $chkActive;
		public $chkIsInternational;
		public $chkTestMode;
		public $txtSortOrder;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References

		// Other Controls
		public $btnSave;
		public $btnDelete;
		public $btnCancel;

		// Callback
		protected $strClosePanelMethod;

		public function __construct($objParentObject, $strClosePanelMethod, $intId = null, $strControlId = null) {
			// Call the Parent
			try {
				parent::__construct($objParentObject, $strControlId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}

			// Setup Callback and Template
			$this->strTemplate = 'ShippingMethodEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the ShippingMethodMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctShippingMethod = ShippingMethodMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on ShippingMethod's data fields
			$this->lblId = $this->mctShippingMethod->lblId_Create();
			$this->txtTitle = $this->mctShippingMethod->txtTitle_Create();
			$this->txtCarrier = $this->mctShippingMethod->txtCarrier_Create();
			$this->txtServiceType = $this->mctShippingMethod->txtServiceType_Create();
			$this->txtClassName = $this->mctShippingMethod->txtClassName_Create();
			$this->txtTransitTime = $this->mctShippingMethod->txtTransitTime_Create();
			$this->txtDescription = $this->mctShippingMethod->txtDescription_Create();
			$this->chkActive = $this->mctShippingMethod->chkActive_Create();
			$this->chkIsInternational = $this->mctShippingMethod->chkIsInternational_Create();
			$this->chkTestMode = $this->mctShippingMethod->chkTestMode_Create();
			$this->txtSortOrder = $this->mctShippingMethod->txtSortOrder_Create();

			// Create Buttons and Actions on this Form
			$this->btnSave = new QButton($this);
			$this->btnSave->Text = QApplication::Translate('Save');
			$this->btnSave->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnSave_Click'));
			$this->btnSave->CausesValidation = $this;

			$this->btnCancel = new QButton($this);
			$this->btnCancel->Text = QApplication::Translate('Cancel');
			$this->btnCancel->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCancel_Click'));

			$this->btnDelete = new QButton($this);
			$this->btnDelete->Text = QApplication::Translate('Delete');
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('ShippingMethod') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctShippingMethod->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the ShippingMethodMetaControl
			$this->mctShippingMethod->SaveShippingMethod();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the ShippingMethodMetaControl
			$this->mctShippingMethod->DeleteShippingMethod();
			$this->CloseSelf(true);
		}

		public function btnCancel_Click($strFormId, $strControlId, $strParameter) {
			$this->CloseSelf(false);
		}

		// Close Myself and Call ClosePanelMethod Callback
		protected function CloseSelf($blnChangesMade) {
			$strMethod = $this->strClosePanelMethod;
			$this->objForm->$strMethod($blnChangesMade);
		}
	}
?>