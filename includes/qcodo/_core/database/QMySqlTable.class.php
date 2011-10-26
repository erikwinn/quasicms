<?php
/**
* QMySqlTableType - an enumerator class for MySql table types
*@package QCodo
*/ 
    class QMySqlTableType{
                    const MYISAM = 1;
                    const INNODB = 2;
                    const MEMORY = 3;
                    const HEAP = 4;
    }

/**
* QMysqlTable - contains the meta data for a single table
* This is free software. It is licensed under the MIT license covering the
*QCodo library.
*
*@author Erik Winn <erikwinnmail@yahoo.com>
*@package QCodo
*/
class QMySqlTable
{
        /**
        * Flag indicating the type of table (eg: MyISAM, INNODB, etc ..)
        *@var QMySqliTableType
        */  
        protected $intTableType;
        /**
         * Name of the table (as defined in the database)
         * @var string Name
         */
        protected $strName;
        /**
         * Array of QDatabaseField objects (as indexed by Column name)
         * @var array QDatabaseField aryDatabaseFields
         */
        protected $aryDatabaseFields = array();
        /**
         * Array of QDatabaseForeignKey objects (indexed numerically)
         * @var array QDatabaseForeignKey aryQDatabaseForeignKey
         */
        protected $aryForeignKeys = array();
        /**
         * Array of QDatabaseIndex objects (indexed numerically)
         * @var array QDatabaseIndex aryIndexes
         */
        protected $aryIndexes = array();

        /**
         *
         * @param string strName Name of the Table
         * @return QMySqlTable
         */
        public function __construct($strName) {
            $this->strName = $strName;
        }
        public function AddDatabaseField($objDatabaseField)
        {
//            $this->aryDatabaseFields[$objDatabaseField->Name] = $objDatabaseField;
            $this->aryDatabaseFields[] = $objDatabaseField;
        }
        public function AddIndex($objIndex)
        {
            if($objIndex->PrimaryKey || $objIndex->Unique)
            {
                foreach($this->aryDatabaseFields as &$objField)
                {
                    if(in_array($objField->Name, $objIndex->ColumnNameArray))
                    {
                        $objField->PrimaryKey = $objIndex->PrimaryKey;
                        if(count($objIndex->ColumnNameArray) == 1)
                            $objField->Unique = $objIndex->Unique;
                    }
                }
            }
            $this->aryIndexes[] = $objIndex;
        }      
        public function AddForeignKey($objForeignKey)
        {
            $this->aryForeignKeys[] = $objForeignKey;
        }      

        /**
         * Override method to perform a property "Get"
         * This will get the value of $strName
         *
         * @param string strName Name of the property to get
         * @return mixed
         */
        public function __get($strName)
        {
            switch ($strName)
            {
                case 'Name':
                    return $this->strName;
                case 'DatabaseFields':
                    return (array) $this->aryDatabaseFields;
                case 'Indexes':
                    return (array) $this->aryIndexes;
                case 'ForeignKeys':
                    return (array) $this->aryForeignKeys;
                default:
                    try {
                        return parent::__get($strName);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }

        /**
         * Override method to perform a property "Set"
         * This will set the property $strName to be $mixValue
         *
         * @param string strName Name of the property to set
         * @param string mixValue New value of the property
         * @return mixed
         */
        public function __set($strName, $mixValue)
        {
            try {
                switch ($strName)
                {
                    case 'Name':
                        return $this->strName = QType::Cast($mixValue, QType::String);
                    case 'DatabaseFields':
                        return $this->aryDatabaseFields = QType::Cast($mixValue, QType::ArrayType);
                    case 'Indexes':
                        return $this->aryIndexes = QType::Cast($mixValue, QType::ArrayType);
                    case 'ForeignKeys':
                        return $this->aryForeignKeys = QType::Cast($mixValue, QType::ArrayType);
                    default:
                        return parent::__set($strName, $mixValue);
                }
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
        }
    }

?>