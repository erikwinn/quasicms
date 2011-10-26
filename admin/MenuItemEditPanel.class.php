<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the MenuItem class.  It uses the code-generated
	 * MenuItemMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a MenuItem columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both menu_item_edit.php AND
	 * menu_item_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class MenuItemEditPanel extends QPanel {
		// Local instance of the MenuItemMetaControl
		protected $mctMenuItem;

		// Controls for MenuItem's Data Fields
		public $lblId;
		public $txtName;
		public $txtCssClass;
		public $txtLabel;
		public $txtUri;
		public $chkIsLocal;
		public $chkIsSsl;
		public $txtSortOrder;
		public $lstPublicPermissions;
		public $lstUserPermissions;
		public $lstGroupPermissions;
		public $lstStatus;
		public $lstType;
		public $lstPage;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References
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
			$this->strTemplate = 'MenuItemEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the MenuItemMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctMenuItem = MenuItemMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on MenuItem's data fields
			$this->lblId = $this->mctMenuItem->lblId_Create();
			$this->txtName = $this->mctMenuItem->txtName_Create();
			$this->txtCssClass = $this->mctMenuItem->txtCssClass_Create();
			$this->txtLabel = $this->mctMenuItem->txtLabel_Create();
			$this->txtUri = $this->mctMenuItem->txtUri_Create();
			$this->chkIsLocal = $this->mctMenuItem->chkIsLocal_Create();
			$this->chkIsSsl = $this->mctMenuItem->chkIsSsl_Create();
			$this->txtSortOrder = $this->mctMenuItem->txtSortOrder_Create();
			$this->lstPublicPermissions = $this->mctMenuItem->lstPublicPermissions_Create();
			$this->lstUserPermissions = $this->mctMenuItem->lstUserPermissions_Create();
			$this->lstGroupPermissions = $this->mctMenuItem->lstGroupPermissions_Create();
			$this->lstStatus = $this->mctMenuItem->lstStatus_Create();
			$this->lstType = $this->mctMenuItem->lstType_Create();
			$this->lstPage = $this->mctMenuItem->lstPage_Create();
			$this->lstMenus = $this->mctMenuItem->lstMenus_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('MenuItem') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctMenuItem->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the MenuItemMetaControl
			$this->mctMenuItem->SaveMenuItem();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the MenuItemMetaControl
			$this->mctMenuItem->DeleteMenuItem();
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