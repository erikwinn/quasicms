<?php
	/**
	 * This is the abstract Panel class for the List All functionality
	 * of the Account class.  This code-generated class
	 * contains a datagrid to display an HTML page that can
	 * list a collection of Account objects.  It includes
	 * functionality to perform pagination and sorting on columns.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QPanel which extends this AccountListPanelBase
	 * class.
	 *
	 * Any and all changes to this file will be overwritten with any subsequent re-
	 * code generation.
	 * 
	 * @package Quasi
	 * @subpackage Drafts
	 * 
	 */
	class AccountListPanel extends QPanel {
		// Local instance of the Meta DataGrid to list Accounts
		public $dtgAccounts;

		// Other public QControls in this panel
		public $btnCreateNew;
        public $pxyEdit;
        public $objPaginator;

        public $lblMessage;
        public $txtNameSearch;
        public $txtNumberSearch;
        
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
			$this->Template = 'AccountListPanel.tpl.php';
            //messages (eg. Name not found ..)
            $this->lblMessage = new QLabel($this);
                     
            // a search field for Account by name ..
            $this->txtNameSearch = new QTextBox($this);
            $this->txtNameSearch->AddAction(new QEnterKeyEvent(), new QServerControlAction($this, 'txtNameSearch_Click'));
            $this->txtNameSearch->Name = 'Search by Name:';

            // a search field for Account by number ..
            $this->txtNumberSearch = new QIntegerTextBox($this);
            $this->txtNumberSearch->AddAction(new QEnterKeyEvent(), new QServerControlAction($this, 'txtNumberSearch_Click'));
            $this->txtNumberSearch->Name = 'Search by Number:';
            $this->txtNumberSearch->CausesValidation = $this->txtNumberSearch;
			// Instantiate the Meta DataGrid
			$this->dtgAccounts = new AccountDataGrid($this);

			$this->dtgAccounts->CssClass = 'datagrid';
			$this->dtgAccounts->AlternateRowStyle->CssClass = 'alternate';

            $this->objPaginator = new QPaginator($this->dtgAccounts);
			$this->dtgAccounts->Paginator = $this->objPaginator;
			$this->dtgAccounts->ItemsPerPage = 25;

			// Create an Edit Column
 			$this->pxyEdit = new QControlProxy($this);
 			$this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
// 			$this->dtgAccounts->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

            $this->dtgAccounts->MetaAddProxyColumn($this->pxyEdit, QQN::Account()->Person);
            $this->dtgAccounts->MetaAddColumn('Id');
            $this->dtgAccounts->GetColumn(1)->Name = 'Account Number';
                     
			$this->dtgAccounts->MetaAddColumn('RegistrationDate');
			$this->dtgAccounts->MetaAddColumn('Username');
//			$this->dtgAccounts->MetaAddColumn('Password');
//			$this->dtgAccounts->MetaAddColumn('Notes');
			$this->dtgAccounts->MetaAddColumn('LastLogin');
			$this->dtgAccounts->MetaAddColumn('LoginCount');
			$this->dtgAccounts->MetaAddColumn('Online');
//			$this->dtgAccounts->MetaAddColumn('OnetimePassword');
//			$this->dtgAccounts->MetaAddColumn('ValidPassword');
			$this->dtgAccounts->MetaAddTypeColumn('TypeId', 'AccountType');
			$this->dtgAccounts->MetaAddTypeColumn('StatusId', 'AccountStatusType');

			// Setup the Create New button
			$this->btnCreateNew = new QButton($this);
			$this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' ' . QApplication::Translate('Account');
			$this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
		}

        public function pxyEdit_Click($strFormId, $strControlId, $strParameter)
        {
            $this->lblMessage->Text = '';
            
            $strParameterArray = explode(',', $strParameter);
            $objEditPanel = new AccountEditPanel($this, $this->strCloseEditPanelMethod, $strParameterArray[0]);

            $strMethodName = $this->strSetEditPanelMethod;
            $this->objForm->$strMethodName($objEditPanel);
        }
        
        public function txtNumberSearch_Click($strFormId, $strControlId, $strParameter)
        {
            $intAccountId = $this->txtNumberSearch->Text;
            if(Account::Load($intAccountId))
            {
                $this->lblMessage->Text = '';
                
                $objEditPanel = new AccountEditPanel($this, $this->strCloseEditPanelMethod, $intAccountId );

                $strMethodName = $this->strSetEditPanelMethod;
                $this->objForm->$strMethodName($objEditPanel);
            }
            else
                $this->lblMessage->Text = 'Account ' . $intAccountId . ' not found.';
        }
        
        public function txtNameSearch_Click($strFormId, $strControlId, $strParameter)
        {
            $this->lblMessage->Text = '';
            $aryNamesToFind = explode(' ', $this->txtNameSearch->Text);
            $aryPersons = array();
            $aryPersonIds = array();
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

            $aryAccounts = Account::QueryArray( QQ::In( QQN::Account()->PersonId, $aryPersonIds) );
            $this->dtgAccounts->TotalItemCount = sizeof($aryAccounts);
            
            $this->dtgAccounts->DataSource = $aryAccounts;
            $this->dtgAccounts->Refresh();
        }

		public function btnCreateNew_Click($strFormId, $strControlId, $strParameter) {
			$objEditPanel = new AccountEditPanel($this, $this->strCloseEditPanelMethod, null);
			$strMethodName = $this->strSetEditPanelMethod;
			$this->objForm->$strMethodName($objEditPanel);
		}
	}
?>