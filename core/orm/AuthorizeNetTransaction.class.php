<?php
    require(__DATAGEN_CLASSES__ . '/AuthorizeNetTransactionGen.class.php');

    /**
     * The AuthorizeNetTransaction class defined here contains any
     * customized code for the AuthorizeNetTransaction class in the
     * Object Relational Model.  It represents the "authorize_net_transaction" table 
     * in the database, and extends from the code generated abstract AuthorizeNetTransactionGen
     * class, which contains all the basic CRUD-type functionality as well as
     * basic methods to handle relationships and index-based loading.
     * 
     * @package Quasi
     * @subpackage DataObjects
     * 
     */
    class AuthorizeNetTransaction extends AuthorizeNetTransactionGen
    {
    /// Indexes for the response array (pipe delimited string ..)
        const ResponseCodeIdx = 0;
        const ResponseSubcodeIdx = 1;
        const ResponseReasonCodeIdx = 2;
        const ResponseReasonTextIdx = 3;
        const AuthorizationCodeIdx = 4;
        const AVSResponseIdx = 5;
        const TransactionIdIdx = 6;
        const InvoiceNumberIdx = 7;
        const DescriptionIdx = 8;
        const AmountIdx = 9;
        const MethodIdx = 10;
        const TransactionTypeIdx = 11;
        const CustomerIdIdx = 12;
        const FirstNameIdx = 13;
        const LastNameIdx = 14;
        const CompanyIdx = 15;
        const AddressIdx = 16;
        const CityIdx = 17;
        const StateIdx = 18;
        const ZipCodeIdx = 19;
        const CountryIdx = 20;
        const PhoneIdx = 21;
        const FaxIdx = 22;
        const EmailAddressIdx = 23;
        const ShipToFirstNameIdx = 24;
        const ShipToLastNameIdx = 25;
        const ShipToCompanyIdx = 26;
        const ShipToAddressIdx = 27;
        const ShipToCityIdx = 28;
        const ShipToStateIdx = 29;
        const ShipToZipCodeIdx = 30;
        const ShipToCountryIdx = 31;
        const TaxIdx = 32;
        const DutyIdx = 33;
        const FreightIdx = 34;
        const TaxExemptIdx = 35;
        const PurchaseOrderNumberIdx = 36;
        const MD5HashIdx = 37;
        const CCVResponseIdx = 38;
        const CAVResponseIdx = 39;
   
        /**
         * Default "to string" handler
         * Allows pages to _p()/echo()/print() this object, and to define the default
         * way this object would be outputted.
         *
         * Can also be called directly via $objAuthorizeNetTransaction->__toString().
         *
         * @return string a nicely formatted string representation of this object
         */
        public function __toString()
        {
            return sprintf('AuthorizeNetTransaction Object %s',  $this->intId);
        }

    }
?>