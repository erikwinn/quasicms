<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/
if(!defined('QUASICMS') ) die('No Quasi.');

if (!defined("PAYPALNVPACTION.CLASS.PHP")){
define("PAYPALNVPACTION.CLASS.PHP",1);

/**
* Class PayByMailAction - Pay by mail (check or money order)
*
* This class provides an option for the customer to pay with a check or money order
* by mail. The order status has already been set to Pending so no further action is taken until the
* status is changed manually. Really this class is just a place holder for the logic so that we
* can create the option to select it. 
*
*NOTE: This action does NOT approve the transaction - therefor no order status will change
* when it returns and no order_totals will be inserted.
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: PayByMailAction.class.php 323 2008-10-27 16:14:55Z erikwinn $
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

    class PayByMailAction extends PaymentActionBase
    {
        /**
        * PayByMailAction Constructor
        *
        * @param Order objOrder - the Order to process
        */
        public function __construct(Order $objOrder)
        {
            try {
                parent::__construct($objOrder);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
        }
        
        /**
        * There is nothing to do with this payment method - all processing waits until we receive
        * a check and then must be completed via the adminstration interface. The Order has been
        * saved already as "Pending" - but we set it again here to trigger an email to the customer.
        *@return bool true on success
        */        
        public function Process()
        {
            $this->blnApproved = true;
            $this->objOrder->SetStatus(OrderStatusType::Pending);
            IndexPage::$objShoppingCart->DeleteAllShoppingCartItems();
            return true;
        }        
        public function PreProcess(){ return true;}
        public function PostProcess(){ return true;}
        protected function handleResponse(){}
        protected function createPOSTRequest(){}
        protected function createGETRequest(){}
    }//end class
}//end define

?>
