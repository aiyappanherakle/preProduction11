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

$ilance->portfolio = construct_object('api.portfolio');
$ilance->subscription = construct_object('api.subscription');
$ilance->accounting = construct_object('api.accounting');
$ilance->accounting_reminders = construct_object('api.accounting_reminders');

$cronlog = '';

($apihook = $ilance->api('cron_reminders_start')) ? eval($apihook) : false;

// #### subscription invoice reminders #################################
$cronlog .= $ilance->subscription->send_user_subscription_frequency_reminders();
        
// #### other unpaid invoice reminders #################################
$cronlog .= $ilance->accounting_reminders->send_unpaid_invoice_frequency_reminders();

// #### scheduled subscription invoice cancellations ###########################
$cronlog .= $ilance->subscription->cancel_scheduled_subscription_invoices();

// #### expire verified profile verification credentials #######################
$cronlog .= $ilance->portfolio->expire_verified_profile_credentials();

// #### expire featured portfolio items ########################################
$cronlog .= $ilance->portfolio->expire_featured_portfolios();

($apihook = $ilance->api('cron_reminders_end')) ? eval($apihook) : false;

log_cron_action('The reminder tasks were successfully executed: ' . $cronlog, $nextitem);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>