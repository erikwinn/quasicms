<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the Menu class.  It uses the code-generated
	 * MenuMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a Menu columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both menu_edit.php AND
	 * menu_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class MenuEditPanel extends QPanel {
		// Local instance of the MenuMetaControl
		protected $mctMenu;

		// Controls for Menu's Data Fields
		public $lblId;
		public $txtName;
		public $txtTitle;
		public $txtCssClass;
		public $txtSortOrder;
		public $chkShowTitle;
		public $txtMenuItemId;
		public $lstPublicPermissions;
		public $lstUserPermissions;
		public $lstGroupPermissions;
		public $lstStatus;
		public $lstType;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References
		public $lstContentBlocks;
		public $lstMenuItemsAsItem;

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
			$this->strTemplate = 'MenuEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the MenuMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctMenu = MenuMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on Menu's data fields
			$this->lblId = $this->mctMenu->lblId_Create();
			$this->txtName = $this->mctMenu->txtName_Create();
			$this->txtTitle = $this->mctMenu->txtTitle_Create();
			$this->txtCssClass = $this->mctMenu->txtCssClass_Create();
			$this->txtSortOrder = $this->mctMenu->txtSortOrder_Create();
			$this->chkShowTitle = $this->mctMenu->chkShowTitle_Create();
			$this->txtMenuItemId = $this->mctMenu->txtMenuItemId_Create();
			$this->lstPublicPermissions = $this->mctMenu->lstPublicPermissions_Create();
			$this->lstUserPermissions = $this->mctMenu->lstUserPermissions_Create();
			$this->lstGroupPermissions = $this->mctMenu->lstGroupPermissions_Create();
			$this->lstStatus = $this->mctMenu->lstStatus_Create();
			$this->lstType = $this->mctMenu->lstType_Create();
			$this->lstContentBlocks = $this->mctMenu->lstContentBlocks_Create();
			$this->lstMenuItemsAsItem = $this->mctMenu->lstMenuItemsAsItem_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('Menu') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctMenu->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the MenuMetaControl
			$this->mctMenu->SaveMenu();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the MenuMetaControl
			$this->mctMenu->DeleteMenu();
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