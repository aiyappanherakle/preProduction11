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

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

$ilance->db->query("
        UPDATE " . DB_PREFIX . "users
        SET bidsthismonth = '0',
        auctiondelists = '0',
        bidretracts = '0'
", 0, null, __FILE__, __LINE__);

if ($ilconfig['resetpopulartags'])
{
        $ilance->db->query("
                UPDATE " . DB_PREFIX . "search
                SET count = '0'
                WHERE count > " . intval($ilconfig['populartagcount']) . "
        ", 0, null, __FILE__, __LINE__);
}

$ilance->db->query("
        TRUNCATE TABLE " . DB_PREFIX . "shipping_rates_cache
");

($apihook = $ilance->api('cron_monthly')) ? eval($apihook) : false;

log_cron_action('The monthly tasks were executed: Reset Bids / Delists / Retracts for all users', $nextitem);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>