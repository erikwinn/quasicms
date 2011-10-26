<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("CHECKOUTTOTALSVIEW.CLASS.PHP")){
define("CHECKOUTTOTALSVIEW.CLASS.PHP",1);

/**
* Class CheckOutTotalsView - provides display of order totals, shipping and handling
* charges for display during checkout
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: CheckOutTotalsView.class.php 234 2008-09-30 15:49:13Z erikwinn $
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
* @subpackage Classes
*/

 class CheckOutTotalsView extends QPanel
 {
        /**
        *@var CheckOutModule objControlBlock - the main control block for the check out module
        */
        protected $objControlBlock;
        /**
        *@var float fltShippingCharge - shipping for order
        */
        protected $fltShippingCharge;
        /**
        *@var float fltHandlingCharge - handling for order
        */
        protected $fltHandlingCharge;
        /**
        *@var float fltTaxesCharge - taxes for order
        */
        protected $fltTaxesCharge;
        /**
        *@var float fltTotalItemsCharge - sub total of all the items on an order
        */
        protected $fltTotalItemsCharge;
        /**
        *@var float fltTotalCharge - grand total of all charges for order
        */
        protected $fltTotalCharge;
        
        /**
        * @var QLabel lblHandlingCharge - display for handling charges ..
        */
        public $lblHandlingCharge;
        /**
        * @var QLabel lblShippingCharge - display for shipping charges ..
        */
        public $lblShippingCharge;
        /**
        * @var QLabel lblTaxesCharge - display for total taxes for the order
        */
        public $lblTaxesCharge;
        /**
        * @var QLabel lblTotalItemsCharge - display for total of Item charges for the order
        */
        public $lblTotalItemsCharge;
        /**
        * @var QLabel lblTotalCharge - display for grand total charges for the order
        */
        public $lblTotalCharge;

        /**
        * View constructor
        *@param CheckOutView  objControlBlock - parent controller module.
        *@param Order objOrder - the 
        */
        public function __construct( QPanel $objControlBlock)
        {
        
            try {
                parent::__construct($objControlBlock);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            
            $this->AutoRenderChildren = true;
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/CheckOutTotalsView.tpl.php';
            
            $this->lblShippingCharge = new QLabel($this);                        
            $this->lblHandlingCharge = new QLabel($this);
            $this->lblTotalItemsCharge = new QLabel($this);
            $this->lblTaxesCharge = new QLabel($this);
            $this->lblTotalCharge = new QLabel($this);
        }
        
        public function RefreshTotal()
        {
            $this->TotalCharge = $this->fltShippingCharge
                                               + $this->fltHandlingCharge
                                               + $this->fltTaxesCharge
                                               + $this->fltTotalItemsCharge;
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
                case 'ShippingCharge':
                    return $this->fltShippingCharge ;
                case 'HandlingCharge':
                    return $this->fltHandlingCharge ;
                case 'TotalCharge':
                    return $this->fltTotalCharge ;
                case 'TotalCharge':
                    return $this->fltTotalItemsCharge ;
                case 'TaxesCharge':
                    return $this->fltTaxesCharge ;
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
                case 'ShippingCharge':
                        $this->fltShippingCharge = $mixValue;
                        $this->lblShippingCharge->Text = money_format('%n', $mixValue);
                        $this->RefreshTotal();
                        break;                        
                case 'HandlingCharge':
                        $this->fltHandlingCharge = $mixValue;
                        $this->lblHandlingCharge->Text = money_format('%n', $mixValue);
                        $this->RefreshTotal();
                        break;                        
                case 'TaxesCharge':
                        $this->fltTaxesCharge = $mixValue;
                        $this->lblTaxesCharge->Text = money_format('%n', $mixValue);
                        $this->RefreshTotal();
                        break;                        
                case 'TotalItemsCharge':
                        $this->fltTotalItemsCharge = $mixValue;
                        $this->lblTotalItemsCharge->Text = money_format('%n', $mixValue);
                        $this->RefreshTotal();
                        break;                        
                case 'TotalCharge':
                        $this->fltTotalCharge = $mixValue;
                        $this->lblTotalCharge->Text = money_format('%n', $mixValue);
                        break;

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