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

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration',
	'accounting',
	'buying',
	'selling'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[accounting]" => $ilcrumbs["$ilpage[accounting]"]);

$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['accounting']);

if (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
{
	refresh(HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

// #### ESCROW LISTINGS AND MANAGEMENT #########################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'escrow')
{
	$area_title = $phrase['_escrow_management'];
	$page_title = SITE_NAME . ' - ' . $phrase['_escrow_management'];

	($apihook = $ilance->api('admincp_escrow_settings')) ? eval($apihook) : false;
	
	// #### build our subnav menu ##################################
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=escrow', $_SESSION['ilancedata']['user']['slng']);
	
	// #### FORCE CONFIRMATION OF DELIVERY OF BUY NOW ITEMS
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'confirm-buynow-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->escrow = construct_object('api.escrow');
		$ilance->escrow_handler = construct_object('api.escrow_handler');

		$success = $ilance->escrow_handler->escrow_handler('sellerconfirmdelivery', 'buynow', intval($ilance->GPC['id']), false);
		if ($success)
		{
			print_action_success($phrase['_confirmation_of_delivery_has_been_completed_for_this_buy_now_order'], $ilance->GPC['return']);
			exit();
		}
	}
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'cancel-buynow-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->escrow = construct_object('api.escrow');
		$ilance->escrow_handler = construct_object('api.escrow_handler');
		
		$success = $ilance->escrow_handler->escrow_handler('reversal', 'buynow', intval($ilance->GPC['id']), false);
		if ($success)
		{
			print_action_success($phrase['_funds_for_this_buy_now_order_have_been_refunded_to_the_buyer'], $ilance->GPC['return']);
			exit();
		}    
	}
	
	// #### FORCE RELEASE OF BUYNOW FUNDS FROM ESCROW TO PROVIDER
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'force-buynow-release' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->escrow = construct_object('api.escrow');
		$ilance->escrow_handler = construct_object('api.escrow_handler');
		
		$success = $ilance->escrow_handler->escrow_handler('buyerconfirmrelease', 'buynow', intval($ilance->GPC['id']), false);
		if ($success)
		{
			print_action_success($phrase['_funds_for_this_buy_now_order_have_been_released_to_the_seller'], $ilance->GPC['return']);
			exit();
		}  
	}
	
	// #### FORCE REFUND OF FUNDS FROM ESCROW BACK TO PAYER ########
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_force-refund' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['mode']))
	{
		$ilance->escrow = construct_object('api.escrow');
		$ilance->escrow_handler = construct_object('api.escrow_handler');
		
		$success = $ilance->escrow_handler->escrow_handler('refund', $ilance->GPC['mode'], intval($ilance->GPC['id']), false);
		if ($success)
		{
			print_action_success($phrase['_funds_were_debitted_from_the_providers_account_back_into_the_buyers_account_the_escrow_status_for_this_auction_is_pending'], $ilpage['accounting'] . '?cmd=escrow');
			exit();
		}
	}
	
	// #### FORCE ESCROW ACCOUNT CANCELLATION ######################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_force-cancel' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['mode']))
	{
		$ilance->escrow = construct_object('api.escrow');
		$ilance->escrow_handler = construct_object('api.escrow_handler');
		
		$success = $ilance->escrow_handler->escrow_handler('buyercancelescrow', $ilance->GPC['mode'], intval($ilance->GPC['id']), false);
		if ($success)
		{
			print_action_success($phrase['_funds_were_debitted_from_the_providers_account_back_into_the_buyers_account_the_escrow_status_for_this_auction_is_pending'], $ilpage['accounting'] . '?cmd=escrow');
			exit();	
		}
	}
	
	// #### FORCE ESCROW ACCOUNT RELEASE FROM ESCROW TO RECEIVER ###
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_force-release' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['mode']))
	{
		$ilance->escrow = construct_object('api.escrow');
		$ilance->escrow_handler = construct_object('api.escrow_handler');
		
		$success = $ilance->escrow_handler->escrow_handler('buyerconfirmrelease', $ilance->GPC['mode'], intval($ilance->GPC['id']), false);
		if ($success)
		{
			print_action_success($phrase['_from_were_forcefully_moved_from_within_this_escrow_account_to_the_sellers_online_account_balance'], $ilpage['accounting'] . '?cmd=escrow');
			exit();	
		}
	}
	
	$ilance->auction = construct_object('api.auction');
	$ilance->auction_award = construct_object('api.auction_award');
	
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	
	$limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	
	$numberrows = $ilance->db->query("
		SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.fee, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, e.isfeepaid, e.isfee2paid, e.feeinvoiceid, e.fee2invoiceid, b.bid_id, b.user_id as bidder_id, b.bidstatus, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
		FROM " . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "projects_escrow AS e,
		" . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "invoices AS i
		WHERE e.user_id = u.user_id
		    AND e.status != 'cancelled'
		    AND e.bid_id = b.bid_id
		    AND e.user_id = b.user_id
		    AND e.project_id = p.project_id
		    AND e.invoiceid = i.invoiceid
		    AND i.invoicetype = 'escrow'
		    AND p.project_state = 'service'
		    AND i.projectid = e.project_id
	");
	$numberservice = $ilance->db->num_rows($numberrows);
	
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$row_count = 0;
	
	$result = $ilance->db->query("
		SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, p.currencyid, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.fee, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, e.isfeepaid, e.isfee2paid, e.feeinvoiceid, e.fee2invoiceid, b.bid_id, b.user_id as bidder_id, b.bidstatus, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
		FROM " . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "projects_escrow AS e,
		" . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "invoices AS i
		WHERE e.user_id = u.user_id
		    AND e.status != 'cancelled'
		    AND e.bid_id = b.bid_id
		    AND e.user_id = b.user_id
		    AND e.project_id = p.project_id
		    AND e.invoiceid = i.invoiceid
		    AND i.invoicetype = 'escrow'
		    AND p.project_state = 'service'
		    AND i.projectid = e.project_id
		$limit
	");
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			$row['fees'] = ($row['fee'] > 0)
				? print_escrow_fees('as_admin', $row['fee'], $row['project_id'])
				: $phrase['_none'];
				
			$row['fees2'] = ($row['fee2'] > 0)
				? print_escrow_fees('as_admin', $row['fee2'], $row['project_id'])
				: $phrase['_none'];
				
			$row['job_title'] = stripslashes($row['project_title']);
			$row['description'] = short_string(stripslashes($row['description']), 100);
			$row['buyer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id='.$row['project_user_id'].'">' . fetch_user('username', $row['project_user_id']) . '</a>';
			$row['buyer_id'] = $row['project_user_id'];
			$row['provider'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id='.$row['user_id'].'">' . fetch_user('username', $row['user_id']) . '</a>';
			$row['awarddate'] = print_date($row['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
			$row['bidamount'] = $ilance->currency->format($row['bidamount'], $row['currencyid']);
			$row['escrowamount'] = $ilance->currency->format($row['escrowamount'], $row['currencyid']);
			
			if ($row['status'] == 'pending')
			{
				$row['status'] = $phrase['_pending_escrow'];
				$row['actions'] = '<div><input type="button" value="' . $phrase['_cancel'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
			else if ($row['status'] == 'started')
			{
				$row['status'] = '<div class="green">' . $phrase['_funds_secured'] . '</div>';
				$row['actions'] = '<div><input type="button" value="' . $phrase['_cancel'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
			else if ($row['status'] == 'confirmed')
			{
				$row['status'] = '<span style="float:left; padding-right:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow.gif" border="0" alt="' . $phrase['_funds_secured_in_escrow'] . '" /></span>' . $phrase['_pending_release'];
				if ($ilance->auction_award->has_provider_accepted_award($row['project_id'], $row['user_id']))
				{
					$row['actions'] = '<div><input type="button" value="' . $phrase['_release_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-release&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /> <input type="button" value="' . $phrase['_return_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
				}
				else
				{
					$row['actions'] = '<div><span title="' . $phrase['_awarded_provider_has_not_accepted_their_bid_award'] . '"><input type="button" value="' . $phrase['_release_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-release&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" disabled="disabled" /></span> <input type="button" value="' . $phrase['_return_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
				}
			}
			else if ($row['status'] == 'finished')
			{
				$row['status'] = $phrase['_funds_released'];
				// todo: if more than 30 days finished, hide forcable control from admin..
				$row['actions'] = '<div><span style="float:left; padding-right:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow_funded.gif" border="0" alt="' . $phrase['_funds_released'] . '" /></span><input type="button" value="' . $phrase['_refund'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-refund&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
			
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$serviceescrows[] = $row;
			$row_count++;
		}
	}
	else
	{
		$show['no_serviceescrows'] = true;
	}
	
	$serviceprevnext = print_pagnation($numberservice, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['accounting'] . '?cmd=escrow');

	// #### PERFORM PRODUCT ESCROW SEARCH ##########################
	
	$ilance->GPC['page2'] = (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0) ? 1 : intval($ilance->GPC['page2']);
	
	$limit2 = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	
	$SQL = "
		SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, p.currencyid, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, b.bid_id, b.user_id as bidder_id, b.bidamount, b.bidstatus, b.buyershipcost, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
		FROM " . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "projects_escrow AS e,
		" . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "invoices AS i
		WHERE e.user_id = u.user_id
		    AND e.status != 'cancelled'
		    AND e.bid_id = b.bid_id
		    AND e.user_id = b.user_id
		    AND e.project_id = p.project_id
		    AND e.invoiceid = i.invoiceid
		    AND i.invoicetype = 'escrow'
		    AND p.project_state = 'product'
		    AND i.projectid = e.project_id
		$limit2
	";
			
	$SQL2 = "
		SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, p.currencyid, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, b.bid_id, b.user_id as bidder_id, b.bidamount, b.bidstatus, b.buyershipcost, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
		FROM " . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "projects_escrow AS e,
		" . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "invoices AS i
		WHERE e.user_id = u.user_id
		    AND e.status != 'cancelled'
		    AND e.bid_id = b.bid_id
		    AND e.user_id = b.user_id
		    AND e.project_id = p.project_id
		    AND e.invoiceid = i.invoiceid
		    AND i.invoicetype = 'escrow'
		    AND p.project_state = 'product'
		    AND i.projectid = e.project_id
	";
			
	$condition = $condition2 = '';
	$numberrows2 = $ilance->db->query($SQL2);		
	$numberproduct = $ilance->db->num_rows($numberrows2);
	$counter2 = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$row_count2 = 0;
	
	$result2 = $ilance->db->query($SQL);		
	if ($ilance->db->num_rows($result2) > 0)
	{
		$altrows2 = 0;
		while ($row = $ilance->db->fetch_array($result2, DB_ASSOC))
		{
			$altrows2++;
			$row['class2'] = (floor($altrows2/2) == ($altrows2/2)) ? 'alt2' : 'alt1';
			$row['fees'] = ($row['fee'] > 0)
				? print_escrow_fees('as_admin', $row['fee'], $row['project_id'])
				: $phrase['_none'];
				
			$row['fees2'] = ($row['fee2'] > 0)
				? print_escrow_fees('as_admin', $row['fee2'], $row['project_id'])
				: $phrase['_none'];
				
			$row['job_title'] = stripslashes($row['project_title']);
			$row['buyer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $row['bidder_id'] . '">' . fetch_user('username', $row['bidder_id']) . '</a>';
			$row['merchant'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $row['owner_id'] . '">' . fetch_user('username', $row['owner_id']) . '</a>';
			$row['awarddate'] = print_date($row['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
			$row['bidamount'] = $ilance->currency->format($row['bidamount'], $row['currencyid']);
			$row['escrowamount'] = $ilance->currency->format($row['escrowamount'], $row['currencyid']);
			$row['shipfees'] = $ilance->currency->format($row['buyershipcost'], $row['currencyid']);
				
			if ($row['status'] == 'pending')
			{
				// pending - waiting for buyer to forward funds into escrow account
				$row['status'] = '<div class="red">' . $phrase['_do_not_ship'] . '</div>';
				$row['actions'] = '<div><input type="button" value="' . $phrase['_cancel'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=product&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
			else if ($row['status'] == 'started')
			{
				// started - funds forwarded by bidder into escrow account
				$row['status'] = '<div class="green">' . $phrase['_funds_secured'] . '</div>';
				$row['actions'] = '<div><input type="button" value="' . $phrase['_release'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-release&amp;mode=product&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /> <input type="button" value="' . $phrase['_cancel'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=product&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
			else if ($row['status'] == 'confirmed')
			{
				$row['status'] = $phrase['_pending_release'];
				$row['actions'] = '<div><input type="button" value="' . $phrase['_release'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-release&amp;mode=product&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /> <input type="button" value="' . $phrase['_refund'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-refund&amp;mode=product&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
			else if ($row['status'] == 'finished')
			{
				$row['status'] = $phrase['_funds_released'];
				// todo: if more than 30 days finished, hide forcable control from admin..
				$row['actions'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow_funded.gif" border="0" alt="' . $phrase['_funds_released'] . '" />';
			}
			
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$productescrows[] = $row;
			$row_count2++;
		}
	}
	else
	{
		$show['no_productescrows'] = true;
	}
	
	$productprevnext = print_pagnation($numberproduct, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], $counter2, $ilpage['accounting'] . '?cmd=escrow', 'page2');
	
	// does admin want to see cancelled orders?
	
	$extrasql = (isset($ilance->GPC['cancelled']) AND $ilance->GPC['cancelled']) ? "" : "WHERE status != 'cancelled'";
	
	// #### PURCHASE NOW ESCROW BUYING ACTIVITY ####################
	
	$ilance->GPC['page3'] = (!isset($ilance->GPC['page3']) OR isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] <= 0) ? 1 : intval($ilance->GPC['page3']);
	
	$orderby3 = ' ORDER BY orderdate DESC';
	$limit3 = ' LIMIT ' . (($ilance->GPC['page3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	
	// #### PURCHASE NOW ACTIVITY FOR THIS EXPANDED AUCTION ########
	$sql_orders = "
		SELECT orderid, project_id, buyer_id, owner_id, invoiceid, qty, amount, ship_required, ship_location, orderdate, canceldate, arrivedate, paiddate, status, fvf, isfvfpaid, fvfinvoiceid, escrowfee, escrowfeeinvoiceid, escrowfeebuyer, escrowfeebuyerinvoiceid, isescrowfeepaid, isescrowfeebuyerpaid
		FROM " . DB_PREFIX . "buynow_orders
		$extrasql
		$orderby3
		$limit3
	";
	
	$numberrows3 = $ilance->db->query($sql_orders);
	$numberpurchasenow = $ilance->db->num_rows($numberrows3);		
	$result_orders = $ilance->db->query($sql_orders);
	if ($ilance->db->num_rows($result_orders) > 0)
	{
		$order_count = 0;
		$altrows3 = 0;
		while ($orderrows = $ilance->db->fetch_array($result_orders, DB_ASSOC))
		{
			$altrows3++;
			$currencyid = fetch_auction('currencyid', $orderrows['project_id']);
			$orderrows['class3'] = (floor($altrows3/2) == ($altrows3/2)) ? 'alt2' : 'alt1';
			$orderrows['item'] = fetch_auction('project_title', $orderrows['project_id']);
			$orderrows['merchant'] = '<a href="'. $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $orderrows['owner_id'] . '">' . fetch_user('username', $orderrows['owner_id']) . '</a>';
			$orderrows['merchant_id'] = $orderrows['owner_id'];
			$orderrows['buyer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $orderrows['buyer_id'] . '">' . fetch_user('username', $orderrows['buyer_id']) . '</a>';
			$orderrows['orderphone'] = fetch_user('phone', $orderrows['owner_id']);
			$orderrows['orderemail'] = fetch_user('email', $orderrows['owner_id']);
			$orderrows['orderamount'] = $ilance->currency->format($orderrows['amount'], $currencyid);
			
			if ($orderrows['fvf'] > 0)
			{
				$orderrows['fvf']  = ($orderrows['isfvfpaid']) ? '<div class="blue"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['fvfinvoiceid'] . '">' . $ilance->currency->format($orderrows['fvf']) . '</a></div>' : '<div class="red"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['fvfinvoiceid'] . '">' . $ilance->currency->format($orderrows['fvf']) . '</a></div>';
				$orderrows['fvf'] .= '<div class="smaller gray" style="padding-top:3px">' . $phrase['_commission'] . ' ' . $phrase['_fee'] . '</div>';
			}
			else
			{
				$orderrows['fvf'] = $phrase['_none'];
			}
			
			if ($orderrows['escrowfee'] > 0)
			{
				$orderrows['escrowfee'] = ($orderrows['isescrowfeepaid']) ? '<span class="blue"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['escrowfeeinvoiceid'] . '">' . $ilance->currency->format($orderrows['escrowfee']) . '</a></span>' : '<span class="red"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['escrowfeeinvoiceid'] . '">' . $ilance->currency->format($orderrows['escrowfee']) . '</a></span>';
			}
			else
			{
				$orderrows['escrowfee'] = $phrase['_none'];
			}
			
			if ($orderrows['escrowfeebuyer'] > 0)
			{
				$orderrows['escrowfeebuyer'] = ($orderrows['isescrowfeebuyerpaid']) ? '<span class="blue"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['escrowfeebuyerinvoiceid'] . '">' . $ilance->currency->format($orderrows['escrowfeebuyer']) . '</a></span>' : '<span class="red"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['escrowfeebuyerinvoiceid'] . '">' . $ilance->currency->format($orderrows['escrowfeebuyer']) . '</a></span>';
			}
			else
			{
				$orderrows['escrowfeebuyer'] = $phrase['_none'];
			}
			
			$orderrows['orderdate'] = print_date($orderrows['orderdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
			$orderrows['orderqty'] = $orderrows['qty'];
			$orderrows['orderinvoiceid'] = $orderrows['invoiceid'];
			$orderrows['orderid'] = $orderrows['orderid'];
			
			if ($orderrows['status'] == 'paid')
			{
				$orderrows['orderstatus'] = $phrase['_funds_secured'] . ', ' . $phrase['_pending_shipment'];
				$orderrows['orderactions'] = '<div><span title="' . $phrase['_let_the_buyer_know_the_item_was_shipped'] . '"><input type="button" value="' . $phrase['_confirm_delivery'] . '" onclick="if (confirm_js(\'' . $phrase['_let_the_buyer_know_the_item_was_shipped'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=confirm-buynow-delivery&amp;id=' . $orderrows['orderid'] . '\'" class="buttons" style="font-size:10px" /></span></div><div style="padding-top:3px"><span title="' . $phrase['_advise_the_buyer_there_was_a_problem'] . '"><input type="button" value="' . $phrase['_return_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_let_the_buyer_know_the_item_was_shipped'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=cancel-buynow-delivery&amp;id=' . $orderrows['orderid'] . '\'" class="buttons" style="font-size:10px" /></span></div>';
			}
			else if ($orderrows['status'] == 'pending_delivery')
			{ 
				$orderrows['orderstatus'] = $phrase['_shipped_pending_buyer_release'];
				$orderrows['orderactions'] = '<div><input type="button" value="' . $phrase['_force_refund'] . '" onclick="if (confirm_js(\'' . $phrase['_forcing_a_refund_will_recredit_the_buyers_online_account_for_the_amount_forwarded_into_escrow'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=cancel-buynow-delivery&amp;id=' . $orderrows['orderid'] . '\'" class="buttons" style="font-size:10px" /></div><div style="padding-top:3px"><input type="button" value="' . $phrase['_force_release'] . '" onclick="if (confirm_js(\'' . $phrase['_forcing_the_release_of_funds_will_credit_the_sellers_online_account_for_the_amount_paid_into_escrow'] . '\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=force-buynow-release&amp;id=' . $orderrows['orderid'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
			else if ($orderrows['status'] == 'delivered')
			{
				$orderrows['orderactions'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow_funded.gif" border="0" alt="' . $phrase['_funds_released'] . '" id="" />';
				$orderrows['orderstatus'] = $phrase['_funds_released'];
			}
			else if ($orderrows['status'] == 'cancelled')
			{ 
				$orderrows['orderactions'] = '-';
				$orderrows['orderstatus'] = $phrase['_cancelled'];
			}
			
			// #### OFFLINE PAYMENT MODE ###################
			else if ($orderrows['status'] == 'offline')
			{
				$orderrows['orderamount'] = '-';
				$orderrows['orderactions'] = '-';
				$orderrows['orderstatus'] = '<span>' . $phrase['_offline_payment_pending'] . '<div class="smaller gray">' . $phrase['_seller_waiting_for_payment'] . '</div></span>';
			}
			else if ($orderrows['status'] == 'offline_delivered')
			{
				$orderrows['orderamount'] = '-';
				$orderrows['orderactions'] = '-';
				$orderrows['orderstatus'] = '<span class="gray">' . $phrase['_offline_payment_completed'] . '</span>';
			}
			
			$orderrows['orderlocation'] = ($orderrows['ship_required']) ? $orderrows['ship_location'] : $phrase['_digital_delivery'];
			
			$buynowescrows[] = $orderrows;
			$order_count++;
		}
	}
	else
	{
		$show['no_buynowescrows'] = true;
	}
	
	// escrow settings tab
	$configuration_escrowsystem = $ilance->admincp->construct_admin_input('escrowsystem', $ilpage['accounting'] . '?cmd=escrow');
	$cancelled = isset($ilance->GPC['cancelled']) ? '&amp;cancelled=1' : '&amp;cancelled=0';
	$purchasenowprevnext = print_pagnation($numberpurchasenow, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page3'], ($ilance->GPC['page3']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . '?cmd=escrow' . $cancelled, 'page3');
	
	$income['jan'] = 0;
	$totalincome = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-01-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['jan'] =+ $res['sum'];
		$totalincome =+ $income['jan'];
	}
	$income['jan'] = $ilance->currency->format($income['jan']);
	
	$income['feb'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-02-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['feb'] =+ $res['sum'];
		$totalincome =+ $income['feb'];
	}
	$income['feb'] = $ilance->currency->format($income['feb']);
	
	$income['mar'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-03-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['mar'] =+ $res['sum'];
		$totalincome =+ $income['mar'];
	}
	$income['mar'] = $ilance->currency->format($income['mar']);
	
	$income['apr'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-04-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['apr'] =+ $res['sum'];
		$totalincome =+ $income['apr'];
	}
	$income['apr'] = $ilance->currency->format($income['apr']);
	
	$income['may'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-05-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['may'] =+ $res['sum'];
		$totalincome =+ $income['may'];
	}
	$income['may'] = $ilance->currency->format($income['may']);
	
	$income['jun'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-06-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['jun'] =+ $res['sum'];
		$totalincome =+ $income['jun'];
	}
	$income['jun'] = $ilance->currency->format($income['jun']);
	
	$income['jul'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-07-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['jul'] =+ $res['sum'];
		$totalincome =+ $income['jul'];
	}
	$income['jul'] = $ilance->currency->format($income['jul']);
	
	$income['aug'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-08-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['aug'] =+ $res['sum'];
		$totalincome =+ $income['aug'];
	}
	$income['aug'] = $ilance->currency->format($income['aug']);
	
	$income['sep'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-09-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['sep'] =+ $res['sum'];
		$totalincome =+ $income['sep'];
	}
	$income['sep'] = $ilance->currency->format($income['sep']);
	
	$income['oct'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-10-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['oct'] =+ $res['sum'];
		$totalincome =+ $income['oct'];
	}
	$income['oct'] = $ilance->currency->format($income['oct']);
	
	$income['nov'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-11-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['nov'] =+ $res['sum'];
		$totalincome =+ $income['nov'];
	}
	$income['nov'] = $ilance->currency->format($income['nov']);
	
	$income['dec'] = 0;
	$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%".date('Y')."-12-%'");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$income['dec'] =+ $res['sum'];
		$totalincome =+ $income['dec'];
	}
	$income['dec'] = $ilance->currency->format($income['dec']);
	$totalincome = $ilance->currency->format($totalincome);
	
	$escrowincome[] = $income;
	$escrowbalance[] = $ilance->admincp->construct_escrow_balance();
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','totalincome','configuration_escrowsystem','numberservice','numberproduct','serviceprevnext','productprevnext','purchasenowprevnext','numberpurchasenow','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_accounting_escrows_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'escrows.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','escrowincome','serviceescrows','productescrows','buynowescrows','escrowbalance'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### WITHDRAW MANAGEMENT ####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'withdraws')
{
	$ilance->accounting = construct_object('api.accounting');
	
	// #### MARK CHECK REQUEST AS PAID IN FULL #########################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-check-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$uid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "user_id");
		
		$uname = fetch_user('username', $uid);
		$uemail = fetch_user('email', $uid);
		
		// 100.00 (withdraw request )
		$amount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "amount");
		
		$fee = 0;
		if ($ilconfig['check_withdraw_fee_active'] AND $ilconfig['check_withdraw_fee'])
		{
			$fee = $ilconfig['check_withdraw_fee'];
		}
		
		// set withdraw request as paid / sent to customer
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET status = 'paid',
			paid = amount,
			custommessage = '".$ilance->db->escape_string($phrase['_check_sent_to_customer_address_on']) . " " . DATETIME24H . "'
			WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		
		// readjust user's online account balance
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET total_balance = total_balance - $amount
			WHERE user_id = '" . intval($uid) . "'
		");
			    
		// [ADD EMAIL]
		$subject = "Check Withdraw Request Complete - ".stripslashes($uname);
		$message = stripslashes($uname).",
		
This email is confirmation that ".SITE_NAME." has sent your withdraw request in the amount of ".$ilance->currency->format($amount)." via check (using regular postal mail) to your location.  Please allow 5 to 10 business days depending on your location. Your total online account balance has been adjusted accordingly.

********************
Withdrawal Details
********************
Withdraw Amount: " . $ilance->currency->format($amount) . "
Withdraw Fee: " . $ilance->currency->format($fee) . "

Thank you for using ".SITE_NAME.".

";
		
		$messagea = "Administration,
		
This email is confirmation ".SITE_NAME." has sent the withdraw request in the amount of ".$ilance->currency->format($amount)." via check (using regular postal mail) to ".stripslashes($uname)."'s location.  The customer was informed to allow 5 to 10 business days to receive mail (depending on their location). The total online account balance has been adjusted accordingly for the customer.

********************
Withdrawal Details
********************
Withdraw Amount: " . $ilance->currency->format($amount) . "
Withdraw Fee: " . $ilance->currency->format($fee) . "

Thank you for using ".SITE_NAME."

";
		send_email($uemail, $subject, $message, SITE_EMAIL);
		send_email(SITE_EMAIL, $subject, $messagea, SITE_EMAIL);
		
		print_action_success($phrase['_the_selected_withdrawal_request_was_marked_as_being_sent_paid'], $ilpage['accounting'] . '?cmd=withdraws');
		exit();
	}
	
	// #### MARK WIRE TRANSFER AS PAID IN FULL #########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-wire-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$uid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "user_id");
		
		$uname = fetch_user('username', $uid);
		$uemail = fetch_user('email', $uid);
		
		// 100.00 (withdraw request)
		$amount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "amount");
		$fee = 0;
		if ($ilconfig['bank_withdraw_fee_active'] AND $ilconfig['bank_withdraw_fee'] > 0)
		{
			$fee = $ilconfig['bank_withdraw_fee'];
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET status = 'paid',
			paid = amount,
			custommessage = '" . $ilance->db->escape_string($phrase['_wire_transfer_processed_on']) . " " . DATETIME24H . "'
			WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
		");
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET total_balance = total_balance - $amount
			WHERE user_id = '" . intval($uid) . "'
		");
		
		$subject = "Wire Transfer Withdraw Request Complete - ".stripslashes($uname);
		
		$message = stripslashes($uname).",
		
This email is confirmation that ".SITE_NAME." has processed your withdraw request in the amount of ".$ilance->currency->format($amount)." via wire transfer (using the bank account deposit information provided by you) to your deposit location.  Please allow 5 to 10 business days depending on your location. Your total online account balance has been adjusted accordingly.

********************
Withdrawal Details
********************
Withdraw Amount: " . $ilance->currency->format($amount) . "
Withdraw Fee: " . $ilance->currency->format($fee) . "

Thank you for using ".SITE_NAME.".

";
		$messagea = "Administration,
		
This email is confirmation that Admin: ".$_SESSION['ilancedata']['user']['username']." has sent (and / or) updated the withdraw request in the amount of ".$ilance->currency->format($amount)." via wire transfer (using the bank account deposit information provided by ".stripslashes($uname).") to their deposit location. ".stripslashes($uname)." was informed to allow 5 to 10 business days to receive payment (depending on their bank and location). The total online account balance has been adjusted accordingly.

********************
Withdrawal Details
********************
Withdraw Amount: " . $ilance->currency->format($amount) . "
Withdraw Fee: " . $ilance->currency->format($fee) . "

Thank you for using ".SITE_NAME.".

";
		send_email($uemail, $subject, $message, SITE_EMAIL);
		send_email(SITE_EMAIL, $subject, $messagea, SITE_EMAIL);
		
		print_action_success($phrase['_the_selected_withdrawal_request_was_marked_as_being_sent_paid'], $ilpage['accounting'] . '?cmd=withdraws');
		exit();
	}
	
	// #### MARK PAYPAL WITHDRAW REQUEST AS PAID IN FULL ###########
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-paypal-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$amount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "amount");
		$uid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "user_id");
		$uname = fetch_user('username', $uid);
		$uemail = fetch_user('email', $uid);
		
		$fee = ($ilconfig['paypal_withdraw_fee_active'] AND $ilconfig['paypal_withdraw_fee'] > 0) ? $ilconfig['paypal_withdraw_fee'] : 0;
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET status = 'paid',
			paid = amount,
			custommessage = '" . $ilance->db->escape_string($phrase['_paypal_payment_sent_on']) . " " . DATETIME24H . "'
			WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
		");
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET total_balance = total_balance - $amount
			WHERE user_id = '" . intval($uid) . "'
		");
		
		$subject = "Paypal Withdraw Request Complete - ".stripslashes($uname);
		$message = stripslashes($uname).",
		
This email is confirmation that ".SITE_NAME." has sent your withdraw request in the amount of ".$ilance->currency->format($amount)." (minus withdraw fees if any) via paypal.com to your email address.  Your total online account balance has been adjusted accordingly.

********************
Withdrawal Details
********************
Withdraw Amount: " . $ilance->currency->format($amount) . "
Withdraw Fee: " . $ilance->currency->format($fee) . "

Thank you for using ".SITE_NAME.".

";
		$messagea = "Administration,
		
This email is confirmation ".SITE_NAME." Admin: ".$_SESSION['ilancedata']['user']['username']." has sent the withdraw request in the amount of ".$ilance->currency->format($amount)." (minus withdraw fees if any) via paypal.com to the customers [".stripslashes($uname)."] paypal.com email address.  The customer was informed that their total online account balance has been adjusted accordingly.

********************
Withdrawal Details
********************
Withdraw Amount: " . $ilance->currency->format($amount) . "
Withdraw Fee: " . $ilance->currency->format($fee) . "

Thank you for using ".SITE_NAME.".

";                        
		send_email($uemail, $subject, $message, SITE_EMAIL);
		send_email(SITE_EMAIL, $subject, $messagea, SITE_EMAIL);
		
		$status = '';
		if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']))
		{
			$status = '&amp;status=' . $ilance->GPC['status'];        
		}
		
		print_action_success($phrase['_the_selected_withdrawal_request_was_marked_as_being_sent_paid'], $ilpage['accounting'] . '?cmd=withdraws' . $status);
		exit();
	}
	
	// #### MARK PAYPAL REQUEST CANCELLED ##########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-withdraw-cancelled' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$gateway = '';
		$fee = $feeinvoiceid = 0;
		$uid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "user_id");
		$gateway = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "paymethod");
		$gateway = ucwords($gateway);
		$fee = $ilance->db->fetch_field(DB_PREFIX . "invoices", "parentid = '" . intval($ilance->GPC['id']) . "' AND user_id = '" . intval($uid) . "' AND status = 'paid' AND invoicetype = 'debit'", "paid");
		$amount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "amount");
		
		if (isset($fee) AND $fee > 0)
		{
			$total = ($amount + $fee);
			$feeinvoiceid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "parentid = '" . intval($ilance->GPC['id']) . "' AND user_id = '" . intval($uid) . "' AND status = 'paid' AND invoicetype = 'debit'", "invoiceid");
		}
		else
		{
			$total = $amount;
			$fee = 0;
		}
		
		$uname = fetch_user('username', $uid);
		$uemail = fetch_user('email', $uid);
		
		// cancel the withdrawal request
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET status = 'cancelled',
			custommessage = '" . $ilance->db->escape_string($phrase['_paypal_withdraw_request_cancelled_on']) . " " . DATETIME24H . "'
			WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		
		if ($feeinvoiceid > 0)
		{
			// cancel the withdraw fee
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "invoices
				SET status = 'cancelled',
				custommessage = '" . $ilance->db->escape_string($phrase['_paypal_withdraw_request_cancelled_on']) . " " . DATETIME24H . "'
				WHERE invoiceid = '" . intval($feeinvoiceid) . "'
				LIMIT 1
			");        
		}
		
		// re-adjust the users account balance
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET available_balance = available_balance + $total,
			total_balance = total_balance + $fee
			WHERE user_id = '" . intval($uid) . "'
		");
	    
		$ilance->email = construct_dm_object('email', $ilance);
	
		$ilance->email->mail = $uemail;
		$ilance->email->slng = fetch_user_slng($uid);
		
		$ilance->email->get('withdraw_request_cancelled');		
		$ilance->email->set(array(
			'{{username}}' => stripslashes($uname),
			'{{amount}}' => $ilance->currency->format($amount),
			'{{fee}}' => $ilance->currency->format($fee),
			'{{feeinvoiceid}}' => $feeinvoiceid,
			'{{total}}' => $ilance->currency->format($total),
			'{{gateway}}' => $gateway,
		));
		
		$ilance->email->send();
		
		$ilance->email->mail = SITE_EMAIL;
		$ilance->email->slng = fetch_site_slng();
		
		$ilance->email->get('withdraw_request_cancelled_admin');		
		$ilance->email->set(array(
			'{{username}}' => stripslashes($uname),
			'{{amount}}' => $ilance->currency->format($amount),
			'{{fee}}' => $ilance->currency->format($fee),
			'{{feeinvoiceid}}' => $feeinvoiceid,
			'{{total}}' => $ilance->currency->format($total),
			'{{gateway}}' => $gateway,
			'{{staff}}' => $_SESSION['ilancedata']['user']['username'],
		));
		
		$ilance->email->send();
		
		print_action_success($phrase['_the_selected_withdrawal_request_was_cancelled'], $ilpage['accounting'] . '?cmd=withdraws');
		exit();
	}
	    
	$area_title = $phrase['_withdrawal_management'];
	$page_title = SITE_NAME . ' - ' . $phrase['_withdrawal_management'];

	($apihook = $ilance->api('admincp_withdraw_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=withdraws', $_SESSION['ilancedata']['user']['slng']);
	
	$status = '';
	if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']))
	{
		$status = '&amp;status=' . $ilance->GPC['status'];        
	}
	
	// filter via invoice status
	$sqlinvoicestatus = '';
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'withdraws' AND isset($ilance->GPC['status']) AND $ilance->GPC['status'] != '')
	{
		if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] == 'unpaid')
		{
			$sqlinvoicestatus = "AND ( i.status = '".$ilance->db->escape_string($ilance->GPC['status'])."' OR i.status = 'scheduled' )";
			$searchstatus = 'pending';
		}
		else
		{
			$sqlinvoicestatus = "AND i.status = '".$ilance->db->escape_string($ilance->GPC['status'])."'";
			$searchstatus = $ilance->GPC['status'];
		}
	}
	    
	// filter via invoiceid
	$sqlinvoiceid = $invid = '';
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'withdraws' AND isset($ilance->GPC['invoiceid']) AND $ilance->GPC['invoiceid'] > 0)
	{
		$invid = intval($ilance->GPC['invoiceid']);
		$sqlinvoiceid = "AND i.invoiceid = '" . $invid . "'";
	}
	    
	// filter via transaction id
	$sqlinvoicetxnid = '';
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == "withdraws" AND isset($ilance->GPC['transactionid']) AND $ilance->GPC['transactionid'] != "")
	{
		$sqlinvoicetxnid = "AND i.transactionid = '".$ilance->db->escape_string($ilance->GPC['transactionid'])."'";
	}

	// #### CHECK REQUESTS #########################################
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}

	$orderlimit = ' ORDER BY i.invoiceid DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$requesttotal1 = $feetotal1 = 0;

	$sqlchecks = $ilance->db->query("
		SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.custommessage, i.withdrawinvoiceid, i.currency_id
		FROM " . DB_PREFIX . "users AS c
		LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
		WHERE i.invoicetype = 'debit'
		    AND i.paymethod = 'check'
		    $sqlinvoicestatus
		    $sqlinvoiceid
		    $sqlinvoicetxnid
		$orderlimit
	");
	
	$sqlchecks2 = $ilance->db->query("
		SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.custommessage, i.withdrawinvoiceid, i.currency_id
		FROM " . DB_PREFIX . "users AS c
		LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
		WHERE i.invoicetype = 'debit'
		    AND i.paymethod = 'check'
		    $sqlinvoicestatus
		    $sqlinvoicetxnid
		$sqlinvoiceid
	");
	if ($ilance->db->num_rows($sqlchecks) > 0)
	{
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($sqlchecks, DB_ASSOC))
		{
			$res['message'] = ucfirst($res['status']);
			$res['action'] = ($res['status'] == 'cancelled') ? '--' : '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-withdraw-cancelled&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="' . $phrase['_click_to_cancel_this_withdrawal_request'] . '" border="0" /></a>';
			$res['status'] = ($res['status'] == 'unpaid' OR $res['status'] == 'scheduled') ? '<a href="'.$ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-check-paid&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_click_to_update_check_payment_request_as_being_paid_sent'] . '" border="0" /></a>' : '--';
					
			if ($res['withdrawinvoiceid'] > 0)
			{
				$feetotal1 += $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount");
				$res['fee'] = $ilance->currency->format($ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount"));
			}
			else
			{
				$feetotal1 += 0;
				$res['fee'] = $ilance->currency->format(0);
			}
			
			$res['paid'] = $ilance->currency->format($res['paid']);
			$res['createdate'] = print_date($res['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
			$res['invoicetype'] = ucfirst($res['invoicetype']);
			$res['remove'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_remove-invoice&amp;id=' . $res['invoiceid'] . '" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
			$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			
			$requesttotal1 += ($res['amount']);
			$res['request'] = $ilance->currency->format($res['amount']);
			$res['amount'] = $ilance->currency->format($res['amount']);
			
			$check[] = $res;
			$row_count++;
		}
		
		$numbercheck = $ilance->db->num_rows($sqlchecks2);
		$checkprevnext = print_pagnation($numbercheck, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . '?cmd=withdraws&amp;viewtype=check');
	}
	else
	{
		$show['no_check'] = true;
		$numbercheck = 0;
	}
	
	$requesttotal1 = $ilance->currency->format($requesttotal1);
	$feetotal1 = $ilance->currency->format($feetotal1);
		
	// #### WIRE REQUESTS ##################################################
	if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
	{
		$ilance->GPC['page2'] = 1;
	}
	else
	{
		$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
	}
	
	$orderlimit2 = ' ORDER BY i.invoiceid DESC LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$requesttotal2 = $feetotal2 = 0;

	$sqlwire = $ilance->db->query("
		SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.withdrawinvoiceid
		FROM " . DB_PREFIX . "users AS c
		LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
		WHERE i.invoicetype = 'debit'
		    AND i.paymethod = 'bank'
		    $sqlinvoicestatus
		    $sqlinvoiceid
		    $sqlinvoicetxnid
		$orderlimit2
	");
	
	$sqlwire2 = $ilance->db->query("
		SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.withdrawinvoiceid
		FROM " . DB_PREFIX . "users AS c
		LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
		WHERE i.invoicetype = 'debit'
		    AND i.paymethod = 'bank'
		    $sqlinvoicestatus
		    $sqlinvoiceid
		    $sqlinvoicetxnid
	");
	
	if ($ilance->db->num_rows($sqlwire) > 0)
	{
		$row_count2 = 0;
		while ($res = $ilance->db->fetch_array($sqlwire))
		{
			$res['message'] = ucfirst($res['status']);
			$res['status'] = ($res['status'] == "unpaid" OR $res['status'] == 'scheduled') ? '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-wire-paid&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_click_to_update_wire_payment_request_as_being_processed_from_your_bank'] . '" border="0" /></a>' : '--';
			
			if ($res['withdrawinvoiceid'] > 0)
			{
				$feetotal2 += $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount");
				$res['fee'] = $ilance->currency->format($ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount"));
			}
			else
			{
				$feetotal2 += 0;
				$res['fee'] = $ilance->currency->format(0);
			}
			
			$res['action'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-withdraw-cancelled&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="' . $phrase['_click_to_cancel_this_withdrawal_request'] . '" border="0" /></a>';
			$res['createdate'] = print_date($res['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
			$res['invoicetype'] = ucfirst($res['invoicetype']);
			$res['remove'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_remove-invoice&amp;id=' . $res['invoiceid'] . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
			$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">'.fetch_user('username', $res['user_id']).'</a>';
			$res['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
			
			$requesttotal2 += ($res['amount']);
			$res['request'] = $ilance->currency->format($res['amount']);
			$res['amount'] = $ilance->currency->format($res['amount']);
			
			$wire[] = $res;
			$row_count2++;
		}
		$numberwire = $ilance->db->num_rows($sqlwire2);
		$wireprevnext = print_pagnation($numberwire, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], ($ilance->GPC['page2']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=withdraws&amp;viewtype=wire", 'page2');
	}
	else
	{
		$show['no_wire'] = true;
		$numberwire = 0;
	}
	
	$requesttotal2 = $ilance->currency->format($requesttotal2);
	$feetotal2 = $ilance->currency->format($feetotal2);
		
	// #### PAYPAL REQUESTS ########################################
	if (!isset($ilance->GPC['page3']) OR isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] <= 0)
	{
		$ilance->GPC['page3'] = 1;
	}
	else
	{
		$ilance->GPC['page3'] = intval($ilance->GPC['page3']);
	}
	
	$orderlimit3 = ' ORDER BY i.invoiceid DESC LIMIT ' . (($ilance->GPC['page3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$requesttotal3 = $feetotal3 = 0;
	
	$status = '';
	if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']))
	{
		$status = '&amp;status=' . $ilance->GPC['status'];        
	}

	$sqlpp = $ilance->db->query("
		SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.custommessage, i.withdrawinvoiceid
		FROM " . DB_PREFIX . "users AS c
		LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
		WHERE i.invoicetype = 'debit'
		    AND i.paymethod = 'paypal'
		    $sqlinvoicestatus
		    $sqlinvoiceid
		    $sqlinvoicetxnid
		$orderlimit3
	");
	
	$sqlpp2 = $ilance->db->query("
		SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.custommessage, i.withdrawinvoiceid
		FROM " . DB_PREFIX . "users AS c
		LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
		WHERE i.invoicetype = 'debit'
		    AND i.paymethod = 'paypal'
		    $sqlinvoicestatus
		    $sqlinvoiceid
		    $sqlinvoicetxnid
	");        
	if ($ilance->db->num_rows($sqlpp) > 0)
	{
		$row_count3 = 0;
		    
		while ($res = $ilance->db->fetch_array($sqlpp))
		{
			$res['customername'] = fetch_user('fullname', $res['user_id']);
			
			$res['button'] = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" accept-charset="UTF-8" style="margin: 0px;" target="_blank">';
			$res['button'] .= '<input type="hidden" name="cmd" value="_xclick" />';
			$res['button'] .= '<input type="hidden" name="business" value="' . $res['custommessage'] . '" />';
			$res['button'] .= '<input type="hidden" name="return" value="' . HTTPS_SERVER_ADMIN . $ilpage['accounting'] . '?cmd=withdraws" />';
			$res['button'] .= '<input type="hidden" name="undefined_quantity" value="0" />';
			$res['button'] .= '<input type="hidden" name="item_name" value="Withdraw Payment Request - ' . $res['customername'] . '" />';
			$res['button'] .= '<input type="hidden" name="amount" value="'.$res['amount'].'" />';
			$res['button'] .= '<input type="hidden" name="currency_code" value="' . $ilconfig['paypal_master_currency'] . '" />';
			$res['button'] .= '<input type="hidden" name="no_shipping" value="1" />';
			$res['button'] .= '<input type="hidden" name="cancel_return" value="' . HTTPS_SERVER_ADMIN . $ilpage['accounting'] . '?cmd=withdraws" />';
			$res['button'] .= '<input type="hidden" name="no_note" value="1" />';
			$res['button'] .= '<input type="submit" name="submit" value=" Pay " class="buttons_smaller" />';
			$res['button'] .= '</form>';
			
			$res['message'] = ucfirst($res['status']);
			if ($ilconfig['paypal_withdraw_fee_active'])
			{
				if ($res['status'] == 'cancelled')
				{
					$res['action'] = '--';
					$res['button'] = '--';
				}
				else
				{
					$res['action'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-withdraw-cancelled&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="' . $phrase['_click_to_cancel_this_withdrawal_request'] . '" border="0" /></a>';
				}
				
				if ($res['status'] == 'unpaid' OR $res['status'] == 'scheduled')
				{
					$res['status'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-paypal-paid&amp;amount=' . ($res['amount'] + $ilconfig['paypal_withdraw_fee']) . '&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_click_to_update_paypal_withdrawal_request_as_being_paid_in_full'] . '" border="0" /></a>';
				}
				else
				{
					$res['status'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_paid'] . '" border="0" />';
					$res['button'] = '--';
				}
			}
			else
			{
				if ($res['status'] == 'cancelled')
				{
					$res['action'] = '--';
					$res['button'] = '--';
				}
				else
				{
					$res['action'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-withdraw-cancelled&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="' . $phrase['_click_to_cancel_this_withdrawal_request'] . '" border="0" /></a>';
				}
				
				if ($res['status'] == 'unpaid' OR $res['status'] == 'scheduled')
				{
					$res['status'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-paypal-paid&amp;amount=' . $res['amount'] . '&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_click_to_update_paypal_withdrawal_request_as_being_paid_in_full'] . '" border="0" /></a>';
				}
				else
				{
					$res['status'] = '--';
					$res['button'] = '--';
				}
			}
			
			if ($res['withdrawinvoiceid'] > 0)
			{
				$feetotal3 += $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount");
				$res['fee'] = $ilance->currency->format($ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount"));
			}
			else
			{
				$feetotal3 += 0;
				$res['fee'] = $ilance->currency->format(0);
			}
			
			$res['createdate'] = print_date($res['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
			$res['invoicetype'] = ucfirst($res['invoicetype']);
			$res['remove'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_remove-invoice&amp;id=' . $res['invoiceid'] . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
			$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
			$res['customeremail'] = $res['custommessage'];
			$res['class'] = ($row_count3 % 2) ? 'alt2' : 'alt1';
			
			$requesttotal3 += ($res['amount']);
			$res['request'] = $ilance->currency->format($res['amount']);
			$res['amount'] = $ilance->currency->format($res['amount']);
			
			$paypal[] = $res;
			$row_count3++;
		}
		
		$numberpaypal = $ilance->db->num_rows($sqlpp2);
		$paypalprevnext = print_pagnation($numberpaypal, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page3'], ($ilance->GPC['page3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . '?cmd=withdraws&amp;viewtype=paypal', 'page3');
	}
	else
	{
		$show['no_paypal'] = true;
		$numberpaypal = 0;
	}
	
	$invoice_status_pulldown = '<select name="status" style="font-family: verdana">';
	$invoice_status_pulldown .= '<optgroup label="' . $phrase['_select_status'] . '">';
	$invoice_status_pulldown .= '<option value="">' . $phrase['_all'] . '</option>';
	$invoice_status_pulldown .= '<option value="paid"'; if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] == "paid") { $invoice_status_pulldown .= ' selected="selected"';  } $invoice_status_pulldown .= '>'.$phrase['_paid_requests'].'</option>';
	$invoice_status_pulldown .= '<option value="unpaid"'; if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] == "unpaid") { $invoice_status_pulldown .= ' selected="selected"';  } $invoice_status_pulldown .= '>'.$phrase['_pending_requests'].'</option>';
	$invoice_status_pulldown .= '<option value="cancelled"'; if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] == "cancelled") { $invoice_status_pulldown .= ' selected="selected"';  } $invoice_status_pulldown .= '>'.$phrase['_cancelled_requests'].'</option>';
	$invoice_status_pulldown .= '</optgroup>';
	$invoice_status_pulldown .= '</select>';
	
	$requesttotal3 = $ilance->currency->format($requesttotal3);
	$feetotal3 = $ilance->currency->format($feetotal3);
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','requesttotal1','requesttotal2','requesttotal3','feetotal1','feetotal2','feetotal3','searchstatus','invoice_status_pulldown','numberpaypal','paypalprevnext','wireprevnext','numberwire','numbercheck','checkprevnext','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_accounting_withdraws_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'withdraws.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','check','wire','paypal'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### CREDIT CARD LISTINGS AND MANAGEMENT ####################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'creditcards')
{
	$area_title = $phrase['_credit_card_management'];
	$page_title = SITE_NAME . ' - ' . $phrase['_credit_card_management'];

	($apihook = $ilance->api('admincp_creditcard_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=creditcards', $_SESSION['ilancedata']['user']['slng']);
	
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	
	$orderlimit = ' ORDER BY cc_id DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$sqlverified = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE authorized = 'yes' " . $orderlimit;
	$sqlverified2 = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE authorized = 'yes'";
	$resultverified = $ilance->db->query($sqlverified);        
	if ($ilance->db->num_rows($resultverified) > 0)
	{
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($resultverified))
		{
			$res['ccnum'] = substr_replace($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), 'XX XXXX XXXX ', 2 , (mb_strlen($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) - 6));
			$res['username'] = stripslashes($res['name_on_card']);
			$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
			$res['phone'] = $res['phone_of_cardowner'];
			$res['expiry'] = $res['creditcard_expiry'];
			
			if ($res['authorized'] == 'yes')
			{
			    $res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_unauthorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="'.$phrase['_click_to_unauthorize_credit_card_cannot_use_card'].'" border="0" /></a>';
			}
			else
			{
			    $res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_authorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="'.$phrase['_click_to_authorize_credit_card_can_use_card'].'" border="0" /></a>';
			}
			
			$res['address'] = $res['card_billing_address1'].", ";
			if ($res['card_billing_address2'] != "")
			{
			    $res['address'] .= $res['card_billing_address2'].", ";
			}
			$res['address'] .= ucfirst($res['card_city']).", ".ucfirst($res['card_state']).", ".mb_strtoupper($res['card_postalzip']).", ";
			$res['address'] .= stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations","locationid=".$ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$res['cc_id'],"card_country"),"location_eng"));
					
			if ($res['creditcard_type'] == 'visa')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/visa.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'mc')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mc.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'amex')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amex.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'disc')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/disc.gif" border="0" alt="">';
			}
			$res['status'] = ucfirst($res['creditcard_status']);
			$res['remove'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
			$res['authamounts'] = $ilance->currency->format($res['auth_amount1'] + $res['auth_amount2']);
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$verifiedcreditcards[] = $res;
			$row_count++;
		}
	}
	else
	{
		$show['no_verifiedcreditcards'] = true;
	}		
	$resultverified2 = $ilance->db->query($sqlverified2);
	if ($ilance->db->num_rows($resultverified2) > 0)
	{
		$numberverified = $ilance->db->num_rows($resultverified2);
	}
	else
	{
		$numberverified = 0;
	}		
	$verifiedprevnext = print_pagnation($numberverified, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], ($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=creditcards&amp;viewtype=verified");
	
	// #### UNVERIFIED CREDIT CARDS ON FILE ################################
	if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
	{
		$ilance->GPC['page2'] = 1;
	}
	else
	{
		$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
	}
	
	$orderlimit2 = ' ORDER BY cc_id DESC LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];    
	$sqlunverified = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE authorized = 'no' " . $orderlimit2;
	$sqlunverified2 = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE authorized = 'no'";        
	$resultunverified = $ilance->db->query($sqlunverified);
	if ($ilance->db->num_rows($resultunverified) > 0)
	{
		$row_count2 = 0;
		while ($res = $ilance->db->fetch_array($resultunverified))
		{
			$res['ccnum'] = substr_replace($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), 'XX XXXX XXXX ', 2 , (mb_strlen($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) - 6));
			$res['username'] = stripslashes($res['name_on_card']);
			$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
			$res['phone'] = $res['phone_of_cardowner'];
			$res['expiry'] = $res['creditcard_expiry'];
			if ($res['authorized'] == "yes")
			{
				$res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_unauthorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . $phrase['_click_to_unauthorize_credit_card_cannot_use_card'] . '" border="0" /></a>';
			}
			else
			{
				$res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_authorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . $phrase['_click_to_authorize_credit_card_can_use_card'] . '" border="0" /></a>';
			}				
			$res['address'] = $res['card_billing_address1'].", ";				
			if ($res['card_billing_address2'] != "")
			{
			    $res['address'] .= $res['card_billing_address2'].", ";
			}				
			$res['address'] .= ucfirst($res['card_city']).", ".ucfirst($res['card_state']).", ".mb_strtoupper($res['card_postalzip']).", ";
			$res['address'] .= stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations","locationid=".$ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$res['cc_id'],"card_country"),"location_eng"));
			
			if ($res['creditcard_type'] == 'visa')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/visa.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'mc')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mc.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'amex')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amex.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'disc')
			{
			    $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/disc.gif" border="0" alt="">';
			}				
			$res['status'] = ucfirst($res['creditcard_status']);
			$res['remove'] = '<a href="'.$ilpage['subscribers'] . '?subcmd=_remove-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
			$res['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
			$unverifiedcreditcards[] = $res;
			$row_count2++;
		}
	}
	else
	{
		$show['no_unverifiedcreditcards'] = true;
	}	    
	$resultunverified2 = $ilance->db->query($sqlunverified2);		
	if ($ilance->db->num_rows($resultunverified2) > 0)
	{
		$numberunverified = $ilance->db->num_rows($resultunverified2);
	}
	else
	{
		$numberunverified = 0;
	}		
	$unverifiedprevnext = print_pagnation($numberunverified, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], ($ilance->GPC['page2']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=creditcards&amp;viewtype=unverified", 'page2');

	// #### EXPIRED CREDIT CARDS ON FILE ###################################
	if (!isset($ilance->GPC['page3']) OR isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] <= 0)
	{
		$ilance->GPC['page3'] = 1;
	}
	else
	{
		$ilance->GPC['page3'] = intval($ilance->GPC['page3']);
	}
	
	$orderlimit3 = ' ORDER BY cc_id DESC LIMIT ' . (($ilance->GPC['page3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$sqlexpired  = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE creditcard_status = 'expired' " . $orderlimit3;
	$sqlexpired2 = "SELECT * FROM " . DB_PREFIX . "creditcards WHERE creditcard_status = 'expired'";        
	$resultexpired = $ilance->db->query($sqlexpired);        
	if ($ilance->db->num_rows($resultexpired) > 0)
	{
		$row_count3 = 0;
		while ($res = $ilance->db->fetch_array($resultexpired))
		{
			$res['ccnum'] = substr_replace($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), 'XX XXXX XXXX ', 2 , (mb_strlen($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) - 6));
			$res['username'] = stripslashes($res['name_on_card']);
			$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">'.fetch_user('username', $res['user_id']).'</a>';
			$res['phone'] = $res['phone_of_cardowner'];
			$res['expiry'] = $res['creditcard_expiry'];
			
			if ($res['authorized'] == 'yes')
			{
				$res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_unauthorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to unauthorize credit card (cannot use card)" border="0"></a>';
			}
			else
			{
				$res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_authorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to authorize credit card (can use card)" border="0"></a>';
			}
			
			$res['address'] = $res['card_billing_address1'].", ";                
			if ($res['card_billing_address2'] != "")
			{
				$res['address'] .= $res['card_billing_address2'].", ";
			}				
			$res['address'] .= ucfirst($res['card_city']).", ".ucfirst($res['card_state']).", ".mb_strtoupper($res['card_postalzip']).", ";
			$res['address'] .= stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations","locationid=".$ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$res['cc_id'],"card_country"),"location_eng"));
			if ($res['creditcard_type'] == 'visa')
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/visa.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'mc')
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mc.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'amex')
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amex.gif" border="0" alt="">';
			}
			else if ($res['creditcard_type'] == 'disc')
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/disc.gif" border="0" alt="">';
			}
			$res['status'] = ucfirst($res['creditcard_status']);
			$res['remove'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . $res['user_id'] . '&amp;ccmgr=1" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
			$res['class'] = ($row_count3 % 2) ? 'alt2' : 'alt1';
			$expiredcreditcards[] = $res;
			$row_count3++;
		}
	}
	else
	{
		$show['no_expiredcreditcards'] = true;
	}		
	$resultexpired2 = $ilance->db->query($sqlexpired2);
	if ($ilance->db->num_rows($resultexpired2) > 0)
	{
		$numberexpired = $ilance->db->num_rows($resultexpired2);
	}
	else
	{
		$numberexpired = 0;
	}
	
	$expiredprevnext = print_pagnation($numberexpired, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page3'], ($ilance->GPC['page3']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=creditcards&amp;viewtype=expired", 'page3');
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','numberexpired','expiredprevnext','unverifiedprevnext','numberunverified','verifiedprevnext','numberverified','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_accounting_creditcards_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'creditcards.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','verifiedcreditcards','unverifiedcreditcards','expiredcreditcards'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### BANK ACCOUNTS ##########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bankaccounts')
{
	$area_title = $phrase['_bank_account_management'];
	$page_title = SITE_NAME . ' - ' . $phrase['_bank_account_management'];

	($apihook = $ilance->api('admincp_bankaccount_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=bankaccounts', $_SESSION['ilancedata']['user']['slng']);
	
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	
	$orderlimit = ' ORDER BY bank_id DESC LIMIT '.(($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay']).','.$ilconfig['globalfilters_maxrowsdisplay'];
	$sqlbankaccounts  = "SELECT * FROM " . DB_PREFIX . "bankaccounts ".$orderlimit;
	$sqlbankaccounts2 = "SELECT * FROM " . DB_PREFIX . "bankaccounts";
	$result = $ilance->db->query($sqlbankaccounts);
	if ($ilance->db->num_rows($result) > 0)
	{
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($result))
		{
			$res['bankname'] = ucfirst(stripslashes($res['beneficiary_bank_name']));
			$res['accountnum'] = $res['beneficiary_account_number'];
			$res['accounttype'] = ucfirst($res['bank_account_type']);
			$res['address'] = ucfirst(stripslashes($res['beneficiary_bank_address_1']));
			$res['swiftnum'] = $res['beneficiary_bank_routing_number_swift'];
			if ($res['beneficiary_bank_address_2'] != "")
			{
			    $res['address'] .= ", ".stripslashes($res['beneficiary_bank_address_2']); 
			}				
			$res['city'] = ucfirst($res['beneficiary_bank_city']);
			$res['zipcode'] = mb_strtoupper($res['beneficiary_bank_zipcode']);
			$res['country'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations","locationid=".$ilance->db->fetch_field(DB_PREFIX . "bankaccounts","bank_id=".$res['bank_id'],"beneficiary_bank_country_id"),"location_eng"));
			$res['currency'] = $ilance->db->fetch_field(DB_PREFIX . "currency","currency_id=".$ilance->db->fetch_field(DB_PREFIX . "bankaccounts","bank_id=".$res['bank_id'],"destination_currency_id"),"currency_abbrev");
			$res['username'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id='.$res['user_id'].'">'.fetch_user('username', $res['user_id']).'</a>';
			$res['remove'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-bankaccount&amp;id='.$res['bank_id'].'&amp;uid='.$res['user_id'].'" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$bankaccounts[] = $res;
			$row_count++;
		}
	}
	else
	{
		$show['no_bankaccounts'] = true;
	}
		
	$numberactive = $ilance->db->num_rows($ilance->db->query($sqlbankaccounts2));
	$activeprevnext = print_pagnation($numberactive, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], ($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=bankaccounts&amp;viewtype=active");
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','activeprevnext','numberactive','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_accounting_bankaccounts_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'bankaccounts.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','bankaccounts'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### CUSTOM REPORT MANAGEMENT ###############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'reports')
{
	$area_title = $phrase['_report_management'];
	$page_title = SITE_NAME . ' - ' . $phrase['_report_management'];

	($apihook = $ilance->api('admincp_report_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=reports', $_SESSION['ilancedata']['user']['slng']);
	
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_do-reports')
	{
		$show['showreportoutput'] = true;
		$action = $ilance->GPC['action'];
		if (isset($action))
		{
			if (empty($ilance->GPC['doshow']))
			{
				print_action_failed($phrase['_you_did_not_select_a_desired_report_type_please_go_back_and_retry'], $ilpage['accounting'] . '?cmd=reports');
			}
			
			$sql = "
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE ";
			
			// #### generate custom reporting sql
			switch ($ilance->GPC['doshow'])
			{
				case 'subscription':
				{
					$sql .= "invoicetype = '" . $ilance->db->escape_string($ilance->GPC['doshow']) . "'";
					break;
				}
				case 'credential':
				{
					$sql .= "invoicetype = '" . $ilance->db->escape_string($ilance->GPC['doshow']) . "'";
					break;
				}
				case 'portfolio':
				{
					$sql .= "invoicetype = 'debit' AND isportfoliofee = '1'";
					break;
				}
				case 'enhancements':
				{
					$sql .= "invoicetype = 'debit' AND isenhancementfee = '1'";
					break;
				}
				case 'fvf':
				{
					$sql .= "invoicetype = 'debit' AND isfvf = '1'";
					break;
				}
				case 'insfee':
				{
					$sql .= "invoicetype = 'debit' AND isif = '1'";
					break;
				}
				case 'escrow':
				{
					$sql .= "invoicetype = 'debit' AND isescrowfee = '1'";
					break;
				}
				case 'withdraw':
				{
					$sql .= "invoicetype = 'debit' AND iswithdrawfee = '1'";
					break;
				}
				case 'p2b':
				{
					$sql .= "invoicetype = 'debit' AND isp2bfee = '1'";
					break;
				}
				// expenses
				case 'tax':
				{
					$sql .= "istaxable = '1' AND taxamount > 0";
					break;
				}
				case 'registerbonus':
				{
					$sql .= "invoicetype = 'credit' AND isregisterbonus = '1'";
					break;
				}
				// loses
				case 'refund':
				{
					$sql .= "invoicetype = '" . $ilance->db->escape_string($ilance->GPC['doshow']) . "'";
					break;
				}
				case 'cancelled':
				{
					$sql .= "status = '" . $ilance->db->escape_string($ilance->GPC['doshow']) . "'";
					break;
				}
				// disputed
				case 'disputed':
				{
					$sql .= "indispute = '1'";
					break;
				}
				// nonprofit donation fees collected
				case 'donationfee':
				{
					$sql .= "isdonationfee = '1'";
					break;
				}
			}
			
			// ensure every transaction shown has a cost to tween out the freebie/waived transactions
			$sql .= " AND totalamount > 0";
			
			$fields = array(
				array('invoiceid', 'ID'),
				array('transactionid', $phrase['_transaction_id']),
				array('status', $phrase['_status']),
				array('invoicetype', $phrase['_type']),
				array('amount', $phrase['_amount']),
				array('taxamount', $phrase['_tax']),
				array('totalamount', $phrase['_total']),
				array('paid', $phrase['_paid']),
				array('description', $phrase['_description']),
				array('user_id', 'UID'),
				array('projectid', 'PID'),
				array('createdate', $phrase['_created']),
				array('duedate', $phrase['_due']),
				array('paiddate', $phrase['_paid_date']),
				array('custommessage', $phrase['_message'])
			);
			
			foreach ($fields AS $column)
			{
				if (isset($ilance->GPC[$column[0]]) AND $ilance->GPC[$column[0]] == 'generate')
				{
					$fieldsToGenerate[] = $column[0];
					$headings[] = $column[1];
				}
			}
			
			// #### date range in the past
			if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
			{
				$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
				$endDate = print_datetime_from_timestamp(time());
			}
			// #### date range exactly as entered
			else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
			{
				$startDate = print_array_to_datetime($ilance->GPC['range_start']);
				$startDate = substr($startDate, 0, -9);
				
				$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
				$endDate = substr($endDate, 0, -9);
			}
			
			$sql .= " AND (createdate <= '" . $endDate . "' AND createdate >= '" . $startDate . "')";
			
			// #### display order
			if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
			{
				$sql .= " ORDER BY invoiceid ASC";
			}
			else
			{
				$sql .= " ORDER BY invoiceid DESC";
			}
			
			//echo $sql;
			$data = $ilance->admincp->fetch_reporting_fields($sql, $fieldsToGenerate);
			switch ($action)
			{
				case 'csv':
				{
					$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
					break;
				}
				case 'tsv':
				{
					$reportoutput = $ilance->admincp->construct_tsv_data($data, $headings);
					break;
				}                                   
				case 'list':
				default:
				{
					$reportoutput = $ilance->admincp->construct_html_table($data, $headings);
					break;
				}
			}
			
			$timeStamp = date("Y-m-d-H-i-s");
			$fileName = "reports-$timeStamp";
			if ($action == 'csv')
			{
				header("Pragma: cache");
				header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
				header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
				echo $reportoutput;
				die();
			}
			else if ($action == 'tsv')
			{
				header("Pragma: cache");
				header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
				header("Content-Disposition: attachment; filename=" . $fileName . ".txt");
				echo $reportoutput;
				die();
			}
		}            
		$range = $ilance->GPC['range'];
		$rangepast = $ilance->GPC['rangepast'];
	}
	else
	{
		$show['showreportoutput'] = false;
	}
	
	// #### reporting action #######################################
	$reportaction = '<select name="action" style="font-family: verdana"><option value="list"'; 
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'list')
	{
		$reportaction .= ' selected="selected"';
	}
	$reportaction .= '>' . $phrase['_show_report_listings'] . '</option>';
	$reportaction .= '<option value="csv"'; 
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'csv')
	{
		$reportaction .= ' selected="selected"';
	}
	$reportaction .= '>' . $phrase['_download_comma_delimited_file'] . '</option>';
	$reportaction .= '<option value="tsv"';
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'tsv')
	{
		$reportaction .= ' selected="selected"'; 
	}
	$reportaction .= '>' . $phrase['_download_tab_delimited_file'] . '</option></select>';
	
	// #### reporting columns output for search menu ###############
	$reportcolumns = '<table width="100%" border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">';
	
	// invoice id
	$reportcolumns .= '<tr><td width="6%"><input type="checkbox" name="invoiceid" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['invoiceid']) AND $ilance->GPC['invoiceid'] == "generate")
	{
		$reportcolumns .= ' checked="checked"';
	}
	$reportcolumns .= '></td><td width="17%">Invoice ID</td>';
	
	// user id
	$reportcolumns .= '<td width="6%"><input type="checkbox" name="user_id" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] == "generate")
	{
		$reportcolumns .= ' checked="checked"';
	}
	$reportcolumns .= '></td><td width="21%">User ID</td>';
	
	// description
	$reportcolumns .= '<td width="6%"><input type="checkbox" name="description" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['description']) AND $ilance->GPC['description'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td width="23%">Description</td>';
	$reportcolumns .= '<td>&nbsp;</td><td>&nbsp;</td></tr>';
	
	// amount
	$reportcolumns .= '<tr><td><input type="checkbox" name="amount" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['amount']) AND $ilance->GPC['amount'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td>Invoice Amount</td>';
	
	// total amount
	$reportcolumns .= '<td><input type="checkbox" name="totalamount" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['totalamount']) AND $ilance->GPC['totalamount'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td>Total Amount</td>';
	
	// tax amount
	$reportcolumns .= '<td><input type="checkbox" name="taxamount" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['taxamount']) AND $ilance->GPC['taxamount'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td>Tax Amount</td>';
	$reportcolumns .= '<td>&nbsp;</td><td>&nbsp;</td></tr>';
	
	// project id
	$reportcolumns .= '<tr><td><input type="checkbox" name="projectid" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td>Project ID</td>';
	
	// invoice status
	$reportcolumns .= '<td><input type="checkbox" name="status" value="generate"'; 
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['status']) AND $ilance->GPC['status'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td>Invoice Status</td>';
	
	// invoice type
	$reportcolumns .= '<td><input type="checkbox" name="invoicetype" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['invoicetype']) AND $ilance->GPC['invoicetype'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td>Invoice Type</td>';
	
	$reportcolumns .= '<td>&nbsp;</td><td>&nbsp;</td></tr>';
	
	// create date
	$reportcolumns .= '<td><input type="checkbox" name="createdate" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['createdate']) AND $ilance->GPC['createdate'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td>Create Date</td>';
	
	// due date
	$reportcolumns .= '<td><input type="checkbox" name="duedate" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['duedate']) AND $ilance->GPC['duedate'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td>Due Date</td>';
	
	// paid date
	$reportcolumns .= '<td><input type="checkbox" name="paiddate" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['paiddate']) AND $ilance->GPC['paiddate'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td>Paid Date</td>';
	$reportcolumns .= '<td>&nbsp;</td><td>&nbsp;</td>';
	
	// custom message
	$reportcolumns .= '<tr><td><input type="checkbox" name="custommessage" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['custommessage']) AND $ilance->GPC['custommessage'] == "generate")
	{
		//$reportcolumns .= ' checked="checked"';
		$reportcolumns .= ''; 
	}
	$reportcolumns .= '></td><td>Custom Message</td>';
	
	// transaction id
	$reportcolumns .= '<td><input type="checkbox" name="transactionid" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['transactionid']) AND $ilance->GPC['transactionid'] == "generate")
	{
		//$reportcolumns .= ' checked="checked"';
		$reportcolumns .= ''; 
	}
	$reportcolumns .= '></td><td>Transaction ID</td>';
	
	// amount paid
	$reportcolumns .= '<td><input type="checkbox" name="paid" value="generate"';
	if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['paid']) AND $ilance->GPC['paid'] == "generate")
	{
		$reportcolumns .= ' checked="checked"'; 
	}
	$reportcolumns .= '></td><td>Amount Paid</td>';
	$reportcolumns .= '<td>&nbsp;</td><td>&nbsp;</td></tr></table>';
	
	// #### date range #############################################
	$radiopast = '<input type="radio" name="range" value="past"'; 
	if ((!isset($ilance->GPC['action']) OR (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past")))
	{
		$radiopast .= ' checked="checked"';
	}
	$radiopast .= '>';
	$radioexact = '<input type="radio" name="range" value="exact"'; 
	if ((!isset($ilance->GPC['action']) OR (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "exact")))
	{
		$radioexact .= ' checked="checked"';
	}
	$radioexact .= '>';
	$reportrange = '<select name="rangepast" style="font-family: verdana"><option value="-1 day"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 day")
	{
		$reportrange .= ' selected="selected"'; 
	}
	$reportrange .= '>The Past Day</option><option value="-1 week"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 week")
	{
		$reportrange .= ' selected="selected"'; 
	}
	$reportrange .= '>The Past Week</option><option value="-1 month"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 month")
	{
		$reportrange .= ' selected="selected"';
	}
	$reportrange .= '>The Past Month</option><option value="-1 year"'; 
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 year")
	{
		$reportrange .= ' selected="selected"';
	}
	$reportrange .= '>The Past Year</option></select>';
	
	// #### advanced reporting from range ##########################
	$reportfromrange = $ilance->admincp->print_from_to_date_range();
	
	// #### order by ascending / desending #########################
	$reportorderby = '<input type="radio" name="order" value="ascending"';
	if (!isset($ilance->GPC['action']) OR $ilance->GPC['order'] == "ascending")
	{
		$reportorderby .= ' checked="checked"'; 
	}
	$reportorderby .= '>' . $phrase['_ascending'] . ' &nbsp;&nbsp;&nbsp; <input type="radio" name="order" value="descending"';
	if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == "descending")
	{
		$reportorderby .= ' checked="checked"';
	}
	$reportorderby .= '>' . $phrase['_descending'];
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','reportorderby','reportfromrange','reportrange','radiopast','radioexact','reportcolumns','reportaction','reportshow','customprevnext','reportoutput','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'reports.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','reports'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### CURRENCY MANAGEMENT ####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'currencies')
{
	// #### REMOVE CURRENCY HANDLER ################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-currency' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		if ($ilconfig['globalserverlocale_defaultcurrency'] != $ilance->GPC['id'])
		{
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "currency
				WHERE currency_id = '" . intval($ilance->GPC['id']) . "'
			");
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET currencyid = '" . $ilconfig['globalserverlocale_defaultcurrency'] . "'
				WHERE currencyid = '" . intval($ilance->GPC['id']) . "'
			");
			
			print_action_success($phrase['_the_selected_currency_rate_was_deleted_successfully'], $ilpage['accounting'] . '?cmd=currencies');
			exit();
		}
		else
		{
			print_action_failed($phrase['_you_cannot_delete_this_currency_because_it_appears_it_is_associated_as_the_main_marketplace_currency'], $ilpage['accounting'] . '?cmd=currencies');
			exit();        
		}
	}
	
	// #### UPDATE CURRENCIES HANDLER ##############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-currency')
	{
		$query = '';
		foreach ($ilance->GPC['currency'] AS $currencyid)
		{
			foreach ($currencyid AS $key => $value)
			{
				if ($key == 'currency_id')
				{
					$query .= " time = '" . DATETIME24H . "' WHERE currency_id = '" . intval($value) . "'";
				}
				else
				{
					$query .= "$key = '" . $ilance->db->escape_string($value) . "', ";
				}
			}
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "currency
				SET " . $query . "
			");
			
			$query = '';
		}
		
		print_action_success($phrase['_currency_rates_were_updated_successfully_changes_should_take_effect_immediately'], $ilpage['accounting'] . '?cmd=currencies');
		exit();
	}
	
	// #### CREATE NEW CURRENCY HANDLER ####################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-currency' AND !empty($ilance->GPC['currency_name']) AND !empty($ilance->GPC['rate']) AND !empty($ilance->GPC['currency_abbrev']) AND !empty($ilance->GPC['symbol_left']))
	{
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "currency
			(currency_id, currency_abbrev, currency_name, rate, time, isdefault, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places)
			VALUES(
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['currency_abbrev']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['currency_name']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['rate']) . "',
			'" . DATETIME24H . "',
			'0',
			'" . $ilance->db->escape_string($ilance->GPC['symbol_left']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['symbol_right']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['decimal_point']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['thousands_point']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['decimal_places']) . "')
		");
		
		print_action_success($phrase['_the_new_currency_rate_was_successfully_created_within_the_database'], $ilpage['accounting'] . '?cmd=currencies');
		exit();
	}
	else
	{
		$area_title = $phrase['_currency_management'];
		$page_title = SITE_NAME . ' - ' . $phrase['_currency_management'];
    
		($apihook = $ilance->api('admincp_currency_settings')) ? eval($apihook) : false;
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=currencies', $_SESSION['ilancedata']['user']['slng']);
		
		$result = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "currency
			WHERE isdefault = '1'
		");
		if ($ilance->db->num_rows($result) > 0)
		{
			$row_count = 0;                            
			while ($res = $ilance->db->fetch_array($result))
			{
				$res['currencyname'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_name]" value="' . stripslashes($res['currency_name']) . '" style="width: 100%; font-family: verdana" />';
				$res['rate'] = '<input type="text" name="currency[' . $res['currency_id'] . '][rate]" value="' . stripslashes($res['rate']) . '" style="width: 100%; font-family: verdana" />';
				$res['abbrev'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_abbrev]" value="' . stripslashes($res['currency_abbrev']) . '" style="width: 100%; font-family: verdana" />';
				$res['symbolleft'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_left]" value="' . stripslashes($res['symbol_left']) . '" style="width: 100%; font-family: verdana" />';
				$res['symbolright'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_right]" value="' . stripslashes($res['symbol_right']) . '" style="width: 100%; font-family: verdana" />';
				$res['decimalpoint'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_point]" value="' . stripslashes($res['decimal_point']) . '" style="width: 100%; font-family: verdana" />';
				$res['thousandspoint'] = '<input type="text" name="currency[' . $res['currency_id'] . '][thousands_point]" value="' . stripslashes($res['thousands_point']) . '" style="width: 100%; font-family: verdana" />';
				$res['decimalplaces'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_places]" value="' . stripslashes($res['decimal_places']) . '" style="width: 100%; font-family: verdana" />';
				$res['actions'] = '<input type="hidden" name="currency[' . $res['currency_id'] . '][currency_id]" value="' . $res['currency_id'] . '" /> <a href="'.$ilpage['accounting'] . '?cmd=currencies&amp;subcmd=_remove-currency&amp;id=' . $res['currency_id'] . '" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1'; 
				$defaultcurrencies[] = $res;
				$row_count++;
			}
		}
		else
		{
			$show['no_defaultcurrencies'] = true;
		}
			
		$resultcustom = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "currency
			WHERE isdefault != '1'
		");
		if ($ilance->db->num_rows($resultcustom) > 0)
		{
			$row_count = 0;                            
			while ($res = $ilance->db->fetch_array($resultcustom))
			{
				$res['currencyname'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_name]" value="' . stripslashes($res['currency_name']) . '" style="width: 100%; font-family: verdana" />';
				$res['rate'] = '<input type="text" name="currency[' . $res['currency_id'] . '][rate]" value="' . stripslashes($res['rate']) . '" style="width: 100%; font-family: verdana" />';
				$res['abbrev'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_abbrev]" value="' . stripslashes($res['currency_abbrev']) . '" style="width: 100%; font-family: verdana" />';
				$res['symbolleft'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_left]" value="' . stripslashes($res['symbol_left']) . '" style="width: 100%; font-family: verdana" />';
				$res['symbolright'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_right]" value="' . stripslashes($res['symbol_right']) . '" style="width: 100%; font-family: verdana" />';
				$res['decimalpoint'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_point]" value="' . stripslashes($res['decimal_point']) . '" style="width: 100%; font-family: verdana" />';
				$res['thousandspoint'] = '<input type="text" name="currency[' . $res['currency_id'] . '][thousands_point]" value="' . stripslashes($res['thousands_point']) . '" style="width: 100%; font-family: verdana" />';
				$res['decimalplaces'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_places]" value="' . stripslashes($res['decimal_places']) . '" style="width: 100%; font-family: verdana" />';
				$res['actions'] = '<input type="hidden" name="currency[' . $res['currency_id'] . '][currency_id]" value="' . $res['currency_id'] . '" /> <a href="'.$ilpage['accounting'] . '?cmd=currencies&amp;subcmd=_remove-currency&amp;id=' . $res['currency_id'] . '" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$customcurrencies[] = $res;
				$row_count++;
			}
		}
		else
		{
			$show['no_customcurrencies'] = true;
		}
			
		$currencyname = '<input type="text" name="currency_name" value="' . stripslashes($res['currency_name']) . '" style="width: 100%; font-family: verdana">';
		$rate = '<input type="text" name="rate" value="' . stripslashes($res['rate']) . '" style="width: 100%; font-family: verdana" />';
		$abbrev = '<input type="text" name="currency_abbrev" value="' . stripslashes($res['currency_abbrev']) . '" style="width: 100%; font-family: verdana" />';
		$symbolleft = '<input type="text" name="symbol_left" value="' . stripslashes($res['symbol_left']) . '" style="width: 100%; font-family: verdana" />';
		$symbolright = '<input type="text" name="symbol_right" value="' . stripslashes($res['symbol_right']) . '" style="width: 100%; font-family: verdana" />';
		$decimalpoint = '<input type="text" name="decimal_point" value="' . stripslashes($res['decimal_point']) . '" style="width: 100%; font-family: verdana" />';
		$thousandspoint = '<input type="text" name="thousands_point" value="' . stripslashes($res['thousands_point']) . '" style="width: 100%; font-family: verdana" />';
		$decimalplaces = '<input type="text" name="decimal_places" value="' . stripslashes($res['decimal_places']) . '" style="width: 100%; font-family: verdana" />';
		
		$global_currencysettings = $ilance->admincp->construct_admin_input('globalserverlocalecurrency', $ilpage['accounting'] . '?cmd=currencies');
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','global_currencysettings','symbolleft','symbolright','decimalpoint','thousandspoint','decimalplaces','currencyname','rate','abbrev','symbol','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_accounting_currencies_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'currencies.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','defaultcurrencies','customcurrencies'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}

// #### REMOVING A TRANSACTION #################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-invoice' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "attachment
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "buynow_orders
		SET invoiceid = '0',
		escrowfeeinvoiceid = '0',
		escrowfeebuyerinvoiceid = '0',
		fvfinvoiceid = '0',
		fvfbuyerinvoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "portfolio
		SET featured_invoiceid = '0'
		WHERE featured_invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "profile_answers
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects
		SET insertionfee = '0',
		ifinvoiceid = '0',
		isifpaid = '0'
		WHERE ifinvoiceid = '" . intval($ilance->GPC['id']) . "'
	", 0, null, __FILE__, __LINE__);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects
		SET fvf = '0',
		fvfinvoiceid = '0',
		isfvfpaid = '0'
		WHERE fvfinvoiceid = '" . intval($ilance->GPC['id']) . "'
	", 0, null, __FILE__, __LINE__);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects_escrow
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	// this is a final value fee.. update auction listing table
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects_escrow
		SET isfeepaid = '0',
		feeinvoiceid = '0'
		WHERE feeinvoiceid = '" . intval($ilance->GPC['id']) . "'
	", 0, null, __FILE__, __LINE__);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects_escrow
		SET isfee2paid = '0',
		fee2invoiceid = '0'
		WHERE fee2invoiceid = '" . intval($ilance->GPC['id']) . "'
	", 0, null, __FILE__, __LINE__);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "referral_data
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "subscription_user
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "subscription_user_exempt
		SET invoiceid = '0'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	($apihook = $ilance->api('admincp_remove_invoice_end')) ? eval($apihook) : false;
	
	print_action_success($phrase['_the_selected_invoice_was_removed_from_the_system'], $ilpage['accounting']);
	exit();
}

// #### MARKING A CHARITY AS BEING PAID ########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-charity-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET ischaritypaid = '1'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
			AND isdonationfee = '1'
	");
	
	$charityid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "charityid");
	if ($charityid > 0)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "charities
			SET donations = donations + 1,
			earnings = earnings + " . $ilance->db->escape_string($ilance->GPC['amount']) . "
			WHERE charityid = '" . intval($charityid) . "'
				LIMIT 1
		");
	}
	
	($apihook = $ilance->api('admincp_mark_charity_invoice_paid_end')) ? eval($apihook) : false;
	
	print_action_success($phrase['_the_amount_owing_from_this_users_payment_has_been_marked'], $ilpage['accounting']);
	exit();
}

// #### MARKING A TRANSACTION AS PAID ##########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-invoice-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET status = 'paid',
		paid = totalamount,
		paiddate = '" . DATETIME24H . "'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		
		if ($res_invoice['isif'])
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isifpaid = '1'
				WHERE ifinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($res_invoice['isfvf'])
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isfvfpaid = '1'
				WHERE fvfinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($res_invoice['isescrowfee'])
		{
			// this is a final value fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfeepaid = '1'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND feeinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfee2paid = '1'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND fee2invoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		
		($apihook = $ilance->api('admincp_mark_invoice_paid_end')) ? eval($apihook) : false;
		
		unset($res_invoice);
	}
	
	$sql = $ilance->db->query("
		SELECT invoiceid, projectid, buynowid
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		
		// this could also be a payment from the "seller" for an unpaid "buy now" escrow fee OR unpaid "buy now" fvf.
		// let's check the buynow order table to see if we have a matching invoice to update as "ispaid"..
		// this scenerio would kick in once a buyer or seller deposits funds, this script runs and tries to pay the unpaid fees automatically..
		// at the same time we need to update the buy now order table so the presentation layer knows what's paid, what's not.
		$buynowcheck = $ilance->db->query("
			SELECT escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . $res_invoice['projectid'] . "'
				AND orderid = '" . $res_invoice['buynowid'] . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($buynowcheck) > 0)
		{
			$resbuynow = $ilance->db->fetch_array($buynowcheck);
			
			// #### handle seller escrow fee ###############
			if ($res_invoice['invoiceid'] == $resbuynow['escrowfeeinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeepaid = '1'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			
			// #### handle buyer escrow fee ################
			else if ($res_invoice['invoiceid'] == $resbuynow['escrowfeebuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeebuyerpaid = '1'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			
			// #### handle seller fvf's for items sold via buy now
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfpaid = '1'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			
			// #### handle buyer fvf for items bought (not used at the moment as sellers are only charged fvf's)..
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfbuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfbuyerpaid = '1'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
	}
	
	// #### handle subscription activation logic if we can #########
	$sql = $ilance->db->query("
		SELECT user_id, invoicetype, subscriptionid, paymethod
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		if ($res_invoice['invoicetype'] == 'subscription' AND $res_invoice['subscriptionid'] > 0)
		{
			// #### activate currently selected subscription for user as it may be inactive due to payment and admin is marking invoice as paid..
			$ilance->subscription = construct_object('api.subscription');
			
			$units = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $res_invoice['subscriptionid'] . "'", "units");
			$length = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $res_invoice['subscriptionid'] . "'", "length");
			$roleid = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $res_invoice['subscriptionid'] . "'", "roleid");
			$cost = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $res_invoice['subscriptionid'] . "'", "cost");
			$startdate = DATETIME24H;
			$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($units, $length));
			
			// #### activate subscription plan for user ####
			activate_subscription_plan($res_invoice['user_id'], $startdate, $renewdate, 0, intval($ilance->GPC['id']), $res_invoice['subscriptionid'], $res_invoice['paymethod'], $roleid, $cost);
			
			// #### referral tracker for this user #########
			update_referral_action('subscription', $res_invoice['user_id']);
		}
		unset($res_invoice);
	}
	
	// #### handle donation nonprofit transactions
	$sql = $ilance->db->query("
		SELECT amount, isdonationfee, projectid
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		if ($res_invoice['isdonationfee'])
		{
			$sql2 = $ilance->db->query("
				SELECT charityid
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . $res_invoice['projectid'] . "'
			");
			if ($ilance->db->num_rows($sql2) > 0)
			{
				$resproject = $ilance->db->fetch_array($sql2);
				
				if ($resproject['charityid'] > 0)
				{
					// this is a final value donation fee.. update auction listing table
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET donermarkedaspaid = '1',
						donermarkedaspaiddate = '" . DATETIME24H . "'
						WHERE project_id = '" . $res_invoice['projectid'] . "'
					", 0, null, __FILE__, __LINE__);
					
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "charities
						SET donations = donations + 1,
						earnings = earnings + $res_invoice[amount]
						WHERE charityid = '" . $resproject['charityid'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
			}
		}
	}
	
	print_action_success($phrase['_the_selected_invoice_was_marked_as_being_paid_in_full'], $ilpage['accounting']);
	exit();
}



// #### MARKING A TRANSACTION AS UNPAID ########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-invoice-unpaid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET status = 'unpaid',
		paid = '0.00',
		paiddate = '0000-00-00 00:00:00'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		if ($res_invoice['isif'])
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isifpaid = '0'
				WHERE ifinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($res_invoice['isfvf'])
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isfvfpaid = '0'
				WHERE fvfinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($res_invoice['isescrowfee'])
		{
			// this is a final value fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfeepaid = '1'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND feeinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfee2paid = '1'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND fee2invoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		
		($apihook = $ilance->api('admincp_mark_invoice_unpaid_end')) ? eval($apihook) : false;
		
		unset($res_invoice);
	}
	
	$sql = $ilance->db->query("
		SELECT invoiceid, projectid, buynowid
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		
		// this could also be a payment from the "seller" for an unpaid "buy now" escrow fee OR unpaid "buy now" fvf.
		// let's check the buynow order table to see if we have a matching invoice to update as "ispaid"..
		// this scenerio would kick in once a buyer or seller deposits funds, this script runs and tries to pay the unpaid fees automatically..
		// at the same time we need to update the buy now order table so the presentation layer knows what's paid, what's not.
		$buynowcheck = $ilance->db->query("
			SELECT escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . $res_invoice['projectid'] . "'
				AND orderid = '" . $res_invoice['buynowid'] . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($buynowcheck) > 0)
		{
			$resbuynow = $ilance->db->fetch_array($buynowcheck);
			if ($res_invoice['invoiceid'] == $resbuynow['escrowfeeinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeepaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['escrowfeebuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeebuyerpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfbuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfbuyerpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
	}
	
	// #### handle donation nonprofit transactions
	$sql = $ilance->db->query("
		SELECT amount, isdonationfee, projectid
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		if ($res_invoice['isdonationfee'])
		{
			$sql2 = $ilance->db->query("
				SELECT charityid
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . $res_invoice['projectid'] . "'
			");
			if ($ilance->db->num_rows($sql2) > 0)
			{
				$resproject = $ilance->db->fetch_array($sql2);
				
				if ($resproject['charityid'] > 0)
				{
					// this is a final value donation fee.. update auction listing table
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET donermarkedaspaid = '0',
						donermarkedaspaiddate = '0000-00-00 00:00:00'
						WHERE project_id = '" . $res_invoice['projectid'] . "'
					", 0, null, __FILE__, __LINE__);
					
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "charities
						SET donations = donations - 1,
						earnings = earnings - $res_invoice[amount]
						WHERE charityid = '" . $resproject['charityid'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
			}
		}
	}
	
	print_action_success($phrase['_the_selected_invoice_was_marked_as_being_unpaid'], $ilpage['accounting']);
	exit();
}

// #### INVOICE CANCELLATION ###################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_cancel-invoice' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET status = 'cancelled'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		if ($res_invoice['isif'])
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isifpaid = '0'
				WHERE ifinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($res_invoice['isfvf'])
		{
			// this is an insertion fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET isfvfpaid = '0'
				WHERE fvfinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($res_invoice['isescrowfee'])
		{
			// this is a final value fee.. update auction listing table
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfeepaid = '0'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND feeinvoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET isfee2paid = '0'
				WHERE project_id = '" . $res_invoice['projectid'] . "'
					AND fee2invoiceid = '" . intval($ilance->GPC['id']) . "'
			", 0, null, __FILE__, __LINE__);
		}
		
		($apihook = $ilance->api('admincp_mark_invoice_cancelled_end')) ? eval($apihook) : false;
		
		unset($res_invoice);
	}
	
	$sql = $ilance->db->query("
		SELECT invoiceid, projectid, buynowid
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res_invoice = $ilance->db->fetch_array($sql);
		
		// this could also be a payment from the "seller" for an unpaid "buy now" escrow fee OR unpaid "buy now" fvf.
		// let's check the buynow order table to see if we have a matching invoice to update as "ispaid"..
		// this scenerio would kick in once a buyer or seller deposits funds, this script runs and tries to pay the unpaid fees automatically..
		// at the same time we need to update the buy now order table so the presentation layer knows what's paid, what's not.
		$buynowcheck = $ilance->db->query("
			SELECT escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . $res_invoice['projectid'] . "'
				AND orderid = '" . $res_invoice['buynowid'] . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($buynowcheck) > 0)
		{
			$resbuynow = $ilance->db->fetch_array($buynowcheck);
			if ($res_invoice['invoiceid'] == $resbuynow['escrowfeeinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeepaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['escrowfeebuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now escrow fee
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isescrowfeebuyerpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfinvoiceid'])
			{
				// invoice being paid is from seller paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			else if ($res_invoice['invoiceid'] == $resbuynow['fvfbuyerinvoiceid'])
			{
				// invoice being paid is from buyer paying a buy now fvf
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET isfvfbuyerpaid = '0'
					WHERE orderid = '" . $res_invoice['buynowid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
	}
	
	print_action_success($phrase['_the_selected_invoice_was_cancelled'], $ilpage['accounting']);
	exit();
}
// #### MAIN INVOICE PAGE ######################################################
else
{
	$area_title = $phrase['_invoice_management'];
	$page_title = SITE_NAME . ' - ' . $phrase['_invoice_management'];
	
	($apihook = $ilance->api('admincp_accounting_settings')) ? eval($apihook) : false;
	
	// #### print sub nav ##########################################
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'], $_SESSION['ilancedata']['user']['slng']);
	
	// #### display date to and from range #########################
	$reportfromrange = $ilance->admincp->print_from_to_date_range();
	
	$tab = (isset($ilance->GPC['tab'])) ? intval($ilance->GPC['tab']) : '0';
	
	// #### build our sql for transactions #########################
	$sqlinvoicetype = "AND i.invoicetype != 'p2b' AND i.invoicetype != 'buynow'";
	
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoices' AND !empty($ilance->GPC['invoicetype']))
	{
		switch ($ilance->GPC['invoicetype'])
		{
			case 'subscription':
			{
				$sqlinvoicetype = "AND i.invoicetype = '" . $ilance->db->escape_string($ilance->GPC['invoicetype']) . "' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'credential':
			{
				$sqlinvoicetype = "AND i.invoicetype = '" . $ilance->db->escape_string($ilance->GPC['invoicetype']) . "' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'portfolio':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.isportfoliofee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'enhancements':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.isenhancementfee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'fvf':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND isfvf = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'insfee':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.isif = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'escrow':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.isescrowfee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'withdraw':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.iswithdrawfee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'p2b':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'p2b' AND i.p2b_user_id > 0 AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;        
			}
			case 'p2bfee':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.isp2bfee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			// expenses
			case 'tax':
			{
				$sqlinvoicetype = "AND i.istaxable = '1' AND i.taxamount > 0 AND i.invoicetype != 'credit' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'registerbonus':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'credit' AND i.isregisterbonus = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			// loses
			case 'refund':
			{
				$sqlinvoicetype = "AND i.invoicetype = '" . $ilance->db->escape_string($ilance->GPC['invoicetype']) . "' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			case 'cancelled':
			{
				$sqlinvoicetype = "AND i.status = '" . $ilance->db->escape_string($ilance->GPC['invoicetype']) . "' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			// disputed
			case 'disputed':
			{
				$sqlinvoicetype = "AND i.indispute = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			// nonprofit donation fees collected
			case 'donationfee':
			{
				$sqlinvoicetype = "AND i.isdonationfee = '1' AND i.iswithdraw = '0' AND i.isdeposit = '0'";
				break;
			}
			// deposits
			case 'deposits':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'credit' AND i.isdeposit = '1'";
				break;
			}
			// withdraws
			case 'withdraws':
			{
				$sqlinvoicetype = "AND i.invoicetype = 'debit' AND i.iswithdraw = '1'";
				break;
			}
		}
		
		($apihook = $ilance->api('admincp_accounting_invoicetype_switch_end')) ? eval($apihook) : false;
	}
	else
	{
		$sqlinvoicetype .= " AND i.invoicetype != 'credit'";
	}
	
	$sqlinvoiceid = '';
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoices' AND !empty($ilance->GPC['invoiceid']) AND $ilance->GPC['invoiceid'] > 0)
	{
		$invid = intval($ilance->GPC['invoiceid']);
		$sqlinvoiceid = "AND i.invoiceid = '" . intval($invid) . "'";
	}
		
	// via txn id?
	$sqlinvoicetxnid = '';
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoices' AND !empty($ilance->GPC['transactionid']))
	{
		$sqlinvoicetxnid = "AND i.transactionid = '" . $ilance->db->escape_string($ilance->GPC['transactionid']) . "'";
	}
		
	// invoice status (unpaid showing as default)
	$sqlinvoicestatus = "AND i.status != ''";
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'invoices' AND !empty($ilance->GPC['status']))
	{
		$sqlinvoicestatus = "AND i.status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "'";
	}
	
	// #### date range exactly as entered
	$sqldaterange = '';
	if (isset($ilance->GPC['range_start']) AND isset($ilance->GPC['range_end']))
	{
		$startdate = print_array_to_datetime($ilance->GPC['range_start']);
		$startdate = substr($startdate, 0, -9);
		
		$enddate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
		$enddate = substr($enddate, 0, -9);
		
		$sqldaterange = " AND (createdate <= '" . $ilance->db->escape_string($enddate) . " " . TIMENOW . "' AND createdate >= '" . $ilance->db->escape_string($startdate) . "')";
	}
	$sqlextra = '';
	
	$amounttotal = $paidtotal = 0;
		
	// #### PAID INVOICES ##################################################
	$pp = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : '10';
	$ilance->GPC['pp'] = $pp;
	$ilance->GPC['pp'] = (!isset($ilance->GPC['pp']) OR isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] < 0) ? 10 : intval($ilance->GPC['pp']);
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	
	$orderlimit = ' ORDER BY i.invoiceid DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilance->GPC['pp']) . ',' . $ilance->GPC['pp'];

	$sqlpaid = $ilance->db->query("
		SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.totalamount, i.paid, i.status, i.invoicetype, i.p2b_user_id, i.createdate, i.duedate, i.paiddate, i.paymethod, i.paymentgateway, i.transactionid, i.p2b_paymethod, i.paymentgateway, i.isdonationfee, i.ischaritypaid, i.currency_id
		FROM " . DB_PREFIX . "users AS c
		LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
		WHERE i.archive = '0'
		    AND i.paymethod != 'external'
		    AND i.invoicetype != 'escrow'
		    AND i.totalamount > 0
		    $sqlinvoicetype
		    $sqlinvoicestatus
		    $sqlinvoiceid
		    $sqlinvoicetxnid
		    $sqldaterange
		    $sqlextra
		    $orderlimit
	");
	
	$sqlpaid2 = $ilance->db->query("
		SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.totalamount, i.paid, i.status, i.invoicetype, i.p2b_user_id, i.createdate, i.duedate, i.paiddate, i.paymethod, i.paymentgateway, i.transactionid, i.p2b_paymethod, i.paymentgateway, i.isdonationfee, i.ischaritypaid, i.currency_id
		FROM " . DB_PREFIX . "users AS c
		LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
		WHERE i.archive = '0'
		    AND i.paymethod != 'external'
		    AND i.invoicetype != 'escrow'
		    AND i.totalamount > 0
		    $sqlinvoicetype
		    $sqlinvoicestatus
		    $sqlinvoiceid
		    $sqlinvoicetxnid
		    $sqldaterange
		    $sqlextra
	");
	if ($ilance->db->num_rows($sqlpaid) > 0)
	{
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($sqlpaid, DB_ASSOC))
		{
			if ($res['status'] == 'paid')
			{
				//$res['status'] = '<span style="color:black"><strong>' . $phrase['_paid'] . '</strong></span><div><span class="smaller"><a href="' . $ilpage['accounting'] . '?subcmd=_mark-invoice-unpaid&amp;id=' . $res['invoiceid'] . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')">' . $phrase['_mark_as_unpaid'] . '</a></span></div>';
				$res['status'] = '<div class="black"><strong>' . $phrase['_paid'] . '</strong></div><div><input type="button" value="' . $phrase['_mark_as_unpaid'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_mark-invoice-unpaid&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" /></div>';
				$res['cancel'] = '<div><input type="button" value="' . $phrase['_cancel'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_cancel-invoice&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
			else if ($res['status'] == 'cancelled')
			{
				$res['status'] = '<strong>' . $phrase['_cancelled'] . '</div>';
				$res['cancel'] = '';
			}
			else
			{
				$res['status'] = '<div class="red"><strong>' . $phrase['_unpaid'] . '</strong></div><div><input type="button" value="' . $phrase['_mark_as_paid'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_mark-invoice-paid&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" /></div>';
				$res['cancel'] = '<div><input type="button" value="' . $phrase['_cancel'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_cancel-invoice&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
			
			//$res['remove'] = '<a href="'.$ilpage['accounting'] . '?subcmd=_remove-invoice&amp;id='.$res['invoiceid'].'" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')">'.$phrase['_remove'].'</a>';
			$res['remove'] = '<div><input type="button" value="' . $phrase['_remove'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['accounting'] . '?subcmd=_remove-invoice&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" /></div>';
			
			$amounttotal += $res['totalamount'];
			$paidtotal += ($res['paid']);
			
			$res['amount'] = $ilance->currency->format($res['totalamount'], $res['currency_id']);
			$res['paid'] = $ilance->currency->format($res['paid'], $res['currency_id']);
			$res['due'] = ($res['duedate'] != "0000-00-00 00:00:00") ? print_date($res['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0) : '-';
			$res['paiddate'] = ($res['paiddate'] != "0000-00-00 00:00:00") ? print_date($res['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0) : $phrase['_never'];
			$res['createdate'] = print_date($res['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
			$res['archive'] = '<a href="'.$ilpage['accounting'] . '?subcmd=_archive-invoice&amp;id='.$res['invoiceid'].'" onclick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')">'.$phrase['_archive'].'</a>';
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			if (($res['invoicetype'] == 'p2b' OR $res['invoicetype'] == 'p2bfee') AND $res['user_id'] > 0 AND $res['p2b_user_id'] > 0)
			{
				$invoiceto = fetch_user('username', $res['p2b_user_id']);
				$res['invoicetype'] = $phrase['_generated_by'] . ' <span class="blue"><a href="' . $ilpage['subscribers'] . '?cmd=_update-customer&amp;id=' . $res['p2b_user_id'] . '">' . $invoiceto . '</a></span>';
				if ($res['invoicetype'] == 'p2bfee')
				{
					$res['method'] = print_paymethod_icon($res['paymethod']);
					$res['gateway'] = mb_strtoupper($res['paymentgateway']);
				}
				else
				{
					$res['method'] = ilance_htmlentities($res['p2b_paymethod']);
					$res['gateway'] = $phrase['_none'];
				}
			}
			else
			{
				$res['invoicetype'] = ucfirst($res['invoicetype']);
				$res['method'] = print_paymethod_icon($res['paymethod']);
				if (!empty($res['paymentgateway']))
				{
					$res['gateway'] = mb_strtoupper($res['paymentgateway']);
				}
				else
				{
					$res['gateway'] = $phrase['_none'];
				}
			}
			
			if ($res['isdonationfee'])
			{
				if ($res['ischaritypaid'])
				{
					$res['extrabutton'] = '&nbsp;&nbsp;<input type="button" value=" ' . $phrase['_mark_charity_paid'] . ' " onclick="location.href=\'' . HTTP_SERVER_ADMIN . $ilpage['accounting'] . '?subcmd=_mark-charity-paid&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" disabled="disabled" />';
				}
				else
				{
					$res['extrabutton'] = '&nbsp;&nbsp;<input type="button" value=" ' . $phrase['_mark_charity_paid'] . ' " onclick="location.href=\'' . HTTP_SERVER_ADMIN . $ilpage['accounting'] . '?subcmd=_mark-charity-paid&amp;id=' . $res['invoiceid'] . '\'" class="buttons" style="font-size:10px" />';
				}
					
				$res['icon'] = '<span style="float:left; padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nonprofits.gif" border="0" alt="" id="" /></span>';
			}
			else
			{
				$res['extrabutton'] = '';
				$res['icon'] = '';
			}
			
			$invoices[] = $res;
			$row_count++;
		}
		
		$numberpaid = $ilance->db->num_rows($sqlpaid2);
		
		$invoiceid = isset($ilance->GPC['invoiceid']) ? intval($ilance->GPC['invoiceid']) : '';
		$transactionid = isset($ilance->GPC['transactionid']) ? intval($ilance->GPC['transactionid']) : '';
		$invoicetype = isset($ilance->GPC['invoicetype']) ? $ilance->GPC['invoicetype'] : '';
		$status = isset($ilance->GPC['status']) ? $ilance->GPC['status'] : '';
		$rangestart0 = isset($ilance->GPC['range_start'][0]) ? $ilance->GPC['range_start'][0] : '01';
		$rangestart1 = isset($ilance->GPC['range_start'][1]) ? $ilance->GPC['range_start'][1] : '01';
		$rangestart2 = isset($ilance->GPC['range_start'][2]) ? $ilance->GPC['range_start'][2] : date("Y");
		$rangeend0 = isset($ilance->GPC['range_end'][0]) ? $ilance->GPC['range_end'][0] : date("m");
		$rangeend1 = isset($ilance->GPC['range_end'][1]) ? $ilance->GPC['range_end'][1] : date("d");
		$rangeend2 = isset($ilance->GPC['range_end'][2]) ? $ilance->GPC['range_end'][2] : date("Y");
		
		$paidprevnext = print_pagnation($numberpaid, $pp, $ilance->GPC['page'], ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=invoices&amp;invoiceid=" . $invoiceid . "&amp;transactionid=" . $transactionid . "&amp;invoicetype=" . $invoicetype . "&amp;status=" . $status . "&amp;range_start[0]=" . $rangestart0 . "&amp;range_start[1]=" . $rangestart1 . "&amp;range_start[2]=" . $rangestart2 . "&amp;range_end[0]=" . $rangeend0 . "&amp;range_end[1]=" . $rangeend1 . "&amp;range_end[2]=" . $rangeend2 . "&amp;tab=0", 'page');
	}
	else
	{
		$show['no_invoices'] = true;
		$numberpaid = 0;
	}
	
	$amounttotal = $ilance->currency->format($amounttotal);
	$paidtotal = $ilance->currency->format($paidtotal);

	// invoice type pulldown
	$ilance->GPC['invoicetype'] = isset($ilance->GPC['invoicetype']) ? $ilance->GPC['invoicetype'] : '';
	$ilance->GPC['status'] = isset($ilance->GPC['status']) ? $ilance->GPC['status'] : '';
	
	$invoice_type_pulldown = $ilance->admincp->print_invoicetype_pulldown($ilance->GPC['invoicetype']);
	
	$configuration_invoicesystem  = $ilance->admincp->construct_admin_input('invoicesystem', $ilpage['accounting']);
	
	// revenue balance sheet
	$revenuebalance = $ilance->admincp->construct_revenue_balance();
	$revenuebalance = array($revenuebalance);
	
	$pprint_array = array('reportfromrange','invid','buildversion','ilanceversion','login_include_admin','amounttotal','paidtotal','invoice_status_pulldown','tab','configuration_invoicesystem','numberarchived','archivedprevnext','invoice_type_pulldown','scheduledprevnext','cancelledprevnext','numbercancelled','numberscheduled','numberpaid','numberunpaid','paidprevnext','unpaidprevnext','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_accounting_invoices_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'invoices.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','invoices','revenuebalance'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>