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

// if (!isset($GLOBALS['ilance']->db))
// {
//         die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
// }
//error_reporting(E_ALL);
require_once('../config.php');

$ilance->feedback = construct_object('api.feedback');
$ilance->auction = construct_object('api.auction');
$ilance->auction_expiry = construct_object('api.auction_expiry');
$ilance->vjsub = construct_object('api.vjsub');

$cronlog = '';

echo '$ilance->vjsub->send_category_notification_subscriptions()';
$cronlog .= $ilance->vjsub->send_category_notification_subscriptions();




echo 'cron start<br/>';exit;
log_cron_action('The auction tasks were successfully executed: ' . $cronlog, $nextitem);
exit;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
