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

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

($apihook = $ilance->api('cron_creditcards_start')) ? eval($apihook) : false;

if ($ilconfig['use_internal_gateway'] != 'none')
{
        $ilance->accounting = construct_object('api.accounting');
        $ilance->accounting_creditcard = construct_object('api.accounting_creditcard');
        
        // expire cards that have not started authentication
        $days_ago_expired_noauth = gmdate('Y-m-d', mktime(0, 0, 0, gmdate('m', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('d', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))-$ilconfig['admin_cc_expired_days'], gmdate('Y', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))));
        $days_ago_expired_yesauth = gmdate('Y-m-d', mktime(0, 0, 0, gmdate('m', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('d', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))-$ilconfig['admin_cc_auth_expired_days'], gmdate('Y', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))));
        
        $sql_noauthattempts = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "creditcards
                WHERE authorized = 'no'
                    AND date_added LIKE '%" . $days_ago_expired_noauth . "%'
                    AND auth_amount1 = ''
                    AND auth_amount2 = ''
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql_noauthattempts) > 0)
        {
                while ($res_noauthattempts = $ilance->db->fetch_array($sql_noauthattempts))
                {
                        $sql_user = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . $res_noauthattempts['user_id'] . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_user) > 0)
                        {
                                $res_user = $ilance->db->fetch_array($sql_user);
                                
                                $ilance->email = construct_dm_object('email', $ilance);
                                                
                                // email user
                                $ilance->email->mail = array($res_user['email'], $res_noauthattempts['email_of_cardowner']);
                                $ilance->email->slng = fetch_user_slng($res_noauthattempts['user_id']);
                                
                                $ilance->email->get('expired_creditcard_removal_notice');		
                                $ilance->email->set(array(
                                        '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                        '{{customer}}' => ucfirst($res_user['first_name']) . " " . ucfirst($res_user['last_name']) . " > " . ucfirst($res_user['username']),
                                ));
                                
                                //$ilance->email->send();
                                
                                // email admin
                                $ilance->email->mail = SITE_EMAIL;
                                $ilance->email->slng = fetch_site_slng();
                                
                                $ilance->email->get('expired_creditcard_removal_notice_admin');		
                                $ilance->email->set(array(
                                        '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                        '{{customer}}' => ucfirst($res_user['first_name']) . " " . ucfirst($res_user['last_name']) . " > " . ucfirst($res_user['username']),
                                ));
                                
                                //$ilance->email->send();
                                
                                // remove the card from the db
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "creditcards
                                        WHERE cc_id = '" . $res_noauthattempts['cc_id'] . "'
                                            AND user_id = '" . $res_user['user_id'] . "'
                                        LIMIT 1
                                ");    
                        }
                }
        }
    
        // expire credit cards that have attempted authentication but have expired after x days
        $sql_cc_yesattempts = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "creditcards
                WHERE authorized = 'no'
                    AND date_added LIKE '%" . $days_ago_expired_yesauth . "%'
                    AND auth_amount1 != ''
                    AND auth_amount2 != ''
                    AND trans1_id != ''
                    AND trans2_id != ''
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql_cc_yesattempts) > 0)
        {
                while ($res_cc_yesattempts = $ilance->db->fetch_array($sql_cc_yesattempts))
                {
                        $ccid = $res_cc_yesattempts['cc_id'];
                        
                        $sql_user = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . $res_cc_yesattempts['user_id'] . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_user) > 0)
                        {
                                $res_user = $ilance->db->fetch_array($sql_user);
                                $name_on_card = $res_cc_yesattempts['name_on_card'];
                                $namesplit = explode(' ', $name_on_card);
                                
                                // does admin allow automated refunds via cron job?
                                if ($ilconfig['cron_refund_on_max_cc_auth_days'])
                                {
                                        // refund to credit card
                                        $v3customer_ccid = $ccid;
                                        $v3customer_fname = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$v3customer_ccid,"name_on_card"));
                                        $v3customer_lname = '';
                                        $v3customer_address = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$v3customer_ccid,"card_billing_address1")) . " " . stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$v3customer_ccid,"card_billing_address2"));
                                        $v3customer_city = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$v3customer_ccid,"card_city"));
                                        $v3customer_state = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$v3customer_ccid,"card_state"));
                                        $v3customer_zip = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$v3customer_ccid,"card_postalzip"));
                                        $v3customer_country = stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations","locationid=".$ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$v3customer_ccid,"card_country"),"location_".fetch_site_slng()));
                                        $input_auth = $ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$v3customer_ccid,"auth_amount1")+$ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=".$v3customer_ccid,"auth_amount2");
                                        
                                        $refundsuccess = $ilance->accounting_creditcard->creditcard_authentication_refund($input_auth, $v3customer_ccid, $v3customer_fname, $v3customer_lname, $v3customer_address, $v3customer_city, $v3customer_state, $v3customer_zip, $v3customer_country);
                                        
                                        if ($refundsuccess)
                                        {
                                                // remove credit card from db
                                                $ilance->db->query("
                                                        DELETE FROM " . DB_PREFIX . "creditcards
                                                        WHERE cc_id = '" . $res_cc_yesattempts['cc_id'] . "'
                                                            AND user_id = '" . $res_user['user_id'] . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                $ilance->email = construct_dm_object('email', $ilance);
                                                
                                                $existing = array(
                                                        '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                                        '{{customer}}' => ucfirst($v3customer_fname)." > ".ucfirst($res_user['username']),
                                                        '{{refundamount}}' => $ilance->currency->format($res_cc_yesattempts['auth_amount1']+$res_cc_yesattempts['auth_amount2']),
                                                        '{{paymentmodule}}' => $ilconfig['paymodulename'],
                                                );
                                                
                                                // email user
                                                $ilance->email->mail = array($res_user['email'], $res_cc_yesattempts['email_of_cardowner']);
                                                $ilance->email->slng = fetch_user_slng($res_user['user_id']);
                                                
                                                $ilance->email->get('expired_creditcard_removal_and_refund');		
                                                $ilance->email->set($existing);
                                                
                                               // $ilance->email->send();
                                                
                                                // email admin
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                
                                                $ilance->email->get('expired_creditcard_removal_and_refund_admin');		
                                                $ilance->email->set($existing);
                                                
                                              //  $ilance->email->send();
                                        }
                                }
                                else
                                {
                                        // send email to admin regarding refund to card - manual process mode
                                        
                                        $ilance->email = construct_dm_object('email', $ilance);
                                        
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                                
                                        $ilance->email->get('expired_creditcard_removal_and_autorefund_admin');		
                                        $ilance->email->set(array(
                                                '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                                '{{customer}}' => ucfirst($res_user['first_name'])." ".ucfirst($res_user['last_name'])." > ".ucfirst($res_user['username']),
                                                '{{refundamount}}' => $ilance->currency->format($res_cc_yesattempts['auth_amount1']+$res_cc_yesattempts['auth_amount2']),
                                        ));
                                        
                                      //  $ilance->email->send();
                                }                
                        }
                }
        }
    
        // expired credit card month/year checkup
        $sqlccexpiries = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "creditcards
                WHERE creditcard_status != 'expired'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sqlccexpiries) > 0)
        {
                while ($resccexpiries = $ilance->db->fetch_array($sqlccexpiries))
                {
                        $ccexpiry = $resccexpiries['creditcard_expiry'];
                        $ccexpirymonth = mb_substr($ccexpiry, 0, -2);
                        $ccexpiryyear = mb_substr($ccexpiry, -2);
                        
                        if ($ccexpiryyear > date('y'))
                        {
                        }
                        else if ($ccexpiryyear == date('y'))
                        {
                                if ($ccexpirymonth > date('m'))
                                {
                                }
                                else if ($ccexpirymonth == date('m'))
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "creditcards
                                                SET creditcard_status = 'expired',
                                                authorized = 'no'
                                                WHERE cc_id = '" . $resccexpiries['cc_id'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $ilance->email = construct_dm_object('email', $ilance);
                                        
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                                    
                                        $ilance->email->get('expired_creditcard_notice_admin');		
                                        $ilance->email->set(array(
                                                '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                                '{{username}}' => fetch_user('username', $resccexpiries['user_id']),
                                                '{{cardid}}' => $resccexpiries['cc_id'],
                                                '{{emailaddress}}' => fetch_user('email', $resccexpiries['user_id']),
                                        ));
                                        
                                        //$ilance->email->send();
                                }
                        }
                }
        }
}

($apihook = $ilance->api('cron_creditcards_end')) ? eval($apihook) : false;

log_cron_action('Credit Card tasks were executed successfully', $nextitem);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>