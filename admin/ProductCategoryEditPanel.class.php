<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the ProductCategory class.  It uses the code-generated
	 * ProductCategoryMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a ProductCategory columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both product_category_edit.php AND
	 * product_category_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class ProductCategoryEditPanel extends QPanel {
		// Local instance of the ProductCategoryMetaControl
		protected $mctProductCategory;

		// Controls for ProductCategory's Data Fields
		public $lblId;
		public $txtName;
		public $txtTitle;
		public $txtDescription;
		public $txtImageUri;
		public $lstParentProductCategory;
		public $lstPublicPermissions;
		public $lstUserPermissions;
		public $lstGroupPermissions;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References
		public $lstProducts;

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
			$this->strTemplate = 'ProductCategoryEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the ProductCategoryMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctProductCategory = ProductCategoryMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on ProductCategory's data fields
			$this->lblId = $this->mctProductCategory->lblId_Create();
			$this->txtName = $this->mctProductCategory->txtName_Create();
			$this->txtTitle = $this->mctProductCategory->txtTitle_Create();
			$this->txtDescription = $this->mctProductCategory->txtDescription_Create();
			$this->txtImageUri = $this->mctProductCategory->txtImageUri_Create();
			$this->lstParentProductCategory = $this->mctProductCategory->lstParentProductCategory_Create();
			$this->lstPublicPermissions = $this->mctProductCategory->lstPublicPermissions_Create();
			$this->lstUserPermissions = $this->mctProductCategory->lstUserPermissions_Create();
			$this->lstGroupPermissions = $this->mctProductCategory->lstGroupPermissions_Create();
			$this->lstProducts = $this->mctProductCategory->lstProducts_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('ProductCategory') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctProductCategory->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the ProductCategoryMetaControl
			$this->mctProductCategory->SaveProductCategory();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the ProductCategoryMetaControl
			$this->mctProductCategory->DeleteProductCategory();
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