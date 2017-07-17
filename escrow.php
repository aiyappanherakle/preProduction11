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
        'buying',
        'selling',
        'rfp',
        'search',
        'feedback',
        'accounting',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
        'countries',
        'tabfx',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION','escrow');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### require shipping backend ###############################################
require_once(DIR_CORE . 'functions_shipping.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[escrow]" => $ilcrumbs["$ilpage[escrow]"]);

$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();

$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
$ilance->GPC['subcmd'] = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';
refresh(HTTPS_SERVER . 'buy.php?cmd=buynow');
exit();
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	// #### SERVICE BUYER ESCROW HANDLER ###################################
	if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['sub']) AND $uncrypted['sub'] == 'rfp-escrow' AND isset($uncrypted['subcmd']) AND isset($uncrypted['id']))
        {
                // #### define top header nav ##################################
                $topnavlink = array(
                        'mycp'
                );
                
                $show['widescreen'] = true;
		
		// #### service buyer cancelling release of funds and returning them to buyers account balance (from escrow already paid into)
		// #### this will refund his account balance based on escrow fees paid already otherwise the escrow fee transaction becomes cancelled
		if ($uncrypted['subcmd'] == '_cancel-release' AND $uncrypted['id'] > 0 AND $ilconfig['escrowsystem_enabled'])
                {
			$ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
                        $success = $ilance->escrow_handler->escrow_handler('buyercancelescrow', 'service', $uncrypted['id'], false);
			
			if ($success)
			{
				$area_title = $phrase['_rfp_escrow_management'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_rfp_escrow_management'];
				
				print_notice($area_title, $phrase['_you_have_cancelled_funds_within_your_escrow_account_for_a_particular_service_auction'], HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow', $phrase['_return_to_the_previous_menu']);
                                exit();
			}
		}
		
		// #### service buyer releasing funds within escrow to service provider account balance
		else if ($uncrypted['subcmd'] == '_confirm-release' AND $uncrypted['id'] > 0 AND $ilconfig['escrowsystem_enabled'])
                {
			$ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
                        $success = $ilance->escrow_handler->escrow_handler('buyerconfirmrelease', 'service', $uncrypted['id'], false);
			
			if ($success)
			{
				$area_title = $phrase['_rfp_escrow_management'] . ' - ' . $phrase['_release_of_funds_complete'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_rfp_escrow_management'] . ' - ' . $phrase['_release_of_funds_complete'];
				
				print_notice($phrase['_funds_released_escrow_process_complete'], $phrase['_you_have_successfully_released_funds_within_the_escrow'] . '<br /><br />' . $phrase['_please_contact_customer_support'], $ilpage['accounting'], $phrase['_my_account']);
				exit();
			}
		}
	}

	// #### SERVICE BUYER ESCROW MANAGEMENT : PAYOUTS ######################
	else if ($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'rfp-escrow' AND empty($ilance->GPC['subcmd']) AND $ilconfig['escrowsystem_enabled'])
        {
		$ilance->auction = construct_object('api.auction');
		$ilance->auction_award = construct_object('api.auction_award');
		
                // #### define top header nav ##################################
                $topnavlink = array(
                        'mycp',
                        'servicebuyingescrow'
                );
                
                $show['widescreen'] = true;
		
		$area_title = $phrase['_rfp_escrow_management'];
		$page_title = SITE_NAME . ' - ' . $phrase['_rfp_escrow_management'];
                
                $navcrumb = array();
                $navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
                $navcrumb[HTTP_SERVER . "$ilpage[buying]?cmd=management"] = $phrase['_buying_activity'];
                $navcrumb[""] = $phrase['_service_escrow_buying_activity'];
                
		$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
                
		$limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];

                // #### LISTING PERIOD #########################################
                require_once(DIR_CORE . 'functions_search.php');
                require_once(DIR_CORE . 'functions_tabs.php');
                
                $ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
                $extra = '&amp;period=' . $ilance->GPC['period'];
                $periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');

                $servicetabs = print_buying_activity_tabs('rfp-escrow', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);

		$condition = $condition2 = '';
                
		$numberrows = $ilance->db->query("
                        SELECT p.project_id, p.project_state, p.user_id AS owner_id, p.project_title, p.description, p.currencyid, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.fee, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.isfeepaid, e.feeinvoiceid, b.bid_id, b.user_id AS bidder_id, b.bidstatus, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "users AS u,
                        " . DB_PREFIX . "projects_escrow AS e,
                        " . DB_PREFIX . "project_bids AS b,
                        " . DB_PREFIX . "invoices AS i
                        WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                $periodsql
                                AND u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND e.project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND e.status != 'cancelled'
                                AND e.bid_id = b.bid_id
                                AND e.user_id = b.user_id
                                AND e.project_id = p.project_id
                                AND e.invoiceid = i.invoiceid
                                AND i.invoicetype = 'escrow'
                                AND p.project_state = 'service'
                                AND i.projectid = e.project_id
                ", 0, null, __FILE__, __LINE__);
		$number = $ilance->db->num_rows($numberrows);

		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
                
		$result = $ilance->db->query("
                        SELECT p.project_id, p.project_state, p.user_id AS owner_id, p.project_title, p.description, p.currencyid, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.fee, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.isfeepaid, e.feeinvoiceid, b.bid_id, b.user_id AS bidder_id, b.bidstatus, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "users AS u,
                        " . DB_PREFIX . "projects_escrow AS e,
                        " . DB_PREFIX . "project_bids AS b,
                        " . DB_PREFIX . "invoices AS i
                        WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                $periodsql
                                AND u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND e.project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND e.status != 'cancelled'
                                AND e.bid_id = b.bid_id
                                AND e.user_id = b.user_id
                                AND e.project_id = p.project_id
                                AND e.invoiceid = i.invoiceid
                                AND i.invoicetype = 'escrow'
                                AND p.project_state = 'service'
                                AND i.projectid = e.project_id
                        $limit
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
                {
                        $row_count = 0;
			while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                        {
                                $row['taxinfo'] = fetch_escrow_taxinfo_bit($_SESSION['ilancedata']['user']['userid'], fetch_service_buyer_escrow_fee($row['bidamount']), $row['project_id'], false);
                                $row['total'] = ($row['bidamount'] + $row['fee']);
                                $row['total'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['total'], $row['currencyid']);
				if ($row['fee'] > 0)
				{
					if ($row['feeinvoiceid'] > 0)
					{
						$row['feepaid'] = (($row['feeinvoiceid'] > 0 AND $row['isfeepaid'])
							? '<span class="smaller gray">[ <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['feeinvoiceid'] . '">' . $phrase['_paid'] . '</a></span> ]</span>'
							: '<span class="smaller gray">[ <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['feeinvoiceid'] . '">' . $phrase['_unpaid'] . '</a></span> ]</span>');
					}
					else
					{
						$row['feepaid'] = '';	
					}
					
					$row['fee'] = ($row['fee'] > 0)
						? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['fee'])
						: $phrase['_none'];
				}
				else
				{
					$row['feepaid'] = '';
					$row['fee'] = $phrase['_none'];
				}
				
				$row['job_title'] = stripslashes($row['project_title']);
				$row['description'] = short_string(stripslashes($row['description']), 100);
				$row['provider'] = fetch_user('username', $row['user_id']);
				$row['vendor_id'] = $row['user_id'];
				$row['awarddate'] = print_date($row['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
				$row['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['bidamount'], $row['currencyid']);
				$row['escrowamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['escrowamount'], $row['currencyid']);
				
				if ($row['status'] == 'pending')
                                {
					$row['status'] = $phrase['_pay_escrow'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'' . $phrase['_you_must_forward_funds_into_this_escrow_account_before_the_service_provider'] . '\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
					$row['actions'] = '<div><input type="button" value="' . $phrase['_pay_now'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?id=' . $row['invoiceid'] . '\'" class="buttons" style="font-size:10px" /></div>';
				}
				else if ($row['status'] == 'started')
                                {
					$row['status'] = $phrase['_funds_secured'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'' . $phrase['_funds_for_this_service_auction_have_been_forwarded_into_this_particular_escrow_account'] . '\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
					$row['actions'] = '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow_funded.gif" border="0" alt="' . $phrase['_funds_secured_in_escrow'] . '" /></div> ';

					// does admin allow buyer to cancel release of his own funds from service provider? (default = no)
					if ($ilconfig['escrowsystem_payercancancelfunds'])
                                        {
						$crypted = array(
                                                        "cmd" => "management",
                                                        "sub" => "rfp-escrow",
                                                        "subcmd" => "_cancel-release",
                                                        "id" => $row['escrow_id']
                                                );
                                                
						$row['actions'] .= '<a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '" target="_self" onclick="return confirm_js(\'' . $phrase['_cancel_release_of_funds_and_forward_entire_amount'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="' . $phrase['_cancel_release_of_funds'] . '" /></a>';
					}
				}
				else if ($row['status'] == 'confirmed')
                                {
					$row['status'] = '<div><span style="float:left; padding-right:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow.gif" border="0" alt="' . $phrase['_funds_secured_in_escrow'] . '" /></span>' . $phrase['_confirm_release'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'' . $phrase['_confirm_release_allows_you_to_forward_funds_within_this_escrow_account_to_your_service_provider'] . '\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></div>';					
					$crypted = array(
                                                'cmd' => 'management',
                                                'sub' => 'rfp-escrow',
                                                'subcmd' => '_confirm-release',
                                                'id' => $row['escrow_id']
                                        );
                                        
					// #### make sure provider accepted buyers award!!!
					if ($ilance->auction_award->has_provider_accepted_award($row['project_id'], $row['user_id']))
					{
						$row['actions'] = '<div><input type="button" value="' . $phrase['_release_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_you_are_about_to_release_funds_within_this_escrow_account_to_your_service_provider_continue'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" /></div>';
					}
					else
					{
						$row['actions'] = '<div><span title="' . $phrase['_awarded_provider_has_not_accepted_their_bid_award'] . '"><input title="' . $phrase['_awarded_provider_has_not_accepted_their_bid_award'] . '" type="button" value="' . $phrase['_release_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_you_are_about_to_release_funds_within_this_escrow_account_to_your_service_provider_continue'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" disabled="disabled" /></span></div>';	
					}

					// does admin allow buyer to cancel release of his own funds from provider? (default = no)
					if ($ilconfig['escrowsystem_payercancancelfunds'])
                                        {
						$crypted = array(
                                                        'cmd' => 'management',
                                                        'sub' => 'rfp-escrow',
                                                        'subcmd' => '_cancel-release',
                                                        'id' => $row['escrow_id']
                                                );
						
						$row['actions'] .= '<div style="padding-top:3px"><input type="button" value="' . $phrase['_return_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_cancel_release_of_funds_and_forward_entire_amount'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" /></div>';
					}
				}
				else if ($row['status'] == 'finished')
                                {
					$row['status'] = $phrase['_funds_released'];
					$row['actions'] = '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow_funded.gif" border="0" alt="'.$phrase['_funds_released_to_vendor'].'" /></div>';
				}
                                
				$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$servicebuyingescrow[] = $row;
				$row_count++;
			}
                        
                        $show['no_servicebuyingescrow'] = false;
		}
		else
                {
			$show['no_servicebuyingescrow'] = true;
		}

		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $counter, HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow');

		$ilance->template->fetch('main', 'buying_rfp_escrow.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'servicebuyingescrow');
                $ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array('servicetabs','rfpvisible','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
		exit();
	}

	// #### PRODUCT BUYER ESCROW MANAGEMENT & HANDLER ######################
	else if (($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'product-escrow' OR isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['bidsub']) AND $uncrypted['bidsub'] == 'product-escrow' AND $ilconfig['escrowsystem_enabled']))
        {
                // #### define top header nav ##################################
                $topnavlink = array(
                        'mycp',
                        'productbuyingescrow'
                );
                
                $show['widescreen'] = true;
                
		// #### does bidder confirm release of funds to merchant? ######
		if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_confirm-release' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_confirm-release' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
                {
			$id = isset($uncrypted['id'])
				? intval($uncrypted['id'])
				: intval($ilance->GPC['id']);

			$ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
			$success = $ilance->escrow_handler->escrow_handler('buyerconfirmrelease', 'product', $uncrypted['id'], false);
			if ($success)
			{
				$area_title = $phrase['_product_escrow_release_of_funds_complete'];
				$page_title = SITE_NAME . ' - ' . $phrase['_product_escrow_release_of_funds_complete'];
				
				print_notice($phrase['_funds_released_escrow_process_complete'], $phrase['_you_have_successfully_released_funds_within_the_escrow'], HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow', $phrase['_product_escrow_payments_out']);
				exit();
			}
		}
                
		// #### PRODUCT ESCROW MANAGEMENT: BUYER CANCELS FUNDS #########
		else if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_cancel-release' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND $ilconfig['escrowsystem_payercancancelfunds'] AND $ilconfig['escrowsystem_enabled'] OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_cancel-release' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0 AND $ilconfig['escrowsystem_payercancancelfunds'] AND $ilconfig['escrowsystem_enabled']))
                {
			$id = isset($uncrypted['id'])
				? intval($uncrypted['id'])
				: intval($ilance->GPC['id']);

			$ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
			$success = $ilance->escrow_handler->escrow_handler('buyercancelescrow', 'product', $id, false);
			if ($success)
			{
				$area_title = $phrase['_bidder_cancelled_release_of_funds'];
				$page_title = SITE_NAME . ' - ' . $phrase['_bidder_cancelled_release_of_funds'];
				
				print_notice($phrase['_release_of_funds_cancelled_funds_returned'], $phrase['_you_have_successfully_cancelled_release_of_funds_to'], $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow', $phrase['_product_escrow_payments_out']);
				exit();
			}
		}

                // #### PRODUCT ESCROW MANAGEMENT ##############################
		$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
		
		$area_title = $phrase['_product_escrow_management'];
		$page_title = SITE_NAME . ' - ' . $phrase['_product_escrow_management'];
		
                $navcrumb = array();
                $navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
                $navcrumb[HTTP_SERVER . "$ilpage[buying]?cmd=management"] = $phrase['_buying_activity'];
                $navcrumb[""] = $phrase['_product_escrow_management'];
                
		$orderby = ' ORDER BY p.date_added DESC';
		$limit = ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];

                require_once(DIR_CORE . 'functions_search.php');
                require_once(DIR_CORE . 'functions_tabs.php');
                
                // #### LISTING PERIOD #########################################
                $extra = '';
                $ilance->GPC['period2'] = (isset($ilance->GPC['period2']) ? intval($ilance->GPC['period2']) : -1);
                $periodsql = fetch_startend_sql($ilance->GPC['period2'], 'DATE_SUB', 'p.date_added', '>=');
                $extra .= '&amp;period2=' . $ilance->GPC['period2'];
                
                $producttabs = print_buying_activity_tabs('product-escrow', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
		$row_count = 0;
                $condition = $condition2 = '';

		$numberrows = $ilance->db->query("
                        SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, p.currencyid, e.escrowamount, e.date_awarded, e.date_paid, e.status, e.escrow_id, e.fee, e.fee2, e.total, e.fee2invoiceid, e.isfee2paid, b.bid_id, b.user_id AS bidder_id, b.bidstatus, b.bidamount, b.buyershipcost, i.invoiceid, i.buynowid, i.paid, i.invoicetype, i.paiddate
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "users AS u,
                        " . DB_PREFIX . "projects_escrow AS e,
                        " . DB_PREFIX . "project_bids AS b,
                        " . DB_PREFIX . "invoices AS i
                        WHERE e.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                $periodsql
                                AND u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND e.status != 'cancelled'
                                AND e.bid_id = b.bid_id
                                AND e.user_id = b.user_id
                                AND e.project_id = p.project_id
                                AND e.invoiceid = i.invoiceid
                                AND i.invoicetype = 'escrow'
                                AND i.projectid = e.project_id
                                AND p.project_state = 'product'
                ", 0, null, __FILE__, __LINE__);
		$number = $ilance->db->num_rows($numberrows);

		$result = $ilance->db->query("
                        SELECT p.project_id, p.project_state, p.user_id AS owner_id, p.project_title, p.description, p.currencyid, e.escrowamount, e.date_awarded, e.date_paid, e.status, e.escrow_id, e.fee, e.fee2, e.total, e.fee2invoiceid, e.isfee2paid, b.bid_id, b.user_id AS bidder_id, b.bidstatus, b.bidamount, b.buyershipcost, i.invoiceid, i.buynowid, i.paid, i.invoicetype, i.paiddate, i.status AS invoicestatus
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "users AS u,
                        " . DB_PREFIX . "projects_escrow AS e,
                        " . DB_PREFIX . "project_bids AS b,
                        " . DB_PREFIX . "invoices AS i
                        WHERE e.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                $periodsql
                                AND u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND e.status != 'cancelled'
                                AND e.bid_id = b.bid_id
                                AND e.user_id = b.user_id
                                AND e.project_id = p.project_id
                                AND e.invoiceid = i.invoiceid
                                AND i.invoicetype = 'escrow'
                                AND i.projectid = e.project_id
                                AND p.project_state = 'product'
                        $orderby
                        $limit
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
                {
			while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                        {
				$row['job_title'] = stripslashes($row['project_title']);
				$row['merchant'] = fetch_user('username', $row['owner_id']);
				$row['merchant_id'] = $row['owner_id'];
				$row['awarddate'] = print_date($row['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
				$row['orderlocation'] = print_shipping_address_text($row['bidder_id']);
				
				$escrowamount = $row['escrowamount'];
				$row['escrowamount'] = '<span style="float:left; padding-right:3px" title="' . SITE_NAME . ' ' . $phrase['_secure_escrow_payments_deposit_account'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bank.gif" border="0" alt="' . SITE_NAME . ' ' . $phrase['_secure_escrow_payments_deposit_account'] . '" /></span>';
				$row['escrowamount'] .= '<span style="float:right">' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $escrowamount, $row['currencyid']) . '</span>';
				unset($escrowamount);
                                
                                $noshippingfees = 1;
				if ($row['buyershipcost'] > 0)
				{
					$noshippingfees = 0;
					$row['shipfees'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['buyershipcost'], $row['currencyid']);
				}
				else
				{
					$row['shipfees'] = $phrase['_none'];
				}
				
				// is this escrow account pending payment?
				if ($row['status'] == 'pending')
                                {
					// advise to forward funds into escrow account
					$crypted = array(
                                                'id' => $row['invoiceid']
                                        );
					
					$row['status'] = $phrase['_forward_funds'];
					$row['actions'] = '<input type="button" value="' . $phrase['_pay_now'] . '" onclick="location.href=\'' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
				}
				else if ($row['status'] == 'started')
                                {
					$row['status'] = $phrase['_funds_secured'];
					$row['actions'] = ($ilconfig['escrowsystem_payercancancelfunds'])
						? '<input type="button" value="' . $phrase['_return_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_cancel_release_of_funds_and_forward_entire_amount'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow&amp;subcmd=_cancel-release&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" />'
						: '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow_funded.gif" border="0" alt="' . $phrase['_funds_secured_in_escrow'] . '" />';
				}
				else if ($row['status'] == 'confirmed')
                                {
					$crypted = array(
                                                'cmd' => 'management',
                                                'bidsub' => 'product-escrow',
                                                'subcmd' => '_confirm-release',
                                                'id' => $row['escrow_id']
                                        );
					
					$row['status'] = $phrase['_confirm_release'];
					$row['actions'] = '<input type="button" value="' . $phrase['_release_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_confirm_you_are_about_to_release_funds_within_this_escrow_account_to'] . ' ' . $row['merchant'] . '. ' . $phrase['_continue_questionmark'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" />';

                                        // does admin allow bidder to cancel funds within escrow? (default = no)
					if ($ilconfig['escrowsystem_payercancancelfunds'])
                                        {
						$crypted = array(
                                                        'cmd' => 'management',
                                                        'bidsub' => 'product-escrow',
                                                        'subcmd' => '_cancel-release',
                                                        'id' => $row['escrow_id']
                                                );
                                                
						$row['actions'] .= '<div style="padding-top:3px"><input type="button" value="' . $phrase['_return_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_cancel_release_of_funds_and_forward_entire_amount'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" /></div>';
					}
				}
				else if ($row['status'] == 'finished')
                                {
					$row['status'] = $row['actions'] = '-';
					$row['escrowamount'] = '<span style="float:right" class="black">' . $phrase['_funds_released'] . '</span><span style="float:left" title="' . $phrase['_funds_released'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bank_gray.gif" border="0" alt="' . $phrase['_funds_released'] . '" /></span>';
				}
				
                                $row['ispaid'] = '';
                                $row['taxinfo'] = fetch_escrow_taxinfo_bit($_SESSION['ilancedata']['user']['userid'], fetch_product_bidder_escrow_fee($_SESSION['ilancedata']['user']['userid'], $row['bidamount']), $row['project_id']);
                                $row['total'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['total'], $row['currencyid']);
                                
				if ($row['fee2invoiceid'] > 0)
				{
					$row['fee'] = ($row['isfee2paid'])
						? '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['fee2invoiceid'] . '">(' . $ilance->currency->format($row['fee2']) . ')</a></span>'
						: '<span class="red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['fee2invoiceid'] . '">(' . $ilance->currency->format($row['fee2']) . ')</a></span>';
				}
				else
				{
					$row['fee'] = ($row['isfee2paid'])
						? '<span class="blue">(' . $ilance->currency->format($row['fee2']) . ')</span>'
						: '<span class="red">(' . $ilance->currency->format($row['fee2']) . ')</span>';
				}
				
                                $row['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['bidamount'], $row['currencyid']);
                                $row['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumb', $row['project_id']);
				$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$project_results_rows[] = $row;
				$row_count++;
			}
		}
		else
                {
			$show['no_project_rows_returned'] = true;
		}
                
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow');
		
		$pprint_array = array('producttabs','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                
		$ilance->template->fetch('main', 'buying_product_escrow.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('project_results_rows','purchase_now_activity'));
                $ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
        
	// #### PRODUCT BUYER BUY NOW ESCROW MANAGEMENT & HANDLER ##############
        else if (($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'buynow-escrow' OR isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['bidsub']) AND $uncrypted['bidsub'] == 'buynow-escrow'))
        {
                // #### define top header nav ##################################
                $topnavlink = array(
                        'buynowbuyingescrow'
                );
                
                $show['widescreen'] = true;

                // #### BUYER RELEASE FUNDS FROM ESCROW TO SELLER ##############
		if (isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_release-buynow-funds' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0)
                {
			$id = intval($uncrypted['id']);
                        
                        $ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
			$success = $ilance->escrow_handler->escrow_handler('buyerconfirmrelease', 'buynow', $id, false);
			
                        if ($success)
                        {
                                $area_title = $phrase['_product_escrow_release_of_funds_complete'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_product_escrow_release_of_funds_complete'];
                                
                                print_notice($phrase['_funds_released_escrow_process_complete'], $phrase['_you_have_successfully_released_funds_within_the_escrow'], HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow', $phrase['_purchase_now_escrow_buying_activity']);
                                exit();
                        }
		}
                
                // #### BUYER WANTS BUY NOW ESCROW REFUND (MAYBE WAITED TOO LONG)
                // this returns all fees as well (buynow escrow fee, fvf fee and buyer buynow escrow fee)
                // fees unpaid will be cancelled
                else if (isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_cancel-buynow-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0)
                {
                        $id = intval($uncrypted['id']);
                        
                        $ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
			$success = $ilance->escrow_handler->escrow_handler('reversal', 'buynow', $id, false);
			
                        if ($success)
                        {
                                $area_title = $phrase['_buy_now_order_cancellation'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_buy_now_order_cancellation'];
                                
                                print_notice($phrase['_escrow_account_cancelled_funds_returned'], $phrase['_you_have_successfully_cancelled_delivery_for_this_particular_auctions_escrow_account'], HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow', $phrase['_purchase_now_escrow_buying_activity']);
                                exit();
                        }
                }
                
                $headinclude .= '
<script language="Javascript">
function show_prompt_payment(urlbit)
{
	var prompttext = ilance_prompt(\'<div style="padding-bottom:3px"><strong>' . $phrase['_how_exactly_did_you_pay_the_seller_for_this_item'] . '</strong></div><div style="padding-bottom:4px">' . $phrase['_be_specific_example_paypal_visa_wire_etc'] . '</div>\');
	var newurl = \'\';
	if (prompttext != null && prompttext != false && prompttext != \'\')
	{
		newurl = urlbit + "&winnermarkedaspaidmethod=" + prompttext;
		var xyz = \'\';
		xyz = confirm_js(\'' . $phrase['_you_are_about_to_inform_the_seller_that_payment_for_this_item_has_been_paid_in_full'] . '\');
		if (xyz)
		{
			document.location = newurl;
		}
		else
		{		
			return false;
		}
	}
	else
	{
		if (prompttext == null || prompttext == false)
		{
			alert(\'' . $phrase['_please_describe_how_you_paid_the_seller_for_this_item'] . '\');
		}
	}
}
</script>';
		
		$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
                $ilance->GPC['sub'] = isset($ilance->GPC['sub']) ? $ilance->GPC['sub'] : '';
		
		$limit = ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];

                require_once(DIR_CORE . 'functions_search.php');
                require_once(DIR_CORE . 'functions_tabs.php');
                
                $area_title = $phrase['_purchase_now_escrow_buying_activity'];
		$page_title = SITE_NAME . ' - ' . $phrase['_purchase_now_escrow_buying_activity'];
		
                $navcrumb = array();
                $navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
                $navcrumb[HTTP_SERVER . "$ilpage[buying]?cmd=management"] = $phrase['_buying_activity'];
                $navcrumb[""] = $phrase['_purchase_now_escrow_buying_activity'];
                
                // #### does buyer want to see their cancelled orders? #########
                $extrasql = (isset($ilance->GPC['cancelled']) AND $ilance->GPC['cancelled']) ? "" : "AND status != 'cancelled'";
                $extra = '';
		
                // #### ordering by fields defaults ############################
                $orderbyfields = array('project_id', 'amount', 'orderdate', 'paiddate', 'qty', 'escrowfeebuyer');
                $orderby = '&amp;orderby2=amount';
                $orderbysql = 'amount';
                if (isset($ilance->GPC['orderby2']) AND in_array($ilance->GPC['orderby2'], $orderbyfields))
                {
                        $orderby = '&amp;orderby2=' . $ilance->GPC['orderby2'];
                        $orderbysql = $ilance->GPC['orderby2'];
                }
		
		$ilance->GPC['orderby2'] = $orderbysql;
                
                // #### display order defaults #################################
                $displayorderfields = array('asc', 'desc');
                $displayorder = '&amp;displayorder2=asc';
                $currentdisplayorder = $displayorder;
                $displayordersql = 'DESC';
                if (isset($ilance->GPC['displayorder2']) AND $ilance->GPC['displayorder2'] == 'asc')
                {
                        $displayorder = '&amp;displayorder2=desc';
                        $currentdisplayorder = '&amp;displayorder2=asc';
                }
                else if (isset($ilance->GPC['displayorder2']) AND $ilance->GPC['displayorder2'] == 'desc')
                {
                        $displayorder = '&amp;displayorder2=asc';
                        $currentdisplayorder = '&amp;displayorder2=desc';
                }
		
                if (isset($ilance->GPC['displayorder2']) AND in_array($ilance->GPC['displayorder2'], $displayorderfields))
                {
                        $displayordersql = mb_strtoupper($ilance->GPC['displayorder2']);
                }
		
		$extra .= (!empty($ilance->GPC['sub'])) ? '&amp;sub=' . $ilance->GPC['sub'] : '';
		
		// #### used within templates for sorting ######################
                $php_self = HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management' . $displayorder . $extra;
                
		// #### default listing period #################################
                $ilance->GPC['period2'] = (isset($ilance->GPC['period2']) ? intval($ilance->GPC['period2']) : -1);
                $period = '&amp;period2=' . $ilance->GPC['period2'];
                $periodsql = fetch_startend_sql($ilance->GPC['period2'], 'DATE_SUB', 'p.date_added', '>=');
                $producttabs = print_buying_activity_tabs('buynow-escrow', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
		$periodsql = fetch_startend_sql($ilance->GPC['period2'], 'DATE_SUB', 'orderdate', '>=');

		$numberrows = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "buynow_orders
                        WHERE buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
                        $extrasql
                        ORDER BY $orderbysql $displayordersql
                ", 0, null, __FILE__, __LINE__);
		$number = $ilance->db->num_rows($numberrows);                
		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
                
		$result_orders = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "buynow_orders
                        WHERE buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
                        $extrasql
                        ORDER BY $orderbysql $displayordersql
                        $limit
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result_orders) > 0)
                {
			$order_count = 0;
                        $ilance->auction = construct_object('api.auction');
                        $ilance->feedback = construct_object('api.feedback');
                        
			while ($orderrows = $ilance->db->fetch_array($result_orders, DB_ASSOC))
                        {
				$orderrows['currencyid'] = fetch_auction('currencyid', $orderrows['project_id']);
                                $orderrows['taxinfo'] = fetch_escrow_taxinfo_bit($_SESSION['ilancedata']['user']['userid'], fetch_product_bidder_escrow_fee($_SESSION['ilancedata']['user']['userid'], $orderrows['amount']), $orderrows['project_id']);
                                $orderrows['itemid'] = '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $orderrows['project_id'] . '">' . $orderrows['project_id'] . '</a>';
				$orderrows['ordermerchant'] = print_username($orderrows['owner_id'], 'href');
				$orderrows['ordermerchant_id'] = $orderrows['owner_id'];
				$orderrows['orderphone'] = fetch_user('phone', $orderrows['owner_id']);
				$orderrows['orderemail'] = fetch_user('email', $orderrows['owner_id']);

                                $title = fetch_auction('project_title', $orderrows['project_id']);
                                $orderrows['item_name'] = '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $orderrows['project_id'] . '">' . handle_input_keywords($title) . '</a>';
                                $orderamount = fetch_auction('buynow_price', $orderrows['project_id']);
				$orderrows['orderamount'] = $ilance->currency->format($orderamount, $orderrows['currencyid']);
                                $orderrows['orderqty'] = $orderrows['qty'];
				$orderrows['orderdate'] = print_date($orderrows['orderdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
				$orderrows['orderinvoiceid'] = $orderrows['invoiceid'];
				$orderrows['orderid'] = $orderrows['orderid'];
                                
                                if ($orderrows['escrowfeebuyer'] > 0)
                                {
                                        $orderrows['escrowfeebuyer'] = ($orderrows['isescrowfeebuyerpaid'])
						? '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $orderrows['escrowfeebuyerinvoiceid'] . '">(' . $ilance->currency->format($orderrows['escrowfeebuyer']) . ')</a></span>'
						: '<span class="red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $orderrows['escrowfeebuyerinvoiceid'] . '">(' . $ilance->currency->format($orderrows['escrowfeebuyer']) . ')</a></span>';
                                }
                                else
                                {
                                        $orderrows['escrowfeebuyer'] = '-';
                                }
                                
                                // shipping or digital download?
                                if ($orderrows['ship_required'])
                                {
                                        $orderrows['shipping'] = $ilance->currency->format($orderrows['buyershipcost'], $orderrows['currencyid']);
                                        $orderrows['orderlocation'] = stripslashes($orderrows['ship_location']);
					$orderrows['shippingpartner'] = print_shipping_partner($orderrows['buyershipperid']);
					if ($orderrows['sellermarkedasshipped'] AND $orderrows['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
					{
						$orderrows['delivery'] = '<span class="black">' . $phrase['_marked_as_shipped_on'] . ' <span class="blue">' . print_date($orderrows['sellermarkedasshippeddate']) . '</span></span>';        
					}
					else
					{
						$orderrows['delivery'] = $phrase['_the_seller_has_not_yet_marked_your_shipment_as_delivered'];
					}
                                }
                                else
                                {
                                        $orderrows['buyershipcost'] = 0;
                                        $orderrows['shipping'] = $ilance->currency->format($orderrows['buyershipcost'], $orderrows['currencyid']);
					$orderrows['shippingpartner'] = $phrase['_none'];
					$orderrows['delivery'] = '';
                                        
                                        // digital download
                                        $digitalfile = $phrase['_contact_seller'];
                                        $dquery = $ilance->db->query("
                                                SELECT filename, counter, filesize, attachid
                                                FROM " . DB_PREFIX . "attachment
                                                WHERE project_id = '" . intval($orderrows['project_id']) . "'
                                                        AND attachtype = 'digital'
                                                        AND user_id = '" . $orderrows['ordermerchant_id'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($dquery) > 0)
                                        {
                                                $dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
						
                                                if ($orderrows['status'] == 'pending_delivery' OR $orderrows['status'] == 'delivered' OR $orderrows['status'] == 'offline_delivered')
                                                {
                                                        $crypted = array('id' => $dfile['attachid']);
                                                        $digitalfile = '<strong><a href="' . $ilpage['attachment'] . '?crypted=' . encrypt_url($crypted) . '">' . stripslashes($dfile['filename']) . '</a></strong> (' . print_filesize($dfile['filesize']) . ')';
                                                }
                                                else
                                                {
                                                        $digitalfile = '<strong>' . stripslashes($dfile['filename']) . '</strong> (' . print_filesize($dfile['filesize']) . ')<div class="smaller gray">' . $phrase['_waiting_for_seller_to_confirm_delivery'] . '</div>';
                                                }
						
						$orderrows['orderlocation'] = $phrase['_digital_delivery'] . ': ' . $digitalfile;
                                        }
					
					// no shipping local pickup only
					else
					{
						$orderrows['orderlocation'] = $phrase['_local_pickup_only'];
						$orderrows['shipping'] = '-';
					}
                                }
                                
                                ($apihook = $ilance->api('buying_management_buynow_escrow_end')) ? eval($apihook) : false;
                                
				$escrowamount = $orderrows['amount'];
				$orderrows['escrowtotal'] = '<span style="float:left; padding-right:3px" title="' . SITE_NAME . ' ' . $phrase['_secure_escrow_payments_deposit_account'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bank.gif" border="0" alt="' . SITE_NAME . ' ' . $phrase['_secure_escrow_payments_deposit_account'] . '" /></span>';
				$orderrows['escrowtotal'] .= '<span style="float:right" class="blue">' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $escrowamount, $orderrows['currencyid']) . '</span>';
				unset($escrowamount);
				
                                $orderrows['total'] = $ilance->currency->format(($orderamount * $orderrows['orderqty']) + $orderrows['buyershipcost'] + $orderrows['escrowfeebuyer'], $orderrows['currencyid']);
                                
                                // does buyer need to give feedback to seller for this order?
                                $leftfeedback = 0;
                                if ($ilance->feedback->has_left_feedback($orderrows['owner_id'], $_SESSION['ilancedata']['user']['userid'], $orderrows['project_id'], 'seller'))
                                {
                                        // buyer already rated seller
                                        $leftfeedback = 1;
                                        $orderrows['feedback'] = '<div align="center"><span title="' . $phrase['_feedback_submitted__thank_you'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="' . $phrase['_feedback_submitted__thank_you'] . '" /></span></div>';        
                                }
                                else
                                {
                                        // buyer rating seller logic
					$orderrows['feedback'] = '<div align="center"><span title="' . $phrase['_submit_feedback_for'] . ' ' . fetch_user('username', $orderrows['ordermerchant_id']) . '"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}" onmouseover="rollovericon(\'' . md5($orderrows['owner_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $orderrows['project_id'] . ':seller:' . $orderrows['orderid']) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_rate.gif\')" onmouseout="rollovericon(\'' . md5($orderrows['owner_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $orderrows['project_id'] . ':seller:' . $orderrows['orderid']) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="' . $phrase['_submit_feedback_for'] . ' ' . fetch_user('username', $orderrows['ordermerchant_id']) . '" name="' . md5($orderrows['owner_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $orderrows['project_id'] . ':seller:' . $orderrows['orderid']) . '" /></a></span></div>';
                                }
                                
                                // #### BUY NOW ESCROW ACCOUNT FUNDED AND PAID FOR ###########################
				if ($orderrows['status'] == 'paid')
                                {
					$orderrows['orderstatus'] = $phrase['_funds_secured'];
					$orderrows['orderactions'] = '<div class="smaller">' . $phrase['_waiting_for_seller_to_confirm_delivery'] . '</div>';
                                        
					// does admin allow bidder to cancel funds? (default = no)
					if ($ilconfig['escrowsystem_payercancancelfunds'])
                                        {
						$crypted = array(
                                                        'cmd' => 'management',
                                                        'bidsub' => 'buynow-escrow',
                                                        'subcmd' => '_cancel-buynow-delivery',
                                                        'id' => $orderrows['orderid']
                                                );
                                                
						$orderrows['orderactions'] .= '<div style="padding-top:6px"><input type="button" value="' . $phrase['_return_my_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_cancel_payment_return_funds_in_escrow_back_to_my_online_account'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" /><div class="smaller gray">' . $phrase['_this_will_cancel_the_order'] . '</div></div>';
					}
					
					$orderrows['buyerpaymethod'] = print_fixed_payment_method($orderrows['buyerpaymethod'], false);
					$orderrows['payment'] = ($orderrows['paiddate'] == '0000-00-00 00:00:00')
						? '-'
						: '<span class="black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($orderrows['paiddate']) . '</span></span>';
						
                                        $orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
                                        $orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 1);
				}
                                
                                // #### BUY NOW ESCROW ACCOUNT FUNDED; BUYER WAITING FOR ITEM (SELLER MARKED ITEM AS DELIVERED)
                                // in this scenerio - it would be too easy for the buyer to "return my funds" after the seller shipped the item
                                // so we'll not let the buyer "cancel" funds.  If he has problem, he can contact admin to "return funds".
				else if ($orderrows['status'] == 'pending_delivery')
                                {
					$crypted = array(
                                                'cmd' => 'management',
                                                'bidsub' => 'buynow-escrow',
                                                'subcmd' => '_release-buynow-funds',
                                                'id' => $orderrows['orderid']
                                        );
					
					$orderrows['orderactions'] = '<div><span title="' . $phrase['_move_funds_from_escrow_to_the_seller'] . '"><input type="button" value="' . $phrase['_release_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_confirm_you_are_about_to_release_funds_within_this_escrow_account_to_the_merchant_continue'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" /></span></div>';
                                        $orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
                                        $orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 1);
					$orderrows['buyerpaymethod'] = print_fixed_payment_method($orderrows['buyerpaymethod'], false);
                                        $orderrows['payment'] = ($orderrows['paiddate'] == '0000-00-00 00:00:00')
						? '-'
						: '<span class="black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($orderrows['paiddate']) . '</span></span>';
				}
                                
                                // #### BUYER MARKED ORDER AS DELIVERED; RELEASED FUNDS FROM ESCROW TO SELLER
				else if ($orderrows['status'] == 'delivered')
                                {
					$orderrows['escrowtotal'] = '<span style="float:right" class="black">' . $phrase['_funds_released'] . '</span><span style="float:left" title="' . $phrase['_funds_released'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bank_gray.gif" border="0" alt="' . $phrase['_funds_released'] . '" /></span>';
                                        $orderrows['orderactions'] = ($leftfeedback)
						? '<strong>' . $phrase['_finished'] . '</strong> <a href="javascript:void(0)" onmouseover="Tip(\'' . $phrase['_funds_within_escrow_have_been_released_to_your_merchant'] . '\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>'
						: '<span class="black">' . $phrase['_leave_feedback'] . '</span>';
						
                                        $orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
                                        $orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 1);
                                        $orderrows['buyerpaymethod'] = print_fixed_payment_method($orderrows['buyerpaymethod'], false);
					$orderrows['payment'] = ($orderrows['paiddate'] == '0000-00-00 00:00:00')
						? '-'
						: '<span class="black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($orderrows['paiddate']) . '</span></span>';
				}
                                
                                // #### SOMEONE CANCELLED THE BUY NOW ORDER ####
				else if ($orderrows['status'] == 'cancelled')
                                {
					$orderrows['escrowtotal'] = '<span title="' . $phrase['_funds_returned'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bank_gray.gif" border="0" alt="' . $phrase['_funds_returned'] . '" /></span>';
					$orderrows['orderactions'] = '<strong>' . $phrase['_funds_returned'] . '</strong>';
                                        $orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
                                        $orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 0);
                                        $orderrows['payment'] = '';
				}
                                
                                // #### THIS ORDER IS AN OFFLINE ORDER (OUTSIDE PAYMENT)
                                else if ($orderrows['status'] == 'offline')
                                {
                                        //$orderrows['escrowtotal'] = '<span style="float:right" class="smaller litegray">' . $phrase['_not_in_use'] . '</span><span title="' . $phrase['_escrow_not_in_use_offline_payment'] . '" style="float:left"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bank_gray.gif" border="0" alt="' . $phrase['_escrow_not_in_use_offline_payment'] . '" /></span>';
					$orderrows['escrowtotal'] = '-';
                                        
                                        if (strchr($orderrows['buyerpaymethod'], 'gateway'))
                                        {
						$orderrows['buyerpaymethod'] = print_fixed_payment_method($orderrows['buyerpaymethod'], false);
                                                $orderrows['orderactions'] = '<div><span class="gray">' . $phrase['_next'] . ':</span> <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . $orderrows['project_id'] . '&amp;orderid=' . $orderrows['orderid'] . '" style="text-decoration:underline"><strong>' . $phrase['_pay_now'] . '</strong></a></span></div>';
                                                $orderrows['payment'] = '<span class="gray">' . $phrase['_next'] . ':</span> <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . $orderrows['project_id'] . '&amp;orderid=' . $orderrows['orderid'] . '" style="text-decoration:underline"><strong>' . $phrase['_pay_now'] . '</strong></a></span>';
                                        }
                                        else
                                        {
						$orderrows['buyerpaymethod'] = print_fixed_payment_method($orderrows['buyerpaymethod'], false);
                                                $orderrows['orderactions'] = '<div class="smaller black">' . $phrase['_waiting_for_seller_to_confirm_payment'] . '</div>';
                                                if ($orderrows['paiddate'] == '0000-00-00 00:00:00')
                                                {
                                                        $crypted = array(
                                                                'cmd' => 'management',
                                                                'subcmd' => 'markorderaspaid',
                                                                'id' => $orderrows['project_id'],
                                                                'orderid' => $orderrows['orderid']
                                                        );
                                                        
                                                        $orderrows['payment'] = '<span class="gray">' . $phrase['_next'] . ':</span> <span class="blue"><a href="javascript:void(0)" onclick="return show_prompt_payment(\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;bidsub=' . $ilance->GPC['bidsub'] . '&amp;page=' . intval($ilance->GPC['page']) . '\')" style="text-decoration:underline"><strong>' . $phrase['_mark_as_paid'] . '</strong></a></span>';
                                                        unset($crypted);
                                                }
                                                else
                                                {
                                                        $orderrows['payment'] = '<span class="black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($orderrows['paiddate']) . '</span></span>';
                                                }
                                        }
                                        
                                        $orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
                                        $orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 1);
				}
                                
                                // #### THIS OUTSIDE ORDER PAYMENT IS COMPLETED
                                else if ($orderrows['status'] == 'offline_delivered')
                                {
					$orderrows['buyerpaymethod'] = print_fixed_payment_method($orderrows['buyerpaymethod'], false);
                                        $orderrows['bgcolcolor'] = '#FFFFED';
                                        $orderrows['escrowtotal'] = '<span style="float:right" class="smaller litegray">' . $phrase['_not_in_use'] . '</span><span title="' . $phrase['_escrow_not_in_use_offline_payment'] . '" style="float:left"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bank_gray.gif" border="0" alt="' . $phrase['_escrow_not_in_use_offline_payment'] . '" /></span>';
                                        if ($leftfeedback)
                                        {
                                                $orderrows['bgcolcolor'] = '#EAFFE5';
                                                $orderrows['orderactions'] = '<strong>' . $phrase['_finished'] . '</strong>';
                                        }
                                        else
                                        {
                                                $orderrows['orderactions'] = '<span class="black">' . $phrase['_leave_feedback'] . '</span>';
                                        }
                                        $orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
                                        $orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 1);
                                        
                                        if ($orderrows['paiddate'] == '0000-00-00 00:00:00')
                                        {
                                                $crypted = array(
                                                        'cmd' => 'management',
                                                        'subcmd' => 'markorderaspaid',
                                                        'id' => $orderrows['project_id'],
                                                        'orderid' => $orderrows['orderid']
                                                );
                                                
                                                $orderrows['payment'] = '<span class="gray">' . $phrase['_next'] . ':</span> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;sub=' . $ilance->GPC['sub'] . '&amp;page=' . intval($ilance->GPC['page']) . '" onclick="return confirm_js(\'' . $phrase['_you_are_about_to_inform_the_seller_that_payment_for_this_item_has_been_paid_in_full'] . '\');"><strong>' . $phrase['_mark_as_paid'] . '</strong></a></span>';
                                                unset($crypted);
                                        }
                                        else
                                        {
                                                $orderrows['payment'] = '<span class="black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($orderrows['paiddate']) . '</span></span>';
                                        }
				}
                                
                                // display thumbnail
                                if ($ilconfig['globalauctionsettings_seourls'])
                                {
                                        $url = construct_seo_url('productauctionplain', 0, $orderrows['project_id'], $title, '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                        $orderrows['sample'] = print_item_photo($url, 'thumb', $orderrows['project_id']);
                                        unset($url);
                                }
                                else
                                {
                                        $orderrows['sample'] = print_item_photo($ilpage['merch'] . '?id=' . $orderrows['project_id'], 'thumb', $orderrows['project_id']);
                                }
				
                                $orderrows['class'] = ($order_count % 2) ? 'alt2' : 'alt1';
				$purchase_now_activity[] = $orderrows;
				$order_count++;
			}
                        
                        $show['no_purchase_now_activity'] = false;
		}
		else
                {
			$show['no_purchase_now_activity'] = true;
		}
                
		$scriptpage = HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=' . $ilance->GPC['bidsub'] . $orderby . $currentdisplayorder . $period;
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $counter, $scriptpage);
		
		$pprint_array = array('php_self','producttabs','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                
                $ilance->template->fetch('main', 'buying_buynow_escrow.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'purchase_now_activity');
                $ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	// #### SERVICE PROVIDER ESCROW MANAGEMENT #############################
        else if ($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'rfp-escrow' AND $ilconfig['escrowsystem_enabled'])
        {
                // #### define top header nav ##################################
                $topnavlink = array(
                        'mycp',
                        'servicesellingescrow'
                );
                
                $show['widescreen'] = true;
		
		$area_title = $phrase['_rfp_escrow_management'];
                $page_title = SITE_NAME . ' - ' . $phrase['_rfp_escrow_management'];
		
                require_once(DIR_CORE . 'functions_tabs.php');
		$servicetabs = print_selling_activity_tabs('escrow', 'service', $_SESSION['ilancedata']['user']['userid']);
                
                $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
		
                $limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
		$row_count = 0;
		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
                
                $numberrows = $ilance->db->query("
                        SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, p.currencyid, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.fee, e.fee2, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee2invoiceid, e.isfee2paid, b.bid_id, b.user_id as bidder_id, b.bidstatus, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "users AS u,
                        " . DB_PREFIX . "projects_escrow AS e,
                        " . DB_PREFIX . "project_bids AS b,
                        " . DB_PREFIX . "invoices AS i
                        WHERE u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND e.user_id = u.user_id
                                AND e.status != 'cancelled'
                                AND e.bid_id = b.bid_id
                                AND e.user_id = b.user_id
                                AND e.project_id = p.project_id
                                AND e.invoiceid = i.invoiceid
                                AND i.invoicetype = 'escrow'
                                AND p.project_state = 'service'
                                AND i.projectid = e.project_id
                ", 0, null, __FILE__, __LINE__);
                $number = $ilance->db->num_rows($numberrows);
                
                $result = $ilance->db->query("
                        SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, p.currencyid, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.fee, e.fee2, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee2invoiceid, e.isfee2paid, b.bid_id, b.user_id as bidder_id, b.bidstatus, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
                        FROM " . DB_PREFIX . "projects AS p,
                        " . DB_PREFIX . "users AS u,
                        " . DB_PREFIX . "projects_escrow AS e,
                        " . DB_PREFIX . "project_bids AS b,
                        " . DB_PREFIX . "invoices AS i
                        WHERE u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND e.user_id = u.user_id
                                AND e.status != 'cancelled'
                                AND e.bid_id = b.bid_id
                                AND e.user_id = b.user_id
                                AND e.project_id = p.project_id
                                AND e.invoiceid = i.invoiceid
                                AND i.invoicetype = 'escrow'
                                AND p.project_state = 'service'
                                AND i.projectid = e.project_id
                        $limit
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($result) > 0)
                {
                        $altrows = 0;
                        while ($row = $ilance->db->fetch_array($result))
                        {
                                $altrows++;
                                $row['class'] = (floor($altrows/2) == ($altrows/2)) ? 'alt2' : 'alt1';
                                $row['taxinfo'] = fetch_escrow_taxinfo_bit($_SESSION['ilancedata']['user']['userid'], fetch_provider_escrow_fee($row['bidamount']), $row['project_id']);
                                
                                // escrow fee logic
                                // we are a service provider looking at our escrow payments from buyers
                                // let's determine what the admin has arranged for the escrow fees
                                //$row['fee2'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['fee2']);
				if ($row['fee2'] > 0 AND $row['fee2invoiceid'] > 0)
				{
					if ($row['isfee2paid'])
					{
						$row['fee2'] = '<div class="smaller blue"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['fee2invoiceid'] . '">(' . $ilance->currency->format($row['fee2']) . ')</a></span></span>';
					}
					else
					{
						$row['fee2'] = '<div class="smaller red"><span class="red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['fee2invoiceid'] . '">(' . $ilance->currency->format($row['fee2']) . ')</a></span></span>';
					}
				}
				else
				{
					$row['fee2'] = '-';
				}
				
                                $row['job_title'] = stripslashes($row['project_title']);                                        
                                $row['buyer'] = fetch_user('username', $row['project_user_id']);
                                $row['buyer_id'] = $row['project_user_id'];
                                $row['awarddate'] = print_date($row['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                $row['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['bidamount'], $row['currencyid']);
                                $row['escrowamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['escrowamount'], $row['currencyid']);
				
				// #### escrow actions for service providers
                                if ($row['status'] == 'pending')
                                {
                                        $row['status'] = $phrase['_pending_escrow'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'' . $phrase['_pending_means_the_buyer_has_not_forwarded_funds_for_the_awarded_bid_amount'] . '\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
                                        $row['actions'] = '-';
                                }
                                else if ($row['status'] == 'started')
                                {
                                        $row['status'] = $phrase['_funds_secured'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'' . $phrase['_funds_secured_means_that_the_buyer_has_forwarded_funds'] . '\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
                                        $row['actions'] = "<img src='" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "escrow_funded.gif' border='0' alt='" . $phrase['_funds_secured_in_escrow'] . "' />";
                                }
                                else if ($row['status'] == 'confirmed')
                                {
                                        $row['status'] = $phrase['_pending_release'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'' . $phrase['_funds_within_this_escrow_account_are_pending_release_of_funds_from_your_buyer'] . '\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
                                        $row['actions'] = "<img src='" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "escrow.gif' border='0' alt='" . $phrase['_funds_within_this_escrow_account_are_pending_release_of_funds_from_your_buyer'] . "' />";
                                }
                                else if ($row['status'] == 'finished')
                                {
                                        $row['status'] = $phrase['_funds_released'];
                                        $row['actions'] = "<img src='" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "escrow_funded.gif' border='0' alt='" . $phrase['_funds_released_to_vendor'] . "' />";
                                }
				
                                $project_results_rows[] = $row;
                                $row_count++;
                        }
                }
                else
                {
                        $show['no_project_rows_returned'] = true;
                }
                
                $prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow');
                
                $ilance->template->fetch('main', 'selling_rfp_escrow.html');
                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                $ilance->template->parse_loop('main', 'project_results_rows');
                $ilance->template->parse_if_blocks('main');
                $ilance->template->pprint('main', array('servicetabs','serviceescrow','rfpescrow','rfpvisible','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
                exit();
        }
        
        // #### PRODUCT SELLER BUY NOW ESCROW HANDLER ##########################
        else if (($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'buynow-escrow' OR isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['sub']) AND $uncrypted['sub'] == 'buynow-escrow'))
        {
                // #### define top header nav ##################################
                $topnavlink = array(
                        'mycp'
                );
                
                // #### SELLER CONFIRMS OFFLINE DELIVERY FOR BUYER #############
                if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_confirm-offline-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_confirm-offline-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
                {
			$id = ($uncrypted['id'] != '' AND $uncrypted['id'] > 0) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
			
                        $ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
			$success = $ilance->escrow_handler->escrow_handler('sellerconfirmofflinedelivery', 'buynow', $id, false);
			
                        if ($success)
                        {
                                $area_title = $phrase['_merchant_product_delivery_confirmation'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_merchant_product_delivery_confirmation'];
                                
                                print_notice($phrase['_offline_delivery_status_updated'], $phrase['_you_have_successfully_confirmed_delivery_status_for_this_particular_auctions_items_please_communicate'], $ilpage['selling'] . '?cmd=management&amp;sub=sold', $phrase['_items_ive_sold']);
                                exit();
                        }
                }
                
                // #### MERCHANT CONFIRMS DELIVERY #############################
                else if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_confirm-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_confirm-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
                {
			$id = ($uncrypted['id'] > 0) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
			
                        $ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
                        $success = $ilance->escrow_handler->escrow_handler('sellerconfirmdelivery', 'buynow', $id, false);
			
                        if ($success)
                        {
                                $area_title = $phrase['_merchant_product_delivery_confirmation'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_merchant_product_delivery_confirmation'];
                                
                                print_notice($phrase['_escrow_account_confirmed'], $phrase['_you_have_successfully_confirmed_delivery_status_for_this_particular_auctions_items_please_communicate'], HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold', $phrase['_items_ive_sold']);
                                exit();
                        }
                }
                
                // #### MERCHANT CANCELS DELIVERY - FUNDS RETURN TO BUYER ######
                // this returns all fees as well (escrow fee, fvf fee and buyer escrow fee)
                // fees unpaid will be cancelled
                else if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_cancel-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_cancel-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
                {
                        $id = ($uncrypted['id'] > 0) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
                        
			$ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
			$success = $ilance->escrow_handler->escrow_handler('reversal', 'buynow', $id, false);
			
                        if ($success)
                        {
                                $area_title = $phrase['_merchant_product_cancelled_delivery'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_merchant_product_cancelled_delivery'];
                                
                                print_notice($phrase['_escrow_account_cancelled_funds_returned'], $phrase['_you_have_successfully_cancelled_delivery_for_this_particular_auctions_escrow_account'], HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold', $phrase['_items_ive_sold']);
                                exit();
                        }
                }
		
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management&sub=sold');
		exit();
        }
        
        // #### PRODUCT SELLER BUY NOW ESCROW MANAGEMENT & HANDLER #############
        else if (($ilance->GPC['cmd'] == 'management' AND $ilconfig['escrowsystem_enabled'] AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'product-escrow' OR isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['sub']) AND $uncrypted['sub'] == 'product-escrow'))
        {
                // #### define top header nav ##################################
                $topnavlink = array(
                        'mycp',
                        'productsellingescrow'
                );
                
                $show['widescreen'] = true;
		
		$area_title = $phrase['_product_escrow_management'];
                $page_title = SITE_NAME . ' - ' . $phrase['_product_escrow_management'];
                
                $navcrumb = array();
                $navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
                $navcrumb[HTTP_SERVER . "$ilpage[selling]?cmd=management"] = $phrase['_selling_activity'];
                $navcrumb[""] = $phrase['_product_escrow_management'];
                
		// #### SELLER CONFIRMS BUYERS SHIPMENT AND DELIVERY (ESCROW)
                if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_confirm-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_confirm-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
                {
                        $id = ($uncrypted['id'] != '' AND $uncrypted['id'] > 0) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
                        
                        $ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
			$success = $ilance->escrow_handler->escrow_handler('sellerconfirmdelivery', 'product', $id, false);
			
			if ($success)
			{
				$area_title = $phrase['_merchant_product_delivery_confirmation'];
				$page_title = SITE_NAME . ' - ' . $phrase['_merchant_product_delivery_confirmation'];
				
				print_notice($phrase['_escrow_account_confirmed'], $phrase['_you_have_successfully_confirmed_delivery_status_for_this_particular_auctions_items_please_communicate'], HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&sub=product-escrow', $phrase['_escrow_management']);
				exit();
			}
                }
                
		// #### SELLER CANCEL BUYERS SHIPMENT AND DELIVERY (ESCROW)
		else if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_cancel-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_cancel-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
                {
                        $id = isset($uncrypted['id'])
				? intval($uncrypted['id'])
				: intval($ilance->GPC['id']);
                        
                        $ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_handler = construct_object('api.escrow_handler');
			
			$success = $ilance->escrow_handler->escrow_handler('sellercancelescrow', 'product', $id, false);
			if ($success)
			{
				$area_title = $phrase['_merchant_product_cancelled_delivery'];
				$page_title = SITE_NAME . ' - ' . $phrase['_merchant_product_cancelled_delivery'];
				
				print_notice($phrase['_escrow_account_cancelled_funds_returned'], $phrase['_you_have_successfully_cancelled_delivery_for_this_particular_auctions_escrow_account'], HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&sub=product-escrow', $phrase['_escrow_management']);
				exit();
			}
                }
                
                // #### PRODUCT ESCROW SELLER MANAGEMENT #######################
                $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
                
                // #### listing period #########################################
                require_once(DIR_CORE . 'functions_search.php');
                
                // #### listing period #########################################
                $ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
                $extra = '&amp;period=' . $ilance->GPC['period'];
                $periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'e.date_awarded', '>=');
                
                // #### ordering by fields defaults ############################
                $orderbyfields = array('date_awarded');
                $orderby = '&amp;orderby=date_awarded';
                $orderbysql = 'e.date_awarded';
                if (isset($ilance->GPC['orderby']) AND in_array($ilance->GPC['orderby'], $orderbyfields))
                {
                        $orderby = '&amp;orderby=' . $ilance->GPC['orderby'];
                        $orderbysql = 'e.' . $ilance->GPC['orderby'];
                }
                
                // #### display order defaults #################################
                $displayorderfields = array('asc', 'desc');
                $displayorder = '&amp;displayorder=asc';
                $currentdisplayorder = $displayorder;
                $displayordersql = 'DESC';
                if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
                {
                        $displayorder = '&amp;displayorder=desc';
                        $currentdisplayorder = '&amp;displayorder=asc';
                }
                else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
                {
                        $displayorder = '&amp;displayorder=asc';
                        $currentdisplayorder = '&amp;displayorder=desc';
                }
                if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields))
                {
                        $displayordersql = mb_strtoupper($ilance->GPC['displayorder']);
                }
                if (!empty($ilance->GPC['sub']))
                {
                        $extra .= '&amp;sub=' . $ilance->GPC['sub'];
                }
                
		$limit = ' ORDER BY ' . $orderbysql . ' ' . $displayordersql . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
                
                // used within templates
                $php_self = HTTP_SERVER . $ilpage['escrow'] . '?cmd=management' . $displayorder . $extra;
                
                // used within prev / next page nav
                $scriptpage = HTTP_SERVER . $ilpage['escrow'] . '?cmd=management' . $currentdisplayorder . $orderby . $extra;
		
                require_once(DIR_CORE . 'functions_tabs.php');
		$producttabs = print_selling_activity_tabs('escrow', 'product', $_SESSION['ilancedata']['user']['userid']);
		
                $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
                $row_count = 0;
                 
                $SQL = "
			SELECT p.project_id, p.user_id, p.project_title, p.description, p.currencyid, u.username, u.user_id, e.project_user_id, e.user_id, e.escrowamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, e.isfeepaid, e.feeinvoiceid, b.bid_id, b.user_id, b.project_user_id, b.bidstatus, b.bidamount, b.buyershipcost, i.invoiceid, i.projectid, i.buynowid, i.user_id, i.paid, i.invoicetype, i.paiddate
			FROM " . DB_PREFIX . "projects AS p,
			" . DB_PREFIX . "users AS u,
			" . DB_PREFIX . "projects_escrow AS e,
			" . DB_PREFIX . "project_bids AS b,
			" . DB_PREFIX . "invoices AS i
			WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND e.project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                $periodsql
				AND e.status != 'cancelled'
				AND e.bid_id = b.bid_id
				AND e.user_id = b.user_id
				AND e.project_id = p.project_id
				AND e.invoiceid = i.invoiceid
				AND i.invoicetype = 'escrow'
				AND i.projectid = e.project_id 
				AND p.project_state = 'product'
			$limit
		";
                
                $SQL2 = "
			SELECT p.project_id, p.user_id, p.project_title, p.description, p.currencyid, u.username, u.user_id, e.project_user_id, e.user_id, e.escrowamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, e.isfeepaid, e.feeinvoiceid, b.bid_id, b.user_id, b.project_user_id, b.bidstatus, b.bidamount, b.buyershipcost, i.invoiceid, i.projectid, i.buynowid, i.user_id, i.paid, i.invoicetype, i.paiddate
			FROM " . DB_PREFIX . "projects AS p,
			" . DB_PREFIX . "users AS u,
			" . DB_PREFIX . "projects_escrow AS e,
			" . DB_PREFIX . "project_bids AS b,
			" . DB_PREFIX . "invoices AS i
			WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND e.project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                $periodsql
				AND e.status != 'cancelled'
				AND e.bid_id = b.bid_id
				AND e.user_id = b.user_id
				AND e.project_id = p.project_id
				AND e.invoiceid = i.invoiceid
				AND i.invoicetype = 'escrow'
				AND i.projectid = e.project_id
				AND p.project_state = 'product'
		";
		
                $condition = $condition2 = '';
                
                $numberrows = $ilance->db->query($SQL2, 0, null, __FILE__, __LINE__);
                $number = $ilance->db->num_rows($numberrows);
                
                $result = $ilance->db->query($SQL, 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($result) > 0)
                {
                        while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                        {
                                $row['job_title'] = strip_vulgar_words(stripslashes($row['project_title']));
                                $row['buyer'] = fetch_user('username', $row['user_id']);
                                $row['provider'] = stripslashes($row['username']);
                                $row['awarddate'] = print_date($row['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
				
                                $escrowamount = $row['escrowamount'];
				$row['escrowamount'] = '<span style="float:left; padding-right:3px" title="' . SITE_NAME . ' ' . $phrase['_secure_escrow_payments_deposit_account'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bank.gif" border="0" alt="' . SITE_NAME . ' ' . $phrase['_secure_escrow_payments_deposit_account'] . '" /></span>';
				$row['escrowamount'] .= '<span style="float:right" class="blue">' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $escrowamount, $row['currencyid']) . '</span>';
				unset($escrowamount);
                                
                                // does bidder pay shipping?
                                $noshippingfees = 1;
				if ($row['buyershipcost'] > 0)
				{
					$noshippingfees = 0;
					$row['shipfees'] = $ilance->currency->format($row['buyershipcost'], $row['currencyid']);
				}
				else
				{
					$row['shipfees'] = $phrase['_none'];
				}
				
                                // merchant viewing escrow information
                                if ($row['status'] == 'pending')
                                {
                                        // pending - waiting for buyer to forward funds
                                        $row['status'] = '<strong>' . $phrase['_do_not_ship_upper'] . '</strong>: ' . $phrase['_waiting_for_buyer_to_fund_this_escrow_account'];
                                        $row['actions'] = $phrase['_pending'];
                                }
                                else if ($row['status'] == 'started')
                                {
                                        // started - funds forwarded by buyer into escrow
                                        $row['status'] = $phrase['_funds_secured'];
                                        
                                        // funds secured - show release funds back to customer or confirm delivery
                                        $crypted = array(
                                                'cmd' => 'management',
                                                'sub' => 'product-escrow',
                                                'subcmd' => '_confirm-delivery',
                                                'id' => $row['escrow_id']
                                        );
                                        
                                        $row['actions'] = '<div><input type="button" value="' . $phrase['_mark_as_shipped'] . '" onclick="if (confirm_js(\'' . $phrase['_confirm_the_product_has_been_shipped_or_delivered_to_the_highest_bidder'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" /></div>';
                                        
                                        $crypted = array(
                                                'cmd' => 'management',
                                                'sub' => 'product-escrow',
                                                'subcmd' => '_cancel-delivery',
                                                'id' => $row['escrow_id']
                                        );
                                        
                                        $row['actions'] .= '<!--<div style="padding-top:3px; padding-bottom:3px">' . $phrase['_or_upper'] . '</div>--><div style="padding-top:3px"><input type="button" value="' . $phrase['_return_funds'] . '" onclick="if (confirm_js(\'' . $phrase['_return_funds_in_escrow_back_to_highest_bidder'] . '\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" /></div>';
                                }
                                else if ($row['status'] == 'confirmed')
                                {
                                        $row['status'] = $phrase['_pending_release'];
                                        $row['actions'] = '-';
                                }
                                else if ($row['status'] == 'finished')
                                {
                                        $row['status'] = $row['actions'] = '-';
					$row['escrowamount'] = '<span style="float:right" class="black">' . $phrase['_funds_released'] . '</span><span style="float:left" title="' . $phrase['_funds_released'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bank_gray.gif" border="0" alt="' . $phrase['_funds_released'] . '" /></span>';
                                }
                                
                                $row['taxinfo'] = fetch_escrow_taxinfo_bit($_SESSION['ilancedata']['user']['userid'], fetch_merchant_escrow_fee($row['bidamount']), $row['project_id']);
                                
                                // fee to seller
                                $row['total'] = ($noshippingfees == 0 AND $row['buyershipcost'] > 0) ? ($row['bidamount'] + $row['buyershipcost']) : $row['bidamount'];
                                $row['total'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['total'], $row['currencyid']);
				
				if ($row['feeinvoiceid'] > 0)
				{
					$row['fee'] = ($row['isfeepaid'])
						? '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['feeinvoiceid'] . '">(' . $ilance->currency->format($row['fee']) . ')</a></span>'
						: '<span class="red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['feeinvoiceid'] . '">(' . $ilance->currency->format($row['fee']) . ')</a></span>';
				}
				else
				{
					$row['fee'] = ($row['isfeepaid'])
						? '<span class="blue">(' . $ilance->currency->format($row['fee']) . ')</span>'
						: '<span class="red">(' . $ilance->currency->format($row['fee']) . ')</span>';
				}
				
				$row['orderlocation'] = print_shipping_address_text($row['user_id']);				
                                $row['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['bidamount'], $row['currencyid']);
				$row['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumb', $row['project_id']);
                                $row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                $project_results_rows[] = $row;
                                $row_count++;
                        }
                }
                else
                {
                        $show['no_project_rows_returned'] = true;
                }
                
                $prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $counter, HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow');

		$pprint_array = array('php_self','producttabs','rfpvisible','countdelisted','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

                $ilance->template->fetch('main', 'selling_product_escrow.html');
                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                $ilance->template->parse_loop('main', 'project_results_rows');
                $ilance->template->parse_if_blocks('main');
                $ilance->template->pprint('main', $pprint_array);
                exit();
        }
}
else
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(HTTPS_SERVER . $ilpage['escrow'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>