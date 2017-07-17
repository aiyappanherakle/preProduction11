<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1943
|| # -------------------------------------------------------------------- # ||
|| # Customer License # DPIzbreDT-vNiTuW=AuzteStDb2sio-n--kbWLg-MYBvswO-yG
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula | info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

/**
* Registration class to perform the majority of registration handling tasks
*
* @package      iLance
* @version  $Revision: 1.0.0 $
* @author       ILance
*/
class registration
{
    /**
    * Function for creating a valid ILance member using the registration datastore.
    *
    * @param       array        user information
    * @param       array        user preferences information
    * @param       array        user subscription information
    * @param       array        custom registration questions and answers
    * @param       string       tells this function what data to return when completed: return_userid OR return_userstatus OR return_userarray
    * @param       bool         tells this function if it should skip sessions (for api calls from other applications if required)
    *
    * @return      mixed        returns integers, strings and arrays
    */
    function build_user_datastore(&$user, &$preferences, &$subscription, &$questions, $custom = 'return_userarray', $skipsessions = 0)
    {
        global $ilance, $myapi, $ilconfig, $ilpage, $phrase;
		
        
        if (!empty($user) AND is_array($user))
        {
            // #### password logic #################################

            if (!empty($user['password_md5']) AND !empty($user['salt']))
            {
                // we are sending an already salted md5 password ready to store in the database
                $user['password'] = $user['password_md5'];
            }
            else
            {
                if (empty($user['salt']))
                {
                    // no salt found! just a clear text password! encode password!
                    $user['salt'] = construct_password_salt(5);
                    $user['password'] = md5(md5($user['password']) . $user['salt']);
                }
                else
                {
                    // clear text password and salt found! encode password!
                    $user['password'] = md5(md5($user['password']) . $user['salt']);
                }
            }
                    
            // address 2 checkup
            if (empty($user['address2']))
            {
                $user['address2'] = '';
            }

            /* We can not use this is europe so it needs to be removed
            // So user needs to be aware what is he adding
            
            // fix zipcode by removing spaces and/or dashes
            if (!empty($user['zipcode']))
            {
                $user['zipcode'] = str_replace('-', '', $user['zipcode']);
                $user['zipcode'] = str_replace(' ', '', $user['zipcode']);
            }
            */
            
            // date of birth checkup
            if (empty($user['dob']))
            {
                $user['dob'] = '0000-00-00';
            }

            // email link code verification
            $user['status'] = 'active';
            
            // ip address checkup
            if (empty($user['ipaddress']))
            {
                $user['ipaddress'] = IPADDRESS;
            }
            
            if (empty($user['roleid']))
            {
                // DEV NOTE: should be changed to search and find any available roles...
                $user['roleid'] = '1';    
            }
            
            if (empty($user['country']) OR empty($user['state']))
            {
                $user['country'] = $ilconfig['registrationdisplay_defaultcountry'];
                $user['countryid'] = fetch_country_id($user['country']);
                $user['state'] = $ilconfig['registrationdisplay_defaultstate'];
            }
                    
            // generate unique referral id code
            // murugan changes on feb 28 for referal code
            //$user['ridcode'] = create_referral_code(6);
            $user['ridcode'] = $user['referal_name'];
            
            if (!empty($preferences['companyname']))
            {
                $preferences['usecompanyname'] = '1';
            }
            else
            {
                $preferences['usecompanyname'] = '0';
            }
                    
            if (!empty($user['username']) )
            {
                // ##### BUILD MEMBER ##########################
                
                if (!is_array($preferences) OR count($preferences) < 5)
                {
                    // set default elements
                    $preferences['languageid'] = $ilance->language->fetch_default_languageid();
                    $preferences['currencyid'] = $ilance->currency->fetch_default_currencyid();
                    $preferences['usertimezone'] = $this->fetch_default_timezone();
                    $preferences['notifyservices'] = $preferences['notifyproducts'] = $preferences['usecompanyname'] = '0';
                    $preferences['notifyservicescats'] = $preferences['notifyproductscats'] = $preferences['companyname'] = '';
                    $preferences['usertimezone_dst'] = '1';
                }
                // Murugan Changes on Apr 28 for Double registration
				$check =  $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE username = '".$user['username']."'");
				if($ilance->db->num_rows($check) == 0 )
				{
                    $chkuserssql = $ilance->db->query("SELECT user_id,username,first_name,ipaddress,zip_code,last_name,phone,
                        email,address,address2,city,state,status FROM " . DB_PREFIX . "users 
                        WHERE (ipaddress = '" . $user['ipaddress'] . "') 
                        OR (zip_code = '" . $user['zipcode'] . "' AND last_name = '" . $user['last_name'] . "') 
                        OR (phone = '" . $user['phone'] . "')
                    ", 0, null, __FILE__, __LINE__);

                    if ($ilance->db->num_rows($chkuserssql) > 0)
                    {
                        $results = array();
                        while ($chkuserrslt = $ilance->db->fetch_array($chkuserssql)) {
                            $chkuserrslts[] = $chkuserrslt;
                        }

                        foreach ($chkuserrslts as $key => $resee) {

                            if($resee['ipaddress'] == $user['ipaddress'])
                            {
                                $result_ip[] = $user['ipaddress'];
                            }
                            else
                            {
                                $result_ip[] = '';
                            }

                            if(($resee['zip_code'] == $user['zipcode']) AND ($resee['last_name'] == $user['last_name']))
                            {
                                $result_zc[] = $user['zipcode'];
                                $result_ln[] = $user['last_name'];
                            }
                            else
                            {
                                $result_zc[] = '';
                                $result_ln[] = '';
                            }

                            if($resee['phone'] == $user['phone'])
                            {
                                $result_p[] = $user['phone'];
                            }
                            else
                            {
                                $result_p[] = '';
                            }
                        } 

                        if(count(array_filter($result_ip)) > 0)
                        {
                            $f_result_ip = "<b>".$user['ipaddress']."</b>";
                        }
                        else
                        {
                            $f_result_ip = $user['ipaddress'];
                        }

                        if(count(array_filter($result_zc)) > 0)
                        {
                            $f_result_zc = "<b>".$user['zipcode']."</b>";
                            $f_result_ln = "<b>".$user['last_name']."</b>";
                        }
                        else
                        {
                            $f_result_zc = $user['zipcode'];
                            $f_result_ln = $user['last_name'];
                        }

                        if(count(array_filter($result_p)) > 0)
                        {
                            $f_result_pp = "<b>".$user['phone']."</b>";
                        }
                        else
                        {
                            $f_result_pp = $user['phone'];
                        }
                        
                        $Max_user_id_result= $ilance->db->query("SELECT MAX(user_id) as max FROM " . DB_PREFIX . "users");
                        $get_max_userid = $ilance->db->fetch_array($Max_user_id_result);
                        $final_result = $get_max_userid['max']+1;

                        $to = $ilconfig['globalserversettings_adminemail'];
                        $subject = 'Duplicate New Account - '.$final_result.'';

                        $messagebody .= "Dear Ian,"."\n\n";
                        $messagebody .= "*********************************"."\n";
                        $messagebody .= "Duplicate New Account"."\n";
                        $messagebody .= "*********************************"."\n";
                        $messagebody .= "Username: ". $user['username']. "\n";
                        $messagebody .= "Full Name: ". $user['first_name']. ' '.$f_result_ln. "\n";
                        $messagebody .= "Telephone: " . $f_result_pp. "\n";
                        $messagebody .= "Email: " . $user['email'] . "\n";
                        $messagebody .= "Address: ". ucwords($user['address']) . ' ' . ucwords($user['address2']).' '.ucwords($user['city']).' '.ucwords($user['state']).' '.mb_strtoupper(trim($f_result_zc)). "\n";
                        $messagebody .= "IP address: " . $f_result_ip. "\n";
                        $messagebody .= "*********************************"."\n";
                        $messagebody .= "\n";
                        $messagebody .= "*********************************"."\n";
                        $messagebody .= "Old User Information"."\n";
                        $messagebody .= "*********************************"."\n";

                        foreach ($chkuserrslts as $key => $ress) {

                            $sql_regardlist_pi = $ilance->db->query("SELECT i.invoiceid FROM " . DB_PREFIX . "invoices i
                                JOIN " . DB_PREFIX . "users u ON u.user_id = i.user_id
                                WHERE i.user_id = '" . $ress['user_id']."'
                                AND i.isfvf != 1
                                AND i.isif != 1
                                AND i.Site_Id != 1
                                AND i.isbuyerfee != 1
                                AND i.subscriptionid != 1
                                AND i.isenhancementfee != 1
                            ", 0, null, __FILE__, __LINE__);

                            $invcount = $ilance->db->num_rows($sql_regardlist_pi);

                            if($invcount > 0)
                            {
                                $usralrdPurch = 'Yes';
                            }
                            else
                            {
                                $usralrdPurch = 'No';
                            }

                            if($ress['ipaddress'] == $user['ipaddress'])
                            {
                                $result_ipp = "<b>".$user['ipaddress']."</b>";
                            }else
                            {
                                $result_ipp = $ress['ipaddress'];
                            }

                            if(($ress['zip_code'] == $user['zipcode']) AND ($ress['last_name'] == $user['last_name']))
                            {
                                $result_zcc = "<b>".$user['zipcode']."</b>";
                                $result_lnn = "<b>".$user['last_name']."</b>";
                            }else
                            {
                                $result_zcc = $ress['zip_code'];
                                $result_lnn = $ress['last_name'];
                            }
                            if($ress['phone'] == $user['phone'])
                            {
                                $result_pp = "<b>".$user['phone']."</b>";
                            }else
                            {
                                $result_pp = $ress['phone'];
                            }                              

                            $messagebody .= "Username: ". $ress['username']. "\n";
                            $messagebody .= "Full Name: ". $ress['first_name']. ' '.$result_lnn. "\n";
                            $messagebody .= "Telephone: " . $result_pp. "\n";
                            $messagebody .= "Email: " . $ress['email'] . "\n";
                            $messagebody .= "Address: ". ucwords($ress['address']) . ' ' . ucwords($ress['address2']).' '.ucwords($ress['city']).' '.ucwords($ress['state']).' '.mb_strtoupper(trim($result_zcc)). "\n";
                            $messagebody .= "IP address: " . $result_ipp. "\n";
                            $messagebody .= "Status: " . $ress['status']. "\n";
                            $messagebody .= "User Already Purchased: " . $usralrdPurch. "\n";
                            $messagebody .= "*********************************"."\n";
                        }

                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
                        $ianemail=$ilconfig['globalserversettings_adminemail'];
                        $headers .= 'From: '.$ianemail.'' . "\r\n" ;
                        $success= send_email($to,$subject, $messagebody,OWNER_EMAIL,SITE_NAME,1);
                    }

                    $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "users
                        (user_id, ipaddress, username, password, salt, secretquestion, secretanswer, email, first_name, last_name, address, address2, city, state, zip_code, phone, country, date_added, status, lastseen, dob, rid, styleid, languageid, currencyid, timezoneid, timezone_dst, notifyservices, notifyproducts, notifyservicescats, notifyproductscats, displayprofile, emailnotify, companyname, usecompanyname)
                        VALUES(
                        NULL,
                        '" . $ilance->db->escape_string($user['ipaddress']) . "',
                        '" . $ilance->db->escape_string($user['username']) . "',
                        '" . $ilance->db->escape_string($user['password']) . "',
                        '" . $ilance->db->escape_string($user['salt']) . "',
                        '" . $ilance->db->escape_string($user['secretquestion']) . "',
                        '" . $ilance->db->escape_string($user['secretanswer']) . "',
                        '" . $ilance->db->escape_string($user['email']) . "',
                        '" . $ilance->db->escape_string($user['first_name']) . "',
                        '" . $ilance->db->escape_string($user['last_name']) . "',
                        '" . $ilance->db->escape_string($user['address']) . "',
                        '" . $ilance->db->escape_string($user['address2']) . "',
                        '" . $ilance->db->escape_string($user['city']) . "',
                        '" . $ilance->db->escape_string($user['state']) . "',
                        '" . $ilance->db->escape_string($user['zipcode']) . "',
                        '" . $ilance->db->escape_string($user['phone']) . "',
                        '" . intval($user['countryid']) . "',
                        '" . DATETIME24H . "',
                        '" . $ilance->db->escape_string($user['status']) . "',
                        '" . DATETIME24H . "',
                        '" . $ilance->db->escape_string($user['dob']) . "',
                        '" . $ilance->db->escape_string($user['ridcode']) . "',
                        '" . intval($user['styleid']) . "',
                        '" . intval($preferences['languageid']) . "',
                        '" . intval($preferences['currencyid']) . "',
                        '" . $ilance->db->escape_string($preferences['usertimezone']) . "',
                        '" . intval($preferences['usertimezone_dst']) . "',
                        '" . intval($preferences['notifyservices']) . "',
                        '" . intval($preferences['notifyproducts']) . "',
                        '" . $ilance->db->escape_string($preferences['notifyservicescats']) . "',
                        '" . $ilance->db->escape_string($preferences['notifyproductscats']) . "',
                        '1',
                        '1',
                        '" . $ilance->db->escape_string($preferences['companyname']) . "',
                        '" . intval($preferences['usecompanyname']) . "')
                    ", 0, null, __FILE__, __LINE__);
    
                    // assign new member id
                    $member['userid'] = $ilance->db->insert_id();
					
                    // murugan changes on march 12
                    $ilance->db->query("INSERT INTO " . DB_PREFIX . "email_preference
                        VALUES(
                        NULL,
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '" . $member['userid'] . "')
                    ");

                    $sqluser= $ilance->db->query("UPDATE " . DB_PREFIX . "users_unregistered
                        SET user_id = '" .$member['userid']. "'
                        WHERE email = '".$ilance->db->escape_string($user['email'])."' 
                        AND first_name= '" . $ilance->db->escape_string($user['first_name']) . "' 
                        AND last_name= '" . $ilance->db->escape_string($user['last_name']) . "'
                    ");

                    if($user['referal_name'] != '')
                    {
                        // Murugan Changes On Feb 17 For Refferal name Add                              
                        $selref = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "referal_id WHERE 
                            referalcode = '".$user['referal_name']."' ");
                        $resref = $ilance->db->fetch_array($selref);
                        $refcount = $resref['registercount'] + 1;
                        /*$sqlreff= $ilance->db->query("
                        UPDATE " . DB_PREFIX . "referal_id
                        SET registercount = '" .$refcount. "'
                        WHERE referalcode = '".$ilance->db->escape_string($user['referal_name'])."'");*/

                        $userup = $ilance->db->query("UPDATE " . DB_PREFIX . "users
                            SET referal_id = '" .$resref['id']. "'
                            WHERE user_id = '".$member['userid']."'
                        ");
                    }
                }
                else
                {
                    // User name Exist
                    // User name Already Exist
                    return false;
                }                        
            }
            else
            {
                // invalid member credentials
                // one or more elements within the $user array is missing
                return false;
            }
        }
        else
        {
            return false;
        }
            
        // at this point, we should have a new member id created, if so let's continue
        if (!empty($member['userid']) AND $member['userid'] > 0)
        {                    
            // ##### BUILD SUBSCRIPTION ############################
            if (!is_array($subscription))
            {
                // set default elements
                $subscription['subscriptionid'] = '1';
                $subscription['subscriptionpaymethod'] = 'account';
                $subscription['promocode'] = '';
            }
            
            // referral check: anyone referring this new member to register?
            if ($ilconfig['referalsystem_active'] AND !empty($_COOKIE[COOKIE_PREFIX . 'rid']))
            {
                $this->referral_check($member['userid'], $_COOKIE[COOKIE_PREFIX . 'rid']);
            }

            // obtain subscription plan information
            $sqlplan = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "subscription
                WHERE subscriptionid = '" . intval($subscription['subscriptionid']) . "'
            ", 0, null, __FILE__, __LINE__);
            if ($ilance->db->num_rows($sqlplan) > 0)
            {
                $subscription_plan_result = $ilance->db->fetch_array($sqlplan);
            }
    
            $sqlcurrencies = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "currency
                WHERE currency_id = '" . intval($preferences['currencyid']) . "'
            ", 0, null, __FILE__, __LINE__);
            if ($ilance->db->num_rows($sqlcurrencies) > 0)
            {
                $res_currencies = $ilance->db->fetch_array($sqlcurrencies);
            }

            unset($sqlplan, $sqlcurrencies);
    
            // construct full member session
            if ($skipsessions == 0)
            {
                $user['browseragent'] = USERAGENT;
                $user['languagecode'] = $ilance->language->print_language_code($_SESSION['ilancedata']['user']['languageid']);
                $user['slng'] = $_SESSION['ilancedata']['user']['slng'];
                $user['styleid'] = $_SESSION['ilancedata']['user']['styleid'];
                
                // if the status of this user is active, admin is disabling new user email verifications
                if ($user['status'] == 'active')
                {
                    // globalize user
                    $_SESSION['ilancedata'] = array(
                        'user' => array(
                            // carry over session details
                            'agreeterms' => 1,
                            'browseragent' => $user['browseragent'],
                            'languagecode' => $user['languagecode'],
                            'slng' => $user['slng'],
                            'styleid' => $user['styleid'],
                            'status' => $user['status'],
                            'userid' => intval($member['userid']),
                            'username' => $user['username'],
                            'password' => $user['password'],
                            'salt' => $user['salt'],
                            'email' => $user['email'],
                            'phone' => $user['phone'],
                            'firstname' => $user['firstname'],
                            'lastname' => $user['lastname'],
                            'fullname' => $user['firstname'] . ' ' . $user['lastname'],
                            'address' => ucwords($user['address']),
                            'address2' => ucwords($user['address2']),
                            'fulladdress' => ucwords($user['address']) . ' ' . ucwords($user['address2']),
                            'city' => ucwords($user['city']),
                            'state' => ucwords($user['state']),
                            'postalzip' => mb_strtoupper(trim($user['zipcode'])),
                            'zipcode' => mb_strtoupper(trim($user['zipcode'])),
                            'country' => ucwords($user['country']),
                            'countryid' => intval($user['countryid']),
                            'lastseen' => DATETIME24H,
                            'ipaddress' => $user['ipaddress'],
                            'iprestrict' => 0,
                            'auctiondelists' => 0,
                            'bidretracts' => 0,
                            'ridcode' => $user['ridcode'],
                            'dob' => $user['dob'],
                            'serviceawards' => 0,
                            'productawards' => 0,
                            'servicesold' => 0,
                            'productsold' => 0,
                            'rating' => 0,
                            'languageid' => intval($preferences['languageid']),
                            'timezoneid' => $preferences['usertimezone'],
                            'timezonedst' => $preferences['usertimezone_dst'],
                            'distance' => 0,
                            'emailnotify' => 1,
                            'companyname' => stripslashes($preferences['companyname']),
                            'roleid' => $user['roleid'],
                            'subscriptionid' => intval($subscription['subscriptionid']),
                            'active' => 'no',
                            'currencyid' => intval($preferences['currencyid']),
                            'currencyname' => stripslashes($res_currencies['currency_name']),
                            'currencysymbol' => $ilance->currency->currencies[$preferences['currencyid']]['symbol_left'],
                            'currencyabbrev' => mb_strtoupper($res_currencies['currency_abbrev']),
                            'token' => TOKEN,
                            'siteid' => SITE_ID,
                            'isadmin' => 0
                        )
                    );
                }
                else
                {
                    // admin requires new members to verify their emails
                    $_SESSION['ilancedata'] = array(
                        'user' => array(
                            'agreeterms' => 1,
                            'browseragent' => $user['browseragent'],
                            'languagecode' => $user['languagecode'],
                            'slng' => $user['slng'],
                            'styleid' => $user['styleid'],
                            'status' => $user['status'],
                            'username' => $user['username'],
                            'password' => $user['password'],
                            'salt' => $user['salt'],
                            'email' => $user['email'],
                            'phone' => $user['phone'],
                            'firstname' => $user['firstname'],
                            'lastname' => $user['lastname'],
                            'fullname' => $user['firstname'] . ' ' . $user['lastname'],
                            'address' => ucwords($user['address']),
                            'address2' => ucwords($user['address2']),
                            'fulladdress' => ucwords($user['address']) . ' ' . ucwords($user['address2']),
                            'city' => ucwords($user['city']),
                            'state' => ucwords($user['state']),
                            'postalzip' => mb_strtoupper(trim($user['zipcode'])),
                            'zipcode' => mb_strtoupper(trim($user['zipcode'])),
                            'country' => ucwords($user['country']),
                            'countryid' => intval($user['countryid']),
                            'lastseen' => DATETIME24H,
                            'ipaddress' => $user['ipaddress'],
                            'iprestrict' => 0,
                            'auctiondelists' => 0,
                            'bidretracts' => 0,
                            'ridcode' => $user['ridcode'],
                            'dob' => $user['dob'],
                            'serviceawards' => 0,
                            'productawards' => 0,
                            'servicesold' => 0,
                            'productsold' => 0,
                            'rating' => 0,
                            'languageid' => intval($preferences['languageid']),
                            'timezoneid' => $preferences['usertimezone'],
                            'timezonedst' => $preferences['usertimezone_dst'],
                            'distance' => 0,
                            'emailnotify' => 1,
                            'companyname' => stripslashes($preferences['companyname']),
                            'roleid' => $user['roleid'],
                            'subscriptionid' => intval($subscription['subscriptionid']),
                            'active' => 'no',
                            'currencyid' => intval($preferences['currencyid']),
                            'currencyname' => stripslashes($res_currencies['currency_name']),
                            'currencysymbol' => $ilance->currency->currencies[$preferences['currencyid']]['symbol_left'],
                            'currencyabbrev' => mb_strtoupper($res_currencies['currency_abbrev']),
                            'token' => TOKEN,
                            'siteid' => SITE_ID,
                            'isadmin' => 0
                        )
                    );
                }
            }
                
            // ###### BUILD ACCOUNTDATA ############################
            // does admin permit new account registration bonuses?
            if ($ilconfig['registrationupsell_bonusactive'])
            {
                // lets construct a little payment bonus for new member
                // if user status is active, this function will create a transaction and send bonus email to user
                // if user is unverified, this function will create a transaction and will not send bonus email to user
                // `-- it will only send bonus email when this user finally verifies his account via email link code
                //     `-- OR if user is moderated, the admin cp will verify and send email to new user
                $preferences['registerbonus'] = construct_account_bonus($member['userid'], $user['status']);
            }
            else
            {
                $preferences['registerbonus'] = '0.00';
            }
                
            // create account data
            $ilance->db->query("
                UPDATE " . DB_PREFIX . "users
                SET account_number = '" . $ilance->db->escape_string($ilconfig['globalserversettings_accountsabbrev']) . $this->construct_account_number() . "',
                available_balance = '" . $ilance->db->escape_string($preferences['registerbonus']) . "',
                total_balance = '" . $ilance->db->escape_string($preferences['registerbonus']) . "',
                income_reported = '" . $ilance->db->escape_string($preferences['registerbonus']) . "',
                income_spent = '0.00'
                WHERE user_id = '" . intval($member['userid']) . "'
            ");
                
            // ###### BUILD SUBSCRIPTION ###########################
            $this->build_user_subscription($member['userid'], $subscription['subscriptionid'], $subscription['subscriptionpaymethod'], $subscription['promocode'], $user['roleid']);
            
            // ##### INVITED TO BID MEMBER CHECKUP #################
            // tie invited auction to new user id!
            $this->build_invitation_datastore($member['userid'], $user['email']);

            // ##### BUILD REGISTRATION QUESTIONS ##################
            if (!empty($questions))
            {
                $this->build_registration_questions($questions, $member['userid']);
            }                        
                
            // #### SEND WELCOME EMAIL #############################
            if ($user['status'] == 'active')
            {
                $categories = '';           
                if ($ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                    $getcats = $ilance->db->query("
                        SELECT cid, title_" . $user['slng'] . " AS title
                        FROM " . DB_PREFIX . "categories
                        WHERE parentid = '0'
                        AND cattype = 'product'
                        AND visible = '1'
                        ORDER BY title_" . $user['slng'] . " ASC
                        LIMIT 10
                    ", 0, null, __FILE__, __LINE__);
                    if ($ilance->db->num_rows($getcats) > 0)
                    {
                        while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
                        {
                            $categories .= "$res[title]\n";
                        }
                    }
                }
                   
                $ilance->ipcountry = construct_object('api.ipcountry');
                $locations = $ilance->ipcountry->getCity($user['ipaddress']);
                $iptrace = $locations['cityName'].' '.$locations['regionName'];
                $ilance->email = construct_dm_object('email', $ilance);
                $ilance->email->logtype = 'Welcome Email';
                $ilance->email->mail = $user['email'];
                $ilance->email->slng = $user['slng'];
                $ilance->email->get('register_welcome_email');      
                $ilance->email->set(array(
                    '{{username}}' => $user['username'],
                    '{{user_id}}' => $member['userid'],
                    '{{first_name}}' => $user['first_name'],
                    '{{last_name}}' => $user['last_name'],
                    '{{phone}}' => $user['phone'],
                    '{{categories}}' => $categories
                ));
                $ilance->email->send();
                
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->logtype = 'Welcome Email';
                $ilance->email->slng = fetch_site_slng();
                $ilance->email->get('register_welcome_email_admin');        
                $ilance->email->set(array(
                    '{{username}}' => $user['username'],
                    '{{user_id}}' => $member['userid'],
                    '{{fullname}}' => $user['first_name']. ' '.$user['last_name'],                                        
                    '{{phone}}' => $user['phone'],
                    '{{emailaddress}}' => $user['email'],
                    '{{address}}' => ucwords($user['address']) . ' ' . ucwords($user['address2']).' '.ucwords($user['city']).' '.ucwords($user['state']).' '.mb_strtoupper(trim($user['zipcode'])),
                    '{{referral}}' => $user['referal_name'],
                    '{{ipaddr}}' => $user['ipaddress'],
                    '{{iplocation}}'=> $iptrace                                
                ));
                $ilance->email->send();
                    
                $ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
                $ilance->email->logtype = 'Welcome Email';
                $ilance->email->slng = fetch_site_slng();
                $ilance->email->get('register_welcome_email_admin');        
                $ilance->email->set(array(
                    '{{username}}' => $user['username'],
                    '{{user_id}}' => $member['userid'],
                    '{{fullname}}' => $user['first_name']. ' '.$user['last_name'],                                        
                    '{{phone}}' => $user['phone'],
                    '{{emailaddress}}' => $user['email'],
                    '{{address}}' => ucwords($user['address']) . ' ' . ucwords($user['address2']).' '.ucwords($user['city']).' '.ucwords($user['state']).' '.mb_strtoupper(trim($user['zipcode'])),
                    '{{referral}}' => $user['referal_name'],
                    '{{ipaddr}}' => $user['ipaddress'],
                    '{{iplocation}}'=> $iptrace                                
                ));
                $ilance->email->send();
                    
                $ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
                $ilance->email->logtype = 'Welcome Email';
                $ilance->email->slng = fetch_site_slng();
                $ilance->email->get('register_welcome_email_admin');        
                $ilance->email->set(array(
                    '{{username}}' => $user['username'],
                    '{{user_id}}' => $member['userid'],
                    '{{fullname}}' => $user['first_name']. ' '.$user['last_name'],                                        
                    '{{phone}}' => $user['phone'],
                    '{{emailaddress}}' => $user['email'],
                    '{{address}}' => ucwords($user['address']) . ' ' . ucwords($user['address2']).' '.ucwords($user['city']).' '.ucwords($user['state']).' '.mb_strtoupper(trim($user['zipcode'])),
                    '{{referral}}' => $user['referal_name'],
                    '{{ipaddr}}' => $user['ipaddress'],
                    '{{iplocation}}'=> $iptrace                                
                ));
                $ilance->email->send();
            }
            else
            {
                if ($user['status'] == 'unverified')
                {
                    // send link code verification email
                    $this->send_email_activation($user['email']);
                }
                else if ($user['status'] == 'moderated')
                {
                        
                }
            }
                
            // you may define, create, update or alter any information you see fit within
            // this area.  By the time the code reaches this point, the new member would
            // have been created within the database, new subscription account setup
            // and preferences all good to go!
            // ($apihook = $ilance->api('registration_end')) ? eval($apihook) : false;
           
        }
        else
        {
            return false;
        }
            
        $custom = 'return_userarray';
        // handle custom arguments to send valid response back
        if (isset($custom))
        {                       
            switch($custom)
            {
                // let's return the new member ID to the script
                case 'return_userid':
                {
                    return intval($member['userid']);
                    break;
                }                                
                // let's return the new member user / login status
                case 'return_userstatus':
                {
                    return $user['status'];
                    break;
                }                                
                // let's return the new member array
                case 'return_userarray':
                {                        
                    $user['userid'] = intval($member['userid']);
                    return $user;
                    break;                            
                }
                    
            }
        }
    }
    
    /**
    * Function to insert custom registration questions into the database based on formame (key) and answer (value).
    *
    * @param       array        question formname (key) and answer (value)
    * @param       integer      user id
    *
    * @return      nothing
    */
    function build_registration_questions(&$questions, $userid)
    {
        global $ilance, $myapi;
        
        if (!empty($questions) AND is_array($questions))
        {
            foreach ($questions AS $formname => $answer)
            {
                if (isset($formname) AND isset($answer))
                {
                    if (is_array($answer))
                    {
                        // multiple choice
                        $answer = serialize($answer);
                    }
                    
                    $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "register_answers
                        (answerid, questionid, user_id, answer, date, visible)
                        VALUES(
                        NULL,
                        '" . intval($this->fetch_formname_questionid($formname)) . "',
                        '" . intval($userid) . "',
                        '" . $ilance->db->escape_string($answer) . "',
                        '" . DATETIME24H . "',
                        '1')
                    ", 0, null, __FILE__, __LINE__);                    
                }
            }        
        }
    }
        
    /**
    * Function for dispatching the activation email to new clients.
    *
    * @param       string       user email address
    *
    * @return      nothing
    */
    function send_email_activation($useremail)
    {
        global $ilance, $ilconfig, $myapi, $phrase, $ilpage;
        
        $ilance->email = construct_dm_object('email', $ilance);
        
        $sql = $ilance->db->query("
            SELECT *
            FROM " . DB_PREFIX . "users
            WHERE email = '" . $ilance->db->escape_string($useremail) . "'
                AND status = 'unverified'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
            $res = $ilance->db->fetch_array($sql);
            $member['userid'] = $res['user_id'];

            $ilance->email->mail = $res['email'];
            $ilance->email->slng = fetch_user_slng($res['user_id']);
            
            $ilance->email->get('registration_email');      
            $ilance->email->set(array(
                '{{username}}' => $res['username'],
                '{{user_id}}' => $res['user_id'],
                '{{first_name}}' => $res['first_name'],
                '{{last_name}}' => $res['last_name'],
                '{{phone}}' => $res['phone'],
                '{{http_server}}' => HTTP_SERVER,
                '{{site_name}}' => SITE_NAME,
                '{{staff}}' => SITE_EMAIL,
                '{{link}}' => HTTP_SERVER . $ilpage['registration'] . '?cmd=activate&u=' . $ilance->crypt->three_layer_encrypt($member['userid'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']),
            ));
            
            $ilance->email->send();
            
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
    * Function for fetching the question id based on a formname question.
    *
    * @param       string       name of the form field
    *
    * @return      integer      question id number
    */
    function fetch_formname_questionid($formname)
    {
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
            SELECT questionid
            FROM " . DB_PREFIX . "register_questions
            WHERE formname = '" . $ilance->db->escape_string($formname) . "'
            LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
            $res = $ilance->db->fetch_array($sql);
            return intval($res['questionid']);
        }
        
        return 0;
    }
    
    /**
    * Function for creating a new user subscription plan.
    *
    * @param       integer      user id
    * @param       integer      subscription id
    * @param       string       payment method (account, paypal, cashu, stormpay, moneybookers, etc)
    * @param       string       promotional code
    * @param       integer      subscription role id
    * @param       boolean      skip session functionality (maybe calling from external script)
    *
    * @return      nothing
    */
    function build_user_subscription($userid = 0, $subscriptionid = 0, $paymethod = 'account', $promocode = '', $roleid = '1', $skipsession = 0)
    {
        global $ilance, $myapi, $phrase, $ilconfig, $ilpage;
        
        if (empty($roleid))
        {
            $roleid = (!empty($ilance->GPC['roleid']) AND $ilance->GPC['roleid'] > 0) ? intval($ilance->GPC['roleid']) : '-1';
        }
        
        $ilance->subscription = construct_object('api.subscription');
        $ilance->accounting = construct_object('api.accounting');
        
        $subscription_plan_result = array();
        
        $sql = $ilance->db->query("
            SELECT *
            FROM " . DB_PREFIX . "subscription
            WHERE subscriptionid = '" . intval($subscriptionid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
            $subscription_plan_result = $ilance->db->fetch_array($sql);
            
            ($apihook = $ilance->api('registration_build_user_subscription_start')) ? eval($apihook) : false;
            
            $subscription_plan_cost = sprintf('%01.2f', $subscription_plan_result['cost']);
            $subscription_length = $ilance->subscription->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
            $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
            
            $sql_check = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "subscription_user
                WHERE user_id = '" . intval($userid) . "'
                LIMIT 1
            ", 0, null, __FILE__, __LINE__);
            if ($ilance->db->num_rows($sql_check) == 0)
            {
                if ($paymethod == 'wire' OR empty($paymethod))
                {
                    $paymethod = 'account';
                }
                        
                // build subscription for user and set to unpaid / not active
                $ilance->db->query("
                    INSERT INTO " . DB_PREFIX . "subscription_user
                    (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, roleid)
                    VALUES(
                    NULL,
                    '" . intval($subscriptionid) . "',
                    '" . intval($userid) . "',
                    '" . $ilance->db->escape_string($paymethod) . "',
                    '" . DATETIME24H . "',
                    '" . $subscription_renew_date . "',
                    '1',
                    'no',
                    '" . $subscription_plan_result['migrateto'] . "',
                    '" . $subscription_plan_result['migratelogic'] . "',
                    '" . $roleid . "')
                ", 0, null, __FILE__, __LINE__);
        
                // if plan is free, update subscription for user to active
                if (isset($subscription_plan_result['cost']) AND $subscription_plan_result['cost'] == 0)
                {
                    $ilance->db->query("
                        UPDATE " . DB_PREFIX . "subscription_user
                        SET active = 'yes',
                        autopayment = '1'
                        WHERE user_id = '" . intval($userid) . "'
                        LIMIT 1
                    ", 0, null, __FILE__, __LINE__);
                    
                    // set subscription session to active
                    // this will also prevent an admin subscription sessions from changing
                    if ((defined('LOCATION') AND LOCATION != 'admin') OR $skipsession == 0)
                    {
                        $_SESSION['ilancedata']['user']['active'] = 'yes';
                    }
                }
            }
        }

        $subscription_length = $ilance->subscription->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
        $invoice_due_date = print_subscription_renewal_datetime($subscription_length);

        $sql_check = $ilance->db->query("
            SELECT *
            FROM " . DB_PREFIX . "invoices
            WHERE user_id = '" . intval($userid) . "'
                AND subscriptionid = '" . intval($subscriptionid) . "'
            LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql_check) == 0)
        {
            $ispurchaseorder = '0';
            $purchaseorderbit = '';
            $subscriptionordernumber = construct_transaction_id();
            
            if ($paymethod == 'wire')
            {
                $paymethod = 'account';
            }
            else if ($paymethod == 'purchaseorder')
            {
                $ispurchaseorder = '1';
                $paymethod = 'purchaseorder';
                $purchaseorderbit = $phrase['_purchase_order_invoice'] . ' (' . $subscriptionordernumber . '): ';
            }
            else
            {
                $paymethod = 'account';
            }
                
            $subscription_invoice_id = $ilance->accounting->insert_transaction(
                intval($subscriptionid),
                0,
                0,
                intval($userid),
                0,
                0,
                0,
                $purchaseorderbit . $phrase['_subscription_payment_for'] . ' ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
                sprintf("%01.2f", $subscription_plan_cost),
                '',
                'scheduled',
                'subscription',
                $paymethod,
                DATETIME24H,
                $invoice_due_date,
                '',
                $phrase['_thank_you_for_your_business'],
                0,
                $ispurchaseorder,
                1,
                $subscriptionordernumber
            );
                
            if (isset($subscription_plan_result['cost']) AND $subscription_plan_result['cost'] == 0)
            {
                // if free plan, update invoice
                $ilance->db->query("
                    UPDATE " . DB_PREFIX . "invoices
                    SET status = 'paid',
                    paiddate = '" . DATETIME24H . "',
                    paid = '0.00'
                    WHERE invoiceid = '" . intval($subscription_invoice_id) . "'
                            AND user_id = '" . intval($userid) . "'
                    LIMIT 1
                ", 0, null, __FILE__, __LINE__);
            }
            
            ($apihook = $ilance->api('registration_build_user_subscription_end')) ? eval($apihook) : false;

            $ilance->db->query("
                UPDATE " . DB_PREFIX . "subscription_user
                SET invoiceid = '" . intval($subscription_invoice_id) . "'
                WHERE user_id = '" . intval($userid) . "'
            ", 0, null, __FILE__, __LINE__);
                
            // if purchase order, send invoice to admin and customer
            // provide both with a printable link to the invoice
            if (isset($paymethod) AND $paymethod == 'purchaseorder')
            {
                $address2 = ' / ';
                if (!empty($_SESSION['ilancedata']['user']['address2']))
                {
                    $address2 = ' / ' . $_SESSION['ilancedata']['user']['address2'] . ' / ';
                }
                
                $url = HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=print&txn=' . $subscriptionordernumber;
                
                $ilance->email = construct_dm_object('email', $ilance);                
                $ilance->email->mail = array(SITE_EMAIL, $_SESSION['ilancedata']['user']['email']);
                $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                
                $ilance->email->get('registration_purchase_order');     
                $ilance->email->set(array(
                    '{{subscriptionordernumber}}' => $subscriptionordernumber,
                    '{{url}}' => $url,
                    '{{date}}' => print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                    '{{itemtitle}}' => stripslashes($subscription_plan_result['title']),
                    '{{length}}' => $subscription_plan_result['length'],
                    '{{units}}' => print_unit($subscription_plan_result['units']),
                    '{{subscription_plan_cost}}' => $ilance->currency->format($subscription_plan_cost),
                    '{{totalpaid}}' => $ilance->currency->format(0),
                    '{{firstname}}' => $_SESSION['ilancedata']['user']['firstname'],
                    '{{lastname}}' => $_SESSION['ilancedata']['user']['lastname'],
                    '{{address}}' => $_SESSION['ilancedata']['user']['address'],
                    '{{address2}}' => $_SESSION['ilancedata']['user']['address2'],
                    '{{city}}' => $_SESSION['ilancedata']['user']['city'],
                    '{{state}}' => $_SESSION['ilancedata']['user']['state'],
                    '{{zipcode}}' => $_SESSION['ilancedata']['user']['zipcode'],
                    '{{country}}' => $_SESSION['ilancedata']['user']['country'],
                    '{{phone}}' => $_SESSION['ilancedata']['user']['phone'],
                    '{{emailaddress}}' => $_SESSION['ilancedata']['user']['email']
                ));
                
                $ilance->email->send();
            }
        }
    }

    /**
    * Function for checking a referral code
    *
    * @param       integer      user id
    * @param       string       referral code
    *
    * @return      nothing
    */
    function referral_check($userid, $referralcode)
    {
        global $ilance, $myapi, $ilconfig, $ilpage;
        
        // select user who owns this rid code
        $sql = $ilance->db->query("
            SELECT rid, user_id
            FROM " . DB_PREFIX . "users
            WHERE rid = '" . $ilance->db->escape_string($referralcode) . "'
            LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
            while ($res = $ilance->db->fetch_array($sql))
            {
                $sql2 = $ilance->db->query("
                    SELECT *
                    FROM " . DB_PREFIX . "users
                    WHERE user_id = '" . intval($userid) . "'
                    LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql2) > 0)
                {
                    $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "referral_data
                        (id, user_id, referred_by, date)
                        VALUES(
                        NULL,
                        '" . intval($userid) . "',
                        '" . intval($res['user_id']) . "',
                        '" . DATETIME24H . "')
                    ", 0, null, __FILE__, __LINE__);
    
                    $ilance->email = construct_dm_object('email', $ilance);                
                    $ilance->email->mail = fetch_user('email', $res['user_id']);
                    $ilance->email->slng = fetch_user_slng(intval($userid));
                    
                    $ilance->email->get('referral_registered_referrer');        
                    $ilance->email->set(array(
                        '{{username}}' => fetch_user('username', $res['user_id']),
                        '{{rid}}' => $referralcode,
                        '{{payout_amount}}' => $ilance->currency->format($ilconfig['referalsystem_payout'])
                    ));
                    
                    $ilance->email->send();
                }
            }
        }
    }
    
    /**
    * Function for creating a new user account number used in the ILance accounting system.
    *
    * @return      mixed         unique online account balance number
    */
    function construct_account_number()
    {
        $rand1 = rand(100, 999);
        $rand2 = rand(100, 999);
        $rand3 = rand(100, 999);
        $rand4 = rand(100, 999);
        $rand5 = rand(1, 9);
        return $rand1 . $rand2 . $rand3 . $rand4 . $rand5;
    }
    
    /**
    * Function to process submitted custom registration questions to be stored within the database
    *
    * @param       array         custom answers stored in array format
    * @param       integer       user id
    * 
    * @return      mixed         unique online account balance number
    */
    function process_custom_register_questions(&$custom, $userid)
    {
        global $ilance;
        
        if (isset($custom) AND is_array($custom))
        {
            foreach ($custom as $questionid => $answerarray)
            {
                foreach ($answerarray as $formname => $answer)
                {
                    $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "register_answers
                        WHERE user_id = '" . intval($userid) . "'
                        AND questionid = '" . intval($questionid) . "'
                    ");
                    if ($ilance->db->num_rows($sql) > 0)
                    {
                        // update
                        if (is_array($answer))
                        {
                            // multiple choice
                            $answer = serialize($answer);
                        }
                        $ilance->db->query("
                            UPDATE " . DB_PREFIX . "register_answers
                            SET answer = '" . $ilance->db->escape_string($answer) . "',
                            date = '" . DATETIME24H . "'
                            WHERE questionid = '" . intval($questionid) . "'
                            AND user_id = '" . intval($userid) . "'
                        ");
                    }
                    else
                    {
                        // create
                        if (is_array($answer))
                        {
                            // multiple choice
                            $answer = serialize($answer);
                        }
                        $ilance->db->query("
                            INSERT INTO " . DB_PREFIX . "register_answers
                            (answerid, questionid, user_id, answer, date, visible)
                            VALUES (
                            NULL,
                            '" . intval($questionid) . "',
                            '" . intval($userid) . "',
                            '" . $ilance->db->escape_string($answer) . "',
                            '" . DATETIME24H . "',
                            '1')
                        ");
                    }
                }
            }
        }
    }

    /**
    * Function for checking if a user attempting to register is coming from a proxy service and
    * displays a custom template denying registration if registration proxy disabling is enabled.
    *
    * @return      string        HTML representation of the question registration question
    */
    function proxy_check()
    {
        global $ilconfig, $ilcrumbs, $ilpage, $phrase;
        
        if (isset($ilconfig['globalfilters_blockregistrationproxies']) AND $ilconfig['globalfilters_blockregistrationproxies'] != "")
        {
            if (isset($_SERVER['HTTP_FORWARDED']) OR isset($_SERVER['HTTP_X_FORWARDED_FOR']) OR isset($_SERVER['HTTP_VIA']))
            {
                $area_title = $phrase['_cannot_register_behind_proxy'];
                $page_title = SITE_NAME . ' - ' . $phrase['_cannot_register_behind_proxy'];
                
                $navcrumb = array("$ilpage[main]" => "$ilcrumbs[registration]");
                print_notice($phrase['_cannot_register_behind_proxy'], $phrase['_sorry_registration_to_the_marketplace_requires_our_members_not_to_be_behind_a_proxy'], $ilpage['main'], $phrase['_main_menu']);
                exit();
            }
        }
    }
    
    /**
    * Function for returning the subscription id of a free subscription plan that is active and visible
    * for the permission of 'servicebid' with a value of 'true'
    *
    * @return      bool          false or the integer of the subscription id
    */
    function fetch_invite_subscriptionid()
    {
        global $ilance, $myapi;
        
        $found = 0;
        $sql = $ilance->db->query("
            SELECT subscriptionid, subscriptiongroupid
            FROM " . DB_PREFIX . "subscription
            WHERE cost = '0.00'
            AND active = 'yes'
            AND visible = '1'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
            while ($res = $ilance->db->fetch_array($sql))
            {
                // we have free subscription plans: which plan has "servicebid" enabled?
                $sql2 = $ilance->db->query("
                    SELECT *
                    FROM " . DB_PREFIX . "subscription_permissions
                    WHERE subscriptiongroupid = '" . $res['subscriptiongroupid'] . "'
                        AND accessname = 'servicebid'
                        AND value = 'yes'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql2) > 0)
                {
                    // found a plan! lets assign this plan to externally invited bidders!
                    return $res['subscriptionid'];
                }
                else
                {
                    return 0;
                }
            }
        }
        else
        {
            return 0;
        }
    }
    
    /**
    * Function for returning the subscription id of a free subscription plan that is active and visible
    * for the permission of 'servicebid' with a value of 'true'
    *
    * @param       integer       user id
    * @param       string        email address
    *
    * @return      nothing
    */
    function build_invitation_datastore($userid = 0, $email)
    {
        global $ilance, $myapi;
        
        // transform this once external provider into a registered member invited to bid
        // for service buyers that invite providers to their projects
        $sql = $ilance->db->query("
            SELECT *
            FROM " . DB_PREFIX . "project_invitations
            WHERE email = '" . $ilance->db->escape_string($email) . "'
                AND seller_user_id = '-1'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
            while ($res = $ilance->db->fetch_array($sql))
            {
                $ilance->db->query("
                    UPDATE " . DB_PREFIX . "project_invitations
                    SET seller_user_id = '" . intval($userid) . "'
                    WHERE email = '" . $ilance->db->escape_string($email) . "'
                        AND seller_user_id = '-1'
                    LIMIT 1
                ", 0, null, __FILE__, __LINE__);
            }
        }
        
        // transfer this once external buyer into a registered member invited to bid
        // for merchants that invite buyers to their products
        $sql = $ilance->db->query("
            SELECT *
            FROM " . DB_PREFIX . "project_invitations
            WHERE email = '" . $ilance->db->escape_string($email) . "'
            AND buyer_user_id = '-1'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
            while ($res = $ilance->db->fetch_array($sql))
            {
                $ilance->db->query("
                    UPDATE " . DB_PREFIX . "project_invitations
                    SET buyer_user_id = '" . intval($userid) . "'
                    WHERE email = '" . $ilance->db->escape_string($email) . "'
                        AND buyer_user_id = '-1'
                    LIMIT 1
                ", 0, null, __FILE__, __LINE__);
            }
        }
    }
    
    /**
    * Function for returning the default time zone
    *
    * @return      string        Returns default time zone
    */
    function fetch_default_timezone()
    {
        global $ilconfig;
        
        $tzn = (!empty($ilconfig['globalserverlocale_officialtimezone']) ? $ilconfig['globalserverlocale_officialtimezone'] : '0');
        
        return $tzn;
    }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Dec 16th, 2010
|| ####################################################################
\*======================================================================*/
?>