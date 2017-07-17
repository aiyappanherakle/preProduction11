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
define('LOCATION', 'buying');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[buying]" => $ilcrumbs["$ilpage[buying]"]);

$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();

$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
$ilance->GPC['subcmd'] = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';




if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['buying'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}
refresh(HTTPS_SERVER . 'buy.php?cmd=active');exit();
// #### INVITATION CONTROLS ####################################################
if ($ilance->GPC['cmd'] == 'management' AND $ilance->GPC['subcmd'] == 'invitations')
{
	// #### INVITATION REMINDER ############################################
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'remind' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "project_invitations
			WHERE id = '" . intval($ilance->GPC['id']) . "'
				AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			
			$process = false;
			if ($res['date_of_remind'] != '0000-00-00 00:00:00')
			{
				$dor = explode(' ', $res['date_of_remind']);
				if ($dor[0] == DATETODAY)
				{
					$process = false;
				}
				else
				{
					$process = true;
				}
			}
			else
			{
				$process = true;						
			}
			
			if ($process)
			{
				$touser = fetch_user('username', $res['seller_user_id']);
				$toemail = fetch_user('email', $res['seller_user_id']);
			    
				$ilance->email = construct_dm_object('email', $ilance);
				$ilance->email->mail = $toemail;
				$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
				$ilance->email->get('auction_invite_reminder');		
				$ilance->email->set(array(
					'{{receiver}}' => $touser,
					'{{username}}' => $_SESSION['ilancedata']['user']['username'],
					'{{project_id}}' => $res['project_id'],
				));
				$ilance->email->send();
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_invitations
					SET date_of_remind = '" . DATETIME24H . "'
					WHERE id = '" . intval($ilance->GPC['id']) . "'
				");
				
				refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management');
				exit();
			}
			else
			{
				$area_title = $phrase['_error_invitation_reminder'];
				$page_title = SITE_NAME . ' - ' . $phrase['_error_invitation_reminder'];
				
				print_notice($phrase['_one_invitation_per_each_user_per_day'], $phrase['_it_appears_you_have_already_reminded_this_user_about_your_project_today'], $ilpage['buying'] . '?cmd=management', $phrase['_buying_activity']);
				exit();
			}
		}
	}
	
	// #### REMOVE INVITATION ##############################################
	else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'remove-invite' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "project_invitations
			WHERE id = '" . intval($ilance->GPC['id']) . "'
				AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			
			$sql2 = $ilance->db->query("
				DELETE FROM " . DB_PREFIX . "project_invitations
				WHERE id = '" . intval($ilance->GPC['id']) . "'
					AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
			
			$touser = fetch_user('username', $res['seller_user_id']);
			$toemail = fetch_user('email', $res['seller_user_id']);
			
			$ilance->email = construct_dm_object('email', $ilance);
			
			$ilance->email->mail = $toemail;
			$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			$ilance->email->get('auction_uninvite_reminder');		
			$ilance->email->set(array(
				'{{receiver}}' => $touser,
				'{{username}}' => $_SESSION['ilancedata']['user']['username'],
				'{{project_id}}' => $res['project_id'],
			));
			$ilance->email->send();
				
			refresh(HTTP_SERVER . $ilpage['buying']);
			exit();
		}
	}
	
	// #### INVALID INVITATION ACTION ######################################
	else
	{
		print_notice($phrase['_invalid_auction_command'], $phrase['_sorry_the_action_you_are_trying_to_take_is_invalid_please_click_back_on_your_browser'], $ilpage['main'], $phrase['_main_menu']);
		exit();
	}
}

// #### NEW AUCTION CREATION ###################################################
else if ($ilance->GPC['cmd'] == 'rfp')
{
	// #### are we are inviting single or multiple members? ################
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'invite')
	{
		// it appears we are inviting members to a new service auction
		// let's create temp sessions with the invitation data
		if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
		{
			// we are inviting members to an existing auction ..
			$ilance->auction = construct_object('api.auction');
			$ilance->auction_rfp = construct_object('api.auction_rfp');
			
			// count how many members are being invited        
			$count = count($ilance->GPC['invitationid']);
			if ($count > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$ilance->auction_rfp->insert_auction_invitation($_SESSION['ilancedata']['user']['userid'], $ilance->GPC['invitationid'][$i], $ilance->GPC['project_id'], 0, 'service');
				}
			}
			
			$area_title = $phrase['_vendor_was_invited_to_rfp_menu'];
			$page_title = SITE_NAME . ' - ' . $phrase['_vendor_was_invited_to_rfp_menu'];
			
			print_notice($phrase['_vendor_invited_to_your_rfp'], $phrase['_congratulations_you_have_successfully_invited_a_service_vendor_to_bid_on_your_auction'], $ilpage['buying'], $phrase['_buying_activity']);
			exit();
		}
		else
		{
			// we are inviting members to a new auction we'll be creating now (or later)
			// let's build a new tmp session and hold the members to be invited
			$_SESSION['ilancedata']['tmp'] = array(
				'invitations' => serialize($ilance->GPC['invitationid'])
			);
			
			$url = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . 'buy' : HTTP_SERVER . $ilpage['main'] . '?cmd=buying';
			header('Location: ' . $url);
			exit();
		}                                
	}
	
	else
	{
		// #### define top header nav ##################################
		$topnavlink = array(
			'main_buying'
		);
		
		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] == 0)
		{
			print_notice($phrase['_disabled'], $phrase['_were_sorry_this_feature_is_currently_disabled'], $ilpage['main'], $phrase['_main_menu']);
			exit();
		}
    
		$ilance->subscription = construct_object('api.subscription');                        
		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceauctions') == 'no')
		{
			$area_title = $phrase['_viewing_access_denied_menu'];
			$page_title = SITE_NAME . ' - ' . $phrase['_viewing_access_denied_menu'];

			$navcrumb = array();
			$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
			$navcrumb["$ilpage[buying]?cmd=management"] = $phrase['_buying_activity'];
			$navcrumb[""] = $phrase['_new_service_auction'];
			
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('createserviceauctions'));
			exit();
		}
    
		$area_title = $phrase['_posting_new_service_rfp_category_selection_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_posting_new_service_rfp_category_selection_menu'];
		
		$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		
		$navcrumb = array();
		$navcrumb["$ilpage[buying]?cmd=rfp"] = $phrase['_buy'];
    
		// #### handle pre-invited registered members ##################	
		$inviteduserlist = $invitespaces = '';
		$invitewidth = '0';
		if (!empty($_SESSION['ilancedata']['tmp']['invitations']) AND is_serialized($_SESSION['ilancedata']['tmp']['invitations']))
		{
			$invitedusers = unserialize($_SESSION['ilancedata']['tmp']['invitations']);
			$invitedcount = count($invitedusers);
			if ($invitedcount > 0 AND is_array($invitedusers))
			{
				foreach ($invitedusers AS $userid)
				{
					$inviteduserlist .= '<strong>' . fetch_user('username', $userid) . '</strong>, ';
				}
				
				if (!empty($inviteduserlist))
				{
					$inviteduserlist = '<div>' . mb_substr($inviteduserlist, 0, -2) . '</div>';
				}
			}
			
			$inviteduserlist = '
			<div class="block-wrapper">
				<div class="block2">
						<div class="block2-top">
								<div class="block2-right">
										<div class="block2-left"></div>
								</div>
						</div>
						<div class="block2-header">' . $phrase['_vendors_invited'] . '</div>
						<div class="block2-content" style="padding:9px">' . $inviteduserlist . '</div>
						<div class="block2-footer">
								<div class="block2-right">
										<div class="block2-left"></div>
								</div>
						</div>
				</div>
			</div>';
			
			$invitespaces = '&nbsp;';
			$invitewidth = '200';
		}
		
		$pprint_array = array('invitewidth','invitespaces','inviteduserlist','category','additionalcategories','cid','categories','hidden_invitations','invitation','rfp_category_js','rfp_category_left','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'listing_reverse_auction_category.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}

// #### CREATE AND UPDATE SERVICE AUCTION RFP ##################################
else if (($ilance->GPC['cmd'] == 'new-rfp' AND isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0 OR $ilance->GPC['cmd'] == 'rfp-management' AND (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($ilance->GPC['rfpid']) AND $ilance->GPC['rfpid'] > 0)))
{
	$ilance->subscription = construct_object('api.subscription');
	
	if ($ilance->GPC['cmd'] == 'new-rfp')
	{
		// set top nav link to "Buy" hover state
		// #### define top header nav ##################################
		$topnavlink = array(
			'main_buying'
		);
	}
	else
	{
		// set top nav link to "My CP" hover state
		// #### define top header nav ##################################
		$topnavlink = array(
			'mycp'
		);
	}
	
	if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] == 0)
	{
		print_notice($phrase['_disabled'], $phrase['_were_sorry_this_feature_is_currently_disabled'], $ilpage['main'], $phrase['_main_menu']);
		exit();
	}
	
	// check permissions of buyer attemping to post a new service project
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceauctions') == 'no' OR !empty($_SESSION['ilancedata']['user']['active']) AND $_SESSION['ilancedata']['user']['active'] == 'no')
	{
		if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
		{
			$area_title = $phrase['_access_denied'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
			
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <a href="' . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('createserviceauctions'));
			exit();
		}
	}
	
	$ilance->auction = construct_object('api.auction');
	$ilance->auction_rfp = construct_object('api.auction_rfp');
	$ilance->auction_post = construct_object('api.auction_post');
	$ilance->auction_questions = construct_object('api.auction_questions');
	$ilance->subscription = construct_object('api.subscription');
	$ilance->categories_pulldown = construct_object('api.categories_pulldown');
	
	$categorycache = $ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true);
	
	$navcrumb = array();
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$navcrumb[HTTP_SERVER . "buy"] = $phrase['_buy'];
	}
	else
	{
		$navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=buying"] = $phrase['_buy'];
	}
	
	$show['bidsplaced'] = false;
	
	// #### set default listing state ######################################
	$ilance->GPC['project_state'] = 'service';
	
	// #### SUBMIT NEW SERVICE AUCTION #####################################
	if (isset($ilance->GPC['dosubmit']))
	{
		// #### final category checkup #################################
		if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'service', $ilance->GPC['cid']) == false)
		{
			print_notice($phrase['_this_is_a_nonposting_category'], $phrase['_please_choose_another_category_to_list_your_auction_under_this_category_is_currently_reserved_for_postable_subcategories_and_does_not_allow_any_auction_postings'], 'javascript:history.back(1);', $phrase['_back']);
			exit();
		}
		
		$_SESSION['ilancedata']['tmp']['new_project_id'] = '';
		unset($_SESSION['ilancedata']['tmp']['new_project_id']);
    
		$area_title = $phrase['_saving_new_service_auction'];
		$page_title = SITE_NAME . ' - ' . $phrase['_saving_new_service_auction'];
    
		// #### AUCTION FILTERS ########################################
		$ilance->GPC['filtered_auctiontype'] = 'regular';
		$ilance->GPC['filter_rating'] = isset($ilance->GPC['filter_rating']) ? intval($ilance->GPC['filter_rating']) : '0';
		$ilance->GPC['filtered_rating'] = isset($ilance->GPC['filtered_rating']) ? $ilance->GPC['filtered_rating'] : '';
		$ilance->GPC['filter_country'] = isset($ilance->GPC['filter_country']) ? intval($ilance->GPC['filter_country']) : '0';
		$ilance->GPC['filtered_country'] = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : '';
		$ilance->GPC['filter_state'] = isset($ilance->GPC['filter_state']) ? intval($ilance->GPC['filter_state']) : '0';
		$ilance->GPC['filtered_state'] = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : '';
		$ilance->GPC['filter_city'] = isset($ilance->GPC['filter_city']) ? intval($ilance->GPC['filter_city']) : '0';
		$ilance->GPC['filtered_city'] = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : '';
		$ilance->GPC['filter_zip'] = isset($ilance->GPC['filter_zip']) ? intval($ilance->GPC['filter_zip']) : '0';
		$ilance->GPC['filtered_zip'] = isset($ilance->GPC['filtered_zip']) ? $ilance->GPC['filtered_zip'] : '';
		$ilance->GPC['filter_underage'] = isset($ilance->GPC['filter_underage']) ? $ilance->GPC['filter_underage'] : '0';
		$ilance->GPC['filter_businessnumber'] = isset($ilance->GPC['filter_businessnumber']) ? $ilance->GPC['filter_businessnumber'] : '0';
		$ilance->GPC['filter_publicboard'] = isset($ilance->GPC['filter_publicboard']) ? intval($ilance->GPC['filter_publicboard']) : '0';
		$ilance->GPC['filter_escrow'] = isset($ilance->GPC['filter_escrow']) ? intval($ilance->GPC['filter_escrow']) : '0';
		$ilance->GPC['filter_gateway'] = '0';
		$ilance->GPC['filter_offline'] = isset($ilance->GPC['filter_offline']) ? intval($ilance->GPC['filter_offline']) : '0';
		$ilance->GPC['paymethod'] = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : array();
		$ilance->GPC['paymethodoptions'] = isset($ilance->GPC['paymethodoptions']) ? $ilance->GPC['paymethodoptions'] : array();
		$ilance->GPC['paymethodoptionsemail'] = isset($ilance->GPC['paymethodoptionsemail']) ? $ilance->GPC['paymethodoptionsemail'] : array();
		
		// #### CUSTOM BIDDING TYPE ACCEPTANCE FILTERS #########
		$ilance->GPC['filter_bidtype'] = isset($ilance->GPC['filter_bidtype']) ? $ilance->GPC['filter_bidtype'] : '0';
		$ilance->GPC['filtered_bidtype'] = isset($ilance->GPC['filtered_bidtype']) ? $ilance->GPC['filtered_bidtype'] : 'entire';
		
		// #### AUCTION DETAILS ################################
		$ilance->GPC['description_videourl'] = isset($ilance->GPC['description_videourl']) ? strip_tags($ilance->GPC['description_videourl']) : '';
		$ilance->GPC['project_type'] = 'reverse';
		$ilance->GPC['status'] = 'open';
		$ilance->GPC['draft'] = '0';
		if (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft'])
		{
			$ilance->GPC['draft'] = '1';
			$ilance->GPC['status'] = 'draft';
		}
		
		// #### BUDGET DETAILS #################################
		if ($ilance->GPC['filter_budget'] == 0)
		{
			$ilance->GPC['filtered_budgetid'] = 0;
		}
    
		// #### CUSTOM INFORMATION #############################
		$ilance->GPC['custom'] = (!empty($ilance->GPC['custom']) ? $ilance->GPC['custom'] : array());
		$ilance->GPC['pa'] = (!empty($ilance->GPC['pa']) ? $ilance->GPC['pa'] : array());
		$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
		
		// #### SCHEDULED AUCTION ONLY #########################
		$ilance->GPC['year'] = (isset($ilance->GPC['year'])) ? $ilance->GPC['year'] : '';
		$ilance->GPC['month'] = (isset($ilance->GPC['month'])) ? $ilance->GPC['month'] : '';
		$ilance->GPC['day'] = (isset($ilance->GPC['day'])) ? $ilance->GPC['day'] : '';
		$ilance->GPC['hour'] = (isset($ilance->GPC['hour'])) ? $ilance->GPC['hour'] : '';
		$ilance->GPC['min'] = (isset($ilance->GPC['min'])) ? $ilance->GPC['min'] : '';
		$ilance->GPC['sec'] = (isset($ilance->GPC['sec'])) ? $ilance->GPC['sec'] : '';
		
		// #### service location #######################################
		$ilance->GPC['city'] = (isset($ilance->GPC['city'])) ? $ilance->GPC['city'] : $_SESSION['ilancedata']['user']['city'];
		$ilance->GPC['state'] = (isset($ilance->GPC['state'])) ? $ilance->GPC['state'] : $_SESSION['ilancedata']['user']['state'];
		$ilance->GPC['zipcode'] = (isset($ilance->GPC['zipcode'])) ? $ilance->GPC['zipcode'] : $_SESSION['ilancedata']['user']['postalzip'];
		$ilance->GPC['country'] = (isset($ilance->GPC['country'])) ? $ilance->GPC['country'] : $_SESSION['ilancedata']['user']['country'];
		
		// #### currency ###############################################
		$ilance->GPC['currencyid'] = (isset($ilance->GPC['currencyid'])) ? intval($ilance->GPC['currencyid']) : $ilconfig['globalserverlocale_defaultcurrency'];
		
		// #### invited registered service providers ###################
		$ilance->GPC['invitedmember'] = isset($ilance->GPC['invitedmember']) ? $ilance->GPC['invitedmember'] : array();

		
		$apihookcustom = array();
		
		($apihook = $ilance->api('buying_submit_end')) ? eval($apihook) : false;
		
		// #### CREATE AUCTION #################################
		$ilance->auction_rfp->insert_service_auction(
			$_SESSION['ilancedata']['user']['userid'],
			$ilance->GPC['project_type'],
			$ilance->GPC['status'],
			$ilance->GPC['project_state'],
			$ilance->GPC['cid'],
			$ilance->GPC['rfpid'],
			$ilance->GPC['project_title'],
			$ilance->GPC['description'],
			$ilance->GPC['description_videourl'],
			$ilance->GPC['additional_info'],
			$ilance->GPC['keywords'],
			$ilance->GPC['custom'],
			$ilance->GPC['pa'],
			$ilance->GPC['filter_bidtype'],
			$ilance->GPC['filtered_bidtype'],
			$ilance->GPC['filter_budget'],
			$ilance->GPC['filtered_budgetid'],
			$ilance->GPC['filtered_auctiontype'],
			$ilance->GPC['filter_escrow'],
			$ilance->GPC['filter_gateway'],
			$ilance->GPC['filter_offline'],
			$ilance->GPC['paymethod'],
			$ilance->GPC['paymethodoptions'],
			$ilance->GPC['paymethodoptionsemail'],
			$ilance->GPC['project_details'],
			$ilance->GPC['bid_details'],
			$ilance->GPC['invitelist'],
			$ilance->GPC['invitemessage'],
			$ilance->GPC['invitedmember'],
			$ilance->GPC['year'],
			$ilance->GPC['month'],
			$ilance->GPC['day'],
			$ilance->GPC['hour'],
			$ilance->GPC['min'],
			$ilance->GPC['sec'],
			$ilance->GPC['duration'],
			$ilance->GPC['duration_unit'],
			$ilance->GPC['filtered_rating'],
			$ilance->GPC['filtered_country'],
			$ilance->GPC['filtered_state'],
			$ilance->GPC['filtered_city'],
			$ilance->GPC['filtered_zip'],
			$ilance->GPC['filter_rating'],
			$ilance->GPC['filter_country'],
			$ilance->GPC['filter_state'],
			$ilance->GPC['filter_city'],
			$ilance->GPC['filter_zip'],
			$ilance->GPC['filter_underage'],
			$ilance->GPC['filter_businessnumber'],
			$ilance->GPC['filter_publicboard'],
			$ilance->GPC['enhancements'],
			$ilance->GPC['draft'],
			$ilance->GPC['city'],
			$ilance->GPC['state'],
			$ilance->GPC['zipcode'],
			$ilance->GPC['country'],
			$skipemailprocess = 0,
			$apihookcustom,
			$isbulkupload = false,
			$ilance->GPC['currencyid']
		);

		exit();
	}
	
	// #### SAVE EXISTING SERVICE AUCTION ##################################
	else if (isset($ilance->GPC['dosave']))
	{
		// #### final category checkup #################################
		if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'service', $ilance->GPC['cid']) == false)
		{
			print_notice($phrase['_this_is_a_nonposting_category'], $phrase['_please_choose_another_category_to_list_your_auction_under_this_category_is_currently_reserved_for_postable_subcategories_and_does_not_allow_any_auction_postings'], 'javascript:history.back(1);', $phrase['_back']);
			exit();
		}
		
		if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'])
		{
			$ownerid = fetch_auction('user_id', intval($ilance->GPC['rfpid']));
		}
		else
		{
			$ownerid = $_SESSION['ilancedata']['user']['userid'];
		}
		
		if (empty($ilance->GPC['rfpid']) OR empty($ilance->GPC['description']) OR empty($ilance->GPC['date_end']) OR !mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})", $ilance->GPC['date_end']))
		{
			$area_title = $phrase['_rfp_details_update_error'];
			$page_title = SITE_NAME . ' - ' . $phrase['_rfp_details_update_error'];
			
			print_notice($phrase['_rfp_was_not_updated'], '<p>' . $phrase['_were_sorry_there_was_a_problem_updating_your_request_for_proposal'] . '</p><ul><li />' . $phrase['_description_can_not_be_empty'] . '<li />' . $phrase['_budget_can_not_be_empty'] . '<li />' . $phrase['_verify_the_end_date_for_your_rfp_is_formatted_correctly'] . '</ul><p>' . $phrase['_please_contact_customer_support'] . '</p>', 'javascript:history.back(1);', $phrase['_retry']);
			exit();
		}
		
		// handle updating any category questions for this auction (if changed)
		if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
		{
			$ilance->auction_post->process_custom_questions($ilance->GPC['custom'], $ilance->GPC['rfpid'], 'service');
		}
		
		// #### PROCESS CUSTOM PROFILE ANSWER FILTERS ##########
		if (isset($ilance->GPC['pa']) AND is_array($ilance->GPC['pa']))
		{
			// process our answer input and store them into the datastore
			$ilance->auction_rfp->insert_profile_answers($ilance->GPC['pa'], $ilance->GPC['rfpid']);
		}
		
		// handle listing differences and store into revision log
		$ilance->auction_post->handle_revision_log_changes('service');
		
		// #### HANDLE AUCTION LISTING ENHANCEMENTS ####################
		// this will attempt to debit the acocunt of the users account balance if possible
		$ilance->GPC['featured'] = isset($ilance->GPC['old']['featured']) ? $ilance->GPC['old']['featured'] : '';
		$ilance->GPC['highlite'] = isset($ilance->GPC['old']['highlite']) ? $ilance->GPC['old']['highlite'] : '';
		$ilance->GPC['bold'] = isset($ilance->GPC['old']['bold']) ? $ilance->GPC['old']['bold'] : '';                                
		$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
		
		if (is_array($ilance->GPC['enhancements']))
		{
			$enhance = $ilance->auction_rfp->process_listing_enhancements_transaction($ilance->GPC['enhancements'], $_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['rfpid']), 'update', 'service');
			if (is_array($enhance))
			{
				$ilance->GPC['featured'] = (int)$enhance['featured'];
				$ilance->GPC['highlite'] = (int)$enhance['highlite'];
				$ilance->GPC['bold'] = (int)$enhance['bold'];
			}
		}
		
		$ilance->GPC['featured_date'] = ($ilance->GPC['featured'] AND isset($ilance->GPC['old']['featured_date']) AND $ilance->GPC['old']['featured_date'] == '0000-00-00 00:00:00') ? DATETIME24H : '0000-00-00 00:00:00';
	
		// does owner extend the auction?
		$sqlextend = (isset($ilance->GPC['extend']) AND $ilance->GPC['extend'] > 0) ? "date_end = DATE_ADD(date_end, INTERVAL " . intval($ilance->GPC['extend']) . " DAY)," : '';
		
		$ilance->GPC['filter_rating'] = isset($ilance->GPC['filter_rating']) ? $ilance->GPC['filter_rating'] : $ilance->GPC['old']['filter_rating'];
		$ilance->GPC['filter_country'] = isset($ilance->GPC['filter_country']) ? $ilance->GPC['filter_country'] : $ilance->GPC['old']['filter_country'];
		$ilance->GPC['filter_state'] = isset($ilance->GPC['filter_state']) ? $ilance->GPC['filter_state'] : $ilance->GPC['old']['filter_state'];
		$ilance->GPC['filter_city'] = isset($ilance->GPC['filter_city']) ? $ilance->GPC['filter_city'] : $ilance->GPC['old']['filter_city'];
		$ilance->GPC['filter_zip'] = isset($ilance->GPC['filter_zip']) ? $ilance->GPC['filter_zip'] : $ilance->GPC['old']['filter_zip'];
		$ilance->GPC['filter_underage'] = isset($ilance->GPC['filter_underage']) ? $ilance->GPC['filter_underage'] : $ilance->GPC['old']['filter_underage'];
		$ilance->GPC['filter_businessnumber'] = isset($ilance->GPC['filter_businessnumber']) ? $ilance->GPC['filter_businessnumber'] : $ilance->GPC['old']['filter_businessnumber'];
		$ilance->GPC['filter_publicboard'] = isset($ilance->GPC['filter_publicboard']) ? $ilance->GPC['filter_publicboard'] : $ilance->GPC['old']['filter_publicboard'];
		$ilance->GPC['filter_bidtype'] = isset($ilance->GPC['filter_bidtype']) ? $ilance->GPC['filter_bidtype'] : $ilance->GPC['old']['filter_bidtype'];
		$ilance->GPC['filter_budget'] = isset($ilance->GPC['filter_budget']) ? $ilance->GPC['filter_budget'] : $ilance->GPC['old']['filter_budget'];
		$ilance->GPC['filter_escrow'] = isset($ilance->GPC['filter_escrow']) ? $ilance->GPC['filter_escrow'] : $ilance->GPC['old']['filter_escrow'];
		$ilance->GPC['filter_offline'] = isset($ilance->GPC['filter_offline']) ? $ilance->GPC['filter_offline'] : $ilance->GPC['old']['filter_offline'];
		$ilance->GPC['filtered_rating'] = isset($ilance->GPC['filtered_rating']) ? $ilance->GPC['filtered_rating'] : $ilance->GPC['old']['filtered_rating'];
		$ilance->GPC['filtered_country'] = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : $ilance->GPC['old']['filtered_country'];
		$ilance->GPC['filtered_state'] = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : $ilance->GPC['old']['filtered_state'];
		$ilance->GPC['filtered_city'] = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : $ilance->GPC['old']['filtered_city'];
		$ilance->GPC['filtered_zip'] = isset($ilance->GPC['filtered_zip']) ? $ilance->GPC['filtered_zip'] : $ilance->GPC['old']['filtered_zip'];
		$ilance->GPC['filtered_bidtype'] = isset($ilance->GPC['filtered_bidtype']) ? mb_strtolower($ilance->GPC['filtered_bidtype']) : $ilance->GPC['old']['filtered_bidtype'];
		/*
		$ilance->GPC['filtered_budgetid'] = (isset($ilance->GPC['filtered_budgetid']) AND $ilance->GPC['filtered_budgetid'] > 0 AND $ilance->GPC['filter_budget'] == 1)
			? $ilance->GPC['filtered_budgetid']
			: $ilance->GPC['old']['filtered_budgetid'];
		*/
		if (isset($ilance->GPC['filtered_budgetid']) AND $ilance->GPC['filtered_budgetid'] > 0 AND $ilance->GPC['filter_budget'] == 1)
		{
			$ilance->GPC['filtered_budgetid'] = $ilance->GPC['filtered_budgetid'];
		}
		else if (isset($ilance->GPC['filtered_budgetid']) AND $ilance->GPC['filter_budget'] == 0)
		{
			$ilance->GPC['filtered_budgetid'] = 0;
		}
		else
		{
			$ilance->GPC['filtered_budgetid'] = $ilance->GPC['old']['filtered_budgetid'];
		}

		$ilance->GPC['additional_info'] = isset($ilance->GPC['additional_info']) ? $ilance->GPC['additional_info'] : $ilance->GPC['old']['additional_info'];
		$ilance->GPC['description_videourl'] = isset($ilance->GPC['description_videourl']) ? strip_tags($ilance->GPC['description_videourl']) : $ilance->GPC['old']['description_videourl'];
		$ilance->GPC['keywords'] = isset($ilance->GPC['keywords']) ? strip_tags($ilance->GPC['keywords']) : $ilance->GPC['old']['keywords'];
		$ilance->GPC['paymethod'] = isset($ilance->GPC['paymethod']) ? serialize($ilance->GPC['paymethod']) : $ilance->GPC['old']['paymethod'];
		$ilance->GPC['paymethodoptions'] = isset($ilance->GPC['paymethodoptions']) ? serialize($ilance->GPC['paymethodoptions']) : $ilance->GPC['old']['paymethodoptions'];
		$ilance->GPC['paymethodoptionsemail'] = isset($ilance->GPC['paymethodoptionsemail']) ? serialize($ilance->GPC['paymethodoptionsemail']) : $ilance->GPC['old']['paymethodoptionsemail'];
		$ilance->GPC['project_title'] = isset($ilance->GPC['project_title']) ? strip_tags($ilance->GPC['project_title']) : $ilance->GPC['old']['project_title'];
		$ilance->GPC['project_details'] = isset($ilance->GPC['project_details']) ? $ilance->GPC['project_details'] : $ilance->GPC['old']['project_details'];
		
		// auction moderation logic
		$sql = $ilance->db->query("
			SELECT cid, status, project_state
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
				AND user_id = '" . intval($ownerid) . "'
		", 0, null, __FILE__, __LINE__);
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		
		$visible = (($ilconfig['moderationsystem_disableauctionmoderation'] == '1' OR $res['status'] == 'draft')
			? '1'
			: '0');
		
		// #### service location ###############################
		$ilance->GPC['city'] = (isset($ilance->GPC['city'])) ? $ilance->GPC['city'] : $ilance->GPC['old']['city'];
		$ilance->GPC['state'] = (isset($ilance->GPC['state'])) ? $ilance->GPC['state'] : $ilance->GPC['old']['state'];
		$ilance->GPC['zipcode'] = (isset($ilance->GPC['zipcode'])) ? $ilance->GPC['zipcode'] : $ilance->GPC['old']['zipcode'];
		$ilance->GPC['country'] = (isset($ilance->GPC['country'])) ? $ilance->GPC['country'] : $ilance->GPC['old']['country'];
		$ilance->GPC['countryid'] = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
		
		// developers can use below query to attach an api hook to final sql
		$query_field_data = '';
		
		($apihook = $ilance->api('update_service_auction_submit_start')) ? eval($apihook) : false;
		
		// update auction
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects 
			SET $sqlextend
			cid = '" . intval($ilance->GPC['cid']) . "',
			visible = '" . $visible . "',
			project_title = '" . $ilance->db->escape_string($ilance->GPC['project_title']) . "',
			description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
			description_videourl = '" . $ilance->db->escape_string($ilance->GPC['description_videourl']) . "',
			keywords = '" . $ilance->db->escape_string($ilance->GPC['keywords']) . "',
			additional_info = '" . $ilance->db->escape_string($ilance->GPC['additional_info']) . "',
			paymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
			paymethodoptions = '" . $ilance->db->escape_string($ilance->GPC['paymethodoptions']) . "',
			paymethodoptionsemail = '" . $ilance->db->escape_string($ilance->GPC['paymethodoptionsemail']) . "',
			bid_details = '" . $ilance->db->escape_string($ilance->GPC['bid_details']) . "',
			project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "',
			filter_rating = '" . intval($ilance->GPC['filter_rating']) . "',
			filter_country = '" . intval($ilance->GPC['filter_country']) . "',
			filter_state = '" . intval($ilance->GPC['filter_state']) . "',
			filter_city = '" . intval($ilance->GPC['filter_city']) . "',
			filter_zip = '" . intval($ilance->GPC['filter_zip']) . "',
			filter_underage = '" . intval($ilance->GPC['filter_underage']) . "',
			filter_businessnumber = '" . intval($ilance->GPC['filter_businessnumber']) . "',
			filter_publicboard = '" . intval($ilance->GPC['filter_publicboard']) . "',
			filter_bidtype = '" . intval($ilance->GPC['filter_bidtype']) . "',
			filter_budget = '" . intval($ilance->GPC['filter_budget']) . "',
			filter_escrow = '" . intval($ilance->GPC['filter_escrow']) . "',
			filter_gateway = '0',
			filter_offline = '" . intval($ilance->GPC['filter_offline']) . "',
			filtered_rating = '" . $ilance->db->escape_string($ilance->GPC['filtered_rating']) . "',
			filtered_country = '" . $ilance->db->escape_string($ilance->GPC['filtered_country']) . "',
			filtered_state = '" . ucfirst($ilance->db->escape_string($ilance->GPC['filtered_state'])) . "',
			filtered_city = '" . ucfirst($ilance->db->escape_string($ilance->GPC['filtered_city'])) . "',
			filtered_zip = '" . mb_strtoupper($ilance->db->escape_string($ilance->GPC['filtered_zip'])) . "',
			filtered_bidtype = '" . mb_strtoupper($ilance->db->escape_string($ilance->GPC['filtered_bidtype'])) . "',
			filtered_budgetid = '" . intval($ilance->GPC['filtered_budgetid']) . "',
			featured = '" . intval($ilance->GPC['featured']) . "',
			featured_date = '" . $ilance->db->escape_string($ilance->GPC['featured_date']) . "',
			highlite = '" . intval($ilance->GPC['highlite']) . "',
			bold = '" . intval($ilance->GPC['bold']) . "',
			countryid = '" . intval($ilance->GPC['countryid']) . "',
			country = '" . $ilance->db->escape_string($ilance->GPC['country']) . "',
			state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "',
			city = '" . $ilance->db->escape_string($ilance->GPC['city']) . "',
			zipcode = '" . $ilance->db->escape_string(format_zipcode($ilance->GPC['zipcode'])) . "',
			$query_field_data
			updateid = updateid + 1
			WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
				AND user_id = '" . intval($ownerid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		
		// #### determine if we need to move the category (user change)
		move_listing_category_from_to($ilance->GPC['rfpid'], $res['cid'], $ilance->GPC['cid'], $res['project_state'], $res['status'], $res['status']);
		unset($res);
		
		($apihook = $ilance->api('update_service_auction_submit')) ? eval($apihook) : false;
		
		$area_title = $phrase['_rfp_detailed_information_updated'];
		$page_title = SITE_NAME . ' - ' . $phrase['_rfp_detailed_information_updated'];
		   
		print_notice($phrase['_rfp_successfully_updated'], $phrase['_you_have_successfully_updated_your_request_for_proposal'] . " " . $phrase['_if_you_have_entered_a_new_ending_date_for_your_rfp_this_change_would_take_effect_immediately'] . "<br /><br />" . $phrase['_please_contact_customer_support'] . "<br /><br />", $ilpage['rfp'] . '?id=' . intval($ilance->GPC['rfpid']), $phrase['_return_to_the_previous_menu']);
		exit();                        
	}
	// #### UPDATE EXISTING SERVICE AUCTION ################################
	else
	{
		if ($ilance->GPC['cmd'] == 'new-rfp')
		{
			if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], $cattype = 'service', $ilance->GPC['cid']) == false)
			{
				$url = ($ilconfig['globalauctionsettings_seourls'])
					? HTTP_SERVER . 'buy'
					: HTTP_SERVER . $ilpage['main'] . '?cmd=buying';
				
				print_notice($phrase['_this_is_a_nonposting_category'], $phrase['_please_choose_another_category_to_list_your_auction_under_this_category_is_currently_reserved_for_postable_subcategories_and_does_not_allow_any_auction_postings'], $url, $phrase['_try_again']);
				exit();
			}
			
			$area_title = $phrase['_post_project'];
			$page_title = SITE_NAME . ' - ' . $phrase['_post_project'];
			
			// #### main category being posted in ##########
			$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
			
			// #### prevent the top cats in breadcrumb to contain any fields from this form
			$show['nourlbit'] = true;
			$ilance->categories->breadcrumb($cid, 'service', $_SESSION['ilancedata']['user']['slng']);
			$navcrumb[""] = $phrase['_post_project'];
			
			if (!empty($_SESSION['ilancedata']['tmp']['new_project_id']) AND $_SESSION['ilancedata']['tmp']['new_project_id'] > 0)
			{
				$project_id = $_SESSION['ilancedata']['tmp']['new_project_id'];
			}
			else
			{
				$project_id = $ilance->auction_rfp->construct_new_auctionid();
				$_SESSION['ilancedata']['tmp']['new_project_id'] = $project_id;
			}
			
			// #### saving as draft? #######################
			$draft = (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft']) ? 'checked="checked"' : '';
			
			$wysiwyg_area = print_wysiwyg_editor('description', '', 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $project_id, 'input', 'service', $columns = 4);
			$bidfilters = $ilance->auction_post->print_bid_filters();
			$profilebidfilters = $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'service');        
		}
		else
		{
			$area_title = $phrase['_update_project'];
			$page_title = SITE_NAME . ' - ' . $phrase['_update_project'];
			
			$project_id = intval($ilance->GPC['id']);
			
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($ilance->GPC['id']) . "'
					AND project_state = 'service'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$ilance->GPC = array_merge($ilance->GPC, $ilance->db->fetch_array($sql, DB_ASSOC));
				
				// #### can we update auction? #########
				$show['noupdateauction'] = ($ilance->GPC['status'] == 'open' OR $ilance->GPC['status'] == 'draft') ? 0 : 1;
				
				$date_end = $ilance->GPC['date_end'];
			}
			else
			{
				print_notice($phrase['_disabled'], $phrase['_were_sorry_this_feature_is_currently_disabled'], $ilpage['main'], $phrase['_main_menu']);
				exit();
			}
			
			// #### ADMIN UPDATING LISTING? ################
			if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] AND isset($ilance->GPC['admincp']) AND $ilance->GPC['admincp'])
			{
				// admin updating listing: show additional details
				$ilance->admincp = construct_object('api.admincp');
				$ilance->feedback = construct_object('api.feedback');
				
				// inline auction ajax controls
				$headinclude .= "
<script type=\"text/javascript\">
<!--
var searchid = 0;
var value = '';
var type = '';
var imgtag = '';
var favoriteicon = '';
var status = '';
function reset_image()
{
	imgtag.src = favoriteicon;
}
function fetch_response()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
	{
		// format response
		response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
		phpstatus = xmldata.fetch_data(response);
		
		searchiconsrc = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '').src;
		status = searchiconsrc.match(/\/unchecked.gif/gi);
		if (status == '/unchecked.gif')
		{
		       status = 'unchecked';
		}
		else
		{
		       status = 'checked';
		}                                
		if (status == 'unchecked')
		{
			if (phpstatus == 'on' || phpstatus == 'off')
			{
				favoriteiconsrc = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '').src;
				imgtag = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '');
				
				favoriteicon2 = favoriteiconsrc.replace(/unchecked.gif/gi, 'working.gif');
				imgtag.src = favoriteicon2;
				
				favoriteicon = favoriteiconsrc.replace(/unchecked.gif/gi, 'checked.gif');
				var t = window.setTimeout('reset_image()', 700);
			}
			else
			{
				alert(phpstatus);
			}
		}
		else if (status == 'checked')
		{
			if (phpstatus == 'on' || phpstatus == 'off')
			{
				favoriteiconsrc = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '').src;
				imgtag = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '');
				
				favoriteicon2 = favoriteiconsrc.replace(/checked.gif/gi, 'working.gif');
				imgtag.src = favoriteicon2;
	
				favoriteicon = favoriteiconsrc.replace(/checked.gif/gi, 'unchecked.gif');
				var t = window.setTimeout('reset_image()', 700);
			}
			else
			{
				alert(phpstatus); 
			}
		}
		xmldata.handler.abort();
	}
}
function update_enhancement(searchid, type)
{                        
	// set ajax handler
	xmldata = new AJAX_Handler(true);
	
	// url encode the vars
	searchid = urlencode(searchid);
	xmldata.searchid = searchid;
	
	type = urlencode(type);
	xmldata.type = type;
	
	searchiconsrc = fetch_js_object('inline_enhancement_' + searchid + '_' + type + '').src;
	status = searchiconsrc.match(/\/unchecked.gif/gi);
	if (status == '/unchecked.gif')
	{
	       value = 'on';
	}
	else
	{
	       value = 'off';
	}
	xmldata.onreadystatechange(fetch_response);
	
	// send data to php
	xmldata.send('ajax.php', 'do=acpenhancements&value=' + value + '&id=' + searchid + '&type=' + type + '&s=' + ILSESSION + '&token=' + ILTOKEN);                        
}
//-->
</script>";                                        
				$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
				$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown($ilance->GPC['cid'], $cattype = 'service', $type = 'level', $fieldname = 'cid', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 1, $expertspulldown = 0, $canassigntoall = false, $showbestmatching = false, $ilance->categories->cats);
				
				if ($ilance->GPC['visible'])
				{
					$auctionvisible = ($ilance->GPC['status'] == 'draft') ? '<label for="visible1"><input type="radio" name="visible" value="1" disabled="disabled" id="visible1" /> ' . $phrase['_yes'] . '</label> <label for="visible0"><input type="radio" name="visible" value="0" checked="checked" id="visible0" /> ' . $phrase['_no'] . '</label>' : '<label for="visible1"><input type="radio" name="visible" value="1" checked="checked" id="visible1" /> ' . $phrase['_yes'] . '</label> <label for="visible0"><input type="radio" name="visible" value="0" id="visible0" /> ' . $phrase['_no'] . '</label>';
				}
				else
				{
					$auctionvisible = '<label for="visible1"><input type="radio" name="visible" value="1" id="visible1" /> ' . $phrase['_yes'] . '</label> <label for="visible0"><input type="radio" name="visible" value="0" checked="checked" id="visible0" /> ' . $phrase['_no'] . '</label>';
				}
				
				$transfer_ownership = $ilance->auction->fetch_transfer_ownership($project_id);
				$project_state_pulldown = $ilance->admincp->auction_state_pulldown($project_id);
				$project_details_pulldown = $ilance->admincp->auction_details_pulldown($ilance->GPC['project_details'], 0, 'service');
				$status_pulldown = $ilance->admincp->auction_status_pulldown($ilance->GPC['status'], false, 'service');
				$enhancement_list = $ilance->admincp->fetch_auction_enhancements_list($project_id);
				$date_added = $ilance->GPC['date_added'];
				$date_starts = $ilance->GPC['date_starts'];
			}
			
			// main category being posted in
			$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
			
			$show['nourlbit'] = true;
			$ilance->categories->breadcrumb($cid, 'service', $_SESSION['ilancedata']['user']['slng']);
			$navcrumb[""] = $phrase['_update_project'];
			
			// fetch attachments uploaded
			$attachmentlist = fetch_inline_attachment_filelist($_SESSION['ilancedata']['user']['userid'], $project_id, 'project');
			
			// saving as draft?
			$draft = '';
			if ($ilance->GPC['status'] == 'draft')
			{
				$draft = 'checked="checked"';
			}
			
			// existing information in hidden fields so we can compare for revision log
			$hiddenfields = print_hidden_fields(false, array('buynow','buynow_price','buynow_qty','reserve','startprice','retailprice','invoiceid','escrow_id','bids','budgetgroup','transfer_to_userid','transfer_from_userid','cmd','date_end','filtered_auctiontype','project_id','project_type','reserve_price','rfpid','state','updateid','fvf','insertionfee','currentprice','bid_details','project_state','transfertype','close_date','status','views','visible','user_id','date_added','id','fvfinvoiceid','ifinvoiceid','isifpaid','isfvfpaid'), $questionmarkfirst = false, $prepend_text = 'old[', $append_text = ']');
			$wysiwyg_area = print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $project_id, 'update', 'service', $columns = 4);
			$bidfilters = $ilance->auction_post->print_bid_filters();
			$profilebidfilters = $ilance->auction_post->print_profile_bid_filters($cid, 'update', 'service', $project_id);
			
			// update mode
			$date_end = $ilance->GPC['date_end'];
			$extendauction = $ilance->auction_post->print_extend_auction($fieldname = 'extend');
			
			// rebuild selected auction enhancements
			$show['disableselectedenhancements'] = true;
			if ($ilance->GPC['featured'])
			{
				$ilance->GPC['enhancements']['featured'] = 1;
			}
			if ($ilance->GPC['highlite'])
			{
				$ilance->GPC['enhancements']['highlite'] = 1;
			}
			if ($ilance->GPC['bold'])
			{
				$ilance->GPC['enhancements']['bold'] = 1;
			}
			
			// repopulate the invitation user list
			$invitesql = $ilance->db->query("
				SELECT invite_message, email, name
				FROM " . DB_PREFIX . "project_invitations
				WHERE project_id = '$project_id'
					AND email != ''
					AND name != ''
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($invitesql) > 0)
			{
				while ($inviteres = $ilance->db->fetch_array($invitesql, DB_ASSOC))
				{
					$ilance->GPC['invitelist']['email'][] = $inviteres['email'];
					$ilance->GPC['invitelist']['name'][] = $inviteres['name'];
					$ilance->GPC['invitemessage'] = $inviteres['invite_message'];
				}
			}
			
			$show['bidsplaced'] = false;
			if ($ilance->GPC['bids'] > 0)
			{
				if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'])
				{
					// this is so admin can update all fields of the listing
					$show['bidsplaced'] = false;
				}
				else
				{
					$show['bidsplaced'] = true;
				}
			}
		}
	}
	
	// some javascript above the template (not between <head>..)
	$js_start = $ilance->auction_post->print_js('service');
	
	// build an if condition to either show advanced profile filters or hide them if none available
	$filter_quantity = $ilance->auction_post->get_filters_quantity($cid);
	
	// #### auction title ##########################################
	if ($ilance->GPC['cmd'] == 'new-rfp')
	{
		$title = $ilance->auction_post->print_title_input('project_title');	
	}
	else
	{
		if ($ilconfig['globalfilters_changeauctiontitle'] == '1' AND $show['bidsplaced'] == false)
		{
			$title = $ilance->auction_post->print_title_input('project_title');
		}
		else
		{
			$title = $ilance->auction_post->print_title_input('project_title', true);
		}
	}
	
	// #### video description cost #################################
	$videodescriptioncost = ($ilconfig['serviceupsell_videodescriptioncost'] > 0)
		? $ilance->currency->format($ilconfig['serviceupsell_videodescriptioncost'])
		: $phrase['_free'];
	
	// #### video description ######################################
	$description_videourl = $ilance->auction_post->print_video_description_input($fieldname = 'description_videourl', $disabled = $show['bidsplaced']);
	
	// additional info input
	$additional = $ilance->auction_post->print_additional_info_input($fieldname = 'additional_info');

	// keywords input
	$keywordinput = $ilance->auction_post->print_keywords_input($fieldname = 'keywords');

	// upload attachment button
	$attachment_style = ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'attachments') == 'yes') ? '' : 'disabled="disabled"';
	$uploadbutton = '<input name="attachment" onclick=Attach("' . HTTP_SERVER . $ilpage['upload'] . '?crypted=' . encrypt_url(array('attachtype' => 'project', 'project_id' => $project_id, 'user_id' => $_SESSION['ilancedata']['user']['userid'], 'category_id' => $cid, 'filehash' => md5(time()), 'max_filesize' => $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit'))) . '") type="button" value="' . $phrase['_upload'] . '" class="buttons" ' . $attachment_style . ' style="font-size:15px" />';

	// bid amount type pulldown
	$bidtypefilter = $ilance->auction_post->print_bid_amount_type($cid, 'service');

	// construct budget logic pulldown
	$budgetfilter = $ilance->auction_post->print_budget_logic_type($cid, 'service');

	// escrow filter (if enabled, javascript will hide the payment methods input box on preview also)
	$escrowfilter = $ilance->auction_post->print_escrow_filter($cid, 'service', 'servicebuyer');

	// auction event access
	$auctioneventtype = $ilance->auction_post->print_event_type_filter('service');
	
	// invitation options and controls
	$inviteoptions = $ilance->auction_post->print_invitation_controls('service');

	// duration
	$duration = $ilance->auction_post->duration($fieldname = 'duration');

	// realtime scheduled event date/time
	$durationbits = $ilance->auction_post->print_duration_logic($fieldname = 'duration_unit');

	// bidding privacy
	$biddingprivacy = $ilance->auction_post->print_bid_privacy($fieldname = 'bid_details');

	// public message boards?
	$publicboard = $ilance->auction_post->print_public_board($fieldname = 'filter_publicboard');
	
	// construct countries / states pulldown
	$jscountry = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : $ilconfig['registrationdisplay_defaultcountry'];
	$jsstate = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : $ilconfig['registrationdisplay_defaultstate'];
	$jscity = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : $ilconfig['registrationdisplay_defaultcity'];
	
	$countryid = fetch_country_id($jscountry, $_SESSION['ilancedata']['user']['slng']);
	$country_js_pulldown = construct_country_pulldown($countryid, $jscountry, 'filtered_country', false, 'filtered_state');
	$state_js_pulldown = '<div id="stateid" style="height:20px">' . construct_state_pulldown($countryid, $jsstate, 'filtered_state') . '</div>';
	unset($jscountry, $jsstate, $jscity);

	// save as draft
	$saveasdraft = '<label for="savedraft"><input type="checkbox" id="savedraft" name="saveasdraft" value="1" ' . $draft . ' /> ' . $phrase['_save_this_auction_as_a_draft'] . '</label>';
	
	// print listing auction enhancements
	$enhancements = $ilance->auction_post->print_listing_enhancements('service');

	// custom insertion fees in this category
	$insertionfees = $ilance->auction_post->print_insertion_fees($cid, 'service');
	
	// custom budget based insertion fees
	$budgetinsertionfees = $ilance->auction_post->print_budget_insertion_fees($cid);
	
	// default livefee breakdown
	$currency = print_left_currency_symbol();
	$livefee = ($show['insertionfeeamount'] + $show['selectedbudgetlogic'] + $show['selectedenhancements']);
	$livefee = sprintf("%01.2f", $livefee);
	
	$ilance->template->fetch('main', 'listing_reverse_auction_create.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	
	$pprint_array = array('videodescriptioncost','description_videourl','transfer_ownership','auctionvisible','category_pulldown','project_state_pulldown','project_details_pulldown','status_pulldown','enhancement_list','date_added','date_starts','tab','hiddenfields','date_end','currency','livefee','paymentmethod','extendauction','inviteoptions','title','additional','keywordinput','budgetinsertionfees','instantpay','js_end','js_start','wysiwyg_area','insertionfees','additionalcategory','listingfees','enhancements','keywords','saveasdraft','maincategory','paymentmethods','attachmentlist','bidfilters','profilebidfilters','publicboard','biddingprivacy','durationbits','auctioneventtype','escrowfilter','budgetfilter','bidtypefilter','attachmentlist','additional_info','description','preview_pane','cid','js','state_js_pulldown','country_js_pulldown','bidamounttype_pulldown','moderationalert','project_questions','uploadbutton','project_title','budget_pulldown','duration','year','month','day','hour','min','sec','invitation','invitationid','country_pulldown','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','project_id','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('new_rfp_end')) ? eval($apihook) : false;
	
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### DRAFT AUCTION MANAGEMENT ###############################################
else if ($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'drafts')
{
	// #### define top header nav ##################################################
	$topnavlink = array(
		'mycp'
	);
	
	$show['widescreen'] = true;
	
	// #### DELETE DRAFT AUCTION ###################################
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'deleterfp' AND isset($ilance->GPC['rfp']))
		{
			foreach ($ilance->GPC['rfp'] AS $value)
			{
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND status = 'draft'
				", 0, null, __FILE__, __LINE__);
			}                                      
		}
	}
				       
	// #### POST DRAFT AUCTION PUBLIC ##############################
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_do-draft-create' AND isset($ilance->GPC['rfp']))
	{
		$ilance->auction = construct_object('api.auction');
		$ilance->auction_rfp = construct_object('api.auction_rfp');
		$ilance->email = construct_dm_object('email', $ilance);
		$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, false);
				
		foreach ($ilance->GPC['rfp'] AS $value)
		{
			// does admin enable or disable moderation?
			if ($ilconfig['moderationsystem_disableauctionmoderation'])
			{
				$sql = $ilance->db->query("
					SELECT user_id, cid, project_title, description, project_state, project_details, bid_details, date_starts, date_end, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(date_added) AS seconds
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					
					$cid1 = $res['cid'];
					
					// seconds that have past since the listing was posted
					$secondspast = $res['seconds'];
					
					// fetch the new future date end based on elapsed seconds
					$sqltime = $ilance->db->query("
						SELECT DATE_ADD('$res[date_end]', INTERVAL $secondspast SECOND) AS new_date_end
					");
					$restime = $ilance->db->fetch_array($sqltime, DB_ASSOC);
					
					// new date end 
					$new_date_end = $restime['new_date_end'];
					$datenow = DATETIME24H;
					
					if ($res['project_details'] == 'realtime')
					{
						if ($datenow > $res['date_starts'])
						{
							$new_date_start = $datenow;
						}
						else
						{
							$new_date_start = $res['date_starts'];	
						}
					}
					else
					{
						$new_date_start = DATETIME24H;
					}
					
					// set auction to open state
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET status = 'open',
						visible = '1',
						date_starts = '" . $ilance->db->escape_string($new_date_start) . "',
						date_end = '" . $ilance->db->escape_string($new_date_end) . "'				
						WHERE project_id = '" . intval($value) . "'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND status = 'draft'
					", 0, null, __FILE__, __LINE__);
					
					($apihook = $ilance->api('buyer_draft_action_validate_foreach')) ? eval($apihook) : false;
					
					// rebuild category count
					build_category_count($cid1, 'add', "post draft listing public: adding increment count category id $cid1");
					
					// send email to auction owner
					$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
					$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
					$ilance->email->get('new_auction_open_for_bids');		
					$ilance->email->set(array(
						'{{username}}' => $_SESSION['ilancedata']['user']['username'],
						'{{projectname}}' => strip_tags($res['project_title']),
						'{{description}}' => strip_tags($res['description']),
						'{{bids}}' => '0',
						'{{category}}' => $ilance->categories->recursive($cid1, 'service', $_SESSION['ilancedata']['user']['slng'], 1, '', 0),
						'{{budget}}' => $ilance->auction_rfp->construct_budget_overview($cid1, fetch_auction('filtered_budgetid', intval($value))),
						'{{p_id}}' => intval($value),
						'{{details}}' => ucfirst($res['project_details']),
						'{{privacy}}' => ucfirst($res['bid_details']),
						'{{closing_date}}' => print_date(fetch_auction('date_end', intval($value)), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
					));
					$ilance->email->send();
					
					$area_title = $phrase['_new_service_auctions_posted_menu'];
					$page_title = SITE_NAME . ' - ' . $phrase['_new_service_auctions_posted_menu'];
			
					// dispatch email to any service providers the buyer had chose to invite to bid (empty array should fetch all invited users from db instead)
					$ilance->auction_rfp->dispatch_invited_members_email(array(), 'service', fetch_auction('project_id', intval($value)), $_SESSION['ilancedata']['user']['userid']);
					
					// did this buyer manually enter email addresses to invite users to bid?
					$ilance->auction_rfp->dispatch_external_members_email('service', fetch_auction('project_id', intval($value)), $_SESSION['ilancedata']['user']['userid'], strip_tags(fetch_auction('project_title', intval($value))), fetch_auction('bid_details', intval($value)), fetch_auction('date_end', intval($value)), '', '', $skipemailprocess = 0);
					
					// #### REFERRAL SYSTEM TRACKER ########
					update_referral_action('postauction', $_SESSION['ilancedata']['user']['userid']);
				}
			}
			else
			{
				// moderation enabled place in the rfp queue in admincp and send email to admin
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET status = 'open',
					visible = '0',
					date_starts = '" . DATETIME24H . "'
					WHERE project_id = '" . intval($value) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND status = 'draft'
				", 0, null, __FILE__, __LINE__);
			}
		}
		
		if ($ilconfig['moderationsystem_disableauctionmoderation'])
		{
			// moderation disabled
			$area_title = $phrase['_new_service_auctions_posted_menu'];
			$page_title = SITE_NAME . ' - ' . $phrase['_new_service_auctions_posted_menu'];
			
			$url = '';
			
			$pprint_array = array('url','session_project_title','session_description','session_additional_info','session_budget','country_pulldown','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			$ilance->template->fetch('main', 'listing_reverse_auction_complete.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			// show auction under moderation notice
			$area_title = $phrase['_new_service_auctions_posted_menu'];
			$page_title = SITE_NAME . ' - ' . $phrase['_new_service_auctions_posted_menu'];
			
			$ilance->email = construct_dm_object('email', $ilance);
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('new_auction_pending_moderation');		
			$ilance->email->set(array());
			$ilance->email->send();

			$url = '<a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending"><strong>' . $phrase['_pending_auctions_menu'] . '</strong></a>';
			$pprint_array = array('url','session_project_title','session_description','session_additional_info','session_budget','country_pulldown','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			$ilance->template->fetch('main', 'listing_reverse_auction_moderation.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
	
	// #### DRAFT AUCTION LISTINGS #################################
	
	$ilance->auction = construct_object('api.auction');
	$ilance->auction_rfp = construct_object('api.auction_rfp');
	$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $categorymode = 0, $propersort = false);
	
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	
	$limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	
	// #### LISTING PERIOD #########################################
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');
	
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
	$extra = '&amp;period=' . $ilance->GPC['period'];
	
	$servicetabs = print_buying_activity_tabs('drafts', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	
	$numberrows = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.status = 'draft'
			AND p.visible = '1'
	", 0, null, __FILE__, __LINE__);
	
	$number = $ilance->db->num_rows($numberrows);
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	
	$area_title = $phrase['_draft_rfps'];
	$page_title = SITE_NAME . ' - ' . $phrase['_draft_rfps'];
	
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
	$navcrumb["$ilpage[buying]?cmd=management"] = $phrase['_buying_activity'];
	$navcrumb[""] = $phrase['_draft_auctions'];

	$condition = $condition2 = '';
	$row_count = 0;
	
	$result = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.status = 'draft'
			AND p.visible = '1'
		$limit
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			// check for auction attachments
			$row['attach'] = '-';                                
			$sql_attachments = $ilance->db->query("
				SELECT attachid, filename, filehash
				FROM " . DB_PREFIX . "attachment
				WHERE project_id = '" . $row['project_id'] . "'
					AND user_id = '" . $row['user_id'] . "'
					AND visible = '1'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_attachments) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
				{
					$row['attach'] .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif"><span class="smaller"><a href="' . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a></span> ';
				}
			}
			$row['added'] = print_date($row['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$row['starts'] = print_date($row['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$row['ends'] = print_date($row['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$row['job_title'] = stripslashes($row['project_title']);
			$row['type'] = ucfirst($row['project_state']);
			$row['state'] = $row['project_state'];
			$row['description'] = short_string(stripslashes($row['description']), 100);				
			$row['category'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['project_state'], $row['cid']));
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="'.$row['project_id'].'" id="'.$row['project_state'].'_'.$row['project_id'].'" />';
			$row['status'] = $phrase['_pending'];
			$row['revisions'] = $row['updateid'];
			$row['invitecount'] = $ilance->auction_rfp->fetch_invited_users_count($row['project_id']);
			if ($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0)
			{
				$row['insfee'] = ($row['isifpaid'])
					? '<div class="smaller blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>'
					: '<div class="smaller red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>';
			}
			else
			{
				$row['insfee'] = '-';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$project_results_rows[] = $row;
			$row_count++;
		}
		$show['no_project_rows_returned'] = false;
		$show['rfppulldownmenu'] = true;
	}
	else
	{
		$show['no_project_rows_returned'] = true;
		$show['rfppulldownmenu'] = false;
	}
	
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['buying'].'?cmd=management&amp;sub=drafts');
	
	$pprint_array = array('servicetabs','rfpvisible','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'buying_drafts.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### ARCHIVED AUCTION MANAGEMENT ############################################
else if ($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'archived')
{
	// #### define top header nav ##################################################
	$topnavlink = array(
		'mycp'
	);
	
	$show['widescreen'] = true;
	
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	
	$limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];

	// #### LISTING PERIOD #########################################
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');                
	
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
	$extra = '&amp;period=' . $ilance->GPC['period'];
	
	$servicetabs = print_buying_activity_tabs('archived', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);

	$numberrows = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			AND p.status = 'archived'
			AND p.visible = '1'
	", 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);

	$area_title = $phrase['_archived_rfps'];
	$page_title = SITE_NAME . ' - ' . $phrase['_archived_rfps'];
			
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
	$navcrumb["$ilpage[buying]?cmd=management"] = $phrase['_buying_activity'];
	$navcrumb[""] = $phrase['_archived_auctions'];

	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	
	$row_count = 0;
	$condition = $condition2 = '';
			
	$result = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			AND p.status = 'archived'
			AND p.visible = '1'
		$limit
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			$row['provider'] = $row['pmb'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$row['job_title'] = stripslashes($row['project_title']);
			$row['description'] = short_string(stripslashes($row['description']), 100);
			$row['state'] = ucfirst($row['project_state']);
			if ($row['bids'] == 0)
			{
				$row['bids'] = '-';
			}
			if ($row['views'] == 0)
			{
				$row['views'] = '-';
			}
			if ($row['status'] != 'closed')
			{
				$dif = $row['mytime'];
				$ndays = floor($dif / 86400);
				$dif -= $ndays * 86400;
				$nhours = floor($dif / 3600);
				$dif -= $nhours * 3600;
				$nminutes = floor($dif / 60);
				$dif -= $nminutes * 60;
				$nseconds = $dif;				
				$sign = '+';
				if ($row['mytime'] < 0)
				{
					$row['mytime'] = - $row['mytime'];
					$sign = '-';
				}
				if ($sign == '-')
				{
					$project_time_left = '<span class="gray">' . $phrase['_ended'] . '</span>';
				}
				else
				{
					if ($ndays != '0')
					{
						$project_time_left = $ndays . $phrase['_d_shortform'].', ';
						$project_time_left .= $nhours . $phrase['_h_shortform'] . '+';
					}
					elseif ($nhours != '0')
					{
						$project_time_left = $nhours . $phrase['_h_shortform'] . ', ';
						$project_time_left .= $nminutes . $phrase['_m_shortform'] . '+';
					}
					else
					{
						$project_time_left = $nminutes . $phrase['_m_shortform'] . ', ';
						$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
					}
				}
				
				$row['timeleft'] = $project_time_left;
			}
			else
			{
				$project_time_left = '-';
			}
			
			$row['timeleft'] = $project_time_left;
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
			$row['status'] = $phrase['_archived'];
			if ($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0)
			{
				$row['insfee'] = ($row['isifpaid'])
					? '<div class="smaller blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>'
					: '<div class="smaller red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>';
			}
			else
			{
				$row['insfee'] = '-';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$project_results_rows[] = $row;
			$row_count++;
		}
		
		$show['no_project_rows_returned'] = false;
	}
	else
	{
		$show['no_project_rows_returned'] = true;
	}
	
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['buying'].'?cmd=management&amp;sub=archived');
	
	$pprint_array = array('servicetabs','rfpvisible','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'buying_archived.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### DELISTED AUCTION MANAGEMENT ############################################
else if ($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'delisted')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	$show['widescreen'] = true;
	
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	
	$limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	
	// #### LISTING PERIOD #########################################
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');
	
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
	$extra = '&amp;period=' . $ilance->GPC['period'];
	
	$servicetabs = print_buying_activity_tabs('delisted', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	
	$numberrows = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			AND p.status = 'delisted'
			AND p.visible = '1'
	", 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);
	
	$area_title = $phrase['_delisted_rfps'];
	$page_title = SITE_NAME . ' - ' . $phrase['_delisted_rfps'];
	
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
	$navcrumb["$ilpage[buying]?cmd=management"] = $phrase['_buying_activity'];
	$navcrumb[""] = $phrase['_delisted_auctions'];

	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	
	$row_count = 0;
	$condition = $condition2 = '';

	$result = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			AND p.status = 'delisted'
			AND p.visible = '1'
		$limit
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result))
		{
			// check for attachments
			$row['attach'] = '-';
			$sql_attachments = $ilance->db->query("
				SELECT attachid, filename, filehash
				FROM " . DB_PREFIX . "attachment
				WHERE attachtype = 'project'
					AND project_id = '" . $row['project_id'] . "'
					AND user_id = '" . $row['user_id'] . "'
					AND visible = '1'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_attachments) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql_attachments))
				{
					$row['attach'] .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif"><span class="smaller"><a href="' . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a></span> ';
				}
			}
			$row['provider'] = '';
			$row['pmb'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$row['state'] = ucfirst($row['project_state']);
			$row['job_title'] = stripslashes($row['project_title']);
			$row['description'] = short_string(stripslashes($row['description']), 100);
			if ($row['status'] != 'closed')
			{
				$dif = $row['mytime'];
				$ndays = floor($dif / 86400);
				$dif -= $ndays * 86400;
				$nhours = floor($dif / 3600);
				$dif -= $nhours * 3600;
				$nminutes = floor($dif / 60);
				$dif -= $nminutes * 60;
				$nseconds = $dif;
				$sign = '+';
				if ($row['mytime'] < 0)
				{
					$row['mytime'] = - $row['mytime'];
					$sign = '-';
				}
				if ($sign == '-')
				{
					$project_time_left = '<span class="gray">' . $phrase['_ended'] . '</span>';
				}
				else
				{
					if ($ndays != '0')
					{
						$project_time_left = $ndays . $phrase['_d_shortform'] . ', ';
						$project_time_left .= $nhours . $phrase['_h_shortform'] .'+';
					}
					elseif ($nhours != '0')
					{
						$project_time_left = $nhours . $phrase['_h_shortform'] . ', ';
						$project_time_left .= $nminutes . $phrase['_m_shortform'] . '+';
					}
					else
					{
						$project_time_left = $nminutes . $phrase['_m_shortform'] . ', ';
						$project_time_left .= $nseconds . $phrase['_s_shortform']. '+';
					}
				}
				$row['timeleft'] = $project_time_left;
			}
			else
			{
				$project_time_left = '-';
			}
			$row['timeleft'] = $project_time_left;
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
			$row['status'] = $phrase['_delisted'];
			if ($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0)
			{
				$row['insfee'] = ($row['isifpaid'])
					? '<div class="smaller blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>'
					: '<div class="smaller red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>';
			}
			else
			{
				$row['insfee'] = '-';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$project_results_rows[] = $row;
			$row_count++;
		}
		
		$show['no_project_rows_returned'] = false;
	}
	else
	{
		$show['no_project_rows_returned'] = true;
	}
	
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['buying'].'?cmd=management&amp;sub=delisted');
	
	$pprint_array = array('servicetabs','rfpvisible','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'buying_delisted.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### AUCTION PENDING BY ADMIN ###############################################
// additionally auctions that have not paid the entire insertion fee will also be listed below with their payment status
else if ($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'rfp-pending')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	$show['widescreen'] = true;
	
	$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $categorymode = 0, $propersort = false);
	
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	
	$limit = 'ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];

	// #### LISTING PERIOD #########################################
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');
	
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
	$extra = '&amp;period=' . $ilance->GPC['period'];
	
	$servicetabs = print_buying_activity_tabs('rfp-pending', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);

	$numberrows = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0'))" : "AND p.visible = '0'") . "
	", 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);

	$area_title = $phrase['_pending_auctions'];
	$page_title = SITE_NAME . ' - ' . $phrase['_pending_auctions'];
	
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
	$navcrumb["$ilpage[buying]?cmd=management"] = $phrase['_buying_activity'];
	$navcrumb[""] = $phrase['_pending_auctions'];

	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$row_count = 0;
	$condition =$condition2 = '';

	$result = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0'))" : "AND p.visible = '0'") . "
		$limit
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			$row['job_title'] = stripslashes($row['project_title']);
			$row['description'] = short_string(stripslashes($row['description']), 100);
			$row['category'] = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $row['cid']);
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
			$row['state'] = ucfirst($row['project_state']);
			if ($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0)
			{
				$row['insfee'] = ($row['isifpaid'])
					? '<div class="smaller blue"><a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>'
					: '<div class="smaller red"><a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>';
					
				$row['status'] = ($row['isifpaid'])
					? $phrase['_pending_payment']
					: $phrase['_review_in_progress'];
				
				$row['paystatus'] = ($row['isifpaid'])
					? $phrase['_paid']
					: '<a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">' . $phrase['_pay_now'] . '</a>';
			}
			else
			{
				$row['insfee'] = $row['paystatus'] = '-';
				$row['status'] = $phrase['_review_in_progress'];
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$project_results_rows[] = $row;
			$row_count++;
		}
		
		$show['no_project_rows_returned'] = false;
	}
	else
	{
		$show['no_project_rows_returned'] = true;
	}
	
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['buying'].'?cmd=management&amp;sub=rfp-pending');
	
	$pprint_array = array('servicetabs','rfpvisible','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'buying_rfp_pending.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
	
// #### RETRACT BIDS ###########################################################
else if ($ilance->GPC['cmd'] == '_do-bid-action' AND !empty($ilance->GPC['bidcmd']) AND isset($ilance->GPC['bidid']) AND empty($ilance->GPC['rfpcmd']))
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'mycp'
	);
	
	// #### empty inline cookie ############################################
	set_cookie('inlineservice', '', false);
	
	$bidid = isset($ilance->GPC['bidid']) ? $ilance->GPC['bidid'] : 0;
	if (count($bidid) > 0)
	{
		$ilance->bid = construct_object('api.bid');
		$ilance->bid_retract = construct_object('api.bid_retract');
		
		for ($i = 0; $i < count($bidid); $i++)
		{
			$sql = $ilance->db->query("
				SELECT project_id, bidstatus
				FROM " . DB_PREFIX . "project_bids
				WHERE bid_id = '" . intval($bidid[$i]) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);				
				$res['isawarded'] = ($res['bidstatus'] == 'awarded') ? true : false;
				$res['reason'] = (!empty($ilance->GPC['bidretractreason'])) ? ilance_htmlentities($ilance->GPC['bidretractreason']) : $phrase['_no_reason_provided'];
				
				// #### process bid retraction if applicable
				$ilance->bid_retract->construct_bid_retraction($_SESSION['ilancedata']['user']['userid'], intval($bidid[$i]), $res['project_id'], $res['reason'], $res['isawarded'], false);
			}
		}
	}
}

// #### AUCTION ACTIONS ########################################################
else if ($ilance->GPC['cmd'] == '_do-rfp-action' AND isset($ilance->GPC['rfpcmd']) AND empty($ilance->GPC['bidcmd']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	// #### ARCHIVE ################################################
	if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'archive' AND !empty($ilance->GPC['rfp']) AND is_array($ilance->GPC['rfp']))
	{
		if (count($ilance->GPC['rfp']) > 0)
		{
			// empty inline cookie
			set_cookie('inlineservice', '', false);
			
			for ($i = 0; $i < count($ilance->GPC['rfp']); $i++)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET status = 'archived'
					WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND (status = 'closed' OR status = 'expired' OR status = 'finished')
						AND visible = '1'
				", 0, null, __FILE__, __LINE__);
				
				$sqlupd = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND status = 'archived'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlupd) == 0)
				{
					$area_title = $phrase['_archive_rfp_error_rfp_in_progress'];
					$page_title = SITE_NAME . ' - ' . $phrase['_archive_rfp_error_rfp_in_progress'];
					
					print_notice($area_title, $phrase['_one_of_the_requested_rfps_you_are_trying_to_archive'], $ilpage['buying'], $phrase['_return_to_the_previous_menu']);
				}
			}
			
			$area_title = $phrase['_rfps_archive_display'];
			$page_title = SITE_NAME . ' - ' . $phrase['_rfps_archive_display'];
			
			print_notice($phrase['_requested_rfps_have_been_archived'], $phrase['_you_will_now_be_able_to_review_these_rfps_from_your_archived_rfps_menu'], $ilpage['buying'], $phrase['_return_to_the_previous_menu']);
		}
		else
		{
			$area_title = $phrase['_invalid_items_selected'];
			$page_title = SITE_NAME . ' - ' . $phrase['_invalid_items_selected'];
			
			print_notice($area_title, $phrase['_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps'] . '</p><p>' . $phrase['_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive'] . '</p>', $ilpage['buying'], $phrase['_return_to_the_previous_menu']);
		}
	}
	
	// #### CANCEL #################################################
	else if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'cancel' AND !empty($ilance->GPC['rfp']))
	{
		if ($ilconfig['globalfilters_enablerfpcancellation'])
		{
			if (count($ilance->GPC['rfp']) > 0)
			{
				// empty inline cookie
				set_cookie('inlineservice', '', false);
			
				for ($i = 0; $i < count($ilance->GPC['rfp']); $i++)
				{
					$delist_msg = $phrase['_delisted_by'].' '.$_SESSION['ilancedata']['user']['username'].' ['.print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0).']';
					
					$presql = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND (status = 'open' OR status = 'expired')
							AND visible = '1'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($presql) > 0)
					{
						$auctioninfo = $ilance->db->fetch_array($presql);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'delisted'
							WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						", 0, null, __FILE__, __LINE__);
						
						// subtract auction count
						build_category_count($auctioninfo['cid'], 'subtract', "buyer cancel service listing: subtracting increment count category id $auctioninfo[cid]");
					}
					else
					{
						$area_title = $phrase['_cancel_rfp_error_rfp_in_progress'];
						$page_title = SITE_NAME . ' - ' . $phrase['_cancel_rfp_error_rfp_in_progress'];
						
						print_notice($area_title, $phrase['_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps'].'</p><p>'.$phrase['_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive'].'</p>', $ilpage['buying'], $phrase['_return_to_the_previous_menu']);
					}
				}
				
				$area_title = $phrase['_requested_rfps_have_been_cancelled'];
				$page_title = SITE_NAME . ' - ' . $phrase['_auctions_have_been_cancelled'];
				
				print_notice($area_title, $phrase['_you_have_successfully_delisted_cancelled_one_or_more_auctions_from_your_buying_activity_menu_no_more_bids_can_be_placed'] . '</p><p>' . $phrase['_you_will_now_be_able_to_review_these_delisted_rfps_from_your'] . ' <a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted">' . $phrase['_delisted_auctions'] . '</a> ' . $phrase['_menu'] . '</p>', $ilpage['buying'], $phrase['_return_to_the_previous_menu']);
			}
			else
			{
				$area_title = $phrase['_invalid_items_selected'];
				$page_title = SITE_NAME . ' - ' . $phrase['_cancel_rfp_error_rfp_in_progress'];
				
				print_notice($area_title, $phrase['_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps'].'</p><p>'.$phrase['_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive'].'</p>', $ilpage['buying'], $phrase['_return_to_the_previous_menu']);
			}
		}
		else
		{
			$area_title = $phrase['_access_denied_cancel_rfp_feature_currently_disabled'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_cancel_rfp_feature_currently_disabled'];
			print_notice($phrase['_access_to_feature_denied'], $phrase['_were_sorry_this_feature_is_currently_disabled']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
			exit();
		}
	}
	
	// #### RELIST PRODUCT AUCTIONS ################################
	else if ($ilance->GPC['rfpcmd'] == 'relist')
	{
		$ilance->auction = construct_object('api.auction');
		$ilance->auction_expiry = construct_object('api.auction_expiry');
		
		foreach ($ilance->GPC['rfp'] AS $key => $value)
		{
			$success = $ilance->db->query("UPDATE " . DB_PREFIX . "projects SET autorelist = '1', autorelist_date = '0000-00-00 00:00:00' WHERE project_id = '" . intval($value) . "' AND bids = '0' AND status='expired' AND user_id='".$_SESSION['ilancedata']['user']['userid']."'", 0, null, __FILE__, __LINE__);
			if ($success)
			{
				$ilance->auction_expiry->process_auction_relister($value, $dontsendemail = true);
			}
		}
		
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management&sub=expired');
		exit();
	}
	
	// #### TRANSFER AUCTIONS TO ANOTHER USER ######################
	else if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'transfer' AND !empty($ilance->GPC['rfp']))
	{
		$show['submit'] = true;
		
		$area_title = $phrase['_transfer_of_project_ownership'];
		$page_title = SITE_NAME . ' - ' . $phrase['_transfer_of_project_ownership'];
		
		$navcrumb[""] = $phrase['_transfer_of_project_ownership'];
		
		$rfp_list = $transfer_hidden_inputs = '';
		if (count($ilance->GPC['rfp']) > 0)
		{
			// empty inline cookie
			set_cookie('inlineservice', '', false);
			
			for ($i = 0; $i < count($ilance->GPC['rfp']); $i++)
			{
				$sql = $ilance->db->query("
					SELECT project_id, project_title, status
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql);
					if ($res['status'] == 'open')
					{
						$transfer_hidden_inputs .= '<input type="hidden" name="rfp[]" value="' . intval($ilance->GPC['rfp'][$i]) . '" />';
						$rfp_list .= '<li><a href="' . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . stripslashes($res['project_title']) . '</a> <span class="gray">(#' . $res['project_id'] . ')</span></li><br />';
					}
					else
					{
						$rfp_list .= '<li><span class="gray">' . $phrase['_ended'] . ': ' . stripslashes($res['project_title']) . ' (#' . $res['project_id'] . ')</span></li><br />';
					}
				}
			}
			
			if (empty($transfer_hidden_inputs))
			{
				$show['submit'] = false;
			}
			
			$ilance->template->fetch('main', 'buying_transfer.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', 'project_results_rows');
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('rfp_list','selectedprojects','transfer_hidden_inputs','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
			exit();
		}
	}
	else
	{
		$area_title = $phrase['_rfp_error_no_rfp_selected'];
		$page_title = SITE_NAME . ' - ' . $phrase['_rfp_error_no_rfp_selected'];
		
		print_notice($phrase['_invalid_rfp_specified'], $phrase['_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal'], $ilpage['search'], $phrase['_search_rfps']);
	}
}

// #### AUCTION TAKEOVER #######################################################
else if ($ilance->GPC['cmd'] == '_do-transfer-type' AND isset($ilance->GPC['transfertype']) AND $ilance->GPC['transfertype'] == 'userid')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	$area_title = $phrase['_transfer_rfp_ownership_menu'];
	$page_title = SITE_NAME . ' - ' . $phrase['_transfer_rfp_ownership_menu'];
	
	$headinclude .= '
<script type="text/javascript">
<!--
function validate_all(f)
{
	haveerrors = 0;
	(f.username.value.length < 1) ? showImage(\'usernameerror\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif\', true) : showImage(\'usernameerror\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif\', false);
	return (!haveerrors);
}
//-->
</script>';
	$rfpcount = 0;
	$transfer_hidden_inputs = '';
	if (!empty($ilance->GPC['rfp']) AND is_array($ilance->GPC['rfp']))
	{
		foreach ($ilance->GPC['rfp'] AS $value)
		{
			$rfpcount++;
			$transfer_hidden_inputs .= '<input type="hidden" name="rfp[]" value="' . intval($value) . '" />';
		}
	}
	
	$selectedprojects = $rfpcount;
	
	$pprint_array = array('selectedprojects','transfer_pulldown','transfer_hidden_inputs','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'buying_transfer_userid.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### AUCTION TAKEOVER VIA EMAIL #############################################
else if ($ilance->GPC['cmd'] == '_do-transfer-type' AND isset($ilance->GPC['transfertype']) AND $ilance->GPC['transfertype'] == 'email')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	$area_title = $phrase['_transfer_rfp_ownership_menu'];
	$page_title = SITE_NAME . ' - ' . $phrase['_transfer_rfp_ownership_menu'];
	
	$rfpcount = 0;
	foreach ($ilance->GPC['rfp'] as $value)
	{
		$rfpcount++;
		$transfer_hidden_inputs .= '<input type="hidden" name="rfp[]" value="'.$value.'" />';
	}
	
	$selectedprojects = $rfpcount;
	
	$ilance->template->fetch('main', 'buying_transfer_email.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', array('selectedprojects','transfer_pulldown','transfer_hidden_inputs','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
	exit();
}


// #### DO PROJECT TAKEOVER USERID TYPE ########################################
else if ($ilance->GPC['cmd'] == '_do-transfer-userid' AND isset($ilance->GPC['username']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	$rfpcount = 0;

	$sqluser = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "users
		WHERE username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sqluser) > 0)
	{
		$ilance->auction = construct_object('api.auction');
		$ilance->email = construct_dm_object('email', $ilance);
		
		$resuser = $ilance->db->fetch_array($sqluser);
		
		foreach ($ilance->GPC['rfp'] AS $value)
		{
			$rfpcount++;
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
					AND (status = 'draft' OR status = 'open')
					AND project_state = 'service'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql);
				$newmd5hash = md5(rand(0, 9999));
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET transfertype = 'userid',
					transfer_to_userid = '" . $resuser['user_id'] . "',
					transfer_from_userid = '" . $_SESSION['ilancedata']['user']['userid'] . "',
					transfer_to_email = '" . $resuser['email'] . "',
					transfer_status = 'pending',
					transfer_code = '" . $ilance->db->escape_string($newmd5hash) . "'
					WHERE project_id = '" . intval($value) . "'
						AND (status = 'draft' OR status = 'open')
						AND project_state = 'service'
				", 0, null, __FILE__, __LINE__);
				
				$budget = $ilance->auction->construct_budget_overview($res['cid'], $res['filtered_budgetid']);
				
				$ilance->email->mail = $resuser['email'];
				$ilance->email->slng = fetch_user_slng($resuser['user_id']);
	
				$ilance->email->get('rfp_takeover_via_userid');		
				$ilance->email->set(array(
					'{{transfer_to_username}}' => ucfirst(stripslashes($resuser['username'])),
					'{{transfer_from_username}}' => $_SESSION['ilancedata']['user']['username'],
					'{{rfp_title}}' => stripslashes($res['project_title']),
					'{{status}}' => print_auction_status($res['status']),
					'{{bids}}' => $res['bids'],
					'{{closing_date}}' => print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
					'{{description}}' => short_string(stripslashes($res['description']),150),
					'{{project_id}}' => $value,
					'{{transfer_hash}}' => $newmd5hash,
					'{{transfer_from_email}}' => $_SESSION['ilancedata']['user']['email'],
					'{{budget}}' => $budget,
				));
				
				$ilance->email->send();
				
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				
				$ilance->email->get('rfp_takeover_via_userid_admin');		
				$ilance->email->set(array(
					'{{transfer_to_username}}' => ucfirst(stripslashes($resuser['username'])),
					'{{transfer_from_username}}' => $_SESSION['ilancedata']['user']['username'],
					'{{rfp_title}}' => stripslashes($res['project_title']),
					'{{status}}' => print_auction_status($res['status']),
					'{{bids}}' => $res['bids'],
					'{{closing_date}}' => print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
					'{{description}}' => short_string(stripslashes($res['description']),150),
					'{{project_id}}' => $value,
					'{{transfer_hash}}' => $newmd5hash,
					'{{transfer_from_email}}' => $_SESSION['ilancedata']['user']['email'],
					'{{budget}}' => $budget,
				));
				
				$ilance->email->send();
			}
			else
			{
				$area_title = $phrase['_invalid_rfp_state_rfp_can_not_be_transferred'];
				$page_title = SITE_NAME . ' - ' . $phrase['_invalid_rfp_state_rfp_can_not_be_transferred'];
				
				print_notice($area_title, $phrase['_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps'].'</p><p>'.$phrase['_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive'].'</p>', $ilpage['buying'], $phrase['_return_to_the_previous_menu']);
			}
		}
		
		$selectedprojects = $rfpcount;
		
		print_notice($phrase['_project_pending_ownership'], $phrase['_please_allow_up_to_five_business_days_for_project_takeover_status']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['buying'].'?cmd=management', $phrase['_buying_activity']);
		exit();
	}
	else
	{
		$area_title = $phrase['_invalid_username_for_rfp_transfer'];
		$page_title = SITE_NAME . ' - ' . $phrase['_invalid_username_for_rfp_transfer'];
		
		print_notice($phrase['_invalid_rfp_transfer_to_new_owner'], $phrase['_were_sorry_there_was_a_problem_with_the_rfp_transfer_to_the_new_owner']."<br /><br /><li>".$phrase['_the_buyer_no_longer_exists_on_our_marketplace']."</li><li>".$phrase['_the_buyer_has_been_suspended_from_using_the_marketplace_resources']."</li><li>".$phrase['_the_buyer_does_not_have_proper_permissions_to_accept_rfp_takeover_requests_from_others']."</li><br /><br />".$phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
		exit();
	}
}
else if ($ilance->GPC['cmd'] == '_do-transfer-email' AND isset($ilance->GPC['email']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	$rfpcount = 0;
	foreach ($ilance->GPC['rfp'] AS $value)
	{
		$rfpcount++;
	}
	
	$selectedprojects = $rfpcount;
	print_notice($phrase['_project_pending_ownership'], $phrase['_please_allow_up_to_five_business_days_for_project_takeover_status']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['buying'].'?cmd=management', $phrase['_buying_activity']);
	exit();
}

// #### AWARD BID PROPOSAL PREVIEW #############################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_do-rfp-action' AND isset($uncrypted['bidcmd']) AND $uncrypted['bidcmd'] == 'awardbid' AND empty($ilance->GPC['rfpcmd']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	$area_title = $phrase['_awarding_bid_proposal'];
	$page_title = SITE_NAME . ' - ' . $phrase['_awarding_bid_proposal'];
	
	if (!isset($uncrypted['bid_id']) OR $uncrypted['bid_id'] == 0)
	{
		$area_title = $phrase['_bid_award_error_no_bidid_selected'];
		$page_title = SITE_NAME . ' - ' . $phrase['_bid_award_error_no_bidid_selected'];
		
		print_notice($phrase['_invalid_bidid_selected_no_radio_button_selected'], $phrase['_your_requested_action_cannot_be_completed']."<br /><br />".$phrase['_in_order_to_successfully_award']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
		exit();
	}
	
	// #### require backend ################################################
	$ilance->feedback = construct_object('api.feedback');
	$ilance->bbcode = construct_object('api.bbcode');
	$ilance->auction = construct_object('api.auction');
	
	// #### require shipping backend #######################################
	require_once(DIR_CORE . 'functions_shipping.php');
	
	$bid_id = intval($uncrypted['bid_id']);
	$show['disableaward'] = 0;
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "project_bids
		WHERE bid_id = '" . intval($bid_id) . "'
			AND bidstate != 'wait_approval'
			AND bidstatus = 'placed'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$result = $ilance->db->fetch_array($sql, DB_ASSOC);
		
		$prosql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . $result['project_id'] . "'
				AND (status = 'open' OR status = 'expired')
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($prosql) > 0)
		{
			$projectsinfo = $ilance->db->fetch_array($prosql, DB_ASSOC);
			
			$winner_user_id = $result['user_id'];
			$winner_bid_message = stripslashes($result['proposal']);
			$winner_bid_message = $ilance->bbcode->strip_bb_tags($winner_bid_message);
			$winner_bid_price = $ilance->currency->format($result['bidamount'], $projectsinfo['currencyid']);
			$winner_bid_estimate_days = $result['estimate_days'];
			
			$show['escrow'] = ($projectsinfo['filter_escrow'] == '1' AND $result['winnermarkedaspaidmethod'] == 'escrow') ? 1 : 0;
			
			// total award amount logic
			$awardamount = ($result['bidamounttype'] == 'entire')
				? $winner_bid_price
				: $ilance->currency->format(($result['bidamount'] * $winner_bid_estimate_days), $projectsinfo['currencyid']);
			
			$winner_bid_date_added = print_date($result['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$winner_project_id = $result['project_id'];
			
			$paymethod = !empty($result['winnermarkedaspaidmethod'])
				? print_fixed_payment_method($result['winnermarkedaspaidmethod'], false)
				: '<span class="red">' . $phrase['_provider_has_not_selected_a_payment_method_yet'] . '</span>';
				
			$show['disableaward'] = empty($result['winnermarkedaspaidmethod']) ? 1 : 0;
		}
		else
		{
			$area_title = $phrase['_bid_award_error_bidid_has_already_been_awarded'];
			$page_title = SITE_NAME . ' - ' . $phrase['_bid_award_error_bidid_has_already_been_awarded'];
			
			print_notice($phrase['_invalid_bid_action'], $phrase['_your_requested_bid_control_action'] . '<br /><br />' . $phrase['_if_this_rfp_is_in_the_waiting_approval_phase'] . '<br /><br />' . $phrase['_please_contact_customer_support'], $ilpage['buying'] . '?cmd=management', $phrase['_buying_activity']);
			exit();
		}
	}
	else
	{
		$area_title = $phrase['_bid_award_error_bidid_has_already_been_awarded'];
		$page_title = SITE_NAME . ' - ' . $phrase['_bid_award_error_bidid_has_already_been_awarded'];
		
		print_notice($phrase['_invalid_bid_action'], $phrase['_your_requested_bid_control_action'] . '<br /><br />' . $phrase['_if_this_rfp_is_in_the_waiting_approval_phase'] . '<br /><br />' . $phrase['_please_contact_customer_support'], $ilpage['buying'] . '?cmd=management', $phrase['_buying_activity']);
		exit();
	}
	
	// #### awarded service provider details ###############################
	$sql_winner = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "users
		WHERE user_id = '".intval($winner_user_id)."'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_winner) > 0)
	{
		$result_winner = $ilance->db->fetch_array($sql_winner, DB_ASSOC);
		$winner_user_email = $result_winner['email'];
		$winner_user_username = stripslashes($result_winner['username']);
		$winner_user_first_name = stripslashes($result_winner['first_name']);
		$winner_user_last_name = stripslashes($result_winner['last_name']);
		$winner_user_address = stripslashes($result_winner['address']);
		$winner_user_address2 = stripslashes($result_winner['address2']);
		$winner_user_city = stripslashes($result_winner['city']);
		$winner_user_state = stripslashes($result_winner['state']);
		$winner_user_zip_code = mb_strtoupper($result_winner['zip_code']);
		$winner_user_phone = $result_winner['phone'];
		$winner_user_country = print_user_country($winner_user_id, $_SESSION['ilancedata']['user']['slng']);
	}
	
	// #### project details ################################################
	$sql_project = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($winner_project_id) . "'
			AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_project) > 0)
	{
		$result_project = $ilance->db->fetch_array($sql_project, DB_ASSOC);
		$project_title = stripslashes($result_project['project_title']);
		$project_description = stripslashes($result_project['description']);
		$project_date_added = print_date($result_project['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$project_date_end = print_date($result_project['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$project_bids = $result_project['bids'];
		$project_budget = $ilance->auction->construct_budget_overview($result_project['cid'], $result_project['filtered_budgetid']);
		$project_user_id = $result_project['user_id'];
	}
	
	// #### service buyer details ##########################################
	$sql_project_owner = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "users
		WHERE user_id = '".intval($project_user_id)."'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_project_owner) > 0)
	{
		$result_owner = $ilance->db->fetch_array($sql_project_owner, DB_ASSOC);
		$project_user_email = $result_owner['email'];
		$project_user_username = stripslashes($result_owner['username']);
		$project_user_first_name = stripslashes($result_owner['first_name']);
		$project_user_last_name = stripslashes($result_owner['last_name']);
		$project_user_address = stripslashes($result_owner['address']);
		$project_user_address2 = stripslashes($result_owner['address2']);
		$project_user_city = stripslashes($result_owner['city']);
		$project_user_state = stripslashes($result_owner['state']);
		$project_user_zip_code = mb_strtoupper($result_owner['zip_code']);
		$project_user_phone = $result_owner['phone'];
		$project_user_country = print_user_country($project_user_id, $_SESSION['ilancedata']['user']['slng']);
	}
	
	// #### bid amount type ################################################
	switch ($result['bidamounttype'])
	{
		case 'entire':
		{
			$bidamounttype = $phrase['_for_entire_project'];
			$measure = $phrase['_days'];
			break;
		}                        
		case 'hourly':
		{
			$bidamounttype = $phrase['_per_hour'];
			$measure = $phrase['_hours'];
			break;
		}                        
		case 'daily':
		{
			$bidamounttype = $phrase['_per_day'];
			$measure = $phrase['_days'];
			break;
		}                        
		case 'weekly':
		{
			$bidamounttype = $phrase['_weekly'];
			$measure = $phrase['_weeks'];
			break;
		}                        
		case 'monthly':
		{
			$bidamounttype = $phrase['_monthly'];
			$measure = $phrase['_months'];
			break;
		}                        
		case 'lot':
		{
			$bidamounttype = $phrase['_per_lot'];
			$measure = $phrase['_lots'];
			break;
		}                        
		case 'weight':
		{
			$bidamounttype = $phrase['_per_weight'] . ' ' . stripslashes($row['bidcustom']);
			$measure = $phrase['_weight'];
			break;
		}                        
		case 'item':
		{
			$bidamounttype = $phrase['_per_item'];
			$measure = $phrase['_items'];
			break;
		}
	}
	
	$area_title = $phrase['_award_bid_preview'];
	$page_title = SITE_NAME . ' - ' . $phrase['_award_bid_preview'];
	
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
	$navcrumb["$ilpage[buying]?cmd=management"] = $phrase['_buying_activity'];
	$navcrumb[""] = $phrase['_award_bid_preview'];
	
	$pprint_array = array('paymethod','measure','awardamount','bidamounttype','stars','project_budget','referrer','winner_bid_message','winner_bid_price','winner_bid_estimate_days','project_bids','bid_id','winner_project_id','winner_user_id','winner_user_username','project_title','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'buying_award_bid_preview.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### AWARD BID PROPOSAL HANDLER #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_award-bid' AND isset($ilance->GPC['bid_id']) AND $ilance->GPC['bid_id'] > 0 AND isset($ilance->GPC['vendor_id']) AND $ilance->GPC['vendor_id'] > 0 AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND (isset($ilance->GPC['awardthisbid']) OR isset($ilance->GPC['cancelaward'])))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	if (isset($ilance->GPC['cancelaward']))
	{
		refresh(HTTP_SERVER . $ilpage['buying']);
		exit();
	}
	
	$ilance->auction = construct_object('api.auction');
	$ilance->auction_award = construct_object('api.auction_award');
	
	$notifybidders = isset($ilance->GPC['notifybidders']) ? intval($ilance->GPC['notifybidders']) : 0;
	
	$success = $ilance->auction_award->award_service_auction(intval($ilance->GPC['project_id']), intval($ilance->GPC['bid_id']), intval($_SESSION['ilancedata']['user']['userid']), intval($ilance->GPC['vendor_id']), $notifybidders, $_SESSION['ilancedata']['user']['slng']);
	if ($success)
	{
		$area_title = $phrase['_rfp_was_awarded_to_a_vendor'];
		$page_title = SITE_NAME . ' - ' . $phrase['_rfp_was_awarded_to_a_vendor'];
		
		$ilance->template->fetch('main', 'buying_award_bid_final.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array('login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
		exit();
	}
}

// #### DECLINE BID PROPOSAL ###################################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_do-rfp-action' AND isset($uncrypted['bidcmd']) AND $uncrypted['bidcmd'] == 'declinebid' AND empty($ilance->GPC['rfpcmd']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	if (isset($uncrypted['bid_id']) AND $uncrypted['bid_id'] > 0)
	{
		$bidid = intval($uncrypted['bid_id']);
		
		$ilance->auction = construct_object('api.auction');
		$ilance->auction_award = construct_object('api.auction_award');
		
		$success = $ilance->auction_award->service_auction_bid_decline($bidid, $_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['slng']);
		
		if ($success)
		{
			$area_title = $phrase['_selected_bid_was_declined'];
			$page_title = SITE_NAME . ' - ' . $phrase['_selected_bid_was_declined'];
			
			print_notice($phrase['_vendors_bid_was_declined'], $phrase['_you_have_successfully_declined_this_vendors_bid_from_your_rfp'], $ilpage['buying'], $phrase['_buying_activity']);
			exit();	
		}
		else
		{
			$area_title = $phrase['_decline_bid_error_rfp_in_progress'];
			$page_title = SITE_NAME . ' - ' . $phrase['_decline_bid_error_rfp_in_progress'];
		
			print_notice($phrase['_invalid_bid_action'], $phrase['_your_requested_bid_control_action']."<br /><br />".$phrase['_if_this_rfp_is_in_the_waiting_approval_phase']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['buying'], $phrase['_buying_activity']);
			exit();
		}
	}
	else
	{
		$area_title = $phrase['_decline_bid_error_no_bidid_selected'];
		$page_title = SITE_NAME . ' - ' . $phrase['_decline_bid_error_no_bidid_selected'];
		
		print_notice($phrase['_invalid_bidid_selected_no_radio_button_selected'], $phrase['_your_requested_action_cannot_be_completed']."<br /><br />".$phrase['_in_order_to_successfully_award']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
		exit();
	}
}

// #### CANCEL AWARD PROPOSAL ##################################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_do-rfp-action' AND isset($uncrypted['bidcmd']) AND $uncrypted['bidcmd'] == 'unawardbid' AND empty($ilance->GPC['rfpcmd']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	// let who's online know where we are
	$area_title = $phrase['_unawarding_service_provider'];
	$page_title = SITE_NAME . ' - ' . $phrase['_unawarding_service_provider'];
	
	if (isset($uncrypted['bid_id']) AND $uncrypted['bid_id'] > 0)
	{
		$ilance->auction = construct_object('api.auction');
		$ilance->auction_award = construct_object('api.auction_award');
		
		$success = $ilance->auction_award->unaward_service_auction(intval($uncrypted['bid_id']));
		
		if ($success)
		{
			$area_title = $phrase['_bid_was_un_awarded'];
			$page_title = SITE_NAME . ' - ' . $phrase['_bid_was_un_awarded'];
			
			print_notice($phrase['_awarded_vendor_was_un_awarded'], $phrase['_you_have_successfully_un_awarded_this_vendor_from_your_rfp'], $ilpage['buying'] . '?cmd=management', $phrase['_buying_activity']);
			exit();
		}
		else
		{
			$area_title = $phrase['_cancel_award_error_vendor_is_not_awarded_yet'];
			$page_title = SITE_NAME . ' - ' . $phrase['_cancel_award_error_vendor_is_not_awarded_yet'];
			
			print_notice($phrase['_cannot_unaward_bid'], $phrase['_were_sorry_you_cannot_unaward'] . '<br /><br />' . $phrase['_if_you_require_additional_information'], $ilpage['buying'] . '?cmd=management', $phrase['_buying_activity']);
			exit();
		}
	}
	else
	{
		$area_title = $phrase['_cancel_award_error_no_bidid_selected'];
		$page_title = SITE_NAME . ' - ' . $phrase['_cancel_award_error_no_bidid_selected'];
		
		print_notice($phrase['_invalid_bidid_selected_no_radio_button_selected'], $phrase['_your_requested_action_cannot_be_completed'] . '<br /><br />' . $phrase['_in_order_to_successfully_award'] . '<br /><br />' . $phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
		exit();
	}
}

// #### SET AUCTION AS FINISHED ################################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_do-auction-action' AND isset($uncrypted['sub']) AND $uncrypted['sub'] == 'finished' AND $uncrypted['project_id'] > 0 AND $uncrypted['buyer_id'] > 0 AND $uncrypted['seller_id'] > 0)
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects
		SET status = 'finished'
		WHERE project_id = '" . $uncrypted['project_id'] . "'
			AND user_id = '" . $uncrypted['buyer_id'] . "'
			AND status = 'approval_accepted'
	", 0, null, __FILE__, __LINE__);
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . $uncrypted['project_id'] . "'
			AND user_id = '" . $uncrypted['buyer_id'] . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$ilance->auction = construct_object('api.auction');
		
		while ($result_p = $ilance->db->fetch_array($sql))
		{
			$project_budget = $ilance->auction->construct_budget_overview($result_p['cid'], $result_p['filtered_budgetid']);
			$project_title = stripslashes($result_p['project_title']);
			$project_description = $result_p['description'];
			$project_date_added = $result_p['date_added'];
			$project_date_end = $result_p['date_end'];
			$project_bids = $result_p['bids'];
			$project_user_id = $result_p['user_id'];
		}
	}
	
	$sql2 = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $uncrypted['buyer_id'] . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql2) > 0)
	{
		$result_email = $ilance->db->fetch_array($sql2);
		
		$project_user_currency = $ilance->currency->fetch_user_currency($result_email['user_id']);
		$project_user_email = $result_email['email'];
		$project_user_username = stripslashes($result_email['username']);
		$project_user_first_name = ucfirst($result_email['first_name']);
		$project_user_last_name = ucfirst($result_email['last_name']);
		$project_user_address = stripslashes($result_email['address']);
		$project_user_address2 = stripslashes($result_email['address2']);
		$project_user_city = ucfirst($result_email['city']);
		$project_user_state = ucfirst($result_email['state']);
		$project_user_zip_code = mb_strtoupper($result_email['zip_code']);
		$project_user_phone = $result_email['phone'];
		$project_user_country = print_user_country($uncrypted['buyer_id'], $_SESSION['ilancedata']['user']['slng']);
	}
	
	$provider_user_email = fetch_user('email', $uncrypted['seller_id']);
	$provider_user_username = fetch_user('username', $uncrypted['seller_id']);
	
	$ilance->email = construct_dm_object('email', $ilance);
	
	$existing = array(
		'{{provider_user_username}}' => $provider_user_username,
		'{{provider_user_email}}' => $provider_user_email,
		'{{project_user_username}}' => $project_user_username,
		'{{project_user_email}}' => $project_user_email,
		'{{project_title}}' => $project_title,
		'{{project_description}}' => $project_description,
		'{{project_date_added}}' => $project_date_added,
		'{{project_date_end}}' => $project_date_end,
		'{{project_bids}}' => $project_bids,
		'{{project_budget}}' => $project_budget,
	);
	
	$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
	
	$ilance->email->get('project_marked_finished');		
	$ilance->email->set($existing);
	
	$ilance->email->send();
	
	$ilance->email->mail = $provider_user_email;
	$ilance->email->slng = fetch_user_slng($uncrypted['seller_id']);
	
	$ilance->email->get('project_finished_notify');		
	$ilance->email->set($existing);
	
	$ilance->email->send();
	
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	
	$ilance->email->get('project_finished_notify_admin');		
	$ilance->email->set($existing);
	
	$ilance->email->send();
	
	print_notice($phrase['_service_auction_complete'], $phrase['_you_have_just_set_this_service_auction_as_being_finished_and_your_requirements_have_been_delivered'] . '<br /><br />' . $phrase['_please_contact_customer_support'], $ilpage['buying'] . '?cmd=management', $phrase['_buying_activity']);
	exit();
}

// #### BUYING ACTIVITY MENU ###################################################
else
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp',
		'buying'
	);
	
	$show['widescreen'] = true;
	
	$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, false);
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_tabs = construct_object('api.bid_tabs');
	$ilance->bid_fields = construct_object('api.bid_fields');
	$ilance->auction = construct_object('api.auction');
	$ilance->feedback = construct_object('api.feedback');
	
	// #### LISTING PERIOD #################################################
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');
	require_once(DIR_CORE . 'functions_pmb.php');
	require_once(DIR_CORE . 'functions_shipping.php');
		
	// #### service auction buying activity ########################
	if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
	{
		$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
		
		// #### default listing period #################################
		$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
		$extra = '&amp;period=' . $ilance->GPC['period'];
		$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
		
		// #### ordering by fields defaults ############################
		$orderbyfields = array('project_title', 'date_added', 'date_end', 'bids', 'insertionfee');
		$orderby = '&amp;orderby=bids';
		$orderbysql = 'bids';
		if (isset($ilance->GPC['orderby']) AND in_array($ilance->GPC['orderby'], $orderbyfields))
		{
			$orderby = '&amp;orderby=' . $ilance->GPC['orderby'];
			$orderbysql = $ilance->GPC['orderby'];
		}
		
		$ilance->GPC['orderby'] = $orderbysql;
		
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
		
		$extra .= (!empty($ilance->GPC['bidsub'])) ? '&amp;bidsub=' . $ilance->GPC['bidsub'] : '';
		$extra .= (!empty($ilance->GPC['sub'])) ? '&amp;sub=' . $ilance->GPC['sub'] : '';
		
		// #### header tabs ############################################
		if (!empty($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'ended')
		{
			$servicetabs = print_buying_activity_tabs('ended', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
			$projectstatussql =  "AND p.status = 'expired'";
		}
		else if (!empty($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'awarded')
		{
			// #### define top header nav ##################################
			$topnavlink = array(
				'mycp',
				'buying_awarded'
			);
			
			$servicetabs = print_buying_activity_tabs('awarded', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
			$projectstatussql = "AND (p.status = 'wait_approval' OR p.status = 'approval_accepted' OR p.status = 'finished')";
		}
		else
		{
			$servicetabs = print_buying_activity_tabs('active', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
			$projectstatussql = "AND p.status = 'open'";
		}
		
		$limit = ' ORDER BY ' . $orderbysql . ' ' . $displayordersql . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
		
		// used within templates
		$php_self = $ilpage['buying'] . '?cmd=management' . $displayorder . $extra;
		
		// used within prev / next page nav
		$scriptpage = $ilpage['buying'] . '?cmd=management' . $currentdisplayorder . $orderby . $extra;
		
		$numberrows = $ilance->db->query("
			SELECT p.*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
			FROM " . DB_PREFIX . "projects AS p
			WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				$periodsql
				AND p.project_state = 'service'
				$projectstatussql
				" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "AND p.visible = '1'") . "
		", 0, null, __FILE__, __LINE__);
		
		$number = $ilance->db->num_rows($numberrows);
		
		$area_title = $phrase['_buying_activity'];
		$page_title = SITE_NAME . ' - ' . $phrase['_buying_activity'];
		
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
		$navcrumb[""] = $phrase['_buying_activity'];
		
		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
		$row_count = 0;
		
		$result = $ilance->db->query("
			SELECT p.*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
			FROM " . DB_PREFIX . "projects AS p
			WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				$periodsql
				AND p.project_state = 'service'
				$projectstatussql
				" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "AND p.visible = '1'") . "
			$limit
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
		{
			while ($row = $ilance->db->fetch_array($result))
			{
				if ($row['bids'] == 0)
				{
					//$row['bids'] = '-';
				}
				
				$row['paymethod'] = '-';
				
				// live bid applet
				$row['livebid'] = '';
				$show['livebid'] = 0;
				if ($row['project_details'] == 'realtime')
				{
					$show['livebid'] = 1;
					$row['livebid'] = '
<div id="applet' . $row['project_id'] . '"></div>
<script type="text/javascript">
var fo = new FlashObject("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/' . DIR_SWF_NAME . '/livebid.swf", "applet' . $row['project_id'] . '", "730", "530", "8,0,0,0", "#ffffff");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("flashvars", "languageConfig=' . DIR_FUNCT_NAME . '/' . DIR_XML_NAME . '/livebid_' . $_SESSION['ilancedata']['user']['slng'] . '.xml&prId=' . $row['project_id'] . '&sId=' . session_id() . '&rand=' . rand(100000, 999999) . '");
fo.addParam("menu", "false");
fo.write("applet' . $row['project_id'] . '");
</script>';
				}
				
				// check for attachments
				$sqlattachments2 = $ilance->db->query("
					SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
					FROM " . DB_PREFIX . "attachment
					WHERE attachtype = 'project'
						AND project_id = '" . $row['project_id'] . "'
						AND user_id = '" . $row['user_id'] . "'
						AND visible = '1'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlattachments2) > 0)
				{
					while ($res = $ilance->db->fetch_array($sqlattachments2, DB_ASSOC))
					{
						if (isset($row['attach']))
						{
							$row['attach'] .= '<span class="smaller blue"><a href="' . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a></span> ';
						}
						else
						{
							$row['attach'] = '<span class="smaller blue"><a href="' . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a></span> ';
						}
					}
				}
				else
				{
					$row['attach'] = '<span class="smaller" align="center">-</span>';
				}
				
				// auction status
				if ($row['status'] == 'draft')
				{
					$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
					$row['statusmsg'] = $phrase['_draft_not_public'];
					$row['provider'] = $row['pmb'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
				}
				else if ($row['status'] == 'expired')
				{
					$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
					$row['statusmsg'] = '<span class="gray">' . $phrase['_ended'] . '</span>';
					$row['provider'] = $row['pmb'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
				}
				else if ($row['status'] == 'open')
				{
					if ($row['date_starts'] < DATETIME24H)
					{
						$row['statusmsg'] = $phrase['_open'];
					}
					else
					{
						$dif = $row['starttime'];
						$ndays = floor($dif / 86400);
						$dif -= $ndays * 86400;
						$nhours = floor($dif / 3600);
						$dif -= $nhours * 3600;
						$nminutes = floor($dif / 60);
						$dif -= $nminutes * 60;
						$nseconds = $dif;
						$sign = '+';
						if ($row['starttime'] < 0)
						{
							$row['starttime'] = - $row['starttime'];
							$sign = '-';
						}
						if ($sign == '-')
						{
							$project_time_left = '-';
						}
						else
						{
							if ($ndays != '0')
							{
								$project_time_left = $ndays . $phrase['_d_shortform'] . ', ';
								$project_time_left .= $nhours . $phrase['_h_shortform'] . '+';
							}
							else if ($nhours != '0')
							{
								$project_time_left = $nhours . $phrase['_h_shortform'] . ', ';
								$project_time_left .= $nminutes . $phrase['_m_shortform'] . '+';
							}
							else
							{
								$project_time_left = $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
							}
						}
						
						$row['timeleft'] = $project_time_left;
						$row['statusmsg'] = $phrase['_starts'] . ': ' . $row['timeleft'];
					}
					
					$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
					$row['provider'] = $row['pmb'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
				}
				else if ($row['status'] == 'closed')
				{
					$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
					$row['statusmsg'] = $phrase['_bidding_closed'];
					$row['provider'] = $row['pmb'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
				}
				else if ($row['status'] == 'delisted')
				{
					$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
					$row['statusmsg'] = $phrase['_delisted'];
					$row['provider'] = $row['pmb'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
				}
				else if ($row['status'] == 'wait_approval')
				{
					// fetch days since the provider has been awarded giving more direction to the buyer
					$date1split = explode(' ', $row['close_date']);
					$date2split = explode('-', $date1split[0]);
					$days = $ilance->datetime->fetch_days_between($date2split[1], $date2split[2], $date2split[0], gmdate('m'), gmdate('d'), gmdate('Y'));
					if ($days == 0)
					{
						$days = 1;
					}
					
					$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
					$row['statusmsg'] = $phrase['_pending_approval'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>' . $phrase['_pending_approval'] . ' ' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . '</strong></div><div>' . $phrase['_pending_approval_allows_the_awarded_service_provider_to_accept_or_reject_the_service_auction'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a><div class="smaller gray">(' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . ')</div>';
					
					// service provider information
					$sql_provider = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "project_bids
						WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND project_id = '" . $row['project_id'] . "'
							AND bidstatus = 'placed'
							AND bidstate = 'wait_approval'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$res_provider = $ilance->db->fetch_array($sql_provider);
					
					$sql_biddername = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . $res_provider['user_id'] . "'
					", 0, null, __FILE__, __LINE__);
					$res_biddername = $ilance->db->fetch_array($sql_biddername);
					
					$row['provider_id'] = $res_biddername['user_id'];
					$row['provider'] = print_username($res_biddername['user_id']);
					$row['work'] = $row['invoice'] = $row['feedback'] = '-';
					
					$crypted = array(
						'project_id' => $row['project_id'],
						'from_id' => $_SESSION['ilancedata']['user']['userid'],
						'to_id' => $res_biddername['user_id']
					);
					
					$row['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res_biddername['user_id'], $row['project_id']);
					unset($crypted);
				}
				else if ($row['status'] == 'approval_accepted')
				{
					$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
					$row['statusmsg'] = $phrase['_approval_accepted'];
					
					$sql_provider = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "project_bids
						WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND project_id = '" . $row['project_id'] . "'
							AND bidstatus = 'awarded'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_provider) > 0)
					{
						$res_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
						
						$sql_biddername = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . $res_provider['user_id'] . "'
						", 0, null, __FILE__, __LINE__);
						$res_biddername = $ilance->db->fetch_array($sql_biddername);
						
						$row['provider_id'] = $res_biddername['user_id'];
						$row['provider'] = print_username($res_biddername['user_id']);
						
						$crypted = array(
							'project_id' => $row['project_id'],
							'from_id' => $_SESSION['ilancedata']['user']['userid'],
							'to_id' => $res_biddername['user_id']
						);
						
						$row['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res_biddername['user_id'], $row['project_id']);
						unset($crypted);
						
						$crypted = array(
							'project_id' => $row['project_id'],
							'buyer_id' => $_SESSION['ilancedata']['user']['userid'],
							'seller_id' => $row['provider_id']
						);
						
						$row['work'] = $ilance->auction->construct_mediashare_icon($row['provider_id'], $_SESSION['ilancedata']['user']['userid'], $row['project_id'], true);
						
						##############################################################
						## INVOICE DATA FOR THIS PROJECT FROM THIS BUYER TO THE SELLER
						## DOES INVOICE TABLE HAVE 'p2b' TYPE INVOICES?
						## IF SO, SHOW ICON OF NEW PAYMENT WITH DATA FOR BUYER TO PAY
						## PROVIDER VIA BILLING AND PAYMENTS SYSTEM
						
						// next step is to leave feedback if escrow is paid
						$sql_escrowchk = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "projects_escrow
							WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND user_id = '" . $row['provider_id'] . "'
								AND project_id = '" . $row['project_id'] . "'
								AND status = 'pending'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_escrowchk) > 0 AND $ilconfig['escrowsystem_enabled'])
						{
							// wait for escrow payment
							$row['statusmsg'] = '<span class="gray">' . $phrase['_next'] . ': <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow"><strong>' . $phrase['_pay_escrow'] . '</strong></a></span> <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>' . $phrase['_pay_escrow'] . '</strong></div><div>' . $phrase['_in_order_to_ensure_your_service_provider_will_have_funds_available_to_them'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
							$row['feedback'] = $row['invoice'] = '-';
						}
						else
						{
							// does auction have pending provider to buyer invoices that are unpaid?
							$sql_invchk = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "invoices
								WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
									AND p2b_user_id = '" . $row['provider_id'] . "'
									AND projectid = '" . $row['project_id'] . "'
									AND status = 'unpaid'
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql_invchk) > 0)
							{
								$pendinvoices = '';
								while ($res_inv = $ilance->db->fetch_array($sql_invchk, DB_ASSOC))
								{
									$crypted = array('id' => $res_inv['invoiceid']);
									$pendinvoices .= '<span class="blue"><a href="' . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '" title="' . $ilance->language->construct_phrase($phrase['_invoice_x_was_generated_by_x_and_requires_payment'], array($res_inv['invoiceid'], stripslashes($res_biddername['username']))) . '">#' . $res_inv['invoiceid'] . '</a></span>, ';
								}
								
								if (!empty($pendinvoices))
								{
									$pendinvoices = mb_substr($pendinvoices, 0, -2);
								}
								
								$row['statusmsg'] = $phrase['_pay_invoices'];
								$row['invoice'] = '<span class="gray">' . $phrase['_pay'] . ':</span> ' . $pendinvoices;
								$row['feedback'] = '-';
							}
							else
							{
								$row['feedback'] = '-';
								$row['invoice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_gray.gif" border="0" alt="" />';        
								$provider_rated_buyer = $buyer_rated_provider = 0;
								
								if ($ilance->feedback->has_left_feedback($_SESSION['ilancedata']['user']['userid'], $row['provider_id'], $row['project_id'], 'buyer'))
								{
									// service provider already rated buyer
									$provider_rated_buyer = 1;
									$row['feedback'] = '<div align="center"><span title="' . $phrase['_feedback_already_submitted__thank_you'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="' . $phrase['_feedback_already_submitted__thank_you'] . '" /></span></div>';        
								}
								else
								{
									// service provider did not rate this buyer!
									$row['statusmsg'] = '<span class="smaller">' . $phrase['_pending_feedback'] . ' ' . $phrase['_from'] . ' ' . fetch_user('username', $row['provider_id']) . '</span>';
								}
								
								// did this buyer give feedback to the awarded service provider?
								if ($ilance->feedback->has_left_feedback($row['provider_id'], $_SESSION['ilancedata']['user']['userid'], $row['project_id'], 'seller'))
								{
									// buyer already rated awarded service provider
									$buyer_rated_provider = 1;
									$row['feedback'] = '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="' . $phrase['_feedback_already_submitted__thank_you'] . '" /></div>';
								}
								else
								{
									//echo '1';
									// this buyer did not rate seller
									$row['feedback'] = '<div align="center"><span title="' . $phrase['_submit_feedback_for'] . ' ' . fetch_user('username', $row['provider_id']) . '"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}" onmouseover="rollovericon(\'' . md5($row['provider_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row['project_id'] . ':feedback') . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_rate.gif\')" onmouseout="rollovericon(\'' . md5($row['provider_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row['project_id'] . ':feedback') . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="' . $phrase['_submit_feedback_for'] . ' ' . fetch_user('username', $row['provider_id']) . '" name="' . md5($row['provider_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row['project_id'] . ':feedback') . '" /></a></span></div>';
									$row['statusmsg'] = '<span class="gray">' . $phrase['_leave_feedback'] . '</span>';
								}
								
								if ($provider_rated_buyer AND $buyer_rated_provider)
								{
									// buyer and service provider both left feedback and ratings
									$crypted = array(
										'cmd' => '_do-auction-action',
										'sub' => 'finished',
										'project_id' => $row['project_id'],
										'buyer_id' => $_SESSION['ilancedata']['user']['userid'],
										'seller_id' => $row['provider_id']
									);
									
									$row['statusmsg'] = '<span class="gray">' . $phrase['_next'] . ': </span><span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '" onclick="return confirm_js(\'' . $phrase['_please_confirm_setting_this_service_auction_to_finished'] . '\')"><strong>' . $phrase['_set_as_finished'] . '</strong></a></span>';
								}
							}
						}
					}
					else
					{
						$row['pmb'] = $row['work'] = $row['feedback'] = $row['invoice'] = $row['paymethod'] = '-';
					}
				}
				else if ($row['status'] == 'finished')
				{
					$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
					$row['statusmsg'] = $phrase['_finished'];
					
					$sql_provider = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "project_bids
						WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND project_id = '" . $row['project_id'] . "'
							AND bidstatus = 'awarded'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_provider) > 0)
					{
						$res_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
						
						$sql_biddername = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . $res_provider['user_id'] . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_biddername) > 0)
						{
							$res_biddername = $ilance->db->fetch_array($sql_biddername, DB_ASSOC);
							$row['provider_id'] = $res_biddername['user_id'];
							$row['provider'] = print_username($res_biddername['user_id']);
							$row['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res_biddername['user_id'], $row['project_id']);
						}
						
						$sqlms = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "attachment_folder
							WHERE project_id = '" . $row['project_id'] . "'
								AND folder_type = '2'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sqlms) > 0)
						{
							$crypted = array(
								'project_id' => $row['project_id'],
								'buyer_id' => $_SESSION['ilancedata']['user']['userid'],
								'seller_id' => $res_biddername['user_id']
							);
							
							$row['work'] = $ilance->auction->construct_mediashare_icon($res_biddername['user_id'], $_SESSION['ilancedata']['user']['userid'], $row['project_id'], $active = true);
						}
						else
						{
							$row['work'] = '-';
						}
						
						if ($ilconfig['escrowsystem_enabled'] AND $row['filter_escrow'] == '1')
						{
							$do_invoices_exist = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "invoices
								WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
									AND invoicetype = 'escrow'
									AND projectid = '" . $row['project_id'] . "'
									AND status = 'unpaid'
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($do_invoices_exist) > 0)
							{
								$row['invoice'] = '<a href="' . HTTPS_SERVER . $ilpage['escrow'] . '"?cmd=management&amp;sub=rfp-escrow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" /></a>';
							}
							else
							{
								$row['invoice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" />';
							}
							
							$row['feedback'] = '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="' . $phrase['_service_auction_is_finished_and_feedback_has_been_submitted_by_both_parties'] . '" /></div>';
						}
						else
						{
							// escrow system disabled and auction is finished
							$row['feedback'] = '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="' . $phrase['_feedback_submitted__thank_you'] . '" /></div>';
							$row['invoice'] = '-';
						}
					}
					else
					{
						$row['pmb'] = '-';       
					}
				}
				else if ($row['status'] == 'archived')
				{
					$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
					$row['statusmsg'] = $phrase['_archived'];
					
					$sql_provider = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "project_bids
						WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND project_id = '" . $row['project_id'] . "'
							AND bidstatus = 'awarded'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$res_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
					
					$sql_biddername = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . $res_provider['user_id'] . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_biddername) > 0)
					{
						$res_biddername = $ilance->db->fetch_array($sql_biddername, DB_ASSOC);
						$row['provider_id'] = $res_biddername['user_id'];
						$row['provider'] = print_username($res_biddername['user_id']);
					}
					else
					{
						$row['provider'] = '-';
						$row['provider_id'] = 0;
					}
					
					$row['pmb'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
				}
				
				// transfer project ownership in progress?
				if ($row['transfer_status'] == 'pending')
				{
					$transfername = fetch_user('username', $row['transfer_to_userid']);
					$row['transfer'] = $phrase['_transfer_in_progress'] . ': <strong>' . $transfername . '</strong>';
				}
				else
				{
					$row['transfer'] = '';
				}
				
				$row['job_title'] = handle_input_keywords($row['project_title']);
				
				// fetch 3 lowest bids placed
				$sel_lowbid = $ilance->db->query("
					SELECT MIN(bidamount) AS bidamount
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . $row['project_id'] . "'
						AND bidstatus != 'declined'
						AND bidstate != 'retracted'
					ORDER BY bidamount DESC
					LIMIT 3
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sel_lowbid) > 0)
				{
					$row['lowest_bid'] = '';
					while ($res_lowbid = $ilance->db->fetch_array($sel_lowbid, DB_ASSOC))
					{
						if ($res_lowbid['bidamount'] > 0)
						{
							// todo: check for seo urls
							$row['lowest_bid'] .= '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $row['project_id'] . '#bids">' . $ilance->currency->format($res_lowbid['bidamount'], $row['currencyid']) . '</a>, ';
						}
						else
						{
							$row['lowest_bid'] = '-, ';       
						}
					}
					$row['lowest_bid'] = mb_substr($row['lowest_bid'], 0, -2);
				}
				else
				{
					$row['lowest_bid'] = '-';
				}
				
				// is buyer using escrow?
				$row['escrow'] = ($row['filter_escrow'] == '1')
					? '<a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow">' . $phrase['_manage'] . '</a>'
					: '-';
				
				// auction time left
				if ($row['status'] != 'closed')
				{
					$dif = $row['mytime'];
					$ndays = floor($dif / 86400);
					$dif -= $ndays * 86400;
					$nhours = floor($dif / 3600);
					$dif -= $nhours * 3600;
					$nminutes = floor($dif / 60);
					$dif -= $nminutes * 60;
					$nseconds = $dif;
					$sign = '+';
					if ($row['mytime'] < 0)
					{
						$row['mytime'] = - $row['mytime'];
						$sign = '-';
					}
					if ($sign == '-')
					{
						$project_time_left = '-';
					}
					else
					{
						if ($ndays != '0')
						{
							$project_time_left = $ndays . $phrase['_d_shortform'].', ';
							$project_time_left .= $nhours . $phrase['_h_shortform'] . '+';
						}
						else if ($nhours != '0')
						{
							$project_time_left = $nhours . $phrase['_h_shortform'] . ', ';
							$project_time_left .= $nminutes . $phrase['_m_shortform'] . '+';
						}
						else
						{
							$project_time_left = $nminutes . $phrase['_m_shortform'] . ', ';
							$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
						}
					}
					
					$row['timeleft'] = $project_time_left;
				}
				else
				{
					$project_time_left = '-';
				}
				
				if ($row['date_starts'] < DATETIME24H)
				{
					$row['timeleft'] = $project_time_left;
				}
				else
				{
					$row['timeleft'] = '-';
				}
				
				// auction icons
				$row['icons'] = $ilance->auction->auction_icons($row['project_id'], $row['user_id']);
				
				// #### INVITATION LIST FOR THIS EXPANDED AUCTION
				$sql_invites = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "project_invitations
					WHERE project_id = '" . $row['project_id'] . "'
						AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_invites) > 0)
				{
					$row_count_invites = 0;
					while ($rowinvites = $ilance->db->fetch_array($sql_invites, DB_ASSOC))
					{
						if ($rowinvites['seller_user_id'] != '-1')
						{
							$rowinvites['vendor'] = print_username($rowinvites['seller_user_id'], 'href', 0, '&amp;feedback=1', '');
							$rowinvites['lastseen'] = print_date(fetch_user('lastseen', $rowinvites['seller_user_id']), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
							$rowinvites['invitedate'] = print_date($rowinvites['date_of_invite'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
							if ($rowinvites['date_of_bid'] == '0000-00-00 00:00:00')
							{
								$rowinvites['biddate'] = '-';
							}
							else
							{
								$rowinvites['biddate'] = print_date($rowinvites['date_of_bid'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
							}
							if ($rowinvites['date_of_remind'] == '0000-00-00 00:00:00')
							{
								$rowinvites['reminddate'] = '-';
							}
							else
							{
								$rowinvites['reminddate'] = print_date($rowinvites['date_of_remind'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
							}
							$rowinvites['action'] = '<a href="'.$ilpage['buying'].'?cmd=management&amp;subcmd=invitations&amp;action=remove-invite&amp;id='.$rowinvites['id'].'" onclick="return confirm_js(phrase[\'_confirmation_you_are_about_to_uninvite_this_provider_from_bidding_on_your_auction_continue\'])"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="'.$phrase['_remove_invitation'].'" border="0" /></a>';
							
							switch ($rowinvites['bid_placed'])
							{
								case '1':
								{
									$rowinvites['bidplaced'] = $phrase['_yes'];
									$rowinvites['remind'] = '-';
									break;
								}                                                        
								case '0':
								{
									$rowinvites['bidplaced'] = $phrase['_not_yet'];
									if ($row['status'] == 'open')
									{
										$rowinvites['remind'] = '<a href="'.$ilpage['buying'].'?cmd=management&amp;subcmd=invitations&amp;action=remind&amp;id='.$rowinvites['id'].'" title="'.$phrase['_remind'].'" onclick="return confirm_js(phrase[\'_confirmation_you_are_about_to_send_a_notification_reminder_to_this_provider_regarding_invitation_to_your_auction\'])">'.$phrase['_remind'].'</a>';
									}
									else
									{
										$rowinvites['remind'] = '-';
									}
									break;
								}
							}        
						}
						else
						{
							$rowinvites['vendor'] = ucfirst(stripslashes($rowinvites['name'])). ' ('.$rowinvites['email'].')';
							$rowinvites['lastseen'] = print_date(fetch_user('lastseen', $rowinvites['seller_user_id']), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
							$rowinvites['invitedate'] = print_date($rowinvites['date_of_invite'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
							if ($rowinvites['date_of_bid'] == '0000-00-00 00:00:00')
							{
								$rowinvites['biddate'] = '-';
							}
							else
							{
								$rowinvites['biddate'] = print_date($rowinvites['date_of_bid'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
							}
							if ($rowinvites['date_of_remind'] == '0000-00-00 00:00:00')
							{
								$rowinvites['reminddate'] = '-';
							}
							else
							{
								$rowinvites['reminddate'] = print_date($rowinvites['date_of_remind'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
							}
							$rowinvites['action'] = '<a href="'.$ilpage['buying'].'?cmd=management&amp;subcmd=invitations&amp;action=remove-invite&amp;id='.$rowinvites['id'].'" onclick="return confirm_js(phrase[\'_confirmation_you_are_about_to_uninvite_this_provider_from_bidding_on_your_auction_continue\'])"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="'.$phrase['_remove_invitation'].'" border="0" /></a>';
							
							switch ($rowinvites['bid_placed'])
							{
								case '1':
								{
									$rowinvites['bidplaced'] = $phrase['_yes'];
									$rowinvites['remind'] = '-';
									break;
								}
								case '0':
								{
									$rowinvites['bidplaced'] = $phrase['_not_yet'];
									if ($row['status'] == 'open')
									{
										$rowinvites['remind'] = '<a href="'.$ilpage['buying'].'?cmd=management&amp;subcmd=invitations&amp;action=remind&amp;id='.$rowinvites['id'].'" title="'.$phrase['_remind'].'" onclick="return confirm_js(phrase[\'_confirmation_you_are_about_to_send_a_notification_reminder_to_this_provider_regarding_invitation_to_your_auction\'])">'.$phrase['_remind'].'</a>';
									}
									else
									{
										$rowinvites['remind'] = '-';
									}
									break;
								}
							}       
						}
						$rowinvites['bgclass'] = ($row_count_invites % 2) ? 'alt2' : 'alt1';
						$GLOBALS['invitationlist' . $row['project_id']][] = $rowinvites;
						$row_count_invites++;
					}
				}
				else
				{
					$GLOBALS['no_invitationlist' . $row['project_id']][] = 1;
				}
				
				$query['bids'] = array();
				$groupby = 'GROUP BY b.bid_id ';
				$inbidgroup = '';
				
				$sqlver = $ilance->db->query("SELECT version() AS version", 0, null, __FILE__, __LINE__);
				$sqlres = $ilance->db->fetch_array($sqlver);
				
				$cangroupbids = (version_compare($sqlres['version'], '4.1.22', '>')) ? true : false;
				if ($ilance->categories->bidgrouping($row['cid']) AND $cangroupbids)
				{
					if ($ilance->categories->bidgroupdisplay($row['cid']) == 'lowest')
					{
						// group each bidders bid by lowest placed
						$query['bids'] = "SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, MIN(b.bidamount) AS bidamount, b.bidamounttype, b.bidcustom, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, ";
						$inbidgroup = "AND b.bid_id = (SELECT bid_id FROM " . DB_PREFIX . "project_bids WHERE user_id = b.user_id AND project_id = '" . $row['project_id'] . "' ORDER BY bidamount ASC LIMIT 1) ";
					}
					else
					{
						// group each bidders bid by highest placed
						$query['bids'] = "SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, MAX(b.bidamount) AS bidamount, b.bidamounttype, b.bidcustom, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, ";
						$inbidgroup = "AND b.bid_id = (SELECT bid_id FROM " . DB_PREFIX . "project_bids WHERE user_id = b.user_id AND project_id = '" . $row['project_id'] . "' ORDER BY bidamount DESC LIMIT 1) ";
					}
				}
				else
				{
					// no bid grouping enabled or supported by installed mysql server
					$query['bids'] = "SELECT MAX(b.bid_id) AS bid_id, MAX(b.estimate_days) AS estimate_days, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.bidamounttype, b.bidcustom, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, ";
				}
				
				$query['bids'] .= "b.winnermarkedaspaidmethod, p.status AS project_status, p.escrow_id, p.cid, p.description, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.filtered_budgetid, p.currencyid, u.username, u.city, u.state, u.zip_code
				FROM " . DB_PREFIX . "project_bids AS b,
				" . DB_PREFIX . "projects AS p,
				" . DB_PREFIX . "users AS u
				WHERE b.project_id = '" . $row['project_id'] . "'
					AND p.project_id = b.project_id
					AND b.user_id = u.user_id
					AND b.bidstatus != 'declined'
					AND b.bidstate != 'retracted'
				$inbidgroup
				$groupby
				ORDER BY b.bidamount ASC ";
				
				$result2 = $ilance->db->query($query['bids'], 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($result2) > 0)
				{
					$row_count_bids = 0;
					$ilance->bbcode = construct_object('api.bbcode');
					while ($rows = $ilance->db->fetch_array($result2, DB_ASSOC))
					{
						$project_status = $rows['project_status'];
						$p_id = $row['project_id'];

						$rows['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $ilance->db->fetch_field(DB_PREFIX . "project_bids","bid_id=".$rows['bid_id'],"bidamount"), $rows['currencyid']);
						$rows['delivery'] = $rows['estimate_days'] . ' ' . $ilance->auction->construct_measure($rows['bidamounttype']);
						$proposal = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $rows['bid_id'] . "'", "proposal");
						if (!empty($proposal))
						{
							$rows['proposal'] = $ilance->bbcode->bbcode_to_html($proposal);
							$rows['proposal'] = strip_vulgar_words($rows['proposal']);
						}
						else
						{
							$rows['proposal'] = $phrase['_no_bid_proposal_was_provided'];
						}
						
						$rows['isonline'] = print_online_status($rows['user_id']);
						$rows['verified'] = fetch_verified_credentials($rows['user_id']);
						$rows['bidder'] = print_username($rows['user_id'], 'href', 0, '', '');
						$rows['city'] = ucfirst($rows['city']);
						$rows['state'] = ucfirst($rows['state']);
						$rows['zip'] = trim(mb_strtoupper($rows['zip_code']));
						$rows['location'] = $rows['state'] . ' &gt; ' . print_user_country($rows['user_id']);
						$rows['awarded'] = print_username($rows['user_id'], 'custom', 0, '', '', fetch_user('serviceawards', $rows['user_id']) . ' ' . $phrase['_awards']);
						$rows['reviews'] = print_username($rows['user_id'], 'custom', 0, '', '', fetch_service_reviews_reported($rows['user_id']) . ' ' . $phrase['_reviews']);
						$rows['earnings'] = print_username($rows['user_id'], 'custom', 0, '', '', print_income_reported($rows['user_id']));
						$rows['portfolio'] = '<span class="blue"><a href="' . $ilpage['portfolio'] . '?id=' . $rows['user_id'] .'">' . $phrase['_review'] . '</a></span>';
						$rows['bidattach'] = '-';
						$rows['paymethod2'] = print_fixed_payment_method($rows['winnermarkedaspaidmethod'], false);
						if ($rows['paymethod2'] == '')
						{
							$rows['paymethod2'] = '<span class="smaller">-</span>';
						}
						
						$sql_attachments = $ilance->db->query("
							SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
							FROM " . DB_PREFIX . "attachment
							WHERE attachtype = 'bid'
								AND project_id = '" . $row['project_id'] . "'
								AND user_id = '" . $rows['user_id'] . "'
								AND visible = '1'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_attachments) > 0)
						{
							$bidattach = '';
							$c = 1;
							while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
							{
								$bidattach .= '<div class="smaller blue" style="padding-bottom:3px" title="' . handle_input_keywords($res['filename']) . '">' . $c . '. <a href="' . HTTP_SERVER . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . print_string_wrap(handle_input_keywords($res['filename'])) . '</a></div>';
								$c++;
							}
							
							$rows['bidattach'] = $bidattach;
						}
						
						$rows['actionclass'] = 'award';
						$rows['pmb2'] = '-';
						
						// #### custom bid field answers #######
						$rows['custom_bid_fields'] = $ilance->bid_fields->construct_bid_fields($rows['cid'], $rows['project_id'], 'output1', 'service', $rows['bid_id'], false);
						$row['awardedbidsbit'] = '';
						
						// open or expired actions
						switch ($project_status)
						{
							// #### open or expired
							case 'open':
							case 'expired':
							{
								$bidstatus = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $rows['bid_id'] . "'", "bidstatus");
								if ($bidstatus == 'declined')
								{
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
									$rows['bidaction'] = $phrase['_this_bid_has_been_declined'];
									$rows['actionclass'] = 'declined';
								}
								else
								{
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
									
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'awardbid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" style="font-size:15px" value=" ' . $phrase['_award'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" />&nbsp;&nbsp;';
								    
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'declinebid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] .= '<input type="button" style="font-size:15px" value=" ' . $phrase['_decline'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" />';
								}
								
								$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
								break;
							}                                                
							// #### provider accepted buyers award
							case 'approval_accepted':
							{
								$row['awardedbidsbit'] = ', 1 ' . $phrase['_awarded_lower'];
								$bidstatus = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $rows['bid_id'] . "'", "bidstatus");
								$bidstate = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $rows['bid_id'] . "'", "bidstate");
								
								if ($bidstatus == 'declined')
								{
								    $rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
								    $rows['bidaction'] = $phrase['_this_bid_has_been_declined'];
								    $rows['actionclass'] = 'declined';
								}
								else if ($bidstatus == 'awarded' AND ($bidstate != 'reviewing' OR $bidstate != 'wait_approval'))
								{
									$awarded_vendor = stripslashes($rows['username']);
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" id="" />';
									
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'unawardbid',
										'bid_id' => $rows['bid_id']
									);
									
									$buttonvisible = 'disabled="disabled"';
									if ($ilconfig['servicebid_buyerunaward'])
									{
										$buttonvisible = '';        
									}
									
									$rows['bidaction'] = '<input type="button" style="font-size:15px" value=" ' . $phrase['_unaward'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" ' . $buttonvisible . ' />';
									$rows['actionclass'] = 'unaward';
									$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
								}
								else if ($bidstatus == 'placed' AND $bidstate == 'reviewing' OR $bidstatus == 'choseanother' AND $bidstate == 'reviewing')
								{
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
									$rows['pmb2'] = '-';
									
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'declinebid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
								}
								else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
								{
									$awarded_vendor = stripslashes($rows['username']);
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" />';
									
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'unawardbid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value="' . $phrase['_unaward'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
									
									$rows['actionclass'] = 'unaward';
									$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
								}
								else if ($bidstatus == 'placed' AND empty($bidstate))
								{
									$rows['pmb2'] = '-';
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
									
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'declinebid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
								}
								else
								{
									$rows['pmb2'] = '-';
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
									
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'declinebid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
								}
								break;
							}                                                        
							// #### buyer waiting for provider's acceptance to award
							case 'wait_approval':
							{
								$row['awardedbidsbit'] = ', 1 ' . $phrase['_awarded_lower'];
								// buyer awarded provider :: enable radio icons :: create additional award cancellation button
								$bidstatus = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $rows['bid_id'] . "'", "bidstatus");
								$bidstate = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $rows['bid_id'] . "'", "bidstate");
								if ($bidstatus == 'declined')
								{
								    $rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
								    $rows['bidaction'] = $phrase['_this_bid_has_been_declined'];
								    $rows['actionclass'] = 'declined';
								    $rows['pmb2'] = '-';
								}
								else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
								{
									// buyer pending approval from service provider (provider did not confirm acceptance to project)
									$awarded_vendor = stripslashes($rows['username']);
									$rows['award'] = $phrase['_pending_approval'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>' . $phrase['_pending_approval'] . ' ' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . '</strong></div><div>' . $phrase['_pending_approval_allows_the_awarded_service_provider_to_accept_or_reject_the_service_auction'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a><div class="smaller gray">(' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . ')</div>';
									
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'unawardbid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value="' . $phrase['_unaward'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
									
									$rows['actionclass'] = 'unaward';
									$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
								}
								else if ($bidstatus == 'placed' AND $bidstate == 'reviewing')
								{
									// service provider in review mode - 90% change will not become awarded
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'declinebid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
								}
								else
								{
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'awardbid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value=" ' . $phrase['_award'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" />&nbsp;';
									
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'declinebid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] .= '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
								}        
								break;
							}                                                
							// #### listing is finished/completed
							case 'finished':
							{
								$bidstatus = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $rows['bid_id'] . "'", "bidstatus");
								$bidstate = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $rows['bid_id'] . "'", "bidstate");
								
								// project in a phase to not allow any bid controls
								if ($bidstatus == 'declined')
								{
								    $rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
								    $rows['bidaction'] = $phrase['_this_bid_has_been_declined'];
								    $rows['actionclass'] = 'declined';
								    $rows['pmb2'] = '-';
								}
								else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
								{
									$row['awardedbidsbit'] = ', 1 ' . $phrase['_awarded_lower'];
									
									// buyer pending approval from service provider (provider did not confirm acceptance to project)
									$awarded_vendor = stripslashes($rows['username']);
									$rows['award'] = $phrase['_pending_approval'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>' . $phrase['_pending_approval'] . ' ' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . '</strong></div><div>' . $phrase['_pending_approval_allows_the_awarded_service_provider_to_accept_or_reject_the_service_auction'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a><div class="smaller gray">(' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . ')</div>';
									
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'unawardbid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value="' . $phrase['_unaward'] . '" onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" style="font-size:15px" />';
									
									$rows['actionclass'] = 'unaward';
									$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
								}
								else if ($bidstatus == 'placed' AND $bidstate == 'reviewing')
								{
									// service provider in review mode - 90% change will not become awarded
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'declinebid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
								}
								else if ($bidstatus == 'choseanother' AND $bidstate == 'reviewing')
								{
									// service provider in review mode - 90% change will not become awarded
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'declinebid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
								}
								else if ($bidstatus == 'awarded')
								{
									$row['awardedbidsbit'] = ', 1 ' . $phrase['_awarded_lower'];
									$awarded_vendor = stripslashes($rows['username']);
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" id="" />';
									$rows['bidaction'] = '<input type="button" value=" ' . $phrase['_unaward'] . ' " class="buttons" disabled="disabled" style="font-size:15px" />';
									$rows['actionclass'] = 'unaward';
									$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
								}
								else
								{
									$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'awardbid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] = '<input type="button" value=" ' . $phrase['_award'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" />&nbsp;';
									
									$crypted = array(
										'cmd' => '_do-rfp-action',
										'bidcmd' => 'declinebid',
										'bid_id' => $rows['bid_id']
									);
									$rows['bidaction'] .= '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="if (confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" style="font-size:15px" />';
								}
								break;
							}                                                
							// #### listing is closed or delisted
							case 'closed':
							case 'delisted':
							{
								// project in a phase to not allow any bid controls
								$rows['pmb2'] = '-';
								$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
								$rows['bidaction'] = '&nbsp;';
								break;
							}
						}
						
						// bid amount type
						switch ($rows['bidamounttype'])
						{
							case 'entire':
							{
								$rows['bidamounttype'] = $phrase['_for_entire_project'];
								break;
							}
							case 'hourly':
							{
								$rows['bidamounttype'] = $phrase['_per_hour'];
								break;
							}                                                
							case 'daily':
							{
								$rows['bidamounttype'] = $phrase['_per_day'];
								break;
							}                                                
							case 'weekly':
							{
								$rows['bidamounttype'] = $phrase['_weekly'];
								break;
							}                                                
							case 'monthly':
							{
								$rows['bidamounttype'] = $phrase['_monthly'];
								break;
							}                                                
							case 'lot':
							{
								$rows['bidamounttype'] = $phrase['_per_lot'];
								break;
							}                                                
							case 'weight':
							{
								$rows['bidamounttype'] = $phrase['_per_weight'] . ' ' . stripslashes($row['bidcustom']);
								break;
							}
							case 'item':
							{
								$rows['bidamounttype'] = $phrase['_per_item'];
								break;
							}
						}
						
						$rows['bid_datetime'] = print_date($rows['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$rows['class2'] = ($row_count_bids % 2) ? 'alt2' : 'alt1';
						
						$GLOBALS['service_buying_bidding_activity' . $row['project_id']][] = $rows;
						$row_count_bids++;
					}
				}
				else
				{
					$GLOBALS['no_service_buying_bidding_activity' . $row['project_id']] = 1;
					$row['awardedbidsbit'] = '';
				}

				// insertion fee in this category
				if ($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0)
				{
					$row['insfee'] = ($row['isifpaid'])
						? '<div class="smaller"><span class="blue" title="' . $phrase['_unpaid'] . '"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></span></div>'
						: '<div class="smaller"><span class="red" title="' . $phrase['_unpaid'] . '"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></span></div>';
				}
				else
				{
					$row['insfee'] = '-';
				}
				
				if ($row['highlite'])
				{
					$row['class'] = 'featured_highlight';
				}
				else
				{
					$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				}
				
				$service_buying_activity[] = $row;
				$row_count++;
			}
			
			$show['no_service_buying_activity'] = false;
			$show['rfppulldownmenu'] = true;
		}
		else
		{
			$show['no_service_buying_activity'] = true;
			$show['rfppulldownmenu'] = false;
		}
		
		// prev / next links
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $counter, $scriptpage, 'page');	
	}
	
	// #### product auction buying/bidding activity ########################
	if ($ilconfig['globalauctionsettings_productauctionsenabled'])
	{
		// #### require shipping backend ###############################
		require_once(DIR_CORE . 'functions_shipping.php');
		
		// #### buyer marking listing as paid ##########################
		if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markaspaid' AND isset($uncrypted['pid']) AND $uncrypted['pid'] > 0 AND isset($uncrypted['bid']) AND $uncrypted['bid'] > 0)
		{
			// #### buyer input as to how the payment was made to the seller
			$ilance->GPC['winnermarkedaspaidmethod'] = isset($ilance->GPC['winnermarkedaspaidmethod'])
				? handle_input_keywords($ilance->GPC['winnermarkedaspaidmethod'])
				: $phrase['_unknown'];
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = '" . $ilance->db->escape_string($ilance->GPC['winnermarkedaspaidmethod']) . "'
				WHERE project_id = '" . intval($uncrypted['pid']) . "'
					AND bid_id = '" . intval($uncrypted['bid']) . "'
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = '" . $ilance->db->escape_string($ilance->GPC['winnermarkedaspaidmethod']) . "'
				WHERE project_id = '" . intval($uncrypted['pid']) . "'
					AND bid_id = '" . intval($uncrypted['bid']) . "'
			", 0, null, __FILE__, __LINE__);
			
			if (isset($ilance->GPC['sub']) AND !empty($ilance->GPC['sub']))
			{
				$urlbit = '&sub=' . $ilance->GPC['sub'];
			}
			else if (isset($ilance->GPC['bidsub']) AND !empty($ilance->GPC['bidsub']))
			{
				$urlbit = '&bidsub=' . $ilance->GPC['bidsub'];
			}
			
			refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management' . $urlbit . '&page2=' . $ilance->GPC['page2']);
			exit();
		}
		
		// #### buyer marking listing unpaid ###########################
		else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markasunpaid' AND isset($uncrypted['pid']) AND $uncrypted['pid'] > 0 AND isset($uncrypted['bid']) AND $uncrypted['bid'] > 0)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET winnermarkedaspaid = '0',
				winnermarkedaspaiddate = '0000-00-00 00:00:00',
				winnermarkedaspaidmethod = ''
				WHERE project_id = '" . intval($uncrypted['pid']) . "'
					AND bid_id = '" . intval($uncrypted['bid']) . "'
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET winnermarkedaspaid = '0',
				winnermarkedaspaiddate = '0000-00-00 00:00:00',
				winnermarkedaspaidmethod = ''
				WHERE project_id = '" . intval($uncrypted['pid']) . "'
					AND bid_id = '" . intval($uncrypted['bid']) . "'
			", 0, null, __FILE__, __LINE__);
			
			if (isset($ilance->GPC['sub']) AND !empty($ilance->GPC['sub']))
			{
				$urlbit = '&sub=' . $ilance->GPC['sub'];
			}
			else if (isset($ilance->GPC['bidsub']) AND !empty($ilance->GPC['bidsub']))
			{
				$urlbit = '&bidsub=' . $ilance->GPC['bidsub'];
			}
			
			refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management' . $urlbit . '&page2=' . $ilance->GPC['page2']);
			exit();
		}
		
		// #### marking buy now order as paid ##########################
		else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markorderaspaid' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0 AND isset($uncrypted['orderid']) AND $uncrypted['orderid'] > 0)
		{
			$winnermarkedaspaidmethod = isset($ilance->GPC['winnermarkedaspaidmethod']) ? $ilance->GPC['winnermarkedaspaidmethod'] : '';
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET paiddate = '" . DATETIME24H . "',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaid = '1',
				winnermarkedaspaidmethod = '" . $ilance->db->escape_string($winnermarkedaspaidmethod) . "'
				WHERE orderid = '" . intval($uncrypted['orderid']) . "'
					AND project_id = '" . intval($uncrypted['id']) . "'
			");
			
			if (isset($ilance->GPC['sub']) AND !empty($ilance->GPC['sub']))
			{
				$urlbit = '&sub=' . $ilance->GPC['sub'];
			}
			else if (isset($ilance->GPC['bidsub']) AND !empty($ilance->GPC['bidsub']))
			{
				$urlbit = '&bidsub=' . $ilance->GPC['bidsub'];
			}
			
			refresh(HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management' . $urlbit . '&page=' . $ilance->GPC['page']);
			exit();
		}
		
		// #### buyer marking lub listing as paid ######################
		if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'marklubaspaid' AND isset($uncrypted['pid']) AND $uncrypted['pid'] > 0 AND isset($uncrypted['bid']) AND $uncrypted['bid'] > 0)
		{
			// #### buyer input as to how the payment was made to the seller
			$ilance->GPC['winnermarkedaspaidmethod'] = isset($ilance->GPC['winnermarkedaspaidmethod'])
				? handle_input_keywords($ilance->GPC['winnermarkedaspaidmethod'])
				: $phrase['_unknown'];
			
			// #### buyer marking lub listing as paid in full ######
			mark_lub_listing_as_paid($uncrypted['pid'], $ilance->GPC['winnermarkedaspaidmethod']);
			
			refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&subcmd=lub&bidsub=awarded');
			exit();	
		}
		// #### buyer marking lub listing as unpaid ####################
		else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'marklubasunpaid' AND isset($uncrypted['pid']) AND $uncrypted['pid'] > 0 AND isset($uncrypted['bid']) AND $uncrypted['bid'] > 0)
		{
			// #### buyer marking lub listing as paid in full ######
			mark_lub_listing_as_unpaid($uncrypted['pid']);
			
			refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&subcmd=lub&bidsub=awarded');
			exit();	
		}
		
		$ilance->GPC['page2'] = (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0) ? 1 : intval($ilance->GPC['page2']);
		
		$extra2 = '';
		$extra2 .= (!empty($ilance->GPC['bidsub'])) ? '&amp;bidsub=' . $ilance->GPC['bidsub'] : '';
		
		// #### LISTING PERIOD #########################################
		$ilance->GPC['period2'] = (isset($ilance->GPC['period2']) ? intval($ilance->GPC['period2']) : -1);
		$periodsql2 = fetch_startend_sql(intval($ilance->GPC['period2']), 'DATE_SUB', 'p.date_added', '>=');
		$extra2 .= '&amp;period2=' . intval($ilance->GPC['period2']);
		
		// #### RESULTS ORDERING BY COLUMN NAME DEFAULTS ###############
		$orderby2 = '&amp;orderby2=date_end';
		$orderbysql2 = 'date_added';
		
		$orderbyfields2 = array('project_title', 'date_added', 'date_end', 'bids');
		if (isset($ilance->GPC['orderby2']) AND in_array($ilance->GPC['orderby2'], $orderbyfields2))
		{
			$orderby2 = '&amp;orderby2=' . $ilance->GPC['orderby2'];
			$orderbysql2 = $ilance->GPC['orderby2'];
		}
		
		// #### RESULTS DISPLAY ORDER DEFAULTS #########################
		$displayorderfields2 = array('asc', 'desc');
		$displayorder2 = '&amp;displayorder2=asc';
		$currentdisplayorder2 = $displayorder2;
		$displayordersql2 = 'DESC';
		
		if (isset($ilance->GPC['displayorder2']) AND $ilance->GPC['displayorder2'] == 'asc')
		{
			$displayorder2 = '&amp;displayorder2=desc';
			$currentdisplayorder2 = '&amp;displayorder2=asc';
		}
		else if (isset($ilance->GPC['displayorder2']) AND $ilance->GPC['displayorder2'] == 'desc')
		{
			$displayorder2 = '&amp;displayorder2=asc';
			$currentdisplayorder2 = '&amp;displayorder2=desc';
		}
		if (isset($ilance->GPC['displayorder2']) AND in_array($ilance->GPC['displayorder2'], $displayorderfields2))
		{
			$displayordersql2 = mb_strtoupper($ilance->GPC['displayorder2']);
		}
		
		$groupby = "GROUP BY b.project_id";
		$orderby = "ORDER BY $orderbysql2 $displayordersql2";
		$limit = "LIMIT " . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay'];                
		
		// #### LOWEST UNIQUE BID BUYING/WON PANEL DISPLAY #############
		if ($ilconfig['enable_uniquebidding'] AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'lub')
		{
			$headinclude .= '
<script language="Javascript" type="text/javascript">
<!--
function show_prompt_payment_lub(urlbit)
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
//-->
</script>';			
			// #### require lowest unique bid backend ##############
			$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
			
			// #### used within templates
			$php_self2 = $ilpage['buying'] . '?cmd=management&amp;subcmd=lub' . $displayorder2 . $extra2;
			
			// #### used within prev / next page nav
			$scriptpage2 = $ilpage['buying'] . '?cmd=management&amp;subcmd=lub' . $currentdisplayorder2 . $orderby2 . $extra2;
			
			$bids_awarded = $bids_expired = $bids_active = 0;
			$query = array();
			
			// #### awarded bid tabs ###############################
			if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'awarded')
			{
				$bids_awarded = 1;
				$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('awarded_unique', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('awarded_unique', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$producttabs = print_buying_activity_tabs('awarded', 'unique', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
			}
			
			// #### expired bid tabs ###############################
			else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'expired')
			{
				$bids_expired = 1;
				$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('expired_unique', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('expired_unique', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$producttabs = print_buying_activity_tabs('expired', 'unique', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
			}
			
			// #### active bid tabs ################################
			else
			{
				$bids_active = 1;
				$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('active_unique', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('active_unique', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$producttabs = print_buying_activity_tabs('active', 'unique', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
			}
			
			$counter = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
			$condition = $condition2 = '';
			
			$row_count2 = 0;
			$numberrows = $ilance->db->query($query['2'], 0, null, __FILE__, __LINE__);
			$number = $ilance->db->num_rows($numberrows);
			$result2 = $ilance->db->query($query['1'], 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result2) > 0)
			{
				$row_count2 = 0;
				while ($row2 = $ilance->db->fetch_array($result2, DB_ASSOC))
				{
					$row2['merchant'] = fetch_user('username', $row2['user_id']);
					$row2['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $row2['project_id'], 'thumb', $row2['project_id']);
					$row2['bidamount'] = $ilance->currency->format($row2['uniquebid'], $row2['currencyid']);
					$row2['item_title'] = stripslashes($row2['project_title']);
					$row2['bids'] = $ilance->bid_lowest_unique->fetch_bid_count($row2['project_id']);
					$row2['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
					$row2['timeleft'] = $ilance->auction->calculate_time_left($row2['date_starts'], $row2['starttime'], $row2['mytime']);
					$row2['actions'] = '<input type="checkbox" name="bid_id[]" value="' . $row2['project_id'] . '" disabled="disabled" />';
					//$row2['delivery'] = '<span class="gray">' . $phrase['_pending_shipment_by_seller'] . '</span>';
					$row2['orderlocation'] = '-';
					
					if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'awarded')
					{
						$row2['lastuniquebid'] = $ilance->bid_lowest_unique->fetch_lowest_unique_bid_winner_amount($row2['project_id'], false);
						if ($row2['lastuniquebid'] > 0)
						{
							$row2['lastuniquebid'] = $ilance->currency->format($row2['lastuniquebid'], $row2['currencyid']);
						}
						
						// this will additionally show a link to invoice payment if unpaid..
						//$row2['payment'] = $ilance->bid->fetch_transaction_status($row2['project_id'], true, true, true);
						//////////////
						
						// #### populate shipping field actions
						if ($row2['ship_method'] == 'flatrate' OR $row2['ship_method'] == 'calculated')
						{
							$shippercount = print_shipping_methods($row2['project_id'], 1, false, true);
							
							if ($row2['sellermarkedasshipped'] AND $row2['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
							{
								$row2['orderlocation'] = '<div class="smaller black">' . $phrase['_marked_as_shipped_on'] . ' <span class="blue">' . print_date($row2['sellermarkedasshippeddate']) . '</span></div>';
								$row2['orderlocation'] .= '<div style="padding-top:6px" class="smaller gray">' . $phrase['_download'] . ': <span class="blue">' . $phrase['_none'] . '</span></div>';
								$row2['shipping'] = $ilance->currency->format($row2['buyershipcost'], $row2['currencyid']);
								$row2['shipservice'] = '<span class="smaller blue" title="' . print_shipping_partner($row2['buyershipperid']) . '">' . shorten(print_shipping_partner($row2['buyershipperid']), 28) . '</span>';
							}
							else
							{
								// did buyer select a ship service yet?
								// he would have chosen a method when placing a bid.. unless only 1 service was available
								if ($row2['buyershipperid'] > 0)
								{
									$row2['orderlocation'] = '<span class="smaller black">' . $phrase['_waiting_for_merchant_to_update_order_as_delivered'] . '</span>';
									$shipperid = $row2['buyershipperid'];
									$shippingcosts = fetch_ship_cost_by_shipperid($row2['project_id'], $shipperid, 1);
									$row2['buyershipcost'] = $shippingcosts['total'];
									
									if ($shippercount == 1)
									{
										$row2['shipservice'] = '<span class="smaller blue" title="' . print_shipping_partner($shipperid) . '">' . shorten(print_shipping_partner($shipperid), 28) . '</span>';
									}
									else
									{
										if ($shippercount > 1)
										{
											$row2['shipservice'] = '<span class="smaller blue" title="' . print_shipping_partner($shipperid) . '"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id='. $row2['project_id'] . '&amp;shipperid=' . $shipperid . '&amp;paymethod=' . $row2['buyerpaymethod'] . '&amp;returnurl={pageurl_urlencoded}" style="text-decoration:underline">' . shorten(print_shipping_partner($shipperid), 28) . '</a></span>';	
										}
									}
									
									$row2['shipping'] = $ilance->currency->format($shippingcosts['total'], $row2['currencyid']);
									unset($shipperid, $shippingcosts);
								}
								else
								{
									if ($shippercount == 1)
									{
										$row2['orderlocation'] = '<span class="smaller black">' . $phrase['_waiting_for_merchant_to_update_order_as_delivered'] . '</span>';
										print_shipping_methods($row2['project_id'], 1, false, false);
										$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . $row2['project_id'] . "'", "ship_service_$shipperidrow");
										$shippingcosts = fetch_ship_cost_by_shipperid($row2['project_id'], $shipperid, 1);
										$row2['buyershipcost'] = $shippingcosts['total'];
										$row2['shipservice'] = '<span class="smaller blue" title="' . print_shipping_partner($shipperid) . '">' . shorten(print_shipping_partner($shipperid), 28) . '</span>';
										$row2['shipping'] = $ilance->currency->format($shippingcosts['total'], $row2['currencyid']);
										unset($shipperid, $shippingcosts);
									}
									else
									{
										if ($shippercount > 1)
										{
											$row2['orderlocation'] = '<div class="smaller black">' . $phrase['_seller_cannot_ship_yet'] . '</div><div><span class="smaller blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id='. $row2['project_id'] . '&amp;shipperid=" style="text-decoration:underline">' . $phrase['_choose_shipping_service'] . '</a></span></div>';
											$row2['shipservice'] = '<span class="smaller blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id='. $row2['project_id'] . '&amp;shipperid=" style="text-decoration:underline">' . $phrase['_choose'] . '...</a></span>';
											$row2['shipping'] = $row2['shipservice'];
											$row2['buyershipcost'] = 0;
										}
									}
								}
							}
						}
						
						// #### local pickup only
						else
						{
							$row2['orderlocation'] = $phrase['_local_pickup_only'];
							$row2['shipping'] = '-';
							$row2['shipservice'] = $phrase['_local_pickup'];
							$row2['buyershipcost'] = 0;
						}
						
						$methodscount = print_payment_methods($row2['project_id'], false, true);
						
						// #### single payment method offered by seller
						if ($methodscount == 1)
						{
							$row2['buyerpaymethod'] = print_payment_method_title($row2['project_id']);
							
							// #### gateway ########
							if (strchr($row2['buyerpaymethod'], 'gateway'))
							{
								$row2['payment'] = ($row2['winnermarkedaspaid'] == '0')
									? '<span class="gray">' . $phrase['_next'] . ':</span> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . $row2['project_id'] . '" style="text-decoration:underline"><strong>' . $phrase['_pay_now'] . '</strong></a></span>'
									: '<div class="smaller black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($row2['winnermarkedaspaiddate']) . '</span> ' . $phrase['_using'] . ' <span class="blue">' . print_fixed_payment_method($row2['buyerpaymethod'], false) . '</span></div>';	
							}
							
							// #### offline ########
							else if (strchr($row2['buyerpaymethod'], 'offline'))
							{
								if ($row2['winnermarkedaspaid'] == '0')
								{
									$crypted = array(
										'cmd' => 'management',
										'subcmd' => 'marklubaspaid',
										'pid' => $row2['project_id'],
										'bid' => $row2['uid']
									);
									
									$row2['payment'] = '<span class="gray"><span style="float:left; padding-right:6px">' . $phrase['_next'] . ':</span> <span class="blue"><a href="javascript:;" onclick="return show_prompt_payment(\'' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;bidsub=' . $ilance->GPC['bidsub'] . '&amp;page2=' . $ilance->GPC['page2'] . '\')" style="text-decoration:underline"><strong>' . $phrase['_mark_paid_manually'] . '</strong></a></span>';
									unset($crypted);
								}
								else
								{
									$crypted = array(
										'cmd' => 'management',
										'subcmd' => 'marklubasunpaid',
										'pid' => $row2['project_id'],
										'bid' => $row2['uid']
									);
									
									$row2['payment'] = '<div class="smaller black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($row2['winnermarkedaspaiddate']) . '</span> ' . $phrase['_using'] . ' <span class="blue">' . handle_input_keywords($row2['winnermarkedaspaidmethod']) . '</span></div>';
									
									// before we let the "buyer" mark as "unpaid" we should check when he last "marked as paid" and if it's
									// like more than 7 days we do not allow the buyer to play god with payment status avoiding future confusion
									// to the seller (who already would have received the funds).. this is just a quick hack to prevent
									// abuse to payment details when the admin is finally reviewing details from the admincp
									$date1split = explode(' ', $row2['winnermarkedaspaiddate']);
									$date2split = explode('-', $date1split[0]);
									$totaldays = 14;
									$elapsed = $ilance->datetime->fetch_days_between($date2split[1], $date2split[2], $date2split[0], gmdate('m'), gmdate('d'), gmdate('Y'));
									$daysleft = ($totaldays - $elapsed);
									if ($elapsed <= 14)
									{
										$row2['payment'] .= '<div class="smaller blue" style="padding-top:7px"><span class="gray">' . $phrase['_optional'] . ':</span> <span title="(' . $daysleft . ' ' . $phrase['_days_left'] . ')"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;bidsub=' . $ilance->GPC['bidsub'] . '&amp;page2=' . intval($ilance->GPC['page2']) . '" onclick="return confirm_js(\'' . $phrase['_you_are_about_to_change_the_status_for_the_payment_on_this_item_to_unpaid'] . '\')" style="text-decoration:underline">' . $phrase['_mark_as_unpaid'] . '</a></span></div>';
									}
									unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
								}	
							}							
						}
						// #### multiple payment methods offered by seller
						else
						{
							if (!empty($row2['buyerpaymethod']))
							{
								$row2['paymethod'] = print_fixed_payment_method($row2['buyerpaymethod'], false);
								
								//$row2['payment'] = print_fixed_payment_method($row2['buyerpaymethod'], false);
								// #### gateway ########
								if (strchr($row2['buyerpaymethod'], 'gateway'))
								{
									$row2['payment'] = ($row2['winnermarkedaspaid'] == '0')
										? '<span class="gray">' . $phrase['_next'] . ':</span> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . $row2['project_id'] . '" style="text-decoration:underline"><strong>' . $phrase['_pay_now'] . '</strong></a></span>'
										: '<div class="smaller black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($row2['winnermarkedaspaiddate']) . '</span> ' . $phrase['_using'] . ' <span class="blue">' . $row2['paymethod'] . '</span></div>';	
								}
								
								// #### offline ########
								else if (strchr($row2['buyerpaymethod'], 'offline'))
								{
									if ($row2['winnermarkedaspaid'] == '0')
									{
										$crypted = array(
											'cmd' => 'management',
											'subcmd' => 'marklubaspaid',
											'pid' => $row2['project_id'],
											'bid' => $row2['uid']
										);
										
										$row2['payment'] = '<span class="gray"><span style="float:left; padding-right:6px">' . $phrase['_next'] . ':</span> <span class="blue"><a href="javascript:void(0)" onclick="return show_prompt_payment_lub(\'' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;bidsub=' . $ilance->GPC['bidsub'] . '&amp;page2=' . $ilance->GPC['page2'] . '\')" style="text-decoration:underline"><strong>' . $phrase['_mark_paid_manually'] . '</strong></a></span>';
										unset($crypted);
									}
									else
									{
										$crypted = array(
											'cmd' => 'management',
											'subcmd' => 'marklubasunpaid',
											'pid' => $row2['project_id'],
											'bid' => $row2['uid']
										);
										
										$row2['payment'] = '<div class="smaller black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($row2['winnermarkedaspaiddate']) . '</span> ' . $phrase['_via'] . ' <span class="blue">' . $row2['winnermarkedaspaidmethod'] . '</span></div>';
										
										// before we let the "buyer" mark as "unpaid" we should check when he last "marked as paid" and if it's
										// like more than 7 days we do not allow the buyer to play god with payment status avoiding future confusion
										// to the seller (who already would have received the funds).. this is just a quick hack to prevent
										// abuse to payment details when the admin is finally reviewing details from the admincp
										$date1split = explode(' ', $row2['winnermarkedaspaiddate']);
										$date2split = explode('-', $date1split[0]);
										$totaldays = 14;
										$elapsed = $ilance->datetime->fetch_days_between($date2split[1], $date2split[2], $date2split[0], gmdate('m'), gmdate('d'), gmdate('Y'));
										$daysleft = ($totaldays - $elapsed);
										if ($elapsed <= 14)
										{
											$row2['payment'] .= '<div class="smaller blue" style="padding-top:7px"><span class="gray">' . $phrase['_optional'] . ':</span> <span title="(' . $daysleft . ' ' . $phrase['_days_left'] . ')"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;bidsub=' . $ilance->GPC['bidsub'] . '&amp;page2=' . intval($ilance->GPC['page2']) . '" onclick="return confirm_js(\'' . $phrase['_you_are_about_to_change_the_status_for_the_payment_on_this_item_to_unpaid'] . '\')" style="text-decoration:underline">' . $phrase['_mark_as_unpaid'] . '</a></span></div>';
										}
										unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
									}	
								}								
							}
							else
							{
								$row2['payment'] = '<span class="smaller blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;paymethod=" style="text-decoration:underline">' . $phrase['_choose_payment_method'] . '</a></span>';
							}
						}
					}
					else
					{
						$row2['lastuniquebid'] = $ilance->bid_lowest_unique->fetch_last_unique_bid_amount($_SESSION['ilancedata']['user']['userid'], $row2['project_id']);
						if ($row2['lastuniquebid'] > 0)
						{
							$row2['lastuniquebid'] = $ilance->currency->format($row2['lastuniquebid'], $row2['currencyid']);
						}
						
						$row2['payment'] = '-';
						$row2['delivery'] = '-';
					}
					
					if ($ilance->feedback->has_left_feedback($row2['user_id'], $_SESSION['ilancedata']['user']['userid'], $row2['project_id'], 'seller'))
					{
						// bidder already rated seller
						$row2['feedback'] = '<div align="center"><span title="' . $phrase['_feedback_submitted__thank_you'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="' . $phrase['_feedback_submitted__thank_you'] . '" /></span></div>';
					}
					else
					{
						// can we show feedback icon?
						$row2['feedback'] = ($bids_expired OR $bids_active)
							? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_gray.gif" border="0" alt="" /></div>'
							: '<div align="center"><span title="' . $phrase['_submit_feedback_for'] . ' ' . fetch_user('username', $row2['user_id']) . '"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}" onmouseover="rollovericon(\'' . md5($row2['user_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row2['project_id'] . ':feedback') . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_rate.gif\')" onmouseout="rollovericon(\'' . md5($row2['user_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row2['project_id'] . ':feedback') . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="' . $phrase['_submit_feedback_for'] . ' ' . fetch_user('username', $row2['user_id']) . '" name="' . md5($row2['user_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row2['project_id'] . ':feedback') . '" /></a></span></div>';
					}
					
					if ($row2['uniquestatus'] == 'lowestunique' AND $row2['status'] == 'open')
					{
						$row2['uniquebidstatus'] = '<span style=""><strong>' . $phrase['_lowest_unique_bid'] . '</strong></span>';
					}
					else if ($row2['uniquestatus'] == 'lowestunique' AND $row2['status'] == 'open')
					{
						$row2['uniquebidstatus'] = '<span style=""><strong>' . $phrase['_lowest_unique_bid'] . '</strong></span>';
						$row2['class2'] = 'alt1 featured_highlight';
					}
					else if ($row2['uniquestatus'] == 'lowestunique' AND $row2['status'] != 'open')
					{
						$row2['uniquebidstatus'] = '<span style=""><strong>' . $phrase['_lowest_unique_bid'] . '</strong></span>';
					}
					else if ($row2['uniquestatus'] == 'unique')
					{
						$row2['uniquebidstatus'] = '<strong>' . $phrase['_unique'] . '</strong>';
					}
					else if ($row2['uniquestatus'] == 'nonunique')
					{
						$row2['uniquebidstatus'] = $phrase['_nonunique'];
					}
					
					$row2['total'] = $ilance->currency->format(($row2['uniquebid'] + $row2['buyershipcost']), $row2['currencyid']);
					$row2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					
					// #### buyers payment method selector
					$methodscount = print_payment_methods($row2['project_id'], false, true);
					if (empty($row2['buyerpaymethod']))
					{
						$row2['paymethod'] = ($methodscount == 1)
							? '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;shipperid=' . $row2['buyershipperid'] . '" style="text-decoration: underline">' . print_fixed_payment_method(print_payment_method_title($row2['project_id']), false) . '</a></span>'
							: '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;shipperid=' . $row2['buyershipperid'] . '" style="text-decoration: underline">' . $phrase['_choose'] . '...</a></span>';
					}
					else
					{
						if ($methodscount == 1)
						{
							$row2['paymethod'] = '<span class="blue">' . print_fixed_payment_method($row2['buyerpaymethod'], false) . '</span>';
						}
						else
						{
							$row2['paymethod'] = ($row2['winnermarkedaspaid'] == '0')
								? '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;paymethod=' . $row2['buyerpaymethod'] . '&amp;shipperid=' . $row2['buyershipperid'] . '&amp;returnurl={pageurl_urlencoded}" style="text-decoration: underline">' . print_fixed_payment_method($row2['buyerpaymethod'], false) . '</a></span>'
								: '<span class="blue">' . print_fixed_payment_method($row2['buyerpaymethod'], false) . '</span>';
						}
					}
					
					$product_unique_bidding_activity[] = $row2;
					$show['no_product_unique_bidding_activity'] = false;
					$row_count2++;   
				}
			}
			else
			{
				$show['no_product_unique_bidding_activity'] = true;
			}
		}
		
		// #### REGULAR BIDDING / BUY NOW / FIXED PRICE DISPLAY ########
		else
		{
			$headinclude .= '
<script language="Javascript" type="text/javascript">
<!--
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
//-->
</script>';
			$bids_retracted = $bids_awarded = $bids_invited = $bids_expired = $bids_active = 0;
			$query = array();
			
			// used within templates
			$php_self2 = $ilpage['buying'] . '?cmd=management' . $displayorder2 . $extra2;
			
			// used within prev / next page nav
			$scriptpage2 = $ilpage['buying'] . '?cmd=management' . $currentdisplayorder2 . $orderby2 . $extra2;
			
			if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'retracted')
			{
				// delisted bid tabs
				$bids_retracted = 1;
				
				$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('retracted', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('retracted', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				
				$producttabs = print_buying_activity_tabs('retracted', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
			}
			else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'awarded')
			{
				// #### define top header nav ##################################
				$topnavlink = array(
					'mycp',
					'buying_won'
				);
				
				// awarded bid tabs
				$bids_awarded = 1;
				
				$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('awarded', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('awarded', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				
				$producttabs = print_buying_activity_tabs('awarded', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
			}
			else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'invited')
			{
				// invited bid tabs
				$bids_invited = 1;
				
				$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('invited', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('invited', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				
				$producttabs = print_buying_activity_tabs('invited', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
			}
			else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'expired')
			{
				// expired bid tabs
				$bids_expired = 1;
				
				$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('expired', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('expired', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				
				$producttabs = print_buying_activity_tabs('expired', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
			}
			else
			{
				// active bid tabs
				$bids_active = 1;
				
				$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('active', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('active', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
				
				$producttabs = print_buying_activity_tabs('active', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
			}
	
			$counter = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
			$condition = $condition2 = '';
			
			$row_count2 = 0;
			$numberrows = $ilance->db->query($query['2'], 0, null, __FILE__, __LINE__);
			$number = $ilance->db->num_rows($numberrows);
			$result2 = $ilance->db->query($query['1'], 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result2) > 0)
			{
				while ($row2 = $ilance->db->fetch_array($result2, DB_ASSOC))
				{
					if (isset($bids_invited) AND $bids_invited)
					{
						$row2['bid_id'] = '-';
					}
					
					// disable auction due to expired or retracted or invited bid
					$row2['actions'] = (isset($bids_retracted) AND $bids_retracted OR isset($bids_expired) AND $bids_expired OR isset($bids_invited) AND $bids_invited)
						? '<input type="checkbox" name="bidid[]" value="' . $row2['bid_id'] . '" disabled="disabled" />'
						: '<input type="checkbox" name="bidid[]" value="' . $row2['bid_id'] . '" />';
	
					// live bid applet
					$row2['livebid'] = '';
					if ($row2['project_details'] == 'realtime')
					{
						$row2['livebid'] = '
<div id="applet' . $row2['project_id'] . '"></div>
<script language="javascript" type="text/javascript">
var fo = new FlashObject("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/' . DIR_SWF_NAME . '/livebid.swf", "applet' . $row2['project_id'] . '", "730", "530", "8,0,0,0", "#ffffff");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("flashvars", "languageConfig=' . DIR_FUNCT_NAME . '/' . DIR_XML_NAME . '/livebid_' . $_SESSION['ilancedata']['user']['slng'] . '.xml&prId=' . $row2['project_id'] . '&sId=' . session_id() . '&rand=' . rand(100000, 999999) . '");
fo.addParam("menu", "false");
fo.write("applet' . $row2['project_id'] . '");
</script>';
					}
	
					$row2['merchant'] = fetch_user('username', $row2['user_id']);
	
					// #### highest bid amounts ############
					$sql_highest = $ilance->db->query("
						SELECT MAX(bidamount) AS highest
						FROM " . DB_PREFIX . "project_bids
						WHERE project_id = '" . $row2['project_id'] . "'
							AND bidstate != 'retracted'
						ORDER BY highest
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_highest) > 0)
					{
						$res_highest = $ilance->db->fetch_array($sql_highest, DB_ASSOC);
						$row2['highest'] = $res_highest['highest'];
					}
					else
					{
						$row2['highest'] = '-';
					}
	
					$row2['icons'] = $ilance->auction->auction_icons($row2['project_id'], $row2['user_id']);
					$row2['price'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], (isset($highest) ? $highest : 0), $row2['currencyid']);
					$row2['item_title'] = stripslashes($row2['project_title']);
	
					// #### time left ######################
					if (isset($row2['date_starts']) AND $row2['date_starts'] > DATETIME24H)
					{
						$dif = $row2['starttime'];
						$ndays = floor($dif / 86400);
						$dif -= $ndays * 86400;
						$nhours = floor($dif / 3600);
						$dif -= $nhours * 3600;
						$nminutes = floor($dif / 60);
						$dif -= $nminutes * 60;
						$nseconds = $dif;
						$sign = '+';
						if ($row2['starttime'] < 0)
						{
							$row2['starttime'] = - $row2['starttime'];
							$sign = '-';
						}
						if ($sign != '-')
						{
							if ($ndays != '0')
							{
								$project_time_left = $ndays . $phrase['_d_shortform'].', ';
								$project_time_left .= $nhours . $phrase['_h_shortform'] . '+';
							}
							else if ($nhours != '0')
							{
								$project_time_left = $nhours . $phrase['_h_shortform'] . ', ';
								$project_time_left .= $nminutes . $phrase['_m_shortform'] . '+';
							}
							else
							{
								$project_time_left = $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
							}
	
						}
						
						$row2['timetostart'] = $project_time_left;
						$row2['timeleft'] = $phrase['_starts'] . ': ' . $row2['timetostart'];
					}
					else
					{
						$dif = $row2['mytime'];
						$ndays = floor($dif / 86400);
						$dif -= $ndays * 86400;
						$nhours = floor($dif / 3600);
						$dif -= $nhours * 3600;
						$nminutes = floor($dif / 60);
						$dif -= $nminutes * 60;
						$nseconds = $dif;
						if ($row2['mytime'] < 0)
						{
							$row2['mytime'] = - $row2['mytime'];
							$sign = '-';
						}
						else
						{
							$sign = '+';
						}
						
						if ($sign == '-')
						{
							$project_time_left = '<span class="gray">' . $phrase['_ended'] . '</span>';
							$expiredauction = 1;
						}
						else
						{
							$expiredauction = 0;
							
							if ($ndays != '0')
							{
								$project_time_left = $ndays . $phrase['_d_shortform'].', ';
								$project_time_left .= $nhours . $phrase['_h_shortform'] . '+';
							}
							else if ($nhours != '0')
							{
								$project_time_left = $nhours . $phrase['_h_shortform'] . ', ';
								$project_time_left .= $nminutes . $phrase['_m_shortform'] . '+';
							}
							else
							{
								$project_time_left = $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
							}
						}
						
						$row2['timeleft'] = $project_time_left;
					}
	
					// high bidder info
					$sql_highest_bidder = $ilance->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "project_bids
						WHERE project_id = '" . $row2['project_id'] . "'
						ORDER BY bidamount DESC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_highest_bidder) > 0)
					{
						$res_highest_bidder = $ilance->db->fetch_array($sql_highest_bidder, DB_ASSOC);
	
						// average, lowest and highest bid amounts
						$sel_bids_av = $ilance->db->query("
							SELECT AVG(bidamount) AS average, MIN(bidamount) AS lowest, MAX(bidamount) AS highest
							FROM " . DB_PREFIX . "project_bids
							WHERE project_id = '" . $row2['project_id'] . "'
								AND bidstate != 'retracted'
								AND bidstatus != 'declined'
							ORDER BY highest
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sel_bids_av) > 0)
						{
							$res_bids_av = $ilance->db->fetch_array($sel_bids_av);
							$row2['highbidderid'] = $res_highest_bidder['user_id'];
							$row2['highbidder'] = fetch_user('username', $row2['highbidderid']);
							
							$highbidderid = $row2['highbidderid'];
							$highbidamount = $res_bids_av['highest'];
						}
						else
						{
							$highbidamount = '0.00';
							$highbidder = $highbidderid = '';
						}
					}
					else
					{
						$highbidamount = '0.00';
						$highbidder = $highbidderid = '';
					}
					
					
					$row2['awarded'] = '';
					if ($highbidderid > 0 AND $_SESSION['ilancedata']['user']['userid'] == $highbidderid AND ($row2['status'] == 'expired' OR $row2['status'] == 'archived' OR $row2['status'] == 'finished'))
					{
						// #### has reserve price?
						if ($row2['reserve'])
						{
							// #### reserve price active
							if ($row2['reserve_price'] <= $highbidamount)
							{
								// #### reserve price has been met
								if ($expiredauction)
								{
									if ($bids_retracted OR $bids_expired)
									{
										//$row2['timeleft'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" />';
										$row2['timeleft'] = '<span class="gray">' . $phrase['_ended'] . '</span>';
									}
									else
									{
										$row2['awarded'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="' . $phrase['_winner'] . '" />';
										$row2['timeleft'] = '<span class="gray">' . $phrase['_ended'] . '</span>';
									}
								}
	
								// #### pmb
								$row2['pmb'] = ($bids_retracted OR $bids_expired OR $bids_invited)
									? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_gray.gif" border="0" alt="" /></div>'
									: $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
								
								// #### feedback
								if ($ilance->feedback->has_left_feedback($row2['user_id'], $_SESSION['ilancedata']['user']['userid'], $row2['project_id'], 'seller'))
								{
									$row2['feedback'] = '<div align="center"><span title="' . $phrase['_feedback_submitted__thank_you'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="' . $phrase['_feedback_submitted__thank_you'] . '" /></span></div>';
								}
								else
								{
									$row2['feedback'] = ($bids_retracted OR $bids_expired OR $bids_invited)
										? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_gray.gif" border="0" alt="" /></div>'
										: '<div align="center"><span title="' . $phrase['_submit_feedback_for'] . ' ' . fetch_user('username', $row2['user_id']) . '"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="' . $phrase['_submit_feedback_for'] . ' ' . fetch_user('username', $row2['user_id']) . '" /></a></span></div>';
								}
	
								// mediashare
								$row2['work'] = ($bids_retracted OR $bids_expired OR $bids_invited)
									? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_gray.gif" border="0" alt="" /></div>'
									: $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id'], $active = true);
							}
							// #### reserve price not met
							else
							{
								if ($expiredauction)
								{
									$row2['timeleft'] = ($bids_retracted OR $bids_expired)
										? '<span class="gray">' . $phrase['_ended'] . '</span>'
										: '<span class="gray">' . $phrase['_ended'] . '</span><div class="smaller gray">' . $phrase['_no_winner_reserve_not_met'] . '</div>';
										
									$row2['pmb'] = $row2['feedback'] = $row2['work'] = '-';
								}
							}
						}
						// #### no reserve price active
						else
						{
							if ($expiredauction)
							{
								if ($bids_retracted OR $bids_expired)
								{
									$row2['timeleft'] = '<span class="gray">' . $phrase['_ended'] . '</span>';
								}
								else
								{
									$row2['awarded'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="' . $phrase['_winner'] . '" />';
									$row2['timeleft'] = '<span class="gray">' . $phrase['_ended'] . '</span>';
								}
							}
	
							$row2['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
							
							// #### feedback experience with buyer
							if ($ilance->feedback->has_left_feedback($row2['user_id'], $_SESSION['ilancedata']['user']['userid'], $row2['project_id'], 'seller'))
							{
								// #### bidder already rated merchant!
								$row2['feedback'] = '<div align="center"><span title="' . $phrase['_feedback_submitted__thank_you'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="' . $phrase['_feedback_submitted__thank_you'] . '" /></span></div>';
							}
							else
							{
								// #### can we show feedback icon to leave feedback?
								$row2['feedback'] = ($bids_retracted OR $bids_expired OR $bids_invited)
									? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_gray.gif" border="0" alt="" /></div>'
									: '<div align="center"><span title="' . $phrase['_submit_feedback_for'] . ' ' . fetch_user('username', $row2['user_id']) . '"><a href="' . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="' . $phrase['_submit_feedback_for'] . ' ' . fetch_user('username', $row2['user_id']) . '" /></a></span></div>';
							}
	
							// ### can we show mediashare icon?
							$row2['work'] = ($bids_retracted OR $bids_expired OR $bids_invited)
								? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_gray.gif" border="0" alt="" /></div>'
								: $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id'], $active = true);
						}
					}
					else
					{
						// there is a high bidder but this user is not the winner
						$row2['pmb'] = $row2['feedback'] = $row2['work'] = '-';
					}
	
					// fetch highest bid amounts
					$sql_highest = $ilance->db->query("
						SELECT MAX(bidamount) AS highest
						FROM " . DB_PREFIX . "project_bids
						WHERE project_id = '" . $row2['project_id'] . "'
						ORDER BY highest
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_highest) > 0)
					{
						$res_highest = $ilance->db->fetch_array($sql_highest, DB_ASSOC);
						$row2['highest'] = $ilance->currency->format($res_highest['highest'], $row2['currencyid']);
					}
					else
					{
						$row2['highest'] = '-';
					}
					
					// #### viewing items i've won tab #####
					if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'awarded')
					{
						// #### populate shipping field actions
						if ($row2['ship_method'] == 'flatrate' OR $row2['ship_method'] == 'calculated')
						{
							$shippercount = print_shipping_methods($row2['project_id'], $row2['qty'], false, true);
							
							if ($row2['sellermarkedasshipped'] AND $row2['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
							{
								$row2['orderlocation'] = '<div class="smaller black">' . $phrase['_marked_as_shipped_on'] . ' <span class="blue">' . print_date($row2['sellermarkedasshippeddate']) . '</span></div>';
								$row2['orderlocation'] .= '<div style="padding-top:6px" class="smaller gray">' . $phrase['_download'] . ': <span class="blue">' . $phrase['_none'] . '</span></div>';
								$row2['shipping'] = $ilance->currency->format($row2['buyershipcost'], $row2['currencyid']);
								$row2['shipservice'] = '<span class="smaller blue" title="' . print_shipping_partner($row2['buyershipperid']) . '">' . shorten(print_shipping_partner($row2['buyershipperid']), 28) . '</span>';
							}
							else
							{
								// did buyer select a ship service yet?
								// he would have chosen a method when placing a bid.. unless only 1 service was available
								if ($row2['buyershipperid'] > 0)
								{
									$row2['orderlocation'] = '<span class="smaller black">' . $phrase['_waiting_for_merchant_to_update_order_as_delivered'] . '</span>';
									$shipperid = $row2['buyershipperid'];
									$shippingcosts = fetch_ship_cost_by_shipperid($row2['project_id'], $shipperid, $row2['qty']);
									$row2['buyershipcost'] = $shippingcosts['total'];
									
									if ($shippercount == 1)
									{
										$row2['shipservice'] = '<span class="smaller blue" title="' . print_shipping_partner($shipperid) . '">' . shorten(print_shipping_partner($shipperid), 28) . '</span>';
									}
									else
									{
										if ($shippercount > 1)
										{
											$row2['shipservice'] = '<span class="smaller blue" title="' . print_shipping_partner($shipperid) . '"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id='. $row2['project_id'] . '&amp;shipperid=' . $shipperid . '&amp;paymethod=' . $row2['buyerpaymethod'] . '&amp;returnurl={pageurl_urlencoded}" style="text-decoration:underline">' . shorten(print_shipping_partner($shipperid), 28) . '</a></span>';	
										}
									}
									
									$row2['shipping'] = $ilance->currency->format($shippingcosts['total'], $row2['currencyid']);
									unset($shipperid, $shippingcosts);
								}
								else
								{
									if ($shippercount == 1)
									{
										$row2['orderlocation'] = '<span class="smaller black">' . $phrase['_waiting_for_merchant_to_update_order_as_delivered'] . '</span>';
										print_shipping_methods($row2['project_id'], $row2['qty'], false, false);
										$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . $row2['project_id'] . "'", "ship_service_$shipperidrow");
										$shippingcosts = fetch_ship_cost_by_shipperid($row2['project_id'], $shipperid, $row2['qty']);
										$row2['buyershipcost'] = $shippingcosts['total'];
										$row2['shipservice'] = '<span class="smaller blue" title="' . print_shipping_partner($shipperid) . '">' . shorten(print_shipping_partner($shipperid), 28) . '</span>';
										$row2['shipping'] = $ilance->currency->format($shippingcosts['total'], $row2['currencyid']);
										unset($shipperid, $shippingcosts);
									}
									else
									{
										if ($shippercount > 1)
										{
											$row2['orderlocation'] = '<div class="smaller black">' . $phrase['_seller_cannot_ship_yet'] . '</div><div><span class="smaller blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id='. $row2['project_id'] . '&amp;shipperid=" style="text-decoration:underline">' . $phrase['_choose_shipping_service'] . '</a></span></div>';
											$row2['shipservice'] = '<span class="smaller blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id='. $row2['project_id'] . '&amp;shipperid=" style="text-decoration:underline">' . $phrase['_choose'] . '...</a></span>';
											$row2['shipping'] = $row2['shipservice'];
											$row2['buyershipcost'] = 0;
										}
									}
								}
							}
						}
						
						// #### local pickup only
						else
						{
							$row2['orderlocation'] = $phrase['_local_pickup_only'];
							$row2['shipping'] = '-';
							$row2['shipservice'] = $phrase['_local_pickup'];
						}
						
						$methodscount = print_payment_methods($row2['project_id'], false, true);
						
						// #### single payment method offered by seller
						if ($methodscount == 1)
						{
							$row2['buyerpaymethod'] = print_payment_method_title($row2['project_id']);
							
							// #### gateway ########
							if (strchr($row2['buyerpaymethod'], 'gateway'))
							{
								$row2['payment'] = ($row2['winnermarkedaspaid'] == '0')
									? '<span class="gray">' . $phrase['_next'] . ':</span> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . $row2['project_id'] . '" style="text-decoration:underline"><strong>' . $phrase['_pay_now'] . '</strong></a></span>'
									: '<div class="smaller black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($row2['winnermarkedaspaiddate']) . '</span> ' . $phrase['_using'] . ' <span class="blue">' . print_fixed_payment_method($row2['buyerpaymethod'], false) . '</span></div>';	
							}
							
							// #### offline ########
							else if (strchr($row2['buyerpaymethod'], 'offline'))
							{
								if ($row2['winnermarkedaspaid'] == '0')
								{
									$crypted = array(
										'cmd' => 'management',
										'subcmd' => 'markaspaid',
										'pid' => $row2['project_id'],
										'bid' => $row2['bid_id']
									);
									
									$row2['payment'] = '<span class="gray"><span style="float:left; padding-right:6px">' . $phrase['_next'] . ':</span> <span class="blue"><a href="javascript:;" onclick="return show_prompt_payment(\'' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;bidsub=' . $ilance->GPC['bidsub'] . '&amp;page2=' . $ilance->GPC['page2'] . '\')" style="text-decoration:underline"><strong>' . $phrase['_mark_paid_manually'] . '</strong></a></span>';
									unset($crypted);
								}
								else
								{
									$crypted = array(
										'cmd' => 'management',
										'subcmd' => 'markasunpaid',
										'pid' => $row2['project_id'],
										'bid' => $row2['bid_id']
									);
									
									$row2['payment'] = '<div class="smaller black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($row2['winnermarkedaspaiddate']) . '</span> ' . $phrase['_using'] . ' <span class="blue">' . handle_input_keywords($row2['winnermarkedaspaidmethod']) . '</span></div>';
									
									// before we let the "buyer" mark as "unpaid" we should check when he last "marked as paid" and if it's
									// like more than 7 days we do not allow the buyer to play god with payment status avoiding future confusion
									// to the seller (who already would have received the funds).. this is just a quick hack to prevent
									// abuse to payment details when the admin is finally reviewing details from the admincp
									$date1split = explode(' ', $row2['winnermarkedaspaiddate']);
									$date2split = explode('-', $date1split[0]);
									$totaldays = 14;
									$elapsed = $ilance->datetime->fetch_days_between($date2split[1], $date2split[2], $date2split[0], gmdate('m'), gmdate('d'), gmdate('Y'));
									$daysleft = ($totaldays - $elapsed);
									if ($elapsed <= 14)
									{
										$row2['payment'] .= '<div class="smaller blue" style="padding-top:7px"><span class="gray">' . $phrase['_optional'] . ':</span> <span title="(' . $daysleft . ' ' . $phrase['_days_left'] . ')"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;bidsub=' . $ilance->GPC['bidsub'] . '&amp;page=' . intval($ilance->GPC['page2']) . '" onclick="return confirm_js(\'' . $phrase['_you_are_about_to_change_the_status_for_the_payment_on_this_item_to_unpaid'] . '\')" style="text-decoration:underline">' . $phrase['_mark_as_unpaid'] . '</a></span></div>';
									}
									unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
								}	
							}
							
							// #### escrow #########
							else if ($row2['buyerpaymethod'] == 'escrow')
							{
								$row2['payment'] = '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow" style="text-decoration:underline">' . print_fixed_payment_method($row2['buyerpaymethod'], false) . '</a></span>';
							}
						}
						// #### multiple payment methods offered by seller
						else
						{
							if (!empty($row2['buyerpaymethod']))
							{
								$row2['paymethod'] = print_fixed_payment_method($row2['buyerpaymethod'], false);
								
								//$row2['payment'] = print_fixed_payment_method($row2['buyerpaymethod'], false);
								// #### gateway ########
								if (strchr($row2['buyerpaymethod'], 'gateway'))
								{
									$row2['payment'] = ($row2['winnermarkedaspaid'] == '0')
										? '<span class="gray">' . $phrase['_next'] . ':</span> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . $row2['project_id'] . '" style="text-decoration:underline"><strong>' . $phrase['_pay_now'] . '</strong></a></span>'
										: '<div class="smaller black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($row2['winnermarkedaspaiddate']) . '</span> ' . $phrase['_using'] . ' <span class="blue">' . $row2['paymethod'] . '</span></div>';	
								}
								
								// #### offline ########
								else if (strchr($row2['buyerpaymethod'], 'offline'))
								{
									if ($row2['winnermarkedaspaid'] == '0')
									{
										$crypted = array(
											'cmd' => 'management',
											'subcmd' => 'markaspaid',
											'pid' => $row2['project_id'],
											'bid' => $row2['bid_id']
										);
										
										$row2['payment'] = '<span class="gray"><span style="float:left; padding-right:6px">' . $phrase['_next'] . ':</span> <span class="blue"><a href="javascript:void(0)" onclick="return show_prompt_payment(\'' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;bidsub=' . $ilance->GPC['bidsub'] . '&amp;page2=' . $ilance->GPC['page2'] . '\')" style="text-decoration:underline"><strong>' . $phrase['_mark_paid_manually'] . '</strong></a></span>';
										unset($crypted);
									}
									else
									{
										$crypted = array(
											'cmd' => 'management',
											'subcmd' => 'markasunpaid',
											'pid' => $row2['project_id'],
											'bid' => $row2['bid_id']
										);
										
										$row2['payment'] = '<div class="smaller black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($row2['winnermarkedaspaiddate']) . '</span> ' . $phrase['_via'] . ' <span class="blue">' . $row2['winnermarkedaspaidmethod'] . '</span></div>';
										
										// before we let the "buyer" mark as "unpaid" we should check when he last "marked as paid" and if it's
										// like more than 7 days we do not allow the buyer to play god with payment status avoiding future confusion
										// to the seller (who already would have received the funds).. this is just a quick hack to prevent
										// abuse to payment details when the admin is finally reviewing details from the admincp
										$date1split = explode(' ', $row2['winnermarkedaspaiddate']);
										$date2split = explode('-', $date1split[0]);
										$totaldays = 14;
										$elapsed = $ilance->datetime->fetch_days_between($date2split[1], $date2split[2], $date2split[0], gmdate('m'), gmdate('d'), gmdate('Y'));
										$daysleft = ($totaldays - $elapsed);
										if ($elapsed <= 14)
										{
											$row2['payment'] .= '<div class="smaller blue" style="padding-top:7px"><span class="gray">' . $phrase['_optional'] . ':</span> <span title="(' . $daysleft . ' ' . $phrase['_days_left'] . ')"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;bidsub=' . $ilance->GPC['bidsub'] . '&amp;page2=' . intval($ilance->GPC['page2']) . '" onclick="return confirm_js(\'' . $phrase['_you_are_about_to_change_the_status_for_the_payment_on_this_item_to_unpaid'] . '\')" style="text-decoration:underline">' . $phrase['_mark_as_unpaid'] . '</a></span></div>';
										}
										unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
									}	
								}
								
								// #### escrow #########
								else if ($row2['buyerpaymethod'] == 'escrow')
								{
									$row2['payment'] = '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow" style="text-decoration:underline">' . print_fixed_payment_method($row2['buyerpaymethod'], false) . '</a></span>';
								}
							}
							else
							{
								$row2['payment'] = '<span class="smaller blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;paymethod=" style="text-decoration:underline">' . $phrase['_choose_payment_method'] . '</a></span>';
							}
						}
					}
					
					// #### all other tabs #################
					else
					{
						$row2['orderlocation'] = $row2['payment'] = $row2['shipping'] = $row2['shipservice'] = '-';
					}
					
					$row2['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $row2['project_id'], 'thumb', $row2['project_id']);
					$row2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$row2['total'] = $ilance->currency->format(($row2['bidamount'] + $row2['buyershipcost']), $row2['currencyid']);
					$row2['bidamount'] = $ilance->currency->format($row2['bidamount'], $row2['currencyid']);
					
					// #### buyers payment method selector
					$methodscount = print_payment_methods($row2['project_id'], false, true);
					if (empty($row2['buyerpaymethod']))
					{
						$row2['paymethod'] = ($methodscount == 1)
							? '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;shipperid=' . $row2['buyershipperid'] . '" style="text-decoration: underline">' . print_fixed_payment_method(print_payment_method_title($row2['project_id']), false) . '</a></span>'
							: '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;shipperid=' . $row2['buyershipperid'] . '" style="text-decoration: underline">' . $phrase['_choose'] . '...</a></span>';
					}
					else
					{
						if ($row2['buyerpaymethod'] == 'escrow')
						{
							$row2['paymethod'] = $phrase['_escrow'];	
						}
						else
						{
							if ($methodscount == 1)
							{
								$row2['paymethod'] = '<span class="blue">' . print_fixed_payment_method($row2['buyerpaymethod'], false) . '</span>';
							}
							else
							{
								$row2['paymethod'] = ($row2['winnermarkedaspaid'] == '0')
									? '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;paymethod=' . $row2['buyerpaymethod'] . '&amp;shipperid=' . $row2['buyershipperid'] . '&amp;returnurl={pageurl_urlencoded}" style="text-decoration: underline">' . print_fixed_payment_method($row2['buyerpaymethod'], false) . '</a></span>'
									: '<span class="blue">' . print_fixed_payment_method($row2['buyerpaymethod'], false) . '</span>';
							}
						}
					}
					
					$show['no_product_bidding_activity'] = false;
					$show['bidpulldownmenu'] = true;
					$GLOBALS['show_realtime_bid' . $row2['project_id']] = ($row2['project_details'] == 'realtime') ? 1 : 0;
					$product_bidding_activity[] = $row2;
					$row_count2++;
				}
			}
			else
			{
				$show['no_product_bidding_activity'] = true;
				$show['bidpulldownmenu'] = false;
			}        
		}
		
		$sub = isset($ilance->GPC['sub']) ? $ilance->GPC['sub'] : '';
		$bidsub = isset($ilance->GPC['bidsub']) ? $ilance->GPC['bidsub'] : '';

		$prevnext2 = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page2']), $counter, $scriptpage2, 'page2');
	}
	
	$pprint_array = array('php_self2','sub','bidsub','servicetabs','producttabs','activebids','awardedbids','archivedbids','invitedbids','expiredbids','retractedbids','productescrow','buynowproductescrow','activerfps','draftrfps','archivedrfps','delistedrfps','pendingrfps','serviceescrow','highbidder','highbidderid','highest','php_self','searchquery','p_id','rfpescrow','rfpvisible','countdelisted','prevnext','prevnext2','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
	$ilance->template->fetch('main', 'buying_activity.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('service_buying_activity','product_bidding_activity','product_unique_bidding_activity'));
	if (!isset($service_buying_activity))
	{
		$service_buying_activity = array();
	}
	@reset($service_buying_activity);
	while ($i = @each($service_buying_activity))
	{
		$ilance->template->parse_loop('main', 'invitationlist' . $i['value']['project_id']);
		$ilance->template->parse_loop('main', 'service_buying_bidding_activity' . $i['value']['project_id']);
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