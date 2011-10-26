<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the Address class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of Address objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this AddressListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class AddressListPanel extends QPanel
    {
		
        protected $strNameToFind;
        protected $objAccount = null;
        
        // Callback Method Names
        protected $strSetEditPanelMethod;
        protected $strCloseEditPanelMethod;
        
        // Local instance of the Meta DataGrid to list Addresses
		public $dtgAddresses;

        public $lblMessage;
        public $txtNameSearch;
        public $txtNumberSearch;

		// Other public QControls in this panel
		public $btnCreateNew;
		public $pxyEdit;

		
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
			$this->Template = 'AddressListPanel.tpl.php';
            
            //messages (eg. Name not found ..)
            $this->lblMessage = new QLabel($this);
            
            // a search field for Address by name ..
            $this->txtNameSearch = new QTextBox($this);
            $this->txtNameSearch->AddAction(new QEnterKeyEvent(), new QServerControlAction($this, 'txtNameSearch_Click'));
            $this->txtNameSearch->Name = 'Search by Name:';

            // a search field for Address by account number ..
            $this->txtNumberSearch = new QIntegerTextBox($this);
            $this->txtNumberSearch->AddAction(new QEnterKeyEvent(), new QServerControlAction($this, 'txtNumberSearch_Click'));
            $this->txtNumberSearch->Name = 'Search by Number:';
            $this->txtNumberSearch->CausesValidation = $this->txtNumberSearch;

			// Instantiate the Meta DataGrid
			$this->dtgAddresses = new AddressDataGrid($this);

			// Style the DataGrid (if desired)
			$this->dtgAddresses->CssClass = 'datagrid';
			$this->dtgAddresses->AlternateRowStyle->CssClass = 'alternate';
            $this->dtgAddresses->SetDataBinder('AddressDataBinder', $this);

			// Add Pagination (if desired)
			$this->dtgAddresses->Paginator = new QPaginator($this->dtgAddresses);
			$this->dtgAddresses->ItemsPerPage = 8;

			// Use the MetaDataGrid functionality to add Columns for this datagrid

			// Create an Edit Column
			$this->pxyEdit = new QControlProxy($this);
			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
			$this->dtgAddresses->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

			// Create the Other Columns (note that you can use strings for address's properties, or you
			// can traverse down QQN::address() to display fields that are down the hierarchy)
			$this->dtgAddresses->MetaAddColumn('Id');
			$this->dtgAddresses->MetaAddColumn('Title');
			$this->dtgAddresses->MetaAddColumn(QQN::Address()->Person);
			$this->dtgAddresses->MetaAddColumn('Street1');
			$this->dtgAddresses->MetaAddColumn('Street2');
			$this->dtgAddresses->MetaAddColumn('Suburb');
			$this->dtgAddresses->MetaAddColumn('City');
			$this->dtgAddresses->MetaAddColumn('County');
			$this->dtgAddresses->MetaAddTypeColumn('ZoneId', 'ZoneType');
			$this->dtgAddresses->MetaAddTypeColumn('CountryId', 'CountryType');
			$this->dtgAddresses->MetaAddColumn('PostalCode');
			$this->dtgAddresses->MetaAddColumn('IsCurrent');
			$this->dtgAddresses->MetaAddTypeColumn('TypeId', 'AddressType');
			$this->dtgAddresses->MetaAddColumn('CreationDate');
			$this->dtgAddresses->MetaAddColumn('LastModificationDate');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('Address');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}
        public function txtNumberSearch_Click($strFormId, $strControlId, $strParameter)
        {
            $intAccountId = $this->txtNumberSearch->Text;
            if( $this->objAccount = Account::Load($intAccountId) )
            {
                $this->lblMessage->Text = '';
                $this->dtgAddresses->Refresh();
            }
            else
                $this->lblMessage->Text = 'Account ' . $intAccountId . ' not found.';
        }
        
        public function txtNameSearch_Click($strFormId, $strControlId, $strParameter)
        {
            $this->lblMessage->Text = '';
            $this->strNameToFind =$this->txtNameSearch->Text;
            $this->dtgAddresses->Refresh();
        }
        public function AddressDataBinder()
        {
            $aryClauses = array();
            $aryConditions = array();
            $aryPersonIds = array();
            $objCondition = null;
            
            if ($objClause = $this->dtgAddresses->OrderByClause)
                array_push($aryClauses, $objClause);

            if ($objClause = $this->dtgAddresses->LimitClause)
                array_push($aryClauses, $objClause);
            
            if($this->strNameToFind)
            {
                $aryNamesToFind = explode(' ', $this->txtNameSearch->Text);
                $aryPersons = array();
                if(sizeof($aryNamesToFind) > 1 )
                {
                    $aryPersons = Person::QueryArray(
                                            QQ::AndCondition(
                                                QQ::Like(QQN::Person()->FirstName, $aryNamesToFind[0]),
                                                QQ::Like(QQN::Person()->LastName,  $aryNamesToFind[1])
                                            )
                                        );
                }
                elseif(sizeof($aryNamesToFind) == 1 )
                {
                    $aryPersons = Person::QueryArray(
                                            QQ::OrCondition(
                                                QQ::Like(QQN::Person()->FirstName, $this->txtNameSearch->Text),
                                                QQ::Like(QQN::Person()->LastName, $this->txtNameSearch->Text)
                                            )
                                        );
                }

                foreach( $aryPersons as $objPerson )
                    $aryPersonIds[] = $objPerson->Id;
            }

/*            if($this->intAddressStatusId )
            {    
                $this->dtgAddresses->TotalItemCount = Address::QueryCount(QQ::Equal(QQN::Address()->StatusId, $this->intAddressStatusId));
                $aryAddresses = Address::QueryArray(QQ::Equal( QQN::Address()->StatusId, $this->intAddressStatusId), $aryClauses );
            }*/
            
            if($this->objAccount )
            {
                $aryPersons = array();
                $aryPersons = Person::QueryArray(
                                        QQ::OrCondition(
                                            QQ::Equal(QQN::Person()->Id, $this->objAccount->Person->Id),
                                            QQ::Equal(QQN::Person()->OwnerPersonId, $this->objAccount->Person->Id)
                                        )
                                    );

                
                foreach( $aryPersons as $objPerson )
                    $aryPersonIds[] = $objPerson->Id;
                
            }
            if( count( $aryPersonIds) )
                $aryConditions[] =  QQ::In( QQN::Address()->PersonId, $aryPersonIds);
                 
            if(count($aryConditions) > 1)
                $objCondition = QQ::AndCondition($aryConditions);
            elseif(count($aryConditions) == 1)
                $objCondition = $aryConditions[0];
            else
                $objCondition = QQ::All();
            
            $this->dtgAddresses->TotalItemCount = Address::QueryCount($objCondition);
            $aryAddresses = Address::QueryArray($objCondition, $aryClauses );
            $this->dtgAddresses->DataSource = $aryAddresses;
        }

		public function pxyEdit_Click($strFormId, $strControlId, $strParameter) {
			$strParameterArray = explode(',', $strParameter);
			$objEditPanel = new AddressEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new AddressEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>