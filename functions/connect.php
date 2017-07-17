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

// #### MASTER DB CONFIGURATION (REQUIRED) #####################################
define('DB_DATABASE', DATABASE);
define('DB_SERVER', SERVER);
define('DB_SERVER_PORT', '3306');
define('DB_SERVER_USERNAME', SERVER_USERNAME);
define('DB_SERVER_PASSWORD', SERVER_PASSWORD);
define('DB_PERSISTANT_MASTER', 1);

// #### SLAVE DB CONFIGURATION (OPTIONAL) ######################################
define('DB_SERVER2', 'localhost');
define('DB_SERVER_PORT2', '3306');
define('DB_SERVER_USERNAME2', '');
define('DB_SERVER_PASSWORD2', '');
define('DB_PERSISTANT_SLAVE', 1);


// #### OTHER DB CONSTANTS #####################################################
define('DB_SERVER_TYPE', 'mysqli');
//define('DB_PREFIX', 'ilance_');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_general_ci');

// #### DATABASE CACHING CNFIGURATION ##########################################
// Supported database caching methods:
// none - Do not use caching
// ilance_filecache - To use file based caching in ./cache/datastore/
// ilance_apc - To use APC memory caching servers
// ilance_memcached - To use memcache memory caching servers
define('DB_CACHE', 'none');

// #### DATABASE CACHE PREFIX ##################################################
define('DB_CACHE_PREFIX', 'ilance_');

// #### memcache server 1 ######################################################
$i = 1;
$memcacheserver = array();
$memcacheserver[$i]['server'] = '127.0.0.1';
$memcacheserver[$i]['port'] = '11211';
$memcacheserver[$i]['persistent'] = true;
$memcacheserver[$i]['weight'] = '1'; // give more weight on larger ram servers
$memcacheserver[$i]['timeout'] = '1';
$memcacheserver[$i]['retry'] = '15';
// #### memcache server 2 ######################################################
/*$i++;
$memcacheserver[$i]['server'] = '127.0.0.1';
$memcacheserver[$i]['port'] = '11211';
$memcacheserver[$i]['persistent'] = true;
$memcacheserver[$i]['weight'] = '1';
$memcacheserver[$i]['timeout'] = '1';
$memcacheserver[$i]['retry'] = '15';*/
unset($i);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
