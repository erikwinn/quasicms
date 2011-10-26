<?php

/**
* QuasiMysql - driver class for a MySQL database server.
*   This class inherits QuasiDBI, implementing the actual
* connection and query methods. Do _not_ use this class 
* directly! Use QuasiDBI::getInstance() to retrieve a dbi object
* or QuasiDBI::getInstance()->method().
*
* Note: We use the newer mysqli functions so this requires PHP5
* and MySQL 4.1 and above.
*
* @author Erik Winn <ewinn@erikwinn.com>
*
* $Id: QuasiMysql.class.php 97 2008-08-29 21:36:11Z erikwinn $
*
*@version 0.2
*
* @copyright Erik Winn 2008
* @license GPL v.2
* @class
*/


class QuasiMysql extends QuasiDBI
{
  
/** connect()
* Connects to the db server, setting $objDatabaseHandle to the current link to db
*@access public
* @return boolean true on success or false
*/
  
    public function connect()
    {
        // We don't handle persistent connections.  Yet, at least.
        // if( defined(USE_PCONNECT) && USE_PCONNECT == true );

        $this->objDatabaseHandle = mysqli_init();

        /* If DB_USE_SSL is defined, we attempt to set some parameters.  A client key,
        client certificate, and CA certificate matching the one on the server
        must be available on the local filesystem. */

        if ( defined('DB_USE_SSL') && true === DB_USE_SSL )
            $this->objDatabaseHandle->ssl_set(DB_SSL_KEY, DB_SSL_CERT, DB_SSL_CA_CERT, null, null);

        $this->objDatabaseHandle->real_connect($this->strDatabaseServer,
                                                                            $this->strUsername,
                                                                            $this->strPassword,
                                                                            $this->strDatabase);

        /* make sure we are connected */
        if (mysqli_connect_errno())
        {
            $this->strErrors .= "Connection to database failed in QuasiMysql: " . mysqli_connect_error() . "\n";
            $this->blnIsConnected = false;
            return false;
        }

        $this->blnIsConnected = true;
        return true;
    }

    /** disconnect()
    * Closes current connection.
    *@access public
    * 
    */
    public function disconnect()
    {
        if($this->objDatabaseHandle)
            $this->objDatabaseHandle->close();
        $this->blnIsConnected = false;
    }

    /** getInsertId()
    * Returns the insert id of the most recent insert action.
    *@access public
    * @return integer insert id of last query.
    */
    public function getInsertId()
    {
        if($this->objDatabaseHandle)
            return $this->objDatabaseHandle->insert_id;
        return 0;
    }

    /** doQuery()
    * Send a preformatted (i.e. complete) query to the server.
    * NOTE:With out the second parameter, this stores the result in
    * $objResultSet which we manage internally -- CAUTION: if you
    * intend to conduct new queries in a loop based on data from rows in the
    * first result set, you MUST accept the result set handle and pass
    * it to nextRow or else the rows from the first query _will_ be overwritten!!
    * 
    *@access public
    *@param string strQuery string
    *@param boolean blnReturnResultSet - return a copy of the result set
    *@param boolean blnUnbuffered - use MYSQLI_USE_RESULT, ie. do not buffer results on the server
    * @return mixed mysqli_result on success or false
    */
    public function doQuery($strQuery, $blnReturnResultSet=false, $blnUnbuffered=true )
    {// todo - optionally use MYSQLI_USE_RESULT, set in config?
        if(! $this->objDatabaseHandle)
            return false;
        
        // clean up the string
        if($this->isBad($strQuery))
            return false;

        $objResultSet = $this->objDatabaseHandle->query($strQuery);
        
        if($objResultSet === false)
        { //query of any kind failed ..
        $this->strDbErrors .= $this->objDatabaseHandle->error;
            return false;
        }

        if($blnReturnResultSet)
            return $objResultSet;
        else
            $this->objResultSet =& $objResultSet;
        
        return $this->objResultSet;
    }

    /** nextRow()
    * Fetches the next row of most recent result set as an associative array,
    * where $key = column name and $value = column value.
    * Optionally, you may provide the parameter false to return a simple
    * numerically indexed array of column values.
    *  Returns 0 after last row.
    *
    * You may optionally provide the result set through which to iterate
    * as the first parameter - by default we use the result set internal
    * to this class.
    *
    *@access public
    *@param object $objResultSet - object of type mysqli_result
    *@param boolean $blnReturnAssocArray=true returns an associative array, or false for numerical index keys
    * @return mixed object|integer
    */
    public function nextRow($objResultSet=NULL,$blnReturnAssocArray=true)
    {
        if(!$this->getNumRows() && ! $objResultSet)
            return 0;
        if( ! $objResultSet)
        {
            if($blnReturnAssocArray)
                return $this->objResultSet->fetch_assoc();
            else   // numerical indices ..
                return $this->objResultSet->fetch_array();
        }
        //use provided result set ..
        if($blnReturnAssocArray)
            return $objResultSet->fetch_assoc();
        else   // numerical indices ..
            return $objResultSet->fetch_array();
    }

    /** getDbError()
    *Returns a string containing the db error from the last query
    * including errorno...
    *@access public
    * @return string errors
    */
    public function getDbError()
    {
        //erm, this is confused ..todo: work out sensible error reporting ..
        if($this->objDatabaseHandle->errorno)
            $this->strDbErrors = $this->objDatabaseHandle->error . "\n Error No.:" . $this->objDatabaseHandle->errorno ;
        return  $this->strDbErrors;
    }
    
    /** insertArray
    *  Perform a simple insert or update on a specified table with
    * an array - adds a where clause filter when using UPDATE,
    *  eg. $where = "name like 'joe' ", or $where = " id = $idno ".
    *
    * $aryValues must be an associative array in the format:
    *      'columname' = 'columnvalue'
    * Examples:
        insertArray($tablename, array('col1'=>'bar', 'col2' = 'baz') )
        insertArray($tablename, array('col1'=>'bar', 'col2' = 'baz'), 'update', $where )
    *
    *
    *@access public
    *@param string $strTable - which table to address
    *@param object $aryValues - assoc array of values to insert|update
    *@param string $strAction - may be either INSERT or UPDATE (case insensitive)
    *@param string $where - optional WHERE [conditions]
    *
    * @return boolean true on success or false
    */
    public function insertArray( $strTable, $aryValues, $strAction = "INSERT", $where = '')
    {
        if(!is_array($aryValues) || !isset($aryValues))
        {
            $this->strErrors .= "insertArray called with bad array ..";
            return false;
        }
        $strAction = trim(strtoupper($strAction));
        $numcols = count($aryValues);
        $strQuery = '';
        if($strAction == 'INSERT')
        {
            $strQuery = 'INSERT INTO ' . $strTable;
            $cols = ' (';
            $vals = ' VALUES (';
            $i=0;
            foreach($aryValues as $col => $val)
            {
                $i++;
                $end =  $i < $numcols ? ", " : ") ";
                //if(empty($val))     continue;
                $cols .= $col . $end;
                if(trim(strtoupper($val)) === "NOW()" || trim(strtoupper($val)) === "NULL")
                    $vals .= $val;
                else
                    $vals .= " '" . $val . "' ";
                $vals .= $end;
            }
            $strQuery .= $cols . $vals;
        }
        elseif($strAction == 'UPDATE')
        {
            $strQuery = 'UPDATE ' . $strTable . ' SET ';
            $cols = '';
            $i=0;
            foreach($aryValues as $col => $val)
            {
                $i++;
                $end =  $i < $numcols ? ", " : " ";
                //if(empty($val))  continue;
                $cols .= $col . ' = ';
                if(trim(strtoupper($val)) === "NOW()" || trim(strtoupper($val)) === "NULL")
                    $vals = $val;
                else
                    $vals = " '" . $val . "' ";
                $cols .= $vals . $end;
            }
            $strQuery .= $cols;
        }
        if(!empty($where))
            $strQuery .= ' WHERE ' . $where;

        return $this->doQuery($strQuery);
        
    }

    /** getNumRows() 
    * Returns the number of rows in the most recent result set.
    * Note: if you are using a returned objResultSet from doQuery($strQuery,true)
    * you _must_ provide it as a parameter to get an accurate count.
    *
    *@access public
    *
    *@param object $objResultSet - object of type mysqli_result
    * @return integer number of rows
    */
    public function getNumRows($objResultSet=NULL)
    {
        if($objResultSet)
            return $objResultSet->num_rows;
        
        if(!$this->objDatabaseHandle || !$this->objResultSet)
            return 0;
        return $this->objResultSet->num_rows;
    }

    /** getAffectedRows() 
    * Returns the number of rows affected by the most recent query.
    * Note: if you are using a returned objResultSet from doQuery($strQuery,true)
    * you _must_ provide it as a parameter to get an accurate count.
    *
    *@access public
    *
    *@param object $objResultSet - object of type mysqli_result
    * @return integer number of rows
    */
    public function getAffectedRows($objResultSet=NULL)
    {
        if($objResultSet)
            return $objResultSet->affected_rows;
        
        if(!$this->objDatabaseHandle || !$this->objResultSet)
            return 0;
        return $this->objResultSet->affected_rows;
    }

    
    /** prepInput()
    * Returns a string suitable for database input with escaped
    * single quotes, slashes, etc.. Use this to clean up a string or
    * array of strings from the outside world before constructing
    * a query. Note: do not use this on the whole query esp. if you have
    * single quotes in eg. the where clause - that will cause a mysql error.
    * Note also that we trim whitespace from input string.
    * Example:
    *  $safe = $dbi->prepInput($_GET[unsafe]);
    * $query = 'SELECT foo FROM table WHERE bar LIKE \'' . $safe . '\' [AND other conditions ] ';
    * Wrong:  $dbi->prepare($query);
    * Characters escaped are NUL (ASCII 0), \n, \r, \, ', ", and Control-Z.
    *
    *  !! RUDENESS ALERT !! If input has MySQL injection attempts like LOAD DATA
    * ( see QuasiDBI::BADNESS ) this will return an empty string!!!
    *
    *@access public
    *@param string | array $input string or array to process
    * @return mixed string|array  query safe string or array
    */
    public function prepInput($input)
    {
        if(!$this->objDatabaseHandle ) //shouldn't happen but momma's don't need objDatabaseHandle so ..
        return parent::prepInput($input);
        if(is_string($input))
        {
            // remove magic quotes and let mysqli escape the string below
            if(get_magic_quotes_gpc())
                $t_input = trim(stripslashes($input));
            else
                $t_input = trim($input);
                
            // check for prohibited SQL ..
            if($this->isBad($t_input))
            {//TODO: - error msg ..
                $t_input = "";
                return $t_input;
            }
            return $this->objDatabaseHandle->escape_string( $t_input);
        }
        elseif (is_array($input))
        {
            foreach($input as $key => &$value)
                $input[$key] = $this->prepInput($value);
            return $input;
        } else 
            return $input;
    }
    
    /**changeDatabase
    *Resets the default database for queries and ensures that there
    * is a valid connection. returns false if this fails or if there
    * is not a valid objDatabaseHandle, or if connect fails ..
    *@access public
    *@param string $strNewDatabase - new database name
    * @return boolean true on success or false
    */  
    public function changeDatabase($strNewDatabase)
    {
        if(!$this->objDatabaseHandle )
        {
            $this->strDbErrors .= "Change database called with no objDatabaseHandle!!";
            return false;
        }
        
        $this->setDbName($strNewDatabase);
        if(!$this->isConnected())
            return $this->connect();
        else
            return $this->objDatabaseHandle->select_db($strNewDatabase);
    }

 /**
*just a comment template - ignore at will ..
*@access public
*@param string foo
* @return boolean true on success or false
*/
  
}

?>
