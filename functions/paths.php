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
* base folders
*/
define('DIR_FUNCTIONS', DIR_SERVER_ROOT . DIR_FUNCT_NAME . '/');
define('DIR_CORE', DIR_FUNCTIONS . DIR_CORE_NAME . '/');
define('DIR_API', DIR_FUNCTIONS . DIR_API_NAME . '/');
define('DIR_XML', DIR_FUNCTIONS . DIR_XML_NAME . '/');

/**
* lanceads
*/
define('DIR_ADS', DIR_SERVER_ROOT);
define('HTTP_ADS', HTTP_SERVER);
define('HTTPS_ADS', HTTPS_SERVER);

/**
* lancekb
*/
define('DIR_KB', DIR_SERVER_ROOT . 'kb/');
define('DIR_KB_TEMPLATES', DIR_KB . 'templates/');
define('DIR_KB_FUNCTIONS', DIR_KB . 'functions/');
define('HTTP_KB', HTTP_SERVER . 'kb/');
define('HTTPS_KB', HTTPS_SERVER . 'kb/');

/**
* writable cache folder
*/
define('DIR_TMP', DIR_SERVER_ROOT . DIR_TMP_NAME . '/');
if(!is_writable(DIR_TMP))
	{echo "Cache not writable, ".DIR_TMP;exit;}
/**
* cron job folder
*/
define('DIR_CRON', DIR_FUNCTIONS . DIR_CRON_NAME . '/');

/**
* uploads folder
*/
define('DIR_UPLOADS', DIR_SERVER_ROOT . DIR_UPLOADS_NAME . '/');
define('HTTP_UPLOADS', HTTP_SERVER . DIR_UPLOADS_NAME . '/');
define('HTTPS_UPLOADS', HTTPS_SERVER . DIR_UPLOADS_NAME . '/');

/**
* attachments folder
*/
define('DIR_ATTACHMENTS', DIR_UPLOADS . DIR_ATTACHMENTS_NAME . '/');
define('HTTP_ATTACHMENTS', HTTP_UPLOADS . DIR_ATTACHMENTS_NAME . '/');
define('HTTPS_ATTACHMENTS', HTTPS_UPLOADS . DIR_ATTACHMENTS_NAME . '/');

/**
* mediashare attachment folder
*/
define('DIR_WS_ATTACHMENTS', DIR_ATTACHMENTS . 'ws/');
define('DIR_WS', DIR_SERVER_ROOT . 'ws/');
define('HTTP_WS', HTTP_SERVER . 'ws/');
define('HTTPS_WS', HTTPS_SERVER . 'ws/');
define('DIR_WS_TEMPLATES', DIR_WS . 'templates/');
define('DIR_WS_FUNCTIONS', DIR_WS . 'functions/');

/**
* portfolio attachment folder
*/
define('DIR_PORTFOLIO_ATTACHMENTS', DIR_ATTACHMENTS . 'portfolios/');
define('HTTP_PORTFOLIO', HTTP_ATTACHMENTS . 'portfolios/');
define('HTTPS_PORTFOLIO', HTTPS_ATTACHMENTS . 'portfolios/');

/**
* profile attachment folder
*/
define('DIR_PROFILE_ATTACHMENTS', DIR_ATTACHMENTS . 'profiles/');
define('HTTP_PROFILE', HTTP_ATTACHMENTS . 'profiles/');
define('HTTPS_PROFILE', HTTPS_ATTACHMENTS . 'profiles/');

/**
* auction attachment folder
*/
define('DIR_AUCTION_ATTACHMENTS', DIR_ATTACHMENTS . 'auctions/');
define('HTTP_AUCTION', HTTP_ATTACHMENTS . 'auctions/');
define('HTTPS_AUCTION', HTTPS_ATTACHMENTS . 'auctions/');

/**
* bid attachment folder
*/
define('DIR_BID_ATTACHMENTS', DIR_ATTACHMENTS . 'bids/');
define('HTTP_BID', HTTP_ATTACHMENTS . 'bids/');
define('HTTPS_BID', HTTPS_ATTACHMENTS . 'bids/');

/**
* private message board attachment folder
*/
define('DIR_PMB_ATTACHMENTS', DIR_ATTACHMENTS . 'pmbs/');
define('HTTP_PMB', HTTP_ATTACHMENTS . 'pmbs/');
define('HTTPS_PMB', HTTPS_ATTACHMENTS . 'pmbs/');

/**
* lancekb attachment folder
*/
define('DIR_KB_ATTACHMENTS', DIR_ATTACHMENTS . 'kb/');

/**
* lanceads attachment folder
*/
define('DIR_ADS_ATTACHMENTS', DIR_ATTACHMENTS . 'ads/');

/**
* buynow attachment folder
*/
define('DIR_BUYNOW_ATTACHMENTS', DIR_ATTACHMENTS . 'buynow/');
define('HTTP_BUYNOW', HTTP_ATTACHMENTS . 'buynow/');
define('HTTPS_BUYNOW', HTTPS_ATTACHMENTS . 'buynow/');

/**
* stores attachment folder
*/
define('DIR_STORE_ATTACHMENTS', DIR_ATTACHMENTS . 'stores/');
define('HTTP_STORE', HTTP_ATTACHMENTS . 'stores/');
define('HTTPS_STORE', HTTPS_ATTACHMENTS . 'stores/');

/**
* default admin folders
*/
define('DIR_ADMIN', DIR_SERVER_ROOT . DIR_ADMIN_NAME . '/');
define('HTTP_SERVER_ADMIN', HTTP_SERVER . DIR_ADMIN_NAME . '/');
define('HTTPS_SERVER_ADMIN', HTTPS_SERVER . DIR_ADMIN_NAME . '/');

/**
* fonts folder
*/
define('DIR_FONTS', DIR_FUNCTIONS . DIR_FONTS_NAME . '/');

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>