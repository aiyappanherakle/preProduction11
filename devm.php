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
        'rfp',
        'search',
        'accounting',
        'buying',
        'selling',
        'subscription',
        'feedback'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'jquery',
        'modal',
	'flashfix'
);
// #### define top header nav ##################################################
$topnavlink = array(
	'main_listings'
);
// #### setup script location ##################################################
define('LOCATION', 'merch');
// #### require backend ########################################################
require_once('./functions/config.php');
// #### require shipping backend #######################
require_once(DIR_CORE . 'functions_shipping.php');
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_search_prefs.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[merch]" => $ilcrumbs["$ilpage[merch]"]);
// #### decrypt our encrypted url ##############################################
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
// #### HANDLE SELLER TOOLS FROM LISTING PAGE ##################################
/*echo '<pre>';
 print_r ($_GET);
 
 echo '<pre>';
print_r($_SESSION);*/
 $ilance->auction = construct_object('api.auction');
	
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'sellertools' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'enhancements' AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0)
{
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
        {
                refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['merch'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
                exit();
        }
	           
	        
	// #### HANDLE AUCTION LISTING ENHANCEMENTS ####################
	// this will attempt to debit the acocunt of the users account balance if possible
	$ilance->GPC['featured'] = $ilance->GPC['old']['featured'];
	$ilance->GPC['highlite'] = $ilance->GPC['old']['highlite'];
	$ilance->GPC['bold'] = $ilance->GPC['old']['bold'];
	$ilance->GPC['autorelist'] = $ilance->GPC['old']['autorelist'];
	$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
	if (is_array($ilance->GPC['enhancements']))
	{
		$ilance->auction = construct_object('api.auction'); 
		$ilance->auction_rfp = construct_object('api.auction_rfp');
		$enhance = $ilance->auction_rfp->process_listing_enhancements_transaction($ilance->GPC['enhancements'], $_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['pid']), 'update', 'product');
		if (is_array($enhance))
		{
			$ilance->GPC['featured'] = (int)$enhance['featured'];
			$ilance->GPC['highlite'] = (int)$enhance['highlite'];
			$ilance->GPC['bold'] = (int)$enhance['bold'];
			$ilance->GPC['autorelist'] = (int)$enhance['autorelist'];
			$ilance->GPC['featured_date'] = ($ilance->GPC['featured'] AND isset($ilance->GPC['old']['featured_date']) AND $ilance->GPC['old']['featured_date'] == '0000-00-00 00:00:00') ? DATETIME24H : '0000-00-00 00:00:00';
		}
		
		// #### update auction #########################################
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects 
			SET featured = '" . intval($ilance->GPC['featured']) . "',
			featured_date = '" . $ilance->db->escape_string($ilance->GPC['featured_date']) . "',
			highlite = '" . intval($ilance->GPC['highlite']) . "',
			bold = '" . intval($ilance->GPC['bold']) . "',
			autorelist = '" . intval($ilance->GPC['autorelist']) . "'
			WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	
	print_notice($phrase['_listing_updated'], $phrase['_the_options_you_selected_have_been_completed_successfully'], HTTP_SERVER . $ilpage['merch'] . '?id=' . $ilance->GPC['pid'], $phrase['_return_to_listing']);
	exit();
}
// #### HANDLE DIRECT PAYMENT FROM BUYER TO SELLER #############################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'directpay' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	// #### are we logged in ? #############################################
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
        {
		$area_title = $phrase['_access_denied'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
	
                refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['merch'] . print_hidden_fields(true, array(), true)));
                exit();
        }
	
	// #### load backend ###################################################
	$ilance->feedback = construct_object('api.feedback');
	$ilance->accounting = construct_object('api.accounting');
	$ilance->accounting_fees = construct_object('api.accounting_fees');
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
	$ilance->email = construct_dm_object('email', $ilance);
	
	$ilance->GPC['orderid'] = ((isset($ilance->GPC['orderid']) AND $ilance->GPC['orderid'] > 0)
		? intval($ilance->GPC['orderid'])
		: 0);
		
	$ilance->GPC['sellerid'] = fetch_auction('user_id', intval($ilance->GPC['id']));
	
	if (isset($ilance->GPC['sellerid']) AND $ilance->GPC['sellerid'] > 0 AND $ilance->GPC['sellerid'] == $_SESSION['ilancedata']['user']['userid'])
	{
		$area_title = $phrase['_access_denied'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
	
		print_notice($area_title, $phrase['_it_appears_you_are_the_seller_of_this_listing_in_this_case_you_cannot_bid_or_purchase_items_from_your_own_listing'], 'javascript:history.back(1);', $phrase['_back']);
		exit();
	}
	
	// #### direct pay handler default for ipn challenge response ##########
        $customencrypted = 'ITEMWIN|' . intval($ilance->GPC['id']);
	
	// #### winning bid amount #############################################
	$total = $ilance->bid->fetch_auction_win_amount(intval($ilance->GPC['id']), $ilance->GPC['sellerid'], $_SESSION['ilancedata']['user']['userid']);
	
	// #### listing details ################################################
        $itemid = intval($ilance->GPC['id']);
        $title = fetch_auction('project_title', intval($ilance->GPC['id']));
        $sample = print_item_photo($ilpage['merch'] . '?id=' . $itemid, 'thumb', $itemid);
	$qty = isset($ilance->GPC['qty']) ? intval($ilance->GPC['qty']) : 1;
	$currencyid = fetch_auction('currencyid', intval($ilance->GPC['id']));
	$show['lub'] = false;
	
	// populate $show['multipleorders']
	$orderidradios = print_orderid_methods($itemid, $_SESSION['ilancedata']['user']['userid'], $ilance->GPC['orderid']);
	
	$hiddenfields = '';
	
	// #### set default url to send ipn handler ############################
	$returnurl = HTTP_SERVER . $ilpage['buying'] . '?cmd=management&bidsub=awarded';
	
	// #### build nav crumb ################################################
        $navcrumb = array();
        $navcrumb[HTTP_SERVER . "$ilpage[buying]?cmd=management&bidsub=awarded"] = $phrase['_buying_activity'];
        $navcrumb[HTTP_SERVER . "$ilpage[merch]?id=" . intval($ilance->GPC['id'])] = $title;
        $navcrumb[""] = $phrase['_complete_your_payment_to_seller'];
	
	// #### buy now order details for this payment to seller ###############
	if ($ilance->GPC['orderid'] > 0)
	{
		// if this item is lowest unique bid,
		$hiddenfields = '<input type="hidden" name="cmd" value="purchase-confirm" /><input type="hidden" name="pid" value="' . $itemid . '" /><input type="hidden" name="qty" value="' . $qty . '" /><input type="hidden" name="orderid" value="' . intval($ilance->GPC['orderid']) . '" />';
		// #### direct pay gateway ipn response challenge ##############
		$customencrypted = 'BUYNOW|' . intval($ilance->GPC['orderid']) . '|' . intval($ilance->GPC['id']);
		
		// #### set default url to send ipn handler ############################
		$returnurl = HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=buynow-escrow';
		
		// #### item buy now price (including shipping of currently selected shipper)
		//$total = $ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "orderid = '" . intval($ilance->GPC['orderid']) . "'", "amount");
		
		$sql = $ilance->db->query("
			SELECT status, buyerpaymethod, buyershipcost, buyershipperid
			FROM " . DB_PREFIX . "buynow_orders
			WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
				AND project_id = '" . intval($ilance->GPC['id']) . "'
				AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			
			$buyerpaymethod = $res['buyerpaymethod'];
			if (empty($ilance->GPC['paymethod']) AND !empty($res['buyerpaymethod']))
			{
				$ilance->GPC['paymethod'] = $res['buyerpaymethod'];									     			
			}
			
			if ($res['status'] != 'offline')
			{
				$area_title = $phrase['_your_order_for_this_purchase_is_complete'];
				$page_title = SITE_NAME . ' - ' . $phrase['_your_order_for_this_purchase_is_complete'];
		
				print_notice($phrase['_your_order_for_this_purchase_is_complete'], $phrase['_the_order_id_for_this_purchase_has_been_completed'], "javascript: history.go(-1)", $phrase['_back']);
				exit();        
			}
		}
		
		// #### no buy now order information found #############################
		else
		{
			$area_title = $phrase['_your_order_for_this_purchase_is_complete'];
			$page_title = SITE_NAME . ' - ' . $phrase['_your_order_for_this_purchase_is_complete'];
				
			print_notice($phrase['_your_order_for_this_purchase_is_complete'], $phrase['_the_order_id_for_this_purchase_has_been_completed'], "javascript: history.go(-1)", $phrase['_back']);
			exit();        
		}
		
		// #### build nav crumb ################################################
		$navcrumb = array();
		$navcrumb[HTTP_SERVER . "$ilpage[buying]?cmd=management"] = $phrase['_buying_activity'];
		$navcrumb[HTTPS_SERVER . "$ilpage[escrow]?cmd=management&bidsub=buynow-escrow"] = $phrase['_buy_now_manager'];
		$navcrumb[HTTP_SERVER . "$ilpage[merch]?id=" . intval($ilance->GPC['id'])] = $title;
		$navcrumb[""] = $phrase['_complete_your_payment_to_seller'];
		
		$buyershipperid = 0;
		if (!empty($ilance->GPC['paymethod']))
		{
			$buyershipperid = ((isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] > 0)
				? intval($ilance->GPC['shipperid'])
				: 0);
			
			if ($buyershipperid == 0 AND $res['buyershipperid'] > 0)
			{
				$buyershipperid = $res['buyershipperid'];
			}
			
			$buyershipcost = fetch_ship_cost_by_shipperid($ilance->GPC['id'], $buyershipperid, $qty);
			$newtotal = (($total * $qty) + $buyershipcost['total']);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
				buyershipcost = '" . sprintf("%01.2f", $buyershipcost['total']) . "',
				buyershipperid = '" . intval($buyershipperid) . "',
				amount = '" . sprintf("%01.2f", $newtotal) . "'
				WHERE project_id = '" . intval($ilance->GPC['id']) . "'
					AND orderid = '" . intval($ilance->GPC['orderid']) . "'
			", 0, null, __FILE__, __LINE__);
			
			if ($buyershipperid > 0 AND $buyershipcost['total'] > 0)
			{
				$total = $newtotal;
			}
		}
		
		$buyerpaymethod = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : '';
		if (empty($buyerpaymethod) AND $buyershipperid == 0)
		{
			refresh(HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&subcmd=choose&id=' . intval($ilance->GPC['id']));
			exit();	
		}
		
		if ($buyerpaymethod == 'escrow')
		{
			refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=product-escrow');
			exit();	
		}
		else
		{
			if (strchr($buyerpaymethod, 'offline'))
			{
				refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=buynow-escrow');
				exit();
			}
		}
	}
	
	// #### winning bid details for this payment to seller #################
	else
	{
		$orderids = array();
		$pid = intval($ilance->GPC['id']);
		$methodscount = print_payment_methods($pid, false, true);
		$shippercount = print_shipping_methods($pid, $qty, false, true);
		
		$hiddenfields = '<input type="hidden" name="cmd" value="directpay" /><input type="hidden" name="id" value="' . $pid . '" />';
		$hiddenfields .= ((isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
			? '<input type="hidden" name="returnurl" value="' . urlencode($ilance->GPC['returnurl']) . '" />'
			: '<input type="hidden" name="returnurl" value="' . urlencode($returnurl) . '" />');
		// #### check if our bid exists within the bids table ##########
		$sql = $ilance->db->query("
			SELECT b.bid_id, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.bidamount, s.ship_handlingtime, s.ship_method
			FROM " . DB_PREFIX . "project_bids b
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON b.project_id = s.project_id
			WHERE b.state = 'product'
				AND b.bidstatus = 'awarded'
				AND b.project_id = '" . $pid . "'
				AND b.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND b.project_user_id = '" . intval($ilance->GPC['sellerid']) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			
			$show['localpickuponly'] = ($res['ship_method'] == 'localpickup') ? true : false;
			$buyerpaymethod = $res['buyerpaymethod'];
			if (!empty($buyerpaymethod) AND !isset($ilance->GPC['paymethod']))
			{
				$ilance->GPC['paymethod'] = $buyerpaymethod;
			}
			
			if ($res['buyershipperid'] > 0 AND $res['buyershipcost'] > 0)
			{
				$total = ($total + $res['buyershipcost']);
			}
			if (!empty($ilance->GPC['paymethod']))
			{
				$buyerpaymethod = $ilance->GPC['paymethod'];
				$buyershipperid = ((isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] > 0)
					? intval($ilance->GPC['shipperid'])
					: 0);
				
				if ($buyershipperid == 0 AND $res['buyershipperid'] > 0)
				{
					$buyershipperid = $res['buyershipperid'];
				}
				
				$buyershipcost = fetch_ship_cost_by_shipperid($pid, $buyershipperid, $qty);
				
				if ($buyerpaymethod == 'escrow')
				{
					// #### check if escrow account was already created
					$sql = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "projects_escrow
						WHERE project_id = '" . $pid . "'
							AND bid_id = '" . $res['bid_id'] . "'
					");
					if ($ilance->db->num_rows($sql) == 0)
					{
						// #### do shipping fees apply?
						$highestbid = $res['bidamount'];
						$totalescrowamount = $highestbid;
						$shippinginformation = $ilance->currency->format($res['buyershipcost'], $currencyid);
						
						// #### create new item escrow account for this winning bidder
						$fee = fetch_merchant_escrow_fee_plus_tax($ilance->GPC['sellerid'], $totalescrowamount);
						$fee2 = fetch_product_bidder_escrow_fee_plus_tax($_SESSION['ilancedata']['user']['userid'], $totalescrowamount);
	    
						// amount to forward plus the merchant fee to fund escrow (including any taxes if applicable)
						$totalescrowamount = ($totalescrowamount + $res['buyershipcost']);
						
						// #### create escrow invoice #####################################
						$escrow_invoice_id = $ilance->accounting->insert_transaction(
							0,
							$pid,
							0,
							$_SESSION['ilancedata']['user']['userid'],
							0,
							0,
							0,
							$phrase['_escrow_payment_forward'] . ': ' . $phrase['_item_id'] . ' #' . $pid . ': ' . $title,
							sprintf("%01.2f", $totalescrowamount),
							'',
							'unpaid',
							'escrow',
							'account',
							DATETIME24H,
							DATEINVOICEDUE,
							'',
							$phrase['_additional_shipping_fees'] . ': ' . $shippinginformation,
							0,
							0,
							1
						);
						
						// create product escrow account
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "projects_escrow
							(escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, bidamount, shipping, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, fee2invoiceid, status)
							VALUES(
							NULL,
							'" . $res['bid_id'] . "',
							'" . $pid . "',
							'" . $escrow_invoice_id . "',
							'" . intval($ilance->GPC['sellerid']) . "',
							'" . $_SESSION['ilancedata']['user']['userid'] . "',
							'" . DATETIME24H . "',
							'" . sprintf("%01.2f", $highestbid) . "',
							'" . sprintf("%01.2f", $res['buyershipcost']) . "',
							'" . sprintf("%01.2f", $totalescrowamount) . "',
							'" . sprintf("%01.2f", $fee) . "',
							'" . sprintf("%01.2f", $fee2) . "',
							'0',
							'0',
							'0',
							'0',
							'pending')
						", 0, null, __FILE__, __LINE__);
						
						$escrow_id = $ilance->db->insert_id();
		
						// associate escrow to listing
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET escrow_id = '" . $escrow_id . "',
							haswinner = '1',
							winner_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							WHERE project_id = '" . $pid . "'
						", 0, null, __FILE__, __LINE__);
						
						// track products purchased
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "users
							SET productawards = productawards + 1
							WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						");
						
						// track products sold
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "users
							SET productsold = productsold + 1
							WHERE user_id = '" . intval($ilance->GPC['sellerid']) . "'
						");
						
						// #### update winning bidders default pay method to escrow
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "project_bids
							SET buyerpaymethod = 'escrow',
							winnermarkedaspaidmethod = '" . $ilance->db->escape_string($phrase['_escrow']) . "'
							WHERE bid_id = '" . $res['bid_id'] . "'
							    AND project_id = '" . $pid . "'
						", 0, null, __FILE__, __LINE__);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "project_realtimebids
							SET buyerpaymethod = 'escrow',
							winnermarkedaspaidmethod = '" . $ilance->db->escape_string($phrase['_escrow']) . "'
							WHERE bid_id = '" . $res['bid_id'] . "'
							    AND project_id = '" . $pid . "'
						", 0, null, __FILE__, __LINE__);
						
						$existing = array(
							'{{project_title}}' => $title,
							'{{project_id}}' => $res_rfp['project_id'],
							'{{owner}}' => fetch_user('username', $ilance->GPC['sellerid']),
							'{{owneremail}}' => fetch_user('email', $ilance->GPC['sellerid']),
							'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $pid,
							'{{bidamount}}' => $ilance->currency->format($bidamount, $currencyid),
							'{{shippingcost}}' => $ilance->currency->format($res['buyershipcost'], $currencyid),
							'{{shippingservice}}' => print_shipping_partner($res['buyershipperid']),
							'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
							'{{totalamount}}' => $ilance->currency->format($totalescrowamount, $currencyid),
							'{{winningbidder}}' => $_SESSION['ilancedata']['user']['username'],
							'{{winningbidderemail}}' => $_SESSION['ilancedata']['user']['email'],
							'{{paymethod}}' => SITE_NAME . ' ' . $phrase['_escrow'],
							'{{buyerfee}}' => $ilance->currency->format($fee2),
							'{{sellerfee}}' => $ilance->currency->format($fee),						
						);
						
						// email owner
						$ilance->email->mail = fetch_user('email', $ilance->GPC['sellerid']);
						$ilance->email->slng = fetch_user_slng($ilance->GPC['sellerid']);
						$ilance->email->get('product_auction_expired_via_cron_owner');		
						$ilance->email->set($existing);
						// murugan changes on Mar 07 disable email
						//$ilance->email->send();
						
						// email winning bidder
						$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
						$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
						$ilance->email->get('product_auction_expired_via_cron_winner');		
						$ilance->email->set($existing);
						$ilance->email->send();
						
						// email admin
						$ilance->email->mail = SITE_EMAIL;
						$ilance->email->slng = fetch_site_slng();
						$ilance->email->get('product_auction_expired_via_cron_admin');		
						$ilance->email->set($existing);
						$ilance->email->send();	
					}
					
					// #### winning bidder redirect to item escrow activity
					refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=product-escrow');
					exit();	
				}
				else
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "project_bids
						SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
						buyershipcost = '" . sprintf("%01.2f", $buyershipcost['total']) . "',
						buyershipperid = '" . intval($buyershipperid) . "'
						WHERE bid_id = '" . $res['bid_id'] . "'
						LIMIT 1
					");
					
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "project_realtimebids
						SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
						buyershipcost = '" . sprintf("%01.2f", $buyershipcost['total']) . "',
						buyershipperid = '" . intval($buyershipperid) . "'
						WHERE bid_id = '" . $res['bid_id'] . "'
						LIMIT 1
					");
					
					// #### winning bidder redirected to i've won items activity menu
					if ($methodscount == 1 AND $shippercount == 1)
					{
						if (strchr($ilance->GPC['paymethod'], 'offline'))
						{
							refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&bidsub=awarded');
							exit();
						}
					}
				}
			}
			
			if (empty($res['buyerpaymethod']))
			{
				if ($methodscount == 1)
				{
					$ilance->GPC['paymethod'] = print_payment_method_title($pid);
					$hiddenfields .= '<input type="hidden" name="paymethod" value="' . handle_input_keywords($ilance->GPC['paymethod']) . '" />';
					
					if (strchr($ilance->GPC['paymethod'], 'offline') OR strchr($ilance->GPC['paymethod'], 'gateway'))
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "project_bids
							SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "'
							WHERE project_id = '" . $pid . "'
								AND bid_id = '" . $res['bid_id'] . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND project_user_id = '" . $ilance->GPC['sellerid'] . "'
						");
						
						if (strchr($ilance->GPC['paymethod'], 'offline'))
						{
							refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&bidsub=awarded');
							exit();
						}
					}
					else if ($ilance->GPC['paymethod'] == 'escrow')
					{
						// #### winning bidder redirect to item escrow activity
						refresh(HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=product-escrow');
						exit();	
					}
				}
				
				else if ($methodscount > 1)
				{
					$area_title = $phrase['_confirm_payment_method'];
					$page_title = SITE_NAME . ' - ' . $phrase['_confirm_payment_method'];
					
					$navcrumb = array();
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['listingsidentifier'])] = $phrase['_buy'];
					}
					else
					{
						$navcrumb["$ilpage[merch]?cmd=listings"] = $phrase['_buy'];
					}
					$navcrumb["$ilpage[merch]?id=" . $pid] = fetch_auction('project_title', $pid);
					$navcrumb[""] = $phrase['_confirm_payment_method'];
					
					// #### radio input for buyers payment decision ################
					$paymethodsradios = print_payment_methods($pid, true);
					
					// #### radio input for buyers payment decision ################
					$shippingradios = print_shipping_methods($pid, $qty, true);
					$shippingradioscount = print_shipping_methods($pid, $qty, false, true);
					$shippingservice = '';
					$shipperid = 0;
					$days = 3;	
					print_shipping_methods($pid, $qty, false, false);
					if ($shippingradioscount == 1 AND $shipperidrow > 0)
					{
						$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '$pid'", "ship_service_$shipperidrow");
						$shippingservice = print_shipping_partner($shipperid);
						$days = $res['ship_handlingtime'];
					}
					
					$pprint_array = array('onsubmit','shipperid','days','shippingservice','shippingradios','hiddenfields','pid','qty','paymethodsradios','paymethods','returnurl','tax','paymethod','fees','digitalfile','cb_shipping_address_required1','cb_shipping_address_required0','encrypted','samount','amount_formatted','total','shipping_address_pulldown','forceredirect','payment_method_pulldown','attachment','project_id','seller_id','buyer_id','user_cookie','project_title','seller','qty','topay','amount','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','category','subcategory');
				
					($apihook = $ilance->api('listing_payment_selection_end')) ? eval($apihook) : false;
					
					$ilance->template->fetch('main', 'listing_payment_selection.html');
					$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
					$ilance->template->parse_loop('main', 'paymentoptions');
					$ilance->template->parse_if_blocks('main');
					$ilance->template->pprint('main', $pprint_array);
					exit();	
				}
			}
			else
			{
				if ($res['buyerpaymethod'] == 'escrow')
				{
					refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=product-escrow');
					exit();	
				}
				else
				{
					if ($methodscount == 1 AND $shippercount == 1)
					{
						if (strchr($res['buyerpaymethod'], 'offline'))
						{
							refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&bidsub=awarded');
							exit();
						}
					}
				}
			}
		}
		
		// #### check if our purchase was made via buy now #############
		else
		{
			$sql = $ilance->db->query("
				SELECT b.orderid, b.status, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, s.ship_handlingtime, s.ship_method
				FROM " . DB_PREFIX . "buynow_orders b
				LEFT JOIN " . DB_PREFIX ."projects_shipping s ON b.project_id = s.project_id
				WHERE b.project_id = '" . $pid . "'
					AND b.buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND b.owner_id = '" . intval($ilance->GPC['sellerid']) . "'
					AND b.paiddate = '0000-00-00 00:00:00'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$show['localpickuponly'] = ($res['ship_method'] == 'localpickup') ? true : false;
					
					if (!empty($res['buyerpaymethod']))
					{
						$ilance->GPC['paymethod'] = $res['buyerpaymethod'];
						
						$buyershipperid = ((isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] > 0)
							? intval($ilance->GPC['shipperid'])
							: $res['buyershipperid']);
						
						$ilance->GPC['shipperid'] = $buyershipperid;
						
						if ($buyershipperid == 0 AND $res['buyershipperid'] > 0)
						{
							$buyershipperid = $res['buyershipperid'];
						}
						
						$buyershipcost = fetch_ship_cost_by_shipperid($pid, $buyershipperid, $qty);
						if ($show['localpickuponly'])
						{
							$buyershipperid = 0;
							$buyershipcost['total'] = 0;
						}
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "buynow_orders
							SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
							buyershipcost = '" . sprintf("%01.2f", $buyershipcost['total']) . "',
							buyershipperid = '" . intval($buyershipperid) . "'
							WHERE project_id = '" . intval($pid) . "'
								AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND owner_id = '" . intval($ilance->GPC['sellerid']) . "'
								AND orderid = '" . $res['orderid'] . "'
						", 0, null, __FILE__, __LINE__);
						
						$orderids[] = $res['orderid'];
					}
					
					$buyerpaymethod = $res['buyerpaymethod'];
					if ($res['buyershipperid'] > 0 AND $res['buyershipcost'] > 0 AND $show['localpickuponly'] == false)
					{
						$total = ($total + $res['buyershipcost']);
					}
				}
			}
			
			// #### check to see if this is a lowest unique bid purchase
			else
			{
				$sql = $ilance->db->query("
					SELECT b.uid, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.uniquebid, s.ship_handlingtime, s.ship_method
					FROM " . DB_PREFIX . "projects_uniquebids b
					LEFT JOIN " . DB_PREFIX . "projects_shipping s ON b.project_id = s.project_id
					WHERE b.status = 'lowestunique'
						AND b.project_id = '" . $pid . "'
						AND b.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND b.project_user_id = '" . intval($ilance->GPC['sellerid']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					
					$show['lub'] = true;
					$show['localpickuponly'] = ($res['ship_method'] == 'localpickup') ? true : false;
					
					$buyerpaymethod = $res['buyerpaymethod'];
					if (!empty($buyerpaymethod) AND !isset($ilance->GPC['paymethod']))
					{
						$ilance->GPC['paymethod'] = $buyerpaymethod;
					}
					
					if ($res['buyershipperid'] > 0 AND $res['buyershipcost'] > 0 AND $show['localpickuponly'] == false)
					{
						$total = ($total + $res['buyershipcost']);
					}
		
					if (!empty($ilance->GPC['paymethod']))
					{
						$buyerpaymethod = $ilance->GPC['paymethod'];
						$buyershipperid = ((isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] > 0)
							? intval($ilance->GPC['shipperid'])
							: 0);
						
						if ($buyershipperid == 0 AND $res['buyershipperid'] > 0)
						{
							$buyershipperid = $res['buyershipperid'];
						}
						
						$buyershipcost = fetch_ship_cost_by_shipperid($pid, $buyershipperid, 1);
						if ($show['localpickuponly'])
						{
							$buyershipperid = 0;
							$buyershipcost['total'] = 0;
						}
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects_uniquebids
							SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
							buyershipcost = '" . sprintf("%01.2f", $buyershipcost['total']) . "',
							buyershipperid = '" . intval($buyershipperid) . "'
							WHERE uid = '" . $res['uid'] . "'
						");
						
						// #### winning bidder redirected to i've won items activity menu
						if ($methodscount == 1 AND $shippercount == 1)
						{
							if (strchr($ilance->GPC['paymethod'], 'offline'))
							{
								refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&subcmd=lub&bidsub=awarded');
								exit();
							}
						}
					}
					
					if (empty($res['buyerpaymethod']))
					{
						if ($methodscount == 1)
						{
							$ilance->GPC['paymethod'] = print_payment_method_title($pid);
							$hiddenfields .= '<input type="hidden" name="paymethod" value="' . handle_input_keywords($ilance->GPC['paymethod']) . '" />';
							
							if (strchr($ilance->GPC['paymethod'], 'offline') OR strchr($ilance->GPC['paymethod'], 'gateway'))
							{
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects_uniquebids
									SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "'
									WHERE project_id = '" . $pid . "'
										AND uid = '" . $res['uid'] . "'
										AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
										AND project_user_id = '" . $ilance->GPC['sellerid'] . "'
								");
								
								if (strchr($ilance->GPC['paymethod'], 'offline'))
								{
									refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&subcmd=lub&bidsub=awarded');
									exit();
								}
							}
						}
						
						else if ($methodscount > 1)
						{
							$area_title = $phrase['_confirm_payment_method'];
							$page_title = SITE_NAME . ' - ' . $phrase['_confirm_payment_method'];
							
							$navcrumb = array();
							if ($ilconfig['globalauctionsettings_seourls'])
							{
								$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['listingsidentifier'])] = $phrase['_buy'];
							}
							else
							{
								$navcrumb["$ilpage[merch]?cmd=listings"] = $phrase['_buy'];
							}
							$navcrumb["$ilpage[merch]?id=" . $pid] = fetch_auction('project_title', $pid);
							$navcrumb[""] = $phrase['_confirm_payment_method'];
							
							// #### radio input for buyers payment decision ################
							$paymethodsradios = print_payment_methods($pid, true);
							
							// #### radio input for buyers payment decision ################
							$shippingradios = print_shipping_methods($pid, $qty, true);
							$shippingradioscount = print_shipping_methods($pid, $qty, false, true);
							$shippingservice = '';
							$shipperid = 0;
							$days = 3;	
							print_shipping_methods($pid, $qty, false, false);
							if ($shippingradioscount == 1 AND $shipperidrow > 0)
							{
								$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '$pid'", "ship_service_$shipperidrow");
								$shippingservice = print_shipping_partner($shipperid);
								$days = $res['ship_handlingtime'];
							}
							
							$pprint_array = array('onsubmit','shipperid','days','shippingservice','shippingradios','hiddenfields','pid','qty','paymethodsradios','paymethods','returnurl','tax','paymethod','fees','digitalfile','cb_shipping_address_required1','cb_shipping_address_required0','encrypted','samount','amount_formatted','total','shipping_address_pulldown','forceredirect','payment_method_pulldown','attachment','project_id','seller_id','buyer_id','user_cookie','project_title','seller','qty','topay','amount','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','category','subcategory');
						
							($apihook = $ilance->api('listing_payment_selection_end')) ? eval($apihook) : false;
							
							$ilance->template->fetch('main', 'listing_payment_selection.html');
							$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
							$ilance->template->parse_loop('main', 'paymentoptions');
							$ilance->template->parse_if_blocks('main');
							$ilance->template->pprint('main', $pprint_array);
							exit();	
						}
					}
					else
					{
						if ($methodscount == 1 AND $shippercount == 1)
						{
							if (strchr($res['buyerpaymethod'], 'offline'))
							{
								refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&subcmd=lub&bidsub=awarded');
								exit();
							}
						}
					}
				}
				
				// #### the viewing user doesn't have any winning bids or purchases with this seller for this item!!
				else
				{
					// check for seo?
					refresh(HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($ilance->GPC['id']));
					exit();
				}
			}
		}
		
		// #### we've made it this far because the buyer wishes to pay the seller via major gateway (Paypal, Moneybookers, etc)
		if (count($orderids) == 1)
		{
			$ilance->GPC['orderid'] = $orderids[0];
			$hiddenfields .= '<input type="hidden" name="orderid" value="' . $ilance->GPC['orderid'] . '" />';
		}
		
		// #### buyer choosing payment method ##########################
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'choose')
		{
			$area_title = $phrase['_confirm_payment_method'];
			$page_title = SITE_NAME . ' - ' . $phrase['_confirm_payment_method'];
			
			$navcrumb = array();
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['listingsidentifier'])] = $phrase['_buy'];
			}
			else
			{
				$navcrumb["$ilpage[merch]?cmd=listings"] = $phrase['_buy'];
			}
			$navcrumb["$ilpage[merch]?id=" . $pid] = fetch_auction('project_title', $pid);
			$navcrumb[""] = $phrase['_confirm_payment_method'];
			
			// #### radio input for buyers payment & shipping service decision
			$paymethodsradios = print_payment_methods($pid, true);
			$shippingradios = print_shipping_methods($pid, $qty, true);
			$shippingradioscount = print_shipping_methods($pid, $qty, false, true);
			$days = 3;
			$shipperid = 0;
			$shippingservice = '';
			$orderidradios = print_orderid_methods($pid, $_SESSION['ilancedata']['user']['userid']);
			
			if (isset($show['multipleorders']) AND $show['multipleorders'] == true)
			{		
				$onsubmit = 'return validate_all(this);';
				$headinclude .= '
<script language="javascript" type="text/javascript">
<!--
function validate_order_id()
{
';
				for ($x = 1; $x < $orderidradiocount; $x++)
				{
					$headinclude .= '
	if (fetch_js_object(\'orderid_' . $x . '\').checked == true)
	{
		return(true);	
	}
';
				}
					$headinclude .= '
	alert(\'' . $phrase['_you_forgot_to_select_an_order_to_update_this_is_required'] . '\');
	return(false);					
}
function validate_paymethod()
{
	return(true);
}
function validate_ship_service()
{
	return(true);
}
function validate_all(formobj)
{
	return validate_order_id() && validate_paymethod() && validate_ship_service();
}
//-->
</script>';
			}
			
			if ($shippercount == 1)
			{
				print_shipping_methods($pid, $qty, false, false);
				if ($shipperidrow > 0)
				{
					$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . $pid . "'", "ship_service_$shipperidrow");
					$shippingservice = print_shipping_partner($shipperid);
					$days = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping", "project_id = '" . $pid . "'", "ship_handlingtime");
				}
			}
			
			$pprint_array = array('onsubmit','orderidradios','shipperid','days','shippingservice','shippingradios','hiddenfields','pid','qty','paymethodsradios','paymethods','returnurl','tax','paymethod','fees','digitalfile','cb_shipping_address_required1','cb_shipping_address_required0','encrypted','samount','amount_formatted','total','shipping_address_pulldown','forceredirect','payment_method_pulldown','attachment','project_id','seller_id','buyer_id','user_cookie','project_title','seller','qty','topay','amount','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','category','subcategory');
		
			($apihook = $ilance->api('listing_payment_selection_end')) ? eval($apihook) : false;
			
			$ilance->template->fetch('main', 'listing_payment_selection.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', 'paymentoptions');
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		
		if ($buyerpaymethod == 'escrow')
		{
			refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=product-escrow');
			exit();	
		}
		else if (strchr($buyerpaymethod, 'offline'))
		{
			if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
			{
				refresh(urldecode($ilance->GPC['returnurl']));
				exit();
			}
			
			if (isset($show['lub']) AND $show['lub'])
			{
				refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&subcmd=lub&bidsub=awarded');
				exit();
			}
			else
			{
				if (isset($returnurl) AND !empty($returnurl))
				{
					refresh(urldecode($returnurl));
					exit();
				}
				else
				{
					refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=buynow-escrow');
					exit();
				}
			}
		}
	}
        
        $area_title = $phrase['_complete_your_payment_to_seller'];
        $page_title = SITE_NAME . ' - ' . $phrase['_complete_your_payment_to_seller'];
	
	$totalformatted = $ilance->currency->format($total, $currencyid);
	if (empty($buyerpaymethod) AND isset($ilance->GPC['paymethod']) AND !empty($ilance->GPC['paymethod']))
	{
		$buyerpaymethod = $ilance->GPC['paymethod'];
	}
	
        ($apihook = $ilance->api('print_notice_direct_payment_start')) ? eval($apihook) : false;
        
	// #### buyer's selected payment method ################################
        switch ($buyerpaymethod)
        {
                case 'gateway_paypal':
                {
                        $ilance->paypal = construct_object('api.paypal');
			
			$paye = fetch_payment_method_email($itemid, 'paypal');
                        $formstart = $ilance->paypal->print_direct_payment_form($total, $title, $paye, $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code'], $customencrypted, $returnurl);
			$paymenticon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'payment/paypal.gif" border="0" alt="" id="" />';
                        break;
                }
                case 'gateway_stormpay':
                {
                        $ilance->stormpay = construct_object('api.stormpay');
			
			$paye = fetch_payment_method_email($itemid, 'stormpay');
                        $formstart = $ilance->stormpay->print_direct_payment_form($total, $title, $paye, $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code'], $customencrypted, $returnurl);
			$paymenticon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'payment/stormpay.gif" border="0" alt="" id="" />';
                        break;
                }
                case 'gateway_moneybookers':
                {
                        $ilance->moneybookers = construct_object('api.moneybookers');
			
			$paye = fetch_payment_method_email($itemid, 'moneybookers');
                        $formstart = $ilance->moneybookers->print_direct_payment_form($total, $title, $paye, $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code'], $customencrypted, $returnurl);
			$paymenticon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'payment/moneybookers.gif" border="0" alt="" id="" />';
                        break;
                }
                case 'gateway_cashu':
                {
                        $ilance->cashu = construct_object('api.cashu');
			
			$paye = fetch_payment_method_email($itemid, 'cashu');
                        $formstart = $ilance->cashu->print_direct_payment_form($total, $title, $paye, $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code'], $customencrypted, $returnurl);
			$paymenticon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'payment/cashu.gif" border="0" alt="" id="" />';
                        break;
                }
        }
        
	($apihook = $ilance->api('print_notice_direct_payment_options_end')) ? eval($apihook) : false;
	
        $formend = '</form>';
        
        $pprint_array = array('sample','itemid','title','totalformatted','paymenticon','orderid','formstart','formend','url','country_pulldown','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('print_notice_direct_payment_end')) ? eval($apihook) : false;
        
        $ilance->template->fetch('main', 'print_notice_direct_payment.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
// #### PREVIEW UNIQUE BID #####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'preview-unique-bid' AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
{
        $area_title = $phrase['_preview_my_unique_bid'];
        $page_title = SITE_NAME . ' - ' . $phrase['_preview_my_unique_bid'];
        
        $navcrumb = array("$ilpage[merch]" => $phrase['_preview_my_unique_bid']);
	
        if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
        {
                print_notice($area_title, $phrase['_your_session_has_expired_please_login'], 'javascript:history.back(1);', $phrase['_back']);
                exit();         
        }
	
	$ilance->subscription = construct_object('api.subscription');
        
        $project_id = intval($ilance->GPC['project_id']);
	$title = fetch_auction('project_title', $project_id);
	$currencyid = fetch_auction('currencyid', $project_id);
        $bidamountformatted = $ilance->currency->format($ilance->GPC['uniquebid'], $currencyid);
        $uniquebid = sprintf("%01.2f", $ilance->GPC['uniquebid']);
        $owner_id = intval($ilance->GPC['owner_id']);
        
        // check bids per day limit
        $bidtotal = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday');
        $bidsleft = max(0, ($bidtotal - fetch_bidcount_today($_SESSION['ilancedata']['user']['userid'])));
        
        if ($uniquebid <= 0.001)
        {
                print_notice($phrase['_your_offer_is_less_than_1_penny'], $phrase['_you_must_bid_1_penny_or_more_go_back_and_bid_again'] . ' <a href=' . HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($ilance->GPC['project_id']) . '>' . $phrase['_click_here'] . '</a>', 'javascript: history.go(-1)', $phrase['_retry']);
                exit();
        }
	
        if ($bidsleft == 0)
        {
                $area_title = $phrase['_access_to_bid_is_denied'];
                $page_title = SITE_NAME . ' - ' . $phrase['_access_to_bid_is_denied'];
                
                print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('bidlimitperday'));
                exit();        
        }
	
	$pprint_array = array('title','owner_id','uniquebid','bidamountformatted','project_id','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        $ilance->template->fetch('main', 'listing_forward_auction_placebidunique_preview.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
// #### SUBMIT UNIQUE BID ######################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'submit-unique-bid' AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND isset($ilance->GPC['owner_id']) AND $ilance->GPC['owner_id'] > 0 AND isset($ilance->GPC['uniquebid']) AND $ilance->GPC['uniquebid'] > 0 AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        $ilance->bid = construct_object('api.bid');
        $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
        $ilance->bid_lowest_unique->insert_unique_bid($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['owner_id']), intval($ilance->GPC['project_id']), $ilance->GPC['uniquebid']);
        
        // todo: check for seo
        refresh($ilpage['merch'] . '?id=' . intval($ilance->GPC['project_id']));
        exit();
}
// #### INSERT NEW PUBLIC MESSAGE ##############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'insertmessage' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	if (empty($ilance->GPC['message']))
	{
		print_notice($phrase['_message_cannot_be_empty'], $phrase['_please_retry_your_action'], 'javascript: history.go(-1)', $phrase['_retry']);
		exit();
	}
	
	insert_public_message(intval($ilance->GPC['pid']), intval($ilance->GPC['sellerid']), $_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['username'], $ilance->GPC['message'], $visible = '1');
        
        // todo: check for seo
	refresh($ilpage['merch'] . '?id=' . intval($ilance->GPC['pid']) . '#messages');
	exit();
}
// #### REMOVE PUBLIC MESSAGE ##################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'removemessage' AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	if (empty($ilance->GPC['messageid']))
	{
		print_notice($phrase['_message_does_not_exist'], $phrase['_please_retry_your_action'], 'javascript: history.go(-1)', $phrase['_retry']);
		exit();
	}
        
	$sql = $ilance->db->query("
                DELETE FROM " . DB_PREFIX . "messages
                WHERE messageid = '" . intval($ilance->GPC['messageid']) . "'
                    AND project_id = '" . intval($ilance->GPC['pid']) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        
        // todo: check for seo
	refresh($ilpage['merch'] . '?id=' . intval($ilance->GPC['pid']) . '#messages');
	exit();
}
// #### BUY NOW INSTANT PAYMENT PROCESS HANDLER ################################
else if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0
	 AND (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_instant-purchase-process'
	 AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0
	 AND isset($ilance->GPC['seller_id']) AND $ilance->GPC['seller_id'] > 0
	 AND isset($ilance->GPC['buyer_id']) AND $ilance->GPC['buyer_id'] > 0
	 AND isset($ilance->GPC['qty']) AND $ilance->GPC['qty'] > 0)
	 OR
	 isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_instant-purchase-process'
	 AND isset($uncrypted['project_id']) AND $uncrypted['project_id'] > 0
	 AND isset($uncrypted['seller_id']) AND $uncrypted['seller_id'] > 0
	 AND isset($uncrypted['buyer_id']) AND $uncrypted['buyer_id'] > 0
	 AND isset($uncrypted['qty']) AND $uncrypted['qty'] > 0)
{
               //new changes from herakle
		        $result_open = $ilance->db->query("
				SELECT qty
				FROM " . DB_PREFIX . "buynow_orders
				WHERE buyer_id = '".$_SESSION['ilancedata']['user']['userid']."'
				AND project_id = '".$uncrypted['project_id']."'
				");
				if($ilance->db->num_rows($result_open) > 0 and $_SESSION['ilancedata']['user']['userid'] > 0)
                {
				    while ($orderops = $ilance->db->fetch_array($result_open))
					{
					       $total_qty[]=$orderops['qty'];
					}
				}
				if(isset($total_qty))
                {
					 $product_count   =   array_sum($total_qty);
					 
                     $max_count = fetch_auction('max_qty',$uncrypted['project_id']);
					
					
					 $total_count = $product_count + $uncrypted['qty'];
					
					
					 if($max_count < $total_count)
					 {
					
					       print_notice('Sorry','Maximum quantity of this Item is ('.$max_count.').You have  purchased the maximum quantity ('.$product_count.' ) of this item.So Not more than ('.$max_count.') Maximum quantity per member at GreatCollections',  'merch.php?id='.$uncrypted['project_id'].'' ,'Go Back');
					
					       exit();
					
					 }
					  
				}
        $ilance->escrow = construct_object('api.escrow');
	$ilance->escrow_buynow = construct_object('api.escrow_buynow');
        $ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true);
        
	$ilance->GPC['buyershipcost'] = sprintf("%01.2f", $uncrypted['buyershipcost']);
	$ilance->GPC['shipperid'] = intval($uncrypted['shipperid']);
	
        $success = 0;
	
        $status = fetch_auction('status', $uncrypted['project_id']);
        if ($status != 'open')
        {
		$area_title = $phrase['_access_denied'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
		
                print_notice($phrase['_access_denied'], $phrase['_this_listing_has_ended'], 'javascript:history.back(2);', $phrase['_back']);
                exit();         
        }
	// #### escrow payment method mode #####################################
        if ($ilconfig['escrowsystem_enabled'] AND isset($uncrypted['paymethod']) AND $uncrypted['paymethod'] == 'escrow')
        {
                // #### instant purchase via online account balance only #######
                if ($ilance->GPC['account_id'] == 'account')
                {
				
// murugan Promo Code Check Date Oct 07
					   $checkvalue = explode(' ',$ilance->GPC['checkpromo']);
					   if($checkvalue[1] == '%' || $checkvalue[1]== '$')
					   {
					   	 if($checkvalue[1] == '%')
						 {
						   							
							$calculate = ($uncrypted['amount'] * $uncrypted['qty'])* ($checkvalue[0]/100);
							$finalvalue = $uncrypted['total'] - $calculate;
							$finalvalue = $finalvalue.'.00';
							$murugan = $calculate;
						 }
						 if($checkvalue[1] == '$')
						 {						    
							$murugan = $checkvalue[0];
							$finalvalue = $uncrypted['total'] - $checkvalue[0];
							$finalvalue = $finalvalue .'.00';
						 }
					  
					   }
					   else
					   {
					      $murugan = '0';
						  $finalvalue = $uncrypted['total'].'.00';
					   }
					   
					   // murugan here i remove the $uncrypted['total'] and put the variable as $finalvalue.  Date Oct 07
					   // This Final Value is the calculated Amount For the Buynow Product.
					   // Date Oct 08 Added new Item in this instant_purchase_now as $murugan 
				
                
					    $success = $ilance->escrow_buynow->instant_purchase_now('account', $uncrypted['project_id'], $uncrypted['qty'], $uncrypted['amount'], $uncrypted['total'], $uncrypted['seller_id'], $_SESSION['ilancedata']['user']['userid'], $ilance->GPC['shipping_address_required'], $ilance->GPC['shipping_address_id'], $ilance->GPC['account_id'], $phrase['_account_balance'], $ilance->GPC['buyershipcost'], $ilance->GPC['shipperid'],$murugan);
                        
						//#########  herakle start feb 23 2011 //work by karthik #############//
						
                       /* if ($success[0])
                        {*/
						//karthik start mar 02 2011 //
						$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects 
			SET status='closed'
			WHERE project_id = '" . $uncrypted['project_id'] . "'
				AND buynow_qty = '0'
			");
			$show['chec'] = 'inchec';
                                $area_title = $phrase['_instant_purchase_to_escrow_via_online_account_complete'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_instant_purchase_to_escrow_via_online_account_complete'];
                              //karthik  
                                 print_notice($phrase['_congratulations_you_used_buy_now_at_greatcollections'], $phrase['_the_buy_now_item_should_be_added_to_an_invoice'], HTTPS_SERVER . 'buyer_invoice.php', $phrase['_pending_invoice_check_out_and_pay']);
                                exit();
								
                       /* }
                        else
                        {
                                $area_title = $phrase['_no_funds_available_in_online_account'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_no_funds_available_in_online_account'];
                                
                                print_notice($phrase['_invoice_payment_warning_insufficient_funds'], $phrase['_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['accounting'], $phrase['_my_account']);
                                exit();
                        }*/
						//######## end ##############//
                }
                
		// #### some error has occured #################################
		else
                {
                        refresh($ilpage['merch'] . '?id=' . $uncrypted['project_id'], $ilpage['merch'] . '?id=' . $uncrypted['project_id']);
                        exit();
                }    
        }
        else
        {
                // no escrow enabled: assuming outside payment communications
                // because of this we'll make sure that the seller has funds to cover this "FVF" if applicable in this category
                // if he does we'll debit it right away
                // if he does not we'll generate unpaid invoice as a last alternative to get that commission fee!
// murugan Promo code Check offline mode
// this commented code copied below
              /*  $success = $ilance->escrow_buynow->instant_purchase_now('offline', $uncrypted['project_id'], $uncrypted['qty'], $uncrypted['amount'], $uncrypted['total'], $uncrypted['seller_id'], $_SESSION['ilancedata']['user']['userid'], $ilance->GPC['shipping_address_required'], $ilance->GPC['shipping_address_id'], 0, $uncrypted['paymethod'], $ilance->GPC['buyershipcost'], $ilance->GPC['shipperid']);*/
				
				// herakle work by karthik
				
				 //#######   HERAKLE start feb 14 2011   #############//
			 
			$sq=$ilance->db->query(" SELECT currencyid,buynow
                FROM " . DB_PREFIX . "projects 
                WHERE project_id = '" . $uncrypted['project_id'] . "'");
			 if ($ilance->db->num_rows($sq) > 0)
                                {
                                        $res = $ilance->db->fetch_array($sq, DB_ASSOC);
                                        $currencyid = $res['currencyid'];
										$buynow_id  = $res['buynow'];
			echo 'off';
				$success1= invoice_offline('offline',$currencyid, $uncrypted['project_id'],$buynow_id, $uncrypted['qty'], $uncrypted['amount'], $uncrypted['total'], $uncrypted['seller_id'], $_SESSION['ilancedata']['user']['userid'], $ilance->GPC['shipping_address_required'], $ilance->GPC['shipping_address_id'], 0, $uncrypted['paymethod'], $ilance->GPC['buyershipcost'], $ilance->GPC['shipperid']);
				}
			
				
                 $success = $ilance->escrow_buynow->instant_purchase_now('offline', $uncrypted['project_id'], $uncrypted['qty'], $uncrypted['amount'], $uncrypted['total'], $uncrypted['seller_id'], $_SESSION['ilancedata']['user']['userid'], $ilance->GPC['shipping_address_required'], $ilance->GPC['shipping_address_id'], 0, $uncrypted['paymethod'], $ilance->GPC['buyershipcost'], $ilance->GPC['shipperid']);
				
				$account=	$ilance->db->query("
                                        SELECT invoiceid
                                        FROM " . DB_PREFIX . "invoices
                                        WHERE projectid = '" . $uncrypted['project_id'] . "' ORDER BY invoiceid DESC 
								
                                ");
												
									 if ($ilance->db->num_rows($account) > 0)
                                {
                                        $res = $ilance->db->fetch_array($account, DB_ASSOC);
                                        $invoiceid1 = $res['invoiceid'];
										
										$ilance->db->query("
							UPDATE " . DB_PREFIX . "buynow_orders
							SET invoiceid='".$invoiceid1."' WHERE invoiceid='0'
							
								");
                                }			
							// herakle end	
							
                if ($success[0])
                {
			$orderid = $success[1];
			if (strchr($uncrypted['paymethod'], 'gateway'))
			{
				$shipserviceurlbit = '';
				if (isset($ilance->GPC['shipping_address_required']) AND $ilance->GPC['shipping_address_required'] AND isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] > 0)
				{
					$shipserviceurlbit = '&shipperid=' . $ilance->GPC['shipperid'];
				}
				
				refresh(HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&id=' . $uncrypted['project_id'] . '&orderid=' . $orderid . '&paymethod=' . $uncrypted['paymethod'] . $shipserviceurlbit);
                                exit();
			}
			else
			{
                                $area_title = $phrase['_congratulations_you_purchased_this_item'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_congratulations_you_purchased_this_item'];
                                
				print_notice($phrase['_you_confirmed_a_purchase_now_order'], $phrase['_congratulations_you_have_successfully_confirmed_the_purchase_of_this_item_we_have_dispatched_you_an_email'], HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=buynow-escrow', $phrase['_buying_activity']);
				exit();
			}
                }
                else
                {
                        $area_title = $phrase['_there_was_a_problem_confirming_your_order'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_there_was_a_problem_confirming_your_order'];
                        
                        print_notice($phrase['_there_was_a_problem_confirming_your_order'], $phrase['_we_are_sorry_but_there_appears_to_be_a_problem_with_the_purchase_of_this_item'], "javascript: history.go(-1)", $phrase['_back']);
                        exit();
                }
        }
}
// #### BUY NOW CONFIMRATION PAGE AND ITEM ORDER DISPLAY #######################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'purchase-confirm' AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0 AND isset($ilance->GPC['qty']) AND $ilance->GPC['qty'] > 0)
{
        if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
        {
                refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['merch'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
                exit();
        }
		
		//new changes from herakle
		$result_open = $ilance->db->query("
					SELECT qty
					FROM " . DB_PREFIX . "buynow_orders
					WHERE buyer_id = '".$_SESSION['ilancedata']['user']['userid']."'
						AND project_id = '".$ilance->GPC['pid']."'
				");
				if($ilance->db->num_rows($result_open) > 0 and $_SESSION['ilancedata']['user']['userid'] > 0)
				{
				
				while ($orderops = $ilance->db->fetch_array($result_open))
					{
					$total_qty[]=$orderops['qty'];
					}
					
				}
				
				if(isset($total_qty))
				{
					$cou   =   array_sum($total_qty);
					
					$max_q = fetch_auction('max_qty',$ilance->GPC['pid']);
					
					//new hidden
					
					$hidden_val = '<input type="hidden"  id="cou_val" value="'.$cou.'"><input type="hidden"  id="max_val" value="'.$max_q.'">';
					
					$onload = "check('".$ilance->GPC['qty']."')";
					  
				}
        
        ($apihook = $ilance->api('merch_purchase_confirm_start')) ? eval($apihook) : false;
        
        $project_id = intval($ilance->GPC['pid']);
        $seller_id = fetch_auction('user_id', $project_id);
        $amount = fetch_auction('buynow_price', $project_id);
        $qty = (isset($ilance->GPC['qty']) AND $ilance->GPC['qty'] > 0)
		? number_format(intval($ilance->GPC['qty']), 0)
		: 1;
		
        $qtyleft = fetch_auction('buynow_qty', $project_id);
	//$hiddenfields = '<input type="hidden" name="cmd" value="directpay" /><input type="hidden" name="id" value="' . intval($project_id) . '" />';
	$hiddenfields = '<input type="hidden" name="cmd" value="purchase-confirm" /><input type="hidden" name="pid" value="' . $project_id . '" /><input type="hidden" name="qty" value="' . $qty . '" />';
        
        if ($qty > $qtyleft)
        {
		$area_title = $phrase['_access_denied'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
		
                print_notice($area_title, $phrase['_it_appears_you_are_trying_to_purchase_more_quantity_than_this_seller_is_currently_offering'], 'javascript:history.back(1);', $phrase['_back']);
                exit();         
        }
    
        // #### make sure we are not the seller of this auction! ###############
        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $seller_id)
        {
		$area_title = $phrase['_access_denied'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
	
                print_notice($area_title, $phrase['_it_appears_you_are_the_seller_of_this_listing_in_this_case_you_cannot_bid_or_purchase_items_from_your_own_listing'], 'javascript:history.back(1);', $phrase['_back']);
                exit();       
        }
	
        // #### do we have anything left to purchase? ##########################
        $sql = $ilance->db->query("
                SELECT p.filter_escrow, p.filter_gateway, p.filter_offline, p.filtered_auctiontype, p.buynow_qty, p.project_title, p.paymethod, p.paymethodoptions, p.paymethodoptionsemail, p.currencyid, s.ship_handlingtime, s.ship_method 
                FROM " . DB_PREFIX . "projects p
		LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
		LEFT JOIN " . DB_PREFIX . "projects_shipping_destinations sd ON p.project_id = sd.project_id
                WHERE p.project_id = '" . intval($project_id) . "'
                    AND p.buynow_qty > 0
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) == 0)
        {
		$area_title = $phrase['_access_denied'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
		
                print_notice($phrase['_this_item_has_sold_out'], $phrase['_were_sorry_either_one_or_more_customers_have_already_purchased_this_item_and_the_qty'], $ilpage['merch'] . '?cmd=listings', $phrase['_view_other_merchandise']);
                exit();
        }
	
	$ilance->subscription = construct_object('api.subscription');
	$ilance->bid = construct_object('api.bid');
        $ilance->tax = construct_object('api.tax');
	
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	
	$show['localpickuponly'] = $res['ship_method'] == 'localpickup' ? true : false;
	$show['hidepaymethodchange'] = false;
	
	// #### fetch the number of payment methods and shipping services available to buyer
	$methodscount = print_payment_methods($project_id, false, true);
	$shippercount = print_shipping_methods($project_id, $qty, false, true);
	
	// #### if we only have 1 payment method and it hasn't been selected, auto-select it for buyer
	if ($methodscount == 1 AND empty($ilance->GPC['paymethod']))
	{
		$show['hidepaymethodchange'] = true;
		//$ilance->GPC['paymethod'] = print_payment_method_title($project_id);
	}
	
	// #### if we only have 1 shipping service and it hasn't been selected, auto-select it for buyer
	if ($shippercount == 1 AND empty($ilance->GPC['shipperid']))
	{
		print_shipping_methods($project_id, $qty, false, false);
		$ilance->GPC['shipperid'] = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . intval($project_id) . "'", "ship_service_$shipperidrow");
	}
	
	//else if (($methodscount > 1 OR $shippercount > 1) AND (empty($ilance->GPC['paymethod']) OR empty($ilance->GPC['shipperid'])))
	if ($methodscount > 1 AND empty($ilance->GPC['paymethod']) OR $shippercount > 1 AND empty($ilance->GPC['shipperid']))
	{
		$area_title = $phrase['_confirm_payment_method'];
		$page_title = SITE_NAME . ' - ' . $phrase['_confirm_payment_method'];
		
		$qty = isset($ilance->GPC['qty']) ? intval($ilance->GPC['qty']) : 1;
		$pid = $project_id;
		
		$navcrumb = array();
		if ($ilconfig['globalauctionsettings_seourls'])
		{
			$navcrumb[HTTP_SERVER . $ilconfig['listingsidentifier']] = $phrase['_buy'];
		}
		else
		{
			$navcrumb["$ilpage[merch]?cmd=listings"] = $phrase['_buy'];
		}
		$navcrumb["$ilpage[merch]?id=" . $project_id] = fetch_auction('project_title', $project_id);
		$navcrumb[""] = $phrase['_confirm_payment_method'];
		
		// #### radio input for buyers payment decision ################
		$paymethodsradios = print_payment_methods($project_id, true);
		$shippingradios = print_shipping_methods($pid, $qty, true);
		$shippingradioscount = print_shipping_methods($pid, $qty, false, true);
		$shippingservice = '';
		$shipperid = 0;
		$days = 3;	
		print_shipping_methods($pid, $qty, false, false);
		if ($shippingradioscount == 1 AND $shipperidrow > 0)
		{
			$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '$pid'", "ship_service_$shipperidrow");
			$shippingservice = print_shipping_partner($shipperid);
			$days = $res['ship_handlingtime'];
		}
		$orderidradios = print_orderid_methods($pid, $_SESSION['ilancedata']['user']['userid']);
		
		if (isset($show['multipleorders']) AND $show['multipleorders'] == true)
		{		
			$onsubmit = 'return validate_all()';
			$headinclude .= '
<script type="text/javascript">
function validate_order_id()
{
	if (fetch_js_object(\'orderid\').checked == false)
	{
		alert(\'Please select one existing order by clicking one radio button beside that order.\');
		return(false);
	}
	return(true);
}
function validate_paymethod()
{
	return(true);
}
function validate_ship_service()
{
	return(true);
}
function validate_all()
{
	return validate_order_id() && validate_paymethod() && validate_ship_service();
}
</script>';
		}
		
		$pprint_array = array('orderidradios','paymethod','shipperid','days','shippingservice','shippingradios','hiddenfields','pid','qty','paymethodsradios','paymethods','returnurl','tax','paymethod','fees','digitalfile','cb_shipping_address_required1','cb_shipping_address_required0','encrypted','samount','amount_formatted','total','shipping_address_pulldown','forceredirect','payment_method_pulldown','attachment','project_id','seller_id','buyer_id','user_cookie','project_title','seller','qty','topay','amount','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','category','subcategory');
        
		($apihook = $ilance->api('listing_payment_selection_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'listing_payment_selection.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'paymentoptions');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();	
	}
	
	$area_title = $phrase['_purchase_now_order_confirmation'];
	$page_title = SITE_NAME . ' - ' . $phrase['_purchase_now_order_confirmation'];
	
	$navcrumb = array();
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['listingsidentifier'])] = $phrase['_buy'];
	}
	else
	{
		$navcrumb["$ilpage[merch]?cmd=listings"] = $phrase['_buy'];
	}
	$navcrumb["$ilpage[merch]?id=" . $project_id] = fetch_auction('project_title', $project_id);
	$navcrumb[""] = $phrase['_commit_to_purchase'];
	
	// #### template conditions ############################################
	$show['taxbit'] = $show['sellerusingescrow'] = $show['filter_escrow'] = $show['noshipping'] = $show['digitaldownload'] = false;
	$show['makepayment'] = ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		? true
		: false);
	
	// #### shipping address defaults ######################################
	$cb_shipping_address_required0 = $digitalfile = '';
	$cb_shipping_address_required1 = 'checked="checked"';
	
	$dquery = $ilance->db->query("
		SELECT filename, filesize, attachid
		FROM " . DB_PREFIX . "attachment
		WHERE project_id = '" . intval($project_id) . "'
		    AND attachtype = 'digital'
		    AND user_id = '" . intval($seller_id) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($dquery) > 0)
	{
		$dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
		$digitalfile = stripslashes($dfile['filename']) . ' (' . print_filesize($dfile['filesize']) . ')';
		
		$show['noshipping'] = $show['digitaldownload'] = true;
		$cb_shipping_address_required1 = '';
		$cb_shipping_address_required0 = 'checked="checked"';
	}
	else
	{
		$show['noshipping'] = true;
	}
	
	// #### seller url #####################################################
	$seller = print_username($seller_id, 'href');
	
	// #### amounts and shipping costs formatted ###########################
	$shippingservice = '';
	$ilance->GPC['buyershipcost'] = 0;
	$ilance->GPC['shipperid'] = isset($ilance->GPC['shipperid']) ? intval($ilance->GPC['shipperid']) : 0;
	
	if ($ilance->GPC['shipperid'] > 0)
	{
		$shippingcosts = fetch_ship_cost_by_shipperid($project_id, $ilance->GPC['shipperid'], $qty);
		$ilance->GPC['buyershipcost'] = $shippingcosts['total'];
		$shippingservice = print_shipping_partner($ilance->GPC['shipperid']);
	}
	
	$amount_formatted = $ilance->currency->format($amount, $res['currencyid']);
	$samount = $ilance->currency->format($ilance->GPC['buyershipcost'], $res['currencyid']);
	$topay = $ilance->currency->format(($amount * $qty) + $ilance->GPC['buyershipcost'], $res['currencyid']);
	$total = ($amount * $qty) + $ilance->GPC['buyershipcost'];
			
	$project_title = handle_input_keywords($res['project_title']);
	$tax = $ilance->currency->format(0, $res['currencyid']);
	
	// #### thumbnail ######################################################
	$attachment = print_item_photo('javascript:void(0)', 'thumb', $project_id);
	
	// #### buyer escrow fee presentation ##################################
	$fee = 0;
	
	// we'll populate the fee2 field which denotes any fees the buyer of this buynow purchase
	// must pay the site owner .. we'll also calculate any tax if applicable to ensure that the
	// fee to the buyer will include the full fee amount + any applicable taxes (for commission txns)
   
	// #### escrow commission fees to buyer enabled? ###############
	if (($res['filter_escrow'] == '1' AND $res['filter_offline'] == '0' AND $res['filter_gateway'] == '0' AND $ilconfig['escrowsystem_bidderfixedprice'] > 0) OR (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == 'escrow' AND $ilconfig['escrowsystem_bidderfixedprice'] > 0))
	{
		// fixed escrow cost to buyer
		$fee = sprintf("%01.2f", $ilconfig['escrowsystem_bidderfixedprice']);
	}
	else
	{
		if (($res['filter_escrow'] == '1' AND $res['filter_offline'] == '0' AND $res['filter_gateway'] == '0' AND $ilconfig['escrowsystem_bidderpercentrate'] > 0) OR (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == 'escrow' AND $ilconfig['escrowsystem_bidderpercentrate'] > 0))
		{
			// percentage rate of total winning bid amount
			// which would be the same as the amount being forwarded into escrow
			$fee = sprintf("%01.2f", ($total * $ilconfig['escrowsystem_bidderpercentrate'] / 100));
		}
	}
	
	 
		$taxamount = 0;
		// murugan changes for nuy now blow on Feb 22 
		/*if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'commission') or $ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'buynow'))
		{
		
			// fetch tax amount to charge for this invoice type
			$taxamount = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $fee, 'commission', 0);
			// #### exact amount to charge buyer ###########################
			$fee = sprintf("%01.2f", ($fee + $taxamount));
			$taxamount+= $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $total, 'buynow', 0);
			$tax = $ilance->currency->format($taxamount, $res['currencyid']);
			$show['taxbit'] = true;
			
		}*/
		
		
	 
 
	
	
	
	// #### total plus escrow fee ##########################################
	$total = ($total + $taxamount);
	$topay = $ilance->currency->format($total, $res['currencyid']);
	$fees = ($fee > 0)
		? $ilance->currency->format($fee)
		: '-';
	
	$ilance->GPC['paymethod'] = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : '';
	if ($methodscount == 1 AND empty($ilance->GPC['paymethod']))
	{
		$ilance->GPC['paymethod'] = print_payment_method_title($project_id);
	}
	
	// #### payment method pulldown ########################################
	if (($res['filter_escrow'] == '1' AND $res['filter_offline'] == '0' AND $res['filter_gateway'] == '0') OR (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == 'escrow'))
	{
		$ilance->GPC['paymethod'] = 'escrow';
		$show['sellerusingescrow'] = $show['filter_escrow'] = $show['depositlink'] = true;
		$payment_method_pulldown = print_paymethod_pulldown('account', 'account_id', $_SESSION['ilancedata']['user']['userid']);
	}
	else
	{
		$payment_method_pulldown = print_fixed_payment_method($ilance->GPC['paymethod']);	
	}
	
	// #### shipping address pulldown ######################################
	$shipping_address_pulldown = print_shipping_address_pulldown($_SESSION['ilancedata']['user']['userid']);
	$paymethod = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : '';
	$shipperid = isset($ilance->GPC['shipperid']) ? intval($ilance->GPC['shipperid']) : 0;
	$hiddeninput = array(
		'cmd' => '_instant-purchase-process',
		'project_id' => $project_id,
		'buyer_id' => $_SESSION['ilancedata']['user']['userid'],
		'seller_id' => $seller_id,
		'fee' => sprintf("%01.2f", $fee),
		'qty' => $qty,
		'total' => sprintf("%01.2f", $total),
		'amount' => $amount,
		'paymethod' => $ilance->GPC['paymethod'],
		'buyershipcost' => sprintf("%01.2f", $ilance->GPC['buyershipcost']),
		'shipperid' => $ilance->GPC['shipperid'],
	);
	
	$ilance->bid->bid_filter_checkup($project_id);
	
	$encrypted = encrypt_url($hiddeninput);
	$returnurl = urlencode($ilpage['merch'] . '?cmd=purchase-confirm&pid=' . intval($project_id) . '&qty=' . intval($ilance->GPC['qty']));
	
        $pprint_array = array('hidden_val','paymethod','shipperid','shippingservice','hiddenfields','returnurl','tax','paymethod','fees','digitalfile','cb_shipping_address_required1','cb_shipping_address_required0','encrypted','samount','amount_formatted','total','shipping_address_pulldown','forceredirect','payment_method_pulldown','attachment','project_id','seller_id','buyer_id','user_cookie','project_title','seller','qty','topay','amount','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','category','subcategory');
        
        ($apihook = $ilance->api('merch_purchase_confirm_end')) ? eval($apihook) : false;
        
        $ilance->template->fetch('main', 'listing_forward_auction_buynow.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
// #### ITEM CATEGORY LISTINGS #################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'listings')
{
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
//list all denominations
	$show['widescreen'] = true;
	$area_title = $phrase['_buy'] . ' - ' . $phrase['_viewing_all_categories'];
	$page_title = SITE_NAME . ' - ' . $phrase['_buy'] . ' - ' . $phrase['_viewing_all_categories'];
	$topnavlink = array('main_categories');
		$ilance->categories_parser=construct_object('api.categories_parser');
	$text="List of all Denominations / Categories";
	$categoryresults=$ilance->categories_parser->html_denomination();
	
	$check = $ilconfig['globalauctionsettings_seourls'];
    //new change
	
	//$search_category_pulldown=$ilance->categories_parser->demonomination_dropwdown('denominationid',0,true);
	$search_category_pulldown=$ilance->categories_parser->demonomination_dropwdown_new('denominationid',0,true);
	  $pprint_array = array('check','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
	$ilance->template->fetch('main', 'merch_denomination_listings.html'); 
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
}
//karthik changes for search on apr 27
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'go')
{
$show['widescreen'] = true;
 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
				 $scriptpageprevnext = $ilpage['merch'] . '?denomination='.$ilance->GPC['denomination'].'&cmd=go&q='. $ilance->GPC['q'] .'&series='.$ilance->GPC['series'].'&sort='.$ilance->GPC['sort'].'';
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
if (!empty($ilance->GPC['q']) AND !empty($ilance->GPC['series']))
{
     $condition ="AND(c.project_title LIKE '%" . $ilance->GPC['q'] . "%'OR c.project_id LIKE '%" . $ilance->GPC['q'] . "%' OR c.description LIKE '%" . $ilance->GPC['q'] . "%')AND     cc.coin_series_unique_no='".$ilance->GPC['series']."'";
	 
}
else if (!empty($ilance->GPC['q']) AND empty($ilance->GPC['series']))
{
   $condition ="AND(c.project_title LIKE '%" . $ilance->GPC['q'] . "%'OR c.project_id LIKE '%" . $ilance->GPC['q'] . "%' OR c.description LIKE '%" . $ilance->GPC['q'] . "%')AND    cc.coin_series_denomination_no='".$ilance->GPC['denomination']."'";
   
}
else if (!empty($ilance->GPC['series']) )
{
$condition="AND(cc.coin_series_unique_no='".$ilance->GPC['series']."')";
}
else
{
$condition="AND(cc.coin_series_denomination_no='".$ilance->GPC['denomination']."')";
}
if ($ilance->GPC['sort']=='31') 
{
$orderby ="ORDER BY c.Orderno ASC";
}
else if ($ilance->GPC['sort']=='32') 
{
$orderby ="ORDER BY c.Orderno DESC";
}
else if ($ilance->GPC['sort']=='01') 
{
$orderby ="ORDER BY c.date_starts ASC";
}
else if ($ilance->GPC['sort']=='02') 
{
$orderby ="ORDER BY c.date_starts DESC";
}
else if ($ilance->GPC['sort']=='11') 
{
$orderby ="ORDER BY c.currentprice ASC";
}
else if ($ilance->GPC['sort']=='12') 
{
$orderby ="ORDER BY c.currentprice DESC";
}
  $select_featurednew= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow				   
                                          from 
					                      " . DB_PREFIX . "catalog_coin cc, 
					                      " . DB_PREFIX . "catalog_second_level cs,
					                      " . DB_PREFIX . "projects c				
					                      where 
										  c.status = 'open'
										  $condition
										  AND c.visible = '1'
										   AND c.cid=cc.PCGS
										 group by c.project_id
										  $orderby  LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
                                           ");
				   $total_num1=$ilance->db->num_rows($select_featurednew);
				   if($total_num1)
				   {
						   $total_num=true;
				   }
				   if($ilance->db->num_rows( $select_featurednew) > 0)
				   {
                       $select_featurednew12= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow
																	   from 
																		" . DB_PREFIX . "catalog_coin cc, 
																		" . DB_PREFIX . "catalog_second_level cs,
																		" . DB_PREFIX . "projects c				
																		where 
																	   c.status = 'open'
																	    $condition
																	   AND c.visible = '1'
																	   AND c.cid=cc.PCGS
																	   group by c.project_id
                                                                   ");
				     $number = (int)$ilance->db->num_rows( $select_featurednew12);
	                 while($det=$ilance->db->fetch_array($select_featurednew))
				     {
				               $projectid=$det['project_id'].'<br>';
                               if ($ilconfig['globalauctionsettings_seourls'])	
								$listpageg['project_title'] ='<span class="blue"><a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $listpageg['project_title']='<span class="blue"><a href="merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
					            $listpageg['date_end']='<strong>' . $ilance->auction->auction_timeleft($det['project_id'], 'center') . '</strong>';
								$buynow=$det['buynow'];
								// Murugan changes on mar 22
								$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
								$respjt = $ilance->db->fetch_array($selectpjt);
							if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
                                 {
								 	if ($respjt['filtered_auctiontype'] == 'regular')
                                    {
										if ($respjt['bids'] > 0)
										{
											$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										}
										else
										{
											$listpageg['currentprice'] ='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
											$listpageg['currentprice'] .='' .'<br>Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '';
										}
										 $listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									}
									else if ($respjt['filtered_auctiontype'] == 'fixed')
                                     {
									 	$listpageg['currentprice'] ='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
										$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
									 }
								 }
								  else
									 {
									 	$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										$listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									 }  $sql_attya = $ilance->db->query("
																				SELECT * FROM
														
														
														
																				" . DB_PREFIX . "attachment
														
														
														
																				WHERE visible='1' 
														
														
														
																				AND project_id = '".$det['project_id']."'
														
														
														
																				AND attachtype='itemphoto' order by attachid desc
						
                        ");
				    $fetch_newa=$ilance->db->fetch_array($sql_attya);
					if($ilance->db->num_rows($sql_attya) > 0)
					{
						$uselistra = $ilpage['attachment'] . '?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper">
						<a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$uselistra.'" ></a> 
          </div>
        </div>
      </div>
    </div>
  </div>';
						else
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper"><a href="merch.php?id='.$det['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a> 
          </div>
        </div>
      </div>
    </div>
  </div>';
					}
					if($ilance->db->num_rows($sql_attya) == 0)
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $listpageg['imgval'] ='<a href="merch.php?id='.$det['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
									 	$listpage[]=$listpageg;	
									 }
									 
					 
						
							} 
							else
							{
							
							$total_num=false;
							
							$listpage[]='';	
							
							}
							
						$prof = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
							
						$search_category_pulldown=$ilance->categories_parser->series_dropwdown_new($ilance->GPC['denomination'],'series',0,true);
						
						$categoryresults=$ilance->categories_parser->html_coin_series($ilance->GPC['denomination']);
					
						$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
						
						$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', 'product');
									
						$pprint_array = array('prof','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','sortpulldown');
	                   $ilance->template->fetch('main', 'merch_series_listings.html'); 
	                   $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	                    $ilance->template->parse_loop('main', 'listpage');
	 //sekar listings on categoris finished 
						$ilance->template->parse_if_blocks('main');
				
					    $ilance->template->pprint('main', $pprint_array);
				
					    exit();
				
}
//karthik end
// #### COIN SERIES LISTINGS #################################################
else if(isset($ilance->GPC['denomination']) AND $ilance->GPC['denomination'] >0)
{
echo '1';

$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
//list series for selected denominations
	$show['widescreen'] = true;
	$area_title = $phrase['_viewing_all_categories'];
	$page_title = SITE_NAME . ' - ' . $phrase['_viewing_all_categories'];
	$topnavlink = array('main_categories');
	$ilance->categories_parser=construct_object('api.categories_parser');
	$denomination_details=$ilance->categories_parser->fetch_denominations($ilance->GPC['denomination']);
	$text=$denomination_details['denomination_long'];
	$categoryresults=$ilance->categories_parser->html_coin_series($ilance->GPC['denomination']);
	$navcrumb = array("$ilpage[merch]" => $text);	
//new change
	//$search_category_pulldown=$ilance->categories_parser->series_dropwdown($ilance->GPC['denomination'],'series_id',0,true);
	
	$search_category_pulldown=$ilance->categories_parser->series_dropwdown_new($ilance->GPC['denomination'],'series',0,true);
	
	
	//sekar listings on categoris
	
	//$ilance->GPC['denomination'];
//sekar working for search in merch on june 01
	
	if (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'])
				{
				     $featured=" AND c.buynow='1' ";
				}
				else if (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'])
				{
				     $featured=" AND c.buynow = '0' ";
				}
				else
				{
				    $featured='';
				}
		
		
	if(isset($ilance->GPC['bidrange']))
	{
	if($ilance->GPC['bidrange']=='1')
	{
	$bidrng="AND c.bids < 5";
	}
	else if($ilance->GPC['bidrange']=='2')
	{
	$bidrng="AND c.bids < 10 AND c.bids > 5";
	}
	else if($ilance->GPC['bidrange']=='3')
	{
	$bidrng="AND c.bids > 10";
	}
	}
	else
	{
	$bidrng=" ";
	}	
			
		echo '2';		
	if(isset($ilance->GPC['completed']))
	{
	  if($ilance->GPC['completed']=='1')
	   {
	   $comp="c.status = 'expired'";
	   }
	}
	else
	{
	$comp="c.status = 'open'";
	}
	
	
		//sekar working on price on june02
	
	
		//for price



  $sqlquery['pricerange'] = $clear_price = '';
                        if ($ilance->GPC['mode'] == 'product')
                        {
					
                                if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0)
                                {
								
								
					$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
					$removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
					$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
					
                                        $sqlquery['pricerange'] .= "AND (c.currentprice >= " . intval($ilance->GPC['fromprice']) . " ";
                                        handle_search_verbose('<span class="black"><!--' . $phrase['_min_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong></span> &ndash; ');
                                        handle_search_verbose_save($phrase['_min_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong>, ');
                                }
                                else
                                {
                                        $sqlquery['pricerange'] .= "AND (c.currentprice >= 0 ";
                                }
                                
                                if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0)
                                {
					$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
                                        $removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
					$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
					
                                        $sqlquery['pricerange'] .= "AND c.currentprice <= " . intval($ilance->GPC['toprice']) . ") ";
                                        handle_search_verbose('<span class="black"><!--' . $phrase['_max_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                        handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong>, ');
                                }
                                else
                                {
                                        $sqlquery['pricerange'] .= ")";
                                        handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $phrase['_unlimited'] . '</strong>, ');
                                }
                        }
                $con=$sqlquery['pricerange'];
				
	//sekar finished working on price on june02
	
  //counter for page 
                 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
				 $scriptpageprevnext = $ilpage['merch'] . '?denomination='.$ilance->GPC['denomination'];
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
	    
	
	
	
	
        $scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
		
		
		
		
	
				   
				    $select_featurednew= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow,c.highlite,c.bold
				   from 
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs,
					" . DB_PREFIX . "projects c				
					
					where 
				   $comp
				   $featured
				   $bidrng
				   AND c.visible = '1'
				   $con
				   AND cc.coin_series_denomination_no='".$ilance->GPC['denomination']."'
				   AND c.cid=cc.PCGS
                   group by c.project_id
				   ORDER BY c.date_end ASC LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
                   ");
				   
				   
				    $select_featurednew12= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow,c.highlite,c.bold
				   from 
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs,
					" . DB_PREFIX . "projects c				
					
					where 
				   $comp
				   $featured
				   $bidrng
				   AND c.visible = '1'
				   $con
				   AND cc.coin_series_denomination_no='".$ilance->GPC['denomination']."'
				   AND c.cid=cc.PCGS
                   group by c.project_id
				   ORDER BY c.date_end ASC ");
				   
				
				   
				   $total_num1=$ilance->db->num_rows($select_featurednew);
				   if($total_num1)
				   {
				   $total_num=true;
				   }
				      if($ilance->db->num_rows( $select_featurednew) > 0)
				{
				     $number = (int)$ilance->db->num_rows( $select_featurednew12);
				   
			  
	              while($det=$ilance->db->fetch_array($select_featurednew))
				   {
				                $projectid=$det['project_id'];
					
                               if ($ilconfig['globalauctionsettings_seourls'])	
								$listpageg['project_title'] ='<span class="blue"><a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $listpageg['project_title']='<span class="blue"><a href="merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
					           
					            $listpageg['date_end']='<strong>' . $ilance->auction->auction_timeleft($det['project_id'], 'center') . '</strong>';
								$buynow=$det['buynow'];
								
								// Murugan changes on mar 22
								$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
								$respjt = $ilance->db->fetch_array($selectpjt);
							if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
                                 {
								 	if ($respjt['filtered_auctiontype'] == 'regular')
                                    {
										if ($respjt['bids'] > 0)
										{
											$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										}
										else
										{
											$listpageg['currentprice'] ='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
											$listpageg['currentprice'] .='' .'<br>Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '';
										}
										 $listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
										 
									}
									else if ($respjt['filtered_auctiontype'] == 'fixed')
                                     {
									 	$listpageg['currentprice'] ='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
										$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
									 }
									
								 }
								  else
									 {
									 	$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										$listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									 }
								
								
								/*$cat=$ilance->db->query("select bids from " . DB_PREFIX . "projects where buynow='0' AND project_id= '".$projectid."'");
								 if($ilance->db->num_rows($cat) > 0)
									{
										$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($det['currentprice']). '</strong>';
									}
									else
									{
										$listpageg['currentprice']='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($det['currentprice'],$currencyid). '</strong>';
									}
					            //$listpageg['currentprice']='<strong>' .$ilance->currency->format($det['currentprice'], $currencyid). '</strong>';
							    
								
		       $type=$ilance->db->query("select bids from " . DB_PREFIX . "projects where $buynow='0' AND project_id= '".$projectid."'");
		       $fet=$ilance->db->fetch_array($type);
			   if($ilance->db->num_rows($type)>0)
			   {
			   $listpageg['bids']='<span class="blue">'.$fet['bids'].' '.'Bids</span>';
			   }
			   else
			   {
			   $listpageg['bids']='<span class="blue">Buy<br>Now</span>';
			   }*/
		                        		
								
					   $sql_attya = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$det['project_id']."'
						AND attachtype='itemphoto' order by attachid desc
						
                        ");
				    $fetch_newa=$ilance->db->fetch_array($sql_attya);
				
					if($ilance->db->num_rows($sql_attya) > 0)
					{
						$uselistra = $ilpage['attachment'] . '?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper">
						<a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$uselistra.'" ></a> 
          </div>
        </div>
      </div>
    </div>
  </div>';
						else
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper"><a href="merch.php?id='.$det['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a> 
          </div>
        </div>
      </div>
    </div>
  </div>';
						
						
					}
					if($ilance->db->num_rows($sql_attya) == 0)
				
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $listpageg['imgval'] ='<a href="merch.php?id='.$det['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
						
					   //$listpageg['yy']=$htm;
					   // sekar changes on june 1
					   $listpageg['class']=($det['highlite'] == '1') ? 'featured_highlight' : '';
					   $listpageg['class1']=($det['bold'] =='1')?'bold_highlight' : '';
					     
					   $listpage[]=	$listpageg;	
			   
				
			   
             }
                   }
						$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
						$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', 'product');
						
						//amutha changes	
					$cid>0;
					$denomination=$ilance->GPC['denomination'];
					//given mode as product
					$leftnav_options = print_options1('product',$denomination);
					$search_bidrange_pulldown_product = print_bid_range_pulldown1($ilance->GPC['bidrange'], 'bidrange', 'productbidrange', 'links',$denomination);
					
					$denomination=$ilance->GPC['denomination'];
					$leftnav_buyingformats = print_buying_formats1($denomination);
					
				/*	$clear_listtype = ($show['allbuyingformats'])
				? ''
				: '<a href="' . $clear_listtype_url . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
				
				$clear_bidrange = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';*/

			//sekar working on searc same page on may28
			
                                $v3left_nav = $ilance->template->print_left_nav1('product', $cid, $dosubcats = 1, $displayboth = 0, $ilconfig['globalfilters_enablecategorycount'], true);
								
								
								
								                        ($apihook = $ilance->api('search_results_providers_end')) ? eval($apihook) : false;
                        
                        $ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
                        $sortpulldown2 = print_sort_pulldown($ilance->GPC['sort'], 'sort', $expertsmode = true);
                        
                        $hiddenfields = print_hidden_fields(false, array('searchid','cid','isonline','images','portfolios','city','state','zip_code','endstart','endstart_filter','q','sort','page'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
                        $hiddenfields_leftnav = print_hidden_fields(false, array('searchid','feedback','country','isonline','images','portfolios','city','state','zip_code','endstart','endstart_filter','page','radius','radiuscountry','radiuszip'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
		
                       //amutha changes	end
					   
					   //sekar working on featured on june o2
					   
					    ///// featured sekar
					   
				  
					   //featured
					   $count_fea=1;
					   
					   
					   $featured_select1= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow
				   from 
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs,
					" . DB_PREFIX . "projects c				
					
					where 
				   $comp
				   $featured
				   $bidrng
				   
				   AND c.visible = '1'
				   $con
				   AND cc.coin_series_denomination_no='".$ilance->GPC['denomination']."'
				   AND c.cid=cc.PCGS
				   AND featured = '1'
				   AND c.cid !='0'
                   group by c.project_id
				   ORDER BY c.date_end ASC LIMIT 4
                   ");
				   
				   
			
				   $total_num1=$ilance->db->num_rows($featured_select1);
				   if($total_num1)
				   {
				   $total_num=true;
				   }
				      if($ilance->db->num_rows( $featured_select1) > 0)
				{
				$c=0;
				
				//$sep = '<div id="seperator" style="height:165px;"></div>';
				     $number = (int)$ilance->db->num_rows( $featured_select2);
				   
				   $show['featured']=1;
			  $table_featured='<tr>';
			  
	              while($det=$ilance->db->fetch_array($featured_select1))
				   {
				   if($c < 1)
				$sep = '';
				else
				$sep = '<div id="seperator" style="height:165px;"></div>';
				
				             $projectid=$det['project_id'];
					//karthik may03
					  if (!empty($_SESSION['ilancedata']['user']['userid']))
                                {
								  
                                    $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($det['project_id'], $_SESSION['ilancedata']['user']['userid']);
								  	
									if ($pbit > 0)
                                    {
																		
											 $highbidderidtest = $ilance->bid->fetch_highest_bidder($det['project_id']);
																										// murugan on feb 25
										if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
										$listpageg['proxybit'] = '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>';
										else
										$listpageg['proxybit'] ='<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>
';                                          $show['proxy']=true;
																		
																		}
                                                                                        //unset($pbit);
                                                                                }
																				//karthik end may03
					
                               if ($ilconfig['globalauctionsettings_seourls'])	
								$listpageg['project_title'] ='<span class="blue"><a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $listpageg['project_title']='<span class="blue"><a href="merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
					           
					            $listpageg['date_end']='<strong>' . $ilance->auction->auction_timeleft($det['project_id'], 'center') . '</strong>';
								$buynow=$det['buynow'];
								
								// Murugan changes on mar 22
								
								
								$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
								$respjt = $ilance->db->fetch_array($selectpjt);
							if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
                                 {
								 	if ($respjt['filtered_auctiontype'] == 'regular')
                                    {
										if ($respjt['bids'] > 0)
										{
											$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										}
										else
										{
											$listpageg['currentprice'] ='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
											$listpageg['currentprice'] .='' .'<br>Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '';
										}
										 $listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
										 
									}
									else if ($respjt['filtered_auctiontype'] == 'fixed')
                                     {
									 	$listpageg['currentprice'] ='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
										$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
									 }
									
								 }
								  else
									 {
									 	$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										$listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									 }
								
								
			
		                        		
								
					   $sql_attya = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$det['project_id']."'
						AND attachtype='itemphoto' order by attachid desc
						
                        ");
				    $fetch_newa=$ilance->db->fetch_array($sql_attya);
				
					if($ilance->db->num_rows($sql_attya) > 0)
					{
						$uselistra = $ilpage['attachment'] . '?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper">
						<a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255); margin-left:32px;"  src="'.$uselistra.'" ></a> 
          </div>
        </div>
      </div>
    </div>
  </div>';
						else
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper"><a href="merch.php?id='.$det['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px; margin-left:32px;"></a> 
          </div>
        </div>
      </div>
    </div>
  </div>';
						
						
					}
					if($ilance->db->num_rows($sql_attya) == 0)
				
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $listpageg['imgval'] ='<a href="merch.php?id='.$det['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
						
					   //$listpageg['yy']=$htm; 
					   
					   //featured 
					
					   
					   $table_featured.='<td><table> <tr><td >'.$listpageg['imgval'].'</td></tr><tr><td>'.$listpageg['project_title'].'</td></tr><tr><td>'.$listpageg['currentprice'].'</td></tr><tr><td>'.$listpageg['date_end'].'</td></tr></table></td>';
					   
					   $c++;
					  
					  
					    
						
					   $count_fea++;
					  //featured div   
					  
			 
				
			   
             }
                   }
					   //sekar finishing working on featured on june o2
					
						
	$pprint_array = array('prof','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','sortpulldown','listing','clear_bidrange','clear_listtype','leftnav_buyingformats','search_bidrange_pulldown_product','v3left_nav','prof','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','sortpulldown','hiddenfields','clear_budgetrange','clear_currencies','leftnav_currencies','clear_local','clear_feedback','leftnav_feedbackrange','leftnav_ratingrange','clear_rating','clear_award','leftnav_awardrange','sort','country','clear_price','clear_options','leftnav_options','leftnav_options','showallurl','clear_region','leftnav_regions','full_country_pulldown','didyoumean','search_radius_country_pulldown_experts','search_country_pulldown_experts','favtext','favoritesearchurl','profilebidfilters','fewer_keywords','fromprice','toprice','hiddenfields_leftnav','city','state','zip_code','radiuszip','mode','search_country_pulldown2','hiddenfields','search_results_table','sortpulldown2','keywords','two_column_category_vendors','keywords','php_self','php_self_urlencoded','pfp_category_left','pfp_category_js','rfp_category_left','rfp_category_js','input_style','search_country_pulldown','search_jobtype_pulldown','five_last_keywords_buynow','five_last_keywords_projects','five_last_keywords_providers','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','search_category_pulldown','input_style','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','table_featured');
       //sekar finished on june 01
	$ilance->template->fetch('main', 'merch_series_listings.html'); 
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_loop('main', 'listpage');
	 //sekar listings on categoris finished 
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
}


// #### COINS CATEGORIES LISTINGS #################################################
else if(isset($ilance->GPC['series']) AND $ilance->GPC['series'] >0 AND !isset($ilance->GPC['cid']))
{
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
//list series for selected denominations
	$show['widescreen'] = true;
	$area_title = $phrase['_buy'] . ' - ' . $phrase['_viewing_all_categories'];
	$page_title = SITE_NAME . ' - ' . $phrase['_buy'] . ' - ' . $phrase['_viewing_all_categories'];
	$topnavlink = array('main_categories');
	
	$ilance->categories_parser=construct_object('api.categories_parser');
	$series_details=$ilance->categories_parser->fetch_coin_series(0,$ilance->GPC['series']);
	$text=$series_details['coin_series_name'];
	$categoryresults=$ilance->categories_parser->html_coin_class($ilance->GPC['series']);
	$search_category_pulldown=$ilance->categories_parser->coin_class_dropwdown($ilance->GPC['series'],0,'cid',0,true);
	$pprint_array = array('seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'merch_coin_listings.html'); 
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
}
// #### PRODUCT AUCTION CATEGORY LISTINGS VIA CATEGORY ID ######################
else if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0 AND empty($ilance->GPC['cmd']))
{
        // update category view count
        add_category_viewcount(intval($ilance->GPC['cid']));
        
        $ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = false);
        
        if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', intval($ilance->GPC['cid'])))
        {
                $urlbit = print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = false);
                header('Location: ' . $ilpage['search'] . '?mode=product' . $urlbit);
                exit();
        }
        
        $urlbit = print_hidden_fields($string = true, $excluded = array('cid'), $questionmarkfirst = false);
        header('Location: ' . $ilpage['merch'] . '?cmd=listings&cid=' . intval($ilance->GPC['cid']) . $urlbit);
        exit();
}
// #### ITEM REVISION LOG ######################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'revisionlog' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
        $page_title = SITE_NAME . ' - ' . $phrase['_listing_revision_details'];
	$area_title = $phrase['_listing_revision_details'];
        $id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
        $navcrumb = array();
        $navcrumb["$ilpage[merch]?id=" . $id] = $id;
        $navcrumb[""] = $phrase['_listing_revision_details'];
        
        $returnurl = $ilpage['merch'] . '?id=' . $id;
        
        $sql = $ilance->db->query("
                SELECT datetime, changelog
                FROM " . DB_PREFIX . "projects_changelog
                WHERE project_id = '" . $id . "'
                ORDER BY id DESC
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                // murugan changes on mar30 
				//$show['revision'] = true;
                $row_count = 0;
                while ($rows = $ilance->db->fetch_array($sql))
                {
                        $rows['datetime'] = print_date($rows['datetime'], $ilconfig['globalserverlocale_globaltimeformat'], 1, 1, 0, 1);
                        $rows['info'] = stripslashes($rows['changelog']);
                        $rows['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                        $revisions[] = $rows;
                        $row_count++;
                }
        }
        else
        {
                $show['revision'] = false;
        }
        
        $ilance->template->fetch('main', 'listing_revision_log.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'revisions');
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', array('returnurl','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
        exit();
}
// #### OTHER ##################################################################
else
{
	// #### BAD AUCTION ID PAGE ############################################
	if (empty($ilance->GPC['id']) OR $ilance->GPC['id'] == 0)
	{
		$area_title = $phrase['_bad_rfp_warning_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_bad_rfp_warning_menu'];
                
		print_notice($phrase['_invalid_rfp_specified'], $phrase['_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal'], $ilpage['search'], $phrase['_search_rfps']);
                exit();
	}
        
        // #### DETAILED AUCTION LISTING #######################################
	else
	{
                $show['widescreen'] = true;
		
		$ilance->bid = construct_object('api.bid');
		//$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
		$ilance->bid_proxy = construct_object('api.bid_proxy');
		$ilance->bid_permissions = construct_object('api.bid_permissions');
		$ilance->subscription = construct_object('api.subscription');
		//$ilance->feedback = construct_object('api.feedback');
        //$ilance->feedback_rating = construct_object('api.feedback_rating');
		$ilance->auction = construct_object('api.auction');
        //$categorycache = $ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1);
		
		$project_id = intval($ilance->GPC['id']);
		
		$sql = $ilance->db->query("
			SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, s.ship_method, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(p.date_starts) AS start
			FROM " . DB_PREFIX . "projects p
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
			WHERE p.project_id = '" . intval($ilance->GPC['id']) . "'
			    AND p.project_state = 'product'
			    " . (($ilconfig['globalauctionsettings_payperpost'])
				 ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))"
				 : "AND p.visible = '1'") . "
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        
                        $auction_start = $res['start'];
                        
                        // #### prevent duplicate content from search engines 
                        if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef'])))
                        {
                                 $seourl = construct_seo_url('productauctionplain', $res['cid'], $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0, $removevar = '');
                                $view = isset($ilance->GPC['view']) ? '?view=' . $ilance->GPC['view'] : '';
                                header('Location: ' . $seourl . $view);
                                unset($seourl);
                                exit();
                        }
                        
                        ($apihook = $ilance->api('merch_detailed_start')) ? eval($apihook) : false;
                        
                        $views = $res['views'];
                        
                        // #### revision details ###############################
                        $updateid = $res['updateid'];
                        $show['revision'] = false;
                        if ($updateid > 0)
                        {
                                // murugan changes on mar30 
								//$show['revision'] = true;
                                $updateid = '<a href="' . $ilpage['merch'] . '?cmd=revisionlog&amp;id=' . $res['project_id'] . '" style="text-decoration:underline">' . ($res['updateid']) . '</a>';
                        }
                        
                        // #### bid increments in this category ################
                        $show['increments'] = false;
                        $increment = '';
                        $cbid = $ilance->bid->fetch_current_bid($res['project_id'], $noformat = 1);
                        $incrementgroup = $ilance->categories->incrementgroup($res['cid']);
                        $sqlincrements = $ilance->db->query("
                                SELECT amount
                                FROM " . DB_PREFIX . "increments
                                WHERE ((increment_from <= $cbid
                                        AND increment_to >= $cbid)
                                                OR (increment_from < $cbid
                                        AND increment_to < $cbid))
                                        AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
                                ORDER BY amount DESC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlincrements) > 0)
                        {
                                $show['increments'] = true;
                                
                                // increment
                                $resincrement = $ilance->db->fetch_array($sqlincrements, DB_ASSOC);
                                $increment = $ilance->currency->format($resincrement['amount'], $res['currencyid']) . ' - <span class="blue"><a href="javascript:void(0)" onclick="Attach(\'' . HTTP_SERVER . $ilpage['rfp'] . '?msg=bid-increments&amp;c=' . $res['cid'] . '\')">' . $phrase['_view_bid_increments'] . '</a></span>';
                        }
                        
			// #### payment methods accepted #######################
			$paymentmethods = print_payment_methods($res['project_id']);
			
			// #### recently viewed auction cookie saver ###########
			if (!empty($_COOKIE[COOKIE_PREFIX . 'productauctions']))
			{
				$arr = explode('|', $_COOKIE[COOKIE_PREFIX . 'productauctions']);
				if (!in_array(intval($ilance->GPC['id']), $arr))
				{
					$_COOKIE[COOKIE_PREFIX . 'productauctions'] = $_COOKIE[COOKIE_PREFIX . 'productauctions'] . '|' . intval($ilance->GPC['id']);
					set_cookie('productauctions', $_COOKIE[COOKIE_PREFIX . 'productauctions'], true);
				}
			}
			else
			{
				$_COOKIE[COOKIE_PREFIX . 'productauctions'] = intval($ilance->GPC['id']);
				set_cookie('productauctions', $_COOKIE[COOKIE_PREFIX . 'productauctions'], true);
			}
				    
/*			// #### public message board ###########################
			$show['publicboard'] = $msgcount = 0;
			if ($res['filter_publicboard'])
			{
				$show['publicboard'] = 1;
                                
				$sqlmessages = $ilance->db->query("
					SELECT messageid, date, message, project_id, user_id, username
					FROM " . DB_PREFIX . "messages
					WHERE project_id = '" . $res['project_id'] . "'
					ORDER BY messageid ASC
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlmessages) > 0)
				{
					$msgcount = 0;
					while ($message = $ilance->db->fetch_array($sqlmessages))
					{
						$message['date'] = print_date($message['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                                $message['message'] = ($message['user_id'] == $res['user_id']) ? '<span class="green">[' . $phrase['_seller'] . ']</span> ' . strip_vulgar_words(ilance_htmlentities($message['message'])) . '' : '<span class="blue">[' . $phrase['_bidder'] . ']</span> ' . strip_vulgar_words(ilance_htmlentities($message['message'])) . '';
						$message['message'] = strip_vulgar_words($message['message']);
						$message['class'] = ($msgcount % 2) ? 'alt2' : 'alt1';
						$messages[] = $message;	
						$msgcount++;
					}
				}
			}*/
				
			//page view kkk	 
			if(fetch_auction('status',intval($ilance->GPC['id'])) != 'expired')
			{
			  
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET views = views + 1
				WHERE project_id = '" . intval($ilance->GPC['id']) . "'
				    AND status != 'draft'
			", 0, null, __FILE__, __LINE__);
			}
			
			$row_count = 0;
	    
			// photo slideshow
			// we use '-5' to determine the image as the main product auction image so let's not include in slideshow
			$show['slideshow'] = false;
           /*             
			$slideimage1 = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
			$slideshowpulldown = '<select id="slidepulldown" name="slide" onchange="change();" style="font-family: verdana" class="smaller">';
			$project_attachment = $phrase['_no_attachments_available'];
			$firstslide = '';
			$imagecount = 0;
			
			$sql_attachments = $ilance->db->query("
				SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
				FROM " . DB_PREFIX . "attachment
				WHERE project_id = '" . intval($ilance->GPC['id']) . "'
				    AND tblfolder_ref != '-5'
                                    AND attachtype != 'digital'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_attachments) > 0)
			{
				while ($res2 = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
				{
					$imagecount++;
					if ($imagecount == 1)
					{
						$firstslide = 'selected="selected"';
                                                $slideimage1 = $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $res2['filehash'] .'&w=400&h=400';
					}
					
					if ($res2['visible'])
					{
						switch (fetch_extension($res2['filename']))
						{
							case 'gif':
                                                        {
                                                                $slideshowpulldown .= '<option value="' . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $res2['filehash'] . '&w=' . $ilconfig['portfoliodisplay_popups_width'] . '&h=' . $ilconfig['portfoliodisplay_popups_height'] . '" ' . $firstslide . '>' . $res2['filename'] . ' (' . $res2['filesize'] . ' ' . $phrase['_bytes'] . ')</option>';
                                                                break;
                                                        }			    
							case 'jpg':
                                                        {
                                                                $slideshowpulldown .= '<option value="' . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $res2['filehash'] . '&w=' . $ilconfig['portfoliodisplay_popups_width'] . '&h=' . $ilconfig['portfoliodisplay_popups_height'] . '" ' . $firstslide . '>' . $res2['filename'] . ' (' . $res2['filesize'] . ' ' . $phrase['_bytes'] . ')</option>';
                                                                break;
                                                        }			    
							case 'png':
                                                        {
                                                                $slideshowpulldown .= '<option value="' . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $res2['filehash'] . '&w=' . $ilconfig['portfoliodisplay_popups_width'] . '&h=' . $ilconfig['portfoliodisplay_popups_height'] . '" ' . $firstslide . '>' . $res2['filename'] . ' (' . $res2['filesize'] . ' ' . $phrase['_bytes'] . ')</option>';
                                                                break;
                                                        }			    
							default:
                                                        {
                                                                // not an image -- send to normal attachments box for user to click and download
                                                                $project_attachment .= '<span class="blue"><a href="' . $ilpage['attachment'] . '?id=' . $res2['filehash'] . '" target="_blank">' . $res2['filename'] . '</a></span> (' . $res2['filesize'] . ' ' . $phrase['_bytes'] . ')<br />';
                                                                break;
                                                        }
						}
					}
					else
					{
						if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
						{
							// are we owner?
							if ($_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
							{
								// viewing auction as owner (and the images are not moderated as of yet.. show to owner anyways!)
								switch (fetch_extension($res2['filename']))
								{
									case 'gif':
                                                                        {
                                                                                $slideshowpulldown .= '<option value="' . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $res2['filehash'] . '&w=' . $ilconfig['portfoliodisplay_popups_width'] . '&h=' . $ilconfig['portfoliodisplay_popups_height'] . '" ' . $firstslide . '>'.$res2['filename'].' ('.$res2['filesize'].' '.$phrase['_bytes'].') ['.$phrase['_review_in_progress'].']</option>';
                                                                                break;
                                                                        }				    
									case 'jpg':
                                                                        {
                                                                                $slideshowpulldown .= '<option value="' . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $res2['filehash'] . '&w=' . $ilconfig['portfoliodisplay_popups_width'] . '&h=' . $ilconfig['portfoliodisplay_popups_height'] . '" ' . $firstslide . '>'.$res2['filename'].' ('.$res2['filesize'].' '.$phrase['_bytes'].') ['.$phrase['_review_in_progress'].']</option>';
                                                                                break;
                                                                        }				    
									case 'png':
                                                                        {
                                                                                $slideshowpulldown .= '<option value="' . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $res2['filehash'] . '&w=' . $ilconfig['portfoliodisplay_popups_width'] . '&h=' . $ilconfig['portfoliodisplay_popups_height'] . '" ' . $firstslide . '>'.$res2['filename'].' ('.$res2['filesize'].' '.$phrase['_bytes'].') ['.$phrase['_review_in_progress'].']</option>';
                                                                                break;
                                                                        }				    
									default:
                                                                        {
                                                                                // not an image -- send to normal attachments box for user to click and download
                                                                                $project_attachment .= '<a href="' . $ilpage['attachment'] . '?id=' . $res2['filehash'] . '" target="_blank">' . $res2['filename'] . '</a> (' . $res2['filesize'] . ' ' . $phrase['_bytes'] . ') [<em>' . $phrase['_review_in_progress'] . '</em>]<br />';
                                                                                break;
                                                                        }
								}
							}
							else
							{
								// some guest or member viewing .. images are not yet moderated by staff... don't show
								$project_attachment .= $res2['filename'] . ' (' . $res2['filesize'] . ' ' . $phrase['_bytes'] . ') [<em>' . $phrase['_review_in_progress'] . '</em>]<br />';
							}
						}
					}
				}
			}
			else
			{
				$slideshowpulldown .= '<option value="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif">' . $phrase['_no_attachments_available'] . '</option>';
			}
                        
			$slideshowpulldown .= '</select>';
                        
                        if ($imagecount <= 1)
                        {
                                $show['slideshow'] = false;        
                        }
			else if ($imagecount > 1)
			{
				$show['slideshow'] = true;
				$show['productimage'] = true;
			}
	    
			// double check to see if media exists other than images (for attachments box)
			if ($project_attachment == '')
			{
				$project_attachment = $phrase['_no_attachments_available'];
			}
	    */
                        $date_starts = print_date($res['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
            
			// additional auction information
			$additional_info = stripslashes($res['additional_info']);
			
			$show['livebid'] = false;
			$show['bidderuninvited'] = false;
                        
			switch ($res['project_details'])
			{
				case 'public':
                                {
                                        // does admin require members to be logged in before viewing full description?
                                        $ilance->bbcode = construct_object('api.bbcode');
                                        $description = strip_vulgar_words($res['description']);
                                        $description = $ilance->bbcode->bbcode_to_html($description);
                                        $description = print_string_wrap($description, 50);
                                        break;
                                }			    
				case 'invite_only':
                                {
                                        // does admin require members to be logged in before viewing full description?
                                        $show['bidderuninvited'] = true;
                                        if (empty($_SESSION['ilancedata']['user']['userid']))
                                        {
                                                $ilance->bbcode = construct_object('api.bbcode');
                                                $description = strip_vulgar_words($res['description']);
                                                $description = $ilance->bbcode->bbcode_to_html($description);
                                                $description = print_string_wrap($description, 50);
                                        }
                                        else
                                        {
                                                // fetch invites
                                                $sql_invites = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "project_invitations
                                                        WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                                            AND seller_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_invites) > 0)
                                                {
                                                        $show['bidderuninvited'] = false;
                                                        $ilance->bbcode = construct_object('api.bbcode');
                                                        $description = strip_vulgar_words($res['description']);
                                                        $description = $ilance->bbcode->bbcode_to_html($description);
                                                        $description = print_string_wrap($description, 50);
                                                }
                                                else
                                                {
                                                        $ilance->bbcode = construct_object('api.bbcode');
                                                        $description = strip_vulgar_words($res['description']);
                                                        $description = $ilance->bbcode->bbcode_to_html($description);
                                                        $description = print_string_wrap($description, 50);
                                                }                                       
                                        }
                                        break;
                                }			    
				case 'realtime':
                                {
                                        // does admin require members to be logged in before viewing full description?
                                        // is realtime auction (show flash mx applet)?
                                        $show['livebid'] = 1;
                                        
                                        // livebid flash applet
                                        if (!empty($_SESSION['ilancedata']['user']['userid']))
                                        {
                                                $bidapplet = '<div id="applet' . $res['project_id'] . '"></div>
<script type="text/javascript">
var fo2 = new FlashObject("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/' . DIR_SWF_NAME . '/livebid.swf", "applet' . $res['project_id'] . '", "730", "530", "8,0,0,0", "#ffffff");
fo2.addParam("quality", "high");
fo2.addParam("allowScriptAccess", "sameDomain");
fo2.addParam("flashvars", "languageConfig=' . DIR_FUNCT_NAME . '/' . DIR_XML_NAME . '/livebid_' . $_SESSION['ilancedata']['user']['slng'] . '.xml&prId=' . $res['project_id'] . '&sId=' . session_id() . '&rand=' . rand(100000, 999999) . '");
fo2.addParam("menu", "false");
</script>';
                                        }
                                        else 
                                        {
                                                $bidapplet = '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['login'] . '">' . $phrase['_become_a_registered_member_to_view_and_place_realtime_bids_using_our_brand_new_realtime_bid_interface'] . '</a></span>';	
                                        }
                                        
                                        $ilance->bbcode = construct_object('api.bbcode');
                                        $description = strip_vulgar_words($res['description']);
                                        $description = $ilance->bbcode->bbcode_to_html($description);
                                        $description = print_string_wrap($description, 50);
                                        break;
                                }			    
				case 'unique':
                                {
                                        $retailprice = $ilance->currency->format($res['retailprice'], $res['currencyid']);
                                        $ilance->bbcode = construct_object('api.bbcode');
                                        $description = strip_vulgar_words($res['description']);
                                        $description = $ilance->bbcode->bbcode_to_html($description);
                                        $description = print_string_wrap($description, 50);
                                        break;
                                }
			}
				    
			// vulgar censor for description
			$description = isset($description) ? $description : $phrase['_no_description'];
	    
			// filtered auction type specified by seller
			// used as an template if condition
			if ($res['filtered_auctiontype'] == 'fixed')
			{
				$auctiontype = 'fixed';
				$sel_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
												WHERE user_id = '".$_SESSION['ilancedata']['user']['userid']."'
												AND projectid = '".$ilance->GPC['id']."'");
								if($ilance->db->num_rows($sel_inv) > 0)
								{
									$res_inv = $ilance->db->fetch_array($sel_inv);
									if($res_inv['status'] == 'cancelled')
									{
										$transactionstatus = 'The transaction associated with this listing was combined with another Invoice.';
									}
									else if($res_inv['status'] == 'unpaid')
									{
										$transactionstatus = $phrase['_the_transaction_associated_with_this_listing_has_not_been_paid'];
									}
									else
									{
									  	 $transactionstatus = $ilance->bid->fetch_transaction_status(intval($ilance->GPC['id']));
									}
								}
                               
			}
			else if ($res['filtered_auctiontype'] == 'regular')
			{
				$auctiontype = 'regular';
                                $transactionstatus = $ilance->bid->fetch_transaction_status(intval($ilance->GPC['id']));
			}
			if ($res['project_details'] == 'unique')
			{
				$auctiontype = 'unique';
                                $transactionstatus = $ilance->bid_lowest_unique->fetch_transaction_status(intval($ilance->GPC['id']));
			}
			
			// does seller require bidders to use escrow to purchase item?
			$show['seller_using_escrow'] = $show['filter_escrow'] = false;
			$escrowbit = '';
			if ($res['filter_escrow'] == '1' AND $ilconfig['escrowsystem_enabled'])
			{
				$show['filter_escrow'] = $show['seller_using_escrow'] = true;
				$escrowbit = $phrase['_seller_ships_item_after_secure_payment_via_escrow'] . ' <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow.gif" border="0" alt="" />';
			}
			
			// #### has reserve price ##############################
			$show['reserve_auction'] = false;
                        $show['reserve_met'] = true;
			if ($res['reserve'])
			{
				$show['reserve_auction'] = true;
                                
				$highest_amount = '';
				$sql_highest = $ilance->db->query("
					SELECT MAX(bidamount) AS highest
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . $res['project_id'] . "'
					ORDER BY highest
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql_highest) > 0)
				{
					$res_highest = $ilance->db->fetch_array($sql_highest);
                                        if ($res_highest['highest'] >= $res['reserve_price'])
                                        {
                                                $show['reserve_met'] = true;
                                                $reserve_met = $phrase['_yes_reserve_price_met'];
                                        }
                                        else
                                        {
                                                $show['reserve_met'] = false;
                                                $reserve_met = '<span style="color:#ff6600">' . $phrase['_no_reserve_price_not_met'] . '</span>';
                                        }
				}
				else
                                {
                                        $show['reserve_met'] = false;
                                        $reserve_met = '<span style="color:#ff6600">' . $phrase['_no_reserve_price_not_met'] . '</span>';
                                }
                                unset($highest_amount);
			}
			
			// #### is buynowable? #################################
			$show['buynow_available'] = $show['buynow'] = $show['multipleqty'] = false;
                        $buynow_qty = $buynow_price = $buynow_price_plain = 0;
			if ($res['buynow_price'] > 0)
			{
                                $show['buynow'] = true;
                                
				if ($res['buynow_qty'] >= 1)
				{
					$show['buynow_available'] = true;
                                        
                                        $qty_pulldown = '';
					$buynow_price = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
                                        $buynow_price_plain = $res['buynow_price'];
                                        $buynow_qty = intval($res['buynow_qty']);
					$amount = $res['buynow_price'];
					 $maxqty = $res['max_qty'];
                                        if ($maxqty == 1)
                                        {
                                                $qty_pulldown = '<input type="hidden" name="qty" value="1" />';
                                        }
                                        else
                                        {
						$show['multipleqty'] = true;
                                                $qty_pulldown = '<select name="qty" style="font-family: verdana" id="check_id"><optgroup label="' . $phrase['_qty'] . '">';
												//kannan max
                                               
											
                                                for ($i = 1; $i <= $buynow_qty; $i++)
                                                {
                                                        if ($i <= $maxqty)
                                                        {
                                                                $qty_pulldown .= '<option value="' . $i . '">' . $i . '</option>';
                                                        }
                                                }
                                                $qty_pulldown .= '</optgroup></select>';
                                        }
				}
				
				// #### purchase now activity limit for buyer jan30##################
				$result_pro = $ilance->db->query("
					SELECT p.currentprice,p.project_id
					FROM " . DB_PREFIX . "project_bids b,
					 " . DB_PREFIX . "projects p
					WHERE p.project_id = '".$ilance->GPC['id']."'
					AND p.project_id = b.project_id
				");
				if($ilance->db->num_rows($result_pro) > 0)
				{
				$order_ops = $ilance->db->fetch_array($result_pro);
				$mesa = 'This item is going to sell, the current bid is US$'.$order_ops['currentprice'].'';
					
				}
				else
				{
				$mesa = 'This item is not currently selling';
				}	
				
				$result_open = $ilance->db->query("
					SELECT qty
					FROM " . DB_PREFIX . "buynow_orders
					WHERE buyer_id = '".$_SESSION['ilancedata']['user']['userid']."'
						AND project_id = '".$ilance->GPC['id']."'
				");
				if($ilance->db->num_rows($result_open) > 0 and $_SESSION['ilancedata']['user']['userid'] > 0)
				{
				
				while ($orderops = $ilance->db->fetch_array($result_open))
					{
					$total_qty[]=$orderops['qty'];
					}
					
				}
				
				if(isset($total_qty))
				{
					$cou   =   array_sum($total_qty);
					
					$max_q = $res['max_qty'];
					
					//new hidden
					
					$hidden_val = '<input type="hidden"  id="cou_val" value="'.$cou.'"><input type="hidden"  id="max_val" value="'.$max_q.'">';
					
					  if($max_q <= $cou)
					  {
						$disab = 'disabled="disabled"';
						$mess = 'You have already purchased the maximum quantity ('.$max_q.' ) of this item per member at GreatCollections';
					  }
					  else
					  {
						$disab = '';
						$mess = '';
					  }
				}
				
				// #### purchase now activity ##################
				$result_orders = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "buynow_orders
					WHERE owner_id = '" . $res['user_id'] . "'
						AND project_id = '" . $res['project_id'] . "'
				");
				if ($ilance->db->num_rows($result_orders) > 0)
				{
					$no_purchase_now_activity = 0;
					$order_count = 0;
					while ($orderrows = $ilance->db->fetch_array($result_orders))
					{
						//$orderrows['orderbuyer'] = print_username($orderrows['buyer_id'], 'href', 0, '', '');
						$orderrows['orderbuyer'] = fetch_user('username', $orderrows['buyer_id']);
						$orderrows['orderamount'] = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
						$orderrows['orderdate'] = print_date($orderrows['orderdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
						$orderrows['orderqty'] = $orderrows['qty'];
						
						// escrow order status
						if ($orderrows['status'] == 'paid')
						{
							// started - funds forwarded by bidder into escrow for item
						}
						else if ($orderrows['status'] == 'pending_delivery')
						{
							// started - funds forwarded by bidder into escrow for item
						}
						else if ($orderrows['status'] == 'delivered')
						{
							// started - funds forwarded by bidder into escrow for item
						}
						else if ($orderrows['status'] == 'cancelled')
						{
							// started - funds forwarded by bidder into escrow for item
						}
						else if ($orderrows['status'] == 'offline')
						{
							// offline delivery - user to user (nothing to do with site)
						}
						else if ($orderrows['status'] == 'offline_delivered')
						{
							// offline delivery completed
						}
						
						$orderrows['buyershipcost'] = ($orderrows['ship_required']) ? $orderrows['buyershipcost'] : 0;
						$orderrows['total'] = $ilance->currency->format(($orderrows['amount'] * $orderrows['orderqty']) + $orderrows['buyershipcost'], $res['currencyid']);
						$orderrows['class'] = ($order_count % 2) ? 'alt2' : 'alt1';
						$GLOBALS['purchase_now_activity'][] = $orderrows;
						$order_count++;
					}
				}
				else
				{
					$no_purchase_now_activity = 1;
				}
			}
						
			// #### seller information #############################
			$sql_user_results = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $res['user_id'] . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_user_results) > 0)
			{
				$res_project_user  = $ilance->db->fetch_array($sql_user_results);
			}
			else
			{
				print_notice($phrase['_owner_delisted'], $phrase['_sorry_the_owner_of_this_auction_has_been_delisted'], $ilpage['main'], $phrase['_main_menu']);
				exit();
			}
			
			$project_user_id = $res_project_user['user_id'];
			//$seller = print_username($res_project_user['user_id'], 'href', 0, '', '');
            $sellerplain = $res_project_user['username'];;
			
			//$memberinfo = $ilance->feedback->datastore($res['user_id']);
                      //  $merchantscore = $memberinfo['pcnt'];
                        
			//$project_title = strip_vulgar_words(stripslashes($res['project_title']));
			//$icons = $ilance->auction->auction_icons($res['project_id'], $res['user_id']);
			$project_title = stripslashes($res['project_title']);
			// auction type
			if ($res['project_type'] == 'reverse')
			{
				$project_type = $phrase['_reverse_auction'];
			}
			
			if ($res['project_type'] == 'forward')
			{
				$project_type = $phrase['_standard_auction'];
			}
			
			if ($res['project_type'] == 'dutch')
			{
				$project_type = $phrase['_dutch_auction'];
			}
			
			if ($res['project_type'] == 'quote')
			{
				$project_type = $phrase['_rfp_quote_only'];
			}
			
			if ($res['project_type'] == 'trade')
			{
				$project_type = $phrase['_trade_only'];
			}
			
			if ($res['project_type'] == 'resume')
			{
				$project_type = $phrase['_resume_listing'];
			} 
			
			// auction details
                        $uniquebidcount = 0;
			if ($res['project_details'] == 'public')
			{
				$project_details = $phrase['_public_viewing'];
			}            
			if ($res['project_details'] == 'invite_only')
			{
				$project_details = $phrase['_by_invitation_only'];
			}
			if ($res['project_details'] == 'realtime')
			{
				$project_details = $phrase['_realtime'];
			}
			if ($res['project_details'] == 'unique')
			{
				$project_details = $phrase['_lowest_unique_bid_event'];
                                $uniquebidcount = $res['uniquebidcount'];
			}
			
			// auction bid details
			if ($res['bid_details'] == 'sealed')
			{
				$bid_details = $phrase['_sealed_bidding_hidden'];
			}
			
			if ($res['bid_details'] == 'open')
			{
				$bid_details = $phrase['_public_bidding'];
			}
			
			if ($res['bid_details'] == 'blind')
			{
				$bid_details = $phrase['_blind_bidding'];
			}
			
			if ($res['bid_details'] == 'full')
			{
				$bid_details = $phrase['_full_bidding_privacy'];
			}
	    
/*			// #### distance calculation ###########################
			$distance = '-';
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], $accessname='distance') == "yes")
			{
				$ilance->distance = construct_object('api.distance');
				$distance = $ilance->distance->print_distance_results($res_project_user['country'], $res_project_user['zip_code'], $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['postalzip']);
			}
			*/
			// #### UNIQUE BID HISTORY OVERVIEW ####################
			if ($res['project_details'] == 'unique')
			{
				if (empty($_SESSION['ilancedata']['user']['userid']))
				{
					$userid = 0;
				}
				else
				{
					$userid = $_SESSION['ilancedata']['user']['userid'];
				}
				
				// is user sorting by status?
				if (isset($ilance->GPC['sortby']) AND $ilance->GPC['sortby'] == 'status' AND isset($ilance->GPC['sort']))
				{
					$ilance->GPC['sort'] = mb_strtoupper($ilance->GPC['sort']);
				}
				else
				{
					$ilance->GPC['sort'] = 'DESC';
				}
				
				// #### unique bidders query ###################
				$result = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "projects_uniquebids
					WHERE user_id = '" . $userid . "'
					    AND project_id = '" . $res['project_id'] . "'
					ORDER BY status " . $ilance->db->escape_string($ilance->GPC['sort']) . "
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($result) > 0)
				{
					while ($resbids = $ilance->db->fetch_array($result, DB_ASSOC))
					{
						$resbids['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
						
						// date of placed bid
						$resbids['date'] = print_date($resbids['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						if ($resbids['status'] == 'lowestunique' AND $res['status'] != 'open')
						{
							$resbids['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="" />';
						}
						else
						{
							//$resbids['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" />';
							$resbids['award'] = '';
						}
						
						if ($resbids['status'] == 'unique')
						{
							$resbids['amountstatus'] = '<strong><em>'.$phrase['_unique_bid'].'</em></strong>';
							$resbids['response'] = $phrase['_your_bid'] . ': ' . $ilance->currency->format($resbids['uniquebid'], $res['currencyid']) . ' ' . $phrase['_is_unique_but_there_is_currently'] . ' <strong>' . $ilance->bid_lowest_unique->fetch_lower_unique_bids($resbids['user_id'], $resbids['project_id'], $resbids['uniquebid']) . '</strong> ' . $phrase['_lower_unique_bids_placed_than_yours'];
						}
						else if ($resbids['status'] == 'nonunique')
						{
							$resbids['amountstatus'] = $phrase['_non_unique'];
							$resbids['response'] = $phrase['_your_bid'] . ': ' . $ilance->currency->format($resbids['uniquebid'], $res['currencyid']) . ' is <strong>not-unique</strong> because there is currently <strong>' . $resbids['totalbids'] . '</strong> other bids with the same amount placed.';
						}
						else if ($resbids['status'] == 'lowestunique')
						{
							$resbids['amountstatus'] = '<span style="font-size:15px; color:green"><strong>'.$phrase['_lowest_unique'].'</strong></span>';
							$resbids['response'] = $phrase['_your_bid'] . ': ' . $ilance->currency->format($resbids['uniquebid'], $res['currencyid']) . ' ' . $phrase['_is_currently_the_lowest_unique_bid_placed'];
						}
						$resbids['uniquebid'] = $ilance->currency->format($resbids['uniquebid'], $res['currencyid']);
						$unique_bid_results_rows[] = $resbids;
						$row_count++;
					}
				}
				else
				{
					$show['no_unique_bid_rows_returned'] = true;
				}    
			}
                        
                        // #### bidders query ##################################
			else
			{
				$result = $ilance->db->query("
					SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added AS bidadded, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.qty, p.project_id, p.escrow_id, p.cid, p.description, p.date_added, p.buynow_qty, p.date_end, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
					FROM " . DB_PREFIX . "project_bids AS b,
					" . DB_PREFIX . "projects AS p,
					" . DB_PREFIX . "users AS u
					WHERE b.project_id = '".intval($ilance->GPC['id'])."'
					    AND b.project_id = p.project_id
					    AND u.user_id = b.user_id
					    AND b.bidstatus != 'declined'
					    AND b.bidstate != 'retracted'
					ORDER by b.bidamount DESC, b.bid_id DESC
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($result) > 0)
				{
					while ($resbids = $ilance->db->fetch_array($result, DB_ASSOC))
					{
						// date of placed bid
						$resbids['bid_datetime'] = print_date($resbids['bidadded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						
						// bidder information
						$sql_user_results = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . $resbids['project_user_id'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						$res_project_user = $ilance->db->fetch_array($sql_user_results, DB_ASSOC);
						
						if ($resbids['bid_details'] == 'open' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] != $resbids['user_id'])
						{
							// allow currency conversion?
							if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], $accessname='enablecurrencyconversion') == 'yes')
							{
								$rowbeforeexchange = $resbids['bidamount'];
								$resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $rowbeforeexchange, $resbids['currencyid']);
							}
							else
							{
								$rowbeforeexchange = $resbids['bidamount'];
								$resbids['bidamount'] = $ilance->currency->format($rowbeforeexchange, $resbids['currencyid']);
							}
						}
						else if ($resbids['bid_details'] == 'open' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['user_id'])
						{
							if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], $accessname='enablecurrencyconversion') == 'yes')
							{
								$rowbeforeexchange = $resbids['bidamount'];
								$resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $rowbeforeexchange, $resbids['currencyid']);
							}
							else
							{
								$rowbeforeexchange = $resbids['bidamount'];
								$resbids['bidamount'] = $ilance->currency->format($rowbeforeexchange, $resbids['currencyid']);
							}
						}
						else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] != $resbids['user_id'] AND $_SESSION['ilancedata']['user']['userid'] != $res_project_user['user_id'])
						{
							$resbids['bidamount'] = '= ' . $phrase['_sealed'] . ' =';
						}
						else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['user_id'])
						{
							if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], $accessname='enablecurrencyconversion') == 'yes')
							{
								$rowbeforeexchange = $resbids['bidamount'];
								$resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $rowbeforeexchange, $resbids['currencyid']);
							}
							else
							{
								$rowbeforeexchange = $resbids['bidamount'];
								$resbids['bidamount'] = $ilance->currency->format($rowbeforeexchange, $resbids['currencyid']);
							}
						}
						else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $res_project_user['user_id'])
						{
							$resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
						}
                                                else
                                                {
                                                        $resbids['bidamount'] = $ilance->currency->format($resbids['bidamount'], $resbids['currencyid']);
                                                }
						
						//$resbids['isonline'] = print_online_status($resbids['user_id']);
						//$resbids['provider'] = fetch_user('username', $resbids['user_id']);
						$resbids['city'] = ucfirst($resbids['city']);
						$resbids['state'] = ucfirst($resbids['state']);
						$resbids['zip'] = trim(mb_strtoupper($resbids['zip_code']));
						//$resbids['location'] = $resbids['state'].', '.print_user_country($resbids['user_id']);
						$resbids['awarded'] = print_username($resbids['user_id'], 'custom', 0, '', '', fetch_user('serviceawards', $resbids['user_id']) . ' ' . $phrase['_awards']);
						//$resbids['reviews'] = fetch_product_reviews_reported($resbids['user_id']);
						if ($resbids['bidstatus'] == 'awarded')
						{
							$resbids['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="" />';
							$awarded_vendor = stripslashes($resbids['username']);
							$resbids['bidamount'] = '<span style="font-size:15px"><strong>' . $resbids['bidamount'] . '</strong></span>';
						}
						else
						{
							$resbids['award'] = '';
						}
                                                
                                                if ($resbids['qty'] == 0)
                                                {
                                                        $resbids['qty'] = '-';
                                                }
                                                
                                                if (!empty($resbids['proposal']))
                                                {
                                                        // proxy bid
                                                        $resbids['class'] = 'featured_highlight';
                                                        $resbids['provider'] = '<span class="gray">' . $resbids['provider'] . '</span>';
                                                        $resbids['bidamount'] = '<span class="gray">' . $resbids['bidamount'] . '</span>';
                                                        $resbids['qty'] = '<span class="gray">' . $resbids['qty'] . '</span>';
                                                        $resbids['bid_datetime'] = '<span class="gray">' . $resbids['bid_datetime'] . '</span>';
                                                }
                                                else
                                                {
                                                        // user bid
                                                        $resbids['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                                }
                              //suku
							    $users_list[]=$resbids['user_id'];                
                                                
						$bid_results_rows[] = $resbids;
						$row_count++;
					}
				}
				else
				{
					$show['no_bid_rows_returned'] = true;
				}    
			}
			
			//suku
				$bidderid_list=array_reverse(array_unique($users_list));
				
				foreach($bid_results_rows as $bids_row)
				{
				if(in_array($bids_row['user_id'],$bidderid_list))
				$seq=array_search($bids_row['user_id'],$bidderid_list)+1;
				$bids_row['provider']="bidder ".$seq;
				//new change
				$newvar[] = $bids_row['provider'];
				$temp_bid_results_rows[]=$bids_row;
				}
				unset($bid_results_rows);
				$bid_results_rows=$temp_bid_results_rows;
				    
			$filter_permissions = $ilance->bid_permissions->print_filters('product', intval($ilance->GPC['id']));
			
			// #### bidders sql query ##############################
			$result_bidtop = $ilance->db->query("
				SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($ilance->GPC['id']) . "'
				    AND project_state = 'product'
				    AND visible = '1'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result_bidtop) > 0)
			{
				while ($row = $ilance->db->fetch_array($result_bidtop, DB_ASSOC))
				{
					if ($row['project_details'] == 'unique')
					{
						$bids = $ilance->bid_lowest_unique->fetch_bid_count($row['project_id']);
                                                
                                                if ($uniquebidcount > 0 AND $bids > 0)
                                                {
                                                        $left = ($uniquebidcount - $bids);
                                                        
                                                        if ($left <= 0)
                                                        {
                                                                $show['ended'] = true;
                                                                $uniquebidcount = $left;
                                                        }
                                                        else
                                                        {
                                                                $uniquebidcount = $left;
                                                        }
                                                }
					}
					else
					{
						$bids = $row['bids'];
					}
					
					// seller information
					$sql_user_results = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . $row['user_id'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$res_project_user = $ilance->db->fetch_array($sql_user_results);
					
					// fetch highest bidder info
					$sql_highest_bidder = $ilance->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "project_bids
						WHERE project_id = '" . $row['project_id'] . "'
						ORDER BY bidamount DESC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_highest_bidder) > 0)
					{
						$res_highest_bidder = $ilance->db->fetch_array($sql_highest_bidder);						
						// is bid placed?
						if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
						{
							$sql_bidplaced = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "project_bids
								WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								    AND project_id = '" . $row['project_id'] . "'
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql_bidplaced) > 0)
							{
								$row['bidplaced'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="' . $phrase['_you_have_placed_a_bid_on_this_auction'] . '" />';
							}
							else
							{
								$row['bidplaced'] = '';
							}
						}
						else
						{
							$row['bidplaced'] = '';
						}
									
						// format average, lowest and highest amounts
						$sel_bids_av = $ilance->db->query("
							SELECT AVG(bidamount) AS average, MIN(bidamount) AS lowest, MAX(bidamount) AS highest
							FROM " . DB_PREFIX . "project_bids
							WHERE project_id = '" . $row['project_id'] . "'
                                                                AND bidstate != 'retracted'
                                                                AND bidstatus != 'declined'
							ORDER BY highest
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sel_bids_av) > 0)
						{
							$res_bids_av = $ilance->db->fetch_array($sel_bids_av);
							
							// fetch highest bidders username
							$highbidderid = $ilance->bid->fetch_highest_bidder($row['project_id']);
							//$highbidder = print_username($highbidderid, 'href', 0, '', '');
							
							//new change apr4
							$highbidder = $newvar['0'];
							//$highbidder = fetch_user('username', $highbidderid);
						}
						else
						{
							$res_bids_av['average'] = $res_bids_av['lowest'] = $res_bids_av['highest'] = '';
							$highbidder = $highbidderid = $highbidderscore = $merchantstars = '';
						}
					}
					else
					{
						$res_bids_av['average'] = $res_bids_av['lowest'] = $res_bids_av['highest'] = $row['bidplaced'] = '';
						$highbidder = $highbidderid = $highbidderscore = $merchantstars = '';
					}
                                        
					$cid = $row['cid'];
					if ($row['bid_details'] == 'open')
					{
						if (!empty($_SESSION['ilancedata']['user']['currencyid']))
						{
							$average = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['average'], $row['currencyid']);
							$lowest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['lowest'], $row['currencyid']);
							$highest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['highest'], $row['currencyid']);
						}
						else
						{
							$average = print_currency_conversion(0, $res_bids_av['average'], $row['currencyid']);
							$lowest  = print_currency_conversion(0, $res_bids_av['lowest'], $row['currencyid']);
							$highest = print_currency_conversion(0, $res_bids_av['highest'], $row['currencyid']);
						}
					}
					else if ($row['bid_details'] == 'sealed')
					{
						// is auction owner viewing?
						if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $res_project_user['user_id'])
						{
							$average = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['average'], $row['currencyid']);
							$lowest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['lowest'], $row['currencyid']);
							$highest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['highest'], $row['currencyid']);
						}
						else
						{
							// auction owner not viewing
							$average = "= " . $phrase['_sealed'] . " =";
							$lowest = "= " . $phrase['_sealed'] . " =";
							$highest = "= " . $phrase['_sealed'] . " =";
						}
					}
					
                                        // template if condition
                                        $show['ended'] = false;
                                        
					if ($row['date_starts'] > DATETIME24H)
					{
						// auction has not begun yet
						$placeabid = '<input type="button" name="bid" value="' . $phrase['_place_a_bid'] . '" onclick="location.href=\'' . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $row['project_id'] . '&amp;state=product\'" class="buttons" disabled="disabled" /> ';
                                                
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
							$show['buynow_available'] = false;
						}
						else
						{
							if ($ndays != '0')
							{
								$project_time_left = $ndays . $phrase['_d_shortform'] . ', ';	
								$project_time_left .= $nhours . $phrase['_h_shortform'] . ', ';
								$project_time_left .= $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
							}
							else if ($nhours != '0')
							{
								$project_time_left = $nhours . $phrase['_h_shortform'] . ', ';
								$project_time_left .= $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
							}
							else
							{
								$project_time_left = $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';	
							}
						}
                                                
						$row['timetostart'] = $project_time_left;
						$started = $phrase['_starts'] . ': ' . $row['timetostart'];
						$ends = print_date($row['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$timeleft = '--';
						if ($row['status'] == 'open')
						{
							$project_status = $started;
						}
					}
					else
					{
						$placeabid = '<input type="button" name="bid" value="' . $phrase['_place_a_bid'] . '" onclick="location.href=\'' . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $row['project_id'] . '&amp;state=product\'" class="buttons" /> ';
									
						$started = print_date($row['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$ends = print_date($row['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						
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
							$project_time_left = '<span class="black" style="font-size:13px; font-weight:normal">' . $phrase['_ended'] . '</span>';
							$show['buynow_available'] = false;
						}
						else
						{
							if ($ndays != '0')
							{
								$project_time_left = $ndays . $phrase['_d_shortform'] . ', ';	
								$project_time_left .= $nhours . $phrase['_h_shortform'] . ', ';
								$project_time_left .= $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
							}
							else if ($nhours != '0')
							{
								$project_time_left = $nhours . $phrase['_h_shortform'] . ', ';
								$project_time_left .= $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
							}
							else
							{
								$project_time_left = $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
							}
						}
						
						$timeleft = $project_time_left;
						
						if ($row['status'] == 'open')
						{
							$project_status = $phrase['_event_open_for_bids'];
						}
						else
						{
							$project_status = print_auction_status($row['status']);
							$timeleft = '<span class="black" style="font-size:13px; font-weight:normal">' . $phrase['_ended'] . '</span>';
                                                        $show['ended'] = true;
						}
					}
					
					$winningbidder = $winningbid = '';
                                        $winningbidderid = $ilance->bid->fetch_highest_bidder(intval($ilance->GPC['id']));
                                        if ($winningbidderid > 0)
                                        {
                                                //$winningbidder = print_username($winningbidderid, 'href', 0, '', '');
												
						                        //$winningbidder = fetch_user('username', $winningbidderid);
												
												//new change
												if(isset($newvar))
												{
												  $user_val =  $newvar['0'];
												}
												
												if (!empty($_SESSION['ilancedata']['user']['userid']))
												{
												  if($_SESSION['ilancedata']['user']['userid'] == $winningbidderid || $_SESSION['ilancedata']['user']['isadmin'] == '1')												  {
												  $winningbidder = fetch_user('username', $winningbidderid);
												  }
												  else
												  {
												  $winningbidder = $user_val;
												  }
												
												}
												else
												{
												$winningbidder = $user_val;
												}
												
                                                $winningbid = $ilance->bid->fetch_awarded_bid_amount(intval($ilance->GPC['id']));
                                                $winningbid = $ilance->currency->format($winningbid, $row['currencyid']);
                                        }
                                        
                                        if ($row['status'] == 'finished')
					{
						$placeabid = '<input type="button" name="bid" value="' . $phrase['_place_a_bid'] . '" onclick="location.href=\'' . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $row['project_id'] . '&amp;state=product\'" class="buttons" disabled="disabled" /> ';
						$ends = print_auction_status($row['status']) . ': ' . print_date($row['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                                $show['ended'] = true;
					}
					else if ($row['status'] == 'expired')
					{
						$placeabid = '<input type="button" name="bid" value="' . $phrase['_place_a_bid'] . '" onclick="location.href=\'' . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $row['project_id'] . '&amp;state=product\'" class="buttons" disabled="disabled" /> ';
                                                $show['ended'] = true;
					}
                                        else if ($row['status'] != 'open' AND $row['close_date'] != '0000-00-00 00:00:00')
					{
						$placeabid = '<input type="button" name="bid" value="' . $phrase['_place_a_bid'] . '" onclick="location.href=\'' . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $row['project_id'] . '&amp;state=product\'" class="buttons" disabled="disabled" /> ';
						$ends = print_auction_status($row['status']) . ': ' . print_date($row['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$timeleft = $phrase['_ended_early'];
                                                $show['ended'] = true;
					}
                                        
					if ($row['close_date'] != '0000-00-00 00:00:00')
					{
						if ($row['close_date'] < $row['date_end'])
						{
							$ends = print_date($row['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
							$timeleft = '<span class="blue" style="font-size:13px">' . $phrase['_ended_early'] . '</span>';
						}
					}
					
					if ($row['project_details'] == 'unique')
					{
						if (!empty($_SESSION['ilancedata']['user']['userid']))
						{
							if ($row['status'] == 'open')
							{
								$placeabid = '
								<form method="post" action="' . $ilpage['merch'] . '" name="uniquebid" accept-charset="UTF-8" style="margin:0px">
								<input type="hidden" name="cmd" value="preview-unique-bid" />
								<input type="hidden" name="project_id" value="' . intval($ilance->GPC['id']) . '" />
								<input type="hidden" name="owner_id" value="' . $row['user_id'] . '" />
								<div><strong>' . $phrase['_enter_lowest_unique_bid'] . '</strong>: <input type="text" name="uniquebid" value="" size="5" /> <input type="submit" value="' . $phrase['_preview_bid'] . '" class="buttons" /></div>
								<div class="smaller">' . $phrase['_place_your_bid_below_for_example_enter_25_for_25_cents'].'</div>
								</form>';       
							}
							else
							{
								$placeabid = '--';
                                                                $show['ended'] = true;
							}
						}
						else
						{
							$placeabid = '';
						}
					}
					else
					{
						if (empty($_SESSION['ilancedata']['user']['userid']))
						{
							$placeabid = '';
						}
					}
					
					/*// invited buyers listings
					$invite_list = '';
					$externalbidders = $registeredbidders = 0;
					
					$sql_invitations = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "project_invitations
						WHERE project_id = '" . $row['project_id'] . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_invitations) > 0)
					{
						while ($res_invitations = $ilance->db->fetch_array($sql_invitations, DB_ASSOC))
						{
							if ($res_invitations['buyer_user_id'] != '-1')
							{
								$sql_vendor = $ilance->db->query("
									SELECT user_id
									FROM " . DB_PREFIX . "users
									WHERE user_id = '" . $res_invitations['buyer_user_id'] . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql_vendor) > 0)
								{
									$registeredbidders++;
									$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
									if ($res_invitations['bid_placed'] == '0')
									{
										$invite_list .= print_username($res_vendor['user_id'], 'href');
										$invite_list .= ' [ <em>' . $phrase['_not_placed'] . '</em> ], ';
									}
									else if ($res_invitations['bid_placed'] == '1')
									{
										$invite_list .= print_username($res_vendor['user_id'], 'href');
										$invite_list .= ' [ <strong>' . $phrase['_placed'] . '</strong> ], ';
									}        
								}        
							}
							else
							{
								// this invited bidder appears to be an external bidder
								// so we only have their email address to work with...
								$externalbidders++;
							}
						}        
					}
		    
					if ($externalbidders > 0 OR $registeredbidders > 0)
					{
						if ($res['bid_details'] == 'blind' OR $res['bid_details'] == 'full')
						{
							if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
							{
								// admin
								$invite_list = mb_substr($invite_list, 0, -2);        
							}
							else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $row['user_id'])
							{
								// owner
								$invite_list = mb_substr($invite_list, 0, -2);        
							}
							else if (empty($_SESSION['ilancedata']['user']['userid']))
							{
								// guest
								$invite_list = '= '.$phrase['_sealed'].' =';        
							}
							else if (!empty($_SESSION['ilancedata']['user']['userid']))
							{
								// member
								$invite_list = '= '.$phrase['_sealed'].' =';        
							}
						}
						else
						{
							$invite_list = mb_substr($invite_list, 0, -2);         
						}
						
						// formatted invite list display
						$invite_list = $invite_list . '<ul style="margin:18px; padding:0px;"><li>' . $externalbidders . ' ' . $phrase['_bidders_invited_via_email'] . '</li><li>' . $registeredbidders . ' ' . $phrase['_registered_members_invited'] . '</li></ul>';
					}
					else
					{
						$invite_list = $phrase['_no_bidders_invited'];		
					}*/
							    
					// product auction image
					$checkup = print_item_photo('', 'checkup', $row['project_id']);
					
					$product_image = $product_image_thumb = '';
					if ($checkup == '1')
					{
						$show['productimage'] = 1;
                                                $product_image_thumb = print_item_photo('javascript:void(0)', 'thumb', $row['project_id'], $borderwidth = '0', $bordercolor = '');
					}
					
					//$feedback_score = $positive = $negative = '';
					//$memberstart = print_date(fetch_user('date_added', $row['user_id']), '%d-%b-%Y', 0, 0);
                                        
					$city = $row['city'];
					$state = $row['state'];
					$location = $city . ', ' . $state . ', ' . $row['country'];
					$owner_id = $row['user_id'];
				}
			}
                        
                        // template if conditionals: admin viewing
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
			{
                                $show['is_owner'] = false;
                                $show['cannot_bid'] = true;
				if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $owner_id)
				{
					$show['is_owner'] = true;
				}
                                
				if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
				{
					$show['cannot_bid'] = false;
				}
			}
                        // template if conditionals: registered member viewing
			else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
                                $show['is_owner'] = false;
				$show['cannot_bid'] = false;
				if ($_SESSION['ilancedata']['user']['userid'] == $owner_id)
				{
					$show['is_owner'] = true;
					$show['cannot_bid'] = false;
				}
			}
                        // template if conditionals: guest viewing
			else
			{
				$show['is_owner'] = false;
				$show['cannot_bid'] = true;
			}
			
                        $purchases = fetch_buynow_ordercount(intval($ilance->GPC['id']));
                        if ($purchases == 0)
                        {
                                //$purchases = '-';
                        }
                        
                        $show['soldbypurchase'] = $purchases;
                        $show['soldbyauction'] = $ilance->bid->has_winning_bidder(intval($ilance->GPC['id']));
                        
			$auctionridcode = fetch_user('rid', intval($owner_id));
			
			// purchase now logic
			if (isset($show['buynow_available']) AND $show['buynow_available'] AND isset($amount) AND $amount > 0)
			{
				// is there a highest bid placed?
				if ($res_bids_av['highest'] > 0)
				{
					// is the highest bid placed greater than the purchase now price?
                                        // make sure there is 1 or less qty available to purchase also.. we don't want to remove buy now option
                                        // if the seller has 2 or more items being sold via fixed price... 
					if ($res_bids_av['highest'] > $amount AND $res['buynow_qty'] <= 1)
					{
                                                // it is.. so let's remove buy now option!
						$show['buynow_available'] = false;
					}
				}
			}
			else
			{
				$show['buynow_available'] = false;
			}
			
		/*	$categoryname = $ilance->categories->recursive($cid, 'product', $_SESSION['ilancedata']['user']['slng'], 0, '', $ilconfig['globalauctionsettings_seourls']);
			$listingcategory = $categoryname;*/
                        // prevent the top cats in breadcrumb to contain any fields from this form
			$show['nourlbit'] = true;
                        $navcrumb = array();
                       /* if ($ilconfig['globalauctionsettings_seourls'])
                        {
                                $catmap = print_seo_url($ilconfig['productcatmapidentifier']);
                                $navcrumb["$catmap"] = $phrase['_buy'];
                                unset($catmap);
                        }
                        else
                        {
                                $navcrumb["$ilpage[merch]?cmd=listings"] = $phrase['_buy'];
                        }
                        
			$ilance->categories->breadcrumb($cid, 'product', $_SESSION['ilancedata']['user']['slng']);*/
								$cat_details=$ilance->categories_parser->fetch_coin_class(0,0,$cid);
								 
								$series_details=$ilance->categories_parser->fetch_coin_series(0,$cat_details[0]['coin_series_unique_no']);
								$denomination_detail=$ilance->categories_parser->fetch_denominations($series_details['coin_series_denomination_no']);
								 
								$subcatname .= ', <span class="black">' . $series_details['coin_series_name'] . '</span>';
								$childrenids=$ilance->categories_parser->fetch_children_pcgs($series_id);
								$navcrumb=array();
			$navcrumb["$ilpage[merch]?denomination=".$denomination_detail['denomination_unique_no']] = $denomination_detail['denomination_long'];
			$navcrumb["$ilpage[search]?mode=product&series=".$series_details['coin_series_unique_no']] = $series_details['coin_series_name'];
			$navcrumb[""] = $project_title;
			
			// custom category questions
			$ilance->auction_questions = construct_object('api.auction_questions');
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, intval($ilance->GPC['id']), 'output', 'product', $columns = 4);
			
                        // template if conditionals
			$show['is_winner'] = $show['is_high_bidder'] = $show['is_outbid'] = $show['directpay'] = $show['directpaycompleted'] = false;
			$directpayurl = $directpaybit = '';
                        $buynoworderid = 0;
                        
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $ilance->bid->fetch_highest_bidder(intval($ilance->GPC['id'])) == $_SESSION['ilancedata']['user']['userid'])
			{
				$show['is_high_bidder'] = true;
			}
			
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
				$show['is_outbid'] = $ilance->bid->is_outbid($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['id']));
                                
                                // this will also populate $show['wonbyauction'] and/or $show['wonbypurchase'] so we can present proper url link to user
				$show['is_winner'] = $ilance->bid->is_winner($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['id']));
				
                                //$show['directpay'] = true;
				$buynoworderid = $ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "project_id = '" . intval($ilance->GPC['id']) . "' AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' AND amount > 0 AND status = 'offline'", "orderid");
				$winnermarkedaspaid = $ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "project_id = '" . intval($ilance->GPC['id']) . "' AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' AND amount > 0 AND status = 'offline'", "winnermarkedaspaid");
				$winnermarkedaspaiddate = $ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "project_id = '" . intval($ilance->GPC['id']) . "' AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' AND amount > 0 AND status = 'offline'", "winnermarkedaspaiddate");
				
				$directpayurl = ($buynoworderid > 0)
					? HTTPS_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . intval($ilance->GPC['id']) . '&amp;orderid=' . $buynoworderid
					: HTTPS_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . intval($ilance->GPC['id']);
				
				if ($buynoworderid > 0 AND $winnermarkedaspaid == 1)
				{
					$show['directpaycompleted'] = true;
					$directpaybit = '<span class="black">' . $phrase['_marked_as_paid_on'] . ' <span class="blue">' . print_date($winnermarkedaspaiddate) . '</span></span>';
				}
			}
			
		/*	if ($res['project_details'] == 'unique')
			{
                                $show['lowestuniquebidalert'] = false;
                                
				$uniquebidstatus = '<div style="font-family: arial; font-size:15px;"><strong>' . $phrase['_lowest_unique_bid'] . '</strong></div><div class="gray">' . $phrase['_place_your_bid_below_for_example_enter_25_for_25_cents'] . '</div>';
				if (empty($_SESSION['ilancedata']['user']['userid']))
				{
					// bids left today
					$bidtotal = 0;
					$bidsleft = max(0,($bidtotal - fetch_bidcount_today(0)));
					$lastuniquebid = $ilance->bid_lowest_unique->fetch_last_unique_bid_amount(0, intval($ilance->GPC['id']));
				}
				else
				{
                                        $show['lowestuniquebidalert'] = true;
                                        
					$bidtotal = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday');
					$bidsleft = max(0,($bidtotal-fetch_bidcount_today($_SESSION['ilancedata']['user']['userid'])));
					$lastuniquebid = $ilance->bid_lowest_unique->fetch_last_unique_bid_amount($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['id']));
                                        
					if ($lastuniquebid > 0)
					{
                                                $uniquebidstatus = (isset($show['is_owner']) AND $show['is_owner']) ? '' : $ilance->bid_lowest_unique->print_unique_bid_response($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['id']), $lastuniquebid);
						$uniquewinner = ($res['status'] != 'open' AND $ilance->bid_lowest_unique->has_unique_bid_winner($res['project_id'])) ? $ilance->bid_lowest_unique->fetch_lowest_unique_bid_winner($res['project_id']) : '';
					}
				}
			}*/
			
                        $returnpolicy = $returnsaccepted = $returnwithin = $returngivenas = $returnshippingpaidby = $additional_info = '';
                        $returnpolicy = (!empty($res['returnpolicy'])) ? handle_input_keywords($res['returnpolicy']) : '';
                        
                        $show['returnpolicy'] = false;
                        $returnsaccepted = $phrase['_no'];
                        if ($res['returnaccepted'])
                        {
                                $show['returnpolicy'] = true;
                                
                                $returnsaccepted = $phrase['_yes'];
                                $returnwithin = intval($res['returnwithin']);
                                $returngivenas = ucwords($res['returngivenas']);
                                $returnshippingpaidby = ucwords($res['returnshippaidby']);
                        }
                        
                        $min_bidamount = sprintf("%.02f", '0.01');
                        $min_bidamountformatted = $ilance->currency->format('0.01', $res['currencyid']);
                        $highestbid = 0;
                                        
                        if ($res['bids'] <= 0)
                        {
                                // do we have starting price?
                                if ($res['startprice'] > 0)
                                {
                                        $min_bidamount = sprintf("%.02f", $res['startprice']);
                                        $min_bidamountformatted = $ilance->currency->format($res['startprice'], $res['currencyid']);
                                }
                        }
                        else if ($res['bids'] > 0)
                        {
                                // highest bid amount placed for this auction
                                $highestbid = $ilance->bid->fetch_highest_bid($res['project_id']);
                                
                                // if we have more than 1 bid start the bid increments since the first bidder cannot bid against the opening bid
                                if (isset($resincrement['amount']) AND !empty($resincrement['amount']) AND $resincrement['amount'] > 0)
                                {
                                        $min_bidamount = sprintf("%.02f", $highestbid + $resincrement['amount']);
                                        $min_bidamountformatted = $ilance->currency->format(($highestbid + $resincrement['amount']), $res['currencyid']);
                                }
                                else
                                {
                                        $min_bidamount = sprintf("%.02f", $highestbid);
                                        $min_bidamountformatted = $ilance->currency->format($highestbid, $res['currencyid']);
                                }
                        }
                        
                       /* // #### sellers other items ############################
                        $otherlistings = $ilance->auction->fetch_users_other_listings($res['user_id'], 'product', 5, $excludelist = array($res['project_id']), true);
                        
                        // #### last viewed items ##############################
                        $lastviewedlistings = $ilance->auction->fetch_recently_viewed_auctions('product', 5, 1, 0, '', true);
                        */
                        $show['categoryuseproxybid'] = false;
			if ($ilconfig['productbid_enableproxybid'] AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']))
                        {
                                $show['categoryuseproxybid'] = true;
                        }
			
			$show['startprice'] = $show['currentbid'] = 0;
                        
                        // starting bid price
			$startprice = $ilance->currency->format($res['startprice'], $res['currencyid']);
			
			if ($ilance->bid->has_bids($res['project_id']) > 0)
			{
				$show['currentbid'] = 1;
				
				// current bid amount display
				$currentbid = '<strong>' . $ilance->bid->fetch_current_bid($res['project_id']) . '</strong>';
				$proxybit = '';
				
			    //ends 
				if (!empty($_SESSION['ilancedata']['user']['userid']))
				{
					$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($res['project_id'], $_SESSION['ilancedata']['user']['userid']);
					if ($pbit > 0)
					{
                                                // murugan test
												$highbidderidtest = $ilance->bid->fetch_highest_bidder($res['project_id']);
												if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
												$proxybit = '<span class="green">'.$phrase['_your_maximum_bid'] . ':' . $ilance->currency->format($pbit, $res['currencyid']) . '</span>';
												else
												$proxybit = '<span class="red">'.$phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $res['currencyid']) . '</span>';
						$proxybit2 = $ilance->currency->format($pbit, $res['currencyid']);
						
						if ($pbit > $min_bidamount)
						{
							$min_bidamount = sprintf("%.02f", $pbit) + 0.01;
							$min_bidamountformatted = $ilance->currency->format($min_bidamount, $res['currencyid']);
						}
					}
				}
			}
			else 
			{
				$show['startprice'] = true;
			}
                        
                        $show['donation'] = false;
                        $donationtransaction = '<span class="gray">' . $phrase['_the_donation_associated_with_this_nonprofit_has_not_been_marked'] . '</span>';
                        if ($res['donation'] AND $res['charityid'] > 0)
                        {
                                $show['donation'] = true;
                                $charity = fetch_charity_details($res['charityid']);
                                $donationto = '<a href="' . $ilpage['nonprofits'] . '?id=' . $res['charityid'] . '">' . $charity['title'] . '</a>';
                                $donationurl = $charity['url'];
                                $donationpercentage = intval($res['donationpercentage']);
                                
                                if ($res['donermarkedaspaid'] AND $res['donermarkedaspaiddate'] != '0000-00-00 00:00:00')
                                {
                                        $donationtransaction = '' . $phrase['_the_donation_assoicated_with_this_nonprofit_was_marked_as_paid_on'] . ' <span class="blue"><strong>' . print_date($res['donermarkedaspaiddate']) . '</strong></span>';        
                                }
                        }
                        
                        // page url
                        $pageurl = urlencode($ilpage['merch'] . '?id=' . $res['project_id']);
                        
                        // trackbacks
                        $trackbacks = '<span class="gray">' . $phrase['_no_referring_sites'] . '</span>';
                        
                        // video description
                        $videodescription = print_listing_video($res['project_id'], $videowidth = '490', $videoheight = '364');
			
			$ship_handlingtime = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping", "project_id = '" . $res['project_id'] . "'", "ship_handlingtime");
                        
			$headinclude .= '<script language="javascript" type="text/javascript" src="' . $ilconfig['template_relativeimagepath'] . DIR_FUNCT_NAME . '/javascript/functions_slideshow.js"></script>' . "\n";
                        
                        // update category view count
                        add_category_viewcount($cid);
			
			$show['localpickuponly'] = ($res['ship_method'] == 'localpickup') ? true : false;
                        
                       	// Murugan Changes on Jan 06
                       //$onload .= (isset($show['ended']) AND $show['ended']) ? '' : 'window.setInterval(\'refresh_item_details(\\\'' . $auctiontype . '\\\')\', \'1000\');';
					   // murugan for to stop the ajax to load a page
			if ($ilconfig['globalfilters_ajaxrefresh'])
			{
				$onload .= (isset($show['ended']) AND $show['ended']) ? '' : 'window.setInterval(\'refresh_item_details(\\\'' . $auctiontype . '\\\')\', \'' . $ilconfig['globalfilters_countdowndelayms'] . '\');';
			}
			else
			{
				$onload .= (isset($show['ended']) AND $show['ended']) ? '' : 'refresh_item_details(\'' . $auctiontype . '\'); ';
				//refresh_item_countdown(gsecs, \'product\');
			}
			if(PROTOCOL_REQUEST == 'https')
			{
		    $aj = HTTPS_SERVER;
			}
			else
			{
			 $aj = HTTP_SERVER;
			}
			
			
			//new change kkk
			if (!empty($_SESSION['ilancedata']['user']['userid']) && $show['ended'] != '1')
			{
			             $my_var_open ='fetch_js_object(\'proxy_id_my\').innerHTML = myString[19];
						 
						 if (myString[20] != \'\')
								{ 
								   if(' . $show['is_outbid'] . ' == 0)
								   {
								   fetch_js_object(\'topcongratsrow\').innerHTML = myString[20];
								   }
								}
								if (myString[21] != \'\')
                                {
								   if(' . $show['is_outbid'] . ' == 1)
								   {
								   fetch_js_object(\'outbidnotice\').innerHTML = myString[21];
								   }
								   else
								   {
								   fetch_js_object(\'topcongratsrow\').innerHTML = myString[21];
								   }
								
								}';
				}	
			else
			{
			$my_var_open ='';
			}			
				
                        $jsend = '
<script language="javascript" type="text/javascript">
<!--
refresh_item_details(\'' . $auctiontype . '\');
//-->
</script>';
                        $headinclude .= '
<script language="javascript" type="text/javascript">
<!--
function timed_refresh(period)
{
	setTimeout("document.location.reload(true);", period);
}
if (!window.XMLHttpRequest)
{
        var reqObj = 
        [
                function() {return new ActiveXObject("Msxml2.XMLHTTP");},
                function() {return new ActiveXObject("Microsoft.XMLHTTP");},
                function() {return window.createRequest();}
        ];
        for(a = 0, z = reqObj.length; a < z; a++)
        {
                try
                {
                        window.XMLHttpRequest = reqObj[a];
                        break;
                }
                catch(e)
                {
                        window.XMLHttpRequest = null;
                }
        }
}
 
var req = new XMLHttpRequest();
window.onload = function()
{
        if (req == null)
        {
                return;
        }
        refresh_item_details(\'' . $auctiontype . '\');
        window.setInterval("refresh_item_details(\'' . $auctiontype . '\')", \'5000\');
}
 
function refresh_item_details(type)
{
        req.abort();
        req.open(\'GET\', \'' . $aj . $ilpage['ajax'] . '?do=refreshitemdetails&id=' . $res['project_id'] . '&type=' . $auctiontype . '\');
        req.onreadystatechange = function()
        {
                if (req.readyState != 4)
                {
                        return;
                }
                
                if (req.status == 200)
                {
                        var myString;
                        myString = req.responseText;
                        myString = myString.split("|");
                        
						
                        if (type == \'regular\')
                        {
                                fetch_js_object(\'timelefttext\').innerHTML = myString[0];
                                fetch_js_object(\'timelefttext_modal\').innerHTML = myString[0];
				fetch_js_object(\'timelefttext_tab\').innerHTML = myString[0];
				
				var newg = "("+myString[11]+" Pacific Time)";
				
				fetch_js_object(\'endstext\').innerHTML = newg;
                                fetch_js_object(\'bidstext\').innerHTML = myString[1];
                                fetch_js_object(\'bidstext_modal\').innerHTML = myString[1];
				fetch_js_object(\'bidstext_tab\').innerHTML = myString[1];
				fetch_js_object(\'refreshbidders\').innerHTML = myString[13];
				
				
				'.$my_var_open.'
                                if (myString[2] != \'\')
                                {
                                        fetch_js_object(\'startbidtext\').innerHTML = myString[2];
                                        fetch_js_object(\'startbidtext_modal\').innerHTML = myString[2];
                                        
                                        toggle_show(\'startpricerow\');
                                        toggle_show(\'startpricerow_modal\');
                                        toggle_show(\'placebidrow\');
                                        
                                        toggle_hide(\'currentpricerow\');
                                        toggle_hide(\'currentpricerow_modal\');
                                        toggle_hide(\'highbidderrow\');
                                        
                                        if (myString[9] == \'1\')
                                        {
                                                toggle_hide(\'buynowactionrow\');
                                                toggle_hide(\'buynowrow\');
                                                
                                                fetch_js_object(\'blockheadertext\').innerHTML = \'' . $phrase['_place_a_bid_on_this_item_now'] . '\';
                                        }
                                        
                                        if (myString[9] == \'0\')
                                        {
                                                toggle_show(\'buynowactionrow\');
                                                toggle_show(\'buynowrow\');
                                                toggle_show(\'purchasesrow\');
                                                
                                                fetch_js_object(\'blockheadertext\').innerHTML = \'' . $phrase['_take_action_on_this_item'] . '\';
                                        }
                                }
                                else if (myString[3] != \'\' && myString[1] > 0)
                                {
                                        fetch_js_object(\'currentbidtext\').innerHTML = myString[3];
                                        fetch_js_object(\'currentbidtext_modal\').innerHTML = myString[3];
                                        
                                        toggle_hide(\'startpricerow\');
                                        toggle_hide(\'startpricerow_modal\');
                                        
                                        toggle_show(\'placebidrow\');
                                        toggle_show(\'currentpricerow\');
                                        toggle_show(\'currentpricerow_modal\');
                                        
                                        if (myString[8] != \'\')
                                        {
                                                toggle_show(\'highbidderrow\');
                                                fetch_js_object(\'highbiddertext\').innerHTML = myString[8];
                                        }
                                        
                                        if (myString[9] == \'1\')
                                        {
                                                toggle_hide(\'buynowactionrow\');
                                                fetch_js_object(\'blockheadertext\').innerHTML = \'' . $phrase['_place_a_bid_on_this_item_now'] . '\';
                                        }
                                        
                                        if (myString[9] == \'0\')
                                        {
                                                toggle_show(\'buynowrow\');
                                                toggle_show(\'buynowactionrow\');
                                                toggle_show(\'purchasesrow\');
                                                
                                                fetch_js_object(\'blockheadertext\').innerHTML = \'' . $phrase['_take_action_on_this_item'] . '\';
                                        }
                                }
                                
                                if (myString[4] != \'\')
                                {
                                        fetch_js_object(\'reservemettext\').innerHTML = myString[4];
                                        fetch_js_object(\'reservemettext_modal\').innerHTML = myString[4];
                                        
                                        if (myString[8] != \'\')
                                        {
                                                toggle_show(\'highbidderrow\');
                                                toggle_hide(\'winningbidderrow\');
                                                
                                                fetch_js_object(\'highbiddertext\').innerHTML = myString[8];
                                        }
                                        
                                        if (myString[9] == \'1\')
                                        {
                                                toggle_hide(\'buynowactionrow\');
                                                fetch_js_object(\'blockheadertext\').innerHTML = \'' . $phrase['_place_a_bid_on_this_item_now'] . '\';
                                        }
                                        
                                        if (myString[9] == \'0\')
                                        {
                                                toggle_show(\'buynowactionrow\');
                                                toggle_hide(\'winningbidderrow\');
                                                fetch_js_object(\'blockheadertext\').innerHTML = \'' . $phrase['_take_action_on_this_item'] . '\';
                                        }
                                }
                                
                                if (myString[5] != \'\' && myString[6] != \'\')
                                {
                                        fetch_js_object(\'minimumbidtext\').innerHTML = myString[5];
                                        fetch_js_object(\'minimumbidtext_modal\').innerHTML = myString[5];
                                        fetch_js_object(\'hiddenfieldminimum\').value = myString[6];
                                }
                                
                                if (myString[7] != \'\')
                                {
                                        if (myString[7] > 0)
                                        {
                                                toggle_show(\'purchasesrow\');        
                                        }
                                        fetch_js_object(\'purchasestext\').innerHTML = myString[7];
                                }
                                
                                if (myString[14] == \'1\')
                                {
                                        fetch_js_object(\'blockheadertext\').innerHTML = \'' . $phrase['_this_listing_has_ended'] . '\';
                                        fetch_js_object(\'winningbidamounttext\').innerHTML = myString[17];
                                
                                        toggle_show(\'winningbidderrow\');
                                        toggle_show(\'winningbidrow\');
                                        
                                        toggle_hide(\'currentpricerow\');
                                        toggle_hide(\'startpricerow\');
                                        toggle_hide(\'placebidrow\');
                                        toggle_hide(\'highbidderrow\');
                                        toggle_hide(\'winninguniquebidderrow\');
                                        toggle_hide(\'buynowactionrow\');
                                }
                                
                                if (myString[14] == \'0\')
                                {
                                        toggle_hide(\'winningbidderrow\');
                                        toggle_hide(\'winningbidrow\');
                                }
                                
                                if (myString[16] == \'1\')
                                {
                                        fetch_js_object(\'blockheadertext\').innerHTML = \'' . $phrase['_this_listing_has_ended'] . '\';                                        
                                }
				
                                if (myString[12] == \'1\')
                                {
                                        toggle_hide(\'currentpricerow\');
                                        toggle_hide(\'startpricerow\');
                                        toggle_hide(\'placebidrow\');
                                }
                                
                                if (myString[10] == \'1\')
                                {
                                        toggle_hide(\'placebidrow\');
                                        toggle_hide(\'buynowactionrow\');
                                }
				
				if (myString[18] == \'1\')
				{
					toggle_show(\'shippinginforow\');
				}
				';
				$headinclude .= ((isset($show['ended']) AND $show['ended'])
					? ''
					: '
				if (myString[14] == \'1\' || myString[16] == \'1\')
				{
					timed_refresh(2000);
				}');
				
                        $headinclude .= '
			}
                        else if (type == \'fixed\')
                        {
                                fetch_js_object(\'timelefttext\').innerHTML = myString[0];
                                fetch_js_object(\'timelefttext_modal\').innerHTML = myString[0];
				fetch_js_object(\'timelefttext_tab\').innerHTML = myString[0];
				var newh = "("+myString[5]+" Pacific Time)";
				fetch_js_object(\'endstext\').innerHTML = newh;
                                fetch_js_object(\'purchasestext\').innerHTML = myString[1];
                                
                                toggle_show(\'endsrow\');
                                toggle_show(\'buynowrow\');
                                toggle_show(\'buynowactionrow\');
                                toggle_show(\'purchasesrow\');
                                
                                toggle_hide(\'placeuniquebidrow\');
                                toggle_hide(\'winninguniquebidderrow\');
                                toggle_hide(\'placebidrow\');
                                toggle_hide(\'highbidderrow\');
                                toggle_hide(\'winningbidderrow\');
                                toggle_hide(\'currentpricerow\');
                                toggle_hide(\'startpricerow\');
                                toggle_hide(\'retailpricerow\');
                                toggle_hide(\'bidsrow\');
                                
                                if (myString[2] == \'1\')
                                {
                                        toggle_hide(\'buynowactionrow\');
                                }
                                
                                if (myString[3] == \'1\')
                                {
                                        fetch_js_object(\'blockheadertext\').innerHTML = \'' . $phrase['_this_listing_has_ended'] . '\';
                                }
				
				if (myString[4] == \'1\')
				{
					toggle_show(\'shippinginforow\');
				}
				';
				$headinclude .= ((isset($show['ended']) AND $show['ended'])
					? ''
					: '
				if (myString[3] == \'1\')
				{
					timed_refresh(2000);
				}');
				
                        $headinclude .= '
                        }
                        else if (type == \'unique\')
                        {
                                fetch_js_object(\'timelefttext\').innerHTML = myString[0];
                                fetch_js_object(\'timelefttext_modal\').innerHTML = myString[0];
				fetch_js_object(\'timelefttext_tab\').innerHTML = myString[0];
				var newj = "("+myString[10]+" Pacific Time)";
				fetch_js_object(\'endstext\').innerHTML = newj;
                                fetch_js_object(\'bidstext\').innerHTML = myString[1];
                                fetch_js_object(\'bidstext_modal\').innerHTML = myString[1];
				fetch_js_object(\'bidstext_tab\').innerHTML = myString[1];
				fetch_js_object(\'refreshuniquebidders\').innerHTML = myString[8];
                                
                                if (myString[3] > 0)
                                {
                                        fetch_js_object(\'uniquebidcounttext\').innerHTML = myString[3];
                                        toggle_show(\'uniquebiduntilendtext\');
                                }
                                else
                                {
                                        toggle_hide(\'uniquebiduntilendtext\');
                                }
                                
                                toggle_show(\'retailpricerow\');
                                toggle_show(\'bidsrow\');
                                toggle_show(\'endsrow\');
                                
                                if (myString[4] == \'1\')
                                {
                                        fetch_js_object(\'blockheadertext\').innerHTML = \'' . $phrase['_this_listing_has_ended'] . '\';
                                        toggle_hide(\'placeuniquebidrow\');
                                        toggle_hide(\'uniquebiduntilendtext\');
                                        
                                        if (myString[7] != \'\')
                                        {
                                                fetch_js_object(\'winningbidamounttext\').innerHTML = myString[7];
                                                toggle_show(\'winningbidrow\');
                                                toggle_show(\'winninguniquebidderrow\');
                                        }
                                        
                                        if (myString[6] != \'\')
                                        {
                                                fetch_js_object(\'lowestuniquebiddertext\').innerHTML = myString[6];
                                                toggle_show(\'winninguniquebidderrow\');
                                        }
                                }
                                else
                                {
                                        toggle_show(\'placeuniquebidrow\');
                                        toggle_hide(\'winninguniquebidderrow\');
                                }
                                
                                toggle_hide(\'placebidrow\');
                                toggle_hide(\'buynowrow\');
                                toggle_hide(\'purchasesrow\');
                                toggle_hide(\'highbidderrow\');
                                toggle_hide(\'winningbidderrow\');
                                toggle_hide(\'currentpricerow\');
                                toggle_hide(\'startpricerow\');
				
				if (myString[9] == \'1\')
				{
					toggle_show(\'shippinginforow\');
				}
				';
				$headinclude .= ((isset($show['ended']) AND $show['ended'])
					? ''
					: '
				if (myString[4] == \'1\')
				{
					timed_refresh(2000);
				}');
				
                        $headinclude .= '
                        }
                }
        }
        
        req.send(null);	
}
function validate_place_bid(f)
{
        var Chars = "0123456789.,";
        haveerrors = 0;
	
        (f.bidamount.value.length < 1) ? showImage("bidamounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("bidamounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        
        for (var i = 0; i < f.bidamount.value.length; i++)
        {
                if (Chars.indexOf(f.bidamount.value.charAt(i)) == -1)
                {
                        alert(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
                        haveerrors = 1;
                }
        }
        
        if (haveerrors != 1)
        {
                val = fetch_js_object(\'bidamount_modal\').value;
                var bidamount = parseFloat(val);
		bidamount = parseFloat(bidamount);
                
                val2 = fetch_js_object(\'hiddenfieldminimum\').value;
                var minimumbid = string_to_number(val2);
		minimumbid = parseFloat(minimumbid);
                
                val3 = fetch_js_object(\'hiddenfieldreserve\').value;
                var reserve = val3;
		reserve = parseFloat(reserve);
                
                val4 = fetch_js_object(\'hiddenfieldreservemet\').value;
                var reservemet = val4;
                
                val5 = fetch_js_object(\'hiddenfieldbuynow\').value;
                var buynow = val5;
		buynow = parseFloat(buynow);
                
                val6 = fetch_js_object(\'hiddenfieldbuynowprice\').value;
                var buynowprice = string_to_number(val6);
		buynowprice = parseFloat(buynowprice);
              
                if (bidamount == \'NaN\' || bidamount == \'\' || bidamount <= \'0\')
                {
                        alert(\'' . $phrase['_you_have_entered_an_incorrect_bid_amount_please_try_again'] . '\');
                        haveerrors = 1;
                }
                else
                {
                        if (bidamount < minimumbid)
                        {
                                alert(phrase[\'_cannot_place_value_for_your_bid_amount_your_bid_amount_must_be_greater_than_the_minimum_bid_amount\']);
                                haveerrors = 1;
                        }
                }
                
                fetch_js_object(\'bidamount_modal\').value = bidamount;
        }
        
        return (!haveerrors);
}
function update_bid_amount_modal()
{
        price = fetch_js_object(\'bidamount_temp\').value;
        fetch_js_object(\'bidamount_modal\').value = price;
}
function update_bid_amount()
{
        setTimeout("update_bid_amount_modal()", 100);
}
function show_listing_shipping_rows()
{
	';
	$headinclude .= (isset($show['localpickuponly']) AND $show['localpickuponly'])
		? 'return false;
		'
		: '
	cookieexpire = new Date();
	cookieexpire.setTime(cookieexpire.getTime() + (500 * 86400 * 3));	
	var countryid = fetch_js_object(\'showshippingdestinations\').options[fetch_js_object(\'showshippingdestinations\').selectedIndex].value;
	if (countryid == \'\')
	{
		return false;
	}
	';
	
	$headinclude .= '
	// hide services rows temporarily so we can redraw later
	for (var i = 1; i <= ' . $ilconfig['maxshipservices'] . '; i++)
	{
		var o = fetch_js_object(\'ship_options_\' + i);
		if (o)
		{
			toggle_hide(\'ship_options_\' + i);
		}
		var z = fetch_js_object(\'shippinginfobit_\' + i);
		if (z)

		{
			fetch_js_object(\'shippinginfobit_\' + i).innerHTML = \'\';
			update_js_cookie(ILNAME + \'shipping_\' + i + \'_' . $res['project_id'] . '\', \'\', cookieexpire);
		}
	}
	
	var countrytitle = fetch_js_object(\'showshippingdestinations\').options[fetch_js_object(\'showshippingdestinations\').selectedIndex].text;
	var qty = fetch_js_object(\'ship_qty\').value;
	var radiuszip = fetch_js_object(\'shipradiuszip\').value;
	var ajaxRequest;
	
	fetch_js_object(\'showshippingdestinations\').disabled = true;
	fetch_js_object(\'ship_getratesbutton\').disabled = true;
	fetch_js_object(\'ship_qty\').disabled = true;
	try
	{
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}	
		catch (e)
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4)
		{
			var myString, myString2;
			
			toggle_show(\'shippinginforow\');
                        myString = ajaxRequest.responseText;
                        myString = myString.split("|");
			for (var i = 1; i <= myString[0]; i++)
			{
				myString2 = myString[i].split("~~~~");
				var b = fetch_js_object(\'ship_amount_\' + i)
				if (b)
				{
					if (myString2[2] != \'\')
					{
						toggle_show(\'ship_options_\' + i);
						fetch_js_object(\'ship_amount_\' + i).innerHTML = \'<span class="blue">\' + myString2[0] + \'</span>\';
						fetch_js_object(\'ship_countries_\' + i).innerHTML = \'<span class="gray">\' + myString2[1] + \'</span>\';
						fetch_js_object(\'ship_service_\' + i).innerHTML = \'<span class="black">\' + myString2[2] + \'</span>\';
						fetch_js_object(\'ship_estimate_\' + i).innerHTML = myString2[3];
						fetch_js_object(\'shippinginfobit_\' + i).innerHTML = \'<span class="black"><strong>\' + myString2[0] + \'</strong></span> ' . $phrase['_via'] . ' <span class="black">\' + myString2[2] + \'</span> ' . $phrase['_to'] . ' <span class="black">\' + countrytitle + \'</span>\';
						update_js_cookie(ILNAME + \'shipping_\' + i + \'_' . $res['project_id'] . '\', \'<span class="black"><strong>\' + myString2[0] + \'</strong></span> ' . $phrase['_via'] . ' <span class="black">\' + myString2[2] + \'</span> ' . $phrase['_to'] . ' <span class="black">\' + countrytitle + \'</span>\', cookieexpire);
					}
				}
			}			
			fetch_js_object(\'showshippingdestinations\').disabled = false;
			fetch_js_object(\'ship_getratesbutton\').disabled = false;
			fetch_js_object(\'ship_qty\').disabled = false;
		}
	}	
	var querystring = "&countryid=" + countryid + "&pid=' . $res['project_id'] . '&qty=" + qty + "&radiuszip=" + radiuszip + "&s=" + ILSESSION + "&token=" + ILTOKEN;
	ajaxRequest.open(\'GET\', ILBASE + \'' . $ilpage['ajax'] . '?do=showshipservicerows\' + querystring, true);
	ajaxRequest.send(null);
}
//-->
</script>';
			// #### item watchlist logic ###########################
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
				$ilance->watchlist = construct_object('api.watchlist');
				$show['addedtowatchlist'] = $ilance->watchlist->is_listing_added_to_watchlist($res['project_id']);
				$show['selleraddedtowatchlist'] = $ilance->watchlist->is_seller_added_to_watchlist($res['user_id']);
			}
			
			// #### seller tools ###################################
			$ilance->auction_post = construct_object('api.auction_post');
			
			// #### seller tools: enhancements #####################
			$show['disableselectedenhancements'] = true;
			if ($res['featured'])
			{
				$ilance->GPC['enhancements']['featured'] = 1;
			}
			if ($res['highlite'])
			{
				$ilance->GPC['enhancements']['highlite'] = 1;
			}
			if ($res['bold'])
			{
				$ilance->GPC['enhancements']['bold'] = 1;
			}
			if ($res['autorelist'])
			{
				$ilance->GPC['enhancements']['autorelist'] = 1;
			}
			
			$enhancements = $ilance->auction_post->print_listing_enhancements('product');
			$featured = $res['featured'];
			$featured_date = $res['featured_date'];
			$highlite = $res['highlite'];
			$bold = $res['bold'];
			$autorelist = $res['autorelist'];
			
			$area_title = $phrase['_viewing_detailed_item'] . ' ' . stripslashes($res['project_title']) . ' (' . $phrase['_item'] . ' ' . intval($ilance->GPC['id']) . ')';
                        $page_title = stripslashes($res['project_title']) . ': ' . SITE_NAME . ' (' . $phrase['_item'] . ' ' . intval($ilance->GPC['id']) . ' ' . $phrase['_ends'] . ' ' . $ends . ')';
                        $metakeywords = stripslashes($res['project_title']) . ', ' . $res['keywords'] . ', ' . $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']);
                        $metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']);
			
			/*// #### shipping logic controls ########################
			// if we're a guest and we don't have the region modal cookie let's ask for it
			$cookieregion = (!empty($_COOKIE[COOKIE_PREFIX . 'region'])) ? $_COOKIE[COOKIE_PREFIX . 'region'] : '';
			$full_country_pulldown = construct_country_pulldown(0, $cookieregion, 'region', true, '', false, true, true);
				
			if (empty($_COOKIE[COOKIE_PREFIX . 'regionmodal']))
			{
				//$onload .= 'jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow();';
			
				// don't ask this guest for region info via popup modal for 3 days
				set_cookie('regionmodal', DATETIME24H, true, true, false, 3);
			}
			
			$shippinginfobit_1 = $shippinginfobit_2 = $shippinginfobit_3 = $shippinginfobit_4 = $shippinginfobit_5 = '';
			if (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_1_' . $ilance->GPC['id']]))
			{
				$shippinginfobit_1 = $_COOKIE[COOKIE_PREFIX . 'shipping_1_' . $ilance->GPC['id']];
			}
			if (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_2_' . $ilance->GPC['id']]))
			{
				$shippinginfobit_2 = $_COOKIE[COOKIE_PREFIX . 'shipping_2_' . $ilance->GPC['id']];
			}
			if (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_3_' . $ilance->GPC['id']]))
			{
				$shippinginfobit_3 = $_COOKIE[COOKIE_PREFIX . 'shipping_3_' . $ilance->GPC['id']];
			}
			if (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_4_' . $ilance->GPC['id']]))
			{
				$shippinginfobit_4 = $_COOKIE[COOKIE_PREFIX . 'shipping_4_' . $ilance->GPC['id']];
			}
			if (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_5_' . $ilance->GPC['id']]))
			{
				$shippinginfobit_5 = $_COOKIE[COOKIE_PREFIX . 'shipping_5_' . $ilance->GPC['id']];
			}*/
			
			$show['localpickuponly'] = false;
			
			$countryid = 0;
			if (!empty($_SESSION['ilancedata']['user']['countryid']))
			{
				$countryid = !empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : '';
			}
			else
			{
				if (!empty($_COOKIE[COOKIE_PREFIX . 'region']) AND strrchr($_COOKIE[COOKIE_PREFIX . 'region'], '.'))
				{
					$c = explode('.', $_COOKIE[COOKIE_PREFIX . 'region']);
					$countryid = $c[1];
					unset($c);
				}
			}
			
			/*$shiptocountries = print_item_shipping_countries_string($res['project_id']);
			$changelocationpulldown = print_item_shipping_countries_pulldown($res['project_id'], false, false, $show['shipsworldwide'], $countryid);
			$shipservicepulldown = print_shipping_methods($res['project_id'], 1, false, false, true);
			$shippercount = print_shipping_methods($project_id, 1, false, true);
			*/$onload .= (isset($countryid) AND $res['ship_method'] != 'localpickup' AND $countryid > 0 AND can_item_ship_to_countryid($res['project_id'], $countryid))
				? ' show_listing_shipping_rows();'
				: '';
				
			unset($countryid);
			
			if ($res['ship_method'] == 'localpickup')
			{
				$show['localpickuponly'] = true;
				$shippinginfobit_1 = $phrase['_no_shipping_local_pickup'];
				$shiptocountries = $phrase['_no_shipping_local_pickup'];
				$changelocationpulldown = '';
			}
			
			if ($shippercount == 1)
			{
				print_shipping_methods($project_id, 1, false, false);
				$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . intval($project_id) . "'", "ship_service_$shipperidrow");
			}
			
			//total page custom for image views
			$slideq = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "projects p,
					         " . DB_PREFIX . "attachment a
                        WHERE p.project_id = '" .$ilance->GPC['id'] . "'
						        AND p.project_id = a.project_id
                                AND a.visible = '1'
								ORDER BY 
								cast(SUBSTR(a.filename from LOCATE('-',a.filename)+1 for LOCATE('.',a.filename)-LOCATE('-',a.filename)-1) as UNSIGNED)");
				
				$i = 1;
				
				$k = 0;
				$l = 0;
				
				
				if ($ilance->db->num_rows($slideq) > 0)
				{
				$newheadthumb = '';	
				
				$newthumb = '<table   cellpadding="20" >';	
			    while ($rowt = $ilance->db->fetch_array($slideq)) {
				
			    $profile_slidq = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?id=' . $rowt['filehash'];
			
			    $profile_slidqft = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $rowt['filehash'] .'&w=268';
				 $profile_sl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?id=' . $rowt['filehash'] .'';
			 
			  /*	
			  	
			    $profile_slidq = $ilpage['attachment'] . '?id=' . $rowt['filehash'];
			
			    $profile_slidqft = $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $rowt['filehash'] .'&w=268';
				 $profile_sl = $ilpage['attachment'] . '?id=' . $rowt['filehash'] .'';
			   */
			   /* $max_width = 100; 
				$max_height = 80; 
				list($width, $height) = getimagesize($profile_slidqft); 
				$ratioh = $max_height/$height; 
				$ratiow = $max_width/$width; 
				$ratio = min($ratioh, $ratiow); 
				// New dimensions 
				$width = intval($ratio*$width); 
				$height = intval($ratio*$height); */
			
				$titlemq = $rowt['filename'];
				$title[] = $titlemq;
				$profile_slides[]= $profile_slidqft;
				$kk[] =$profile_sl;
	
					
			    if($profile_slides[$k] == $profile_slides['0'] )
			    {
							 //echo $profile_slides['0'];	
						$newhead = '<a id="thumb1" href="'.$kk[$k].'" class="highslide" onclick="return hs.expand(this)" ><img class="rounded" alt="" src="'.$profile_slides['0'].'" /></a><div class="highslide-caption" align="center">
For a larger image, click on lower right hand corner.
</div>'	 ;
		        // $newhead ='<img class="rounded" alt="" src="'.$profile_slides['0'].'" />';
			    }
				                  /* if ($l == 0)
	                                {
	                                        $newheadthumb['separator_begin'] = '<tr>';
	                                        $td = 0;
	                                }
	                                else 
	                                {
	                                        $newheadthumb['separator_begin'] = '';
	                                }
                                        
	                                if ($l%6==0)
	                                { 
	                                        $newheadthumb['separator_end'] = '</tr>';
	                                }
	                                else 
	                                {
	                                        $newheadthumb['separator_end'] = '';
	                                }
									*/
									/*  if ($l == 1)
	                                {
	                                        $newheadthumb['separator_begin'] = '<tr>';
	                                        $td = 0;
	                                }
	                                else 
	                                {
	                                        $newheadthumb['separator_begin'] = '';
	                                }
                                        
	                                if ($l == $rowstotal)
	                                {
	                                        $newheadthumb['separator_end'] = '</tr>';
	                                }
	                                else 
	                                {
	                                        $newheadthumb['separator_end'] = '';
	                                }
									 if ($rows != 1)
                                        {
	                                        if ($td == $columns - 1 OR $td == $rowstotal - 1)
	                                        {
	                                        	$newheadthumb['separator_end'] = '</tr>';
	                                        }
	                                        else if($td == $columns)
	                                        {
	                                        	$newheadthumb['separator_end'] = '';
	                                        	$newheadthumb['separator_begin'] = '<tr>';
	                                        	$td = 0;
	                                        }
	                                        else 
	                                        {
	                                        	$newheadthumb['separator_end'] = '';
	                                        }
	                                        
	                                        $td++;

	                                        
	                                }*/
								 if($l%6==0)
								 {
								 $newthumb.='</tr><tr>';
								 }
									
				/* $newheadthumb['ert'] =	'<a id="thumb1" href="'.$kk[$k].'" class="highslide" onclick="return hs.expand(this)">
	<img  class="rounded" src="' . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $rowt['filehash'] .'&w=170&h=140"/></a><div class="highslide-caption" align="center">
 For a larger image, click on lower right hand corner.
</div>';		*/
                                 
								 $newthumb.= '<td><a id="thumb1" href="'.$kk[$k].'" class="highslide" onclick="return hs.expand(this)">
	<img  class="rounded" src="' . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $rowt['filehash'] .'&w=170&h=140"/></a><div class="highslide-caption" align="center">
 For a larger image, click on lower right hand corner.
</div></td>';
		       
		        // $newheadthumb['ert'] = '<div  id="thum_b"><a href="'.$kk[$k].'" rev="'.$profile_slides[$k].'" class="MagicThumb"  rel="swap-image: mouseover;"><img  class="rounded" src="' . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $rowt['filehash'] .'&w=170&h=140"/></a></div>';
				
				
			
				
				$myslide[]= $newheadthumb;
		        $i++;
				$k++;
				$l++;
				
				
				 
			    }
				$newthumb.= '</table>';
		        }
				else
				{
				 $uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						
					    $newhead ='<center><img src="'.HTTP_SERVER.'images/gc/nophoto.gif" style="padding: 10px;" ></center>';
				}
				//item info
				$itinfo = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "coins c,
					         " . DB_PREFIX . "grading_service g
						
                        WHERE  c.project_id = '" .$ilance->GPC['id'] . "'
						
						      ");
							 
				$row_info = $ilance->db->fetch_array($itinfo);
				//karthik start on may 12 for displaying Pedigee
				if($row_info['Pedigee']!='')
				{
				 $show['pedigee']=true;
				 }
				 else
				 {
				 $show['pedigee']=false;
				 } 
				 //karthik end on may12
				$itt = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "coin_proof
					       
                        WHERE  value = '" .$row_info['Grade'] . "'");
				if($ilance->db->num_rows($itt) > 0)
				{
				$rowt = $ilance->db->fetch_array($itt);
				
				//new change
				$pro_tes =  fetch_cat('coin_detail_proof',fetch_auction('cid',$ilance->GPC['id']));
								if($pro_tes == 'y')
								$text_pro = 'Proof-';
								else if($pro_tes == 's')
								$text_pro = 'Specimen-';
								else
								$text_pro = $rowt['proof'].'-';
								
								
				 $testop = $text_pro;
				}
				else
				{
				$testop = '';
				}
				
				 $pro_tes1= fetch_cat('coin_detail_suffix',fetch_auction('cid',$ilance->GPC['id']));
				$show['nodescription'] = false;
				if(!empty($row_info['Description']))
				{
				  $show['nodescription'] = true;
				}
				$row_info['grv'] = $testop;
				$info_val[] = $row_info;
				//print_r($info_val);
				//coin info
				$itinfo_cid = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "catalog_coin
					       
                        WHERE  PCGS = '" .$cid . "'
						
						      ");
							 
				$row_cid = $ilance->db->fetch_array($itinfo_cid);
				$info_cid[] = $row_cid;
				//prev price history
			//$show['tes'] = 'no';
			  /*  $itinfo_pre = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "project_bids u,
					         " . DB_PREFIX . "buynow_orders b,
						     " . DB_PREFIX . "projects p
                        WHERE  p.cid = '" .$cid . "'
						
						AND p.project_state = 'product'
										        AND (p.status = 'expired' OR p.status = 'finished' OR p.status = 'open')
												AND (p.haswinner = '1' OR p.hasbuynowwinner = '1')
                                                AND p.visible = '1'
												AND u.bidstatus = 'awarded'
												group by p.project_id
						      ");
				while($row_pre = $ilance->db->fetch_array($itinfo_pre))
				{
				    if($row_pre['winner_user_id'] != '0')
					{
					$str[] = $row_pre['currentprice'];
					}
					else
					{
					$str[] = $row_pre['buynow_price']; 
					}
				}
				//max value 
				if(isset($str))
				{
				$trval[] = max($str);
				$trva    = max($str);
				}
				if(isset($trval))
				{
				$myop = $trval['0'];
				}
				else
				{
				$myop = '';
				}
				//print_r($trval);
				
				if($ilance->db->num_rows($itinfo_pre) > 0)
				//max value related project 
				{$itinfo_val_pro = $ilance->db->query("
                         SELECT *
                         FROM " . DB_PREFIX . "project_bids u,
					          " . DB_PREFIX . "buynow_orders b,
						      " . DB_PREFIX . "projects p
                        WHERE  p.cid = '" .$cid . "'
						AND (p.buynow_price='".$myop."' or p.currentprice ='".$myop."')
						AND p.project_state = 'product'
						AND (p.status = 'expired' OR p.status = 'finished' OR p.status = 'open')
						AND (p.haswinner = '1' OR p.hasbuynowwinner = '1')
						AND p.visible = '1'
						AND u.bidstatus = 'awarded'
						group by p.project_id
						    ");
				if($ilance->db->num_rows($slideq) > 0)
				{			 
					$row_cid_pro = $ilance->db->fetch_array($itinfo_val_pro);
					
					if($row_cid_pro['winner_user_id'] != '0')
					{
					
					$order_k =  explode('-',$row_cid_pro['date_awarded']);
					$dt = explode(' ',$order_k['2']);
					$order_e = $order_k['1'].'-'.$dt['0'].'-'.$order_k['0'];
					$row_cid_pro['SoldDate'] = $order_e;
					}
					else
					{
					
					$order =  explode('-',$row_cid_pro['orderdate']);
					$dt    = explode(' ',$order['2']);
					$order_list = $order['1'].'-'.$dt['0'].'-'.$order['0'];
					$row_cid_pro['SoldDate'] = $order_list;
					}
					$info_cid_pro[] = $row_cid_pro;
				}
				else
				{
				$show['tes'] = 'no';
				}
				}
				else
				{
				$show['tes'] = 'no';
				}*/
				$show['relv'] = 'rels';
				/*//related item
				$itinfo_rel = $ilance->db->query("
                        SELECT project_title,project_id,cid,currentprice
                        FROM 
						     " . DB_PREFIX . "projects
                        WHERE cid = '" .$cid . "'
						AND project_id != '".$project_id."'
						AND project_state = 'product'
						AND status = 'open'
						AND (haswinner = '0' OR hasbuynowwinner = '0')
                        AND visible = '1'
						
						group by project_id limit 7
						      ");
				if($ilance->db->num_rows($itinfo_rel) > 0)
				{			  
					while($row_rel = $ilance->db->fetch_array($itinfo_rel))
					{
					
					$row_rel['tit_rel'] = '<a href="merch.php?id='.$row_rel['project_id'].'">'.$row_rel['project_title'].'</a>';
					$row_rel['time_left'] = $ilance->auction->auction_timeleft($row_rel['project_id']);
					$info_rel[] = $row_rel;
					}
				}
				else
				{
				 $show['relv'] = 'rels';
				}	*/
				//alt no 
				$itinfo_no = $ilance->db->query("
                        SELECT project_title,project_id,cid,alt_no
                        FROM 
						     " . DB_PREFIX . "projects
                        WHERE project_id = '".$project_id."'");
				if($ilance->db->num_rows($itinfo_no) > 0)
				{
				$row_e = $ilance->db->fetch_array($itinfo_no);
				$alt_no = $row_e['alt_no'];
				}
			    $currency = $ilance->currency->currencies[$res['currencyid']]['symbol_left'];
			
				//suku
			$sql = $ilance->db->query("
                        SELECT count(*)
                        FROM " . DB_PREFIX . "watchlist
                        WHERE watching_project_id = '" . intval($project_id) . "' 
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
				$no_tracking_users=0;
                if ($ilance->db->num_rows($sql) > 0)
                {                        
                       $rrrr=$ilance->db->fetch_array($sql);
					   $no_tracking_users=$rrrr[0];
                }
				
				 
			$sql = $ilance->db->query("
                        SELECT comment
                        FROM " . DB_PREFIX . "watchlist
                        WHERE watching_project_id = '" . intval($project_id) . "' 
                                AND user_id = '".$_SESSION['ilancedata']['user']['userid']."'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
				$watch_list_note=''; 
                if ($ilance->db->num_rows($sql) > 0)
                {                        
                       $rrrr=$ilance->db->fetch_array($sql);
 				$show['personalnote']=true;
					   $watch_list_note=$rrrr[0];
                }else
				{
				$show['']=false;
				}
					$show['statetax']=false;
					$ilance->tax = construct_object('api.tax');
				if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'commission') or $ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'buynow') or $ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'escrow'))
				{
					$show['statetax']=true;
				}
				
				//$onload.="myvald();";
				
				$pageurlpath = "http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];
				$facepath = 'http://www.facebook.com/share.php?u='. $pageurlpath.'?rid=FACEBO';
				
				$twittitle = $project_title;
				$patterns = array();
				$patterns[0] = '/ /';
				$patterns[1] = '/-/';
				
				$replacements = array();
				$replacements[1] = '+';
				$replacements[0] = '+';
				$twittitle = preg_replace($patterns, $replacements, $twittitle);
				$twitpath = 'http://twitter.com/home?status='. $twittitle.'+'.$pageurlpath. '?rid=TWITTE';
				
				require_once(DIR_CORE . 'functions_suggestions.php');
				//population price suggestions
				$pps_data=fetch_price_population_html($project_id);
				$population_guide=$pps_data['pop_html'];
				$price_guide_info=$pps_data['price_html'];
				$pop_guide=strlen($pps_data['pop_html']);
				$price_guide=strlen($pps_data['price_html']);
				$price_condition=$pps_data['value'];
				if ($price_condition=='true' AND $pop_guide > 200 AND $price_guide > 200)
				{
					$show['condition']=true;
				}
				else
				{
				    $show['condition']=false;
				}
                
				//new change
				$statusnew = $res['status'];//fetch_auction('status',$project_id);
				if($statusnew == 'closed' || $statusnew == 'expired' || $statusnew == 'finished')
				{
				
				    $new_var = ($res['filtered_auctiontype'] == 'fixed') ? $res['buynow_price'] : $res['currentprice'];
				  
					$soldprice = $new_var;//fetch_auction('currentprice',$project_id);
				}
				
				/*// murugan changes on apr1
				$statusnew = fetch_auction('status',$project_id);
				if($statusnew == 'closed' || $statusnew == 'expired' || $statusnew == 'finished')
				{
					$soldprice = fetch_auction('currentprice',$project_id);
				}*/
				
				//new change apr27
				
				if($statusnew == 'open')
				{  
				
					$sql_idly = $ilance->db->query("
					SELECT live_date,project_id FROM
					" . DB_PREFIX . "dailydeal
					WHERE project_id = '".$project_id."'
					");
					
					$fetch_idly=$ilance->db->fetch_array($sql_idly); 
					
						if($ilance->db->num_rows($sql_idly) > 0)
						{
						  $date_exp = explode('-',$fetch_idly['live_date']);
						  
						  $mydate = date("F j, Y", mktime(0, 0, 0, $date_exp['1'], $date_exp['2'], $date_exp['0']));
						  
						  $dail = 'This item will be featured as a 24-Hour Deal on '.$mydate.'';
						  
						  //new chnage may10
				          $daily = ($fetch_idly['live_date'] == DATETODAY) ? '' : $dail;
						
						}
						else
						{					
						  $daily = '';
						}
				}
			   /* $pps_data1=fetch_price_information();
				$population_guide=$pps_data1['pop_html'];
				$price_guide_info=$pps_data1['price_html']; */
				 
                        $pprint_array = array('newthumb','daily','soldprice','hidden_val','pageurlpath','twitpath','facepath','mesa','mess','disab','population_guide','price_guide_info','price_condition','watch_list_note','no_tracking_users','alt_no','totalvalue','dismsg','vel','trva','newhead','newheadthumb','currency','proxybit2','shipperid','shipservicepulldown','shippinginfobit_1','shippinginfobit_2','shippinginfobit_3','shippinginfobit_4','shippinginfobit_5','full_country_pulldown','changelocationpulldown','shiptocountries','ship_handlingtime','featured','featured_date','highlite','bold','autorelist','enhancements','directpaybit','directpayurl','buynoworderid','donationtransaction','donationto','donationurl','donationpercentage','transactionstatus','winningbid','winningbidder','videodescription','pageurl','jsend','purchases','trackbacks','min_bidamountformatted','min_bidamount','date_starts','returnsaccepted','returnwithin','returngivenas','returnshippingpaidby','additional_info','returnpolicy','uniquebidcount','updateid','lastrevision','ship_partner','msgcount','product_image_thumb','increment','views','feed1','uniquewinner','uniquebidstatus','bidsleft','retailprice','buynow_qty','paymentmethods','maincid','proxybit','escrowbit','icons','merchantstars','merchantscore','highbidderscore','currentbid','startprice','listingcategory','slideshowpulldown','slideimage1','slideimage1caption','project_attachment','project_questions','auctionridcode','countdownapplet','bidapplet','questionhiddeninput','questionsubmit','questions','questionpulldown','collapseobj_livebid_auctiontab','collapseimg_livebid_auctiontab','collapseimg_merch_shipping','collapseobj_merch_shipping','collapseobj_askquestion','collapseimg_askquestion','product_image','merchantstars','feed1','feed6','feed12','realtime','qty_pulldown','placeabid','cid','reserve_met','buynow','buynow_price','buynow_price_plain','amount','reserve','featured','highest','timeleft','started','ends','average','ship_insurance','bids','highbidder','highbidderid','location','memberstart','countryname','collapserfpinfo_id','invite_list','rfpposted','rfpawards','additional_info','project_user_id','lowest_bidder','highest_bidder','filter_permissions','awarded_vendor','project_status','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','seller','sellerplain','projects_posted','projects_awarded','project_currency','project_attachment','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','category','subcategory','pro_tes1');
		
                        			
			
			($apihook = $ilance->api('merch_detailed_end')) ? eval($apihook) : false;
			
                        $ilance->template->fetch('main', 'listing_forward_auction.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('myslide','info_rel','info_cid_pro','info_cid','info_val','bid_results_rows','unique_bid_results_rows','messages','purchase_now_activity','otherlistings','lastviewedlistings'));
			
			($apihook = $ilance->api('merch_detailed_loop')) ? eval($apihook) : false;
			
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			 
			exit();
		}
		else
		{
			$area_title = $phrase['_bad_rfp_warning_menu'];
			$page_title = SITE_NAME . ' - ' . $phrase['_bad_rfp_warning_menu'];
                        
			print_notice($phrase['_invalid_rfp_specified'], $phrase['_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal'], $ilpage['search'], $phrase['_search_rfps']);
			exit();
		}
	}
}
 
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>