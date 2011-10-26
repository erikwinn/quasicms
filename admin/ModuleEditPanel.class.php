<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the Module class.  It uses the code-generated
	 * ModuleMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a Module columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both module_edit.php AND
	 * module_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class ModuleEditPanel extends QPanel {
		// Local instance of the ModuleMetaControl
		protected $mctModule;

		// Controls for Module's Data Fields
		public $lblId;
		public $txtName;
		public $txtCssclass;
		public $txtTitle;
		public $txtDescription;
		public $txtClassName;
		public $chkShowTitle;
		public $chkShowDescription;
		public $lstContentBlock;
		public $lstParentModule;
		public $lstPublicPermissions;
		public $lstUserPermissions;
		public $lstGroupPermissions;

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
			$this->strTemplate = 'ModuleEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the ModuleMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctModule = ModuleMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on Module's data fields
			$this->lblId = $this->mctModule->lblId_Create();
			$this->txtName = $this->mctModule->txtName_Create();
			$this->txtCssclass = $this->mctModule->txtCssclass_Create();
			$this->txtTitle = $this->mctModule->txtTitle_Create();
			$this->txtDescription = $this->mctModule->txtDescription_Create();
			$this->txtClassName = $this->mctModule->txtClassName_Create();
			$this->chkShowTitle = $this->mctModule->chkShowTitle_Create();
			$this->chkShowDescription = $this->mctModule->chkShowDescription_Create();
			$this->lstContentBlock = $this->mctModule->lstContentBlock_Create();
			$this->lstParentModule = $this->mctModule->lstParentModule_Create();
			$this->lstPublicPermissions = $this->mctModule->lstPublicPermissions_Create();
			$this->lstUserPermissions = $this->mctModule->lstUserPermissions_Create();
			$this->lstGroupPermissions = $this->mctModule->lstGroupPermissions_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('Module') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctModule->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the ModuleMetaControl
			$this->mctModule->SaveModule();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the ModuleMetaControl
			$this->mctModule->DeleteModule();
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