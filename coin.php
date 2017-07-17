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
	'functions',
	'ajax',
	'jquery',
    'modal',
);
// #### load required javascript ###############################################
 
// #### define top header nav ##################################################
$topnavlink = array(
	'main_listings'
);
// #### setup script location ##################################################
define('LOCATION', 'coin-page');
// #### require backend ########################################################
require_once('./functions/config.php');
//error_reporting(E_ALL);
 
$navcrumb = array("$ilpage[merch]" => $ilcrumbs["$ilpage[merch]"]);
// #### decrypt our encrypted url ##############################################
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
$ilance->auction = construct_object('api.auction');
 
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
			SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(p.date_starts) AS start
			FROM " . DB_PREFIX . "projects p
			WHERE p.project_id = '" . intval($ilance->GPC['id']) . "'
			    AND p.project_state = 'product'
			    " . (($ilconfig['globalauctionsettings_payperpost'])
				 ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))"
				 : "AND p.visible = '1'") . "
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        //suku norder
						$alt_no = $res['alt_no'];
						if(($res['norder']) == 1)
						{
							$show['norder_list'] = '1'; 
						}
                        $auction_start = $res['start'];
                         
                       // ($apihook = $ilance->api('merch_detailed_start')) ? eval($apihook) : false;
                        
                        $views = $res['views'];
                        
                        // #### bid increments in this category ################
                        $show['increments'] = false;
                        $increment = '';
                        $cbid = $ilance->bid->fetch_current_bid($res['project_id'], $noformat = 1);
					   
						
                        //$incrementgroup = $ilance->categories->incrementgroup($res['cid']);
						//there is only one bid increment group so over riding the check logic
						$incrementgroup='default';
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
			//$paymentmethods = print_payment_methods($res['project_id']);
			
			
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
	 
			 
			  
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects SET views = views + 1 WHERE project_id = '" . intval($ilance->GPC['id']) . "' AND status != 'draft' AND status!='expired'", 0, null, __FILE__, __LINE__);
			 
			
			$row_count = 0;
	    
			$date_starts = print_date($res['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
            
			// additional auction information
			$additional_info = stripslashes($res['additional_info']);
			
			$show['livebid'] = false;
			$show['bidderuninvited'] = false;
          
			if ($res['filtered_auctiontype'] == 'fixed')
			{
				$auctiontype = 'fixed';
				if(isset($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid']>0)
				{
				$sel_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
												WHERE user_id = '".$_SESSION['ilancedata']['user']['userid']."'
												AND projectid = '".$ilance->GPC['id']."'", 0, null, __FILE__, __LINE__);
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
			}
			else if ($res['filtered_auctiontype'] == 'regular')
			{
				$auctiontype = 'regular';
				// murugan changes on aug 06 for transaction status message
                //$transactionstatus = $ilance->bid->fetch_transaction_status(intval($ilance->GPC['id']));
				if(isset($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid']>0)
				{				
					$sel_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
									WHERE user_id = '".$_SESSION['ilancedata']['user']['userid']."'
									AND projectid = '".$ilance->GPC['id']."'", 0, null, __FILE__, __LINE__);
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
				", 0, null, __FILE__, __LINE__);
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
				", 0, null, __FILE__, __LINE__);
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
				", 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($result_open) > 0)
				{
				while ($orderops = $ilance->db->fetch_array($result_open))
					{
					$total_qty[]=$orderops['qty'];
					$show['buynow_check'] = '1';
					}
				}
				if(isset($total_qty))
				{
					$cou   =   array_sum($total_qty);
					$max_q = $res['max_qty'];
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
				
			}
						
			// #### seller information #############################
			$sql_user_results = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $res['user_id'] . "' and status='active'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_user_results) > 0)
			{
				//$res_project_user  = $ilance->db->fetch_array($sql_user_results);
			}
			else
			{
				print_notice($phrase['_owner_delisted'], $phrase['_sorry_the_owner_of_this_auction_has_been_delisted'], $ilpage['main'], $phrase['_main_menu']);
				exit();
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
			$result = $ilance->db->query("
                                SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added AS bidadded, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.qty, p.project_id, p.escrow_id, p.cid, p.description, p.date_added, p.buynow_qty, p.date_end, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
                                FROM " . DB_PREFIX . "project_bids AS b,
                                " . DB_PREFIX . "projects AS p,
                                " . DB_PREFIX . "users AS u
                                WHERE b.project_id = '" . intval($ilance->GPC['id']) . "'
                                    AND b.project_id = p.project_id
                                    AND u.user_id = b.user_id
                                    AND b.bidstatus != 'declined'
                                    AND b.bidstate != 'retracted'
                                ORDER by b.bidamount DESC, b.bid_id DESC", 0, null, __FILE__, __LINE__);
							$result=$result;	 
                        if ($ilance->db->num_rows($result) > 0)
                        {
								$row_count = 0;
                                while ($resbids = $ilance->db->fetch_array($result, DB_ASSOC))
                                {
								$list_bidderid[$row_count]=$resbids['user_id'];
								$row_count++;
								}
								$bidderid_list=array_reverse(array_unique($list_bidderid));
								$counter=0;
								foreach($bidderid_list as $bidders)
								{
									$seq=$counter+1; 
									$bidder_name_list[$bidders]="Bidder ".$seq;
									$counter++;
								} 
						} 
			 $refreshbidders='';
                        
            //BIDDER DETAILS
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
				$row_count = 0;
					while ($resbids = $ilance->db->fetch_array($result, DB_ASSOC))
					{
					
					if($row_count==0 )
					{
					// murugan changes on jan 31 for bidder name display
					if(isset($_SESSION['ilancedata']['user']['userid']) AND $resbids['user_id']==$_SESSION['ilancedata']['user']['userid'])
						$highbidder = fetch_user('username', $resbids['user_id']);
					else
						//$resbids['provider'] = $bidder_name_list[$resbids['user_id']];
						$highbidder=$bidder_name_list[$resbids['user_id']];
					}
					
					if(isset($_SESSION['ilancedata']['user']['userid']) AND $resbids['user_id']==$_SESSION['ilancedata']['user']['userid'])
						$resbids['provider'] = fetch_user('username', $resbids['user_id']);
					else
						$resbids['provider'] = $bidder_name_list[$resbids['user_id']];
								
						// date of placed bid
						$resbids['bid_datetime'] = print_date($resbids['bidadded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						
						 
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
						//$resbids['awarded'] = print_username($resbids['user_id'], 'custom', 0, '', '', fetch_user('serviceawards', $resbids['user_id']) . ' ' . $phrase['_awards']);
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
                            
							    $users_list[]=$resbids['user_id'];                
                               $refreshbidders .= '
                                        <tr class="' . $resbids['class'] . '" valign="top"> 
                                              <td nowrap="nowrap"><span style="float:right">' . $resbids['award'] . '</span><div><span style="font-family: arial; font-weight:' . ($row_count == 1 ? 'bold' : 'normal') . '; font-size:' . ($row_count == 1 ? '13px' : '13px') . '"" class="blue">' . $resbids['provider'] . '</span></div></td>
                                              <td nowrap="nowrap"><div style="font-weight:' . ($row_count == 1 ? 'bold' : 'normal') . '; font-size:' . ($row_count == 1 ? '14px' : '13px') . '">' . $resbids['bidamount'] . '</div></td>
                                              <td nowrap="nowrap"><div style="font-weight:' . ($row_count == 1 ? 'bold' : 'normal') . '; font-size:' . ($row_count == 1 ? '13px' : '13px') . '">' . $resbids['bid_datetime'] . '</div></td>
                                        </tr>';                 
						$bid_results_rows[] = $resbids;
						$row_count++;
					}
				}
				else
				{
					$show['no_bid_rows_returned'] = true;
				}  
			
			 
			if(isset($users_list) AND is_array($users_list) AND count($users_list)>0)
			{
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
			}	    
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
					 
					$bids = $row['bids'];
					// fetch highest bidder info 
					 
					
                                        
					$cid = $row['cid'];
					/*if ($row['bid_details'] == 'open')
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
					}*/
					 
					
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
                                        //$winningbidderid = $ilance->bid->fetch_highest_bidder(intval($ilance->GPC['id']));
										 $winningbidderid = fetch_highest_bidder(intval($ilance->GPC['id']));
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
					
					 
						if (empty($_SESSION['ilancedata']['user']['userid']))
						{
							$placeabid = '';
						}
					 
					
				 
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
                        
			/*$auctionridcode = fetch_user('rid', intval($owner_id));*/
			
			// purchase now logic
			if (isset($show['buynow_available']) AND $show['buynow_available'] AND isset($amount) AND $amount > 0)
			{
				// is there a highest bid placed?
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
			
// prevent the top cats in breadcrumb to contain any fields from this form
$show['nourlbit'] = true;
$navcrumb = array();
//suku
$cat_details=$ilance->categories_parser->fetch_coin_class(0,0,$cid);
$series_details=$ilance->categories_parser->fetch_coin_series(0,$cat_details[0]['coin_series_unique_no']);
$denomination_detail=$ilance->categories_parser->fetch_denominations($series_details['coin_series_denomination_no']);
								
								
//sekar works on back to search on july 28
								
     $denom = isset($_SESSION['ilancedata']['user']['denomin'])?$_SESSION['ilancedata']['user']['denomin']:0;
	 $ser = isset($_SESSION['ilancedata']['user']['search'])?$_SESSION['ilancedata']['user']['search']:'';
			if($ser == '')
			{
		
			 $dd =  "<a href=\"".$denom."\">Back to Search/Browse Page</a>";
			}
			else
			{
			  $dd  = "<a href=\"".$ser."\">Back to Search/Browse Page</a>";
			}
			$navcrumb=array();
		 
		   if ($res['status'] == 'open')
		   {
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				  $nav_url=HTTP_SERVER .'Denomination'.'/'.$denomination_detail['denomination_unique_no'].'/'.construct_seo_url_name($denomination_detail['denomination_long']);
			}
			else
			{
			    $nav_url=$ilpage['denomination'].'?denomination='.$denomination_detail['denomination_unique_no'];
			}					
			$navcrumb[$nav_url] = $denomination_detail['denomination_long'];

			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$nav_url=HTTP_SERVER .'Series'.'/'.$series_details['coin_series_unique_no'].'/'.construct_seo_url_name($series_details['coin_series_name']);
								
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
				$nav_url=HTTP_SERVER .'CoinPrices'.'/'.$denomination_detail['denomination_unique_no'].'/'.construct_seo_url_name($denomination_detail['denomination_long']);
			}
			else
			{
			    $nav_url=$ilpage['denomination'].'?denomination='.$denomination_detail['denomination_unique_no'].'&ended=1';
			}					
			$navcrumb[$nav_url] = $denomination_detail['denomination_long'];
			
			
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$nav_url=HTTP_SERVER .'CoinPrices'.'/'.'SeriesCoin'.'/'.$series_details['coin_series_unique_no'].'/'.construct_seo_url_name($series_details['coin_series_name']);
								
			}
			else
			{
			    $nav_url=$ilpage['search'].'?mode=product&series="'.$series_details['coin_series_unique_no'].'"='.$series_details['coin_series_name'].'&ended=1';
			}	
			
			
			
			$navcrumb[$nav_url] = $series_details['coin_series_name'];
			$navcrumb["project_title"] = $project_title;
			
			$navcrumb[""] = $dd;
		}						
      
  
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
				$show['is_winner'] = $ilance->bid->is_winner($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['id']));
			}
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
                        
 
                   
			$show['categoryuseproxybid'] = true;
			$show['startprice'] = $show['currentbid'] = 0;
                        
                        // starting bid price
			$startprice = $ilance->currency->format($res['startprice'], $res['currencyid']);
			
			if ($ilance->bid->has_bids($res['project_id']) > 0)
			{
				$show['currentbid'] = 1;
				
				// current bid amount display
				$currentbid = '<strong>' . $ilance->currency->format($cbid, $res['currencyid']) . '</strong>';
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
						{
						$proxybit = '<span class="green">'.$phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $res['currencyid']) . '</span>';
						$winner_replace = '<table cellspacing="0" cellpadding="0" border="0" width="100%" style="padding: 0px 2px 15px 0px;"><tbody><tr><td><div class="grayborder"><div class="n"><div class="e"><div class="w"></div></div></div><div><table cellspacing="0" cellpadding="0" border="0"><tbody><tr><td valign="top"></td><td><img height="1" border="0" width="5" id="" alt="" src="'. $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'].'spacer.gif"></td><td style="padding-right: 5px; padding-left: 3px;"><div><img border="0" id="" alt="" src="'. $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'].'icons/checkmark.gif"><strong>Congratulations! You are currently the high bidder for this auction!</strong></div><div style="padding-top: 4px;" class="black">However, another bidder might place a higher bid.  Please check your Watchlist regularly.  This item has been added to your Watchlist.</div></td></tr></tbody></table></div><div class="s"><div class="e"><div class="w"></div></div></div></div></td></tr></tbody></table>';
						}
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
                        


// page url
$pageurl = urlencode($ilpage['merch'] . '?id=' . $res['project_id']);
 
// update category view count
add_category_viewcount($cid);
			
			$show['localpickuponly'] = false;
	//$onload .= (isset($show['ended']) AND $show['ended']) ? '' : 'refresh_item_details(\'' . $auctiontype . '\'); ';
				 
			 
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
//-->
</script>';
			// #### item watchlist logic ###########################
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
				$ilance->watchlist = construct_object('api.watchlist');
				$show['addedtowatchlist'] = $ilance->watchlist->is_listing_added_to_watchlist($res['project_id']);
//				$show['selleraddedtowatchlist'] = $ilance->watchlist->is_seller_added_to_watchlist($res['user_id']);
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
			
 
//nov 04 bug id 1003 - Change to merch.php - SEO related... Title, Description, Keywords
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
			
		 
			 
			 
			
				$attachment_query = $ilance->db->query("
                        SELECT filehash
                        FROM " . DB_PREFIX . "attachment 
                        WHERE project_id = '" .$ilance->GPC['id'] . "'
						AND visible = '1'
						ORDER BY 
						cast(SUBSTR(filename from LOCATE('-',filename)+1 for LOCATE('.',filename)-LOCATE('-',filename)-1) as UNSIGNED)", 0, null, __FILE__, __LINE__);
				 $k = 0;
				if ($ilance->db->num_rows($attachment_query) > 0)
				{
				$newheadthumb = '';	
				$newthumb = '<table   cellpadding="20" >';	
			    while ($rowt = $ilance->db->fetch_array($attachment_query)) 
				{
					$profile_slidqft = HTTPS_SERVER . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $rowt['filehash'] .'&w=268';
					$profile_sl = HTTPS_SERVER . $ilpage['attachment'] . '?id=' . $rowt['filehash'] .'';
					$profile_slides[]= $profile_slidqft;
					$kk[] =$profile_sl;
					if($profile_slides[$k] == $profile_slides['0'] )
					{
						$newhead = '<a id="thumb1" href="'.$kk[$k].'" class="highslide" onclick="return hs.expand(this)" ><img class="rounded" alt="" src="'.$profile_slides['0'].'" /></a><div class="highslide-caption" align="center">For a larger image, click on lower right hand corner.</div>'	 ;
					}
					 if($k%6==0)
					 {
					 $newthumb.='</tr><tr>';
					 }
					$newthumb.= '<td><a id="thumb1" href="'.$kk[$k].'" class="highslide" onclick="return hs.expand(this)"><img  class="rounded" src="' .HTTPS_SERVER. $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $rowt['filehash'] .'&w=170&h=140"/></a><div class="highslide-caption" align="center">For a larger image, click on lower right hand corner.</div></td>';
					$myslide[]= $newheadthumb;
					$k++;
				}
				$newthumb.= '</table>';
		        }
				else
				{
				$uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
				$newhead ='<center><img src="images/gc/nophoto.gif" style="padding: 10px;" ></center>';
				}
				
				//item info
				$itinfo = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "coins c,
					         " . DB_PREFIX . "grading_service g
						
                        WHERE  c.coin_id = '" .$ilance->GPC['id'] . "'
						
						      ", 0, null, __FILE__, __LINE__);
							 
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
					       
                        WHERE  value = '" .$row_info['Grade'] . "'", 0, null, __FILE__, __LINE__);
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
				
				 $pro_tes1= fetch_cat('coin_detail_suffix',$res['cid']);
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
						
						      ", 0, null, __FILE__, __LINE__);
							 
				$row_cid = $ilance->db->fetch_array($itinfo_cid);
				
				if($row_cid['coin_detail_mintage'] != '')
				{
				$show['coin_detail_mintage'] = '1';
				}
				$info_cid[] = $row_cid;
 
				
				
			   // $currency = $ilance->currency->currencies[$res['currencyid']]['symbol_left'];
			
				 
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
				if(isset($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid']>0)
				{
				 
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
					
					 $bid_det = fetch_auction('haswinner',$project_id);
					
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
				$sql_dai = $ilance->db->query("
				SELECT dd.offer_amt,dd.live_date,pp.buynow_price,pp.status FROM
				" . DB_PREFIX . "dailydeal dd," . DB_PREFIX . "projects pp
				WHERE dd.project_id = pp.project_id AND  dd.project_id = '".$project_id."' AND dd.live_date = '".DATETODAY."' group by dd.project_id", 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($sql_dai) > 0)
				{
					$show['daily']='1';
					$line_daily_deal=$ilance->db->fetch_array($sql_dai); 
					if($line_daily_deal['status']=='open')
					{
						$offamt = $line_daily_deal['offer_amt'];
						$rrr = $offamt+$line_daily_deal['buynow_price'];
						$tt_est = 'Currently Discounted: 24-Hour Deal. Was $'.$rrr.'. Save $'.$offamt.'!';
						
						$date_exp = explode('-',$line_daily_deal['live_date']);
					  
						$mydate = date("F j, Y", mktime(0, 0, 0, $date_exp['1'], $date_exp['2'], $date_exp['0']));
					  
						$daily = 'This item will be featured as a 24-Hour Deal on '.$mydate.'';
						
					}else
					{
						$list='<div style="color:#FF0000; font-weight:bold;">24-Hour Deal is now Sold Out.</div>';
					}
				}

				else
				{
					$tt_est = '';
				}
 //print_r($show);
 
                        	$pprint_array = array('refreshbidders','bids','winner_replace','highbidder','tt_est','newthumb','daily','soldprice','hidden_val','pageurlpath','twitpath','facepath','mesa','mess','disab','population_guide','price_guide_info','watch_list_note','no_tracking_users','alt_no','totalvalue','dismsg','vel','trva','newhead','currency','proxybit2','shipperid','featured','featured_date','highlite','bold','autorelist','enhancements','transactionstatus','winningbid','winningbidder','min_bidamountformatted','min_bidamount','date_starts','additional_info','views','buynow_qty','proxybit','currentbid','startprice','qty_pulldown','cid','reserve_met','buynow_price','buynow_price_plain','timeleft','ends','bids','collapserfpinfo_id','project_user_id','filter_permissions','project_title','description','project_id','login_include','headinclude','onload','area_title','page_title','https_server','http_server','pro_tes1','list','sold_text');		
			
			//($apihook = $ilance->api('merch_detailed_end')) ? eval($apihook) : false;
			
            $ilance->template->fetch('main', 'coin_auction.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		 
			 $ilance->template->parse_loop('main', array('bid_results_rows','info_val','info_cid' ));
			
			//($apihook = $ilance->api('merch_detailed_loop')) ? eval($apihook) : false;
			
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
 
function fetch_highest_bidder($projectid = 0 )
        {
                global $ilance, $myapi, $ilconfig;

                
                        
                        if ($ilconfig['productbid_enableproxybid'] )
                        {
                                $highbid = $ilance->db->query("
                                        SELECT b.user_id
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "proxybid AS p
                                        WHERE b.project_id = '" . intval($projectid) . "'
                                            AND b.project_id = p.project_id
                                            AND b.user_id = p.user_id
                                        ORDER BY b.bidamount DESC,b.bid_id DESC
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($highbid) > 0)
                                {
                                        $res = $ilance->db->fetch_array($highbid);
                                        return $res['user_id'];
                                }
                                else
                                {
                                        $highbid = $ilance->db->query("
                                                SELECT user_id
                                                FROM " . DB_PREFIX . "project_bids
                                                WHERE project_id = '" . intval($projectid) . "'
                                                ORDER BY bidamount DESC, date_added ASC
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($highbid) > 0)
                                        {
                                                $res = $ilance->db->fetch_array($highbid);
                                                return $res['user_id'];
                                        }
                                        else
                                        {
                                                return 0;
                                        }
                                }
                        }
                        else
                        {
                                $highbid = $ilance->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "project_bids
                                        WHERE project_id = '" . intval($projectid) . "'
                                        ORDER BY bidamount DESC, date_added ASC
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($highbid) > 0)
                                {
                                        $res = $ilance->db->fetch_array($highbid);
                                        return $res['user_id'];
                                }
                                else
                                {
                                        return 0;
                                }
                        }        
                 

        }
         
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>