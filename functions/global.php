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
define('ILANCEVERSION', '3.2.0'); // this should match installer.php
define('VERSIONSTRING', str_replace('.', '', ILANCEVERSION));
define('SVNVERSION', '1352');
$new_page=1;
$buildversion = SVNVERSION;
/**
 * Define our line-break pattern (Windows: \r\n or Linux: \n)
 */
define('LINEBREAK', "\r\n");
/**
 * defines if we have multibyte encoding available
 */
define('MULTIBYTE', (extension_loaded('mbstring') AND function_exists('mb_detect_encoding')) ? true : false);
/**
 * Initialize our $show array for template conditionals
 */
$show = array();
/**
 * Begin $myapi array so that developers can use {myapi[xxx]} in templates
 * by assigning them like $myapi['xxx'] = 'something'; in the php
 */
$myapi = array();
/**
 * Supported regions to display on this site
 */
$ilregions = array(
	'africa' => false,
	'antarctica' => false,
	'asia' => false,
	'europe' => false,
	'north_america' => true,
	'oceania' => false,
	'south_america' => false,
);
/**
 * Paths
 */
require_once './paths.php';
/**
 * Begin our HTML onload document body placeholder
 */
$onload = '';
/**
 * Initialize core functions
 */
require_once DIR_FUNCTIONS . 'connect.php';
require_once DIR_CORE . 'functions.php';
require_once DIR_CORE . 'functions_seo.php';
require_once DIR_CORE . 'functions_email.php';
require_once DIR_FUNCTIONS . 'init.php';
/**
 * Used only when installing the software.
 */
if (defined('LOCATION') AND LOCATION == 'installer') {
	// define templates folder based on the style currently selected
	define('DIR_TEMPLATES', DIR_SERVER_ROOT . 'templates/');
	return;
}
/**
 * Initialize session datastore
 */
session_start();
/**
 * Determine and handle a user selected language or style switch
 * This function also sets the default currency for the visitor
 */
$ilance->sessions->handle_language_style_changes();
if (isset($_SESSION['referrer1'])) {
	$_SESSION['referrer2'] = $_SESSION['referrer1'];
}

if (isset($_SERVER['HTTP_REFERER'])) {
	$_SESSION['referrer1'] = $_SERVER['HTTP_REFERER'];
}

/**
 * This will remember users that have selected to be rememberd
 * It basically checks and reads $_COOKIE['userid'], $_COOKIE['username'] and $_COOKIE['password']
 * and when found, attempts to auto-login and set proper user sessions
 */
$ilance->sessions->init_remembered_session();
if (isset($ilance->GPC['referal_name'])) {
	$_SESSION['ilancedata']['user']['referal_name'] = $ilance->GPC['referal_name'];
//New Change on Dec-29 for Referal Name
	update_referal_count($ilance->GPC['referal_name']);
}
/**
 * Initialize locale environment (helpful for languages other than english)
 */
$locale = $ilance->fetch_language_locale($_SESSION['ilancedata']['user']['languageid']);
setlocale(LC_TIME, $locale['locale']);
unset($locale);
$ilconfig['official_time'] = print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], true, false);
$ilconfig['template_charset'] = $ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset'];
$ilconfig['template_languagecode'] = $ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['languageiso'];
$ilconfig['template_textdirection'] = $ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['textdirection'];
/**
 * Handle cookies and template collapsable expand/deflate icons
 * For each cookie identifier in the list, this code will hide those elements
 */
$ilcollapse = array();
if (!empty($_COOKIE[COOKIE_PREFIX . 'collapse'])) {
	$cookiedata = explode('|', $_COOKIE[COOKIE_PREFIX . 'collapse']);

	// #### begin hiding table rows and set our icons to collapsed state
	foreach ($cookiedata AS $cookiekey) {
		$ilcollapse["collapseobj_$cookiekey"] = 'display: none;';
		$ilcollapse["collapseimg_$cookiekey"] = '_collapsed';
	}
}
unset($cookiedata, $cookiekey);
/**
 * Handle cookies and template collapsable deflate boxes
 * For each cookie identifier in the list, this code will show those elements
 */
if (!empty($_COOKIE[COOKIE_PREFIX . 'deflate'])) {
	$cookiedata = explode('|', $_COOKIE[COOKIE_PREFIX . 'deflate']);
	foreach ($cookiedata AS $cookiekey) {
		$ilcollapse["collapseobj_$cookiekey"] = 'display: inline;';
		$ilcollapse["collapseimg_$cookiekey"] = '_collapsed';
	}
}
unset($cookiedata, $cookiekey);
// #### handle updating of the region cookie so we can remember guests for a year
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'saveregion') {
	// check if a region along with a country id is selected: example: europe.219
	if (!empty($ilance->GPC['region']) AND strrchr($ilance->GPC['region'], '.')) {
		set_cookie('region', handle_input_keywords($ilance->GPC['region']), true);
	}

	// check if user supplied us with a zip code
	if (!empty($ilance->GPC['radiuszip'])) {
		set_cookie('radiuszip', handle_input_keywords(format_zipcode($ilance->GPC['radiuszip'])), true);
	}
}
($apihook = $ilance->api('global_start')) ? eval($apihook) : false;
/**
 * Initialize language phrases datastore
 */

$phrase = $ilance->language->init_phrases();
/**
 * Initialize main breadcrumb phrases
 */
$ilcrumbs = fetch_breadcrumb_titles();
/**
 * Initialize styles and template variables backend
 */
$ilance->styles = construct_object('api.styles');
// this style may require a different template folder for html files
define('DIR_TEMPLATES', DIR_SERVER_ROOT . $ilconfig['template_folder']);
define('DIR_TEMPLATES_ADMIN', DIR_SERVER_ROOT . $ilconfig['template_folder'] . 'admincp/');
//arsath add for template adminstuff on 18/09/2010
define('DIR_TEMPLATES_ADMINSTUFF', DIR_SERVER_ROOT . $ilconfig['template_folder'] . 'staff/');
//bug1736 starts// added for asst folder by tamil on 22/08/2012
define('DIR_TEMPLATES_ADMIN_ASST', DIR_SERVER_ROOT . $ilconfig['template_folder'] . 'asst/');
define('DIR_TEMPLATES_ADMIN_ASST1', DIR_SERVER_ROOT . $ilconfig['template_folder'] . 'asst1/');
//bug1736 ends
/*
 * Initialize ip banning backend
 */
$ilance->ipban = construct_object('api.ipban');
/**
 * Initialize admin control panel breadcrumb and login info
 */
if (defined('LOCATION') AND LOCATION == 'admin') {
	$ilance->admincp = construct_object('api.admincp');

	$login_include_admin = $ilance->common->admin_login_include();
	$ilanceversion = $ilance->admincp->print_version();
}
/**
 * Initialize client breadcrumb and login info
 */
if (defined('LOCATION') AND LOCATION == 'registration') {
	$login_include = $phrase['_registration'] . '...';
	if (!empty($_SESSION['ilancedata']['user']['builduser'])) {
		$login_include = $ilance->common->login_include();
	}
} else {
	$login_include = $ilance->common->login_include();
}
/*
//login redirect
if(strpos(SCRIPT_URI,'beta'))
$script_page_uri=substr(SCRIPT_URI,strpos(SCRIPT_URI,'beta')+5);
else
 */
$script_page_uri = SCRIPT_URI;
$log_red = urlencode(strip_tags($script_page_uri));
/**
 * Initalize our category popup menu on category nav hover state
 */
$categorypopup = '';
if ((defined('SKIP_SESSION') AND SKIP_SESSION == false) OR !defined('SKIP_SESSION')) {
	$categorypopup = (defined('LOCATION') AND (LOCATION != 'admin' AND LOCATION != 'cron' AND LOCATION != 'attachment' AND LOCATION != 'pmb' AND LOCATION != 'stylesheet' AND LOCATION != 'upload' AND LOCATION != 'ipn') AND $ilconfig['categorylinkheaderpopup'])
	? $ilance->categories_parser->print_popupintop_nav() : '';
	$motd_list = construct_motd_list();
}

/**
 * Initalize our category array
 */
$ilance->categories->cats = array();
/**
 * Initalize referral system tracker
 */
init_referral_tracker();
/**
 * Initalize multibyte character encoding
 */
if (MULTIBYTE) {
	mb_internal_encoding($ilconfig['template_charset']);
}
($apihook = $ilance->api('global_end')) ? eval($apihook) : false;
header('Content-type: text/html; charset="' . $ilconfig['template_charset'] . '"');
header('Cache-control: private');
//karthik on may16 for pending invoice
if (isset($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid'] > 0 AND !(defined('SKIP_SESSION'))) {
	// $sql1 = "
				// SELECT user_id
				// FROM " . DB_PREFIX . "users
				// WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				// AND username = '" . $_SESSION['ilancedata']['user']['username'] . "'
				// AND status = 'active'";
	// $sql_query1 = $ilance->db->query($sql1);
	// if ($ilance->db->num_rows($sql_query1) == 0) {

		// $msg1 = "

		// userid tresspassing had happened
			// <br> User_id = " . $_SESSION['ilancedata']['user']['userid'] . "
			// <br> Username = " . $_SESSION['ilancedata']['user']['username'] . "
			// <br> Ipaddress = " . $ilance->db->escape_string(IPADDRESS) . "
			// <br> Page = " . $ilance->db->escape_string($_SERVER['REQUEST_URI']) . "
			// <br> from = " . $ilance->db->escape_string($_SESSION['referrer1']) . "
			// ";

		// unset($_SESSION['ilancedata']['user']['userid']);
		// session_unset();
		// $ilance->sessions->session_destroy(session_id());
		// session_destroy();
		// $ilance->db->dberror($msg1);

		// refresh($ilpage['main']);
		// exit;
	// }

	$sql_query = $ilance->db->query("
				SELECT invoiceid
				FROM " . DB_PREFIX . "invoices
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND status = 'unpaid'
				AND isfvf != 1
				AND isif != 1
				AND isbuyerfee != 1
				AND isenhancementfee != 1
				and not combine_project");

	if ($ilance->db->num_rows($sql_query) > 0) {
		$show['pending_invoice'] = 'show';
	} else {
		$show['pending_invoice'] = '';
	}
}
if (!(defined('SKIP_SESSION'))) {
	if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) {
		$user_id = $_SESSION['ilancedata']['user']['userid'];
	} else {
		$user_id = 0;
	}

	$browser_details = $ilance->common->getBrowser();
	$is_bot = is_search_crawler() != true ? false : true;
	$detailed_sql = "insert into " . DB_PREFIX . "user_activity (user_id,ipaddress,occurrence,url,userAgent,
	browsername,
	version,
	platform,
	referer,
	is_bot) values (
	'" . $user_id . "','" . IPADDRESS . "','" . DATETIME24H . "','" . $ilance->db->escape_string($_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) . "',
	'" . $ilance->db->escape_string($browser_details['userAgent']) . "',
	'" . $ilance->db->escape_string($browser_details['browsername']) . "',
	'" . substr($ilance->db->escape_string($browser_details['version']),0,10) . "',
	'" . $ilance->db->escape_string($browser_details['platform']) . "',
	'" . $ilance->db->escape_string($_SESSION['referrer1']) . "',
	'" . $is_bot . "')";
	unset($user_id);
	unset($browser_details);
	$ilance->db->query($detailed_sql, 0, null, __FILE__, __LINE__);
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
