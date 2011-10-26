<?php
	require(__DATAGEN_CLASSES__ . '/OrderGen.class.php');

	/**
	 * The Order class defined here represents the "order" table
	 * in the database, and extends from the code generated abstract OrderGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
     *
     * This class overrides several generated methods and also provides several
     * other processing methods including sending emails on status change, setting
     * shipping/billing addresses, creating shipping labels, etc..
     *   
	 * $Id: Order.class.php 514 2009-03-19 15:43:15Z erikwinn $
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */
	class Order extends OrderGen
    {
        /**
         * An array of new order items for a newly created order.
         * Note: These OrderItems do not have the order_id set until SaveOrderItems is called and this
         * may be called only after an the Order has been saved.
         *
         * @var array aryNewOrderItems - an array of OrderItems
         */
        public $aryNewOrderItems;
        /**
        * An image to be used to print a shipping label
        *@var gdimage objShippingLabelImage
        */
        protected $objShippingLabelImage;
        /**
        * The weight and size members are for use during checkout - as yet they are temporary values
        * and not tracked in the database by order since they are in the Product information.
        * 
        *@todo - Consider putting dimensions (weight, size, etc ) into the Order table as well
        * as they could also be (sorry) dimensional attributes ..
        *@var integer the total weight in pounds
        */
        protected $intTotalPounds = 0;
        /**
        *@var float the total weight in ounces
        */
        protected $fltTotalOunces = 0;
        /**
        * This refers to the X axis, translates to "width" for shipping, "width" in product
        * Why not "fltWidth"? Products may be eg. CAD designs, in which case "width" is ambiguous.
        * Basing this on screen coordinates is more precise.
        *@var float the total width in inches
        */
        protected $fltXAxisSize = 0.0;
        /**
        * This refers to the Y axis, translates to "length" for shipping, "height" in product
        *@var float the total height in inches
        */
        protected $fltYAxisSize = 0.0;
        /**
        * This refers to the Z axis, translates to "height" for shipping, "depth" in product
        *@var float the total height in inches
        */
        protected $fltZAxisSize = 0.0;
        /**
        *@var boolean blnIsInternational - true if the ShippingCountryId != shipping origin country.
        */
        protected $blnIsInternational;
        /**
        *@var boolean intShippingAddressId - the shipping address used to initialize the fields or null.
        */
        protected $intShippingAddressId;
        /**
        *@var boolean intBillingAddressId - the billing address used to initialize the fields or null.
        */
        protected $intBillingAddressId;
        
        
		/**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objOrder->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */      
		public function __toString()
        {
			return sprintf('Order# %s',  $this->intId);
		}
        /**
        * This function sets the StatusId for the Order. Depending on the status it also
        * can send an email notification to the customer. The Order is also Saved with
        * the new status.
        */
        public function SetStatus($intStatusId)
        {
            switch($intStatusId)
            {
                case OrderStatusType::Pending:
                    $this->SendPendingNotice();
                    break;
                case OrderStatusType::Paid:
                    $this->SendPaidNotice();
                    break;
                case OrderStatusType::Shipped:
                    if( 'PickUp' == $this->ShippingMethod->Carrier)
                        $this->SendLocalPickupNotice();
                    else
                        $this->SendShippingNotice();
                    break;
                case OrderStatusType::Cancelled:
                    $this->SendCancellationNotice();
                    break;
                case OrderStatusType::Problem:
                    $this->SendProblemNotice();
                    break;
                default:
            }
            $this->StatusId = $intStatusId;
            $this->Save(false, true);
            $this->Reload();
        }
        /**
        * Initializes the taxes by destination ..
        */
        public function InitTax()
        {
            $objTaxRate = TaxRate::LoadByZoneId($this->ShippingZoneId);
            if($objTaxRate)
                $this->fltTax = $this->ProductTotalCharged * $objTaxRate->Rate;
        }
       /**
        *Send a pending payment confirmation notification to the customer
        */
        public function SendPendingNotice()
        {
            $objPerson = Person::Load($this->Account->PersonId);

            $strText = Quasi::Translate('Dear') . ' ' . $objPerson->FullName . ",\n\n";
            
            $strText .= Quasi::Translate('Thank You for your order! Your payment is currently pending.') . "\n";
            $strText .= Quasi::Translate('You will receive a notification as soon as the payment has been confirmed.') . "\n\n";
            
            $strText .= $this->formatEmailOrderItemsInfo();
            $strText .= '   --------------------------------------------------' . "\n";
            $strText .= Quasi::Translate(' Thank You for placing an order with us!') . "\n";
            $strText .= Quasi::Translate(' If you have any questions please email support at ') ;
            $strText .= STORE_EMAIL_ADDRESS . "\n";
            $strText .=  STORE_NAME . ' ' . Quasi::Translate('Customer Service') . "\n";
            
            $this->SendEmail($strText, STORE_NAME . ' Pending Payment Confirmation - Order #' . $this->Id );
        }
        /**
        *Send a payment received confirmation notification to the customer
        */
        public function SendPaidNotice()
        {
            $objPerson = Person::Load($this->Account->PersonId);

            $strText = Quasi::Translate('Dear') . ' ' . $objPerson->FullName . ",\n\n";
            
            $strText .= Quasi::Translate('Thank You for your order!') . "\n\n";
            
            $strText .= $this->formatEmailOrderItemsInfo();
            $strText .= '   --------------------------------------------------' . "\n";
            $strText .= $this->formatEmailOrderPaymentMethod();
            $strText .= '   --------------------------------------------------' . "\n";
            $strText .= $this->formatEmailOrderShippingMethod();
            $strText .= '   --------------------------------------------------' . "\n";
            $strText .= $this->formatEmailOrderShippingAddress();
            $strText .= '   --------------------------------------------------' . "\n";
            $strText .= $this->formatEmailOrderBillingAddress();
            $strText .= '   --------------------------------------------------' . "\n";
            $strText .= Quasi::Translate(' Thank You for placing an order with us!') . "\n";
            $strText .= Quasi::Translate(' You will recieve a shipping confirmation as soon as your order is shipped.') . "\n";
            $strText .= Quasi::Translate(' If you have any questions please email support at ') ;
            $strText .= STORE_EMAIL_ADDRESS . "\n Thank You, \n";
            $strText .=  STORE_NAME . ' ' . Quasi::Translate('Customer Service') . "\n";

            $this->SendEmail($strText, STORE_NAME . ' Payment Confirmation - Order #' . $this->Id );
        }
        /**
        *Send a shipping confirmation notification to the customer
        */
        public function SendShippingNotice()
        {
            $objPerson = Person::Load($this->Account->PersonId);

            $strText = Quasi::Translate('Dear') . ' ' . $objPerson->FullName . ",\n\n";
            
            $strText .= Quasi::Translate('Your order has been shipped!') . "\n\n";
            $strText .= '   --------------------------------------------------' . "\n";
            $strText .= $this->formatEmailOrderShippingMethod();
            $strText .= '   --------------------------------------------------' . "\n";
            $strText .= $this->formatEmailOrderShippingAddress();
            $strText .= '   --------------------------------------------------' . "\n";            
            $strText .= Quasi::Translate(' Thank You for placing an order with us!') . "\n";
            $strText .= Quasi::Translate(' If you have any questions please email support at ') ;
            $strText .= STORE_EMAIL_ADDRESS . "\n Thank You, \n";
            $strText .=  STORE_NAME . ' ' . Quasi::Translate('Customer Service') . "\n";

            $this->SendEmail($strText, STORE_NAME . ' Shipping Confirmation - Order #' . $this->Id );
        }
        /**
        *Send a local pickup confirmation notification to the customer
        */
        public function SendLocalPickupNotice()
        {
            $objPerson = Person::Load($this->Account->PersonId);

            $strText = Quasi::Translate('Dear') . ' ' . $objPerson->FullName . ",\n\n";
            
            $strText .= Quasi::Translate(' Your order is ready!') . "\n\n";
            $strText .= Quasi::Translate(' You can pickup your order during regular business hours.') . "\n";
            $strText .= Quasi::Translate(' Our address is: ') . "\n\n";
            $strText .= STORE_ADDRESS1 . "\n";
            $strText .= STORE_ADDRESS2 . "\n";
            $strText .= STORE_CITY . "\n";
            $strText .= STORE_STATE . "\n";
            $strText .= STORE_POSTAL_CODE . "\n";
            $strText .= '   Phone: ' . STORE_PHONE  . "\n";
            $strText .= '   --------------------------------------------------' . "\n";            
            $strText .= Quasi::Translate(' Thank You for placing an order with us!') . "\n";
            $strText .= Quasi::Translate(' If you have any questions please email support at ') ;
            $strText .= STORE_EMAIL_ADDRESS . "\n Thank You, \n";
            $strText .=  STORE_NAME . ' ' . Quasi::Translate('Customer Service') . "\n";

            $this->SendEmail($strText, STORE_NAME . ' Local Pickup Confirmation - Order #' . $this->Id );
        }
        /**
        *Send a cancelation confirmation notification to the customer
        *@todo - make cancellation email notice ..??
        */
        public function SendCancellationNotice()
        {
        }
        /**
        *Send a problem notification to the customer
        */
        public function SendProblemNotice()
        {
            $objPerson = Person::Load($this->Account->PersonId);

            $strText = Quasi::Translate('Dear') . ' ' . $objPerson->FullName . ",\n\n";
            
            $strText .= Quasi::Translate(' We have encountered a problem processing your order!') . "\n\n";
            
            $strText .= Quasi::Translate(' Thank You for placing an order with us - we have run into a '
                                                         . 'problem that requires your attention.') . "\n";
            $strText .= Quasi::Translate(' Please reply to this or email support at ') ;
            $strText .= STORE_EMAIL_ADDRESS . " for further information with the Order number ("
                                . $this->Id . ") in the Subject line of the email. \nThank You, \n";
            $strText .=  STORE_NAME . ' ' . Quasi::Translate('Customer Service');

            $this->SendEmail($strText, STORE_NAME . ' Problem Alert - Order #' . $this->Id );
        }
        /**
        *Send an email message to the customer for this order.
        *@param string strEmailBody - the text body of the email
        *@param string strSubject - the subject line
        */
        public function SendEmail($strEmailBody, $strSubject)
        {
            $objPerson = Person::Load($this->Account->PersonId);
            $objEmail = new QEmailMessage();
            $objEmail->Subject = $strSubject;
            $objEmail->From = STORE_NAME . ' <' . STORE_EMAIL_ADDRESS . '>';
            $objEmail->To = $objPerson->FullName . ' <' . $objPerson->EmailAddress . '>';
            $objEmail->Body = $strEmailBody;
             
            QEmailServer::Send($objEmail);            
        }
        /**
        *Print a shipping label for this order.
        */
        public function CreateShippingLabel()
        {
            if('PickUp' == $this->ShippingMethod->Carrier)
                return null;
            
//            $this->ShippingMethod->TestMode = true;
                
            $this->ShippingMethod->Init($this);
            $objImage = $this->objShippingMethod->GetShippingLabel();
            if($this->objShippingMethod->HasErrors)
                die($this->objShippingMethod->Errors);
            return $objImage;
        }
        /**
        * This function adds an OrderItem to the NewOrderItems array - subsequently it will be
        * associated with this order and saved with the other new items. The order_id is not
        * checked and may be null, it will be set on saving. If another OrderItem for the Product
        * already exists in the array, the quantity of the new item will be added to the existing item.
        *
        *@param OrderItem objNewOrderItem - a new order item for the new order.
        */
        public function AddNewOrderItem(OrderItem $objNewOrderItem)
        {
            if(is_null( $objNewOrderItem->ProductId))
                throw new QCallerException('Cannot add an OrderItem without a ProductId!');
            $blnItemExists = false;
            if($objNewOrderItem->Quantity > 0)
            {
                if(is_array($this->aryNewOrderItems))
                {
                    foreach( $this->aryNewOrderItems as $objItem )
                    {
                        if( $objItem->ProductId == $objNewOrderItem->ProductId )
                        {
                            $objItem->Quantity += $objNewOrderItem->Quantity;
                            $blnItemExists = true;
                            break;
                        }
                    }
                }               
                if( ! $blnItemExists )
                    $this->aryNewOrderItems[] = $objNewOrderItem;
            }
        }
        /**
        * This function saves the array of new order items to the database making of them "real"
        * order items.
        */
        public function SaveNewOrderItems()
        {
            if( is_null($this->Id) )
                throw new QCallerException('Cannot save OrderItems for an unsaved Order!');
                
            if( is_array($this->aryNewOrderItems) && ! empty( $this->aryNewOrderItems ) )
            {
                foreach( $this->aryNewOrderItems as $objOrderItem )
                {
                    $objOrderItem->OrderId = $this->Id;
                    $objOrderItem->StatusId = OrderItemStatusType::Ordered;
                    $objOrderItem->Save();
                }
            }
        }
        /**
        * This function returns the array of  new order items. It is provided to avoid buggy PHP get magic
        * when returning arrays of objects.
        * Note that the OrderItems may not have order_id set as they are new items for a new order
        *@return array of OrderItems
        */
        public function GetNewOrderItemsArray()
        {
            if( is_array($this->aryNewOrderItems))
                return $this->aryNewOrderItems;
            return array();
        }
        
        /**
        * This fuction initializes the Shipping address fields
        *@param Address objAddress - address to use for initialization
        */
        public function SetShippingAddress(Address $objAddress)
        {
                 $this->ShippingNamePrefix = $objAddress->Person->NamePrefix;
                 $this->ShippingFirstName = $objAddress->Person->FirstName;
                 $this->ShippingMiddleName = $objAddress->Person->MiddleName;
                 $this->ShippingLastName = $objAddress->Person->LastName;
                 $this->ShippingNameSuffix = $objAddress->Person->NameSuffix;
                 $this->ShippingCompany = $objAddress->Person->CompanyName;
                 $this->ShippingStreet1 = $objAddress->Street1;
                 $this->ShippingStreet2 = $objAddress->Street2;
                 $this->ShippingSuburb = $objAddress->Suburb;
                 $this->ShippingCounty = $objAddress->County;
                 $this->ShippingCity = $objAddress->City;
                 $this->ShippingZoneId = $objAddress->ZoneId;
                 $this->ShippingCountryId = $objAddress->CountryId;
                 $this->ShippingPostalCode = $objAddress->PostalCode;
                 
                 $this->ShippingAddressId = $objAddress->Id;
                 $intStoreCountryId = CountryType::GetId(STORE_COUNTRY);
                 if($objAddress->CountryId != $intStoreCountryId)
                    $this->blnIsInternational = true;
                 else
                    $this->blnIsInternational = false;
                    
        }
        /**
        * This fuction initializes the Billing address fields
        *@param Address objAddress - address to use for initialization
        */
        public function SetBillingAddress(Address $objAddress)
        {
                 $this->BillingNamePrefix = $objAddress->Person->NamePrefix;
                 $this->BillingFirstName = $objAddress->Person->FirstName;
                 $this->BillingMiddleName = $objAddress->Person->MiddleName;
                 $this->BillingLastName = $objAddress->Person->LastName;
                 $this->BillingNameSuffix = $objAddress->Person->NameSuffix;
                 $this->BillingCompany = $objAddress->Person->CompanyName;
                 $this->BillingStreet1 = $objAddress->Street1;
                 $this->BillingStreet2 = $objAddress->Street2;
                 $this->BillingSuburb = $objAddress->Suburb;
                 $this->BillingCounty = $objAddress->County;
                 $this->BillingCity = $objAddress->City;
                 $this->BillingZoneId = $objAddress->ZoneId;
                 $this->BillingCountryId = $objAddress->CountryId;
                 $this->BillingPostalCode = $objAddress->PostalCode;
                 
                 $this->BillingAddressId = $objAddress->Id;
        }
        /**
        * This fuction returns the Shipping address fields as an Address object - it attempts
        * to return an existing address by searching for a match on fields, if that fails it
        * calls createShippingAddress and returns a new Address object initilized with the
        * values in the order.
        *@return Address objAddress - address containing Shipping address fields
        */
        public function GetShippingAddress()
        {
            if($this->AccountId)
                $intAccountId = $this->AccountId;
            elseif(IndexPage::$objAccount)
                $intAccountId = IndexPage::$objAccount->Id;
            else
                $intAccountId = null;

            $aryPersonConditions = array();
            if($intAccountId)
                $aryPersonConditions[] = QQ::Equal(QQN::Person()->Account->Id, $intAccountId);
            $aryPersonConditions[] = QQ::Equal(QQN::Person()->FirstName, $this->ShippingFirstName);
            $aryPersonConditions[] = QQ::Equal(QQN::Person()->LastName, $this->ShippingLastName);
            $objPerson = Person::QuerySingle( QQ::AndCondition($aryPersonConditions) );

          //for imported orders the person may not yet exist so ..
            if(!$objPerson instanceof Person)
            {
                $objPerson = new Person();
                $objPerson->FirstName = $this->ShippingFirstName;
                $objPerson->LastName = $this->ShippingLastName;
                $objPerson->EmailAddress = $this->Account->Person->EmailAddress;
                $objPerson->OwnerPersonId = $this->Account->Person->Id;
                $objPerson->IsVirtual = true;
                $objPerson->Save();                
            }
            $aryConditions = array();
//            $aryConditions[] = QQ::Equal(QQN::Address()->PersonId, $this->Account->PersonId );            
            $aryConditions[] = QQ::Equal(QQN::Address()->PersonId, $objPerson->Id );

            if(!empty($this->ShippingStreet1))
                $aryConditions[] =  QQ::Equal(QQN::Address()->Street1, $this->ShippingStreet1);
            if(!empty($this->ShippingStreet2))
                $aryConditions[] =  QQ::Equal(QQN::Address()->Street2, $this->ShippingStreet2);
            if(!empty($this->ShippingSuburb))
                $aryConditions[] =  QQ::Equal(QQN::Address()->Suburb, $this->ShippingSuburb);
            $aryConditions[] =  QQ::Equal(QQN::Address()->City, $this->ShippingCity);
            $aryConditions[] =  QQ::Equal(QQN::Address()->ZoneId, $this->ShippingZoneId);
            $aryConditions[] =  QQ::Equal(QQN::Address()->CountryId, $this->ShippingCountryId);

            $objAddress = Address::QuerySingle( QQ::AndCondition( $aryConditions ));

            if($objAddress instanceof Address)
                return $objAddress;
            return $this->createShippingAddress();
            
        }
        /**
        * This fuction returns the Billing address fields as an Address object - it attempts
        * to return an existing address by searching for a match on fields, if that fails it
        * calls createBillingAddress and returns a new Address object initilized with the
        * values in the order.
        *@return Address objAddress - address containing Billing address fields
        */
        public function GetBillingAddress()
        {
            if($this->AccountId)
                $intAccountId = $this->AccountId;
            elseif(IndexPage::$objAccount)
                $intAccountId = IndexPage::$objAccount->Id;
            else
                $intAccountId = null;

            $aryPersonConditions = array();
            if($intAccountId)
                $aryPersonConditions[] = QQ::Equal(QQN::Person()->Account->Id, $intAccountId);
            $aryPersonConditions[] = QQ::Equal(QQN::Person()->FirstName, $this->ShippingFirstName);
            $aryPersonConditions[] = QQ::Equal(QQN::Person()->LastName, $this->ShippingLastName);
            $objPerson = Person::QuerySingle( QQ::AndCondition($aryPersonConditions) );
                        
          //for imported orders the person may not yet exist so ..
            if(!$objPerson instanceof Person)
            {
                $objPerson = new Person();
                $objPerson->FirstName = $this->ShippingFirstName;
                $objPerson->LastName = $this->ShippingLastName;
                $objPerson->EmailAddress = $this->Account->Person->EmailAddress;
                $objPerson->OwnerPersonId = $this->Account->Person->Id;
                $objPerson->IsVirtual = true;
                $objPerson->Save();                
            }
            
            $aryConditions = array();
            
//            $aryConditions[] = QQ::Equal(QQN::Address()->PersonId, $this->Account->PersonId );            
            $aryConditions[] = QQ::Equal(QQN::Address()->PersonId, $objPerson->Id );
            
            if(!empty($this->BillingStreet1))
                $aryConditions[] =  QQ::Equal(QQN::Address()->Street1, $this->BillingStreet1);
            if(!empty($this->BillingStreet2))
                $aryConditions[] =  QQ::Equal(QQN::Address()->Street2, $this->BillingStreet2);
            if(!empty($this->BillingSuburb))
                $aryConditions[] =  QQ::Equal(QQN::Address()->Suburb, $this->BillingSuburb);
            $aryConditions[] =  QQ::Equal(QQN::Address()->City, $this->BillingCity);
            $aryConditions[] =  QQ::Equal(QQN::Address()->ZoneId, $this->BillingZoneId);
            $aryConditions[] =  QQ::Equal(QQN::Address()->CountryId, $this->BillingCountryId);

            $objAddress = Address::QuerySingle( QQ::AndCondition( $aryConditions ));

            if($objAddress instanceof Address)
                return $objAddress;
            return $this->createBillingAddress();
            
        }
        /**
         * Insert this order, including the id, and timestamps - Save() autoincrements Id, this function
         * is for overriding that behaviour (eg. for imports).
         * @todo - merge with Save()?
         */
        public function Insert()
        {
            $objDatabase = Order::GetDatabase();

            $strQuery = 'INSERT INTO `order` (
                    `id`,
                    `account_id`,
                    `creation_date`,
                    `last_modification_date`,
                    `completion_date`,
                    `shipping_cost`,
                    `product_total_cost`,
                    `shipping_charged`,
                    `handling_charged`,
                    `tax`,
                    `product_total_charged`,
                    `shipping_name_prefix`,
                    `shipping_first_name`,
                    `shipping_middle_name`,
                    `shipping_last_name`,
                    `shipping_name_suffix`,
                    `shipping_company`,
                    `shipping_street1`,
                    `shipping_street2`,
                    `shipping_suburb`,
                    `shipping_county`,
                    `shipping_city`,
                    `shipping_zone_id`,
                    `shipping_country_id`,
                    `shipping_postal_code`,
                    `billing_name_prefix`,
                    `billing_first_name`,
                    `billing_middle_name`,
                    `billing_last_name`,
                    `billing_name_suffix`,
                    `billing_company`,
                    `billing_street1`,
                    `billing_street2`,
                    `billing_suburb`,
                    `billing_county`,
                    `billing_city`,
                    `billing_zone_id`,
                    `billing_country_id`,
                    `billing_postal_code`,
                    `notes`,
                    `shipping_method_id`,
                    `payment_method_id`,
                    `status_id`,
                    `type_id`
                ) VALUES (
                    ' . $objDatabase->SqlVariable($this->intId) . ', 
                    ' . $objDatabase->SqlVariable($this->intAccountId) . ',
                    ' . $objDatabase->SqlVariable($this->strCreationDate) . ',
                    ' . $objDatabase->SqlVariable($this->strLastModificationDate) . ',
                    ' . $objDatabase->SqlVariable($this->dttCompletionDate) . ',
                    ' . $objDatabase->SqlVariable($this->fltShippingCost) . ',
                    ' . $objDatabase->SqlVariable($this->fltProductTotalCost) . ',
                    ' . $objDatabase->SqlVariable($this->fltShippingCharged) . ',
                    ' . $objDatabase->SqlVariable($this->fltHandlingCharged) . ',
                    ' . $objDatabase->SqlVariable($this->fltTax) . ',
                    ' . $objDatabase->SqlVariable($this->fltProductTotalCharged) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingNamePrefix) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingFirstName) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingMiddleName) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingLastName) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingNameSuffix) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingCompany) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingStreet1) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingStreet2) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingSuburb) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingCounty) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingCity) . ',
                    ' . $objDatabase->SqlVariable($this->intShippingZoneId) . ',
                    ' . $objDatabase->SqlVariable($this->intShippingCountryId) . ',
                    ' . $objDatabase->SqlVariable($this->strShippingPostalCode) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingNamePrefix) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingFirstName) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingMiddleName) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingLastName) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingNameSuffix) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingCompany) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingStreet1) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingStreet2) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingSuburb) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingCounty) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingCity) . ',
                    ' . $objDatabase->SqlVariable($this->intBillingZoneId) . ',
                    ' . $objDatabase->SqlVariable($this->intBillingCountryId) . ',
                    ' . $objDatabase->SqlVariable($this->strBillingPostalCode) . ',
                    ' . $objDatabase->SqlVariable($this->strNotes) . ',
                    ' . $objDatabase->SqlVariable($this->intShippingMethodId) . ',
                    ' . $objDatabase->SqlVariable($this->intPaymentMethodId) . ',
                    ' . $objDatabase->SqlVariable($this->intStatusId) . ', 
                    ' . $objDatabase->SqlVariable($this->intTypeId) . ' )';
                    
            try{
                $objDatabase->NonQuery($strQuery);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
            $this->__blnRestored = true;
        }
        /**
         * Save this Order - overrides the generated class implementation to be able to update last_modification_date
         *
         * @param bool $blnForceInsert
         * @param bool $blnForceUpdate
         * @return integer - the Id of this Order
         */
        public function Save($blnForceInsert = false, $blnForceUpdate = false)
        {
            // Get the Database Object for this Class
            $objDatabase = Order::GetDatabase();
            if(! $this->dttCompletionDate instanceof QDateTime)
                $this->dttCompletionDate = new QDateTime();
            $mixToReturn = null;
            $strLastModificationDate = date("Y-m-d H:i:s");

            try {
                if ((!$this->__blnRestored) || ($blnForceInsert))
                {
                    // Perform an INSERT query
                    $strQuery = 'INSERT INTO `order` (
                            `account_id`,
                            `last_modification_date`,
                            `completion_date`,
                            `shipping_cost`,
                            `product_total_cost`,
                            `shipping_charged`,
                            `handling_charged`,
                            `tax`,
                            `product_total_charged`,
                            `shipping_name_prefix`,
                            `shipping_first_name`,
                            `shipping_middle_name`,
                            `shipping_last_name`,
                            `shipping_name_suffix`,
                            `shipping_company`,
                            `shipping_street1`,
                            `shipping_street2`,
                            `shipping_suburb`,
                            `shipping_county`,
                            `shipping_city`,
                            `shipping_zone_id`,
                            `shipping_country_id`,
                            `shipping_postal_code`,
                            `billing_name_prefix`,
                            `billing_first_name`,
                            `billing_middle_name`,
                            `billing_last_name`,
                            `billing_name_suffix`,
                            `billing_company`,
                            `billing_street1`,
                            `billing_street2`,
                            `billing_suburb`,
                            `billing_county`,
                            `billing_city`,
                            `billing_zone_id`,
                            `billing_country_id`,
                            `billing_postal_code`,
                            `notes`,
                            `shipping_method_id`,
                            `payment_method_id`,
                            `status_id`,
                            `type_id`
                        ) VALUES (
                            ' . $objDatabase->SqlVariable($this->intAccountId) . ',
                            ' . $objDatabase->SqlVariable($strLastModificationDate) . ',
                            ' . $objDatabase->SqlVariable($this->dttCompletionDate) . ',
                            ' . $objDatabase->SqlVariable($this->fltShippingCost) . ',
                            ' . $objDatabase->SqlVariable($this->fltProductTotalCost) . ',
                            ' . $objDatabase->SqlVariable($this->fltShippingCharged) . ',
                            ' . $objDatabase->SqlVariable($this->fltHandlingCharged) . ',
                            ' . $objDatabase->SqlVariable($this->fltTax) . ',
                            ' . $objDatabase->SqlVariable($this->fltProductTotalCharged) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingNamePrefix) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingFirstName) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingMiddleName) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingLastName) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingNameSuffix) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingCompany) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingStreet1) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingStreet2) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingSuburb) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingCounty) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingCity) . ',
                            ' . $objDatabase->SqlVariable($this->intShippingZoneId) . ',
                            ' . $objDatabase->SqlVariable($this->intShippingCountryId) . ',
                            ' . $objDatabase->SqlVariable($this->strShippingPostalCode) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingNamePrefix) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingFirstName) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingMiddleName) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingLastName) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingNameSuffix) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingCompany) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingStreet1) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingStreet2) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingSuburb) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingCounty) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingCity) . ',
                            ' . $objDatabase->SqlVariable($this->intBillingZoneId) . ',
                            ' . $objDatabase->SqlVariable($this->intBillingCountryId) . ',
                            ' . $objDatabase->SqlVariable($this->strBillingPostalCode) . ',
                            ' . $objDatabase->SqlVariable($this->strNotes) . ',
                            ' . $objDatabase->SqlVariable($this->intShippingMethodId) . ',
                            ' . $objDatabase->SqlVariable($this->intPaymentMethodId) . ',
                            ' . $objDatabase->SqlVariable($this->intStatusId) . ',
                            ' . $objDatabase->SqlVariable($this->intTypeId) . '
                        )
                    ';
                    $objDatabase->NonQuery($strQuery);
                    // Update Identity column and return its value
                    $mixToReturn = $this->intId = $objDatabase->InsertId('order', 'id');
                } else {
                    // Perform an UPDATE query

                    if (!$blnForceUpdate)
                    {
                        // Perform the Optimistic Locking check
                        $objResult = $objDatabase->Query('
                            SELECT
                                `last_modification_date`
                            FROM
                                `order`
                            WHERE
                                `id` = ' . $objDatabase->SqlVariable($this->intId) . '
                        ');
                        
                        $objRow = $objResult->FetchArray();
                        if ($objRow[0] != $this->strLastModificationDate)
                            throw new QOptimisticLockingException('Order');
                    }

                    // Perform the UPDATE query
                    $objDatabase->NonQuery('
                        UPDATE
                            `order`
                        SET
                            `account_id` = ' . $objDatabase->SqlVariable($this->intAccountId) . ',
                            `last_modification_date` = ' . $objDatabase->SqlVariable($strLastModificationDate) . ',
                            `completion_date` = ' . $objDatabase->SqlVariable($this->dttCompletionDate) . ',
                            `shipping_cost` = ' . $objDatabase->SqlVariable($this->fltShippingCost) . ',
                            `product_total_cost` = ' . $objDatabase->SqlVariable($this->fltProductTotalCost) . ',
                            `shipping_charged` = ' . $objDatabase->SqlVariable($this->fltShippingCharged) . ',
                            `handling_charged` = ' . $objDatabase->SqlVariable($this->fltHandlingCharged) . ',
                            `tax` = ' . $objDatabase->SqlVariable($this->fltTax) . ',
                            `product_total_charged` = ' . $objDatabase->SqlVariable($this->fltProductTotalCharged) . ',
                            `shipping_name_prefix` = ' . $objDatabase->SqlVariable($this->strShippingNamePrefix) . ',
                            `shipping_first_name` = ' . $objDatabase->SqlVariable($this->strShippingFirstName) . ',
                            `shipping_middle_name` = ' . $objDatabase->SqlVariable($this->strShippingMiddleName) . ',
                            `shipping_last_name` = ' . $objDatabase->SqlVariable($this->strShippingLastName) . ',
                            `shipping_name_suffix` = ' . $objDatabase->SqlVariable($this->strShippingNameSuffix) . ',
                            `shipping_company` = ' . $objDatabase->SqlVariable($this->strShippingCompany) . ',
                            `shipping_street1` = ' . $objDatabase->SqlVariable($this->strShippingStreet1) . ',
                            `shipping_street2` = ' . $objDatabase->SqlVariable($this->strShippingStreet2) . ',
                            `shipping_suburb` = ' . $objDatabase->SqlVariable($this->strShippingSuburb) . ',
                            `shipping_county` = ' . $objDatabase->SqlVariable($this->strShippingCounty) . ',
                            `shipping_city` = ' . $objDatabase->SqlVariable($this->strShippingCity) . ',
                            `shipping_zone_id` = ' . $objDatabase->SqlVariable($this->intShippingZoneId) . ',
                            `shipping_country_id` = ' . $objDatabase->SqlVariable($this->intShippingCountryId) . ',
                            `shipping_postal_code` = ' . $objDatabase->SqlVariable($this->strShippingPostalCode) . ',
                            `billing_name_prefix` = ' . $objDatabase->SqlVariable($this->strBillingNamePrefix) . ',
                            `billing_first_name` = ' . $objDatabase->SqlVariable($this->strBillingFirstName) . ',
                            `billing_middle_name` = ' . $objDatabase->SqlVariable($this->strBillingMiddleName) . ',
                            `billing_last_name` = ' . $objDatabase->SqlVariable($this->strBillingLastName) . ',
                            `billing_name_suffix` = ' . $objDatabase->SqlVariable($this->strBillingNameSuffix) . ',
                            `billing_company` = ' . $objDatabase->SqlVariable($this->strBillingCompany) . ',
                            `billing_street1` = ' . $objDatabase->SqlVariable($this->strBillingStreet1) . ',
                            `billing_street2` = ' . $objDatabase->SqlVariable($this->strBillingStreet2) . ',
                            `billing_suburb` = ' . $objDatabase->SqlVariable($this->strBillingSuburb) . ',
                            `billing_county` = ' . $objDatabase->SqlVariable($this->strBillingCounty) . ',
                            `billing_city` = ' . $objDatabase->SqlVariable($this->strBillingCity) . ',
                            `billing_zone_id` = ' . $objDatabase->SqlVariable($this->intBillingZoneId) . ',
                            `billing_country_id` = ' . $objDatabase->SqlVariable($this->intBillingCountryId) . ',
                            `billing_postal_code` = ' . $objDatabase->SqlVariable($this->strBillingPostalCode) . ',
                            `notes` = ' . $objDatabase->SqlVariable($this->strNotes) . ',
                            `shipping_method_id` = ' . $objDatabase->SqlVariable($this->intShippingMethodId) . ',
                            `payment_method_id` = ' . $objDatabase->SqlVariable($this->intPaymentMethodId) . ',
                            `status_id` = ' . $objDatabase->SqlVariable($this->intStatusId) . ',
                            `type_id` = ' . $objDatabase->SqlVariable($this->intTypeId) . '
                        WHERE
                            `id` = ' . $objDatabase->SqlVariable($this->intId) . '
                    ');
                }

            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }

            $this->__blnRestored = true;
            
            // Update Local Timestamp
            $objResult = $objDatabase->Query('
                SELECT
                    `creation_date`
                FROM
                    `order`
                WHERE
                    `id` = ' . $objDatabase->SqlVariable($this->intId) . '
            ');
                        
            $objRow = $objResult->FetchArray();
            $this->strCreationDate = $objRow[0];

            $this->strLastModificationDate = $strLastModificationDate;

            return $mixToReturn;
        }
        /**
        * Utility function to format the list of Order information in a customer email notification
        * @return string - a string containing the formatted information
        */
        protected function formatEmailOrderItemsInfo()
        {            
            $strToReturn = Quasi::Translate(' The following is your order information') . ":\n\n";
            $strToReturn .= '   --------------------------------------------------'  . "\n";
            $strToReturn .= Quasi::Translate('Order Number') . ': ' . $this->Id  . "\n";
            $strToReturn .= Quasi::Translate('Detailed Invoice') . ': http://' . Quasi::$ServerName . __QUASI_SUBDIRECTORY__
                                                                                     . '/index.php/AccountHome/Order/' . $this->Id  . "\n";
            $strToReturn .= Quasi::Translate('Date Ordered') . ': ' . $this->CreationDate . "\n" ;

            $strToReturn .= '   --------------------------------------------------' . "\n";
            $strToReturn .= Quasi::Translate('Products on Order') . ":\n\n" ;
            foreach( OrderItem::LoadArrayByOrderId($this->Id) as $objOrderItem )
            {
                $objProduct = Product::Load($objOrderItem->ProductId);
                $strToReturn .= $objOrderItem->Quantity . ' ' . $objProduct->Name . ': ' . $objProduct->Model 
                              . ' [' . number_format($objProduct->Height, 2  ) . '" x ' . number_format($objProduct->Width, 2) . '" ]'
                              . ' at ' . money_format('%n', $objProduct->RetailPrice )
                              . '/ea.  = ' . money_format('%n', $objProduct->RetailPrice * $objOrderItem->Quantity ) . "\n";
            
            }
            $strToReturn .= "\n" . Quasi::Translate('Subtotal') . ': ' . money_format('%n', $this->ProductTotalCharged);
            $strToReturn .= "\n" . Quasi::Translate('Shipping') . ': ' . money_format('%n', $this->ShippingCharged);
            $strToReturn .= "\n" . Quasi::Translate('Handling') . ': ' . money_format('%n', $this->HandlingCharged);
            $strToReturn .= "\n" . Quasi::Translate('Total') . ': ' . money_format('%n', ($this->HandlingCharged
                                                                                           + $this->ShippingCharged
                                                                                           + $this->ProductTotalCharged) ) . "\n";
            return $strToReturn;
        }
        /**
        * Utility function to format the Payment method in a customer email notification
        * @return string - a string containing the formatted information
        */
        protected function formatEmailOrderPaymentMethod()
        {
            $objPaymentMethod = PaymentMethod::Load($this->PaymentMethodId);
            $strToReturn = Quasi::Translate('Payment Method') . ": \n";
            $strToReturn .= PaymentType::ToString($objPaymentMethod->PaymentTypeId) . ' via ' . $objPaymentMethod->ServiceProvider . "\n";
            return $strToReturn;
        }
        /**
        * Utility function to format the Shipping method in a customer email notification
        * @return string - a string containing the formatted information
        */
        protected function formatEmailOrderShippingMethod()
        {
            $objShippingMethod = ShippingMethod::Load($this->ShippingMethodId);
            $strToReturn = Quasi::Translate('Shipping Method') . ": \n";
            $strToReturn .= $objShippingMethod->Title . ' ' . $objShippingMethod->ServiceType . "\n";
            $strToReturn .= Quasi::Translate('Estimated Transit time: ') . $objShippingMethod->TransitTime . "\n";            
            return $strToReturn;
        }
        /**
        * Utility function to format the Shipping Address information in a customer email notification
        * @return string - a string containing the formatted information
        */
        protected function formatEmailOrderShippingAddress()
        {
            $strToReturn = Quasi::Translate('Delivery Address:') . "\n\n";
            if('' != $this->ShippingCompany )
                $strToReturn .= $this->ShippingCompany . "\n";
            if('' != $this->ShippingNamePrefix )
                $strToReturn .= $this->ShippingNamePrefix . ' ';
            $strToReturn .= $this->ShippingFirstName . ' ';
            if('' != $this->ShippingMiddleName )
                $strToReturn .= $this->ShippingMiddleName . ' ';
            $strToReturn .= $this->ShippingLastName. ' ';
            if('' != $this->ShippingNameSuffix )
                $strToReturn .= $this->ShippingNameSuffix . ' ';
            $strToReturn .= "\n" . $this->ShippingStreet1 . "\n";
            if('' != $this->ShippingStreet2)
                $strToReturn .= $this->ShippingStreet2 . "\n";
            if('' != $this->ShippingSuburb)
                $strToReturn .= $this->ShippingSuburb . "\n";
            if('' != $this->ShippingCounty)
                $strToReturn .= $this->ShippingCounty . "\n";
            $strToReturn .= $this->ShippingCity . "\n";
            $strToReturn .= ZoneType::ToString($this->ShippingZoneId) . "\n";
            $strToReturn .= $this->ShippingPostalCode . "\n";
            $strToReturn .= CountryType::ToString($this->ShippingCountryId) . "\n";
            return $strToReturn;
        }
        /**
        * Utility function to format the Billing Address information in a customer email notification
        * @return string - a string containing the formatted information
        */
        protected function formatEmailOrderBillingAddress()
        {
            $strToReturn = Quasi::Translate('Billing Address:') . "\n\n";
            if('' != $this->BillingCompany )
                $strToReturn .= $this->BillingCompany . "\n";
            if('' != $this->BillingNamePrefix )
                $strToReturn .= $this->BillingNamePrefix . ' ';
            $strToReturn .= $this->BillingFirstName . ' ';
            if('' != $this->BillingMiddleName )
                $strToReturn .= $this->BillingMiddleName . ' ';
            $strToReturn .= $this->BillingLastName. ' ';
            if('' != $this->BillingNameSuffix )
                $strToReturn .= $this->BillingNameSuffix . ' ';
            $strToReturn .= "\n" . $this->BillingStreet1 . "\n";
            if('' != $this->BillingStreet2)
                $strToReturn .= $this->BillingStreet2 . "\n";
            if('' != $this->BillingSuburb)
                $strToReturn .= $this->BillingSuburb . "\n";
            if('' != $this->BillingCounty)
                $strToReturn .= $this->BillingCounty . "\n";
            $strToReturn .= $this->BillingCity . "\n";
            $strToReturn .= ZoneType::ToString($this->BillingZoneId) . "\n";
            $strToReturn .= $this->BillingPostalCode . "\n";
            $strToReturn .= CountryType::ToString($this->BillingCountryId) . "\n";
            return $strToReturn;
        }
        /**
        * This fuction returns the Shipping address fields as an Address object
        *@return Address objAddress - address containing Shipping address fields
        */
        protected function createShippingAddress()
        {            
            $objShippingAddress = new Address();
            $objShippingAddress->PersonId = $this->Account->PersonId;
            $objShippingAddress->Street1 = $this->ShippingStreet1;
            $objShippingAddress->Street2 = $this->ShippingStreet2;
            $objShippingAddress->Suburb = $this->ShippingSuburb;
            $objShippingAddress->County = $this->ShippingCounty;
            $objShippingAddress->City = $this->ShippingCity;
            $objShippingAddress->ZoneId = $this->ShippingZoneId;
            $objShippingAddress->CountryId = $this->ShippingCountryId;
            $objShippingAddress->PostalCode = $this->ShippingPostalCode;
            $objShippingAddress->TypeId = AddressType::Shipping;            
            $objShippingAddress->Save();
            return $objShippingAddress;
        }
        /**
        * This fuction returns the Billing address fields as an Address object
        *@return Address objAddress - address containing Billing address fields
        */
        protected function createBillingAddress()
        {            
            $objBillingAddress = new Address();
            $objBillingAddress->PersonId = $this->Account->PersonId;
            $objBillingAddress->Street1 = $this->BillingStreet1;
            $objBillingAddress->Street2 = $this->BillingStreet2;
            $objBillingAddress->Suburb = $this->BillingSuburb;
            $objBillingAddress->County = $this->BillingCounty;
            $objBillingAddress->City = $this->BillingCity;
            $objBillingAddress->ZoneId = $this->BillingZoneId;
            $objBillingAddress->CountryId = $this->BillingCountryId;
            $objBillingAddress->PostalCode = $this->BillingPostalCode;
            $objBillingAddress->TypeId = AddressType::Billing;
            $objBillingAddress->Save();
            return $objBillingAddress;
        }
       
        public function __get($strName)
        {
            switch ($strName) {
                case 'FullBillingName':
                    $strToReturn = '';
                    if('' != $this->BillingNamePrefix )
                        $strToReturn .= $this->BillingNamePrefix . ' ';
                    $strToReturn .= $this->BillingFirstName . ' ';
                    if('' != $this->BillingMiddleName )
                        $strToReturn .= $this->BillingMiddleName . ' ';
                    $strToReturn .= $this->BillingLastName. ' ';
                    if('' != $this->BillingNameSuffix )
                        $strToReturn .= $this->BillingNameSuffix . ' ';
                    return $strToReturn;
                case 'FullShippingName':
                    $strToReturn = '';
                    if('' != $this->ShippingNamePrefix )
                        $strToReturn .= $this->ShippingNamePrefix . ' ';
                    $strToReturn .= $this->ShippingFirstName . ' ';
                    if('' != $this->ShippingMiddleName )
                        $strToReturn .= $this->ShippingMiddleName . ' ';
                    $strToReturn .= $this->ShippingLastName. ' ';
                    if('' != $this->ShippingNameSuffix )
                        $strToReturn .= $this->ShippingNameSuffix . ' ';
                    return $strToReturn;
                case 'ShippingCountry':
                    return ($this->ShippingCountryId) ? CountryType::$NameArray[$this->ShippingCountryId] : null;
                case 'BillingCountry':
                    return ($this->BillingCountryId) ? CountryType::$NameArray[$this->BillingCountryId] : null;
                case 'ShippingZone':
                case 'ShippingState':
                    return ($this->ShippingZoneId) ? ZoneType::$NameArray[$this->ShippingZoneId] : null;
                case 'BillingZone':
                case 'BillingState':
                    return ($this->BillingZoneId) ? ZoneType::$NameArray[$this->BillingZoneId] : null;
                case 'TotalPounds':
                    return $this->intTotalPounds;
                case 'TotalOunces':
                    return $this->fltTotalOunces;
                case 'XAxisSize':
                    return $this->fltXAxisSize;
                case 'YAxisSize':
                    return $this->fltYAxisSize;
                case 'ZAxisSize':
                    return $this->fltZAxisSize;
                case 'IsInternational':
                    if(isset($this->blnIsInternational))
                        return $this->blnIsInternational;
                    else
                        return ($this->ShippingCountryId != CountryType::GetId(STORE_COUNTRY) );
                case 'ShippingAddressId':
                    return $this->intShippingAddressId;
                case 'BillingAddressId':
                    return $this->intBillingAddressId;
                case 'ExtraDocumentImages':
                        return $this->ShippingMethod->ExtraDocumentImages;
                case 'CustomsFormImages':
                        return $this->ShippingMethod->CustomsFormImages;

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
                case 'Restored':
                    try {
                        return ($this->__blnRestored = QType::Cast($mixValue, QType::Boolean));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'Id':
                    try {
                        return ($this->intId = QType::Cast($mixValue, QType::Integer));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'StatusId':
                    try {
                        $intStatusIdCheck = QType::Cast($mixValue, QType::Integer);
                        $blnStatusChanged = ($intStatusIdCheck != $this->intStatusId);
                        $this->intStatusId = QType::Cast($mixValue, QType::Integer);
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                    if($this->Id)
                    {
                        if($blnStatusChanged)
                        {
                            $objOrderStatusHistory = new OrderStatusHistory();
                            $objOrderStatusHistory->OrderId = $this->Id;
                            $objOrderStatusHistory->StatusId = $this->intStatusId;
                            $objOrderStatusHistory->Save();
                            switch($mixValue)
                            {
                                ///set completion date when shipped:
                                case OrderStatusType::Shipped:
                                    $this->dttCompletionDate = QDateTime::Now();
                                break;
                                //add other cases as needed ..                        
                            }
                        }
                    }
                    return $this->intStatusId;
                case 'CreationDate':
                    try {
                        return ($this->strCreationDate = QType::Cast($mixValue, QType::String));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'LastModificationDate':
                    try {
                        return ($this->strLastModificationDate = QType::Cast($mixValue, QType::String));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'CompletionDate':
                    if(is_string($mixValue))
                        try {
                            return($this->dttCompletionDate = new QDateTime($mixValue));
                        } catch (QCallerException $objExc) {
                            $objExc->IncrementOffset();
                            throw $objExc;
                        }
                    else
                        try {
                            return ($this->dttCompletionDate = QType::Cast($mixValue, 'QDateTime'));
                        } catch (QInvalidCastException $objExc) {
                            $objExc->IncrementOffset();
                            throw $objExc;
                        }
/*                    try {
                        return ($this->strCompletionDate = QType::Cast($mixValue, QType::String));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }*/
                case 'TotalPounds':
                    try {
                        return ($this->intTotalPounds = QType::Cast($mixValue, QType::Integer));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'TotalOunces':
                    try {
                        return ($this->fltTotalOunces = QType::Cast($mixValue, QType::Float));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'XAxisSize':
                    try {
                        return ($this->fltXAxisSize = QType::Cast($mixValue, QType::Float));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'YAxisSize':
                    try {
                        return ($this->fltYAxisSize = QType::Cast($mixValue, QType::Float));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'ZAxisSize':
                    try {
                        return ($this->fltZAxisSize = QType::Cast($mixValue, QType::Float));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'IsInternational':
                    try {
                        return ($this->blnIsInternational = QType::Cast($mixValue, QType::Boolean));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'ShippingAddressId':
                    try {
                        return ($this->intShippingAddressId = QType::Cast($mixValue, QType::Integer));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'BillingAddressId':
                    try {
                        return ($this->intBillingAddressId = QType::Cast($mixValue, QType::Integer));
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
        
        public function Dump()
        {
            $strToReturn = '';
            $strToReturn .= ' | intId => ' . $this->intId;
            $strToReturn .= ' | strCreationDate => ' . $this->strCreationDate;
            $strToReturn .= ' | strLastModificationDate => ' . $this->strLastModificationDate;
            $strToReturn .= ' | dttCompletionDate => ' . $this->dttCompletionDate;
            $strToReturn .= ' | fltShippingCost => ' . $this->fltShippingCost;
            $strToReturn .= ' | fltProductTotalCost => ' . $this->fltProductTotalCost;
            $strToReturn .= ' | fltShippingCharged => ' . $this->fltShippingCharged;
            $strToReturn .= ' | fltHandlingCharged => ' . $this->fltHandlingCharged;
            $strToReturn .= ' | fltTax => ' . $this->fltTax;
            $strToReturn .= ' | fltProductTotalCharged => ' . $this->fltProductTotalCharged;
            $strToReturn .= ' | strShippingNamePrefix => ' . $this->strShippingNamePrefix;
            $strToReturn .= ' | strShippingFirstName => ' . $this->strShippingFirstName;
            $strToReturn .= ' | strShippingMiddleName => ' . $this->strShippingMiddleName;
            $strToReturn .= ' | strShippingLastName => ' . $this->strShippingLastName;
            $strToReturn .= ' | strShippingNameSuffix => ' . $this->strShippingNameSuffix;
            $strToReturn .= ' | strShippingCompany => ' . $this->strShippingCompany;
            $strToReturn .= ' | strShippingStreet1 => ' . $this->strShippingStreet1;
            $strToReturn .= ' | strShippingStreet2 => ' . $this->strShippingStreet2;
            $strToReturn .= ' | strShippingSuburb => ' . $this->strShippingSuburb;
            $strToReturn .= ' | strShippingCounty => ' . $this->strShippingCounty;
            $strToReturn .= ' | strShippingCity => ' . $this->strShippingCity;
            $strToReturn .= ' | intShippingZoneId => ' . $this->intShippingZoneId;
            $strToReturn .= ' | intShippingCountryId => ' . $this->intShippingCountryId;
            $strToReturn .= ' | strShippingPostalCode => ' . $this->strShippingPostalCode;
            $strToReturn .= ' | strBillingNamePrefix => ' . $this->strBillingNamePrefix;
            $strToReturn .= ' | strBillingFirstName => ' . $this->strBillingFirstName;
            $strToReturn .= ' | strBillingMiddleName => ' . $this->strBillingMiddleName;
            $strToReturn .= ' | strBillingLastName => ' . $this->strBillingLastName;
            $strToReturn .= ' | strBillingNameSuffix => ' . $this->strBillingNameSuffix;
            $strToReturn .= ' | strBillingCompany => ' . $this->strBillingCompany;
            $strToReturn .= ' | strBillingStreet1 => ' . $this->strBillingStreet1;
            $strToReturn .= ' | strBillingStreet2 => ' . $this->strBillingStreet2;
            $strToReturn .= ' | strBillingSuburb => ' . $this->strBillingSuburb;
            $strToReturn .= ' | strBillingCounty => ' . $this->strBillingCounty;
            $strToReturn .= ' | strBillingCity => ' . $this->strBillingCity;
            $strToReturn .= ' | intBillingZoneId => ' . $this->intBillingZoneId;
            $strToReturn .= ' | intBillingCountryId => ' . $this->intBillingCountryId;
            $strToReturn .= ' | strBillingPostalCode => ' . $this->strBillingPostalCode;
            $strToReturn .= ' | strNotes => ' . $this->strNotes;
            $strToReturn .= ' | intShippingMethodId => ' . $this->intShippingMethodId;
            $strToReturn .= ' | intPaymentMethodId => ' . $this->intPaymentMethodId;
            $strToReturn .= ' | intStatusId => ' . $this->intStatusId;
            $strToReturn .= ' | intTypeId => ' . $this->intTypeId;
            return $strToReturn;
                            
        }
        
	}//end class
?>