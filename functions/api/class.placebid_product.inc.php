<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1575
|| # -------------------------------------------------------------------- # ||
|| # Customer License # =ryotOqStzEoc1gDhm2kyaoC2VZLPe-ZTcK=-2d-y-SXgzbKia
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

if (!class_exists('bid'))
{
	exit;
}

/**
* Function to handle inserting a forward auction bid
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class placebid_product extends bid
{
	/**
        * Function for inserting a new product bid on a product auction event.  Additionally, this function will
        * detect if another bidder just placed a bid before this bid is inserted.  This will allow the system to
        * generate an error message back to the current bidder informing them to bid higher due to another bidder
        * placing a bid first.
        *
        * @param       integer      higher bid notify filter (optional)
        * @param       integer      last hour notify filter (optional)
        * @param       integer      project id
        * @param       integer      project owner id
        * @param       string       bid amount
        * @param       integer      qty
        * @param       integer      bidder id
        * @param       bool         is proxy bid?
        * @param       string       minimum bid amount
        * @param       string       reserve price amount
        * @param       string       custom argument for live bidding (future)
        * @param       boolean      show error messages (disable if you want to call this function via API to hide html error messages; this will then only return true or false) - default true
        * @param       string       buyer shipping cost (based on his selected shipping service when placing bid)
        * @param       integer      buyer selected shipping service id
        */
        function placebid($highbidnotify = 0, $lasthournotify = 0, $subscribed = 0, $id = 0, $project_user_id = 0, $bidamount = 0, $qty = 1, $bidderid = 0, $isproxy, $minimumbid, $reserveprice, $showerrormessages = true, $buyershipcost = 0, $buyershipperid = 0,$bid_time=DATETIME24H)
        {
                global $ilance, $myapi, $ilpage, $phrase, $ilconfig;
                
                $ilance->watchlist = construct_object('api.watchlist');
                $ilance->subscription = construct_object('api.subscription');
                $ilance->bid_proxy = construct_object('api.bid_proxy');
                $ilance->bid = construct_object('api.bid');
		$ilance->email = construct_dm_object('email', $ilance);
                
                if ($ilance->subscription->check_access($bidderid, 'productbid') == 'no')
                {
                        $area_title = $phrase['_buying_menu_denied_upgrade_subscription'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_buying_menu_denied_upgrade_subscription'];
                        
                        if ($showerrormessages)
                        {
                                print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('productbid'));
                                exit();
                        }
                        else
                        {
                                return false;
                        }       
                }
                
                $area_title = $phrase['_submitting_bid_proposal'];
                $page_title = SITE_NAME . ' ' . $phrase['_submitting_bid_proposal'];
                
		$resexpiry = array();
                $sqlexpiry = $ilance->db->query("
                        SELECT UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, status, cid, bids, project_title, buynow, buynow_price, buynow_qty, reserve, reserve_price, currentprice
                        FROM " . DB_PREFIX . "projects 
                        WHERE project_id = '" . intval($id) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlexpiry) > 0)
                {
                        $resexpiry = $ilance->db->fetch_array($sqlexpiry, DB_ASSOC);
                        if ($resexpiry['mytime'] < 0 OR $resexpiry['status'] != 'open')
                        {
                                $area_title = $phrase['_this_rfp_has_expired_bidding_is_over'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_this_rfp_has_expired_bidding_is_over'];
                                
                                if ($showerrormessages)
                                {
                                        print_notice($area_title, $phrase['_this_rfp_has_expired_bidding_is_over'], $ilpage['main'], $phrase['_main_menu']);
                                        exit();
                                }
                                else
                                {
                                        return false;
                                }
                        }
                }
                unset($sqlexpiry);
                
                $ilance->watchlist->insert_item(intval($bidderid), $id, 'auction', 'n/a', 0, $highbidnotify, $lasthournotify, $subscribed);
                
                // is proxy bid (and does category permit proxy bidding usage)?
                $proxycid = fetch_auction('cid', $id);
				$currencyid = fetch_auction('currencyid', $id);
		
		// #### anti-bid sniping feature ###############################
		if ($ilconfig['productbid_enablesniping'] AND isset($resexpiry['cid']) AND $resexpiry['cid'] > 0)
		{
			// #### check if bid sniping is active in this category
			$useantisnipe = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . $resexpiry['cid'] . "'", "useantisnipe");
			if ($resexpiry['mytime'] <= $ilconfig['productbid_snipedurationcount'] AND $useantisnipe)
			{
				// #### extend the listing x seconds ###########
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET date_end = DATE_ADD(date_end, INTERVAL " . $ilconfig['productbid_snipeduration'] . " SECOND)
					WHERE project_id = '" . intval($id) . "'
				");					
			}
		}
		
                if ($ilconfig['productbid_enableproxybid'] AND isset($isproxy) AND $isproxy AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], 'product', $proxycid))
                {		
				
				//vijay work for place time add 
				
					//$new_time = date('Y-m-d H:i:s', strtotime('+59 seconds'));
				 
				 // vijay work end
                        DEBUG("------------------------------------------", 'NOTICE');
                        DEBUG("Proxy Bid Enabled for Category ID $proxycid", 'NOTICE');
                        DEBUG("------------------------------------------", 'NOTICE');
                        DEBUG("User ID: $bidderid is placing a proxy bid amount: $bidamount, minimum bid including category increment is currently: $minimumbid", 'NOTICE');

                        $res = $ilance->bid_proxy->fetch_first_highest_proxybid($id);
                        $highestproxy = $res[0];
                        $highestproxyuserid = $res[1];
                        unset($res);
                        
                        $res = $ilance->bid_proxy->fetch_second_highest_proxybid($id);
                        $secondhighestproxy = $res[0];
                        $secondhighestproxyuserid = $res[1];
                        unset($res);
                        
                        $highestproxybiduser = $ilance->bid_proxy->fetch_highest_proxy_bid($id, $bidderid);
                        $secondhighestproxybiduser = $ilance->bid_proxy->fetch_second_highest_proxy_bid($id, $bidderid);

                        DEBUG("------------------------------------------", 'NOTICE');                                
                        DEBUG("Highest proxy bid is currently: $highestproxy by user id: $highestproxyuserid", 'NOTICE');
                        DEBUG("------------------------------------------", 'NOTICE');
                        DEBUG("Second Highest proxy bid is currently $secondhighestproxy by user id: $secondhighestproxyuserid", 'NOTICE');
                        DEBUG("------------------------------------------", 'NOTICE');
                        
                        // did this bidder already place a proxy bid for this auction?
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "proxybid
                                WHERE user_id = '" . intval($bidderid) . "'
                                    AND project_id = '" . intval($id) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                // this bidder already placed a proxy bid at some point .. is this new proxy bid higher? if so, place it!
                                $resproxy = $ilance->db->fetch_array($sql, DB_ASSOC);
                                if ($resproxy['maxamount'] < $bidamount)
                                {
                                        DEBUG("SQL: UPDATE " . DB_PREFIX . "proxybid with proxy bid amount $bidamount for User ID: $bidderid", 'NOTICE');
                                        DEBUG("------------------------------------------", 'NOTICE');
                                       
                                        // update existing proxybid
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "proxybid
                                                SET maxamount = '" . sprintf("%01.2f", $bidamount) . "',
                                                date_added = '" . $bid_time . "'
                                                WHERE user_id = '" . intval($bidderid) . "'
                                                    AND project_id = '" . intval($id) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
										
										if($highestproxyuserid == $bidderid)
										{
										
										// ensure that the user have enabled this feature
										
										  $email_notify = fetch_user('emailnotify', $bidderid);
										
										  $query_bid_confirmation = $ilance->db->query("SELECT bidconfirm FROM " . DB_PREFIX . "email_preference 
						                                                            WHERE user_id ='" . intval($bidderid) . "'");
						
						                   $row_bid_confirmation = $ilance->db->fetch_array($query_bid_confirmation);							
				 
				                           if( $row_bid_confirmation['bidconfirm'] == '1' AND $email_notify=='1')
						                   {
												//may2
												$bidends = print_date(fetch_auction('date_end',$id), '%A, %B %d, %Y %I:%M:%S %p', 0, 0).' (Pacific Time)';
												$ilance->email->mail = fetch_user('email', $bidderid);
												$ilance->email->slng = fetch_user_slng($bidderid);                
												$ilance->email->get('bid_notification_alert_bidder');		
												$ilance->email->set(array(
														'{{provider}}' => fetch_user('username', $bidderid),
														'{{price}}' => $ilance->currency->format($bidamount, $currencyid),
														'{{p_id}}' => intval($id),
														'{{project_title}}' => strip_tags(fetch_auction('project_title',$id)),																		
														'{{current_price}}' => $ilance->currency->format(fetch_auction('currentprice',$id), $currencyid),
														'{{start_price}}' => $ilance->currency->format($bidamount, $currencyid),
														'{{urlpath}}' => HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($id),
														'{{bidends}}' => $bidends,
												));
						
												$ilance->email->send();
												
											} 
												// murugan changes on march 25 for email to admin
												$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
												$ilance->email->slng = fetch_site_slng();
												
												$ilance->email->get('bid_notification_alert_bidder');		
												$ilance->email->set(array(
														'{{provider}}' => fetch_user('username', $bidderid),
														'{{price}}' => $ilance->currency->format($bidamount, $currencyid),
														'{{p_id}}' => intval($id),
														'{{project_title}}' => strip_tags(fetch_auction('project_title',$id)),																		
														'{{current_price}}' => $ilance->currency->format(fetch_auction('currentprice',$id), $currencyid),
														'{{start_price}}' => $ilance->currency->format($bidamount, $currencyid),
														'{{urlpath}}' => HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($id),
														'{{bidends}}' => $bidends,
												));
												
												$ilance->email->send();
											
											
									}		
                                }
                                else
                                {
                                        // inform bidder of proxy bid error (lower proxy bid being placed for same auction)
                                        if ($showerrormessages)
                                        {
                                                print_notice($phrase['_cannot_bid_lower_than_original_proxy_amount'] . ' ' . $ilance->currency->format($resproxy['maxamount'], $currencyid), $phrase['_it_has_been_detected_that_you_have_already_placed_a_higher_proxy_bid'], HTTP_SERVER . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $id . '&amp;state=product', $phrase['_back']);
                                                exit();
                                        }
                                        else
                                        {
                                                return false;
                                        }
                                }
                        }
                        else
                        {
                                DEBUG("SQL: INSERT INTO " . DB_PREFIX . "proxybid with proxy bid amount $bidamount for User ID: $bidderid", 'NOTICE');
                                DEBUG("------------------------------------------", 'NOTICE');
                                
                                // bidder wishes to enter a new maximum highest bid for proxy
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "proxybid
                                        (id, project_id, user_id, maxamount, date_added)
                                        VALUES(
                                        NULL,
                                        '" . intval($id) . "',
                                        '" . intval($bidderid) . "',
                                        '" . sprintf("%01.2f", $bidamount) . "',
                                        '" . $bid_time . "')
                                ", 0, null, __FILE__, __LINE__);
				
                                $proxybidid = $ilance->db->insert_id();
                        }
                        
                        $sqlbids = $ilance->db->query("
                                SELECT COUNT(*) AS bids
                                FROM " . DB_PREFIX . "project_bids
                                WHERE project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlbids) > 0)
                        {
                                $resbids = $ilance->db->fetch_array($sqlbids, DB_ASSOC);
				
				// #### no bids placed #########################
                                if ($resbids['bids'] == 0)
                                {
                                        DEBUG("No bids currently placed for this auction", 'NOTICE');
                                        
                                        // #### reserve price enabled ##########
                                        if ($reserveprice > 0)
                                        {
                                                DEBUG("Reserve price exists: $reserveprice", 'NOTICE');
                                                
                                                // when enabled, any bid placed that is lower than the reserve price
                                                // will be placed at the rate of the actual bid 
                                                // this will get the bids rolling up to the reserve amount bypassing the increment logic.
                                                // side note: in this situation, this is the first bid being placed.
                                                if ($bidamount >= $reserveprice)
                                                {
                                                        DEBUG("Reserve price has been met ($reserveprice); set the bid amount price ($bidamount) as reserve: $reserveprice", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        DEBUG("BID: $reserveprice", 'NOTICE');
                                                        
                                                        $bidamount = $reserveprice;
                                                }
                                                else
                                                {
                                                        DEBUG("Reserve price: $reserveprice has not been met", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        DEBUG("BID: $bidamount", 'NOTICE');
                                                }
                                        }
                                        
                                        // #### reserve price disabled #########
                                        else 
                                        {
                                                DEBUG("Reserve price disabled", 'NOTICE');
                                                DEBUG("------------------------------------------", 'NOTICE');
                                                DEBUG("BID: $minimumbid", 'NOTICE');
                                                
                                                // when reserve price is inactive, the bid placed will adhere to the admin defined increment logic for the bids
                                                // which is basically the minimum bid amount passed to this function
                                                // side note: in this situation, this is the first bid being placed.
                                                $bidamount = $minimumbid;
                                        }
                                }
				
				// #### one or more bids placed ################
                                else if ($resbids['bids'] > 0)
                                {
                                        DEBUG("Auction has $resbids[bids] bids currently placed", 'NOTICE');
                                        DEBUG("------------------------------------------", 'NOTICE');
                                        
                                        // #### reserve price enabled ##########
                                        if ($reserveprice > 0)
                                        {
                                                DEBUG("Reserve price exists: $reserveprice", 'NOTICE');
                                                DEBUG("------------------------------------------", 'NOTICE');
                                                
                                                if ($bidamount == $reserveprice)
                                                {
                                                        DEBUG("Reserve price has been met: $reserveprice; set bid amount: $bidamount as reserve price: $reserveprice", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        
                                                        $bidamount = $reserveprice;
                                                        DEBUG("BID: $bidamount", 'NOTICE');
                                                }
                                                else if ($bidamount > $reserveprice)
                                                {
                                                        DEBUG("Reserve price has been met; bid being placed: $bidamount is higher than our reserve price ($reserveprice)", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        
                                                        $cid = fetch_auction('cid', $id);
                                                        
                                                        // if we have a high proxy bid, and if our bid amount is higher than the proxy bid, and if the highest proxy + increment <= bid amount
                                                        //     310.01     > 0 and 360.00     > 310.01        and              320.01                           <= 360.00
                                                        //     360.00     > 0 and 330.01     > 360.00        and              370.00                           <= 330.01
                                                        if ($highestproxy > 0 AND $bidamount > $highestproxy AND $this->fetch_minimum_bid($highestproxy, $cid) <= $bidamount)
                                                        {
                                                        	if ($highestproxyuserid != $bidderid)
                                                                {
									if ($highestproxy > $reserveprice)
									{
										$nextamount = $this->fetch_minimum_bid($highestproxy, $cid);
									}
									else 
									{
										$nextamount = $this->fetch_minimum_bid($reserveprice, $cid);
									}
	                                                        }
                                                                else
                                                                {
									if ($showerrormessages)
									{
										refresh($ilpage['merch'] . '?id=' . $id);
										exit();
									}
									else
									{
										return true;
									}
                                                                }
                                                                
                                                                DEBUG("Next Bid + Increment $nextamount is <= proxy bid $bidamount", 'NOTICE');
                                                                DEBUG("------------------------------------------", 'NOTICE');
                                                                $bidamount = $nextamount;
                                                                DEBUG("BID: $bidamount", 'NOTICE');
                                                        }
                                                        else
                                                        {
                                                                DEBUG("Higher proxy bid exists $highestproxy by user id: $highestproxyuserid, and this bid: $bidamount by user id: $bidderid", 'NOTICE');
                                                                DEBUG("------------------------------------------", 'NOTICE');
                                                                
                                                                if ($highestproxyuserid != $bidderid)
                                                                {
                                                                        DEBUG("Highest proxy bid user id: $highestproxyuserid ($highestproxy) is not the same bidder as user id: $bidderid ($bidamount) so we'll place the bid without the increment logic as it's much greater and will get the bids moving faster", 'NOTICE');
                                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                                }
                                                                else
                                                                {
                                                                        DEBUG("Highest proxy bid user id: $highestproxyuserid ($highestproxy) is not the same bidder as user id: $bidder ($bidamount) so we'll place the bid a minimum bid instead", 'NOTICE');
                                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                                        
                                                                        if ($showerrormessages)
									{
										refresh($ilpage['merch'] . '?id=' . $id);
										exit();
									}
									else
									{
										return true;
									}
                                                                }
								
                                                                DEBUG("BID: $bidamount", 'NOTICE');
                                                        }
                                                }
                                                else
                                                {
                                                        DEBUG("Reserve price has not been met", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        DEBUG("BID: $bidamount", 'NOTICE');
                                                }
                                        }
                                        
					// #### reserve price disabled #########
					else 
                                        {
                                                DEBUG("Reserve price is disabled", 'NOTICE');
                                                DEBUG("------------------------------------------", 'NOTICE');
                                                
                                                if ($bidamount > $highestproxy)
                                                {
                                                        DEBUG("Proxy bid amount being placed: $bidamount is greater than highest proxy: $highestproxy", 'NOTICE');
                                                        
                                                        $cid = fetch_auction('cid', $id);
                                                        
                                                        DEBUG("Category ID: #$cid", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        
                                                        $nextamount = $this->fetch_minimum_bid($highestproxy, $cid);
                                                        
                                                        
                                                        
                                                        if ($nextamount >= $bidamount)
                                                        {
                                                                DEBUG("Next Bid + Bid Increment (in this category) $nextamount is >= to proxy bid $bidamount", 'NOTICE');
                                                                DEBUG("------------------------------------------", 'NOTICE');
                                                              	if ($highestproxyuserid == $bidderid)
                                                        		{
                                                        			if ($showerrormessages)
																	{
																		refresh($ilpage['merch'] . '?id=' . $id);
																		exit();
																	}
																	else
																	{
																		return true;
																	}
                                                        		}
                                                        }
                                                        else
                                                        {
                                                                DEBUG("Next Bid + Increment $nextamount is < proxy bid $bidamount", 'NOTICE');
                                                                DEBUG("------------------------------------------", 'NOTICE');
                                                                
                                                                if ($highestproxyuserid != $bidderid)
                                                                {
                                                                        DEBUG("Highest proxy bid user id: $highestproxyuserid ($highestproxy) is not the same bidder as user id: $bidderid ($bidamount) so we'll place the bid without the increment logic as it's much greater and will get the bids moving faster", 'NOTICE');
                                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                                	$bidamount = $nextamount;
                                                                }
                                                                else
                                                                {
                                                                        DEBUG("Highest proxy bid user id: $highestproxyuserid ($highestproxy) is not the same bidder as user id: $bidderid ($bidamount) so we'll place the bid a minimum bid instead", 'NOTICE');
                                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                                        
                                                                        if ($showerrormessages)
																		{
																			refresh($ilpage['merch'] . '?id=' . $id);
																			exit();
																		}
																		else
																		{
																			return true;
																		}
																		
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                }
    
                DEBUG("SQL: INSERT INTO " . DB_PREFIX . "project_bids bid amount: $bidamount for bidder id $bidderid", 'NOTICE');
                //vijay work for place time add 
				
				// $new_time = date('Y-m-d H:i:s', strtotime('+59 seconds'));
				 
				 // vijay work endy
                // insert the next minimum bid for the bidder
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "project_bids
                        (bid_id, user_id, project_id, project_user_id, bidamount, qty, date_added, bidstatus, bidstate, state, buyershipcost, buyershipperid)
                        VALUES(
                        NULL,
                        '" . intval($bidderid) . "',
                        '" . intval($id) . "',
                        '" . intval($project_user_id) . "',
                        '" . sprintf("%01.2f", $bidamount) . "',
                        '" . intval($qty) . "',
                        '" . $bid_time . "',
                        'placed',
                        '',
                        'product',
			'" . sprintf("%01.2f", $buyershipcost) . "',
			'" . intval($buyershipperid) . "')
                ", 0, null, __FILE__, __LINE__);
                $this_bid_id = $ilance->db->insert_id();
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "project_realtimebids
                        (id, bid_id, user_id, project_id, project_user_id, bidamount, qty, date_added, bidstatus, bidstate, state, buyershipcost, buyershipperid)
                        VALUES(
                        NULL,
                        '" . intval($this_bid_id) . "',
                        '" . intval($bidderid) . "',
                        '" . intval($id) . "',
                        '" . intval($project_user_id) . "',
                        '" . sprintf("%01.2f", $bidamount) . "',
                        '" . intval($qty) . "',
                        '" . $bid_time . "',
                        'placed',
                        '',
                        'product',
			'" . sprintf("%01.2f", $buyershipcost) . "',
			'" . intval($buyershipperid) . "')
                ", 0, null, __FILE__, __LINE__);
    
                DEBUG("------------------------------------------", 'NOTICE');
                DEBUG("set_bid_counters() - Set bid counters for this bidder (increases bidstoday + bidsthismonth)", 'NOTICE');
                
                // will increase bidstoday and bidsthismonth
                $this->set_bid_counters($bidderid, 'increase');
                
                DEBUG("------------------------------------------", 'NOTICE');
                DEBUG("SQL UPDATE project table bids + 1", 'NOTICE');
                
                // update bid count
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "projects
                        SET bids = bids + 1,
                        currentprice = '" . sprintf("%01.2f", $bidamount) . "'
                        WHERE project_id = '" . intval($id) . "'
                            AND project_state = 'product'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		
		// #### do we need to hide buy now price and controls? #########
		$hidebuynow = false;
		if ($resexpiry['buynow'] AND $resexpiry['buynow_price'] > 0 AND $resexpiry['buynow_qty'] > 0)
		{
			/*
			// is a reserve price set?
			if ($resexpiry['reserve'] AND $resexpiry['reserve_price'] > 0)
			{
				// has reserve price been met?
				if ($bidamount >= $resexpiry['reserve_price'])
				{
					$hidebuynow = true;
				}
			}
			else
			{
				// no reserve price set ... is current bid higher than buy now price?
				if ($bidamount >= $resexpiry['buynow_price'])
				{
					// bid amount higher than buy now price! hide buy now controls
					$hidebuynow = true;
				}
			}
			*/
			$cid = fetch_auction('cid', intval($id));
			$sql = $ilance->db->query("SELECT hidebuynow FROM " . DB_PREFIX . "categories WHERE cid = '" . intval($cid) . "'");
			$res = $ilance->db->fetch_array($sql);

			if ($bidamount >= $resexpiry['buynow_price'] OR $res['hidebuynow'])
			{
				// bid amount higher than buy now price! hide buy now controls
				$hidebuynow = true;
			}
			
		}
		
		// #### determine if we need to hide the buynow price (based on our bids exceeding or equaling the buy now price set)
		if ($hidebuynow)
		{
			// hide buy now controls
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET buynow = '0'
				WHERE project_id = '" . intval($id) . "'
			", 0, null, __FILE__, __LINE__);
		}
                
                // #### was this buyer invited? ################################
                $sql_invites = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE project_id = '" . intval($id) . "'
                            AND buyer_user_id = '" . intval($bidderid) . "'
                            AND bid_placed = 'no'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_invites) > 0)
                {
                        DEBUG("------------------------------------------", 'NOTICE');
                        DEBUG("It appears this bidder was invited by the seller, update invite table to bid_placed = yes", 'NOTICE');
                
                        // update invite table 
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "project_invitations
                                SET bid_placed = 'yes',
                                date_of_bid = '" . DATETIME24H . "'
                                WHERE buyer_user_id = '" . intval($bidderid) . "'
                                    AND project_id = '" . intval($id) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
        
                        $url = HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($id);
                        
                        // email owner
                        $ilance->email->mail = fetch_user('email', $project_user_id);
                        $ilance->email->slng = fetch_user_slng($project_user_id);
                        
                        $ilance->email->get('invited_bid_placed_buyer');		
                        $ilance->email->set(array(
                                '{{buyer}}' => fetch_user('username', $project_user_id),
                                '{{vendor}}' => fetch_user('username', $bidderid),
                                '{{rfp_title}}' => fetch_auction('project_title', intval($id)),
                                '{{project_id}}' => $id,
                                '{{url}}' => $url,
                        ));
                        
                        $ilance->email->send();
                }
                
                // #### AUTOMATED PROXY BIDDER ENGINE ##################
                // now supports proxy bidding on a per category basis
                if ($ilconfig['productbid_enableproxybid'] AND isset($isproxy) AND $isproxy AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], 'product', $proxycid))
                {
                        DEBUG("---------------------------------------------", 'NOTICE');
                        DEBUG("PROXY: ILance Proxy Bid Engine " . ILANCEVERSION . "", 'NOTICE');
                        DEBUG("---------------------------------------------", 'NOTICE');
                        // background proxy bidder init for this last bidder
                        // this is where the proxy automation comes into action
                        $ilance->bid_proxy->do_proxy_bidder(intval($id), $bidderid, $project_user_id, $skipbidder = 1, $bidderid);
                        
                        DEBUG("---------------------------------------------", 'NOTICE');
                        DEBUG("PROXY: Finished", 'NOTICE');
                        DEBUG("---------------------------------------------", 'NOTICE');
                }
                
                // #### for debug purposes only ################################
                // print_r($GLOBALS['DEBUG']); exit;
				
				
					//Nov-07
                
			       $op = $ilance->bid_proxy->fetch_second_highest_proxybid($id);
				
				  $sql6 = $ilance->db->query("SELECT bid_id,user_id,project_id FROM " . DB_PREFIX . "project_bids WHERE user_id='".$op[1]."' and project_id = '".$id."' ORDER BY bid_id  DESC limit 1");
				  $res6 = $ilance->db->fetch_array($sql6);
				 
				  $op1 =  $ilance->bid_proxy->fetch_second_highest_proxy_bid($id,$op[1]);
				  
				
				  $ilance->db->query("
                                UPDATE " . DB_PREFIX . "project_bids
                                SET bidamount = '".$op1."'
                                
                                WHERE bid_id = '" . $res6['bid_id'] . "'
                                    
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
			
			//nov-07
		
                $ilance->watchlist->send_notification(intval($bidderid), 'lowbidnotify', intval($id), $bidamount);
                $ilance->watchlist->send_notification(intval($bidderid), 'highbidnotify', intval($id), $bidamount);
                
                $ilance->email->mail = fetch_user('email', $project_user_id);
                $ilance->email->slng = fetch_user_slng($project_user_id);
                
                $ilance->email->get('bid_notification_alert');		
                $ilance->email->set(array(
                		'{{ownername}}' => fetch_user('username', $project_user_id),
                        '{{provider}}' => fetch_user('username', $bidderid),
                        '{{price}}' => $ilance->currency->format($bidamount, $currencyid),
                        '{{p_id}}' => intval($id),
						'{{project_title}}' => strip_tags(fetch_auction('project_title', intval($id))),
						'{{url}}' => HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($id),
                ));
                
                 // Murugan Changes On Mar 2 for Disable Email
				//$ilance->email->send();  
				
				//bidder confirm email herakle
				
				//may2
				$bidends = print_date(fetch_auction('date_end',$id), '%A, %B %d, %Y %I:%M:%S %p', 0, 0).' (Pacific Time)';
				$sqlnew = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "proxybid
                                WHERE user_id = '" . intval($bidderid) . "'
                                    AND project_id = '" . intval($id) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
						
				$resnew = $ilance->db->fetch_array($sqlnew);
				
				// ensure that the user have enabled Email preference for Bidconfirm mail
				
				$email_notify1 = fetch_user('emailnotify', $bidderid);
				
		        $query_bid_confirmation1 = $ilance->db->query("SELECT bidconfirm FROM " . DB_PREFIX . "email_preference 
						                                      WHERE user_id ='" . intval($bidderid) . "'");
						
				$row_bid_confirmation1 = $ilance->db->fetch_array($query_bid_confirmation1);							
				 
				 if( $row_bid_confirmation1['bidconfirm'] == '1' AND $email_notify1 =='1')
			     {
				
					$ilance->email->mail = fetch_user('email', $bidderid);
					$ilance->email->slng = fetch_user_slng($bidderid);
					
					$ilance->email->get('bid_notification_alert_bidder');		
					$ilance->email->set(array(
							'{{provider}}' => fetch_user('username', $bidderid),
							'{{price}}' => $ilance->currency->format($bidamount, $currencyid),
							'{{p_id}}' => intval($id),
							'{{project_title}}' => strip_tags($resexpiry['project_title']),
							'{{bids}}' => $resexpiry['bids'],						
							'{{current_price}}' => $ilance->currency->format(fetch_auction('currentprice',$id), $currencyid),
							'{{start_price}}' => $ilance->currency->format($resnew['maxamount'], $currencyid),
							'{{urlpath}}' => HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($id),
							'{{bidends}}' => $bidends,
					));
					
					$ilance->email->send(); 
					
				}	
				// murugan changes on march 25 for send Admin Email
				$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
				$ilance->email->slng = fetch_site_slng();
				
				$ilance->email->get('bid_notification_alert_bidder');		
				$ilance->email->set(array(
						'{{provider}}' => fetch_user('username', $bidderid),
						'{{price}}' => $ilance->currency->format($bidamount, $currencyid),
						'{{p_id}}' => intval($id),
						'{{project_title}}' => strip_tags(fetch_auction('project_title',$id)),																		
						'{{current_price}}' => $ilance->currency->format(fetch_auction('currentprice',$id), $currencyid),
						'{{start_price}}' => $ilance->currency->format($bidamount, $currencyid),
						'{{urlpath}}' => HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($id),
						'{{bidends}}' => $bidends,
				));
				
				$ilance->email->send();
				
				
				//outbid email for user
				
			    $sqlcat_coin_detail = $ilance->db->query("

				SELECT *

				FROM " . DB_PREFIX . "project_bids

				WHERE project_id = '".$id."'
				
				 ");
				 

				 if($ilance->db->num_rows($sqlcat_coin_detail) > 1)
				 {
				 
				           $sqlcat_coin = $ilance->db->query("
						   
                           SELECT *

				           FROM " . DB_PREFIX . "project_bids

				           WHERE project_id = '".$id."' order by bidamount DESC,bid_id desc limit 1,1
						   
				          ");
						  
						  $row_coin = $ilance->db->fetch_array($sqlcat_coin);
						  
						 $new_proxid=$ilance->db->query("

				SELECT MAX(maxamount) AS maxamount

				FROM " . DB_PREFIX . "proxybid

				WHERE user_id = '".$row_coin['user_id']."' AND project_id='".$id."'
				
				 ");
				 
				
				 $row_proxid = $ilance->db->fetch_array($new_proxid);
				 
			
				if ($row_proxid['maxamount'] <= fetch_auction('currentprice',$id) and  $row_coin['user_id']!=$ilance->bid->fetch_highest_bidder($id) )
				{
				
				
				// ensure that the user have enabled Email preference for Outbid mail
				
				$email_notify2 = fetch_user('emailnotify', $row_coin['user_id']);
				
				 $query_outbid = $ilance->db->query("SELECT outbid FROM " . DB_PREFIX . "email_preference 
						                                    WHERE user_id ='".$row_coin['user_id']."'");
						
			     $row_outbid = $ilance->db->fetch_array($query_outbid);							
				 
				  if($row_outbid['outbid'] == '1' AND $email_notify2 =='1')
				  {		  
						  $link = HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($id);
				 
						$ilance->email->mail = fetch_user('email', $row_coin['user_id']);
						$ilance->email->slng = fetch_user_slng($row_coin['user_id']);
						$ilance->email->project =intval($id);
						$ilance->email->get('bid_notification_alert_for_outbid');		
						$ilance->email->set(array(
								'{{provider}}' => fetch_user('username', $row_coin['user_id']),
								'{{price}}' => $ilance->currency->format(fetch_auction('currentprice',$id), $currencyid),
								'{{p_id}}' => intval($id),
								'{{project_title}}' => strip_tags(fetch_auction('project_title',$id)),								
								'{{firstname}}'=>fetch_user('first_name', $row_coin['user_id']),
								'{{secret_maximum_bid}}'=>$ilance->currency->format($row_proxid['maxamount'], $currencyid),
							    '{{end_time}}'=>date("l, F d, Y h:i:s A",strtotime(fetch_auction('date_end',$id))) . " (Pacific Time)",
								'{{prject_url}}'=>$link,
								
						));
						$ilance->email->send(); 
						
					 
					
				   }  
				 
				 }
				 
				 }                   
                
                if ($showerrormessages)
                {
			// todo check for seo
                        refresh(HTTP_SERVER . $ilpage['merch'] . '?id=' . $id);
						
						//new change apr28,apr29
						
						echo '<script type="text/javascript">
							/* <![CDATA[ */
							var google_conversion_id = 972477887;
							var google_conversion_language = "en";
							var google_conversion_format = "3";
							var google_conversion_color = "666666";
							var google_conversion_label = "g1tmCPnc4gIQv6vbzwM";
							var google_conversion_value = 0;
							/* ]]> */
							</script>
							<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js">
							</script>
							<noscript>
							<div style="display:inline;">
							<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/972477887/?label=g1tmCPnc4gIQv6vbzwM&amp;guid=ON&amp;script=0"/>
							</div>
							</noscript>';
						//new may 10
						
						echo '<script type="text/javascript">
							/* <![CDATA[ */
							var google_conversion_id = 972477887;
							var google_conversion_language = "en";
							var google_conversion_format = "3";
							var google_conversion_color = "ffffff";
							var google_conversion_label = "AlKeCPHa6AIQv6vbzwM";
							var google_conversion_value = 0;
							/* ]]> */
							</script>
							<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js">
							</script>
							<noscript>
							<div style="display:inline;">
							<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/972477887/?label=AlKeCPHa6AIQv6vbzwM&amp;guid=ON&amp;script=0"/>
							</div>
							</noscript>';	
								
                        exit();
                }
                else
                {
                        return true;
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Fri, Sep 17th, 2010
|| ####################################################################
\*======================================================================*/
?>