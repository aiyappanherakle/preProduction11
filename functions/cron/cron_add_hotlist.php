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


($apihook = $ilance->api('cron_add_hotlists_coins_start')) ? eval($apihook) : false;

$cronlog='';
$ilance->add_hotlists_coins = construct_object('api.add_hotlists_coins');
// Automatic  Add The Hot List
$cronlog .= $ilance->add_hotlists_coins->add_hotlists();

log_cron_action('Automatic Add The Hot List was successfully executed', $nextitem);	
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>