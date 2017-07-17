<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

/**
* force some recommended php.ini configuration values
*/
@set_time_limit(0);
@ini_set('memory_limit', -1);
@ini_set('magic_quotes_gpc', 0);
@ini_set('magic_quotes_runtime', 0);
@ini_set('session.save_handler', 'user');

/**
* function overload makes easy to port ILance supporting only single-byte encoding for multibyte usage
* for example, where substr() is used, mb_substr() will be called automatically instead
*/
if (MULTIBYTE)
{
        // this must be set in php.ini or httpd.conf !!!!
        @ini_set('mbstring.func_overload', 7);
}

/**
* initialize our custom session identifier
*/
session_name('s');

/**
* Load up our function timer class used to get the exact time for scripts to be executed
*/
require_once(DIR_API . 'class.timer.inc.php');

/**
* Function to build an array of information used in ILance for debugging purposes
*
* @param       string       message
* @param       string       debug type (FUNCTION, CLASS, NOTICE, OTHER)
*/
function debug($text = '', $type = 'OTHER')
{
	global $ilance;
	$GLOBALS['DEBUG']["$type"][] = $text;
}

/**
* Function to handle the construction of the registry object datastore
*
* @param       string       class name (2 parts: api.xxx) where api is folder and xxx is class filename to load
* @param       string       class argument set 1 (optional)
* @param       string       class argument set 2 (optional)
* @param       string       class argument set 3 (optional)
* @param       string       class argument set 4 (optional)
* @param       string       class argument set 5 (optional)
* 
* @return      object       Returns our registry object
*/  
function construct_object($classname, $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = '')
{
        global $ilance;
        
        $timer = new timer;
        
        if (empty($classname))
        {
                return false;
        }
        
        $parts = explode('.', $classname);
        $classtitle = $parts[1];
               
        if (empty($function->$classtitle))
        {
                $timer->start();
                require_once(DIR_FUNCTIONS . $parts[0] . '/class.' . $classtitle . '.inc.php');
                $timer->stop();
                
                DEBUG("$classtitle loaded in " . $timer->get() . " seconds", "CLASS");
        }
        
        $object = new $classtitle($param1, $param2, $param3, $param4, $param5);
        
        return $object;
}

/**
* Function to handle the construction of the data manager object datastore
*
* @param       string       data manager name
* @param       object       ilance data object
* 
* @return      object       Returns our registry object
*/
function &construct_dm_object($manager = 'email', &$registry)
{
        $class = "datamanager_$manager";
        
        DEBUG("$class", "CLASS");
        
        $accepted_dms = array(
                'auction',
                'email',
                'users'
        );
        
        if (!is_object($registry))
        {
                return false;
        }
        
        if (!empty($class) AND in_array($manager, $accepted_dms))
        {
                require_once(DIR_API . 'class.datamanager.inc.php');
                require_once(DIR_API . 'class.' . $class . '.inc.php');
                
                $object = new $class($registry);
        
                return $object;
        }
        
        return false;
}

/**
* some hosts do not have this function which is required for the bulk uploader component.
*/
if (!function_exists('str_getcsv'))
{ 
	function str_getcsv($input, $delimiter = ",", $enclosure = '"', $escape = "\\")
	{ 
		$fiveMBs = 5 * 1024 * 1024; 
		$fp = fopen("php://temp/maxmemory:$fiveMBs", 'r+'); 
		fputs($fp, $input); 
		rewind($fp); 
		$data = fgetcsv($fp, 1000, $delimiter, $enclosure);
		fclose($fp); 
		return $data; 
	} 
}

/**
* important input filter handling constants
*/
define('TYPE_INT', 1);
define('TYPE_NUM', 2);
define('TYPE_STR', 3);
define('TYPE_NOTRIM', 4);
define('TYPE_NOHTML', 5);
define('TYPE_BOOL', 6);
define('TYPE_ARRAY', 7);

/**
* print a stop message when our url is being manipulated or malform globals
*/
if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS']))
{
        die('<strong>Fatal error:</strong> Request manipulation attempted.');
}

/**
* Function to emulate a unicode version of htmlspecialchars()
* 
* @param	string	     text to be converted into unicode
* @param        bool         (optional) disable entities? (default true)
*
* @return	string
*/
function htmlspecialchars_uni($text, $entities = true)
{
        return str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), preg_replace('/&(?!' . ($entities ? '#[0-9]+' : '(#[0-9]+|[a-z]+)') . ';)/si', '&amp;', $text));
}

/**
* Function to fetch all $_POST and $_GET recursively
* 
* @param	array	     array
*/
function array_recursive($array)
{
        $html = '';
        foreach ($array AS $key => $value)
        {
                if (is_array($value))
                {
                        $value = array_recursive($value);
                        $html .= "$key=$value&amp;";                        
                }
                else
                {
                        $html .= "$key=$value&amp;";
                }
        }
        
        return $html;    
}

/**
* Function to emulate an expression where the values for true and false are predefined
*
* @param	string	     expression
* @param        string       value to return if expression is true
* @param	string       value to return if expression is false
*
* @return	string
*/
function iif($exp, $rettrue, $retfalse = '')
{
        return ($exp ? $rettrue : $retfalse);
}

                        
/**
* defines if safemode in php is enabled or disabled
*/
define('SAFEMODE', (mb_strtolower(@ini_get('safe_mode')) == 'on' OR @ini_get('safe_mode') == 1) ? true : false);

/**
* fetch ip address of browsing visitor
*/
define('IPADDRESS', fetch_ip_address());

/**
* fetch alternative ip address of browsing visitor from a proxy
*/
define('IPADDRESS_ALT', fetch_proxy_ip_address());

/**
* fetch protocol request method
*/
define('PROTOCOL_REQUEST', ((!empty($_SERVER['HTTPS']) AND ($_SERVER['HTTPS'] == 'on' OR $_SERVER['HTTPS'] == '1')) ? 'https' : 'http'));

/**
* fetch user agent
*/
define('USERAGENT', (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown'));

/**
* fetch referrer
*/
define('REFERRER', (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not referred'));

/**
* this token should not change during a valid session
*/
define('TOKEN', md5(USERAGENT . IPADDRESS . IPADDRESS_ALT));

/**
* defines if we should explain all queries executed by the current script(s) in action
*/
define('DB_EXPLAIN', false);

/**
* defines if we should show the actual database error output to the browser within a textarea field
*/
define('DB_DEBUGMODE', true);

/**
* defines if we should hide the db error information in the textarea but actually show it in the view source as commenting?
*/
define('DB_DEBUGMODE_VIEWSOURCE', true);

/**
* defines if the admincp should check the ilance web site to see if there is a greater version than currently installed
*/
define('VERSIONCHECK', false);

/**
* defines if we should disable any custom api code that might be causing any issues to the framework of ilance
*/
define('DISABLE_PLUGINAPI', false);

/**
* defines if we should show any memory consumption debug information to the browser
*/
define('MEMORY_DEBUG', false);

/**
* defines if we want to output the function debug feature at the footer of the marketplace showing execution times
*/
define('DEBUG_FOOTER', false);

/**
* defines the ability to let admin cp edit all the config settings and option text inline
*/
define('ADMINCP_INLINE', false);

/**
* defines if we're using a www prefix in our domain name
*/
$domainprefix = (mb_ereg('www', HTTP_SERVER)) ? 'www.' : '';

/**
* defines the script url
*/
define('SCRIPT_URI', (!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''));

/**
* defines the server domain name
*/
if (mb_ereg('www', $_SERVER['HTTP_HOST']))
{
	define('DOMAINNAME', $_SERVER['HTTP_HOST']);
}
else
{
	define('DOMAINNAME', $domainprefix . $_SERVER['HTTP_HOST']);
}
unset($domainprefix);

/**
* defines the entire url
*/
if (PROTOCOL_REQUEST == 'https')
{
        define('PAGEURL', 'https://' . DOMAINNAME . SCRIPT_URI);
}
else
{
        define('PAGEURL', 'http://' . DOMAINNAME . SCRIPT_URI);
}

/**
* initialize our ilance registry object
*/
$ilance = construct_object('api.ilance');

/**
* initialize our timer object so we know how long our functions take to execute
*/
$ilance->timer = new timer;

/**
* initialize and connect to our datastore
*/
require_once(DIR_API . 'class.db.inc.php');

// #### prepare caching engine #################################
$accepted = array('none','ilance_filecache','ilance_apc','ilance_memcached');
$cache_engine = DB_CACHE;
if (in_array($cache_engine, $accepted))
{
	if ($cache_engine != 'none')
	{
		$ilance->cache = new $cache_engine;
	}
	else
	{
		$ilance->cache = new ilance_nocache;
	}
}
else
{
	echo '<b>Fatal Error:</b> Cache engine selected within connection defaults does not exist.';
	exit();
}

/**
* set the payment gateway transaction log
*/
define('PAYMENTGATEWAYLOG', DIR_TMP . 'gateway.log');

/**
* set the curl path and application
*/
define('CURLPATH', '/usr/local/bin/curl');

/**
* set the curl path to your certification file used for curl operations over ssl
*/
define('CURLCERT', '');

/**
* pre-installation folder checkup
*/
if (@file_exists(DIR_SERVER_ROOT . 'functions/connect.php.new') OR @file_exists(DIR_SERVER_ROOT . 'functions/config.php.new'))
{
	if (IPADDRESS != '127.0.0.1')
	{
		die('<strong>Fatal</strong>: There are pre-installation steps that require your attention.  Please review <a href="install/how-to-install.txt">how-to-install.txt</a> step 3.');
	}
}

/**
* license key post-installation checkup
*/
if ((defined('LICENSEKEY') AND LICENSEKEY == '????') OR (defined('LICENSEKEY') AND LICENSEKEY == '') OR (!defined('LICENSEKEY')))
{
	if (IPADDRESS != '127.0.0.1')
	{
		die('<strong>Fatal</strong>: License key was not entered correctly within the config.php file.');
	}
}

/**
* used when installing the software - DO NOT REMOVE!
*/
if (defined('LOCATION') AND LOCATION == 'installer')
{
        // because we are installing a fresh copy, we don't have a default template folder
        $ilconfig = array();
        $ilconfig['template_folder'] = 'templates/default/';        
        return;
}

/**
* installation folder protection
*/
if (@file_exists(DIR_SERVER_ROOT . 'install/installer.php'))
{
	if (IPADDRESS != '127.0.0.1')
	{
		die('<strong>Fatal</strong>: The installation folder still exists.  Please remove or at least rename the installer script within the installation folder.');
	}
}

/**
* create our initial $ilconfig configuration datastore
*/
$ilconfig = $ilance->init_configuration();

/**
* initialize our encryption engine
*/
$ilance->crypt = construct_object('api.crypt');

/**
* initialize common functions and routines
*/
$ilance->common = construct_object('api.common');

/**
* initialize our sessions engine
*/
$ilance->sessions = construct_object('api.sessions', $ilance);

/**
* initialize our date and time localisation settings
*/
$ilance->datetime = construct_object('api.datetimes');

/**
* initialize our currency engine
*/
$ilance->currency = construct_object('api.currency');

/**
* initialize our template engine
*/
$ilance->template = construct_object('api.template', $ilance);

/**
* initialize our language engine
*/
$ilance->language = construct_object('api.language');

/**
* initialize our category engine
*/
$ilance->categories = construct_object('api.categories');

/**
* initialize our category parser engine
*/
$ilance->categories_parser = construct_object('api.categories_parser');


//$ilance->ip2country = construct_object('api.ip2country');
/**
* read from our crawlers.xml and detect if visitor is a search crawler
*/
$show['searchengine'] = is_search_crawler();

/**
* if we're on linux, detect the server load limit
*/
$show['serveroverloaded'] = init_server_overload_checkup();
$show['ADMINCP_TEST_MODE'] = ADMINCP_TEST_MODE;

($apihook = $ilance->api('init_configuration_end')) ? eval($apihook) : false;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
