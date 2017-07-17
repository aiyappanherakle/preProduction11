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

$ilance->accounting = construct_object('api.accounting');
$ilance->subscription = construct_object('api.subscription');
$ilance->subscription_expiry = construct_object('api.subscription_expiry');

($apihook = $ilance->api('cron_subscriptions_start')) ? eval($apihook) : false;

// expire subscription exemptions
$notice = '';
$notice .= $ilance->subscription_expiry->user_subscription_exemptions();

// expire user subscription plans
// does not include recurring subscription plans as they are handled using a different logic (deleted then recreated based on paypal hitting the ipn script)
$notice .= $ilance->subscription_expiry->user_subscription_plans();

if (empty($notice))
{
        $notice = 'None';
}

($apihook = $ilance->api('cron_subscriptions_end')) ? eval($apihook) : false;

log_cron_action('The following subscription tasks were executed: ' . $notice, $nextitem);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>