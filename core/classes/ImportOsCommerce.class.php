<?php
/**
* This file is a part of Quasi CMS
*@package Quasi
*/
    
    require_once('../../includes/prepend.inc.php');
    require('QuasiDBI.php');

/**
* This class imports OsCommerce tables into Quasi.
* Actions are as follows:
*   1. Get the addresses and customer accounts, addresses are many to one with customers so they
*    are the first part of a left join. We loop through the addresses and save for each account.
*   2. On the first loop for a customer Id, Create an Account for the customer. This uses the customers
*    and address_book tables to create the Person associated with the Account.
*   3. Retrieve orders, totals and order_status_history from OSC tables for the customer (first pass only).
        This in turn calculates order_totals and translates order_status_history for each order found.
        At this point we also import products and order_items
*   4. Save addresses for each additional pass with this customer id.
*
*@todo
*   - set the image file for products
*   - import categories
*   - import and set manufacturer
*   - generally test, debug and improve; this is largely untested code except for one tailored import ..(08/29/08)
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*
* $Id: ImportOsCommerce.class.php 324 2008-10-28 22:11:39Z erikwinn $
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
*/
    
class ImportOsCommerce
{
    protected $objAccount = null;
    protected $objPerson = null;
//    protected $objAddress = null;

    protected $objDBI = null;
            
    protected $intCurrentImportId = null;
    
    protected $strAccountInfoQuery = 'SELECT address_book.address_book_id,
        address_book.customers_id as entry_customers_id,
        address_book.entry_gender,
        address_book.entry_company,
        address_book.entry_firstname,
        address_book.entry_lastname,
        address_book.entry_street_address,
        address_book.entry_suburb,
        address_book.entry_postcode,
        address_book.entry_city,
        address_book.entry_state,
        address_book.entry_country_id,
        address_book.entry_zone_id,
        address_book.entry_street_address2,
        customers.customers_id,
        customers.customers_gender,
        customers.customers_firstname,
        customers.customers_lastname,
        customers.customers_dob,
        customers.customers_email_address,
        customers.customers_default_address_id,
        customers.customers_telephone,
        customers.customers_fax,
        customers.customers_password,
        customers.customers_newsletter,
        customers_info.customers_info_date_of_last_logon,
        customers_info.customers_info_number_of_logons,
        customers_info.customers_info_date_account_created
        FROM address_book
        LEFT JOIN customers ON customers.customers_id = address_book.customers_id
        LEFT JOIN customers_info ON customers_info.customers_info_id = customers.customers_id
        GROUP BY address_book.customers_id
        ';
    public function __construct()
    {
        $this->objDBI = QuasiDBI::getInstance();
        if(! $this->objDBI instanceof QuasiDBI)
            echo('Failed to get database handle ..');
    }
    
    public function Run()
    {
        $intPreviousId = null;
        $intPersonId = null;
        $intDefaultAddressId = null;
        $aryAddresses = array();
        
        $this->objDBI->doQuery($this->strAccountInfoQuery);
        while($aryRow = $this->objDBI->nextRow() )
        {
            $this->intCurrentImportId = $aryRow['entry_customers_id'];
            if('root@localhost' == $aryRow['customers_email_address'])
                continue;
                
            if(!$this->intCurrentImportId )
            {
                echo "Warning address_book customers id is Null! Address Id: " . $aryRow['address_book_id'] ;
                continue;
            }
            //if we are not in loop to handle multiple addresses, create new account.
            if($intPreviousId !== $this->intCurrentImportId || null == $intPreviousId )
            {
                //clear the address stack
                $aryAddresses = array();
                $this->importAccount($aryRow);
                $this->importOrders($this->intCurrentImportId);
            }

            //First address type defaults to Primary
            if( empty( $aryAddresses ))
                $intTypeId = AddressType::Primary;
            elseif( $aryRow['address_book_id'] == $aryRow['customers_default_address_id'] )
            {//in case primary was set by defualt, adjust types ..
                    foreach($aryAddresses as $objAddress )
                    {
                        if(AddressType::Primary == $objAddress->TypeId )
                        {
                            $objAddress->TypeId = AddressType::Shipping;
                            $objAddress->Save();
                        }
                    }
                $intTypeId = AddressType::Primary;
            }
            else //additional addresses default to Billing ..
                $intTypeId = AddressType::Billing;
            
            $aryAddresses[] = $this->importAddress($aryRow, $intTypeId);
            $intPreviousId = $this->intCurrentImportId;
        }
        
    }
    /**
    *
    */
    protected function importAccount($aryRow)
    {
        $this->objAccount = new Account;
        $this->objPerson = new Person;

        ///First create a Person ..
        //remove TEMP garbage from oscommerce name columns - this may have been local junk ..
        if( strpos('TEMP', $aryRow['customers_firstname']) === false )
        {
            $this->objPerson->FirstName = $aryRow['entry_firstname'];
            $this->objPerson->LastName = $aryRow['entry_lastname'];
        }
        else
        {
            $this->objPerson->FirstName = $aryRow['customers_firstname'];
            $this->objPerson->LastName = $aryRow['customers_lastname'];
        }
        $this->objPerson->CompanyName = $aryRow['entry_company'];
        $this->objPerson->EmailAddress = $aryRow['customers_email_address'];
        $this->objPerson->PhoneNumber = $aryRow['customers_telephone'];
        $this->objPerson->Save();

        ///associate the Account and Address with this person
        $this->objAccount->PersonId = $this->objPerson->Id;
        $objAddress->PersonId = $this->objPerson->Id;

        /// transfer Account values - Note: this assumes import to a virgin database ..
        /// Quasi login supports OsCommerce style password encryption so we do not change login info
        $this->objAccount->Password = $aryRow['customers_password'];
        $this->objAccount->Username = $aryRow['customers_email_address'];
        $this->objAccount->RegistrationDate = $aryRow['customers_info_date_account_created'];
        $this->objAccount->LastLogin = $aryRow['customers_info_date_of_last_logon'];
        $this->objAccount->LoginCount = $aryRow['customers_info_number_of_logons'];
        $this->objAccount->TypeId = AccountType::Customer;
        $this->objAccount->StatusId = AccountStatusType::Active;
        $this->objAccount->Save();
    }
    /**
    *
    */
    protected function importAddress($aryRow, $intTypeId)
    {
        $objAddress = new Address;
        
        $strState = trim($aryRow['entry_state']);

        $objAddress->PersonId = $this->objPerson->Id;
        $objAddress->Street1 = $aryRow['entry_street_address'];
        $objAddress->Street2 = $aryRow['entry_street_address2'];
        $objAddress->Suburb = $aryRow['entry_suburb'];
        $objAddress->City = $aryRow['entry_city'];
        
        if($aryRow['entry_zone_id'] > 0)
            $objAddress->ZoneId = $aryRow['entry_zone_id'];
        elseif('' != $strState)
        {
            $objAddress->ZoneId = ZoneType::GetId($strState);
            if(ZoneType::NoZone == $objAddress->ZoneId)
                $objAddress->County = $strState;
        }
        else
             $objAddress->ZoneId = ZoneType::NoZone;
        
        if($aryRow['entry_country_id'] > 0)
            $objAddress->CountryId = $aryRow['entry_country_id'];
        elseif('' != $strState && CountryType::GetId($strState != CountryType::World))//international orders sometimes do this ..
            $objAddress->CountryId = CountryType::GetId($strState);
        else
            $objAddress->CountryId = ZoneType::$ExtraColumnValuesArray[$objAddress->ZoneId]['CountryId'];
                
        $objAddress->PostalCode = $aryRow['entry_postcode'];
        $objAddress->TypeId = $intTypeId;
        
        $objAddress->Save();
        return $objAddress;
    }

    protected function importOrders($intAccountId)
    {
        $q = 'SELECT * FROM orders WHERE customers_id = ' . $intAccountId;
        $objResultSet = $this->objDBI->doQuery($q, true);
        while($aryRow = $this->objDBI->nextRow($objResultSet))
        {
            $objOrder = new Order();
            $objOrder->AccountId = $this->objAccount->Id;
            $objOrder->Id = $aryRow['orders_id'];
            $objOrder->CreationDate = $aryRow['date_purchased'];
            $objOrder->LastModificationDate = $aryRow['last_modified'];
            $objOrder->CompletionDate = $aryRow['orders_date_finished'];
            
            $strPaymentMethod = $aryRow['payment_method']; 
            if( false !== strpos( $strPaymentMethod, 'Authorize.net' ) )
                $objOrder->PaymentMethodId = 3;
            elseif( false !== strpos( $strPaymentMethod, 'PayPal' ) )
                $objOrder->PaymentMethodId = 2;
            else
                $objOrder->PaymentMethodId = 1;

            $objOrder->ShippingMethodId = 1;
            $strShippingMethod =  $aryRow['shipping_method'];
            if( false !== strpos( $strShippinMethod, 'USPS' ) || false !== strpos( $strShippinMethod, 'United States Postal' ))
            {
                if( false !== strpos( $strShippinMethod, 'Priority' ) )
                    if( false !== strpos( $strShippinMethod, 'Global' ) )
                        $objOrder->ShippingMethodId = 7;
                    else
                        $objOrder->ShippingMethodId = 3;
                elseif( false !== strpos( $strShippinMethod, 'First' ) )
                    if( false !== strpos( $strShippinMethod, 'International' ) )
                        $objOrder->ShippingMethodId = 8;
                    else
                        $objOrder->ShippingMethodId = 2;
                elseif( false !== strpos( $strShippinMethod, 'Express' ) || false !== strpos( $strShippinMethod, 'EXPRESS' ))
                    if( false !== strpos( $strShippinMethod, 'Global' ) )
                        $objOrder->ShippingMethodId = 8;
                    else
                        $objOrder->ShippingMethodId = 2;
                    
            }
            elseif( false !== strpos( $strShippinMethod, 'FedEx' ) || false !== strpos( $strShippinMethod, 'Federal Express' ))
            {
                if( false !== strpos( $strShippinMethod, '2 Day' ) )
                    $objOrder->ShippingMethodId = 10;
                elseif( false !== strpos( $strShippinMethod, 'Ground' ) || false !== strpos( $strShippinMethod, 'Home' ) )
                    $objOrder->ShippingMethodId = 9;
                elseif( false !== strpos( $strShippinMethod, 'Standard Overnight' ) )
                    $objOrder->ShippingMethodId = 11;                    
            }
            
            $intStatusId = $aryRow['orders_status'];
            $objOrder->StatusId = $this->translateStatusId($intStatusId);
                        
            $aryName = explode(' ', $aryRow['delivery_name'] );
            
            $objOrder->ShippingFirstName = $aryName[0];
            $objOrder->ShippingLastName = $aryName[1];
            $objOrder->ShippingStreet1 = $aryRow['delivery_street_address'];
            $objOrder->ShippingStreet2 = $aryRow['delivery_street_address2'];
            $objOrder->ShippingSuburb = $aryRow['delivery_suburb'];
            $objOrder->ShippingCity = $aryRow['delivery_city'];
            $objOrder->ShippingZoneId = ZoneType::GetId( $aryRow['delivery_state']);
            $objOrder->ShippingCountryId = CountryType::GetId( $aryRow['delivery_country']);
            $objOrder->ShippingPostalCode = $aryRow['delivery_postcode'];
            
            $aryName = explode(' ', $aryRow['billing_name'] );
            
            $objOrder->BillingFirstName = $aryName[0];
            $objOrder->BillingLastName = $aryName[1];
            $objOrder->BillingStreet1 = $aryRow['billing_street_address'];
            $objOrder->BillingStreet2 = $aryRow['billing_street_address2'];
            $objOrder->BillingSuburb = $aryRow['billing_suburb'];
            $objOrder->BillingCity = $aryRow['billing_city'];
            $objOrder->BillingZoneId = ZoneType::GetId( $aryRow['billing_state']);
            $objOrder->BillingCountryId = CountryType::GetId( $aryRow['billing_country']);
            $objOrder->BillingPostalCode = $aryRow['billing_postcode'];
            
            $objOrder->Insert();
            $objOrder->Reload();
            
            $this->importOrderItems($objOrder->Id);
            $this->importOrderHistory($objOrder->Id);
            $this->importOrderTotals($objOrder);
            
            $objOrder->HandlingCharged = 10.00;
            $objOrder->Save();
        }
        
        
    }
    /**
    *
    */
    protected function importOrderTotals($objOrder)
    {
        $q = 'SELECT * FROM orders_total WHERE orders_id = ' . $objOrder->Id;
        $objResultSet = $this->objDBI->doQuery($q, true);
        while($aryRow = $this->objDBI->nextRow($objResultSet))
        {
            switch($aryRow['class'])
            {
                case 'ot_total':
                    break;//ignore ..
                case 'ot_subtotal':
                    $objOrder->ProductTotalCharged = $aryRow['value'];
                    break;
                case 'ot_shipping':
                    $fltShipping = ($aryRow['value'] - 10);
                    $objOrder->ShippingCharged = $fltShipping;
                    break;
                case 'ot_tax':
                    $objOrder->Tax = $aryRow['value'];
                    break;
                default:
                    print('Warning: Unknown orders_total class:' . $aryRow['class']);
            }
        }       
    }
    /**
    *
    */
    protected function importOrderItems($intOrderId)
    {
        $q = 'SELECT * FROM orders_products WHERE orders_id = ' . $intOrderId;
        $objResultSet = $this->objDBI->doQuery($q, true);
        $fltTotal = 0;
        while($aryRow = $this->objDBI->nextRow($objResultSet))
        {   
            $objOrderItem = new OrderItem();
            
            $objOrderItem->OrderId = $intOrderId;
            $objOrderItem->ProductId = $aryRow['products_id'];
            $objOrderItem->Quantity = $aryRow['products_quantity'];
            if( is_numeric($objOrderItem->ProductId)
                && $objOrderItem->ProductId > 0
                && $objOrderItem->ProductId < 16777215)
                $this->importProduct($objOrderItem->ProductId);
            else
                continue;
            
            $objOrderItem->Save();
            
            $fltPrice = $aryRow['final_price']; 
            $fltTotal += ( $fltPrice * $objOrderItem->Quantity );
            
        }
        return $fltTotal;
    }
    /**
    *
    */
    protected function importOrderHistory($intOrderId)
    {
        $aryHistories  = array();
        
        $q = 'SELECT * FROM orders_status_history WHERE orders_id = ' . $intOrderId;
        $objResultSet = $this->objDBI->doQuery($q, true);
        while($aryRow = $this->objDBI->nextRow($objResultSet))
        {
            if(6 == $aryRow['orders_status_id'])
                continue;
            
            $objStatusHistory = new OrderStatusHistory();
            $objStatusHistory->OrderId = $intOrderId;            
            $objStatusHistory->Date = $aryRow['date_added'];
            
            $objStatusHistory->StatusId = $this->translateStatusId($aryRow['orders_status_id']);
            $objStatusHistory->Notes = $aryRow['comments'];
            
            foreach($aryHistories as $idx_1 => &$objStackedHistory)
            {
                if($objStackedHistory->Date == $objStatusHistory->Date)
                {
                //remove PayPal double notifications ..
                    if($objStatusHistory->StatusId == OrderStatusType::Pending)
                        continue 2;
                    elseif($objStackedHistory->StatusId == OrderStatusType::Pending)
                            unset($aryHistories[$idx_1]);
                    //random duplicates
                    elseif( $objHistory->StatusId == $objStatusHistory->StatusId)
                            unset($aryHistories[$idx_1]);
                    else                        
                        foreach($aryHistories as $idx_2 => &$objHistory)
                            if( $objHistory->StatusId == $objStatusHistory->StatusId)
                                unset($aryHistories[$idx_2]);
                }
            }
            $aryHistories[] = $objStatusHistory;
        }
            
        foreach( $aryHistories as $objHistory )
            if($objHistory instanceof OrderStatusHistory)
                $objHistory->InsertDated();
    }
    /**
    *
    */
    protected function importProduct($intProductId)
    {
        $objProduct = Product::Load($intProductId);
        if( $objProduct instanceof Product )
            return true;
            
        $q = 'SELECT p.*, pd.products_name, pd.products_description
                FROM products AS p
                JOIN products_description AS pd ON pd.products_id = p.products_id
                WHERE p.products_id ='. $intProductId;
        
        $aryRow = $this->objDBI->fetchRow($q);
        $objProduct = new Product();
        $objProduct->Id = $intProductId;
        $objProduct->CreationDate = $aryRow['products_date_added'];
        $objProduct->IsVirtual = false;
        $objProduct->TypeId = ProductType::Storefront;
        $objProduct->StatusId = ProductStatusType::Active;
        $objProduct->ManufacturerId =  $aryRow['manufacturer_id'];
        $objProduct->SupplierId = 1; //default to store owner ..
        
        $objProduct->UserPermissionsId = PermissionType::Delete;
        $objProduct->PublicPermissionsId = PermissionType::View;
        $objProduct->GroupPermissionsId = PermissionType::View;

        $objProduct->RetailPrice = $aryRow['products_price'];
        $objProduct->Weight = $aryRow['products_weight'];

        if ( '' != $aryRow['products_model'])
            $objProduct->Model = $aryRow['products_model'];
        else
            $objProduct->Model = 'Model_' . $intProductId;
        
        if ( '' != $aryRow['products_name'])
            $objProduct->Name = $aryRow['products_name'];
        else
            $objProduct->Name = 'Product #' . $intProductId;
        
        if ( '' != $aryRow['products_description'])
            $objProduct->LongDescription = $aryRow['products_description'];
        else
            $objProduct->LongDescription = 'No product description available.';
                
/* FTR - other things we may want to import .. currently disabled
        if ( '' != $aryRow['Manufacturer']) $objProduct->ManufacturerId = $aryRow['Manufacturer'];
        if ( '' != $aryRow['Supplier']) $objProduct->SupplierId = $aryRow['Supplier'];
        if ( '' != $aryRow['ShortDescription']) $objProduct->ShortDescription = $aryRow['ShortDescription'];
        if ( '' != $aryRow['Msrp']) $objProduct->Msrp = $aryRow['Msrp'];
        if ( '' != $aryRow['WholesalePrice']) $objProduct->WholesalePrice = $aryRow['WholesalePrice'];
        if ( '' != $aryRow['Cost']) $objProduct->Cost = $aryRow['Cost'];
        if ( '' != $aryRow['IsVirtual'])  $aryRow['IsVirtual'];
        if ( '' != $aryRow['Type'])$aryRow['Type'];
        if ( '' != $aryRow['Status'])  = $aryRow['Status'];
        if ( '' != $aryRow['ViewCount']) $objProduct->ViewCount = $aryRow['ViewCount'];
*/

       $objProduct->InsertWithId();
       return true;
    }
    /**
    * This function translates an OsCommerce status to Quasi - customize to suit ..
    */
    protected function translateStatusId($intStatusId)
    {
        switch($intStatusId)
        {
            case 1:
                return OrderStatusType::Paid;
                break;
            case 2: //invoice printed
            case 3: //label printed
            case 4: //shipped
                return OrderStatusType::Shipped;
                break;
            case 12:
                return OrderStatusType::Processing;
                break;
            case 14:
                return OrderStatusType::Cancelled;
                break;
            case 15: //preparing paypal ..
                return OrderStatusType::Pending;
                break;
            case 16:
                return OrderStatusType::Processing;
                break;
            default:
                return OrderStatusType::Shipped;
        }
    }
}
?>