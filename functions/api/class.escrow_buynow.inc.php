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

if (!class_exists('escrow'))
{
	exit;
}

/**
* Function to handle escrow payments for buy now items
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class escrow_buynow extends escrow
{
	/**
        * Function to process a purchase now escrow payment for a particular user for a specific amount.  This function takes final value fee and insertion
        * fee permission exemptions into consideration.
        *
        * This function is also responsible for updating `buynow_purchases` field in the listings table so members can sort their listings based on most/least item sales.
        *
        * @param       string       payment method (offline or account)
        * @param       integer      project id
        * @param       integer      order qty
        * @param       integer      order amount
        * @param       integer      order total amount
        * @param       integer      seller id
        * @param       integer      buyer id
        * @param       bool         is shipping address required?
        * @param       integer      shipping address id profile for buyers location
        * @param       integer      account id
        * @param       string       buyers selected payment method string (just the title to show on buying/selling act)
        * @param       integer      buyers selected shipping cost
        * @param       integer      buyers selected shipping service id
        *
        * @return      array        Returns true or false if payment could be completed including order id
        */
		// Murugan Changes On OCT 08
		// Here added variable murugan
        function instant_purchase_now($method = '', $projectid = 0, $qty = 0, $amount = 0, $total = 0, $seller_id = 0, $buyer_id = 0, $shipping_address_required = 1, $shipping_address_id = 0, $accountid = 0, $buyerpaymethod = 'Unknown', $buyershipcost = 0, $buyershipperid = 0,$murugan = 0)
        {
		
                global $ilance, $myapi, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                
                $ilance->subscription = construct_object('api.subscription');
                $ilance->accounting = construct_object('api.accounting');
		$ilance->email = construct_dm_object('email', $ilance);
		
		// #### require shipping backend ###############################
		require_once(DIR_CORE . 'functions_shipping.php');
                
		$currencyid = fetch_auction('currencyid', $projectid);
		
                $shippingaddress = ($shipping_address_required)
			? print_shipping_address_text(intval($buyer_id))
			: '_no_shipping_address_required_assuming_digital_item_delivery_please_communicate_with_customer';

                // #### INSTANT PURCHASE VIA OFFLINE PAYMENT NO ESCROW #########
                if ($method == 'offline')
                {
                        // #### FINAL VALUE FEE ################################
                        // calculate final value fee to seller for the total amount passed to this function
                        // we will not include shipping in the final value fee amount to calculate
                        // try to debit funds, else create unpaid txn
                        $invoiceid = $isfvfpaid = $avail_bal = $total_bal = 0;
                        $paymentstatus = $phrase['_no_charge'];
                        $cid = fetch_auction('cid', $projectid);
                        // Murugan Changes On Nov 15 For Subscription Based FVF
                        //$fvf = calculate_final_value_fee(($amount * $qty), $cid, 'product');
						$fvf = calculate_final_value_fee_new(($amount * $qty), $projectid, 'product');
                        
                        // check if we're exempt from fvf fees
                        if (!empty($seller_id) AND $seller_id > 0 AND $ilance->subscription->check_access($seller_id, 'fvfexempt') == 'yes')
                        {
                                $fvf = 0;
                        }
                        
						//check for seller is inhouse? if inhouse no fvf
						$query12="SELECT *  FROM " . DB_PREFIX . "users WHERE user_id = '".$seller_id."' and house_account=1";
						$result12=$ilance->db->query($query12);
						if($ilance->db->num_rows($result12)==1)
						{
							$fvf = 0;
						}
			// #### do we have a fee? ##############################
                      if ($fvf > 0)
                       {
					 
                                                $fvf = sprintf("%01.2f", $fvf);
                                                
                                                // charge escrow fee to seller as a separate transaction
                                                // this creates a transaction history item for the buyer of item
                                                $txn = construct_transaction_id();
                                                
                                                $availablebalance = fetch_user('available_balance', $seller_id);
                                                $totalbalance = fetch_user('total_balance', $seller_id);
                                                $autopayment = fetch_user('autopayment', $seller_id);
                                                
                                               
												
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                (invoiceid, projectid, user_id, description, amount, totalamount, status, invoicetype, paymethod, ipaddress, createdate, duedate,paiddate, custommessage, transactionid, isfvf)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($projectid) . "',
                                                                '" . intval($seller_id) . "',
                                                                '" . $ilance->db->escape_string($phrase['_purchase_now_seller_final_value_fee']) . " - " . $ilance->db->escape_string(fetch_auction('project_title', $projectid)) . " #" . $projectid . "',
                                                                '" . $fvf . "',
                                                                '" . $fvf . "',
                                                                'paid',
                                                                'debit',
                                                                'account',
                                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                                '" . DATETIME24H . "',
                                                                '" . DATETIME24H . "',
																'" . DATETIME24H . "',
                                                                '" . $ilance->db->escape_string($phrase['_may_include_applicable_taxes']) . "',
                                                                '" . $txn . "',
                                                                '1')
                                                        ");
                                                        
                                                        $fvfinvoiceid = $ilance->db->insert_id();
                                                        $isfvfpaid = '1';
                                                
                                        }
                        
                        // #### create buy now order ###########################
                        // in this case, since escrow is disabled the fee column
                        // will still show a fee based on the "final value fee" vs the escrow commission.
                        // and the fee2 column will be just 0.00 (fee to buyer which should be nill)
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "buynow_orders
                                (orderid, project_id, buyer_id, owner_id, qty, amount, fvf, fvfinvoiceid, isfvfpaid, ship_required, ship_location, orderdate, buyerpaymethod, buyershipcost, buyershipperid, status,item_end_date)
                                VALUES(
                                NULL,
                                '" . intval($projectid) . "',
                                '" . intval($buyer_id) . "',
                                '" . intval($seller_id) . "',
                                '" . intval($qty) . "',
                                '" . sprintf("%01.2f", $total) . "',
                                '" . sprintf("%01.2f", $fvf) . "',
                                '" . intval($invoiceid) . "',
                                '" . intval($isfvfpaid) . "',
                                '" . intval($shipping_address_required) . "',
                                '" . $ilance->db->escape_string($shippingaddress) . "',
                                '" . DATETIME24H . "',
				'" . $ilance->db->escape_string($buyerpaymethod) . "',
				'" . sprintf("%01.2f", $buyershipcost) . "',
				'" . intval($buyershipperid) . "',
                                'offline',
								'".fetch_auction('date_end', $projectid)."')
                        ");
                        
                        $neworderid = $ilance->db->insert_id();
                        
                        // #### associate this fvf to a buynow order id ########
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "invoices
                                SET buynowid = '" . $neworderid . "'
                                WHERE invoiceid = '" . intval($invoiceid) . "'
                        ");
                        
                        $bqty = fetch_auction('buynow_qty', $projectid);
                        $buynowqty = ($bqty - $qty);
                        if ($buynowqty <= 0)
                        {
                                $buynowqty = 0;
                        }
                        
                        // #### update the qty available for this item #########
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET hasbuynowwinner = '1',
                                buynow_qty = " . intval($buynowqty) . ",
                                buynow_purchases = buynow_purchases + 1
                                WHERE project_id = '" . intval($projectid) . "'
                                LIMIT 1
                        ");
                        
                        // #### if there are no qty available then change open status to expired
                        $buynow_qtyleft = $bqty;
                        $filtered_auctiontype = fetch_auction('filtered_auctiontype', $projectid);
                        $projectstatus = fetch_auction('status', $projectid);
                        
			$existing = array(
                                '{{buyer}}' => $_SESSION['ilancedata']['user']['username'],
                                '{{buyer_fullname}}' => $_SESSION['ilancedata']['user']['fullname'],
                                '{{buyer_email}}' => $_SESSION['ilancedata']['user']['email'],
                                '{{seller}}' => fetch_user('username', intval($seller_id)),
                                '{{project_title}}' => stripslashes(fetch_auction('project_title', $projectid)),
                                '{{project_id}}' => $projectid,
                                '{{qty}}' => $qty,
                                '{{amount}}' => $ilance->currency->format($amount, $currencyid),
                                '{{total}}' => $ilance->currency->format($total, $currencyid),
                                '{{ship_costs}}' => $ilance->currency->format($buyershipcost, $currencyid),
                                '{{shippingaddress}}' => $shippingaddress,
				'{{shippingservice}}' => print_shipping_partner($buyershipperid),
                                '{{fvf}}' => $ilance->currency->format($fvf),
                                '{{fvftotal}}' => $ilance->currency->format($fvf),
                                '{{paymentstatus}}' => $paymentstatus,
				'{{paymentmethod}}' => print_fixed_payment_method($buyerpaymethod),
                        );
			
                        $ilance->email->mail = fetch_user('email', intval($buyer_id));
                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                        
                        $ilance->email->get('buynow_offline_purchase_buyer');		
                        $ilance->email->set($existing);
                        
                        $ilance->email->send();
                        
                        $ilance->email->mail = fetch_user('email', intval($seller_id));
                        $ilance->email->slng = fetch_user_slng($seller_id);
                        
                        $ilance->email->get('buynow_offline_purchase_seller');		
                        $ilance->email->set($existing);
                        
                         // Murugan Changes On Mar 2 for Disable Email
						//$ilance->email->send();
                        
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        
                        $ilance->email->get('buynow_offline_purchase_admin');		
                        $ilance->email->set($existing);
                        
                        $ilance->email->send();
                        
                        return array(true, $neworderid);
                }
                    
                // #### INSTANT PURCHASE TO ESCROW VIA ACCOUNT BALANCE #########
                else if ($method == 'account')
                {
                       
						$sel_balance = $ilance->db->query("
                                SELECT available_balance, total_balance
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($buyer_id) . "'
                        ");
                        
                                $res_balance = $ilance->db->fetch_array($sel_balance, DB_ASSOC);
                                
                                
                                        $area_title = $phrase['_instant_purchase_to_escrow_via_online_account'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_instant_purchase_to_escrow_via_online_account'];
                                        
                                        // the total amount the buyer forwarded would have included any escrow fees if applicable
                                        // because of this our total amount has changed so lets find out what the original amount
                                        // is including any shipping costs to forward into the buynow escrow account
										// murugan changs on Oct 08 Here i added new variable as murugan for the promocode reduction..
										// promocode is deducted from the rawtotal. And we add the actualrawtotal
                                        $item_costs = fetch_auction('buynow_price', $projectid);
										$item_end_date= fetch_coin_table('End_Date', $projectid);
										$actualrawtotal = ($buyershipcost > 0)
						? sprintf("%01.2f", ($item_costs * $qty) + $buyershipcost)
						: sprintf("%01.2f", ($item_costs * $qty));
                                        $rawtotal = ($buyershipcost > 0)
						? sprintf("%01.2f", ($item_costs * $qty) + $buyershipcost - $murugan)
						: sprintf("%01.2f", ($item_costs * $qty)- $murugan);
                        
                                        // #### ESCROW FEES ####################
                                        // we'll populate the fee fields we expect to be paid from the seller or buyer of this listing
                                        // $fee = seller escrow fee
                                        // $escrowfeebuyer = buyer escrow fee
                                        
                                        $escrowfee = $escrowfeebuyer = 0;
                                        $escrowfeebuyerinvoiceid = $isescrowfeebuyerpaid = $escrowfeeinvoiceid = $isescrowfeepaid = '0';
                                        
                                        if ($ilconfig['escrowsystem_escrowcommissionfees'])
                                        {
                                                $ilance->tax = construct_object('api.tax');
                                                
                                                // escrow commission fees to auction owner enabled
                                                if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
                                                {
                                                        // fixed escrow cost to provider for release of funds
                                                        $escrowfee = sprintf("%01.2f", $ilconfig['escrowsystem_merchantfixedprice']);
                                                }
                                                else
                                                {
                                                        if ($ilconfig['escrowsystem_merchantpercentrate'] > 0)
                                                        {
                                                                // percentage rate of total winning bid amount
                                                                // which would be the same as the amount being forwarded into escrow
                                                                $escrowfee = sprintf("%01.2f", ($rawtotal * $ilconfig['escrowsystem_merchantpercentrate'] / 100));
                                                        }
                                                }
                                                
                                                if ($escrowfee > 0)
                                                {
                                                        $taxamount = 0;
                                                        $istaxable = '0';
                                                        $taxinfo = '';
                                                        if ($ilance->tax->is_taxable(intval($seller_id), 'commission'))
                                                        {
                                                                // fetch tax amount to charge for this invoice type
                                                                $taxamount = $ilance->tax->fetch_amount(intval($seller_id), $escrowfee, 'commission', 0);
                                                                $taxinfo = $ilance->tax->fetch_amount(intval($seller_id), $escrowfee, 'commission', 1);
                                                                $istaxable = '1';
                                                        }
                                                        
                                                        // exact amount to charge buyer
                                                        $escrowfeenotax = $escrowfee;
                                                        $escrowfee = sprintf("%01.2f", ($escrowfee + $taxamount));
                                                        
                                                        // charge escrow fee to seller as a separate transaction
                                                        // this creates a transaction history item for the buyer of item
                                                        $txn = construct_transaction_id();
                                                        
                                                        $availablebalance = fetch_user('available_balance', $seller_id);
                                                        $totalbalance = fetch_user('total_balance', $seller_id);
                                                        $autopayment = fetch_user('autopayment', $seller_id);
                                                        
                                                        														
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "invoices
                                                                        (invoiceid, projectid, user_id, description, amount, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, custommessage, transactionid, isescrowfee)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($projectid) . "',
                                                                        '" . intval($seller_id) . "',
                                                                        '" . $ilance->db->escape_string($phrase['_purchase_now_seller_escrow_fee']) . " - " . $ilance->db->escape_string(fetch_auction('project_title', $projectid)) . " #" . $projectid . "',
                                                                        '" . $escrowfeenotax . "',
                                                                        '" . $escrowfee . "',
                                                                        '" . $istaxable . "',
                                                                        '" . $taxamount . "',
                                                                        '" . $taxinfo . "',
                                                                        'unpaid',
                                                                        'escrow',
                                                                        'account',
                                                                        '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                                        '" . DATETIME24H . "',
                                                                        '" . DATETIME24H . "',
                                                                        '" . $ilance->db->escape_string($phrase['_may_include_applicable_taxes']) . "',
                                                                        '" . $txn . "',
                                                                        '1')
                                                                ");
                                                                
                                                                $escrowfeeinvoiceid = $ilance->db->insert_id();
                                                                $isescrowfeepaid = '0';         
                                                       
                                                }
                        
                                                // #### PRODUCT BUYER ESCROW FEES ##########################
                                                // we'll populate the fee2 field which denotes any fees the buyer of this buynow purchase
                                                // must pay the site owner .. we'll also calculate any tax if applicable to ensure that the
                                                // fee to the buyer will include the full fee amount + any applicable taxes (for commission txns)
                                           
                                                // escrow commission fees to auction owner enabled
                                                if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
                                                {
                                                        // fixed escrow cost to buyer
                                                        $escrowfeebuyer = sprintf("%01.2f", $ilconfig['escrowsystem_bidderfixedprice']);
                                                }
                                                else
                                                {
                                                        if ($ilconfig['escrowsystem_bidderpercentrate'] > 0)
                                                        {
                                                                // percentage rate of total winning bid amount
                                                                // which would be the same as the amount being forwarded into escrow
                                                                $escrowfeebuyer = sprintf("%01.2f", ($rawtotal * $ilconfig['escrowsystem_bidderpercentrate'] / 100));
                                                        }
                                                }
                                                
                                                if ($escrowfeebuyer > 0)
                                                {
                                                        $taxamount = 0;
                                                        $istaxable = '0';
                                                        $taxinfo = '';
                                                        if ($ilance->tax->is_taxable(intval($buyer_id), 'commission'))
                                                        {
                                                                // fetch tax amount to charge for this invoice type
                                                                $taxamount = $ilance->tax->fetch_amount(intval($buyer_id), $escrowfeebuyer, 'commission', 0);
                                                                $taxinfo = $ilance->tax->fetch_amount(intval($buyer_id), $escrowfeebuyer, 'commission', 1);
                                                                $istaxable = '1';
                                                        }
                                                        
                                                        // exact amount to charge buyer
                                                        $escrowfeebuyernotax = $escrowfeebuyer;
                                                        $escrowfeebuyer = sprintf("%01.2f", ($escrowfeebuyer + $taxamount));
                                                        
                                                        // charge escrow fee to buyer as a separate transaction
                                                        // this creates a transaction history item for the buyer of item
                                                        $txn = construct_transaction_id();
                                                        
                                                        $availablebalance = fetch_user('available_balance', $buyer_id);
                                                        $totalbalance = fetch_user('total_balance', $buyer_id);
														$autopayment = fetch_user('autopayment', $buyer_id);
							
                                                        
														
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "invoices
                                                                        (invoiceid, projectid, user_id, description, amount, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, custommessage, transactionid, isescrowfee)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($projectid) . "',
                                                                        '" . intval($buyer_id) . "',
                                                                        '" . $ilance->db->escape_string($phrase['_purchase_now_buyer_escrow_fee']) . " - " . $ilance->db->escape_string(fetch_auction('project_title', $projectid)) . " #" . $projectid . "',
                                                                        '" . $escrowfeebuyernotax . "',
                                                                        '" . $escrowfeebuyer . "',
                                                                        '" . $istaxable . "',
                                                                        '" . $taxamount . "',
                                                                        '" . $taxinfo . "',
                                                                        'unpaid',
                                                                        'escrow',
                                                                        'account',
                                                                        '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                                        '" . DATETIME24H . "',
                                                                        '" . DATETIME24H . "',
                                                                        '" . $ilance->db->escape_string($phrase['_may_include_applicable_taxes']) . "',
                                                                        '" . $txn . "',
                                                                        '1')
                                                                ");
                                                                
                                                                $escrowfeebuyerinvoiceid = $ilance->db->insert_id();
                                                                $isescrowfeebuyerpaid = '0';
                                                       
                                                }
                                        }
                                        
                                        // this creates a transaction history item for the buyer of item
                                        $transactionid = construct_transaction_id();
                                        
										//sales tax for buyer logic
										//suku 2:37 PM 1/20/2011
										
										$istaxable=0;
										$taxamount='';
										$taxinfo='';
										
										$ilance->tax = construct_object('api.tax');
										if ($ilance->tax->is_taxable(intval($buyer_id), 'buynow'))
                                                        {
														 
                                                                // fetch tax amount to charge for this invoice type
                                                                $taxamount = $ilance->tax->fetch_amount(intval($buyer_id), $rawtotal, 'buynow', 0);
                                                                $taxinfo = $ilance->tax->fetch_amount(intval($buyer_id), $rawtotal, 'buynow', 1);
                                                                $istaxable = '1';
                                                        }
														
										$totalamount=$rawtotal+$taxamount;
										
                                         $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "invoices
                                                (invoiceid, projectid, user_id, p2b_user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid,statement_date)
                                                VALUES(
                                                NULL,
                                                '" . intval($projectid) . "',
                                                '" . intval($buyer_id) . "',
                                                '" . intval($seller_id) . "',
                                                '" . $ilance->db->escape_string($phrase['_purchase_now']) . " " . $ilance->db->escape_string($phrase['_escrow_payment_forward']) . " - " . $ilance->db->escape_string(fetch_auction('project_title', $projectid)) . " #" . $projectid . "',
                                                '" . $rawtotal . "',
                                                '" . $totalamount . "',
                                                '" . $totalamount . "',
												'" . $istaxable . "',
												'" . $taxamount . "',
												'" . $taxinfo . "',
												'unpaid',
                                                'escrow',
                                                'account',
                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                '" . DATETIME24H . "',
                                                '" . DATETIME24H . "',
                                                '',
                                                '" . $ilance->db->escape_string($phrase['_funds_held_within_escrow_until_item_has_been_delivered']) . " - " . DATETIME24H . "',
                                                '" . $transactionid . "',
												'".$item_end_date."')
                                        ");
					
					
                                        $newinvoiceid = $ilance->db->insert_id();
                                        
                                                                                
                                        // this creates a separate escrow fee transaction (which we can refund if seller refunds escrow amount in future)
                                        
                                        // #### CALCULATE FVF TO SELLER FOR THIS ORDER #####################################
                                        // this is a separate final value fee if applicable which is separate from the escrow fee logic above
                                        $cid = fetch_auction('cid', $projectid);
                                        $fvfbuyer = '0.00'; // not in use
                                        $fvfinvoiceid = $isfvfpaid = '0';                                          
                                        // Murugan Changes On Nov 15 For Subscription Based FVF                                          
                                        //$fvf = calculate_final_value_fee($rawtotal, $cid, 'product');
										// murugan changes on mar 23
										//$fvf = calculate_final_value_fee($rawtotal, $seller_id, 'product');
										$fvf = calculate_final_value_fee_new($rawtotal, $projectid, 'product');
                                       
                                        // check if we're exempt from fvf fees
                                        if (!empty($seller_id) AND $seller_id > 0 AND $ilance->subscription->check_access($seller_id, 'fvfexempt') == 'yes')
                                        {
                                                $fvf = 0;
                                        }
                                        //check for seller is inhouse? if inhouse no fvf
										$query12="SELECT *  FROM " . DB_PREFIX . "users WHERE user_id = '".$seller_id."' and house_account=1";
										$result12=$ilance->db->query($query12);
										if($ilance->db->num_rows($result12)==1)
										{
											$fvf = 0;
										}
                                        if ($fvf > 0)
                                        {
										
                                                $fvf = sprintf("%01.2f", $fvf);
                                                
                                                // charge escrow fee to seller as a separate transaction
                                                // this creates a transaction history item for the buyer of item
                                                $txn = construct_transaction_id();
                                                
                                                $availablebalance = fetch_user('available_balance', $seller_id);
                                                $totalbalance = fetch_user('total_balance', $seller_id);
                                                $autopayment = fetch_user('autopayment', $seller_id);
                                                
                                               
												
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                (invoiceid, projectid, user_id, description, amount, totalamount, status, invoicetype, paymethod, ipaddress, createdate, duedate,paiddate, custommessage, transactionid, isfvf,statement_date)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($projectid) . "',
                                                                '" . intval($seller_id) . "',
                                                                '" . $ilance->db->escape_string($phrase['_purchase_now_seller_final_value_fee']) . " - " . $ilance->db->escape_string(fetch_auction('project_title', $projectid)) . " #" . $projectid . "',
                                                                '" . $fvf . "',
                                                                '" . $fvf . "',
                                                                'paid',
                                                                'debit',
                                                                'account',
                                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                                '" . DATETIME24H . "',
                                                                '" . DATETIME24H . "',
																'" . DATETIME24H . "',
                                                                '" . $ilance->db->escape_string($phrase['_may_include_applicable_taxes']) . "',
                                                                '" . $txn . "',
                                                                '1',
																'".$item_end_date."')
                                                        ");
                                                        
                                                        $fvfinvoiceid = $ilance->db->insert_id();
                                                        $isfvfpaid = '1';
                                                
                                        }
                                        
                                        // this creates the buy now order and any applicable invoicing logic
                                        $ilance->db->query(utf8_decode("
                                                INSERT INTO " . DB_PREFIX . "buynow_orders
                                                (orderid, project_id, buyer_id, owner_id, invoiceid, qty, amount, escrowfee, isescrowfeepaid, escrowfeeinvoiceid, escrowfeebuyer, isescrowfeebuyerpaid, escrowfeebuyerinvoiceid, fvf, isfvfpaid, fvfinvoiceid, fvfbuyer, isfvfbuyerpaid, ship_required, ship_location, orderdate, paiddate, buyerpaymethod, buyershipcost, buyershipperid, status, actualamount, `item_end_date` )
                                                VALUES(
                                                NULL,
                                                '" . intval($projectid) . "',
                                                '" . intval($buyer_id) . "',
                                                '" . intval($seller_id) . "',
                                                '" . $newinvoiceid . "',
                                                '" . $qty . "',
                                                '" . $rawtotal . "',
                                                '" . $escrowfee . "',
                                                '" . $isescrowfeepaid . "',
                                                '" . $escrowfeeinvoiceid . "',
                                                '" . $escrowfeebuyer . "',
                                                '" . $isescrowfeebuyerpaid . "',
                                                '" . $escrowfeebuyerinvoiceid . "',
                                                '" . $fvf . "',
                                                '" . $isfvfpaid . "',
                                                '" . $fvfinvoiceid . "',
                                                '" . $fvfbuyer . "',
                                                '0',
                                                '" . $shipping_address_required . "',
                                                '" . $ilance->db->escape_string($shippingaddress) . "',
                                                '" . DATETIME24H . "',
                                                '',                                               
												'escrow',
												'" . sprintf("%01.2f", $buyershipcost) . "',
												'" . intval($buyershipperid) . "',
                                                'paid',
												'".$actualrawtotal."',
												'".trim($item_end_date)."'
												)
                                        "));
                                        
                                        $neworderid = $ilance->db->insert_id();
                                        
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "users
                                                SET productsold = productsold + ".$qty."
                                                WHERE user_id = '" . $seller_id . "'
                                        ", 0, null, __FILE__, __LINE__);

                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "users
                                                SET productawards = productawards +".$qty."
                                                WHERE user_id = '" . $buyer_id . "'
                                        ", 0, null, __FILE__, __LINE__);

                                        // tie fvf invoice to seller to this buy now order so we have some association
										
										 //kkk feb2
										  $sql_new = $ilance->db->query("SELECT orderid,project_id,buyer_id,owner_id,invoiceid
                                          FROM " . DB_PREFIX . "buynow_orders WHERE orderid = '" . intval($neworderid) . "'");
		                                  $row_new = $ilance->db->fetch_array($sql_new);
										                                               
												//kkk
												$ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET buynowid = '" . intval($neworderid) . "'
                                                        WHERE invoiceid = '" . intval($row_new['invoiceid']) . "'
                                                ");
												
                                        if ($fvfinvoiceid > 0)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET buynowid = '" . intval($neworderid) . "'
                                                        WHERE invoiceid = '" . intval($fvfinvoiceid) . "'
                                                ");
                                        }
                                        
                                        // tie escrow fee invoice to seller so we can update isescrowfeepaid used by other cancel or paid invoice actions
                                        if ($escrowfeeinvoiceid > 0)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET buynowid = '" . intval($neworderid) . "'
                                                        WHERE invoiceid = '" . intval($escrowfeeinvoiceid) . "'
                                                ");        
                                        }
                                        
                                        if ($escrowfeebuyerinvoiceid > 0)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET buynowid = '" . intval($neworderid) . "'
                                                        WHERE invoiceid = '" . intval($escrowfeebuyerinvoiceid) . "'
                                                ");       
                                        }
                                        
                                        $bqty = fetch_auction('buynow_qty', $projectid);
                                        //new changes  $buynowqty = $bqty - 1;
                                        $buynowqty = ($bqty - $qty);
                                        if ($buynowqty <= 0)
                                        {
                                                $buynowqty = 0;
                                        }
                                        
                                        // update the qty available for this item
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET hasbuynowwinner = '1',
                                                buynow_qty = " . intval($buynowqty) . ",
                                                buynow_purchases = buynow_purchases + 1
                                                WHERE project_id = '" . intval($projectid) . "'
                                        ");
										
										$ilance->db->query("
											UPDATE  " . DB_PREFIX . "coins
											SET  sold_qty = sold_qty + 1						
											 WHERE  coin_id = '".$projectid."'
											");	
                                        
                                        // if there are no qty available then change open status to expired
                                        $buynow_qtyleft = fetch_auction('buynow_qty', $projectid);
                                        $filtered_auctiontype = fetch_auction('filtered_auctiontype', $projectid);
                                        $projectstatus = fetch_auction('status', $projectid);
                                                                                
					$existing = array(
                                                '{{customer}}' => $_SESSION['ilancedata']['user']['username'],
                                                '{{transactionid}}' => $transactionid,
                                                '{{merchant}}' => fetch_user('username', intval($seller_id)),
                                                '{{fullname}}' => fetch_user('fullname', intval($buyer_id)),
                                                '{{emailaddress}}' => fetch_user('email', intval($buyer_id)),
                                                '{{phone}}' => fetch_user('phone', intval($buyer_id)),
                                                '{{projectid}}' => $projectid,
                                                '{{description}}' => $phrase['_escrow_payment_forward'].": (".$projectid.")",
                                                '{{qty}}' => $qty,
                                                '{{amount_formatted}}' => $ilance->currency->format($rawtotal, $currencyid),
                                                '{{total_amount_formatted}}' => $ilance->currency->format($total, $currencyid),
                                                '{{invoiceid}}' => $newinvoiceid,
                                                '{{shippingaddress}}' => $shippingaddress,
						'{{ship_costs}}' => $ilance->currency->format($buyershipcost, $currencyid),
						'{{shippingservice}}' => print_shipping_partner($buyershipperid),
						'{{paymentmethod}}' => $phrase['_escrow'],
                                        );
		
                                        $ilance->email->mail = fetch_user('email', intval($buyer_id));
                                        $ilance->email->slng = fetch_user_slng(intval($buyer_id));
                                        
                                        $ilance->email->get('escrow_buynow_payment_confirmation');		
                                        $ilance->email->set($existing);
                                        
										// Murugan chagnges on 28 
										//$ilance->email->send();
									// This Code Added by Murugan On March 28	
								$existing = array(
                                '{{buyer}}' => $_SESSION['ilancedata']['user']['username'],
                                '{{buyer_fullname}}' => $_SESSION['ilancedata']['user']['fullname'],
                                '{{buyer_email}}' => $_SESSION['ilancedata']['user']['email'],
                                '{{seller}}' => fetch_user('username', intval($seller_id)),
                                '{{project_title}}' => stripslashes(fetch_auction('project_title', $projectid)),
                                '{{project_id}}' => $projectid,
                                '{{qty}}' => $qty,
                                '{{amount}}' => $ilance->currency->format($rawtotal, $currencyid),
                                '{{total}}' => $ilance->currency->format($total, $currencyid),
                                '{{ship_costs}}' => $ilance->currency->format($buyershipcost, $currencyid),
                                '{{shippingaddress}}' => $shippingaddress,
				'{{shippingservice}}' => print_shipping_partner($buyershipperid),
                                '{{fvf}}' => $ilance->currency->format($fvf),
                                '{{fvftotal}}' => $ilance->currency->format($fvf),
                                '{{paymentstatus}}' => $phrase['_pending_immediate_payment'],
				'{{paymentmethod}}' => print_fixed_payment_method($buyerpaymethod),
                        );
			
                        $ilance->email->mail = fetch_user('email', intval($buyer_id));
                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                        
                        $ilance->email->get('buynow_offline_purchase_buyer');		
                        $ilance->email->set($existing);
                        
                        $ilance->email->send();
						
						$ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        
                        $ilance->email->get('buynow_offline_purchase_buyer');		
                        $ilance->email->set($existing);
                        
                        $ilance->email->send();
						
						$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
                        $ilance->email->slng = fetch_site_slng();
                        
                        $ilance->email->get('buynow_offline_purchase_buyer');		
                        $ilance->email->set($existing);
                        
                        $ilance->email->send();
						
                                        
                                        $ilance->email->mail = fetch_user('email', intval($seller_id));
                                        $ilance->email->slng = fetch_user_slng(intval($seller_id));
                                        
                                        $ilance->email->get('product_escrow_payment_foward_merchant');		
                                        $ilance->email->set($existing);
                                        
										// murugan changes on mar 28 
                                      //  $ilance->email->send();
                                        
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        
                                        //murugan changes on mar 28 
										$ilance->email->get('escrow_buynow_payment_confirmation_admin');		
                                        $ilance->email->set($existing);
                                        
                                        //$ilance->email->send();
                                        
                                        return array(true, $neworderid);
                                
                                
								   
                        
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