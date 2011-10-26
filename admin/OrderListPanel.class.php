<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the Order class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of Order objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this OrderListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class OrderListPanel extends QPanel
    {
		// Local instance of the Meta DataGrid to list Orders
		public $dtgOrders;

		// Other public QControls in this panel
		public $btnCreateNew;
        public $pxyViewOrder;
        public $pxyViewAccount;
                
        public $lblMessage;

        public $txtOrderNumberSearch;        
        public $txtAccountNumberSearch;
        public $lstStatus;
		
        protected $intOrderStatusId = null;
        protected $intAccountId = null;
              
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
			$this->Template = 'OrderListPanel.tpl.php';

            //messages (eg. Name not found ..)
            $this->lblMessage = new QLabel($this);
                     
            $this->txtOrderNumberSearch = new QIntegerTextBox($this);
            $this->txtOrderNumberSearch->AddAction(new QEnterKeyEvent(), new QServerControlAction($this, 'txtViewOrder_Click'));
            $this->txtOrderNumberSearch->Name = 'Order No.:';
            $this->txtOrderNumberSearch->Width = '5em';
            $this->txtOrderNumberSearch->CausesValidation = $this->txtOrderNumberSearch;
            
            $this->txtAccountNumberSearch = new QIntegerTextBox($this);
            $this->txtAccountNumberSearch->AddAction(new QEnterKeyEvent(), new QServerControlAction($this, 'txtViewByAccount_Click'));
            $this->txtAccountNumberSearch->Name = 'Account No.:';
            $this->txtAccountNumberSearch->Width = '5em';
            $this->txtAccountNumberSearch->CausesValidation = $this->txtAccountNumberSearch;
            
            $this->lstStatus = new QListBox($this, $strControlId);
            $this->lstStatus->Name = 'Select Status:';
            $this->lstStatus->AddItem(new QListItem('Any', 0));
            foreach (OrderStatusType::$NameArray as $intId => $strValue)
                $this->lstStatus->AddItem(new QListItem($strValue, $intId+1));
            $this->lstStatus->AddAction(new QChangeEvent(), new QAjaxControlAction($this, 'lstStatus_Selected') );
            
			// Instantiate the Meta DataGrid
			$this->dtgOrders = new OrderDataGrid($this);
            $this->dtgOrders->SetDataBinder('OrderDataBinder', $this);

			$this->dtgOrders->CssClass = 'datagrid';
			$this->dtgOrders->AlternateRowStyle->CssClass = 'alternate';

			$this->dtgOrders->Paginator = new QPaginator($this->dtgOrders);
			$this->dtgOrders->ItemsPerPage = 25;

            $this->pxyViewOrder = new QControlProxy($this);
            $this->pxyViewOrder->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyViewOrder_Click'));
            $this->pxyViewAccount = new QControlProxy($this);
            $this->pxyViewAccount->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyViewAccount_Click'));

			$this->dtgOrders->MetaAddProxyColumn($this->pxyViewOrder, 'Id');
			$this->dtgOrders->MetaAddProxyColumn($this->pxyViewAccount, QQN::Order()->Account);
            $this->dtgOrders->GetColumn(0)->Name = "Order Number";
                     
			$this->dtgOrders->MetaAddColumn('CreationDate');
			$this->dtgOrders->MetaAddColumn('LastModificationDate');
			$this->dtgOrders->MetaAddColumn('CompletionDate');
			$this->dtgOrders->MetaAddColumn(QQN::Order()->ShippingMethod);
			$this->dtgOrders->MetaAddColumn(QQN::Order()->PaymentMethod);
			$this->dtgOrders->MetaAddTypeColumn('StatusId', 'OrderStatusType');
            
            $strOrderTotalParam = '<?= money_format("%n", $_ITEM->ProductTotalCharged '
                                                 . ' + $_ITEM->ShippingCharged '
                                                 . ' + $_ITEM->HandlingCharged '
                                                 . ' + $_ITEM->Tax ) ?>';
            $objOrderTotalColumn = new QDataGridColumn('Order Total', $strOrderTotalParam );
            $this->dtgOrders->AddColumn($objOrderTotalColumn);
/*          
            $this->dtgOrders->MetaAddColumn('ShippingCost');
            $this->dtgOrders->MetaAddColumn('ProductTotalCost');
            $this->dtgOrders->MetaAddColumn('ShippingCharged');
            $this->dtgOrders->MetaAddColumn('HandlingCharged');
            $this->dtgOrders->MetaAddColumn('Tax');
            $this->dtgOrders->MetaAddColumn('ProductTotalCharged');
            $this->dtgOrders->MetaAddColumn('ShippingNamePrefix');
            $this->dtgOrders->MetaAddColumn('ShippingFirstName');
            $this->dtgOrders->MetaAddColumn('ShippingMiddleName');
            $this->dtgOrders->MetaAddColumn('ShippingLastName');
            $this->dtgOrders->MetaAddColumn('ShippingNameSuffix');
            $this->dtgOrders->MetaAddColumn('ShippingStreet1');
            $this->dtgOrders->MetaAddColumn('ShippingStreet2');
            $this->dtgOrders->MetaAddColumn('ShippingSuburb');
            $this->dtgOrders->MetaAddColumn('ShippingCounty');
            $this->dtgOrders->MetaAddColumn('ShippingCity');
            $this->dtgOrders->MetaAddTypeColumn('ShippingZoneId', 'ZoneType');
            $this->dtgOrders->MetaAddTypeColumn('ShippingCountryId', 'CountryType');
            $this->dtgOrders->MetaAddColumn('ShippingPostalCode');
            $this->dtgOrders->MetaAddColumn('BillingNamePrefix');
            $this->dtgOrders->MetaAddColumn('BillingFirstName');
            $this->dtgOrders->MetaAddColumn('BillingMiddleName');
            $this->dtgOrders->MetaAddColumn('BillingLastName');
            $this->dtgOrders->MetaAddColumn('BillingNameSuffix');
            $this->dtgOrders->MetaAddColumn('BillingStreet1');
            $this->dtgOrders->MetaAddColumn('BillingStreet2');
            $this->dtgOrders->MetaAddColumn('BillingSuburb');
            $this->dtgOrders->MetaAddColumn('BillingCounty');
            $this->dtgOrders->MetaAddColumn('BillingCity');
            $this->dtgOrders->MetaAddTypeColumn('BillingZoneId', 'ZoneType');
            $this->dtgOrders->MetaAddTypeColumn('BillingCountryId', 'CountryType');
            $this->dtgOrders->MetaAddColumn('BillingPostalCode');
            $this->dtgOrders->MetaAddColumn('Notes');
*/

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('Order');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}
        

        public function pxyViewAccount_Click($strFormId, $strControlId, $intOrderId)
        {
            $objOrder = Order::Load($intOrderId);
            if( $objOrder instanceof Order )
            {
                $objEditPanel = new AccountEditPanel($this, $this->strCloseEditPanelMethod, $objOrder->AccountId );

                $strMethodName = $this->strSetEditPanelMethod;
                $this->objForm->$strMethodName($objEditPanel);
            }
        }

        public function pxyViewOrder_Click($strFormId, $strControlId, $strParameter)
        {
            $strParameterArray = explode(',', $strParameter);
            $objEditPanel = new OrderEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

            $strMethodName = $this->strSetEditPanelMethod;
            $this->objForm->$strMethodName($objEditPanel);
        }
        public function txtViewOrder_Click($strFormId, $strControlId, $strParameter)
        {
            $intOrderId = $this->txtOrderNumberSearch->Text;
            if(Order::Load($intOrderId))
            {
                $this->lblMessage->Text = '';
                
                $objEditPanel = new OrderEditPanel($this, $this->strCloseEditPanelMethod, $intOrderId );

                $strMethodName = $this->strSetEditPanelMethod;
                $this->objForm->$strMethodName($objEditPanel);
            }
            else
                $this->lblMessage->Text = 'Order ' . $intOrderId . ' not found.';
        }
        public function txtViewByAccount_Click($strFormId, $strControlId, $strParameter)
        {
            $this->intAccountId = $this->txtAccountNumberSearch->Text;
            $this->dtgOrders->Refresh();
        }
        public function lstStatus_Selected($strFormId, $strControlId, $strParameter)
        {
            $this->intOrderStatusId = $this->lstStatus->SelectedIndex;            
            $this->dtgOrders->Refresh();
        }
        
        public function OrderDataBinder()
        {
            $objClauses = array();

            if ($objClause = $this->dtgOrders->OrderByClause)
                array_push($objClauses, $objClause);

            if ($objClause = $this->dtgOrders->LimitClause)
                array_push($objClauses, $objClause);
            
            if($this->intOrderStatusId && $this->intAccountId)
            {
                $this->dtgOrders->TotalItemCount = Order::QueryCount(QQ::AndCondition(
                                            QQ::Equal( QQN::Order()->AccountId, $this->intAccountId),
                                            QQ::Equal( QQN::Order()->StatusId, $this->intOrderStatusId)));
                $aryOrders = Order::QueryArray( QQ::AndCondition(
                                            QQ::Equal( QQN::Order()->AccountId, $this->intAccountId),
                                            QQ::Equal( QQN::Order()->StatusId, $this->intOrderStatusId)),
                                             $objClauses );
            }
            elseif($this->intOrderStatusId )
            {    
                $this->dtgOrders->TotalItemCount = Order::QueryCount(QQ::Equal(QQN::Order()->StatusId, $this->intOrderStatusId));
                $aryOrders = Order::QueryArray(QQ::Equal( QQN::Order()->StatusId, $this->intOrderStatusId), $objClauses );
            }
            elseif($this->intAccountId )
            {    
                $this->dtgOrders->TotalItemCount = Order::QueryCount(QQ::Equal(QQN::Order()->AccountId, $this->intAccountId));
                $aryOrders = Order::QueryArray(QQ::Equal( QQN::Order()->AccountId, $this->intAccountId), $objClauses );
            }
            else
            {
                $this->dtgOrders->TotalItemCount = Order::CountAll();
                $aryOrders = Order::LoadAll( $objClauses );
            }
            $this->dtgOrders->DataSource = $aryOrders;
        }

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter)
        {
			$objEditPanel = new OrderEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>