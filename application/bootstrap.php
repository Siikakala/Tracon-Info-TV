<?php defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/kohana/core'.EXT;

if (is_file(APPPATH.'classes/kohana'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/kohana'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/kohana'.EXT;
}

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Europe/Helsinki');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'fi_FI.utf8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV']))
{
	//Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
	Kohana::$environment = constant('Kohana::PRODUCTION');
}
if (Kohana::$environment === Kohana::PRODUCTION)
{
    // We are live!

    define("__db","prod"); //Käytetään tuotantokantaa

    define("__documentroot","/home/www/info.tracon.fi/"); //ja tuotantohakemistoa.

    // Turn off notices and strict errors
    error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

    //Turn off all errors.
    //error_reporting(0);

}elseif (Kohana::$environment === Kohana::DEVELOPMENT){

    //tehdään juttuja.

    error_reporting(-1);//Let's get errors from everything! Muahahahaa!

    define("__db","dev"); //Käytetään kehitystietokantaa

    define("__documentroot","/var/www/tracon_info-tv/"); //ja kehityshakemistoa.

}
/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array(
	'base_url'   => Kohana::$environment === Kohana::DEVELOPMENT ? '/tracon_info-tv/' : '/tracon_info-tv/',
	'index_file' => false,
	'errors'     => Kohana::$environment === Kohana::DEVELOPMENT,
	'profile'    => Kohana::$environment === Kohana::PRODUCTION,
	'caching'    => Kohana::$environment === Kohana::PRODUCTION
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	 'auth'       => MODPATH.'auth',       // Basic authentication
	 'cache'      => MODPATH.'cache',      // Caching with multiple backends
	 //'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	 'database'   => MODPATH.'database',   // Database access
	 'image'      => MODPATH.'image',      // Image manipulation
	// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
	// 'unittest'   => MODPATH.'unittest',   // Unit testing
	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
	));

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

Route::set('backend', '<controller>(/<page>(/<action>(/<param1>)))',
     array(
         'controller' => 'backend'
     ))->defaults(array(
         'action' => 'check',
     ));

Route::set('admin', '<controller>(/<action>(/<param1>(/<param2>)))',
     array(
         'controller' => 'admin|android'
     ))->defaults(array(
         'action' => 'index'
     ));

Route::set('ajax', 'ajax(/<param1>(/<param2>))')
     ->defaults(array(
         'controller' => 'ajax',
         'action' => 'ajax'
     ));

Route::set('frontend', 'tv(/<id>)')
	->defaults(array(
		'controller' => 'frontend',
		'action'     => 'index',
	));

Route::set('frontpage', '(<id>)')
	->defaults(array(
		'controller' => 'frontend',
		'action'     => 'to_tv',
	));


Route::set('error', 'error/<action>(/<message>)', array('action' => '[0-9]++', 'message' => '.+'))
->defaults(array(
    'controller' => 'errors'
));


define("__title","Höylä");

//ohjelmakarttaprosessointia varten.
define("ALKUAIKA_Lauantai", 10);
define("LOPPUAIKA_Lauantai", 24);
define("ALKUAIKA_Sunnuntai", 0);
define("LOPPUAIKA_Sunnuntai", 18);

// -- URI test bench -----------------------------------------------------------

//comment line bellow and change $uri to relative path what to test.
/*
  $uri = 'backend';
// This will loop trough all the defined routes and
// tries to match them with the URI defined above
foreach (Route::all() as $r)
{
  echo Debug::dump($r->matches($uri));
}
exit;
//*/