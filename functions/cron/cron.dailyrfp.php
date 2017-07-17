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
$ilance->admincp = construct_object('api.admincp');
$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->workspace = construct_object('api.workspace');
$ilance->subscription = construct_object('api.subscription');
$ilance->escrow = construct_object('api.escrow');

$cronlog = '';

($apihook = $ilance->api('rfp_daily')) ? eval($apihook) : false;

// #### reset daily bid counters for all members ###############################
$ilance->db->query("
        UPDATE " . DB_PREFIX . "users
        SET bidstoday = '0'
", 0, null, __FILE__, __LINE__);

$cronlog .= 'Reset the bids today field for all users back to zero, ';
//new change mar23 for cancel_unlinked_escrow_invoices 
//$cronlog .= $ilance->escrow->cancel_unlinked_escrow_invoices();
$cronlog .= $ilance->workspace->remove_mediashare_content_daily(7);
$cronlog .= $ilance->subscription->send_category_notification_subscriptions();
$cronlog .= $ilance->subscription->send_saved_search_subscriptions(50);
//$cronlog .= $ilance->subscription->expire_saved_search_subscriptions(3600);
$cronlog .= $ilance->subscription->send_subscription_expiry_reminders(7);
$cronlog .= $ilance->bid->wait_approval_unaward_cron();
$cronlog .= $ilance->auction->expire_featured_status_listings();
// $cronlog .= $ilance->admincp->fetch_latest_news(); // not ready yet -Peter
// $cronlog .= $ilance->auction->category_listing_count_fixer(); // not ready yet -Peter

// #### clean outdated log entries after n days as defined in admin cp #########
if ($ilconfig['clean_old_log_entries'] > 0)
{
        $ilance->db->query("
                DELETE FROM " . DB_PREFIX . "emaillog
                WHERE date < DATE_SUB(CURDATE(), INTERVAL $ilconfig[clean_old_log_entries] DAY)
                        AND logtype != 'alert'
        ");
        
        $ilance->db->query("
                DELETE FROM " . DB_PREFIX . "cronlog
                WHERE FROM_UNIXTIME(dateline) < DATE_SUB(CURDATE(), INTERVAL $ilconfig[clean_old_log_entries] DAY)
        ");
        
        $ilance->db->query("
                DELETE FROM " . DB_PREFIX . "failed_logins
                WHERE datetime_failed < DATE_SUB(CURDATE(), INTERVAL $ilconfig[clean_old_log_entries] DAY)
        ");
        
        /*
        $ilance->db->query("
              DELETE FROM " . DB_PREFIX . "invoicelog
              WHERE date_sent < DATE_SUB(CURDATE(), INTERVAL $ilconfig[clean_old_log_entries] DAY)
        ");
        */
        
        $cronlog .= 'Log entries older than ' . $ilconfig['clean_old_log_entries'] . ' days deleted for (email, cron and failed logins), ';
}

if (!empty($cronlog))
{
        $cronlog = mb_substr($cronlog, 0, -2);
}

($apihook = $ilance->api('cron_dailyrfp_end')) ? eval($apihook) : false;

log_cron_action('The daily tasks executed the following events: ' . $cronlog, $nextitem);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>