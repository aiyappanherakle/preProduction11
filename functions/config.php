<?php
error_reporting(E_ALL);

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
define('LICENSEKEY', '=asdasdasd');
include_once('conf.php');

$path1=dirname($_SERVER['SCRIPT_FILENAME']);
define('SETUP_SERVER', $_SERVER['HTTP_HOST'].'/'.SUB_FOLDER_ROOT);
define('HTTP_SERVER', 'http://'.SETUP_SERVER);
define('HTTPS_SERVER', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')?'https://'.SETUP_SERVER:'http://'.SETUP_SERVER); 
//define('DIR_SERVER_ROOT',  (defined('LOCATION') && LOCATION=='admin')?dirname($_SERVER['SCRIPT_FILENAME']).'/../':dirname($_SERVER['SCRIPT_FILENAME']).'/' );


/**
* Marketplace identifier id
*/
define('SITE_ID', '001');
//

if(SETUP_SERVER==='www.greatcollections.com')
define('ENVIROMNENT', 'production');
else
define('ENVIROMNENT', 'testing');

/**
* Folder name settings
*/
define('DIR_FUNCT_NAME', 'functions');
define('DIR_ADMIN_NAME', 'admincp');
define('DIR_ADMIN_ADDONS_NAME', 'addons');
define('DIR_CORE_NAME', 'core');
define('DIR_CRON_NAME', 'cron');
define('DIR_TMP_NAME', 'cache');
define('DIR_API_NAME', 'api');
define('DIR_XML_NAME', 'xml');
define('DIR_UPLOADS_NAME', 'uploads');
define('DIR_ATTACHMENTS_NAME', 'attachments');
define('DIR_FONTS_NAME', 'fonts');
define('DIR_SOUNDS_NAME', 'sounds');
define('DIR_LIVEBID_NAME', 'livebid');
define('DIR_SWF_NAME', 'swf');
/**
* Define if we're using the stock header bit nav
*/
define('SEARCHBOXHEADER', 1);
/**
* SMTP settings
define('SMTP_ENABLED', 0);
define('SMTP_USE_TLS', 0);
define('SMTP_HOST', '');
define('SMTP_PORT', '25');
define('SMTP_USER', '');
define('SMTP_PASS', '');
*/
define('SMTP_ENABLED', 1);
define('SMTP_USE_TLS', 1);
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 25);
define('SMTP_USER', 'web-send@greatcollections.com');
define('SMTP_PASS', '682fix128');
/**
* UPS shipping service api settings
*/
define('UPS_ACCESS_ID', 'XXXXXXXXXXXX');
define('UPS_PASSWORD', 'XXXXXXXXXXXX');
define('UPS_SERVER', 'https://www.ups.com/ups.app/xml/Rate');
/**
* USPS shipping service api settings
*/
define('USPS_LOGIN', 'XXXXXXXXXXXX');
define('USPS_PASSWORD', 'XXXXXXXXXXXX');
define('USPS_SERVER', 'http://production.shippingapis.com/ShippingAPI.dll');
/**
* FedEx shipping service api settings
*/
define('FEDEX_ACCOUNT', 'XXXXXXXXXXXX');
define('FEDEX_ACCESS_ID', 'XXXXXXXXXXXX');
define('FEDEX_SERVER', 'https://gateway.fedex.com/GatewayDC');

chdir(DIR_SERVER_ROOT . DIR_FUNCT_NAME);
require_once('./global.php');
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
