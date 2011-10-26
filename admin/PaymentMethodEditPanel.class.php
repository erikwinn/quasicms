<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the PaymentMethod class.  It uses the code-generated
	 * PaymentMethodMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a PaymentMethod columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both payment_method_edit.php AND
	 * payment_method_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class PaymentMethodEditPanel extends QPanel {
		// Local instance of the PaymentMethodMetaControl
		protected $mctPaymentMethod;

		// Controls for PaymentMethod's Data Fields
		public $lblId;
		public $txtTitle;
		public $txtServiceProvider;
		public $txtServiceType;
		public $txtActionClassName;
		public $txtDescription;
		public $txtImageUri;
		public $chkActive;
		public $chkRequiresCcNumber;
		public $chkSaveCcNumber;
		public $chkTestMode;
		public $txtSortOrder;
		public $lstPaymentType;

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
			$this->strTemplate = 'PaymentMethodEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the PaymentMethodMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctPaymentMethod = PaymentMethodMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on PaymentMethod's data fields
			$this->lblId = $this->mctPaymentMethod->lblId_Create();
			$this->txtTitle = $this->mctPaymentMethod->txtTitle_Create();
			$this->txtServiceProvider = $this->mctPaymentMethod->txtServiceProvider_Create();
			$this->txtServiceType = $this->mctPaymentMethod->txtServiceType_Create();
			$this->txtActionClassName = $this->mctPaymentMethod->txtActionClassName_Create();
			$this->txtDescription = $this->mctPaymentMethod->txtDescription_Create();
			$this->txtImageUri = $this->mctPaymentMethod->txtImageUri_Create();
			$this->chkActive = $this->mctPaymentMethod->chkActive_Create();
			$this->chkRequiresCcNumber = $this->mctPaymentMethod->chkRequiresCcNumber_Create();
			$this->chkSaveCcNumber = $this->mctPaymentMethod->chkSaveCcNumber_Create();
			$this->chkTestMode = $this->mctPaymentMethod->chkTestMode_Create();
			$this->txtSortOrder = $this->mctPaymentMethod->txtSortOrder_Create();
			$this->lstPaymentType = $this->mctPaymentMethod->lstPaymentType_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('PaymentMethod') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctPaymentMethod->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the PaymentMethodMetaControl
			$this->mctPaymentMethod->SavePaymentMethod();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the PaymentMethodMetaControl
			$this->mctPaymentMethod->DeletePaymentMethod();
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