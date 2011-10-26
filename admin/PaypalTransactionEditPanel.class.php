<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the PaypalTransaction class.  It uses the code-generated
	 * PaypalTransactionMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a PaypalTransaction columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both paypal_transaction_edit.php AND
	 * paypal_transaction_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class PaypalTransactionEditPanel extends QPanel {
		// Local instance of the PaypalTransactionMetaControl
		protected $mctPaypalTransaction;

		// Controls for PaypalTransaction's Data Fields
		public $lblId;
		public $lstOrder;
		public $txtCorrelationId;
		public $txtTransactionId;
		public $txtPpToken;
		public $txtPayerId;
		public $txtPayerStatus;
		public $txtPaymentStatus;
		public $txtAckReturned;
		public $txtApiAction;
		public $calTimeStamp;
		public $txtApiVersion;
		public $txtMessages;
		public $txtAmount;
		public $txtPpFee;
		public $lstPaymentMethod;

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
			$this->strTemplate = 'PaypalTransactionEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the PaypalTransactionMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctPaypalTransaction = PaypalTransactionMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on PaypalTransaction's data fields
			$this->lblId = $this->mctPaypalTransaction->lblId_Create();
			$this->lstOrder = $this->mctPaypalTransaction->lstOrder_Create();
			$this->txtCorrelationId = $this->mctPaypalTransaction->txtCorrelationId_Create();
			$this->txtTransactionId = $this->mctPaypalTransaction->txtTransactionId_Create();
			$this->txtPpToken = $this->mctPaypalTransaction->txtPpToken_Create();
			$this->txtPayerId = $this->mctPaypalTransaction->txtPayerId_Create();
			$this->txtPayerStatus = $this->mctPaypalTransaction->txtPayerStatus_Create();
			$this->txtPaymentStatus = $this->mctPaypalTransaction->txtPaymentStatus_Create();
			$this->txtAckReturned = $this->mctPaypalTransaction->txtAckReturned_Create();
			$this->txtApiAction = $this->mctPaypalTransaction->txtApiAction_Create();
			$this->calTimeStamp = $this->mctPaypalTransaction->calTimeStamp_Create();
			$this->txtApiVersion = $this->mctPaypalTransaction->txtApiVersion_Create();
			$this->txtMessages = $this->mctPaypalTransaction->txtMessages_Create();
			$this->txtAmount = $this->mctPaypalTransaction->txtAmount_Create();
			$this->txtPpFee = $this->mctPaypalTransaction->txtPpFee_Create();
			$this->lstPaymentMethod = $this->mctPaypalTransaction->lstPaymentMethod_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('PaypalTransaction') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctPaypalTransaction->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the PaypalTransactionMetaControl
			$this->mctPaypalTransaction->SavePaypalTransaction();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the PaypalTransactionMetaControl
			$this->mctPaypalTransaction->DeletePaypalTransaction();
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