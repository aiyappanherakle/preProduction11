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


($apihook = $ilance->api('cron_dailydeal_start')) ? eval($apihook) : false;

$cronlog='';
$ilance->daily_deals = construct_object('api.daily_deals');
// ADDING DAILYDEAL ITEMS TO LIVE

$cronlog .= $ilance->daily_deals->list_daily_deal();

log_cron_action('The Daily Deal items were listed to live successfully', $nextitem);	
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>