<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the StyleSheet class.  It uses the code-generated
	 * StyleSheetMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a StyleSheet columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both style_sheet_edit.php AND
	 * style_sheet_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class StyleSheetEditPanel extends QPanel {
		// Local instance of the StyleSheetMetaControl
		protected $mctStyleSheet;

		// Controls for StyleSheet's Data Fields
		public $lblId;
		public $txtName;
		public $txtDescription;
		public $txtFilename;
		public $txtType;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References
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
			$this->strTemplate = 'StyleSheetEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			// Construct the StyleSheetMetaControl
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctStyleSheet = StyleSheetMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on StyleSheet's data fields
			$this->lblId = $this->mctStyleSheet->lblId_Create();
			$this->txtName = $this->mctStyleSheet->txtName_Create();
			$this->txtDescription = $this->mctStyleSheet->txtDescription_Create();
			$this->txtFilename = $this->mctStyleSheet->txtFilename_Create();
			$this->txtType = $this->mctStyleSheet->txtType_Create();
			$this->lstPages = $this->mctStyleSheet->lstPages_Create();

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('StyleSheet') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctStyleSheet->EditMode;
		}

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the StyleSheetMetaControl
			$this->mctStyleSheet->SaveStyleSheet();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the StyleSheetMetaControl
			$this->mctStyleSheet->DeleteStyleSheet();
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