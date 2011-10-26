<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the HtmlMetaTag class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of HtmlMetaTag objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this HtmlMetaTagListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class HtmlMetaTagListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list HtmlMetaTags
		public $dtgHtmlMetaTags;

		// Other public QControls in this panel
		public $btnCreateNew;
		public $pxyEdit;

		// Callback Method Names
		protected $strSetEditPanelMethod;
		protected $strCloseEditPanelMethod;
		
		public function __construct($objParentObject, $strSetEditPanelMethod, $strCloseEditPanelMethod, $strControlId = null) {
			// Call the Parent
			try {
				parent::__construct($objParentObject, $strControlId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}

			// Record Method Callbacks
			$this->strSetEditPanelMethod = $strSetEditPanelMethod;
			$this->strCloseEditPanelMethod = $strCloseEditPanelMethod;

			// Setup the Template
			$this->Template = 'HtmlMetaTagListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgHtmlMetaTags = new HtmlMetaTagDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgHtmlMetaTags->CssClass = 'datagrid';
			$this->dtgHtmlMetaTags->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgHtmlMetaTags->Paginator = new QPaginator($this->dtgHtmlMetaTags);
			$this->dtgHtmlMetaTags->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgHtmlMetaTags->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for html_meta_tag's properties, or you
			// can traverse down QQN::html_meta_tag() to display fields that are down the hierarchy)
			$this->dtgHtmlMetaTags->MetaAddColumn('Id');
			$this->dtgHtmlMetaTags->MetaAddColumn('Name');
			$this->dtgHtmlMetaTags->MetaAddColumn('Content');
			$this->dtgHtmlMetaTags->MetaAddColumn('Type');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('HtmlMetaTag');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new HtmlMetaTagEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new HtmlMetaTagEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>