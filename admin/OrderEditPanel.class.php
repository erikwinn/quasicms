<?php
	/**
	 * This is a quick-and-dirty draft QPanel object to do Create, Edit, and Delete functionality
	 * of the Order class.  It uses the code-generated
	 * OrderMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a Order columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both order_edit.php AND
	 * order_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Quasi
	 * @subpackage Drafts
	 */
	class OrderEditPanel extends QPanel {
		// Local instance of the OrderMetaControl
		protected $mctOrder;
        
        protected $pnlListPanel;
        
        public $dtgOrderItems;
        public $pxyEditOrderItem;
        public $pxyViewProduct;
        public $objOrderItemsPaginator;
        
        public $lblOrderTotal;
		// Controls for Order's Data Fields
		public $lblId;
		public $lblAccount;
		public $lblCreationDate;
		public $lblLastModificationDate;
		public $lblCompletionDate;
		public $txtShippingCost;
		public $txtProductTotalCost;
		public $txtShippingCharged;
		public $txtHandlingCharged;
		public $txtTax;
		public $txtProductTotalCharged;
		public $txtShippingNamePrefix;
		public $txtShippingFirstName;
		public $txtShippingMiddleName;
		public $txtShippingLastName;
		public $txtShippingNameSuffix;
		public $txtShippingStreet1;
		public $txtShippingStreet2;
		public $txtShippingSuburb;
		public $txtShippingCounty;
		public $txtShippingCity;
		public $lstShippingZone;
		public $lstShippingCountry;
		public $txtShippingPostalCode;
		public $txtBillingNamePrefix;
		public $txtBillingFirstName;
		public $txtBillingMiddleName;
		public $txtBillingLastName;
		public $txtBillingNameSuffix;
		public $txtBillingStreet1;
		public $txtBillingStreet2;
		public $txtBillingSuburb;
		public $txtBillingCounty;
		public $txtBillingCity;
		public $lstBillingZone;
		public $lstBillingCountry;
		public $txtBillingPostalCode;
		public $txtNotes;
		public $lstShippingMethod;
		public $lstPaymentMethod;
		public $lstStatus;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References

		// Other Controls
		public $btnSave;
		public $btnDelete;
		public $btnCancel;

		// Callback
		protected $strClosePanelMethod;

		public function __construct($objParentObject, $strClosePanelMethod, $intId = null, $strControlId = null)
        {
            $this->pnlListPanel =& $objParentObject;
			
            try {
				parent::__construct($objParentObject, $strControlId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}

			$this->strTemplate = 'OrderEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			$this->mctOrder = OrderMetaControl::Create($this, $intId);

			$this->lblId = $this->mctOrder->lblId_Create();
            $this->lblId->Name = "Order Number:";
            
			$this->lblAccount = $this->mctOrder->lblAccountId_Create();
            $this->lblAccount->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'lblAccount_Click'));
            $this->lblAccount->AddCssClass('button');
            $this->lblAccount->Name = 'Account No.: ' . $this->mctOrder->Order->AccountId;
            
			$this->lblCreationDate = $this->mctOrder->lblCreationDate_Create();
			$this->lblLastModificationDate = $this->mctOrder->lblLastModificationDate_Create();
			$this->lblCompletionDate = $this->mctOrder->lblCompletionDate_Create();
			$this->txtShippingCost = $this->mctOrder->txtShippingCost_Create();
			$this->txtProductTotalCost = $this->mctOrder->txtProductTotalCost_Create();
			$this->txtShippingCharged = $this->mctOrder->txtShippingCharged_Create();
			$this->txtHandlingCharged = $this->mctOrder->txtHandlingCharged_Create();
			$this->txtTax = $this->mctOrder->txtTax_Create();
			$this->txtProductTotalCharged = $this->mctOrder->txtProductTotalCharged_Create();
			$this->txtShippingNamePrefix = $this->mctOrder->txtShippingNamePrefix_Create();
			$this->txtShippingFirstName = $this->mctOrder->txtShippingFirstName_Create();
			$this->txtShippingMiddleName = $this->mctOrder->txtShippingMiddleName_Create();
			$this->txtShippingLastName = $this->mctOrder->txtShippingLastName_Create();
			$this->txtShippingNameSuffix = $this->mctOrder->txtShippingNameSuffix_Create();
			$this->txtShippingStreet1 = $this->mctOrder->txtShippingStreet1_Create();
			$this->txtShippingStreet2 = $this->mctOrder->txtShippingStreet2_Create();
			$this->txtShippingSuburb = $this->mctOrder->txtShippingSuburb_Create();
			$this->txtShippingCounty = $this->mctOrder->txtShippingCounty_Create();
			$this->txtShippingCity = $this->mctOrder->txtShippingCity_Create();
			$this->lstShippingZone = $this->mctOrder->lstShippingZone_Create();
			$this->lstShippingCountry = $this->mctOrder->lstShippingCountry_Create();
			$this->txtShippingPostalCode = $this->mctOrder->txtShippingPostalCode_Create();
			$this->txtBillingNamePrefix = $this->mctOrder->txtBillingNamePrefix_Create();
			$this->txtBillingFirstName = $this->mctOrder->txtBillingFirstName_Create();
			$this->txtBillingMiddleName = $this->mctOrder->txtBillingMiddleName_Create();
			$this->txtBillingLastName = $this->mctOrder->txtBillingLastName_Create();
			$this->txtBillingNameSuffix = $this->mctOrder->txtBillingNameSuffix_Create();
			$this->txtBillingStreet1 = $this->mctOrder->txtBillingStreet1_Create();
			$this->txtBillingStreet2 = $this->mctOrder->txtBillingStreet2_Create();
			$this->txtBillingSuburb = $this->mctOrder->txtBillingSuburb_Create();
			$this->txtBillingCounty = $this->mctOrder->txtBillingCounty_Create();
			$this->txtBillingCity = $this->mctOrder->txtBillingCity_Create();
			$this->lstBillingZone = $this->mctOrder->lstBillingZone_Create();
			$this->lstBillingCountry = $this->mctOrder->lstBillingCountry_Create();
			$this->txtBillingPostalCode = $this->mctOrder->txtBillingPostalCode_Create();
			$this->txtNotes = $this->mctOrder->txtNotes_Create();
			$this->lstShippingMethod = $this->mctOrder->lstShippingMethod_Create();
			$this->lstPaymentMethod = $this->mctOrder->lstPaymentMethod_Create();
			$this->lstStatus = $this->mctOrder->lstStatus_Create();
            
            $strOrderTotal =  money_format("%n", $this->mctOrder->Order->ProductTotalCharged
                                                  + $this->mctOrder->Order->ShippingCharged
                                                  + $this->mctOrder->Order->HandlingCharged
                                                  + $this->mctOrder->Order->Tax );
            $this->lblOrderTotal = new QLabel($this);
            $this->lblOrderTotal->Name = 'Order Total: ';
            $this->lblOrderTotal->Text = $strOrderTotal;

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
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('Order') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctOrder->EditMode;
            
            $this->dtgOrderItems = new OrderItemDataGrid($this);

            $this->dtgOrderItems->CssClass = 'datagrid';
            $this->dtgOrderItems->SetDataBinder('OrderItemsDataBinder', $this);
            $this->dtgOrderItems->AlternateRowStyle->CssClass = 'alternate';
            $this->objOrderItemsPaginator = new QPaginator($this->dtgOrderItems);
            $this->dtgOrderItems->Paginator = $this->objOrderItemsPaginator;
            $this->dtgOrderItems->ItemsPerPage = 20;

            $this->pxyEditOrderItem = new QControlProxy($this);
            $this->pxyEditOrderItem->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEditOrderItem_Click'));
            $this->pxyViewProduct = new QControlProxy($this);
            $this->pxyViewProduct->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyViewProduct_Click'));
            $this->dtgOrderItems->MetaAddEditProxyColumn($this->pxyEditOrderItem, 'Edit', 'Edit');
            $this->dtgOrderItems->MetaAddProxyColumn($this->pxyViewProduct, QQN::OrderItem()->Product);
            $this->dtgOrderItems->MetaAddColumn('Quantity');
            
            $strItemPriceParam = '<?= money_format("%n", $_ITEM->Product->RetailPrice  ) ?>';
            $objItemPriceColumn = new QDataGridColumn('Item Price', $strItemPriceParam );
            $this->dtgOrderItems->AddColumn($objItemPriceColumn);
            
            $strItemTotalParam = '<?= money_format("%n", $_ITEM->Quantity '
                                                 . ' * $_ITEM->Product->RetailPrice  ) ?>';
            $objItemTotalColumn = new QDataGridColumn('Item Total', $strItemTotalParam );
            $this->dtgOrderItems->AddColumn($objItemTotalColumn);

        }

        public function lblAccount_Click($strFormId, $strControlId, $strParameter)
        {
            $objEditPanel = new AccountEditPanel($this->pnlListPanel, 'CloseEditPane', $this->mctOrder->Order->AccountId);
            $this->objForm->SetEditPane($objEditPanel);
        }
        public function pxyEditOrderItem_Click($strFormId, $strControlId, $strParameter)
        {
            $strParameterArray = explode(',', $strParameter);
//            $objEditPanel = new OrderItemEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0], $strParameterArray[1]);
            $objEditPanel = new OrderItemEditPanel($this->pnlListPanel, 'CloseEditPane', $strParameterArray[0], $strParameterArray[1]);
            $this->objForm->SetEditPane($objEditPanel);
        }
        public function pxyViewProduct_Click($strFormId, $strControlId, $strParameter)
        {
            $strParameterArray = explode(',', $strParameter);
            $objOrderItem = OrderItem::Load($strParameterArray[0], $strParameterArray[1]);
            
//            $objEditPanel = new OrderItemEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0], $strParameterArray[1]);
            $objEditPanel = new ProductEditPanel($this->pnlListPanel, 'CloseEditPane', $objOrderItem->ProductId);
            $this->objForm->SetEditPane($objEditPanel);
        }
       
       /**
        * This binds the OrderItems Datagrid data retrieval to this Order, the items listed in the Datagrid will be those
        * associated with this user in the database.
        *
        * If a paginator is set on this DataBinder, it will use it.  If not, then no pagination will be used.
        * It will also perform any sorting requested in by clicking on the column titles in the Datagrid.
        */
        public function OrderItemsDataBinder()
        {
            if ($this->objOrderItemsPaginator)
                $this->dtgOrderItems->TotalItemCount = OrderItem::CountByOrderId($this->mctOrder->Order->Id) ;
            
            $objClauses = array();

            // If a column is selected to be sorted, and if that column has a OrderByClause set on it, then let's add
            // the OrderByClause to the $objClauses array - this is in the datagrid if the user clicks on column title
            if ($objClause = $this->dtgOrderItems->OrderByClause)
                array_push($objClauses, $objClause);

            // Add the LimitClause information, as well
            if ($objClause = $this->dtgOrderItems->LimitClause)
                array_push($objClauses, $objClause);

//            array_push($objClauses, QQ::OrderBy(QQN::OrderItem()->CreationDate, false) );
            
            $intOrderId = $this->mctOrder->Order->Id;
            $this->dtgOrderItems->DataSource = OrderItem::LoadArrayByOrderId(
                $intOrderId, $objClauses
            );
        }

		// Control AjaxAction Event Handlers
		public function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the OrderMetaControl
			$this->mctOrder->SaveOrder();
			$this->CloseSelf(true);
		}

		public function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the OrderMetaControl
			$this->mctOrder->DeleteOrder();
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