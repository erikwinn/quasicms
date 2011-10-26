<?php
	require(__DATAGEN_CLASSES__ . '/AccountGen.class.php');

	/**
	 * The Account class defined here contains any
	 * customized code for the Account class in the
	 * Object Relational Model.  It represents the "account" table 
	 * in the database, and extends from the code generated abstract AccountGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 * 
	 * @package Quasi
	 * @subpackage ORM
	 * 
	 */
	class Account extends AccountGen {
        /**
         * Protected member variable that maps to the database column account.online
         * @var boolean blnOnline
         */
        protected $blnOnline = false;
        const OnlineDefault = false;


        /**
         * Protected member variable that maps to the database column account.type_id
         * @var integer intTypeId
         */
        protected $intTypeId = 1;
        const TypeIdDefault = 1;


        /**
         * Protected member variable that maps to the database column account.status_id
         * @var integer intStatusId
         */
        protected $intStatusId = 1;
        const StatusIdDefault = 1;
        /**
         * Protected member variable that maps to the database column account.onetime_password
         * @var boolean blnOnetimePassword
         */
        protected $blnOnetimePassword = false;
        const OnetimePasswordDefault = false;
        /**
         * Protected member variable that maps to the database column account.valid_password
         * @var boolean blnValidPassword
         */
        protected $blnValidPassword = true;
        const ValidPasswordDefault = true;
        
		/**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * Can also be called directly via $objAccount->__toString().
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
                return $this->Name;
		}

        public function UpdateLoginState()
        {
            $objDatabase = Account::GetDatabase();
            $online =  $this->blnOnline;
            $lastlogin = $this->LastLogin;
            $logincount = $this->LoginCount;
            $id = $this->intId;
            $q = "UPDATE `account` SET `online` = '$online', `last_login` = '$lastlogin', `login_count` = '$logincount' WHERE `id` = '$id';";
            $objDatabase->NonQuery($q);
            $this->__blnRestored = true;
//            $this->Reload();
       }

        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Name':
                    return $this->Person->FirstName . ' ' . $this->Person->LastName;

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
                case 'LastLogin':
                    try {
                        return ($this->strLastLogin = QType::Cast($mixValue, QType::String));
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case 'RegistrationDate':
                    try {
                        return ($this->strLastLogin = QType::Cast($mixValue, QType::String));
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
	}
?>