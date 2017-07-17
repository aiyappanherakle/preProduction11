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
if (!class_exists('auction'))
{
	exit;
}
/**
* Auction award class to perform the majority of functions dealing with anything to do with listing expiry within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class auction_expiry extends auction
{
        function all()
        {
                global $ilance, $phrase, $ilconfig, $ilpage;
                
                $cronlog = $this->listings();
				// murugan commented on Oct 22 for stop the unwated process
               // $cronlog .= $this->other();
                
                return $cronlog;
        }
        
        function listings_expired_to_finished()
        {
                global $ilance, $phrase, $ilconfig, $ilpage;
                
                $ilance->feedback = construct_object('api.feedback');
                $ilance->accounting = construct_object('api.accounting');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                $ilance->email = construct_dm_object('email', $ilance);
                
                $cronlog = '';
                
                // #### escrow enabled #########################################
                if ($ilconfig['escrowsystem_enabled'])
                {
                        // #### product ########################################
                        $sql_items = $ilance->db->query("
                                SELECT filter_escrow, project_id
                                FROM " . DB_PREFIX . "projects
                                WHERE project_state = 'product'
                                    AND status = 'expired'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_items) > 0)
                        {
                                while ($res_items = $ilance->db->fetch_array($sql_items, DB_ASSOC))
                                {
                                        if ($res_items['filter_escrow'] == '1')
                                        {
                                                // is this escrow finished?
                                                $sql_esc = $ilance->db->query("
                                                        SELECT project_id
                                                        FROM " . DB_PREFIX . "projects_escrow
                                                        WHERE date_paid != '0000-00-00 00:00:00'
                                                            AND status = 'finished'
                                                            AND project_id = '" . $res_items['project_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_esc) > 0)
                                                {
                                                        while ($res_esc = $ilance->db->fetch_array($sql_esc, DB_ASSOC))
                                                        {
                                                                // is feedback process finished?
                                                                if ($ilance->feedback->is_feedback_complete($res_esc['project_id']))
                                                                {
                                                                        // update auction as finished
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "projects
                                                                                SET status = 'finished'
                                                                                WHERE project_id = '".$res_esc['project_id']."'
                                                                                    AND project_state = 'product'
                                                                                    AND (status != 'archived' OR status != 'delisted')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        
                                                                        $cronlog .= '';
                                                                }                                                
                                                        }
                                                }		
                                        }
                                }
                                unset($res_items);
                        }
                    
                        // #### service ########################################
                        $sql_proj = $ilance->db->query("
                                SELECT filter_escrow, project_id
                                FROM " . DB_PREFIX . "projects
                                WHERE project_state = 'service'
                                    AND status = 'expired'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_proj) > 0)
                        {
                                while ($res_projs = $ilance->db->fetch_array($sql_proj, DB_ASSOC))
                                {
                                        if ($res_projs['filter_escrow'] == '1')
                                        {
                                                // is this escrow finished?
                                                $sql_projesc = $ilance->db->query("
                                                        SELECT project_id
                                                        FROM " . DB_PREFIX . "projects_escrow
                                                        WHERE date_paid != '0000-00-00 00:00:00'
                                                            AND status = 'finished'
                                                            AND project_id = '" . $res_projs['project_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_projesc) > 0)
                                                {
                                                        while ($res_projesc = $ilance->db->fetch_array($sql_projesc, DB_ASSOC))
                                                        {
                                                                // is feedback process finished?
                                                                if ($ilance->feedback->is_feedback_complete($res_projesc['project_id']))
                                                                {
                                                                        // update auction as finished
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "projects
                                                                                SET status = 'finished'
                                                                                WHERE project_id = '" . $res_projesc['project_id'] . "'
                                                                                    AND project_state = 'service'
                                                                                    AND (status != 'archived' OR status != 'delisted')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        
                                                                        $cronlog .= '';
                                                                }
                                                        }
                                                        unset($res_projesc);
                                                }
                                        }
                                }
                                unset($res_projs);
                        }
                }
		
		      // #### escrow disabled ########################################
                else
                {
                        // escrows disabled: check for expired product auctions so we can learn
                        // what is going on with the feedback between both members
                        $sql_items = $ilance->db->query("
                                SELECT project_id 
                                FROM " . DB_PREFIX . "projects
                                WHERE status = 'expired'
                                    AND project_state = 'product'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_items) > 0)
                        {
                                while ($res_items = $ilance->db->fetch_array($sql_items, DB_ASSOC))
                                {
                                        // is feedback complete?
                                        if ($ilance->feedback->is_feedback_complete($res_items['project_id']))
                                        {
                                                // set auction to finished
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET status = 'finished'
                                                        WHERE project_id = '" . $res_items['project_id'] . "'
                                                            AND project_state = 'product'
                                                            AND (status != 'archived' OR status != 'delisted')
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                $cronlog .= '';
                                        }
                                }
                                unset($res_items);
                        }
                    
                        // expired service auction checkup
                        $sql_proj = $ilance->db->query("
                                SELECT project_id
                                FROM " . DB_PREFIX . "projects
                                WHERE status = 'expired'
                                        AND project_state = 'service'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_proj) > 0)
                        {
                                while ($res_projs = $ilance->db->fetch_array($sql_proj, DB_ASSOC))
                                {
                                        // is feedback complete?
                                        if ($ilance->feedback->is_feedback_complete($res_projs['project_id']))
                                        {
                                                // update auction as finished
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET status = 'finished'
                                                        WHERE project_id = '" . $res_projs['project_id'] . "'
                                                            AND project_state = 'service'
                                                            AND (status != 'archived' OR status != 'delisted')
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                $cronlog .= '';
                                        }
                                }
                                unset($res_projs);
                        }
                }
                
                return $cronlog;
        }
        
        function listings()
        {
                global $ilance, $phrase, $ilconfig, $ilpage;
                
                $ilance->feedback = construct_object('api.feedback');
                $ilance->accounting = construct_object('api.accounting');
		$ilance->accounting_fees = construct_object('api.accounting_fees');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                $ilance->email = construct_dm_object('email', $ilance);
		
		// #### require our shipping backend ###########################
		require_once(DIR_CORE . 'functions_shipping.php');
                
                $cronlog = '';
                
                $sql_rfp = $ilance->db->query("
                        SELECT p.*, s.ship_method, s.ship_handlingfee, s.ship_handlingtime
                        FROM " . DB_PREFIX . "projects p
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
                        WHERE p.date_end <= '" . DATETODAY . " " . TIMENOW . "' 
                            AND p.status = 'open'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_rfp) > 0)
                {
                        while ($res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC))
                        {
                                if ($res_rfp['project_state'] == 'product')
                                {
                                        $sql_owner = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res_rfp['user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_owner) > 0)
                                        {
                                                $res_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
                                                
                                                //$canrelist = $this->process_auction_relister($res_rfp['project_id'], $dontsendemail = true);
                                                $canrelist=false;
                                                // #### determine if seller is auto-relisting this item (if no bids were received)
                                                if ($canrelist == false)
                                                {
                                                        // #### subtract auction count
                                                        reduce_catalog_count($res_rfp['coin_series_denomination_no'],$res_rfp['coin_series_unique_no'],$res_rfp['pcgs']);
                                                                
                                                        // #### update open product auctions to expired
                                                        // #### check for high bidders as well and if the reserve price was met
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET status = 'expired'
                                                                WHERE project_id = '" . $res_rfp['project_id'] . "' 
                                                                AND date_end <= '" . DATETODAY . " " . TIMENOW . "' 
                                                                AND status = 'open'
                                                        ", 0, null, __FILE__, __LINE__);
												if($ilance->db->affected_rows() > 0)
												{
                                                        
                                                        {
                                                                // fetch highest maximum bid placed
								$highbid = $ilance->db->query("
									SELECT bidamount, user_id, bid_id, date_added, buyershipcost, buyershipperid
									FROM " . DB_PREFIX . "project_bids
									WHERE project_id = '" . $res_rfp['project_id'] . "'
									ORDER BY bidamount DESC, date_added ASC
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($highbid) > 0)
								{
									$res_highest = $ilance->db->fetch_array($highbid, DB_ASSOC);
									
									$highestbid = $res_highest['bidamount'];
									$highbidderid = $res_highest['user_id'];
									$highbiddate = $res_highest['date_added'];
									$highbidderbidid = $res_highest['bid_id'];
									$buyershipcost = $res_highest['buyershipcost'];
									$buyershipperid = $res_highest['buyershipperid'];
								}
								else
								{
									$highbidderid = $highbidderbidid = $buyershipcost = $buyershipperid = 0;
									$highestbid = '0.00';
									$highbiddate = '0000-00-00 00:00:00';
								}
                                                
                                                                // #### do we have a highest bid placed?
                                                                if ($highestbid > 0 AND $highbidderid > 0)
                                                                {
                                                                        // #### check to see if we're using reserve pricing
                                                                        if ($res_rfp['reserve'])
                                                                        {
                                                                                // #### RESERVE PRICE IN EFFECT ########################
                                                                                if ($res_rfp['reserve_price'] <= $highestbid)
                                                                                {
                                                                                        // #### select all bidders other than our highest bidder
                                                                                        // #### so we can send expired listing notifications
                                                                                        $sql_bids = $ilance->db->query("
                                                                                                SELECT *
                                                                                                FROM " . DB_PREFIX . "project_bids
                                                                                                WHERE project_user_id = '" . $res_rfp['user_id'] . "'
                                                                                                    AND project_id = '" . $res_rfp['project_id'] . "'
                                                                                                    AND user_id != '" . $highbidderid . "'
                                                                                                GROUP BY user_id
                                                                                        ", 0, null, __FILE__, __LINE__);
                                                                                        if ($ilance->db->num_rows($sql_bids) > 0)
                                                                                        {
                                                                                                while ($res_bids = $ilance->db->fetch_array($sql_bids, DB_ASSOC))
                                                                                                {
                                                                                                        // update all bidders (except the winning bidder) bids to 'outbid'
                                                                                                        $ilance->db->query("
                                                                                                                UPDATE " . DB_PREFIX . "project_bids
                                                                                                                SET bidstatus = 'outbid',
                                                                                                                bidstate = 'expired'
                                                                                                                WHERE user_id = '" . $res_bids['user_id'] . "'
                                                                                                                    AND project_id = '" . $res_bids['project_id'] . "'
                                                                                                        ", 0, null, __FILE__, __LINE__);
																										// murugan chages on mar 07 
																										// murugan changes on mar 10
																										$lastbid = $ilance->db->query("SELECT max(bidamount) AS lastbidamount
																										 					FROM " . DB_PREFIX . "project_bids
																															WHERE project_id = '" . $res_bids['project_id'] . "'
																															AND user_id = '" . $res_bids['user_id'] . "' ");
																										$lastbidres = $ilance->db->fetch_array($lastbid);
																										
																										 $sqlproxy = $ilance->db->query("SELECT maxamount FROM " . DB_PREFIX . "proxybid
																															 WHERE project_id = '".$res_bids['project_id']."'
																															 AND user_id = '".$res_bids['user_id']."' ");
																										$resproxy = $ilance->db->fetch_array($sqlproxy);
																										
																										if($lastbidres['lastbidamount'] <= $resproxy['maxamount'])
																										{
																											$lastbidamountuser = $resproxy['maxamount'];
																										}
																										else
																										{
																											$lastbidamountuser = $lastbidres['lastbidamount'];
																										}
                                                                    									// murugan end here below we changes the $lowbidamount to $lastbidamountuser
                                                                                                        $sql_bidder = $ilance->db->query("
                                                                                                                SELECT *
                                                                                                                FROM " . DB_PREFIX . "users
                                                                                                                WHERE user_id = '" . $res_bids['user_id'] . "'
                                                                                                        ", 0, null, __FILE__, __LINE__);
                                                                                                        if ($ilance->db->num_rows($sql_bidder) > 0)
                                                                                                        {
                                                                                                                $res_bidder = $ilance->db->fetch_array($sql_bidder, DB_ASSOC);
                                                                                                                
														$existing = array(
                                                                                                                        '{{project_title}}' => stripslashes($res_rfp['project_title']),
                                                                                                                        '{{bidder}}' => $res_bidder['username'],
                                                                                                                        '{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
                                                                                                                        '{{datetime}}' => DATETODAY . ' ' . TIMENOW,
															'{{lowbiddate}}' => print_date($lowbiddate, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
															'{{highbiddate}}' => print_date($highbiddate, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                                                                                                                        '{{lowbidamount}}' => $ilance->currency->format($lastbidamountuser, $res_rfp['currencyid']),
                                                                                                                        '{{highbidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
                                                                                                                );
														
                                                                                                                $lowbiddate = $res_bids['date_added'];
                                                                                                                //$lowbidamount = number_format($res_bids['bidamount'], 2);
                                                                                                                
                                                                                                                // email user
																												// murugan changes on apr 5
																											$sql1 = $ilance->db->query("
																											SELECT *
																											FROM " . DB_PREFIX . "emaillog
																											WHERE user_id = '".$res_bids['user_id']."'
																											AND project_id = '".$res_rfp['project_id']."'
																											AND logtype = 'auctionloser'             
																											AND date LIKE '%" . DATETODAY . "%'
																									", 0, null, __FILE__, __LINE__);
																												if ($ilance->db->num_rows($sql1) == 0)
																												{	
																												$ilance->email->project = $res_rfp['project_id'];
																												$ilance->email->logtype = 'auctionloser';
                                                                                                                $ilance->email->mail = $res_bidder['email'];
                                                                                                                $ilance->email->slng = fetch_user_slng($res_bids['user_id']);
                                                                                                                $ilance->email->get('product_auction_expired_another_bidder');		
                                                                                                                $ilance->email->set($existing);
                                                                                                                
														($apihook = $ilance->api('product_auction_expired_another_bidder_reserve_met')) ? eval($apihook) : false;
														
																												$ilance->email->send();
																												}
                                                                                                        }
                                                                                                        
                                                                                                        $cronlog .= '';
                                                                                                }
                                                                                        }
                                                                                        
                                                                                        // #### do shipping fees apply?
                                                                                        $shippinginformation = $phrase['_none'];
											$totalescrowamount = $highestbid;
											$shippinginformation = $ilance->currency->format($buyershipcost, $res_rfp['currencyid']);
											$totalescrowamount = ($totalescrowamount + $buyershipcost);
                                                                                                            
                                                                                        // #### fetch highest bidders information
                                                                                        $sql_winner = $ilance->db->query("
                                                                                                SELECT *
                                                                                                FROM " . DB_PREFIX . "users
                                                                                                WHERE user_id = '" . $highbidderid . "'
                                                                                        ", 0, null, __FILE__, __LINE__);
                                                                                        if ($ilance->db->num_rows($sql_winner) > 0)
                                                                                        {
                                                                                                $res_winner = $ilance->db->fetch_array($sql_winner, DB_ASSOC);
                                                                                                
                                                                                                // #### fetch owners information
                                                                                                $sql_owner = $ilance->db->query("
                                                                                                        SELECT *
                                                                                                        FROM " . DB_PREFIX . "users
                                                                                                        WHERE user_id = '" . $res_rfp['user_id'] . "'
                                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                                if ($ilance->db->num_rows($sql_owner) > 0)
                                                                                                {
                                                                                                        $res_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
                                                                                                        
                                                                                                        //if ($ilconfig['escrowsystem_enabled'] AND $res_rfp['filter_escrow'] == '1' AND $res_rfp['filter_gateway'] == '0' AND $res_rfp['filter_offline'] == '0')
													$methodtype = print_payment_method_title($res_rfp['project_id']);
													$methodscount = print_payment_methods($res_rfp['project_id'], false, true);
													
													// #### USING ESCROW ONLY ########################################################
													if ($ilconfig['escrowsystem_enabled'] AND $res_rfp['filter_escrow'] == '1' AND $methodscount == 1 AND $methodtype == 'escrow')
                                                                                                        {
                                                                                                                // #### SELLER AND BUYER ESCROW FEES #####################################
                                                                                                                // also applies tax to the fees
                                                                                                                $fee  = fetch_merchant_escrow_fee_plus_tax($res_rfp['user_id'], $totalescrowamount);
                                                                                                                $fee2 = fetch_product_bidder_escrow_fee_plus_tax($highbidderid, $totalescrowamount);
                                                                        
                                                                                                                // #### create the escrow invoice to be paid by bidder
                                                                                                                $escrow_invoice_id = $ilance->accounting->insert_transaction(
                                                                                                                        0,
                                                                                                                        intval($res_rfp['project_id']),
                                                                                                                        0,
                                                                                                                        intval($highbidderid),
                                                                                                                        intval($res_rfp['user_id']),
                                                                                                                        0,
                                                                                                                        0,
                                                                                                                        $phrase['_escrow_payment_forward'] . ': ' . stripcslashes($res_rfp['project_title']) . ' #' . $res_rfp['project_id'],
                                                                                                                        sprintf("%01.2f", $totalescrowamount),
                                                                                                                        '',
                                                                                                                        'unpaid',
                                                                                                                        'escrow',
                                                                                                                        'account',
                                                                                                                        DATETIME24H,
                                                                                                                        DATEINVOICEDUE,
                                                                                                                        '',
                                                                                                                        $phrase['_additional_shipping_fees_reserve'] . ': ' . $shippinginformation,
                                                                                                                        0,
                                                                                                                        0,
                                                                                                                        1
                                                                                                                );
$ilance->db->query("
													UPDATE " . DB_PREFIX . "invoices
													SET statement_date = '".DATETIME24H."'
													WHERE invoiceid = '" . $escrow_invoice_id . "'", 0, null, __FILE__, __LINE__);

													
													
													
// murugan changes on may 10
																												if($ilconfig['staffsettings_feeinnumber'] != 0)
																												{
																													$buyerfee_calnum = $ilconfig['staffsettings_feeinnumber'];
																												}
																												else
																												{
																													$buyerfee_calnum = 0;
																												}
																												if($ilconfig['staffsettings_feeinpercentage'] != 0)
																												{
																													$buyerfee_calper = ($totalescrowamount * ($ilconfig['staffsettings_feeinpercentage'] / 100));
																												}
																												else
																												{
																													$buyerfee_calper = 0;
																												}
																												if($buyerfee_calnum <= $buyerfee_calper )
																												{																													
																													$buyerfee1 = $buyerfee_calper;																													
																												}
																												else
																												{																													
																													$buyerfee1 = $buyerfee_calnum;																													
																												}	
																												
																												$transactionidnew =  construct_transaction_id();
																												$ilance->db->query("INSERT INTO ".DB_PREFIX."invoices(projectid,user_id,p2b_user_id,description,amount,totalamount,
																												status,invoicetype,createdate,duedate,custommessage,isbuyerfee,transactionid)
																												VALUES(
																												'".intval($res_rfp['project_id'])."',
																												'".intval($highbidderid)."',
																												'".intval($res_rfp['user_id'])."',
																												'buyer fees',
																												'".$buyerfee1."',
																												'".$buyerfee1."',
																												'paid',
																												'debit',
																												'".DATETIME24H."',
																												'".DATEINVOICEDUE."',
																												'buyer fees for buyer',
																												'1',
																												'".$transactionidnew."'																												
																												)");
																												 $buyerfee_id = $ilance->db->insert_id();
                                                                                                                
                                                                                                                // create the product escrow account
                                                                                                                $ilance->db->query("
                                                                                                                        INSERT INTO " . DB_PREFIX . "projects_escrow
                                                                                                                        (escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, bidamount, shipping, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, fee2invoiceid, status)
                                                                                                                        VALUES(
                                                                                                                        NULL,
                                                                                                                        '" . $highbidderbidid . "',
                                                                                                                        '" . $res_rfp['project_id'] . "',
                                                                                                                        '" . $escrow_invoice_id . "',
                                                                                                                        '" . $res_rfp['user_id'] . "',
                                                                                                                        '" . $highbidderid . "',
                                                                                                                        '" . DATETIME24H . "',
                                                                                                                        '" . sprintf("%01.2f", $highestbid) . "',
                                                                                                                        '" . sprintf("%01.2f", $buyershipcost) . "',
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
                                                                                
                                                                                                                // tie the escrow account to the project
                                                                                                                $ilance->db->query("
                                                                                                                        UPDATE " . DB_PREFIX . "projects
                                                                                                                        SET escrow_id = '" . $escrow_id . "',
                                                                                                                        haswinner = '1',
                                                                                                                        winner_user_id = '" . $highbidderid . "'
                                                                                                                        WHERE project_id = '" . $res_rfp['project_id'] . "'
                                                                                                                ", 0, null, __FILE__, __LINE__);
// murugan changes on may 10
																												
																												$ilance->db->query("
                                                                                                                        UPDATE " . DB_PREFIX . "projects
                                                                                                                        SET buyer_fee = '" . $buyerfee1 . "',
                                                                                                                        isbuyerfee = '1',
                                                                                                                        buyerfeeinvoiceid = '" . $buyerfee_id . "'
                                                                                                                        WHERE project_id = '" . $res_rfp['project_id'] . "'
                                                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                                                
														// #### increase product wins for the user
                                                                                                                $ilance->db->query("
                                                                                                                        UPDATE " . DB_PREFIX . "users
                                                                                                                        SET productawards = productawards + 1
                                                                                                                        WHERE user_id = '" . $highbidderid . "'
                                                                                                                ", 0, null, __FILE__, __LINE__);
														
														// #### increase product sold for seller
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "users
															SET productsold = productsold + 1
															WHERE user_id = '" . $res_rfp['user_id'] . "'
														", 0, null, __FILE__, __LINE__);
														
														// #### update winning bidders default pay method to escrow
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "project_bids
															SET buyerpaymethod = 'escrow',
															winnermarkedaspaidmethod = '" . $ilance->db->escape_string($phrase['_escrow']) . "'
															WHERE bid_id = '" . $highbidderbidid . "'
															    AND project_id = '" . $res_rfp['project_id'] . "'
														", 0, null, __FILE__, __LINE__);
														
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "project_realtimebids
															SET buyerpaymethod = 'escrow',
															winnermarkedaspaidmethod = '" . $ilance->db->escape_string($phrase['_escrow']) . "'
															WHERE bid_id = '" . $highbidderbidid . "'
															    AND project_id = '" . $res_rfp['project_id'] . "'
														", 0, null, __FILE__, __LINE__);
                                                                                                                
														$existing = array(
                                                                                                                        '{{project_title}}' => stripslashes($res_rfp['project_title']),
															'{{project_id}}' => $res_rfp['project_id'],
                                                                                                                        '{{owner}}' => $res_owner['username'],
															'{{owneremail}}' => $res_owner['email'],
                                                                                                                        '{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
                                                                                                                        '{{datetime}}' => DATETODAY . ' ' . TIMENOW,
                                                                                                                        '{{totalamount}}' => $ilance->currency->format($totalescrowamount, $res_rfp['currencyid']),
                                                                                                                        '{{winningbidder}}' => $res_winner['username'],
                                                                                                                        '{{winningbidderemail}}' => $res_winner['email'],
															'{{bidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
															'{{shippingcost}}' => $shippinginformation,
															'{{shippingservice}}' => print_shipping_partner($buyershipperid),
															'{{buyerfee}}' => $ilance->currency->format($fee2),
															'{{sellerfee}}' => $ilance->currency->format($fee),
															'{{paymethod}}' => SITE_NAME . ' ' . $phrase['_escrow'],
                                                                                                                );
														
														($apihook = $ilance->api('product_auction_expired_reserve_met_escrow_end')) ? eval($apihook) : false;
														/*
                                                                                                                // #### email owner
                                                                                                                $ilance->email->mail = $res_owner['email'];
                                                                                                                $ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
                                                                                                                $ilance->email->get('product_auction_expired_via_cron_owner');		
                                                                                                                $ilance->email->set($existing);
                                                                                                                // murugan changes on Mar 07 disable email
																												//$ilance->email->send();
                                                                                                                */
                                                                                                                // #### email winning bidder
																												// murugan apr5
																												$sql1 = $ilance->db->query("
																											SELECT *
																											FROM " . DB_PREFIX . "emaillog
																											WHERE user_id = '".$highbidderid."'
																											AND project_id = '".$res_rfp['project_id']."'
																											AND logtype = 'auctionwinner'             
																											AND date LIKE '%" . DATETODAY . "%'
																									", 0, null, __FILE__, __LINE__);
																												if ($ilance->db->num_rows($sql1) == 0)
																												{	
																												$ilance->email->project = $res_rfp['project_id'];
																												$ilance->email->logtype = 'auctionwinner';
                                                                                                                $ilance->email->mail = $res_winner['email'];
                                                                                                                $ilance->email->slng = fetch_user_slng($highbidderid);
                                                                                                                $ilance->email->get('product_auction_expired_via_cron_winner');		
                                                                                                                $ilance->email->set($existing);
                                                                                                                $ilance->email->send();
																												}
																												
																												// murugan changes on mar 28
																												$sql1 = $ilance->db->query("
																											SELECT *
																											FROM " . DB_PREFIX . "emaillog
																											WHERE email = 'ian@greatcollections.com'
																											AND project_id = '".$res_rfp['project_id']."'
																											AND logtype = 'auctionwinner'             
																											AND date LIKE '%" . DATETODAY . "%'
																									", 0, null, __FILE__, __LINE__);
																												if ($ilance->db->num_rows($sql1) == 0)
																												{	
																												$ilance->email->project = $res_rfp['project_id'];
																												$ilance->email->logtype = 'auctionwinner';
																												$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
                                                                                                                $ilance->email->slng = fetch_site_slng();
                                                                                                                $ilance->email->get('product_auction_expired_via_cron_winner');		
                                                                                                                $ilance->email->set($existing);
                                                                                                               $ilance->email->send();
																												}
                                                                                                                
                                                                                                                // #### email admin
                                                                                                                $ilance->email->mail = SITE_EMAIL;
                                                                                                                $ilance->email->slng = fetch_site_slng();
                                                                                                                $ilance->email->get('product_auction_expired_via_cron_admin');		
                                                                                                                $ilance->email->set($existing);
                                                                                                                $ilance->email->send();
                                                                                                                
                                                                                                                $cronlog .= '';
                                                                                                        }
                                                                                                        
													// #### NO ESCROW #######################################################
													else
                                                                                                        {
														if ($methodscount == 1)
														{
															// #### update winning bidders default pay method to only method available by seller..
															$ilance->db->query("
																UPDATE " . DB_PREFIX . "project_bids
																SET buyerpaymethod = '" . $ilance->db->escape_string($methodtype) . "'
																WHERE bid_id = '" . $highbidderbidid . "'
																    AND project_id = '" . $res_rfp['project_id'] . "'
															", 0, null, __FILE__, __LINE__);
															
															$ilance->db->query("
																UPDATE " . DB_PREFIX . "project_realtimebids
																SET buyerpaymethod = '" . $ilance->db->escape_string($methodtype) . "'
																WHERE bid_id = '" . $highbidderbidid . "'
																    AND project_id = '" . $res_rfp['project_id'] . "'
															", 0, null, __FILE__, __LINE__);	
														}
														
														// todo: include fvf to email template..
														
                                                                                                                // no escrow enabled for this product listing
                                                                                                                $ilance->db->query("
                                                                                                                        UPDATE " . DB_PREFIX . "projects
                                                                                                                        SET haswinner = '1',
                                                                                                                        winner_user_id = '" . $highbidderid . "'
                                                                                                                        WHERE project_id = '" . $res_rfp['project_id'] . "'
                                                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                                                
														// #### increase product wins for the user
                                                                                                                $ilance->db->query("
                                                                                                                        UPDATE " . DB_PREFIX . "users
                                                                                                                        SET productawards = productawards + 1
                                                                                                                        WHERE user_id = '" . $highbidderid . "'
                                                                                                                ", 0, null, __FILE__, __LINE__);
														
														// #### increase product sold for seller
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "users
															SET productsold = productsold + 1
															WHERE user_id = '" . $res_rfp['user_id'] . "'
														", 0, null, __FILE__, __LINE__);
                                                                                                                
														// #### dispatch some email
														$existing = array(
                                                                                                                        '{{project_title}}' => stripslashes($res_rfp['project_title']),
                                                                                                                        '{{owner}}' => ucfirst(stripslashes($res_owner['username'])),
															'{{owneremail}}' => $res_owner['email'],
                                                                                                                        '{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
                                                                                                                        '{{datetime}}' => DATETODAY . ' ' . TIMENOW,
                                                                                                                        '{{totalamount}}' => $ilance->currency->format($totalescrowamount, $res_rfp['currencyid']),
                                                                                                                        '{{winningbidder}}' => $res_winner['username'],
                                                                                                                        '{{winningbidderemail}}' => $res_winner['email'],
															'{{bidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
															'{{shippingcost}}' => $shippinginformation,
															'{{shippingservice}}' => print_shipping_partner($buyershipperid)
                                                                                                                );
														
														($apihook = $ilance->api('product_auction_expired_reserve_met_end')) ? eval($apihook) : false;
														
                                                                                                                // #### email owner
                                                                                                                $ilance->email->mail = $res_owner['email'];
                                                                                                                $ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
                                                                                                                $ilance->email->get('product_auction_expired_via_cron_no_escrow_owner');		
                                                                                                                $ilance->email->set($existing);
                                                                                                                $ilance->email->send();
                                                                                                                
                                                                                                                // #### email winning bidder
                                                                                                                $ilance->email->mail = $res_winner['email'];
                                                                                                                $ilance->email->slng = fetch_user_slng($highbidderid);
                                                                                                                $ilance->email->get('product_auction_expired_via_cron_no_escrow_winner');		
                                                                                                                $ilance->email->set($existing);
                                                                                                                $ilance->email->send();
                                                                                                                
                                                                                                                // #### email admin
                                                                                                                $ilance->email->mail = SITE_EMAIL;
                                                                                                                $ilance->email->slng = fetch_site_slng();
                                                                                                                $ilance->email->get('product_auction_expired_via_cron_no_escrow_admin');		
                                                                                                                $ilance->email->set($existing);
                                                                                                                $ilance->email->send();
                                                                                                                
                                                                                                                $cronlog .= '';
                                                                                                        }
                                                                            
                                                                                                        unset($methodtype, $methodscount);
													
													// #### update highest bidders bid status to 'awarded'
                                                                                                        $ilance->db->query("
                                                                                                                UPDATE " . DB_PREFIX . "project_bids
                                                                                                                SET bidstate = '',
                                                                                                                bidstatus = 'awarded',
                                                                                                                date_awarded = '" . DATETIME24H . "'
                                                                                                                WHERE bid_id = '" . $highbidderbidid . "'
                                                                                                                    AND project_id = '" . $res_rfp['project_id'] . "'
                                                                                                        ", 0, null, __FILE__, __LINE__);
                                                                            
                                                                                                        // #### update all highest bidders lowest bids to 'outbid'
                                                                                                        $ilance->db->query("
                                                                                                                UPDATE " . DB_PREFIX . "project_bids
                                                                                                                SET bidstate = '',
                                                                                                                bidstatus = 'outbid'
                                                                                                                WHERE user_id = '" . $highbidderid . "'
                                                                                                                    AND bidstatus != 'awarded'
                                                                                                                    AND project_id = '" . $res_rfp['project_id'] . "'
                                                                                                        ", 0, null, __FILE__, __LINE__);
                                                                                                        
                                                                                                        // #### generate final value fee to the seller (if applicable)
																										// Murugan Changes On NOv 12 For Subscription Based FVF
                                                                                                       // $ilance->accounting_fees->construct_final_value_fee($highbidderbidid, $res_rfp['cid'], $res_rfp['project_id'], 'charge', 'product');
																									   // Murugan changes on mar 23
																										//$ilance->accounting_fees->construct_final_value_fee($highbidderbidid, $res_rfp['user_id'], $res_rfp['project_id'], 'charge', 'product');
																										$ilance->accounting_fees->construct_final_value_fee_new($highbidderbidid, $res_rfp['user_id'], $res_rfp['project_id'], 'charge', 'product');
													
													// #### generate final value donation fee to the seller (if applicable)
													//$ilance->accounting_fees->construct_final_value_donation_fee($res_rfp['project_id'], $highestbid, 'charge');
                                                                                                        
                                                                                                        $cronlog .= '';
                                                                                                }
                                                                                        }
                                                                                }
                                                                                
                                                                                // #### reserve price not met
                                                                                else
                                                                                {
                                                                                        $sql_bids = $ilance->db->query("
                                                                                                SELECT *
                                                                                                FROM " . DB_PREFIX . "project_bids
                                                                                                WHERE project_user_id = '" . $res_rfp['user_id'] . "'
                                                                                                    AND project_id = '" . $res_rfp['project_id'] . "'
                                                                                                GROUP BY user_id
                                                                                        ", 0, null, __FILE__, __LINE__);
                                                                                        if ($ilance->db->num_rows($sql_bids) > 0)
                                                                                        {
                                                                                                while ($res_bids = $ilance->db->fetch_array($sql_bids, DB_ASSOC))
                                                                                                {
                                                                                                        // update all bids placed to outbid/expired
                                                                                                        $ilance->db->query("
                                                                                                                UPDATE " . DB_PREFIX . "project_bids
                                                                                                                SET bidstatus = 'outbid',
                                                                                                                bidstate = 'expired'
                                                                                                                WHERE user_id = '" . $res_bids['user_id'] . "'
                                                                                                                    AND project_id = '" . $res_bids['project_id'] . "'
                                                                                                        ", 0, null, __FILE__, __LINE__);
                                                                                                        
													$lowbiddate = $res_bids['date_added'];
													$lowbidamount = number_format($res_bids['bidamount'], 2);
													
													$existing = array(
														'{{project_title}}' => stripslashes($res_rfp['project_title']),
														'{{bidder}}' => fetch_user('username', $res_bids['user_id']),
														'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
														'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
														'{{lowbiddate}}' => $lowbiddate,
														'{{lowbidamount}}' => $ilance->currency->format($lowbidamount, $res_rfp['currencyid']),
													);
													
													// email owner
													$sql1 = $ilance->db->query("
																SELECT *
																FROM " . DB_PREFIX . "emaillog
																WHERE user_id = '".$res_bids['user_id']."'
																AND project_id = '".$res_rfp['project_id']."'
																AND logtype = 'auctionloserreserve'             
																AND date LIKE '%" . DATETODAY . "%'
														", 0, null, __FILE__, __LINE__);
													if ($ilance->db->num_rows($sql1) == 0)
													{
													$ilance->email->project = $res_rfp['project_id'];
													$ilance->email->logtype = 'auctionloserreserve';
													$ilance->email->mail = fetch_user('email', $res_bids['user_id']);
													$ilance->email->slng = fetch_user_slng($res_bids['user_id']);
													$ilance->email->get('product_auction_expired_reserve_not_met');		
													$ilance->email->set($existing);
													
													($apihook = $ilance->api('product_auction_expired_another_bidder_reserve_not_met')) ? eval($apihook) : false;
													
													$ilance->email->send();
													}
                                                                                                        
                                                                                                        $cronlog .= '';
                                                                                                }
                                                                                        }
                                                                                }
                                                                        }
                                                                        
                                                                        // #### no reserve price in effect
                                                                        else
                                                                        {
                                                                                $sql_bids = $ilance->db->query("
                                                                                        SELECT *
                                                                                        FROM " . DB_PREFIX . "project_bids
                                                                                        WHERE project_user_id = '" . $res_rfp['user_id'] . "'
                                                                                            AND project_id = '" . $res_rfp['project_id'] . "'
                                                                                            AND user_id != '" . $highbidderid . "'
                                                                                        GROUP BY user_id
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                if ($ilance->db->num_rows($sql_bids) > 0)
                                                                                {
                                                                                        while ($res_bids = $ilance->db->fetch_array($sql_bids, DB_ASSOC))
                                                                                        {
                                                                                                // murugan changes on Mar 04 
																								
																								$rev_bid = $ilance->db->query("
																								SELECT MAX(bidamount) AS testamount
																								FROM " . DB_PREFIX . "project_bids
																								 WHERE project_id = '" . $res_bids['project_id'] . "'
                                                                                           			 AND user_id = '" . $res_bids['user_id'] . "'																								
																								");
																								$rev_res = $ilance->db->fetch_array($rev_bid);
																								
																								$sqlproxy = $ilance->db->query("SELECT maxamount FROM " . DB_PREFIX . "proxybid
																															 WHERE project_id = '".$res_bids['project_id']."'
																															 AND user_id = '".$res_bids['user_id']."' ");
																										$resproxy = $ilance->db->fetch_array($sqlproxy);
																										
																										if($rev_res['testamount'] <= $resproxy['maxamount'])
																										{
																											$lastbidamountuser = $resproxy['maxamount'];
																										}
																										else
																										{
																											$lastbidamountuser = $rev_res['testamount'];
																										}
																								// update all bids placed to outbid/expired
                                                                                                $ilance->db->query("
                                                                                                        UPDATE " . DB_PREFIX . "project_bids
                                                                                                        SET bidstatus = 'outbid',
                                                                                                        bidstate = 'expired'
                                                                                                        WHERE user_id = '" . $res_bids['user_id'] . "'
                                                                                                            AND project_id = '" . $res_bids['project_id'] . "'
                                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                                
												$lowbiddate = $res_bids['date_added'];
												//$lowbidamount = number_format($res_bids['bidamount'], 2);
												// $lastbidamountuser murugan chagens on mar 10
												// murugan changes on mar 04 
												$lowbidamount = number_format($lastbidamountuser, 2);
												
												$existing = array(
													'{{project_title}}' => stripslashes($res_rfp['project_title']),
													'{{bidder}}' => fetch_user('username', $res_bids['user_id']),
													'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
													'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
													'{{lowbiddate}}' => print_date($lowbiddate, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
													'{{highbiddate}}' => print_date($highbiddate, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
													'{{lowbidamount}}' => $ilance->currency->format($lowbidamount, $res_rfp['currencyid']),
													'{{highestbid}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
													'{{highbidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
													'{{winningbidder}}' => $res_winner['username'],
												);
												
												// email outbid user
												$sql1 = $ilance->db->query("
												SELECT *
												FROM " . DB_PREFIX . "emaillog
												WHERE user_id = '".$res_bids['user_id']."'
												AND project_id = '".$res_rfp['project_id']."'
												AND logtype = 'auctionloser'             
												AND date LIKE '%" . DATETODAY . "%'
										", 0, null, __FILE__, __LINE__);
												if ($ilance->db->num_rows($sql1) == 0)
												{
												$ilance->email->project = $res_rfp['project_id'];
												$ilance->email->logtype = 'auctionloser';
												$ilance->email->mail = fetch_user('email', $res_bids['user_id']);
												$ilance->email->slng = fetch_user_slng($res_bids['user_id']);
												$ilance->email->get('product_auction_expired_another_bidder');		
												$ilance->email->set($existing);
												
												($apihook = $ilance->api('product_auction_expired_another_bidder_no_reserve')) ? eval($apihook) : false;
												
												$ilance->email->send();
												}
                                                                                                $cronlog .= '';
                                                                                        }
                                                                                }
                                                                                
										$totalescrowamount = $highestbid;
										$shippinginformation = $ilance->currency->format($buyershipcost, $res_rfp['currencyid']);
										$totalescrowamount = ($totalescrowamount + $buyershipcost);
                                                                                
                                                                                // fetch winning bidder
                                                                                $sql_winner = $ilance->db->query("
                                                                                        SELECT *
                                                                                        FROM " . DB_PREFIX . "users
                                                                                        WHERE user_id = '" . $highbidderid . "'
                                                                                        LIMIT 1
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                if ($ilance->db->num_rows($sql_winner) > 0)
                                                                                {
                                                                                        $res_winner = $ilance->db->fetch_array($sql_winner, DB_ASSOC);
                                                                                        
                                                                                        // #### fetch winning bidder info
                                                                                        $sql_owner = $ilance->db->query("
                                                                                                SELECT *
                                                                                                FROM " . DB_PREFIX . "users
                                                                                                WHERE user_id = '" . $res_rfp['user_id'] . "'
                                                                                                LIMIT 1
                                                                                        ", 0, null, __FILE__, __LINE__);
                                                                                        if ($ilance->db->num_rows($sql_owner) > 0)
                                                                                        {
                                                                                                $res_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
                                                                                                
                                                                                                // #### escrow system enabled?
												$methodtype = print_payment_method_title($res_rfp['project_id']);
												$methodscount = print_payment_methods($res_rfp['project_id'], false, true);
												
												// #### USING ESCROW ONLY ########################################################
												if ($ilconfig['escrowsystem_enabled'] AND $res_rfp['filter_escrow'] == '1' AND $methodscount == 1 AND $methodtype == 'escrow')
                                                                                                {
                                                                                                        // #### buyer and seller escrow fee transactions
                                                                                                        // also applies tax to fees
                                                                                                        $fee = fetch_merchant_escrow_fee_plus_tax($res_rfp['user_id'], $totalescrowamount);
                                                                                                        $fee2 = fetch_product_bidder_escrow_fee_plus_tax($highbidderid, $totalescrowamount);
                                                                    
													// #### create escrow transaction
                                                                                                        $escrow_invoice_id = $ilance->accounting->insert_transaction(
                                                                                                                0,
                                                                                                                intval($res_rfp['project_id']),
                                                                                                                0,
                                                                                                                intval($highbidderid),
                                                                                                                intval($res_rfp['user_id']),
                                                                                                                0,
                                                                                                                0,
                                                                                                                $phrase['_escrow_payment_forward'] . ' ' . $phrase['_item_id'] . ' #' . intval($res_rfp['project_id']) . ': ' . $res_rfp['project_title'],
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
													$ilance->db->query("
													UPDATE " . DB_PREFIX . "invoices
													SET statement_date = '".DATETIME24H."'
													WHERE invoiceid = '" . $escrow_invoice_id . "'", 0, null, __FILE__, __LINE__);
																												if($ilconfig['staffsettings_feeinnumber'] != 0)
																												{
																													$buyerfee_calnum = $ilconfig['staffsettings_feeinnumber'];
																												}
																												else
																												{
																													$buyerfee_calnum = 0;
																												}
																												if($ilconfig['staffsettings_feeinpercentage'] != 0)
																												{
																													$buyerfee_calper = ($totalescrowamount * ($ilconfig['staffsettings_feeinpercentage'] / 100));
																												}
																												else
																												{
																													$buyerfee_calper = 0;
																												}
																												if($buyerfee_calnum <= $buyerfee_calper )
																												{																													
																													$buyerfee1 = $buyerfee_calper;																													
																												}
																												else
																												{																													
																													$buyerfee1 = $buyerfee_calnum;																													
																												}	
																												$transactionidnew =  construct_transaction_id();
																												$ilance->db->query("INSERT INTO ".DB_PREFIX."invoices	(projectid,user_id,p2b_user_id,description,amount,totalamount,
																												status,invoicetype,createdate,duedate,custommessage,isbuyerfee,transactionid)
																												VALUES(
																												'".intval($res_rfp['project_id'])."',
																												'".intval($highbidderid)."',
																												'".intval($res_rfp['user_id'])."',
																												'buyer fees',
																												'".$buyerfee1."',
																												'".$buyerfee1."',
																												'paid',
																												'debit',
																												'".DATETIME24H."',
																												'".DATEINVOICEDUE."',
																												'buyer fees for buyer',
																												'1',
																												'".$transactionidnew."'																												
																												)");
																												 $buyerfee_id = $ilance->db->insert_id();
                                                                                                        
                                                                                                        // create product escrow account
                                                                                                        $ilance->db->query("
                                                                                                                INSERT INTO " . DB_PREFIX . "projects_escrow
                                                                                                                (escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, bidamount, shipping, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, fee2invoiceid, status)
                                                                                                                VALUES(
                                                                                                                NULL,
                                                                                                                '" . $highbidderbidid . "',
                                                                                                                '" . $res_rfp['project_id'] . "',
                                                                                                                '" . $escrow_invoice_id . "',
                                                                                                                '" . $res_rfp['user_id'] . "',
                                                                                                                '" . $highbidderid . "',
                                                                                                                '" . DATETIME24H . "',
                                                                                                                '" . sprintf("%01.2f", $highestbid) . "',
                                                                                                                '" . sprintf("%01.2f", $buyershipcost) . "',
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
                                                                                                                winner_user_id = '" . $highbidderid . "'
                                                                                                                WHERE project_id = '" . $res_rfp['project_id'] . "'
                                                                                                        ", 0, null, __FILE__, __LINE__);
                                                                                                        
// murugan changes on may 10
																												
																												$ilance->db->query("
                                                                                                                        UPDATE " . DB_PREFIX . "projects
                                                                                                                        SET buyer_fee = '" . $buyerfee1 . "',
                                                                                                                        isbuyerfee = '1',
                                                                                                                        buyerfeeinvoiceid = '" . $buyerfee_id . "'
                                                                                                                        WHERE project_id = '" . $res_rfp['project_id'] . "'
                                                                                                                ", 0, null, __FILE__, __LINE__);
																												
																												$ilance->db->query("
																														UPDATE  " . DB_PREFIX . "coins
																														SET  sold_qty = sold_qty + 1						
																														 WHERE  coin_id = '".$res_rfp['project_id']."'
																														");	
																												
													// #### increase product wins for the user
                                                                                                        $ilance->db->query("
                                                                                                                UPDATE " . DB_PREFIX . "users
                                                                                                                SET productawards = productawards + 1
                                                                                                                WHERE user_id = '" . $highbidderid . "'
                                                                                                        ", 0, null, __FILE__, __LINE__);
													
													// #### increase product sold for seller
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "users
														SET productsold = productsold + 1
														WHERE user_id = '" . $res_rfp['user_id'] . "'
													", 0, null, __FILE__, __LINE__);
													
													// #### update winning bidders default pay method to escrow
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "project_bids
														SET buyerpaymethod = 'escrow',
														winnermarkedaspaidmethod = '" . $ilance->db->escape_string($phrase['_escrow']) . "'
														WHERE bid_id = '" . $highbidderbidid . "'
														    AND project_id = '" . $res_rfp['project_id'] . "'
													", 0, null, __FILE__, __LINE__);
													
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "project_realtimebids
														SET buyerpaymethod = 'escrow',
														winnermarkedaspaidmethod = '" . $ilance->db->escape_string($phrase['_escrow']) . "'
														WHERE bid_id = '" . $highbidderbidid . "'
														    AND project_id = '" . $res_rfp['project_id'] . "'
													", 0, null, __FILE__, __LINE__);
                                                                                                        
													$shippingservice = $res_rfp['ship_method'] == 'flatrate'
														? print_shipping_partner($buyershipperid)
														: $phrase['_local_pickup_only'];
														
													$shippingcost = $res_rfp['ship_method'] == 'flatrate'
														? $ilance->currency->format($buyershipcost, $res_rfp['currencyid'])
														: $phrase['_none'];
													
													$existing = array(
														'{{project_title}}' => stripslashes($res_rfp['project_title']),
														'{{owner}}' => $res_owner['username'],
														'{{owneremail}}' => $res_owner['email'],
														'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
														'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
														'{{totalamount}}' => $ilance->currency->format($totalescrowamount, $res_rfp['currencyid']),
														'{{winningbidder}}' => $res_winner['username'],
														'{{winningbidderemail}}' => $res_winner['email'],
														'{{bidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
														'{{shippingcost}}' => $shippingcost,
														'{{shippingservice}}' => $shippingservice,
														'{{buyerfee}}' => $ilance->currency->format($fee2),
														'{{sellerfee}}' => $ilance->currency->format($fee),
														'{{paymethod}}' => SITE_NAME . ' ' . $phrase['_escrow'],
													);
													
													($apihook = $ilance->api('product_auction_expired_winner_escrow_no_reserve')) ? eval($apihook) : false;
													/*
                                                                                                        // email owner
                                                                                                        $ilance->email->mail = $res_owner['email'];
                                                                                                        $ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
                                                                                                        $ilance->email->get('product_auction_expired_via_cron_owner');		
                                                                                                        $ilance->email->set($existing);
																										// murugan changes on Mar 07 disable email
                                                                                                        //$ilance->email->send();
                                                                                                        */
                                                                                                        // email winning bidder
																										$sql1 = $ilance->db->query("
																											SELECT *
																											FROM " . DB_PREFIX . "emaillog
																											WHERE user_id = '".$highbidderid."'
																											AND project_id = '".$res_rfp['project_id']."'
																											AND logtype = 'auctionwinner'             
																											AND date LIKE '%" . DATETODAY . "%'
																									", 0, null, __FILE__, __LINE__);
																										if ($ilance->db->num_rows($sql1) == 0)
																										{	
																										$ilance->email->project = $res_rfp['project_id'];
																										$ilance->email->logtype = 'auctionwinner';
                                                                                                        $ilance->email->mail = $res_winner['email'];
                                                                                                        $ilance->email->slng = fetch_user_slng($highbidderid);
                                                                                                        $ilance->email->get('product_auction_expired_via_cron_winner');		
                                                                                                        $ilance->email->set($existing);
                                                                                                        $ilance->email->send();
																										}
                                                                                                        
																										// murugan changes on mar 28
																										$sql1 = $ilance->db->query("
																											SELECT *
																											FROM " . DB_PREFIX . "emaillog
																											WHERE email = 'ian@greatcollections.com'
																											AND project_id = '".$res_rfp['project_id']."'
																											AND logtype = 'auctionwinner'             
																											AND date LIKE '%" . DATETODAY . "%'
																									", 0, null, __FILE__, __LINE__);
																										if ($ilance->db->num_rows($sql1) == 0)
																										{	
																										$ilance->email->project = $res_rfp['project_id'];
																										$ilance->email->logtype = 'auctionwinner';
																										$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
                                                                                                        $ilance->email->slng = fetch_site_slng();
                                                                                                        $ilance->email->get('product_auction_expired_via_cron_winner');		
                                                                                                        $ilance->email->set($existing);
                                                                                                        $ilance->email->send();
																										}
                                                                                                        
																										
                                                                                                        // email admin
                                                                                                        $ilance->email->mail = SITE_EMAIL;
                                                                                                        $ilance->email->slng = fetch_site_slng();
                                                                                                        $ilance->email->get('product_auction_expired_via_cron_admin');		
                                                                                                        $ilance->email->set($existing);
                                                                                                        $ilance->email->send();
                                                                                                        
                                                                                                        $cronlog .= '';
                                                                                                }
                                                                                                
                                                                                                // #### no escrow enabled for this listing
                                                                                                else
                                                                                                {
													if ($methodscount == 1)
													{
														// #### update winning bidders default pay method to only method available by seller..
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "project_bids
															SET buyerpaymethod = '" . $ilance->db->escape_string($methodtype) . "'
															WHERE bid_id = '" . $highbidderbidid . "'
															    AND project_id = '" . $res_rfp['project_id'] . "'
														", 0, null, __FILE__, __LINE__);
														
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "project_realtimebids
															SET buyerpaymethod = '" . $ilance->db->escape_string($methodtype) . "'
															WHERE bid_id = '" . $highbidderbidid . "'
															    AND project_id = '" . $res_rfp['project_id'] . "'
														", 0, null, __FILE__, __LINE__);	
													}
													
													// #### increase product wins for the user
                                                    $ilance->db->query("
                                                            UPDATE " . DB_PREFIX . "projects
                                                            SET haswinner = '1',
                                                            winner_user_id = '" . $highbidderid . "'
                                                            WHERE project_id = '" . $res_rfp['project_id'] . "'
                                                    ", 0, null, __FILE__, __LINE__);
													
													
                                                    
                                                    $ilance->db->query("
                                                            UPDATE " . DB_PREFIX . "users
                                                            SET productawards = productawards + 1
                                                            WHERE user_id = '" . $highbidderid . "'
                                                    ", 0, null, __FILE__, __LINE__);
													
													// #### increase product sold for seller
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "users
														SET productsold = productsold + 1
														WHERE user_id = '" . $res_rfp['user_id'] . "'
													", 0, null, __FILE__, __LINE__);
                                                                                                        
													$shippingservice = $res_rfp['ship_method'] == 'flatrate'
														? print_shipping_partner($buyershipperid)
														: $phrase['_local_pickup_only'];
														
													$shippingcost = $res_rfp['ship_method'] == 'flatrate'
														? $ilance->currency->format($buyershipcost, $res_rfp['currencyid'])
														: $phrase['_none'];
													
													$existing = array(
                                                                        '{{project_title}}' => stripslashes($res_rfp['project_title']),
                                                                        '{{owner}}' => ucfirst(stripslashes($res_owner['username'])),
                                                                        '{{owneremail}}' => $res_owner['email'],
                                                                        '{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
                                                                        '{{datetime}}' => DATETODAY . ' ' . TIMENOW,
                                                                        '{{totalamount}}' => $ilance->currency->format($totalescrowamount, $res_rfp['currencyid']),
                                                                        '{{bidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
                                                                        '{{shippingcost}}' => $shippingcost,
                                                                        '{{shippingservice}}' => $shippingservice,
                                                                        '{{winningbidder}}' => $res_winner['username'],
                                                                        '{{winningbidderemail}}' => $res_winner['email'],
                                                                        '{{paymethod}}' => ''
                                                                                                        );
													
													($apihook = $ilance->api('product_auction_expired_winner_no_escrow_no_reserve')) ? eval($apihook) : false;
													
                                                                                                        // email owner
                                                                                                        $ilance->email->mail = $res_owner['email'];
                                                                                                        $ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
                                                                                                        $ilance->email->get('product_auction_expired_via_cron_no_escrow_owner');		
                                                                                                        $ilance->email->set($existing);
                                                                                                        $ilance->email->send();
                                                                                                        
                                                                                                        // email winning bidder
                                                                                                        $ilance->email->mail = $res_winner['email'];
                                                                                                        $ilance->email->slng = fetch_user_slng($highbidderid);
                                                                                                        $ilance->email->get('product_auction_expired_via_cron_no_escrow_winner');		
                                                                                                        $ilance->email->set($existing);
                                                                                                        $ilance->email->send();
                                                                                                        
                                                                                                        // email admin
                                                                                                        $ilance->email->mail = SITE_EMAIL;
                                                                                                        $ilance->email->slng = fetch_site_slng();
                                                                                                        $ilance->email->get('product_auction_expired_via_cron_no_escrow_admin');		
                                                                                                        $ilance->email->set($existing);
                                                                                                        $ilance->email->send();
                                                                                                        
                                                                                                        $cronlog .= '';
                                                                                                }
                                                                                                
                                                                                                $ilance->db->query("
                                                                                                        UPDATE " . DB_PREFIX . "project_bids
                                                                                                        SET bidstate = '',
                                                                                                        bidstatus = 'awarded',
                                                                                                        date_awarded = '" . DATETIME24H . "'
                                                                                                        WHERE bid_id = '" . $highbidderbidid . "'
                                                                                                            AND project_id = '" . $res_rfp['project_id'] . "'
                                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                                
                                                                                                // #### update the highest bidder's low bids to outbid..
                                                                                                $ilance->db->query("
                                                                                                        UPDATE " . DB_PREFIX . "project_bids
                                                                                                        SET bidstate = '',
                                                                                                        bidstatus = 'outbid'
                                                                                                        WHERE user_id = '" . $highbidderid . "'
                                                                                                            AND bidstatus != 'awarded'
                                                                                                            AND project_id = '" . $res_rfp['project_id'] . "'
                                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                                
                                                                                                // #### generate final value fee to seller (if applicable)
																								// Murugan Changes On Nov 12 For Subscription Based FVF
                                                                                               // $ilance->accounting_fees->construct_final_value_fee($highbidderbidid, $res_rfp['cid'], $res_rfp['project_id'], 'charge', 'product');
																								 // murugan changes on mar 23
																								// $ilance->accounting_fees->construct_final_value_fee($highbidderbidid, $res_rfp['user_id'], $res_rfp['project_id'], 'charge', 'product');
																								 $ilance->accounting_fees->construct_final_value_fee_new($highbidderbidid, $res_rfp['user_id'], $res_rfp['project_id'], 'charge', 'product');
												
												// #### generate final value donation fee to seller (if applicable)
                                                                                                //$ilance->accounting_fees->construct_final_value_donation_fee($res_rfp['project_id'], $highestbid, 'charge');
                                                                                                
                                                                                                $cronlog .= '';
                                                                                        }
                                                                                }
                                                                        }
                                                                }
                                                                
                                                                // #### STOP BIDDING : we don't have a highest winning bidder
                                                                else
                                                                {
									$existing = array(
										'{{project_title}}' => stripslashes($res_rfp['project_title']),
										'{{owner}}' => fetch_user('username', $res_rfp['user_id']),
										'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
										'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
									);
									
									($apihook = $ilance->api('product_auction_expired_no_winning_bidder')) ? eval($apihook) : false;
									/*
									// #### email owner
									$ilance->email->mail = fetch_user('email', $res_rfp['user_id']);
									$ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
									$ilance->email->get('product_auction_expired_via_cron_no_bidder_owner');		
									$ilance->email->set($existing);
									//$ilance->email->send();
									
									// #### email admin
									$ilance->email->mail = SITE_EMAIL;
									$ilance->email->slng = fetch_site_slng();
									$ilance->email->get('product_auction_expired_via_cron_no_bidder_admin');		
									$ilance->email->set($existing);
									//$ilance->email->send();
									*/
									$cronlog .= '';
                                                                }    
                                                        }            
                                                }
												}
                                        }
                                }
                        }
                        unset($res_rfp);
                }
                
                // #### EXPIRE PRODUCT AUCTIONS WITH 0 BUY NOW QTY #############
                
                // this will catch product auctions that end early due to the seller having 1 qty
                // left and a buyer purchasing that item via Buy Now (escrow or offline mode). at
                // that point, a trigger will update the project table with buynow_qty = 0 and this
                // task will end the auction early.
                $sql_rfp = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "projects
                        WHERE status = 'open'
                                AND project_state = 'product'
                                AND project_details != 'unique'
                                AND buynow_price > 0
                                AND buynow_qty = '0'
                                AND buynow = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_rfp) > 0)
                {
                        $res_owner = array();
                        
                        while ($res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC))
                        {
                                $sql_owner = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $res_rfp['user_id'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_owner) > 0)
                                {
                                        $res_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
                                                
                                        // #### update open product auctions to expired
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET status = 'expired',
                                                close_date = '" . DATETIME24H . "'
                                                WHERE project_id = '" . $res_rfp['project_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        // subtract auction count
                                        reduce_catalog_count($res_rfp['coin_series_denomination_no'],$res_rfp['coin_series_unique_no'],$res_rfp['pcgs']);
                                        
					$existing = array(
                                                '{{project_title}}' => stripslashes($res_rfp['project_title']),
                                                '{{project_id}}' => $res_rfp['project_id'],
                                                '{{owner}}' => $res_owner['username'],
                                                '{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
                                                '{{close_date}}' => DATETIME24H,
                                        );
					
					($apihook = $ilance->api('product_auction_expired_no_buynow_qty')) ? eval($apihook) : false;
					
                                        // email owner
                                        $ilance->email->mail = $res_owner['email'];
                                        $ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
                                        $ilance->email->get('product_auction_ended_early_via_cron_no_buynow_qty');		
                                        $ilance->email->set($existing);
										// murugan changes on july 12 for disable the email 188
                                        //$ilance->email->send();
                                        
                                        // email admin
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        $ilance->email->get('product_auction_ended_early_via_cron_no_buynow_qty_admin');		
                                        $ilance->email->set($existing);
										//// murugan changes on july 12 for disable the email 188
                                        //$ilance->email->send();
                                        
                                        $cronlog .= '';
                                }
                        }
			
                        unset($res_rfp, $res_owner);
                }
                
		      
                return $cronlog;
        }
        
        function other()
        {
                global $ilance, $phrase, $ilconfig, $ilpage;
                
                $ilance->feedback = construct_object('api.feedback');
                $ilance->accounting = construct_object('api.accounting');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                $ilance->email = construct_dm_object('email', $ilance);
                
                $cronlog = '';
                
                 
                
                // #### check-up customers rating and update `users` table
                $awardupdate = $ilance->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "users
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($awardupdate) > 0)
                {
                        while ($res = $ilance->db->fetch_array($awardupdate, DB_ASSOC))
                        {
                                // update service proposal wins / awards
                                fetch_service_bids_awarded($res['user_id'], true);
                                
                                // update product wins / awards
                                fetch_product_bids_awarded($res['user_id'], true);
                                
                                // update feedback, rating and score
                                $ilance->feedback->construct_ratings($res['user_id']);
                                
                                $cronlog .= '';
                        }
                        unset($res);
                }
                
                // In many cases, an admin or developer may remove bids within phpmyadmin
                // for testing purposes if somehow, this bid had an escrow account tied to it,
                // we must cancel the escrow account so no unlinking can occur within the
                // production system.  We will also cancel any invoices that are still unpaid
                $sqlescrowbids = $ilance->db->query("
                        SELECT bid_id, invoiceid, project_id
                        FROM " . DB_PREFIX . "projects_escrow
                        WHERE status != 'finished'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlescrowbids) > 0)
                {
                        while ($bids = $ilance->db->fetch_array($sqlescrowbids, DB_ASSOC))
                        {
                                // check for orphan bids
                                $sqlexist = $ilance->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "project_bids
                                        WHERE bid_id = '" . $bids['bid_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sqlexist) == 0)
                                {
                                        // bid does not exist anymore - cancel escrow account
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects_escrow
                                                SET status = 'cancelled'
                                                WHERE bid_id = '" . $bids['bid_id'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        // cancel associated invoice as well (if its not paid)
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "invoices
                                                SET status = 'cancelled'
                                                WHERE invoiceid = '" . $bids['invoiceid'] . "'
                                                    AND status != 'paid'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $cronlog .= '';
                                }
                        }
                        unset($bids);
                        
                        while ($auctions = $ilance->db->fetch_array($sqlescrowbids, DB_ASSOC))
                        {
                                // check for orphan auctions
                                $sqlexist = $ilance->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "project_bids
                                        WHERE project_id = '" . $auctions['project_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sqlexist) == 0)
                                {
                                        // auction does not exist anymore - remove escrow account
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects_escrow
                                                SET status = 'cancelled'
                                                WHERE project_id = '" . $auctions['invoiceid'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        // cancel associated invoice as well (if its not paid)
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "invoices
                                                SET status = 'cancelled'
                                                WHERE invoiceid = '" . $auctions['invoiceid'] . "'
                                                    AND status != 'paid'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $cronlog .= '';
                                }
                        }
                        unset($auctions);
                }
                
                // In some cases, an auction could have a winning bid, but the bid or user
                // becomes removed by an admin or staff resulting in an unlinked awarded
                // auction.  Let's prevent that via search and delist
                $sqlapproved = $ilance->db->query("
                        SELECT project_id
                        FROM " . DB_PREFIX . "projects
                        WHERE status = 'approval_accepted' OR status = 'wait_approval'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlapproved) > 0)
                {
                        while ($projects = $ilance->db->fetch_array($sqlapproved, DB_ASSOC))
                        {
                                // check for orphan awards
                                $sqlbidexist = $ilance->db->query("
                                        SELECT bid_id
                                        FROM " . DB_PREFIX . "project_bids
                                        WHERE project_id = '" . $projects['project_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sqlbidexist) == 0)
                                {
                                        // bid does not exist anymore - delist auction
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET status = 'delisted'
                                                WHERE project_id = '" . $projects['project_id'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $cronlog .= '';
                                }
                        }
                        unset($projects);
                }
                
                $cronlog .= $this->listings_expired_to_finished();
                
                return $cronlog;
        }
        
        /**
        * Function to process a listing relist
        *
        * @param       integer        project id
        * @param       boolean        prevent email from sending? (default false)
        *
        * @return      boolean        true or false based on successful auto-relist of a valid listing
        */
        function process_auction_relister($projectid = 0, $dontsendemail = false)
        {
                global $ilance, $ilconfig, $ilpage;
                
                $array = array();
                
                $sql = $ilance->db->query("
                        SELECT bids, autorelist, date_starts, date_end, project_state, buynow, buynow_qty, user_id, project_title
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                                AND autorelist_date = '0000-00-00 00:00:00'
                        ORDER BY user_id ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        
                        if ($res['autorelist'] == '1' AND $res['bids'] == '0')
                        {
                                // new ending date for listing
                                if ($res['project_state'] == 'product')
                                {
                                        if ($res['buynow'])
                                        {
                                                if ($res['buynow_qty'] <= 0)
                                                {
                                                        // we cannot relist this buy now listing due to no available buy now quantity
                                                        return false;
                                                }
                                        }
                                        
                                        $rfpurl = HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($projectid);
                                        $emailx = 'product_auction_relisted_via_cron';
                                        $duration = $ilconfig['productupsell_autorelistmaxdays'];        
                                }
                                else
                                {
                                        $rfpurl = HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($projectid);
                                        $emailx = 'service_auction_relisted_via_cron';
                                        $duration = $ilconfig['serviceupsell_autorelistmaxdays'];
                                }
                                
                                $moffset = ($duration * 86400);
                                
                                $start_date = DATETIME24H;
                                $date_end = date("Y-m-d H:i:s", (strtotime($start_date) + $moffset));
                                
                                // update listing with new ending date and record the date we're auto-relisting
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET autorelist_date = '" . DATETIME24H . "',
                                        date_end = '" . $date_end . "',
                                        close_date = '0000-00-00 00:00:00',
                                        status = 'open'
                                        WHERE project_id = '" . intval($projectid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                
                                // #### send email to owner about auto-relisting actions
				if ($dontsendemail == false)
				{
					// #### email owner
					$ilance->email = construct_dm_object('email', $ilance);
					
					$ilance->email->mail = fetch_user('email', $res['user_id']);
					$ilance->email->slng = fetch_user_slng($res['user_id']);
					
					$ilance->email->get($emailx);		
					$ilance->email->set(array(
						'{{project_title}}' => stripslashes($res['project_title']),
						'{{owner}}' => fetch_user('username', $res['user_id']),
						'{{rfpurl}}' => $rfpurl,
						'{{new_date_end}}' => $date_end,
						'{{relisted_days}}' => $duration,
					));
					
					$ilance->email->send();
				}
                                
                                return true;
                        }
                }
                
                return false;
        }
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
