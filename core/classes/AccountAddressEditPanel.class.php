<?php
	/**
    * AccountAddressEditPanel - provides a panel for user viewing and modification of an address
    *    
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: AccountAddressEditPanel.class.php 322 2008-10-24 20:30:55Z erikwinn $
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

    *
    * @package Quasi
    * @subpackage Classes
	*/
	class AccountAddressEditPanel extends QPanel
    {
		// Local instance of the AddressMetaControl
		public $mctAddress;

        protected $objControlBlock;
        protected $objParentBlock;
        
		// Controls for Address's Data Fields
		public $txtTitle;
		public $lstMyPeople;
		public $txtStreet1;
		public $txtStreet2;
		public $txtSuburb;
		public $txtCity;
		public $txtCounty;
		public $lstZone;
		public $lstCountry;
		public $txtPostalCode;
		public $lstType;

		// Other Controls
		public $btnSave;
		public $btnDelete;
		public $btnCancel;
        public $btnAddPerson;

		// Callback
		protected $strClosePanelMethod;
        
        public function __construct($objParentObject,
                                                      $objControlBlock,
                                                      $strClosePanelMethod,
                                                      $intId = null,
                                                      $strControlId = null)
        {
            try {
                parent::__construct($objParentObject, $strControlId);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            
            $this->objControlBlock =& $objControlBlock;
            $this->objParentBlock =& $objParentObject;
            
			$this->strTemplate = __QUASI_CORE_TEMPLATES__ .  '/AccountAddressEditPanel.tpl.php';
			$this->strClosePanelMethod = $strClosePanelMethod;

			$this->mctAddress = AddressMetaControl::Create($this, $intId);

			// Call MetaControl's methods to create qcontrols based on Address's data fields
			$this->txtTitle = $this->mctAddress->txtTitle_Create();
            $this->txtTitle->Name = 'Address Title: ';
            $this->lstMyPeople = $this->mctAddress->lstMyPeople_Create($this->objControlBlock->Account->PersonId);
			$this->lstMyPeople->Name = 'Address for : ';
            $this->txtStreet1 = $this->mctAddress->txtStreet1_Create();
            $this->txtStreet1->Name = 'Street :';
			$this->txtStreet2 = $this->mctAddress->txtStreet2_Create();
            $this->txtStreet2->Name = 'Street 2 or Apt.#:';
			$this->txtSuburb = $this->mctAddress->txtSuburb_Create();
            $this->txtSuburb->Name = 'Suburb :';          
			$this->txtCity = $this->mctAddress->txtCity_Create();
            $this->txtCity->Name = 'City :';
			$this->txtCounty = $this->mctAddress->txtCounty_Create();
            $this->txtCounty->Name = 'County/District :';
			$this->lstZone = $this->mctAddress->lstZone_Create();
            $this->lstZone->Name = 'State/Province :';
			$this->lstCountry = $this->mctAddress->lstCountry_Create();
            $this->lstCountry->Name = 'Country :';
			$this->txtPostalCode = $this->mctAddress->txtPostalCode_Create();
            $this->txtPostalCode->Name = 'Zip/Postal Code :';
			$this->lstType = $this->mctAddress->lstType_Create();
            $this->lstType->Name = 'Type of Address :';

			
            // Create Buttons and Actions 
            $this->btnAddPerson = new QButton($this);
            $this->btnAddPerson->Text = QApplication::Translate('Add a Person');
            if(IndexPage::$blnAjaxOk)
                $this->btnAddPerson->AddAction(new QClickEvent(), new QAjaxControlAction($this->objParentBlock, 'btnAddPerson_Click'));
            else
                $this->btnAddPerson->AddAction(new QClickEvent(), new QServerControlAction($this->objParentBlock, 'btnAddPerson_Click'));

            $this->btnSave = new QButton($this);
            $this->btnSave->Text = QApplication::Translate('Save');
            if(IndexPage::$blnAjaxOk)
                $this->btnSave->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnSave_Click'));
            else
                $this->btnSave->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnSave_Click'));
			$this->btnSave->CausesValidation = $this;

			$this->btnCancel = new QButton($this);
			$this->btnCancel->Text = QApplication::Translate('Cancel');
            if(IndexPage::$blnAjaxOk)
                $this->btnCancel->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCancel_Click'));
            else
                $this->btnCancel->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnCancel_Click'));

			$this->btnDelete = new QButton($this);
			$this->btnDelete->Text = QApplication::Translate('Delete');
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('Address') . '?'));
            if(IndexPage::$blnAjaxOk)
                $this->btnDelete->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnDelete_Click'));
            else
                $this->btnDelete->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctAddress->EditMode;
		}
        
        
        public function btnSave_Click($strFormId, $strControlId, $strParameter)
        {
            if( '' == $this->txtTitle->Text)
            {
                if( AddressType::Primary == $this->lstType->SelectedValue )
                    $this->txtTitle->Text = 'Primary Address';
                else
                {
                    $aryPersons = Person::QueryArray(
                                QQ::OrCondition(
                                    QQ::Equal( QQN::Person()->Id, $this->objControlBlock->Account->PersonId),
                                    QQ::Equal( QQN::Person()->OwnerPersonId, $this->objControlBlock->Account->PersonId)
                                    ));
                                    
                    foreach( $aryPersons as $objPerson )
                        $aryPersonIds[] = $objPerson->Id;
                    
                    if(AddressType::Shipping == $this->lstType->SelectedValue)
                    {
                        $this->txtTitle->Text = 'Shipping Address';
                        $intCount = Address::QueryCount(
                                QQ::AndCondition(
                                    QQ::In( QQN::Address()->PersonId, $aryPersonIds),
                                    QQ::Equal( QQN::Address()->TypeId, AddressType::Shipping)
                                ));
                    }
                    elseif(AddressType::Billing == $this->lstType->SelectedValue)
                    {
                        $this->txtTitle->Text = 'Billing Address';
                        $intCount = Address::QueryCount(
                                QQ::AndCondition(
                                    QQ::In( QQN::Address()->PersonId, $aryPersonIds),
                                    QQ::Equal( QQN::Address()->TypeId, AddressType::Billing)                                    
                                ));
                    }
                    else
                    {
                        $this->txtTitle->Text = 'Extra Address';
                        $intCount = Address::QueryCount(
                                QQ::AndCondition(
                                    QQ::In( QQN::Address()->PersonId, $aryPersonIds),
                                    QQ::NotEqual( QQN::Address()->TypeId, AddressType::Billing),                                    
                                    QQ::NotEqual( QQN::Address()->TypeId, AddressType::Shipping)
                                ));
                    }
                                        
    
                    $this->txtTitle->Text .= ' ' . $intCount;
                }
            }
            $this->mctAddress->SaveAddress();
            $this->CloseSelf(true);
        }

		public function btnDelete_Click($strFormId, $strControlId, $strParameter)
        {
            if( Address::CountByPersonId($this->objControlBlock->Account->PersonId) > 1 )
     			$this->mctAddress->DeleteAddress();
			$this->CloseSelf(true);
		}

		public function btnCancel_Click($strFormId, $strControlId, $strParameter)
        {
			$this->CloseSelf(false);
		}

		// Close Myself and Call ClosePanelMethod Callback
		protected function CloseSelf($blnChangesMade)
        {
			$strMethod = $this->strClosePanelMethod;
			$this->objControlBlock->$strMethod($blnChangesMade);
		}
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Account':
                    return $this->objControlBlock->Account ;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
	}
?>