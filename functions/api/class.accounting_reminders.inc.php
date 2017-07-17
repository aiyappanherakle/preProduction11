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
* Function to handle accounting reminders
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class accounting_reminders extends accounting
{
	/**
        * Function designed to send out commission and credential invoice reminder notices based on an admin defined email dispatch frequency
        * This function is called from cron.reminders.php
        */
        function send_unpaid_invoice_frequency_reminders()
        {
                global $ilance, $phrase, $ilconfig;
                
                $ilance->email = construct_dm_object('email', $ilance);
                
                $cronlog = '';
                $count = 0;
                $remindfrequency = $ilance->datetime->fetch_date_fromnow($ilconfig['invoicesystem_resendfrequency']);
                
                $expiry = $ilance->db->query("
                        SELECT user_id, invoiceid, projectid, buynowid, description, paiddate, invoicetype, duedate, createdate, amount, paid, totalamount, transactionid, isif, isfvf, currency_id
                        FROM " . DB_PREFIX . "invoices
                        WHERE (invoicetype = 'commission' OR invoicetype = 'credential' OR invoicetype = 'debit')
                            AND isdeposit = '0'
                            AND iswithdraw = '0'
                            AND ispurchaseorder = '0'
                            AND (status = 'unpaid' OR status = 'scheduled')
                            AND amount > 0
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($expiry) > 0)
                {
                        while ($reminder = $ilance->db->fetch_array($expiry, DB_ASSOC))
                        {
                                $user = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $reminder['user_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($user) > 0)
                                {
                                        $res_user = $ilance->db->fetch_array($user, DB_ASSOC);
                                        
                                        // does user have sufficient funds within online account to cover invoice
                                        // before we send an unpaid reminder?
                                        if ($res_user['available_balance'] >= $reminder['totalamount'] AND fetch_user('autopayment', $res_user['user_id']))
                                        {
                                                // pay invoice using funds available via online account
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET status = 'paid',
                                                        paid = '" . $reminder['totalamount'] . "',
                                                        paymethod = 'account',
                                                        paiddate = '" . DATETIME24H . "',
                                                        custommessage = '" . $ilance->db->escape_string($phrase['_automated_debit_from_account_balance_via_billing_and_payments']) . "'
                                                        WHERE user_id = '" . $res_user['user_id'] . "'
                                                            AND invoiceid = '" . $reminder['invoiceid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // adjust customers online account balances
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET total_balance = total_balance - $reminder[totalamount],
                                                        available_balance = available_balance - $reminder[totalamount]
                                                        WHERE user_id = '" . $res_user['user_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // handle the insertion fee and/or final value fee automatic payment settlement and set the fields in the auction table to paid
                                                if ($reminder['isif'])
                                                {
                                                        // this is an insertion fee.. update auction listing table
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET isifpaid = '1'
                                                                WHERE project_id = '" . $reminder['projectid'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                if ($reminder['isfvf'])
                                                {
                                                        // this is a final value fee.. update auction listing table
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET isfvfpaid = '1'
                                                                WHERE project_id = '" . $reminder['projectid'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                
                                                // this could also be a payment from the "seller" for an unpaid "buy now" escrow fee OR unpaid "buy now" fvf.
                                                // let's check the buynow order table to see if we have a matching invoice to update as "ispaid"..
                                                // this scenerio would kick in once a buyer or seller deposits funds, this script runs and tries to pay the unpaid fees automatically..
                                                // at the same time we need to update the buy now order table so the presentation layer knows what's paid, what's not.
                                                $buynowcheck = $ilance->db->query("
                                                        SELECT escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid
                                                        FROM " . DB_PREFIX . "buynow_orders
                                                        WHERE project_id = '" . $reminder['projectid'] . "'
                                                                AND orderid = '" . $reminder['buynowid'] . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($buynowcheck) > 0)
                                                {
                                                        $resbuynow = $ilance->db->fetch_array($buynowcheck);
                                                        
                                                        if ($reminder['invoiceid'] == $resbuynow['escrowfeeinvoiceid'])
                                                        {
                                                                // invoice being paid is from seller paying a buy now escrow fee
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                                        SET isescrowfeepaid = '1'
                                                                        WHERE orderid = '" . $reminder['buynowid'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        else if ($reminder['invoiceid'] == $resbuynow['escrowfeebuyerinvoiceid'])
                                                        {
                                                                // invoice being paid is from buyer paying a buy now escrow fee
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                                        SET isescrowfeebuyerpaid = '1'
                                                                        WHERE orderid = '" . $reminder['buynowid'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        else if ($reminder['invoiceid'] == $resbuynow['fvfinvoiceid'])
                                                        {
                                                                // invoice being paid is from seller paying a buy now fvf
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                                        SET isfvfpaid = '1'
                                                                        WHERE orderid = '" . $reminder['buynowid'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        else if ($reminder['invoiceid'] == $resbuynow['fvfbuyerinvoiceid'])
                                                        {
                                                                // invoice being paid is from buyer paying a buy now fvf
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                                        SET isfvfbuyerpaid = '1'
                                                                        WHERE orderid = '" . $reminder['buynowid'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                }
                                                
                                                // adjust members total amount paid for subscription plan
                                                insert_income_spent($res_user['user_id'], sprintf("%01.2f", $reminder['totalamount']), 'credit');
                                                
                                                // remove old invoice logs now that it's paid in full
                                                $ilance->db->query("
                                                        DELETE FROM " . DB_PREFIX . "invoicelog
                                                        WHERE invoiceid = '" . $reminder['invoiceid'] . "'
                                                ");
                                                
						$existing = array(
                                                        '{{provider}}' => $res_user['username'],
                                                        '{{invoiceid}}' => $reminder['invoiceid'],
                                                        '{{new_txn_transaction}}' => $reminder['transactionid'],
                                                        '{{invoice_amount}}' => $ilance->currency->format($reminder['totalamount'], $reminder['currency_id']),
                                                        '{{description}}' => $reminder['description'],
                                                        '{{datepaid}}' => $reminder['paiddate'],
                                                );
						
                                                // email user
                                                $ilance->email->mail = $res_user['email'];
                                                $ilance->email->slng = fetch_user_slng($res_user['user_id']);
                                                
                                                $ilance->email->get('transaction_payment_complete');		
                                                $ilance->email->set($existing);
                                                
                                                // murugan changes on Mar 19
												//$ilance->email->send();
                                                
                                                // email admin
                                                /*
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                
                                                $ilance->email->get('transaction_payment_complete_admin');		
                                                $ilance->email->set($existing);
                                                
                                                $ilance->email->send();
                                                */
                                        }
                                        
                                        // insufficient funds
                                        else 
                                        {
                                                // unpaid invoice reminder for this customer
                                                $logs = $ilance->db->query("
                                                        SELECT invoicelogid, date_sent, date_remind
                                                        FROM " . DB_PREFIX . "invoicelog
                                                        WHERE user_id = '" . $reminder['user_id'] . "'
                                                            AND invoiceid = '" . $reminder['invoiceid'] . "'
                                                        ORDER BY invoicelogid DESC
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($logs) == 0)
                                                {
                                                        $ilance->db->query("
                                                                 INSERT INTO " . DB_PREFIX . "invoicelog
                                                                 (invoicelogid, user_id, invoiceid, invoicetype, date_sent, date_remind)
                                                                 VALUES(
                                                                 NULL,
                                                                 '" . $res_user['user_id'] . "',
                                                                 '" . $reminder['invoiceid'] . "',
                                                                 '" . $reminder['invoicetype'] . "',
                                                                 '" . DATETODAY . "',
                                                                 '" . $remindfrequency . "')
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // email user
                                                        $ilance->email->mail = $res_user['email'];
                                                        $ilance->email->slng = fetch_user_slng($res_user['user_id']);
                                                            
                                                        $ilance->email->get('cron_expired_subscription_invoice_reminder');		
                                                        $ilance->email->set(array(
                                                                '{{username}}' => $res_user['username'],
                                                                '{{firstname}}' => $res_user['first_name'],
                                                                '{{description}}' => $reminder['description'],
                                                                '{{transactionid}}' => $reminder['transactionid'],
                                                                '{{amount}}' => $ilance->currency->format($reminder['amount'], $reminder['currency_id']),
                                                                '{{total}}' => $ilance->currency->format($reminder['totalamount'], $reminder['currency_id']),
                                                                '{{paid}}' => $ilance->currency->format($reminder['paid'], $reminder['currency_id']),
                                                                '{{duedate}}' => $reminder['duedate'],
                                                                '{{invoiceid}}' => $reminder['invoiceid'],
                                                                '{{reminddate}}' => $remindfrequency,
                                                        ));
                                                        
                                                        if ($ilconfig['invoicesystem_unpaidreminders'])
                                                        {
                                                                // Murugan Changes On Mar 2 for Disable Email
																//$ilance->email->send();
                                                                $count++;
                                                        }                                                        
                                                }
                                                else if ($ilance->db->num_rows($logs) > 0)
                                                {
                                                        // it appears we have a log for this invoice id ..
                                                        $reslogs = $ilance->db->fetch_array($logs, DB_ASSOC);
                                                        
                                                        // time to send an update to this user for this invoice
                                                        // make sure we didn't already send one today
                                                        if ($reslogs['date_remind'] == DATETODAY AND $reslogs['date_sent'] == DATETODAY)
                                                        {
                                                                // we've sent a reminder to this user for this invoice today already.. do nothing until next reminder frequency        
                                                        }
                                                        else if ($reslogs['date_remind'] == DATETODAY AND $reslogs['date_sent'] != DATETODAY)
                                                        {
                                                                // time to send a new frequency reminder.. update table with new email sent date as today
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoicelog
                                                                        SET date_sent = '" . DATETODAY . "',
                                                                        date_remind = '" . $remindfrequency . "'
                                                                        WHERE invoiceid = '" . $reminder['invoiceid'] . "'
                                                                                AND user_id = '" . $reminder['user_id'] . "'
                                                                ");
                                                                
                                                                // email user
                                                                $ilance->email->mail = $res_user['email'];
                                                                $ilance->email->slng = fetch_user_slng($res_user['user_id']);
                                                                    
                                                                $ilance->email->get('cron_expired_subscription_invoice_reminder');		
                                                                $ilance->email->set(array(
                                                                        '{{username}}' => $res_user['username'],
                                                                        '{{firstname}}' => $res_user['first_name'],
                                                                        '{{description}}' => $reminder['description'],
                                                                        '{{transactionid}}' => $reminder['transactionid'],
                                                                        '{{amount}}' => $ilance->currency->format($reminder['amount'], $reminder['currency_id']),
                                                                        '{{total}}' => $ilance->currency->format($reminder['totalamount'], $reminder['currency_id']),
                                                                        '{{paid}}' => $ilance->currency->format($reminder['paid'], $reminder['currency_id']),
                                                                        '{{duedate}}' => $reminder['duedate'],
                                                                        '{{invoiceid}}' => $reminder['invoiceid'],
                                                                        '{{reminddate}}' => $remindfrequency,
                                                                ));
                                                                
                                                                if ($ilconfig['invoicesystem_unpaidreminders'])
                                                                {
                                                                        // Murugan Changes On Mar 2 for Disable Email
																		//$ilance->email->send();
                                                                        $count++;
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                }
                
                return $cronlog;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>