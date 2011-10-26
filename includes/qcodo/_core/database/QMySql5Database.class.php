<?php
/**
* Include Table class
*/
require_once('QMySqlTable.class.php');

/**
* QCodo database adapter for MySQL 5 (mysqli driver)
*@package QCodo
*/  
    class QMySql5Database extends QDatabaseBase
    {
		const Adapter = 'MySql Improved Database Adapter for MySQL 5';

        /**
        * Database handle/object for the mysqli driver
        *@var object mysqli database handle
        */  
		protected $objMySqli;
        /**
        * An array containing the names for all of the tables in this database
        * This is initilized once by the first call to GetTables() and referenced thereafter.
        * Note: Adapters may optionally initialize this array at their convenience (eg. by parsing create statements
        * for the entire database at once for increased efficiency.) and simply refer to it
        * when the Get* methods are called
        *@var array of strings aryTableNames 
        */
        protected $aryTableNames = null;
        
        /**
        * An array containing the indices (QDatabaseIndex objects) for all of the tables in this database
        * The array is a multidimensional hash indexed by table name: array(tablename => array(object, object ...)).
        * This is initilized once by the first call to GetIndexesForTable and referenced thereafter.
        * Note: Adapters may optionally initialize this array at their convenience (eg. by parsing create statements
        * for the entire database at once for increased efficiency.) and simply refer to it
        * when the Get* methods are called
        *@var array of QDatabaseIndex aryTableIndexes
        */
        protected $aryTableIndexes = null;
        
        /**
        * An array containing the foreign keys (QDatabaseForeignKey objects) for all of the tables in this database
        * The array is a multidimensional hash indexed by table name: array(tablename => array(object, object ...)).
        * This is initilized once by the first call to GetForeignKeysForTable and referenced thereafter.
        * Note: Adapters may optionally initialize this array at their convenience (eg. by parsing create statements
        * for the entire database at once for increased efficiency.) and simply refer to it
        * when the Get* methods are called
        *@var array of QDatabaseForeignKey aryTableForeignKeys
        */
        protected $aryTableForeignKeys = null;

        /**
        * Array of table objects representing the database meta data
        *@var array QMySqlTables
        */  
        protected $aryTables;
		
        protected $strEscapeIdentifierBegin = '`';
		protected $strEscapeIdentifierEnd = '`';

		public function SqlLimitVariablePrefix($strLimitInfo) {
			// MySQL uses Limit by Suffixes (via a LIMIT clause)

			// If requested, use SQL_CALC_FOUND_ROWS directive to utilize GetFoundRows() method
			if (array_key_exists('usefoundrows', $this->objConfigArray) && $this->objConfigArray['usefoundrows'])
				return 'SQL_CALC_FOUND_ROWS';

			return null;
		}

		public function SqlLimitVariableSuffix($strLimitInfo) {
			// Setup limit suffix (if applicable) via a LIMIT clause 
			if (strlen($strLimitInfo)) {
				if (strpos($strLimitInfo, ';') !== false)
					throw new Exception('Invalid Semicolon in LIMIT Info');
				if (strpos($strLimitInfo, '`') !== false)
					throw new Exception('Invalid Backtick in LIMIT Info');
				return "LIMIT $strLimitInfo";
			}

			return null;
		}

		public function SqlSortByVariable($strSortByInfo) {
			// Setup sorting information (if applicable) via a ORDER BY clause
			if (strlen($strSortByInfo)) {
				if (strpos($strSortByInfo, ';') !== false)
					throw new Exception('Invalid Semicolon in ORDER BY Info');
				if (strpos($strSortByInfo, '`') !== false)
					throw new Exception('Invalid Backtick in ORDER BY Info');

				return "ORDER BY $strSortByInfo";
			}
			
			return null;
		}

        /**
        * This creates the database handle (a MySQL object). It supports SSL connections.
        *  Note: 
        * You must place and configure SSL keys for SSL support. A client key,
        * client certificate, and CA certificate matching the one on the server
        * must be available on the local filesystem. Then these must be specified
        * in configuration.inc.php in the DB_CONNECTION_* array for this adapter.
        */
		public function Connect()
        {
            //Instantiate the db handle
            $this->objMySqli = mysqli_init();
            //Set the SSL key information if needed
            if ($this->Secure )
                $this->objMySqli->ssl_set($this->SslKey, $this->SslCertificate, $this->SslCACertificate, null, null);
			// Connect to the Database Server
			$this->objMySqli->real_connect($this->Server, $this->Username, $this->Password, $this->Database, $this->Port);

            if (mysqli_connect_errno())
                throw new QMySql5DatabaseException("Unable to connect to Database" . $this->objMySqli->error, -1, null);
//                throw new QMySql5DatabaseException("Unable to connect to Database: " . mysqli_connect_error(), -1, null);
			
			if ($this->objMySqli->error)
				throw new QMySql5DatabaseException($this->objMySqli->error, $this->objMySqli->errno, null);

			// Update "Connected" Flag
			$this->blnConnectedFlag = true;

			// Set to AutoCommit
			$this->NonQuery('SET AUTOCOMMIT=1;');

			// Set NAMES (if applicable)
			if (array_key_exists('encoding', $this->objConfigArray))
				$this->NonQuery('SET NAMES ' . $this->objConfigArray['encoding'] . ';');
		}

		public function __get($strName)
        {
			switch ($strName) {
                case 'AffectedRows':
                    return $this->objMySqli->affected_rows;
                case 'Tables':
                    return $this->aryTables;
				default:
					try {
						return parent::__get($strName);
					} catch (QCallerException $objExc) {
						$objExc->IncrementOffset();
						throw $objExc;
					}
			}
		}

		public function Query($strQuery) {
			// Connect if Applicable
			if (!$this->blnConnectedFlag) $this->Connect();

			// Log Query (for Profiling, if applicable)
			$this->LogQuery($strQuery);

			// Perform the Query
			$objResult = $this->objMySqli->query($strQuery);
			if ($this->objMySqli->error)
				throw new QMySql5DatabaseException($this->objMySqli->error, $this->objMySqli->errno, $strQuery);

			// Return the Result
			$objMySqliDatabaseResult = new QMySql5DatabaseResult($objResult, $this);
			return $objMySqliDatabaseResult;
		}

		public function NonQuery($strNonQuery) {
			// Connect if Applicable
			if (!$this->blnConnectedFlag) $this->Connect();

			// Log Query (for Profiling, if applicable)
			$this->LogQuery($strNonQuery);

			// Perform the Query
			$this->objMySqli->query($strNonQuery);
			if ($this->objMySqli->error)
				throw new QMySql5DatabaseException($this->objMySqli->error, $this->objMySqli->errno, $strNonQuery);
		}
        /**
         * Performs a Multi Result-Set Query, which is available with Stored Procs in MySQL 5
         * Written by Mike Hostetler
         *
         * @param string $strQuery
         * @return QMySql5DatabaseResult[] array of results
         */
        public function MultiQuery($strQuery) {
            // Connect if Applicable
            if (!$this->blnConnectedFlag) $this->Connect();

            // Log Query (for Profiling, if applicable)
            $this->LogQuery($strQuery);

            // Perform the Query
            $this->objMySqli->multi_query($strQuery);
            if ($this->objMySqli->error)
                throw new QMySql5DatabaseException($this->objMySqli->error, $this->objMySqli->errno, $strQuery);

            $objResultSets = array();
            do {
                if ($objResult = $this->objMySqli->store_result()) {
                    array_push($objResultSets,new QMySql5DatabaseResult($objResult, $this));
                }
            } while ($this->objMySqli->next_result());

            return $objResultSets;
        }
    

        /**
        * This function returns an array of table names for the database.
        * The array is stored locally and only initialized on the first call.
        * 
        * Note: Since normally only QCodeGen functions call this (in fact only
        * once AFAIK ..), we also go ahead and initilize the rest of the database
        * meta data that will be called for during code generation. This allows
        * for much greater efficiency and cleaner methods for returning meta data.
        *
        *@return array of strings containing the table names
        */      
		public function GetTables()
        {
            if(null !== $this->aryTableNames)
                return (array) $this->aryTableNames;

             $this->aryTableNames = array();
             
			if (!$this->blnConnectedFlag)
                $this->Connect();

            // Use the MySQL5 Information Schema to get a list of all the tables in this database
            // (excluding views, etc.)
            $strDatabaseName = $this->Database;

            $objResult = $this->Query("
                SELECT
                    table_name
                FROM
                    information_schema.tables
                WHERE
                    table_type <> 'VIEW' AND
                    table_schema = '$strDatabaseName';
            ");
			
            while ($aryRow = $objResult->FetchRow())
                $this->aryTableNames[] = $aryRow[0];

            //Now go ahead and cache the tables meta data
            $this->InitializeMetaData();
            
            return (array) $this->aryTableNames;
		}
        /**
        * This function returns an array of QDatabaseFields  for the given table.
        *@param string name of the table from which to return field objects
        *@return array of QDatabaseField objects
        */              
        public function GetFieldsForTable($strTableName)
        {
            if(!is_array($this->aryTables))
                $this->GetTables();
            $objTable = $this->aryTables[$strTableName];
            if(!$objTable instanceof QMySqlTable)
                throw new QCallerException('GetFields - Table does not exist: ' . $strTableName);
            return $objTable->DatabaseFields;
        }
        /**
        * This function returns an array of QDatabaseIndexes  for the given table.
        *@param string name of the table from which to return index objects
        *@return array of QDatabaseIndex objects
        */
        public function GetIndexesForTable($strTableName)
        {
             if(! is_array($this->aryTables))
                $this->GetTables(); 
            return $this->aryTables[$strTableName]->Indexes;
        }
        /**
        * This function returns an array of QDatabaseForeignKeys  for the given table.
        *@param string name of the table from which to return foreign key objects
        *@return array of QDatabaseForeignKey objects
        */      
        public function GetForeignKeysForTable($strTableName)
        {
             if(! is_array($this->aryTables))
                $this->GetTables(); 
            return $this->aryTables[$strTableName]->ForeignKeys;
        }

        /**
        * This function iterates through the array of table names and initilizes
        * QMySqlTable object for each, including the QDatabaseFields, QDatabaseIndexes
        * and QDatabaseForeignKeys.
        */
        public function InitializeMetaData()
        {
            if(!is_array($this->aryTableNames))
                $this->GetTables();
                
            foreach( $this->aryTableNames as $strTableName )
            {
                $objResult = $this->Query("SHOW CREATE TABLE `" . $strTableName . "` ");
                $aryRow = $objResult->FetchRow();
                $strStatement = $aryRow[1];
                $aryStatement = explode("\n", strtr($strStatement, "\r", ''));
                
                $objTable = new QMySqlTable($strTableName);
                
                foreach($aryStatement as $strLine)
                {
                    $strLine = trim($strLine);
                    if(false !== strpos( $strLine, 'CREATE TABLE'))
                        continue;
                    if(0 === strpos( $strLine, '`'))
                        $this->ParseColumnStatement($strLine, $objTable);
                    elseif(false !== strpos( $strLine, 'FOREIGN KEY'))
                        $this->ParseForeignKeyStatement($strLine, $objTable);
                    elseif(false !== strpos( $strLine, 'KEY '))
                        $this->ParseKeyStatement($strLine, $objTable);
/*                    elseif(false !== strpos( $strLine, 'ENGINE=')
                        $this->ParseEngineStatement($strLine, $objTable);*/
                    else//shouldn't happen ..
                        continue;                    
                }
                $this->aryTables[$objTable->Name] = $objTable;
            }            
        }
        /**
        * This function parses an index (aka key)  creation statement for the index
        * meta data and adds a QDatabaseIndex to the given table object.
        *@param string line containing the SQL command
        *@param QMySqlTable table to which index is added
        */              
        public function ParseKeyStatement($strLine, &$objTable)
        {
            $strKeyName = '';
            $blnPrimary = false;
            $blnUnique = false;
            $aryColumnNames = array();
            $intPos = strpos($strLine, 'KEY ') + strlen('KEY ');
            $intNextPos = strpos($strLine, ' ',  $intPos + 1);
            $strKeyName = trim(substr($strLine, $intPos + 1, ($intNextPos - $intPos - 1)), '`');
            $aryColumnNames = $this->ExtractColumnNamesArray($strLine);

            if(false !== strpos( $strLine, 'PRIMARY '))
                $blnPrimary = true;
            if( $blnPrimary && count($aryColumnNames) == 1)
                $blnUnique = true;
            if( false !== strpos( $strLine, 'UNIQUE '))
                $blnUnique = true;
                
            $objIndex = new QMySqlIndex($strKeyName, $blnPrimary, $blnUnique, $aryColumnNames);
            $objTable->AddIndex($objIndex);
        }
        /**
        * This function parses a foreign key constraint creation statement for the key
        * meta data and adds a QDatabaseForeignKey to the given table object.
        *@param string line containing the SQL command
        *@param QMySqlTable table to which index is added
        */              
        public function ParseForeignKeyStatement($strLine, &$objTable)
        {
            $strKeyName = '';
            $strReferenceTable = '';
            $aryColumnNames = array();
            $aryReferenceColumnNames = array();
            
            $intPos = strpos($strLine, 'FOREIGN KEY ') + strlen('FOREIGN KEY ');
            $intNextPos = strpos($strLine, ' ',  $intPos + 1);
            $strKeyName = substr($strLine, $intPos + 1, ($intNextPos - $intPos - 1));
            $strRemoveMePat = '/[()`]/';
            $strKeyName = preg_replace($strRemoveMePat,'', $strKeyName );
            $intPos = strpos($strLine, 'REFERENCES ') + strlen('REFERENCES ');
            $intNextPos = strpos($strLine, ' ',  $intPos + 1);
            
            //send the first half of line to get column names, second for references ..
            $strColumnNames = substr($strLine,0,$intPos);
            $strReferenceColumnNames = substr($strLine, $intPos + 1);
            
            $aryColumnNames = $this->ExtractColumnNamesArray($strColumnNames);
            $strReferenceTable = substr($strLine, $intPos + 1, ($intNextPos - $intPos - 2));
            
            $aryReferenceColumnNames = $this->ExtractColumnNamesArray($strReferenceColumnNames);
                        
            $objIndex = new QDatabaseForeignKey($strKeyName,
                                                                                $aryColumnNames,
                                                                                $strReferenceTable,
                                                                                $aryReferenceColumnNames);
            $objTable->AddForeignKey($objIndex);
        }
        /**
        * This function parses a column creation statement for the column
        * meta data and adds a QDatabaseField to the given table object.
        *@param string line containing the  SQL command
        *@param QMySqlTable table to which column is added
        */              
        public function ParseColumnStatement($strLine, &$objTable)
        {            
            $strType = '';
            $strDefault = '';
            $strLength = '';
            $strFieldName = '';
            
            $intPos = strpos($strLine, ' ');
            $intNextPos = strpos($strLine, ' ', $intPos + 1);
            $strFieldName = trim(substr($strLine, 0, $intPos), '`');

            //no more white space? just use the rest of the line (eg. "`notes` text,")
            if(false === $intNextPos)
                $strType = trim(substr($strLine, $intPos),',');
            else
                $strType = trim(substr($strLine, $intPos, ($intNextPos - $intPos)));
            
            $intPos = strpos($strType, '(');
            if(false !== $intPos )
            {
                $intNextPos = strpos($strType, ')', $intPos + 1);
                if(false === stripos($strType,'enum') && false === stripos($strType,'set'))
                {
                    $strLength = trim(substr($strType, $intPos + 1, ($intNextPos - $intPos - 1) ));
                    ///@todo - support decimal(12,2), for now we ignore the point ..
                    $intCommaPos = strpos($strLength,',');
                    if(false !== $intCommaPos )
                        $strLength = substr($strLength, 0, $intCommaPos);
                }
                $strType = substr($strType, 0, $intPos);
            }
            $strType = trim($strType);
            
            $intPos = strpos($strLine, 'default');
            if(false !== $intPos)
            {
                $strDefault = rtrim(substr($strLine, $intPos + strlen('default')),',');
                $strDefault = trim(trim($strDefault),'\'');
            }
            
            $objField = new QMySql5DatabaseField(null,null,false);
            $objField->NotNull = (false !== strpos( $strLine, 'NOT NULL'));
            $objField->Timestamp = (false !== strpos( $strLine, 'timestamp'));
            $objField->Default = $strDefault;
            $objField->Type = $this->GetColumnTypeFromString($strType,$strLength);
            $objField->Name = $strFieldName;
            $objField->MaxLength = $strLength;
            $objField->OriginalName = $strFieldName;
            $objField->Table = $objTable->Name;
            $objField->OriginalTable = $objTable->Name;
            $objField->Identity = (false !== strpos( $strLine, 'auto_increment'));
            if($objField->Identity)
            {
                $objField->Unique = true;
                $objField->PrimaryKey = true;
            }
                
            $objTable->AddDatabaseField($objField);
        }
        
        protected function GetColumnTypeFromString($strType, $intLength)
        {
            switch ($strType)
            {
                case 'int':
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'bigint':
                    if ($intLength == 1)
                        $intType = QDatabaseFieldType::Bit;
                    else
                        $intType = QDatabaseFieldType::Integer;
                    break;
                case 'real':
                case 'float':
                case 'decimal':
                    $intType = QDatabaseFieldType::Float;
                    break;
                case 'double':
                    // NOTE: PHP does not offer full support of double-precision floats.
                    // Value will be set as a VarChar which will guarantee that the precision will be maintained.
                    //    However, you will not be able to support full typing control (e.g. you would
                    //    not be able to use a QFloatTextBox -- only a regular QTextBox)
                    $intType = QDatabaseFieldType::VarChar;
                    break;
                case 'timestamp':
                    // System-generated Timestamp values need to be treated as plain text
                    $intType = QDatabaseFieldType::VarChar;
                    break;
                case 'date':
                    $intType = QDatabaseFieldType::Date;
                    break;
                case 'time':
                    $intType = QDatabaseFieldType::Time;
                    break;
                case 'datetime':
                    $intType = QDatabaseFieldType::DateTime;
                    break;
                case 'text':
                case 'blob':
                    $intType = QDatabaseFieldType::Blob;
                    break;
                case 'string':
                case 'enum':
                case 'set':
                case 'varchar':
                    $intType = QDatabaseFieldType::VarChar;
                    break;
                case 'char':
                    $intType = QDatabaseFieldType::Char;
                    break;
                default:
                    throw new Exception("Unable to determine MySqli Database Field Type: " . $strType);
                    break;
            }
            return $intType;
        }
        /**
        * This function parses an index creation statement for the column names.
        * The column names will be in the string in this format:
        *   "(`panel_id`,`product_id`,`order_id`)"
        * Parens and back tics will be removed and an array split on commas
        * will be returned.
        *NOTE: Only _one_ such pattern is expected! To use on a line with more
        * (like foreign keys), send only the substring containing the column names.
        *
        *@param string line containing the  SQL command
        *@return array of strings containing the column names for the index
        */              
        private function ExtractColumnNamesArray($strLine)
        {
            //remove index size data if present (eg: "(255)")
            $strRemoveMePat = '/\(\d+\)/';
            $strLine = preg_replace($strRemoveMePat,'', $strLine);
            $intStart = strpos($strLine, '(') + 1;
            $intEnd = strpos($strLine, ')'); 
            
            $strColumnNames = rtrim(substr($strLine, $intStart, $intEnd - $intStart),',');
            //remove back tics ..
            $strRemoveMePat = '/`/';
            $strColumnNames = preg_replace($strRemoveMePat,'', $strColumnNames );
            return explode(',', $strColumnNames);
        }
		public function InsertId($strTableName = null, $strColumnName = null) {
			return $this->objMySqli->insert_id;
		}

		public function Close() {
			$this->objMySqli->close();
		}
		
		public function TransactionBegin() {
			// Connect if Applicable
			if (!$this->blnConnectedFlag) $this->Connect();

			// Set to AutoCommit
			$this->NonQuery('SET AUTOCOMMIT=0;');
		}

		public function TransactionCommit() {
			// Connect if Applicable
			if (!$this->blnConnectedFlag) $this->Connect();

			$this->NonQuery('COMMIT;');
			// Set to AutoCommit
			$this->NonQuery('SET AUTOCOMMIT=1;');
		}

		public function TransactionRollback() {
			// Connect if Applicable
			if (!$this->blnConnectedFlag) $this->Connect();

			$this->NonQuery('ROLLBACK;');
			// Set to AutoCommit

			$this->NonQuery('SET AUTOCOMMIT=1;');
		}

		public function GetFoundRows() {
			if (array_key_exists('usefoundrows', $this->objConfigArray) && $this->objConfigArray['usefoundrows']) {
				$objResult = $this->Query('SELECT FOUND_ROWS();');
				$strRow = $objResult->FetchArray();
				return $strRow[0];
			} else
				throw new QCallerException('Cannot call GetFoundRows() on the database when "usefoundrows" configuration was not set to true.');
		}
	}

	class QMySql5DatabaseException extends QDatabaseExceptionBase {
		public function __construct($strMessage, $intNumber, $strQuery) {
			parent::__construct(sprintf("MySqli Error: %s", $strMessage), 2);
			$this->intErrorNumber = $intNumber;
			$this->strQuery = $strQuery;
		}
	}

	class QMySql5DatabaseResult extends QDatabaseResultBase {
		protected $objMySqliResult;
		protected $objDb;

		public function __construct(mysqli_result $objResult, QMySql5Database $objDb) {
			$this->objMySqliResult = $objResult;
			$this->objDb = $objDb;
		}

		public function FetchArray() {
			return $this->objMySqliResult->fetch_array();
		}

		public function FetchFields()
        {
            ///@todo - if(is_array($this->objDb->Tables)) get from there ..
                
			$objArrayToReturn = array();
            while ($objField = $this->objMySqliResult->fetch_field())
                array_push($objArrayToReturn, new QMySql5DatabaseField($objField, $this->objDb));
			return $objArrayToReturn;
		}

		public function FetchField() {
			if ($objField = $this->objMySqliResult->fetch_field())
				return new QMySql5DatabaseField($objField, $this->objDb);
		}

		public function FetchRow() {
			return $this->objMySqliResult->fetch_row();
		}

		public function MySqlFetchField() {
			return $this->objMySqliResult->fetch_field();
		}

		public function CountRows() {
			return $this->objMySqliResult->num_rows;
		}

		public function CountFields() {
			return $this->objMySqliResult->num_fields();
		}

		public function Close() {
			$this->objMySqliResult->free();
		}
		
		public function GetNextRow() {
			$strColumnArray = $this->FetchArray();
			
			if ($strColumnArray)
				return new QMySql5DatabaseRow($strColumnArray);
			else
				return null;
		}

		public function GetRows() {
			$objDbRowArray = array();
			while ($objDbRow = $this->GetNextRow())
				array_push($objDbRowArray, $objDbRow);
			return $objDbRowArray;
		}
	}

	class QMySql5DatabaseRow extends QDatabaseRowBase {
		protected $strColumnArray;

		public function __construct($strColumnArray) {
			$this->strColumnArray = $strColumnArray;
		}

		public function GetColumn($strColumnName, $strColumnType = null) {
			if (array_key_exists($strColumnName, $this->strColumnArray)) {
				if (is_null($this->strColumnArray[$strColumnName]))
					return null;

				switch ($strColumnType) {
					case QDatabaseFieldType::Bit:
						// Account for single bit value
						$chrBit = $this->strColumnArray[$strColumnName];
						if ((strlen($chrBit) == 1) && (ord($chrBit) == 0))
							return false;

						// Otherwise, use PHP conditional to determine true or false
						return ($this->strColumnArray[$strColumnName]) ? true : false;

					case QDatabaseFieldType::Blob:
					case QDatabaseFieldType::Char:
					case QDatabaseFieldType::VarChar:
						return QType::Cast($this->strColumnArray[$strColumnName], QType::String);

					case QDatabaseFieldType::Date:
					case QDatabaseFieldType::DateTime:
					case QDatabaseFieldType::Time:
						return new QDateTime($this->strColumnArray[$strColumnName]);

					case QDatabaseFieldType::Float:
						return QType::Cast($this->strColumnArray[$strColumnName], QType::Float);

					case QDatabaseFieldType::Integer:
						return QType::Cast($this->strColumnArray[$strColumnName], QType::Integer);

					default:
						return $this->strColumnArray[$strColumnName];
				}
			} else
				return null;
		}

		public function ColumnExists($strColumnName) {
			return array_key_exists($strColumnName, $this->strColumnArray);
		}

		public function GetColumnNameArray() {
			return $this->strColumnArray;
		}
	}

    /**
    * This class represents the meta data of a single column in the database
    * Note that we need to initilize much of the data manually (currently) as
    * Mysql's API ( mysql_fetch_field_direct()) does not populate the object we
    * are looking at with expected values, eg. max_length is the maximum _found_
    * and "def" (default value) is not populated at all ...
    *
    *@todo Move initialization to QMysqliDatabase, optimize with one query for the table and
    * store the results ..
    */
    class QMySql5DatabaseField extends QDatabaseFieldBase
    {
        /**
        * Constructor - optionally initializes the member data ..
        *@param object mixFieldData - the object returned by mysql_fetch_field
        *@param QDatabase objDb - database handle ..
        *@param boolean $blnInitialize - if true, initialize internally in constructor
        */
        public function __construct($mixFieldData, $objDb = null, $blnInitialize = true)
        {
            if($blnInitialize)
                $this->Init($mixFieldData, $objDb);
        }
        /**
        * Initializes the member data ..
        * This function exists only to allow QDatabaseResult::FetchFields to work as the
        * constructor here is expected to take parameters and initilize itself ..
        *
        *@param object mixFieldData - the object returned by mysql_fetch_field
        *@param QDatabase objDb - database handle ..
        */
        protected function Init($mixFieldData, $objDb = null)
        {
            $this->strName = $mixFieldData->name;
            $this->strOriginalName = $mixFieldData->orgname;
            $this->strTable = $mixFieldData->table;
            $this->strOriginalTable = $mixFieldData->orgtable;
            $this->blnIdentity = ($mixFieldData->flags & MYSQLI_AUTO_INCREMENT_FLAG) ? true: false;
            $this->blnNotNull = ($mixFieldData->flags & MYSQLI_NOT_NULL_FLAG) ? true : false;
            $this->blnPrimaryKey = ($mixFieldData->flags & MYSQLI_PRI_KEY_FLAG) ? true : false;
            $this->blnUnique = ($mixFieldData->flags & MYSQLI_UNIQUE_KEY_FLAG) ? true : false;
            if (!$this->strOriginalName)
                $this->strOriginalName = $this->strName;

            // Now try to get the rest of the data we need ..
            $strQuery = sprintf("SHOW COLUMNS FROM `%s` WHERE Field = '%s'", $this->strTable, $this->strName);
            
            $objResult = $objDb->Query($strQuery);
            $aryRow = $objResult->FetchArray();
            if(!$aryRow)
                 throw new Exception("Not a valid Column: " . $this->strName);

            $this->strDefault = $aryRow['Default'];
            
            //Try to determine max length (_not_ max found ..)
            $this->intMaxLength = null;
            $strType = $aryRow['Type'];
            // these two types will list possible values in parens, we don't want these ..
            if(false === stripos($strType,'enum') && false === stripos($strType,'set'))
            {
                $intStart = strpos($strType, '(' );
                if(false !== $intStart)
                {
                    $intStart += 1;
                    $intStrLen = strpos($strType,')') - $intStart;
                    $strLength = trim(substr($strType, $intStart, $intStrLen));
                }
            }
            
            if(!empty($strLength) && is_numeric($strLength) )
                $this->intMaxLength = (int) $strLength;

            $this->SetFieldType($mixFieldData->type);
        }
        /**
        * Initializes the data type for this field.
        * This function exists only to allow QDatabaseResult::FetchFields to work as the
        * constructor here is expected to take parameters and initilize itself ..
        *
        *@param integer intMySqlFieldType
        */
        protected function SetFieldType($intMySqlFieldType) {
            switch ($intMySqlFieldType) {
                case MYSQLI_TYPE_TINY:
                    if ($this->intMaxLength == 1)
                        $this->strType = QDatabaseFieldType::Bit;
                    else
                        $this->strType = QDatabaseFieldType::Integer;
                    break;
                case MYSQLI_TYPE_SHORT:
                case MYSQLI_TYPE_LONG:
                case MYSQLI_TYPE_LONGLONG:
                case MYSQLI_TYPE_INT24:
                    $this->strType = QDatabaseFieldType::Integer;
                    break;
                case MYSQLI_TYPE_NEWDECIMAL:
                case MYSQLI_TYPE_DECIMAL:
                case MYSQLI_TYPE_FLOAT:
                    $this->strType = QDatabaseFieldType::Float;
                    break;
                case MYSQLI_TYPE_DOUBLE:
                    // NOTE: PHP does not offer full support of double-precision floats.
                    // Value will be set as a VarChar which will guarantee that the precision will be maintained.
                    //    However, you will not be able to support full typing control (e.g. you would
                    //    not be able to use a QFloatTextBox -- only a regular QTextBox)
                    $this->strType = QDatabaseFieldType::VarChar;
                    break;
                case MYSQLI_TYPE_TIMESTAMP:
                    // System-generated Timestamp values need to be treated as plain text
                    $this->strType = QDatabaseFieldType::VarChar;
                    $this->blnTimestamp = true;
                    break;
                case MYSQLI_TYPE_DATE:
                    $this->strType = QDatabaseFieldType::Date;
                    break;
                case MYSQLI_TYPE_TIME:
                    $this->strType = QDatabaseFieldType::Time;
                    break;
                case MYSQLI_TYPE_DATETIME:
                    $this->strType = QDatabaseFieldType::DateTime;
                    break;
                case MYSQLI_TYPE_TINY_BLOB:
                case MYSQLI_TYPE_MEDIUM_BLOB:
                case MYSQLI_TYPE_LONG_BLOB:
                case MYSQLI_TYPE_BLOB:
                    $this->strType = QDatabaseFieldType::Blob;
                    break;
                case MYSQLI_TYPE_STRING:
                case MYSQLI_TYPE_VAR_STRING:
                    $this->strType = QDatabaseFieldType::VarChar;
                    break;
                case MYSQLI_TYPE_CHAR:
                    $this->strType = QDatabaseFieldType::Char;
                    break;
                case MYSQLI_TYPE_INTERVAL:
                    throw new Exception("Qcodo MySqliDatabase library: MYSQLI_TYPE_INTERVAL is not supported");
                    break;
                case MYSQLI_TYPE_NULL:
                    throw new Exception("Qcodo MySqliDatabase library: MYSQLI_TYPE_NULL is not supported");
                    break;
                case MYSQLI_TYPE_YEAR:
                    $this->strType = QDatabaseFieldType::Integer;
                    break;
                case MYSQLI_TYPE_NEWDATE:
                    throw new Exception("Qcodo MySqliDatabase library: MYSQLI_TYPE_NEWDATE is not supported");
                    break;
                case MYSQLI_TYPE_ENUM:
                    throw new Exception("Qcodo MySqliDatabase library: MYSQLI_TYPE_ENUM is not supported.  Use TypeTables instead.");
                    break;
                case MYSQLI_TYPE_SET:
                    throw new Exception("Qcodo MySqliDatabase library: MYSQLI_TYPE_SET is not supported.  Use TypeTables instead.");
                    break;
                case MYSQLI_TYPE_GEOMETRY:
                    throw new Exception("Qcodo MySqliDatabase library: MYSQLI_TYPE_GEOMETRY is not supported");
                    break;
                default:
                    throw new Exception("Unable to determine MySqli Database Field Type: " . $intMySqlFieldType);
                    break;
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
                    case 'OriginalName':
                        return $this->strOriginalName = QType::Cast($mixValue, QType::String);
                    case 'Table':
                        return $this->strTable = QType::Cast($mixValue, QType::String);
                    case 'OriginalTable':
                        return $this->strOriginalTable = QType::Cast($mixValue, QType::String);
                    case 'Type':
                        return $this->strType = QType::Cast($mixValue, QType::String);
                    case 'Default':
                        return $this->strDefault = QType::Cast($mixValue, QType::String);
                    case 'PrimaryKey':
                        return $this->blnPrimaryKey = QType::Cast($mixValue, QType::Boolean);
                    case 'NotNull':
                        return $this->blnNotNull = QType::Cast($mixValue, QType::Boolean);
                    case 'Timestamp':
                        return $this->blnTimestamp = QType::Cast($mixValue, QType::Boolean);
                    case 'Identity':
                        return $this->blnIdentity = QType::Cast($mixValue, QType::Boolean);
                    case 'Unique':
                        return $this->blnUnique = QType::Cast($mixValue, QType::Boolean);
                    case 'MaxLength':
                        return $this->intMaxLength = QType::Cast($mixValue, QType::Integer);
                    default:
                        return parent::__set($strName, $mixValue);
                }
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
        }
      
	}
       
    class QMySqlIndex extends QDatabaseIndex
    {
        public function __construct($strKeyName, $blnPrimaryKey, $blnUnique, $mixColumnNames)
        {
            $this->strKeyName = $strKeyName;
            $this->blnPrimaryKey = $blnPrimaryKey;
            $this->blnUnique = $blnUnique;
            if(is_array($mixColumnNames))         
                $this->strColumnNameArray = $mixColumnNames;
            else
                $this->strColumnNameArray[] = $mixColumnNames;
        }
        public function AddColumnName($strColumnName)
        {
            if(! in_array($strColumnName, $this->strColumnNameArray))
                $this->strColumnNameArray[] = $strColumnName; 
        }      
    }
?>