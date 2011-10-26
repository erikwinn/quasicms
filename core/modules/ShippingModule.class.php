<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("SHIPPINGMODULE.CLASS.PHP")){
define("SHIPPINGMODULE.CLASS.PHP",1);

/**
* Class ShippingModule - a module to display a selection of ShippingMethods
*
* This class obtains any shipping methods flagged as active in the database and
* adds them to a radiobutton list which is the only display object. For each method
* a new ShippingCalculator is instantiated and GetEstimate is called to obtain a price
* for the order based on information in the OrderItems and the addresses (ie. weight,
* size and destination).
*
* @todo 
*   - check availability more gracefully
*   - handle errors
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: ShippingModule.class.php 471 2009-01-07 21:16:50Z erikwinn $
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
*@package Quasi
* @subpackage Modules
*/


 class ShippingModule extends QPanel
 {
        public $blnDebug = false;
        /**
        * This is the main control block for this module - it is designed to be the CheckOutModule,
        * but it may be used with other controllers as long as you pass an Order object as we obtain
        * address information, weight, etc from the order ..
        *@var QPanel objControlBlock - the main control block for this module, usually CheckOutModule
        */
        protected $objControlBlock;
        /**
        *@var Order objOrder - a local reference to the Order
        */
        protected $objOrder;
        /**
        *@var Address objAddress - a local reference to the Address
        */
        protected $objAddress;
        /**
        *@var ShippingMethod objSelectedMethod - the selected method for this module
        */
        protected $objSelectedMethod;        
        /**
        *@var array aryShippingMethods - the active methods available for this module
        */
        protected $aryShippingMethods;
        
        protected $strDefaultCarrier;
        protected $strDefaultServiceType;
        
        protected $fltShippingTotal;
        protected $blnHasActiveMethods=true;
        protected $blnIsInternational=false;
        protected $intPreviousAddressId;
        /**
        * This is a mapping of shipping provider to method. Each ShippingMethod has a title
        * field, this is displayed at the top of  a block showing each method active for a provider.
        * The title field is used as the title of the block, so the map is in the form title => objShippingMethodView
        * the view containing a radiobutton for the selection of that method.
        *@var array aryShippingProviders - an array/map of Carriers to methods
        */
        public $aryShippingProviders;

        /**
        *@var AddressSelectionModule objAddressSelectionModule - handles selecting the shipping address
        */
        public $objAddressSelectionModule;
        /**
        *@var QTextBox txtNotes - customer comments added to order ..
        */
        public $txtNotes;
        

        /**
        * Module constructor
        *@param QPanel objControlBlock - the main control block for this module, usually CheckOutModule
        *@param Order objOrder - the order to be shipped ...
        */
        public function __construct($pnlParentObject, $objControlBlock, Order $objOrder, $blnDebug = false )
        {
            
            try {
                parent::__construct($pnlParentObject );
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }

            if($blnDebug)
                $this->blnDebug = $blnDebug;
            $this->strDefaultCarrier = DEFAULT_SHIPPING_CARRIER;
            $this->strDefaultServiceType = DEFAULT_SHIPPING_SERVICE;
        
            //normally refers to the CheckOutModule ..
            $this->objControlBlock =& $objControlBlock;
            
            $this->objOrder =& $objOrder;
                
            $this->AutoRenderChildren = true;
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/ShippingModule.tpl.php';
                          
            $this->initMethodViews();
            if( !empty($this->aryShippingMethods))
            {
                $this->objAddressSelectionModule = new AddressSelectionModule($this,
                                                                                                                     'SelectAddress',
                                                                                                                     $this->objOrder->ShippingAddressId
                                                                                                                     );
                $this->objAddress = $this->objAddressSelectionModule->Address;
            }
            else
                $this->blnHasActiveMethods = false;

            $this->txtNotes = new QTextBox($this);
            $this->txtNotes->TextMode = QTextMode::MultiLine;
            $this->txtNotes->Columns = 30;
            
        }
        
        /**
        * This function initializes the array of potential shipping methods
        *@todo make the local pickup option configurable, currently you have to change the check here ..
        */
        protected function initShippingMethods()
        {
            if( $this->objOrder->IsInternational)
                $this->aryShippingMethods = ShippingMethod::QueryArray( QQ::AndCondition(
                                            QQ::Equal(QQN::ShippingMethod()->Active, true),
                                            QQ::Equal(QQN::ShippingMethod()->IsInternational,true)
                                                    ));
             else
                $this->aryShippingMethods = ShippingMethod::QueryArray( 
                                            QQ::Equal(QQN::ShippingMethod()->Active, true)
                                                    );
                                                    
             if(ZoneType::Colorado == $this->objOrder->ShippingZoneId )
                $this->aryShippingMethods[] = ShippingMethod::Load(1);                                                
        }
    /**
        * This function creates a radio button to display for each active shipping method as
        * appropriate - if the method is not international no button will be created for an international
        * order and if a method is not available or returns a 0 rate charge it will also not be created.
        * @todo
        *   - check availability
        *   - implement try/catch to handle errors, log them when not debugging.
        */
        protected function initMethodViews()
        {
            $this->initShippingMethods();
            
            if( empty($this->aryShippingMethods))
                return;
                
            if( is_array($this->aryShippingProviders) )
            {
                foreach( $this->aryShippingProviders as $strName => &$aryMethodViews )
                {
                    foreach($aryMethodViews as $it => $objMethodView)
                    {
                        $strControlId = $objMethodView->ControlId;
                        $this->RemoveChildControl($strControlId, true);
                        unset($aryMethodViews[$it]);
                    }
                    unset($this->aryShippingProviders[$strName]);
                }                
                $this->aryShippingProviders = array();               
            }
            
            foreach($this->aryShippingMethods as $objShippingMethod)
            {
                //Fedex Ground international only goes for Canada
                if($this->objOrder->IsInternational && CountryType::Canada != $this->objOrder->ShippingCountryId
                        && false !== stripos( $objShippingMethod->ServiceType, 'FEDEX_GROUND' ) )
                    continue;
                    
                //Skip the Fedex international methods for domestic orders ..
                if( !$this->objOrder->IsInternational && ( false !== stripos( $objShippingMethod->ServiceType, 'GLOBAL' )
                                                              || false !== stripos( $objShippingMethod->ServiceType, 'INTERNATIONAL' )) )
                    continue;
                
                $objShippingMethod->Init($this->objOrder);
                    
                    ///@todo check availability ..
    /*             if( ! $objShippingMethod->MethodAvailable() ) continue;*/
    
                 $objShippingMethod->GetRate();
                    
                /**
                *@todo figure this out - USPS, eg. provides no clear way to determine availability (in fact i can't even
                * find their !@#$ing error codes ..) so for now if there is no charge we assume it is not available ..
                */
                if( ! $objShippingMethod->IsAvailable ||  $objShippingMethod->HasErrors || 0 == $objShippingMethod->Rate  )
                {
//                    if($this->blnDebug &&   'FEDEX_2_DAY' != $objShippingMethod->ServiceType)
                    if($this->blnDebug)
//                    exit(var_dump($objShippingMethod));
                        die($objShippingMethod->Title . ', '
                             . $objShippingMethod->ServiceType . '<br /> '
                             . $objShippingMethod->Errors );
                    else
                        continue;
                }
                //eh, could be a server error .. skip it. todo: make me smarter ..
                if( ! is_numeric($objShippingMethod->Rate)  )
                    continue;
                                                    
                $objShippingMethodView = new ShippingMethodView($this, $objShippingMethod);

                //set the defaults here - note that if the default method is not active this leaves everything
                //null until/unless the user selects a method; hence default should be properly configured.
                if( $objShippingMethod->Carrier == $this->strDefaultCarrier
                    && $objShippingMethod->ServiceType == $this->strDefaultServiceType )
                {
                    $objShippingMethodView->Checked = true;
                    $this->objSelectedMethod = $objShippingMethod;
                    $this->objOrder->ShippingMethodId = $objShippingMethod->Id;
                    $this->objOrder->ShippingCharged = $objShippingMethod->Rate;
                }
                //store by title for the method display ..
                $this->aryShippingProviders[$objShippingMethod->Title][] = $objShippingMethodView;
            }
        }
        
        /**
         * This Function is called when the user selects a method - it sets objSelectedMethod
         * and updates ShippingMethodId and ShippingCharged in the Order ..
         *@param integer intShippingMethodId - the id of the selected method
         */
        public function SelectMethod($intShippingMethodId)
        {
            foreach($this->aryShippingMethods as $objMethod )
                if($intShippingMethodId == $objMethod->Id)
                {
                    //this is redundant .. todo: pick one way or the other?
                    $this->objSelectedMethod = $objMethod;
                    $this->objOrder->ShippingMethodId = $objMethod->Id;
                    $this->objOrder->ShippingCharged = $objMethod->Rate;
                    if($this->objControlBlock instanceof CheckOutModule)
                        $this->objControlBlock->RefreshOrderTotalsView();
                    break;
                }
        }
        
        public function SelectAddress($intAddressId, $strParameter=null)
        {
            if( is_numeric($intAddressId) )
            {
                $this->intPreviousAddressId = $intAddressId;            
                $this->objOrder->SetShippingAddress($this->objAddressSelectionModule->Address);
                $this->objAddress = $this->objAddressSelectionModule->Address;
            }

            $this->objAddressSelectionModule->RemoveChildControls(true);
            $this->RemoveChildControl($this->objAddressSelectionModule->ControlId, false);
            
            if( 'Edit' == $strParameter )
                $this->objAddressSelectionModule = new AddressSelectionModule($this, 'SelectAddress', $intAddressId, true);
            elseif( 'New' == $strParameter )
                $this->objAddressSelectionModule = new AddressSelectionModule($this, 'SelectAddress', null, true);
            else//Note: includes Save and Cancel ..
            {
                if($intAddressId)
                    $this->objAddressSelectionModule = new AddressSelectionModule($this, 'SelectAddress', $intAddressId);
                else
                    $this->objAddressSelectionModule = new AddressSelectionModule($this, 'SelectAddress', $this->intPreviousAddressId);
                //Refresh the options listing ..
                $this->initMethodViews();
            }
            
            $this->objAddressSelectionModule->Visible = true;
            $this->AddChildControl($this->objAddressSelectionModule);            
        }
        /**
         * This Function is called when any input is sent - on failure the
         * fields are redrawn with optional error messages.
         */
        public function Validate()
        {
            $blnToReturn = true;
            // validate input here
            return $blnToReturn;
        }
        
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Notes':
                    return $this->txtNotes->Text ;
                case 'SelectedMethod':
                    return $this->objSelectedMethod ;
                case 'Address':
                    return $this->objAddress ;
                case 'HasActiveMethods':
                    return $this->blnHasActiveMethods ;
                case 'IsInternational':
                    return $this->blnIsInternational ;
                case 'DefaultCarrier':
                    return $this->strDefaultCarrier ;
                case 'DefaultServiceType':
                    return $this->strDefaultServiceType ;
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
                case 'IsInternational':
                    try {
                        return ($this->blnIsInternational = QType::Cast($mixValue, Qtype::Boolean ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'DefaultCarrier':
                    try {
                        return ($this->strDefaultCarrier = QType::Cast($mixValue, Qtype::String ));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'DefaultServiceType':
                    try {
                        return ($this->strDefaultServiceType = QType::Cast($mixValue, Qtype::String ));
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
        
  }//end class
}//end define
?>