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

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
        'tabfx',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';


if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
		$id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
		
		//date
		$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';

						$day = date('d');
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$daylist .= "<option value='$i' selected>$i</option>";
						else
						$daylist .= "<option value='$i'>$i</option>";
	
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';

				$month = date('m');
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$monthlist .= "<option value='$j' selected>$j</option>";
						else
						$monthlist .= "<option value='$j'>$j</option>";
						
						
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
					$year = date('Y');;
					for($k=date("Y"); $k<=date("Y")+5; $k++)
					if($year == $k)
					$yearlist .= "<option value='$k' selected>$k</option>";
					else
					$yearlist .= "<option value='$k'>$k</option>";
				
				$yearlist .='</select>';
				//date 
        // #### UPDATE USER REGISTRATION QUESTIONS #############################
        if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-customer-profile-questions' AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
        {
                // process custom registration questions
                if (!empty($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
                {
                        //$ilance->registration = construct_object('api.registration');
                        //$ilance->registration->process_custom_register_questions($ilance->GPC['custom'], intval($ilance->GPC['uid']));
                }
                
                print_action_success($phrase['_profile_answers_were_updated_for_this_profile_an_email_was_not_sent'], $ilpage['users'] . '?subcmd=_update-customer&id='.intval($ilance->GPC['uid']));
                exit();        
        }
        
        // #### UPDATE USER REGISTRATION QUESTIONS #############################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-customer-register-questions' AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
        {
                // process custom registration questions
                if (!empty($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
                {
                        $ilance->registration = construct_object('api.registration');
                        $ilance->registration->process_custom_register_questions($ilance->GPC['custom'], intval($ilance->GPC['uid']));
                }
                
                print_action_success($phrase['_registration_answers_were_updated_for_this_profile_an_email_was_not_sent_to_the_customer'], $ilpage['users'].'?subcmd=_update-customer&id='.intval($ilance->GPC['uid']));
                exit();        
        }
        
        // #### REMOVE CUSTOM REGISTRATION ANSWER FOR A SUBSCRIBER #############
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-answer' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
        {
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "register_answers
                        WHERE answerid = '" . intval($ilance->GPC['id']) . "'
                        AND user_id = '".intval($ilance->GPC['uid'])."'
                ");
                
                print_action_success($phrase['_you_have_removed_a_registration_answer_for_this_user_an_email_was_not_sent'], $ilpage['users'].'?subcmd=_update-customer&id='.intval($ilance->GPC['uid']));
                exit();
        }
    
        // #### save role ######################################################
        else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-role-change')
        {
                if (!empty($ilance->GPC['roleid']) AND !empty($ilance->GPC['uid']))
                {
                        // admin is changing the role for this user
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "subscription_user
                                SET roleid = '".intval($ilance->GPC['roleid'])."'
                                WHERE user_id = '".intval($ilance->GPC['uid'])."'
                        ");
                }
                
                print_action_success($phrase['_you_have_updated_the_role_for_this_user'], $ilpage['users'].'?subcmd=_update-customer&id='.intval($ilance->GPC['uid']));		
        }
            
        // #### remove single user #############################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deleteuser' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                // empty inline cookie
		set_cookie('inlinemembers', '', false);
		
		$removedusers = $ilance->admincp->remove_user(array($ilance->GPC['id']));
		if (!empty($removedusers))
		{
			$removedusers = mb_substr($removedusers, 0, -2);
			print_action_success($phrase['_the_selected_users_were_removed_from_the_marketplace_indefinately'] . " " . $removedusers . ". " . $phrase['_these_customers_will_not_be_able_to_login_to_the_marketplace_unless'], $ilpage['users']);
			exit();
		}
		else
		{
			print_action_failed($phrase['_the_selected_user_was_not_found_to_be_removed_from_the_marketplace'], $ilpage['users']);
			exit();	
		}
        }
        
        // #### remove multiple users ##########################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deleteusers')
        {
		// empty inline cookie
		set_cookie('inlinemembers', '', false);
		
                if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
                {
                        $removedusers = $ilance->admincp->remove_user($ilance->GPC['user_id']);
			if (!empty($removedusers))
			{
				$removedusers = mb_substr($removedusers, 0, -2);
				
				print_action_success($phrase['_the_selected_users_were_removed_from_the_marketplace_indefinately'] . " " . $removedusers . ". " . $phrase['_these_customers_will_not_be_able_to_login_to_the_marketplace_unless'], $ilpage['users']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_no_customers_were_selected_for_removal_please_try_again'], $ilpage['users']);
				exit();	
			}
                }
                else
                {
                        print_action_failed($phrase['_no_customers_were_selected_for_removal_please_try_again'], $ilpage['users']);
                        exit();
                }
        }
            
        // #### REMOVE SUBSCRIPTION PERMISSION EXEMPTION #######################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-exemption' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
        {
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "subscription_user_exempt
                        WHERE exemptid = '" . intval($ilance->GPC['id']) . "'
                            AND user_id = '".$ilance->GPC['uid']."'
                        LIMIT 1
                ");
                
                print_action_success($phrase['_the_selected_exemption_was_removed_from_the_customers_subscription'], $ilpage['users'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['uid']));
                exit();
        }
            
        // #### suspend customer ###############################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'suspenduser' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
		// empty inline cookie
		set_cookie('inlinemembers', '', false);
		
		$suspendusers = $ilance->admincp->suspend_user(array($ilance->GPC['id']));
		if (!empty($suspendusers))
		{
			$suspendusers = mb_substr($suspendusers, 0, -2);
			print_action_success($phrase['_the_selected_users_have_been_suspended'].' '.$suspendusers, $ilpage['users']);
			exit();
		}
		else
		{
			print_action_failed($phrase['_could_not_suspend_one_or_more_users_please_try_again'], $ilpage['users']);
			exit();	
		}
        }
	
	// #### suspend customers ##############################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'suspendusers')
        {
		// empty inline cookie
		set_cookie('inlinemembers', '', false);
		
		if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
                {
			$suspendusers = $ilance->admincp->suspend_user($ilance->GPC['user_id']);
			if (!empty($suspendusers))
			{
				$suspendusers = mb_substr($suspendusers, 0, -2);
				print_action_success($phrase['_the_selected_users_have_been_suspended'].' '.$suspendusers, $ilpage['users']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_could_not_suspend_one_or_more_users_please_try_again'], $ilpage['users']);
				exit();	
			}
		}
		else
		{
			print_action_failed($phrase['_could_not_suspend_one_or_more_users_please_try_again'], $ilpage['users']);
			exit();		
		}
        }
	
	// #### ban users ######################################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'banusers')
        {
		// empty inline cookie
		set_cookie('inlinemembers', '', false);
		
		if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
                {
			$bannedusers = $ilance->admincp->ban_user($ilance->GPC['user_id']);
			if (!empty($bannedusers))
			{
				$bannedusers = mb_substr($bannedusers, 0, -2);
				print_action_success($phrase['_the_selected_users_have_been_banned'].' '.$bannedusers, $ilpage['users']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_could_not_place_a_ban_one_or_more_users_please_try_again'], $ilpage['users']);
				exit();	
			}
		}
		else
		{
			print_action_failed($phrase['_could_not_place_a_ban_one_or_more_users_please_try_again'], $ilpage['users']);
			exit();		
		}
        }
	
	// #### cancel users ###################################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'cancelusers')
        {
		// empty inline cookie
		set_cookie('inlinemembers', '', false);
		
		if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
                {
			$cancelledusers = $ilance->admincp->cancel_user($ilance->GPC['user_id']);
			if (!empty($cancelledusers))
			{
				$cancelledusers = mb_substr($cancelledusers, 0, -2);
				print_action_success($phrase['_the_selected_users_have_been_cancelled'].' '.$cancelledusers, $ilpage['users']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_could_not_cancel_one_or_more_users_please_try_again'], $ilpage['users']);
				exit();	
			}
		}
		else
		{
			print_action_failed($phrase['_could_not_cancel_one_or_more_users_please_try_again'], $ilpage['users']);
			exit();		
		}
        }
        
        // #### activate user ##################################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'activateuser' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
		// empty inline cookie
		set_cookie('inlinemembers', '', false);
		
		$activatedusers = $ilance->admincp->activate_user(array($ilance->GPC['id']));
		if (!empty($activatedusers))
		{
			$activatedusers = mb_substr($activatedusers, 0, -2);
			print_action_success($phrase['_the_selected_users_have_been_activated'].' '.$activatedusers, $ilpage['users']);
			exit();
		}
		else
		{
			print_action_failed($phrase['_could_not_activate_one_or_more_users_please_try_again'], $ilpage['users']);
			exit();	
		}
        }
	
	// #### activate multiple users ########################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'activateusers')
        {
		// empty inline cookie
                set_cookie('inlinemembers', '', false);
			
                if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
                {
                        $activatedusers = $ilance->admincp->activate_user($ilance->GPC['user_id']);
			if (!empty($activatedusers))
			{
				$activatedusers = mb_substr($activatedusers, 0, -2);
				
				print_action_success($phrase['_the_selected_users_have_been_activated'].' '.$activatedusers, $ilpage['users']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_could_not_activate_one_or_more_users_please_try_again'], $ilpage['users']);
				exit();	
			}
                }
                else
                {
                        print_action_failed($phrase['_could_not_activate_one_or_more_users_please_try_again'], $ilpage['users']);
                        exit();
                }
        }
	
	// #### unverify multiple users ########################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'unverifyusers')
        {
		// empty inline cookie
                set_cookie('inlinemembers', '', false);
			
                if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
                {
                        $unverifiedusers = $ilance->admincp->unverify_user($ilance->GPC['user_id']);
			if (!empty($unverifiedusers))
			{
				$unverifiedusers = mb_substr($unverifiedusers, 0, -2);
				
				print_action_success($phrase['_the_selected_users_have_been_unverified_and_will_need_to_verify_their_email_again_to_become_activated'].' '.$unverifiedusers, $ilpage['users']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_could_not_unverify_one_or_more_users_please_try_again'], $ilpage['users']);
				exit();	
			}
                }
                else
                {
                        print_action_failed($phrase['_could_not_unverify_one_or_more_users_please_try_again'], $ilpage['users']);
                        exit();
                }
        }
            
        // #### CREATE NEW CUSTOMER ############################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-new-customer')
        {
        	if(!isset($ilance->GPC['password']) OR !isset($ilance->GPC['password2']) OR empty($ilance->GPC['password']) OR empty($ilance->GPC['password2']) OR $ilance->GPC['password'] != $ilance->GPC['password2'])  
        	{
        		print_action_failed($phrase['_passwords_are_empty_or_do_not_match'], $ilpage['users']);
            	exit();
        	}
        	else if(!isset($ilance->GPC['username']) OR empty($ilance->GPC['username']))
        	{
        		print_action_failed($phrase['_please_enter_correct_username'], $ilpage['users']);
            	exit();
        	}     
		    else if(!isset($ilance->GPC['email']) OR empty($ilance->GPC['email'])) 
		    {         
		    	print_action_failed($phrase['_please_enter_correct_email'], $ilpage['users']);
            	exit();
		    }
		    else 
		    {
        		
		    	$unicode_name = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['username']);
                        
                if ($ilance->common->is_username_banned($ilance->GPC['username']) OR $ilance->common->is_username_banned($unicode_name))
                {
                	print_action_failed($phrase['_this_username_is_banned'], $ilpage['users']);
            		exit();
                }
		    	
                $sql = $ilance->db->query("
                        SELECT locationid
                        FROM " . DB_PREFIX . "locations
                        WHERE location_" . $_SESSION['ilancedata']['user']['slng'] . " = '" . $ilance->db->escape_string($ilance->GPC['country']) . "'
                        LIMIT 1
                ");
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);

				if (empty($ilance->GPC['secretanswer']))
				{
					$ilance->GPC['secretanswer'] = $ilance->GPC['email'];
					$ilance->GPC['secretquestion'] = "What's my email address?";
					$secretanswer = md5($ilance->GPC['secretanswer']);
				}
				else if (empty($ilance->GPC['secretquestion']))
				{
					$ilance->GPC['secretanswer'] = $ilance->GPC['email'];
					$ilance->GPC['secretquestion'] = "What's my email address?";
					$secretanswer = md5($ilance->GPC['secretanswer']);
				}
				else
				{
					$secretanswer = md5($ilance->GPC['secretanswer']);
				}
				
		                $salt = construct_password_salt(5);
		                $pass = md5(md5($ilance->GPC['password']) . $salt);
				
		                $ilance->GPC['isadmin'] = ((isset($ilance->GPC['isadmin']) AND $ilance->GPC['isadmin']) ? 1 : 0);

		                
		                $newuserid = $ilance->admincp->construct_new_member(
		                        $ilance->GPC['username'],
		                        $pass,
		                        $salt,
		                        $ilance->GPC['secretquestion'],
		                        $secretanswer,
		                        $ilance->GPC['email'],
		                        $ilance->GPC['firstname'],
		                        $ilance->GPC['lastname'],
		                        $ilance->GPC['address'],
		                        $ilance->GPC['address2'],
		                        $ilance->GPC['city'],
		                        $ilance->GPC['state'],
		                        $ilance->GPC['zipcode'],
		                        $ilance->GPC['phone'],
		                        $res['locationid'],
		                        '0000-00-00',
		                        create_referral_code(6),
		                        $ilconfig['globalserverlanguage_defaultlanguage'],
		                        $ilconfig['globalserverlocale_defaultcurrency'],
		                        $ilconfig['globalserverlocale_officialtimezone'],
		                        0,
		                        '',
		                        $ilance->GPC['isadmin']
		                );
		                
		                // is account bonus active?
		                $accountbonus = 0;
		                if ($ilconfig['registrationupsell_bonusactive'] AND empty($ilance->GPC['bonusdisable']))
		                {
		                        $accountbonus = construct_account_bonus($newuserid, 'active');
		                }
		                
		                // let's build users subscription
		                $ilance->registration = construct_object('api.registration');
		                
		                // role id
		                $ilance->GPC['roleid'] = isset($ilance->GPC['roleid']) ? intval($ilance->GPC['roleid']) : '-1';
		                
		                // invoice methods can be:
		                // account, purchaseorder
		                $ilance->registration->build_user_subscription($newuserid, intval($ilance->GPC['subscriptionid']), 'account', '', $ilance->GPC['roleid']);
		                
		                $ilance->email = construct_dm_object('email', $ilance);
		                
		                if (isset($ilance->GPC['notifyregister']) AND $ilance->GPC['notifyregister'])
		                {
					$categories = '';
					
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$getcats = $ilance->db->query("
							SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
							FROM " . DB_PREFIX . "categories
							WHERE parentid = '0'
								AND cattype = 'product'
								AND visible = '1'
							ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
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
					
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
					{
						$getcats = $ilance->db->query("
							SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
							FROM " . DB_PREFIX . "categories
							WHERE parentid = '0'
								AND cattype = 'service'
								AND visible = '1'
							ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
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
					
					$ilance->email->mail = $ilance->GPC['email'];
					$ilance->email->slng = fetch_user_slng($newuserid);
					$ilance->email->get('register_welcome_email_admincp');		
					$ilance->email->set(array(
						'{{username}}' => $ilance->GPC['username'],
						'{{user_id}}' => $newuserid,
						'{{first_name}}' => $ilance->GPC['firstname'],
						'{{last_name}}' => $ilance->GPC['lastname'],
						'{{phone}}' => $ilance->GPC['phone'],
						'{{categories}}' => $categories
					));
					$ilance->email->send();
		                }
		                
		                $ilance->email->mail = SITE_EMAIL;
		                $ilance->email->slng = fetch_site_slng();
		                $ilance->email->get('register_welcome_email_admin_admincp');		
		                $ilance->email->set(array(
		                        '{{username}}' => $ilance->GPC['username'],
		                        '{{user_id}}' => $newuserid,
		                        '{{first_name}}' => $ilance->GPC['firstname'],
		                        '{{last_name}}' => $ilance->GPC['lastname'],
		                        '{{phone}}' => $ilance->GPC['phone'],
		                        '{{emailaddress}}' => $ilance->GPC['email'],
		                ));
		                $ilance->email->send();
		                
		                print_action_success($phrase['_the_new_customer_was_created_the_new_customer_will_be_required_to'], $ilance->GPC['return']);
		                exit();
		    }
        }
    
        // #### UPDATE CUSTOMER PROFILE ########################################	
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-customer-profile' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {		
                $country = $ilance->db->escape_string($ilance->GPC['country']);
                $ilance->GPC['locationid'] = intval(fetch_country_id($country));
                
                ($apihook = $ilance->api('update_customer_profile_start')) ? eval($apihook) : false;  
                
                $ipres = ((isset($ilance->GPC['iprestrict']) AND $ilance->GPC['iprestrict']) ? '1' : '0');
        
                $passwordsql = '';
                if (!empty($ilance->GPC['password']))
                {
                        $newsalt = construct_password_salt($length = 5);
                        $newpassword = md5(md5($ilance->db->escape_string($ilance->GPC['password'])) . $newsalt);
                        $passwordsql = "password = '" . $newpassword . "',";
                        $passwordsql .= "salt = '" . $newsalt . "',";
                }
        
                $status = isset($ilance->GPC['status']) ? $ilance->db->escape_string($ilance->GPC['status']) : 'unverified';
                $dob = isset($ilance->GPC['dob']) ? $ilance->db->escape_string($ilance->GPC['dob']) : '0000-00-00';
                $isadmin = isset($ilance->GPC['isadmin']) ? intval($ilance->GPC['isadmin']) : '0';
        
                // detect if this user is moderated and if admin is changing status from 'moderated' to 'active'
                $oldstatus = fetch_user('status', intval($ilance->GPC['id']));
                if ($oldstatus == 'moderated' AND $status == 'active')
                {
			$categories = '';			
			if ($ilconfig['globalauctionsettings_productauctionsenabled'])
			{
				$getcats = $ilance->db->query("
					SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
					FROM " . DB_PREFIX . "categories
					WHERE parentid = '0'
						AND cattype = 'product'
						AND visible = '1'
					ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
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
			
			if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
			{
				$getcats = $ilance->db->query("
					SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
					FROM " . DB_PREFIX . "categories
					WHERE parentid = '0'
						AND cattype = 'service'
						AND visible = '1'
					ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
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
			
                        // user is moderated and admin is now validating him
                        $ilance->email = construct_dm_object('email', $ilance);
                        $ilance->email->mail = $ilance->GPC['email'];
                        $ilance->email->slng = fetch_user_slng(intval($ilance->GPC['id']));
                        $ilance->email->get('register_welcome_email');		
                        $ilance->email->set(array(
                                '{{username}}' => $ilance->GPC['username'],
                                '{{user_id}}' => intval($ilance->GPC['id']),
                                '{{first_name}}' => $ilance->GPC['first_name'],
                                '{{last_name}}' => $ilance->GPC['last_name'],
                                '{{phone}}' => $ilance->GPC['phone'],
				'{{categories}}' => $categories
                        ));
                        $ilance->email->send();
                        
                        // additionally, we'll run our account bonus function so this email is also dispatched
                        $registerbonus = '0.00';
                        if ($ilconfig['registrationupsell_bonusactive'])
                        {
                                // lets construct a little payment bonus for new member, we will:
                                // - create a transaction and send email to user and admin
                                // - return the bonus amount so we can update the users account
                                $registerbonus = construct_account_bonus(intval($ilance->GPC['id']), $status);
                                if ($registerbonus > 0)
                                {
                                        // update register bonus credit to online account data
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "users
                                                SET total_balance = total_balance + " . $registerbonus . ",
                                                available_balance = available_balance + " . $registerbonus . "
                                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                                        ");
                                }
                        }
                }
        
                // quick username checkup
                $show['error_username'] = false;
                if (isset($ilance->GPC['username']) AND $ilance->GPC['username'] != '')
                {
                        $unicode_name = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['username']);
                        
                        // username ban checkup
                        if ($ilance->common->is_username_banned($ilance->GPC['username']) OR $ilance->common->is_username_banned($unicode_name))
                        {
                                $show['error_username'] = true;
                        }
                        else
                        {
                                $sqlusercheck = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE username IN ('" . addslashes(htmlspecialchars_uni($ilance->GPC['username'])) . "', '" . addslashes(htmlspecialchars_uni($unicode_name)) . "')
                                                AND user_id != '" . intval($ilance->GPC['id']) . "'
                                ");
                                if ($ilance->db->num_rows($sqlusercheck) > 0)
                                {
                                        $show['error_username'] = true;
                                }
                                else
                                {
                                        $ilance->GPC['username'] = stripslashes(strip_tags(trim($ilance->GPC['username'])));
                                }
                        }
                }
                else
                {
                        $show['error_username'] = true;
                }
                
                if ($show['error_username'])
                {
                        print_action_failed($phrase['_sorry_the_username_you_entered_appears_to_be_in_the_username_ban_list'], $ilpage['users'] . '?subcmd=_update-customer&id=' . intval($ilance->GPC['id']));
                        exit();
                }
		
		// gender
		$gendersql = "";
		if ($ilconfig['genderactive'] AND isset($ilance->GPC['gender']) AND !empty($ilance->GPC['gender']))
		{
			$gendersql = "gender = '" . $ilance->db->escape_string($ilance->GPC['gender']) . "',";
		}
		
		// secret question and answer handler or error
		if (empty($ilance->GPC['secretanswer']))
		{
			$ilance->GPC['secretanswer'] = $ilance->GPC['email'];
			$ilance->GPC['secretquestion'] = "What's my email address?";
			$secretanswer = md5($ilance->GPC['secretanswer']);
		}
		else if (empty($ilance->GPC['secretquestion']))
		{
			$ilance->GPC['secretanswer'] = $ilance->GPC['email'];
			$ilance->GPC['secretquestion'] = "What's my email address?";
			$secretanswer = md5($ilance->GPC['secretanswer']);
		}
		else
		{
			$secretanswer = md5($ilance->GPC['secretanswer']);
		}
		

                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "',
                        $passwordsql
                        email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "',
                        first_name = '" . $ilance->db->escape_string($ilance->GPC['first_name']) . "',
                        last_name = '" . $ilance->db->escape_string($ilance->GPC['last_name']) . "',
                        address = '" . $ilance->db->escape_string($ilance->GPC['address']) . "',
                        address2 = '" . $ilance->db->escape_string($ilance->GPC['address2']) . "',
                        city = '" . $ilance->db->escape_string($ilance->GPC['city']) . "',
                        state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "',
                        zip_code = '" . $ilance->db->escape_string($ilance->GPC['zip_code']) . "',
                        phone = '" . $ilance->db->escape_string($ilance->GPC['phone']) . "',
                        country = '" . $ilance->GPC['locationid'] . "',
                        ipaddress = '" . $ilance->db->escape_string($ilance->GPC['ipaddress']) . "',
                        iprestrict = '" . $ipres . "',
                        status = '" . $status . "',
                        dob = '" . $dob . "',
			secretquestion = '" . $ilance->db->escape_string($ilance->GPC['secretquestion']) . "',
			secretanswer = '" . $ilance->db->escape_string($secretanswer) . "',
			$gendersql
                        isadmin = '" . $isadmin . "'
                        WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                ");
                    
                if (isset($ilance->GPC['emailuser']) AND $ilance->GPC['emailuser'])
                {
                        $notice = $phrase['_the_customers_profile_has_been_updated_with_new_changes_and_the_password_was_reset'];                        
                        $email['message'] = $phrase['_dear'] . " " . $ilance->GPC['username'] . ",\n\nThis email is to inform you that you password was reset by a staff member.  Please find your new login information below:\n\n===========================\nUsername: ".$ilance->GPC['username']."\nPassword: ".$ilance->GPC['password']."\n===========================\n\nSincerely,\n".SITE_NAME." ".HTTP_SERVER;
                        send_email($ilance->GPC['email'], $phrase['_your_email_address_was_changed_at'] . ' ' . SITE_NAME, $email['message'], SITE_EMAIL);
                }
                else
                {
                        $notice = $phrase['_the_customers_profile_has_been_updated_with_new_changes_changes'];
                }
                
                print_action_success($notice, $ilpage['users'] . '?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['id']));
                exit();
        }
            
        // #### SUBSCRIPTION PLAN RE-ASSIGNMENT ################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-subscription' AND $ilance->GPC['id'] > 0)
        {
                $ilance->subscription = construct_object('api.subscription');
                $ilance->subscription->subscription_upgrade_process_admincp(intval($ilance->GPC['id']), intval($ilance->GPC['subscriptionid']), $ilance->GPC['txndescription'], $ilance->GPC['action']);
                
                print_action_success($phrase['_the_customer_has_been_reassigned_with_the_selected_subscription_plan'], $ilpage['users'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                exit();
        }
            
        // #### ADMIN ASSIGNS NEW SUBSCRIPTION EXEMPTION PERMISSION TO MEMBER
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-exemption' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                $ilance->subscription = construct_object('api.subscription');
                if ($ilance->subscription->construct_subscription_exemption(intval($ilance->GPC['id']), $ilance->GPC['accessname'], $ilance->GPC['exemptvalue'], $ilance->GPC['exemptcost'], $ilance->GPC['exemptdays'], $ilance->GPC['logic'], $ilance->GPC['description']))
                {
                        print_action_success($phrase['_the_customer_has_been_assigned_with_the_selected_subscription_permission_exemption'], $ilpage['users'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                        exit();
                }
                else 
                {
                        print_action_failed($phrase['_there_was_a_problem_with_the_action_selected_this_may_be_due_to_the_customer_not_having_sufficient_funds'], $ilpage['users'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                        exit();
                }
        }
            
        // #### MANUALLY AUTHORIZE CUSTOMER CREDIT CARD FOR USAGE ##############
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_authorize-creditcard' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['uid'] > 0)
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "creditcards
                        SET authorized = 'yes'
                        WHERE cc_id = '" . intval($ilance->GPC['id']) . "'
                        LIMIT 1
                ");
                
                if (isset($ilance->GPC['ccmgr']) AND $ilance->GPC['ccmgr'] == 1)
                {
                        print_action_success($phrase['_the_selected_credit_card_was_manually_authorized_verified_from_administration'], $ilpage['accounting'] . '?cmd=creditcards');
                        exit();
                }
                else
                {
                        print_action_success($phrase['_the_selected_credit_card_was_manually_authorized_verified_from_administration'], $ilpage['users'] . '?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['uid']));
                        exit();
                }
        }
            
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_unauthorize-creditcard' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0  AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "creditcards
                        SET authorized = 'no'
                        WHERE cc_id = '" . intval($ilance->GPC['id']) . "'
                        LIMIT 1
                ");

                if (isset($ilance->GPC['ccmgr']) AND $ilance->GPC['ccmgr'] == 1)
                {
                        print_action_success($phrase['_the_selected_credit_card_was_manually_unauthorized_and_this_customer_will_be_required_to_manually_verify'], $ilpage['accounting'] . '?cmd=creditcards');
                        exit();
                }
                else
                {
                        print_action_success($phrase['_the_selected_credit_card_was_manually_unauthorized_and_this_customer_will_be_required_to_manually_verify'], $ilpage['users'] . '?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['uid']));
                        exit();
                }
        }
            
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-creditcard' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['uid'] > 0)
        {
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "creditcards
                        WHERE cc_id = '" . intval($ilance->GPC['id']) . "'
                        LIMIT 1
                ");
                
                if (isset($ilance->GPC['ccmgr']) AND $ilance->GPC['ccmgr'] == 1)
                {
                        print_action_success($phrase['_the_selected_credit_card_was_removed_from_the_customers_profile_this_customer_will_be_required_to_verify_any'], $ilpage['accounting'] . '?cmd=creditcards');
                        exit();
                }
                else
                {
                        print_action_success($phrase['_the_selected_credit_card_was_removed_from_the_customers_profile_this_customer_will_be_required_to_verify_any'], $ilpage['users'] . '?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['uid']));
                        exit();
                }
        }
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-bankaccount' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['uid'] > 0)
        {
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "bankaccounts
                        WHERE bank_id = '" . intval($ilance->GPC['id']) . "'
                ");
                
                print_action_success($phrase['_the_selected_bank_account_was_removed_from_the_customers_profile'], $ilpage['users'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['uid']));
                exit();
        }
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-transaction' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['uid'] > 0)
        {
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "invoices
                        WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
                ");
                
                print_action_success($phrase['_the_selected_transaction_was_removed_from_the_transaction_system'], $ilpage['users'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['uid']));
                exit();
        }
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-transaction' AND $ilance->GPC['id'] > 0)
        {
                if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == "debit" AND $ilance->GPC['amount'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT available_balance, total_balance
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                $new_debit_amount = $ilance->GPC['amount'];
                                
                                $total_now = $res['total_balance'];
                                $avail_now = $res['available_balance'];
                                
                                $new_total_now = ($total_now - $new_debit_amount);
                                $new_avail_now = ($avail_now - $new_debit_amount);
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET total_balance = '" . $new_total_now . "',
                                        available_balance = '" . $new_avail_now . "'
                                        WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                                ");
                        
                                $ilance->accounting = construct_object('api.accounting');
                                $ilance->accounting->insert_transaction(
                                        0,
                                        0,
                                        0,
                                        intval($ilance->GPC['id']),
                                        0,
                                        0,
                                        0,
                                        $ilance->GPC['description'],
                                        sprintf("%01.2f", $new_debit_amount),
                                        sprintf("%01.2f", $new_debit_amount),
                                        'paid',
                                        'debit',
                                        'account',
                                        DATETIME24H,
                                        DATEINVOICEDUE,
                                        DATETIME24H,
                                        $ilance->GPC['custom'],
                                        0,
                                        0,
                                        0
                                );
                                    
                                $sqlemail = $ilance->db->query("
                                        SELECT email, username, first_name, last_name
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                                ");
                                if ($ilance->db->num_rows($sqlemail) > 0)
                                {
                                        $resemail = $ilance->db->fetch_array($sqlemail);
                                        
                                        $ilance->email = construct_dm_object('email', $ilance);
                
                                        $ilance->email->mail = $resemail['email'];
                                        $ilance->email->slng = fetch_user_slng(intval($ilance->GPC['id']));
                                        
                                        $ilance->email->get('account_debit_notification');		
                                        $ilance->email->set(array(
                                                '{{customer}}' => $resemail['username'],
                                                '{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
                                        ));
                                        
                                        $ilance->email->send();
                                        
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        
                                        $ilance->email->get('account_debit_notification_admin');		
                                        $ilance->email->set(array(
                                                '{{customer}}' => $resemail['username'],
                                                '{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
                                        ));
                                        
                                        $ilance->email->send();
                                        
                                        print_action_success($phrase['_a_new_transaction_debit_was_successfully_created_and_the_customers'], $ilpage['users'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                                        exit();
                                }
                        }
                }
                else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'credit' AND $ilance->GPC['amount'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT available_balance, total_balance
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                $new_credit_amount = $ilance->GPC['amount'];
                                
                                $total_now = $res['total_balance'];
                                $avail_now = $res['available_balance'];
                                
                                $new_total_now = ($total_now + $new_credit_amount);
                                $new_avail_now = ($avail_now + $new_credit_amount);
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET total_balance = '" . $new_total_now . "',
                                        available_balance = '" . $new_avail_now . "'
                                        WHERE user_id = '" . intval($ilance->GPC['id']) . "'");
                
                                $ilance->accounting = construct_object('api.accounting');
                                $ilance->accounting->insert_transaction(
                                        0,
                                        0,
                                        0,
                                        intval($ilance->GPC['id']),
                                        0,
                                        0,
                                        0,
                                        $ilance->GPC['description'],
                                        sprintf("%01.2f", $new_credit_amount),
                                        sprintf("%01.2f", $new_credit_amount),
                                        'paid',
                                        'credit',
                                        'account',
                                        DATETIME24H,
                                        DATEINVOICEDUE,
                                        DATETIME24H,
                                        $ilance->GPC['custom'],
                                        0,
                                        0,
                                        0
                                );
                    
                                $sqlemail = $ilance->db->query("
                                        SELECT email, username, first_name, last_name
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                                ");
                                if ($ilance->db->num_rows($sqlemail) > 0)
                                {
                                        $resemail = $ilance->db->fetch_array($sqlemail);
                                        
                                        $ilance->email = construct_dm_object('email', $ilance);
                
                                        $ilance->email->mail = $resemail['email'];
                                        $ilance->email->slng = fetch_user_slng(intval($ilance->GPC['id']));
                                        
                                        $ilance->email->get('account_credit_notification');		
                                        $ilance->email->set(array(
                                                '{{customer}}' => $resemail['username'],
                                                '{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
                                        ));
                                        
                                        $ilance->email->send();
                                        
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        
                                        $ilance->email->get('account_credit_notification_admin');		
                                        $ilance->email->set(array(
                                                '{{customer}}' => $resemail['username'],
                                                '{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
                                        ));
                                        
                                        $ilance->email->send();
                                        
                                        print_action_success($phrase['_a_new_transaction_credit_was_successfully_created_and_the_customers'], $ilpage['users'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                                        exit();						
                                }
                        }
                }
        }
		################################################################
		######  Create Advance Payment For Users 			############
		######  Herakle Murugan Coding Oct 28 Starts Here 	############
		################################################################
		
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-advance' AND $ilance->GPC['id'] > 0)
                {
                       
					   if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                       		 $validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
						}
					   
						$today = date('Y-m-d',strtotime(DATETIME24H));
					   //id, amount, date_made, interest, description, user_id
					    $advance = $ilance->db->query("INSERT INTO ".DB_PREFIX."user_advance VALUES (0,'".$ilance->GPC['amount']."','". $validdate."','".$ilance->GPC['interestrate']."','".$ilance->GPC['description']."','".$ilance->GPC['id']."','unpaid')");
						$advanceid =$ilance->db->insert_id();
						
						if($validdate == $today)
						{
							$sql = $ilance->db->query("
									SELECT available_balance, total_balance
									FROM " . DB_PREFIX . "users
									WHERE user_id = '" . intval($ilance->GPC['id']) . "'
							");
							if ($ilance->db->num_rows($sql) > 0)
							{
									$res = $ilance->db->fetch_array($sql);
									$new_credit_amount = $ilance->GPC['amount'];
									
									$total_now = $res['total_balance'];
									$avail_now = $res['available_balance'];
									
									$new_total_now = ($total_now + $new_credit_amount);
									$new_avail_now = ($avail_now + $new_credit_amount);
									
									$ilance->db->query("
											UPDATE " . DB_PREFIX . "users
											SET total_balance = '" . $new_total_now . "',
											available_balance = '" . $new_avail_now . "'
											WHERE user_id = '" . intval($ilance->GPC['id']) . "'");	
									$ilance->db->query("
											UPDATE " . DB_PREFIX . "user_advance
											SET statusnow = 'paid'											
											WHERE id = '" . $advanceid . "'");	
					
									$ilance->accounting = construct_object('api.accounting');
									$ilance->accounting->insert_transaction(
											0,
											0,
											0,
											intval($ilance->GPC['id']),
											0,
											0,
											0,
											$ilance->GPC['description'],
											sprintf("%01.2f", $new_credit_amount),
											sprintf("%01.2f", $new_credit_amount),
											'paid',
											'credit',
											'account',
											DATETIME24H,
											DATETIME24H,
											DATETIME24H,
											$ilance->GPC['custom'],
											0,
											0,
											0
									);
						
									$sqlemail = $ilance->db->query("
											SELECT email, username, first_name, last_name
											FROM " . DB_PREFIX . "users
											WHERE user_id = '" . intval($ilance->GPC['id']) . "'
									");
									if ($ilance->db->num_rows($sqlemail) > 0)
									{
											$resemail = $ilance->db->fetch_array($sqlemail);
											
											$ilance->email = construct_dm_object('email', $ilance);
					
											$ilance->email->mail = $resemail['email'];
											$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['id']));
											
											$ilance->email->get('account_credit_notification');		
											$ilance->email->set(array(
													'{{customer}}' => $resemail['username'],
													'{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
											));
											
											$ilance->email->send();
											
											$ilance->email->mail = SITE_EMAIL;
											$ilance->email->slng = fetch_site_slng();
											
											$ilance->email->get('account_credit_notification_admin');		
											$ilance->email->set(array(
													'{{customer}}' => $resemail['username'],
													'{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
											));
											
											$ilance->email->send();
											
											print_action_success($phrase['_a_new_transaction_credit_was_successfully_created_and_the_customers'], $ilpage['users'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
											exit();						
									}
							}
							
							
						}
						print_action_success("New Advance Trasaction Completed", $ilpage['users'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
						exit();		
                }
		
		############# Herakle Murugan Coding End Here ########################
        else
        {
                $ilance->subscription = construct_object('api.subscription');
            
             // Murugan Changes on Jan 06 For Switch User.   
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'switchuser' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
			{
				$sql = $ilance->db->query("
				SELECT u.*, su.roleid, su.subscriptionid, su.active, sp.cost, c.currency_name, c.currency_abbrev, l.languagecode
				FROM " . DB_PREFIX . "users AS u
				LEFT JOIN " . DB_PREFIX . "subscription_user su ON u.user_id = su.user_id
				LEFT JOIN " . DB_PREFIX . "subscription sp ON su.subscriptionid = sp.subscriptionid
				LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
				LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
				WHERE u.user_id = '" . intval($ilance->GPC['id']) . "'
				GROUP BY username
				LIMIT 1
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$userinfo = $ilance->db->fetch_array($sql, DB_ASSOC);
					build_user_session($userinfo);
					
					refresh(HTTP_SERVER);
					exit();
				}
			}
            
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-customer' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
                {	
                        $area_title = $phrase['_viewing_customer_profile_id'] . '' . intval($ilance->GPC['id']);
                        $page_title = SITE_NAME.' - ' . $phrase['_viewing_customer_profile_id'] . '' . intval($ilance->GPC['id']);
                        
                       // $subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['users'], $ilpage['users'], $_SESSION['ilancedata']['user']['slng']);
                        
                        $show['show_update'] = true;
                        $show['show_search'] = false;
                        
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $res['username'] = stripslashes($res['username']);
                                        $res['first_name'] = stripslashes($res['first_name']);
                                        $res['last_name'] = stripslashes($res['last_name']);
                                        $res['phone'] = stripslashes($res['phone']);
                                        $res['address'] = stripslashes($res['address']);
                                        $res['address2'] = stripslashes($res['address2']);
                                        $res['city'] = stripslashes(ucfirst($res['city']));
                                        $res['zip_code'] = stripslashes(mb_strtoupper($res['zip_code']));
                                        
                                        if ($res['iprestrict'] == '1')
                                        {
                                                $res['restrict'] = '<input type="checkbox" name="iprestrict" value="1" checked="checked" />';
                                        }
                                        else
                                        {
                                                $res['restrict'] = '<input type="checkbox" name="iprestrict" value="1" />';
                                        }
                                                        
                                        $res['added'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        $res['lastseen'] = print_date($res['lastseen'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        
                                        $customername = "(" . $res['username'] . ")";
                                        
                                        ## DYNAMIC JAVASCRIPT COUNTRY PULLDOWN
                                        $sql_loc = $ilance->db->query("
                                                SELECT location_" . $_SESSION['ilancedata']['user']['slng'] . " AS location
                                                FROM " . DB_PREFIX . "locations
                                                WHERE locationid = '" . $res['country'] . "'
                                        ");
                                        $res_loc = $ilance->db->fetch_array($sql_loc);
                                        
                                        $jscity = $res['city'];
                                        
                                        $countryid = fetch_country_id($res_loc['location'], $_SESSION['ilancedata']['user']['slng']);
                                        $res['country_js_pulldown'] = construct_country_pulldown($countryid, $res_loc['location'], 'country', false, 'state');
                                        $res['state_js_pulldown'] = '<div id="stateid" style="height:20px">' . construct_state_pulldown($countryid, $res['state'], 'state') . '</div>';
                                        
                                        $roleid = fetch_user_roleid($res['user_id']);
                                        $roleselected = isset($roleid) ? intval($roleid) : '';
                                        $rolepulldown = print_role_pulldown($roleselected, '', 0);
                                            
                                        switch ($res['status'])
                                        {
                                                case 'active':
                                                {
                                                        $sel1 = 'selected="selected"';
                                                        $sel2 = $sel3 = $sel4 = $sel5 = $sel6 = '';
                                                        break;
                                                }
                                                case 'suspended':
                                                {
                                                        $sel1 = $sel3 = $sel4 = $sel5 = $sel6 = '';
                                                        $sel2 = 'selected="selected"';
                                                        break;
                                                }
                                                case 'unverified':
                                                {
                                                        $sel1 = $sel2 = $sel4 = $sel5 = $sel6 = '';
                                                        $sel3 = 'selected="selected"';
                                                        break;
                                                }       
                                                case 'banned':
                                                {
                                                        $sel1 = $sel2 = $sel3 = $sel5 = $sel6 = '';
                                                        $sel4 = 'selected="selected"';
                                                        break;
                                                }
                                                case 'cancelled':
                                                {
                                                        $sel1 = $sel2 = $sel3 = $sel4 = $sel6 = '';
                                                        $sel5 = 'selected="selected"';
                                                        break;
                                                }
                                                case 'moderated':
                                                {
                                                        $sel1 = $sel2 = $sel3 = $sel4 = $sel5 = '';
                                                        $sel6 = 'selected="selected"';
                                                        break;
                                                }
                                        }
                                        
                                        $res['userstatus'] = '<select name="status" style="font-family: Verdana">';
                                        $res['userstatus'] .= '<option value="active" ' . $sel1 . '>' . $phrase['_active_can_signin'] . '</option>';
                                        $res['userstatus'] .= '<option value="suspended" ' . $sel2 . '>' . $phrase['_suspended_cannot_signin'] . '</option>';
                                        $res['userstatus'] .= '<option value="unverified" ' . $sel3 . '>' . $phrase['_unverified_email_cannot_signin'] . '</option>';
                                        $res['userstatus'] .= '<option value="banned" ' . $sel4 . '>' . $phrase['_banned_cannot_signin'] . '</option>';
                                        $res['userstatus'] .= '<option value="cancelled" ' . $sel5 . '>' . $phrase['_cancelled_can_signin'] . '</option>';
                                        $res['userstatus'] .= '<option value="moderated" ' . $sel6 . '>' . $phrase['_moderated_cannot_signin'] . '</option>';
                                        $res['userstatus'] .= '</select>';
                                        
                                        if ($res['isadmin'])
                                        {
                                                $res['isadministrator'] = '<input type="checkbox" name="isadmin" id="isadmin" value="1" checked="checked" />';
                                        }
                                        else
                                        {
                                                $res['isadministrator'] = '<input type="checkbox" name="isadmin" id="isadmin" value="1" onclick="alert_js(\'' . $phrase['_please_remember_enabling_this_user_as_an_admin_will_provide_access_to_let_this_profile_signin_to_the_admin_control_panel_interface'] . '\')" />';
                                        }
					
					if ($res['gender'] == '')
					{
						$res['cb_gender_undecided'] = 'checked="checked"';
						$res['cb_gender_male'] = '';
						$res['cb_gender_female'] = '';
					}
					else
					{
						if ($res['gender'] == 'male')
						{
							$res['cb_gender_undecided'] = '';
							$res['cb_gender_male'] = 'checked="checked"';
							$res['cb_gender_female'] = '';
						}
						else if ($res['gender'] == 'female')
						{
							$res['cb_gender_undecided'] = '';
							$res['cb_gender_male'] = '';
							$res['cb_gender_female'] = 'checked="checked"';
						}
					}
                                        
                                        $profile[] = $res;
                                }
                        }
                                
                        $sql = $ilance->db->query("
                                SELECT account_number, available_balance, total_balance
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        $res['accountnumber'] = $res['account_number'];
                                        $res['availablebalance'] = $ilance->currency->format($res['available_balance']);
                                        $res['totalbalance'] = $ilance->currency->format($res['total_balance']);
                                        $profileaccount[] = $res;
                                }
                        }
                        
                        $currency = print_left_currency_symbol();
                        
                        // #### TRANSACTIONS HISTORY ###########################
                        if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
                        {
                                $ilance->GPC['page'] = 1;
                        }
                        else
                        {
                                $ilance->GPC['page'] = intval($ilance->GPC['page']);
                        }
                        
                        $rowlimit = $ilconfig['globalfilters_maxrowsdisplay'];
                        $limit = ' ORDER BY invoiceid DESC LIMIT '.(($ilance->GPC['page']-1)*$rowlimit).','.$rowlimit;
                        
                        $cntexe = $ilance->db->query("
                                SELECT COUNT(*) AS number FROM " . DB_PREFIX . "invoices
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "' 
                                    AND status != 'scheduled'
                        ");
            
                        $cntarr = $ilance->db->fetch_array($cntexe);
                        $number = (int)$cntarr['number'];
                        
                        $counter = ($ilance->GPC['page']-1)*$rowlimit;
                        $row_count = 0;
                        
                        $res = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "invoices 
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "' 
                                    AND status != 'scheduled' 
                                $limit
                        ");
                        if ($ilance->db->num_rows($res) > 0)
                        {
                                $altrows = 0;
                                while ($row = $ilance->db->fetch_array($res))
                                {
                                        $altrows++;
                                        if (floor($altrows/2) == ($altrows/2))
                                        {
                                                $row['class'] = 'alt2';
                                        }
                                        else
                                        {
                                                $row['class'] = 'alt1';
                                        }
                                        $row['createdate'] = print_date($row['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                        if ($row['duedate'] == "0000-00-00 00:00:00")
                                        {
                                                $row['duedate'] = '-';
                                        }
                                        else
                                        {
                                                $row['duedate'] = print_date($row['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                        }
                                        
                                        if ($row['paiddate'] == "0000-00-00 00:00:00")
                                        {
                                                $row['paiddate'] = '-';
                                        }
                                        else
                                        {
                                                $row['paiddate'] = print_date($row['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                        }
                                        
                                        $row['description'] = stripslashes($row['description']);
                                        if ($row['amount'] > 0)
                                        {
                                                $row['amount'] = $ilance->currency->format($row['totalamount']);
                                        }
                                        else if ($row['amount'] == 0)
                                        {
                                                $row['amount'] = $phrase['_free'];
                                        }
                                        
                                        if ($row['paid'] > 0)
                                        {
                                                $row['paid'] = $ilance->currency->format($row['paid']);
                                        }
                                        else if ($row['paid'] == 0)
                                        {
                                                $row['paid'] = $ilance->currency->format(0);
                                        }
                                        
                                        $row['method'] = print_paymethod_icon($row['paymethod']);
                                        
                                        if ($row['status'] == 'unpaid')
                                        {
                                                $row['action'] = "<a href='".$ilpage['users']."?subcmd=_remove-transaction&amp;id=".$row['invoiceid']."&amp;uid=" . intval($ilance->GPC['id']) . "' target='_self' onClick=\"return confirm('".$phrase['_please_take_a_moment_to_confirm_your_action']."')\"><img src='" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/delete.gif' border=0 alt=''></a>";
                                                $row['status'] = $phrase['_pending'];
                                        }
                                        else
                                        {
                                                $row['action'] = "<a href='".$ilpage['users']."?subcmd=_remove-transaction&amp;id=".$row['invoiceid']."&amp;uid=" . intval($ilance->GPC['id']) . "' target='_self' onClick=\"return confirm('".$phrase['_please_take_a_moment_to_confirm_your_action']."')\"><img src='" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/delete.gif' border=0 alt=''></a>";
                                        }
                                        $transaction_rows[] = $row;
                                        $row_count++;
                                }
                        }
                        else
                        {
                            $show['no_rows_returned'] = true;
                        }
                        
                        $transactionsprevnext = print_pagnation($number, $rowlimit, $ilance->GPC['page'], $counter, $ilpage['users']."?subcmd=_update-customer&amp;id=".intval($ilance->GPC['id']));
                        
                        $sqlpaidplan = $ilance->db->query("
                                SELECT subscriptionid
                                FROM " . DB_PREFIX . "subscription_user
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sqlpaidplan) > 0)
                        {
                                $resplan = $ilance->db->fetch_array($sqlpaidplan);
                                $whichplan = $ilance->db->query("
                                        SELECT cost
                                        FROM " . DB_PREFIX . "subscription
                                        WHERE subscriptionid = '" . $resplan['subscriptionid'] . "'
                                ");
                                if ($ilance->db->num_rows($whichplan) > 0)
                                {
                                        $respaid = $ilance->db->fetch_array($whichplan);
                                        $thiscost = $respaid['cost'];
                                        
                                        if ($thiscost > 0)
                                        {
                                                $paidplan = 1;
                                        }
                                        else
                                        {
                                                $paidplan = 0;
                                        }
                                }
                        }
                        
                        $SQL = "
                                SELECT UNIX_TIMESTAMP(u.renewdate) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS countdown, s.title, s.description, s.cost, s.length, s.units, u.subscriptionid, u.user_id, u.paymethod, u.startdate, u.renewdate, u.active, i.invoiceid, i.subscriptionid, i.paiddate
                                FROM " . DB_PREFIX . "subscription as s,
                                " . DB_PREFIX . "subscription_user as u,
                                " . DB_PREFIX . "invoices as i
                                WHERE u.user_id = '" . intval($ilance->GPC['id']) . "'
                                    AND u.subscriptionid = s.subscriptionid
                        ";
                        if (isset($paidplain) AND $paidplan)
                        {
                                $SQL .= "
                                            AND u.user_id = i.user_id
                                            AND u.subscriptionid = i.subscriptionid
                                        ORDER BY u.renewdate DESC
                                        LIMIT 1
                                ";
                        }
                        else
                        {
                                $SQL .= "
                                        ORDER BY u.renewdate DESC 
                                        LIMIT 1
                                ";
                        }
                                
                        $res = $ilance->db->query($SQL);
                        if ($ilance->db->num_rows($res) > 0)
                        {
                                while ($row = $ilance->db->fetch_array($res))
                                {
                                        $clock_js = "";
                                        
                                        $row['subscriptionid'] = $row['subscriptionid'];
                                        $row['title'] = stripslashes($row['title']);
                                        $row['description'] = stripslashes($row['description']);
                                        $row['startdate'] = print_date($row['startdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                        $row['renewdate'] = print_date($row['renewdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                        $renewdate_workwith = $row['renewdate'];
                                        $raw_cost = $row['cost'];
                                        if ($row['cost'] > 0)
                                        {
                                                $row['cost'] = $ilance->currency->format($row['cost']);
                                        }
                                        else if ($row['cost'] == 0)
                                        {
                                                $row['cost'] = $phrase['_free'];
                                        }
                                        
                                        if ($row['active'] == 'yes')
                                        {
                                                $row['status'] = $phrase['_active'];
                                                $row['action'] = '--';
                                                $renewal_countdown = 1;
                                        }
                                        else
                                        {
                                                $row['startdate'] = '--';
                                                $row['renewdate'] = '--';
                                                $row['status'] = $phrase['_inactive'];
                                                $renewal_countdown = 0;
						
                                                if ($paidplan == 1)
                                                {
                                                        $row['action'] = "<a href='" . HTTPS_SERVER . $ilpage['invoicepayment'] . "?id=" . $row['invoiceid'] . "'><img src='" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/invoice.gif' border='0' alt='".$phrase['_pay_invoice']."' /></a>";
                                                }
                                                else
                                                {
                                                        $row['status'] = $phrase['_inactive'];
                                                }
                                        }
                                        
                                        $row['paymethod'] = print_paymethod_icon($row['paymethod']);
                                        
                                        $future_date = $renewdate_workwith;
                                        $maxdate = getdate(strtotime ($future_date));
                                        $mindate = getdate(strtotime (DATETODAY));
                                        $difference = $maxdate[0]-$mindate[0];
                                        $difference = $difference/24;
                                        $difference = $difference/60;
                                        $difference = $difference/60;
                                        $subscription_length = $ilance->subscription->subscription_length($row['units'], $row['length']);
                                        $length = $subscription_length;
                                        $length_left = $difference;
                                        $length_paid = $raw_cost;
                                        $row['units'] = print_unit($row['units']);
                                        $row['cost_daily'] = $ilance->currency->format(($length_paid / $length));
                                        $row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                        $subscription_rows[] = $row;
                                        $row_count++;
                                }
                                
                                $show['no_subscription_rows'] = false;
                        }
                        else
                        {
                                $show['no_subscription_rows'] = true;
                        }
                                
                        $sqlexempt = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "subscription_user_exempt
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sqlexempt) > 0)
                        {
                                $row_count = 0;
                                while ($resexempt = $ilance->db->fetch_array($sqlexempt))
                                {
                                        $resexempt['cost'] = $ilance->currency->format($ilance->db->fetch_field(DB_PREFIX."invoices","invoiceid=".$resexempt['invoiceid'],"amount"));
                                        if ($resexempt['active'])
                                        {
                                                $resexempt['status'] = $phrase['_active'];
                                                $resexempt['action'] = '<a href="' . $ilpage['users'] . '?subcmd=_remove-exemption&amp;id='.$resexempt['exemptid'].'&amp;uid='.$resexempt['user_id'].'" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')">'.$phrase['_remove'].'</a>';
                                        }
                                        else
                                        {
                                                $resexempt['status'] = $phrase['_expired'];
                                                $resexempt['action'] = '<a href="' . $ilpage['users'] . '?subcmd=_remove-exemption&amp;id='.$resexempt['exemptid'].'&amp;uid='.$resexempt['user_id'].'" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')">'.$phrase['_remove'].'</a>';
                                        }
                                        $resexempt['accessvalue'] = $resexempt['value'];
                                        $resexempt['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                        $resexempt['exemptfrom'] = print_date($resexempt['exemptfrom'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                        $resexempt['exemptto'] = print_date($resexempt['exemptto'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                        $exemptions[] = $resexempt;
                                        $row_count++;
                                }
                        }
                        else
                        {
                                $show['no_exemptions'] = true;
                        }
                        
                        #######################
                        ## CREDIT CARDS ON FILE
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "creditcards
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $row_count = 0;
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        $res['ccnum'] = substr_replace($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), 'XX XXXX XXXX ', 2 , (mb_strlen($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) - 6));
                                        $res['username'] = stripslashes($res['name_on_card']);
                                        $res['phone'] = $res['phone_of_cardowner'];
                                        $res['expiry'] = $res['creditcard_expiry'];
                                        
                                        if ($res['authorized'] == 'yes')
                                        {
                                                $res['status'] = ucfirst($res['creditcard_status']) . ' &amp; Authorized';
                                                $res['authenticated'] = '<a href="' . $ilpage['users'] . '?subcmd=_unauthorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . intval($ilance->GPC['id']) . '&amp;ccmgr=1" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to unauthorize credit card" border="0"></a>';
                                        }
                                        else
                                        {
                                                $res['status'] = ucfirst($res['creditcard_status']) . ' &amp; Unauthorized';
                                                $res['authenticated'] = '<a href="' . $ilpage['users'] . '?subcmd=_authorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . intval($ilance->GPC['id']) . '&amp;ccmgr=1" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to authorize credit card" border="0"></a>';
                                        }
                                        
                                        $res['address'] = $res['card_billing_address1'].", ";
                                        
                                        if ($res['card_billing_address2'] != "")
                                        {
                                                $res['address'] .= $res['card_billing_address2'].", ";
                                        }
                                        
                                        $res['address'] .= ucfirst($res['card_city']).", ".ucfirst($res['card_state']).", ".mb_strtoupper($res['card_postalzip']).", ";
                                        $res['address'] .= stripslashes($ilance->db->fetch_field(DB_PREFIX."locations","locationid=".$ilance->db->fetch_field(DB_PREFIX."creditcards","cc_id=".$res['cc_id'],"card_country"),"location_eng"));
                                        
                                        if ($res['creditcard_type'] == "visa")
                                        {
                                                $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/visa.gif" border="0" alt="" />';
                                        }
                                        else if ($res['creditcard_type'] == "mc")
                                        {
                                                $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mc.gif" border="0" alt="" />';
                                        }
                                        else if ($res['creditcard_type'] == "amex")
                                        {
                                                $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amex.gif" border="0" alt="" />';
                                        }
                                        else if ($res['creditcard_type'] == "disc")
                                        {
                                                $res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/disc.gif" border="0" alt="" />';
                                        }
                                        
                                        $res['remove'] = '<a href="' . $ilpage['users'] . '?subcmd=_remove-creditcard&amp;id='.$res['cc_id'].'&amp;uid='.intval($ilance->GPC['id']).'" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
                                        $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                        $creditcards[] = $res;
                                        $row_count++;
                                }
                        }
                        else
                        {
                                $show['no_creditcards'] = true;
                        }
                        
                        ########################
                        ## BANK ACCOUNTS ON FILE
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "bankaccounts
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $row_count = 0;
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        $res['bankname'] = stripslashes($res['beneficiary_bank_name']);
                                        $res['accountnum'] = $res['beneficiary_account_number'];
                                        $res['accounttype'] = ucfirst($res['bank_account_type']);
                                        $res['address'] = stripslashes($res['beneficiary_bank_address_1']);
                                        $res['swiftnum'] = $res['beneficiary_bank_routing_number_swift'];
                                        if ($res['beneficiary_bank_address_2'] != "")
                                        {
                                                $res['address'] .= ", ".stripslashes($res['beneficiary_bank_address_2']); 
                                        }
                                        $res['city'] = ucfirst($res['beneficiary_bank_city']);
                                        $res['zipcode'] = mb_strtoupper($res['beneficiary_bank_zipcode']);
                                        $res['country'] = stripslashes($ilance->db->fetch_field(DB_PREFIX."locations","locationid=".$ilance->db->fetch_field(DB_PREFIX."bankaccounts","bank_id=".$res['bank_id'],"beneficiary_bank_country_id"),"location_eng"));
                                        $res['currency'] = $ilance->db->fetch_field(DB_PREFIX."currency","currency_id=".$ilance->db->fetch_field(DB_PREFIX."bankaccounts","bank_id=".$res['bank_id'],"destination_currency_id"),"currency_abbrev");
                                        $res['remove'] = '<a href="' . $ilpage['users'] . '?subcmd=_remove-bankaccount&amp;id='.$res['bank_id'].'&amp;uid='.intval($ilance->GPC['id']).'" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
                                        $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                        $bankaccounts[] = $res;
                                        $row_count++;
                                }
                        }
                        else
                        {
                                $show['no_bankaccounts'] = true;
                        }
						############################################
						###  User Advance Payment Summary      #####
						###  Developed By Murugan Oct 28 2010  #####
						###  Company Herakle     			   #####
						############################################
						
						$sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "user_advance
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                        ");
						 if ($ilance->db->num_rows($sql) > 0)
                        {
                                $row_count = 0;
                                while ($res = $ilance->db->fetch_array($sql))
                                {
									 $advance[] = $res;
                                     $row_count++;
                                }
                        }
                       else
                        {
                                $show['no_rows_advance'] = true;
                        }
						#################### Murugan Coding End Here ###############
						
                }
                else
                {
                        $area_title = $phrase['_subscriber_management'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_subscriber_management'];
                        
                        ($apihook = $ilance->api('admincp_subscriber_management')) ? eval($apihook) : false;
                        
                        $subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['users'], $ilpage['users'], $_SESSION['ilancedata']['user']['slng']);
                        
                        $show['show_update'] = false;
                        $show['show_search'] = true;
                        
                        $customername = '';
                        $reportrange = '<select name="rangepast" style="font-family: verdana"><option value="-1 day"';
                        if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 day")
                        {
                                $reportrange .= ' selected'; 
                        }
                        $reportrange .= '>'.$phrase['_the_past_day'].'</option><option value="-1 week"';
                        if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 week")
                        {
                                $reportrange .= ' selected'; 
                        }
                        $reportrange .= '>'.$phrase['_the_past_week'].'</option><option value="-1 month"';
                        if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 month")
                        {
                                $reportrange .= ' selected';
                        }
                        $reportrange .= '>'.$phrase['_the_past_month'].'</option><option value="-1 year"'; 
                        if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 year")
                        {
                                $reportrange .= ' selected; ';
                        }
                        $reportrange .= '>'.$phrase['_the_past_year'].'</option></select>';
                }
                
                $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
                $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
        
                // #### ADMIN SEARCHING SUBSCRIBERS ####################################
                if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search')
                {
                        $show['advancedsearch'] = $show['showsearch'] = true;
                            
                        $filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : 'user_id';
                        $filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->GPC['filtervalue'] : '';
                        $orderby = (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby'])) ? $ilance->GPC['orderby'] : 'desc';
                        $orderlimit = ' ORDER BY ' . $filterby . ' ' . mb_strtoupper($orderby) . ' LIMIT ' . (($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplaysubscribers']).','.$ilconfig['globalfilters_maxrowsdisplaysubscribers'];
                            
                        // searching via specific user status only
                        $where = "WHERE user_id != '' ";
                        if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']))
                        {
                                $where .= "AND status = '".$ilance->db->escape_string($ilance->GPC['status'])."'";
                        }
                        if (!empty($filtervalue) AND !empty($filterby))
                        {
                                $where .= "AND " . $filterby . " = '" . $filtervalue . "'";
                        }
                        
                        $scriptpage = $ilpage['users'] . "?cmd=search";
                        foreach ($ilance->GPC AS $cmd => $value)
                        {
                                if (!empty($cmd) AND !empty($value) AND $cmd != 'submit' AND $cmd != 'cmd' AND $cmd != 'page')
                                {
                                        $scriptpage .= '&amp;' . $cmd . '=' . $value;
                                }
                        }
                        
                        $sqlsearchcustomers = $ilance->db->query("
                                SELECT user_id, username, first_name, last_name, email, phone, city, state, zip_code, status, available_balance, total_balance, isadmin, permissions
                                FROM " . DB_PREFIX . "users
                                $where
                                $orderlimit
                        ");
                        
                        $sqlsearchcustomers2 = $ilance->db->query("
                                SELECT user_id, username, first_name, last_name, email, phone, city, state, zip_code, status, available_balance, total_balance, isadmin, permissions
                                FROM " . DB_PREFIX . "users
                                $where
                        ");
                        $number = (int)$ilance->db->num_rows($sqlsearchcustomers2);
                        if ($ilance->db->num_rows($sqlsearchcustomers) > 0)
                        {
                                $row_count = 0;
                                while ($res = $ilance->db->fetch_array($sqlsearchcustomers, DB_ASSOC))
                                {
                                        $res['edit'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
                                        $res['remove'] = '<a href="' . $ilpage['users'] . '?subcmd=deleteuser&amp;id=' . $res['user_id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
                                        $res['balance'] = $ilance->currency->format($res['available_balance']);
                                        $res['subscription'] = $ilance->subscription->fetch_subscription_plan($res['user_id']);
                                        $res['role'] = print_role(fetch_user_roleid($res['user_id']));
                                        $res['action'] = '<input type="checkbox" name="user_id[]" value="' . $res['user_id'] . '" id="members_' . $res['user_id'] . '" />';
                                        $res['isadmin'] = ($res['isadmin']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="'.$phrase['_yes'].'" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="'.$phrase['_no'].'" />';
                                        $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                        $res['status'] = ($res['status'] == 'active') ? '<a href="' . $ilpage['users'] . '?subcmd=suspenduser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to suspend customer (cannot log-in)" border="0"></a>' : '<a href="' . $ilpage['users'] . '?subcmd=activateuser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to re-activate customer (can log in)" border="0"></a>';
					
					// quick view of items/service bought and sold in marketplace
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$res['bought'] = '<div class="smaller gray"><span class="black">' . fetch_bought_count($res['user_id'], 'product') . '</span> ' . $phrase['_items_lower'] . '</div>';
					}
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
					{
						$res['bought'] .= '<div class="smaller gray" style="padding-top:3px"><span class="black">' . fetch_bought_count($res['user_id'], 'service') . '</span> ' . $phrase['_services_lower'] . '</div>';
					}
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$res['sold'] = '<div class="smaller gray"><span class="black">' . fetch_sold_count($res['user_id'], 'product') . '</span> ' . $phrase['_items_lower'] . '</div>';
					}					
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
					{
						$res['sold'] .= '<div class="smaller gray" style="padding-top:3px"><span class="black">' . fetch_sold_count($res['user_id'], 'service') . '</span> ' . $phrase['_services_lower'] . '</div>';
					}
                                        $searchcustomers[] = $res;
                                        $row_count++;
                                }
                                
                                $show['no_customers'] = true;
                                
                                $searchprevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);
                                $prevnext = "";
                        }
                        else
                        {
                                $show['advancedsearch'] = true;
                                $show['no_searchcustomers'] = true;
                        }
                }
                else
                {
                        // field display order
                        $displayorderfields = array('asc', 'desc');
                        $displayorder = '&amp;displayorder=asc';
                        $realdisplayorder = $displayorder;
                        $displayordersql = 'ASC';
                        if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
                        {
                                $realdisplayorder = '&amp;displayorder=asc';
                                $displayorder = '&amp;displayorder=desc';
                        }
                        else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
                        {
                                $realdisplayorder = '&amp;displayorder=desc';
                                $displayorder = '&amp;displayorder=asc';
                        }
                        if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields))
                        {
                                $displayordersql = mb_strtoupper($ilance->GPC['displayorder']);
                        }
                        
                        // ordering by display logic
                        $orderbyfields = array('total_balance', 'user_id', 'username');
                        $orderby = '';
                        $orderbysql = 'user_id';
                        if (isset($ilance->GPC['orderby']) AND in_array($ilance->GPC['orderby'], $orderbyfields))
                        {
                                $orderbysql = mb_strtoupper($ilance->GPC['orderby']);
                                $orderby = '';
                        }
                        
                        $scriptpage = $ilpage['users'] . '?cmd=listing' . $displayorder . $orderby;
                        $scriptpageprevnext = $ilpage['users'] . '?cmd=listing' . $realdisplayorder . $orderby;
                        $show['showsearch'] = false;
                
                        $sql = $ilance->db->query("
                                SELECT user_id, username, first_name, last_name, email, phone, city, state, zip_code, status, available_balance, total_balance, isadmin, permissions
                                FROM " . DB_PREFIX . "users
                                ORDER BY $orderbysql
                                $displayordersql
                                LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']
                        );
                        
                        $sql2 = $ilance->db->query("
                                SELECT user_id, username, first_name, last_name, email, phone, city, state, zip_code, status, available_balance, total_balance, isadmin, permissions
                                FROM " . DB_PREFIX . "users
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $number = (int)$ilance->db->num_rows($sql2);
                                $row_count = 0;
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        if ($res['status'] == 'moderated')
                                        {
                                                $res['class'] = '#FFF7F9';   
                                        }
                                        else
                                        {
                                                $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                        }
					
                                        if ($res['status'] == 'active')
                                        {
                                                $res['status'] = '<a href="' . $ilpage['users'] . '?subcmd=suspenduser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to suspend customer (cannot log-in)" border="0"></a>';
                                        }
                                        else
                                        {
                                                $res['status'] = '<a href="' . $ilpage['users'] . '?subcmd=activateuser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to re-activate customer (can log in)" border="0"></a>';
                                        }
					
                                        if ($res['isadmin'])
                                        {
                                                    $res['isadmin'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="'.$phrase['_yes'].'" />';
                                        }
                                        else
                                        {
                                                    $res['isadmin'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="'.$phrase['_no'].'" />';
                                        }
					
                                        $res['edit'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
                                        $res['remove'] = '<a href="' . $ilpage['users'] . '?subcmd=deleteuser&amp;id=' . $res['user_id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
					
                                        if ($res['available_balance'] > 0)
                                        {
                                                $res['balance'] = $ilance->currency->format($res['available_balance']);
                                        }
                                        else
                                        {
                                                $res['balance'] = '<div class="gray">' . $phrase['_none'] . '</div>';
                                        }
                                        $res['subscription'] = $ilance->subscription->fetch_subscription_plan($res['user_id']);
                                        $res['role'] = print_role(fetch_user_roleid($res['user_id']));
                                        $res['action'] = '<input type="checkbox" name="user_id[]" value="' . $res['user_id'] . '" id="members_' . $res['user_id'] . '" />';
					// quick view of items/service bought and sold in marketplace
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$res['bought'] = '<div class="smaller gray"><span class="black">' . fetch_bought_count($res['user_id'], 'product') . '</span> ' . $phrase['_items_lower'] . '</div>';
					}
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
					{
						$res['bought'] .= '<div class="smaller gray" style="padding-top:3px"><span class="black">' . fetch_bought_count($res['user_id'], 'service') . '</span> ' . $phrase['_services_lower'] . '</div>';
					}
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$res['sold'] = '<div class="smaller gray"><span class="black">' . fetch_sold_count($res['user_id'], 'product') . '</span> ' . $phrase['_items_lower'] . '</div>';
					}					
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
					{
						$res['sold'] .= '<div class="smaller gray" style="padding-top:3px"><span class="black">' . fetch_sold_count($res['user_id'], 'service') . '</span> ' . $phrase['_services_lower'] . '</div>';
					}
					$res['login'] = '<a href="' . $ilpage['users'] . '?subcmd=switchuser&amp;id=' . $res['user_id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/picture_blue.gif" alt="' . $phrase['_switch_to_another_user'] . '" border="0"></a>';
                                        $customers[] = $res;
                                        $row_count++;
                                }
                                
                                $searchprevnext = '';
                                $prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
                        }
                        else
                        {
                                $show['no_customers'] = true;
                        }
                }
                
                // #### UPDATE SUBSCRIBER LOGIC ########################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-customer')
                {
                        $ilance->GPC['id'] = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
                        
                        // custom registration questions
                        $ilance->registration_questions = construct_object('api.registration_questions');
                        $customquestions = $ilance->registration_questions->construct_register_questions(0, 'updateprofileadmin', intval($ilance->GPC['id']));
                }
            
                // subscription plan pulldown menu
                $subscription_plan_pulldown = $ilance->subscription->plans_pulldown();
                $subscription_permissions_pulldown = $ilance->subscription->exemptions_pulldown();
                
                // construct countries / states pulldown
                $jscity = $ilconfig['registrationdisplay_defaultcity'];
                $countryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
                $country_js_pulldown = construct_country_pulldown($countryid, $ilconfig['registrationdisplay_defaultcountry'], 'country', false, 'state');
                $state_js_pulldown = '<div id="stateid" style="height:20px">' . construct_state_pulldown($countryid, $ilconfig['registrationdisplay_defaultstate'], 'state') . '</div>';
                
                $subscription_role_pulldown = print_role_pulldown('', '', 0);
                
                $ilance->GPC['script'] = (!empty($ilance->GPC['script']) ? $ilance->GPC['script'] : '');
                $ilance->GPC['user_id'] = (!empty($ilance->GPC['user_id']) ? $ilance->GPC['user_id'] : '');
                $ilance->GPC['admin_id'] = (!empty($ilance->GPC['admin_id']) ? $ilance->GPC['admin_id'] : '');
                
                $scripts_pulldown = $ilance->admincp->print_audit_scripts_pulldown($ilance->GPC['script']);
                //$members_pulldown = $ilance->admincp->print_members_pulldown($ilance->GPC['user_id']);
                $members_pulldown = '<input type="text" name="user_id" value="' . intval($ilance->GPC['user_id']) . '" />';
                
                $admins_pulldown = $ilance->admincp->print_admins_pulldown($ilance->GPC['admin_id']);
        
                // #### VIEWING TASK LOG ###########################################
                if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'view')
                {
                        // filters
                        $ilance->GPC['pp'] = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
                        $ilance->GPC['script'] = isset($ilance->GPC['script']) ? $ilance->GPC['script'] : '';
                        $ilance->GPC['user_id'] = isset($ilance->GPC['user_id']) ? intval($ilance->GPC['user_id']) : '';
                        $ilance->GPC['admin_id'] = isset($ilance->GPC['admin_id']) ? intval($ilance->GPC['admin_id']) : '';
                        $ilance->GPC['order'] = isset($ilance->GPC['order']) ? $ilance->GPC['order'] : 'ASC';
                        $ilance->GPC['where'] = '';
                        
                        if (!empty($ilance->GPC['script']))
                        {
                                $ilance->GPC['where'] = "AND script = '" . $ilance->db->escape_string($ilance->GPC['script']) . "'";
                        }
                        if (!empty($ilance->GPC['user_id']))
                        {
                                $ilance->GPC['where'] .= "AND user_id = '" . $ilance->GPC['user_id'] . "'";
                        }
                        if (!empty($ilance->GPC['admin_id']))
                        {
                                $ilance->GPC['where'] .= "AND user_id = '" . $ilance->GPC['admin_id'] . "'";
                        }
                        
                        if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
                        {
                                $ilance->GPC['page'] = 1;
                        }
                        else
                        {
                                $ilance->GPC['page'] = intval($ilance->GPC['page']);
                        }
                        
                        $ilance->GPC['limit'] = ' ORDER BY '.$ilance->db->escape_string($ilance->GPC['orderby']).' '.$ilance->GPC['order'].' LIMIT '.(($ilance->GPC['page']-1)*$ilance->GPC['pp']).','.$ilance->GPC['pp'];
                        
                        $audittmp = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "audit WHERE logid > 0 ".$ilance->GPC['where']);
                        $ilance->GPC['totalcount'] = $ilance->db->num_rows($audittmp);
                        $ilance->GPC['counter'] = ($ilance->GPC['page']-1)*$ilance->GPC['pp'];
                        
                        $audit = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "audit WHERE logid > 0 ".$ilance->GPC['where']." ".$ilance->GPC['limit']);
                        if ($ilance->db->num_rows($audit) > 0)
                        {
                                $count = 0;
                                while ($res = $ilance->db->fetch_array($audit))
                                {
                                        $res['class'] = ($count % 2) ? 'alt2' : 'alt1';
                                        $res['datetime'] = print_date($ilance->datetime->fetch_datetime_from_timestamp($res['datetime']), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        if ($res['user_id'] > 0)
                                        {
                                                $res['user'] = '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
                                        }
                                        else
                                        {
                                                $res['user'] = '--';
                                        }
                                        if ($res['admin_id'] > 0)
                                        {
                                                $res['admin'] = '<a href="' . $ilpage['settings'] . '?cmd=moderators">' . fetch_adminname($res['admin_id']) . '</a>';
                                        }
                                        else
                                        {
                                                $res['admin'] = '--';
                                        }
                                        $auditlog[] = $res;
                                        $count++;
                                }
                        }
                        
                        $prevnext = print_pagnation($ilance->GPC['totalcount'], $ilance->GPC['pp'], $ilance->GPC['page'], $ilance->GPC['counter'], $ilpage['users'].'?cmd=auditlog&amp;do=view&amp;script='.$ilance->GPC['script'].'&amp;admin_id='.$ilance->GPC['admin_id'].'&amp;user_id='.$ilance->GPC['user_id'].'&amp;orderby='.$ilance->GPC['orderby'].'&amp;order='.$ilance->GPC['order']);
                }
                
              
        
                $profilequestions = $phrase['_not_available_at_this_time'];
        
                $pprint_array = array('yearlist','monthlist','daylist','buildversion','ilanceversion','login_include_admin','city_js_pulldown','scriptpage','profilequestions','customquestions','prevnext','admins_pulldown','members_pulldown','scripts_pulldown','rolepulldown','subscription_role_pulldown','dynamic_js_bodyend2','country_js_pulldown','state_js_pulldown','role_pulldown','subscription_permissions_pulldown','register_questions','reportrange','transactionsprevnext','id','customername','currency','subscription_plan_pulldown','dynamic_js_bodyend','searchprevnext','prevnext','number','get_filtervalue','phrases_selectlist','keyword','base_language_pulldown','limit_pulldown','language_pulldown','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
         ($apihook = $ilance->api('admincp_subscribers_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'users.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main', array('customers','searchcustomers','registration_questions','profile','profileaccount','transaction_rows','subscription_rows','creditcards','bankaccounts','exemptions','auditlog','advance'));
	$ilance->template->pprint('main', $pprint_array);
	exit();}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>