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
        'selling',
        'search'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'countries',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_attachment.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['distribution'] => $ilcrumbs["$ilpage[distribution]"]);

//$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['distribution']);
$navroot = '1';
if (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
        exit();
}

$ilance->auction = construct_object('api.auction');

($apihook = $ilance->api('admincp_distribution_start')) ? eval($apihook) : false;

 
// #### BIDS MANAGER ###################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bids')
{
}
    
// #### AUCTIONS DISTRIBUTION ##########################################
else
{
	$area_title = $phrase['_auctions_distribution'];
	$page_title = SITE_NAME . ' - ' . $phrase['_auctions_distribution'];
	
	($apihook = $ilance->api('admincp_auction_management')) ? eval($apihook) : false;
	// murugan changes on jan 20
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'], $_SESSION['ilancedata']['user']['slng']);
	
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'auctions' OR empty($ilance->GPC['cmd']))
	{
		if (isset($ilance->GPC['pagetype']))
		{
			$pagetype = $ilance->GPC['pagetype'];
			$page = intval($ilance->GPC['page']);
			$viewtype = $ilance->GPC['viewtype'];
		}
		
		// #### UPDATE AUCTION HANDLER #################################
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_update-auction')
		{
			$cid = intval($ilance->GPC['acpcid']);
			$visible = intval($ilance->GPC['visible']);
			
			$query = $ilance->db->query("
				SELECT cid, status
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($ilance->GPC['project_id']) . "'
			");
			if ($ilance->db->num_rows($query) > 0)
			{
				$qres = $ilance->db->fetch_array($query, DB_ASSOC);
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "',
					project_state = '" . $ilance->db->escape_string($ilance->GPC['project_state']) . "',
					project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "',
					cid = '" . intval($cid) . "',
					date_added = '" . $ilance->db->escape_string($ilance->GPC['date_added']) . "',
					date_starts = '" . $ilance->db->escape_string($ilance->GPC['date_starts']) . "',
					date_end = '" . $ilance->db->escape_string($ilance->GPC['date_end']) . "',
					visible = '" . $visible . "'
					WHERE project_id = '" . intval($ilance->GPC['project_id']) . "'
				");
					
				// is the admin changing the category for this listing?
				// if so, we must remove all answers based on this category..
				move_listing_category_from_to($ilance->GPC['project_id'], $qres['cid'], $cid, $ilance->GPC['project_state'], $qres['status'], $ilance->GPC['status']);
			}
			
			print_action_success($phrase['_listing_id_was_updated_no_email_was_dispatched_to_the_member'], $ilance->GPC['return']);
			exit();
		}
		
		// #### AUCTION MODERATION CONTROLS ############################
		
		
		// #### REGULAR AUCTION CONTROLS ###############################
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'auction-action')
		{
			// #### CLOSE MULTIPLE AUCTIONS ################
			if (isset($ilance->GPC['close']))
			{
				$emailtemplate = 'moderate_auction_closed';
				
				$ilance->email = construct_dm_object('email', $ilance);
				
				($apihook = $ilance->api('admincp_action_close_start')) ? eval($apihook) : false;
				
				$notice = '';
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'closed',
							close_date = '" . DATETIME24H . "'
							WHERE project_id = '" . intval($value) . "'
						");
						
						if ($res['status'] == 'open')
						{
							build_category_count($res['cid'], 'subtract', "admin closing multiple listings from admincp: subtracting increment count category id $res[cid]");
						}
						
						if ($res['project_state'] == 'product')
						{
							$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						}
						else if ($res['project_state'] == 'service')
						{
							$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;        
						}
						
						($apihook = $ilance->api('admincp_action_close_foreach')) ? eval($apihook) : false;
						
						$ilance->email->mail = array(fetch_user('email', $res['user_id']), SITE_EMAIL);
						$ilance->email->slng = fetch_user_slng($res['user_id']);
						
						$ilance->email->get($emailtemplate);		
						$ilance->email->set(array(
							'{{project_id}}' => $value,
							'{{url}}' => $url,
						));
						
						$ilance->email->send();
					}
				}
				
				print_action_success($phrase['_the_selected_listings_were_closed_early'], $ilance->GPC['return']);
				exit();
			}
			
			// #### DELIST MULTIPLE AUCTIONS ###############
			else if (isset($ilance->GPC['delist']))
			{
				$emailtemplate = 'moderate_auction_delist';
				
				$ilance->email = construct_dm_object('email', $ilance);
				
				($apihook = $ilance->api('admincp_action_delist_start')) ? eval($apihook) : false;
				
				$notice = '';
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						
						if ($res['status'] == 'open')
						{
							build_category_count($res['cid'], 'subtract', "admin delisting multiple listings from admincp: subtracting increment count category id $res[cid]");
						}
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'delisted',
							close_date = '" . DATETIME24H . "'
							WHERE project_id = '" . intval($value) . "'
						");
						
						if ($res['project_state'] == 'product')
						{
							$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						}
						else if ($res['project_state'] == 'service')
						{
							$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;        
						}
						
						($apihook = $ilance->api('admincp_action_delist_foreach')) ? eval($apihook) : false;
						
						$ilance->email->mail = array(fetch_user('email', $res['user_id']), SITE_EMAIL);
						$ilance->email->slng = fetch_user_slng($res['user_id']);
						
						$ilance->email->get($emailtemplate);		
						$ilance->email->set(array(
							'{{project_id}}' => $value,
							'{{url}}' => $url,
						));
						
						$ilance->email->send();
					}
				}
				
				print_action_success($phrase['_the_selected_listings_were_delisted_closed'], $ilance->GPC['return']);
				exit();
			}
			
			// #### ARCHIVE MULTIPLE AUCTIONS ##############
			else if (isset($ilance->GPC['archive']))
			{
				$emailtemplate = 'moderate_auction_archive';
				
				$ilance->email = construct_dm_object('email', $ilance);
				
				($apihook = $ilance->api('admincp_action_archive_start')) ? eval($apihook) : false;
				
				$notice = '';
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'archived',
							close_date = '" . DATETIME24H . "'
							WHERE project_id = '" . intval($value) . "'
						");
						
						if ($res['status'] == 'open')
						{
							build_category_count($res['cid'], 'subtract', "admin archiving multiple listings from admincp: subtracting increment count category id $res[cid]");
						}
						
						if ($res['project_state'] == 'product')
						{
							$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						}
						else if ($res['project_state'] == 'service')
						{
							$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;        
						}
						
						($apihook = $ilance->api('admincp_action_archive_foreach')) ? eval($apihook) : false;
						
						$ilance->email->mail = SITE_EMAIL;
						$ilance->email->slng = fetch_site_slng();
						
						$ilance->email->get($emailtemplate);		
						$ilance->email->set(array(
							'{{project_id}}' => $value,
							'{{url}}' => $url,
						));
						
						$ilance->email->send();
					}
				}
				
				print_action_success($phrase['_the_selected_listings_were_archived'], $ilance->GPC['return']);
				exit();
			}
			
			// #### REMOVE MULTIPLE AUCTIONS ###############
			else if (isset($ilance->GPC['remove']))
			{
				$ilance->email = construct_dm_object('email', $ilance);
				$notice = $emailnotice = '';
				$count = 1;
				
				($apihook = $ilance->api('admincp_action_remove_start')) ? eval($apihook) : false;
				
				if (isset($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
				{
					foreach ($ilance->GPC['project_id'] AS $value)
					{
						$sql = $ilance->db->query("
							SELECT user_id, cid, status, project_state, project_title
							FROM " . DB_PREFIX . "projects
							WHERE project_id = '" . intval($value) . "'
						");
						if ($ilance->db->num_rows($sql) > 0)
						{
							$res = $ilance->db->fetch_array($sql, DB_ASSOC);
							
							if ($res['status'] == 'open')
							{
								build_category_count($res['cid'], 'subtract', "admin removing multiple listings from admincp: subtracting increment count category id $res[cid]");
							}
							
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_changelog WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "product_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bid_retracts WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bids WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_realtimebids WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_uniquebids WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_invitations WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "proxybid WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment WHERE project_id = '" . intval($value) . "'");                                                                
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment_folder WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "messages WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "pmb WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "pmb_alerts WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist WHERE watching_project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "profile_filter_auction_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "bid_fields_answers WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "buynow_orders WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_escrow WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_trackbacks WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping_destinations WHERE project_id = '" . intval($value) . "'");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping_regions WHERE project_id = '" . intval($value) . "'");
							
							if ($res['project_state'] == 'product')
							{
								$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
							}
							else if ($res['project_state'] == 'service')
							{
								$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;        
							}
							
							$emailnotice .= "$count. #$value - $res[project_title] ($url)";
							
							($apihook = $ilance->api('admincp_action_remove_foreach')) ? eval($apihook) : false;
							
							$count++;
						}
					}
					
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					
					$ilance->email->get('moderate_auction_unverified');		
					$ilance->email->set(array(
						'{{listingsremoved}}' => $emailnotice,
					));
					
					$ilance->email->send();
					
					$notice .= $phrase['_the_selected_listings_were_removed'];
					
					print_action_success($notice, $ilance->GPC['return']);
					exit();	
				}
			}
		}
		else if (!isset($ilance->GPC['subcmd']) OR isset($ilance->GPC['do']) AND $ilance->GPC['do'] != '_update-auction')
		{
			$show['update_auction'] = false;
			$show['no_update_auction'] = true;
			$dosql = $dosql2 = '';
			
			if (isset($ilance->GPC['viewtype']) AND $ilance->GPC['viewtype'] != '')
			{
				$viewtype = $ilance->GPC['viewtype'];
			}
			
			if (isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] > 0)
			{
				$pagetype = 'page3';
				$page = intval($ilance->GPC['page3']);
			}
			else if (isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] > 0)
			{
				$pagetype = 'page2';
				$page = intval($ilance->GPC['page2']);
			}
			else if (isset($ilance->GPC['page']) AND $ilance->GPC['page'] > 0)
			{
				$pagetype = 'page';
				$page = intval($ilance->GPC['page']);
			}
			else
			{
				$pagetype = 'page';
				$page = 1;
			}
	
			if (!isset($ilance->GPC['page3']) OR isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] <= 0)
			{
				$ilance->GPC['page3'] = 1;
			}
			else
			{
				$ilance->GPC['page3'] = intval($ilance->GPC['page3']);
			}
	
			if (isset($ilance->GPC['orderby']) AND $ilance->GPC['orderby'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$ordersort = strip_tags($ilance->GPC['orderby']);
			}
			else
			{
				$ordersort = 'DESC';
			}
			$orderlimit3 = ' ORDER BY id '.$ordersort.' LIMIT '.(($ilance->GPC['page3']-1)*$ilconfig['globalfilters_maxrowsdisplay']).','.$ilconfig['globalfilters_maxrowsdisplay'];
	
			// filtering search via project id
			if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$dosql = " AND project_id = '".intval($ilance->GPC['project_id'])."' ";
			}
			
			// filtering search via user id
			if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0 AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$dosql .= " AND user_id = '".intval($ilance->GPC['user_id'])."' ";
			}
			
			// filtering search via auction type
			if (isset($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$dosql .= " AND project_details = '".$ilance->db->escape_string($ilance->GPC['project_details'])."' ";
			}
			
			// filtering search via auction title
			if (isset($ilance->GPC['project_title']) AND $ilance->GPC['project_title'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$dosql .= " AND project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['project_title']) . "%' ";
			}
			
			// filtering search via auction status
			if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
			{
				$dosql .= " AND status = '".$ilance->db->escape_string($ilance->GPC['status'])."' ";
			}
			
			if (empty($dosql))
			{
				$dosql = '';
			}
			
			if (empty($ilance->GPC['status']))
			{
				$ilance->GPC['status'] = '';
			}
	
			$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '0' ".$dosql." ".$orderlimit3);
			$sql2 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '0' ".$dosql);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$show['no_moderateauctions'] = false;
				
				$ilance->escrow = construct_object('api.escrow');
				
				$row_count = 0;
				while ($res = $ilance->db->fetch_array($sql))
				{
					if (isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] != '')
					{
						$res['pagetype'] = 'page3';
						$res['page'] = intval($ilance->GPC['page3']);
					}
					$res['project_title'] = stripslashes($res['project_title']);
					$res['r3'] = '<input type="checkbox" name="project_id[]" value="' . $res['project_id'] . '" />';
					$res['added'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 1);
					$res['owner'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id='.$res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
					$res['category'] = '<strong>' . $ilance->categories->title(fetch_site_slng(), 'service', $res['cid']) . '</strong>';
					$res['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res['user_id'], $res['project_id'], 1);
					$res['escrow'] = $ilance->escrow->status($res['project_id']);
					$res['auctiontype'] = ucfirst($res['project_details']);
					$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
					$res['type'] = $res['project_state'];
					$res['insertionfee'] = ($res['insertionfee'] > 0)
						? $ilance->currency->format($res['insertionfee'])
						: '-';
						
					$moderateauctions[] = $res;
					$row_count++;
				}
				$numbermoderation = $ilance->db->num_rows($sql2);
			}
			else
			{
				$numbermoderation = 0;
				$show['no_moderateauctions'] = true;
			}
			
			$moderateprevnext = '';
			if ($show['no_moderateauctions'] == false)
			{
				$ilance->GPC['project_id'] = isset($ilance->GPC['project_id']) ? intval($ilance->GPC['project_id']) : 0;
				$ilance->GPC['user_id'] = isset($ilance->GPC['user_id']) ? intval($ilance->GPC['user_id']) : 0;
				$ilance->GPC['project_details'] = isset($ilance->GPC['project_details']) ? $ilance->GPC['project_details'] : '';
				$ilance->GPC['orderby'] = isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : '';
				
				$moderateprevnext = print_pagnation($numbermoderation, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page3'], ($ilance->GPC['page3']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['distribution'] . "?cmd=auctions&amp;viewtype=moderate&amp;project_id=".intval($ilance->GPC['project_id'])."&amp;user_id=".(int)$ilance->GPC['user_id']."&amp;project_details=".$ilance->GPC['project_details']."&amp;orderby=".$ilance->GPC['orderby'], 'page3');
			}
			
			if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
			{
				$ilance->GPC['page'] = 1;
			}
			else
			{
				$ilance->GPC['page'] = intval($ilance->GPC['page']);
			}
	
			// filtering search via ascending / decending
			$ordersort = 'DESC';
			if (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby']) AND $ilance->GPC['viewtype'] == 'service')
			{
				$ordersort = strip_tags($ilance->GPC['orderby']);
			}
			$orderlimit = ' ORDER BY id ' . $ordersort . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	
			// filtering search via project id
			if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND $ilance->GPC['viewtype'] == 'service')
			{
				$dosql2 = " AND project_id = '" . intval($ilance->GPC['project_id']) . "' ";
			}
	
			// filtering search via user id
			if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0 AND $ilance->GPC['viewtype'] == 'service')
			{
				$dosql2 .= " AND user_id = '" . intval($ilance->GPC['user_id']) . "' ";
			}
	
			// filtering search via auction type
			if (isset($ilance->GPC['project_details']) AND !empty($ilance->GPC['project_details']) AND $ilance->GPC['viewtype'] == 'service')
			{
				$dosql2 .= " AND project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "' ";
				$ilance->GPC['auctiontype'] = $ilance->GPC['project_details'];
			}
			
			// filtering search via auction title
			if (isset($ilance->GPC['project_title']) AND $ilance->GPC['project_title'] != "" AND $ilance->GPC['viewtype'] == 'service')
			{
				$dosql2 .= " AND project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['project_title']) . "%' ";
			}
	
			// filtering search via auction status
			if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']) AND $ilance->GPC['viewtype'] == 'service')
			{
				$dosql2 .= " AND status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "' ";
			}
			
			if (!isset($dosql2))
			{
				$dosql2 = '';
			}
			
			if (empty($ilance->GPC['status']))
			{
				$ilance->GPC['status'] = '';
			}
	
			$sqlservice = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "projects
				WHERE visible = '1'
					AND project_state = 'service'
					$dosql2
					$orderlimit
			");
			
			$sqlservice2 = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "projects
				WHERE visible = '1'
					AND project_state = 'service'
					$dosql2
			");
			if ($ilance->db->num_rows($sqlservice) > 0)
			{
				$show['no_serviceauctions'] = false;
				
				$ilance->escrow = construct_object('api.escrow');
				
				$row_count = 0;
				while ($res = $ilance->db->fetch_array($sqlservice, DB_ASSOC))
				{
					$res['r1'] = '<input type="checkbox" name="project_id[]" value="' . $res['project_id'] . '" />';
					$res['added'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 1);
					$res['owner'] = '<a href="'.$ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '"">' . fetch_user('username', $res['user_id']) . '</a>';
					$res['project_title'] = stripslashes($res['project_title']);
					$res['awarded'] = $ilance->auction->fetch_auction_winner($res['project_id']);
					$res['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res['user_id'], $res['project_id'], 1);
					$res['escrow'] = $ilance->escrow->status($res['project_id']);                        
					$res['auctiontype'] = ucfirst($res['project_details']);
					
					if ($res['status'] == 'wait_approval')
					{
						$res['status'] = $phrase['_pending_acceptance'];
					}
					else if ($res['status'] == 'approval_accepted')
					{
						$res['status'] = $phrase['_accepted'];
					}
					else
					{
						$res['status'] = ucwords($res['status']);
					}
					
					$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
					$res['timeleft'] = $ilance->auction->auction_timeleft($res['project_id'], $res['class']);
					
					if ($res['insertionfee'] > 0)
					{
						$res['insertionfee'] = $ilance->currency->format($res['insertionfee']);
					}
					else
					{
						$res['insertionfee'] = '-';
					}
					if ($res['fvf'] > 0)
					{
						$res['fvf'] = $ilance->currency->format($res['fvf']);
					}
					else
					{
						$res['fvf'] = '-';
					}
					
					if ($res['bids'] == 0)
					{
						$res['bids'] = '-';
					}
					
					$serviceauctions[] = $res;
					$row_count++;
				}
				
				$numberservice = $ilance->db->num_rows($sqlservice2);
			}
			else
			{
				$numberservice = '0';
				$show['no_serviceauctions'] = true;
			}
			
			if ($show['no_serviceauctions'] == false)
			{
				if (!isset($ilance->GPC['project_id']))
				{
					$ilance->GPC['project_id'] = 0;
				}
				if (!isset($ilance->GPC['project_details']))
				{
					$ilance->GPC['project_details'] = '';
				}
				if (!isset($ilance->GPC['user_id']))
				{
					$ilance->GPC['user_id'] = 0;
				}
				if (!isset($ilance->GPC['orderby']))
				{
					$ilance->GPC['orderby'] = '';
				}
				$serviceprevnext = print_pagnation($numberservice, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], ($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['distribution'] . "?cmd=auctions&amp;viewtype=service&amp;project_id=".intval($ilance->GPC['project_id'])."&amp;user_id=".intval($ilance->GPC['user_id'])."&amp;project_details=".$ilance->GPC['project_details']."&amp;orderby=".$ilance->GPC['orderby']."&amp;status=" . $ilance->GPC['status'] . "");
			}
			else
			{
				$serviceprevnext = '';
			}
			
			if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
			{
				$ilance->GPC['page2'] = 1;
			}
			else
			{
				$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
			}
	
			// filtering search via ascending / descending
			$ordersort = 'DESC';
			if (isset($ilance->GPC['orderby']) AND $ilance->GPC['orderby'] != "" AND $ilance->GPC['viewtype'] == 'product')
			{
				$ordersort = strip_tags($ilance->GPC['orderby']);
			}
			$orderlimit = ' ORDER BY id ' . $ordersort . ' LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
			$dosql3 = '';
			
			// filtering search via project id
			if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 = " AND project_id = '" . intval($ilance->GPC['project_id']) . "' ";
			}
	
			// filtering search via user id
			if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0 AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 .= " AND user_id = '" . intval($ilance->GPC['user_id']) . "' ";
			}
	
			// filtering search via auction type
			if (isset($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] != "" AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 .= " AND project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "' ";
			}
			
			// filtering by product type regular/fixed
			if (isset($ilance->GPC['project_details2']) AND $ilance->GPC['project_details2'] != "" AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 .= " AND filtered_auctiontype = '" . $ilance->db->escape_string($ilance->GPC['project_details2']) . "' ";
			}
			
			// filtering search via auction title
			if (isset($ilance->GPC['project_title']) AND $ilance->GPC['project_title'] != "" AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 .= " AND project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['project_title']) . "%' ";
			}
	
			// filtering search via auction status
			if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] != "" AND $ilance->GPC['viewtype'] == 'product')
			{
				$dosql3 .= " AND status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "' ";
			}
			
			if (!isset($dosql3))
			{
				$dosql3 = '';
			}
	
			$sqlproduct = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '1' AND project_state = 'product' ".$dosql3." ".$orderlimit);
			$sqlproduct2 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '1' AND project_state = 'product' ".$dosql3);
			if ($ilance->db->num_rows($sqlproduct) > 0)
			{
				$show['no_productauctions'] = false;
				
				$ilance->escrow = construct_object('api.escrow');
				
				$row_count = 0;
				while ($res = $ilance->db->fetch_array($sqlproduct))
				{
					$res['r2'] = '<input type="checkbox" name="project_id[]" value="'.$res['project_id'].'" />';
					$res['merchant'] = '<a href="'.$ilpage['subscribers'].'?subcmd=_update-customer&amp;id='.$res['user_id'].'"">'.fetch_user('username', $res['user_id']).'</a>';
					$res['status'] = ucfirst($res['status']);
					$res['project_title'] = stripslashes($res['project_title']);
					$res['winner'] = $ilance->auction->fetch_auction_winner($res['project_id']);
					//$res['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res['user_id'], $res['project_id'], 1);
					$sold = fetch_buynow_ordercount($res['project_id']);
					if ($sold > 0)
					{
						$res['sales'] = $sold;
					}
					else
					{
						$res['sales'] = '-';
					}
					$res['escrow'] = $ilance->escrow->status($res['project_id']);
					$res['auctiontype'] = ucfirst($res['project_details']);
					$res['auctiontype2'] = ucfirst($res['filtered_auctiontype']);
					$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
					$res['timeleft'] = $ilance->auction->auction_timeleft($res['project_id'], $res['class']);
					if ($res['insertionfee'] > 0)
					{
						$res['insertionfee'] = $ilance->currency->format($res['insertionfee']);
					}
					else
					{
						$res['insertionfee'] = '-';
					}
					if ($res['fvf'] > 0)
					{
						$res['fvf'] = $ilance->currency->format($res['fvf']);
					}
					else
					{
						$res['fvf'] = '-';
					}
					if ($res['bids'] == 0)
					{
						$res['bids'] = '-';
					}
					$productauctions[] = $res;
					$row_count++;
				}
				$numberproduct = $ilance->db->num_rows($sqlproduct2);
			}
			else
			{
				$numberproduct = 0;
				$show['no_productauctions'] = true;
			}
	
			$productprevnext = '';
			if ($show['no_productauctions'] == false)
			{
				$productprevnext = print_pagnation($numberproduct, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], ($ilance->GPC['page2']-1)*$ilconfig['globalfilters_maxrowsdisplay'], $ilpage['distribution'] . "?cmd=auctions&amp;viewtype=product&amp;project_id=" . (isset($ilance->GPC['project_id']) ? intval($ilance->GPC['project_id']) : 0) . "&amp;user_id=" . (isset($ilance->GPC['user_id']) ? intval($ilance->GPC['user_id']) : 0) . "&amp;project_details=".(isset($ilance->GPC['project_details']) ? $ilance->GPC['project_details'] : '') . "&amp;orderby=" . (isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : '') . "&amp;status=" . $ilance->GPC['status'] . "", 'page2');
			}
				
			$ilance->GPC['auctiontype'] = isset($ilance->GPC['auctiontype']) ? $ilance->GPC['auctiontype'] : '';
			$ilance->GPC['project_details2'] = isset($ilance->GPC['project_details2']) ? $ilance->GPC['project_details2'] : 'regular';
			
			$auction_type_pulldown = $ilance->admincp->auction_details_pulldown($ilance->GPC['auctiontype'], 1, 'service');
			$auction_type_pulldown2 = $ilance->admincp->auction_details_pulldown($ilance->GPC['auctiontype'], 1, 'product');
			$auctiontype_pulldown2 = $ilance->admincp->auction_details_pulldown2($ilance->GPC['project_details2'], 1, 'product');
			
			// auction status pulldown
			$ilance->GPC['status'] = isset($ilance->GPC['status']) ? $ilance->GPC['status'] : 0;
			$auction_status_pulldown = $ilance->admincp->auction_status_pulldown($ilance->GPC['status'], 1, 'service');
			$auction_status_pulldown2 = $ilance->admincp->auction_status_pulldown($ilance->GPC['status'], 1, 'product');
		}
		else
		{
			header("Location: " . HTTPS_SERVER . (($ilance->GPC['viewtype'] == 'service') ? $ilpage['buying'] . "?cmd=rfp-management&id=" . intval($ilance->GPC['id']) . "&admincp=1" : $ilpage['selling'] . "?cmd=product-management&id=" . intval($ilance->GPC['id']) . "&admincp=1"));
			exit();
		}
	}
	$id = 0;
	if (isset($ilance->GPC['id']))
	{
		$id = intval($ilance->GPC['id']);
	}
	
	$project_id = '';
	if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
	{
		$project_id = intval($ilance->GPC['project_id']);
	}
	
	$user_id = '';
	if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0)
	{
		$user_id = intval($ilance->GPC['user_id']);
	}
	
	$project_title = '';
	if (isset($ilance->GPC['project_title']) AND !empty($ilance->GPC['project_title']))
	{
		$project_title = $ilance->GPC['project_title'];
	}
	
	// #### AUCTION SETTINGS TAB ###################################
	//$global_auctionoptions = $ilance->admincp->construct_admin_input('globalauctionsettings', $ilpage['distribution']);
	
	$pprint_array = array('auctiontype_pulldown2','project_title','user_id','project_id','buildversion','ilanceversion','login_include_admin','auction_status_pulldown2','auction_type_pulldown2','wysiwyg_area','global_auctionoptions','configuration_moderationsystem','project_questions','auction_status_pulldown','productprevnext','numberproduct','pagetype','page','viewtype','id','bidapplet','auction_type_pulldown','numbermoderation','serviceprevnext','moderateprevnext','numberservice','id','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	//($apihook = $ilance->api('admincp_auctions_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'auctions.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','bid_results_rows','serviceescrows','productescrows','productauctions','updateserviceauction'));
	if (!isset($updateserviceauction))
	{
		$updateserviceauction = array();
	}
	@reset($updateserviceauction);
	while ($i = @each($updateserviceauction))
	{
		$ilance->template->parse_loop('main', 'purchase_now_activity' . $i['value']['project_id']);
	}
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