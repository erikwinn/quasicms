<?php

require_once('QuasiMysql.class.php');

/**
* QuasiDBI - abstract database interface
*  This class provides a base class for various
* server types (MySQL, PgSQL, SQLite) which
* are implemented by inheritors (as of 2008-02-20 only
* MySQL is supported.). It is a Singleton class to
* ensure that we only instantiate one database
* connection - this means that you may only access
* it using QuasiDBI::getInstance(). which will
* return an object of type QuasiDBI that is a child
* corresponding to the configured db server type.
*
* This class is provided as a light weight alternative
* to the QCodo DBI (and may one day replace it..) for
* quick/utility queries - eg. it is used by the OsCommerce
* import module.
* 
* @author Erik Winn <ewinn@erikwinn.com>
*
* $Id: QuasiDBI.class.php 97 2008-08-29 21:36:11Z erikwinn $
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


abstract class QuasiDBI {

  const BADNESS = 'LOAD_FILE|OUTFILE|DUMPFILE|ESCAPED|TERMINATED|CASCADE|INFILE|X509|TRIGGER|REVOKE';
  protected $strDatabase;
  protected $strUsername;
  protected $strPassword;
  protected $strDatabaseServer;
  protected $strDatabaseType;
  protected static $objDatabaseHandle;

  protected static $blnIsConnected = false;

  protected $objResultSet;
  protected $strDbErrors;
  protected $strErrors;

  private static $_instance = null;

  protected function __construct($strDb=DB_DATABASE,
                                                    $strUser=DB_SERVER_USERNAME,
                                                    $strPass=DB_SERVER_PASSWORD,
                                                    $strServer=DB_SERVER)
  {
    $this->strDatabase = $strDb;
    $this->strUsername = $strUser;
    $this->strPassword = $strPass;
    $this->strDatabaseServer = $strServer;

    if( !$this->connect())
      $strErrors .= "QuasiDBI::__construct Failed to connect to database!\n";
  }
  

  /** getInstance()
* Returns an appropriate DBI object - you may provide the
* parameter $type to obtain a driver for a different kind
* of server - currently the default is MySQL
*
* Note: see class description for supported $type parameters ..
*
*@access public
*@param string db server type
*@return object QuasiDBI of appropriate type
*/

public static function getInstance($type="MySQL")
{
    if(!isset(self::$_instance))
    {
        switch($type)
        {
            case "MySQL":
                self::$_instance = new QuasiMysql();
                break;
            default:
            // for now do nothing - maybe set instance to null for unsupported types and die(errormsg) ..
        }
    }
    
    // ensure a current connection ..
    if(! self::$_instance->blnIsConnected)
        self::$_instance->connect();

    return self::$_instance;
}

public function isConnected(){ return $this->blnIsConnected;}

// Accessors ..
public function getServerType() {return $this->strDatabaseType;}
public function setServerType($t) {$this->strDatabaseType = $t;}

public function getServerName() {return $this->strDatabaseServer;}
public function setServerName($n) {$this->strDatabaseType = $n;}

public function getDbUser() {return $this->strUsername;}
public function setDbUser($u) {$this->strUsername = $u;}

public function setDbPassword($p) {$this->strPassword = $p;}
public function getDbPassword() {return $this->strPassword;}

public function getDbName() {return $this->strDatabase;}
public function setDbName($n) {$this->strDatabase = $n;}


public function getErrors() {return $this->strErrors . "\n" . $this->getDbError();}

public function reconnect($strNewHost = DB_SERVER,
                        $strNewDatabase     = DB_DATABASE,
                        $strNewUsername   = DB_SERVER_USERNAME,
                        $strNewPassword   = DB_SERVER_PASSWORD)
{
    $this->disconnect();
    $this->blnIsConnected = false;
    $this->setServerName($strNewHost);
    $this->setDbName($strNewDatabase);
    $this->setDbUser($strNewUsername);
    $this->setDbPassword($strNewPassword);

    if( ! $this->connect())
    return false;

    $this->blnIsConnected = true;
    return true;
}

  // Utilities ..
  
/** prepInput()
* Returns string(s) suitable for database input with escaped
* single quotes, slashes, etc.. Note: this is only accessable via
* child classes - and used only if there is no valid objDatabaseHandle ..
* which is unlikely and this may dissappear in future..
*
*@access protected
*@param mixed mixInput string or array to process
* @return string the prepared string
*/
    protected function prepInput($mixInput)
    {
        if(is_string($mixInput))
        {
            if( get_magic_quotes_gpc() )
               return $mixInput;
            return addslashes($mixInput);
        }
        elseif (is_array($mixInput))
        {
            foreach($mixInput as $key => &$value)
                $mixInput[$key] = $this->prepInput($value);
            return $mixInput;
        } else 
            return $mixInput;
    }

    public function isBad($strQuery)
    {
        if(eregi(self::BADNESS, $strQuery))
            return true;
        return false;
    }

    /** fetchRow()
    * Returns the first row of results for a given query.
    *
    *@access public
    *@param string $strQuery query to process.
    * @return array results
    */

    public function fetchRow ($strQuery)
    {
        $resultset = $this->doQuery($strQuery, true);
        return $this->nextRow($resultset);
    }

    /** getResultSet()
    * Returns a new result set for a given query, or the current
    * resultset internal to this class if not called with a query
    * string.
    * 
    * This is essentially equivalent to doQuery($string, true).
    *
    *@access public
    *@param string strQuery query to process.
    * @return mixed mysqli_result or false
    */

    public function getResultSet ($strQuery)
    {
        // Did we get a strQuery?  If so, get new results and pass them on...
        if ($strQuery)
            return $this->doQuery($strQuery, true);
    }

    // Abstract public functions .. see child class for more complete documentation.

    // connect to server
    abstract public function connect();

    // close connection
    abstract public function disconnect();

    // return last insert id
    abstract public function getInsertId();

    //send a query, return resultset handle or false on failure.
    abstract public function doQuery($strQuery, $blnReturnResultSet=false, $blnUnbuffered=true);

    // fetch the next row as an associative array, optionally supply result set to use, return 0 after last row.
    abstract public function nextRow($objResultSet=null, $blnReturnAssocArray = true);

    // return a string containing the error from the last query.
    abstract public function getDbError();

    // perform a simple insert or update using an array of values with optional where clause
    abstract public function insertArray($strTable, $aryValues, $strAction = "INSERT", $strWhereClause = "");

    // return number of rows in last result set, passing the result set is optional,
    // but _must_ be used in the case that you are using doQuery($q,true)
    abstract public function getNumRows($objResultSet=NULL);


    abstract public function changeDatabase($strNewDatabase);

}

?>
