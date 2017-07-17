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
$ilance->not_list_hotlist = construct_object('api.not_list_hotlist');
$ilance->not_list_placebid = construct_object('api.not_list_placebid');
// Automatic Lowering Buy Now and Min Bids

$cronlog .= $ilance->not_list_hotlist->not_listed_hotlistcoins();
$cronlog .= $ilance->not_list_placebid->not_listed_coins();


log_cron_action('Automatic Lowering Buy Now and Min Bids was successfully', $nextitem);	
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>