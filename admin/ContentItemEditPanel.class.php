<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the ContentItem class.  It uses the code-generated
	 * ContentItemMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a ContentItem columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both content_item_edit.php AND
	 * content_item_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class ContentItemEditPanel extends QPanel {
		// Local instance of the ContentItemMetaControl
		protected $mctContentItem;

		// Controls for ContentItem's Data Fields
		public $lblId;
		public $txtName;
		public $txtCssclass;
		public $txtTitle;
		public $txtDescription;
		public $txtText;
		public $txtSortOrder;
		public $chkShowTitle;
		public $chkShowDescription;
		public $chkShowCreator;
		public $chkShowCreationDate;
		public $chkShowLastModification;
		public $lblCreatorId;
		public $txtCopyrightNotice;
		public $lblCreationDate;
		public $lblLastModification;
		public $lstPublicPermissions;
		public $lstUserPermissions;
		public $lstGroupPermissions;
		public $lstType;
		public $lstStatus;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References
		public $lstContentBlocks;
		public $lstContentCategories;
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
			$this->strTemplate = 'ContentItemEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the ContentItemMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctContentItem = ContentItemMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on ContentItem's data fields
			$this->lblId = $this->mctContentItem->lblId_Create();
			$this->txtName = $this->mctContentItem->txtName_Create();
			$this->txtCssclass = $this->mctContentItem->txtCssclass_Create();
			$this->txtTitle = $this->mctContentItem->txtTitle_Create();
			$this->txtDescription = $this->mctContentItem->txtDescription_Create();
			$this->txtText = $this->mctContentItem->txtText_Create();
			$this->txtSortOrder = $this->mctContentItem->txtSortOrder_Create();
			$this->chkShowTitle = $this->mctContentItem->chkShowTitle_Create();
			$this->chkShowDescription = $this->mctContentItem->chkShowDescription_Create();
			$this->chkShowCreator = $this->mctContentItem->chkShowCreator_Create();
			$this->chkShowCreationDate = $this->mctContentItem->chkShowCreationDate_Create();
			$this->chkShowLastModification = $this->mctContentItem->chkShowLastModification_Create();
			$this->lblCreatorId = $this->mctContentItem->lblCreatorId_Create();
			$this->txtCopyrightNotice = $this->mctContentItem->txtCopyrightNotice_Create();
			$this->lblCreationDate = $this->mctContentItem->lblCreationDate_Create();
			$this->lblLastModification = $this->mctContentItem->lblLastModification_Create();
			$this->lstPublicPermissions = $this->mctContentItem->lstPublicPermissions_Create();
			$this->lstUserPermissions = $this->mctContentItem->lstUserPermissions_Create();
			$this->lstGroupPermissions = $this->mctContentItem->lstGroupPermissions_Create();
			$this->lstType = $this->mctContentItem->lstType_Create();
			$this->lstStatus = $this->mctContentItem->lstStatus_Create();
			$this->lstContentBlocks = $this->mctContentItem->lstContentBlocks_Create();
			$this->lstContentCategories = $this->mctContentItem->lstContentCategories_Create();
			$this->lstUsergroups = $this->mctContentItem->lstUsergroups_Create();

			// Create Buttons and Actions on this Form
			$this->btnSave = new QButton($this);
			$this->btnSave->Text = QApplication::Translate('Save');
            $this->btnSave->AddAction(new QClickEvent(), new QJavaScriptAction('tinyMCE.triggerSave();'));
			$this->btnSave->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnSave_Click'));
			$this->btnSave->CausesValidation = $this;

			$this->btnCancel = new QButton($this);
			$this->btnCancel->Text = QApplication::Translate('Cancel');
			$this->btnCancel->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCancel_Click'));

			$this->btnDelete = new QButton($this);
			$this->btnDelete->Text = QApplication::Translate('Delete');
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('ContentItem') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctContentItem->EditMode;
            
            QApplication::ExecuteJavaScript('tinyMCE.init({
                mode : "textareas",
                theme : "advanced",
                });');
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the ContentItemMetaControl
			$this->mctContentItem->SaveContentItem();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the ContentItemMetaControl
			$this->mctContentItem->DeleteContentItem();
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