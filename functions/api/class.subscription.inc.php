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
* Subscription class to perform the majority of subscription functionality in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class subscription
{
        /**
        * Function for processing a subscription plan payment from a previously generated unpaid subscription transaction.
        *
        * @param       inetger      user id
        * @param       integer      invoice id
        * @param       string       method of payment (ipn or account)
        * @param       string       name of gateway which will be processing this payment (optional)
        * @param       string       gateway transaction id (from gateway provider) (optional)
        * @param       boolean      silent mode (return only true or false; default false)
        *
        * @return      mixed        for ipn processing, boolean is used, others will use a print_notice() function to end user.
        */
        function payment($userid = 0, $invoiceid = 0, $method = 'account', $gateway = '', $gatewaytxn = '', $silentmode = false)
        {
                global $ilance, $myapi, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                
                // #### INSTANT PAYMENT NOTIFICATION ###########################
                if ($method == 'ipn')
                {
                        $sql = $ilance->db->query("
                            SELECT *
                            FROM " . DB_PREFIX . "invoices
                            WHERE invoiceid = '" . intval($invoiceid) . "'
                                AND status = 'unpaid'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "invoices
                                        SET paid = '" . $res['totalamount'] . "',
                                        status = 'paid',
                                        paiddate = '" . DATETIME24H . "',
                                        referer = '" . $ilance->db->escape_string(REFERRER) . "',
                                        custommessage = '" . $ilance->db->escape_string($gatewaytxn) . "'
                                        WHERE user_id = '" . $res['user_id'] . "'
                                            AND invoiceid = '" . intval($res['invoiceid']) . "'
                                ", 0, null, __FILE__, __LINE__);
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "subscription_user
                                        SET paymethod = '" . $ilance->db->escape_string($gateway) . "'
                                        WHERE user_id = '" . $res['user_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                
                                // track income spent
                                insert_income_spent($res['user_id'], sprintf("%01.2f", $res['totalamount']), 'credit');
                                
                                // #### REFERRAL SYSTEM TRACKER ################################
                                update_referral_action('subscription', $res['user_id']);
                                    
                                // generate subscription renew date
                                $sql_subscription_plan = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "subscription 
                                        WHERE subscriptionid = '" . $res['subscriptionid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_subscription_plan) > 0)
                                {
                                        $subscription_plan_result = $ilance->db->fetch_array($sql_subscription_plan);
                                        $subscription_plan_cost = number_format($subscription_plan_result['cost'], 2);
                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                        $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                        
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_user
                                                SET active = 'yes',
                                                renewdate = '" . $subscription_renew_date . "',
                                                startdate = '" . DATETIME24H . "',
                                                autopayment = '1',
                                                subscriptionid = '" . $res['subscriptionid'] . "',
                                                migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                invoiceid = '" . intval($res['invoiceid']) . "'
                                                WHERE user_id = '" . $res['user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $ilance->email = construct_dm_object('email', $ilance);
                                        
                                        $existing = array(
                                                '{{provider}}' => fetch_user('username', $res['user_id']),
                                                '{{invoice_id}}' => $res['invoiceid'],
                                                '{{invoice_amount}}' => $ilance->currency->format($res['totalamount']),
                                        );
                                        
                                        // email user
                                        $ilance->email->mail = fetch_user('email', $res['user_id']);
                                        $ilance->email->slng = fetch_user_slng($res['user_id']);
                                        
                                        $ilance->email->get('subscription_fee_paid_creditcard');		
                                        $ilance->email->set($existing);
                                        
                                        $ilance->email->send();
                        
                                        // email admin
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        
                                        $ilance->email->get('subscription_fee_paid_creditcard_admin');		
                                        $ilance->email->set($existing);
                                        
                                        $ilance->email->send();
                                        
                                        return true;                    
                                }
                        }
                        
                        return false;
                }
                
                // #### PAYMENT METHOD VIA ACCOUNT BALANCE #####################
                else if ($method == 'account')
                {
                        $sql = $ilance->db->query("
                            SELECT *
                            FROM " . DB_PREFIX . "invoices
                            WHERE invoiceid = '" . intval($invoiceid) . "'
                                AND user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) == 0)
                        {
                                if ($silentmode)
                                {
                                        return false;        
                                }
                                
                                $area_title = $phrase['_invoice_payment_menu_denied_subscription_payment_does_not_belong_to_user'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu_denied_subscription_payment'];
                                
                                print_notice($phrase['_invoice_error'], $phrase['_were_sorry_this_invoice_does_not_exist']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['accounting'], $phrase['_my_account']);
                                exit();
                        }
                        
                        $res_invoiceprice = $ilance->db->fetch_array($sql, DB_ASSOC);
                        
                        $totalamount = (($res_invoiceprice['istaxable'] > 0 AND $res_invoiceprice['totalamount'] > 0)
                                ? $res_invoiceprice['totalamount']
                                : $res_invoiceprice['amount']);
        
                        $sel_balance = $ilance->db->query("
                                SELECT available_balance, total_balance
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $res_balance = $ilance->db->fetch_array($sel_balance, DB_ASSOC);
                        if ($res_balance['available_balance'] < $totalamount)
                        {
                                if ($silentmode)
                                {
                                        return false;        
                                }
                                
                                $area_title = $phrase['_no_funds_available_in_online_account'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_no_funds_available_in_online_account'];
                                
                                print_notice($phrase['_invoice_payment_warning_insufficient_funds'], $phrase['_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['accounting'], $phrase['_my_account']);
                                exit();
                        }
                        
                        // pay the subscription fee invoice
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "invoices
                                SET paid = '" . $totalamount . "',
                                status = 'paid',
                                paiddate = '" . DATETIME24H . "',
                                paymethod = 'account',
                                referer = '" . $ilance->db->escape_string(REFERRER) . "'
                                WHERE user_id = '" . intval($userid) . "'
                                    AND invoiceid = '" . intval($invoiceid) . "'
                        ", 0, null, __FILE__, __LINE__);
    
                        // track income spent and reported
                        insert_income_spent(intval($userid), $totalamount, 'credit');
    
                        // #### REFERRAL SYSTEM TRACKER ############################
                        update_referral_action('subscription', intval($userid));
    
                        $paymethod = 'account';
    
                        // set member session to active
                        $_SESSION['ilancedata']['user']['active'] = 'yes';
    
                        $sql_subscription_plan = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . $res_invoiceprice['subscriptionid'] . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_subscription_plan) > 0)
                        {
                                $subscription_plan_result = $ilance->db->fetch_array($sql_subscription_plan, DB_ASSOC);
                                $subscription_plan_cost = number_format($subscription_plan_result['cost'], 2);
                                $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "subscription_user
                                        SET paymethod = 'account',
                                        startdate = '" . DATETIME24H . "',
                                        renewdate = '" . $subscription_renew_date . "',
                                        autopayment = '1',
                                        active = 'yes',
                                        subscriptionid = '" . intval($res_invoiceprice['subscriptionid']) . "',
                                        migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                        migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                        invoiceid = '" . intval($invoiceid) . "'
                                        WHERE user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                        }
    
                        $new_total = ($res_balance['total_balance'] - $totalamount);
                        $new_avail = ($res_balance['available_balance'] - $totalamount);
    
                        // update account minus subscription fee amount
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET available_balance = '" . sprintf("%01.2f", $new_avail) . "',
                                total_balance = '" . sprintf("%01.2f", $new_total) . "'
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
                        $ilance->email = construct_dm_object('email', $ilance);
                        
                        $existing = array(
                                '{{provider}}' => fetch_user('username', intval($userid)),
                                '{{invoice_id}}' => intval($invoiceid),
                                '{{invoice_amount}}' => $ilance->currency->format($totalamount, $res_invoiceprice['currency_id']),
                        );
                        
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                    
                        // email admin
                        $ilance->email->get('subscription_paid_online_account_admin');		
                        $ilance->email->set($existing);
                        
                        if ($silentmode == false)
                        {
                                $ilance->email->send();
                        }
    
                        // email user
                        $ilance->email->mail = fetch_user('email', intval($userid));
                        $ilance->email->slng = fetch_user_slng(intval($userid));
                        
                        $ilance->email->get('subscription_paid_online_account');		
                        $ilance->email->set($existing);
                        
                        if ($silentmode == false)
                        {
                                $ilance->email->send();
                        }
    
                        if ($silentmode)
                        {
                                return true;
                        }
    
                        $area_title = $phrase['_subscription_payment_via_online_account_complete'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_subscription_payment_via_online_account_complete'];
                        
                        print_notice($phrase['_invoice_payment_complete'], $phrase['_your_invoice_has_been_paid_in_full'], $ilpage['accounting'], $phrase['_my_account']);
                        exit();
                }
        }
        
        /**
        * Function used to obtain the time left of a subscription.
        *
        * @param       integer        countdown
        *
        * @return      string         Returns time left
        */
        function subscription_countdown_timeleft($countdown)
        {
                global $phrase;
                
                $dif = $countdown;
                $ndays = floor($dif / 86400);
                $dif -= $ndays * 86400;
                $nhours = floor($dif / 3600);
                $dif -= $nhours * 3600;
                $nminutes = floor($dif / 60);
                $dif -= $nminutes * 60;
                $nseconds = $dif;
                
                $sign = '+';
                if ($countdown < 0) 
                {
                        $countdown = - $countdown;
                        $sign = '-';
                }
                
                if ($sign == '-') 
                {
                        $subscription_time_left = $phrase['_subscription_expired'];
                }
                else 
                {
                        if ($ndays != '0') 
                        {
                                $subscription_time_left = $ndays . $phrase['_d_shortform'] . ', ';	
                                $subscription_time_left .= $nhours . $phrase['_h_shortform'] . '+ ';
                                $subscription_time_left .= $nminutes . $phrase['_m_shortform'] . '+ ';
                                $subscription_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
                        }
                        elseif ($nhours != '0') 
                        {
                                $subscription_time_left = $nhours . $phrase['_h_shortform'] . ', ';
                                $subscription_time_left .= $nminutes . $phrase['_m_shortform'] . '+ ';
                                $subscription_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
                        }
                        else
                        {
                                $subscription_time_left = $nminutes . $phrase['_m_shortform'] . ', ';
                                $subscription_time_left .= $nseconds . $phrase['_s_shortform'] . '+';
                        }
                }
                $subscription_countdown = $subscription_time_left;
                return $subscription_countdown;
        }
            
        /**
        * Function used to obtain the subscription length (in days) from a supplied unit (D/M/Y) and length (in days)
        *
        * @param       string         unit (D or M or Y)
        * @param       integer        length in days
        *
        * @return      string         Returns time left
        */
        function subscription_length($units, $length)
        {
                $days = ($length < 1 ? 1 : $length);        
                switch ($units)
                {
                        case 'Y':
                        {
                            $value = 365 * intval($days);
                            break;
                        }                
                        case 'M':
                        {
                            $value = 30 * intval($days);
                            break;
                        }
                        case 'D':
                        {
                                $value = intval($days);
                                break;
                        }
                }
                
                return $value;
        }
        
        /**
        * Function used to check a user's access when trying to access a certain marketplace resource or area.
        *
        * @param       integer        user id
        * @param       string         access name
        *
        * @return      bool           Returns true or false if boolean setting or will return
        *                             the actual "value" if other (ie: bid limit per day might return 10)..
        */
        function check_access($userid = 0, $accessname = '')
        {
                global $ilance, $myapi;
                
                $value = 'no';
                $userid = isset($userid) ? intval($userid) : 0;
                
                if ($userid > 0 AND !empty($accessname))
                {
                        $sql = $ilance->db->query("
                                SELECT user.subscriptionid, user.user_id, sub.subscriptiongroupid, perm.value
                                FROM " . DB_PREFIX . "subscription_user user
                                LEFT JOIN " . DB_PREFIX . "subscription sub ON (sub.subscriptionid = user.subscriptionid)
                                LEFT JOIN " . DB_PREFIX . "subscription_permissions perm ON (perm.subscriptiongroupid = sub.subscriptiongroupid)
                                WHERE user.user_id = '" . intval($userid) . "'
                                        AND sub.active = 'yes'
                                        AND user.active = 'yes'
                                        AND perm.subscriptiongroupid = sub.subscriptiongroupid
                                        AND perm.accessname = '" . $ilance->db->escape_string($accessname) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                
                                // does admin force a permission exemption?
                                $sql2 = $ilance->db->query("
                                        SELECT value
                                        FROM " . DB_PREFIX . "subscription_user_exempt
                                        WHERE user_id = '" . intval($userid) . "' 
                                            AND accessname = '" . $ilance->db->escape_string($accessname) . "'
                                            AND active = '1'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) > 0)
                                {
                                        $res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
                                        
                                        if ($accessname == 'bidlimitperday')
                                        {
                                                // allows admin to offer bidder extra bids on a per (day/month) basis
                                                $value = ($res['value'] + $res2['value']);
                                        }
                                        else
                                        {
                                                $value = $res2['value'];
                                        }
                                }
                                
                                // if there is no exemption for this user fpr this permission resource
                                else
                                {
                                        $value = $res['value'];
                                }         
                        }
                }
                
                return $value;
        }
        
        /**
        * Function to display any subscription alerts from their my account area
        *
        * @param       integer        user id
        *
        * @return      string         Returns HTML formatted text
        */
        function alerts($userid = 0)
        {
                global $ilance, $myapi, $phrase, $iltemplate, $page_title, $area_title, $ilconfig, $ilpage, $SCRIPT_URL;
        
                $sql = $ilance->db->query("
                        SELECT active, cancelled
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if ($res['cancelled'])
                        {
                                $html = $phrase['_you_have_cancelled_your_subscription_plan_your_subscription_plan_will_remain_active_until_the_expiration_date_your_account_will_not_be_billed'];
                        }
                        else
                        {
                                if ($res['active'] == 'no')
                                {
                                        $html = $phrase['_please_optin_to_a_valid_subscription_plan_to_enable_access_permissions_to_your_online_account_failing_to_optin_to_a_subscription_plan_will_not_allow_you_to_participate'].' <a href="' . HTTP_SERVER . $ilpage['subscription'].'">'.$phrase['_click_here_to_upgrade_your_subscription'].'</a>.';
                                }
                                else
                                {
                                        $html = $phrase['_your_subscription_plan_is_active'].'  <a href="' . HTTP_SERVER . $ilpage['subscription'].'">'.$phrase['_click_here_to_view_other_subscription_plans'].'</a>.';
                                }
                        }
                }
                else
                {
                        $html = $phrase['_the_subscription_plan_system_is_currently_under_maintenance_and_will_be_available_shortly_thank_you_for_your_continued_patience'];
                }
                
                return $html;
        }
        
        /**
        * Function to display subscription plans within a pulldown menu element
        *
        * @return      string         Returns HTML pulldown menu element
        */
        function plans_pulldown()
        {
                global $ilance, $myapi;
                
                $html = '<select name="subscriptionid" style="font-family: verdana">';            
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "subscription
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $html .= '<option value="' . $res['subscriptionid'] . '">' . stripslashes($res['title']) . ' (' . $res['length'] . print_unit($res['units']) . ' - ' . $ilance->currency->format($res['cost']) . ')</option>';
                        }
                }            
                $html .= '</select>';
            
                return $html;
        }
            
        /**
        * Function to display for users any subscription plans within a pulldown menu element
        *
        * @return      string         Returns HTML pulldown menu element
        */
        function pulldown()
        {
                global $ilance, $myapi, $phrase;        
                $html = '<select name="subscriptionid" style="font-family: verdana">';
                $html .= '<optgroup label="'.$phrase['_please_select'].'">';
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "subscription
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html .= '<option value="0">'.$phrase['_all_subscribers'].'</option>';                        
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $html .= '<option value="'.$res['subscriptionid'].'">'.stripslashes($res['title']).'</option>';
                        }
                }
                $html .= '</optgroup>';
                $html .= '</select>';
            
                return $html;
        }
            
        /**
        * Function to display any subscription alerts from their my account area
        *
        * @param       integer        user id
        *
        * @return      string         Returns the subscription plan as requested
        */
        function fetch_subscription_plan($userid = 0)
        {
                global $ilance, $myapi, $phrase;                
                $sql = $ilance->db->query("
                        SELECT subscriptionid
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        
                        $sql2 = $ilance->db->query("
                                SELECT title
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '".$res['subscriptionid']."'
                        ", 0, null, __FILE__, __LINE__);                
                        $res2 = $ilance->db->fetch_array($sql2);
                        
                        return stripslashes($res2['title']);
                }
                else
                {
                    return $phrase['_no_plan'];
                }
        }
            
        /**
        * Function to display any subscription plan exemptions within a pulldown menu element.
        *
        * @return      string         Returns the subscription exemptions as requested
        */
        function exemptions_pulldown()
        {
                global $ilance, $myapi;            
                $html = '<select name="accessname" style="font-family: verdana">';            
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "subscription_permissions
                        GROUP BY accessname
                        ORDER BY accessname ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $html .= '<option value="'.$res['accessname'].'">'.$res['accessname'].' - '.stripslashes($res['accesstext_eng']).' ('.$res['accesstype'].')</option>';
                        }
                }            
                $html .= '</select>';        
                return $html;
        }
            
        /**
        * Function to handle the subscription exemption upgrade process for end users.
        *
        * @param       integer        user id
        * @param       string         access permission name
        * @param       string         access permission value
        * @param       integer        cost for this exemption
        * @param       integer        days this exemption shall last for
        * @param       string         logic to use for determining what to do
        * @param       string         end user comments
        * @param       boolean        defines if this function should dispatch email once it's finished
        *
        * @return      string         Returns the subscription exemptions as requested
        */
        function construct_subscription_exemption($userid = 0, $accessname = '', $accessvalue = '', $cost = 0, $days = 0, $logic = '', $comments = '', $doemail = '')
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $userid = isset($userid) ? intval($userid) : '';
                $accessname = isset($accessname) ? $accessname : '';
                $accessvalue = isset($accessvalue) ? $accessvalue : '';
                $cost = isset($cost) ? $cost : 0;
                $days = isset($days) ? $days : 7;
                $exemptfrom = DATETIME24H;
                $exemptto = $ilance->datetime->fetch_date_fromnow($days) . ' ' . TIMENOW;
                $nofunds = 0;
    
                if ($userid == '')
                {
                        return 0;
                }
                if ($accessname == '')
                {
                        return 0;
                }
                if ($accessvalue == '')
                {
                        return 0;
                }
                
                if (isset($logic))
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "subscription_user_exempt
                                WHERE user_id = '" . intval($userid) . "'
                                    AND accessname = '".$ilance->db->escape_string($accessname)."'
                                    AND active = '1'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) == 0)
                        {
                                switch ($logic)
                                {
                                        case 'active':
                                        {
                                                // insert permission and waive transaction fee for cost amount
                                                $ilance->accounting = construct_object('api.accounting');
                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                0,
                                                0,
                                                0,
                                                $userid,
                                                0,
                                                0,
                                                0,
                                                'Subscription Permission Exemption: '.$accessname.' (From: '.$exemptfrom.' To: '.$exemptto.')',
                                                sprintf("%01.2f", 0),
                                                sprintf("%01.2f", 0),
                                                'paid',
                                                'debit',
                                                'account',
                                                DATETIME24H,
                                                DATEINVOICEDUE,
                                                DATETIME24H,
                                                $comments,
                                                0,
                                                0,
                                                1);
                                                break;
                                        }
                                        case 'activepaid':
                                        {
                                                // insert permission and insert new paid transaction for cost amount
                                                $ilance->accounting = construct_object('api.accounting');
                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                0,
                                                0,
                                                0,
                                                $userid,
                                                0,
                                                0,
                                                0,
                                                'Subscription Permission Exemption: '.$accessname.' (From: '.$exemptfrom.' To: '.$exemptto.')',
                                                sprintf("%01.2f", $cost),
                                                sprintf("%01.2f", $cost),
                                                'paid',
                                                'debit',
                                                'account',
                                                DATETIME24H,
                                                DATEINVOICEDUE,
                                                DATETIME24H,
                                                $comments,
                                                0,
                                                0,
                                                1);
                                                break;
                                        }                                        
                                        case 'activedebit':
                                        {
                                                // attempt to debit customers account for payment for permissions
                                                $sql = $ilance->db->query("
                                                        SELECT available_balance, total_balance
                                                        FROM " . DB_PREFIX . "users
                                                        WHERE user_id = '".$userid."'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql) > 0)
                                                {
                                                        $res = $ilance->db->fetch_array($sql);
                                                        if ($cost <= $res['available_balance'])
                                                        {
                                                                // customer has sufficient funds
                                                                $ilance->accounting = construct_object('api.accounting');
                                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                                0,
                                                                0,
                                                                0,
                                                                $userid,
                                                                0,
                                                                0,
                                                                0,
                                                                'Subscription Permission Exemption: '.$accessname.' (From: '.$exemptfrom.' To: '.$exemptto.')',
                                                                sprintf("%01.2f", $cost),
                                                                sprintf("%01.2f", $cost),
                                                                'paid',
                                                                'debit',
                                                                'account',
                                                                DATETIME24H,
                                                                DATEINVOICEDUE,
                                                                DATETIME24H,
                                                                $comments,
                                                                0,
                                                                0,
                                                                1);
                                                            
                                                                // debit amount from online account
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET available_balance = available_balance - $cost,
                                                                        total_balance = total_balance - $cost
                                                                        WHERE user_id = '" . intval($userid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        else 
                                                        {
                                                                $nofunds = 1;
                                                        }
                                                }
                                                break;
                                        }
                                }
                                
                                if ($nofunds == 0)
                                {
                                        // create new exemption
                                        $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "subscription_user_exempt
                                        (user_id, accessname, value, exemptfrom, exemptto, comments, invoiceid, active) 
                                        VALUES (
                                        '" . intval($userid) . "',
                                        '" . $accessname . "',
                                        '" . $accessvalue . "',
                                        '" . $exemptfrom . "',
                                        '" . $exemptto . "',
                                        '" . $comments . "',
                                        '" . intval($invoiceid) . "',
                                        '1')", 0, null, __FILE__, __LINE__);
                                        
                                        return 1;
                                }
                                else 
                                {
                                        return 0;	
                                }
                        }
                        else
                        {
                                return 0;
                        }
                }
                else 
                {
                        return 0;	
                }
        }
        
        /**
        * Function to handle the subscription upgrade process for end users.
        *
        * @param       integer        user id
        * @param       integer        subscription id
        * @param       boolean        end user agreement of terms value (true / false)
        * @param       boolean        end user instant payment value (true / false)
        * @param       boolean        defines if the subscription cost is zero or not
        * @param       boolean        defines if the transaction will be using the recurring subscription logic
        * @param       string         payment method chosen by the end user
        * @param       boolean        defines if this transaction is a recurring subscription modification or not
        * @param       boolean        defines if this function should automatically delete any previous free or paid subscription transactions to reduce the amount of pending invoices in the admincp
        * @param       string         return url (optional)
        *
        * @return      string         Returns the subscription exemptions as requested
        */
        function subscription_upgrade_process($userid = 0, $subscriptionid = 0, $agreecheck = 0, $instantpay = 0, $nocost = 0, $recurring = 0, $paymethod = '', $ismodify = 0, $removepending = false, $returnurl = '')
        {
                global $ilance, $myapi, $form, $phrase, $iltemplate, $page_title, $area_title, $ilconfig, $ilpage, $show, $hidden_form_start, $hidden_form_end, $cardtype_pulldown, $ilcrumbs, $navcrumb;
                
                $ilance->accounting = construct_object('api.accounting');
                
                // #### REMOVE ANY PENDING SUBSCRIPTION TRANSACTIONS ###########
                if (isset($removepending) AND $removepending)
                {
                        // removing latest pending transactions before we process the new one
                        $sql = $ilance->db->query("
                                SELECT subscriptionid, transactionid, amount
                                FROM " . DB_PREFIX . "invoices
                                WHERE subscriptionid > 0
                                        AND user_id = '" . intval($userid) . "'
                                        AND (status = 'scheduled' OR status = 'pending')
                                ORDER BY createdate DESC
                                LIMIT 1
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                
                                // old transaction exists! let's remove it!
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "invoices
                                        WHERE user_id = '" . intval($userid) . "'
                                                AND transactionid = '" . $res['transactionid'] . "'
                                                AND invoicetype = 'subscription'
                                        LIMIT 1
                                ");
                        }
                        unset($sql, $res);
                }
                
                // #### FREE SUBSCRIPTION PLAN #################################
                if (isset($nocost) AND $nocost)
                {
                        $inv_due_date = date('Y-m-d', mktime(0, 0, 0, gmdate('m', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),gmdate('d',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))+15,gmdate('Y',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))));
                        $inv_due_time = gmdate('H:i:s', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']));
                        $invoice_due_date = $inv_due_date . ' ' . $inv_due_time;
            
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . intval($subscriptionid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $subscription_plan_result = $ilance->db->fetch_array($sql, DB_ASSOC);
                                $subscription_plan_cost = $subscription_plan_result['cost'];
                                if ($subscription_plan_cost == 0)
                                {
                                        // customer agree to site terms?
                                        if (isset($agreecheck) AND $agreecheck)
                                        {
                                                $area_title = $phrase['_subscription_upgrade_via_online_account_process'];
                                                $page_title = SITE_NAME . ' - ' . $phrase['_subscription_upgrade_via_online_account_process'];
                        
                                                $subscription_invoice_id = $ilance->accounting->insert_transaction(
                                                        intval($subscriptionid),
                                                        0,
                                                        0,
                                                        intval($userid),
                                                        0,
                                                        0,
                                                        0,
                                                        $phrase['_subscription_payment_for'] . ' ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
                                                        sprintf("%01.2f", $subscription_plan_cost),
                                                        sprintf("%01.2f", $subscription_plan_cost),
                                                        'paid',
                                                        'subscription',
                                                        'account',
                                                        DATETIME24H,
                                                        $invoice_due_date,
                                                        DATETIME24H,
                                                        '',
                                                        0,
                                                        0,
                                                        1
                                                );
                        
                                                $subscription_item_name = $phrase['_subscription_payment_for'] . ' ' . stripslashes($subscription_plan_result['title']) . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')';
                                                $subscription_item_cost = sprintf("%01.2f", $subscription_plan_cost);
                                                $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                                $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                                
                                                $sqlcheck = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "subscription_user
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sqlcheck) > 0)
                                                {
                                                        // set subscription to active
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "subscription_user
                                                                SET active = 'yes',
                                                                renewdate = '" . $subscription_renew_date . "',
                                                                startdate = '" . DATETIME24H . "',
                                                                subscriptionid = '" . intval($subscriptionid) . "',
                                                                migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                                migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                                invoiceid = '" . $subscription_invoice_id . "',
                                                                cancelled = '0'
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                else
                                                {
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "subscription_user
                                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, cancelled, migrateto, migratelogic, invoiceid)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($subscriptionid) . "',
                                                                '" . intval($userid) . "',
                                                                'account',
                                                                '" . DATETIME24H . "',
                                                                '" . $subscription_renew_date . "',
                                                                '1',
                                                                'yes',
                                                                '0',
                                                                '" . $subscription_plan_result['migrateto'] . "',
                                                                '" . $subscription_plan_result['migratelogic'] . "',
                                                                '" . $subscription_invoice_id . "')
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                
                                                // #### update subscription for user
                                                $_SESSION['ilancedata']['user']['subscriptionid'] = intval($subscriptionid);
                                                if (!empty($_SESSION['ilancedata']['user']['active']) AND $_SESSION['ilancedata']['user']['active'] == 'no')
                                                {
                                                        $_SESSION['ilancedata']['user']['active'] = 'yes';
                                                }
                                                
                                                $ilance->email = construct_dm_object('email', $ilance);
                
                                                $ilance->email->mail = fetch_user('email', intval($userid));
                                                $ilance->email->slng = fetch_user_slng(intval($userid));
                                                
                                                $ilance->email->get('subscription_paid_online_account');		
                                                $ilance->email->set(array(
                                                        '{{provider}}' => fetch_user('username', intval($userid)),
                                                        '{{invoice_id}}' => $subscription_invoice_id,
                                                        '{{invoice_amount}}' => $ilance->currency->format($subscription_item_cost),
                                                ));
                                                
                                                $ilance->email->send();
                                                
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                
                                                $ilance->email->get('subscription_paid_online_account_admin');		
                                                $ilance->email->set(array(
                                                        '{{provider}}' => fetch_user('username', intval($userid)),
                                                        '{{invoice_id}}' => $subscription_invoice_id,
                                                        '{{invoice_amount}}' => $ilance->currency->format($subscription_item_cost),
                                                ));
                                                
                                                $ilance->email->send();
                        
                                                $area_title = $phrase['_subscription_upgrade_via_online_account_process_complete'];
                                                $page_title = SITE_NAME . ' - ' . $phrase['_subscription_upgrade_via_online_account_process_complete'];
                                                
                                                $url = !empty($returnurl) ? urldecode($returnurl) : HTTPS_SERVER . $ilpage['accounting'];
                                                $title = !empty($returnurl) ? $phrase['_return_to_the_previous_page'] : $phrase['_my_account'];
                                                
                                                print_notice($phrase['_invoice_payment_complete'], $phrase['_your_invoice_has_been_paid_in_full'], $url, $title);
                                                exit();
                                        }
                                        else
                                        {
                                                $page_title = $phrase['_subscription_denied_customer_did_not_agree_with_terms'];
                                                $area_name = SITE_NAME . ' - ' . $phrase['_subscription_denied_customer_did_not_agree_with_terms'];
                                                
                                                print_notice($phrase['_access_denied'], $phrase['_subscription_denied_customer_did_not_agree_with_terms'], 'javascript:history.back(1);', $phrase['_back']);
                                                exit();                                                
                                        }
                                }
                                else
                                {
                                        $page_title = $phrase['_subscription_denied_invalid_subscription_information'];
                                        $area_name = SITE_NAME . ' - ' . $phrase['_subscription_denied_invalid_subscription_information'];
                                        
                                        print_notice($phrase['_access_denied'], $phrase['_subscription_denied_invalid_subscription_information'], 'javascript:history.back(1);', $phrase['_back']);
                                        exit();
                                }
                        }
                        else
                        {
                                $page_title = $phrase['_subscription_denied_invalid_subscription_information'];
                                $area_name = SITE_NAME . ' - ' . $phrase['_subscription_denied_invalid_subscription_information'];
                                
                                print_notice($phrase['_access_denied'], $phrase['_subscription_denied_invalid_subscription_information'], 'javascript:history.back(1);', $phrase['_back']);
                                exit();
                        }
                }
                
                // #### PAID SUBSCRIPTION PLAN #################################
                else
                {
                        // #### RECURRING SUBSCRIPTION LOGIC ###################
                        if ($recurring)
                        {
                                $navcrumb = array();
                                $navcrumb["$ilpage[subscription]"] = $phrase['_subscription'];
                                $navcrumb[""] = $phrase['_preview'];
                                
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "subscription
                                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                        
                                        ($apihook = $ilance->api('recurring_subscription_logic_start')) ? eval($apihook) : false;  
                                        
                                        if (isset($paymethod))
                                        {
                                                $customencrypted = 'RECURRINGSUBSCRIPTION|' . intval($userid) . '|0|0|' . $res['length'] . '|' . $res['units'] . '|' . intval($subscriptionid) . '|' . $res['cost'] . '|' . $res['roleid'];
                                                
                                                switch ($paymethod)
                                                {
                                                        // #### PAYPAL RECURRING SERVICE ###################
                                                        case 'paypal':
                                                        {
                                                                $ilance->paypal = construct_object('api.paypal');
                                                                
                                                                $hidden_form_start = $ilance->paypal->print_recurring_payment_form($ilconfig['paypal_business_email'], $subscriptionid, $res['cost'], $res['units'], $res['length'], $phrase['_recurring_subscription'], $phrase['_recurring_subscription'], $ilconfig['paypal_master_currency'], $customencrypted, $onsubmit = 'return validate_pp_email(this);', $ismodify);
                                                                $hidden_form_end = '</form>';
                                                                
                                                                $pprint_array = array('hidden_form_start','hidden_form_end','form','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                                                                
                                                                $ilance->template->fetch('main', 'subscription_paypal_recurring.html');
                                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                                $ilance->template->parse_if_blocks('main');
                                                                $ilance->template->pprint('main', $pprint_array);
                                                                exit();
                                                                break;
                                                        }
                                                    
                                                        // #### STORMPAY RECURRING SERVICE #################
                                                        case 'stormpay':
                                                        {
                                                                $ilance->stormpay = construct_object('api.stormpay');
                                                                
                                                                $hidden_form_start = $ilance->stormpay->print_recurring_payment_form($ilconfig['stormpay_business_email'], $res['cost'], $res['units'], $res['length'], $phrase['_recurring_subscription'], $phrase['_recurring_subscription'], $ilconfig['stormpay_master_currency'], $customencrypted, $onsubmit = 'return validate_sp_email(this);');
                                                                $hidden_form_end = '</form>';
                                                                
                                                                $pprint_array = array('hidden_form_start','hidden_form_end','form','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                                                                
                                                                $ilance->template->fetch('main', 'subscription_stormpay_recurring.html');
                                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                                $ilance->template->parse_if_blocks('main');
                                                                $ilance->template->pprint('main', $pprint_array);
                                                                exit();
                                                                break;
                                                        }
                                                        // #### MONEYBOOKERS RECURRING SERVICE #################
                                                        case 'moneybookers':
                                                        {
                                                                $ilance->moneybookers = construct_object('api.moneybookers');
                                                                
                                                                $hidden_form_start = $ilance->moneybookers->print_recurring_payment_form($ilconfig['moneybookers_business_email'], $res['cost'], $res['units'], $res['length'], $phrase['_recurring_subscription'], $ilconfig['moneybookers_master_currency'], $customencrypted, $onsubmit = 'return validate_mb_email(this);');
                                                                $hidden_form_end = '</form>';
                                                                
                                                                $pprint_array = array('hidden_form_start','hidden_form_end','form','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                                                                
                                                                $ilance->template->fetch('main', 'subscription_moneybookers_recurring.html');
                                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                                $ilance->template->parse_if_blocks('main');
                                                                $ilance->template->pprint('main', $pprint_array);
                                                                exit();
                                                                break;
                                                        }
                                                        
                                                        // #### AUTHORIZE.NET RECURRING SERVICE ###################
                                                        case 'authnet':
                                                        {
                                                                $ilance->authorizenet = construct_object('api.authorizenet');

                                                                $iscancel = 0;
                                                                
                                                                $hidden_form_start = $ilance->authorizenet->print_recurring_payment_form(DATETODAY, $subscriptionid, $res['roleid'], $res['cost'], $totaloccurrences = 9999, $trialamount = 0, $trialoccurrences = 0, $res['units'], $res['length'], $phrase['_recurring_subscription'], $onsubmit = 'return validate_authorizenet_info(this);', $ismodify, $iscancel);
                                                                $hidden_form_end = '</form>';
                                                                $cardtype_pulldown = $ilance->accounting->creditcard_type_pulldown('', 'cardType');
                                                                
                                                                $pprint_array = array('cardtype_pulldown','hidden_form_start','hidden_form_end','form','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                                                                
                                                                $ilance->template->fetch('main', 'subscription_authnet_recurring.html');
                                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                                $ilance->template->parse_if_blocks('main');
                                                                $ilance->template->pprint('main', $pprint_array);
                                                                exit();
                                                                break;
                                                        }
                                                }
                                                
                                                ($apihook = $ilance->api('subscription_upgrade_process_recurring_paymethod')) ? eval($apihook) : false;
                                        }
                                }
                        }
                        
                        // #### REGULAR SUBSCRIPTION UPGRADE LOGIC #############
                        else
                        {
                                $subscription_plan_result = array();
                                
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "subscription
                                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $subscription_plan_result = $ilance->db->fetch_array($sql, DB_ASSOC);
                                        
                                        ($apihook = $ilance->api('regular_subscription_logic_start')) ? eval($apihook) : false;
                                        
                                        $subscription_plan_cost = $subscription_plan_result['cost'];
                                        
                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                        $invoice_due_date = print_subscription_renewal_datetime($subscription_length);
                        
                                        // does customer agree to terms and agreements?
                                        if ($agreecheck)
                                        {
                                                // does customer take advantage of instant payment from online account balance?
                                                if ($instantpay)
                                                {
                                                        $area_title = $phrase['_subscription_upgrade_via_online_account_process'];
                                                        $page_title = SITE_NAME . ' - ' . $phrase['_subscription_upgrade_via_online_account_process'];
                                
                                                        // is user taxable for this invoice type?
                                                        $ilance->tax = construct_object('api.tax');
                                                        if ($ilance->tax->is_taxable(intval($userid), 'subscription'))
                                                        {
                                                                // fetch total amount to hold within the "totalamount" field
                                                                $subscription_plan_cost = $subscription_plan_cost + $ilance->tax->fetch_amount(intval($userid), sprintf("%01.2f", $subscription_plan_cost), 'subscription', 0);
                                                        }
                                                        
                                                        // create scheduled subscription invoice transaction
                                                        $subscription_invoice_id = $ilance->accounting->insert_transaction(
                                                                intval($subscriptionid),
                                                                0,
                                                                0,
                                                                intval($userid),
                                                                0,
                                                                0,
                                                                0,
                                                                $phrase['_subscription_payment_for'] . ' ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
                                                                sprintf("%01.2f", $subscription_plan_result['cost']),
                                                                '',
                                                                'scheduled',
                                                                'subscription',
                                                                'account',
                                                                DATETIME24H,
                                                                $invoice_due_date,
                                                                '',
                                                                '',
                                                                0,
                                                                0,
                                                                1
                                                        );
                                
                                                        $subscription_item_name = $phrase['_subscription_payment_for'] . ' ' . stripslashes($subscription_plan_result['title']) . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')';
                                                        $subscription_item_cost = $subscription_plan_cost;
                                
                                                        $insorupd = $ilance->db->query("
                                                                SELECT *
                                                                FROM " . DB_PREFIX . "subscription_user
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($insorupd) > 0)
                                                        {
                                                                // set payment method to online account and auto payments to active
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "subscription_user
                                                                        SET paymethod = 'account',
                                                                        autopayment = '1',
                                                                        migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                                        migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                                        invoiceid = '" . $subscription_invoice_id . "',
                                                                        cancelled = '0'
                                                                        WHERE user_id = '" . intval($userid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        else
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "subscription_user
                                                                        (id, subscriptionid, user_id, paymethod, autopayment, active, migrateto, migratelogic, invoiceid)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($subscriptionid) . "',
                                                                        '" . intval($userid) . "',
                                                                        'account',
                                                                        '1',
                                                                        'no',
                                                                        '" . $subscription_plan_result['migrateto'] . "',
                                                                        '" . $subscription_plan_result['migratelogic'] . "',
                                                                        '" . $subscription_invoice_id . "')
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                
                                                        // calculate subscription renewal date
                                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                                        $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                                        
                                                        $sqlgetacc = $ilance->db->query("
                                                                SELECT total_balance, available_balance
                                                                FROM " . DB_PREFIX . "users
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($sqlgetacc) > 0)
                                                        {
                                                                $resgetacc = $ilance->db->fetch_array($sqlgetacc, DB_ASSOC);
                                                                if ($resgetacc['available_balance'] >= $subscription_plan_cost)
                                                                {
                                                                        $new_total = sprintf("%01.2f", $resgetacc['total_balance'] - $subscription_plan_cost);
                                                                        $new_avail = sprintf("%01.2f", $resgetacc['available_balance'] - $subscription_plan_cost);
                                                                        
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "users
                                                                                SET available_balance = '" . $new_avail . "',
                                                                                total_balance = '" . $new_total . "'
                                                                                WHERE user_id = '" . intval($userid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                            
                                                                        // update invoice with payment from online account balance
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "invoices
                                                                                SET paid = '" . $subscription_plan_cost . "',
                                                                                status = 'paid',
                                                                                paiddate = '" . DATETIME24H . "'
                                                                                WHERE user_id = '" . intval($userid) . "'
                                                                                    AND invoiceid = '" . intval($subscription_invoice_id) . "'
                                                                                    AND invoicetype = 'subscription'
                                                                                    AND subscriptionid = '" . intval($subscriptionid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                            
                                                                        // track income spent
                                                                        insert_income_spent(intval($userid), sprintf("%01.2f", $subscription_plan_cost), 'credit');

                                                                        $bidtotal = $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday');
                                                                        $bidsleft = ($bidtotal - fetch_bidcount_today($_SESSION['ilancedata']['user']['userid'])) * (-1);
                                                        
                                                                        $ilance->db->query("
                                                                                        UPDATE " . DB_PREFIX . "users
                                                                                        SET bidstoday = '" . $bidsleft . "'
                                                                                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                                                        ", 0, null, __FILE__, __LINE__);
														                
                                                                        // upgrade customers subscription plan
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "subscription_user
                                                                                SET active = 'yes',
                                                                                renewdate = '" . $subscription_renew_date . "',
                                                                                startdate = '" . DATETIME24H . "',
                                                                                subscriptionid = '" . intval($subscriptionid) . "',
                                                                                migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                                                migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                                                invoiceid = '" . $subscription_invoice_id . "',
                                                                                cancelled = '0'
                                                                                WHERE user_id = '" . intval($userid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                            
                                                                        $_SESSION['ilancedata']['user']['subscriptionid'] = intval($subscriptionid);
                                                                        if (!empty($_SESSION['ilancedata']['user']['active']) AND $_SESSION['ilancedata']['user']['active'] == 'no')
                                                                        {
                                                                                $_SESSION['ilancedata']['user']['active'] = 'yes';
                                                                        }
                                            
                                                                        $ilance->email = construct_dm_object('email', $ilance);
                
                                                                        $ilance->email->mail = fetch_user('email', intval($userid));
                                                                        $ilance->email->slng = fetch_user_slng(intval($userid));
                                                                        
                                                                        $ilance->email->get('subscription_paid_online_account');		
                                                                        $ilance->email->set(array(
                                                                                '{{provider}}' => fetch_user('username', intval($userid)),
                                                                                '{{invoice_id}}' => $subscription_invoice_id,
                                                                                '{{invoice_amount}}' => $ilance->currency->format($subscription_item_cost)
                                                                        ));
                                                                        
                                                                        $ilance->email->send();
                                                                        
                                                                        $ilance->email->mail = SITE_EMAIL;
                                                                        $ilance->email->slng = fetch_site_slng();
                                                                        
                                                                        $ilance->email->get('subscription_paid_online_account_admin');		
                                                                        $ilance->email->set(array(
                                                                                '{{provider}}' => fetch_user('username', intval($userid)),
                                                                                '{{invoice_id}}' => $subscription_invoice_id,
                                                                                '{{invoice_amount}}' => $ilance->currency->format($subscription_item_cost)
                                                                        ));
                                                                        
                                                                        $ilance->email->send();
                                            
                                                                        $area_title = $phrase['_subscription_upgrade_via_online_account_process_complete'];
                                                                        $page_title = SITE_NAME . ' - ' . $phrase['_subscription_upgrade_via_online_account_process_complete'];
                                                                        
                                                                        $url = !empty($returnurl) ? urldecode($returnurl) : HTTPS_SERVER . $ilpage['accounting'];
                                                                        $title = !empty($returnurl) ? $phrase['_return_to_the_previous_page'] : $phrase['_my_account'];
                                                                        
                                                                        ($apihook = $ilance->api('regular_subscription_payment_end')) ? eval($apihook) : false;
                                                                        
                                                                        print_notice($phrase['_invoice_payment_complete'], $phrase['_your_invoice_has_been_paid_in_full'], $url, $title);
                                                                        exit();
                                                                }
                                                                else
                                                                {
                                                                        $area_title = $phrase['_subscription_upgrade_via_online_account_process_denied'];
                                                                        $page_title = SITE_NAME . ' - ' . $phrase['_subscription_upgrade_via_online_account_process_denied'];
                                                                        
                                                                        print_notice($phrase['_invoice_payment_warning_insufficient_funds'], $phrase['_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds'] . '<br /><br />' . $phrase['_please_contact_customer_support'], $ilpage['accounting'], $phrase['_my_account']);
                                                                        exit();
                                                                }        
                                                        }

                                                }
                                                else
                                                {
                                                        // no instant payment selected by user
                                                        $area_title = $phrase['_subscription_upgrade_via_online_account_creating_new_invoice'];
                                                        $page_title = SITE_NAME . ' - ' . $phrase['_subscription_upgrade_via_online_account_creating_new_invoice'];
                                
                                                        // create scheduled subscription transaction to be paid
                                                        $subscription_invoice_id = $ilance->accounting->insert_transaction(
                                                                intval($subscriptionid),
                                                                0,
                                                                0,
                                                                intval($userid),
                                                                0,
                                                                0,
                                                                0,
                                                                $phrase['_subscription_payment_for'] . ' ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
                                                                sprintf("%01.2f", $subscription_plan_cost),
                                                                '',
                                                                'scheduled',
                                                                'subscription',
                                                                'account',
                                                                DATETIME24H,
                                                                $invoice_due_date,
                                                                '',
                                                                '',
                                                                0,
                                                                0,
                                                                1
                                                        );
                                                        
                                                        ($apihook = $ilance->api('regular_subscription_payment_unpaid_end')) ? eval($apihook) : false;
                                                        
                                                        refresh(HTTPS_SERVER . $ilpage['invoicepayment'] . '?id=' . $subscription_invoice_id);
                                                        exit();
                                                }
                                        }
                                        else
                                        {
                                                $page_title = $phrase['_subscription_denied_customer_did_not_agree_with_terms'];
                                                $area_name = SITE_NAME . ' - ' . $phrase['_subscription_denied_customer_did_not_agree_with_terms'];
                                                
                                                print_notice($phrase['_access_denied'], $phrase['_subscription_denied_customer_did_not_agree_with_terms'], 'javascript:history.back(1);', $phrase['_back']);
                                                exit();
                                        }
                                }
                                else
                                {
                                        $page_title = $phrase['_subscription_denied_invalid_subscription_information'];
                                        $area_name = SITE_NAME . ' - ' . $phrase['_subscription_denied_invalid_subscription_information'];
                                        
                                        print_notice($phrase['_access_denied'], $phrase['_subscription_denied_invalid_subscription_information'], 'javascript:history.back(1);', $phrase['_back']);
                                        exit();
                                }
                        }
                }
        }
        
        /**
        * Function to update a users subscription plan within the AdminCP
        *
        * @param       integer      user id
        * @param       integer      subscription id
        * @param       string       transaction description
        * @param       string       subscription action
        */
        function subscription_upgrade_process_admincp($userid = 0, $subscriptionid = 0, $txndescription = 'No description', $action = '')
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "subscription
                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        
                        $subscription_length = $this->subscription_length($res['units'], $res['length']);
                        $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                        
                        // #### MARK ACTIVE - NEW TRANSACTION IS CREATED ###############
                        if ($action == 'active')
                        {
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, subscriptionid, user_id, description, amount, paid, totalamount, status, invoicetype, createdate, duedate, paiddate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($userid) . "',
                                        '" . $ilance->db->escape_string($txndescription) . "',
                                        '0.00',
                                        '0.00',
                                        '0.00',
                                        'paid',
                                        'subscription',
                                        '" . DATETIME24H . "',
                                        '" . DATEINVOICEDUE . "',
                                        '" . DATETIME24H . "',
                                        '" . $ilance->db->escape_string($phrase['_subscription_fee_waived_by_administration']) . "',
                                        '" . construct_transaction_id() . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                                $newinvoiceid = $ilance->db->insert_id();
                                
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "subscription_user
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_user
                                                SET subscriptionid = '" . intval($subscriptionid) . "',
                                                startdate = '" . DATETIME24H . "',
                                                renewdate = '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                autopayment = '1',
                                                active = 'yes',
                                                migrateto = '" . $res['migrateto'] . "',
                                                migratelogic = '" . $res['migratelogic'] . "',
                                                invoiceid = '" . $newinvoiceid . "'
                                                WHERE user_id = '" . intval($userid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "subscription_user
                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, invoiceid)
                                                VALUES(
                                                NULL,
                                                '" . intval($subscriptionid) . "',
                                                '" . intval($userid) . "',
                                                'account',
                                                '" . DATETIME24H . "',
                                                '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                '1',
                                                'yes',
                                                '" . $res['migrateto'] . "',
                                                '" . $res['migratelogic'] . "',
                                                '" . $newinvoiceid . "')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                        
                        // #### MARK ACTIVE PAID - PAYMENT MADE OUTSIDE OF MARKET ######
                        else if ($action == 'activepaid')
                        {
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, subscriptionid, user_id, description, amount, paid, totalamount, status,
                                        invoicetype, createdate, duedate, paiddate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($userid) . "',
                                        '" . $ilance->db->escape_string($txndescription) . "',
                                        '" . $res['cost'] . "',
                                        '" . $res['cost'] . "',
                                        '" . $res['cost'] . "',
                                        'paid',
                                        'subscription',
                                        '" . DATETIME24H . "',
                                        '" . DATEINVOICEDUE . "',
                                        '" . DATETIME24H . "',
                                        '" . $ilance->db->escape_string($phrase['_subscription_fee_payment_paid_outside_marketplace_thank_you_for_your_business']) . "',
                                        '" . construct_transaction_id() . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                                $newinvoiceid = $ilance->db->insert_id();
                                
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "subscription_user
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_user
                                                SET subscriptionid = '" . intval($subscriptionid) . "',
                                                startdate = '" . DATETIME24H . "',
                                                renewdate = '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                autopayment = '1',
                                                active = 'yes',
                                                migrateto = '" . $res['migrateto'] . "',
                                                migratelogic = '" . $res['migratelogic'] . "',
                                                invoiceid = '" . $newinvoiceid . "'
                                                WHERE user_id = '" . intval($userid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "subscription_user
                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, invoiceid)
                                                VALUES(
                                                NULL,
                                                '" . intval($subscriptionid) . "',
                                                '" . intval($userid) . "',
                                                'account',
                                                '" . DATETIME24H . "',
                                                '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                '1',
                                                'yes',
                                                '" . $res['migrateto'] . "',
                                                '" . $res['migratelogic'] . "',
                                                '" . $newinvoiceid . "')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                        
                        // #### MARK INACTIVE & UNPAID - WILL REQUIRE PAYMENT ##########
                        else if ($action == 'inactive')
                        {
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, subscriptionid, user_id, description, amount, paid, status,
                                        invoicetype, createdate, duedate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($userid) . "',
                                        '" . $ilance->db->escape_string($txndescription) . "',
                                        '" . $res['cost'] . "',
                                        '',
                                        'unpaid',
                                        'subscription',
                                        '" . DATETIME24H . "',
                                        '" . DATEINVOICEDUE . "',
                                        '" . $ilance->db->escape_string($phrase['_thank_you_for_your_continued_business']) . "',
                                        '" . construct_transaction_id() . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                                $newinvoiceid = $ilance->db->insert_id();
                                
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "subscription_user
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_user
                                                SET subscriptionid = '" . intval($subscriptionid) . "',
                                                startdate = '" . DATETIME24H . "',
                                                renewdate = '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                autopayment = '1',
                                                active = 'no',
                                                migrateto = '" . $res['migrateto'] . "',
                                                migratelogic = '" . $res['migratelogic'] . "',
                                                invoiceid = '" . $newinvoiceid . "'
                                                WHERE user_id = '" . intval($userid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "subscription_user
                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, invoiceid)
                                                VALUES(
                                                NULL,
                                                '" . intval($subscriptionid) . "',
                                                '" . intval($userid) . "',
                                                'account',
                                                '" . DATETIME24H . "',
                                                '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                '1',
                                                'no',
                                                '" . $res['migrateto'] . "',
                                                '" . $res['migratelogic'] . "',
                                                '" . $newinvoiceid . "')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
        }
        
        /**
        * Function to internally check if a user has an active subscription plan (paid or free).
        *
        * @param       integer        user id
        *
        * @return      bool           Returns true or false
        */
        function has_active_subscription($userid = 0)
        {
                global $ilance;
                
                $sql = $ilance->db->query("
                        SELECT active, cancelled
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if ($res['active'] == 'yes' AND $res['cancelled'] == '0')
                        {
                                return true;
                        }
                }
                
                return false;
        }
        
        /**
        * Function to print a user's subscription title
        *
        * @param        integer     user id
        *
        * @return	string      Returns the subscription title
        */
        function print_subscription_title($userid = 0)
        {
                global $ilance, $myapi, $phrase;
                
                $sql = $ilance->db->query("
                        SELECT subscriptionid
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $sql2 = $ilance->db->query("
                                SELECT title
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . $res['subscriptionid'] . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res2 = $ilance->db->fetch_array($sql2);
                                return stripslashes($res2['title']);
                        }
                }
                
                return $phrase['_registered_subscriber'];
        }
        
        /**
        * Function to print a user's subscription icon
        *
        * @param        integer     user id
        *
        * @return	string      Returns the subscription icon
        */
        function print_subscription_icon($userid = 0)
        {
                global $ilance, $myapi, $phrase, $iltemplate, $ilconfig;
                
                $sql = $ilance->db->query("
                        SELECT subscriptionid
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        
                        $sql2 = $ilance->db->query("
                                SELECT icon, title
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . $res['subscriptionid'] . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql2) > 0)
                        {
                                $res2 = $ilance->db->fetch_array($sql2);
                                return '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $res2['icon'] . '" border="0" alt="' . stripslashes($res2['title']) . '" />';
                        }
                }
                
                return '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/default.gif" border="0" alt="' . $phrase['_registered_member'] . '" />';
        }
        
        /**
        * Function to dispatch subscription notifications to users "x" days before a subscription is expired
        * This function is run via iLance automation script (cron.dailyrfp.php)
        *
        * @param        integer     days to remind user before expiry (default 7)
        *
        * @return	string      Returns the cron log bit information to append to the cron job log for actions taken within this function
        */
        function send_subscription_expiry_reminders($reminddays = 7)
        {
                global $ilance, $phrase, $ilconfig, $ilpage;
                
                $ilance->email = construct_dm_object('email', $ilance);
                
                $sent = 0;
                $cronlog = '';
                
                // since this cron script will run once per day, let fetch
                // upcoming subscriptions in x days and send a friendly reminder
                // informing the user about the subscription renewal
                $remind = $ilance->db->query("
                        SELECT user_id, renewdate
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE cancelled = '0'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($remind) > 0)
                {
                        while ($reminds = $ilance->db->fetch_array($remind, DB_ASSOC))
                        {
                                // renew date
                                $date1split = explode(' ', $reminds['renewdate']);
                                $date2split = explode('-', $date1split[0]);
                                
                                // days left for subscription count (ex: reminder in 7 days from now)
                                $reminder = $reminddays;
                                $days = $ilance->datetime->fetch_days_between(gmdate('m'), gmdate('d'), gmdate('Y'), $date2split[1], $date2split[2], $date2split[0]);
                                if ($days == $reminder)
                                {
                                        $user = $ilance->db->query("
                                                SELECT username, first_name, last_name, email
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $reminds['user_id'] . "'
                                                    AND status = 'active'
                                                    AND email != ''
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($user) > 0)
                                        {
                                                $res_user = $ilance->db->fetch_array($user, DB_ASSOC);
                                                
                                                // #### QUICK EMAIL LOG CHECK > DID USER RECEIVE THIS EMAIL TODAY?
                                                
                                                $sql_emaillog = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "emaillog
                                                        WHERE logtype = 'subscriptionremind'
                                                            AND user_id = '" . $reminds['user_id'] . "'
                                                            AND date LIKE '%" . DATETODAY . "%'
                                                            AND sent = 'yes'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_emaillog) == 0)
                                                {
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "emaillog
                                                                (emaillogid, logtype, user_id, date, sent)
                                                                VALUES(
                                                                NULL,
                                                                'subscriptionremind',
                                                                '" . $reminds['user_id'] . "',
                                                                '" . DATETODAY . "',
                                                                'yes')
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // email user
                                                        $ilance->email->mail = $res_user['email'];
                                                        $ilance->email->slng = fetch_user_slng($reminds['user_id']);                                                        
                                                        $ilance->email->get('upcoming_subscription_reminder');		
                                                        $ilance->email->set(array(
                                                                '{{days}}' => $days,
                                                                '{{customer}}' => ucfirst($res_user['first_name']) . ' ' . ucfirst($res_user['last_name']) . ' > ' . ucfirst($res_user['username']),
                                                                '{{datetime}}' => DATETODAY . ' ' . TIMENOW,
                                                        ));                                                        
                                                        $ilance->email->send();
                                                        
                                                        $sent++;
                                                }
                                        }
                                }		
                        }
                        
                        $cronlog .= 'Sent uncoming subscription reminders to ' . $sent . ' users, ';
                }
                
                return $cronlog;
        }
        
        /**
        * Function to dispatch newsletter subscription notifications for latest listings posted that users have opted in
        * This function is run via iLance automation script (cron.dailyrfp.php)
        */
        function send_category_notification_subscriptions()
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                
                $ilance->email = construct_dm_object('email', $ilance);
                
                $cronlog = '';
                
                 if ($ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                        $new_projects_array = $seller_array = $emailsDuplicatePrevention = array();
                        
                        // fetch service auctions posted yesterday
                        $newprojects = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "projects
                                WHERE date_added LIKE '%" . DATEYESTERDAY . "%'
                                    AND status = 'open'
                                    AND project_details != 'invite_only'
                                    AND project_state = 'product'
                                    AND visible = '1'
                        ", 0, null, __FILE__, __LINE__);
                        while ($row = $ilance->db->fetch_array($newprojects, DB_ASSOC))
                        {
                                $new_projects_array[] = $row;
                        }
                    
                        // fetch sellers with active category subscriptions
                        $users = $ilance->db->query("
                                SELECT user_id, username, email, notifyproductscats, country, zip_code, city
                                FROM " . DB_PREFIX . "users
                                WHERE status = 'active'
                                    AND notifyproducts = '1'
                                    AND notifyproductscats != ''
                                    AND emailnotify = '1'
                                    AND email != ''
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($users) > 0)
                        {
                                while ($row = $ilance->db->fetch_array($users, DB_ASSOC))
                                {
                                        if (!in_array($row['email'], $emailsDuplicatePrevention))
                                        {
                                                $sellers[] = $row;
                                                $emailsDuplicatePrevention[] = $row['email'];
                                        }
                                }
                                unset($row);
                                $ilance->categories_parser = construct_object('api.categories_parser');
                                
                                if (!empty($sellers) AND count($sellers) > 0)
                                {
                                        $sent = 0;
                                        foreach ($sellers AS $seller)
                                        {
                                                $messagebody = '';
                                                $requested_categories = explode(',', $seller['notifyproductscats']);
                                                $projectsToSend = array();
                                                
                                                foreach ($requested_categories AS $category)
                                                {
                                                        if ($category > 0)
                                                        {
                                                                // fetch category's children recursively
                                                                //suku 
                                                                //$tempchildren = $ilance->categories->fetch_children($category, 'product');
																$tempchildren =$ilance->categories_parser->fetch_coin_class($category);
                                                                $children = explode(',', $tempchildren);
                                                                unset($tempchildren);
                                                                
                                                                foreach ($new_projects_array AS $new_project)
                                                                {
                                                                        if (in_array($new_project['cid'], $children))
                                                                        {
                                                                                $projectsToSend[] = $new_project;
                                                                        }
                                                                }
                                                        }
                                                }
                                                
                                                if (count($projectsToSend) > 0)
                                                {
                                                        foreach ($projectsToSend AS $project)
                                                        {
                                                                // fetch auction poster details
                                                                $buyerinfo = $ilance->db->query("
                                                                        SELECT username
                                                                        FROM " . DB_PREFIX . "users
                                                                        WHERE user_id = '" . $project['user_id'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                if ($ilance->db->num_rows($buyerinfo) > 0)
                                                                {
                                                                        $res_buyer_name = $ilance->db->fetch_array($buyerinfo, DB_ASSOC);
                                                                        
                                                                        $messagebody .= strip_vulgar_words(un_htmlspecialchars(stripslashes($project['project_title']))) . "\n";
                                                                        
                                                                        // todo: check for seo
                                                                        $messagebody .= HTTP_SERVER . "merch.php?id=" . $project['project_id'] . "\n";
                                                                        //$messagebody .= $phrase['_category'] . ": " . $ilance->categories->title(fetch_user_slng($seller['user_id']), 'product', $project['cid']) . "\n";
                                                                        $messagebody .= $phrase['_category'] . ": " . $ilance->categories->recursive($project['cid'], 'product', fetch_user_slng($seller['user_id']), 1, '', 0) . "\n";
                                                                        $messagebody .= $phrase['_seller'] . ": " . $res_buyer_name['username'] . "\n";
                                                                        $messagebody .= $phrase['_time_left'] . ": " . $ilance->auction->auction_timeleft($project['project_id'], '', '', 0, 0, 1) . "\n";
                                                                        $messagebody .= "************\n";
                                                                }
                                                        }
                                                        
                                                        $messagebody .= "\n";
                                                        $messagebody .= $phrase['_sell_merchandise_via_product_auctions'] . ":\n";
                                                        $messagebody .= HTTP_SERVER . "main.php?cmd=selling\n\n";
                                                        $messagebody .= $phrase['_browse_product_auctions_and_other_merchandise'] . ":\n";
                                                        $messagebody .= HTTP_SERVER . "merch.php?cmd=listings\n\n";
                                                        $messagebody .= "************\n";
                                                        $messagebody .= $phrase['_please_contact_us_if_you_require_any_additional_information_were_always_here_to_help'];
                                                        
                                                        // #### QUICK EMAIL LOG CHECK > DID USER RECEIVE THIS EMAIL TODAY?
                                                        $sql_emaillog = $ilance->db->query("
                                                                SELECT *
                                                                FROM " . DB_PREFIX . "emaillog
                                                                WHERE logtype = 'dailyproduct'
                                                                    AND user_id = '" . $seller['user_id'] . "'
                                                                    AND date LIKE '%" . DATETODAY . "%'
                                                                    AND sent = 'yes'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($sql_emaillog) == 0)
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "emaillog
                                                                        (emaillogid, logtype, user_id, date, sent)
                                                                        VALUES(
                                                                        NULL,
                                                                        'dailyproduct',
                                                                        '" . $seller['user_id'] . "',
                                                                        '" . DATETODAY . "',
                                                                        'yes')
                                                                ", 0, null, __FILE__, __LINE__);
                                                                
                                                                // just for reference so we can show the user the exact date we sent email last
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET lastemailproductcats = '" . DATETODAY . "'
                                                                        WHERE user_id = '" . $seller['user_id'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                
                                                                // email user
                                                                $ilance->email->mail = $seller['email'];
                                                                $ilance->email->slng = fetch_user_slng($seller['user_id']);                                                                
                                                                $ilance->email->get('cron_daily_auction_newsletter');		
                                                                $ilance->email->set(array(
                                                                        '{{newsletterbody}}' => $messagebody,
                                                                        '{{total}}' => count($projectsToSend),
                                                                ));                                                                
                                                                $ilance->email->send();
                                                                
                                                                $sent++;
                                                        }
                                                }
                                        }
                                }
                                unset($sellers);
                                
                                $cronlog .= 'Sent product auction daily newsletter to ' . $sent . ' users, ';
                                unset($sent);
                        }
                }
                
                return $cronlog;
        }
        
        /**
        * Function to cancel any scheduled subscription invoices based on a timer which the admin defines in max days of invoice cancellation
        */
        function cancel_scheduled_subscription_invoices()
        {
                global $ilance, $phrase, $ilconfig;
                
                $cronlog = '';
                
                $schsub = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "invoices
                        WHERE invoicetype = 'subscription'
                            AND (status = 'unpaid' OR status = 'scheduled')
                            AND paiddate = '0000-00-00 00:00:00'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($schsub) > 0)
                {
                        while ($unpaid = $ilance->db->fetch_array($schsub))	
                        {
                                // is invoice greater than maxpaymentdays?
                                // breakdown invoice create date
                                $date1split = explode(' ', $unpaid['createdate']);
                                $date2split = explode('-', $date1split[0]);
                                $totaldaysunpaid = $ilance->datetime->fetch_days_between($date2split[1], $date2split[2], $date2split[0], gmdate('m'), gmdate('d'), gmdate('Y'));
                                if ($totaldaysunpaid > $ilconfig['invoicesystem_maximumpaymentdays'])
                                {
                                        // cancel this scheduled subscription invoice (no longer being used)
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "invoices 
                                                SET status = 'cancelled'
                                                WHERE invoiceid = '" . $unpaid['invoiceid'] . "'
                                                LIMIT 1
                                        ");
                                        
                                        /*
                                        $ilance->email = construct_dm_object('email', $ilance);
                                                                
                                        // email admin
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        
                                        $ilance->email->get('cron_scheduled_subscription_txn_cancelled');		
                                        $ilance->email->set(array(
                                                '{{username}}' => fetch_user('username', $unpaid['user_id']),
                                                '{{totaldaysunpaid}}' => $totaldaysunpaid,
                                                '{{invoiceid}}' => $unpaid['invoiceid'],
                                        ));
                                        
                                        $ilance->email->send();
                                        */
                                }
                        }
                }
                
                return $cronlog;
        }
        
        /**
        * Function designed to send out subscription reminder notices based on an admin defined email dispatch frequency
        * This function is called from cron.reminders.php
        */
        function send_user_subscription_frequency_reminders()
        {
                global $ilance, $phrase, $ilconfig;
                
                $ilance->email = construct_dm_object('email', $ilance);
                
                $cronlog = '';
                $count = 0;
                $remindfrequency = $ilance->datetime->fetch_date_fromnow($ilconfig['invoicesystem_resendfrequency']);
                
                $expiry = $ilance->db->query("
                        SELECT user_id, invoiceid, invoicetype, createdate, description, amount, paid, totalamount, invoicetype, duedate, transactionid
                        FROM " . DB_PREFIX . "invoices
                        WHERE invoicetype = 'subscription'
                            AND (status = 'unpaid' OR status = 'scheduled')
                            AND amount > 0
                            AND archive = '0'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($expiry) > 0)
                {
                        while ($reminder = $ilance->db->fetch_array($expiry, DB_ASSOC))
                        {
                                $user = $ilance->db->query("
                                        SELECT email, first_name, last_name, username
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $reminder['user_id'] . "'
                                                AND status = 'active'
                                                AND email != ''
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($user) > 0)
                                {
                                        $res_user = $ilance->db->fetch_array($user, DB_ASSOC);
                                        
                                        // subscription reminder for this customer
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
                                                // no subscription logs found for this invoice id .. let's create one
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "invoicelog
                                                        (invoicelogid, user_id, invoiceid, invoicetype, date_sent, date_remind)
                                                        VALUES(
                                                        NULL,
                                                        '" . $reminder['user_id'] . "',
                                                        '" . $reminder['invoiceid'] . "',
                                                        '" . $reminder['invoicetype'] . "',
                                                        '" . DATETODAY . "',
                                                        '" . $remindfrequency . "')
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                if ($ilconfig['invoicesystem_unpaidreminders'])
                                                {
                                                        // email user
                                                        $ilance->email->mail = $res_user['email'];
                                                        $ilance->email->slng = fetch_user_slng($reminder['user_id']);
                                                        $ilance->email->get('cron_expired_subscription_invoice_reminder');		
                                                        $ilance->email->set(array(
                                                                '{{username}}' => $res_user['username'],
                                                                '{{firstname}}' => $res_user['first_name'],
                                                                '{{description}}' => $reminder['description'],
                                                                '{{transactionid}}' => $reminder['transactionid'],
                                                                '{{amount}}' => $ilance->currency->format($reminder['amount']),
                                                                '{{total}}' => $ilance->currency->format($reminder['totalamount']),
                                                                '{{paid}}' => $ilance->currency->format($reminder['paid']),
                                                                '{{duedate}}' => $reminder['duedate'],
                                                                '{{invoiceid}}' => $reminder['invoiceid'],
                                                                '{{reminddate}}' => $remindfrequency,
                                                        ));
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
                                                        
                                                        if ($ilconfig['invoicesystem_unpaidreminders'])
                                                        {
                                                                // email user
                                                                $ilance->email->mail = $res_user['email'];
                                                                $ilance->email->slng = fetch_user_slng($reminder['user_id']);
                                                                $ilance->email->get('cron_expired_subscription_invoice_reminder');		
                                                                $ilance->email->set(array(
                                                                        '{{username}}' => $res_user['username'],
                                                                        '{{firstname}}' => $res_user['first_name'],
                                                                        '{{description}}' => $reminder['description'],
                                                                        '{{transactionid}}' => $reminder['transactionid'],
                                                                        '{{amount}}' => $ilance->currency->format($reminder['amount']),
                                                                        '{{total}}' => $ilance->currency->format($reminder['totalamount']),
                                                                        '{{paid}}' => $ilance->currency->format($reminder['paid']),
                                                                        '{{duedate}}' => $reminder['duedate'],
                                                                        '{{invoiceid}}' => $reminder['invoiceid'],
                                                                        '{{reminddate}}' => $remindfrequency,
                                                                ));
                                                                // Murugan Changes On Mar 2 for Disable Email
																//$ilance->email->send();
                                                                
                                                                $count++;
                                                        }
                                                }
                                        }
                                }
                        }
                }
                
                if ($count > 0)
                {
                        $cronlog .= $count . ' subscription plan email invoice frequency reminders sent, ';
                }
                
                return $cronlog;
        }
        
        /**
        * Function to dispatch emails based on users saved searches where they choose to opt-in
        * This function is run via iLance automation script (cron.dailyrfp.php)
        */
        function send_saved_search_subscriptions($limit = 50)
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                
                if ($ilconfig['savedsearches'] == false)
                {
                        return;
                }
                
                $ilance->email = construct_dm_object('email', $ilance);
                $ilance->xml = construct_object('api.xml');
                $ilance->auction = construct_object('api.auction');
                
                // limits results per email (ie: show 50 results in email sent for services)
                $limit = intval($limit); 
                $cronlog = '';
                
                if ($ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                        $ilance->bid = construct_object('api.bid');
                        $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                        
                        // #### product auctions ###############################
                        // 1. select all subscriptions from search_favorites where subscribed = 1 and lastsent != today for products
                        
                        $sql = $ilance->db->query("
                                SELECT searchid, user_id, searchoptions, searchoptionstext, title, added, lastseenids
                                FROM " . DB_PREFIX . "search_favorites
                                WHERE cattype = 'product'
                                        AND subscribed = '1'
                                        AND lastsent NOT LIKE '%" . DATETODAY . "%'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $lastseen = $lastseenids = $last = array();
                                        
                                        $url = HTTP_SERVER . $ilpage['search'] . '?do=array' . html_entity_decode($res['searchoptions']);
                                        
                                        $c = curl_init();
                                        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($c, CURLOPT_URL, $url);
                                        $results = curl_exec($c);
                                        curl_close($c);
                                        
                                        if (!empty($res['lastseenids']) AND is_serialized($res['lastseenids']))
                                        {
                                                $lastseen = unserialize($res['lastseenids']);
                                        }
                                        
                                        if (!empty($results))
                                        {
                                                $results = urldecode($results);
                                                if (is_serialized($results))
                                                {
                                                        $results = unserialize($results);
                                                        
                                                        $messagebody = '';
                                                        
                                                        $sent = 0;
                                                        foreach ($results AS $key => $listing)
                                                        { // items found
                                                                foreach ($listing AS $field => $value)
                                                                { // fields
                                                                        if ($field == 'project_id' AND !in_array($value, $lastseen))
                                                                        { // save item id's so we don't resend duplicates in future (on a different day)
                                                                                $lastseenids[] = $value;
                                                                        }
                                                                }
															
                                                                if ($sent <= $limit)
                                                                {
																		$flag=0;
																		$sel_attach = $ilance->db->query("
																		SELECT * FROM " . DB_PREFIX . "attachment
																		WHERE project_id ='".$listing['project_id']."'
																		");
																		$isattachment = '0';
																		if($ilance->db->num_rows($sel_attach) > 0)
																		{
																		$isattachment = '1';					
																		}
																		else
																		{
																		$isattachment = '0';
																		}
																		if($isattachment == '1')
																		{
																		 
                                                                        $messagebody .= "<div style=\"padding-bottom:9px\">******************<div>Item ID :" . $listing['project_id'] . "</div><div>" . un_htmlspecialchars(stripslashes($listing['title'])) . "</div><div><strong>" . strip_tags($listing['price'], '<p><a>') . "</strong>(".strip_tags($listing['bids'], '<p><a>') .")</div><div>" . $phrase['_time_left'] . ": " . $ilance->auction->auction_timeleft($listing['project_id'], '', '', 0, 0, 1) . "</div></div>";        
                                                                        $messagebody .= "\n";
                                                                        $sent++;
																		$flag++;
																		
																		}
                                                                }
                                                        }
                                                }
                                        }
                                        
                                        if (!empty($lastseenids) AND is_array($lastseenids))
                                        {
                                                if (!empty($lastseen) AND is_array($lastseen))
                                                {
                                                        $last = array_merge($lastseenids, $lastseen);
                                                }
                                                else
                                                {
                                                        $last = $lastseenids;
                                                }
                                           //ensure that the user have enabled Email preference for Saved Search Reminders
										   
										   $email_notify = fetch_user('emailnotify',$res['user_id']);
										   
										  $query_saved_search = $ilance->db->query("SELECT wantlist FROM " . DB_PREFIX . "email_preference 
						                                                            WHERE user_id ='" .$res['user_id']. "'");
						
						                   $row_saved_search = $ilance->db->fetch_array($query_saved_search);							
				 
				                           if( $row_saved_search['wantlist'] == '1' AND $email_notify =='1')
						                   {
											   
											   if($flag > 0)
											   {
											   
                                                // dispatch product email
                                                $ilance->email->mail = fetch_user('email', $res['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res['user_id']);
                                                $ilance->email->dohtml = true;
                                                $ilance->email->logtype = 'alert';                                                                                                
                                                $ilance->email->get('cron_send_product_saved_searches');		
                                                $ilance->email->set(array(
                                                        '{{searchtitle}}' => un_htmlspecialchars(stripslashes($res['title'])),
                                                        '{{searchoptions}}' => un_htmlspecialchars($res['searchoptionstext']),					  
                                                        '{{username}}' => fetch_user('username', $res['user_id']),
                                                        '{{messagebody}}' => $messagebody,
                                                ));                                                
                                                $ilance->email->send();
												
											    }
                                                $last = serialize($last);
                                                
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "search_favorites
                                                        SET lastseenids = '" . $ilance->db->escape_string($last) . "',
                                                        lastsent = '" . DATETIME24H. "'
                                                        WHERE searchid = '" . $res['searchid'] . "'
                                                ");
												
											}	
                                        }
                                }
                        }
                }
                
                return $cronlog;
        }
        
        function expire_saved_search_subscriptions($days = 30)
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                
                if ($ilconfig['savedsearches'] == false)
                {
                        return;
                }
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "search_favorites
                        SET subscribed = '0', lastseenids = ''
                        WHERE added < DATE_SUB(CURDATE(), INTERVAL $days DAY)
                ");
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>