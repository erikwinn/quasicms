<?php

	define('SERVER_INSTANCE', 'dev');

	switch (SERVER_INSTANCE) {
		case 'dev':
		case 'test':
		case 'stage':
		case 'prod':
			define ('__DOCROOT__', '/var/www');
            define ('__SUBDIRECTORY__', '/quasi');
            define ('__VIRTUAL_DIRECTORY__', '');

			define('DB_CONNECTION_1', serialize(array(
				'adapter' => 'MySqli5',
				'server' => 'localhost',
				'port' => null,
				'database' => 'quasicms',
				'username' => 'quasidbu',
				'password' => 'quasidbp',
				'profiling' => false)));
			break;
	}

	define('ALLOW_REMOTE_ADMIN', true);
	define ('__URL_REWRITE__', 'none');
    
	define ('__DEVTOOLS_CLI__', __DOCROOT__ . __SUBDIRECTORY__ . '/../_devtools_cli');
	define ('__INCLUDES__', __DOCROOT__ .  __SUBDIRECTORY__ . '/includes');
	define ('__QCODO__', __INCLUDES__ . '/qcodo');
	define ('__QCODO_CORE__', __INCLUDES__ . '/qcodo/_core');
    
    define ('__DEVTOOLS__', __SUBDIRECTORY__ . '/_devtools');

/*
    define ('__DATA_CLASSES__', __INCLUDES__ . '/data_classes');
    define ('__DATAGEN_CLASSES__', __INCLUDES__ . '/data_classes/generated');
    define ('__DATA_META_CONTROLS__', __INCLUDES__ . '/data_meta_controls');
    define ('__DATAGEN_META_CONTROLS__', __INCLUDES__ . '/data_meta_controls/generated');
    define ('__FORM_DRAFTS__', __SUBDIRECTORY__ . '/drafts');
    define ('__PANEL_DRAFTS__', __SUBDIRECTORY__ . '/drafts/dashboard');
*/

/**
* The generator directories are defined so that it is easy to have a different local generation.
* All basic functionality for Quasi's ORM layer is under core/orm/ while the generated base classes
* are in generated. Thus, Quasi ORM can be under version control while generated classes
* are not, allowing for local versions of base classes that may have relationships due to local
* database schema changes or extensions. You can move subclassed ORM and MetaControls 
* classes to the directories under local after generation and they will be found by the
* Quasi autoloader (Note: currently if you move the generated base class you will need to modify
* the require statements to suit, if you leave them in generated/ they will function as is.).
* This way local and core code can be under different version control without conflict - generated/
* classes are not under version control.
*/

    /// QUASIBASE defined here in case we are running code generation (ie. without Quasi)
    /// QUASIBASE should be the same as QUASI_ROOT ..
    define ('__QUASIBASE__',  __DOCROOT__ . __SUBDIRECTORY__ );
    /// ORM Classes and MetaControls _all_ go under core/orm 
    define ('__DATA_CLASSES__',  __QUASIBASE__ . '/core/orm');
    define ('__DATA_META_CONTROLS__', __QUASIBASE__ . '/core/meta_controls');
    /// Generated base classes and drafts go under generated/
    define ('__DATAGEN_CLASSES__', __QUASIBASE__ . '/generated/orm');
    define ('__DATAGEN_META_CONTROLS__', __QUASIBASE__ . '/generated/meta_controls');
    ///
    define ('__PANEL_DRAFTS__', __SUBDIRECTORY__ . '/generated/panels');
    define ('__FORM_DRAFTS__', __SUBDIRECTORY__ . '/generated/forms');

	// We don't want "Examples", and we don't want to download them during qcodo_update
	define ('__EXAMPLES__', null);

	define ('__JS_ASSETS__', __SUBDIRECTORY__ . '/assets/js');
	define ('__CSS_ASSETS__', __SUBDIRECTORY__ . '/assets/css');
	define ('__IMAGE_ASSETS__', __SUBDIRECTORY__ . '/assets/images');
	define ('__PHP_ASSETS__', __SUBDIRECTORY__ . '/assets/php');

	if ((function_exists('date_default_timezone_set')) && (!ini_get('date.timezone')))
		date_default_timezone_set('America/Los_Angeles');

	define('ERROR_PAGE_PATH', __PHP_ASSETS__ . '/_core/error_page.php');
//	define('ERROR_LOG_PATH', __INCLUDES__ . '/error_log');

//	define('ERROR_FRIENDLY_PAGE_PATH', __PHP_ASSETS__ . '/friendly_error_page.php');
//	define('ERROR_FRIENDLY_AJAX_MESSAGE', 'Oops!  An error has occurred.\r\n\r\nThe error was logged, and we will take a look into this right away.');
?>