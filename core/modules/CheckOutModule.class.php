<?php
if(!defined('QUASICMS') ) die("No quasi.");

if (!defined("CHECKOUTMODULE.CLASS.PHP")){
define("CHECKOUTMODULE.CLASS.PHP",1);

    /**
    * Stage Types for the Checkout process - oh for an enum type ..
    * These stages represent each of the parts of the checkout process - they
    * are both set to keep track of which module is active so that we may take
    * appropriate actions on forward or back movements according to what the
    * previous Stage was and they are passed as ActionParameters to indicate
    * which Stage to show next.
    *
    * Note that ShoppingCart is not actually a stage module here - it is merely
    * a redirect to the ShoppingCartView page. Likewise, Cancel redirects to Home.
    *
    *@package Quasi
    *@subpackage Modules
    */
    class CheckOutStage{
            /**
            *@var constant integer - flags for indicating current stage of the process
            */
            const ShoppingCart = 0;
            const Shipping = 1;
            const Payment = 2;
            const Review = 3;
            const Confirmation = 4;
            const Cancel = 6;
    }

/**
* Class CheckOutModule - a module for the checkout process
* This class provides a central page module with four modules that make
* up a four part checkout process.
*
*  The first part displays the shipping options information to the user with sections 
* for choosing addresses and shipping methods.
*
*  The second part shows payment options and billing address.
*
*  The third part shows the order again and all of the choices with
* final charges and a confirmation action - the user may also choose to
* return to a previous step to modify the quantities in the order or other selections
* (addresses, shipping method, payment, etc.). Note that if they choose to modify
* the quantity they are redirected to the ShoppingCartView page and must
* reinitiate the checkout process (by clicking "Check Out").
*
* The final module confirms the status of the payment and displays misc confirmation
* messages.
*
*@todo
*   - send order email confirmation
*   - PayPal Express checkout button and action on CheckOutEditModule panel, this
*   will basically call the paypal payment action, ie. it should behave like clicking the
*   "purchase" button in the PaymentModule .. 
*
*@author Erik Winn <erikwinnmail@yahoo.com>
* 
* $Id: CheckOutModule.class.php 457 2008-12-23 20:11:54Z erikwinn $
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


 class CheckOutModule extends QPanel
 {
        protected $objAccount = null;
        protected $objShoppingCart = null;
        protected $intCurrentStage = 0;

        /**
        *@var Order - the current order
        */
        protected $objOrder = null;
        /**
        *@var ShippingMethod - reference to the selected shipping method
        */
        protected $objShippingMethod = null;
        /**
        *@var PaymentMethod - reference to the selected payment method
        */
        protected $objPaymentMethod = null;
        /**
        *@var boolean blnCartEmpty - set to true if there are no items in the cart
        */
        protected $blnCartEmpty = true;
        /**
        * This indicates whether the customer has clicked "Purchase" yet - if they do this and
        * then hit back in the browser we want to clean up and start over ..
        * NOTE: this needs to be moved somewhere global to maintain state ( perhaps even $_SESSION since
        * the user may have been transferred offsite )
        *@todo - handle browser back actions when payment already initiated
        *@var boolean 
        */
        protected $blnTransactionInitiated=false;
        /**
        *@var boolean blnTransactionApproved - indicates whether the payment was accepted or not
        */
        protected $blnTransactionApproved = false;
        /**
        *@var string strTransactionMessage - string to contain messages from the payment server
        */
        protected $strTransactionMessage;
        /**
        *@var string strErrors - general error storage ..
        */
        protected $strErrors;
        
        /**
        *@var Address - reference to the selected or default shipping address
        */
        public $objShippingAddress;
        /**
        *@var Address - reference to the selected or default billing address
        */
        public $objBillingAddress;        
        /**
        * @var OrderTotalsView - view panel to display the order totals
        */
        public $objOrderTotalsView;
        /**
        * @var PaymentModule - module to manage the selection of a payment method
        */
        public $objPaymentModule;
        /**
        * @var ShippingModule - module to manage the selection of a shipping method
        */
        public $objShippingModule;
        /**
        * @var CheckOutReviewModule - module to display a review of the order and accept submission
        */
        public $objCheckOutReviewModule;
        /**
        * @var CheckOutConfirmationModule - module to display order/payment confirmation or errors
        */
        public $objCheckOutConfirmationModule;
        /**
        * @var Qlabel lblProgressBar - shows a progress image indicating the current stage
        */
        public $lblProgressBar;
        
        /**
        * This refers to the currently active module - it is assigned a module according to the current
        * stage of the process
        *@var QPanel - the current panel.
        */
        public $pnlCurrentPanel = null;
        /**
        * This panel shows the title heading for  currently active module - it is assigned text according to the current
        * stage of the process
        *@var QPanel - the current panel title heading.
        */
        public $pnlHeading;
        
        public $btnContinue;
        public $btnCancel;
        public $btnBack;

        public $objWaitIcon;        
        public $pnlHeading;
        public $lblMessages;
        /**
        * Module constructor
        * NOTE: This module ignores the required extra parameters ..
        *@param ContentBlockView - parent controller object.
        *@param mixed - extra parameters, ignored
        */
        public function __construct( ContentBlockView $objParentObject, $mixParameters=null)
        {

            try {
                parent::__construct($objParentObject);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }            
            $this->objAccount =& IndexPage::$objAccount;
            $this->objShoppingCart =& IndexPage::$objShoppingCart;
            $this->AutoRenderChildren = true;
            $this->strTemplate = __QUASI_CORE_TEMPLATES__ . '/CheckOutModule.tpl.php';

            $this->pnlHeading = new QPanel($this);
            $this->pnlHeading->HtmlEntities = false;
            $this->pnlHeading->AddCssClass('CheckoutHeading');
            
            $this->lblProgressBar = new QLabel($this);
            $this->lblProgressBar->HtmlEntities = false;
            $this->lblProgressBar->CssClass = 'ProgressBarShipping';
            $this->lblProgressBar->Text = sprintf('<span class="heading">%s</span><span class="label">%s</span>
                                                                       <span class="label">%s</span><span class="label">%s</span>
                                                                       <span class="label">%s</span><span class="label">%s</span>',
                                                                        STORE_NAME . ' ' . Quasi::Translate('Checkout Process') . ':',
                                                                        Quasi::Translate('cart'),
                                                                        Quasi::Translate('shipping'),
                                                                        Quasi::Translate('payment'),
                                                                        Quasi::Translate('review order'),
                                                                        Quasi::Translate('receipt'));

            $this->lblMessages = new QLabel($this);
            $this->lblMessages->HtmlEntities = false;
            
            $this->pnlCurrentPanel = new QPanel($this);
            $this->pnlCurrentPanel->AutoRenderChildren = true;
            
            // Only show anything if there is an active Account and ShoppingCart
            if( $this->objAccount instanceof Account && $this->objShoppingCart instanceof ShoppingCart)
            {
                $this->objOrder = $this->objShoppingCart->CreateNewOrder(true);

                $aryOrderItems = $this->objOrder->GetNewOrderItemsArray();
                
                if ( empty($aryOrderItems) )
                {
                    $this->pnlHeading->Text = '&nbsp;&nbsp;&nbsp;' . Quasi::Translate('Empty Cart');
//                    $this->pnlCurrentPanel = new QPanel($this);
                    $this->pnlCurrentPanel->Text = Quasi::Translate('There are no items to check out') . '.';
                    return;
                }
                else
                    $this->blnCartEmpty = false;

                //create a display of the order summary ..
                $this->objOrderTotalsView = new OrderTotalsView($this, $this->objOrder);

                //create buttons to manage work flow between panels - the signals are handled here.
                // Continue goes to the next panel, Back returns to previous panel, Cancel goes Home
                $this->btnContinue = new QButton($this);
                $this->btnContinue->Text = Quasi::Translate('Continue');
                if(IndexPage::$blnAjaxOk)
                    $this->btnContinue->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnContinue_Click'));
                else
                    $this->btnContinue->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnContinue_Click'));

                $this->btnBack = new QButton($this);
                $this->btnBack->Text = Quasi::Translate('Back');
                if(IndexPage::$blnAjaxOk)
                    $this->btnBack->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnBack_Click'));
                else
                    $this->btnBack->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnBack_Click'));
                
                $this->btnCancel = new QButton($this);
                $this->btnCancel->Text = Quasi::Translate('Cancel');
                if(IndexPage::$blnAjaxOk)
                    $this->btnCancel->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnCancel_Click'));
                else
                    $this->btnCancel->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnCancel_Click'));
                
                $this->objWaitIcon = new QWaitIcon($this);
                //start out with the shipping options displayed
                $this->GoForward( CheckOutStage::Shipping );
            }
            else
                $this->pnlCurrentPanel->Text = Quasi::Translate('We are sorry, you must be logged in to check out.');
        }
         /**
        * This function is called when the user clicks "Back" - it checks for the current stage and
        * calls GoBack with the appropriate parameters.
        *@param string strFormId - a string representation of the CSS Id for the main form
        *@param string strControlId - a string representation of the CSS Id for the control calling this function
        *@param string strParameter - a string containing optionally set parameters 
        */               
        public function btnBack_Click($strFormId, $strControlId, $strParameter)
        {
            switch( $this->intCurrentStage )
            {
                case CheckOutStage::Shipping:
                    return $this->GoBack(CheckOutStage::ShoppingCart);
                case CheckOutStage::Payment:
                    return $this->GoBack(CheckOutStage::Shipping);
                case CheckOutStage::Review:
                    return $this->GoBack(CheckOutStage::Payment);
            }
        }
        /**
        * This function is called when the user clicks "Continue" - it checks for the current stage and
        * calls GoForward with the appropriate parameters. 
        *@param string strFormId - a string representation of the CSS Id for the main form
        *@param string strControlId - a string representation of the CSS Id for the control calling this function
        *@param string strParameter - a string containing optionally set parameters 
        */
        public function btnContinue_Click($strFormId, $strControlId, $strParameter)
        {
            switch( $this->intCurrentStage )
            {
                case CheckOutStage::Shipping:
                    return $this->GoForward(CheckOutStage::Payment);
                case CheckOutStage::Payment:
                    return $this->GoForward(CheckOutStage::Review);
                case CheckOutStage::Review:
                    return $this->GoForward(CheckOutStage::Confirmation);
                case CheckOutStage::Confirmation:
                    QApplication::Redirect(__QUASI_SUBDIRECTORY__ . '/index.php/Home');
            }
        }
        /**
        * This function is called when the user clicks "Cancel" - it deletes the order and redirects
        * the user to the home page .. 
        *@param string strFormId - a string representation of the CSS Id for the main form
        *@param string strControlId - a string representation of the CSS Id for the control calling this function
        *@param string strParameter - a string containing optionally set parameters 
        */
        public function btnCancel_Click($strFormId, $strControlId, $strParameter)
        {
            //NOTE: perhaps we should clear the shopping cart here? or provide some
            // other way to do that somewhere ..
             $this->GoForward(CheckOutStage::Cancel);          
        }
        
        public function GoBack($NextStage)
        {                

            $this->pnlCurrentPanel->RemoveChildControls(false);
            
            switch($NextStage)
            {            
                case CheckOutStage::ShoppingCart:
                    Quasi::Redirect('http://'  . Quasi::$ServerName . __QUASI_SUBDIRECTORY__ . '/index.php/ShoppingCart');
                    break;
                case CheckOutStage::Shipping:
                    $this->pnlHeading->Text = '&nbsp;&nbsp;&nbsp;' . Quasi::Translate('Shipping Options');
                    $this->lblProgressBar->CssClass = 'ProgressBarShipping';
                    $this->objShippingModule->SetParentControl($this->pnlCurrentPanel);
                    $this->objOrderTotalsView->Visible = true;
                    break;
                case CheckOutStage::Payment:
                    $this->pnlHeading->Text = '&nbsp;&nbsp;&nbsp;' . Quasi::Translate('Payment Options');
                    $this->lblProgressBar->CssClass = 'ProgressBarPayment';
                    $this->objPaymentModule->SetParentControl($this->pnlCurrentPanel);
/*                    if($this->objCheckOutReviewModule instanceof CheckOutReviewModule)
                        $this->objCheckOutReviewModule->Visible = false;*/
                    $this->objOrderTotalsView->Visible = true;
                    break;
            }
            
            $this->btnContinue->Text = Quasi::Translate('Continue');
            $this->intCurrentStage = $NextStage;            
        }
        
        private function GoForward($NextStage)
        {
            if(null !== $this->pnlCurrentPanel)
            {
                $this->pnlCurrentPanel->Visible = false;
                $this->pnlCurrentPanel->RemoveChildControls(false);
            }
            switch($NextStage)
            {
                case CheckOutStage::Shipping:
                    //the spaces make room for the CSS cart image ..
                    $this->pnlHeading->Text = '&nbsp;&nbsp;&nbsp;' . Quasi::Translate('Shipping Options');
                    $this->lblProgressBar->CssClass = 'ProgressBarShipping';
                    
//to debug:                    $this->objShippingModule = new ShippingModule($this->pnlCurrentPanel, $this, $this->objOrder, true);
                    $this->objShippingModule = new ShippingModule($this->pnlCurrentPanel, $this, $this->objOrder);
                    $this->intCurrentStage = CheckOutStage::Shipping;
                    break;
                case CheckOutStage::Payment:
                    $this->pnlHeading->Text = '&nbsp;&nbsp;&nbsp;' . Quasi::Translate('Payment Options');
                    $this->lblProgressBar->CssClass = 'ProgressBarPayment';
                    if($this->objPaymentModule instanceof PaymentModule)
                        $this->objPaymentModule->SetParentControl($this->pnlCurrentPanel);
                    else
                    {
                        $this->pnlCurrentPanel->RemoveChildControls(false);
                        $this->objPaymentModule = new PaymentModule($this->pnlCurrentPanel, $this->objOrder);
                    }
                    $this->intCurrentStage = CheckOutStage::Payment;
                    break;
                case CheckOutStage::Review:
                    $this->pnlHeading->Text = '&nbsp;&nbsp;&nbsp;' . Quasi::Translate('Order Review');
                    $this->lblProgressBar->CssClass = 'ProgressBarReview';
                    if($this->objCheckOutReviewModule instanceof CheckOutReviewModule)
                    {
                        $this->objCheckOutReviewModule->RefreshView($this->objOrder);
                        $this->objCheckOutReviewModule->SetParentControl($this->pnlCurrentPanel);
                    }
                    else
                    {
                        $this->pnlCurrentPanel->RemoveChildControls(true);
                        $this->objCheckOutReviewModule = new CheckOutReviewModule($this->pnlCurrentPanel, $this, $this->objOrder);
                    }
                    
                    //the review panel draws its own order totals
                    $this->objOrderTotalsView->Visible = false;

                    //We have to do a server post back to allow the header(Location:) redirect for PayPal to work
                    // in all cases, else the browser just gets it in XML and does nothing (some complain in fact) ..
                    $this->btnContinue->RemoveAllActions(QClickEvent::EventName);
                    $this->btnContinue->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnContinue_Click'));
                    $this->btnContinue->Text = Quasi::Translate('Submit');
                    
                    $this->intCurrentStage = CheckOutStage::Review;
                    break;
                case CheckOutStage::Confirmation:
                    $this->pnlHeading->Text = '&nbsp;&nbsp;&nbsp;' . Quasi::Translate('Your order has been received') . '.';
                    $this->lblProgressBar->CssClass = 'ProgressBarReceipt';
                    $this->pnlCurrentPanel->RemoveChildControls(true);

                    //submit payment ..
                    $this->SubmitPayment();
                    $this->btnBack->Visible = false;
                    $this->btnCancel->Visible = false;
                    $this->btnContinue->Text = Quasi::Translate('Continue');
                    // set up the confirmation panel ..
                    $this->ShowCheckOutConfirmationModule();
                    
                    $this->intCurrentStage = CheckOutStage::Confirmation;
                    break;
                case CheckOutStage::ShoppingCart:
                    Quasi::Redirect('http://'  . Quasi::$ServerName . __QUASI_SUBDIRECTORY__ . '/index.php/ShoppingCart');
                    break;
                case CheckOutStage::Cancel:
                    //this shouldn't happen (since we don't save til the end) but maybe:
                    if($this->objOrder instanceof Order && null != $this->objOrder->Id)
                       $this->objOrder->Delete();
                    QApplication::Redirect(__QUASI_SUBDIRECTORY__ . '/index.php/Home');
                    break;
                default:
                    throw new QCallerException("Unknown Upload Stage passed.");
            }
            $this->pnlCurrentPanel->Visible = true;
        }
        public function RefreshOrderTotalsView()
        {
            $this->objOrderTotalsView->SetTotals($this->objOrder);
        }
        /**
        * The final act - this function recieves the signal to process payment for an Order. This
        * is where a PaymentAction for the order is created and triggered.
        * Here we also first save the order and update the order status, order_status_history - initially
        * to "Pending" and to "Paid" on approval. Note that if a PaymentAction fails the action itself
        * deletes the order so no further action is taken here in that case.
        *
        * NOTE: Payment methods that require redirecting the user away from here (eg. PayPal ) or
        * methods that do not accept immediate payment (Pay by Mail, Fax, wire transfer etc ..)
        * must naturally finish the process elsewhere so status will remain "Pending" for these.
        */
        public function SubmitPayment()
        {
        
        ///@todo Fixme - this doesn't work if the user hits back in browser, figure out a check here ..
            if( $this->blnTransactionInitiated )
                throw new QCallerException('Error: attempt to submit payment already initiated.');
            if( $this->objOrder->Id )
                throw new QCallerException('Error: attempt to submit payment on completed order.');
                
            $this->blnTransactionInitiated = true;
           
            $this->objOrder->StatusId = OrderStatusType::Pending;
            $this->objOrder->Notes = $this->objShippingModule->Notes;
            //save the order and get an ID (reload is automatic)..
            $intOrderId = $this->objOrder->Save();
            
            //make an entry into the order_status_history table
            $objOrderStatusHistory = new OrderStatusHistory();
            $objOrderStatusHistory->StatusId = OrderStatusType::Pending;
            $objOrderStatusHistory->OrderId = $intOrderId;
            $objOrderStatusHistory->Notes = $this->objShippingModule->Notes;
            $objOrderStatusHistory->Save();
            
            //save the order items - must be done after the order id is set ..
            $this->objOrder->SaveNewOrderItems();

            //create a payment action object ..
            $strActionClass = $this->objPaymentModule->SelectedMethod->ActionClassName;
            if(!class_exists($strActionClass))
                throw new QCallerException( 'Missing PaymentMethod Class: ' . $strActionClass );
                
            $objPaymentAction = new $strActionClass($this->objOrder);
            
            //retrieve credit card input and initialize payment action
            if($this->objPaymentModule->SelectedMethod->RequiresCcNumber)
            {
                $objPaymentAction->CCNumber = $this->objPaymentModule->txtCCNumber->Text;
                $objPaymentAction->CCExpirationYear = $this->objPaymentModule->lstCCExpirationYear->SelectedValue;
                $objPaymentAction->CCExpirationMonth = $this->objPaymentModule->lstCCExpirationMonth->SelectedValue;
                $objPaymentAction->CCVNumber = $this->objPaymentModule->txtCCVNumber->Text;
            }

            $objPaymentAction->TestMode = $this->objPaymentModule->SelectedMethod->TestMode;

            //Note: at this point PayPal Express will redirect to paypal.com ..
            $objPaymentAction->PreProcess();
            $objPaymentAction->Process();
            $objPaymentAction->PostProcess();

            //set some data for the confirmation module ..
            $this->strTransactionMessage = $objPaymentAction->StatusText;
            $this->strErrors = $objPaymentAction->Errors;
            $this->blnTransactionApproved = $objPaymentAction->Approved;
        }
        /**
        * This function is called when a purchase has been completed.
        * Basically it hides all the other panels/modules and shows the payment confirmation message.
        * Any changes to the confirmation display should be done here.
        */
        public function ShowCheckOutConfirmationModule()
        {
            $this->objCheckOutConfirmationModule = new CheckOutConfirmationModule($this->pnlCurrentPanel, $this, $this->objOrder);
            
            $strApprovedText = '<div class="heading">' . Quasi::Translate("Thank You for your purchase") . '! </div>';
            $strApprovedText .= '</p>' . Quasi::Translate('Your Order Number is') . ': &nbsp; &nbsp; ' . $this->objOrder->Id . ' </p>';

            $strApprovedText .= '</p>' . Quasi::Translate('We will email you shortly with a confirmation of your order') . '. </p>'
                                        . '</p>' . Quasi::Translate('Please make sure that you have given a correct email address with which to contact you') . '. </p>';

            $strDeclinedText = Quasi::Translate(" We're Sorry - Your payment has been declined - the message below may help to solve the problem:") . '. <br />';
            
            if( $this->blnTransactionApproved )
                $this->objCheckOutConfirmationModule->Message = $strApprovedText;
            else
            {
               //for testing - extra error messages  ..
//                $msg = $strDeclinedText . '<p> Transaction Errors: <br />' . $this->strErrors . '</p>' ;
//                $this->objCheckOutConfirmationModule->Message = $msg ;
                
                $this->objCheckOutConfirmationModule->Message = $strDeclinedText ;
            }
            if('' != $this->strTransactionMessage)
                $this->objCheckOutConfirmationModule->Message .= '<p> Transaction Messages: <br />' .  $this->strTransactionMessage . '</p>' ;
            $this->objCheckOutConfirmationModule->Visible = true;
        }

        public function Validate(){return true;}
        
        ///Gettors
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Errors':
                    return $this->strErrors ;
                case 'Order':
                    return $this->objOrder ;
                case 'ShippingAddress':
                    return $this->objShippingModule->Address;
                case 'BillingAddress':
                    return $this->objPaymentModule->Address;
                case 'ShippingMethod':
                    return $this->objShippingModule->SelectedMethod;
                case 'PaymentMethod':
                    return $this->objPaymentModule->SelectedMethod;
                case 'ShippingModule':
                    return $this->objShippingModule;
                case 'TotalOunces':
                    return $this->objOrder->fltTotalOunces;
                case 'TotalPounds':
                    return $this->objOrder->intTotalPounds;
                case 'XAxisSize':
                    return $this->objOrder->fltXAxisSize;
                case 'YAxisSize':
                    return $this->objOrder->fltYAxisSize;
                case 'ZAxisSize':
                    return $this->objOrder->fltZAxisSize;
                case 'CartEmpty':
                    return $this->blnCartEmpty;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }
        ///Settors
        public function __set($strName, $mixValue)
        {
            switch ($strName)
            {
                case 'Order':
                    try {
                        return ($this->objOrder = QType::Cast($mixValue, 'Order' ));
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

     ////////////// temp .. ////////////////////////////////////////////////////////////////
        
        /**
         * This function refreshes the information in the order to match user input
         *@todo this should update the CheckOutReviewModule.
        */
/*        private function refreshOrderItems()
        {
//            $this->lblMessages->Text='';
            
            //rebuild Order items array according to user input (quantities)
            $this->aryOrderItems = array();
            $aryItemViews =& $this->objCheckOutEditModule->ItemListModule->aryCheckOutItemViews;
            foreach( $aryItemViews as $objView )
            {
                $item = $objView->objOrderItem;
                //objView->Quantity is from the input field
                $item->Quantity = $objView->Quantity;
                $this->aryOrderItems[] = $item;
            }
            //adjust shopping cart to match.
            $aryCartItems =$this->objShoppingCart->GetShoppingCartItemArray();
            foreach($aryCartItems as &$objCartItem )
            {
                foreach($this->aryOrderItems as $oi_index => $objOrderItem )
                {
                    if($objOrderItem->ProductId != $objCartItem->ProductId)
                        continue;
                    //if the quantity was set to zero or less, remove from both arrays .
                    if($objOrderItem->Quantity <= 0)
                    {
                        $objCartItem->Delete();
                        unset($this->aryOrderItems[$oi_index]);
                    }
                    else
                    {
                        $objCartItem->Quantity = $objOrderItem->Quantity;
                        $objCartItem->Save();                        
                    }
                }
            }
            //refresh the shopping cart's idea of its contents ..
            $this->objShoppingCart->Reload();
            
        }*/
        
    }//end class
}//end define
?>