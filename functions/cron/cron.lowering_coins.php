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


($apihook = $ilance->api('cron_lowering_coin_amount_start')) ? eval($apihook) : false;

$cronlog='';
$ilance->lowering_coin_amount = construct_object('api.lowering_coin_amount');
// Automatic Lowering Buy Now and Min Bids

$cronlog .= $ilance->lowering_coin_amount->lowering_coins();

log_cron_action('Automatic Lowering Buy Now and Min Bids was successfully', $nextitem);	
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>