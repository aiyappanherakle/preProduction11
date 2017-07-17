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
        'accounting',
        'buying',
        'selling',
        'subscription',
        'feedback',
        'registration',
        'search',
        'javascript',
        'watchlist'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'md5',
    'countries',
	'flashfix',
	'jquery'
);
// #### setup script location ##################################################
define('LOCATION', 'rfp');
// #### require backend ########################################################
require_once('./functions/config.php');
// #### require shipping backend ###############################################
require_once(DIR_CORE . 'functions_shipping.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[rfp]" => $ilcrumbs["$ilpage[rfp]"]);

 //vijay work 4.12.14 

		
if(isset($ilance->GPC['place_bid_w'])  AND ($ilance->GPC['place_bid_w'] == 'Place bid' OR $ilance->GPC['place_bid_w'] == 'Revise Bid'))
{
	// #### HANDLE BATCH BIDDINGD FROM LISTING PAGE ###################################		
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
	$area_title = $phrase['_placing_a_bid'];
	$show['widescreen'] = false;

		if(isset($ilance->GPC['bid_amount'])) 
		{
		$coin_array =   array();
		$placebidamount =$ilance->GPC['bid_amount'];
		$notice=0;
			foreach($placebidamount as $key =>  $set_bid_amount)
			{
			
			if($set_bid_amount['next_bid_amount']!='' && $set_bid_amount['next_bid_amount']>=$set_bid_amount['min_bid_amount'])
			{
						
			 $notice++;
			 $set_bid_amount['projectlist_id'];
			 $set_bid_amount['min_bid_amount'];
			 $set_bid_amount['next_bid_amount'];
			// $set_bid_amount['project_title'];

				// //confirm bid image change on apr04

				$slideq = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "projects p								             
									left join  " . DB_PREFIX . "attachment a on a.project_id=p.project_id and a.attachtype='itemphoto'
									WHERE p.project_id = '" .$set_bid_amount['projectlist_id'] . "'
									AND p.project_id = a.project_id
									AND a.visible = '1'
									ORDER BY 
									cast(SUBSTR(a.filename from LOCATE('-',a.filename)+1 for LOCATE('.',a.filename)-LOCATE('-',a.filename)-1) as UNSIGNED)");
					$i = 1;
					$k = 0;
					$l = 0;
					if ($ilance->db->num_rows($slideq) > 0)
					{
					if (!empty($_SESSION['ilancedata']['user']['userid']))
					   {
					   
						 $ilance->bid = construct_object('api.bid');
						 $ilance->bid_proxy = construct_object('api.bid_proxy');
				   
						  $pbid = $ilance->currency->format($ilance->bid_proxy->fetch_user_proxy_bid($set_bid_amount['projectlist_id'], $_SESSION['ilancedata']['user']['userid']));
					   }
				
					}
					else
					{
					 $uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
							
							$newhead ='<center><img src="images/gc/nophoto.gif" style="padding: 10px;" ></center>';
					}
					
					
				  if (!empty($_SESSION['ilancedata']['user']['userid']))
				   {
				   
					 $ilance->bid = construct_object('api.bid');
					 $ilance->bid_proxy = construct_object('api.bid_proxy');
			   
					  $set_bid_amount['previous_bidamount'] = $ilance->currency->format($ilance->bid_proxy->fetch_user_proxy_bid($set_bid_amount['projectlist_id'], $_SESSION['ilancedata']['user']['userid']));
				   }
				

				
			// //end confirm bid image

			// avoid bid minimum manipulations
			$sql_startprice = $ilance->db->query("
			SELECT startprice, currentprice,project_title,description,date_starts,user_id
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($set_bid_amount['projectlist_id']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_startprice) > 0)
			{
			$res = $ilance->db->fetch_array($sql_startprice, DB_ASSOC);
			$set_bid_amount['current_bidamount'] =  sprintf("%01.2f",$res['currentprice']);
			$set_bid_amount['current_bidamount_formatteds']=$ilance->currency->format($res['currentprice'],0);
			$set_bid_amount['title'] = '<span class="blue">' . stripslashes($res['project_title']) . '</span>';

			$set_bid_amount['description'] = $ilance->bbcode->strip_bb_tags($res['description']);
			$set_bid_amount['description'] = short_string($res['description'], 100);
			$set_bid_amount['description'] = handle_input_keywords($res['description']);


			// is owner trying to bid?
			if ($res['user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
					$area_title = $phrase['_bid_denied_cannot_bid_on_own_auction'];
					$page_title = SITE_NAME . ' - ' . $phrase['_bid_denied_cannot_bid_on_own_auction'];
					
					print_notice($area_title, $phrase['_sorry_merchants_cannot_place_bids_on_their_own_product_auctions'].'<br />', 'javascript:history.back(1);', $phrase['_back']);
					exit();
			}

			if ($res['date_starts'] > DATETIME24H)
			{
					print_notice($phrase['_auction_event_is_scheduled'], $phrase['_this_auction_event_is_scheduled_and_has_not_started_yet'], $ilpage['main'], $phrase['_main_menu']);
					exit();
			}


			}

			$string= explode('.',$set_bid_amount['next_bid_amount']);
			$set_bid_amount['next_bid_amount']= preg_replace('/[^0-9\-]/','', $string[0]);
			$string[1]=isset($string[1])?$string[1]:'00';
			$set_bid_amount['next_bid_amount'] = $set_bid_amount['next_bid_amount'].'.'.$string[1];



			$sql2="SELECT sum(bidamount) as allopenbids  FROM " . DB_PREFIX . "project_bids WHERE  user_id='".$_SESSION['ilancedata']['user']['userid']."' and bidstatus='placed'";
			$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($res2)>0)
			{
			while($line2=$ilance->db->fetch_array($res2))
			{
			$bidlimit=fetch_user('bidlimit',$_SESSION['ilancedata']['user']['userid']);
			if($bidlimit>0)
			{
				if(($line2['allopenbids']+$set_bid_amount[next_bid_amount])>$bidlimit)
				{
				print_notice($phrase['_bid_minimum_warning'], $phrase['_you_have_exceeded_the_bid_limit_please_call_Tel_or_click_here_to_contact_us']."<br />", 'javascript:history.back(1);', $phrase['_back']);
			exit();
			}
			}
			}
			}           
			$state = 'product';


			$set_bid_amount['next_bidamount'] =  sprintf("%01.2f",$set_bid_amount['next_bid_amount']);
			$set_bid_amount['next_bidamountformatted'] = $ilance->currency->format($set_bid_amount['next_bidamount'] );
			
			// #### show watchlist options selected ########################
			$sql = $ilance->db->query("
					SELECT *,a.filename as filehash
					FROM " . DB_PREFIX . "watchlist w
					left join  " . DB_PREFIX . "attachment a on w.watching_project_id=a.project_id and a.attachtype='itemphoto'
					WHERE w.watching_project_id = '" . $set_bid_amount['projectlist_id'] . "' 
							AND w.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) == 0)
			{                        
				$set_bid_amount['lasthour'] = '<input type="hidden" name="lasthournotify" id="lasthournotify" value="1" />';
					$set_bid_amount['higherbid'] = '<input type="hidden" name="highbidnotify" id="highbidnotify" value="1" />';
					$set_bid_amount['subscribed'] = '<input type="hidden" name="subscribed" id="subscribed" value="1" />';
			}
			else
			{
					while ($res = $ilance->db->fetch_array($sql))
					{
					if(!empty($res['filehash']))
						{
							$profile_sl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'image/250/400/' . $res['filehash'];

						}else
						{
							$profile_sl=$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						}
						
							$set_bid_amount['newhead']='<img class="rounded" alt="" src="'.$profile_sl.'" /><div class="highslide-caption" align="center"></div>' ;
							
							$set_bid_amount['lasthour'] = (($res['hourleftnotify']) ? '<input  type="hidden" name="lasthournotify" id="lasthournotify" value="1" /> ' : '<input type="hidden" name="lasthournotify" id="lasthournotify" value="1" /> ');
							$set_bid_amount['higherbid'] = (($res['highbidnotify']) ? '<input  type="hidden" name="highbidnotify" id="highbidnotify" value="1" />' : '<input type="hidden" name="highbidnotify" id="highbidnotify" value="1" />');
							$set_bid_amount['subscribed'] = (($res['subscribed']) ? '<input  type="hidden" name="subscribed" id="subscribed"  value="1" />' : '<input type="hidden" name="subscribed" id="subscribed" value="1" />');
					}
					
			}


			 
			$coin_array[]   =   $set_bid_amount;

			}//end

			}
			
			if($notice==0)
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}

			$user_detls = $ilance->db->query(" SELECT *  FROM " . DB_PREFIX . "users WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' ");
			$userdet = $ilance->db->fetch_array($user_detls, DB_ASSOC);
							
			if($userdet['enable_bid'] =='1')
			{
					print_notice($phrase['_sorry '], "Your ability to bid has been restricted. Please <a href='/main-contact'>contact GreatCollections' Customer Service</a> at 1.800.44.COINS (+1.949.679.4180)", "watchlist", $phrase['_back']);
					exit();
			}


			$pprint_array = array('current_bidamount','qty','title','description','remote_addr','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');

			$ilance->template->fetch('main', 'listing_multiple_placebid_preview.html');
			$ilance->template->parse_loop('main', array('coin_array'));
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}

	}
	else
	{
	    refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['watchlist'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	    exit();
	}
}

// #### REVISE BID FOR PRODUCT AUCTION #########################################
			
if(isset($ilance->GPC['revise_bids_w']) AND $ilance->GPC['revise_bids_w'] == 'Revise bid amount')
{


if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
// #### define top header nav ##########################################
$topnavlink = array(
	'main_listings'
);
	
$id = intval($ilance->GPC['id']);

$show['widescreen'] = false;
$area_title = $phrase['_placing_a_bid'];

$page_title = SITE_NAME . ' - ' . $phrase['_placing_a_bid'];


$show['nourlbit'] = true;

    
$confirmbid_array   =   array();

$confirmbidamount =$ilance->GPC['confirm_bid_amount'];
$r=1;

foreach($confirmbidamount as $key   =>  $confirmbidamt)
{


// exit;

        $ilance->bid = construct_object('api.bid');
        $ilance->bid_proxy = construct_object('api.bid_proxy');
        $ilance->auction = construct_object('api.auction');
        $ilance->subscription = construct_object('api.subscription');
        $ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = false);

		// #### check subscription #####################################
		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'productbid') == 'no')
		{
				$area_title = $phrase['_access_to_bid_is_denied'];
				$page_title = SITE_NAME . ' - ' . $phrase['_access_to_bid_is_denied'];
		
				print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('productbid'));
				exit();
		}
		
		$project_state = fetch_auction('project_state', $confirmbidamt['projectlist_id']);
		if ($project_state != 'product')
		{
			$area_title = $phrase['_access_to_bid_is_denied'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_to_bid_is_denied'];
			
			print_notice($phrase['_access_denied'], $phrase['_access_denied'], $ilpage['main'], ucwords($phrase['_click_here']));
			exit();	
		}
        

        
        // #### check if the listing id was properly entered ###########
        if (empty($confirmbidamt['projectlist_id']) OR $confirmbidamt['projectlist_id'] <= 0)
        {
                $area_title = $phrase['_bad_rfp_warning'];
                $page_title = SITE_NAME . ' - ' . $phrase['_bad_rfp_warning'];
                
                print_notice($phrase['_invalid_rfp_specified'], $phrase['_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal'], $ilpage['search'], $phrase['_search_rfps']);
                exit();
        }
        
        // #### determine if bidder can pass any filter requirements by the seller
        $ilance->bid->product_bid_filter_checkup($confirmbidamt['projectlist_id']);
        
        // #### determine if this bidder was invited to place a bid
        if (is_bidder_invited($_SESSION['ilancedata']['user']['userid'], $confirmbidamt['projectlist_id']) == false)
        {
                $area_title = $phrase['_you_have_not_been_invited_to_place_a_bid'];
                $page_title = SITE_NAME . ' - ' . $phrase['_you_have_not_been_invited_to_place_a_bid'];
                
            print_notice($phrase['_bid_filter_restriction'] . "&nbsp;" . $phrase['_this_rfp_has_expired_bidding_is_over'], $phrase['_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project']."<br /><br />".$phrase['_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project'], $ilpage['main'], $phrase['_main_menu']); 
                exit();
        }
        
        // #### show watchlist options selected ########################
        $sql = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "watchlist
                WHERE watching_project_id = '" . $confirmbidamt['projectlist_id'] . "' 
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) == 0)
        {                        
                $lasthour = '<input type="checkbox" name="lasthournotify" id="lasthournotify" value="1" />';
                $higherbid = '<input type="checkbox" name="highbidnotify" id="highbidnotify" value="1" />';
                $subscribed = '<input type="checkbox" name="subscribed" id="subscribed" value="1" />';
        }
        else
        {
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $lasthour = (($res['hourleftnotify']) ? '<input checked type="checkbox" name="lasthournotify" id="lasthournotify" value="1" /> ' : '<input type="checkbox" name="lasthournotify" id="lasthournotify" value="1" /> ');
                        $higherbid = (($res['highbidnotify']) ? '<input checked type="checkbox" name="highbidnotify" id="highbidnotify" value="1" />' : '<input type="checkbox" name="highbidnotify" id="highbidnotify" value="1" />');
                        $subscribed = (($res['subscribed']) ? '<input checked type="checkbox" name="subscribed" id="subscribed"  value="1" />' : '<input type="checkbox" name="subscribed" id="subscribed" value="1" />');
                }
        }
         
       
        
        // #### rebid details (if applicable) ##########################
        $revise_coin_bid_amount['current_bidamount'] = 0;
        $sql_bid = $ilance->db->query("
                SELECT bidamount
                FROM " . DB_PREFIX . "project_bids
                WHERE project_id = '" .$confirmbidamt['projectlist_id'] . "'
                        AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql_bid) > 0)
        {
                $res_bid = $ilance->db->fetch_array($sql_bid);
                $revise_coin_bid_amount['current_bidamount'] = $res_bid['bidamount'];
				
        }
        $revise_coin_bid_amount['next_bid_amount']= sprintf("%.02f", $confirmbidamt['next_bid_amount']);
        // auction details
        $sql_rfp = $ilance->db->query("
                SELECT p.*, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, s.ship_method
                FROM " . DB_PREFIX . "projects p
				LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
                WHERE p.project_id = '" . $confirmbidamt['projectlist_id']  . "'
                        AND p.project_state = 'product'
        ", 0, null, __FILE__, __LINE__);
		
	
		
        if ($ilance->db->num_rows($sql_rfp) > 0)
        {
                $res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC);
                
				$revise_coin_bid_amount['auctiontype']=$res_rfp['filtered_auctiontype'];
                $auctiontype = $res_rfp['filtered_auctiontype'];
                if ($auctiontype == 'fixed')
                {
                        $buynow_qty = $res_rfp['buynow_qty'];
                }
                
                // quantity available
                $qty = $res_rfp['buynow_qty'];
                
                // is owner trying to bid?
                if ($res_rfp['user_id'] == $_SESSION['ilancedata']['user']['userid'])
                {
                        $area_title = $phrase['_bid_denied_cannot_bid_on_own_auction'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_bid_denied_cannot_bid_on_own_auction'];
                        
                        print_notice($area_title, $phrase['_sorry_merchants_cannot_place_bids_on_their_own_product_auctions'].'<br />', 'javascript:history.back(1);', $phrase['_back']);
                        exit();
                }
                
                if ($res_rfp['date_starts'] > DATETIME24H)
                {
                        print_notice($phrase['_auction_event_is_scheduled'], $phrase['_this_auction_event_is_scheduled_and_has_not_started_yet'], $ilpage['main'], $phrase['_main_menu']);
                        exit();
                }
                
                $currency = print_left_currency_symbol();
                // highest bid amount placed for this auction
                $highestbid = 0;
                $highbid = $ilance->db->query("
                        SELECT MAX(bidamount) AS maxbidamount
                        FROM " . DB_PREFIX . "project_bids
                        WHERE project_id = '" . $confirmbidamt['projectlist_id']  . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($highbid) > 0)
                {
                        $res = $ilance->db->fetch_array($highbid, DB_ASSOC);
                        $highestbid = sprintf("%.02f", $res['maxbidamount']);
                }
                
                $revise_coin_bid_amount['title'] = stripslashes($res_rfp['project_title']);
                $revise_coin_bid_amount['projectlist_id'] = intval($confirmbidamt['projectlist_id']);
                
                // show starting bid price
                $startprice = $ilance->currency->format($res_rfp['startprice'], $res_rfp['currencyid']);
                $buynowprice = '';
                if ($res_rfp['buynow_price'] > 0)
                {
                        $buynowprice = $ilance->currency->format($res_rfp['buynow_price'], $res_rfp['currencyid']);
                }
                
                $show['hasnobids'] = false;
                $show['currentbid'] = true;
                $currentprice = $ilance->currency->format($res_rfp['currentprice'], $res_rfp['currencyid']);
                $revise_coin_bid_amount['min_bidamount'] = sprintf("%.02f", '0.01');
                $revise_coin_bid_amount['min_bidamountformatted'] = $ilance->currency->format('0.01', $res_rfp['currencyid']);
                                 
                if ($res_rfp['bids'] <= 0)
                {
                        $show['hasnobids'] = true;
                        $show['currentbid'] = false;
                        
                        // do we have starting price?
                        if ($res_rfp['startprice'] > 0)
                        {
                                $revise_coin_bid_amount['min_bidamount']= sprintf("%.02f", $res_rfp['startprice']);
                                $revise_coin_bid_amount['min_bidamountformatted'] = $ilance->currency->format($res_rfp['startprice'], $res_rfp['currencyid']);
                                
                                // just in case our highest bid is 0 we will check our starting bid
                                // and adjust the $highestbid variable to the start price to at least
                                // generate the next increment if we've defined any in this category
                                if ($highestbid == 0)
                                {
                                        $highestbid = $revise_coin_bid_amount['min_bidamount'];
                                }
                        }
                        
                        $currentprice = $ilance->currency->format($revise_coin_bid_amount['min_bidamount'], $res_rfp['currencyid']);
                }
                
                // is admin using custom bid increments?
                $revise_coin_bid_amount['proxybit'] = '';
                $incrementgroup = $ilance->categories->incrementgroup($res_rfp['cid']);
                $sqlincrements = $ilance->db->query("
                        SELECT amount
                        FROM " . DB_PREFIX . "increments
                        WHERE ((increment_from <= $highestbid
                                AND increment_to >= $highestbid)
                                        OR (increment_from < $highestbid
                                AND increment_to < $highestbid))
                                AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
                        ORDER BY amount DESC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlincrements) > 0)
                {
                        $show['increments'] = 1;
                        
                        $resincrement = $ilance->db->fetch_array($sqlincrements);
                        $increment = $ilance->currency->format($resincrement['amount'], $res_rfp['currencyid']) . ' - <a href="javascript:void(0)" onclick="Attach(\'' . HTTP_SERVER . $ilpage['rfp'] . '?msg=bid-increments&amp;c=' . $res_rfp['cid'] . '\')">' . $phrase['_more'] . '</a>';
                        if ($res_rfp['bids'] > 0)
                        {
                                // if we have more than 1 bid start the bid increments since the first bidder cannot bid against the opening bid
                                $revise_coin_bid_amount['min_bidamount'] = sprintf("%.02f", $highestbid + $resincrement['amount']);
                                $revise_coin_bid_amount['min_bidamountformatted'] = $ilance->currency->format(($highestbid + $resincrement['amount']), $res_rfp['currencyid']);
                        }
                        
                        $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($res_rfp['project_id'], $_SESSION['ilancedata']['user']['userid']);
                        if ($pbit > 0)
                        {
                                $revise_coin_bid_amount['proxybit'] = $ilance->currency->format($pbit, $res_rfp['currencyid']) . ' : ' . $phrase['_invisible'];
                        }
                }
                else
                {
                        $show['increments'] = 0;
                        
                        // admin should define some increments if we get to this point
                        $increment = $ilance->currency->format(0, $res_rfp['currencyid']) . ' - <a href="javascript:void(0)" onclick="Attach(\'' . $ilpage['rfp'] . '?msg=bid-increments&amp;c=' . $res_rfp['cid'] . '\')">' . $phrase['_more'] . '</a>';
                        // minimum bid amount
                        $revise_coin_bid_amount['min_bidamount'] = sprintf("%.02f", $highestbid)+ 0.01;
                        $revise_coin_bid_amount['min_bidamountformatted']= $ilance->currency->format($revise_coin_bid_amount['min_bidamount'], $res_rfp['currencyid']);
                        
                        $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($res_rfp['project_id'], $_SESSION['ilancedata']['user']['userid']);
                        if ($pbit > 0)
                        {
                                $revise_coin_bid_amount['proxybit'] = $ilance->currency->format($pbit, $res_rfp['currencyid']) . ' - ' . $phrase['_invisible'];
                        }
                }
                
                $proxytext = '';
                $show['categoryuseproxybid'] = false;
                if ($ilconfig['productbid_enableproxybid'] AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], 'product', $res_rfp['cid']))
                {
                        $show['categoryuseproxybid'] = true;
                        $proxytext = '<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_when_you_place_a_bid_for_an_item_enter_the_maximum_amount\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a> ' . $phrase['_proxy'] . '';
		
		if (isset($pbit) AND $pbit > $revise_coin_bid_amount['min_bidamount'])
		{
			 $revise_coin_bid_amount['min_bidamount'] = sprintf("%.02f", $pbit) + 0.01;
			$revise_coin_bid_amount['min_bidamountformatted'] = $ilance->currency->format($revise_coin_bid_amount['min_bidamount'], $res_rfp['currencyid']);
		}
                }
                
                
                
               
                
                // #### do we have a reserve price #####################
				$revise_coin_bid_amount['reserve_auction']=0;
                $reserve_auction = 0;
                $reserve_met = '';
                if ($res_rfp['reserve'])
                {
                        $reserve_auction = 1;
						$revise_coin_bid_amount['reserve_auction']=1;
                        $highest_amount = '--';
                        
                        $sql_highest = $ilance->db->query("
                                SELECT MAX(bidamount) AS highest
                                FROM " . DB_PREFIX . "project_bids
                                WHERE project_id = '" . $res_rfp['project_id'] . "'
                                ORDER BY highest
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_highest) > 0)
                        {
                                $res_highest = $ilance->db->fetch_array($sql_highest, DB_ASSOC);
                                $highest_amount = $res_highest['highest'];
                        }
                        
                        // is reserve met?
                        if ($highest_amount != '--' AND $highest_amount >= $res_rfp['reserve_price'])
                        { 
                                $revise_coin_bid_amount['reserve_met'] = $phrase['_yes_reserve_price_met'];
                        }
                        else
                        {
                                $reserve_met = '<span style="color:red">' . $phrase['_no_reserve_price_not_met']. '</span>';
                                if ($show['hasnobids'] AND $show['currentbid'])
                                {
                                       $revise_coin_bid_amount['reserve_met'] .= '<div><strong>' . $phrase['_this_bid_will_be_the_actual_bid_placed_up_to_the_reserve_price'] . '</strong></div>';
                                }
                        }
                }
	


	
	
		}
		
		$revise_coin_bid_amount['r']=$r;
		$r++;
		$revise_coin_array[]   =   $revise_coin_bid_amount;
	//echo '<pre>';
// print_r($revise_coin_bid_amount);
	
	
	
}
		//exit;
		
		
		
		
$pprint_array = array('shipperid','shipservicepulldown','lasthour','higherbid','subscribed','qty','proxybit','buynowprice','currentprice','buynow_qty','reserve_met','min_bidamountformatted','startprice','proxytext','state','increment','highestbid','min_bidamount','current_bidlock_amount','spellcheck_style','attachment_style','pmb_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','currency_proposal','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','input_style','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'listing_forward_auction_rivisebid.html');
		$ilance->template->parse_loop('main', array('revise_coin_array'));
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();

}
else
{
    refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['watchlist'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
    exit();
}

}
		
		
// #### HANDLE BUYER TOOLS FROM LISTING PAGE ###################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyertools' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'enhancements' AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0)
{
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
        {
                refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['rfp'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
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
		$enhance = $ilance->auction_rfp->process_listing_enhancements_transaction($ilance->GPC['enhancements'], $_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['pid']), 'update', 'service');
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
	
	print_notice($phrase['_listing_updated'], $phrase['_the_options_you_selected_have_been_completed_successfully'], HTTP_SERVER . $ilpage['rfp'] . '?id=' . $ilance->GPC['pid'], $phrase['_return_to_listing']);
	exit();
}
// #### EXTERNAL RFP TAKEOVER REQUEST NOTIFICATION HANDLER #####################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'rfp-accept' AND isset($ilance->GPC['xcode']) AND !empty($ilance->GPC['xcode']))
{
        $area_title = $phrase['_rfp_takeover_acceptance_request_in_progress'] . ' . .';
	$page_title = SITE_NAME . ' - ' . $phrase['_rfp_takeover_acceptance_request_in_progress'] . ' . .';
        
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	$sqlxcode = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "projects
                WHERE transfer_code = '" . $ilance->db->escape_string($ilance->GPC['xcode']) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sqlxcode) == 0)
        {
		$area_title = $phrase['_invalid_rfp_transfer_code'];
		$page_title = SITE_NAME . ' - ' . $phrase['_invalid_rfp_transfer_code'];
                print_notice($phrase['_invalid_rfp_transfer_code'], $phrase['_were_sorry_there_was_a_problem_with']."<br /><br />".$phrase['_If_you_are_clicking_a_link_within_your_email_client']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
		exit();	
	}
	
	$ilance->auction = construct_object('api.auction');
	
	$result_project = $ilance->db->fetch_array($sqlxcode, DB_ASSOC);
	
	// #### new project owner information
	$sql_newowner = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $result_project['transfer_to_userid'] . "'
			AND status = 'active'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_newowner) == 0)
	{
		$area_title = $phrase['_invalid_rfp_transfer_to_owner'];
		$page_title = SITE_NAME . ' - ' . $phrase['_invalid_rfp_transfer_to_owner'];
		print_notice($phrase['_invalid_rfp_transfer_to_new_owner'], $phrase['_were_sorry_there_was_a_problem_with_the_rfp_transfer_to_the_new_owner']."<br /><br /><li>".$phrase['_the_buyer_no_longer_exists_on_our_marketplace']."</li><li>".$phrase['_the_buyer_has_been_suspended_from_using_the_marketplace_resources']."</li><li>".$phrase['_the_buyer_does_not_have_proper_permissions_to_accept_rfp_takeover_requests_from_others']."</li><br /><br />".$phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
		exit();
	}
	
	$result_newowner = $ilance->db->fetch_array($sql_newowner, DB_ASSOC);
	
	// #### old project owner information
	$sql_oldowner = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $result_project['transfer_from_userid'] . "'
			AND status = 'active'
	", 0, null, __FILE__, __LINE__);
	
	$result_oldowner = $ilance->db->fetch_array($sql_oldowner, DB_ASSOC);
	
	$area_title = $phrase['_rfp_transfer_of_ownership_complete'].' ['.$result_project['project_title'].']';
	$page_title = SITE_NAME . ' - ' . $phrase['_rfp_transfer_of_ownership_complete'];
	// #### accept rfp transfer takeover request
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects
		SET user_id = '" . $result_project['transfer_to_userid'] . "',
		transfer_status = 'accepted',
		transfer_code = '<---" . $phrase['_transfer_complete_upper'] . "--->'
		WHERE project_id = '" . $result_project['project_id'] . "'
			AND transfer_code = '" . $ilance->db->escape_string($ilance->GPC['xcode']) . "'
	", 0, null, __FILE__, __LINE__);
	
	// #### update bid table for new owner
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "project_bids
		SET project_user_id = '".$result_project['transfer_to_userid']."'
		WHERE project_id = '".$result_project['project_id']."'
	", 0, null, __FILE__, __LINE__);
	
	// #### update pmb alert table for new owner
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "pmb_alerts
		SET from_id = '" . $result_project['transfer_to_userid'] . "'
		WHERE project_id = '" . $result_project['project_id'] . "'
			AND from_id = '" . $result_project['transfer_from_userid'] . "'
	", 0, null, __FILE__, __LINE__);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "pmb_alerts
		SET to_id = '" . $result_project['transfer_to_userid'] . "'
		WHERE project_id = '" . $result_project['project_id'] . "'
			AND to_id = '" . $result_project['transfer_from_userid'] . "'
	", 0, null, __FILE__, __LINE__);
	
	// #### select invoice tied to escrow account for old owner
	$sql_invoice = $ilance->db->query("
		SELECT e.invoiceid, e.project_id, i.invoicetype, i.invoiceid, i.projectid FROM
		" . DB_PREFIX . "projects_escrow AS e,
		" . DB_PREFIX . "invoices AS i
		WHERE e.project_id = '" . $res['project_id'] . "'
			AND i.projectid = '" . $res['project_id'] . "'
			AND i.invoicetype = 'escrow'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_invoice) > 0 AND $ilconfig['escrowsystem_enabled'])
	{
		// #### escrow process exists with old project owner and buyer
		$result_invoice = $ilance->db->fetch_array($sql_invoice, DB_ASSOC);
		
		// #### update escrow table for new owner tied to this auction
		$sql_escrow = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "projects_escrow
			WHERE project_user_id = '" . $result_project['transfer_from_userid'] . "'
				AND project_id = '" . $result_project['project_id'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_escrow) > 0)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET project_user_id = '" . $result_project['transfer_to_userid'] . "'
				WHERE project_id = '" . $result_project['project_id'] . "'
			", 0, null, __FILE__, __LINE__);
			
			// update invoice table tied old buyer invoice to new buyer
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "invoices
				SET user_id = '" . $result_project['transfer_to_userid'] . "'
				WHERE projectid = '" . $result_project['project_id'] . "'
					AND invoicetype = 'escrow'
			", 0, null, __FILE__, __LINE__);
		}
	}
	
	// #### budget overview
	$budget = $ilance->auction->construct_budget_overview($result_project['cid'], $result_project['filtered_budgetid']);                        
	
	$ilance->email = construct_dm_object('email', $ilance);	
	$existing = array(
		'{{transfer_hash}}' => $newmd5hash,
		'{{transfer_to_username}}' => ucfirst($result_newowner['username']),
		'{{transfer_to_email}}' => $result_newowner['email'],
		'{{transfer_from_username}}' => ucfirst($result_oldowner['username']),
		'{{transfer_from_email}}' => $result_oldowner['email'],
		'{{rfp_title}}' => $result_project['project_title'],
		'{{status}}' => print_auction_status($result_project['status']),
		'{{bids}}' => $result_project['bids'],
		'{{closing_date}}' => print_date($result_project['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
		'{{description}}' => short_string(stripslashes($result_project['description']),150),
		'{{project_id}}' => $result_project['project_id'],
		'{{budget}}' => $budget
	);
	// #### email new owner
	$ilance->email->mail = $result_newowner['email'];
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
	$ilance->email->get('rfp_takeover_new_buyer');		
	$ilance->email->set($existing);
	$ilance->email->send();
	
	// #### email old owner
	$ilance->email->mail = $result_oldowner['email'];
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];		
	$ilance->email->get('rfp_takeover_old_buyer');		
	$ilance->email->set($existing);		
	$ilance->email->send();
	
	// email admin
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();		
	$ilance->email->get('rfp_takeover_admin');		
	$ilance->email->set($existing);		
	$ilance->email->send();
	print_notice($phrase['_rfp_takeover_request_was_accepted_and_transferred'], '<p>' . $phrase['_you_have_successfully_accepted_this_rfp_takeover_request_and_nothing_more_is_required_by_you'] . '</p><p>' . $phrase['_you_will_now_be_able_to_review_this_new_rfp_from_your_buying_activity_menu'] . '</p><p>' . $phrase['_please_contact_customer_support_for_more_information'] . '</p>', HTTP_SERVER . $ilpage['main'], $phrase['_main_menu']);
	exit();
}
// #### EXTERNAL RFP TAKEOVER REJECT REQUEST ###################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'rfp-reject' AND isset($ilance->GPC['xcode']) AND $ilance->GPC['xcode'] != "")
{
	$area_title = $phrase['_rfp_takeover_rejection_request_in_progress'].' . .';
	$page_title = SITE_NAME . ' - ' . $phrase['_rfp_takeover_rejection_request_in_progress'].' . .';
        
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        $ilance->auction = construct_object('api.auction');
	// rfp takeover acceptance
	$sql = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "projects
                WHERE transfer_code = '" . $ilance->db->escape_string($ilance->GPC['xcode']) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) == 0)
        {
		$area_title = $phrase['_invalid_rfp_transfer_code'];
		$page_title = SITE_NAME . ' - ' . $phrase['_invalid_rfp_transfer_code'];
                print_notice($phrase['_invalid_rfp_transfer_code'], $phrase['_were_sorry_there_was_a_problem_with']."<br /><br />".$phrase['_If_you_are_clicking_a_link_within_your_email_client']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
		exit();
	}
	
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	
	// new buyer information
	$sqlnew = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $res['transfer_to_userid'] . "'
			AND status = 'active'
	", 0, null, __FILE__, __LINE__);
	
	$resuser = $ilance->db->fetch_array($sqlnew, DB_ASSOC);
	
	// old buyer information
	$sqlold = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $res['transfer_from_userid'] . "'
			AND status = 'active'
	", 0, null, __FILE__, __LINE__);
	$resolduser = $ilance->db->fetch_array($sqlold, DB_ASSOC);
	
	// accept rfp transfer takeover
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects
		SET transfer_status = 'rejected',
		transfer_code = ''
		WHERE project_id = '" . $res['project_id'] . "'
			AND transfer_code = '" . $ilance->db->escape_string($ilance->GPC['xcode']) . "'
	", 0, null, __FILE__, __LINE__);
	// budget                
	$budget = $ilance->auction->construct_budget_overview($res['cid'], $res['filtered_budgetid']);
	$ilance->email = construct_dm_object('email', $ilance);
	
	// email new owner
	$ilance->email->mail = $resuser['email'];
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
	$ilance->email->get('rfp_takeover_rejected_new_buyer');		
	$ilance->email->set(array(
		'{{transfer_hash}}' => $newmd5hash,
		'{{transfer_to_username}}' => ucfirst($resuser['username']),
		'{{transfer_to_email}}' => $resuser['email'],
		'{{transfer_from_username}}' => ucfirst($resolduser['username']),
		'{{transfer_from_email}}' => $resolduser['email'],
		'{{rfp_title}}' => $res['project_title'],
		'{{status}}' => print_auction_status($res['status']),
		'{{bids}}' => $res['bids'],
		'{{closing_date}}' => print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
		'{{description}}' => short_string(stripslashes($res['description']),150),
		'{{project_id}}' => $res['project_id'],
		'{{budget}}' => $budget
	));	
	$ilance->email->send();
	// email old owner
	$ilance->email->mail = $resolduser['email'];
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
	$ilance->email->get('rfp_takeover_rejected_old_buyer');		
	$ilance->email->set(array(
		'{{transfer_hash}}' => $newmd5hash,
		'{{transfer_to_username}}' => ucfirst($resuser['username']),
		'{{transfer_to_email}}' => $resuser['email'],
		'{{transfer_from_username}}' => ucfirst($resolduser['username']),
		'{{transfer_from_email}}' => $resolduser['email'],
		'{{rfp_title}}' => $res['project_title'],
		'{{status}}' => print_auction_status($res['status']),
		'{{bids}}' => $res['bids'],
		'{{closing_date}}' => print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
		'{{description}}' => short_string(stripslashes($res['description']),150),
		'{{project_id}}' => $res['project_id'],
		'{{budget}}' => $budget
	));
	$ilance->email->send();
	
	// email admin
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	$ilance->email->get('rfp_takeover_rejected_admin');		
	$ilance->email->set(array(
		'{{transfer_hash}}' => $newmd5hash,
		'{{transfer_to_username}}' => ucfirst($resuser['username']),
		'{{transfer_to_email}}' => $resuser['email'],
		'{{transfer_from_username}}' => ucfirst($resolduser['username']),
		'{{transfer_from_email}}' => $resolduser['email'],
		'{{rfp_title}}' => $res['project_title'],
		'{{status}}' => print_auction_status($res['status']),
		'{{bids}}' => $res['bids'],
		'{{closing_date}}' => print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
		'{{description}}' => short_string(stripslashes($res['description']),150),
		'{{project_id}}' => $res['project_id'],
		'{{budget}}' => $budget
	));
	$ilance->email->send();
	
	$area_title = $phrase['_rfp_takeover_request_rejected'];
	$page_title = SITE_NAME . ' - ' . $phrase['_rfp_takeover_request_rejected'];
	print_notice($phrase['_rfp_takeover_request_was_rejected'], '<p>' . $phrase['_you_have_successfully_rejected_this_rfp_takeover_request_and_nothing_more_is_required_by_you'] . '</p><p>' . $phrase['_an_rfp_takeover_request_allows_project_managers_and_helpful_moderators_better_serve_our_customers_by_helping'] . '</p><p>' . $phrase['_please_contact_customer_support_for_more_information'] . '</p>', HTTP_SERVER . $ilpage['main'], $phrase['_main_menu']);
	exit();
}
// #### INSERT PUBLIC MESSAGE ##################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'insertmessage')
{
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
	if (empty($ilance->GPC['message']))
	{
		print_notice($phrase['_message_cannot_be_empty'], $phrase['_please_retry_your_action'], 'javascript: history.go(-1)', $phrase['_retry']);
		exit();
	}
        
	insert_public_message(intval($ilance->GPC['pid']), intval($ilance->GPC['buyerid']), $_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['username'], $ilance->GPC['message'], $visible = '1');
        
	refresh($ilpage['rfp'] . '?id='.intval($ilance->GPC['pid']) . '#messages');
	exit();
}
// #### REMOVE PUBLIC MESSAGE ##################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'removemessage' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
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
        
        // check for seo...
	refresh($ilpage['rfp'] . '?id=' . intval($ilance->GPC['pid']) . '#messages');
	exit();
}
// #### SUBMIT BID PROPOSAL SERVICE AUCTION ####################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-bid-submit' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'service-bid')
{
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();	
	}
	
	$ilance->GPC['paymethod'] = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : '';
	$ilance->GPC['bidstate'] = isset($ilance->GPC['bidstate']) ? $ilance->GPC['bidstate'] : '';
	$ilance->GPC['bidfieldanswers'] = isset($ilance->GPC['custom']) ? $ilance->GPC['custom'] : '';	
	$ilance->GPC['lowbidnotify'] = isset($ilance->GPC['lowbidnotify']) ? intval($ilance->GPC['lowbidnotify']) : 0;
	$ilance->GPC['lasthournotify'] = isset($ilance->GPC['lasthournotify']) ? intval($ilance->GPC['lasthournotify']) : 0;
	$ilance->GPC['subscribed'] = isset($ilance->GPC['subscribed']) ? intval($ilance->GPC['subscribed']) : 0;
	
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_service = construct_object('api.bid_service');
	$ilance->bid_service->placebid($_SESSION['ilancedata']['user']['userid'], $ilance->GPC['proposal'], $ilance->GPC['lowbidnotify'], $ilance->GPC['lasthournotify'], $ilance->GPC['subscribed'], intval($ilance->GPC['id']), intval($ilance->GPC['project_user_id']), $ilance->GPC['bidamount'], intval($ilance->GPC['estimate_days']), $ilance->GPC['bidstate'], $ilance->GPC['bidamounttype'], '', $ilance->GPC['bidfieldanswers'], $ilance->GPC['paymethod'], true);
}
// #### SUBMIT BID FOR PRODUCT AUCTION #########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-bid-submit' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'product-bid')
{
        // #### define top header nav ##########################################
        $topnavlink = array(
                'main_buying'
        );
        
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}
	//new change
	//error_reporting(E_ALL);
	//print_r($_POST);
		
		
	/*	echo "
			SELECT *
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . $ilance->GPC['id'] . "'
				AND bidamount = '" . $ilance->GPC['bidamount'] . "'
	";*/
		
		
		    $mytest = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . $ilance->GPC['id'] . "'
				AND bidamount = '" . $ilance->GPC['bidamount'] . "'
	", 0, null, __FILE__, __LINE__);
	
			/*Tamil 3345 Starts*/
				
				$sql_bid_validate = $ilance->db->query("
				SELECT startprice,currentprice,bids
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . $ilance->GPC['id'] . "'
				
				", 0, null, __FILE__, __LINE__);
				
				$res_bid_validate = $ilance->db->fetch_array($sql_bid_validate);
				$current_bid_amount = $res_bid_validate['currentprice'];
				$start_bid_amount=$res_bid_validate['startprice'];
				$bid_count=$res_bid_validate['bids'];
				
				$sqlincrement_validate = $ilance->db->query("
				SELECT amount
				FROM " . DB_PREFIX . "increments
				WHERE increment_from <='" . $current_bid_amount . "'
				AND increment_to  >=  '" . $current_bid_amount . "'
				ORDER BY incrementid ASC
				", 0, null, __FILE__, __LINE__);
				
				$resincrement_validate = $ilance->db->fetch_array($sqlincrement_validate);
				$increment_range_value=$resincrement_validate['amount'];
				$incremented_bid_amount = $increment_range_value  + $current_bid_amount;
				
	
				//|| ($ilance->GPC['bidamount'] < $incremented_bid_amount
                if($ilance->db->num_rows($mytest) > 0)
                {
				
				//echo 'rr';
				//print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
			//exit();
			print_notice($phrase['_sorry '], $phrase['_this_amount_is_already_bid_by_another_bidderso_you_can_change_your_bidamount_greater_than_this_amount'], "merch.php?id=".$ilance->GPC['id']."", $phrase['_back']);
				//print_notice('Sorry', 'Your bidamount is('.$ilance->GPC['bidamount'].').This amount is already bid by another bidder.So you can change your bidamount greater than this amount', "merch.php?id=".$ilance->GPC['id']."", $phrase['_back']);
				
				exit();   
				
				
				}
				
				/*Tamil 3345 Ends*/
				else
				{
	//exit();
	
	//echo 'dfdf';
	// #### lets bid! ######################################################
	$ilance->auction = construct_object('api.auction');
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_product = construct_object('api.bid_product');
	//$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true);
	
	$ilance->GPC['highbidnotify'] = isset($ilance->GPC['highbidnotify']) ? intval($ilance->GPC['highbidnotify']) : 0;
	$ilance->GPC['lasthournotify'] = isset($ilance->GPC['lasthournotify']) ? intval($ilance->GPC['lasthournotify']) : 0;
	$ilance->GPC['subscribed'] = isset($ilance->GPC['subscribed']) ? intval($ilance->GPC['subscribed']) : 0;
	$ilance->GPC['shipperid'] = isset($ilance->GPC['shipperid']) ? intval($ilance->GPC['shipperid']) : 0;
	$fetched_minimum_bid=$ilance->bid->fetch_minimum_bid($ilance->bid->fetch_highest_bid($ilance->GPC['id']));
	if($ilance->GPC['bidamount']<$incremented_bid_amount and $ilance->GPC['bidamount']>$start_bid_amount and $bid_count>0 )	
	{
		$area_title = $phrase['_bid_preview_denied_bad_bid_minimum_entered'];
		$page_title = SITE_NAME . ' - ' . $phrase['_bid_preview_denied_bad_bid_minimum_entered'];
		print_notice($phrase['_bid_minimum_warning'], $phrase['_were_sorry_in_order_to_place_a_bid_on_this_auction_your_bid_amount_must_be_the_same']."<br />", 'javascript:history.back(1);', $phrase['_back']);
		exit();
	}
	$buyershipcost = array('total' => 0);
	
	$ilance->GPC['buyershipcost'] = $buyershipcost['total'];
	
	$ilance->bid_product->placebid($ilance->GPC['highbidnotify'], $ilance->GPC['lasthournotify'], $ilance->GPC['subscribed'], intval($ilance->GPC['id']), intval($ilance->GPC['project_user_id']), $ilance->GPC['bidamount'], $ilance->GPC['qty'], $_SESSION['ilancedata']['user']['userid'], $ilconfig['productbid_enableproxybid'], $ilance->GPC['minimum'], $ilance->auction->fetch_reserve_price(intval($ilance->GPC['id'])), true, $ilance->GPC['buyershipcost'], $ilance->GPC['shipperid']);
	
	}
}
// #### SERVICE AUCTION CATEGORY MAP ###########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'listings')
{
        $show['widescreen'] = true;
        
	$area_title = $phrase['_find_jobs_work_and_services'];
	$page_title = SITE_NAME . ' - ' . $phrase['_find_jobs_work_and_services'] . ' - ' . $phrase['_viewing_all_categories'];
        
	// #### require mootools ###############################################
	if (defined('SUB_FOLDER_ROOT') AND SUB_FOLDER_ROOT != '')
	{
		$jsurl = SUB_FOLDER_ROOT . DIR_FUNCT_NAME;
	}
	else
	{
		$jsurl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME;
	}
	$headinclude .= '<script language="javascript" type="text/javascript" src="' . $jsurl . '/javascript/functions_mootools.js"></script>';
	unset($jsurl);
	
        // #### define top header nav ##########################################
        $topnavlink = array(
                'main_categories'
        );
        $ilance->categories_pulldown = construct_object('api.categories_pulldown');
	$ilance->subscription = construct_object('api.subscription');
	$ilance->auction = construct_object('api.auction');
	$ilance->bid = construct_object('api.bid');
	$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1, 1);
	
	$cid = !empty($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	$seoservicecategories = print_seo_url($ilconfig['categoryidentifier']);
	
	$search_category_pulldown = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'service', 'cid', $_SESSION['ilancedata']['user']['slng'], $ilance->categories->cats, true);
        
        if ($cid > 0)
        {
                $navcrumb = array();
                $ilance->categories->breadcrumb($cid, 'servicecatmap', $_SESSION['ilancedata']['user']['slng']);
                
                // update category view count
                add_category_viewcount($cid);
        }
        else
        {
                $navurl = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . $seoservicecategories : HTTP_SERVER . $ilpage['main'] . '?cmd=categories';
                
		$navcrumb = array();
                $navcrumb["$navurl"] = $phrase['_categories'];
                $navcrumb[""] = $phrase['_services'];
                unset($navurl);
        }
        
        $cathtml = $ilance->categories->recursive($cid, 'servicecatmap', $_SESSION['ilancedata']['user']['slng'], 0, '', $ilconfig['globalauctionsettings_seourls']);
        
        $recursivecategory = $auctioncount = '';
        $show['canpost'] = false;
        
        if (!empty($cathtml))
        {
                $metatitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
                $metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
                $metakeywords = $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
                
                if (empty($metadescription))
                {
                        $metadescription = $phrase['_find_jobs_services_work_and_more_in'] . ' ' . $metatitle;
                }
                
                $area_title = $phrase['_categories'] . ' - ' . $metatitle;
                $page_title = SITE_NAME . ' - ' . $metadescription;
        
                $count = $ilance->categories->auctioncount('service', $cid);
                $auctioncount = ($ilconfig['globalfilters_enablecategorycount']) ? '<span class="gray">(' . $count . ')</span>' : '';
		unset($count);
		
                if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', $cid))
                {
                        $show['canpost'] = true;
                }
                else 
                {
                        $show['categorycolumn'] = true;
                }		
        }
                
	$categoryresults = $ilance->categories_parser->print_subcategory_columns(4, 'service', 1, $_SESSION['ilancedata']['user']['slng'], $cid, '', $ilconfig['globalfilters_enablecategorycount'], 1, 'font-weight: bold;', 'font-weight: normal;', $ilconfig['globalauctionsettings_catmapdepth']);
        
	$category = $description = '';
        $text = $phrase['_browse_service_auctions_via_marketplace_categories'];
	
	if ($cid > 0)
	{
		$category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
                $description = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
                $text = $phrase['_categories_within'] . ' ' . $category;
	}
        
        $recursivecategory = $ilconfig['globalauctionsettings_seourls']
		? '<div style="padding-left:6px; padding-top:6px"><span class="blue"><a href="' . construct_seo_url('servicecatplain', $cid, $auctionid = 0, $category, $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . '"><strong>' . $phrase['_see_all_listings_in_this_category'] . '</strong></a></span>:&nbsp;<span><strong>' . $category . '</strong></span>&nbsp;' . $auctioncount . '</div>'
		: '<div style="padding-left:6px; padding-top:6px"><span class="blue"><a href="' . $ilpage['search'] . '?mode=service&amp;cid=' . $cid . '"><strong>' . $phrase['_see_all_listings_in_this_category'] . '</strong></a></span>:&nbsp;<span><strong>' . $category . '</strong></span>&nbsp;' . $auctioncount . '</div>';
		
        $latestserviceauctions = array();
        $show['searchbar'] = false;
        
        if (empty($recursivecategory) OR $cid == 0)
        {
                $show['searchbar'] = true;
                $recursivecategory = '<div class="gray" style="padding-left:5px; padding-top:6px; font-size:16px;"><strong>' . $phrase['_viewing_all_categories'] . '</strong></div>';
        }
	else
	{
		$latestserviceauctions = $ilance->auction->fetch_latest_auctions('service', 5, 1, $cid);
	}
        
        // if we have no children, redirect user to the appropriate result listings pages for this category
        if (isset($cid) AND $cid > 0 AND $ilance->categories->fetch_children_ids($cid, 'service') == '')
        {
                $url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('servicecatplain', $cid, $auctionid = 0, $category, $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : $ilpage['search'] . '?mode=service&cid=' . $cid;
                header('Location: ' . $url);
                exit();
        }
        
        // #### SEO related ####################################################
        $seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
        $seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
        $seolistings = print_seo_url($ilconfig['listingsidentifier']);
        $seocategories = print_seo_url($ilconfig['categoryidentifier']);
        
        $pprint_array = array('seoservicecategories','seoproductcategories','seolistings','seocategories','description','text','categorypulldown','recursivecategory','category','cid','php_self','categoryresults','three_column_subcategory_results','category','number','prevnext','input_style','keywords','search_country_pulldown','search_jobtype_pulldown','five_last_keywords_buynow','five_last_keywords_projects','five_last_keywords_providers','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','search_category_pulldown','input_style','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                
	$ilance->template->fetch('main', 'rfp_listings.html');
        $ilance->template->parse_loop('main', 'latestserviceauctions');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### SERVICE ACTION CATEGORY LISTINGS ######################################
else if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0 AND empty($ilance->GPC['cmd']))
{
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        // update category view count
        add_category_viewcount(intval($ilance->GPC['cid']));
        
        $urlbit = print_hidden_fields($string = true, $excluded = array());
        
        $ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = false);
        if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'service', intval($ilance->GPC['cid'])))
        {
                header('Location: ' . $ilpage['search'] . '?mode=service' . $urlbit);
                exit();
        }
        
        $urlbit = print_hidden_fields($string = true, $excluded = array('cid'));
        header('Location: ' . $ilpage['rfp'] . '?cmd=listings&cid=' . intval($ilance->GPC['cid']) . $urlbit);
        exit();
}
// #### ADD ITEMS FOR COMPARE VIEW #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'auctioncmd')
{
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
        $show['widescreen'] = false;
	
	// #### empty inline cookie ############################################
	set_cookie('inlineservice', '', false);
	set_cookie('inlineproduct', '', false);
	set_cookie('inlineexperts', '', false);
	
	// #### require backend ################################################
	$ilance->auction = construct_object('api.auction');
	$ilance->auction_expiry = construct_object('api.auction_expiry');
	
	// #### COMPARING SEARCH RESULTS #######################################
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'compare')
	{
		// start our compare engine
		$area_title = $phrase['_compare_items'];
		$page_title = SITE_NAME . ' - ' . $phrase['_compare_items'];
		
		$navcrumb = array("$ilpage[rfp]" => $ilcrumbs["$ilpage[compare]"]);
		
		($apihook = $ilance->api('compare_start')) ? eval($apihook) : false;
		
		$ilance->auction = construct_object('api.auction');
		$ilance->bid = construct_object('api.bid');
		
		$ilance->GPC['project_id'] = isset($ilance->GPC['project_id']) ? $ilance->GPC['project_id'] : array();
		$comparecount = count($ilance->GPC['project_id']);
		if (!$comparecount OR $comparecount <= 0)
		{
			print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
			exit();
		}
		
		if ($ilance->GPC['mode'] == 'product')
		{
			// columns we'll be displaying for products
			$columns = array(
				'remove' => '_remove',
				'project_title' => '_item',
				'date_end' => '_time_left',
				'bids' => '_bids',
				//'username' => '_seller',
				'currentprice' => '_price',
			);
		}
		
		else if ($ilance->GPC['mode'] == 'service')
		{
			// columns we'll be displaying for services
			$columns = array(
				'remove' => '_remove',
				'project_title' => '_title',
				'date_end' => '_time_left',
				'bids' => '_bids',
				'username' => '_buyer',
				'currentprice' => '_average_bid',
			);
		}
		
		else if ($ilance->GPC['mode'] == 'experts')
		{
			// columns we'll be displaying for experts
			$columns = array(
				'remove' => '_remove',
				'logo' => '_logo',
				'date_end' => '_time_left',
				'bids' => '_bids',
				'username' => '_expert',
				'currentprice' => '_price',
			);
		}
			
		$ids = array();
		
		foreach ($ilance->GPC['project_id'] AS $projectid)
		{
			$ids[] = intval($projectid);
		}
		
		$class = 'alt1';
		$compare_html = '';
		foreach ($columns AS $column => $phrasetext)
		{
			$width = 150;
			$columns['columntitle'] = $phrase["$phrasetext"];
			
			$compare_html .= ($column == 'remove')
				? '<tr class="alt3"><td width="150" class="alt3">' . $phrase['_select'] . '</td>'
				: '<tr class="alt1"><td width="200" valign="top" class="alt2">' . $columns['columntitle'] . '</td>';
					
			$sql = $ilance->db->query("
				SELECT p.*, u.username, u.country
				FROM " . DB_PREFIX . "projects p 
					LEFT JOIN " . DB_PREFIX . "users u ON u.user_id = p.user_id
					WHERE project_id IN (" . implode(",", $ids) . ")
			");
			while ($item = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($column == 'remove')
				{
					$compare_html .= '<td class="alt3" width="' . $width . '"><input type="checkbox" name="project_id[]" value="' . $item['project_id'] . '" id="' . $ilance->GPC['mode'] . '_' . $item['project_id'] . '" /></td>';				
				}
				else if ($column == 'action')
				{
					$compare_html .= '<td class="' . $class . '" width="' . $width . '"></td>';				
				}
				else if ($column == 'project_title')
				{
					// auction has bold feature?
					if ($item['bold'])
					{
						if ($ilance->GPC['mode'] == 'service')
						{
							$title = '<span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $item['project_id'] . '"><strong>' . stripslashes($item['project_title']) . '</strong></a></span>';
							$sample = '';
							$height = 0;
							$align = 'left';
							if ($ilconfig['globalauctionsettings_seourls'])
							{
								$title = construct_seo_url('serviceauction', 0, $item['project_id'], $item['project_title'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0);
							}
						}
						else if ($ilance->GPC['mode'] == 'product')
						{
							$title = '<span class="blue"><a href="'.$ilpage['merch'] . '?id=' . $item['project_id'].'"><strong>' . stripslashes($item['project_title']) . '</strong></a></span>';
							$sample = print_item_photo($ilpage['merch'] . '?id=' . $item['project_id'], 'thumb', $item['project_id']);
							$height = 25;
							$align = 'center';
							if ($ilconfig['globalauctionsettings_seourls'])
							{
								$title = construct_seo_url('productauction', 0, $item['project_id'], $item['project_title'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0);
							}
						}
					}
					else
					{
						if ($ilance->GPC['mode'] == 'service')
						{
							$title = '<span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $item['project_id'] . '">' . stripslashes($item['project_title']) . '</a></span>';
							$sample = '';
							$height = 0;
							$align = 'left';
							if ($ilconfig['globalauctionsettings_seourls'])
							{
								$title = construct_seo_url('serviceauction', 0, $item['project_id'], $item['project_title'], $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
							}
						}
						else if ($ilance->GPC['mode'] == 'product')
						{
							$title = '<span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $item['project_id'] . '">' . stripslashes($item['project_title']) . '</a></span>';
							$height = 25;
							$align = 'center';
							if ($ilconfig['globalauctionsettings_seourls'])
							{
								$url = construct_seo_url('productauctionplain', 0, $item['project_id'], stripslashes($item['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
								$sample = print_item_photo($url, 'thumb', $item['project_id']);
								$title = construct_seo_url('productauction', 0, $item['project_id'], $item['project_title'], $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
							}
							else
							{
								$sample = print_item_photo($ilpage['merch'] . '?id=' . $item['project_id'], 'thumb', $item['project_id']);
							}
						}
					}
					if ($item['highlite'])
					{
						$class = 'featured_highlight';
					}
					$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top"><div align="' . $align . '">' . $sample . '</div><div style="padding-top:' . $height . 'px"><span class="blue">' . $title . '</span></div></td>';
					$class = 'alt1';
				}
				else if ($column == 'date_end')
				{
					$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top"><strong>' . $ilance->auction->auction_timeleft($item['project_id'], $class, 'left') . '</strong></td>';
				}
				else if ($column == 'bids')
				{
					if ($item['bids'] == 0)
					{
						$bids = '<div class="black">0 ' . $phrase['_bids_lower'] . '</div>';
					}
					else
					{
						$bids = '<div class="black">' . $item['bids'] . ' ' . $phrase['_bids_lower'] . '</div>';
					}
					$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top">' . $bids . '</td>';
				}
				else if ($column == 'username')
				{
					//$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top"><span class="blue">' . print_username($item['user_id'], 'href') . '</span></td>';
				}
				else if ($column == 'currentprice')
				{
					if ($ilance->GPC['mode'] == 'product')
					{
						$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top"><strong>' . $ilance->currency->format($item['currentprice'], $item['currencyid']) . '</strong></td>';
					}
					else if ($ilance->GPC['mode'] == 'service')
					{
						$average = $ilance->bid->fetch_average_bid($item['project_id'], false, $item['bid_details'], false);
						$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top">' . $average . '</td>';
					}
				}
				else
				{
					$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top">' . $item["$column"] . '</td>';				
				}
					
			}
			$compare_html .= '</tr>';
		}
		$comparecount++;
		
		$hidden_input_fields = print_hidden_fields(false, array('page','project_id','rfpcmd'));
		$mode = $ilance->GPC['mode'];
		
		$returnurl = isset($ilance->GPC['returnurl']) ? urldecode($ilance->GPC['returnurl']) : $ilpage['search'];
		
		$pprint_array = array('returnurl','mode','hidden_input_fields','comparecount','compare_html','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('compare_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'search_compare.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'columns');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### ADD ITEMS TO WATCH LIST ########################################
	else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'watchlist')
	{
		if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
		{
			refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
			exit();
		}
		
		$ilance->subscription = construct_object('api.subscription');
		$ilance->watchlist = construct_object('api.watchlist');
			
		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'addtowatchlist') == 'no')
		{
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('addtowatchlist'));
			exit();
		}
		
		$ilance->GPC['project_id'] = isset($ilance->GPC['project_id']) ? $ilance->GPC['project_id'] : array();
		$count = count($ilance->GPC['project_id']);
		if (empty($ilance->GPC['project_id']) OR $count <= 0)
		{
			print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
			exit();
		}
		
		for ($i = 0; $i < $count; $i++)
		{
			$sql_watchlist = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "watchlist
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND watching_project_id = '" . intval($ilance->GPC['project_id'][$i]) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_watchlist) == 0)
			{
				$ilance->watchlist->insert_item($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['project_id'][$i]), 'auction', 'n/a');
			}						
		}
		
		//refresh(HTTP_SERVER . $ilpage['search'].'?q=&mode=product&sort=01');
		refresh(HTTP_SERVER . $ilpage['watchlist']);
		exit();	
	}
	
	// #### INLINE MODERATION TOOLS ########################################
	else
	{
		$area_title = $phrase['_moderation_tools'];
		$page_title = SITE_NAME . ' - ' . $phrase['_moderation_tools'];
		
		$navcrumb = array();
                $navcrumb[""] = $phrase['_moderation_tools'];
		
		// #### hold return url ########################################
		$returnurl = isset($ilance->GPC['returnurl']) ? $ilance->GPC['returnurl'] : '';
		$show['movecategory'] = $show['sendemail'] = false;
		
		// #### flag for delist ########################################
		if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'delist' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							if ($res['status'] == 'open')
							{
								build_category_count($res['cid'], 'subtract', "admin delisting multiple listings from search results: subtracting increment count category id $res[cid]");
							}
							
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET status = 'delisted',
								close_date = '" . DATETIME24H . "'
								WHERE project_id = '" . intval($value) . "'
							", 0, null, __FILE__, __LINE__);
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_listings_were_delisted_closed'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		
		// #### flag listings for featured homepage ####################
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'featured' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET featured = '1',
								featured_date = '" . DATETIME24H . "'
								WHERE project_id = '" . intval($value) . "'
							", 0, null, __FILE__, __LINE__);
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		// #### flag listings for unfeatured homepage ####################
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'unfeatured' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET featured = '0',
								featured_date = '0000-00-00 00:00:00'
								WHERE project_id = '" . intval($value) . "'
							", 0, null, __FILE__, __LINE__);
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		// #### flag listings for highlight background #################
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'highlight' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET highlite = '1'
								WHERE project_id = '" . intval($value) . "'
							", 0, null, __FILE__, __LINE__);
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		
		// #### flag listings for unhighlight background #################
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'unhighlight' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET highlite = '0'
								WHERE project_id = '" . intval($value) . "'
							", 0, null, __FILE__, __LINE__);
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		
		// #### flag listings for bold title ###########################
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'bold' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET bold = '1'
								WHERE project_id = '" . intval($value) . "'
							", 0, null, __FILE__, __LINE__);
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		
		// #### flag listings for unbold title ###########################
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'unbold' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET bold = '0'
								WHERE project_id = '" . intval($value) . "'
							", 0, null, __FILE__, __LINE__);
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		
		// #### flag listings for relist title #########################
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'relist' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							$ilance->auction_expiry->process_auction_relister(intval($value), true);
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		
		// #### flag listings for time extend ##########################
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'extend' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET date_end = DATE_ADD(date_end, INTERVAL 1 DAY)
								WHERE project_id = '" . intval($value) . "'
							", 0, null, __FILE__, __LINE__);
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		
		// #### flag listings for time retract #########################
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'deextend' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET date_end = DATE_SUB(date_end, INTERVAL 1 DAY)
								WHERE project_id = '" . intval($value) . "'
							", 0, null, __FILE__, __LINE__);
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		
		// #### flag listings for sending bulk message to listing owners
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'email' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = $selecteditems = '';
			$show['sendemail'] = true;
			$emailsduplicateprevention = $uids = array();
			
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
						$selecteditems .= "$res[project_title] (#$res[project_id]) : Category ID (#$res[cid])\n";
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							if (!in_array($res['user_id'], $emailsduplicateprevention))
							{
								$uids[] = $res;
								$emailsduplicateprevention[] = $res['user_id'];
							}
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					if (!isset($ilance->GPC['message']) OR empty($ilance->GPC['message']))
					{
						print_notice($phrase['_email_message_cannot_be_blank'], $phrase['_please_compose_a_message_to_sent_to_the_owners_of_selected_listings'], 'javascript:history.back(-1)', $phrase['_try_again']);
						exit();
					}
					
					if (!empty($uids) AND is_array($uids))
					{
						foreach ($uids AS $user_id)
						{
							$message = "
Hello,
*** Please do not respond to this email.  If you have any questions please contact us on our web site.
**********************
Comment
**********************
" . handle_input_keywords($ilance->GPC['message']) . "
**********************
Listing ID Numbers
**********************
$selecteditems
" . SITE_NAME . " Staff,
" . HTTP_SERVER . "
";
							send_email(fetch_user('email', $user_id), 'Notice RE: one or more listings on ' . SITE_NAME, $message, SITE_EMAIL, SITE_NAME . ' Staff', $html = false, 'alert');
						}
					}
					
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		
		// #### flag listings for sending bulk message to listing owners
		else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'movecategory' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$hiddenfields = '';
			$cid = 0;
			$show['movecategory'] = true;
			if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
			{
				if (!isset($ilance->GPC['cid']) OR isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] <= 0)
				{
					print_notice($phrase['_nothing_to_do'], $phrase['_please_select_the_new_category_you_wish_to_move_selected_listings_into'], 'javascript:history.back(-1)', $phrase['_try_again']);
					exit();
				}
			}
			if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
			{
				$ilance->auction = construct_object('api.auction');
				$ilance->auction_post = construct_object('api.auction_post');
				
				foreach ($ilance->GPC['project_id'] AS $value)
				{
					$sql = $ilance->db->query("
						SELECT user_id, cid, status, project_state, project_title, project_id
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($value) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . intval($value) . '" />';
						
						if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
						{
							// #### move category
							move_listing_category_from_to(intval($value), $res['cid'], $ilance->GPC['cid'], $res['project_state'], $res['status'], $res['status']);
							
							// #### setup new category questions
							if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
							{
								$ilance->auction_post->process_custom_questions($ilance->GPC['custom'], intval($value), $res['project_state']);
							}
						}
						
						$res['value'] = $value;
						$returnurlback = urldecode($returnurl);
						$results[] = $res;
					}
				}
				
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					print_notice($phrase['_action_completed'], $phrase['_the_selected_moderation_action_youve_performed_was_completed_successfully'], urldecode($ilance->GPC['returnurl']), $phrase['_search_results']);
					exit();
				}
			}
			else
			{
				print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
				exit();
			}
		}
		else
		{
			print_notice($phrase['_nothing_to_do'], $phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'javascript:history.back(-1)', $phrase['_try_again']);
			exit();
		}
		
		$pprint_array = array('cid','returnurlback','returnurl','hiddenfields','lanceads_folder','profiles_created_count','escrow_deposit_count','sub_payments_count','two_column_category_buyers','two_column_service_categories','two_column_category_products','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
		($apihook = $ilance->api('main_mycp_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'search_moderation.html');
		$ilance->template->parse_loop('main', 'results');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
// #### SEARCH RESULT LISTINGS ACTIONS #########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'expertcmd')
{
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();	
	}
	
	// #### ADD MEMBER TO WATCHLIST FROM SEARCH RESULT LISTING #############
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'watchlist')
	{
		if (!isset($ilance->GPC['vendor_id']))
		{
			$area_title = $phrase['_invalid_vendor_id_warning_menu'];
			$page_title = SITE_NAME . ' - ' . $phrase['_invalid_vendor_id_warning_menu'];
			
			print_notice($phrase['_invalid_vendor_profile_id'], $phrase['_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id'] . '<br /><br />' . $phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
			exit();
		}
		
		// empty inline cookie
		set_cookie('inlineservice', '', false);
		set_cookie('inlineproduct', '', false);
		set_cookie('inlineexperts', '', false);
	
		$ilance->subscription = construct_object('api.subscription');
		$ilance->watchlist = construct_object('api.watchlist');
		
		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'addtowatchlist') == 'no')
		{
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('addtowatchlist'));
			exit();
		}
		
		$count = count($ilance->GPC['vendor_id']);
		for ($i = 0; $i < $count; $i++)
		{
			$sql_watchlist = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "watchlist
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND watching_user_id = '" . intval($ilance->GPC['vendor_id'][$i]) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_watchlist) == 0)
			{
				$ilance->watchlist->insert_item($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['vendor_id'][$i]), 'sprovider', '');
			}						
		}
		
		refresh(HTTP_SERVER . $ilpage['watchlist']);
		exit();	
	}
	// #### INVITE MULTIPLE MEMBERS TO NEW OR EXISTING SERVICE AUCTION #####
	else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'invite')
	{
		$ilance->subscription = construct_object('api.subscription');	
		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'inviteprovider') == 'no')
		{
			$area_title = $phrase['_provider_invitation_denied_upgrade_subscription'];
			$page_title = SITE_NAME . ' - ' . $phrase['_provider_invitation_denied_upgrade_subscription'];
			
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('inviteprovider'));
			exit();
		}
		
		if (!isset($ilance->GPC['vendor_id']) OR isset($ilance->GPC['vendor_id']) AND $ilance->GPC['vendor_id'] <= 0)
		{
			$area_title = $phrase['_invalid_vendor_id_warning_menu'];
			$page_title = SITE_NAME . ' - ' . $phrase['_invalid_vendor_id_warning_menu'];
	
			print_notice($phrase['_invalid_vendor_profile_id'], $phrase['_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
			exit();
		}
		
		// empty inline cookie
		set_cookie('inlineexperts', '', false);
		
		$area_title = $phrase['_inviting_a_provider_to_a_new_or_existing_rfp'];
		$page_title = SITE_NAME . ' - ' . $phrase['_inviting_a_provider_to_a_new_or_existing_rfp'];
	
		$returnurl = isset($ilance->GPC['returnurl']) ? $ilance->GPC['returnurl'] : '';
		$returnurlback = urldecode($returnurl);
		$provider = $hidden_invitations = '';
		$count = count($ilance->GPC['vendor_id']);
		
		if ($count > 1)
		{
			for ($i = 0; $i < $count; $i++)
			{
				$sql_vendor = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($ilance->GPC['vendor_id'][$i]) . "'
						AND status = 'active'
						AND user_id != '" . $_SESSION['ilancedata']['user']['userid'] . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_vendor) > 0)
				{
					$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
					$provider .= '<span class="black"><strong>' . fetch_user('username', intval($ilance->GPC['vendor_id'][$i])) . '</strong></span>, ';
					$hidden_invitations .= '<input type="hidden" name="invitationid[]" value="' . $res_vendor['user_id'] . '" />';
				}
			}
			$provider = mb_substr($provider, 0, -2);
		}
		else
		{
			// make sure vendors being invted are active
			$sql_vendor = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($ilance->GPC['vendor_id'][0]) . "'
					AND status = 'active'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_vendor) > 0)
			{
				$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
				$provider .= '<span class="black"><strong>' . fetch_user('username', intval($ilance->GPC['vendor_id'][0])) . '</strong></span>';
				$hidden_invitations .= '<input type="hidden" name="invitationid[]" value="' . $res_vendor['user_id'] . '" />';
			}
		}
	
		$invite_pulldown = '<select name="project_id" style="font-family: verdana">';
		$invite_pulldown .= '<optgroup label="' . $phrase['_service_auction'] . '">';
		$invite_pulldown .= '<option value="">' . $phrase['_none'] . '</option>';
		
		$sql_projects = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "projects
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
				AND status = 'open'
				AND project_state = 'service'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_projects) > 0)
		{
			$show['norfps'] = false;
			while ($res = $ilance->db->fetch_array($sql_projects, DB_ASSOC))
			{
				$invite_pulldown .= '<option value="' . $res['project_id'] . '">#' . $res['project_id'] . ': ' . short_string(stripslashes($res['project_title']), 35) . ' (' . $phrase['_bids'] . ': ' . $res['bids'] . ') (' . $phrase['_ends'] . ': ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . ')</option>';
			}
		}
		else
		{
			$show['norfps'] = true;
			$invite_pulldown .= '<option value="">' . $phrase['_no_rfps_available'] . '</option>';
		}
		$invite_pulldown .= '</optgroup>';
		$invite_pulldown .= '</select>';
		
		$navcrumb = array();
		$navcrumb[""] = $phrase['_invite_to_bid'];
		
		$pprint_array = array('returnurlback','hidden_invitations','invite_pulldown','provider','project_user_id','cid','currency_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','project_title','project_id','remote_addr','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'rfp_invitetobid.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();	
	}
}
// #### INVITE SINGLE MEMBERS TO NEW OR EXISTING SERVICE AUCTION ###############
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'rfp-invitation' AND isset($ilance->GPC['id']))
{
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
        if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] <= 0)
	{
		refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}
	
	$area_title = $phrase['_inviting_a_provider_to_a_new_or_existing_rfp'];
	$page_title = SITE_NAME . ' - ' . $phrase['_inviting_a_provider_to_a_new_or_existing_rfp'];
	if ($ilance->GPC['id'] == $_SESSION['ilancedata']['user']['userid'])
	{
		$area_title = $phrase['_access_denied'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
		
		print_notice($phrase['_cannot_invite_yourself'], $phrase['_please_retry_your_action'], "javascript: history.go(-1)", $phrase['_return_to_the_previous_menu']);
		exit();
	}
	
	// empty inline cookie
	set_cookie('inlineexperts', '', false);
	
	$sql_vendor = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . intval($ilance->GPC['id']) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_vendor) == 0)
	{
		$area_title = $phrase['_invalid_vendor_id_warning_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_invalid_vendor_id_warning_menu'];
		print_notice($phrase['_invalid_vendor_profile_id'], $phrase['_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['main'], $phrase['_main_menu']);
		exit();
	}
		
	$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
	$provider = print_username(intval($ilance->GPC['id']), 'href');
	$invitationid = $res_vendor['user_id'];
	$hidden_invitations = '<input type="hidden" name="invitationid[]" value="'.intval($ilance->GPC['id']).'" />';
	$invite_pulldown = '<select name="project_id" style="font-family: verdana">';
	$invite_pulldown .= '<option value="">' . $phrase['_select_rfp'] . ':</option>';
	
	$sql_projects = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "projects
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND project_state = 'service'
			AND status = 'open'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_projects) > 0)
	{
		$show['norfps'] = false;
		while ($res = $ilance->db->fetch_array($sql_projects))
		{
			$invite_pulldown .= '<option value="' . $res['project_id'] . '">#' . $res['project_id'] . ': ' . short_string(stripslashes($res['project_title']), 35) . ' (' . $phrase['_bids'] . ': ' . $res['bids'] . ') (' . $phrase['_ends'] . ': ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . ')</option>';
		}
	}
	else
	{
		$show['norfps'] = true;
		$invite_pulldown .= '<option value="">' . $phrase['_no_rfps_available'] . '</option>';
	}
	$invite_pulldown .= '</select>';
	$navcrumb = array();                                
	$navcrumb[""] = $phrase['_invite_to_bid'];
	
	$pprint_array = array('invitationid','hidden_invitations','invite_pulldown','provider','project_user_id','cid','currency_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','current_proposal','current_estimate_days','delivery_pulldown','currency','input_style','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'rfp_invitetobid.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### PREVIEW BID FOR PRODUCT AUCTION ########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-bid-preview' AND isset($ilance->GPC['minimum']) AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'product-bid')
{
     //confirm bid image change on apr04
        
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
			    while ($rowt = $ilance->db->fetch_array($slideq)) 
				{
				
					if(($rowt['norder']) == 1)
					{
						print_notice($phrase['_information'], $phrase['_we_are_sorry__you_cannot_bid/buy_this_item']."<br />", 'javascript:history.back(1);', $phrase['_back']);
						exit();
					}
					$ilance->GPC['qty'] = isset($ilance->GPC['qty']) ? intval($ilance->GPC['qty']) : 0;
					if ($rowt['buynow_qty'] < $ilance->GPC['qty'])
					{
						print_notice($phrase['_bid_minimum_warning'], $phrase['_were_sorry_in_order_to_place_a_bid_on_this_auction_your_quantity_must_be_the_same_as_available_or_lower']."<br />", 'javascript:history.back(1);', $phrase['_back']);
						exit();
					}
					$profile_slidq = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?id=' . $rowt['filehash'];
					$profile_slidqft = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'image/250/400/' . $rowt['filename'] ;
					 $profile_sl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?id=' . $rowt['filehash'] .'';
			
					$titlemq = $rowt['filename'];
					$title[] = $titlemq;
					$profile_slides[]= $profile_slidqft;
					$kk[] =$profile_sl;
		
					if($profile_slides[$k] == $profile_slides['0'] )
					{
							 //echo $profile_slides['0'];	
							$newhead = '<a id="thumb1" href="'.$kk[$k].'" class="highslide" onclick="return hs.expand(this)" ><img class="rounded" alt="" src="'.$profile_slides['0'].'" /></a><div class="highslide-caption" align="center"></div>'	 ;
					}
					 if($l%6==0)
					 {
					 $newthumb.='</tr><tr>';
					 }
					$newthumb.= '<td><a id="thumb1" href="'.$kk[$k].'" class="highslide" onclick="return hs.expand(this)">
						<img  class="rounded" src="' .HTTPS_SERVER. $ilpage['attachment'] . '?cmd=thumb&bigimge=1&subcmd=results&id=' . $rowt['filehash'] .'&w=170&h=140"/></a><div class="highslide-caption" align="center">
					 
					</div></td>';
			
					$myslide[]= $newheadthumb;
					$i++;
					$k++;
					$l++;
			    }
				$newthumb.= '</table>';
				
				  if (!empty($_SESSION['ilancedata']['user']['userid']))
                   {
				   
					 $ilance->bid = construct_object('api.bid');
                     $ilance->bid_proxy = construct_object('api.bid_proxy');
               
                      $pbid = $ilance->currency->format($ilance->bid_proxy->fetch_user_proxy_bid($ilance->GPC['id'], $_SESSION['ilancedata']['user']['userid']));
                   }
			
		        }
				else
				{
				 $uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						
					    $newhead ='<center><img src="images/gc/nophoto.gif" style="padding: 10px;" ></center>';
				}
  
            
   //end confirm bid image
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	{
		//refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['rfp'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
		exit();
	}
	
	$ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = false);
	$ilance->auction = construct_object('api.auction');
	$ilance->subscription = construct_object('api.subscription');
	
	if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'productbid') == 'no')
	{
		$area_title = $phrase['_bid_preview_denied_upgrade_subscription'];
		$page_title = SITE_NAME . ' - ' . $phrase['_bid_preview_denied_upgrade_subscription'];
		
		print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('productbid'));
		exit();	
	}
	
	
	$id = intval($ilance->GPC['id']);
	$ilance->bid = construct_object('api.bid');
	$ilance->bid->bid_filter_checkup($id);
	
	$bid_limit_per_day = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday'); 
	$bid_limit_per_month = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitpermonth');           
	$bid_per_day = fetch_user_bidcount_per('day', $_SESSION['ilancedata']['user']['userid']);
	$bid_per_month = fetch_user_bidcount_per('month', $_SESSION['ilancedata']['user']['userid']);					
			
	if ($bid_per_day > $bid_limit_per_day OR $bid_per_day == $bid_limit_per_day OR $bid_per_month > $bid_limit_per_month OR $bid_per_month == $bid_limit_per_month)
	{
		$area_title = $phrase['_access_to_bid_is_denied'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_to_bid_is_denied'];
			
		print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('bidlimitperday'));
		exit();  
	}
	
	// avoid bid minimum manipulations
	$sql_startprice = $ilance->db->query("
		SELECT startprice, currentprice
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($id) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_startprice) > 0)
	{
		$res = $ilance->db->fetch_array($sql_startprice, DB_ASSOC);
		//suku
		$project_current_bid_formatted=$ilance->currency->format($res['currentprice'],0);
		$ilance->GPC['minimum'] = ($ilance->GPC['minimum'] < $res['startprice']) ? $res['startprice'] : $ilance->GPC['minimum'];
		if ($res['currentprice'] > $ilance->GPC['minimum'])
		{
			$ilance->GPC['minimum'] = $res['currentprice'];   
		}
		
		
	}
	//nov21 for bug id 1005
	$string= explode('.',$ilance->GPC['bidamount']);
    $ilance->GPC['bidamount']= preg_replace('/[^0-9\-]/','', $string[0]);
	$string[1]=isset($string[1])?$string[1]:'00';
	$ilance->GPC['bidamount'] = $ilance->GPC['bidamount'].'.'.$string[1];
	
	if ($ilance->GPC['bidamount'] >= $ilance->GPC['minimum'])  
	{
		$area_title = $phrase['_previewing_bid_proposal'];
		$page_title = SITE_NAME . ' - ' . $phrase['_previewing_bid_proposal'];
	}
	else
	{
		$area_title = $phrase['_bid_preview_denied_bad_bid_minimum_entered'];
		$page_title = SITE_NAME . ' - ' . $phrase['_bid_preview_denied_bad_bid_minimum_entered'];
		
		print_notice($phrase['_bid_minimum_warning'], $phrase['_were_sorry_in_order_to_place_a_bid_on_this_auction_your_bid_amount_must_be_the_same']."<br />", 'javascript:history.back(1);', $phrase['_back']);
		exit();
	}
	 
	
	$sql2="SELECT sum(bidamount) as allopenbids  FROM " . DB_PREFIX . "project_bids WHERE  user_id='".$_SESSION['ilancedata']['user']['userid']."' and bidstatus='placed'";
	$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($res2)>0)
	{
		while($line2=$ilance->db->fetch_array($res2))
		{
		$bidlimit=fetch_user('bidlimit',$_SESSION['ilancedata']['user']['userid']);
		if($bidlimit>0)
		{
			if(($line2['allopenbids']+$ilance->GPC['bidamount'])>$bidlimit)
			{
			print_notice($phrase['_bid_minimum_warning'], $phrase['_you_have_exceeded_the_bid_limit_please_call_Tel_or_click_here_to_contact_us']."<br />", 'javascript:history.back(1);', $phrase['_back']);
		exit();
		}
		}
		}
	}			
	$state = 'product';
 
	//nov21 for bug id 1005
	$current_bidamount =  sprintf("%01.2f",$ilance->GPC['bidamount']);
	$current_bidamountformatted = $ilance->currency->format($current_bidamount );
 
	// category details
	$sql_rfp = $ilance->db->query("
		SELECT p.user_id,p.filtered_auctiontype,p.cid,p.filtered_budgetid,p.project_state,  p.project_title,p.project_id,p.coin_series_denomination_no,p.coin_series_denomination_no,p.coin_series_unique_no,d.denomination_long,s.coin_series_name 
		FROM " . DB_PREFIX . "projects p
		left join " . DB_PREFIX . "catalog_second_level s on p.coin_series_unique_no=s.coin_series_unique_no
		left join " . DB_PREFIX . "catalog_toplevel d on p.coin_series_denomination_no=d.denomination_unique_no
		WHERE p.project_id = '" . intval($id) . "'	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_rfp) > 0)
	{
		$res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC);
		
		if ($res_rfp['user_id'] == $_SESSION['ilancedata']['user']['userid'])
		{
			refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
			exit();
		}
		
		$auctiontype = $res_rfp['filtered_auctiontype'];                                
		$budget = $ilance->auction->construct_budget_overview($res_rfp['cid'], $res_rfp['filtered_budgetid']);
		$title = stripslashes($res_rfp['project_title']);
		 
		
			$show['nourlbit'] = true;
			$navcrumb = array();
			$project_user_id = $res_rfp['user_id'];
			$navcrumb=array();
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$nav_url=HTTP_SERVER .'Denomination/'.$res_rfp['coin_series_denomination_no'].'/'.construct_seo_url_name($res_rfp['denomination_long']);
			}
			else
			{
				$nav_url=$ilpage['denomination'].'?denomination='.$res_rfp['coin_series_denomination_no'];
			}	
			$navcrumb[$nav_url] = $res_rfp['denomination_long'];
			$navcrumb["Series/".$res_rfp['coin_series_unique_no']."/".construct_seo_url_name($res_rfp['coin_series_name']).""] = $res_rfp['coin_series_name'];			
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$nav_url=HTTP_SERVER .'Coin/'.$id.'/'.construct_seo_url_name($res_rfp['project_title']);
			}
			else
			{
				$nav_url=$ilpage['merch'].'?id='.$id;
			}									
			$navcrumb[$nav_url] = $res_rfp['project_title'];
			$navcrumb[""] = $res_rfp['project_title'];	
			$navcrumb[""] = $phrase['_preview_bid'];
	
	}
	
	
	
	// watchlist bid notification
	$current_highbidnotify = isset($ilance->GPC['highbidnotify']) ? $ilance->GPC['highbidnotify'] : 0;
	$current_lasthournotify = isset($ilance->GPC['lasthournotify']) ? $ilance->GPC['lasthournotify'] : 0;
	$current_subscribed = isset($ilance->GPC['subscribed']) ? $ilance->GPC['subscribed'] : 0;  
	$bidstate = '';
	$min_bidamount = isset($ilance->GPC['minimum']) ? $ilance->GPC['minimum'] : '0.00';
	$proxytext = '';
	$show['categoryuseproxybid'] = false;
	
	
	$qty = isset($ilance->GPC['qty']) ? intval($ilance->GPC['qty']) : 1;

    $user_detls = $ilance->db->query(" SELECT *  FROM " . DB_PREFIX . "users WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' ");
	$userdet = $ilance->db->fetch_array($user_detls, DB_ASSOC);
					
	if($userdet['enable_bid'] =='1')
	{
			print_notice($phrase['_sorry '], "Your ability to bid has been restricted. Please <a href='/main-contact'>contact GreatCollections' Customer Service</a> at 1.800.44.COINS (+1.949.679.4180)", "merch.php?id=".$id."", $phrase['_back']);
			exit();
	}	  

	
	
	$pprint_array = array('shipperid','shippingservice','qty','current_bidamountformatted','proxytext','min_bidamount','state','id','bidstate','current_highbidnotify','current_lasthournotify','current_subscribed','project_user_id','cid','current_email_clarification','currency_id','attachment_style','pmb_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','input_style','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','newhead','pbid','project_current_bid_formatted');
	//if($_SESSION['ilancedata']['user']['userid']=='82')
	$ilance->template->fetch('main', 'listing_forward_auction_placebid_preview_imaged.html');
	//else
	//$ilance->template->fetch('main', 'listing_forward_auction_placebid_preview.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### PLACE BID HANDLER FOR SERVICE AUCTION ##################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bid' AND (isset($ilance->GPC['state']) AND $ilance->GPC['state'] != 'product' OR empty($ilance->GPC['state'])))
{
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
	$id = intval($ilance->GPC['id']);
        
	$area_title = $phrase['_placing_a_bid'];
	$page_title = SITE_NAME . ' - ' . $phrase['_placing_a_bid'];
        
        $navcrumb = array();
        $navcrumb["$ilpage[rfp]?cmd=listings"] = $phrase['_services'];
        $navcrumb["$ilpage[rfp]?id=" . $id] = fetch_auction('project_title', $id);
        $navcrumb[""] = $area_title;
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
                $ilance->subscription = construct_object('api.subscription');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_fields = construct_object('api.bid_fields');
                $ilance->auction = construct_object('api.auction');
                $ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, false);
                
		$project_state = fetch_auction('project_state', $id);
		if ($project_state != 'service')
		{
			$area_title = $phrase['_access_to_bid_is_denied'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_to_bid_is_denied'];
			
			print_notice($phrase['_access_denied'], $phrase['_access_denied'], $ilpage['main'], ucwords($phrase['_click_here']));
			exit();	
		}
		
		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'servicebid') == 'no')
		{
                        $area_title = $phrase['_access_to_bid_is_denied'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_access_to_bid_is_denied'];
                        
                        print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('servicebid'));
                        exit();
                }
                
                $bid_limit_per_day = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday'); 
		$bid_limit_per_month = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitpermonth');           
		$bid_per_day = fetch_user_bidcount_per('day', $_SESSION['ilancedata']['user']['userid']);
		$bid_per_month = fetch_user_bidcount_per('month', $_SESSION['ilancedata']['user']['userid']);					
				
		if ($bid_per_day > $bid_limit_per_day OR $bid_per_day == $bid_limit_per_day OR $bid_per_month > $bid_limit_per_month OR $bid_per_month == $bid_limit_per_month)
		{
			$area_title = $phrase['_access_to_bid_is_denied'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_to_bid_is_denied'];
				
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('bidlimitperday'));
			exit();  
		}
                
                if (empty($ilance->GPC['id']) OR $ilance->GPC['id'] == 0)
                {
                        $area_title = $phrase['_bad_rfp_warning'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_bad_rfp_warning'];
                        
                        print_notice($phrase['_invalid_rfp_specified'], $phrase['_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal'], $ilpage['search'], $phrase['_search_rfps']);
                        exit();
                }
                
                // the ending true defines if the user can't bid, show a template.  Set false to use as boolean true/false.
                $ilance->bid->user_can_bid($_SESSION['ilancedata']['user']['userid'], $id, true);
                
                ($apihook = $ilance->api('rfp_place_service_bid')) ? eval($apihook) : false;
                
                // show watchlist options
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "watchlist
                        WHERE watching_project_id = '" . intval($id) . "' 
                                AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) == 0)
                {                        
                        $lasthour   = '<input type="checkbox" name="lasthournotify" id="lasthournotify" value="1" />';
                        $lowerbid   = '<input type="checkbox" name="lowbidnotify" id="lowbidnotify" value="1" />';
                        $subscribed = '<input type="checkbox" name="subscribed" id="subscribed" value="1" />';
                }
                else
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                               $lasthour   = (($res['hourleftnotify']) ? '<input checked type="checkbox" name="lasthournotify" id="lasthournotify" value="1" /> ' : '<input type="checkbox" name="lasthournotify" id="lasthournotify" value="1" /> ');
                               $lowerbid   = (($res['lowbidnotify']) ? '<input checked type="checkbox" name="lowbidnotify" id="lowbidnotify" value="1"  />' : '<input type="checkbox" name="lowbidnotify" id="lowbidnotify" value="1" />');
                               $subscribed = (($res['subscribed']) ? '<input checked type="checkbox" name="subscribed" id="subscribed" value="1"  />' : '<input type="checkbox" name="subscribed" id="subscribed" value="1" />');
                        }
                };
                
                // check if this project is by invite only
                $res_invite_checklist = array();
                
                $sql_invite_checklist = $ilance->db->query("
                        SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                        FROM " . DB_PREFIX . "projects 
                        WHERE project_id = '" . $id . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_invite_checklist) > 0)
                {
                        $res_invite_checklist = $ilance->db->fetch_array($sql_invite_checklist, DB_ASSOC);
                        
                        // make sure bidder is not the owner of the project
                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $res_invite_checklist['user_id'] == $_SESSION['ilancedata']['user']['userid'])
                        {
                                $area_title = $phrase['_bad_rfp_warning'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_bad_rfp_warning'];
                                
                                print_notice($phrase['_auction_owners_cannot_bid_on_their_own_auctions'], $phrase['_were_sorry_auction_owners_can_not_place_bid_on_their_own_auctions']."<br /><br />".$phrase['_please_contact_customer_support'], 'javascript:history.back(1);', $phrase['_back']);
                                exit();
                        }
                        
                        if ($res_invite_checklist['project_details'] == 'invite_only')
                        {
                                // invite only auction
                                $sign = '+';
                                if ($res_invite_checklist['mytime'] < 0)
                                {
                                        $res_invite_checklist['mytime'] = - $res_invite_checklist['mytime'];
                                        $sign = '-';
                                }
                                
                                if ($sign == '-')
                                {
                                        $area_title = $phrase['_this_rfp_has_expired_bidding_is_over'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_this_rfp_has_expired_bidding_is_over'];
                                        
                                       print_notice($phrase['_bid_filter_restriction'] . "&nbsp;" . $phrase['_this_rfp_has_expired_bidding_is_over'], $phrase['_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project']."<br /><br />".$phrase['_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project'], $ilpage['main'], $phrase['_main_menu']); 
                                        exit();
                                }
                                
                                // project is by invitation only
                                $sql_checklist_invite = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "project_invitations
                                        WHERE project_id = '" . intval($id) . "'
                                                AND buyer_user_id = '" . $res_invite_checklist['user_id'] . "'
                                                AND seller_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_checklist_invite) > 0)
                                {
                                        $sign = '+';
                                        if ($res_invite_checklist['mytime'] < 0)
                                        {
                                                $res_invite_checklist['mytime'] = - $res_invite_checklist['mytime'];
                                                $sign = '-';
                                        }
                                        
                                        if ($sign == '-')
                                        {
                                                $area_title = $phrase['_this_rfp_has_expired_bidding_is_over'];
                                                $page_title = SITE_NAME . ' - ' . $phrase['_this_rfp_has_expired_bidding_is_over'];
                                               print_notice($phrase['_bid_filter_restriction'] . "&nbsp;" . $phrase['_this_rfp_has_expired_bidding_is_over'], $phrase['_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project']."<br /><br />".$phrase['_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project'], $ilpage['main'], $phrase['_main_menu']); 
                                                exit();
                                        }
                                        
                                        $ilance->bid->bid_filter_checkup($id);
                                        
                                        $area_title = $phrase['_placing_a_bid'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_placing_a_bid'];
                                        // let's fetch the existing bid information for this bid proposal
                                        $current_proposal = $current_bidamount = $current_estimate_days = '';
                                        
                                        $res_bid = array();
                                        $show['bidexists'] = false;
                                        
                                        $sql_bid = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "project_bids
                                                WHERE project_id = '" . $id . "'
                                                        AND bidstatus != 'declined'
                                                        AND bidstate != 'retracted' 
                                                        AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                ORDER BY bid_id DESC
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_bid) > 0)
                                        {
                                                $res_bid = $ilance->db->fetch_array($sql_bid, DB_ASSOC);
                                                $show['bidexists'] = true;
                                                $current_proposal = stripslashes($res_bid['proposal']);
                                                $current_bidamount = sprintf("%01.2f", $res_bid['bidamount']);
                                                $current_estimate_days = intval($res_bid['estimate_days']);
						$ilance->GPC['paymethod'] = $res_bid['winnermarkedaspaidmethod'];
                                        }
                                        
                                        $wysiwyg_area = print_wysiwyg_editor('proposal', $current_proposal, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
                                        // project details
                                        $sql_rfp = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "projects
                                                WHERE project_id = '" . $id . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_rfp) > 0)
                                        {
                                                $res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC);
                                                
                                                $title = stripslashes($res_rfp['project_title']);
                                                $project_id = $id;		
                                                $category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res_rfp['project_state'], $res_rfp['cid']);
                                                $category = '<a href="' . $ilpage['rfp'] . '?cid=' . $res_rfp['cid'] . '">' . $category . '</a>';
                                                $cid = $res_rfp['cid'];
						$attachment_style = ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], $accessname='attachments') == 'yes')
							? ''
							: 'disabled="disabled"';
                                                
                                                $hiddeninput = array(
                                                        'attachtype' => 'bid',
                                                        'project_id' => $id,
                                                        'user_id' => $_SESSION['ilancedata']['user']['userid'],
                                                        'category_id' => $res_rfp['cid'],
                                                        'filehash' => md5(time()),
                                                        'max_filesize' => $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit')
                                                );
                                                
                                                $uploadbutton = '<input name="attachment" style="font-size:15px" onclick=Attach("'.$ilpage['upload'].'?crypted='.encrypt_url($hiddeninput).'") type="button" value="'.$phrase['_upload'].'" class="buttons" '.$attachment_style.' />';
                                                // show bid amount pulldown but disable based on buyers bid type payout preference								
                                                if ($res_rfp['filter_bidtype'])
                                                {
                                                        $bidamounttype_pulldown = '<input type="hidden" name="filtered_bidtype" value="' . $res_rfp['filtered_bidtype'] . '" />';
                                                        $bidamounttype = $ilance->auction->construct_bidamounttype($res_rfp['filtered_bidtype']);
                                                        $method = $ilance->auction->construct_measure($res_rfp['filtered_bidtype']);
                                                }
                                                else
                                                {
                                                        $bamounttype = $method = '';
                                                        if (!empty($res_bid['bidamounttype']))
                                                        {
                                                                $bamounttype = $res_bid['bidamounttype'];
                                                                $method = $ilance->auction->construct_measure($res_bid['bidamounttype']); 
                                                        }
                                                        $bidamounttype_pulldown = $ilance->auction->construct_bidamounttype_pulldown($bamounttype, 0, '2', $res_rfp['cid'], 'service');
                                                        
                                                        unset($bidamounttype);
                                                }
                                                
                                                // specific javascript includes
                                                $headinclude .= '
<script type="text/javascript">
<!--
function show_custom(obj)
{
        if (obj.value == \'weight\')
        {
                fetch_js_object("custom").style.display = \'\';
        }
        else
        {
                fetch_js_object("custom").style.display = \'none\';
        }
}
function validate_all()
{
        return validate_bid_amount() && validate_estimate() && validate_title() && validate_paymethod();
}
function validate_paymethod()
{
        if (fetch_js_object(\'paymethod_0\').checked == true)
        {
                alert(\'' . $phrase['_please_choose_how_you_would_like_to_be_paid'] . ' (' . $paymentmethods . ')\');
                return(false);        
        }
        return(true);
}
function validate_title()
{
        fetch_bbeditor_data();        
        if (fetch_js_object(\'proposal_id\').value == \'\')
        {
                alert(phrase[\'_please_include_a_bid_proposal_with_your_bid\']);
                return(false);        
        }
        return(true);
}
function validate_estimate()
{
        var Chars = "0123456789.";
        if (fetch_js_object(\'estimate_days\').value == \'\' || fetch_js_object(\'estimate_days\').value < 1 || fetch_js_object(\'estimate_days\').value == \'0\')
        {
                alert(phrase[\'_enter_the_estimated_measure_of_time_or_delivery_this_project_will_take_you\']);
                return(false);
        }
        for (var i = 0; i < fetch_js_object(\'estimate_days\').value.length; i++)
        {
                if (Chars.indexOf(fetch_js_object(\'estimate_days\').value.charAt(i)) == -1)
                {
                        alert(\'' . $phrase['_delivery_input_accepts_numberonly_values_only_please_try_again'] . '\');
                        return(false);
                }
        }
        return(true);
}
function validate_bid_amount()
{
        var Chars = "0123456789.,";
        for (var i = 0; i < fetch_js_object(\'bidamount\').value.length; i++)
        {
                if (Chars.indexOf(fetch_js_object(\'bidamount\').value.charAt(i)) == -1)
                {
                        alert(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
                        return(false);
                }
        }                                                                                
        if (fetch_js_object(\'bidamount\').value == \'0.00\' || fetch_js_object(\'bidamount\').value == \'0\' || fetch_js_object(\'bidamount\').value.length < 1)
        {
                alert(\'' . $phrase['_you_have_entered_an_incorrect_bid_amount_please_try_again'] . '\');
                return(false);
        }                                                                                
        ';
        if ($res_rfp['filter_bidtype'] == 0)
        {
        $headinclude .= '
        if (fetch_js_object(\'bidamounttype\').value == 0)
        {
                alert(phrase[\'_please_select_a_bid_amount_type_before_submitting_your_bid\']);
                return(false);
        }
        ';
        }        
        $headinclude .= '
        return(true);
}
//-->
</script>
';
                                                // service provider commission fees display
                                                $budget = $ilance->auction->fetch_rfp_budget($res_rfp['project_id']);
                                                $filtered_bidtypecustom = $res_rfp['filtered_bidtypecustom'];
                                                $cid = $res_rfp['cid'];
                                                
                                                // display bidtype filter prefered by buyer
						$bidtypefilter = ($res_rfp['filter_bidtype'])
							? $ilance->auction->construct_bidamounttype($res_rfp['filtered_bidtype'])
							: $phrase['_buyer_accepts_various_bid_amount_types'];
                        
						// #### payment methods the purchaser is offering
						$paymentmethods = print_payment_methods($res_rfp['project_id']);
						$paymethodsradios = print_payment_methods($res_rfp['project_id'], true);
						
                                                $fieldmode = ($show['bidexists']) ? 'update' : 'input';                                                
                                                $custom_bid_fields = $ilance->bid_fields->construct_bid_fields($cid, $res_rfp['project_id'], $fieldmode, 'service', 0, true);
                                                
                                                $pprint_array = array('paymethodsradios','custom_bid_fields','wysiwyg_area','bidamounttype','paymentmethods','bidtypefilter','cid','method','filtered_bidtypecustom','finalvaluefees','bidamounttype_pulldown','cid','uploadbutton','current_bidlock_amount','spellcheck_style','attachment_style','pmb_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','currency_proposal','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','input_style','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                                                
                                                $ilance->template->fetch('main', 'rfp_placebid.html');
                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                $ilance->template->parse_if_blocks('main');
                                                $ilance->template->pprint('main', $pprint_array);
                                                exit();
                                        }
                                }
                                else
                                {
                                        // this service provider has not been invited to this auction!
                                        $area_title = $phrase['_you_have_not_been_invited_to_place_a_bid'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_you_have_not_been_invited_to_place_a_bid'];
                                        
                                        print_notice($phrase['_bid_filter_restriction'] . "&nbsp;" . $phrase['_this_rfp_has_expired_bidding_is_over'], $phrase['_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project']."<br /><br />".$phrase['_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project'], $ilpage['main'], $phrase['_main_menu']); 
                                        exit();
                                }
                        }
                        else
                        {
                                // not by invitation only .. regular bid checkup
                                $id = intval($ilance->GPC['id']);
                                $project_id = $id;
                                $ilance->bid->bid_filter_checkup($id);
                                
                                $area_title = $phrase['_placing_a_bid'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_placing_a_bid'];
                                
                                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                                {
                                        // fetch existing bid proposal for updating if it has not been retracted
                                        $show['bidexists'] = false;
                                        $current_proposal = $current_bidamount = $current_estimate_days = '';
                                        $res_bid = array();
                                        
                                        $sql_bid = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "project_bids
                                                WHERE project_id = '" . intval($id) . "'
                                                        AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                        AND bidstate != 'retracted'
                                                        AND bidstatus != 'declined'
                                                ORDER BY bid_id DESC
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_bid) > 0)
                                        {
                                                $res_bid = $ilance->db->fetch_array($sql_bid, DB_ASSOC);
                                                $show['bidexists'] = true;
                                                $current_proposal = stripslashes($res_bid['proposal']);
                                                $current_bidamount = sprintf("%01.2f", $res_bid['bidamount']);
                                                $current_estimate_days = intval($res_bid['estimate_days']);
						$ilance->GPC['paymethod'] = $res_bid['winnermarkedaspaidmethod'];
                                        }
                                        
					// #### bid proposal editor ############
                                        $wysiwyg_area = print_wysiwyg_editor('proposal', $current_proposal, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
                                        
                                        $sql_rfp = $ilance->db->query("
                                                SELECT *, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                                FROM " . DB_PREFIX . "projects
                                                WHERE project_id = '" . intval($id) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_rfp) > 0)
                                        {
                                                $res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC);
						
						// #### project scheduled for future
                                                if ($res_rfp['date_starts'] > DATETIME24H)
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
                                                        
                                                        if ($sign != '-')
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
							
                                                        $timeleft = $project_time_left;
                                                        $started = $timeleft;
                                                        $project_id = $res_rfp['project_id'];
                                                        
                                                        $area_title = $phrase['_placing_a_bid'];
                                                        $page_title = SITE_NAME . ' - ' . $phrase['_placing_a_bid'];
                                                        
                                                        print_notice($phrase['_auction_event_is_scheduled'], $phrase['_this_auction_event_is_scheduled_and_has_not_started_yet'], $ilpage['main'], $phrase['_main_menu']);
                                                        exit();
                                                }
						
						// #### project started ########
                                                else
                                                {
                                                        $title = stripslashes($res_rfp['project_title']);
                                                        $category = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res_rfp['project_state'], $res_rfp['cid']));
                                                        $category = '<a href="' . $ilpage['rfp'] . '?cid=' . $res_rfp['cid'] . '">' . $category . '</a>';
                                                        
                                                        // #### check for attachment permissions
							$attachment_style = ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], $accessname='attachments') == "yes")
								? ''
								: 'disabled="disabled"';
                                                        
                                                        // #### encrypted upload button
                                                        $cid = $res_rfp['cid'];
                                                        $hiddeninput = array(
                                                                'attachtype' => 'bid',
                                                                'project_id' => $id,
                                                                'user_id' => $_SESSION['ilancedata']['user']['userid'],
                                                                'category_id' => $res_rfp['cid'],
                                                                'filehash' => md5(time()),
                                                                'max_filesize' => $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit')
                                                        );
                                                        $uploadbutton = '<input name="attachment" style="font-size:15px" onclick=Attach("'.$ilpage['upload'].'?crypted='.encrypt_url($hiddeninput).'") type="button" value="'.$phrase['_upload'].'" class="buttons" '.$attachment_style.' />';
                                                        
                                                        // show bid amount pulldown but disable based on buyers bid type payout preference                                                                        
                                                        if ($res_rfp['filter_bidtype'])
                                                        {
                                                                $bidamounttype_pulldown = '<input type="hidden" name="filtered_bidtype" value="'.$res_rfp['filtered_bidtype'].'" />';
                                                                $bidamounttype = $ilance->auction->construct_bidamounttype($res_rfp['filtered_bidtype']);
                                                                $method = $ilance->auction->construct_measure($res_rfp['filtered_bidtype']);
                                                        }
                                                        else
                                                        {
                                                                $bidamounttype = $bamounttype = $method = '';
                                                                if (!empty($res_bid['bidamounttype']))
                                                                {
                                                                        $bamounttype = $res_bid['bidamounttype'];
                                                                        $method = $ilance->auction->construct_measure($res_bid['bidamounttype']); 
                                                                }
                                                                $bidamounttype_pulldown = $ilance->auction->construct_bidamounttype_pulldown($bamounttype, 0, '2', $res_rfp['cid'], 'service');
                                                        }
                                                        
							// #### purchaser payment methods being offered
                                                        $paymentmethods = print_payment_methods($res_rfp['project_id']);
							$paymethodsradios = print_payment_methods($res_rfp['project_id'], true);                                                        
                                                        $headinclude .= '
<script type="text/javascript">
<!--
function show_custom(obj)
{
        if (obj.value == \'weight\')
        {
                fetch_js_object("custom").style.display = \'\';
        }
        else
        {
                fetch_js_object("custom").style.display = \'none\';
        }
}
function validate_all()
{
        return validate_bid_amount() && validate_estimate() && validate_title() && validate_paymethod();
}
function validate_paymethod()
{
        if (fetch_js_object(\'paymethod_0\').checked == true)
        {
                alert(\'' . $phrase['_please_choose_how_you_would_like_to_be_paid'] . ' (' . $paymentmethods . ')\');
                return(false);        
        }
        return(true);
}
function validate_title()
{
        fetch_bbeditor_data();        
        if (fetch_js_object(\'proposal_id\').value == \'\')
        {
                alert(phrase[\'_please_include_a_bid_proposal_with_your_bid\']);
                return(false);        
        }
        return(true);
}
function validate_estimate()
{
        var Chars = "0123456789.";
        if (fetch_js_object(\'estimate_days\').value == \'\' || fetch_js_object(\'estimate_days\').value < 1 || fetch_js_object(\'estimate_days\').value == \'0\')
        {
                alert(phrase[\'_enter_the_estimated_measure_of_time_or_delivery_this_project_will_take_you\']);
                return(false);
        }
        for (var i = 0; i < fetch_js_object(\'estimate_days\').value.length; i++)
        {
                if (Chars.indexOf(fetch_js_object(\'estimate_days\').value.charAt(i)) == -1)
                {
                        alert(\'' . $phrase['_delivery_input_accepts_numberonly_values_only_please_try_again'] . '\');
                        return(false);
                }
        }
        return(true);
}
function validate_bid_amount()
{
        var Chars = "0123456789.,";
        for (var i = 0; i < fetch_js_object(\'bidamount\').value.length; i++)
        {
                if (Chars.indexOf(fetch_js_object(\'bidamount\').value.charAt(i)) == -1)
                {
                        alert(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
                        return(false);
                }
        }                                                                                
        if (fetch_js_object(\'bidamount\').value == \'0.00\' || fetch_js_object(\'bidamount\').value == \'0\' || fetch_js_object(\'bidamount\').value.length < 1)
        {
                alert(\'' . $phrase['_you_have_entered_an_incorrect_bid_amount_please_try_again'] . '\');
                return(false);
        }                                                                                
        ';
        if ($res_rfp['filter_bidtype'] == 0)
        {
        $headinclude .= '
        if (fetch_js_object(\'bidamounttype\').value == 0)
        {
                alert(phrase[\'_please_select_a_bid_amount_type_before_submitting_your_bid\']);
                return(false);
        }
        ';
        }        
        $headinclude .= '
        return(true);
}
//-->
</script>
';
                                                        $budget = $ilance->auction->fetch_rfp_budget($res_rfp['project_id']);
                                                        
                                                        // service provider commission fees display
                                                        $filtered_bidtypecustom = $res_rfp['filtered_bidtypecustom'];
                                                        $cid = $res_rfp['cid'];
                                                        
                                                        // display bidtype filter prefered by buyer
							$bidtypefilter = ($res_rfp['filter_bidtype'])
								? $ilance->auction->construct_bidamounttype($res_rfp['filtered_bidtype'])
								: $phrase['_buyer_accepts_various_bid_amount_types']; 
                                                        
                                                        $fieldmode = ($show['bidexists']) ? 'update' : 'input';
                                                        
                                                        $custom_bid_fields = $ilance->bid_fields->construct_bid_fields($cid, $res_rfp['project_id'], $fieldmode, 'service', 0, true);
							
							$pprint_array = array('paymethodsradios','custom_bid_fields','lasthour', 'lowerbid', 'subscribed', 'wysiwyg_area','bidtypefilter','cid','method','filtered_bidtypecustom','paymentmethods','bidamounttype','finalvaluefees','bidamounttype_pulldown','cid','uploadbutton','current_bidlock_amount','spellcheck_style','attachment_style','pmb_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','currency_proposal','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','input_style','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                                                        
                                                        $ilance->template->fetch('main', 'rfp_placebid.html');
                                                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                        $ilance->template->parse_if_blocks('main');
                                                        $ilance->template->pprint('main', $pprint_array);
                                                        exit();
                                                }
                                        }
                                }
                                else
                                {
                                        refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
                                        exit();
                                }
                        }
                }
	}
	else
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}
}
// #### PLACE NEW BID FOR PRODUCT AUCTION ######################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bid' AND $ilance->GPC['state'] == 'product')
{
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
	$id = intval($ilance->GPC['id']);
	
    $cid = fetch_auction('cid',$id);
	$title = fetch_auction('project_title',$id);   
	$area_title = $phrase['_placing_a_bid'];
	
	$page_title = SITE_NAME . ' - ' . $phrase['_placing_a_bid'];
        
		
		//kannan on 01feb11 breadcrumb code
		
		$categoryname = $ilance->categories->recursive($cid, 'product', $_SESSION['ilancedata']['user']['slng'], 0, '', $ilconfig['globalauctionsettings_seourls']);
		$listingcategory = $categoryname;
		$show['nourlbit'] = true;
                        $navcrumb = array();
                      
								  $cat_details=$ilance->categories_parser->fetch_coin_class(0,0,$cid);
								
								$series_details=$ilance->categories_parser->fetch_coin_series(0,$cat_details[0]['coin_series_unique_no']);
								$denomination_detail=$ilance->categories_parser->fetch_denominations($series_details['coin_series_denomination_no']);
								 
								$subcatname .= ', <span class="black">' . $series_details['coin_series_name'] . '</span>';
								$childrenids=$ilance->categories_parser->fetch_children_pcgs($series_id);
			 $navcrumb=array();
			$navcrumb["$ilpage[merch]?denomination=".$denomination_detail['denomination_unique_no']] = $denomination_detail['denomination_long'];
			$navcrumb["$ilpage[search]?mode=product&series=".$series_details['coin_series_unique_no']] = $series_details['coin_series_name'];
			$navcrumb["$ilpage[merch]?id=" . $id] = $title;
	       $navcrumb["$ilpage[rfp]?cmd=bid&id=" . $id . "&state=product"] = $phrase['_placing_a_bid'];
		
      /*  $navcrumb = array();
        $navcrumb["$ilpage[merch]?cmd=listings"] = $phrase['_product_auctions'];
        $navcrumb["$ilpage[merch]?id=" . $id] = $id;
        $navcrumb[""] = $area_title;*/
		
	
        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_proxy = construct_object('api.bid_proxy');
                $ilance->auction = construct_object('api.auction');
                $ilance->subscription = construct_object('api.subscription');
                $ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = false);
                
                // #### check subscription #####################################
                if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'productbid') == 'no')
                {
                        $area_title = $phrase['_access_to_bid_is_denied'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_access_to_bid_is_denied'];
                
                        print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('productbid'));
                        exit();
                }
		
		$project_state = fetch_auction('project_state', $id);
		if ($project_state != 'product')
		{
			$area_title = $phrase['_access_to_bid_is_denied'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_to_bid_is_denied'];
			
			print_notice($phrase['_access_denied'], $phrase['_access_denied'], $ilpage['main'], ucwords($phrase['_click_here']));
			exit();	
		}
                
		// #### check bids per day limit ###############################
                $bidtotal = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday');
                $bidsleft = max(0, ($bidtotal - fetch_bidcount_today($_SESSION['ilancedata']['user']['userid'])));
                
                if ($bidsleft <= 0)
                {
                        $area_title = $phrase['_access_to_bid_is_denied'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_access_to_bid_is_denied'];
                        
                        print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('bidlimitperday'));
                        exit();        
                }
                
                // #### check if the listing id was properly entered ###########
                if (empty($id) OR $id <= 0)
                {
                        $area_title = $phrase['_bad_rfp_warning'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_bad_rfp_warning'];
                        
                        print_notice($phrase['_invalid_rfp_specified'], $phrase['_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal'], $ilpage['search'], $phrase['_search_rfps']);
                        exit();
                }
                
                // #### determine if bidder can pass any filter requirements by the seller
                $ilance->bid->product_bid_filter_checkup($id);
                
                // #### determine if this bidder was invited to place a bid
                if (is_bidder_invited($_SESSION['ilancedata']['user']['userid'], $id) == false)
                {
                        $area_title = $phrase['_you_have_not_been_invited_to_place_a_bid'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_you_have_not_been_invited_to_place_a_bid'];
                        
                    print_notice($phrase['_bid_filter_restriction'] . "&nbsp;" . $phrase['_this_rfp_has_expired_bidding_is_over'], $phrase['_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project']."<br /><br />".$phrase['_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project'], $ilpage['main'], $phrase['_main_menu']); 
                        exit();
                }
                
                // #### show watchlist options selected ########################
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "watchlist
                        WHERE watching_project_id = '" . $id . "' 
                                AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) == 0)
                {                        
                        $lasthour = '<input type="checkbox" name="lasthournotify" id="lasthournotify" value="1" />';
                        $higherbid = '<input type="checkbox" name="highbidnotify" id="highbidnotify" value="1" />';
                        $subscribed = '<input type="checkbox" name="subscribed" id="subscribed" value="1" />';
                }
                else
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $lasthour = (($res['hourleftnotify']) ? '<input checked type="checkbox" name="lasthournotify" id="lasthournotify" value="1" /> ' : '<input type="checkbox" name="lasthournotify" id="lasthournotify" value="1" /> ');
                                $higherbid = (($res['highbidnotify']) ? '<input checked type="checkbox" name="highbidnotify" id="highbidnotify" value="1" />' : '<input type="checkbox" name="highbidnotify" id="highbidnotify" value="1" />');
                                $subscribed = (($res['subscribed']) ? '<input checked type="checkbox" name="subscribed" id="subscribed"  value="1" />' : '<input type="checkbox" name="subscribed" id="subscribed" value="1" />');
                        }
                }
                 
                $id = intval($ilance->GPC['id']);
                
                // #### rebid details (if applicable) ##########################
                $current_bidamount = 0;
                $sql_bid = $ilance->db->query("
                        SELECT bidamount
                        FROM " . DB_PREFIX . "project_bids
                        WHERE project_id = '" . $id . "'
                                AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_bid) > 0)
                {
                        $res_bid = $ilance->db->fetch_array($sql_bid);
                        $current_bidamount = $res_bid['bidamount'];
                }
                
                // auction details
                $sql_rfp = $ilance->db->query("
                        SELECT p.*, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, s.ship_method
                        FROM " . DB_PREFIX . "projects p
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
                        WHERE p.project_id = '" . $id . "'
                                AND p.project_state = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_rfp) > 0)
                {
                        $res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC);
                        
                        $auctiontype = $res_rfp['filtered_auctiontype'];
                        if ($auctiontype == 'fixed')
                        {
                                $buynow_qty = $res_rfp['buynow_qty'];
                        }
                        
                        // quantity available
                        $qty = $res_rfp['buynow_qty'];
                        
                        // is owner trying to bid?
                        if ($res_rfp['user_id'] == $_SESSION['ilancedata']['user']['userid'])
                        {
                                $area_title = $phrase['_bid_denied_cannot_bid_on_own_auction'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_bid_denied_cannot_bid_on_own_auction'];
                                
                                print_notice($area_title, $phrase['_sorry_merchants_cannot_place_bids_on_their_own_product_auctions'].'<br />', 'javascript:history.back(1);', $phrase['_back']);
                                exit();
                        }
                        
                        if ($res_rfp['date_starts'] > DATETIME24H)
                        {
                                print_notice($phrase['_auction_event_is_scheduled'], $phrase['_this_auction_event_is_scheduled_and_has_not_started_yet'], $ilpage['main'], $phrase['_main_menu']);
                                exit();
                        }
                        
                        $currency = print_left_currency_symbol();
                        // highest bid amount placed for this auction
                        $highestbid = 0;
                        $highbid = $ilance->db->query("
                                SELECT MAX(bidamount) AS maxbidamount
                                FROM " . DB_PREFIX . "project_bids
                                WHERE project_id = '" . $id . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($highbid) > 0)
                        {
                                $res = $ilance->db->fetch_array($highbid, DB_ASSOC);
                                $highestbid = sprintf("%.02f", $res['maxbidamount']);
                        }
                        
                        $title = stripslashes($res_rfp['project_title']);
                        $project_id = intval($ilance->GPC['id']);
                        
                        // show starting bid price
                        $startprice = $ilance->currency->format($res_rfp['startprice'], $res_rfp['currencyid']);
                        $buynowprice = '';
                        if ($res_rfp['buynow_price'] > 0)
                        {
                                $buynowprice = $ilance->currency->format($res_rfp['buynow_price'], $res_rfp['currencyid']);
                        }
                        
                        $show['hasnobids'] = false;
                        $show['currentbid'] = true;
                        $currentprice = $ilance->currency->format($res_rfp['currentprice'], $res_rfp['currencyid']);
                        $min_bidamount = sprintf("%.02f", '0.01');
                        $min_bidamountformatted = $ilance->currency->format('0.01', $res_rfp['currencyid']);
                                        
                        if ($res_rfp['bids'] <= 0)
                        {
                                $show['hasnobids'] = true;
                                $show['currentbid'] = false;
                                
                                // do we have starting price?
                                if ($res_rfp['startprice'] > 0)
                                {
                                        $min_bidamount = sprintf("%.02f", $res_rfp['startprice']);
                                        $min_bidamountformatted = $ilance->currency->format($res_rfp['startprice'], $res_rfp['currencyid']);
                                        
                                        // just in case our highest bid is 0 we will check our starting bid
                                        // and adjust the $highestbid variable to the start price to at least
                                        // generate the next increment if we've defined any in this category
                                        if ($highestbid == 0)
                                        {
                                                $highestbid = $min_bidamount;
                                        }
                                }
                                
                                $currentprice = $ilance->currency->format($min_bidamount, $res_rfp['currencyid']);
                        }
                        
                        // is admin using custom bid increments?
                        $proxybit = '';
                        $incrementgroup = $ilance->categories->incrementgroup($res_rfp['cid']);
                        $sqlincrements = $ilance->db->query("
                                SELECT amount
                                FROM " . DB_PREFIX . "increments
                                WHERE ((increment_from <= $highestbid
                                        AND increment_to >= $highestbid)
                                                OR (increment_from < $highestbid
                                        AND increment_to < $highestbid))
                                        AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
                                ORDER BY amount DESC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlincrements) > 0)
                        {
                                $show['increments'] = 1;
                                
                                $resincrement = $ilance->db->fetch_array($sqlincrements);
                                $increment = $ilance->currency->format($resincrement['amount'], $res_rfp['currencyid']) . ' - <a href="javascript:void(0)" onclick="Attach(\'' . HTTP_SERVER . $ilpage['rfp'] . '?msg=bid-increments&amp;c=' . $res_rfp['cid'] . '\')">' . $phrase['_more'] . '</a>';
                                if ($res_rfp['bids'] > 0)
                                {
                                        // if we have more than 1 bid start the bid increments since the first bidder cannot bid against the opening bid
                                        $min_bidamount = sprintf("%.02f", $highestbid + $resincrement['amount']);
                                        $min_bidamountformatted = $ilance->currency->format(($highestbid + $resincrement['amount']), $res_rfp['currencyid']);
                                }
                                
                                $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($res_rfp['project_id'], $_SESSION['ilancedata']['user']['userid']);
                                if ($pbit > 0)
                                {
                                        $proxybit = $ilance->currency->format($pbit, $res_rfp['currencyid']) . ' : ' . $phrase['_invisible'];
                                }
                        }
                        else
                        {
                                $show['increments'] = 0;
                                
                                // admin should define some increments if we get to this point
                                $increment = $ilance->currency->format(0, $res_rfp['currencyid']) . ' - <a href="javascript:void(0)" onclick="Attach(\'' . $ilpage['rfp'] . '?msg=bid-increments&amp;c=' . $res_rfp['cid'] . '\')">' . $phrase['_more'] . '</a>';
                                // minimum bid amount
                                $min_bidamount = sprintf("%.02f", $highestbid);
                                $min_bidamountformatted = $ilance->currency->format($highestbid, $res_rfp['currencyid']);
                                
                                $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($res_rfp['project_id'], $_SESSION['ilancedata']['user']['userid']);
                                if ($pbit > 0)
                                {
                                        $proxybit = $ilance->currency->format($pbit, $res_rfp['currencyid']) . ' - ' . $phrase['_invisible'];
                                }
                        }
                        
                        $proxytext = '';
                        $show['categoryuseproxybid'] = false;
                        if ($ilconfig['productbid_enableproxybid'] AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], 'product', $res_rfp['cid']))
                        {
                                $show['categoryuseproxybid'] = true;
                                $proxytext = '<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_when_you_place_a_bid_for_an_item_enter_the_maximum_amount\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a> ' . $phrase['_proxy'] . '';
				
				if (isset($pbit) AND $pbit > $min_bidamount)
				{
					$min_bidamount = sprintf("%.02f", $pbit) + 0.01;
					$min_bidamountformatted = $ilance->currency->format($min_bidamount, $res_rfp['currencyid']);
				}
                        }
                        
                        $state = 'product';
                        
                        // #### specific javascript includes ###################
                        $headinclude .= '
<script type="text/javascript">
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
                val = fetch_js_object(\'bidamount_field\').value;
                var bidamount = string_to_number(val);
		bidamount = parseFloat(bidamount);
                
                val2 = fetch_js_object(\'hiddenfieldminimum\').value;
                var minimumbid = string_to_number(val2);
		minimumbid = parseFloat(minimumbid);
                
                if (bidamount == \'NaN\' || bidamount == \'\' || bidamount <= \'0\')
                {
                        alert(phrase[\'_cannot_place_value_for_your_bid_amount_your_bid_amount_must_be_greater_than_the_minimum_bid_amount\']);
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
		
		if (f.qty.value <= 0 || f.qty.value == \'\')
		{
			alert(\'Please enter the amount of quantity to purchase.\');
			haveerrors = 1;
		}
                
                fetch_js_object(\'bidamount_field\').value = bidamount;
        }
        
        return (!haveerrors);
}
//-->
</script>';
                        
                        // #### do we have a reserve price #####################
                        $reserve_auction = 0;
                        $reserve_met = '';
                        if ($res_rfp['reserve'])
                        {
                                $reserve_auction = 1;
                                $highest_amount = '--';
                                
                                $sql_highest = $ilance->db->query("
                                        SELECT MAX(bidamount) AS highest
                                        FROM " . DB_PREFIX . "project_bids
                                        WHERE project_id = '" . $res_rfp['project_id'] . "'
                                        ORDER BY highest
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_highest) > 0)
                                {
                                        $res_highest = $ilance->db->fetch_array($sql_highest, DB_ASSOC);
                                        $highest_amount = $res_highest['highest'];
                                }
                                
                                // is reserve met?
                                if ($highest_amount != '--' AND $highest_amount >= $res_rfp['reserve_price'])
                                { 
                                        $reserve_met = $phrase['_yes_reserve_price_met'];
                                }
                                else
                                {
                                        $reserve_met = '<span style="color:red">' . $phrase['_no_reserve_price_not_met']. '</span>';
                                        if ($show['hasnobids'] AND $show['currentbid'])
                                        {
                                                $reserve_met .= '<div><strong>' . $phrase['_this_bid_will_be_the_actual_bid_placed_up_to_the_reserve_price'] . '</strong></div>';
                                        }
                                }
                        }
			
			// #### shipping information selector ##################
			$show['localpickuponly'] = false;
			if ($res_rfp['ship_method'] == 'localpickup')
			{
				$show['localpickuponly'] = true;
			}
			$shipservicepulldown = print_shipping_methods($res_rfp['project_id'], 1, false, false, true);
			$shippercount = print_shipping_methods($res_rfp['project_id'], 1, false, true);
			if ($shippercount == 1)
			{
				print_shipping_methods($res_rfp['project_id'], 1, false, false);
				$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . $res_rfp['project_id'] . "'", "ship_service_$shipperidrow");
			}
			
			$pprint_array = array('shipperid','shipservicepulldown','lasthour','higherbid','subscribed','qty','proxybit','buynowprice','currentprice','buynow_qty','reserve_met','min_bidamountformatted','startprice','proxytext','state','increment','highestbid','min_bidamount','current_bidlock_amount','spellcheck_style','attachment_style','pmb_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','currency_proposal','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','input_style','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                        
                        $ilance->template->fetch('main', 'listing_forward_auction_placebid.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', $pprint_array);
                        exit();
                }
        }
}
else if (isset($ilance->GPC['msg']) AND $ilance->GPC['msg'] == 'bid-permissions')
{
	$page_title = $phrase['_viewing_bid_permissions_help'];
	$area_title = SITE_NAME . ' - ' . $phrase['_viewing_bid_permissions_help'];
        
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
	$ilance->template->load_popup('popupheader', 'popup_header.html');
	$ilance->template->load_popup('popupmain', 'popup_bid_permissions.html');
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('popupheader');
        $ilance->template->parse_if_blocks('popupmain');
        $ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','meta_desc','meta_keyw','official_time') );
	$ilance->template->pprint('popupmain',   array('remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
	$ilance->template->pprint('popupfooter', array('headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','finaltime','finalqueries'));
	exit();
}
else if (isset($ilance->GPC['msg']) AND $ilance->GPC['msg'] == 'bid-increments')
{
        $page_title = SITE_NAME.' - ' . $phrase['_bid_increments'];
	$area_title = $phrase['_bid_increments'];
        
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
        $cid = isset($ilance->GPC['c']) ? intval($ilance->GPC['c']) : 0;
        
        if ($cid == 0)
        {
                echo $phrase['_you_must_select_a_category_please_close_this_window'];
                exit;
        }
        
        $ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = false);
        
        // custom product bid increment logic
        $show['no_increments'] = true;
        $incrementgroup = $ilance->categories->incrementgroup($cid);
        $categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $cid);
        
        $sqlincrements = $ilance->db->query("
                SELECT increment_from, increment_to, amount
                FROM " . DB_PREFIX . "increments
                WHERE groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
                ORDER BY incrementid ASC
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sqlincrements) > 0)
        {
                $row_count2 = 0;
                $show['no_increments'] = false;
                while ($rows = $ilance->db->fetch_array($sqlincrements, DB_ASSOC))
                {
                        $rows['from'] = $ilance->currency->format($rows['increment_from']);
                        if ($rows['increment_to'] == -1)
                        {
                                $rows['to'] = '<strong>' . $phrase['_or_more'] . '</strong>';
                        }
                        else
                        {
                                $rows['to'] = $ilance->currency->format($rows['increment_to']);
                        }
                        $rows['amount'] = $ilance->currency->format($rows['amount']);
                        $rows['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
                        $increments[] = $rows;
                        $row_count2++;
                }
        }
        else
        {
                $show['no_increments'] = true;
        }
        
        $ilance->template->load_popup('popupheader', 'popup_header.html');
	$ilance->template->load_popup('popupmain', 'popup_increments.html');
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('popupmain', 'increments');
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupmain');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','meta_desc','meta_keyw','official_time') );
	$ilance->template->pprint('popupmain', array('categorytitle','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
	$ilance->template->pprint('popupfooter', array('headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','finaltime','finalqueries'));
	exit();
}
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'revisionlog' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
        $page_title = SITE_NAME . ' - ' . $phrase['_listing_revision_details'];
	$area_title = $phrase['_listing_revision_details'];
        
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        $id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
        $navcrumb = array();
        $navcrumb["$ilpage[rfp]?id=" . $id] = $id;
        $navcrumb[""] = $phrase['_listing_revision_details'];
        
        $returnurl = $ilpage['rfp'] . '?id=' . $id;
        
        $sql = $ilance->db->query("
                SELECT datetime, changelog
                FROM " . DB_PREFIX . "projects_changelog
                WHERE project_id = '" . $id . "'
                ORDER BY id DESC
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $show['revision'] = true;
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
else
{
        // #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
        
	if (empty($ilance->GPC['id']))
	{
		$area_title = $phrase['_bad_rfp_warning_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_bad_rfp_warning_menu'];
                
		print_notice($phrase['_invalid_rfp_specified'], $phrase['_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal'], $ilpage['search'], $phrase['_search_rfps']);
		exit();
	}
	else
	{
                $show['widescreen'] = true;
                
                $ilance->subscription = construct_object('api.subscription');
		$ilance->auction = construct_object('api.auction');
                $ilance->auction_rfp = construct_object('api.auction_rfp');
		$ilance->bid = construct_object('api.bid');
		$ilance->bid_permissions = construct_object('api.bid_permissions');
		$ilance->feedback = construct_object('api.feedback');
                $ilance->feedback_rating = construct_object('api.feedback_rating');
                $categorycache = $ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1);
                
                // #### SHORTLIST BID ##########################################
                if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'shortlist' AND isset($ilance->GPC['bid']) AND $ilance->GPC['bid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "project_bids
                                SET isshortlisted = '1'
                                WHERE bid_id = '" . intval($ilance->GPC['bid']) . "'
                                        AND project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                LIMIT 1
                        ");
                        
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET bidsshortlisted = bidsshortlisted + 1
                                WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                        AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                LIMIT 1
                        ");
                        
                        if (!empty($ilance->GPC['returnurl']))
                        {
                                refresh(handle_input_keywords($ilance->GPC['returnurl']));
                                exit();
                        }
                        else
                        {
                                refresh(HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($ilance->GPC['id']));
                                exit();
                        }
                }
                
                // #### UNSHORTLIST BID ##########################################
                else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'unshortlist' AND isset($ilance->GPC['bid']) AND $ilance->GPC['bid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "project_bids
                                SET isshortlisted = '0'
                                WHERE bid_id = '" . intval($ilance->GPC['bid']) . "'
                                        AND project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                LIMIT 1
                        ");
                        
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET bidsshortlisted = bidsshortlisted - 1
                                WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                        AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                LIMIT 1
                        ");
                        
                        if (!empty($ilance->GPC['returnurl']))
                        {
                                refresh(handle_input_keywords($ilance->GPC['returnurl']));
                                exit();
                        }
                        else
                        {
                                refresh(HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($ilance->GPC['id']));
                                exit();
                        }
                }
                
		$id = intval($ilance->GPC['id']);
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$sql = $ilance->db->query("
                                SELECT *, UNIX_TIMESTAMP(date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime 
                                FROM " . DB_PREFIX . "projects
                                WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                        AND project_state = 'service'
                        ", 0, null, __FILE__, __LINE__);
		}
		else
		{
			$sql = $ilance->db->query("
                                SELECT *, UNIX_TIMESTAMP(date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime 
                                FROM " . DB_PREFIX . "projects
                                WHERE project_id = '" . intval($ilance->GPC['id']) . "'
                                        AND project_state = 'service'
                                        " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND visible = '1' AND (insertionfee = 0 OR (insertionfee > 0 AND ifinvoiceid > 0 AND isifpaid = '1'))" : "AND visible = '1'") . "
                        ", 0, null, __FILE__, __LINE__);
		}
                
		if ($ilance->db->num_rows($sql) == 0)
		{
			$area_title = $phrase['_bad_rfp_warning_menu'];
			$page_title = SITE_NAME . ' - ' . $phrase['_bad_rfp_warning_menu'];
			
			print_notice($phrase['_invalid_rfp_specified'], $phrase['_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal'], $ilpage['search'], $phrase['_search_rfps']);
			exit();
		}
		
		$ilance->bbcode = construct_object('api.bbcode');
		
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		
		// #### prevent duplicate content from search engines 
		if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef'])))
		{
			$seourl = construct_seo_url('serviceauctionplain', $res['cid'], $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0, $removevar = '');
			$view = isset($ilance->GPC['view']) ? '?view=' . $ilance->GPC['view'] . '#bids' : '';
			header('Location: ' . $seourl . $view);
			unset($seourl);
			exit();
		}
		
		// recently reviewed cookie saver
		if (isset($_COOKIE[COOKIE_PREFIX . 'serviceauctions']))
		{
			$arr = explode('|', $_COOKIE[COOKIE_PREFIX . 'serviceauctions']);
			if (!in_array($id, $arr))
			{
				$_COOKIE[COOKIE_PREFIX . 'serviceauctions'] = $_COOKIE[COOKIE_PREFIX . 'serviceauctions'] . "|$id";
				set_cookie('serviceauctions', $_COOKIE[COOKIE_PREFIX . 'serviceauctions'], true);
			}
		}
		else
		{
			$_COOKIE[COOKIE_PREFIX . 'serviceauctions'] = "$id";
			set_cookie('serviceauctions', $_COOKIE[COOKIE_PREFIX . 'serviceauctions'], true);
		}
		
		// service or product?
		$serviceauction = 1;
		$productauction = 0;
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects 
			SET views = views + 1 
			WHERE project_id = '" . intval($ilance->GPC['id']) . "'
				AND status != 'draft'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		
		$area_title = $phrase['_viewing_detailed_rfp'] . ' ' . stripslashes($res['project_title']) . ' (' . intval($ilance->GPC['id']) . ')';
		$page_title = stripslashes($res['project_title']) . ' (' . intval($ilance->GPC['id']) . ') - ' . SITE_NAME;
		
		$row_count = 0;
	
		// revision details
		$updateid = $res['updateid'];
		$show['revision'] = false;
		if ($updateid > 0)
		{
			$show['revision'] = true;
			$updateid = '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=revisionlog&amp;id=' . $res['project_id'] . '">' . $res['updateid'] . '</a>';
		}
	
		// bidding type filter
		$bidtypefilter = $phrase['_buyer_accepts_various_bid_amount_types'];
		if ($res['filter_bidtype'])
		{
			$bidtypefilter = $ilance->auction->construct_bidamounttype($res['filtered_bidtype']);
		}
		
		// does buyer use escrow payment control to ensure his funds are secure?
		$show['filter_escrow'] = false;
		$escrowbit = '';
		if ($res['filter_escrow'] == '1' AND $ilconfig['escrowsystem_enabled'])
		{
			$show['filter_escrow'] = true;
			$escrowbit = $phrase['_funds_will_be_secured_via_escrow_after_award'] . ' <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow.gif" border="0" alt="" id="" />';
		}
		
		// select project attachments
		$project_attachment = '';
		$show['has_attachments'] = false;
		
		$sql_attachments = $ilance->db->query("
			SELECT attachid, filename, filesize, filehash
			FROM " . DB_PREFIX . "attachment
			WHERE attachtype = 'project' 
				AND project_id = '" . intval($ilance->GPC['id']) . "' 
				AND visible = '1'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_attachments) > 0)
		{
			while ($res_attachments = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
			{
				switch (fetch_extension($res_attachments['filename']))
				{
					case 'gif':
					{
						$project_attachment .= '<a href="' . $ilpage['attachment'] . '?id=' . $res_attachments['filehash'] . '" target="_blank"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res_attachments['filehash'] . '" border="0" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
						break;
					}                                        
					case 'jpg':
					{
						$project_attachment .= '<a href="' . $ilpage['attachment'] . '?id=' . $res_attachments['filehash'] . '" target="_blank"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res_attachments['filehash'] . '" border="0" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
						break;
					}                                        
					case 'png':
					{
						$project_attachment .= '<a href="' . $ilpage['attachment'] . '?id=' . $res_attachments['filehash'] . '" target="_blank"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res_attachments['filehash'] . '" border="0" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
						break;
					}                                        
					default:
					{
						$project_attachment .= '<a href="' . $ilpage['attachment'] . '?id=' . $res_attachments['filehash'] . '" target="_blank">' . $res_attachments['filename'] . '</a> (' . $res_attachments['filesize'] . ' ' . $phrase['_bytes'] . ')<br />';
						break;
					}
				}
			}
			
			$show['has_attachments'] = true;
		}
		
		if (empty($project_attachment))
		{
			$show['has_attachments'] = true;
			$project_attachment = $phrase['_no_attachments_available'];
		}
		
		// buyers start date
		$memberstart = print_date(fetch_user('date_added', $res['user_id']), '%d-%b-%Y', 0, 0);
		
		// buyers state
		$state = fetch_user('state', $res['user_id']);
		$buyerstate = $state;
		
		// buyers city
		$city = fetch_user('city', $res['user_id']);
		$buyercity = $city;
		
		// buyers country
		$location = '';
		$countryname = print_user_country($res['user_id']);
		$buyercountry = $countryname;
		
		if (!empty($state))
		{
			$location = $state . ', ';
		}
		$location .= $countryname;
		
		// is realtime auction?
		$show['livebid'] = 0;
		$bidapplet = '';
		if ($res['project_details'] == 'realtime')
		{
			$show['livebid'] = 1;
			
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
				$bidapplet = '<div id="applet' . $res['project_id'] . '"></div>
<script type="text/javascript">
var fo2 = new FlashObject("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/' . DIR_SWF_NAME . '/livebid.swf", "applet' . $res['project_id'] . '", "730", "530", "8,0,0,0", "#ffffff");
fo2.addParam("quality", "high");
fo2.addParam("allowScriptAccess", "sameDomain");
fo2.addParam("flashvars", "languageConfig=' . DIR_FUNCT_NAME . '/' . DIR_XML_NAME . '/livebid_' . $_SESSION['ilancedata']['user']['slng'] . '.xml&prId=' . $res['project_id'] . '&sId=' . session_id() . '&rand=' . rand(100000, 999999) . '");
fo2.addParam("menu", "false");
</script>
';
			}
			else
			{
				$bidapplet = '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['login'] . '">' . $phrase['_become_a_registered_member_to_view_and_place_realtime_bids_using_our_brand_new_realtime_bid_interface'] . '</a></span>';        
			}
		}
		
		// fetch category
		$category = $ilance->categories->recursive($res['cid'], 'service', $_SESSION['ilancedata']['user']['slng'], $nourls = 0, '', $ilconfig['globalauctionsettings_seourls']);
		
		// fetch lowest bidder details (will populate $show['lowbidder_active'] also)..
		$lowbidtemp = $ilance->bid->fetch_lowest_bidder_info($res['project_id'], $res['user_id'], $res['bid_details']);
		$lowbidder = $lowbidtemp['lowbidder'];
		$lowbidderid = $lowbidtemp['lowbidderid'];
		unset($lowbidtemp);
		
		// project id
		$project_id = intval($ilance->GPC['id']);
		// additional information
		$show['has_additional_info'] = false;
		if (!empty($res['additional_info']))
		{
			$show['has_additional_info'] = true;
		}
		
		// #### OWNER VIEWING OWN DESCRIPTION ##################
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
		{
			// owner viewing own description
			$description = strip_vulgar_words($res['description']);
			$description = $ilance->bbcode->bbcode_to_html($description);
			$description = print_string_wrap($description, 100);
			
			$additional_info = strip_vulgar_words($res['additional_info']);
			$additional_info = print_string_wrap($additional_info);
		}
		// #### ADMIN VIEWING DESCRIPTION ######################
		else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			// admin viewing description
			$description = strip_vulgar_words($res['description']);
			$description = $ilance->bbcode->bbcode_to_html($description);
			$description = print_string_wrap($description, 100);
			
			$additional_info = strip_vulgar_words($res['additional_info']);
			$additional_info = print_string_wrap($additional_info);
		}
		// #### EVERYONE ELSE ##################################
		else
		{
			switch ($res['project_details'])
			{
				// #### PUBLIC AUCTION #################
				case 'public':
				{
					$project_details = $phrase['_public_event'];
					
					$description = strip_vulgar_words($res['description']);
					$description = $ilance->bbcode->bbcode_to_html($description);
					$description = print_string_wrap($description, 100);
					
					$additional_info = strip_vulgar_words($res['additional_info']);
					$additional_info = print_string_wrap($additional_info);
					break;
				}
				// #### INVITE ONLY AUCTION ############
				case 'invite_only':
				{
					$project_details = $phrase['_by_invitation_only'];
					
					$description = "[" . $phrase['_full_description_available_to_invited_providers_only'] . "]";
					$additional_info = "[" . $phrase['_full_description_available_to_invited_providers_only'] . "]";
					
					$invited_vendors = 1;
					$show['bidderuninvited'] = 1;
					if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
					{
						$sql_invites = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "project_invitations
							WHERE project_id = '" . $res['project_id'] . "'
								AND seller_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_invites) > 0)
						{
							// member invited
							$description = strip_vulgar_words($res['description']);
							$description = $ilance->bbcode->bbcode_to_html($description);
							$description = print_string_wrap($description, 100);
							
							$additional_info = strip_vulgar_words($res['additional_info']);
							$additional_info = print_string_wrap($additional_info);
							
							$invited_vendors = 1;
							$show['bidderuninvited'] = 0;
						}
					}
					break;
				}
				// #### REALTIME AUCTION ###############
				case 'realtime':
				{
					$project_details = $phrase['_realtime_event'];
					
					// vulgar censor
					$description = strip_vulgar_words($res['description']);
					$description = $ilance->bbcode->bbcode_to_html($description);
					$description = print_string_wrap($description, 100);
					
					$additional_info = strip_vulgar_words($res['additional_info']);
					$additional_info = print_string_wrap($additional_info);
					break;
				}
			}
		}
		// buyer details
		$sql_user_results = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $res['user_id'] . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_user_results) > 0)
		{
			$res_project_user = $ilance->db->fetch_array($sql_user_results);
		}
		else
		{
			print_notice($phrase['_owner_delisted'], $phrase['_sorry_the_owner_of_this_auction_has_been_delisted'], $ilpage['main'], $phrase['_main_menu']);
			exit();
		}
		
		$project_buyer = print_username($res_project_user['user_id'], 'href', 1, '', '', '');
		$buyername = fetch_user('username', $res_project_user['user_id']);
		$project_user_id = $res_project_user['user_id'];
		
		$memberinfo = $ilance->feedback->datastore($project_user_id);
		$feed1 = $memberinfo['rating'];
		$buyerscore = $memberinfo['pcnt'];
		
		$project_title = strip_vulgar_words($res['project_title']);
		$icons = $ilance->auction->auction_icons($res['project_id'], $res['user_id']);
		$views = $res['views'];
		
		// prevent the top cats in breadcrumb to contain any fields from this form
		$show['nourlbit'] = true;
		$navcrumb = array();
		if ($ilconfig['globalauctionsettings_seourls'])
		{
			$catmap = print_seo_url($ilconfig['servicecatmapidentifier']);
			$navcrumb["$catmap"] = $phrase['_services'];
			unset($catmap);
		}
		else
		{
			$navcrumb["$ilpage[rfp]?cmd=listings"] = $phrase['_services'];
		}
		
		$ilance->categories->breadcrumb($res['cid'], 'service', $_SESSION['ilancedata']['user']['slng']);
		$navcrumb[""] = $project_title;
		// distance calculation api
		$distance = '-';
		if (!empty($_SESSION['ilancedata']['user']['userid']))
		{
			if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'distance') == 'yes')
			{
				$ilance->distance = construct_object('api.distance');					
				$distance = $ilance->distance->print_distance_results($res_project_user['country'], $res_project_user['zip_code'], $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['postalzip']);
			}
		}
		
		// bid system permission display
		$filter_permissions = $ilance->bid_permissions->print_filters('service', $id);
		
		$fetchbidstuff = $ilance->bid->fetch_average_lowest_highest_bid_amounts($res['bid_details'], $res['project_id'], $res['user_id']);
		$bidprivacy = $fetchbidstuff['bidprivacy'];
		$average = $fetchbidstuff['average'];
		$lowest = $fetchbidstuff['lowest'];
		$highest = $fetchbidstuff['highest'];
		unset($fetchbidstuff);
		
		$show['ended'] = false;
				
		if ($res['date_starts'] > DATETIME24H)
		{
			$show['can_bid'] = false;
			
			// auction event has not started
			
			$dif = $res['starttime'];
			$ndays = floor($dif / 86400);
			$dif -= $ndays * 86400;
			$nhours = floor($dif / 3600);
			$dif -= $nhours * 3600;
			$nminutes = floor($dif / 60);
			$dif -= $nminutes * 60;
			$nseconds = $dif;
			$sign = '+';
			if ($res['starttime'] < 0)
			{
				$res['starttime'] = - $res['starttime'];
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
			$res['timetostart'] = $project_time_left;
			$started = $phrase['_starts'].': '.$res['timetostart'];
			
			// listing status
			$project_status = $started;
			$ends = print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$timeleft = "--";
		}
		else
		{
			$show['can_bid'] = true;
			
			// auction has already started!
			$started = print_date($res['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0);
			$ends = print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0);
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
				$project_time_left = $phrase['_ended'];
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
			$timeleft = $project_time_left;
			if ($res['status'] == 'open')
			{
				$show['ended'] = false;
				$project_status = $phrase['_event_open_for_bids'];
			}
			else
			{
				$show['ended'] = true;
			}
		}
		
		switch ($res['status'])
		{
			case 'closed':
			{
				$project_status = $phrase['_closed_since'] . ' ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
				break;
			}				
			case 'expired':
			{
				$project_status = $phrase['_expired_since'] . ' ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
				break;
			}				
			case 'delisted':
			{
				$project_status = $phrase['_delisted_since']." ".print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
				break;
			}				
			case 'approval_accepted':
			{
				$project_status = $phrase['_vendor_awarded_bidding_for_event_closed'];
				break;
			}                        
			case 'wait_approval':
			{
				// fetch days since the provider has been awarded giving more direction to the viewer
				$date1split = explode(' ', $res['close_date']);
				$date2split = explode('-', $date1split[0]);
				$days = $ilance->datetime->fetch_days_between($date2split[1], $date2split[2], $date2split[0], gmdate('m'), gmdate('d'), gmdate('Y'));
				if ($days == 0)
				{
					$days = 1;
				}
				$project_status = $phrase['_waiting_for_awarded_provider_to_accept_the_project'] . ' <span class="gray">(' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . ')</span>';
				break;
			}				
			case 'frozen':
			{
				$project_status = $phrase['_frozen_event_temporarily_closed'];
				break;
			}
			case 'finished':
			{
				$project_status = $phrase['_vendor_awarded_event_is_finished'];
				break;
			}				
			case 'archived':
			{
				$project_status = $phrase['_archived_event'];
				break;
			}				
			case 'draft':
			{
				$project_status = $phrase['_draft_mode_pending_post_by_owner'];
				break;
			}
		}
		
		// number of bids placed
		$declinedbids = $ilance->bid->fetch_declined_bids($res['project_id']);
		$retractedbids = $ilance->bid->fetch_retracted_bids($res['project_id']);
		$shortlistedbids = $ilance->bid->fetch_shortlisted_bids($res['project_id'], $res['user_id']);
		$bidsactive = $res['bids'];
		$bids = $res['bids'];
		if ($bids <= 0)
		{
			$bids = 0;
		}
		
		// invited vendors listings
		$invite_list = $ilance->auction_rfp->print_invited_users($id, $res['user_id'], $res['bid_details']);                      
		// payment methods accepted (if user has escrow enabled)
		if ($ilconfig['escrowsystem_enabled'] AND $res['filter_escrow'] == '1')
		{
			// set feetype as service provider since he is the one looking at the page
			$feetype = 'service';
		}
		
		$paymentmethods = print_payment_methods($res['project_id']);
		
		// awarded vendor row (under bid id)
		$row['award'] = '';
		if (isset($row['bidstatus']) AND $row['bidstatus'] == 'awarded')
		{
			$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" />';
			$awarded_vendor = stripslashes($row['username']);
		}
		
		$show['awarded_vendors'] = true;
		if (!isset($awarded_vendor))
		{
			$show['awarded_vendors'] = false;
		}
		
		$show['filters_vendors'] = true;
		if (!isset($filter_permissions))
		{
			$show['filters_vendors'] = false;
		}
		
		// are we viewing page as admin?
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$show['is_owner'] = false;
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
			{
				$show['is_owner'] = true;
			}
	
			$show['can_bid'] = false;
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
				$show['can_bid'] = true;
			}
		}
		else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			$show['is_owner'] = false;
			$show['can_bid'] = true;
			if ($_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
			{
				$show['is_owner'] = true;
				$show['can_bid'] = false;
			}
		}
		else
		{
			$show['is_owner'] = false;
			$show['can_bid'] = false;
		}
		
		($apihook = $ilance->api('rfp_custom_auction_questions')) ? eval($apihook) : false;
		
		// custom category questions
		$ilance->auction_questions = construct_object('api.auction_questions');
		$project_questions = $ilance->auction_questions->construct_auction_questions($res['cid'], $res['project_id'], 'output', 'service', $columns = 4);
		
		// fetch project budget for display
		$project_budget = $ilance->auction->fetch_rfp_budget($res['project_id'], false);
		
		// main category id for this auction
		$cid = $res['cid'];
		
		// #### BIDS PLACED ON THIS LISTING ####################
		if (isset($ilance->GPC['view']) AND ($ilance->GPC['view'] == 'declined' OR $ilance->GPC['view'] == 'retracted') AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
			$bidhistoryinfo = '';
			$show['bidhistoryinfo'] = 1;
			$groupby = "GROUP BY b.bid_id";
			
			$SQL = "SELECT b.winnermarkedaspaidmethod, b.bid_id, b.estimate_days, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.bidamounttype, b.bidcustom, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.isshortlisted, ";
			$inbidgroup = "AND b.bid_id > 0 ";
			$bidhistoryinfo = $phrase['_displaying_all_bids'];
		}
		else
		{
			// enable bid grouping?
			$bidhistoryinfo = '';
			$show['bidhistoryinfo'] = 0;
			if ($ilance->categories->bidgrouping($res['cid']))
			{
				$groupby = "GROUP BY b.user_id ";
				$show['bidhistoryinfo'] = 1;
				if ($ilance->categories->bidgroupdisplay($res['cid']) == 'lowest')
				{
					// group each bidders bid by lowest placed
					$SQL = "SELECT b.winnermarkedaspaidmethod, b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, MIN(b.bidamount) AS bidamount, b.bidamounttype, b.bidcustom, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.isshortlisted, ";
					$inbidgroup = "AND b.bid_id = (SELECT bid_id FROM " . DB_PREFIX . "project_bids WHERE user_id = b.user_id AND project_id = '" . $res['project_id'] . "' ORDER BY bidamount ASC LIMIT 1) ";
					$bidhistoryinfo = $phrase['_grouping_bidders_by_lowest_bids_placed'];
				}
				else
				{
					// group each bidders bid by highest placed
					$SQL = "SELECT b.winnermarkedaspaidmethod, b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, MAX(b.bidamount) AS bidamount, b.bidamounttype, b.bidcustom, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.isshortlisted, ";
					$inbidgroup = "AND b.bid_id = (SELECT bid_id FROM " . DB_PREFIX . "project_bids WHERE user_id = b.user_id AND project_id = '" . $res['project_id'] . "' ORDER BY bidamount DESC LIMIT 1) ";
					$bidhistoryinfo = $phrase['_grouping_bidders_by_highest_bids_placed'];
				}
			}
			else
			{
				// no bid grouping
				$groupby = "";
				$show['bidhistoryinfo'] = 1;
				$SQL = "SELECT b.winnermarkedaspaidmethod, b.bid_id, b.estimate_days, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.bidamounttype, b.bidcustom, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.isshortlisted, ";
				$inbidgroup = '';
				$bidhistoryinfo = $phrase['_displaying_all_bids'];
			}
		}
		$SQL .= "
		p.status AS project_status, p.escrow_id, p.cid, p.description, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.username, u.city, u.state, u.zip_code
		FROM " . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u
		WHERE b.project_id = '" . $res['project_id'] . "'
			AND b.project_id = p.project_id
			AND u.user_id = b.user_id
			$inbidgroup
		";
		if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'declined' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
			$show['active_bids'] = $show['retracted_bids'] = $show['shortlist_bids'] = false;
			$show['declined_bids'] = true;
			$SQL .= " AND b.bidstatus = 'declined' AND b.bidstate != 'retracted' ";
		}
		else if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'retracted' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
			$show['active_bids'] = $show['declined_bids'] = $show['shortlist_bids'] = false;
			$show['retracted_bids'] = true;
			$SQL .= " AND b.bidstate = 'retracted' ";
		}
		else if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'shortlist' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
			$show['active_bids'] = $show['declined_bids'] = $show['retracted_bids'] = false;
			$show['shortlist_bids'] = true;
			$SQL .= " AND b.isshortlisted = '1' ";
		}
		else
		{
			$show['active_bids'] = true;
			$show['declined_bids'] = $show['retracted_bids'] = $show['shortlist_bids'] = false;
			$SQL .= " AND b.bidstatus != 'declined' AND b.bidstate != 'retracted' ";
		}
		
		$SQL .= $groupby . " ORDER BY b.bidamount ASC ";
		
		// #### load our bid fields backend ############################
		$ilance->bid_fields = construct_object('api.bid_fields');
		$result = $ilance->db->query($SQL);
		
		if ($ilance->db->num_rows($result) > 0)
		{
			while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
			{
				// estimated project delivery in days
				$row['delivery'] = $row['estimate_days'] . ' ' . $ilance->auction->construct_measure($row['bidamounttype']);
				
				// bid amount type
				switch ($row['bidamounttype'])
				{
					case 'entire':
					{
						$row['bidamounttype'] = $phrase['_for_entire_project'];
						break;
					}
					case 'hourly':
					{
						$row['bidamounttype'] = $phrase['_per_hour'];
						break;
					}
					case 'daily':
					{
						$row['bidamounttype'] = $phrase['_per_day'];
						break;
					}
					case 'weekly':
					{
						$row['bidamounttype'] = $phrase['_weekly'];
						break;
					}
					case 'monthly':
					{
						$row['bidamounttype'] = $phrase['_monthly'];
						break;
					}
					case 'lot':
					{
						$row['bidamounttype'] = $phrase['_per_lot'];
						break;
					}
					case 'weight':
					{
						$row['bidamounttype'] = $phrase['_per_weight'] . ' ' . stripslashes($row['bidcustom']);
						break;
					}
					case 'item':
					{
						$row['bidamounttype'] = $phrase['_per_item'];
						break;
					}
				}
	    
				// date of bid placed
				$row['bid_datetime'] = print_date($row['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$row['bidamount'] = $ilance->bid->fetch_bid_amount($row['bid_details'], $row['bidamount'], $row['user_id'], $res_project_user['user_id'], $row['currencyid']);
				$row['proposal'] = strip_vulgar_words($row['proposal']);
				$row['proposal'] = $ilance->bbcode->bbcode_to_html($row['proposal']);
				$row['proposal'] = print_string_wrap($row['proposal'], 100);
				$row['isonline'] = print_online_status($row['user_id']);
				$row['verified'] = fetch_verified_credentials($row['user_id']);
				$row['provider'] = print_username($row['user_id']);
				$row['city'] = ucfirst($row['city']);
				$row['state'] = ucfirst($row['state']);
				$row['zip'] = trim(mb_strtoupper($row['zip_code']));
				$row['location'] = $row['state'] . ' &gt; ' . print_user_country($row['user_id'], $_SESSION['ilancedata']['user']['slng']);
				$row['awarded'] = print_username($row['user_id'], 'custom', 0, '', '', fetch_user('serviceawards', $row['user_id']) . ' ' . $phrase['_awards']);
				$row['reviews'] = fetch_service_reviews_reported($row['user_id']);
				$row['earnings'] = print_income_reported($row['user_id']);
				$row['portfolio'] = '<a href="' . HTTP_SERVER . $ilpage['portfolio'] . '?id=' . $row['user_id'] . '" style="text-decoration:underline">' . $phrase['_view'] . '</a>';
				$row['paymethod'] = print_fixed_payment_method($row['winnermarkedaspaidmethod'], false);
				
				$showbidattachment = 0;
				$row['bidattach'] = '';
				
				$sql_attachments = $ilance->db->query("
					SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
					FROM " . DB_PREFIX . "attachment
					WHERE attachtype = 'bid'
						AND project_id = '" . $row['project_id'] . "'
						AND user_id = '" . $row['user_id'] . "'
						AND visible = '1'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_attachments) > 0)
				{
					$row['bidattach'] .= '<div><strong>' . $phrase['_attachments'] . '</strong></div>';
					while ($res_attachments = $ilance->db->fetch_array($sql_attachments))
					{
						$row['bidattach'] .= '<div style="padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" border="0" alt="" id="" /> <span class="blue"><a href="' . $ilpage['attachment'] . '?id=' . $res_attachments['filehash'] . '" target="_blank">' . $res_attachments['filename'] . '</a></span></div>';
					}
				}
				
				// is blind bidding enabled?
				if ($row['bid_details'] == 'blind' OR $row['bid_details'] == 'full')
				{
					if (!empty($_SESSION['ilancedata']['user']['userid']))
					{
						// hide this service provider row if:
						// 1. current logged in user is not the bidder
						// 2. current logged in user is not the owner
						// 3. current logged in user is not the admin
						if ($_SESSION['ilancedata']['user']['userid'] != $row['user_id'] AND $_SESSION['ilancedata']['user']['userid'] != $row['project_user_id'] AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
						{
							// hide all bidder information (we are not project owner)
							$row['provider'] = '= ' . $phrase['_blind_bidder'] . ' =';
							$row['level'] = $row['city'] = $row['state'] = $row['zip'] = $row['location'] = $row['bidattach'] = '';
							$row['awarded'] = $row['reviews'] = $row['stars'] = $row['portfolio'] = $row['delivery'] = '--';
							$row['proposal'] = '<em>' . $phrase['_bid_proposal_hidden_due_to_blind_bidding'] . '</em>';
						}
					}
					else
					{
						$row['provider'] = '= ' . $phrase['_blind_bidder'] . ' =';
						$row['level'] = $row['city'] = $row['state'] = $row['zip'] = $row['location'] = $row['bidattach'] = '';
						$row['awarded'] = $row['reviews'] = $row['stars'] = $row['portfolio'] = $row['delivery'] = '--';
						$row['proposal'] = '<em>' . $phrase['_bid_proposal_hidden_due_to_blind_bidding'] . '</em>';        
					}
				}
				
				// awarded row (under bid id)
				$declinedbit = '';
				if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'declined')
				{
					$declinedbit = 'disabled="disabled"';
				}
				
				$retractedbit = '';
				if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'retracted')
				{
					$retractedbit = 'disabled="disabled"';
				}
				
				// #### handle shortlist button logic
				if (!empty($_SESSION['ilancedata']['user']['userid']) AND $row['isshortlisted'] AND $row['project_user_id'] == $_SESSION['ilancedata']['user']['userid'])
				{
					$row['shortlist'] = '<input type="button" value=" ' . $phrase['_unshortlist'] . ' " onclick="location.href=\'' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=unshortlist&amp;id=' . $row['project_id'] . '&amp;bid=' . $row['bid_id'] . '&amp;returnurl=' . $ilpage['rfp'] . '?id=' . $row['project_id'] . '#bids\'" class="buttons" style="font-size:15px" ' . $declinedbit . $retractedbit . ' />&nbsp;';                
				}
				else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $row['isshortlisted'] == 0 AND $row['project_user_id'] == $_SESSION['ilancedata']['user']['userid'])
				{
					$row['shortlist'] = '<input type="button" value=" ' . $phrase['_shortlist'] . ' " onclick="location.href=\'' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=shortlist&amp;id=' . $row['project_id'] . '&amp;bid=' . $row['bid_id'] . '&amp;returnurl=' . $ilpage['rfp'] . '?id=' . $row['project_id'] . '#bids\'" class="buttons" style="font-size:15px" ' . $declinedbit . $retractedbit . ' />&nbsp;';
				}
				else
				{
					$row['shortlist'] = '';
				}
				
				$row['award'] = $row['unawardbutton'] = $row['declinebutton'] = $row['awardbutton'] = '';
				
				$bidstatus = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $row['bid_id'] . "'", "bidstatus");
				$bidstate = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $row['bid_id'] . "'", "bidstate");
				
				// #### handle bidder buttons logic
				switch ($row['project_status'])
				{
					// #### open or expired
					case 'open':
					case 'expired':
					{
						if ($bidstatus == 'declined')
						{
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
						}
						else
						{
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'awardbid',
								'bid_id' => $row['bid_id']
							);
							$row['awardbutton'] = '<input type="button" style="font-size:15px" value=" ' . $phrase['_award'] . ' " onclick="confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\'); location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" />&nbsp;&nbsp;';
						    
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $row['bid_id']
							);
							$row['awardbutton'] .= '<input type="button" style="font-size:15px" value=" ' . $phrase['_decline'] . ' " onclick="confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\'); location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" />';
						}
						
						if ($bidstate == 'retracted')
						{
							
						}
						break;
					}                                                
					// #### provider accepted buyers award
					case 'approval_accepted':
					{
						if ($bidstatus == 'declined')
						{
						    $row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
						}
						else if ($bidstatus == 'awarded' AND ($bidstate != 'reviewing' OR $bidstate != 'wait_approval'))
						{
							$awarded_vendor = stripslashes($row['username']);
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" id="" />';
							
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'unawardbid',
								'bid_id' => $row['bid_id']
							);
							
							$buttonvisible = 'disabled="disabled"';
							if ($ilconfig['servicebid_buyerunaward'])
							{
								$buttonvisible = '';        
							}
							$row['unawardbutton'] = '<input type="button" style="font-size:15px" value=" ' . $phrase['_unaward'] . ' " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" ' . $buttonvisible . ' />';
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'reviewing' OR $bidstatus == 'choseanother' AND $bidstate == 'reviewing')
						{
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $row['bid_id']
							);
							$row['declinebutton'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " style="font-size:15px" onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" />';
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
						{
							$awarded_vendor = stripslashes($row['username']);
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" id="" />';
							
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'unawardbid',
								'bid_id' => $row['bid_id']
							);
							$row['unawardbutton'] = '<input type="button" value="' . $phrase['_unaward'] . '" onclick="location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else if ($bidstatus == 'placed' AND empty($bidstate))
						{
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $row['bid_id']
							);
							$row['declinebutton'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else
						{
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $row['bid_id']
							);
							$row['declinebutton'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						break;
					}                                                        
					// #### buyer waiting for provider's acceptance to award
					case 'wait_approval':
					{
						// buyer awarded provider :: enable radio icons :: create additional award cancellation button
						if ($bidstatus == 'declined')
						{
						    $row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
						{
							// buyer pending approval from service provider (provider did not confirm acceptance to project)
							$awarded_vendor = stripslashes($row['username']);
							$row['award'] = $phrase['_pending_approval'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>' . $phrase['_pending_approval'] . ' ' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . '</strong></div><div>' . $phrase['_pending_approval_allows_the_awarded_service_provider_to_accept_or_reject_the_service_auction'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a><div class="smaller gray">(' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . ')</div>';
							
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'unawardbid',
								'bid_id' => $row['bid_id']
							);
							$row['unawardbutton'] = '<input type="button" value="' . $phrase['_unaward'] . '" onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'reviewing')
						{
							// service provider in review mode - 90% change will not become awarded
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $row['bid_id']
							);
							$row['declinebutton'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else
						{
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'awardbid',
								'bid_id' => $row['bid_id']
							);
							$row['awardbutton'] = '<input type="button" value=" ' . $phrase['_award'] . ' " onclick="location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />&nbsp;';
							
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $row['bid_id']
							);
							$row['awardbutton'] .= '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="location.href=\'' . HTTPS_SERVER  . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}        
						break;
					}                                                
					// #### listing is finished/completed
					case 'finished':
					{
						// project in a phase to not allow any bid controls
						if ($bidstatus == 'declined')
						{
						    $row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
						{
							// buyer pending approval from service provider (provider did not confirm acceptance to project)
							$awarded_vendor = stripslashes($row['username']);
							$row['award'] = $phrase['_pending_approval'] . ' <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>' . $phrase['_pending_approval'] . ' ' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . '</strong></div><div>' . $phrase['_pending_approval_allows_the_awarded_service_provider_to_accept_or_reject_the_service_auction'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a><div class="smaller gray">(' . $phrase['_day'] . ' ' . $days . ' ' . $phrase['_of'] . ' ' . $ilconfig['servicebid_awardwaitperiod'] . ')</div>';
							
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'unawardbid',
								'bid_id' => $row['bid_id']
							);
							$row['unawardbutton'] = '<input type="button" value="' . $phrase['_unaward'] . '" onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" style="font-size:15px" />';
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'reviewing')
						{
							// service provider in review mode - 90% change will not become awarded
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $row['bid_id']
							);
							$row['declinebutton'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else if ($bidstatus == 'choseanother' AND $bidstate == 'reviewing')
						{
							// service provider in review mode - 90% change will not become awarded
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $row['bid_id']
							);
							$row['declinebutton'] = '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else if ($bidstatus == 'awarded')
						{
							$awarded_vendor = stripslashes($row['username']);
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" id="" />';
							$row['bidaction'] = '<input type="button" value=" ' . $phrase['_unaward'] . ' " class="buttons" disabled="disabled" style="font-size:15px" />';
						}
						else
						{
							$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'awardbid',
								'bid_id' => $row['bid_id']
							);
							$row['awardbutton'] = '<input type="button" value=" ' . $phrase['_award'] . ' " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" style="font-size:15px" />&nbsp;';
							
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $rows['bid_id']
							);
							$rows['awardbutton'] .= '<input type="button" value=" ' . $phrase['_decline'] . ' " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" style="font-size:15px" />';
						}
						break;
					}                                                
					// #### listing is closed or delisted
					case 'closed':
					case 'delisted':
					{
						// project in a phase to not allow any bid controls
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						break;
					}
				}
				
				if (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $row['project_user_id'] != $_SESSION['ilancedata']['user']['userid'])
				{
					// we are not the owner so disable buttons
					$row['shortlist'] = $row['awardbutton'] = $row['unawardbutton'] = $row['declinebutton'] = '';
					$row['bottombubbleclass'] = '';
				}
				else
				{
					$row['bottombubbleclass'] = 'bubble_b';
				}
				
				$row['bidretraction'] = $row['bidretractdate'] = $row['bidretractreason'] = '';
				if ($bidstate == 'retracted' AND isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'retracted')
				{
					$row['bidretractdate'] = 'x';
					$row['bidretractreason'] = 'y';
					$row['bidretraction'] = '<div><blockquote><div><strong>' . $phrase['_bid_retraction_on'] . ' <span class="blue">' . $row['bidretractdate'] . '</span></strong></div><div class="gray" style="padding-top:2px">' . $row['bidretractreason'] . '</div></blockquote></div>';        
				}
				
				// #### custom bid field answers
				$row['custom_bid_fields'] = $ilance->bid_fields->construct_bid_fields($row['cid'], $row['project_id'], 'output1', 'service', $row['bid_id'], false);
				$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$bid_results_rows[] = $row;
				
				$row_count++;
			}
			
			$show['no_bid_rows_returned'] = 0;
		}
		else
		{
			$show['no_bid_rows_returned'] = 1;
		}
		
		// #### PUBLIC MESSAGE BOARD ON LISTING ################
		$show['publicboard'] = 0;
		$boardcount = 0;
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
					$message['message'] = ($message['user_id'] == $res['user_id']) ? '<span class="green">[' . $phrase['_buyer'] . ']</span> ' . strip_vulgar_words(ilance_htmlentities($message['message'])) . '' : '<span class="blue">[' . $phrase['_bidder'] . ']</span> ' . strip_vulgar_words(ilance_htmlentities($message['message'])) . '';
					$message['class'] = ($msgcount % 2) ? 'alt1' : 'alt2';
					$messages[] = $message;	
					$msgcount++;
				}
				$boardcount = $msgcount;
			}
		}
		
		$transactionstatus = $ilance->bid->fetch_transaction_status($id);
		
		$metakeywords =  $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
		$metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
		
		// video description
		$videodescription = print_listing_video($id, $videowidth = '360', $videoheight = '280', $scriptextra = "fo.write('videoapplet-description');");
		
		// update category view count
		add_category_viewcount($cid);
		
		$pageurl = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $id;
		$purl = PAGEURL;
		if (!empty($purl))
		{
			$pageurl = $purl;
		}
		unset($purl);
		
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
		
		// #### purchasers other listings ######################
		$otherlistings = $ilance->auction->fetch_users_other_listings($res['user_id'], 'service', 5, $excludelist = array($id), true);
		// #### last viewed items ##############################
		$lastviewedlistings = $ilance->auction->fetch_recently_viewed_auctions('service', 5, 1, 0, '', true);
		
		$onload .= (isset($show['ended']) AND $show['ended']) ? '' : 'window.setInterval(\'refresh_project_details()\', \'1100\');';
		$jsend = '
<script language="javascript" type="text/javascript">
<!--
refresh_project_details();
//-->
</script>';
		//$headinclude .= (isset($show['ended']) AND $show['ended']) ? '' : '
		$headinclude .= '
<script language="javascript" type="text/javascript">
<!--
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
		//alert(\'Your browser currently does not support the XMLHttpRequest object\');
		return;
	}
	refresh_project_details();
	window.setInterval("refresh_project_details()", \'5000\');
}
function refresh_project_details()
{
	req.abort();
	req.open(\'GET\', \'' . HTTP_SERVER . $ilpage['ajax'] . '?do=refreshprojectdetails&id=' . $res['project_id'] . '\');
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
			
			fetch_js_object(\'timelefttext\').innerHTML = myString[0];
			fetch_js_object(\'bidstext\').innerHTML = myString[1];                        
			fetch_js_object(\'lowestbiddertext\').innerHTML = myString[2];
			fetch_js_object(\'highestbiddertext\').innerHTML = myString[3];
			fetch_js_object(\'awardedbiddertext\').innerHTML = myString[4];                        
			fetch_js_object(\'averagebidtext\').innerHTML = myString[5];
			fetch_js_object(\'projectstatustext\').innerHTML = myString[6];                        
			fetch_js_object(\'declinedbidstext\').innerHTML = myString[7];
			fetch_js_object(\'retractedbidstext\').innerHTML = myString[8];
	
			if (myString[10] == \'1\')
			{
				fetch_js_object(\'awardedbiddertext\').innerHTML = myString[4];
				toggle_show(\'awardedbidderrow\');
			}
			else
			{
				toggle_hide(\'awardedbidderrow\');
			}
			
			if (myString[11] == \'1\')
			{
				toggle_show(\'endedlistingrow\');
				toggle_hide(\'placebidrow\');
				toggle_hide(\'listingrevisionrow\');
			}
			else
			{
				toggle_hide(\'endedlistingrow\');
				toggle_show(\'placebidrow\');
				toggle_show(\'listingrevisionrow\');
				toggle_hide(\'awardedbidderrow\');
			}
			
			if (myString[14] == \'1\')
			{
				toggle_show(\'lowestbiddertextrow\');
				toggle_show(\'lowestbidderrow\');
				
				fetch_js_object(\'lowbiddertext\').innerHTML = myString[2];
				fetch_js_object(\'lowestbiddertext\').innerHTML = myString[12];
			}
			else
			{
				toggle_hide(\'lowestbiddertextrow\');
				toggle_hide(\'lowestbidderrow\');
			}
		}
	}        
	req.send(null);
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
		
		/// #### buyer promotional tools ###############################
		$ilance->auction_post = construct_object('api.auction_post');
		
		$enhancements = $ilance->auction_post->print_listing_enhancements('product');
		$featured = $res['featured'];
		$featured_date = $res['featured_date'];
		$highlite = $res['highlite'];
		$bold = $res['bold'];
		$autorelist = $res['autorelist'];
		// #### buyer facts ####################################
		$facts = fetch_buyer_facts($project_user_id, 'service');
		$jobsposted = (int)$facts['jobsposted'];
		$jobsawarded = (int)$facts['jobsawarded'];
		$awardratio = $facts['awardratio'];
		
		($apihook = $ilance->api('rfp_detailed_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'listing_reverse_auction.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('bid_results_rows','messages','otherlistings','lastviewedlistings'));
		$ilance->template->parse_if_blocks('main');
		
		$pprint_array = array('jobsposted','jobsawarded','awardratio','featured','featured_date','highlite','bold','autorelist','enhancements','videodescription','transactionstatus','jsend','otherlistings','lastviewedlistings','pageurl','bidsactive','shortlistedbids','retractedbids','declinedbids','updateid','lastrevision','buyerscore','buyername','bidprivacy','cid','bidhistoryinfo','category2','userbits','buyercity','buyerstate','buyercountry','paymentmethods','boardcount','views','bidtypefilter','escrowbit','icons','average','lowest','highest','project_questions','bidapplet','collapseobj_livebid_auctiontab','collapseimg_livebid_auctiontab','feed1','feed6','feed12','buyerstars','livebid','location','bids','started','ends','timeleft','placeabid','lowbidderid','lowbidder','cid','featured','realtime','category','subcategory','memberstart','countryname','collapserfpinfo_id','collapseimgrfpinfo_id','invite_list','rfpposted','rfpawards','fbcount','additional_info','project_user_id','lowest_bidder','highest_bidder','filter_permissions','awarded_vendor','project_status','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_budget','project_distance','project_id','bid_details','pmb','project_buyer','projects_posted','projects_awarded','project_currency','project_attachment','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('rfp_detailed_loop')) ? eval($apihook) : false;
		
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
