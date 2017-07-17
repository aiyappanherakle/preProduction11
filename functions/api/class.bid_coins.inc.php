<?php

class bid_coins extends bid
{


 function multiple_placebid($highbidnotify = 0, $lasthournotify = 0, $subscribed = 0, $id = 0, $project_user_id = 0, $bidamount = 0, $qty = 1, $bidderid = 0, $isproxy, $minimumbid, $reserveprice, $showerrormessages = true, $buyershipcost = 0, $buyershipperid = 0)
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
                                                date_added = '" . DATETIME24H . "'
                                                WHERE user_id = '" . intval($bidderid) . "'
                                                    AND project_id = '" . intval($id) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
										
		
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
                                        '" . DATETIME24H . "')
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
                                        
                                        
					// #### reserve price disabled #########
										if ($reserveprice == '0')
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
                        '" . DATETIME24H . "',
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
                        '" . DATETIME24H . "',
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

				$bidends = print_date(fetch_auction('date_end',$id), '%A, %B %d, %Y %I:%M:%S %p', 0, 0).' (Pacific Time)';
				$sqlnew = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "proxybid
                                WHERE user_id = '" . intval($bidderid) . "'
                                    AND project_id = '" . intval($id) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
						
				$resnew = $ilance->db->fetch_array($sqlnew);
				

				
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
                
		return $emails;
                
        }
}

?>