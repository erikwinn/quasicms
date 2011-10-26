<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the ProductImage class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of ProductImage objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this ProductImageListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class ProductImageListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list ProductImages
		public $dtgProductImages;

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
			$this->Template = 'ProductImageListPanel.tpl.php';

			// Instantiate the Meta DataGrid
			$this->dtgProductImages = new ProductImageDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgProductImages->CssClass = 'datagrid';
			$this->dtgProductImages->AlternateRowStyle->CssClass = 'alternate';

			// Add Pagination (if desired)
			$this->dtgProductImages->Paginator = new QPaginator($this->dtgProductImages);
			$this->dtgProductImages->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgProductImages->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for product_image's properties, or you
			// can traverse down QQN::product_image() to display fields that are down the hierarchy)
			$this->dtgProductImages->MetaAddColumn('Id');
			$this->dtgProductImages->MetaAddColumn(QQN::ProductImage()->Product);
			$this->dtgProductImages->MetaAddColumn('Title');
			$this->dtgProductImages->MetaAddColumn('AltTag');
			$this->dtgProductImages->MetaAddColumn('Description');
			$this->dtgProductImages->MetaAddColumn('Uri');
			$this->dtgProductImages->MetaAddColumn('XSize');
			$this->dtgProductImages->MetaAddColumn('YSize');
			$this->dtgProductImages->MetaAddTypeColumn('SizeType', 'ImageSizeType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('ProductImage');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new ProductImageEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new ProductImageEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>