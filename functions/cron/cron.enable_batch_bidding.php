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
//require_once('../config.php');
$ilance->enable_batch_bidding = construct_object('api.enable_batch_bidding');

$cronlog = '';

($apihook = $ilance->api('cron_enable_batch_bidding')) ? eval($apihook) : false;


$cronlog .= $ilance->enable_batch_bidding->enable_batch_bidding_users();

if (!empty($cronlog))
{
        $cronlog = mb_substr($cronlog, 0, -2);
}

($apihook = $ilance->api('cron_enable_batch_bidding')) ? eval($apihook) : false;

log_cron_action('The weekly tasks executed the following events: ' . $cronlog, $nextitem);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
