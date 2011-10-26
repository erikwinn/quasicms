<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the ContentBlock class.  It uses the code-generated
	 * ContentBlockMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a ContentBlock columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both content_block_edit.php AND
	 * content_block_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class ContentBlockEditPanel extends QPanel {
		// Local instance of the ContentBlockMetaControl
		protected $mctContentBlock;

		// Controls for ContentBlock's Data Fields
		public $lblId;
		public $txtName;
		public $txtCssclass;
		public $txtTitle;
		public $txtDescription;
		public $chkShowTitle;
		public $chkShowDescription;
		public $chkCollapsable;
		public $txtSortOrder;
		public $lstParentContentBlock;
		public $lstLocation;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References
		public $lstPages;
		public $lstContentItems;
		public $lstMenus;

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
			$this->strTemplate = 'ContentBlockEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the ContentBlockMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctContentBlock = ContentBlockMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on ContentBlock's data fields
			$this->lblId = $this->mctContentBlock->lblId_Create();
			$this->txtName = $this->mctContentBlock->txtName_Create();
			$this->txtCssclass = $this->mctContentBlock->txtCssclass_Create();
			$this->txtTitle = $this->mctContentBlock->txtTitle_Create();
			$this->txtDescription = $this->mctContentBlock->txtDescription_Create();
			$this->chkShowTitle = $this->mctContentBlock->chkShowTitle_Create();
			$this->chkShowDescription = $this->mctContentBlock->chkShowDescription_Create();
			$this->chkCollapsable = $this->mctContentBlock->chkCollapsable_Create();
			$this->txtSortOrder = $this->mctContentBlock->txtSortOrder_Create();
			$this->lstParentContentBlock = $this->mctContentBlock->lstParentContentBlock_Create();
			$this->lstLocation = $this->mctContentBlock->lstLocation_Create();
			$this->lstPages = $this->mctContentBlock->lstPages_Create();
			$this->lstContentItems = $this->mctContentBlock->lstContentItems_Create();
			$this->lstMenus = $this->mctContentBlock->lstMenus_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('ContentBlock') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctContentBlock->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the ContentBlockMetaControl
			$this->mctContentBlock->SaveContentBlock();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the ContentBlockMetaControl
			$this->mctContentBlock->DeleteContentBlock();
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