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



require_once('../config.php');
$ilance->admincp = construct_object('api.admincp');
$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->workspace = construct_object('api.workspace');
$ilance->subscription = construct_object('api.subscription');
$ilance->escrow = construct_object('api.escrow');

$cronlog = '';




$cronlog .= 'Reset the bids today field for all users back to zero, ';

$cronlog .= $ilance->subscription->send_category_notification_subscriptionsssss();

echo 'sent mails';exit;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>