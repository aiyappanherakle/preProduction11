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

$ilance->feedback = construct_object('api.feedback');
$ilance->auction = construct_object('api.auction');
$ilance->auction_expiry = construct_object('api.auction_expiry');

$cronlog = '';

($apihook = $ilance->api('cron_rfp_start')) ? eval($apihook) : false;

// #### EXPIRE MARKETPLACE AUCTIONS ############################################
$cronlog .= $ilance->auction_expiry->all();

($apihook = $ilance->api('cron_rfp_end')) ? eval($apihook) : false;

log_cron_action('The auction tasks were successfully executed: ' . $cronlog, $nextitem);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>