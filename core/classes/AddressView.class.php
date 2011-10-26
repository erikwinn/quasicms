<?php
	/**
    * AddressView - provides a panel for viewing and modification of an address
    *    
    *@author Erik Winn <erikwinnmail@yahoo.com>
    *
    *
    * $Id: AddressView.class.php 2 2008-07-31 18:55:50Z erikwinn $
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
    * @subpackage Views
	*/
	class AddressView extends QPanel
    {
		// Local instance of the AddressMetaControl
		protected $mctAddress;

        protected $objParentObject;

        public $lblAddressName;
        
        // Controls that allow the viewing of Address's individual data fields
        public $lblTitle;
        public $lblPersonId;
        public $lblStreet1;
        public $lblStreet2;
        public $lblSuburb;
        public $lblCity;
        public $lblCounty;
        public $lblZoneId;
        public $lblCountryId;
        public $lblPostalCode;
        public $lblIsCurrent;
        public $lblTypeId;

        public function __construct($objParentObject,
                                                        $intAddressId,
                                                        $strAddressName=" Address: ",
                                                        $strControlId = null)
        {
            try {
                parent::__construct($objParentObject, $strControlId);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
           
            $this->objParentObject =& $objParentObject;
            
			$this->strTemplate = __QUASI_CORE_TEMPLATES__ .  '/AddressView.tpl.php';

			$this->mctAddress = AddressMetaControl::Create($this, $intAddressId);

            $this->lblAddressName = new QLabel($this);
            $this->lblAddressName->Text = $strAddressName;
             
			// Call MetaControl's methods to create qcontrols based on Address's data fields
			$this->lblTitle = $this->mctAddress->lblTitle_Create();
            $this->lblTitle->Name = 'Address Title: ';
            $this->lblPersonId = $this->mctAddress->lblPersonId_Create();
			$this->lblPersonId->Name = 'Address for : ';
            $this->lblStreet1 = $this->mctAddress->lblStreet1_Create();
            $this->lblStreet1->Name = 'Street :';
			$this->lblStreet2 = $this->mctAddress->lblStreet2_Create();
            $this->lblStreet2->Name = 'Street 2 or Apt.#:';
			$this->lblSuburb = $this->mctAddress->lblSuburb_Create();
            $this->lblSuburb->Name = 'Suburb :';
			$this->lblCity = $this->mctAddress->lblCity_Create();
            $this->lblCity->Name = 'City :';
			$this->lblCounty = $this->mctAddress->lblCounty_Create();
            $this->lblCounty->Name = 'County/District :';
			$this->lblZoneId = $this->mctAddress->lblZoneId_Create();
            $this->lblZoneId->Name = 'State/Province :';
			$this->lblCountryId = $this->mctAddress->lblCountryId_Create();
            $this->lblCountryId->Name = 'Country :';
			$this->lblPostalCode = $this->mctAddress->lblPostalCode_Create();
            $this->lblPostalCode->Name = 'Zip/Postal Code :';
			$this->lblTypeId = $this->mctAddress->lblTypeId_Create();
            $this->lblTypeId->Name = 'Type of Address :';

		}
	}
?>