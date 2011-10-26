<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the ContentCategory class.  It uses the code-generated
	 * ContentCategoryMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a ContentCategory columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both content_category_edit.php AND
	 * content_category_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class ContentCategoryEditPanel extends QPanel {
		// Local instance of the ContentCategoryMetaControl
		protected $mctContentCategory;

		// Controls for ContentCategory's Data Fields
		public $lblId;
		public $txtName;
		public $txtTitle;
		public $txtDescription;
		public $txtImageUri;
		public $lstParentContentCategory;
		public $lstPublicPermissions;
		public $lstUserPermissions;
		public $lstGroupPermissions;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References
		public $lstContentItems;
		public $lstPages;

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
			$this->strTemplate = 'ContentCategoryEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the ContentCategoryMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctContentCategory = ContentCategoryMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on ContentCategory's data fields
			$this->lblId = $this->mctContentCategory->lblId_Create();
			$this->txtName = $this->mctContentCategory->txtName_Create();
			$this->txtTitle = $this->mctContentCategory->txtTitle_Create();
			$this->txtDescription = $this->mctContentCategory->txtDescription_Create();
			$this->txtImageUri = $this->mctContentCategory->txtImageUri_Create();
			$this->lstParentContentCategory = $this->mctContentCategory->lstParentContentCategory_Create();
			$this->lstPublicPermissions = $this->mctContentCategory->lstPublicPermissions_Create();
			$this->lstUserPermissions = $this->mctContentCategory->lstUserPermissions_Create();
			$this->lstGroupPermissions = $this->mctContentCategory->lstGroupPermissions_Create();
			$this->lstContentItems = $this->mctContentCategory->lstContentItems_Create();
			$this->lstPages = $this->mctContentCategory->lstPages_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('ContentCategory') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctContentCategory->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the ContentCategoryMetaControl
			$this->mctContentCategory->SaveContentCategory();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the ContentCategoryMetaControl
			$this->mctContentCategory->DeleteContentCategory();
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