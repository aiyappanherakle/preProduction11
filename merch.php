<?php

$phrase['groups'] = array(
        'rfp',
        'search',
        'accounting',
        'buying',
        'selling',
        'subscription',
        'feedback'
);
$jsinclude = array(
	'functions','jquery','modal','ajax'
);
$topnavlink = array(
	'main_listings'
);
define('LOCATION', 'merch');
require_once('./functions/config.php');
//error_reporting(E_ALL);
require_once(DIR_CORE . 'functions_shipping.php');
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_search_prefs.php');
$ilance->encrypt = construct_object( 'api.encrypt' );
$navcrumb = array("$ilpage[merch]" => $ilcrumbs["$ilpage[merch]"]);
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
//seller tools
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
		
		   $coin_details = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "coins
			WHERE coin_id = '" . $ilance->GPC['pid'] . "' ");
						
			$coin_det = $ilance->db->fetch_array($coin_details, DB_ASSOC);
			
			



			
			
			$consinor_details = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $coin_det['user_id']. "' ");

			$consign_det = $ilance->db->fetch_array($consinor_details, DB_ASSOC);
			
			
			$title1 = $coin_det['Title'];
			$userid = $coin_det['user_id'];
			$consign_username = $consign_det['username'];
			$email1 = $consign_det['email'];
            $current_date = date("F j, Y");
			$pid =$ilance->GPC['pid'];
			
			
			
		

		$bold =  intval($ilance->GPC['bold']);
		$highlite = intval($ilance->GPC['highlite']);
		$featured = intval($ilance->GPC['featured']);

		
		
			
		if($bold == '1')
		{
		$bold = '2.00';
		}
		else 
		{
		$bold = '0.00';
		}
		
		if($highlite == '1')
		{
		$highlite = '3.00';
		}
		else 
		{
		$highlite = '0.00';
		}
		
		if($featured == '1')
		{
		$featured = '10.00';
		}
		else 
		{
		$featured = '0.00';
		}


		
		 $total = ($bold+$highlite+$featured);
		
		
				$ilance->email = construct_dm_object('email', $ilance);
				$ilance->email->mail = SITE_EMAIL;				
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get('enhancement_amount ');		
				$ilance->email->set(array(			
				   
					'{{username}}' => $consign_username ,
					'{{title1}}' => $title1 ,
					'{{email1}}' => $email1 ,
					'{{pid}}' => $pid,
					'{{date}}' => $current_date ,
					'{{bold}}' => $bold ,
					'{{highlite}}' => $highlite , 
					'{{featured}}' => $featured , 
					'{{total}}' => $total , 
														
				));
				$ilance->email->send();
				
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
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'purchase-confirm' AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0 AND isset($ilance->GPC['qty']) AND $ilance->GPC['qty'] > 0)
{
        if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
        {
                refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['merch'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
                exit();
        }
        
        // #### make sure we are not the seller of this auction! ###############
        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == '1' AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
        {
		$area_title = $phrase['_access_denied'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
	
                                       
        print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>');
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
// #### BUY NOW CONFIMRATION PAGE AND ITEM ORDER DISPLAY #######################
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
							  // murugan added dec 23
							  refresh('buyer_invoice.php');
                                // print_notice($phrase['_congratulations_you_used_buy_now_at_greatcollections'], $phrase['_the_buy_now_item_should_be_added_to_an_invoice'], HTTPS_SERVER . 'buyer_invoice.php', $phrase['_pending_invoice_check_out_and_pay']);
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

// #### ITEM LISTINGS #################################################
else
{


//everything else
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
		$ilance->bid_proxy = construct_object('api.bid_proxy');
		$ilance->bid_permissions = construct_object('api.bid_permissions');
		$ilance->subscription = construct_object('api.subscription');
		$ilance->auction = construct_object('api.auction');
		$project_id = intval($ilance->GPC['id']);
		$surfing_user_id=isset($_SESSION['ilancedata']['user']['userid'])?$_SESSION['ilancedata']['user']['userid']:0;
		


		$sql = $ilance->db->query("
			SELECT p.*,b.buyer_id as buynow_purchase,bid.bidamount as cbid,bid.user_id as highest_bidder,bid.bidstatus, count(w.watching_project_id) as watchlist_count,wc.comment,wc.watchlistid as watch_check_watchlistid,dd.live_date,dd.offer_amt,u.username, 
			UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime , 
			UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(p.date_starts) AS start
			FROM " . DB_PREFIX . "projects p
			left join " . DB_PREFIX . "dailydeal dd on p.project_id = dd.project_id
			left join " . DB_PREFIX . "users u on u.user_id=p.user_id and u.status='active'
			left join " . DB_PREFIX . "watchlist_log w on p.project_id = w.watching_project_id
			left join " . DB_PREFIX . "buynow_orders b on p.project_id=b.project_id and b.buyer_id='".$surfing_user_id."'
			left join " . DB_PREFIX . "watchlist wc on p.project_id = wc.watching_project_id and wc.user_id='".$surfing_user_id."'
			left join (select user_id, 	bidstatus,bidamount,project_id from " . DB_PREFIX . "project_bids where project_id = '".intval($ilance->GPC['id'])."' ORDER BY bidamount DESC, date_added ASC LIMIT 1) bid on bid.project_id=p.project_id
			WHERE p.project_id = '" . intval($ilance->GPC['id']) . "'
			    AND p.project_state = 'product'
			    " . (($ilconfig['globalauctionsettings_payperpost'])
				 ? "AND p.visible >= '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))"
				 : "AND p.visible >= '1'") . "
		", 0, null, __FILE__, __LINE__);
		
		if ($ilance->db->num_rows($sql) > 0)
		{
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);

						
						if($res['username']=='')
						{
						print_notice($phrase['_owner_delisted'], $phrase['_sorry_the_owner_of_this_auction_has_been_delisted'], $ilpage['main'], $phrase['_main_menu']);
						exit();
						}
						$show['buynow_check'] = 0;
						if($res['buynow_purchase']==$surfing_user_id)
						{
						$show['buynow_check'] = '1';
						}
						$auction_start = $res['start'];

						$show['personalnote_edit']=$show['personalnote']=false;
						
						$no_tracking_users=$res['users_tracked']+$res['watchlist_count'];
						$watch_list_note=$res['comment'];
						if($res['watch_check_watchlistid']>0)
						{
						$show['personalnote']=true;
						}
						if(strlen($watch_list_note)>0)
						$show['personalnote_edit']=true;
						
						$show['statetax']=false;
						if(isset($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid']>0)
						{
						
						$ilance->tax = construct_object('api.tax');
						if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'buynow') or $ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'escrow'))
						{
							$show['statetax']=true;
						}
						}
						
						// #### vijay work for hot list
						$show['hotlist'] = false;
						
						 if (isset($res['hotlists']) AND $res['hotlists'] > 0)
						{
							 $show['hotlist'] = true;
						}
                        // #### prevent duplicate content from search engines
                        if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef']) OR $ilance->GPC['sef']>1))
                        {
                                 $seourl = construct_seo_url('productauctionplain', $res['cid'], $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0, $removevar = '');
                                $view = isset($ilance->GPC['view']) ? '?view=' . $ilance->GPC['view'] : '';
								header('HTTP/1.1 301 Moved Permanently');
                                header('Location: ' . $seourl . $view);
                                unset($seourl);
                                exit();
                        }

                        ($apihook = $ilance->api('merch_detailed_start')) ? eval($apihook) : false;

                        $views = $res['views'];

                        $show['increments'] = false;
                        $increment = '';
						$cbid=($res['cbid']>0)?$res['cbid']:0;
						$incrementgroup = 'default';
                        $sqlincrements = $ilance->db->query("
                                SELECT amount
                                FROM " . DB_PREFIX . "increments
                                WHERE ((increment_from <= $cbid AND increment_to >= $cbid)
                                                OR (increment_from < $cbid AND increment_to < $cbid))
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

			//page view kkk
			if($res['visible'] != '0')
			{

			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET views = views + 1
				WHERE project_id = '" . intval($ilance->GPC['id']) . "'
				    AND status != 'draft'
			", 0, null, __FILE__, __LINE__);
			}

			$row_count = 0;

			$show['slideshow'] = false;

			$date_starts = print_date($res['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);

			// additional auction information
			$additional_info = stripslashes($res['additional_info']);

			$show['livebid'] = false;
			$show['bidderuninvited'] = false;

			// does admin require members to be logged in before viewing full description?
			$ilance->bbcode = construct_object('api.bbcode');
			$description = strip_vulgar_words($res['description']);
			$description = isset($description) ? $description : $phrase['_no_description'];
			$description = $ilance->bbcode->bbcode_to_html($description);
			$description = print_string_wrap($description, 50);
			

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
										//$transactionstatus = $phrase['_the_transaction_associated_with_this_listing_has_not_been_paid'];
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
				if(isset($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid']>0)
				{
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
										//$transactionstatus = $phrase['_the_transaction_associated_with_this_listing_has_not_been_paid'];
									}
									else if($res_inv['status'] == 'paid')
									{
										$transactionstatus = '<span title="' . $phrase['_the_transaction_associated_with_this_listing_was_marked_as_paid_on'] . ' ' . print_date($res_inv['paiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="' . $phrase['_the_transaction_associated_with_this_listing_was_marked_as_paid_on'] . ' ' . print_date($res_inv['paiddate']) . '" /></span>';
									}
									else
									{
									  	 $transactionstatus = $ilance->bid->fetch_transaction_status(intval($ilance->GPC['id']));
									}
								}

				}
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

				$no_purchase_now_activity = 1;
				
			}

			$project_title = stripslashes($res['project_title']);
			if ($res['project_type'] == 'forward')
			{
				$project_type = $phrase['_standard_auction'];
			}
			if ($res['project_details'] == 'public')
			{
				$project_details = $phrase['_public_viewing'];
			}
			if ($res['bid_details'] == 'open')
			{
				$bid_details = $phrase['_public_bidding'];
			}
			if ($res['filtered_auctiontype']=='regular')
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
				$bids =0;
				if ($ilance->db->num_rows($result) > 0)
				{
				$bids = $ilance->db->num_rows($result);
				$has_wwinner = fetch_auction('haswinner', intval($ilance->GPC['id']));
					while ($resbids = $ilance->db->fetch_array($result, DB_ASSOC))
					{
						// date of placed bid
						$resbids['bid_datetime'] = print_date($resbids['bidadded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$bid_amount = $ilance->currency->format($resbids['bidamount'], $resbids['currencyid']);
						$bidamount = $ilance->encrypt->Encrypt_Amount($bid_amount);
						if($ilconfig['settings_sold_coins_price_to_image'])
		                {
			               	$resbids['bidamount'] = '<img src="images.php?b='.$bidamount.'" />';
			            }
			            else
			            {
			            	$resbids['bidamount'] = $bid_amount;
			            }
						//$resbids['bidamount'] = '<img src="images.php?b='.$bidamount.'" />';
						$resbids['city'] = ucfirst($resbids['city']);
						$resbids['state'] = ucfirst($resbids['state']);
						$resbids['zip'] = trim(mb_strtoupper($resbids['zip_code']));
						if ($resbids['bidstatus'] == 'awarded')
						{
							$resbids['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="" />';
							//$resbids['bidamount'] = '<span style="font-size:15px"><strong>' . $resbids['bidamount'] . '</strong></span>';
							if($ilconfig['settings_sold_coins_price_to_image'])
			                {
				               	$resbids['bidamount'] = '<img src="images.php?b='.$bidamount.'&t=" />';
				            }
				            else
				            {
				            	$resbids['bidamount'] = '<span style="font-size: 14px;"><strong>'.$bid_amount.'</strong></span>';
				            }

						}
						else
						{
							$resbids['award'] = '';
							if($has_wwinner == 0)
								$resbids['bidamount'] = $bid_amount;

						}
						if ($resbids['qty'] == 0)
						{
								$resbids['qty'] = '-';
						}
						if (!empty($resbids['proposal']))
						{
							// proxy bid
							$resbids['class'] = 'featured_highlight';
							//$resbids['provider'] =  $resbids['provider'] ;
							$resbids['bidamount'] =  $resbids['bidamount'] ;
							$resbids['qty'] =  $resbids['qty'] ;
							$resbids['bid_datetime'] =  $resbids['bid_datetime'] ;


							if ($resbids['bidstatus'] == 'awarded')
							{
								if($ilconfig['settings_sold_coins_price_to_image'])
				                {
					               	$resbids['bidamount'] = '<img src="images.php?b='.$bidamount.'&t=&c=" />';
					            }
					            else
					            {
					            	$resbids['bidamount'] = '<span style="font-size: 14px;"><strong>'.$bid_amount.'</strong></span>';
					            }
							}
							else
							{
								
								if($has_wwinner == 1)
								{
									if($ilconfig['settings_sold_coins_price_to_image'])
					                {
						               	$resbids['bidamount'] = '<img src="images.php?b='.$bidamount.'&c=" />';
						            }
						            else
						            {
						            	$resbids['bidamount'] = $bid_amount;
						            }
								}
								else
									$resbids['bidamount'] = $bid_amount;
							}
								
						}
						else
						{
							// user bid
							$resbids['class'] =  'alt1';
						}
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
			if(isset($users_list) and is_array($users_list) and count($users_list)>0)
			{
				$bidderid_list=array_reverse(array_unique($users_list));

				foreach($bid_results_rows as $bids_row)
				{
					if(in_array($bids_row['user_id'],$bidderid_list))
						$seq=array_search($bids_row['user_id'],$bidderid_list)+1;
					$alert_tooltip = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:none"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'alert.png"  border="0");" style="cursor:pointer" onmouseover="this.style.cursor=\'pointer\'" />
					<span>
				        <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
				        <strong>Only you can view your username and/or e-mail address.<br> Other users will only see "Bidder 1", "Bidder 2" etc.</strong> 
				     </span>
					  </a>';						
					//new change
					if(isset($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid']>0 and $bids_row['user_id']==$_SESSION['ilancedata']['user']['userid'])
						$bids_row['provider']=$_SESSION['ilancedata']['user']['username']." ".$alert_tooltip;
					else
						$bids_row['provider']="bidder ".$seq;
					
					$newvar[] = $bids_row['provider'];
					$temp_bid_results_rows[]=$bids_row;
				}
				unset($bid_results_rows);
				
				$bid_results_rows=$temp_bid_results_rows;
			}
			
			$res_bids_av['average'] = $res_bids_av['lowest'] = $res_bids_av['highest'] = $res['bidplaced'] = '';
			$highbidder = $highbidderid = $highbidderscore = $merchantstars = '';
			$highbidder = $newvar['0'];
			
			if($res['bidstatus']=='awarded' AND $_SESSION['ilancedata']['user']['userid'] == $res['highest_bidder'])
			  {
				$winningbidder =$_SESSION['ilancedata']['user']['username'];
			  }
			  else
			  {
				$winningbidder = $newvar['0'];
			  }
			if($res['bidstatus']=='awarded')
			{
				$winningbid = $res['cbid'];
				//$winningbid = $ilance->currency->format($winningbid, $res['currencyid']).' ('.$ilance->currency->format($winningbid+$res['buyer_fee'], $res['currencyid']).' with Buyer\'s Fee)';

				$wbid = $ilance->currency->format($winningbid, $row['currencyid']);
                $byrbid = $ilance->currency->format($winningbid+$res['buyer_fee'], $res['currencyid']);
                $encramnt = $ilance->encrypt->Encrypt_Amount($wbid.' ('.$byrbid.' with Buyer\'s Fee)');
                if($ilconfig['settings_sold_coins_price_to_image'])
                {
	               	$winningbid = '<img width="360" height="23" src="images.php?w='.$encramnt.'" />';
	            }
	            else
	            {
	            	$winningbid = $ilance->currency->format($winningbid, $res['currencyid']).' ('.$ilance->currency->format($winningbid+$res['buyer_fee'], $res['currencyid']).' with Buyer\'s Fee)';
	            }
				//$winningbid = '<img src="images.php?w='.$encramnt.'" />';

				#for bug #5482
				if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
				{
					$maxamount_by_user = $ilance->bid_proxy->fetch_user_proxy_bid($res['project_id'], $_SESSION['ilancedata']['user']['userid']);
					if ($maxamount_by_user > 0)
					{
						$show['user_max_bid'] = true;
						if($maxamount_by_user >= $res['cbid'] AND $_SESSION['ilancedata']['user']['userid'] == $res['highest_bidder'])
						{
							$user_maxbid_amount = "<span class=\"green\">".$ilance->currency->format($maxamount_by_user, $res['currencyid'])."</span>";
						}
						else
						{
							$user_maxbid_amount = "<span class=\"red\">".$ilance->currency->format($maxamount_by_user, $res['currencyid'])."</span>";
						}

					}
				}
			}  
			
						$cid = $res['cid'];
						$show['ended'] = false;
						$placeabid = '<input type="button" name="bid" value="' . $phrase['_place_a_bid'] . '" onclick="location.href=\'' . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $res['project_id'] . '&amp;state=product\'" class="buttons" /> ';
						$ends = print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$dif = $res['mytime'];
						$ndays = floor($dif / 86400);
						$dif -= $ndays * 86400;
						$nhours = floor($dif / 3600);
						$dif -= $nhours * 3600;
						$nminutes = floor($dif / 60);
						$dif -= $nminutes * 60;
						$nseconds = $dif;
						$sign = '+';
						if ($res['mytime'] < 0)
						{
							$res['mytime'] = - $res['mytime'];
							$sign = '-';
						}
						if ($sign == '-')
						{
							$project_time_left =  $phrase['_ended'] ;
							$show['buynow_available'] = false;
						}
						else
						{
							if ($ndays != '0')
							{
								$project_time_left = $ndays . $phrase['_d_shortform'] . ', ';
								$project_time_left .= $nhours . $phrase['_h_shortform'] . ', ';
								$project_time_left .= $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'];
							}
							else if ($nhours != '0')
							{
								$project_time_left = $nhours . $phrase['_h_shortform'] . ', ';
								$project_time_left .= $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'];
							}
							else
							{
								$project_time_left = $nminutes . $phrase['_m_shortform'] . ', ';
								$project_time_left .= $nseconds . $phrase['_s_shortform'];
							}
						}

						$timeleft = $project_time_left;

						if ($res['status'] == 'open')
						{
							$project_status = $phrase['_event_open_for_bids'];
						}
						else
						{
							$project_status = print_auction_status($res['status']);
							$timeleft = $phrase['_ended'];
							$show['ended'] = true;
						}
					 
					if ($res['status'] == 'finished')
					{
						$placeabid = '<input type="button" name="bid" value="' . $phrase['_place_a_bid'] . '" onclick="location.href=\'' . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $res['project_id'] . '&amp;state=product\'" class="buttons" disabled="disabled" /> ';
						$ends = print_auction_status($res['status']) . ': ' . print_date($res['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                                $show['ended'] = true;
					}
					else if ($res['status'] == 'expired')
					{
						$placeabid = '<input type="button" name="bid" value="' . $phrase['_place_a_bid'] . '" onclick="location.href=\'' . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $res['project_id'] . '&amp;state=product\'" class="buttons" disabled="disabled" /> ';
                                                $show['ended'] = true;
					}
					else if ($res['status'] != 'open' AND $res['close_date'] != '0000-00-00 00:00:00')
					{
						$placeabid = '<input type="button" name="bid" value="' . $phrase['_place_a_bid'] . '" onclick="location.href=\'' . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $res['project_id'] . '&amp;state=product\'" class="buttons" disabled="disabled" /> ';
						$ends = print_auction_status($res['status']) . ': ' . print_date($res['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$timeleft = $phrase['_ended_early'];
                                                $show['ended'] = true;
					}

					if ($res['close_date'] != '0000-00-00 00:00:00')
					{
						if ($res['close_date'] < $res['date_end'])
						{
							$ends = print_date($res['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
							$timeleft = '<span class="blue" style="font-size:13px">' . $phrase['_ended_early'] . '</span>';
						}
					}


					if (empty($_SESSION['ilancedata']['user']['userid']))
					{
						$placeabid = '';
					}

					$city = $res['city'];
					$state = $res['state'];
					$location = $city . ', ' . $state . ', ' . $res['country'];
					$owner_id = $res['user_id'];
 
				
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
                        $show['soldbyauction'] = $ilance->bid->has_winning_bidder(intval($ilance->GPC['id']));
 			if (isset($show['buynow_available']) AND $show['buynow_available'] AND isset($amount) AND $amount > 0)
			{
 				if ($res_bids_av['highest'] > 0)
				{
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


			$show['nourlbit'] = true;
			$navcrumb = array();
			$cat_details=$ilance->categories_parser->fetch_coin_class(0,0,$cid);
			$series_details=$ilance->categories_parser->fetch_coin_series(0,$cat_details[0]['coin_series_unique_no']);
			$denomination_detail=$ilance->categories_parser->fetch_denominations($series_details['coin_series_denomination_no']);
			 $denom =isset($_SESSION['ilancedata']['user']['denomin'])?$_SESSION['ilancedata']['user']['denomin']:'';
			 $ser = isset($_SESSION['ilancedata']['user']['search'])?$_SESSION['ilancedata']['user']['search']:'';;
			if($ser == '')
			{
			 $dd =  "<a href=\"".$denom."\">Back to Search/Browse Page</a>";
			}
			else
			{
			  $dd  = "<a href=\"".$ser."\">Back to Search/Browse Page</a>";
			}
			$navcrumb=array();
		   // new change on Dec-04

		   $status = $res['status'];

		   if ($status == 'open')
		   {
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				  $nav_url=HTTP_SERVER .'Denomination/'.$denomination_detail['denomination_unique_no'].'/'.construct_seo_url_name($denomination_detail['denomination_long']);
			}
			else
			{
			    $nav_url=$ilpage['denomination'].'?denomination='.$denomination_detail['denomination_unique_no'];
			}
			$navcrumb[$nav_url] = $denomination_detail['denomination_long'];
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$nav_url=HTTP_SERVER .'Series/'.$series_details['coin_series_unique_no'].'/'.construct_seo_url_name($series_details['coin_series_name']);
			}
			else
			{
			    $nav_url=$ilpage['search'].'?mode=product&series="'.$series_details['coin_series_unique_no'].'"='.$series_details['coin_series_name'];
			}
			$navcrumb[$nav_url] = $series_details['coin_series_name'];
			$navcrumb["project_title"] = $project_title;
			$navcrumb[""] = $dd;
		}
		else
		{
		$CoinPrices = 'Coin Prices';
		$navcrumb["CoinPrices"] = $CoinPrices;
		if ($ilconfig['globalauctionsettings_seourls'])
			{
				$nav_url=HTTP_SERVER .CoinPrices.'/'.$denomination_detail['denomination_unique_no'].'/'.construct_seo_url_name($denomination_detail['denomination_long']);
			}
			else
			{
			    $nav_url=$ilpage['denomination'].'?denomination='.$denomination_detail['denomination_unique_no'].'&ended=1';
			}
			$navcrumb[$nav_url] = $denomination_detail['denomination_long'];
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$nav_url=HTTP_SERVER .CoinPrices.'/'.SeriesCoin.'/'.$series_details['coin_series_unique_no'].'/'.construct_seo_url_name($series_details['coin_series_name']);
			}
			else
			{
			    $nav_url=$ilpage['search'].'?mode=product&series="'.$series_details['coin_series_unique_no'].'"='.$series_details['coin_series_name'].'&ended=1';
			}
			$navcrumb[$nav_url] = $series_details['coin_series_name'];
			$navcrumb["project_title"] = $project_title;
			$navcrumb[""] = $dd;
		}

		$show['is_winner'] = $show['is_high_bidder'] = $show['is_outbid'] = $show['directpay'] = $show['directpaycompleted'] = $show['is_outbid_ended'] = false;
		
		if(isset($_SESSION['ilancedata']['user']['userid'])and $_SESSION['ilancedata']['user']['userid']>0 and $res['winner_user_id']==$_SESSION['ilancedata']['user']['userid'])
		{
		$show['is_winner']=true;
		}
		
			$directpayurl = $directpaybit = '';
                        $buynoworderid = 0;

			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $res['highest_bidder'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$show['is_high_bidder'] = true;
			}
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $res['highest_bidder'] != $_SESSION['ilancedata']['user']['userid'])
			{
				//$show['is_outbid'] = true;

				//for bug #5481
				if($res['status'] != 'open' && $res['bidstatus'] == 'awarded' && $res['winner_user_id']>0)
				{
					$show['is_outbid_ended'] = true;
				}
				else
				{
					$show['is_outbid'] = true;
				}
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

                        
                        $show['categoryuseproxybid'] = false;
			if ($ilconfig['productbid_enableproxybid'] AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']))
                        {
                                $show['categoryuseproxybid'] = true;
                        }

			$show['startprice'] = $show['currentbid'] = 0;

            // starting bid price
            $haswinner = fetch_auction('haswinner', $res['project_id']);
            $encramnt = $ilance->encrypt->Encrypt_Amount($ilance->currency->format($res['startprice'], $res['currencyid']));
            if($haswinner == 1 && $ilconfig['settings_sold_coins_price_to_image'])
            	$startprice = '<img src="images.php?b='.$encramnt.'&v=" />';
            else	
				$startprice = $ilance->currency->format($res['startprice'], $res['currencyid']);

			if ($ilance->bid->has_bids($res['project_id']) > 0)
			{
				$show['currentbid'] = 1;

				// current bid amount display
				$currentbid = '<strong>' . $ilance->currency->format($cbid) . '</strong>';
				$proxybit = '';

			    //ends
				if (!empty($_SESSION['ilancedata']['user']['userid']))
				{
					  $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($res['project_id'], $_SESSION['ilancedata']['user']['userid']);
					if ($pbit > 0)
					{
						// murugan test
						$highbidderidtest = $res['highest_bidder'];
						if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
						$proxybit = '<span class="green">'.$phrase['_your_maximum_bid'] . ':'.'&nbsp;' . $ilance->currency->format($pbit, $res['currencyid']) . '</span>';
						else
						$proxybit = '<span class="red">'.$phrase['_your_maximum_bid'] . ':'.'&nbsp;' . $ilance->currency->format($pbit, $res['currencyid']) . '</span>';
						//$proxybit2 = $ilance->currency->format($pbit, $res['currencyid']);

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

			 

			//$headinclude .= '<script language="javascript" type="text/javascript" src="' . $ilconfig['template_relativeimagepath'] . DIR_FUNCT_NAME . '/javascript/functions_slideshow.js"></script>' . "\n";
$headinclude .= '<script type="text/javascript" src="'.HTTPS_SERVER.'highslide/highslide.js"></script><link rel="stylesheet" type="text/css" href="'.HTTPS_SERVER.'highslide/highslide.css" />';
			add_category_viewcount($cid);

//			$show['localpickuponly'] = ($res['ship_method'] == 'localpickup') ? true : false;

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


                        $headinclude .= '
<script language="javascript" type="text/javascript">
<!--


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

//-->
</script>';
			// #### item watchlist logic ###########################
			$show['addedtowatchlist'] = false;
			
			//Tamil for bug 2433 * Start
			//Added wc.watchlistid column in select clause of the query resultant $res -- Line no ->796
			
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
			if(strlen($res['watch_check_watchlistid'])>0)
				$show['addedtowatchlist'] = true;
			}
			
			//Tamil for bug 2433 * End
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
            $page_title = stripslashes($res['project_title']) . ' ' . '(' . $phrase['_item'] . ' ' . intval($ilance->GPC['id']) . ') | ' . SITE_NAME . ' Coin Auctions';
			$metakeywords = stripslashes($res['project_title']) . ','.$series_details['coin_series_name'].',' . $denomination_detail['denomination_long'].',coin auctions,great collections,rare coins,greatcollections,auction coins';
			$metadescription=stripslashes($res['project_title']) . ' ' .'(' . $phrase['_item'] . ' ' . intval($ilance->GPC['id']) . ') | ' . SITE_NAME . ' Coin Auctions';

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


			//total page custom for image views
			$slideq = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "attachment a
                        WHERE project_id = '" .$ilance->GPC['id'] . "' AND visible = '1'
								ORDER BY
								cast(SUBSTR(filename from LOCATE('-',filename)+1 for LOCATE('.',filename)-LOCATE('-',filename)-1) as UNSIGNED)");
				$i = 1;
				$k = 0;
				$l = 0;

				if ($ilance->db->num_rows($slideq) > 0)
				{
				$newheadthumb = '';

				$newthumb = '<table   cellpadding="20" >';
			    while ($rowt = $ilance->db->fetch_array($slideq)) {

				/*Tamil For Bug 2635 * Starts */
				
			    $profile_slidq = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER:HTTP_SERVER) . 'image.php?id=' . $rowt['filehash'];
			    $profile_slidqft = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER:HTTP_SERVER) .  'image/400/268/' . $rowt['filename'];
				$profile_sl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER:HTTP_SERVER) .  'image.php?id=' . $rowt['filehash'] .'';
				
				/*Tamil For Bug 2635 * Ends */
				$titlemq = $rowt['filename'];
				$title[] = $titlemq;
				$profile_slides[]= $profile_slidqft;
				$kk[] =$profile_sl;


			    if($profile_slides[$k] == $profile_slides['0'] )
			    {
							 //echo $profile_slides['0'];
						$newhead = '<a id="thumb1" href="'.$kk[$k].'" class="highslide" onclick="return hs.expand(this)" ><img class="rounded" title="'.$res['project_title'].'Online Coin Auction at GreatCollections"  alt="'.$res['project_title'].'Online Coin Auction at GreatCollections" src="'.$profile_slides['0'].'" /></a><div class="highslide-caption" align="center">
For a larger image, click on lower right hand corner.
</div>'	 ;
		        // $newhead ='<img class="rounded" alt="" src="'.$profile_slides['0'].'" />';
			    }

								 if($l%6==0)
								 {
								 $newthumb.='</tr><tr>';
								 }

								/*Tamil For Bug 2635 * Starts */

								 $newthumb.= '<td><a id="thumb1" href="'.$kk[$k].'" class="highslide" onclick="return hs.expand(this)">
	<img  class="rounded" title="'.$res['project_title'].'Online Coin Auction at GreatCollections"  alt="'.$res['project_title'].'Online Coin Auction at GreatCollections"  src="' .((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER:HTTP_SERVER).  'image/140/170/' . $rowt['filename'] .'"/></a><div class="highslide-caption" align="center">
 For a larger image, click on lower right hand corner.
</div></td>';

								/*Tamil For Bug 2635 * Ends */

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

					    $newhead ='<center><img src="images/gc/nophoto.gif" style="padding: 10px;" ></center>';
				}
				//item info
				// bug # 4500 kumaravel start

				$itinfo = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "coins c

                        WHERE  c.coin_id = '" .$ilance->GPC['id'] . "'

						      ");

				if($ilance->db->num_rows($itinfo) > 0)
				{
						// coin table only

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
							$pro_tes =  fetch_cat('coin_detail_proof',$res['cid']);
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

							if($row_info['Star']==1)
								$pro_tes1= '★ ';
							else
								$pro_tes1= '';

							$pro_tes1 .= fetch_cat('coin_detail_suffix',$res['cid']);

							if($row_info['Grade'] > 0)
							{
								$testop = $text_pro;
							}
							else
							{
								$testop = $row_info['Grade'] = '';
							}

							$show['nodescription'] = false;
							if(!empty($row_info['Description']))
							{
							  $show['nodescription'] = true;
							}
							$row_info['grv'] = $testop;



				}
				else
				{
						// coin_retruned table only
						$itinfo_4500 = $ilance->db->query("
													SELECT *
                       								 FROM " . DB_PREFIX . "coins_retruned r

                       								 WHERE  r.coin_id = '" .$ilance->GPC['id'] . "'");

							$row_info = $ilance->db->fetch_array($itinfo_4500);
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
							$pro_tes =  fetch_cat('coin_detail_proof',$res['cid']);
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

							if($row_info['Star']==1)
								$pro_tes1= '★ ';
							else
								$pro_tes1= '';

							$pro_tes1 .= fetch_cat('coin_detail_suffix',$res['cid']);

							if($row_info['Grade'] > 0)
							{
								$testop = $text_pro;
							}
							else
							{
								$testop = $row_info['Grade'] = '';
							}
							
							$show['nodescription'] = false;
							if(!empty($row_info['Description']))
							{
							  $show['nodescription'] = true;
							}
							$row_info['grv'] = $testop;



				}



				$info_val[] = $row_info;
				// bug # 4500 kumaravel start end

				
				//coin info
				$itinfo_cid = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "catalog_coin

                        WHERE  PCGS = '" .$cid . "'

						      ");

				$row_cid = $ilance->db->fetch_array($itinfo_cid);

				if($row_cid['coin_detail_mintage'] != '')
				{
				$show['coin_detail_mintage'] = '1';
				}
				$info_cid[] = $row_cid;

				$show['relv'] = 'rels';


				$alt_no = $res['alt_no'];
			  
				if(($res['norder']) == 1)
				{
				  $show['norder_list'] = 1;
				}
				
				
			    $currency = $ilance->currency->currencies[1]['symbol_left'];



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

//##################################################//
// TkQ 10/6/2016: Added convertDate(), cleanDate()  //
// and the vars for the google/iCal event path      //
//##################################################//
				require 'functions/iCalcreator/iCalcreator.class.php';
			//takes a date and converts it to a ISO8601 compliant date object
			function createDate($timestamp) {
				$objDateTime = new DateTime($timestamp); //create time object from the timestamp string
				return $objDateTime;
			}

			function convertDate($objDateTime){
				$objDateTime = $objDateTime->format(DateTime::ISO8601); //convert that object to ISO8601
				return $objDateTime;
			}

			//strips hyphens dashes and extra characters from the date object
			function cleanDate($string) {
				$string = str_replace('-', '', $string); // Removes dashes.
				$string = str_replace(':', '', $string); // Removes hyphens.
				$beforePlus = current(explode('+', $string));  //removes extra characters the old google calender Template cant handle
				return $beforePlus;
			}

			//creates an end time one hour after the start time
			function createEnd($timestamp){
				$timestamp->modify('+1 hour');
				return $timestamp;
			}

			function setMintage($input){

                if($input==0 || $input==null){

                    $mintage = "Sorry, mintage information is not available for this item";

                } else {

                    $mintage = $input; // mint number
                }

			    return $mintage;
            }




			//construct vars for calender entries
			$url = 'http://www.google.com/calendar/event?action=TEMPLATE';
			$dbTime = $res['date_end']; //event start time
			$coinType = $denomination_detail['denomination_long'];//coin type
			$coinSeries = $series_details['coin_series_name'];  // coin series
            $mintage = setMintage($row_cid['coin_detail_mintage']);


			$catalogNum = $res['cid']; //catalog number
			$auctionStatus = $project_status; // auction status
			$GCID = intval($ilance->GPC['id']);
			$evTime = cleanDate(convertDate(createDate($dbTime))); //strt date to be sent to the template
			$evTimeEnd = cleanDate(convertDate(createEnd(createDate($dbTime)))); //end date to be sent to the template

			//coin description construction with line feeds
			$coinDetailConcat = "Coin Type: ".$coinType."\n"."Series: ".$coinSeries."\n"."Mintage: ".$mintage."\n"."Catalog Number: ".$catalogNum."\n"."GC ID #: ".$GCID."\n"."Link: ".$pageurlpath;
			//escaped coin description to be sent to the template
			$escapedCoinDetail = htmlentities(strip_tags($coinDetailConcat));
            //encode coin details in a 64bit string to preserve line breaks
            $encodedCoinDetail = base64_encode($coinDetailConcat);
			//escaped and stripped title to be sent to the tempalte
			$escapedTitle = htmlentities(strip_tags($page_title));

			//construct google url (google path)
			$url .= '&text='.urlencode("Ending: ".$escapedTitle); // title
			$url .= '&dates='.$evTime."/".$evTimeEnd; //clean and set dates
			$url .= '&ctz=America/Los_Angeles'; //hardcode timezone
			$url .= '&details='.urlencode($coinDetailConcat); // event details
			$url .= '&sprop='.$pageurlpath; // url of the event

			$google_path = $url;


//##################################################//
//TkQ End Edit 10/19/16								//
//##################################################//


				//new change
				$statusnew = $res['status'];//fetch_auction('status',$project_id);

				//new change on Dec-04

				if($statusnew == 'closed' || $statusnew == 'expired' || $statusnew == 'finished')
				{

				    if($res['filtered_auctiontype'] == 'fixed')
					{

					  $sold_text ='Sold Price:';

					  $soldprice = $ilance->currency->format($res['buynow_price']);
				   }

				   else
				   {

					 $bid_det = $res['haswinner'];

					if($res['filtered_auctiontype'] == 'regular' AND $bid_det == '1')
					{

					  $sold_text = '';

					  $soldprice = '';

					}
					else
					{
					  $sold_text ='Minimum Bid: ';

					  $soldprice = $ilance->currency->format($res['currentprice']);
					}

					}


				}
//daily deal
						if($res['live_date']!='')
						{
						if($res['status']=='open')
						{
						$date_exp = explode('-',$res['live_date']);
						$mydate = date("F j, Y", mktime(0, 0, 0, $date_exp['1'], $date_exp['2'], $date_exp['0']));
						$dail = 'This item will be featured as a 24-Hour Deal on '.$mydate.'';
						$daily = ($res['live_date'] == DATETODAY) ? '' : $dail;
						  
							if($res['live_date'] == DATETODAY)
							{
							$show['daily']='1';
							 
							$offamt = $res['offer_amt'];
							$rrr = $offamt+$res['buynow_price'];
							
							$tt_est = 'Currently Discounted: 24-Hour Deal. Was $'.$rrr.'. Save $'.$offamt.'!';

							}
						}else
						{
							$list='<div style="color:#FF0000; font-weight:bold;">24-Hour Deal is now Sold Out.</div>';
						}
						}
	
				//for Bug #5140
				global $ask_username;
				if(!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
				{
					$user_detls = $ilance->db->query(" SELECT username, first_name, last_name  FROM " . DB_PREFIX . "users WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' ");
					$userdet = $ilance->db->fetch_array($user_detls, DB_ASSOC);
					
					if($userdet['first_name'] !='' OR $userdet['last_name'] != '')
						$ask_username = $userdet['first_name']." ".$userdet['last_name'];
					else
						$ask_username = $userdet['username'];
				}
	

	
	                        $pprint_array = array('user_maxbid_amount','ask_username','tt_est','newthumb','daily','soldprice','hidden_val','pageurlpath','twitpath','facepath', 'escapedTitle', 'encodedCoinDetail', 'evTime','evTimeEnd','ical_path','google_path','mesa','mess','disab','population_guide','price_guide_info','watch_list_note','no_tracking_users','alt_no','totalvalue','dismsg','vel','trva','newhead','pro_tes1','list','sold_text','currency','shipperid','featured','featured_date','highlite','bold','autorelist','enhancements','transactionstatus','winningbid','winningbidder','jsend','min_bidamountformatted','min_bidamount','date_starts','additional_info','views','proxybit','merchantscore','highbidderscore','currentbid','startprice','feed12','qty_pulldown','cid','reserve_met','buynow_price','buynow_price_plain','timeleft','ends','bids','highbidder','additional_info','buyer_stars','project_title','description','project_id','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server'	);


			($apihook = $ilance->api('merch_detailed_end')) ? eval($apihook) : false;

                        $ilance->template->fetch('main', 'item_page.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('myslide','info_rel','info_cid_pro','info_cid','info_val','bid_results_rows','unique_bid_results_rows','messages','purchase_now_activity','otherlistings','lastviewedlistings'));

			($apihook = $ilance->api('merch_detailed_loop')) ? eval($apihook) : false;

			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);

			exit();
		}
		else
		{

		//Karthik on Oct-25 for navcrumb

		    $navcrumb = array();
           $navcrumb[HTTP_SERVER .'Denominations'] = 'Denomination';
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
