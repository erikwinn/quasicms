<?php
	require(__DATAGEN_CLASSES__ . '/ShoppingCartGen.class.php');

	/**
	 * The ShoppingCart class defined here represents the "shopping_cart" table
	 * in the database, and extends from the code generated abstract ShoppingCartGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 *
     * This class also provides a factory method to generate an Order object from the
     * current contents of the cart - see CreateNewOrder below.
     *   
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */
	class ShoppingCart extends ShoppingCartGen
    {
        /**
        *@var Order objOrder - a new Order object for the contents of this cart
        */
        protected $objOrder;
        
        /**
        *@var Account objAccount - a new Account object for the contents of this cart
        */
        protected $objAccount;
        
        /**
        *@var boolean blnUsePreviousAddresses - if true, attempt to use address from last order for new order.
        */
        protected $blnUsePreviousAddresses;
		
        /**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objShoppingCart->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
			return sprintf('Cart for %s',  $this->Account);
		}

        public static function LoadByAccountId($intAccountId)
        {
            return ShoppingCart::QuerySingle(QQ::Equal(QQN::ShoppingCart()->AccountId, $intAccountId) );
        }
        
        public function AddItem($intProductId)
        {
            $objSCItem = ShoppingCartItem::LoadByProductIdShoppingCartId($intProductId, $this->Id);
            if(!$objSCItem)
            {
                $objSCItem = new ShoppingCartItem();
                $objSCItem->ProductId = $intProductId;
                $objSCItem->ShoppingCartId = $this->Id;
            }//Note: quantity defaults to 1 on creation ..
            else
                $objSCItem->Quantity += 1;
             
            $objSCItem->Save();
            $this->RefreshCartModule();
            
        }
        public function RemoveItem($intProductId)
        {
            $objSCItem = ShoppingCartItem::LoadByProductIdShoppingCartId($intProductId, $this->Id);
            if($objSCItem)
            {
                $objSCItem->Delete();
                $this->RefreshCartModule();
            }
        }
        public function RefreshCartModule()
        {
            ///@todo - kludge to refresh the module, do this without reloading the page ..

             QApplication::Redirect(Quasi::$RequestUri );

/*            $objShoppingCartModule = IndexPage::$MainWindow->GetActiveModule('ShoppingCartModule');
            if($objShoppingCartModule instanceof ShoppingCartModule)
                $objShoppingCartModule->RefreshCart();*/
        }
        /**
        * This function creates a new Order object initilized with the current contents of the cart.
        * Note: the order is returned unsaved, both the order and order items are virtual and not
        * inserted to the database until Save() is called on the order object.
        *
        * The Order is returned unsaved with status set to "Shopping". The array of NewOrderItems in the
        * new order is loaded with OrderItems created from the ShoppingCart Items. 
        * If possible we set the Shipping cost based on the default configured shipping method
        * 
        *
        * This function uses several subfunctions to:
        *  - transfer order items from the shopping cart items
        *  - calculate estimated shipping
        *  - calculate taxes
        *  - set the address fields based on the Account addresses
        *  - set the price totals
        * @todo - be able to save  order/address state if they go back from checkout
        *  really the cart box could be merged into the Checkout module
        *     if($this->objOrder instanceof Order)
        *        return $this->objOrder;
        *@param boolean blnUsePreviousAddresses - if true, use the Addresses from the previous order
        * @return Order - an initialized order object
        */
        public function CreateNewOrder($blnUsePreviousAddresses=false)
        {
            $this->blnUsePreviousAddresses = $blnUsePreviousAddresses;
            
            if(! $this->AccountId)
                throw new QCallerException('CreateOrder called on a ShoppingCart that has no Account Id!');
                
            $this->objOrder = new Order();
            $this->objAccount = Account::LoadById($this->AccountId);
            $this->initOrderAddresses();
            
            if( $this->objOrder->ShippingCountryId != CountryType::GetId(STORE_COUNTRY) )
                $this->objOrder->IsInternational = true;
            else
                $this->objOrder->IsInternational = false;
            
            $this->objOrder->AccountId = $this->AccountId;
            $this->objOrder->StatusId = OrderStatusType::Shopping;

            // create the order items from shopping cart ..
            $this->initOrderItems();

            //try to set a default shipping charge estimate ..
            $this->initOrderShippingCharge();

        ///@todo  this should be more configurable ..
            $this->objOrder->HandlingCharged = DEFAULT_HANDLING_CHARGE;
                
            $this->objOrder->InitTax();
            return $this->objOrder;
            
        }
        /**
         * This function initializes the array of OrderItems and also various product totals in the Order.
         * The order items are obtained from the ShoppingCartItems, a Product is instatiated for each
         * to obtain values added to Order totals.
        */
        private function initOrderItems()
        {
            $fltProductTotalCharged = 0.0;
            $fltProductTotalCost = 0.0;
            $fltMaxX = 0.0;
            $fltMaxY = 0.0;
            $fltMaxZ = 0.0;
            
            foreach ( $this->GetShoppingCartItemArray() as $objShoppingCartItem )
            {
                $objItem = new OrderItem();
                $objItem->ProductId = $objShoppingCartItem->ProductId;
                $objItem->Quantity = $objShoppingCartItem->Quantity;
                $this->objOrder->AddNewOrderItem($objItem);
                
                //now, increment order values for each product ..
                $objProduct = Product::Load($objItem->ProductId);                
                //increment total price
                $fltProductTotalCharged += $objProduct->RetailPrice * $objItem->Quantity;
                $fltProductTotalCost += $objProduct->Cost * $objItem->Quantity;
                //increment total weight
                $this->objOrder->TotalOunces +=  $objProduct->Weight;
                //increment total max sizes
                if($objProduct->Width > $fltMaxX)
                    $fltMaxX = $objProduct->Width;
                if($objProduct->Height > $fltMaxY)
                    $fltMaxY = $objProduct->Height;
                    
                //BUG alert: add thickness, tranlates to "height" in shipping, sorry ..
                // this might need fixing, we are assuming things are stacked up and
                // that they are thin (like PCB boards ..)
                $fltMaxZ += $objProduct->Depth;
                
            }
            
            $this->objOrder->ProductTotalCharged = $fltProductTotalCharged;
            $this->objOrder->ProductTotalCost = $fltProductTotalCost;
            
            if($this->objOrder->TotalOunces >= 16)
            {
                $this->objOrder->TotalPounds = (int) floor( $this->objOrder->TotalOunces / 16 );
                $this->objOrder->TotalOunces = ( $this->objOrder->TotalOunces % 16);
            }
                
            $this->objOrder->XAxisSize = $fltMaxX;
            $this->objOrder->YAxisSize = $fltMaxY;
            $this->objOrder->ZAxisSize = $fltMaxZ;
        }
        /**
         * This function determines the default addresses for the Account and place them in Order fields
         * for shipping and billing address. These may be modified by the user in the CheckOutEditModule
         * The default addresses are those for the Person associated with the Account - note that these
         * may be changed by the user, here we are just setting the defaults for initial display.
         *
         * Note: if CreateNewOrder(true) was called, UsePreviousAddresses will be set true and will trigger
         * an attempt to initilize the order using the last used address - if no previous order exists the
         * usual default assignment occurs.
        */
        private function initOrderAddresses()
        {
            if($this->blnUsePreviousAddresses)
            {
                $aryClauses = array();
                array_push($aryClauses, QQ::OrderBy(QQN::Order()->CreationDate, false) );
                
                $objPreviousOrder = Order::QuerySingle(
                                                    QQ::Equal(QQN::Order()->AccountId, $this->AccountId),
                                                    $aryClauses
                                                    );
                //if possible, just copy addresses from previous order ..           
                if($objPreviousOrder instanceof Order)
                {
                    $this->objOrder = $objPreviousOrder;
                    $this->objOrder->Id = null;
                    $this->objOrder->Restored = false;
                    $this->objOrder->HandlingCharged = null;
                    $this->objOrder->ProductTotalCharged = null;
                    $this->objOrder->ShippingCharged = null;
                    $this->objOrder->ProductTotalCost = null;
                    $this->objOrder->ShippingCost = null;
                    $this->objOrder->Tax = null;
                    $this->objOrder->CreationDate = null;
                    $this->objOrder->LastModificationDate = null;
                    $this->objOrder->CompletionDate = null;
                    $this->objOrder->Notes = null;
                    $this->objOrder->ShippingMethodId = null;
                    $this->objOrder->PaymentMethodId = null;
                    
/* Bug alert - these may need to be reset .. however, they are all private
so we would need to override in Order. Leave unless problematic.
                    $this->objOrder->_objOrderChange = null;
                    $this->objOrder->_objOrderChangeArray = array();
                    $this->objOrder->_objOrderItem = null;
                    $this->objOrder->_objOrderItemArray = array();
                    $this->objOrder->_objOrderStatusHistory = null;
                    $this->objOrder->_objOrderStatusHistoryArray = array();
                    $this->objOrder->_objPaypalTransaction = null;
                    $this->objOrder->_objPaypalTransactionArray = array();
                    $this->objOrder->_objTrackingNumber = null;
                    $this->objOrder->_objTrackingNumberArray = array();
                    $this->objOrder->__strVirtualAttributeArray = array();
*/                    
                    //now, attempt to set address ids used ..
                    $this->objOrder->SetShippingAddress($objPreviousOrder->GetShippingAddress());
                    $this->objOrder->SetBillingAddress($objPreviousOrder->GetBillingAddress());

                    return;
                }
            }
            //Otherwise, try to get a shipping address or default to primary ..
            $objShippingAddress = Address::QuerySingle( QQ::AndCondition (
                            QQ::Equal(QQN::Address()->PersonId, $this->objAccount->PersonId ),
                            QQ::Equal(QQN::Address()->TypeId, AddressType::Shipping)
                        ) );
            if( null === $objShippingAddress )
                $objShippingAddress = Address::QuerySingle( QQ::AndCondition (
                            QQ::Equal(QQN::Address()->PersonId, $this->objAccount->PersonId ),
                            QQ::Equal(QQN::Address()->TypeId, AddressType::Primary)
                        ) );
            //could be that they entered something different in their account so check for associated persons/addresses ..   
            if( null === $objShippingAddress )
            {
                $aryPersonIds = array();
                $aryPersons = Person::QueryArray(QQ::Equal(QQN::Person()->OwnerPersonId, $this->objAccount->PersonId));
                if(!empty($aryPersons) )
                    foreach($aryPersons as $objPerson )
                        $aryPersonIds[] = $objPerson->Id;
                else
                    throw new QCallerException('ShoppingCart: No shipping address found for ' . $this->objAccount);
                
                $objShippingAddress = Address::QuerySingle( QQ::AndCondition (
                            QQ::In(QQN::Address()->PersonId, $aryPersonIds ),
                            QQ::In(QQN::Address()->TypeId, array(AddressType::Primary, AddressType::Shipping, AddressType::Billing))
                                                    ) );                            
            }
            //set the order's fields for shipping
            if(null != $objShippingAddress)
                 $this->objOrder->SetShippingAddress($objShippingAddress);
            else
                throw new QCallerException('ShoppingCart: No shipping address found for ' . $this->objAccount);
                
            //now try to get a billing address or default to primary ..
            $objBillingAddress = Address::QuerySingle( QQ::AndCondition (
                            QQ::Equal(QQN::Address()->PersonId, $this->objAccount->PersonId ),
                            QQ::Equal(QQN::Address()->TypeId, AddressType::Billing)
                        ) );
            if( null === $objBillingAddress )
                $objBillingAddress = Address::QuerySingle( QQ::AndCondition (
                            QQ::Equal(QQN::Address()->PersonId, $this->objAccount->PersonId ),
                            QQ::Equal(QQN::Address()->TypeId, AddressType::Primary)
                        ) );
            if( null === $objBillingAddress )
                $objBillingAddress = $objShippingAddress;
                                 
                
            //set the order's fields for billing, use shipping as a default if none was found above
            if(null != $objBillingAddress)
                 $this->objOrder->SetBillingAddress($objBillingAddress);
            elseif(null != $objShippingAddress)
                 $this->objOrder->SetBillingAddress($objShippingAddress);
                 
        }

        /**
        * This function attempts to set the shipping charge based on the default address
        * and the configured default provider
        */
        private function initOrderShippingCharge()
        {
            $objShippingMethod = ShippingMethod::QuerySingle( QQ::AndCondition(
                       QQ::Equal(QQN::ShippingMethod()->Active, true),
                       QQ::Equal( QQN::ShippingMethod()->Carrier, DEFAULT_SHIPPING_CARRIER ),
                       QQ::Equal( QQN::ShippingMethod()->ServiceType, DEFAULT_SHIPPING_SERVICE )
                     ));
                     
            //possible that default method is inactive ..
            if( $objShippingMethod instanceof ShippingMethod )
            {
                $this->objOrder->ShippingMethodId = $objShippingMethod->Id;
                $objShippingMethod->Init($this->objOrder);
                $this->objOrder->ShippingCharged = $objShippingMethod->GetRate();
                if(! $this->objOrder->ShippingCharged )
                {//errors .. fallback to default.
                    $this->objOrder->ShippingCharged = DEFAULT_SHIPPING_RATE;
                    $this->objOrder->ShippingMethodId = 1;
                }                
            } else {
                $this->objOrder->ShippingCharged = DEFAULT_SHIPPING_RATE;
                $this->objOrder->ShippingMethodId = 1;
            }
        }
		
		// Override or Create New Properties and Variables
		// For performance reasons, these variables and __set and __get override methods
		// are commented out.  But if you wish to implement or override any
		// of the data generated properties, please feel free to uncomment them.
/*
		protected $strSomeNewProperty;

		public function __get($strName) {
			switch ($strName) {
				case 'SomeNewProperty': return $this->strSomeNewProperty;

				default:
					try {
						return parent::__get($strName);
					} catch (QCallerException $objExc) {
						$objExc->IncrementOffset();
						throw $objExc;
					}
			}
		}

		public function __set($strName, $mixValue) {
			switch ($strName) {
				case 'SomeNewProperty':
					try {
						return ($this->strSomeNewProperty = QType::Cast($mixValue, QType::String));
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
*/
	}
?>