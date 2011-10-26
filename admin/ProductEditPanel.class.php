<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the Product class.  It uses the code-generated
	 * ProductMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a Product columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both product_edit.php AND
	 * product_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class ProductEditPanel extends QPanel {
		// Local instance of the ProductMetaControl
		protected $mctProduct;

		// Controls for Product's Data Fields
		public $lblId;
		public $lblManufacturer;
		public $lblSupplier;
		public $lblCreationDate;
		public $txtName;
		public $txtModel;
		public $txtShortDescription;
		public $txtLongDescription;
		public $txtMsrp;
		public $txtWholesalePrice;
		public $txtRetailPrice;
		public $txtCost;
		public $txtWeight;
		public $txtHeight;
		public $txtWidth;
		public $txtDepth;
		public $chkIsVirtual;
		public $lstType;
		public $lstStatus;
		public $txtViewCount;
		public $lstUserPermissions;
		public $lstPublicPermissions;
		public $lstGroupPermissions;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References
		public $lstProductCategoriesAsCategory;
		public $lstParentProductsAsRelated;
		public $lstProductsAsRelated;

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
			$this->strTemplate = 'ProductEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the ProductMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctProduct = ProductMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on Product's data fields
			$this->lblId = $this->mctProduct->lblId_Create();
			$this->lblManufacturer = $this->mctProduct->lblManufacturerId_Create();
			$this->lblSupplier = $this->mctProduct->lblSupplierId_Create();
			$this->lblCreationDate = $this->mctProduct->lblCreationDate_Create();
			$this->txtName = $this->mctProduct->txtName_Create();
			$this->txtModel = $this->mctProduct->txtModel_Create();
			$this->txtShortDescription = $this->mctProduct->txtShortDescription_Create();
			$this->txtLongDescription = $this->mctProduct->txtLongDescription_Create();
			$this->txtMsrp = $this->mctProduct->txtMsrp_Create();
			$this->txtWholesalePrice = $this->mctProduct->txtWholesalePrice_Create();
			$this->txtRetailPrice = $this->mctProduct->txtRetailPrice_Create();
			$this->txtCost = $this->mctProduct->txtCost_Create();
			$this->txtWeight = $this->mctProduct->txtWeight_Create();
			$this->txtHeight = $this->mctProduct->txtHeight_Create();
			$this->txtWidth = $this->mctProduct->txtWidth_Create();
			$this->txtDepth = $this->mctProduct->txtDepth_Create();
			$this->chkIsVirtual = $this->mctProduct->chkIsVirtual_Create();
			$this->lstType = $this->mctProduct->lstType_Create();
			$this->lstStatus = $this->mctProduct->lstStatus_Create();
			$this->txtViewCount = $this->mctProduct->txtViewCount_Create();
			$this->lstUserPermissions = $this->mctProduct->lstUserPermissions_Create();
			$this->lstPublicPermissions = $this->mctProduct->lstPublicPermissions_Create();
			$this->lstGroupPermissions = $this->mctProduct->lstGroupPermissions_Create();
// 			$this->lstProductCategoriesAsCategory = $this->mctProduct->lstProductCategoriesAsCategory_Create();
// 			$this->lstParentProductsAsRelated = $this->mctProduct->lstParentProductsAsRelated_Create();
// 			$this->lstProductsAsRelated = $this->mctProduct->lstProductsAsRelated_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('Product') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctProduct->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the ProductMetaControl
			$this->mctProduct->SaveProduct();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the ProductMetaControl
			$this->mctProduct->DeleteProduct();
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