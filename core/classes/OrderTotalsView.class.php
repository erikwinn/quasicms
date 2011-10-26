<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("ORDERTOTALSVIEW.CLASS.PHP")){
define("ORDERTOTALSVIEW.CLASS.PHP",1);


/**
* Class OrderTotalsView - display totals summary for an order.
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* 
* $Id: OrderTotalsView.class.php 234 2008-09-30 15:49:13Z erikwinn $
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
	class OrderTotalsView extends QPanel
    {
        protected $objOrder;

        protected $blnShowTitle;
        
		public $lblSubTotal;
		public $lblTax;
        public $lblShipping;
        public $lblHandling;
        public $lblGrandTotal;

		public function __construct($objParentObject,
                                                     $objOrder,
                                                     $blnShowTitle = true,      
                                                     $strControlId = null)
        {
			try {
				parent::__construct($objParentObject, $strControlId);
			} catch (QCallerException $objExc) {
				$objExc->IncrementOffset();
				throw $objExc;
			}
            $this->blnShowTitle = $blnShowTitle;
                     
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/OrderTotalsView.tpl.php';            
			$this->lblSubTotal = new QLabel($this);
            $this->lblTax = new QLabel($this);
            $this->lblShipping = new QLabel($this);
            $this->lblHandling = new QLabel($this);
            $this->lblGrandTotal = new QLabel($this);
            
            $this->SetTotals($objOrder);              
		}
        
        public function SetTotals($objOrder)
        {
            $this->objOrder =& $objOrder;
            $this->lblSubTotal->Text = money_format('%n', $objOrder->ProductTotalCharged);
            if($objOrder->Tax > 0)
                $this->lblTax->Text = money_format('%n', $objOrder->Tax);
            if($objOrder->ShippingCharged > 0)
                $this->lblShipping->Text = money_format('%n', $objOrder->ShippingCharged);
            if($objOrder->HandlingCharged > 0)
                $this->lblHandling->Text = money_format('%n', $objOrder->HandlingCharged);
            $fltTotal = $objOrder->ProductTotalCharged
                          + $objOrder->ShippingCharged
                          + $objOrder->HandlingCharged
                          + $objOrder->Tax;
            $this->lblGrandTotal->Text = money_format('%n', $fltTotal);
        }      
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'ShowTitle':
                    return $this->blnShowTitle ;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
	}//end class
 }//end define   
?>