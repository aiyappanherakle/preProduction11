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

/**
* MyCP class to perform the majority of functions found on the MyCP Control Panel Dashboard
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class mycp
{
	/*
	* Function to fetch the referral activity menu block
	*
	* @param      integer      user id
	* @param      integer      days ago range (default 1 year)
	* @param      integer      result limit (default 5)
	*
	* @return     string       Returns HTML formatted details of referral activity   
	*/
	function referal_activity($userid = 0, $daysago = 365, $userlimit = 5)
	{
		global $ilance, $myapi, $ilpage, $phrase, $ilconfig;
		
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "referral_data
			WHERE referred_by = '" . intval($userid) . "'
			ORDER BY date DESC
			LIMIT $userlimit
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$htmlbit = $html = $sep = '';
			$count = 0;
			while ($row = $ilance->db->fetch_array($sql))
			{
				$count++;
				$htmlbit .= '<hr size="1" width="100%" style="color:#cccccc" /><div style="padding-top:9px; padding-bottom:9px"><span style="float:left; padding-right:5px"><a href="javascript:void(0)" onclick="return toggle(\'ref' . $row['id'] . '\');"><div style="padding-right:5px"><img id="collapseimg_ref' . $row['id'] . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'expand_collapsed.gif" border="0" alt="" /></div></a></span><strong>' . fetch_user('username', $row['user_id']) . '</strong> <span class="smaller gray">&nbsp;&nbsp;&nbsp;( ' . $phrase['_date'] . ': ' . print_date($row['date'], '%d-%b-%Y', 0, 0) . ' )</span></div>';
				$htmlbit .= '<div id="collapseobj_ref' . $row['id'] . '" style="display:none">';
				
				// #### valid listing posting ##################
				$htmlbit .= '<div>';
				if ($row['postauction'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= $phrase['_posted_any_valid_auction'] . '</div>';
				
				// #### awarded a valid bid ####################
				$htmlbit .= '<div>';
				if ($row['awardauction'] > 0)
				{
					 $htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';	
				}
				$htmlbit .= $phrase['_awarded_any_valid_bid'] . '</div>';
				
				$htmlbit .= '<div>';
				if ($row['paysubscription'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= $phrase['_paid_subscription'] . '</div>';
				
				$htmlbit .= '<div>';
				if ($row['payfvf'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= $phrase['_paid_final_value_fee'] . '</div>';
				
				$htmlbit .= '<div>';
				if ($row['payins'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= $phrase['_paid_insertion_fee'] . '</div>';
				
				$htmlbit .= '<div>';
				if ($row['paylanceads'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= $phrase['_paid_campaign_fee'] . '</div>';
				
				$htmlbit .= '<div>';
				if ($row['payportfolio'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';	
				}
				$htmlbit .= $phrase['_paid_portfolio_fee'] . '</div>';
				
				$htmlbit .= '<div>';
				if ($row['paycredentials'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= $phrase['_paid_any_credential_fee'] . '</div>';
				
				$htmlbit .= '<div>';
				if ($row['payenhancements'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= $phrase['_paid_auction_upsell_fee'] . '</div>';
				$htmlbit .= '</div>';
			}
			
			$html .= $sep . '<strong>' . $count . '</strong> ' . mb_strtolower($phrase['_referrals_found']) . ' ' . $phrase['_for'] . ' <span class="blue">' . $_SESSION['ilancedata']['user']['ridcode'] . '</span>' . $htmlbit;
		}
		else
		{
			$html = $phrase['_no_results_found'];
		}
		
		return $html;
	}
    
	/*
	* Function to fetch the invitation activity for a particular bidder
	*
	* @param      integer      user id
	* @param      integer      days ago range (default 7 days)   
	*
	* @return     string       Returns HTML formatted details of invitation activity    
	*/
	function invitation_activity($userid = 0, $daysago = 7)
	{
		global $ilance, $myapi, $ilpage, $phrase, $ilconfig;
		
		$html = '';
		
		$sql = $ilance->db->query("
			SELECT p.project_id, p.project_title, p.bids, p.status
			FROM " . DB_PREFIX . "projects AS p,
			" . DB_PREFIX . "project_invitations as i
			WHERE i.project_id = p.project_id
			    AND i.seller_user_id = '" . intval($userid) . "'
			    AND p.project_state = 'service'
			    AND i.bid_placed = 'no'
			    AND p.status = 'open'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$invites = $ilance->db->num_rows($sql);
			$html = '<div><span class="blue"><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited#servicebidding">' . $phrase['_you_currently_have'] . ' ' . $invites . '</a></span> ' . $phrase['_buyers_waiting_for_you_to_place_a_bid_on_their_auctions'] . '</div>';
		}
		else
		{
			$html = '<div class="gray">' . $phrase['_you_have_not_been_invited_to_any_recent_auctions'] . '</div>';
		}
		
		$html .= '<div style="padding-top:6px" class="smaller gray"><span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=profile">' . $phrase['_promote_profile_via_verifications'] . '</a></span> ' . (($ilconfig['portfoliodisplay_enabled']) ? '&nbsp;&nbsp;|&nbsp;&nbsp; <span class="blue"><a href="' . HTTP_SERVER . $ilpage['portfolio'] . '?cmd=management">' . $phrase['_feature_items_in_your_portfolio'] . '</a></span>' : '') . '</div>';
		
		return $html;
	}
	
	/*
	* Function to fetch the unread message count for a particular member
	*
	* @param      integer      user id
	*
	* @return     string       Returns HTML formatted details of the unread message count   
	*/
	function fetch_unread_messages($userid = 0)
	{
		global $ilance, $myapi, $ilpage, $phrase, $ilconfig;
		
		$sql = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "pmb_alerts
			WHERE to_id = '" . intval($userid) . "'
			    AND to_status = 'new'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$unreadcount = $ilance->db->num_rows($sql);
			$html = '<div>' . $phrase['_you_currently_have'] . ' <span class="blue"><a href="' . $ilpage['messages'] . '"><strong>' . $unreadcount . ' ' . $phrase['_unread'] . '</strong></a></span> ' . $phrase['_messages_waiting_in_your_inbox'] . '</div>';
		}
		else
		{
			$html = '<div class="gray">' . $phrase['_you_currently_have_no_new_messages_waiting'] . '</div>';
		}
		
		return $html;
	}
    
	/*
	* Function to fetch the scheduled transactions for a particular member
	*
	* @param      integer      user id
	* @param      integer      days ago range (default 7 days)        
	*
	* @return     string       Returns HTML formatted details of scheduled transactions up and coming      
	*/
	function scheduled_transactions($userid = 0, $daysago = 7)
	{
		global $ilance, $myapi, $ilpage, $phrase;
		
		$sql = $ilance->db->query("
			SELECT invoiceid, description, amount, currency_id
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . intval($userid) . "'
			    AND status = 'scheduled'
			ORDER BY invoiceid DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$html = '';
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC));
			{
				$html .= '<div>' . $phrase['_scheduled_transaction'] . ': <a href="' . $ilpage['invoicepayment'] . '?id=' . $res['invoiceid'] . '">' . $phrase['_invoice_id'] . ' (' . $res['invoiceid'] . ')</a> - ' . stripslashes($res['description']) . ' - ' . $ilance->currency->format($res['amount'], $res['currency_id']) . '.</div>';
			}
		}
		else
		{
			$html = $phrase['_no_scheduled_transactions_have_been_recorded'];
		}
		
		return $html;
	}
    
	/*
	* Function to fetch any unpaid transactions for a particular member
	*
	* @param      integer      user id
	*
	* @return     string       Returns HTML formatted details of unpaid transactions up and coming      
	*/
	function unpaid_transactions($userid = 0)
	{
		global $ilance, $myapi, $ilpage, $phrase;
		
		$html = '';
		$sql = $ilance->db->query("
			SELECT invoiceid, description
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . intval($userid) . "'
			    AND status = 'unpaid'
			    AND invoicetype != 'escrow'
			    AND isdeposit = '0'
			    AND iswithdraw = '0'
			ORDER BY invoiceid DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$html .= '<strong>'.$phrase['_unpaid'].'</strong><br />';
			while ($res3 = $ilance->db->fetch_array($sql))
			{
				$html .= '<a href="' . $ilpage['invoicepayment'] . '?id=' . $res3['invoiceid'] . '"><span style="color:#990000"><strong>' . $phrase['_invoice_id'] . ' (' . $res3['invoiceid'] . ')</strong></span></a> - ' . stripslashes($res3['description']) . '<br />';
			}
		}
		else
		{
			$html = '<strong>' . $phrase['_unpaid'] . '</strong><br />' . $phrase['_no_unpaid_transactions_have_been_recorded'];
		}
		
		return $html;
	}
    
	/*
	* Function to fetch and print the accounting activity block for a particular member
	*
	* @param      integer      user id
	*
	* @return     string       Returns HTML formatted details of accounting information
	*/
	function accounting_activity($userid = 0)
	{
		global $ilance, $myapi, $ilpage, $phrase;
		
		$html = '';
		$sql = $ilance->db->query("
			SELECT invoiceid, description
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . intval($userid) . "'
			ORDER BY invoiceid DESC
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			$html = '<div><strong>' . $phrase['_last_transaction_recorded'] . '</strong><br /><a href="' . $ilpage['invoicepayment'] . '?id=' . $res['invoiceid'] . '" title="' . stripslashes($res['description']) . '"><strong>' . $phrase['_invoice_id'] . ' (' . $res['invoiceid'] . ')</strong></a> - ' . stripslashes($res['description']) . '</div>';
			$html .= '<hr size="1" width="100%">';
		}
		else
		{
			$html = '<strong>' . $phrase['_last_transaction_recorded'] . '</strong><br />' . $phrase['_no_transactions_have_been_recorded'];
			$html .= '<hr size="1" width="100%">';
		}
		
		$html .= $this->unpaid_transactions($userid);
		
		return $html;
	}
    
	/*
	* Function to fetch any related escrow activity and notifications for the currently logged in member
	* Function now groups all escrow activity for a more general overview without dashboard clutter.
	*
	* @param      integer       user id
	* @param      string        viewing type (default buyer)
	* @param      integer       days ago range (default 7)
	*
	* @return     string       Returns HTML formatted details of escrow activity or actions required by member
	*/
	function escrow_activity($userid = 0, $viewtype = 'buying', $daysago = 7)
	{
		global $ilance, $myapi, $ilpage, $phrase, $ilconfig;
		
		$type = ($viewtype == 'buying') ? 'service' : 'product';
		$html = '';
		
		if ($ilconfig['escrowsystem_enabled'])
		{
			switch ($viewtype)
			{
				case 'buying':
				{
					// #### BUYING SERVICES ESCROW NOTIFICATIONS ###################
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
					{
						// AS A BUYER: do we need to PAY any escrow account to any service providers?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.project_user_id = '" . intval($userid) . "'
								AND e.status = 'pending'
								AND e.project_id = p.project_id
								AND p.project_state = 'service'
							ORDER BY e.escrow_id DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_x_providers_waiting_for_you_to_fund_escrow'], $count) . '&nbsp;&nbsp;<span class="smaller gray">( <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow">' . $phrase['_pay_now'] . '</a></span> )</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);

						
						// #### BUYING SERVICES ESCROW NOTIFICATIONS ###################
						// AS A BUYER: do we need to RELEASE any funds in escrow to a service provider?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.project_user_id = '" . intval($userid) . "'
								AND e.status = 'confirmed'
								AND e.project_id = p.project_id
								AND p.project_state = 'service'
							ORDER BY e.escrow_id DESC", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_x_providers_waiting_for_release_of_escrow_funds'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
					}
					
					
					// #### BUYING PRODUCTS ESCROW NOTIFICATIONS ###################
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						// AS A BUYER: do we need to PAY any seller escrow account?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.user_id = '" . intval($userid) . "'
								AND e.status = 'pending'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
							ORDER BY e.escrow_id DESC", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_x_buyers_waiting_for_you_to_fund_escrow'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow">' . $phrase['_pay_now'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						
						// #### BUYING PRODUCTS ESCROW NOTIFICATIONS ###################
						// AS A BUYER: do we need to RELEASE any funds in escrow to a seller?
						$count = 0;
						$sql5 = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.user_id = '" . intval($userid) . "'
								AND e.status = 'confirmed'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
							ORDER BY e.escrow_id DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql5) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql5))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_x_sellers_waiting_for_release_of_escrow_funds'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						
						// #### BUYING ITEMS VIA PURCHASE NOW - ESCROW NOTIFICATIONS ##
						// AS A BUYER (we've already paid the cost): do we need to HOUND any merchants for delivery status?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.orderid, e.project_id, p.project_title
							FROM " . DB_PREFIX . "buynow_orders AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.buyer_id = '" . intval($userid) . "'
								AND e.status = 'paid'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
							ORDER BY e.orderid DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_waiting_on_x_sellers_to_update_my_delivery_status'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						
						// #### BUYING ITEMS VIA REGULAR ESCROW
						// AS A BUYER (we've already paid the cost): do we need to HOUND any sellers for delivery status?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.user_id = '" . intval($userid) . "'
								AND e.status = 'started'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
							ORDER BY e.escrow_id DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_x_buyers_waiting_for_a_delivery_update'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						
						// #### BUYING ITEMS VIA PURCHASE NOW - ESCROW NOTIFICATIONS ##
						// AS A BUYER (already paid, and assumed shipped): do we need to RELEASE any funds in escrow to merchants?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.orderid, e.project_id, p.project_title
							FROM " . DB_PREFIX . "buynow_orders as e,
							" . DB_PREFIX . "projects as p
							WHERE e.buyer_id = '" . intval($userid) . "'
								AND e.status = 'pending_delivery'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
							ORDER BY e.orderid DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_x_sellers_waiting_for_release_of_escrow_funds'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						break;
					}
				}				
				case 'selling':
				{
					// #### SELLING SERVICES ESCROW NOTIFICATIONS ##################
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
					{
						// AS A PROVIDER: are we waiting for buyers to release any funds to us as a service provider?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow as e,
							" . DB_PREFIX . "projects as p
							WHERE e.user_id = '" . intval($userid) . "'
								AND e.status = 'confirmed'
								AND e.project_id = p.project_id
								AND p.project_state = 'service'
							ORDER BY e.escrow_id DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_waiting_for_x_buyers_to_release_escrow_funds'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						
						// #### SELLING SERVICES ESCROW NOTIFICATIONS ##################
						// AS A PROVIDER: did any buyers release funds to finish the project *within last 7 days?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow as e,
							" . DB_PREFIX . "projects as p
							WHERE e.user_id = '" . intval($userid) . "'
								AND e.status = 'finished'
								AND e.project_id = p.project_id
								AND p.project_state = 'service'
								AND e.date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL 7 DAY)
							ORDER BY e.escrow_id DESC", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_received_funds_from_x_buyers_into_my_account_balance'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
					}
					
					// #### SELLING PRODUCTS ESCROW NOTIFICATIONS ##################
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						// AS A MERCHANT: are we waiting for bidders to release any funds to us as a merchant?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow as e,
							" . DB_PREFIX . "projects as p
							WHERE e.project_user_id = '" . intval($userid) . "'
								AND e.status = 'confirmed'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
							ORDER BY e.escrow_id DESC", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_waiting_for_x_buyers_to_release_escrow_funds'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						
						// #### SELLING PRODUCTS ESCROW NOTIFICATIONS ##################
						// AS A MERCHANT: did any bidders release funds to finish the escrow purchase *within last 7 days?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow as e,
							" . DB_PREFIX . "projects as p
							WHERE e.project_user_id = '" . intval($userid) . "'
								AND e.status = 'finished'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
								AND e.date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL 7 DAY)
							ORDER BY e.escrow_id DESC", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_received_funds_from_x_buyers_into_my_account_balance'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						
						// #### SELLING PRODUCTS ESCROW NOTIFICATIONS ##################
						// AS A MERCHANT: do we need to confirm any shipment of products or downloads via escrow?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow as e,
							" . DB_PREFIX . "projects as p
							WHERE e.project_user_id = '" . intval($userid) . "'
								AND e.status = 'started'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
							ORDER BY e.escrow_id DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_x_buyers_waiting_for_a_delivery_update'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						
						// #### SELLING ITEMS VIA PURCHASE NOW - ESCROW NOTIFICATIONS ##
						// AS A PURCHASE NOW MERCHANT (bidder already paid the cost): do we need to set any deliveries to sent/delivered?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.orderid, e.project_id, p.project_title
							FROM " . DB_PREFIX . "buynow_orders as e,
							" . DB_PREFIX . "projects as p
							WHERE e.owner_id = '" . intval($userid) . "'
								AND e.status = 'paid'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
							ORDER BY e.orderid DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								//$html .= '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow.gif" border="0" alt="' . $phrase['_escrow'] . '" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'freeshipping.gif" border="0" alt="" /> ' . $phrase['_item'] . ' <span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . $res['project_title'] . '</a></span> - ' . $phrase['_waiting_for_you_to_update_order_as_delivered'] . ' [ <span class="blue"><a href="' . $ilpage['selling'] . '?cmd=management">' . $phrase['_click_here_to_view_details'] . '</a></span> ]</div><hr size="1" width="100%" style="color:#cccccc" />';
								$count++;
							}
							
							//$html .= $ilance->language->construct_phrase($phrase['_x_buyers_waiting_for_a_delivery_update'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="'.$ilpage['selling'].'?cmd=management">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						
						// #### SELLING ITEMS VIA PURCHASE NOW - ESCROW NOTIFICATIONS ##
						// AS A PURCHASE NOW MERCHANT (already paid, and assumed shipped): do we need to HOUND any bidders for funds in escrow?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.orderid, e.project_id, p.project_title
							FROM " . DB_PREFIX . "buynow_orders as e,
							" . DB_PREFIX . "projects as p
							WHERE e.owner_id = '" . intval($userid) . "'
								AND e.status = 'pending_delivery'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
							ORDER BY e.orderid DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							
							$html .= $ilance->language->construct_phrase($phrase['_waiting_for_x_buyers_to_release_escrow_funds'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
					}
					break;
				}
			}
			
			return $html;
		}
	}
    
	/*
	* Function to fetch any bids or bidding activity and notifications for the currently logged in member
	* Function now groups all bidding activity for a more general overview without dashboard clutter.
	*
	* @param      integer       user id
	* @param      string        viewing type (default buying)
	* @param      integer       days ago range (default 7)
	*
	* @return     string       Returns HTML formatted details of bids activity or actions required by member
	*/
	function bids_activity($userid = 0, $viewtype = 'buying', $daysago = 7)
	{
		global $ilconfig, $ilance, $ilpage, $phrase;
	
		$type = ($viewtype == 'buying') ? 'service' : 'product';
		$html = '';
		$count1 = $count2 = $count3 = 0;
		
		// collect all auctions posted by this member
		$prj = $ilance->db->query("
			SELECT project_details, project_state, project_id, project_title
			FROM " . DB_PREFIX . "projects
			WHERE user_id = '" . intval($userid) . "'
				AND status != 'expired'
				AND status != 'finished'
				AND status != 'approval_accepted'
				AND status != 'archived'
				AND project_state = '" . $type . "'
			ORDER BY date_added DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($prj) > 0)
		{
			while ($projects = $ilance->db->fetch_array($prj))
			{
				if ($projects['project_details'] == 'unique')
				{
					// fetch all bid counts for this unique bid auction in last 7 days
					$sql = $ilance->db->query("
						SELECT COUNT(*) AS count
						FROM " . DB_PREFIX . "projects_uniquebids
						WHERE project_id = '" . $projects['project_id'] . "'
						    AND date >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						if ($res['count'] > 0)
						{
							//$html .= '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="" /> ' . intval($res['count']) . ' ' . $phrase['_new_bids_were_placed_on_your_unique_bid_auction'] . ' - <span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $projects['project_id'] . '">' . stripslashes($projects['project_title']) . '</a></span></div><hr size="1" width="100%" style="color:#cccccc" />';
							$count1 += intval($res['count']);
						}
					}
				}
				else
				{
					// fetch all bid counts for this members listings in last 7 days
					$sql = $ilance->db->query("
						SELECT COUNT(*) AS count
						FROM " . DB_PREFIX . "project_bids
						WHERE project_id = '" . $projects['project_id'] . "'
							AND date_added >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
							AND bidstatus != 'declined'
							AND bidstate != 'retracted'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						switch ($type)
						{
							case 'service':
							{
								if ($res['count'] > 0)
								{
									//$html .= '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="" /> ' . intval($res['count']) . ' ' . $phrase['_new_bids_were_placed_on_your_service_auction'] . ' <span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $projects['project_id'] . '">' . stripslashes($projects['project_title']) . '</a></span></div><hr size="1" width="100%" style="color:#cccccc" />';
									$count2 += intval($res['count']);
								}
								break;
							}							
							case 'product':
							{
								if ($res['count'] > 0)
								{
									//$html .= '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="" /> ' . intval($res['count']) . ' ' . $phrase['_new_bids_were_placed_on_your_product_auction'] . ' <span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $projects['project_id'] . '">' . stripslashes($projects['project_title']) . '</a></span></div><hr size="1" width="100%" style="color:#cccccc" />';
									$count3 += intval($res['count']);
								}
								break;
							}
						}
					}
				}
			}
			
			if ($count1 > 0)
			{
				$html .= $ilance->language->construct_phrase($phrase['_x_unique_bids_on_items_youre_selling'], $count1) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;orderby=bids,buynow_purchases&amp;displayorder=desc">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
			}
			if ($count2 > 0)
			{
				$html .= $ilance->language->construct_phrase($phrase['_x_bids_placed_on_services_youre_buying'], $count2) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;orderby=bids&amp;displayorder=desc">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';	
			}
			if ($count3 > 0)
			{
				$html .= $ilance->language->construct_phrase($phrase['_x_bids_placed_on_items_youre_selling'], $count3) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;orderby=bids,buynow_purchases&amp;displayorder=desc">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';	
			}
		}
		
		return $html;
	}
	
	/*
	* Function to fetch any bids award activity and notifications for the currently logged in member.
	* Function now groups all activity for a more general overview without dashboard clutter.
	*
	* @param      integer       user id
	* @param      string        viewing type (buying or selling)
	* @param      string        category type (service or product)
	* @param      integer       days ago range (default 7)
	*
	* @return     string        Returns HTML formatted details of bidding award activity or actions required by member
	*/
	function bids_award_activity($userid = 0, $viewtype = 'buying', $cattype = 'service', $daysago = 7)
	{
		global $ilance, $myapi, $phrase, $ilconfig, $ilpage;
		
		$html = '';
		$count = $count1 = $count2 = 0;
		
		if ($viewtype == 'buying')
		{
			// #### buying a product as a buyer
			if ($cattype == 'product')
			{
				$sql = $ilance->db->query("
					SELECT bid_id, project_id, bidamount, qty, date_awarded
					FROM " . DB_PREFIX . "project_bids
					WHERE user_id = '" . $userid . "'
						AND state = '" . $cattype . "'
						AND (bidstatus = 'awarded' AND bidstate = 'wait_approval'
							OR bidstatus = 'placed' AND bidstate = 'wait_approval'
							OR bidstatus = 'awarded' AND bidstate = '')
						AND date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$currencyid = fetch_auction('currencyid', $res['project_id']);
						$date = '<strong>' . print_date($res['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0) . '</strong>';
						$cost = '<strong>' . $ilance->currency->format($res['bidamount'], $currencyid) . '</strong>';
						$name = fetch_auction('project_title', $res['project_id']);
						$bidid = $res['bid_id'];
						//$html .= '<div><strong>' . $phrase['_congratulations'] . '</strong> ' . $ilance->language->construct_phrase($phrase['_your_bid_for_x_was_awarded_on_x_for_the_x_x_auction'], array($cost, $date, '<span class="blue"><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded#product">' . $name . '</a></span>', $cattype)) . '</div><hr size="1" width="100%" style="color:#cccccc" />';
						$count1++;
					}
					
					$html .= $ilance->language->construct_phrase($phrase['_congrats_youve_placed_x_winning_bids'], $count1) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded&amp;orderby=bids&amp;displayorder=desc#product">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
				}
				
				// #### lowest unique bid auction ##############
				$sql = $ilance->db->query("
					SELECT u.uid, u.project_id, u.project_user_id, u.user_id, u.uniquebid, u.response, u.date, u.status, u.totalbids, p.currencyid
					FROM " . DB_PREFIX . "projects_uniquebids AS u,
					" . DB_PREFIX . "projects AS p
					WHERE p.project_id = u.project_id
						AND u.user_id = '" . $userid . "'
						AND u.status = 'lowestunique'
						AND u.date > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
						AND p.status != 'open'
						AND p.haswinner = '1'
						AND p.winner_user_id = '" . $userid . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$date = '<strong>' . print_date($res['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0) . '</strong>';
						$cost = '<strong>' . $ilance->currency->format($res['uniquebid'], $res['currencyid']) . '</strong>';
						$name = fetch_auction('project_title', $res['project_id']);
						$bidid = $res['uid'];
						$count2++;
					}
					
					$html .= $ilance->language->construct_phrase($phrase['_congrats_youve_placed_x_winning_lowest_unique_bids'], $count2) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;subcmd=lub&amp;bidsub=awarded&amp;orderby=title&amp;displayorder=asc#product">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
				}
			}
		}
		else if ($viewtype == 'selling')
		{
			// #### selling products as a seller
			if ($cattype == 'product')
			{
				// #### awarded bids from buyers ###############
				$sql2 = $ilance->db->query("
					SELECT bid_id, project_id, bidamount, qty, date_awarded
					FROM " . DB_PREFIX . "project_bids
					WHERE project_user_id = '" . $userid . "'
						AND state = '" . $cattype . "'
						AND (bidstatus = 'awarded' AND bidstate = 'wait_approval'
							OR bidstatus = 'placed' AND bidstate = 'wait_approval'
							OR bidstatus = 'awarded' AND bidstate = '')
						AND date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql2, DB_ASSOC))
					{
						$currencyid = fetch_auction('currencyid', $res['project_id']);
						$date = '<strong>' . print_date($res['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0) . '</strong>';
						$cost = '<strong>' . $ilance->currency->format($res['bidamount'], $currencyid) . '</strong>';
						$name = fetch_auction('project_title', $res['project_id']);
						$bidid = $res['bid_id'];
						//$html .= '<div><strong>' . $phrase['_congratulations'] . '</strong> ' . $phrase['_your_item']. ' <span class="blue"><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold">' . $name . '</a></span> ' . $phrase['_sold_to_the_highest_winning_bidder_for'] . ' ' . $cost . ' ' . $phrase['_on'] . ' ' . $date . '</div><hr size="1" width="100%" style="color:#cccccc" />';
						$count1++;
					}
					
					$html .= $ilance->language->construct_phrase($phrase['_congrats_youve_sold_x_items'], $count1) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold&amp;orderby=bids,buynow_purchases&amp;displayorder=desc">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
				}
				
				// #### lowest unique awarded bids #############
				$sql2 = $ilance->db->query("
					SELECT u.uid, u.project_id, u.project_user_id, u.user_id, u.uniquebid, u.response, u.date, u.status, u.totalbids, p.currencyid
					FROM " . DB_PREFIX . "projects_uniquebids AS u,
					" . DB_PREFIX . "projects AS p
					WHERE p.project_id = u.project_id
						AND u.project_user_id = '" . $userid . "'
						AND u.status = 'lowestunique'
						AND u.date > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
						AND p.status != 'open'
						AND p.haswinner = '1'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql2, DB_ASSOC))
					{
						$date = '<strong>' . print_date($res['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0) . '</strong>';
						$cost = '<strong>' . $ilance->currency->format($res['uniquebid'], $res['currencyid']) . '</strong>';
						$name = fetch_auction('project_title', $res['project_id']);
						$bidid = $res['uid'];
						//$html .= '<div><strong>' . $phrase['_congratulations'] . '</strong> ' . $phrase['_your_item']. ' <span class="blue"><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold">' . $name . '</a></span> ' . $phrase['_sold_to_the_lowest_unique_winning_bidder_for'] . ' ' . $cost . ' ' . $phrase['_on'] . ' ' . $date . '</div><hr size="1" width="100%" style="color:#cccccc" />';
						$count2++;
					}
					
					$html .= $ilance->language->construct_phrase($phrase['_congrats_youve_sold_x_lowest_unique_bid_items'], $count2) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold&amp;orderby=bids,buynow_purchases&amp;displayorder=desc">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
				}
			}
			
			// #### inform a provider that one or more bids have been awarded and waiting for response via accept or reject
			else if ($cattype == 'service')
			{
				$sqlextra = "
					AND (bidstatus = 'awarded' AND bidstate = 'wait_approval'
					  OR bidstatus = 'placed'  AND bidstate = 'wait_approval'
					  OR bidstatus = 'awarded' AND bidstate = '')
				";
				
				$sql2 = $ilance->db->query("
					SELECT bid_id, project_id, bidamount, qty, date_awarded
					FROM " . DB_PREFIX . "project_bids
					WHERE user_id = '" . $userid . "'
						AND state = '" . $cattype . "'
						$sqlextra
						AND date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql2, DB_ASSOC))
					{
						$currencyid = fetch_auction('currencyid', $res['project_id']);
						$date = '<strong>' . print_date($res['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 0) . '</strong>';
						$cost = '<strong>' . $ilance->currency->format($res['bidamount'], $currencyid) . '</strong>';
						$name = fetch_auction('project_title', $res['project_id']);
						$bidid = $res['bid_id'];
						//$html .= '<div>' . $ilance->language->construct_phrase($phrase['_your_bid_for_x_was_awarded_on_x_for_the_x_x_auction'], array($cost, $date, '<span class="blue"><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded#servicebidding">' . $name . '</a></span>', $cattype)) . '</div><hr size="1" width="100%" style="color:#cccccc" />';
						$count++;
					}
					
					$html .= $ilance->language->construct_phrase($phrase['_congrats_x_bid_proposals_have_been_awarded'], $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded&amp;displayorder=desc">' . $phrase['_take_me_there'] . '</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
				}
			}
		}
		
		return $html;
	}
	
	/*
	* Function to fetch any feedback actions or related activity and notifications for the currently logged in member
	* Function now groups all feedback activity for a more general overview letting the user leave multiple feedback at once.
	*
	* @param      integer       user id
	* @param      string        show view type (default all; optional: bought or sold)
	*
	* @return     string        Returns HTML formatted details of feedback activity actions required by member
	*/
	function feedback_activity($userid = 0, $showview = 'all')
	{
		global $ilance, $show, $myapi, $ilpage, $phrase, $ilconfig;
		
		$html = '';
		$final = array();
		$count = 0;
		
		if ($showview == 'all' OR $showview == 'bought')
		{
			// #### AS A BUYER BUYING ITEMS VIA BUY NOW ####################
			// does the buyer need to leave feedback for any seller purchased via buy now?
			$query = $ilance->db->query("
				SELECT project_id, owner_id AS seller_id, buyer_id, orderdate AS enddate
				FROM " . DB_PREFIX . "buynow_orders
				WHERE buyer_id = '" . intval($userid) . "'
					AND buyerfeedback = '0'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$res['project_title'] = fetch_auction('project_title', $res['project_id']);
					$res['project_state'] = 'product';
					$res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = fetch_user('username', $res['seller_id']);
					$res['usertype'] = $phrase['_seller'];
					$res['fromtype'] = 'seller';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
					$res['for_user_id'] = $res['seller_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$GLOBALS['show_stars' . $res['project_id']] = 1;
					$final[] = $res;
					$count++;
				}
			}
			
			// #### AS A BUYER BUYING LOWEST UNIQUE BID ITEMS ##############
			// does the buyer need to leave feedback for any seller who sold via LUB auction?
			$query = $ilance->db->query("
				SELECT p.project_title, p.currencyid, ub.project_id, ub.project_user_id AS seller_id, ub.user_id AS buyer_id, date_end AS enddate
				FROM " . DB_PREFIX . "projects_uniquebids AS ub,
				" . DB_PREFIX . "projects AS p
				WHERE ub.project_id = p.project_id
					AND ub.status = 'lowestunique'
					AND ub.user_id = '" . intval($userid) . "'
					AND p.buyerfeedback = '0'
					AND p.haswinner = '1'
					AND p.status != 'open'
					AND p.winner_user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					//$html .= '<div>[ <span class="smaller blue"><a href="' . $ilpage['buying'] . '?cmd=management&amp;subcmd=lub&amp;bidsub=awarded">' . $phrase['_leave_feedback'] . '</a></span> ] ' . $phrase['_lowest_unique_bid'] . ' ' . $phrase['_item'] . ': <span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . fetch_auction('project_title', $res['project_id']) . '</a></span>: ' . $phrase['_seller_is_waiting_for_your_feedback'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
					$res['project_state'] = 'product';
					$res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = fetch_user('username', $res['seller_id']);
					$res['usertype'] = $phrase['_seller'];
					$res['fromtype'] = 'seller';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
					$res['for_user_id'] =  $res['seller_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$GLOBALS['show_stars' . $res['project_id']] = 1;
					$final[] = $res;
					$count++;
				}
			}
			
			// #### AS A SERVICE BUYER #####################################
			// does this buyer need to leave feedback for any provider?
			$query = $ilance->db->query("
				SELECT p.project_title, p.project_id, p.currencyid, p.user_id AS buyer_id, b.user_id AS seller_id, date_end AS enddate
				FROM " . DB_PREFIX . "projects AS p,
				" . DB_PREFIX . "project_bids AS b
				WHERE p.user_id = '" . intval($userid) . "'
					AND p.user_id = b.project_user_id
					AND b.bidstatus = 'awarded'
					AND p.project_state = 'service'
					AND p.status = 'approval_accepted'
					AND p.buyerfeedback = '0'
					GROUP BY p.project_id
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					//$html .= '<div>[ <span class="smaller blue"><a href="' . $ilpage['buying'] . '?cmd=management">' . $phrase['_leave_feedback'] . '</a></span> ] ' . $phrase['_project'] . ': <span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . fetch_auction('project_title', $res['project_id']) . '</a></span>: ' . $phrase['_provider_is_waiting_for_your_feedback'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
					$res['project_state'] = 'service';
					$res['photo'] = print_item_photo($ilpage['rfp'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = fetch_user('username', $res['seller_id']);
					$res['usertype'] = $phrase['_provider'];
					$res['fromtype'] = 'seller';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
					$res['for_user_id'] =  $res['seller_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$GLOBALS['show_stars' . $res['project_id']] = 1;
					$final[] = $res;
					$count++;
				}
			}
			
			// #### AS A PRODUCT BUYER #####################################
			// does this buyer need to leave feedback for any seller?
			$query = $ilance->db->query("
				SELECT p.project_title, p.project_id, p.currencyid, p.user_id AS seller_id, b.user_id AS buyer_id, date_end AS enddate
				FROM " . DB_PREFIX . "projects AS p,
				" . DB_PREFIX . "project_bids AS b
				WHERE b.user_id = '" . intval($userid) . "'
					AND b.project_user_id = p.user_id
					AND p.project_state = 'product'
					AND b.bidstatus = 'awarded'
					AND b.project_id = p.project_id
					AND p.buyerfeedback = '0'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					//$html .= '<div>[ <span class="smaller blue"><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded#product">' . $phrase['_leave_feedback'] . '</a></span> ] ' . $phrase['_item'] . ': <span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . fetch_auction('project_title', $res['project_id']) . '</a></span>: ' . $phrase['_seller_is_waiting_for_your_feedback'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
					$res['project_state'] = 'product';
					$res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = fetch_user('username', $res['seller_id']);
					$res['usertype'] = $phrase['_seller'];
					$res['fromtype'] = 'seller';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
					$res['for_user_id'] =  $res['seller_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$GLOBALS['show_stars' . $res['project_id']] = 1;
					$final[] = $res;
					$count++;
				}
			}
			
			($apihook = $ilance->api('feedback_activity_show_all_or_bought_end')) ? eval($apihook) : false;
		}
		
		if ($showview == 'all' OR $showview == 'sold')
		{
			// #### AS A PRODUCT SELLER ############################
			// does this seller need to leave feedback for any winning bidders or buy now purchasers?
			$query = $ilance->db->query("
				SELECT p.project_title, p.project_id, p.currencyid, p.user_id AS seller_id, b.user_id AS buyer_id, date_end AS enddate
				FROM " . DB_PREFIX . "projects AS p,
				" . DB_PREFIX . "project_bids AS b
				WHERE p.user_id = '" . intval($userid) . "'
					AND b.project_user_id = p.user_id
					AND p.project_state = 'product'
					AND b.bidstatus = 'awarded'
					AND b.project_id = p.project_id
					AND p.sellerfeedback = '0'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					//$html .= '<div>[ <span class="smaller blue"><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded#product">' . $phrase['_leave_feedback'] . '</a></span> ] ' . $phrase['_item'] . ': <span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . fetch_auction('project_title', $res['project_id']) . '</a></span>: ' . $phrase['_seller_is_waiting_for_your_feedback'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
					$res['project_state'] = 'product';
					$res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = fetch_user('username', $res['buyer_id']);
					$res['usertype'] = $phrase['_buyer'];
					$res['fromtype'] = 'buyer';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
					$res['for_user_id'] =  $res['buyer_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$GLOBALS['show_stars' . $res['project_id']] = 0;
					$final[] = $res;
					$count++;
				}
			}
			
			// #### AS A SELLER SELLING ITEMS VIA BUY NOW ##########
			// does the seller need to leave feedback for any buyer purchased via buy now?
			$query = $ilance->db->query("
				SELECT project_id, buyer_id, owner_id AS seller_id, orderdate AS enddate
				FROM " . DB_PREFIX . "buynow_orders
				WHERE owner_id = '" . intval($userid) . "'
					AND sellerfeedback = '0'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$status = fetch_auction('status', $res['project_id']);
					$url = ($status == 'open')
						? $ilpage['selling'] . '?cmd=management'
						: $ilpage['selling'] . '?cmd=management&amp;sub=sold';
					//$html .= '<div>[ <span class="smaller blue"><a href="' . $url . '">' . $phrase['_leave_feedback'] . '</a></span> ] ' . $phrase['_buy_now_item'] . ': <span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . fetch_auction('project_title', $res['project_id']) . '</a></span>: ' . $phrase['_buyer_is_waiting_for_your_feedback'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
					unset($status, $url);
					
					$res['project_title'] = fetch_auction('project_title', $res['project_id']);
					$res['project_state'] = 'product';
					$res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = fetch_user('username', $res['buyer_id']);
					$res['usertype'] = $phrase['_buyer'];
					$res['fromtype'] = 'buyer';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
					$res['for_user_id'] =  $res['buyer_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$GLOBALS['show_stars' . $res['project_id']] = 0;
					$final[] = $res;
					$count++;
				}
			}
			
			// #### AS A SELLER SELLING LOWEST UNIQUE BID ITEMS ############
			// does the seller need to leave feedback for any buyer who won via LUB auction?
			$query = $ilance->db->query("
				SELECT p.project_title, p.currencyid, ub.project_id, ub.user_id AS buyer_id, ub.project_user_id AS seller_id, date_end AS enddate
				FROM " . DB_PREFIX . "projects_uniquebids AS ub,
				" . DB_PREFIX . "projects AS p
				WHERE ub.project_id = p.project_id
					AND ub.status = 'lowestunique'
					AND ub.project_user_id = '" . intval($userid) . "'
					AND p.sellerfeedback = '0'
					AND p.haswinner = '1'
					AND p.status != 'open'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					//$html .= '<div>[ <span class="smaller blue"><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold">' . $phrase['_leave_feedback'] . '</a></span> ] ' . $phrase['_lowest_unique_bid'] . ' ' . $phrase['_item'] . ': <span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . fetch_auction('project_title', $res['project_id']) . '</a></span>: ' . $phrase['_buyer_is_waiting_for_your_feedback'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
					$res['project_state'] = 'product';
					$res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = fetch_user('username', $res['buyer_id']);
					$res['usertype'] = $phrase['_buyer'];
					$res['fromtype'] = 'buyer';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
					$res['for_user_id'] =  $res['buyer_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$GLOBALS['show_stars' . $res['project_id']] = 0;
					$final[] = $res;
					$count++;
				}
			}
			
			// #### AS A SERVICE PROVIDER ##################################
			// does this provider need to leave feedback for any buyer?
			$query = $ilance->db->query("
				SELECT p.project_title, p.project_id, p.currencyid, p.user_id AS buyer_id, b.user_id AS seller_id, date_end AS enddate
				FROM " . DB_PREFIX . "projects AS p,
				" . DB_PREFIX . "project_bids AS b
				WHERE b.user_id = '" . intval($userid) . "'
					AND b.project_user_id = p.user_id
					AND p.project_state = 'service'
					AND b.bidstatus = 'awarded'
					AND b.project_id = p.project_id
					AND p.sellerfeedback = '0'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					//$html .= '<div>[ <span class="smaller blue"><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded#servicebidding">' . $phrase['_leave_feedback'] . '</a></span> ] ' . $phrase['_project'] . ': <span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . fetch_auction('project_title', $res['project_id']) . '</a></span>: ' . $phrase['_buyer_is_waiting_for_your_feedback'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
					$res['project_state'] = 'service';
					$res['photo'] = print_item_photo($ilpage['rfp'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = fetch_user('username', $res['buyer_id']);
					$res['usertype'] = $phrase['_buyer'];
					$res['fromtype'] = 'buyer';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
					$res['for_user_id'] =  $res['buyer_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$GLOBALS['show_stars' . $res['project_id']] = 0;
					$final[] = $res;
					$count++;
				}
			}
			
			($apihook = $ilance->api('feedback_activity_show_all_or_sold_end')) ? eval($apihook) : false;
		}
		
		if ($count > 0)
		{
			$html = '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback">' . $phrase['_leave_feedback'] . '</a></span> ' . $phrase['_for'] . ' ' . $count . ' ' . $phrase['_transactions_lower'] . '.<hr size="1" width="100%" style="color:#cccccc" />';
		}
		
		return array($final, $html);
	}
    
	/*
	* Function to construct a profile input form based on a particular question id being supplied as the argument
	*
	* @param      integer       question id       
	*
	* @return     string 
	*/
	function construct_profile_input($qid = 0)
	{
		global $ilance, $myapi, $ilconfig, $phrase, $page_title, $area_title, $ilpage;
		
		$sql = $ilance->db->query("
			SELECT questionid, inputtype, multiplechoice
			FROM " . DB_PREFIX . "profile_questions
			WHERE questionid = '" . intval($qid) . "'
				AND visible = '1'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			
			$sql2 = $ilance->db->query("
				SELECT answerid, questionid, user_id, answer, date, visible, isverified, verifyexpiry, invoiceid, contactname, contactnumber, contactnotes
				FROM " . DB_PREFIX . "profile_answers
				WHERE questionid = '" . $res['questionid'] . "'
				    AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				    AND visible = '1'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql2) > 0)
			{
				$res2 = $ilance->db->fetch_array($sql2);
				
				// is answer verified?
				if ($res2['isverified'])
				{
					// does admin allow answer updates after verification?
					$res2['disabled'] = ($ilconfig['verificationupdateafter']) ? '' : 'disabled="disabled"';    
				}
				else
				{
					$res2['disabled'] = '';
				}
				
				// #### answer type input ######################
				switch ($res['inputtype'])
				{
					case 'yesno':
					{
						$selected1 = $selected2 = '';
						if ($res2['answer'] == '1')
						{
							$selected1 = 'checked="checked"';
						}
						else if ($res2['answer'] == '')
						{
							$selected1 = 'checked="checked"';
						}
						
						$input = $phrase['_yes'].' <input type="radio" name="question[' . $res['questionid'] . ']" value="1" ' . $selected1 . ' ' . $res2['disabled'] . ' />&nbsp;&nbsp;';
						if ($res2['answer'] == '0')
						{
							$selected2 = 'checked="checked"';
						}
						else if ($res2['answer'] == '')
						{
							$selected1 = 'checked="checked"';
						}
						$input .= $phrase['_no'] .'<input type="radio" name="question[' . $res['questionid'] . ']" value="0" ' . $selected2 . ' ' . $res2['disabled'] . ' />';
						break;
					}					 
					case 'int':
					{
						$input = '<input type="text" id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" value="' . stripslashes($res2['answer']) . '" style="width:60px; font-family: verdana" ' . $res2['disabled'] . ' />';
						break;
					}					 
					case 'textarea':
					{
						$input = '<textarea id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" style="width: 425px; height: 84px; font-family: Verdana;" wrap="physical" ' . $res2['disabled'] . '>' . stripslashes($res2['answer']) . '</textarea>';
						break;
					}					 
					case 'text':
					{
						$input = '<input type="text" id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" value="' . stripslashes($res2['answer']) . '" class="input" style="font-family: verdana"' . $res2['disabled'] . ' />';
						break;
					}				    
					case 'multiplechoice':
					{
						if (!empty($res['multiplechoice']))
						{
							$choices = explode('|', $res['multiplechoice']);
							$input = $phrase['_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices'] . '<br /><select style="width:425px; height:90px; font-family: verdana" multiple name="question[' . $res['questionid'] . '][]" id="question' . $res['questionid'] . ' ' . $res2['disabled'] . '">';
							$input .= '<optgroup label="' . $phrase['_select'] . ':">';
							$answers = (empty($res2['answer'])) ? array() : unserialize(stripslashes($res2['answer']));
							foreach ($choices AS $choice)
							{
								$input .= (in_array($choice, $answers)) ? '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>' : '<option value="' . trim($choice) . '">' . $choice . '</option>';
							}
							$input .= '</optgroup>';
							$input .= '</select>';
						}
						else
						{
							$input .= '-- ' . $phrase['_not_available'] . ' --';    
						}
						break;
					}		    
					case 'pulldown':
					{
						if (!empty($res['multiplechoice']))
						{
							$choices = explode('|', $res['multiplechoice']);
							$input = '<select name="question[' . $res['questionid'] . ']" id="question' . $res['questionid'] . '" style="font-family: verdana" ' . $res2['disabled'] . '>';
							foreach ($choices AS $choice)
							{
								if (!empty($choice))
								{
									$input .= ($res2['answer'] == $choice) ? '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>' : '<option value="' . trim($choice) . '">' . $choice . '</option>';
								}
							}
							$input .= '</select>';
						}
						break;
					}
				}
			}
			else
			{
				// #### answer type input ######################
				switch ($res['inputtype'])
				{
					case 'yesno':
					{
						$input = '<label for="yes">' . $phrase['_yes'] . '</label><input type="radio" id="yes" name="question[' . $res['questionid'] . ']" value="1" checked="checked" />&nbsp;&nbsp;';
						$input .= '<label for="no">' . $phrase['_no'] . '</label><input type="radio" id="no" name="question[' . $res['questionid'] . ']" value="0" />';
						break;
					}					
					case 'int':
					{
						$input = '<input type="text" id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" value="" class="input" style="font-family: verdana" />';
						break;
					}					
					case 'textarea':
					{
						$input = '<textarea id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" style="width: 425px; height: 84px; font-family: Verdana;" wrap="physical"></textarea>';
						break;
					}					
					case 'text':
					{
						$input = '<input type="text" id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" value="" class="input" style="font-family: verdana" />';
						break;
					}				    
					case 'multiplechoice':
					{
						if (!empty($res['multiplechoice']))
						{
							$choices = explode('|', $res['multiplechoice']);
							$input = $phrase['_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices'] . '<br /><select style="width:425px; height:90px; font-family: verdana" multiple name="question[' . $res['questionid'] . '][]" id="question' . $res['questionid'] . '">';
							$input .= '<optgroup label="' . $phrase['_select'] . ':">';
							foreach ($choices AS $choice)
							{
								if (!empty($choice))
								{
									$input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
								}
							}
							$input .= '</optgroup>';
							$input .= '</select>';
						}
						else
						{
							$input .= '-- ' . $phrase['_not_available'] . ' --';  
						}
						break;
					}		    
					case 'pulldown':
					{
						if (!empty($res['multiplechoice']))
						{
							$choices = explode('|', $res['multiplechoice']);
							$input = '<select name="question[' . $res['questionid'] . ']" id="question' . $res['questionid'] . '" style="font-family: verdana">';
							foreach ($choices AS $choice)
							{
								if (!empty($choice))
								{
									$input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
								}
							}
							$input .= '</select>';
						}
						break;
					}
				}
			}
			
			return $input;
		}
	}
	
	/*
	* Function to fetch and display any unpaid transactions left between the viewing user and other trading partners.
	*
	* @param      integer       user id
	* @param      string        type (buying or selling)
	* @param      integer       date range period (default -1)
	*
	* @return     string        HTML formatted response.
	*/
	function unpaid_p2b_activity($userid = 0, $type = '', $period = -1)
	{
		global $ilance, $ilpage, $phrase, $ilconfig;
		
		$html = '';
		
		if ($type == 'buying')
		{
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE user_id = '" . intval($userid) . "'
					AND invoicetype = 'p2b'
					AND status = 'unpaid'
			");	
		}
		else if ($type == 'selling')
		{
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE p2b_user_id = '" . intval($userid) . "'
					AND invoicetype = 'p2b'
					AND status = 'unpaid'
			");	
		}
		
		// #### does listing have pending p2b invoices unpaid? #########
		if ($ilance->db->num_rows($sql) > 0)
		{
			$pendinvoices = '';
			$invoices = 0;
			while ($res_inv = $ilance->db->fetch_array($sql))
			{
				$title = fetch_auction('project_title', $res_inv['projectid']);
				$projectid = $res_inv['projectid'];
				$providerid = $res_inv['p2b_user_id'];
				$buyerid = $res_inv['user_id'];
				$crypted = array('id' => $res_inv['invoiceid']);
				$pendinvoices .= '<span class="blue"><a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $res_inv['transactionid'] . '" title="#' . $res_inv['invoiceid'] . '">#' . $res_inv['invoiceid'] . '</a></span>, ';
				$invoices++;
			}
			
			$pendinvoices = (!empty($pendinvoices)) ? mb_substr($pendinvoices, 0, -2) : '';
			$invoicephrase = ($invoices == 1) ? $phrase['_invoice_lower'] : $phrase['_invoices_lower'];
			
			if ($type == 'buying')
			{
				$html = $invoices . ' ' . $invoicephrase . ': ' . $pendinvoices . ' ' . $phrase['_generated_by'] . ' <span class="blue"><a href="' . $ilpage['members'] . '?id=' . $providerid . '">' . fetch_user('username', $providerid) . '</a></span> ' . $phrase['_is_waiting_on_payment_for_the'] . ' <span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $projectid . '">' . $title . '</a></span> ' . $phrase['_listing_lower'] . '.<hr size="1" width="100%" style="color:#cccccc" />';
			}
			else if ($type == 'selling')
			{
				$html = $invoices . ' ' . $invoicephrase . ': ' . $pendinvoices . ' ' . $phrase['_generated_to'] . ' <span class="blue"><a href="' . $ilpage['members'] . '?id=' . $buyerid . '">' . fetch_user('username', $buyerid) . '</a></span> ' . $phrase['_for_the'] . ' <span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $projectid . '">' . $title . '</a></span> ' . $phrase['_listing_still_pending_payment'] . '.<hr size="1" width="100%" style="color:#cccccc" />';
			}
		}
		
		return $html;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>