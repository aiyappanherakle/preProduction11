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
* Accounting class to perform the majority of accounting functions within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class accounting
{
        /**
        * Function for processing a valid ILance transaction.
        *
        * @param       integer      subscription id (optional)
        * @param       integer      project id (optional)
        * @param       integer      buy now order id (optional)
        * @param       integer      user id
        * @param       integer      provider to buyer identification (which would be the service provider's id)
        * @param       integer      store id (optional)
        * @param       integer      buy now escrow order id (optional)
        * @param       integer      transaction description
        * @param       string       transaction amount
        * @param       string       transaction amount paid
        * @param       string       transaction status
        * @param       string       transaction type
        * @param       string       transaction payment method
        * @param       string       transaction create date
        * @param       string       transaction due date
        * @param       string       transaction paid date
        * @param       string       custom transaction message (optional) may also be used for payment gateway transaction ids
        * @param       bool         archive this invoice (optional)
        * @param       bool         defines if this transaction is a purchase order (default is no)
        * @param       bool         defines if this function should return the newly created invoice id
        * @param       string       custom transaction id (optional)
        * @param       bool         defines if this transaction is deposit related (default is no)
        * @param       bool         defines if this transaction is withdraw related (default is no)
        * @param       bool         defines if we should force the non-processing of taxes even if we have any
        *
        * @return      integer      returns the newly generated invoice id (if parameter is set)
        */
        function insert_transaction($subscriptionid = 0, $projectid = 0, $buynowid = 0, $user_id = 0, $p2b_user_id = 0, $storeid = 0, $orderid = 0, $description = '', $amount, $paid, $status, $invoicetype, $paymethod, $createdate, $duedate, $paiddate, $custommessage, $archive, $ispurchaseorder = 0, $returnid = 0, $transactionidx = '', $isdeposit = 0, $iswithdraw = 0, $dontprocesstax = 0)
        {
                global $ilance, $myapi;
                
                $subscriptionid  = isset($subscriptionid)           ? intval($subscriptionid)               : '0';
                $projectid       = isset($projectid)                ? intval($projectid)                    : '0';
                $buynowid        = isset($buynowid)                 ? intval($buynowid)                     : '0';
                $user_id         = isset($user_id)                  ? intval($user_id)                      : '0';
                $p2b_user_id     = isset($p2b_user_id)              ? intval($p2b_user_id)                  : '0';
                $storeid         = isset($storeid)                  ? intval($storeid)                      : '0';
                $orderid         = isset($orderid)                  ? intval($orderid)                      : '0';
                $description     = isset($description)              ? $description                          : 'No transaction description provided';
                $amount          = isset($amount)                   ? $amount                               : '0.00';
                $paid            = isset($paid)                     ? $paid                                 : '0.00';
                $status          = isset($status)                   ? $status                               : 'unpaid';
                $invoicetype     = isset($invoicetype)              ? $invoicetype                          : 'debit';
                $paymethod       = isset($paymethod)                ? $paymethod                            : 'account';
                $ipaddress       = IPADDRESS;
                $referer         = REFERRER;
                $createdate      = DATETIME24H;
                $duedate         = isset($duedate)                  ? $duedate                              : DATETIME24H;
                $paiddate        = isset($paiddate)                 ? $paiddate                             : '';
                $custommessage   = isset($custommessage)            ? $custommessage                        : 'No memo or administrative comments';
                $archive         = isset($archive)                  ? intval($archive)                      : '0';
                $ispurchaseorder = isset($ispurchaseorder)          ? $ispurchaseorder                      : '0';
        
                // withdraw and deposit related transactions
                $iswithdraw 	 = isset($iswithdraw)  	            ? intval($iswithdraw)                   : '0';
                $isdeposit 	 = isset($isdeposit)  	            ? intval($isdeposit)                    : '0';
                $totalamount     = '0.00';
                
                $transactionid = (isset($transactionidx) AND !empty($transactionidx)) ? $transactionidx : construct_transaction_id();
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "invoices
                        (invoiceid, subscriptionid, projectid, buynowid, user_id, p2b_user_id, storeid, orderid, description, amount, paid, totalamount, status, paymethod, ipaddress, referer, createdate, duedate, paiddate, custommessage, transactionid, archive, ispurchaseorder, isdeposit, iswithdraw)
                        VALUES(
                        NULL,
                        '" . intval($subscriptionid) . "',
                        '" . intval($projectid) . "',
                        '" . intval($buynowid) . "',
                        '" . intval($user_id) . "',
                        '" . intval($p2b_user_id) . "',
                        '" . intval($storeid) . "',
                        '" . intval($orderid) . "',
                        '" . $ilance->db->escape_string($description) . "',
                        '" . $ilance->db->escape_string($amount) . "',
                        '" . $ilance->db->escape_string($paid) . "',
                        '" . $ilance->db->escape_string($totalamount) . "',
                        '" . $ilance->db->escape_string($status) . "',
                        '" . $ilance->db->escape_string($paymethod) . "',
                        '" . $ilance->db->escape_string($ipaddress) . "',
                        '" . $ilance->db->escape_string($referer) . "',
                        '" . $ilance->db->escape_string($createdate) . "',
                        '" . $ilance->db->escape_string($duedate) . "',
                        '" . $ilance->db->escape_string($paiddate) . "',
                        '" . $ilance->db->escape_string($custommessage) . "',
                        '" . $ilance->db->escape_string($transactionid) . "',
                        '" . $ilance->db->escape_string($archive) . "',
                        '" . intval($ispurchaseorder) . "',
                        '" . intval($isdeposit) . "',
                        '" . intval($iswithdraw) . "')
                ", 0, null, __FILE__, __LINE__); 
                
                // fetch new last invoice id
                $invoiceid = $ilance->db->insert_id();
                
                // do we skip the taxation support for this transaction?
                // we do this in some situations where the tax needs to be applied before the txn is created
                // for situations like escrow fees that we must already know how much to charge the customer for taxes
                // if we don't do this then the txn may have double taxes added to the overall amount
                // and situations like this usually mean that an unpaid transaction is being created (and tax) is
                // auto-applied
                
                // taxation support: is user taxable for this invoice type?
                // this code block will run if a transaction being created is unpaid waiting for payment from the customer
                $ilance->tax = construct_object('api.tax');
                if ($ilance->tax->is_taxable($user_id, $invoicetype) AND isset($dontprocesstax) AND $dontprocesstax == 0)
                {
                        // fetch tax amount to charge for this invoice type
                        $taxamount = $ilance->tax->fetch_amount($user_id, $amount, $invoicetype, 0);
                        
                        // fetch total amount to hold within the "totalamount" field
                        $totalamount = ($amount + $taxamount);
                        
                        // fetch tax bit to display when outputing tax infos
                        $taxinfo = $ilance->tax->fetch_amount($user_id, $amount, $invoicetype, 1);
                        
                        // portfolio invoicetypes are actually debit payments so treat it like so
                        if ($invoicetype == 'portfolio')
                        {
                                $invoicetype = 'debit';
                        }
                        
                        // in cases where an escrow payment is being made, and taxes are involved for commission fees,
                        // we will update our paid amount to the (total amount w/taxes) if our total amount is not the same
                        // as the amount we're paying. we do this because the invoice overview menu will show something like:
                        // Amount Paid: $250.00 but the Total Amount is $300.00 (taxes already applied and paid via escrow)
                        $extra = '';
                        if ($totalamount != $paid AND $totalamount > 0 AND $status == 'paid')
                        {
                                $extra = "paid = '" . $totalamount . "',";
                        }
                        
                        // member is taxable for this invoice type
                        $ilance->db->query("
                            UPDATE " . DB_PREFIX . "invoices
                            SET istaxable = '1',
                            $extra
                            totalamount = '" . sprintf("%01.2f", $totalamount) . "',
                            taxamount = '" . sprintf("%01.2f", $taxamount) . "',
                            taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
                            invoicetype = '" . $invoicetype . "'
                            WHERE invoiceid = '" . intval($invoiceid) . "'
                                AND user_id = '" . intval($user_id) . "'
                            ", 0, null, __FILE__, __LINE__);
                }
                else
                {
                        // portfolio invoicetypes are actually debit payments so treat it like so
                        if ($invoicetype == 'portfolio')
                        {
                                $invoicetype = 'debit';
                        }
                        
                        // in cases where an escrow payment is being made, and taxes are involved for commission fees,
                        // we will update our paid amount to the (total amount w/taxes) if our total amount is not the same
                        // as the amount we're paying. we do this because the invoice overview menu will show something like:
                        // Amount Paid: $250.00 but the Total Amount is $300.00 (taxes already applied and paid via escrow)
                        $extra = '';
                        if ($totalamount != $paid AND $totalamount > 0 AND $status == 'paid')
                        {
                                $extra = "paid = '".$totalamount."',";
                        }
                        
                        // customer not taxable > update totalamount value
                        $ilance->db->query("
                            UPDATE " . DB_PREFIX . "invoices
                            SET totalamount = '" . sprintf("%01.2f", $amount) . "',
                            $extra
                            invoicetype = '" . $invoicetype . "'
                            WHERE invoiceid = '" . intval($invoiceid) . "'
                                AND user_id = '" . intval($user_id) . "'
                            ", 0, null, __FILE__, __LINE__);
                }    
                  // Murugan Changes On Jan 31 For Combine Invoice  
                // New Consolidate Invoices Routine
                $lConsolidate = 0; // Set to 0 to disable
                if ($lConsolidate > 0)
                {				
                 $invoiceid = MP_cons($user_id, $projectid);
                }
 
                if (isset($returnid) AND $returnid > 0)
                {
                        return intval($invoiceid);
                }
			    /*if (isset($returnid) AND $returnid > 0)
                {
                        return intval($invoiceid);
                }*/
        }
        
        
        /**
        * Function for processing a regular debit or commission related transaction.
        *
        * @param       integer      user id
        * @param       integer      invoice id
        * @param       string       invoice type (subscription, escrow, debit, commission, p2b)
        * @param       string       amount to process
        * @param       string       method of payment (ipn/account/creditcard)
        * @param       string       name of gateway processing this transaction
        * @param       string       gateway transaction id
        * @param       boolean      silent mode (return only true or false; default false)
        *
        * @return      mixed        for ipn processing, boolean is used, others will use a print_notice() function to end user.
        */
        function process_debit_payment($userid = 0, $invoiceid = 0, $invoicetype = 'subscription', $amount = 0, $method = 'account', $gateway = '', $gatewaytxn = '', $silentmode = false)
        {
                global $ilance, $show, $myapi, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                
                ($apihook = $ilance->api('process_debit_payment_start')) ? eval($apihook) : false;
                
                // #### INSTANT PAYMENT NOTIFICATION HANDLER ###########################
                if ($method == 'ipn')
                {
                        $sql = $ilance->db->query("
                                SELECT invoiceid, invoicetype, description, amount, paid, duedate, paiddate, createdate, isif, isfvf, isescrowfee, projectid, buynowid
                                FROM " . DB_PREFIX . "invoices
                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                    AND user_id = '" . intval($userid) . "'
                                    AND status = 'unpaid'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res_invoice = $ilance->db->fetch_array($sql);
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "invoices
                                        SET paid = '" . $ilance->db->escape_string($amount) . "',
                                        status = 'paid',
                                        paiddate = '" . DATETIME24H . "',
                                        paymethod = '" . $ilance->db->escape_string($gateway) . "',
                                        custommessage = '" . $ilance->db->escape_string($gatewaytxn) . "'
                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                
                                if ($res_invoice['isif'])
                                {
                                        // this is an insertion fee.. update auction listing table
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET isifpaid = '1'
                                                WHERE ifinvoiceid = '" . intval($invoiceid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                if ($res_invoice['isfvf'])
                                {
                                        // this is a final value fee.. update auction listing table
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET isfvfpaid = '1'
                                                WHERE fvfinvoiceid = '" . intval($invoiceid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                if ($res_invoice['isescrowfee'])
                                {
                                        // this is a final value fee.. update auction listing table
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects_escrow
                                                SET isfeepaid = '1'
                                                WHERE project_id = '" . $res_invoice['projectid'] . "'
                                                        AND feeinvoiceid = '" . $invoiceid . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects_escrow
                                                SET isfee2paid = '1'
                                                WHERE project_id = '" . $res_invoice['projectid'] . "'
                                                        AND fee2invoiceid = '" . $invoiceid . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                ($apihook = $ilance->api('process_debit_payment_ipn_start')) ? eval($apihook) : false;
                                
                                // this could also be a payment from the "seller" for an unpaid "buy now" escrow fee OR unpaid "buy now" fvf.
                                // let's check the buynow order table to see if we have a matching invoice to update as "ispaid"..
                                // this scenerio would kick in once a buyer or seller deposits funds, this script runs and tries to pay the unpaid fees automatically..
                                // at the same time we need to update the buy now order table so the presentation layer knows what's paid, what's not.
                                $buynowcheck = $ilance->db->query("
                                        SELECT escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid
                                        FROM " . DB_PREFIX . "buynow_orders
                                        WHERE project_id = '" . $res_invoice['projectid'] . "'
                                                AND orderid = '" . $res_invoice['buynowid'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($buynowcheck) > 0)
                                {
                                        $resbuynow = $ilance->db->fetch_array($buynowcheck);
                                        if ($res_invoice['invoiceid'] == $resbuynow['escrowfeeinvoiceid'])
                                        {
                                                // invoice being paid is from seller paying a buy now escrow fee
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                        SET isescrowfeepaid = '1'
                                                        WHERE orderid = '" . $res_invoice['buynowid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else if ($res_invoice['invoiceid'] == $resbuynow['escrowfeebuyerinvoiceid'])
                                        {
                                                // invoice being paid is from buyer paying a buy now escrow fee
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                        SET isescrowfeebuyerpaid = '1'
                                                        WHERE orderid = '" . $res_invoice['buynowid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else if ($res_invoice['invoiceid'] == $resbuynow['fvfinvoiceid'])
                                        {
                                                // invoice being paid is from seller paying a buy now fvf
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                        SET isfvfpaid = '1'
                                                        WHERE orderid = '" . $res_invoice['buynowid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else if ($res_invoice['invoiceid'] == $resbuynow['fvfbuyerinvoiceid'])
                                        {
                                                // invoice being paid is from buyer paying a buy now fvf
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                        SET isfvfbuyerpaid = '1'
                                                        WHERE orderid = '" . $res_invoice['buynowid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                }
                                
                                // track income spent
                                insert_income_spent(intval($userid), sprintf("%01.2f", $amount), 'credit');
                                
                                $ilance->email = construct_dm_object('email', $ilance);
                                
                                $existing = array(
                                        '{{provider}}' => fetch_user('username', intval($userid)),
                                        '{{invoice_id}}' => intval($invoiceid),
                                        '{{invoice_amount}}' => $ilance->currency->format($amount),
                                );
                                
                                $ilance->email->mail = SITE_EMAIL;
                                $ilance->email->slng = fetch_site_slng();
                                
                                // email admin
                                $ilance->email->get('debit_fee_paid_online_account_admin');		
                                $ilance->email->set($existing);
                                
                                $ilance->email->send();
                                
                                // email user
                                $ilance->email->get('debit_fee_paid_online_account');		
                                $ilance->email->set($existing);
                                
                                $ilance->email->mail = fetch_user('email', intval($userid));
                                $ilance->email->slng = fetch_user_slng(intval($userid));
                                $ilance->email->send();
                                
                                return true;
                        }
                        
                        return false;
                }
                
                // #### ONLINE ACCOUNT HANDLER #########################################
                else if ($method == 'account')
                {
                        $sql_balance = $ilance->db->query("
                            SELECT available_balance, total_balance
                            FROM " . DB_PREFIX . "users
                            WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_balance) > 0)
                        {
                                $res_balance = $ilance->db->fetch_array($sql_balance);
                                
                                if ($res_balance['available_balance'] >= $amount)
                                {
                                        $avail_balance = $res_balance['available_balance'];
                                        $total_balance = $res_balance['total_balance'];
                                        
                                        $avail_balance_after = ($avail_balance - $amount);
                                        $total_balance_after = ($total_balance - $amount);
                                        
                                        $sql_invoice = $ilance->db->query("
                                                SELECT invoiceid, invoicetype, description, amount, paid, duedate, paiddate, createdate, isif, isfvf, isescrowfee, projectid, buynowid
                                                FROM " . DB_PREFIX . "invoices
                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                                    AND user_id = '" . intval($userid) . "'
                                                    AND status = 'unpaid'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_invoice) > 0)
                                        {
                                                $res_invoice = $ilance->db->fetch_array($sql_invoice);
                                                
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET paid = '" . $ilance->db->escape_string($amount) . "',
                                                        status = 'paid',
                                                        paiddate = '" . DATETIME24H . "'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                            AND user_id = '" . intval($userid) . "'
                                                            AND invoicetype = '" . $ilance->db->escape_string($invoicetype) . "'
                                                ", 0, null, __FILE__, __LINE__);
												
												//######3 herakle start feb 23 2011 ##//
												//work by karthik//
												$ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                        SET 
                                                        paiddate = '" . DATETIME24H . "'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'");
                                                           
														   //##### end #######//
                                                
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET available_balance = '" . $ilance->db->escape_string($avail_balance_after) . "',
                                                        total_balance = '" . $ilance->db->escape_string($total_balance_after) . "'
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                if ($res_invoice['isif'])
                                                {
                                                        // this is an insertion fee.. update auction listing table
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET isifpaid = '1'
                                                                WHERE ifinvoiceid = '" . intval($invoiceid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                
                                                if ($res_invoice['isfvf'])
                                                {
                                                        // this is a final value fee.. update auction listing table
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET isfvfpaid = '1'
                                                                WHERE fvfinvoiceid = '" . intval($invoiceid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                
                                                if ($res_invoice['isescrowfee'])
                                                {
                                                        // this is a final value fee.. update auction listing table
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects_escrow
                                                                SET isfeepaid = '1'
                                                                WHERE project_id = '" . $res_invoice['projectid'] . "'
                                                                        AND feeinvoiceid = '" . $invoiceid . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects_escrow
                                                                SET isfee2paid = '1'
                                                                WHERE project_id = '" . $res_invoice['projectid'] . "'
                                                                        AND fee2invoiceid = '" . $invoiceid . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                
                                                ($apihook = $ilance->api('process_debit_payment_account_start')) ? eval($apihook) : false;
                                                
                                                // this could also be a payment from the "seller" for an unpaid "buy now" escrow fee OR unpaid "buy now" fvf.
                                                // let's check the buynow order table to see if we have a matching invoice to update as "ispaid"..
                                                // this scenerio would kick in once a buyer or seller deposits funds, this script runs and tries to pay the unpaid fees automatically..
                                                // at the same time we need to update the buy now order table so the presentation layer knows what's paid, what's not.
                                                $buynowcheck = $ilance->db->query("
                                                        SELECT orderid, escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid
                                                        FROM " . DB_PREFIX . "buynow_orders
                                                        WHERE project_id = '" . $res_invoice['projectid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($buynowcheck) > 0)
                                                {
                                                        while ($resbuynow = $ilance->db->fetch_array($buynowcheck))
                                                        {
                                                                if ($res_invoice['invoiceid'] == $resbuynow['escrowfeeinvoiceid'])
                                                                {
                                                                        // invoice being paid is from seller paying a buy now escrow fee
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "buynow_orders
                                                                                SET isescrowfeepaid = '1'
                                                                                WHERE orderid = '" . $resbuynow['orderid'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                }
                                                                else if ($res_invoice['invoiceid'] == $resbuynow['escrowfeebuyerinvoiceid'])
                                                                {
                                                                        // invoice being paid is from buyer paying a buy now escrow fee
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "buynow_orders
                                                                                SET isescrowfeebuyerpaid = '1'
                                                                                WHERE orderid = '" . $resbuynow['orderid'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                }
                                                                else if ($res_invoice['invoiceid'] == $resbuynow['fvfinvoiceid'])
                                                                {
                                                                        // invoice being paid is from seller paying a buy now fvf
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "buynow_orders
                                                                                SET isfvfpaid = '1'
                                                                                WHERE orderid = '" . $resbuynow['orderid'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                }
                                                                else if ($res_invoice['invoiceid'] == $resbuynow['fvfbuyerinvoiceid'])
                                                                {
                                                                        // invoice being paid is from buyer paying a buy now fvf
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "buynow_orders
                                                                                SET isfvfbuyerpaid = '1'
                                                                                WHERE orderid = '" . $resbuynow['orderid'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                }
                                                        }
                                                }
                                                
                                                // track income spent
                                                insert_income_spent(intval($userid), sprintf("%01.2f", $amount), 'credit');
                                                
                                                $ilance->email = construct_dm_object('email', $ilance);
                                                
                                                $existing = array(
                                                        '{{provider}}' => $_SESSION['ilancedata']['user']['username'],
                                                        '{{invoice_id}}' => intval($invoiceid),
                                                        '{{invoice_amount}}' => $ilance->currency->format($amount),
                                                );
                                                
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                        
                                                // email admin
                                                $ilance->email->get('debit_fee_paid_online_account_admin');		
                                                $ilance->email->set($existing);
                                                
                                                if ($silentmode == false)
                                                {
                                                        $ilance->email->send();
                                                }
                                                
                                                // email user
                                                $ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
                                                $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                
                                                $ilance->email->get('debit_fee_paid_online_account');		
                                                $ilance->email->set($existing);
                                                
                                                if ($silentmode == false)
                                                {
                                                        $ilance->email->send();
                                                }
                                                
                                                if ($silentmode)
                                                {
                                                        return true;
                                                }
                                                
                                                $area_title = $phrase['_invoice_payment_complete_menu'];
                                                $page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_complete_menu'];
                                                
                                                print_notice($phrase['_invoice_payment_complete'], $phrase['_your_invoice_has_been_paid_in_full'], $ilpage['accounting'], $phrase['_my_account']);
                                                exit();
                                        }
                                        else
                                        {
                                                if ($silentmode)
                                                {
                                                        return false;
                                                }
                                                
                                                $area_title = $phrase['_invoice_payment_menu_denied_payment_does_not_belong_to_user'];
                                                $page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu_denied_payment_does_not_belong_to_user'];
                                                
                                                print_notice($phrase['_invoice_error'], $phrase['_were_sorry_this_invoice_does_not_exist']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['accounting'], $phrase['_my_account']);
                                                exit();
                                        }
                                }
                                else
                                {
                                        if ($silentmode)
                                        {
                                                return false;
                                        }
                                        
                                        $area_title = $phrase['_funds_not_available'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_funds_not_available'];
                                        
                                        print_notice($phrase['_invoice_payment_warning_insufficient_funds'], $phrase['_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['accounting'], $phrase['_my_account']);
                                        exit();
                                }
                        }
                }
                
                return false;
        }
        
        /**
        * Function for processing a credential verification payment request.
        *
        * @param       integer      user id
        * @param       string       payment method
        * @param       integer      answer id
        * @param       integer      question id
        * @param       string       actual value
        * @param       string       contact name of reference
        * @param       string       contact phone of reference
        * @param       string       contact notes
        *
        * @return      nothing
        */
        function process_credential_payment($userid = 0, $method = 'account', $answerid = 0, $questionid = 0, $answer = '', $contactname = '', $contactnumber = '', $contactnotes = '')
        {
                global $ilance, $myapi, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                
                $sql = $ilance->db->query("
                        SELECT verifycost, question
                        FROM " . DB_PREFIX . "profile_questions
                        WHERE questionid = '" . intval($questionid) . "'
                            AND canverify = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $resamount = $ilance->db->fetch_array($sql);
                        $question = stripslashes($resamount['question']);
                        
                        // fetch amount
                        $amount = $resamount['verifycost'];
                        $total = $amount;
                        
                        // does tax apply?
                        $ilance->tax = construct_object('api.tax');
                        if ($ilance->tax->is_taxable(intval($userid), 'credential') AND $amount > 0)
                        {
                                $taxamount = $ilance->tax->fetch_amount(intval($userid), $amount, 'credential', 0);
                                $total = ($amount+$taxamount);
                        }
                        
                        // payment process via online account balance
                        if ($method == 'account')
                        {
                                $sel_balance = $ilance->db->query("
                                        SELECT available_balance, total_balance
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sel_balance) > 0)
                                {
                                        $res_balance = $ilance->db->fetch_array($sel_balance);
                                        if ($res_balance['available_balance'] < $total)
                                        {
                                                $area_title = $phrase['_no_funds_available_in_online_account'];
                                                $page_title = SITE_NAME . ' - ' . $phrase['_no_funds_available_in_online_account'];
                                                
                                                print_notice($phrase['_invoice_payment_warning_insufficient_funds'], $phrase['_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds'] . '<br /><br />' . $phrase['_please_contact_customer_support'], $ilpage['selling'] . '?cmd=profile', $phrase['_selling_profile']);
                                                exit();
                                        }
                                        else
                                        {
                                                $area_title = $phrase['_profile_verification_payment_via_online_account'];
                                                $page_title = SITE_NAME . ' - ' . $phrase['_profile_verification_payment_via_online_account'];
                                                
                                                $transactionid = construct_transaction_id();
                                                
                                                $newinvoiceid = $this->insert_transaction(
                                                        0,
                                                        0,
                                                        0,
                                                        intval($userid),
                                                        0,
                                                        0,
                                                        0,
                                                        $phrase['_profile_verification_fee_question'] . ' ' . $ilance->db->escape_string($question),
                                                        sprintf("%01.2f", $amount),
                                                        sprintf("%01.2f", $total),
                                                        'paid',
                                                        'credential',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        DATETIME24H,
                                                        $phrase['_verification_requested_on'] . ' ' . DATETIME24H,
                                                        0,
                                                        0,
                                                        1,
                                                        $transactionid
                                                );
                                                    
                                                $new_total = ($res_balance['total_balance'] - $total);
                                                $new_avail = ($res_balance['available_balance'] - $total);
                    
                                                // update account data                            
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET available_balance = '" . sprintf("%01.2f", $new_avail) . "',
                                                        total_balance = '" . sprintf("%01.2f", $new_total) . "'
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // track income spent
                                                insert_income_spent(intval($userid), sprintf("%01.2f", $total), 'credit');
                                                
                                                // #### REFERRAL SYSTEM TRACKER ############################
                                                update_referral_action('credential', intval($userid));
                                                
                                                $isverified = '0';
                                                if ($ilconfig['verificationmoderation'] == 0)
                                                {
                                                        $isverified = '1';
                                                }
                                                
                                                $expiry = gmdate('Y-m-d H:i:s', mktime(gmdate('H', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('i', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('s', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('m', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('d', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + $ilconfig['verificationlength'], gmdate('Y', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))));
                                                
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "profile_answers
                                                        SET invoiceid = '" . intval($newinvoiceid) . "',
                                                        contactname = '" . $ilance->db->escape_string($contactname) . "',
                                                        contactnumber = '" . $ilance->db->escape_string($contactnumber) . "',
                                                        contactnotes = '" . $ilance->db->escape_string($contactnotes) . "',
                                                        isverified = '" . $isverified . "',
                                                        verifyexpiry = '" . $expiry . "'
                                                        WHERE questionid = '" . intval($questionid) . "'
                                                            AND answerid = '" . intval($answerid) . "'
                                                            AND user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                $ilance->email = construct_dm_object('email', $ilance);
                
                                                $existing = array(
                                                        '{{contactname}}' => $contactname,
                                                        '{{contactnumber}}' => $contactnumber,
                                                        '{{contactnotes}}' => $contactnotes,
                                                        '{{expiry}}' => $expiry,
                                                        '{{customer}}' => $_SESSION['ilancedata']['user']['username'],
                                                        '{{transactionid}}' => $transactionid,
                                                        '{{question}}' => $question,
                                                        '{{answer}}' => $answer,
                                                        '{{total_amount_formatted}}' => $ilance->currency->format($total)
                                                );
                
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                
                                                $ilance->email->get('profile_verification_pending_admin');		
                                                $ilance->email->set($existing);
                                                
                                                $ilance->email->send();
                                                
                                                $ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
                                                $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                
                                                $ilance->email->get('profile_verification_pending');		
                                                $ilance->email->set($existing);
                                                
                                                $ilance->email->send();
                                                
                                                print_notice($phrase['_invoice_payment_complete'], $phrase['_your_invoice_has_been_paid_in_full'] . '<br /><br />', $ilpage['selling'] . '?cmd=profile', $phrase['_selling_profile']);
                                                exit();
                                        }
                                }
                        }            
                }
                else
                {
                        print_notice($phrase['_access_denied'], $phrase['_sorry_your_profile_verification_process_could_not_be_completed'], 'javascript:history.back(1);', $phrase['_back']);
                        exit();
                }
        }
        
        /**
        * Function to generate a credit card type pulldown menu.
        *
        * @param       string       selected card type (optional)
        * @param       string       select menu field name (default is card[type])
        *
        * @return      string       returns HTML representation of the pulldown selection menu
        */
        function creditcard_type_pulldown($selected = '', $fieldname = 'form[type]')
        {
                $html = '<select name="' . $fieldname . '" style="font-family: Verdana">';
                
                $html .= '<option value="visa"';
                if ($selected == 'visa')
                {
                        $html .= ' selected="selected"';
                }
                $html .= '>Visa</option>';
                $html .= '<option value="mc"';
                if ($selected == 'mc')
                {
                        $html .= ' selected="selected"';
                }
                $html .= '>Mastercard</option>';
                $html .= '<option value="amex"';
                if ($selected == 'amex')
                {
                        $html .= ' selected="selected"';
                }
                $html .= '>American Express</option>';
                $html .= '<option value="disc"';
                if ($selected == 'disc')
                {
                        $html .= ' selected="selected"';
                }
                $html .= '>Discover</option>';
                $html .= '</select>';
                
                return $html;
        }
            
        /**
        * Function to generate a credit card month pulldown menu.
        *
        * @param       string       selected month (optional)
        * @param       string       select menu field name (default is card[expmon])
        * 
        * @return      string       returns HTML representation of the pulldown selection menu
        */
        function creditcard_month_pulldown($selected = '', $fieldname = 'form[expmon]')
        {
                $html  = '<select name="' . $fieldname . '" style="font-family: Verdana">';
                for($i = 1; $i < 13; $i++)
                {
                        $html  .= '<option value="';
                        if ($i < 10)
                        {
                                $html  .= '0' . $i;
                                if ($selected == '0' . $i)
                                {
                                        $html .= '" selected="selected">0' . $i;
                                }
                                else
                                {
                                        $html .= '">0' . $i;
                                }
                        }
                        else
                        {
                                $html .= $i;
                                if ($selected == $i)
                                {
                                        $html .= '" selected="selected">' . $i;
                                }
                                else
                                {
                                        $html .= '">' . $i;
                                }
                                $html .= '</option>';
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
            
        /**
        * Function to generate a credit card year pulldown menu.
        *
        * @param       string       selected year (optional)
        * @param       string       select menu field name (default is card[expyear])
        *
        * @return      string       returns HTML representation of the pulldown selection menu
        */
        function creditcard_year_pulldown($selected = '', $fieldname = 'form[expyear]')
        {
                $html = '<select name="' . $fieldname . '" style="font-family: Verdana">';
                
                for ($i = date('Y'); $i < date('Y')+10; $i++)
                {
                        if ($selected == mb_substr("$i", -2))
                        {
                                $html .= '<option value="' . mb_substr("$i", -2) . '" selected="selected">';
                        }
                        else
                        {
                                $html .= '<option value="' . mb_substr("$i", -2) . '">';
                        }
                        $html .= $i;
                        $html .= '</option>';
                }
                
                $html .= '</select>';
                
                return $html;
        }
            
        /**
        * Function to generate a credit card country pulldown menu.
        *
        * @param       string       selected year (optional)
        * @param       string       short language identifier (default eng)
        * @param       string       pulldown menu fieldname (default form[countryid])
        *
        * @return      string       returns HTML representation of the pulldown selection menu
        */
        function creditcard_country_pulldown($countryid, $slng = 'eng', $fieldname = 'form[countryid]')
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $html = '<select name="' . $fieldname . '" style="font-family: Verdana">';
                
                $sql = $ilance->db->query("
                        SELECT locationid, location_$slng AS location
                        FROM " . DB_PREFIX . "locations
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $html .= '<option value="' . $res['locationid'] . '"';
                                if ($res['locationid'] == $countryid)
                                {
                                        $html .= 'selected="selected"';
                                }
                                $html .= '>' . ucwords(stripslashes($res['location'])) . '</option>';
                        }
                }
                
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print the destination currency pulldown menu
        *
        * @param       string       selected pulldown option (optional)
        * @param       string       actual form field name
        * @param       boolean      show currency names in pulldown menu in uppercase? (default false)
        *
        * @return      string       Returns HTML representation of the destination currency pulldown types
        */
        function print_destination_currency_pulldown($selected = '', $fieldname = 'form[destination_currency_id]', $uppercase = false)
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $html = '<select name="' . $fieldname . '" style="font-family: verdana">';
                
                $sql = $ilance->db->query("
                        SELECT currency_id, currency_name
                        FROM " . DB_PREFIX . "currency
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $html .= '<option value="' . $res['currency_id'] . '"';
                                
                                if (empty($selected) AND $res['currency_id'] == $_SESSION['ilancedata']['user']['currencyid'])
                                {
                                        $html .= ' selected="selected"';
                                }
                                else if (!empty($selected) AND $res['currency_id'] == $selected)
                                {
                                        $html .= ' selected="selected"';
                                }
                                
                                if ($uppercase)
                                {
                                        $html .= '>' . mb_strtoupper($res['currency_name']) . '</option>';
                                }
                                else
                                {
                                        $html .= '>' . $res['currency_name'] . '</option>';
                                }
                        }
                }
                
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print a bank account type pulldown menu
        *
        * @param       string       selected pulldown option (optional)
        * @param       string       actual form field name
        *
        * @return      string       Returns HTML representation of the bank account pulldown types
        */
        function print_bank_account_type_pulldown($selected = '', $fieldname = 'form[bank_account_type]')
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $options = array('checking', 'savings');
                
                $html = '<select name="' . $fieldname . '" style="font-family: verdana">';
                foreach ($options AS $type)
                {
                        if (isset($selected) AND $selected == $type)
                        {
                                $html .= '<option value="' . $type . '" selected="selected">' . $phrase["_$type"] . '</option>';   
                        }
                        else
                        {
                                $html .= '<option value="' . $type . '">' . $phrase["_$type"] . '</option>';
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to verify mod-10 characteristics of a credit card number.
        *
        * @param       string       credit card number
        *
        * @return      bool         true or false valid mod10
        */
        function verify_creditcard_mod10($strccno = '')
        {
                if (empty($strccno))
                {
                        return false;
                }
                $len = mb_strlen($strccno);
                if ($len < 13 OR $len > 16)
                {
                        return false;
                }
                $checkdig = (int)$strccno[--$len];
                for ($i=--$len, $sum = 0, $dou = true; $i >= 0; $i--, $dou =! $dou)
                {
                        $curdig = (int)$strccno[$i];
                        if ($dou)
                        {
                                $curdig *= 2;
                                if ($curdig > 9) $curdig-=9;
                        }
                        $sum += $curdig;
                }
                if (($checkdig+$sum)%10 == 0)
                {
                        return true;
                }
                else
                {
                        return false;
                }
        }
        
        /**
        * Function to create a new bank account deposit account for a registered member.
        *
        * @param       array        array holding all $form['xx'] field values
        * @param       integer      user id
        *
        * @return      bool         true on success
        */
        function insert_bank_account($form = array(), $userid = 0)
        {
                global $ilance, $ilconfig, $phrase, $ilpage, $myapi;
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "bankaccounts
                        (bank_id, user_id, beneficiary_account_name, destination_currency_id, beneficiary_bank_name,
                        beneficiary_account_number, beneficiary_bank_routing_number_swift, bank_account_type, beneficiary_bank_address_1,
                        beneficiary_bank_address_2, beneficiary_bank_city, beneficiary_bank_state, beneficiary_bank_zipcode, beneficiary_bank_country_id) VALUES(
                        NULL,
                        '" . intval($userid) . "',
                        '" . $ilance->db->escape_string($form['beneficiary_account_name']) . "',
                        '" . intval($form['destination_currency_id']) . "',
                        '" . $ilance->db->escape_string($form['beneficiary_bank_name']) . "',
                        '" . $ilance->db->escape_string($form['beneficiary_account_number']) . "',
                        '" . $ilance->db->escape_string($form['beneficiary_bank_routing_number_swift']) . "',
                        '" . $ilance->db->escape_string($form['bank_account_type']) . "',
                        '" . $ilance->db->escape_string($form['beneficiary_bank_address_1']) . "',
                        '" . $ilance->db->escape_string($form['beneficiary_bank_address_2']) . "',
                        '" . $ilance->db->escape_string($form['beneficiary_bank_city']) . "',
                        '" . $ilance->db->escape_string($form['beneficiary_bank_state']) . "',
                        '" . $ilance->db->escape_string($form['beneficiary_bank_zipcode']) . "',
                        '" . intval($form['beneficiary_bank_country_id']) . "')
                ", 0, null, __FILE__, __LINE__);
                $bank_id = $ilance->db->insert_id();
                
                $ilance->email = construct_dm_object('email', $ilance);
                
                $ilance->email->mail = fetch_user('email', intval($userid));
                $ilance->email->slng = fetch_user_slng(intval($userid));
                
                $ilance->email->get('member_added_bankaccount');		
                $ilance->email->set(array(
                        '{{username}}' => fetch_user('username', intval($userid)),
                ));
                
                $ilance->email->send();                    
                
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_site_slng();
                
                $ilance->email->get('member_added_bankaccount_admin');		
                $ilance->email->set(array(
                        '{{username}}' => fetch_user('username', intval($userid)),
                        '{{account_name}}' => $form['beneficiary_account_name'],
                        '{{bank_name}}' => $form['beneficiary_bank_name'],
                        '{{account_type}}' => $form['bank_account_type'],
                        '{{bank_city}}' => $form['beneficiary_bank_city'],
                        '{{bank_zippostal}}' => $form['beneficiary_bank_zipcode'],
                ));
                
                $ilance->email->send();
                
                return true;
        }
        
        /**
        * Function to create a new credit card account for a registered member.
        *
        * @param       array        array holding all $form['xx'] field values
        * @param       integer      user id
        *
        * @return      bool         true on success
        */
        function insert_creditcard($form = array(), $userid = 0)
        {
                global $ilance, $ilconfig, $phrase, $ilpage, $myapi;
                
                $form['number_encrypted'] = $ilance->crypt->three_layer_encrypt($form['number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                $form['authorized'] = 'yes';
                $form['creditcard_status'] = 'active';
                $form['default_card'] = 'yes';
                
                if ($ilconfig['creditcard_authentication'])
                {
                        $form['authorized'] = 'no';
                }
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "creditcards
                        (cc_id, date_added, date_updated, user_id, creditcard_number, creditcard_expiry, cvv2, name_on_card, phone_of_cardowner, email_of_cardowner, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country, creditcard_status, default_card, creditcard_type, authorized) 
                        VALUES(
                        NULL,
                        '" . DATETIME24H . "',
                        '" . DATETIME24H . "',
                        '" . intval($userid) . "',
                        '" . $ilance->db->escape_string($form['number_encrypted']) . "',
                        '" . $ilance->db->escape_string($form['expmon'] . $form['expyear']) . "',
                        '" . intval($form['cvv2']) . "',
                        '" . $ilance->db->escape_string($form['first_name'] . " " . $form['last_name']) . "',
                        '" . $ilance->db->escape_string($form['phone']) . "',
                        '" . $ilance->db->escape_string($form['email']) . "',
                        '" . $ilance->db->escape_string($form['address1']) . "',
                        '" . $ilance->db->escape_string($form['address2']) . "',
                        '" . $ilance->db->escape_string($form['city']) . "',
                        '" . $ilance->db->escape_string($form['state']) . "',
                        '" . $ilance->db->escape_string($form['postalzip']) . "',
                        '" . $ilance->db->escape_string($form['countryid']) . "',
                        '" . $ilance->db->escape_string($form['creditcard_status']) . "',
                        '" . $ilance->db->escape_string($form['default_card']) . "',
                        '" . $ilance->db->escape_string($form['type']) . "',
                        '" . $ilance->db->escape_string($form['authorized']) . "')
                ", 0, null, __FILE__, __LINE__);
                $cc_id = $ilance->db->insert_id();    
                
                $ilance->email = construct_dm_object('email', $ilance);
                
                $ilance->email->mail = fetch_user('email', $userid);
                $ilance->email->slng = fetch_user_slng(intval($userid));
                
                $ilance->email->get('member_added_new_card');		
                $ilance->email->set(array(
                        '{{username}}' => fetch_user('username', intval($userid)),
                ));
                
                $ilance->email->send();
                
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_site_slng();
                
                $ilance->email->get('member_added_new_card_admin');		
                $ilance->email->set(array(
                        '{{username}}' => fetch_user('username', intval($userid)),
                ));
                
                $ilance->email->send();
                                        
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "subscription_user
                        SET paymethod = 'account'
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                
                return true;
        }
        
        /**
        * Function to update an existing bank account for a registered member.
        *
        * @param       array        array holding all $form['xx'] field values
        * @param       integer      user id
        *
        * @return      bool         true on success
        */
        function update_bank_account($form = array(), $userid = 0)
        {
                global $ilance, $ilconfig, $phrase, $ilpage, $myapi;
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "bankaccounts
                        SET beneficiary_account_name = '" . $ilance->db->escape_string($form['beneficiary_account_name']) . "',
                        destination_currency_id = '" . intval($form['destination_currency_id']) . "',
                        beneficiary_bank_name = '" . $ilance->db->escape_string($form['beneficiary_bank_name']) . "',
                        beneficiary_account_number = '" . $ilance->db->escape_string($form['beneficiary_account_number']) . "',
                        beneficiary_bank_routing_number_swift = '" . $ilance->db->escape_string($form['beneficiary_bank_routing_number_swift']) . "',
                        bank_account_type = '" . $ilance->db->escape_string($form['bank_account_type']) . "',
                        beneficiary_bank_address_1 = '" . $ilance->db->escape_string($form['beneficiary_bank_address_1']) . "',
                        beneficiary_bank_address_2 = '" . $ilance->db->escape_string($form['beneficiary_bank_address_2']) . "',
                        beneficiary_bank_city = '" . $ilance->db->escape_string($form['beneficiary_bank_city']) . "',
                        beneficiary_bank_state = '" . $ilance->db->escape_string($form['beneficiary_bank_state']) . "',
                        beneficiary_bank_zipcode = '" . $ilance->db->escape_string($form['beneficiary_bank_zipcode']) . "',
                        beneficiary_bank_country_id = '" . intval($form['beneficiary_bank_country_id']) . "'
                        WHERE bank_id = '".intval($form['bankid'])."'
                        AND user_id = '" . intval($userid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                
                return true;
        }
            
        /**
        * Function to update an existing credit card account for a registered member.
        *
        * @param       array        array holding all $form['xx'] field values
        * @param       integer      user id
        *
        * @return      bool         true on success
        */
        function update_creditcard($form = array(), $userid = 0)
        {
                global $ilance, $ilconfig, $phrase, $ilpage, $myapi;
                
                $form['number_encrypted'] = $ilance->crypt->three_layer_encrypt($form['number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                $form['authorized'] = 'yes';
                
                if ($ilconfig['creditcard_authentication'])
                {
                        $form['authorized'] = 'no';    
                }
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "creditcards 
                        SET creditcard_number = '" . $ilance->db->escape_string($form['number_encrypted']) . "',
                        creditcard_type = '" . mb_strtolower($form['type']) . "',
                        date_updated = '" . DATETIME24H . "',
                        creditcard_expiry = '" . $form['expmon'] . $form['expyear'] . "',
                        cvv2 = '" . $ilance->db->escape_string($form['cvv2']) . "',
                        name_on_card = '" . $ilance->db->escape_string($form['first_name'] . " " . $form['last_name']) . "',
                        phone_of_cardowner = '" . $ilance->db->escape_string($form['phone']) . "',
                        email_of_cardowner = '" . $ilance->db->escape_string($form['email']) . "',
                        card_billing_address1 = '" . $ilance->db->escape_string($form['address1']) . "',
                        card_billing_address2 = '" . $ilance->db->escape_string($form['address2']) . "',
                        card_city = '" . $ilance->db->escape_string($form['city']) . "',
                        card_state = '" . $ilance->db->escape_string($form['state']) . "',
                        card_postalzip = '" . $ilance->db->escape_string($form['postalzip']) . "',
                        card_country = '" . $ilance->db->escape_string($form['countryid']) . "',
                        authorized = '" . $ilance->db->escape_string($form['authorized']) . "'
                        WHERE user_id = '" . intval($userid) . "'
                        AND cc_id = '" . $ilance->db->escape_string($form['cc_id']) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        
                $ilance->email = construct_dm_object('email', $ilance);
                
                $ilance->email->mail = fetch_user('email', intval($userid));
                $ilance->email->slng = fetch_user_slng(intval($userid));
                
                $ilance->email->get('member_updated_creditcard');		
                $ilance->email->set(array(
                        '{{member}}' => fetch_user('username', intval($userid)),
                ));
                
                $ilance->email->send();
                
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_site_slng();
                
                $ilance->email->get('member_updated_creditcard_admin');		
                $ilance->email->set(array(
                        '{{member}}' => fetch_user('username', intval($userid)),
                ));
                
                $ilance->email->send();
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "subscription_user
                        SET paymethod = 'account'
                        WHERE user_id = '" . intval($userid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                
                return true;
        }
        
        /**
        * Function to remove a bank account account for a registered member.
        *
        * @param       integer      bank account id number
        * @param       integer      user id
        *
        * @return      bool         true on success
        */
        function remove_bank_account($bankid = 0, $userid = 0)
        {
                global $ilance, $ilconfig, $ilpage, $phrase;
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "bankaccounts
                        WHERE bank_id = '" . intval($bankid) . "'
                            AND user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
        
                $ilance->email = construct_dm_object('email', $ilance);
                
                $ilance->email->mail = fetch_user('email', intval($userid));
                $ilance->email->slng = fetch_user_slng(intval($userid));
                
		$ilance->email->get('member_removed_bank_account');		
		$ilance->email->set(array(
			'{{member}}' => fetch_user('username', intval($userid)),
		));
		
		$ilance->email->send();
                
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_site_slng();
                
		$ilance->email->get('member_removed_bank_account_admin');		
		$ilance->email->set(array(
			'{{member}}' => fetch_user('username', intval($userid)),
		));
		
		$ilance->email->send();
                
                return true;
        }
        
        /**
        * Function to remove a credit card account for a registered member.
        *
        * @param       integer      credit card id number
        * @param       integer      user id
        *
        * @return      bool         true on success
        */
        function remove_creditcard($ccid = 0, $userid = 0)
        {
                global $ilance, $ilconfig, $ilpage, $phrase;
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "creditcards
                        WHERE cc_id = '" . intval($ccid) . "'
                            AND user_id = '" . intval($userid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        
                // change paymethod to online account
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "subscription_user
                        SET paymethod = 'account'
                        WHERE user_id = '" . intval($userid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        
                $ilance->email = construct_dm_object('email', $ilance);
                
                $ilance->email->mail = fetch_user('email', intval($userid));
                $ilance->email->slng = fetch_user_slng(intval($userid));
                
		$ilance->email->get('member_removed_creditcard');		
		$ilance->email->set(array(
			'{{member}}' => fetch_user('username', intval($userid)),
		));
		
		$ilance->email->send();
                
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_site_slng();
                
		$ilance->email->get('member_removed_creditcard_admin');		
		$ilance->email->set(array(
			'{{member}}' => fetch_user('username', intval($userid)),
		));
		
		$ilance->email->send();
                
                return true;
        }        
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>