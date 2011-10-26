<?php

if (!defined("QUASI.CLASS.PHP")){
define("QUASI.CLASS.PHP",1);

    /**
     * Include Quasi configurations - this assumes that this config file is in the same directory, you
     * can adjust this here if you prefer to put either somewhere else.
     */
    require(dirname(__FILE__) . '/quasi_config.php');

    /**
     * Include QApplication, which will in turn pull in the QCodo framework and configuration
     */
    require(__QCODO_ROOT__  . '/includes/prepend.inc.php');
    
   /**
    * Define the autoloader for PHP here
    * NOTE: This overrides the QApplication definition, for this to work you must
    * put a define guard in qcodo.inc.php like this:
    *
    *    if (!defined("QUASICMS")){
    *        function __autoload($strClassName) {
    *            QApplication::Autoload($strClassName);
    *        }
    *    }
    *
    *  The Quasi autoloader looks in Quasi directories and then calls the QApplication autoloader.
    */
    function __autoload($strClassName) {
        Quasi::Autoload($strClassName);
    }

    /**
     * The Quasi class is an abstract class that statically provides global
     * information and global utilities for the entire CMS application. Since
     * it inherits QApplication, it also provides the connection to the QCodo
     * framework.
     *
     * Custom constants for Quasi CMS, as well as global variables and global
     * methods are declared statically here. Additional initializations for the CMS
     * should also be here - but remember, QApplication has already been initialized
     * in prepend.inc.php so do not use parent::
     *
     * This may also be used to override QApplication (eg. for BrowserType ..)
     *
     *@todo  move things from IndexPage to here ..
     *@
     *@package Quasi
     * @subpackage Classes
     */
    abstract class Quasi extends QApplication
    {
        /**
         *  @var string IsSsl true if $_SERVER['HTTPS'] is set, indicating the request was secure
         */
        public static $IsSsl = false;
        /**
         *  @var string ServerName contains $_SERVER['SERVER_NAME']
         */
        public static $ServerName;
        /**
         *  @var string ServerPort contains $_SERVER['SERVER_PORT'], the port webserver listens on
         */
        public static $ServerPort;

        /**
        *  @var array QuasiClasses - a map array of classes to filenames used by the autoloader.
        */
        public static $QuasiClasses = array();
        
        /**
         *  @var array QuasiIncludePaths - a map array of paths to be searched by the autoloader.
         */
        public static $QuasiIncludePaths = array(
            __QUASI_LOCAL_CLASSES__,
            __QUASI_LOCAL_MODULES__,
            __QUASI_LOCAL_ORM__,
            __QUASI_LOCAL_METAORM__,
            __QUASI_CONTRIB_CLASSES__,
            __QUASI_CONTRIB_MODULES__,
            __QUASI_CONTRIB_ORM__,
            __QUASI_CONTRIB_METAORM__,
            __QUASI_CORE_CLASSES__,
            __QUASI_CORE_MODULES__,
            __QUASI_CORE_ORM__,
            __QUASI_CORE_METAORM__,
        );


        public static  $SupportEmailLink;

        /**
         * Initialize Quasi data, setting autoloader data, servername and other misc ..
         */
        public static function Init()
        {
            // set the Form state handler to use SESSION ..
            QForm::$FormStateHandler = 'QSessionFormStateHandler';
            Quasi::$ServerName = $_SERVER['SERVER_NAME'];
            Quasi::$ServerPort = $_SERVER['SERVER_PORT'];
            Quasi::$SupportEmailLink = ' <a href="mailto:' . STORE_EMAIL_ADDRESS . '">' . STORE_EMAIL_ADDRESS . '</a> ';

            $strSsl =  array_key_exists( 'HTTPS', $_SERVER) ? $_SERVER['HTTPS'] : '';
            if(!empty($strSsl))
                Quasi::$IsSsl = true;
                            
            ///@todo make me international ..
            setlocale(LC_MONETARY, 'en_US');
            
            //load an array of filenames for quick autoloading
            foreach( self::$QuasiIncludePaths as $strPath)
            {
                if (is_dir($strPath))
                {
                    if ($dh = opendir($strPath))
                    {
                        while (($strFileName = readdir($dh)) !== false)
                        {
                            $pos = strrpos( $strFileName, '.class.php' );
                            if(false === $pos || true == strpos( $strFileName , '~' ) )
                                continue;
                            $strClassName = substr( $strFileName, 0, $pos );
                            if( ! array_key_exists(strtolower($strClassName), self::$QuasiClasses) )
                                self::$QuasiClasses[strtolower($strClassName)] = $strPath . '/' . $strFileName;
                        }
                        closedir($dh);
                    }
                }
            }
        }

        /**
         * This is called by the PHP5 Autoloader.  This method overrides the
         * one in QApplication - if Quasi fails to load the class, we attempt
         * to load it from QApplication classes here
         * @return void
         */
        public static function Autoload($strClassName)
        {
            //some Qcodo generated QQ classes go in the same file as the ORM class ..
            $aryQcodoPrefixes = array(
                            'QQNode',
                            );
                            
            //work around for QCodo classes in same file ..
            foreach($aryQcodoPrefixes as $strPrefix)
                if( false !== strpos( $strClassName, $strPrefix ) )
                    $strClassName = substr( $strClassName, strlen( $strPrefix ) );
            
            // first check Quasi directories ..
            if(array_key_exists(strtolower($strClassName), Quasi::$QuasiClasses) )
            {
                require_once(Quasi::$QuasiClasses[strtolower($strClassName)]);
                return true;
            }
                            
            // Otherwise use the Qcodo Autoloader
            if (parent::Autoload($strClassName))
                return true;
            return false;
        }

        /**
         * This will redirect the user to a new web location.  This can be a relative or absolute web path, or it
         * can be an entire URL. This overrides the QApplication::Redirect to work for offsite redirects and to
         * support browsers like Opera and Safari that do not accept document.location assigns.
         *
         *  - any string starting with / is assumed to be local.
         *  - any string with http:// or https:// is assumed to be offsite.
         *
         *@todo - support SEO friendly URLS .. and ssl (buggy, needs time ..)
         *
         *@param string strLocation - the URL to which the user is redirected
         * @return void
         */
        public static function Redirect($strLocation, $blnUseSsl=false)
        {
           ob_clean();
           $strProtocol = '';
            
            if($blnUseSsl)
                $strProtocol =  'https://';
            else    
                $strProtocol =  'http://';
                
            if( false !== strpos( $strLocation, 'http://' ) || false !== strpos( $strLocation, 'https://' ) )
            {
/* candidate:
                if (!headers_sent())
                {
                    header('Location: '. $strLocation );
                } else {
                    $strOutPut = '<script type="text/javascript">window.location.href="'. $strLocation . '";</script>';
                    $strOutPut .= '<noscript><meta http-equiv="refresh" content="0;url=' . $strLocation . '" /></noscript>';
                    _p($strOutPut);
                    exit;
                }
*/
                ob_clean();
                header('Location: ' . $strLocation);
                if( Quasi::IsBrowser(QBrowserType::InternetExplorer ) )
                    header('Connection: close');
            }//these two do not support document.location redirects ..??
            elseif( Quasi::IsBrowser( QBrowserType::Opera) || Quasi::IsBrowser( QBrowserType::Safari) )
            {
                ob_clean();
//                header('Location: ' . $strProtocol  . Quasi::$ServerName . $strLocation);
                header('Location: ' . $strLocation);
            }
            else
                parent::Redirect($strLocation);
            exit;
        }
        
        /**
        * Quasi access control
        * Note: this is only a sketch of an idea, in the event of a real access control you will be notified ..
        * ie. THIS DOES NOTHING YET. And it will definitely change.
        *@todo  implement access control
        */
        public static function CheckAccess($aryAllowGroups)
        {
            if(sizeof($aryAllowGroups) == 0)
                return true;
            $blnLoggedIn = false;
            $objAccount = null;
            $objPerson = null;
            $aryUsergroups = array();

            $blnAllow = false;

            if( isset($_SESSION) && isset($_SESSION['AccountLogin']) )
            {
                $objAccount = unserialize($_SESSION['AccountLogin']);
                if( $objAccount instanceof Account )
                {
                    $blnLoggedIn = true;
                    $objPerson = $objAccount->Person;
                }
            }
            if($blnLoggedIn && $objPerson)
                $aryUsergroups = $objPerson->GetUsergroupArray();
            foreach( $aryUsergroups as $objGroup )
                if(in_array($aryAllowGroups, $objGroup->Name ))
                    $blnAllow = true;
            return $blnAllow;
        }
        
    }//end class

    //now initialize Quasi data
    Quasi::Init();
}//end define
   
?>
