<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the Page class.  It uses the code-generated
	 * PageMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a Page columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both page_edit.php AND
	 * page_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class PageEditPanel extends QPanel {
		// Local instance of the PageMetaControl
		protected $mctPage;

		// Controls for Page's Data Fields
		public $lblId;
		public $lblCreationDate;
		public $lblLastModification;
		public $txtName;
		public $txtTitle;
		public $txtUri;
		public $chkHasHeader;
		public $chkHasLeftColumn;
		public $chkHasRightColumn;
		public $chkHasFooter;
		public $lstPublicPermissions;
		public $lstUserPermissions;
		public $lstGroupPermissions;
		public $lstType;
		public $lstDocType;
		public $lstStatus;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References
		public $lstContentBlocks;
		public $lstContentCategories;
		public $lstHtmlMetaTags;
		public $lstJavaScripts;
		public $lstStyleSheets;
		public $lstUsergroups;

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
			$this->strTemplate = 'PageEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the PageMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctPage = PageMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on Page's data fields
			$this->lblId = $this->mctPage->lblId_Create();
			$this->lblCreationDate = $this->mctPage->lblCreationDate_Create();
			$this->lblLastModification = $this->mctPage->lblLastModification_Create();
			$this->txtName = $this->mctPage->txtName_Create();
			$this->txtTitle = $this->mctPage->txtTitle_Create();
			$this->txtUri = $this->mctPage->txtUri_Create();
			$this->chkHasHeader = $this->mctPage->chkHasHeader_Create();
			$this->chkHasLeftColumn = $this->mctPage->chkHasLeftColumn_Create();
			$this->chkHasRightColumn = $this->mctPage->chkHasRightColumn_Create();
			$this->chkHasFooter = $this->mctPage->chkHasFooter_Create();
			$this->lstPublicPermissions = $this->mctPage->lstPublicPermissions_Create();
			$this->lstUserPermissions = $this->mctPage->lstUserPermissions_Create();
			$this->lstGroupPermissions = $this->mctPage->lstGroupPermissions_Create();
			$this->lstType = $this->mctPage->lstType_Create();
			$this->lstDocType = $this->mctPage->lstDocType_Create();
			$this->lstStatus = $this->mctPage->lstStatus_Create();
			$this->lstContentBlocks = $this->mctPage->lstContentBlocks_Create();
			$this->lstContentCategories = $this->mctPage->lstContentCategories_Create();
			$this->lstHtmlMetaTags = $this->mctPage->lstHtmlMetaTags_Create();
			$this->lstJavaScripts = $this->mctPage->lstJavaScripts_Create();
			$this->lstStyleSheets = $this->mctPage->lstStyleSheets_Create();
			$this->lstUsergroups = $this->mctPage->lstUsergroups_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('Page') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctPage->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the PageMetaControl
			$this->mctPage->SavePage();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the PageMetaControl
			$this->mctPage->DeletePage();
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