<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("ACCOUNTADDRESSLISTPANEL.CLASS.PHP")) {
define("ACCOUNTADDRESSLISTPANEL.CLASS.PHP",1);

/**
* This class provides a panel in which to list addresses from within a user account.
* Each address item contains an "Edit" link with which to access a specific address.
* Additionally, this class creates the individual AddressEditPanel for editting as well
* as another panel (PersonEditPanel) for changing or adding Persons. The Person
* can be associated with the Address via a drop down list of persons.
*
* $Id: AccountAddressListPanel.class.php 286 2008-10-10 23:33:36Z erikwinn $
*@version 0.1
*
*@copyright (C) 2008 by Erik Winn
*@license GPL v.2

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111 USA
     * @package Quasi
     * @subpackage Classes
     * 
     */
   
   class AccountAddressListPanel extends QPanel
   {
        /**
        *@var array aryAddresses - an array of Addresses belonging to the Account
        */
        protected $aryAddresses;
        /**
        *@var array aryPersons - an array of Persons belonging to the Person of this Account
        */
        protected $aryPersons;

        /**
        *@var ContentBlock objControlBlock - the content block containing the callbacks for panel hide/show
        */
        protected $objControlBlock;
        /**
        *@var string strShowEditPanelMethod - Callback Method Names
        */
        protected $strShowEditPanelMethod;
        protected $strCloseEditPanelMethod;
        
        /**
        *@var AccountAddressEditPanel pnlAddressEditPanel - panel to edit/create an address
        */
        public $pnlAddressEditPanel=null;
        /**
        *@var AccountPersonEditPanel pnlPersonEditPanel - panel to edit/create a Person
        */
        public $pnlPersonEditPanel=null;
        
        // Meta DataGrid to list Addresses
        /**
        *@var QDataGrid dtgAddresses - Address Meta DataGrid to list Addresses
        */
        public $dtgAddresses;
        /**
        *@var QPaginator objPaginator - data page control for datagrid
        */
        public $objPaginator;        
        /**
        *@var QButton btnCreateNew - button to create a new Address, shows address edit panel
        */
        public $btnCreateNew;
        
        /**
        *@var QControlProxy pxyEdit - action link in datagrid to edit a specific address, shows edit panel
        */
        public $pxyEdit;
        
        public function __construct( $objParentObject,
                                     $objControlBlock,
                                     $strShowEditPanelMethod,
                                     $strCloseEditPanelMethod,
                                     $strControlId = null )
        {
            try {
                parent::__construct($objParentObject, $strControlId);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->objControlBlock =& $objControlBlock;

            $this->strShowEditPanelMethod = $strShowEditPanelMethod;
            $this->strCloseEditPanelMethod = $strCloseEditPanelMethod;
            
            $this->Template = __QUASI_CORE_TEMPLATES__ . '/AccountAddressListPanel.tpl.php';

            $this->dtgAddresses = new AddressDataGrid($this);
            $this->dtgAddresses->SetDataBinder('AccountAddressDataBinder', $this);
            $this->dtgAddresses->CssClass = 'datagrid';
            $this->dtgAddresses->AlternateRowStyle->CssClass = 'alternate';

            $this->objPaginator = new QPaginator($this->dtgAddresses);
            $this->dtgAddresses->Paginator = $this->objPaginator;
            $this->dtgAddresses->ItemsPerPage = 10;

            // Create an Edit Column
            $this->pxyEdit = new QControlProxy($this);
            if(IndexPage::$blnAjaxOk)
                $this->pxyEdit->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'pxyEdit_Click'));
            else
                $this->pxyEdit->AddAction(new QClickEvent(), new QServerControlAction($this, 'pxyEdit_Click'));
            $this->dtgAddresses->MetaAddEditProxyColumn($this->pxyEdit, 'Edit', 'Edit');

            $this->dtgAddresses->MetaAddColumn(QQN::Address()->Person);
            $this->dtgAddresses->MetaAddColumn('Street1');
            $this->dtgAddresses->MetaAddColumn('City');
            $this->dtgAddresses->MetaAddTypeColumn('ZoneId', 'ZoneType');
            // $this->dtgAddresses->MetaAddColumn('ZoneId');
            $this->dtgAddresses->MetaAddColumn('PostalCode');
            // $this->dtgAddresses->MetaAddTypeColumn('TypeId', 'AddressType');

            $this->btnCreateNew = new QButton($this);
            $this->btnCreateNew->Text = QApplication::Translate('Create a New') . ' '  . QApplication::Translate('Address');
            if(IndexPage::$blnAjaxOk)
                $this->btnCreateNew->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCreateNew_Click'));
            else
                $this->btnCreateNew->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnCreateNew_Click'));
        }

        public function pxyEdit_Click($strFormId, $strControlId, $strParameter)
        {
            $strParameterArray = explode(',', $strParameter);
            $this->pnlAddressEditPanel = new AccountAddressEditPanel($this,
                                                                     $this->objControlBlock,
                                                                     $this->strCloseEditPanelMethod,
                                                                     $strParameterArray[0]);
            
            $strMethodName = $this->strShowEditPanelMethod;
            $this->objControlBlock->$strMethodName($this->pnlAddressEditPanel);
        }

        public function btnCreateNew_Click($strFormId, $strControlId, $strParameter)
        {
            if($this->pnlPersonEditPanel)
            {
                $this->pnlPersonEditPanel->RemoveChildControls(true);
                $this->pnlPersonEditPanel->Visible = false;
            }
            
            if($this->pnlAddressEditPanel)
                $this->pnlAddressEditPanel->RemoveChildControls(true);
            
            $this->pnlAddressEditPanel = new AccountAddressEditPanel($this,
                                                                     $this->objControlBlock,
                                                                     $this->strCloseEditPanelMethod,
                                                                     null);
            $this->pnlAddressEditPanel->Visible = true;                                                                           
            $strMethodName = $this->strShowEditPanelMethod;
            $this->objControlBlock->$strMethodName($this->pnlAddressEditPanel);
        }
        
        //Callbacks ..
        public function btnAddPerson_Click($strFormId, $strControlId, $strParameter)
        {
            $this->pnlAddressEditPanel->RemoveChildControls(true);
            $this->pnlAddressEditPanel->Visible = false;
            $this->pnlPersonEditPanel = new AccountPersonEditPanel($this, $this, 'ClosePersonEditPanel');
            $strMethodName = $this->strShowEditPanelMethod;
            $this->objControlBlock->$strMethodName($this->pnlPersonEditPanel);
            
        }
        
        public function ClosePersonEditPanel($blnChangesMade)
        {        
            $this->pnlPersonEditPanel->RemoveChildControls(true);
            $this->pnlPersonEditPanel->Visible = false;
            $this->pnlAddressEditPanel = new AccountAddressEditPanel($this,
                                                                     $this->objControlBlock,
                                                                     $this->strCloseEditPanelMethod,
                                                                     null);
            
            $strMethodName = $this->strShowEditPanelMethod;
            $this->objControlBlock->$strMethodName($this->pnlAddressEditPanel);
        }
        
        /**
        * This binds the Datagrid data retrieval to this Person, the addresses listed in the Datagrid will be those
        * associated with this user in the database. The addresses loaded will be not only the addresses
        * specific to the user, but also those of others added by this user (eg. addresses of friends and/or
        * family to whom they may wish to have orders shipped.) via the Address management panel 
        *
        * If a paginator is set on this DataBinder, it will use it.  If not, then no pagination will be used.
        * It will also perform any sorting requested in by clicking on the columns in the Datagrid.
        */
        public function AccountAddressDataBinder()
        {
            $this->aryPersons = array();
            $this->aryAddresses = array();
            $aryClauses = array();
            $aryPersonIds = array();
            
            // add extra people that may be in address book .. slightly inefficient but it works for now.
            $this->aryPersons = Person::QueryArray(
                QQ::OrCondition(
                    QQ::Equal( QQN::Person()->Id, $this->objControlBlock->Account->PersonId),
                    QQ::Equal( QQN::Person()->OwnerPersonId, $this->objControlBlock->Account->PersonId)
                )
            );
                                 
            foreach( $this->aryPersons as $objPerson )
                $aryPersonIds[] = $objPerson->Id;

            // If a column is selected to be sorted, and if that column has an OrderByClause 
            // set on it, then let's add the OrderByClause to the $aryClauses array
            if ($objClause = $this->dtgAddresses->OrderByClause)
                array_push($aryClauses, $objClause);

            // Add the LimitClause information as well
            if ($objClause = $this->dtgAddresses->LimitClause)
                array_push($aryClauses, $objClause);
                                
            $this->aryAddresses = Address::QueryArray( 
                QQ::In( QQN::Address()->PersonId, $aryPersonIds),
                $aryClauses
            );
                                             
            if ($this->objPaginator)
                $this->dtgAddresses->TotalItemCount = count($this->aryAddresses);

            // Set the DataSource to be a Query result from Address, given the clauses above
            $this->dtgAddresses->DataSource = $this->aryAddresses;
        }
    }
}   
?>
