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
if (!class_exists('accounting'))
{
	exit;
}
/**
* Function to handle accounting fee logic
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class accounting_fees extends accounting
{
	/**
        * Functions for creating a final value fee based on a bid id, category id and project id.
        * 
        * This function will take the bid id, cat id and project id to determine the final value
        * fee to charge (or refund) to a user based on an awarded auction they accepted to
        * complete within this particular category.  Note: The buyer has ability to unaward this
        * awarded bid, if this happens the service provider or merchant still owes the final value
        * fee even if the project is unawarded.  Note 2: This function will now check to see if
        * funds exist in online account and will auto-debit the fvf if possible.
        *
        * @param       integer          bid id
        * @param       integer          category id
        * @param       integer          project id
        * @param       string           final value fee creation mode (charge or refund)
        * @param       string           category type (service or product)
        *
        * @return      bool             Returns true or false based on the creation of the final value fee or refund
        */
		
		// Murugan Changes On Nov 12 For Subscription Based FVF
		//function construct_final_value_fee($bidid = 0, $cid = 0, $pid = 0, $mode = '', $cattype = '')
		function construct_final_value_fee($bidid = 0, $userid = 0, $pid = 0, $mode = '', $cattype = '')
          {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $ilance->subscription = construct_object('api.subscription');
				// Murugan
                $userid=intval($userid);
				
                $tiers = $price = $total = $remaining = $fvf = 0;
                
                ($apihook = $ilance->api('construct_final_value_fee_start')) ? eval($apihook) : false;
                
                // fetch awarded bid amount
                $project = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "project_bids
                        WHERE project_id = '" . intval($pid) . "'
                            AND bid_id = '" . intval($bidid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($project) > 0)
                {
                        $resproject = $ilance->db->fetch_array($project);
                        
                        // awarded bid amount
                        if ($resproject['bidamounttype'] == 'entire' OR $resproject['bidamounttype'] == 'item' OR $resproject['bidamounttype'] == 'lot' OR $resproject['bidamounttype'] == 'weight')
                        {
                                if ($resproject['qty'] <= 0)
                                {
                                        $resproject['qty'] = 1;
                                }
                                
                                $bidamount = ($resproject['bidamount'] * $resproject['qty']);
                        }
                        else
                        {
                                if ($resproject['estimate_days'] <= 0)
                                {
                                        $resproject['estimate_days'] = 1;
                                }
                                
                                $bidamount = ($resproject['bidamount'] * $resproject['estimate_days']);
                        }
                        
                        // #### fvf commission logic : who gets charged? #######
                        $bidderid = ($cattype == 'product') ? $resproject['project_user_id'] : $resproject['user_id'];
                        
                        // #### ARE WE USING FIXED CATEGORY FEES? ##############
                        // first check if admin uses fixed fees in this category
                        // admin defines fixed fees within AdminCP > Distribution > Categories > (edit mode)
                        if ($ilance->categories->usefixedfees($cid) AND !empty($resproject['bidamounttype']))
                        {
                                // #### let's output our fixed commission amount
                                $fvf = $ilance->categories->fixedfeeamount($cid);
                        }
                        
                        // #### NO FIXED CATEGORIES FEES > CHECK FINAL VALUE GROUP #############
                        else
                        {
                                // fetch final value group for this category
                                // we are at this point because the admin has not defined fixed fees
								
								// Murugan Changes On NOv 12
								$subid=$ilance->db->query(" 
								
								SELECT user.subscriptionid, user.user_id, sub.subscriptiongroupid, perm.value
                                FROM " . DB_PREFIX . "subscription_user user
                                LEFT JOIN " . DB_PREFIX . "subscription sub ON (sub.subscriptionid = user.subscriptionid)
                                LEFT JOIN " . DB_PREFIX . "subscription_permissions perm ON (perm.subscriptiongroupid = sub.subscriptiongroupid)
                                WHERE user.user_id = '" . intval($userid) . "'
								 AND sub.active = 'yes'
                                        AND user.active = 'yes'
                                        AND perm.subscriptiongroupid = sub.subscriptiongroupid
                                        AND perm.accessname = 'fvffees' ");
										
                               /* $categories = $ilance->db->query("
                                        SELECT finalvaluegroup
                                        FROM " . DB_PREFIX . "categories
                                        WHERE cid = '" . intval($cid) . "'
                                ", 0, null, __FILE__, __LINE__);*/
                                // murugan changes 
                                //if ($ilance->db->num_rows($categories) > 0)
								 if ($ilance->db->num_rows($subid) > 0)
                                {
                                       // $cats = $ilance->db->fetch_array($categories);
                                         $cats = $ilance->db->fetch_array($subid);
										 // Murugan Changes On Nov 16
                                       // if (!empty($cats['finalvaluegroup']))
									    if (!empty($cats['value']))
                                        {
                                                // Murugan Changes On Nov 12
											    /*$finalvalues = $ilance->db->query("
                                                        SELECT tierid, groupname, finalvalue_from, finalvalue_to, amountfixed, amountpercent, state, sort
                                                        FROM " . DB_PREFIX . "finalvalue
                                                        WHERE groupname = '" . $ilance->db->escape_string($cats['finalvaluegroup']) . "'
                                                            AND state = '" . $ilance->db->escape_string($cattype) . "'
                                                        ORDER BY finalvalue_from ASC
                                                ", 0, null, __FILE__, __LINE__);*/
                                                
												$finalvalues = $ilance->db->query("
                                                        SELECT tierid, groupname, finalvalue_from, finalvalue_to, amountfixed, amountpercent, state, sort
                                                        FROM " . DB_PREFIX . "finalvalue
                                                        WHERE lower(groupname) = '" . $ilance->db->escape_string(strtolower($cats['value'])) . "'
                                                            AND state = '" . $ilance->db->escape_string($cattype) . "'
                                                        ORDER BY finalvalue_from ASC
                                                ", 0, null, __FILE__, __LINE__);
												
                                                $totaltiers = (int)$ilance->db->num_rows($finalvalues);
                                                
                                                if ($totaltiers == 1)
                                                {
                                                        // #### SINGLE FVF TIER LOGIC ##############################
                                                        $fees = $ilance->db->fetch_array($finalvalues);
                                                        
                                                        if ($bidamount >= $fees['finalvalue_from'])
                                                        {
                                                                if ($fees['amountfixed'] > 0)
                                                                {
                                                                        $fvf += $fees['amountfixed'];
                                                                        $fv = $fees['amountfixed'];
                                                                }
                                                                else
                                                                {
                                                                        $fvf += ($bidamount * $fees['amountpercent'] / 100);
                                                                        $fv = ($bidamount * $fees['amountpercent'] / 100);
                                                                }
                                                        }
                                                }
                                                else
                                                {
                                                        // #### MULTIPLE FVF TIER LOGIC ############################
                                                        if ($totaltiers > 0)
                                                        {
                                                                while ($fees = $ilance->db->fetch_array($finalvalues))
                                                                {
                                                                        $tiers++;
                                                                        if ($fees['finalvalue_to'] != '-1')
                                                                        {
                                                                                if ($bidamount >= $fees['finalvalue_from'] AND $bidamount <= $fees['finalvalue_to'])
                                                                                {
                                                                                        $bid = ($bidamount - ($fees['finalvalue_to'] - $fees['finalvalue_from'])); 
                                                                                        if ($tiers == 1)
                                                                                        {
                                                                                                if ($fees['amountfixed'] > 0)
                                                                                                {
                                                                                                        // fixed
                                                                                                        $fvf += $fees['amountfixed'];
                                                                                                        $fv = $fees['amountfixed'];
                                                                                                }
                                                                                                else
                                                                                                {
                                                                                                        // percentage
                                                                                                        $fvf += ($bidamount * $fees['amountpercent'] / 100);
                                                                                                        $fv = ($bidamount * $fees['amountpercent'] / 100);
                                                                                                }
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                                if ($fees['amountfixed'] > 0)
                                                                                                {
                                                                                                        // fixed
                                                                                                        $fvf += $fees['amountfixed'];
                                                                                                        $fv = $fees['amountfixed'];
                                                                                                }
                                                                                                else
                                                                                                {
                                                                                                        // percent
                                                                                                        $fvf += ($remaining * $fees['amountpercent'] / 100);
                                                                                                        $fv = ($remaining * $fees['amountpercent'] / 100);    
                                                                                                }
                                                                                        }
                                                                                        
                                                                                        break;
                                                                                }
                                                                                else
                                                                                {
                                                                                        // the fees must go on! .-)
                                                                                        if ($fees['amountfixed'] > 0)
                                                                                        {
                                                                                                // fixed
                                                                                                $fvf += $fees['amountfixed'];
                                                                                                $fv = $fees['amountfixed'];
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                                // percent
                                                                                                $fvf += (($fees['finalvalue_to'] - $fees['finalvalue_from']) * $fees['amountpercent'] / 100);
                                                                                                $fv = (($fees['finalvalue_to'] - $fees['finalvalue_from']) * $fees['amountpercent'] / 100);
                                                                                        }
                                                                                        
                                                                                        // calculate remaining bid amount for next tier
                                                                                        $bid = ($bidamount - ($fees['finalvalue_to'] - $fees['finalvalue_from']));
                                                                                        $remaining = ($bid - $fees['finalvalue_from']);
                                                                                }
                                                                        }
                                                                        else
                                                                        {
                                                                                // ie: 1000.01 to -1 denotes 1000.01 - (and above)
                                                                                if ($bidamount >= $fees['finalvalue_from'])
                                                                                {
                                                                                        if ($fees['amountfixed'] > 0)
                                                                                        {
                                                                                                $fvf += $fees['amountfixed'];
                                                                                                $fv = $fees['amountfixed'];
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                                $fvf += ($remaining * $fees['amountpercent'] / 100);
                                                                                                $fv = ($remaining * $fees['amountpercent'] / 100);    
                                                                                        }
                                                                                        
                                                                                        // calculate remaining bid amount for next tier
                                                                                        $bid = ($bidamount - $fees['finalvalue_from']);
                                                                                        $remaining = ($bid - $fees['finalvalue_from']);
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                }    
                        }
                        
                        // check if we're exempt from final value fees
                        if (!empty($bidderid) AND $bidderid > 0 AND $ilance->subscription->check_access($bidderid, 'fvfexempt') == 'yes')
                        {
                                $fee = 0;
                        }
                        
                        if ($fvf > 0)
                        {
                                ($apihook = $ilance->api('construct_final_value_fee_end')) ? eval($apihook) : false;
                                
                                // #### taxes on final valuefees ###############
				$ilance->tax = construct_object('api.tax');
				$extrainvoicesql = '';
				if ($ilance->tax->is_taxable(intval($bidderid), 'finalvaluefee'))
				{
					// #### fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount(intval($bidderid), $fee, 'finalvaluefee', 0);
					
					// #### fetch total amount to hold within the "totalamount" field
					$totalamount = ($fee + $taxamount);
					
					// #### fetch tax bit to display when outputing tax infos
					$taxinfo = $ilance->tax->fetch_amount(intval($bidderid), $fee, 'finalvaluefee', 1);
					
					// #### extra bit to assign tax logic to the transaction 
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
				}
                                
                                // #### CHARGE FVF LOGIC #######################################
                                if ($mode == 'charge')
                                {
                                        // do we have funds in online account?
                                        $account = $ilance->db->query("
                                                SELECT available_balance, total_balance, autopayment
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . intval($bidderid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($account) > 0)
                                        {
                                                $res = $ilance->db->fetch_array($account);
                                                
                                                $avail = $res['available_balance'];
                                                $total = $res['total_balance'];
                                                
                                                if ($total >= $fvf AND $res['autopayment'])
                                                {
                                                        // create a paid final value fee
                                                        $invoiceid = $this->insert_transaction(
                                                                0,
                                                                intval($pid),
                                                                0,
                                                                intval($bidderid),
                                                                0,
                                                                0,
                                                                0,
                                                                $phrase['_final_value_fee_for_auction'] . ' - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid),
                                                                sprintf("%01.2f", $fvf),
                                                                sprintf("%01.2f", $fvf),
                                                                'paid',
                                                                'debit',
                                                                'account',
                                                                DATETIME24H,
                                                                DATETIME24H,
                                                                DATETIME24H,
                                                                $phrase['_auto_debit_from_online_account_balance'],
                                                                0,
                                                                0,
                                                                1
                                                        );
                                                        
                                                        // update invoice mark as final value fee invoice type
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "invoices
                                                                SET
                                                                $extrainvoicesql
                                                                isfvf = '1'
                                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // update final value fee field in bid table & project table for awarded amount
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_bids
                                                                SET fvf = '" . sprintf("%01.2f", $fvf) . "'
                                                                WHERE bid_id = '" . intval($bidid) . "'
                                                                    AND project_id = '" . intval($pid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET fvf = '" . sprintf("%01.2f", $fvf) . "',
                                                                isfvfpaid = '1',
                                                                fvfinvoiceid = '" . intval($invoiceid) . "'
                                                                WHERE project_id = '" . intval($pid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // update account balance
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET available_balance = available_balance - " . sprintf("%01.2f", $fvf) . ",
                                                                total_balance = total_balance - " . sprintf("%01.2f", $fvf) . "
                                                                WHERE user_id = '" . intval($bidderid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // track income history
                                                        insert_income_spent(intval($bidderid), sprintf("%01.2f", $fvf), 'credit');
                                                        
                                                        // #### REFERRAL SYSTEM TRACKER ############################
                                                        update_referral_action('fvf', intval($bidderid));                                                                                            
                                                }
                                                else
                                                {
                                                        // create an unpaid final value fee
                                                        $invoiceid = $this->insert_transaction(
                                                                0,
                                                                intval($pid),
                                                                0,
                                                                intval($bidderid),
                                                                0,
                                                                0,
                                                                0,
                                                                $phrase['_final_value_fee_for_auction'] . ' - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid),
                                                                sprintf("%01.2f", $fvf),
                                                                '',
                                                                'unpaid',
                                                                'debit',
                                                                'account',
                                                                DATETIME24H,
                                                                DATEINVOICEDUE,
                                                                '',
                                                                $phrase['_please_pay_this_invoice_soon_as_possible'],
                                                                0,
                                                                0,
                                                                1
                                                        );
                                                    
                                                        // update invoice mark as final value fee invoice type
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "invoices
                                                                SET
                                                                $extrainvoicesql
                                                                isfvf = '1'
                                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // update final value fee field in bid & project table for awarded amount
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_bids
                                                                SET fvf = '" . sprintf("%01.2f", $fvf) . "'
                                                                WHERE bid_id = '" . intval($bidid) . "'
                                                                    AND project_id = '" . intval($pid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET fvf = '" . sprintf("%01.2f", $fvf) . "',
                                                                isfvfpaid = '0',
                                                                fvfinvoiceid = '" . intval($invoiceid) . "'
                                                                WHERE project_id = '" . intval($pid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                        }
                                }
                                
                                // #### REFUND FVF LOGIC #######################################
                                else if ($mode == 'refund')
                                {
                                        // let's refund this final value fee due to an unaward by the buyer
                                        // find out if the provider paid this fvf or learn if it's still unpaid
                                        
                                        // fetch the most recent fvf for this particular project id
                                        $maxinvoicesql = $ilance->db->query("
                                                SELECT MAX(invoiceid) AS maxinvoiceid
                                                FROM " . DB_PREFIX . "invoices
                                                WHERE projectid = '" . intval($pid) . "'
                                                    AND user_id = '" . intval($bidderid) . "'
                                                    AND isfvf = '1'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($maxinvoicesql) > 0)
                                        { 
                                                $maxid = $ilance->db->fetch_array($maxinvoicesql);
                                                $invsql = $ilance->db->query("
                                                        SELECT invoiceid, status, paid
                                                        FROM " . DB_PREFIX . "invoices
                                                        WHERE projectid = '" . intval($pid) . "'
                                                            AND user_id = '" . intval($bidderid) . "'
                                                            AND isfvf = '1'
                                                            AND invoiceid = '" . $maxid['maxinvoiceid'] . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($invsql) > 0)
                                                {
                                                        $invres = $ilance->db->fetch_array($invsql);
                                                        
                                                        // #### UNPAID FVF HANDLER #####################
                                                        if ($invres['status'] == 'unpaid')
                                                        {
                                                                // provider hasn't paid final value fee yet! let's cancel this invoice
                                                                // so they do not see any pending/unpaid invoices for this project
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                        SET status = 'cancelled',
                                                                        custommessage = '" . $ilance->db->escape_string($phrase['_awarded_bid_was_unawarded_by_the_owner_of_this_project_invoice_cancelled']) . "'
                                                                        WHERE invoiceid = '" . $invres['invoiceid'] . "'
                                                                        LIMIT 1
                                                                ", 0, null, __FILE__, __LINE__);
                                                                
                                                                // reset final value fees for this project
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects
                                                                        SET fvf = '0.00',
                                                                        isfvfpaid = '0',
                                                                        fvfinvoiceid = '0'
                                                                        WHERE project_id = '" . intval($pid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                // perhaps an email gets dispatched informing the provider
                                                                // that the final value fee has been cancelled
                                                        }
                                                        
                                                        // #### PAID FVF HANDLER #######################
                                                        else if ($invres['status'] == 'paid')
                                                        {
                                                                // provider already paid the site for the final value fee 
                                                                // so let's refund this amount and update the providers account balance
                                                                if ($invres['paid'] > 0)
                                                                {
                                                                        // create a final value fee credit to the service provider
                                                                        $refundinvoiceid = $this->insert_transaction(
                                                                                0,
                                                                                intval($pid),
                                                                                0,
                                                                                intval($bidderid),
                                                                                0,
                                                                                0,
                                                                                0,
                                                                                $phrase['_final_value_fee_refund_credit_for_auction'] . ' #' . intval($pid),
                                                                                sprintf("%01.2f", $invres['paid']),
                                                                                sprintf("%01.2f", $invres['paid']),
                                                                                'paid',
                                                                                'credit',
                                                                                'account',
                                                                                DATETIME24H,
                                                                                DATETIME24H,
                                                                                DATETIME24H,
                                                                                '',
                                                                                0,
                                                                                0,
                                                                                1
                                                                        );    
                                                                        
                                                                        // track income history
                                                                        insert_income_spent($bidderid, sprintf("%01.2f", $invres['paid']), 'debit');
                                                                        
                                                                        // re credit the provider online account
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "users
                                                                                SET available_balance = available_balance + " . $invres['paid'] . ",
                                                                                total_balance = total_balance + " . $invres['paid'] . "
                                                                                WHERE user_id = '" . intval($bidderid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        
                                                                        // we should also update the bid and project table fvf fields back to 0.00
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "project_bids
                                                                                SET fvf = '0.00'
                                                                                WHERE project_id = '" . intval($pid) . "'
                                                                                    AND user_id = '" . intval($bidderid) . "'
                                                                                    AND fvf > 0
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "projects
                                                                                SET fvf = '0.00',
                                                                                isfvfpaid = '0',
                                                                                fvfinvoiceid = '0'
                                                                                WHERE project_id = '" . intval($pid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        
                                                                        // perhaps an email gets dispatched informing the provider
                                                                        // that the final value fee has been refunded
                                                                        
                                                                        // additionally, another email to admin advising the loss of FVF funds.
                                                                }
                                                        }    
                                                }
                                        }
                                }
                        
                                return 1;
                        }
                }
                
                return 0;
        }
		
		// This function Created By Murugan On Mar 23 Modified Apr 12
		function construct_final_value_fee_new($bidid = 0, $userid = 0, $pid = 0, $mode = '', $cattype = '')
          {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $ilance->subscription = construct_object('api.subscription');
				// Murugan
                $userid=intval($userid);
				
                $tiers = $price = $total = $remaining = $fvf = 0;
                
                ($apihook = $ilance->api('construct_final_value_fee_start')) ? eval($apihook) : false;
                
                // fetch awarded bid amount
                $project = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "project_bids
                        WHERE project_id = '" . intval($pid) . "'
                            AND bid_id = '" . intval($bidid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($project) > 0)
                {
                        $resproject = $ilance->db->fetch_array($project);
                        
                        // awarded bid amount
                        if ($resproject['bidamounttype'] == 'entire' OR $resproject['bidamounttype'] == 'item' OR $resproject['bidamounttype'] == 'lot' OR $resproject['bidamounttype'] == 'weight')
                        {
                                if ($resproject['qty'] <= 0)
                                {
                                        $resproject['qty'] = 1;
                                }
                                
                                $bidamount = ($resproject['bidamount'] * $resproject['qty']);
                        }
                        else
                        {
                                if ($resproject['estimate_days'] <= 0)
                                {
                                        $resproject['estimate_days'] = 1;
                                }
                                
                                $bidamount = ($resproject['bidamount'] * $resproject['estimate_days']);
                        }
                        
                        // #### fvf commission logic : who gets charged? #######
                $bidderid = ($cattype == 'product') ? $resproject['project_user_id'] : $resproject['user_id'];  
				
				$fvf = $this->calculate_fvf_value($pid,$bidamount,$resproject['user_id'],$resproject['project_user_id']);
                         
                        // check if we're exempt from final value fees
                        if (!empty($bidderid) AND $bidderid > 0 AND $ilance->subscription->check_access($bidderid, 'fvfexempt') == 'yes')
                        {
                                $fee = 0;
                        }
                        
                        if ($fvf > 0)
                        {
                                ($apihook = $ilance->api('construct_final_value_fee_end')) ? eval($apihook) : false;
                                
                                // #### taxes on final valuefees ###############
				$ilance->tax = construct_object('api.tax');
				$extrainvoicesql = '';
				if ($ilance->tax->is_taxable(intval($bidderid), 'finalvaluefee'))
				{
					// #### fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount(intval($bidderid), $fee, 'finalvaluefee', 0);
					
					// #### fetch total amount to hold within the "totalamount" field
					$totalamount = ($fee + $taxamount);
					
					// #### fetch tax bit to display when outputing tax infos
					$taxinfo = $ilance->tax->fetch_amount(intval($bidderid), $fee, 'finalvaluefee', 1);
					
					// #### extra bit to assign tax logic to the transaction 
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
				}
                                
                                // #### CHARGE FVF LOGIC #######################################
                                if ($mode == 'charge')
                                {
                                        // do we have funds in online account?
                                        $account = $ilance->db->query("
                                                SELECT available_balance, total_balance, autopayment
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . intval($bidderid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($account) > 0)
                                        {
                                                $res = $ilance->db->fetch_array($account);
                                                
                                                $avail = $res['available_balance'];
                                                $total = $res['total_balance'];
                                                
                                                      // create a paid final value fee
                                                        $invoiceid = $this->insert_transaction(
                                                                0,
                                                                intval($pid),
                                                                0,
                                                                intval($bidderid),
                                                                0,
                                                                0,
                                                                0,
                                                                $phrase['_final_value_fee_for_auction'] . ' - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid),
                                                                sprintf("%01.2f", $fvf),
                                                                sprintf("%01.2f", $fvf),
                                                                'paid',
                                                                'debit',
                                                                'account',
                                                                DATETIME24H,
                                                                DATETIME24H,
                                                                DATETIME24H,
                                                                $phrase['_auto_debit_from_online_account_balance'],
                                                                0,
                                                                0,
                                                                1
                                                        );
                                                        
                                                        // update invoice mark as final value fee invoice type
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "invoices
                                                                SET
                                                                $extrainvoicesql
                                                                isfvf = '1',createdate='".fetch_auction('date_end', intval($pid))."',statement_date='".fetch_auction('date_end', intval($pid))."'
                                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // update final value fee field in bid table & project table for awarded amount
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_bids
                                                                SET fvf = '" . sprintf("%01.2f", $fvf) . "'
                                                                WHERE bid_id = '" . intval($bidid) . "'
                                                                    AND project_id = '" . intval($pid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET fvf = '" . sprintf("%01.2f", $fvf) . "',
                                                                isfvfpaid = '1',
                                                                fvfinvoiceid = '" . intval($invoiceid) . "'
                                                                WHERE project_id = '" . intval($pid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        
                                                        // #### REFERRAL SYSTEM TRACKER ############################
                                                        update_referral_action('fvf', intval($bidderid));                                                      
                                        }
                                }
                                
                                return 1;
                        }
                }
                
                return 0;
        }
		
	/**
        * Functions for creating a final value donation fee based on particular donation setup.  Additionally,
        * this function can be used to charge or refund the seller based on the final value fee donation amount
        * originally generated.
        * 
        * @param       integer          project id
        * @param       integer          winning bid amount or buy now price
        * @param       string           fee creation mode (charge or refund)
        *
        * @return      bool             Returns true or false based on the creation of the final value fee or refund
        */
        function construct_final_value_donation_fee($pid = 0, $amount = 0, $mode = 'charge')
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $fvf = 0;
                
                // fetch awarded bid amount
                $project = $ilance->db->query("
                        SELECT user_id, donation, charityid, donationpercentage
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($pid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($project) > 0)
                {
                        $resproject = $ilance->db->fetch_array($project, DB_ASSOC);
                        if ($resproject['donation'] AND $resproject['charityid'] > 0 AND $resproject['donationpercentage'] > 0)
                        {
                                $fvf = ($amount * $resproject['donationpercentage'] / 100);
                        }
                        
                        if ($fvf > 0)
                        {
                                // #### CHARGE FVF LOGIC #######################################
                                if ($mode == 'charge')
                                {
                                        // do we have funds in online account?
                                        $account = $ilance->db->query("
                                                SELECT available_balance, total_balance, autopayment
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $resproject['user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($account) > 0)
                                        {
                                                $res = $ilance->db->fetch_array($account, DB_ASSOC);
                                                
                                                $avail = $res['available_balance'];
                                                $total = $res['total_balance'];
                                                
						// #### suffificent funds to cover transaction
                                                if ($res['autopayment'] == '1' AND $avail >= $fvf)
                                                {
                                                        // #### create a paid final value donation fee
                                                        $invoiceid = $this->insert_transaction(
                                                                0,
                                                                intval($pid),
                                                                0,
                                                                $resproject['user_id'],
                                                                0,
                                                                0,
                                                                0,
                                                                'Final Value Donation Fee (' . $resproject['donationpercentage'] . '%) - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid),
                                                                sprintf("%01.2f", $fvf),
                                                                sprintf("%01.2f", $fvf),
                                                                'paid',
                                                                'debit',
                                                                'account',
                                                                DATETIME24H,
                                                                DATETIME24H,
                                                                DATETIME24H,
                                                                $phrase['_auto_debit_from_online_account_balance'],
                                                                0,
                                                                0,
                                                                1
                                                        );
                                                        
                                                        // #### update invoice mark as final value fee invoice type
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "invoices
                                                                SET isdonationfee = '1',
                                                                charityid = '" . $resproject['charityid'] . "'
                                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // #### update donation details in listing table
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET donermarkedaspaid = '1',
                                                                donermarkedaspaiddate = '" . DATETIME24H . "',
                                                                donationinvoiceid = '" . intval($invoiceid) . "'
                                                                WHERE project_id = '" . intval($pid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // #### update account balance
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET available_balance = available_balance - " . sprintf("%01.2f", $fvf) . ",
                                                                total_balance = total_balance - " . sprintf("%01.2f", $fvf) . "
                                                                WHERE user_id = '" . $resproject['user_id'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "charities
                                                                SET donations = donations + 1,
                                                                earnings = earnings + $fvf
                                                                WHERE charityid = '" . $resproject['charityid'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // #### track income history
                                                        insert_income_spent($resproject['user_id'], sprintf("%01.2f", $fvf), 'credit');
                                                        
                                                        // #### referral tracker
                                                        update_referral_action('fvf', $resproject['user_id']);                                                                                            
                                                }
                                                
						// #### insufficient funds to cover transaction
						else
                                                {
                                                        $invoiceid = $this->insert_transaction(
                                                                0,
                                                                intval($pid),
                                                                0,
                                                                $resproject['user_id'],
                                                                0,
                                                                0,
                                                                0,
                                                                'Final Value Donation Fee (' . $resproject['donationpercentage'] . '%) - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid),
                                                                sprintf("%01.2f", $fvf),
                                                                '',
                                                                'unpaid',
                                                                'debit',
                                                                'account',
                                                                DATETIME24H,
                                                                DATEINVOICEDUE,
                                                                '',
                                                                $phrase['_please_pay_this_invoice_soon_as_possible'],
                                                                0,
                                                                0,
                                                                1
                                                        );
                                                    
                                                        // update invoice mark as final value donation fee invoice type
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "invoices
                                                                SET isdonationfee = '1',
                                                                charityid = '" . $resproject['charityid'] . "'
                                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET donermarkedaspaid = '0',
                                                                donationinvoiceid = '" . intval($invoiceid) . "'
                                                                WHERE project_id = '" . intval($pid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                        }
                                }
				else if ($mode == 'refund')
				{
					// do we have funds in online account?
                                        $sql = $ilance->db->query("
                                                SELECT donationinvoiceid, donermarkedaspaid
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($pid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql) > 0)
                                        {
                                                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                                
						// #### reset listing table
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET donermarkedaspaid = '0',
							donermarkedaspaiddate = '0000-00-00 00:00:00',
							donationinvoiceid = '0'
							WHERE project_id = '" . intval($pid) . "'
						", 0, null, __FILE__, __LINE__);
						
						// #### remove old invoice
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "invoices
							WHERE invoiceid = '" . $res['donationinvoiceid'] . "'
						");
						
						// #### refund donation associated invoice
						if ($res['donermarkedaspaid'])
						{
							// #### create a paid final value donation fee refund credit
                                                        $invoiceid = $this->insert_transaction(
                                                                0,
                                                                intval($pid),
                                                                0,
                                                                $resproject['user_id'],
                                                                0,
                                                                0,
                                                                0,
                                                                'Final Value Donation Fee Refund Credit (' . $resproject['donationpercentage'] . '%) - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid),
                                                                sprintf("%01.2f", $fvf),
                                                                sprintf("%01.2f", $fvf),
                                                                'paid',
                                                                'credit',
                                                                'account',
                                                                DATETIME24H,
                                                                DATETIME24H,
                                                                DATETIME24H,
                                                                'Auto-credited to Online Account Balance',
                                                                0,
                                                                0,
                                                                1
                                                        );
							
							// #### update account balance
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET available_balance = available_balance + " . sprintf("%01.2f", $fvf) . ",
                                                                total_balance = total_balance + " . sprintf("%01.2f", $fvf) . "
                                                                WHERE user_id = '" . $resproject['user_id'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
							
							// #### track income history
                                                        insert_income_spent($resproject['user_id'], sprintf("%01.2f", $fvf), 'debit');
							
							$ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "charities
                                                                SET donations = donations - 1,
                                                                earnings = earnings - $fvf
                                                                WHERE charityid = '" . $resproject['charityid'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
						}
                                        }	
				}
                                
                                return true;
                        }
                }
                
                return false;
        }
		
		function calculate_fvf_value($pid,$bidamount,$userid,$seller_id)
		{
		global $ilance;
		$house_acc = $ilance->db->query("SELECT house_account FROM " .DB_PREFIX. "users WHERE user_id = '".$seller_id."' AND house_account='1'");
		$resproject1 = $ilance->db->fetch_array($house_acc);
		if($ilance->db->num_rows($house_acc) > 0)
		{
			$fvf = 0 ;
		}
		else
		{
			//
			$sql="SELECT fvf_id  FROM " . DB_PREFIX . "coins WHERE  coin_id='".$pid."'";
			$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($res)>0)
			{
				while($line=$ilance->db->fetch_array($res))
				{
				
					$sql1="SELECT amountpercent  FROM " . DB_PREFIX . "finalvalue_groups g
					left join 	" . DB_PREFIX . "finalvalue f on g.groupid=f.groupid and f.finalvalue_from<='".$bidamount."' and (f.finalvalue_to>='".$bidamount."'  or f.finalvalue_to<0) and f.amountpercent>0 
					WHERE  g.groupid='".$line['fvf_id']."' and g.state='product'";
					$res1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
					if($ilance->db->num_rows($res1)>0)
					{
						while($line1=$ilance->db->fetch_array($res1))
						{
							$fvf_percentage= $line1['amountpercent'];
							$fvf = ($bidamount * $fvf_percentage / 100);
						}
					}
				}
			}
		}
		return $fvf;
		}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>