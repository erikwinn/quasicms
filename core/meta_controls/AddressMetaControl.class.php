<?php
	require(__DATAGEN_META_CONTROLS__ . '/AddressMetaControlGen.class.php');

	/**
	 * This is a MetaControl customizable subclass, providing a QForm or QPanel access to event handlers
	 * and QControls to perform the Create, Edit, and Delete functionality of the
	 * Address class.  This code-generated class extends from
	 * the generated MetaControl class, which contains all the basic elements to help a QPanel or QForm
	 * display an HTML form that can manipulate a single Address object.
	 *
	 * To take advantage of some (or all) of these control objects, you
	 * must create a new QForm or QPanel which instantiates a AddressMetaControl
	 * class.
	 *
	 * This file is intended to be modified.  Subsequent code regenerations will NOT modify
	 * or overwrite this file.
	 * 
	 * @package Quasi
	 * @subpackage MetaControls
	 */
	class AddressMetaControl extends AddressMetaControlGen
    {
        /**    
        * @var QListBox - a listbox with People associated with a specific person
         */
         protected $lstMyPeople;
        /**    
        * @var QListBox - a listbox with Address associated with a specific Account
         */
         protected $lstMyAddresses;
           
        /**
         * Create and setup QListBox lstMyPeople
         * This creates a listbox containing only persons associated with the Person
         * passed as PersonId.
         *
         *@param integer intPersonId - the person for whom to create an associated listbox
         * @param string $strControlId optional ControlId to use
         * @return QListBox
         */
        public function lstMyPeople_Create($intPersonId, $strControlId = null)
        {
            $this->lstPerson = new QListBox($this->objParentObject, $strControlId);
            $this->lstPerson->Name = QApplication::Translate('Person');
            $this->lstPerson->Required = true;
            if (!$this->blnEditMode)
                $this->lstPerson->AddItem(QApplication::Translate('- Select One -'), null);

            $aryPersons[] = Person::LoadById($intPersonId);
            
            $aryOwnedPersons = Person::LoadArrayByOwnerPersonId($intPersonId);
            
            if($aryOwnedPersons)
                $aryPersons = array_merge($aryPersons, $aryOwnedPersons);
            
            if ($aryPersons)
                foreach ($aryPersons as $objPerson)
                {
                    $objListItem = new QListItem($objPerson->__toString(), $objPerson->Id);
                    if ($this->objAddress->PersonId == $objPerson->Id)
                        $objListItem->Selected = true;
                    $this->lstPerson->AddItem($objListItem);
                }
            return $this->lstPerson;
        }
        /**
         * Create and setup QListBox lstMyAddresses
         * This creates a listbox containing only addresses associated with the Account
         * passed as AccountId.
         *
         * @param Account objAccount - the account for which to create an associated listbox
         * @param string $strControlId optional ControlId to use
         * @return QListBox
         */
        public function lstMyAddresses_Create($objAccount, $strControlId = null)
        {
            
            $this->lstMyAddresses = new QListBox($this->objParentObject, $strControlId);
            $this->lstMyAddresses->Name = QApplication::Translate('My Addresses');
            $this->lstMyAddresses->Required = true;
            if (!$this->blnEditMode)
                $this->lstMyAddresses->AddItem(QApplication::Translate('- Select One -'), null);

            if(!$objAccount instanceof Account)
                return $this->lstMyAddresses;
            
            $aryPersons[] = Person::LoadById($objAccount->PersonId);
            
            $aryOwnedPersons = Person::LoadArrayByOwnerPersonId($objAccount->PersonId);
            if($aryOwnedPersons)
                $aryPersons = array_merge($aryPersons, $aryOwnedPersons);

            $aryPersonIds = array();
            
            foreach($aryPersons as $objPerson)
                $aryPersonIds[] = $objPerson->Id;
                
            $aryAddresses = Address::QueryArray( QQ::In( QQN::Address()->PersonId, $aryPersonIds ));    

            if( is_array($aryAddresses) )
                foreach ($aryAddresses as $objAddress)
                {
                    $objListItem = new QListItem($objAddress->__toString(), $objAddress->Id);
                    if ($this->objAddress->Id == $objAddress->Id)
                        $objListItem->Selected = true;
                    $this->lstMyAddresses->AddItem($objListItem);
                }
            return $this->lstMyAddresses;
        }
        /**
         * Create and setup QListBox lstZone - this checks our Address for a country id and attempts
         * to load only those zones for the country.
         * @param integer intCountryId - optional country to filter zones by
         * @param string $strControlId optional ControlId to use
         * @return QListBox
         */
        public function lstZone_Create($intCountryId = null, $strControlId = null)
        {
            $this->lstZone = new QListBox($this->objParentObject, $strControlId);
            $this->lstZone->Name = QApplication::Translate('Zone');
            $this->lstZone->Required = true;

            if($intCountryId)            
                $aryZones = ZoneType::GetNameArrayByCountryId($intCountryId);
            elseif($this->objAddress->CountryId)
                $aryZones = ZoneType::GetNameArrayByCountryId($this->objAddress->CountryId);
            else
                $aryZones = ZoneType::$NameArray;
                
            foreach (ZoneType::$NameArray as $intId => $strValue)
                $this->lstZone->AddItem(new QListItem($strValue, $intId, $this->objAddress->ZoneId == $intId));
            return $this->lstZone;
        }
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'PersonId':
                    if($this->objAddress instanceof Address)
                        return $this->objAddress->PersonId;
                    return null;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
                case 'PersonId':
                    //set up the lstPerson control so that SaveAddress will work
                    if( ! $this->lstPerson instanceof QListBox )
                        $this->lstPerson_Create();
                                                         
                    try {
                        return ($this->lstPerson->SelectedValue = QType::Cast($mixValue, QType::Integer));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                default:
                    try {
                        return (parent::__set($strName, $mixValue));
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }

	}
?>