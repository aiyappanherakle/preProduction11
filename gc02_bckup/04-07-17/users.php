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
	'administration',
	'accounting',
	'search',
    'stores',
	'wantads',
	'subscription',
	'preferences',
	'javascript'
);
error_reporting(E_ALL);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
    'search',
    'tabfx',
	'jquery',
    'modal',
    'yahoo-jar',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_shipping.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';
//error_reporting(E_ALL);
global $date_down,$securekey_hidden;
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
        // #### UPDATE LISTING FEE FOR USER #############
        if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_update-listing_fee' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
        	//echo "<pre>";print_r($ilance->GPC);echo "</pre>";exit();

        	if (isset($ilance->GPC['listing_fee']) AND $ilance->GPC['listing_fee'] == '')
        	{
        		refresh('users.php?subcmd=_update-customer&id='.$ilance->GPC['id'].'&error_listing=1');
        		exit();
        		//print_action_failed($phrase['_nothing_to_do'].$phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'users.php?subcmd=_update-customer&id='.$ilance->GPC['id']);
				//exit();
        	}

            $sql = $ilance->db->query("SELECT status FROM " . DB_PREFIX . "users 
            	WHERE user_id = '" . intval($ilance->GPC['id']) . "'
            	AND status = 'active'
            ");

			if($ilance->db->num_rows($sql) > 0)
			{
				$update = $ilance->db->query("UPDATE " . DB_PREFIX . "users 
					SET listing_fee = '" . $ilance->db->escape_string($ilance->GPC['listing_fee']) . "'
					WHERE user_id = '" . intval($ilance->GPC['id']) . "'
				");
				print_action_success("Listing Fee is successfully updated.",'users.php?subcmd=_update-customer&id='.$ilance->GPC['id']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_the_selected_user_was_not_found_to_be_removed_from_the_marketplace'], 'users.php?subcmd=_update-customer&id='.$ilance->GPC['id']);
				exit();
			}
        }
        // #### UPDATE FINAL VALUE FEE FOR USER #############
        if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_update-final_value_fee' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
        	//echo "<pre>";print_r($ilance->GPC);echo "</pre>";exit();

        	if (isset($ilance->GPC['final_value_fee']) AND $ilance->GPC['final_value_fee'] == '')
        	{
        		refresh('users.php?subcmd=_update-customer&id='.$ilance->GPC['id'].'&error_final=1');
        		exit();
        		//print_action_failed($phrase['_nothing_to_do'].$phrase['_you_did_not_select_any_listings_from_the_previous_page_please_retry'], 'users.php?subcmd=_update-customer&id='.$ilance->GPC['id']);
				//exit();
        	}

            $sql = $ilance->db->query("SELECT status FROM " . DB_PREFIX . "users 
            	WHERE user_id = '" . intval($ilance->GPC['id']) . "'
            	AND status = 'active'
            ");

			if($ilance->db->num_rows($sql) > 0)
			{
				$update = $ilance->db->query("UPDATE " . DB_PREFIX . "users 
					SET final_value_fee = '" . $ilance->db->escape_string($ilance->GPC['final_value_fee']) . "'
					WHERE user_id = '" . intval($ilance->GPC['id']) . "'
				");
				print_action_success("Final value Fee is successfully updated.",'users.php?subcmd=_update-customer&id='.$ilance->GPC['id']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_the_selected_user_was_not_found_to_be_removed_from_the_marketplace'], 'users.php?subcmd=_update-customer&id='.$ilance->GPC['id']);
				exit();
			}
        }
        // #### UPDATE USER REGISTRATION QUESTIONS #############################
        if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-customer-profile-questions' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                // process custom registration questions
                if (!empty($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
                {
                        //$ilance->registration = construct_object('api.registration');
                        //$ilance->registration->process_custom_register_questions($ilance->GPC['custom'], intval($ilance->GPC['id']));
                }
                
                print_action_success($phrase['_profile_answers_were_updated_for_this_profile_an_email_was_not_sent'], 'users.php?subcmd=_update-customer&id='.intval($ilance->GPC['id']));
                exit();        
        }
        
        
        // #### UPDATE USER Lowering Minimum Bid Percentage #############################
       
         if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'lower_minimum_bid' AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
        {
        	
                if(isset($ilance->GPC['lower_minimum_bid']) and $ilance->GPC['lower_minimum_bid']==1)
                {
                	if(isset($ilance->GPC['lower_minimum_bid_percentage']) and intval($ilance->GPC['lower_minimum_bid_percentage'])>0 and intval($ilance->GPC['lower_minimum_bid_percentage'])<=99)
                	{
                		$sql="Update " . DB_PREFIX . "users 
                		set is_auto_lower_min_bid=1,auto_min_bid_lower_prec='".intval($ilance->GPC['lower_minimum_bid_percentage'])."'
                		WHERE user_id = '".intval($ilance->GPC['uid'])."'";
                		$result=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
                		print_action_success("Task have been successfully completed.The perccentage of lowering is set to ".intval($ilance->GPC['lower_minimum_bid_percentage']), $_SERVER['PHP_SELF']);
                		exit();	                	
                	}else
                	{
                
                		print_action_failed("We're sorry. Can accept only a number between 0 to 99, as the field is a percentage for lowering the minimum bid of any unsold Item for this user", $_SERVER['PHP_SELF']);
                		exit();
                	}
                }else
                {
        		$sql="Update " . DB_PREFIX . "users 
        		set is_auto_lower_min_bid=0,auto_min_bid_lower_prec='0'
        		WHERE user_id = '".intval($ilance->GPC['uid'])."'";
        		$result=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
                print_action_success("We're sorry. You must check that checkbox to set Auto lowering of minimum bid of this seller coins", $_SERVER['PHP_SELF']);
                exit();
                }
        }
        
        
        
        // #### UPDATE USER REGISTRATION QUESTIONS #############################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-customer-register-questions' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                // process custom registration questions
                if (!empty($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
                {
                        $ilance->registration = construct_object('api.registration');
                        $ilance->registration->process_custom_register_questions($ilance->GPC['custom'], intval($ilance->GPC['id']));
                }
                
                print_action_success($phrase['_registration_answers_were_updated_for_this_profile_an_email_was_not_sent_to_the_customer'], 'users.php?subcmd=_update-customer&id='.intval($ilance->GPC['id']));
                exit();        
        }
        
        // #### REMOVE CUSTOM REGISTRATION ANSWER FOR A SUBSCRIBER #############
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-answer' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "register_answers
                        WHERE answerid = '" . intval($ilance->GPC['id']) . "'
                        AND user_id = '".intval($ilance->GPC['id'])."'
                ");
                
                print_action_success($phrase['_you_have_removed_a_registration_answer_for_this_user_an_email_was_not_sent'], 'users.php?subcmd=_update-customer&id='.intval($ilance->GPC['id']));
                exit();
        }
    
        // #### save role ######################################################
        else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-role-change')
        {
                if (!empty($ilance->GPC['roleid']) AND !empty($ilance->GPC['id']))
                {
                        // admin is changing the role for this user
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "subscription_user
                                SET roleid = '".intval($ilance->GPC['roleid'])."'
                                WHERE user_id = '".intval($ilance->GPC['id'])."'
                        ");
                }
                
                print_action_success($phrase['_you_have_updated_the_role_for_this_user'], 'users.php?subcmd=_update-customer&id='.intval($ilance->GPC['id']));		
        }
         else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'uploaduser')
        {
                if (isset($ilance->GPC['submit']))
                {
				
						$column_names = array('username','password','secretquestion','secretanswer','email','first_name','last_name','address','address2','city','state','zip_code','phone','country','dob','roleid','subscriptionid' );	  
				 
					
					if((!empty($_FILES['upload'])) && ($_FILES['upload']['error'] == 0))
					{						
						if($_FILES['upload']['type'] == 'application/vnd.ms-excel' || 'application/octet-stream' )
						{						
							if($_FILES['upload']['size'] > 1000000)
							{
								print_action_failed("We're sorry.  File you are uploading is bigger then 1MB.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
								exit();
							}
							else
							{
								$handle = fopen($_FILES['upload']['tmp_name'],'r');
								$row_count = 0;	
																		
								while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
								 
							   { 							
									$row_count++;
									if ($row_count==1) continue;
									if(count($data) != count($column_names))
									{
									print_action_failed("We're sorry. CSV file is not correct. Number of columns in
	 database and number of columns in file are not the same. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
								exit();
									}
									else if($data[0] =='' ||  $data[1] =='' ||  $data[4] =='' ||  $data[5] =='' ||  $data[6] =='' || $data[13] =='' ||  $data[15] =='' ||  $data[16] =='')
									{
								
									print_action_failed("We're sorry. CSV File is not correct. Number of columns in
	 database and number of columns in file are not the same. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'');
	 
									exit(); 
			
									}
									 $sqlusercheck = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "users
											WHERE username = '".$data[0]."' OR email = '".$data[4]."'
									");
									 if($ilance->db->num_rows($sqlusercheck) > 0)
									{
										   continue;
									}
									else
									  {
									  	
									  
									$user['salt'] = construct_password_salt(5);							    							
									$temp_data['username'] = $data[0];								
									$temp_data['password'] = $data[1];
									$user['password'] = md5(md5($temp_data['password']) . $user['salt']);
									$temp_data['secretquestion'] = $data[2];
									$temp_data['secretanswer'] = $data[3];
									$user['secretanswer'] = md5($temp_data['secretanswer']);
									$temp_data['email'] = $data[6];
									$temp_data['first_name'] = $data[4];
									$temp_data['last_name'] = $data[5];	
									$temp_data['address'] = $data[7];	
									$temp_data['address2'] = $data[8];
									$temp_data['city'] = $data[9];
									$temp_data['state'] = $data[10];
									$temp_data['zip_code'] = $data[11];
									$temp_data['phone'] = $data[12];
									$temp_data['country'] = $data[13];
									$temp_data['dob'] = $data[14];
									$temp_date['roleid'] = $data[15];
									$temp_date['subscriptionid'] = $data[16];
									
													 
								    $newuserid = $ilance->admincp->construct_new_member(
													$ilance->db->escape_string($temp_data['username']),
													$ilance->db->escape_string( $user['password']),
													$ilance->db->escape_string( $user['salt']),
													$ilance->db->escape_string($temp_data['secretquestion']),
													$ilance->db->escape_string($user['secretanswer']),
													$ilance->db->escape_string($temp_data['email']),
													$ilance->db->escape_string($temp_data['first_name']),
													$ilance->db->escape_string($temp_data['last_name']),
													$ilance->db->escape_string($temp_data['address']),
													$ilance->db->escape_string($temp_data['address2']),
													$ilance->db->escape_string($temp_data['city']),
													$ilance->db->escape_string($temp_data['state']),
													$ilance->db->escape_string($temp_data['zip_code']),
													$ilance->db->escape_string($temp_data['phone']),
													$ilance->db->escape_string($temp_data['country'] ),
													'0000-00-00',
													create_referral_code(6),
													$ilconfig['globalserverlanguage_defaultlanguage'],
													$ilconfig['globalserverlocale_defaultcurrency'],
													$ilconfig['globalserverlocale_officialtimezone'],
													0,
													'',
													'',
													''
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
		                $temp_date['roleid'] = isset($temp_date['roleid']) ? intval($temp_date['roleid']) : '-1';
		                
		                // invoice methods can be:
		                // account, purchaseorder
		                $ilance->registration->build_user_subscription($newuserid, intval($temp_date['subscriptionid']), 'account', '', $temp_date['roleid']);
		                
		                $ilance->email = construct_dm_object('email', $ilance);
						
						$ilance->email->mail = SITE_EMAIL;
		                $ilance->email->slng = fetch_site_slng();
		                $ilance->email->get('register_welcome_email_admin_admincp');		
		                $ilance->email->set(array(
		                        '{{username}}' => $temp_data['username'],
		                        '{{user_id}}' => $newuserid,
		                        '{{first_name}}' => $temp_data['firstname'],
		                        '{{last_name}}' => $temp_data['lastname'],
		                        '{{phone}}' => $temp_data['phone'],
		                        '{{emailaddress}}' => $temp_data['email'],
		                ));
		                $ilance->email->send();
							
									}
										
																															
								 }							
							  
							}							
						}
						
						else
						{
							print_action_failed("We're sorry.  Upload Only CSV file.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
							exit();
						}	
						
						fclose($handle);
						print_action_success("CSV File Pack importation success.  Changes reflected within the CSV email template have been successfully imported to the database.", $_SERVER['PHP_SELF']);
									exit();									
					}			
					else 
					{
					   
						print_action_failed("We're sorry.  This CSV file does not exist.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
						exit();							
					}
                      
                }
                
                	
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
			print_action_success($phrase['_the_selected_users_were_removed_from_the_marketplace_indefinately'] . " " . $removedusers . ". " . $phrase['_these_customers_will_not_be_able_to_login_to_the_marketplace_unless'], 'users.php');
			exit();
		}
		else
		{
			print_action_failed($phrase['_the_selected_user_was_not_found_to_be_removed_from_the_marketplace'], 'users.php');
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
				
				print_action_success($phrase['_the_selected_users_were_removed_from_the_marketplace_indefinately'] . " " . $removedusers . ". " . $phrase['_these_customers_will_not_be_able_to_login_to_the_marketplace_unless'], 'users.php');
				exit();
			}
			else
			{
				print_action_failed($phrase['_no_customers_were_selected_for_removal_please_try_again'], 'users.php');
				exit();	
			}
                }
                else
                {
                        print_action_failed($phrase['_no_customers_were_selected_for_removal_please_try_again'], 'users.php');
                        exit();
                }
        }
            
        // #### REMOVE SUBSCRIPTION PERMISSION EXEMPTION #######################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-exemption' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "subscription_user_exempt
                        WHERE exemptid = '" . intval($ilance->GPC['id']) . "'
                            AND user_id = '".$ilance->GPC['id']."'
                        LIMIT 1
                ");
                
                print_action_success($phrase['_the_selected_exemption_was_removed_from_the_customers_subscription'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
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
			print_action_success($phrase['_the_selected_users_have_been_suspended'].' '.$suspendusers, 'users.php');
			exit();
		}
		else
		{
			print_action_failed($phrase['_could_not_suspend_one_or_more_users_please_try_again'], 'users.php');
			exit();	
		}
        }
		
		 // murugan added sep 7 for auction win notification
		 
		  // #### activate notification ##################################################activateauction
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'activateauction' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
		// empty inline cookie
		set_cookie('inlinemembers', '', false);
		
		$activatedauction = $ilance->admincp->activate_auction(array($ilance->GPC['id']));
		if (!empty($activatedauction))
		{
			$activatedauction = mb_substr($activatedauction, 0, -2);
			print_action_success($phrase['_the_selected_users_have_been_activated'].' '.$activatedauction, 'users.php');
			exit();
		}
		else
		{
			print_action_failed($phrase['_could_not_activate_one_or_more_users_please_try_again'], 'users.php');
			exit();	
		}
        }
		
		 // #### suspend notification ###############################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'suspendauction' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
		// empty inline cookie
		set_cookie('inlinemembers', '', false);
		
		$suspendauction = $ilance->admincp->suspend_auction(array($ilance->GPC['id']));
		if (!empty($suspendusers))
		{
			$suspendauction = mb_substr($suspendauction, 0, -2);
			print_action_success($phrase['_the_selected_users_have_been_suspended'].' '.$suspendauction, 'users.php');
			exit();
		}
		else
		{
			print_action_failed($phrase['_could_not_suspend_one_or_more_users_please_try_again'], 'users.php');
			exit();	
		}
        }
	
	//vijay nov 18
	
		//Batch Biddings
		
		   else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'enable_batch_bid_deactivate' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
		
		$updbb = $ilance->db->query("update " .DB_PREFIX . "users set enable_batch_bid = '0' where user_id = '".$ilance->GPC['id']."'");
		 
		print_action_success('The selected user Access Batch Biddings deactivated','users.php');
			exit();
		}
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'enable_batch_bid_activate' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
				
		$updbb = $ilance->db->query("update " .DB_PREFIX . "users set enable_batch_bid = '1' where user_id = '".$ilance->GPC['id']."'");
		
		print_action_success('The selected user Access Batch Biddings activated','users.php');
			exit();
		  
		}

		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'enable_bid_deactivate' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
		
		$updbb = $ilance->db->query("update " .DB_PREFIX . "users set enable_bid = '0' where user_id = '".$ilance->GPC['id']."'");
		 
		print_action_success('The selected user Access Biddings activated','users.php');
			exit();
		}
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'enable_bid_activate' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
				
		$updbb = $ilance->db->query("update " .DB_PREFIX . "users set enable_bid = '1' where user_id = '".$ilance->GPC['id']."'");
		
		print_action_success('The selected user Access Biddings deactivated','users.php');
			exit();
		  
		}

		// bbcode starts
		 else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'access_bbdeactivate' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
		
		
		  $updbb = $ilance->db->query("update " .DB_PREFIX . "users set access_bb = '0' where user_id = '".$ilance->GPC['id']."'");
		  print_action_success('The selected user Access BBcode deactivated','users.php');
			exit();
		}
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'access_bbactivate' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
		
		$updbb = $ilance->db->query("update " .DB_PREFIX . "users set access_bb = '1' where user_id = '".$ilance->GPC['id']."'");
		
		print_action_success('The selected user Access BBcode activated','users.php');
			exit();
		  
		}
	
	// nov 18 end
		//dec 02 for bug id 1077
		  else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'irmanageddeactivate' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
		
		
		  $updat = $ilance->db->query("update " .DB_PREFIX . "users set ir_managed = '0' where user_id = '".$ilance->GPC['id']."'");
		  print_action_success('the selected user IR managed deactivated','users.php');
			exit();
		}
		  else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'irmanagedactivate' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
		
		
		$updat = $ilance->db->query("update " .DB_PREFIX . "users set ir_managed = '1' where user_id = '".$ilance->GPC['id']."'");
	print_action_success('the selected user IR managed activated','users.php');
			exit();
		  
		}
	
	//dec 02 finished for bug id 1077
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
				print_action_success($phrase['_the_selected_users_have_been_suspended'].' '.$suspendusers, 'users.php');
				exit();
			}
			else
			{
				print_action_failed($phrase['_could_not_suspend_one_or_more_users_please_try_again'], 'users.php');
				exit();	
			}
		}
		else
		{
			print_action_failed($phrase['_could_not_suspend_one_or_more_users_please_try_again'], 'users.php');
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
				print_action_success($phrase['_the_selected_users_have_been_banned'].' '.$bannedusers, 'users.php');
				exit();
			}
			else
			{
				print_action_failed($phrase['_could_not_place_a_ban_one_or_more_users_please_try_again'], 'users.php');
				exit();	
			}
		}
		else
		{
			print_action_failed($phrase['_could_not_place_a_ban_one_or_more_users_please_try_again'], 'users.php');
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
				print_action_success($phrase['_the_selected_users_have_been_cancelled'].' '.$cancelledusers, 'users.php');
				exit();
			}
			else
			{
				print_action_failed($phrase['_could_not_cancel_one_or_more_users_please_try_again'], 'users.php');
				exit();	
			}
		}
		else
		{
			print_action_failed($phrase['_could_not_cancel_one_or_more_users_please_try_again'], 'users.php');
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
			print_action_success($phrase['_the_selected_users_have_been_activated'].' '.$activatedusers, 'users.php');
			exit();
		}
		else
		{
			print_action_failed($phrase['_could_not_activate_one_or_more_users_please_try_again'], 'users.php');
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
				
				print_action_success($phrase['_the_selected_users_have_been_activated'].' '.$activatedusers, 'users.php');
				exit();
			}
			else
			{
				print_action_failed($phrase['_could_not_activate_one_or_more_users_please_try_again'], 'users.php');
				exit();	
			}
                }
                else
                {
                        print_action_failed($phrase['_could_not_activate_one_or_more_users_please_try_again'], 'users.php');
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
				
				print_action_success($phrase['_the_selected_users_have_been_unverified_and_will_need_to_verify_their_email_again_to_become_activated'].' '.$unverifiedusers, 'users.php');
				exit();
			}
			else
			{
				print_action_failed($phrase['_could_not_unverify_one_or_more_users_please_try_again'], 'users.php');
				exit();	
			}
                }
                else
                {
                        print_action_failed($phrase['_could_not_unverify_one_or_more_users_please_try_again'], 'users.php');
                        exit();
                }
        }
            
        // #### CREATE NEW CUSTOMER ############################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-new-customer')
        {
		
			// sekar on apr 5
			
			 $sqlusercheck = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE username = '".$ilance->GPC['username']."'
										 ");
										 
		   $mailcheck = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE 
									    email = '".$ilance->GPC['email']."'
										 ");
		
		
        	if(!isset($ilance->GPC['password']) OR !isset($ilance->GPC['password2']) OR empty($ilance->GPC['password']) OR empty($ilance->GPC['password2']) OR $ilance->GPC['password'] != $ilance->GPC['password2'])  
        	{
        		print_action_failed($phrase['_passwords_are_empty_or_do_not_match'], 'users.php');
            	exit();
        	}
        	else if(!isset($ilance->GPC['username']) OR empty($ilance->GPC['username']))
        	{
        		print_action_failed($phrase['_please_enter_correct_username'], 'users.php');
            	exit();
        	}     
		    else if(!isset($ilance->GPC['email']) OR empty($ilance->GPC['email'])) 
		    {         
		    	print_action_failed($phrase['_please_enter_correct_email'], 'users.php');
            	exit();
		    }
			
				 //sekar on april 1    
		    else if ($ilance->db->num_rows($sqlusercheck) > 0) 
		      {         
				  
						print_action_failed($phrase['_were_sorry_that_username_is_taken'], 'users.php');
						exit();
		     }
			
			else if ($ilance->db->num_rows($mailcheck) > 0)
                {
                                       
				   	print_action_failed($phrase['_email_address_already_in_use'], 'users.php');
            	       exit(); 
                }	
			
		    else 
		    {
        		
		    	//$unicode_name = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['username']);
                        
               // if ($ilance->common->is_username_banned($ilance->GPC['username']) OR $ilance->common->is_username_banned($unicode_name))
               // {
               // 	print_action_failed($phrase['_this_username_is_banned'], 'users.php');
            	//	exit();
               // }
		    	
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
								$ilance->GPC['Check_Payable'],	
		                        $ilance->GPC['address'],
		                        $ilance->GPC['address2'],
		                        $ilance->GPC['city'],
		                        $ilance->GPC['state'],
		                        $ilance->GPC['zipcode'],
		                        $ilance->GPC['phone'],
								$ilance->GPC['client_representative'],
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

                $emptyemailsql = '';
                if (!empty($ilance->GPC['email']))
                {        
                        $emptyemailsql = "email = '" . $ilance->GPC['email'] . "',";
                       
                }
              
                $status = isset($ilance->GPC['status']) ? $ilance->db->escape_string($ilance->GPC['status']) : 'unverified';
                $dob = isset($ilance->GPC['dob']) ? $ilance->db->escape_string($ilance->GPC['dob']) : '0000-00-00';
                $isadmin = isset($ilance->GPC['isadmin']) ? intval($ilance->GPC['isadmin']) : '0';
                
				//karthik on sep06 for sales tax reseller
		         $issalestaxreseller = isset($ilance->GPC['issalestaxreseller']) ? intval($ilance->GPC['issalestaxreseller']) : '0';
				//end on sep06 
				 
				 //changes on Dec-15 for Exclude from Prices Realized Exports
				$isexclude_pricesrealized = isset($ilance->GPC['isexclude_pricesrealized']) ? intval($ilance->GPC['isexclude_pricesrealized']) : '0';
				
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
						// murugan changes on march 25 for banned user
                       /* if ($ilance->common->is_username_banned($ilance->GPC['username']) OR $ilance->common->is_username_banned($unicode_name))
                        {
							echo '1';
                                $show['error_username'] = true;
                        }
                        else
                        {*/
                                $sqlusercheck = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE username IN ('" . htmlspecialchars_uni($ilance->GPC['username']) . "', '" . htmlspecialchars_uni($unicode_name) . "')
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
                        //}
						// murugan changes 
                }
                else
                {
                        $show['error_username'] = true;
                }
                
                if ($show['error_username'])
                {
                        print_action_failed($phrase['_sorry_the_username_you_entered_appears_to_be_in_the_username_ban_list'], 'users.php?subcmd=_update-customer&id=' . intval($ilance->GPC['id']));
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
		
		$house_account=intval($ilance->db->escape_string(isset($ilance->GPC['house_account'])?1:0));

                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "',
                        $passwordsql
                        $emptyemailsql 
                        first_name = '" . $ilance->db->escape_string($ilance->GPC['first_name']) . "',
                        last_name = '" . $ilance->db->escape_string($ilance->GPC['last_name']) . "',
						Check_Payable = '" . $ilance->db->escape_string($ilance->GPC['Check_Payable']) . "',
                        address = '" . $ilance->db->escape_string($ilance->GPC['address']) . "',
                        address2 = '" . $ilance->db->escape_string($ilance->GPC['address2']) . "',
                        city = '" . $ilance->db->escape_string($ilance->GPC['city']) . "',
                        state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "',
                        zip_code = '" . $ilance->db->escape_string($ilance->GPC['zip_code']) . "',
                        phone = '" . $ilance->db->escape_string($ilance->GPC['phone']) . "',
						client_representative = '" . $ilance->db->escape_string($ilance->GPC['client_representative']) . "',
                        country = '" . $ilance->GPC['locationid'] . "',
                        ipaddress = '" . $ilance->db->escape_string($ilance->GPC['ipaddress']) . "',
                        iprestrict = '" . $ipres . "',
                        status = '" . $status . "',
                        dob = '" . $dob . "',
						secretquestion = '" . $ilance->db->escape_string($ilance->GPC['secretquestion']) . "',
						secretanswer = '" . $ilance->db->escape_string($secretanswer) . "',
						$gendersql
                        isadmin = '" . $isadmin . "',
						issalestaxreseller = '". $issalestaxreseller."',
						isexclude_pricesrealized = '". $isexclude_pricesrealized."',
						house_account='".$house_account."'
                        WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                ");
                    
                if (isset($ilance->GPC['emailuser']) AND $ilance->GPC['emailuser'])
                {
                                            
                        /*$email['message'] = $phrase['_dear'] . " " . $ilance->GPC['username'] . ",\n\nThis email is to inform you that you password was reset by a staff member.  Please find your new login information below:\n\n===========================\nUsername: ".$ilance->GPC['username']."\nPassword: ".$ilance->GPC['password']."\n===========================\n\nSincerely,\n".SITE_NAME." ".HTTP_SERVER;
                        send_email($ilance->GPC['email'], $phrase['_your_email_address_was_changed_at'] . ' ' . SITE_NAME, $email['message'], SITE_EMAIL);*/
						
						$ilance->email = construct_dm_object('email', $ilance);
						$ilance->email->mail = $ilance->GPC['email'];
		                $ilance->email->slng = fetch_site_slng();
		                $ilance->email->get('password_reset');		
		                $ilance->email->set(array(
		                        '{{username}}' => $ilance->GPC['username'],		                        
		                        '{{first_name}}' => $ilance->GPC['firstname'],
		                        '{{password}}' => $ilance->GPC['password'],
		                       
		                ));
		                $ilance->email->send();
						$notice = $phrase['_the_customers_profile_has_been_updated_with_new_changes_and_the_password_was_reset'];   
                }
                else
                {
                        $notice = $phrase['_the_customers_profile_has_been_updated_with_new_changes_changes'];
                }
                
                print_action_success($notice, 'users.php?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['id']));
                exit();
        }

        // #### SUBSCRIPTION PLAN RE-ASSIGNMENT ################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-subscription' AND $ilance->GPC['id'] > 0)
        {
                $ilance->subscription = construct_object('api.subscription');
                $ilance->subscription->subscription_upgrade_process_admincp(intval($ilance->GPC['id']), intval($ilance->GPC['subscriptionid']), $ilance->GPC['txndescription'], $ilance->GPC['action']);
                
                print_action_success($phrase['_the_customer_has_been_reassigned_with_the_selected_subscription_plan'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                exit();
        }
            
        // #### ADMIN ASSIGNS NEW SUBSCRIPTION EXEMPTION PERMISSION TO MEMBER
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-exemption' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                $ilance->subscription = construct_object('api.subscription');
                if ($ilance->subscription->construct_subscription_exemption(intval($ilance->GPC['id']), $ilance->GPC['accessname'], $ilance->GPC['exemptvalue'], $ilance->GPC['exemptcost'], $ilance->GPC['exemptdays'], $ilance->GPC['logic'], $ilance->GPC['description']))
                {
                        print_action_success($phrase['_the_customer_has_been_assigned_with_the_selected_subscription_permission_exemption'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                        exit();
                }
                else 
                {
                        print_action_failed($phrase['_there_was_a_problem_with_the_action_selected_this_may_be_due_to_the_customer_not_having_sufficient_funds'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                        exit();
                }
        }
            
        // #### MANUALLY AUTHORIZE CUSTOMER CREDIT CARD FOR USAGE ##############
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_authorize-creditcard' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['id'] > 0)
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
                        print_action_success($phrase['_the_selected_credit_card_was_manually_authorized_verified_from_administration'], 'users.php?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['id']));
                        exit();
                }
        }
            
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_unauthorize-creditcard' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0  AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
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
                        print_action_success($phrase['_the_selected_credit_card_was_manually_unauthorized_and_this_customer_will_be_required_to_manually_verify'], 'users.php?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['id']));
                        exit();
                }
        }
            
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-creditcard' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['id'] > 0)
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
                        print_action_success($phrase['_the_selected_credit_card_was_removed_from_the_customers_profile_this_customer_will_be_required_to_verify_any'], 'users.php?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['id']));
                        exit();
                }
        }
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-bankaccount' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['id'] > 0)
        {
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "bankaccounts
                        WHERE bank_id = '" . intval($ilance->GPC['id']) . "'
                ");
                
                print_action_success($phrase['_the_selected_bank_account_was_removed_from_the_customers_profile'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                exit();
        }
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-transaction' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['id'] > 0)
        {
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "invoices
                        WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
                ");
                
                print_action_success($phrase['_the_selected_transaction_was_removed_from_the_transaction_system'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                exit();
        }
			 else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-note' AND $ilance->GPC['id'] > 0 )
        {
		

          if (trim($ilance->GPC['note'])<>'') 
		 { 
				$user_note_check= $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users_note 
												WHERE 
												 user_id='" . intval($ilance->GPC['id']) . "'
												 and note_id = '".$ilance->GPC['note_id']."'
												");
												


			   
				if($ilance->db->num_rows($user_note_check)>0)
				{

				$ilance->db->query("
                       update " . DB_PREFIX . "users_note
                        SET user_id = '" . intval($ilance->GPC['id']) . "',note='" . addslashes($ilance->GPC['note']) . "',follow_up_date='" .$ilance->GPC['follow_up_date']. "', modified =now()
						where note_id = '" . intval($ilance->GPC['note_id']) . "'
                "); 
				    print_action_success($phrase['_the_selected_note_has_been_updated_successfully'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                exit(); 
       
                }
				else
				{
				
				   $ilance->db->query("
                        insert into  " . DB_PREFIX . "users_note
                        SET user_id = '" . intval($ilance->GPC['id']) . "',note='" . addslashes($ilance->GPC['note']) . "',follow_up_date='" .$ilance->GPC['follow_up_date']. "', created=now(),modified =now()
						
                "); 
				    print_action_success($phrase['_the_note_was_created_successfully'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                exit(); 
				
				}
		  }
        }
        
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'save_user_paymethods' AND $ilance->GPC['id'] > 0 )
		{
                	     $ilance->db->query("
                        update  " . DB_PREFIX . "users set allowed_paymethods='".implode(',',$ilance->GPC['paymethods'])."' WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                ");
                
                print_action_success("Selected Pay methods were save for the user", 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                exit();

		// #### Ebay seller Fee Starts###########################
		
		}
		 else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update_seller_percentage' AND $ilance->GPC['id'] > 0 )
        {
	
          if (($ilance->GPC['ebay_seller_percentage']) > '')
		 { 
				$ebay_seller_per= $ilance->db->query("SELECT ebay_seller_percentage
                                FROM " . DB_PREFIX . "users


                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
												");
												


			   
				if($ilance->db->num_rows($ebay_seller_per)>0)
				{





				$ilance->db->query("
                       update " . DB_PREFIX . "users
                        SET ebay_seller_percentage = '" . intval($ilance->GPC['ebay_seller_percentage']) ."'
						 WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                "); 
				    print_action_success($phrase['_the_customers_profile_has_been_updated_with_new_changes_changes'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
                exit(); 
       
                }
			
		  }
		  else 
		 {
			print_action_failed('please enter the correct value try again', 'users.php');
			exit();	
		 }
		 
		  
                                   
        }
		
			// #### Ebay seller Fee End ###########################
		
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-note' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['id'] > 0 )
		{
		     $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "users_note
                        WHERE note_id = '" . intval($ilance->GPC['id']) . "'
                ");
                
                print_action_success($phrase['_the_selected_note_was_deleted_successfully'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
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
                                        
                                        print_action_success($phrase['_a_new_transaction_debit_was_successfully_created_and_the_customers'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
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
                                        
                                        print_action_success($phrase['_a_new_transaction_credit_was_successfully_created_and_the_customers'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
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
							$validdate =  date('Y-m-d',strtotime( $validdate));
						}
						
					   
						$today = date('Y-m-d',strtotime(DATETIME24H));
						
					   //id, amount, date_made, interest, description, user_id
					    $advance = $ilance->db->query("INSERT INTO ".DB_PREFIX."user_advance VALUES (0,'".$ilance->GPC['amount']."','". $validdate."','".$ilance->GPC['interestrate']."','".$ilance->GPC['description']."','".$ilance->GPC['id']."','unpaid','".$ilance->GPC['consignid']."')");
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
									//// Here Changes insert_transaction On Nov 24 credit to advance
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
											'advance',
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
											
											print_action_success($phrase['_a_new_transaction_credit_was_successfully_created_and_the_customers'], 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
											exit();						
									}
							}
							
							
						}
						print_action_success("New Advance Trasaction Completed", 'users.php?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
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

        $sql_user_check = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                LIMIT 1
        ");
        if ($ilance->db->num_rows($sql_user_check) == 0)
        {
        	print_action_failed("We're sorry. Please check user details.", $_SERVER['PHP_SELF']);
			exit();	
        }
        
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
                                    
                                    
              $paymethod_html='<select multiple name="paymethods[]" size=8>';
              $sql_paymethod=$ilance->db->query("select * from ".DB_PREFIX."payment_methods order by sort");
              while($result_paymethods=$ilance->db->fetch_array($sql_paymethod))
              {
                  if(in_array($result_paymethods['id'],explode(",",$res['allowed_paymethods'])))
                  $paymethod_html.='<option value="'.$result_paymethods['id'].'" selected="selected">'.$phrase[$result_paymethods['title']].'</options>';
                  else
                  $paymethod_html.='<option value="'.$result_paymethods['id'].'">'.$phrase[$result_paymethods['title']].'</options>';
              }
               $paymethod_html.='</select>';
               
				$res['access_bb'] = stripslashes($res['access_bb']);
				$res['enable_batch_bid'] = stripslashes($res['enable_batch_bid']);
				$res['enable_bid'] = stripslashes($res['enable_bid']);
                $res['username'] = stripslashes($res['username']);
                $res['first_name'] = stripslashes($res['first_name']);
                $res['last_name'] = stripslashes($res['last_name']);
				$res['Check_Payable'] = stripslashes($res['Check_Payable']);
                $res['phone'] = stripslashes($res['phone']);
                $res['address'] = stripslashes($res['address']);
                $res['address2'] = stripslashes($res['address2']);
                $res['city'] = stripslashes(ucfirst($res['city']));
                $res['zip_code'] = stripslashes(mb_strtoupper($res['zip_code']));
                $res['client_representative'] = stripslashes($res['client_representative']);
                $lower_minimum_bid = $res['is_auto_lower_min_bid']==1?'checked':'';
                $lower_minimum_bid_percentage = stripslashes($res['auto_min_bid_lower_prec']);
										if($res['house_account']==1)
										{
										$res['house_account']='checked="checked"';
										}else
										{
										$res['house_account']='';
										}
				
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
                                        $customerid = "(" . $res['user_id'] . ")";

                                        
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
										
										//karthik on sep06 for salestaxreseller
										  if ($res['issalestaxreseller'])
                                        {
                                                $res['issalestaxreseller'] = '<input type="checkbox" name="issalestaxreseller" id="issalestaxreseller" value="1" checked="checked" />';
                                        }
                                        else
                                        {
                                                $res['issalestaxreseller'] = '<input type="checkbox" name="issalestaxreseller" id="issalestaxreseller" value="1" />';
                                        }
										
										//end on sep06
										
										//changes on dec-15 for Exclude from Prices Realized Exports
										  if ($res['isexclude_pricesrealized'])
                                        {
                                                $res['isexclude_pricesrealized'] = '<input type="checkbox" name="isexclude_pricesrealized" id="isexclude_pricesrealized" value="1" checked="checked" />';
                                        }
                                        else
                                        {
                                                $res['isexclude_pricesrealized'] = '<input type="checkbox" name="isexclude_pricesrealized" id="isexclude_pricesrealized" value="1" />';
                                        }
										
										//end on dec-15
					
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
					//new herakle
					if($res['last_update'] != '')
					{
					$lat_my = print_date($res['last_update'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					}
					else
					{
					$lat_my = print_date($res['lastseen'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					}
					
					$res['last_update'] = $lat_my;
					
				    if($res['who'] == '0')
					{
					$us_mod = 'User';
					}
					else if($res['who'] == '1')
					{
					$us_mod = 'Staff';
					}
					else
					{
					$us_mod = 'Staff';
					}
                    $res['who'] = $us_mod;  
					// murugan changes on mar 08
					$refsel = $ilance->db->query("SELECT * FROM ". DB_PREFIX ."referal_id
					WHERE referalcode = '".$res['rid']."'");
					if($ilance->db->num_rows($refsel) > 0)
					{
					   $resref = $ilance->db->fetch_array($refsel);
					   $referfrom = $resref['description'];
					}
					else
					{
						$referfrom  = 'Direct';
					}
					//e        
                                        
                                        $profile[] = $res;
										// echo '<pre>';
										// print_r($profile);
										// exit;
                                }
                        }

                    
                        
						// #### Ebay seller Fee Starts###########################
						
						$sqlebay_sell = $ilance->db->query("
						
                                SELECT ebay_seller_percentage
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        if ($ilance->db->num_rows($sqlebay_sell) > 0)
                        {
                                while ($ebay_seller = $ilance->db->fetch_array($sqlebay_sell))
                                {
                                      $ebay_seller_percentage['ebay_seller_percentage'] = $ebay_seller['ebay_seller_percentage'];
                                  
                                       $ebay_seller_percentage[] = $ebay_seller;
                                }
                        }
						
						
						// #### Ebay seller Fee End
                                
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
                        if (isset($paidplan) AND $paidplan)
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
                                            AND u.user_id = i.user_id
                                            AND u.subscriptionid = i.subscriptionid
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
                                                $resexempt['action'] = '<a href="users.php?subcmd=_remove-exemption&amp;id='.$resexempt['exemptid'].'&amp;uid='.$resexempt['user_id'].'" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')">'.$phrase['_remove'].'</a>';
                                        }
                                        else
                                        {
                                                $resexempt['status'] = $phrase['_expired'];
                                                $resexempt['action'] = '<a href="users.php?subcmd=_remove-exemption&amp;id='.$resexempt['exemptid'].'&amp;uid='.$resexempt['user_id'].'" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')">'.$phrase['_remove'].'</a>';
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
                                                $res['authenticated'] = '<a href="users.php?subcmd=_unauthorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . intval($ilance->GPC['id']) . '&amp;ccmgr=1" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to unauthorize credit card" border="0"></a>';
                                        }
                                        else
                                        {
                                                $res['status'] = ucfirst($res['creditcard_status']) . ' &amp; Unauthorized';
                                                $res['authenticated'] = '<a href="users.php?subcmd=_authorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . intval($ilance->GPC['id']) . '&amp;ccmgr=1" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to authorize credit card" border="0"></a>';
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
                                        
                                        $res['remove'] = '<a href="users.php?subcmd=_remove-creditcard&amp;id='.$res['cc_id'].'&amp;uid='.intval($ilance->GPC['id']).'" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
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
                                        $res['remove'] = '<a href="users.php?subcmd=_remove-bankaccount&amp;id='.$res['bank_id'].'&amp;uid='.intval($ilance->GPC['id']).'" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
                                        $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                        $bankaccounts[] = $res;
                                        $row_count++;
                                }
                        }
                        else
                        {
                                $show['no_bankaccounts'] = true;
                        }
						
						
							 ########################
                        ## USER NOTE INFO
                        $sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users_note WHERE user_id = '" . intval($ilance->GPC['id']) . "'");
						if ($ilance->db->num_rows($sql) > 0)
                        {
						        $row_count = 0;
                                while ($res = $ilance->db->fetch_array($sql))
                                {
								        $noteid=$res['note_id'];
										$note= ($res['note']);
										$res['noteid'] = stripslashes($res['note_id']);
										$follow_update=$res['follow_up_date'];
										$res['note'] = stripslashes($res['note']);
										$res['follow_up_date'] = stripslashes($res['follow_up_date']);
                                        $res['created'] = print_date($res['created'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
                                        $res['modified'] = print_date($res['modified'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
										$res['edit'] = '<a href="#" onClick="shownote(\''.$noteid.'\',\''.$note.'\',\''.$follow_update.'\'); return false;"><img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'icons/pencil.gif" alt="" border="0" /></a>';
										$res['remove'] = '<a href="'.'users.php?subcmd=_remove-note&amp;id='.$res['note_id'].'&amp;uid='.intval($ilance->GPC['id']).'" onClick="return confirm(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'icons/delete.gif" alt="" border="0" /></a>';
                                        $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                        $notes[] = $res;
                                        $row_count++;



}
                }
                else
                {


$show['no_notes'] = true;
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
						
						
						
						
						// for bug #5204 starts
			
			//consignor statements starts
			$user_id = $ilance->GPC['id'];  
			$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
			$sqlquery['limit'] = 'LIMIT ' . (($ilance->GPC['page'] - 1) * 50) . ',' . 50;		
			 
			
			$SQL2 = " SELECT  date_format(coins.End_Date,'%M %d, %Y') as show_statement_date, date(coins.End_Date) as statement_date , DATE_ADD(date(coins.End_Date),INTERVAL IF(DAYOFWEEK(date(coins.End_Date))=2, 0,(if(2-DAYOFWEEK(date(coins.End_Date))>0,-6,2-DAYOFWEEK(date(coins.End_Date))))) DAY) as start 
			FROM " . DB_PREFIX . "coins coins WHERE user_id = '".$user_id."'  AND End_Date != '0000-00-00' AND DAYOFWEEK(date(coins.End_Date)) = 1	 GROUP BY date(End_Date) order by End_Date desc ";
			
			$numberrows = $ilance->db->query($SQL2, 0, null, __FILE__, __LINE__);
			$number = $ilance->db->num_rows($numberrows);
			$counter = (intval($ilance->GPC['page']) - 1) * 50;
			$series = isset($ilance->GPC['series'])?$ilance->GPC['series']:0;
			$scriptpageprevnext_invoices = 'users.php?subcmd=_update-customer&id='.$user_id;
			$consignor_paginationn = print_pagnation($number, 50, intval($ilance->GPC['page']), $counter, $scriptpageprevnext_invoices);
			$res = $ilance->db->query($SQL2.$sqlquery['limit']."");
			
			$datecount = 0;			
			
			if($ilance->db->num_rows($res) > 0)
			{
					while($line = $ilance->db->fetch_array($res))
					{
				//	echo '<pre>';print_r($line);exit;
					$stmt_date=$line['statement_date'];
					$start=last_monday($stmt_date);
					$user_details['user_id']=$user_id;
					//$coin_count=$line['coin_count'];
				//	$item_count = $res_date_co['endcount'];
					// $query2="SELECT id,item_count FROM " . DB_PREFIX . "consignor_satement WHERE user_id = ".$user_details['user_id']." and date(statement_date)='".$stmt_date."'";
					
					// echo $query2;exit;
						// $result2=$ilance->db->query($query2);
						// if($ilance->db->num_rows($result2))
						// {
							// while($line2=$ilance->db->fetch_array($result2))
							// {
							 // // if($line2['item_count']==0)
							 // // {
								// // // $coin_count=statement_coin_count($user_details,$start,$stmt_date);
								// // // $query3="update " . DB_PREFIX . "consignor_satement set item_count='".$coin_count."' where id='".$line2['id']."'";
								// // // $ilance->db->query($query3);
							 // // }else
							 // // {
								// // //$coin_count=$line2['item_count'];
							 // // }
							// }
						// }else{
						// // $coin_count=statement_coin_count($user_details,$start,$stmt_date);
						 
						// // $ilance->db->query("insert " . DB_PREFIX . "consignor_satement ( user_id,statement_date,item_count ) values ('".$user_details['user_id']."','".$stmt_date."','".$coin_count."')");
						// }
					
					
					$coin_count=statement_coin_count($user_details,$start,$stmt_date);
					//$coin_count= 0;
					if($coin_count > 0)
					{
						$date1 = strtotime($line['statement_date']);
						$date2 = strtotime(date('Y-m-d'));
						if($date1 > $date2)
						{
							$date_down.='<a href="users_print.php?sef=1&subcmd=print&userid='.$user_id.'&date='.$line['statement_date'].'" style="font-size: 13px; line-height: 2em;" >'.$line['show_statement_date'].' <b>('.$coin_count.' items) - Pending</b></a> <br/>';	
						}
						else
						{
							$date_down.='<a href="users_print.php?sef=1&subcmd=print&userid='.$user_id.'&date='.$line['statement_date'].'" style="font-size: 13px; line-height: 2em;">'.$line['show_statement_date'].' <b>('.$coin_count.' items)</b></a> <br/>';	
						}
					}
					
					//echo $date_down.'<br/>'.$consignor_paginationn;exit;
					$datecount++;
					}
			}
			else
			{
				$date_down = 'Results Not Found';
				$consignor_paginationn = '';
			}
			//consignor statements ends
			
			//invoices starts
						 
		//error_reporting(E_ALL)	;

		$ilance->GPC['range_start'] = array('01','01','2015');
		$startDate = print_array_to_datetime($ilance->GPC['range_start']);
		$startDate = substr($startDate, 0, -9);

		$ilance->GPC['searchby'] = 'user_id';
		$ilance->GPC['searchkey']= $ilance->GPC['id'];
 
			 
		$ilance->GPC['range_end'] = array('02','09','2015');
		$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
		$endDate = substr($endDate, 0, -9);
		$sql2 =""; //AND (i.createdate <= '" . $endDate . "' AND i.createdate >= '" . $startDate . "')
		
		if($ilance->GPC['searchby']=='user_id' ){
			$sql2.="AND u.".$ilance->GPC['searchby']." = ".$ilance->GPC['searchkey']." ";
			 $show['all_invoices']=true;
		}
		else{
			if(!empty($ilance->GPC['searchkey']))
			$sql2.="AND ".$ilance->GPC['searchby']." = '".$ilance->GPC['searchkey']."' ";
			$show['all_invoices']=true;
		}
		
		//echo 	$sql2;exit;
			if(isset($ilance->GPC['order']))
			{
				if(isset($_SESSION['sort_order']) and $_SESSION['sort_order']=='desc')
					$_SESSION['sort_order']='';
				else
					$_SESSION['sort_order']='desc';
			}
			
			if(isset($ilance->GPC['invoice_type']))
			$show_invoice_type=$ilance->GPC['invoice_type'];
			else if(isset($_SESSION['invoice_type']))
			{
			$show_invoice_type=$_SESSION['invoice_type'];	
			}
			else
			{
				$show_invoice_type='all';	
			}
			$_SESSION['invoice_type']=$show_invoice_type;
	
		$custmr_id = $ilance->GPC['id'];
		$invoice_type_drop_down='<select name="invoice_type" id="invoice_type" onchange="window.location=\'users.php?subcmd=_update-customer&id='.$custmr_id.'&invoice_type=\'+(this.value);">
						 <option value="all" '.($show_invoice_type=='all'?'selected="selected"':'').'>All Invoices</option>
						 <option value="paid" '.($show_invoice_type=='paid'?'selected="selected"':'').'>Paid Invoices</option>
						 <option value="scheduled" '.($show_invoice_type=='unpaid'?'selected="selected"':'').'>Unpaid Invoices</option>
						 </select>';
		
	if($show_invoice_type != 'all')
	$sql2.= "and i.status='". $show_invoice_type ."' ";
	$sql3=$sql2;
	
	if(isset($ilance->GPC['order']))
	{
		$sql2.= " group by i.invoiceid order by i.invoiceid desc";
	}
	else
	{
		$sql2.= " group by i.invoiceid order by i.invoiceid desc";
	}
	$ilance->GPC['pp'] = (!isset($ilance->GPC['pp']) OR isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] <= 0) ? $ilconfig['globalfilters_maxrowsdisplay'] : intval($ilance->GPC['pp']);
$ilance->GPC['series_page'] = (!isset($ilance->GPC['series_page']) OR isset($ilance->GPC['series_page']) AND $ilance->GPC['series_page'] <= 0) ? 1 : intval($ilance->GPC['series_page']);
$counter = ($ilance->GPC['series_page'] - 1) * $ilance->GPC['pp'];
	if($ilance->GPC['subcmd'] == 'search')
	$scriptpageprevnext_all_invoices="users.php?subcmd=_update-customer&id=".$ilance->GPC['id']."&searchby=".$ilance->GPC['searchby']."&searchkey=".$ilance->GPC['searchkey']."";
	else
	$scriptpageprevnext_all_invoices="users.php?subcmd=_update-customer&id=".$ilance->GPC['id']."&invoice_type=all";
	
	$sql_pagination=" LIMIT ". (($ilance->GPC['series_page'] - 1) * 50) . "," . 50;
	$columns="select i.invoiceid,i.user_id,u.first_name,u.last_name,u.email,u.username,i.status,i.amount,i.totalamount,i.paymethod,i.paiddate,i.paid,
	i.combine_project ,s.shipped_items,ns.non_shipped_items,
	LENGTH(i.combine_project) - LENGTH(REPLACE(i.combine_project, ',', ''))+1 as invoice_item_count";

	$final_query1="
	from ".DB_PREFIX."invoices i 
	left join ".DB_PREFIX."users u on u.user_id=i.user_id 
	left join  (select count(ship_id) as shipped_items,final_invoice_id from ".DB_PREFIX."shippnig_details  where track_no!='' group by final_invoice_id) s on s.final_invoice_id=i.invoiceid
	left join (select count(ship_id) as non_shipped_items,final_invoice_id from ".DB_PREFIX."shippnig_details  where track_no='' group by final_invoice_id) ns on 				ns.final_invoice_id=i.invoiceid 
	where i.combine_project!='' ".$sql2;

//	 echo $final_query1;exit;
	$final_query2="
	from ".DB_PREFIX."invoices i 
	left join ".DB_PREFIX."users u on u.user_id=i.user_id 
	where i.combine_project!='' ".$sql3;
	
	
	
	//echo 'select count(i.invoiceid)  '.$final_query2.'<br/><br/><br/>';
	
	//echo $columns.$final_query1.$sql_pagination;//exit;

	$sql_owing = $ilance->db->query("SELECT sum(amount) as total_owing_amount,
			SUM(taxamount) AS total_tax  FROM ".DB_PREFIX."invoices  
			WHERE user_id = '".$ilance->GPC['id']."' AND status != 'paid' AND combine_project != '' ");
	$invoices_owing = $ilance->db->fetch_array($sql_owing);

	$total_amount = $invoices_owing['total_owing_amount']+$invoices_owing['total_tax'];

	$total_owing_amount = $ilance->currency->format($total_amount);
	//
	$second_level1 = $ilance->db->query('select count(i.invoiceid)  '.$final_query2 , 0, null, __FILE__, __LINE__ );
	$tmp=$ilance->db->fetch_array($second_level1);
	$second_level_number=$tmp['0'];
 	$sql=$ilance->db->query($columns.$final_query1.$sql_pagination, 0, null, __FILE__, __LINE__) ;
		if($ilance->db->num_rows($sql) > 0)
		{
		   while($res = $ilance->db->fetch_array($sql))
		   {	
		   // echo '<pre>';
				// print_r($res);
				// exit;
			 if($res['status'] == 'paid')
			 {
			   $res['status'] = ucfirst(strtolower($res['status']));
			   //new changes
			   $status_paid = $res['status'];
			 }
			if($res['status'] != 'Paid')
			 {
			 if($res['paid']==0)
			  $res['status'] = 'Unpaid';
			else
			  $res['status'] = '<a href='.$ilpage['buyers']. '?cmd=buyer&amp;subcmd=update&amp;invoiceid=' . intval($res['invoiceid']) . '&amp;user_id='.intval($res['user_id']).'>Partially Paid</a>';
			  
			  $status_paid = 'Unpaid';
			 }
			// error_reporting(E_ALL);
 
			 
			 $res['user_name'] = '<a href="'.'users.php?subcmd=_update-customer&amp;id='.$res['user_id'].'"">'.$res['username'].'</a>';
			 //new change apr25 new variable added in line
			  $res['details']='<a href="buyers.php?subcmd=_detail_invoice&user_id='.$res['user_id'].'&paidstatus='.$status_paid.'&amp;id='.$res['invoiceid'].'"">Items</a>';
			 $res['print']='<a href="print_invoice2.php?id='.$res['invoiceid'].'"">Print</a>';
			 $res['paymethod']=ucfirst(strtolower($res['paymethod']));
				 if($res['invoice_item_count']<=$res['shipped_items'])
				{
					$res['ship_status']='Y';
				}elseif($res['shipped_items']>0 and $res['shipped_items']<$res['invoice_item_count'])
				{
					$res['ship_status']='Partial';
				}else if($res['shipped_items']==0)
				{
					$res['ship_status']='N';
				}

			 $invoicelist[] = $res;
			
			
		   }
		   
		}
		else
		{
				$invoicelist_empty = 'Results Not Found';
		}
		$series_prevnext = print_pagnation($second_level_number, 50, $ilance->GPC['series_page'], $counter, $scriptpageprevnext_all_invoices, 'series_page');	
			//invoices ends
			
		
// currently consinged coins & return consignment start
		$show['showsearch_consign'] = true;
		//$counter = ($ilance->GPC['series_page'] - 1) * $ilance->GPC['pp'];
		$counter_k = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];				
		//$counter_k = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];		
		$scriptpageprevnext_k = "users.php?subcmd=_update-customer&id=".$ilance->GPC['id'];	 
		/*if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page_k']) AND $ilance->GPC['page'] <= 0)
		{
			$ilance->GPC['page'] = 1;
		}
		else
		{
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		}
		*/
		
		$filtervalue_consign = $ilance->GPC['id'];
		//echo $filtervalue_consign;

		$sql_pagination_k =" LIMIT ". (($ilance->GPC['page'] - 1) * 100) . "," . 100;

		$suku_5355 = "SELECT c.coin_id,c.Title,ROUND(c.Minimum_bid,2) as minc,
					ROUND(c.Buy_it_now,2) as buyc,c.Create_Date,c.End_Date,
					ROUND(p.currentprice,2) as currentp,p.bids  
			FROM " . DB_PREFIX . "coins c
			left join " . DB_PREFIX . "projects p on p.project_id=c.project_id
			WHERE c.user_id = '".$filtervalue_consign."' 
			and(p.winner_user_id=0 or p.project_id is null or (p.buynow>0 and p.filtered_auctiontype='fixed' and p.hasbuynowwinner ='0'))
			ORDER BY c.coin_id  ASC";

		$select_all_consign_coins_pg = $ilance->db->query("
									SELECT c.coin_id,c.Title,ROUND(c.Minimum_bid,2) as minc,
											ROUND(c.Buy_it_now,2) as buyc,c.Create_Date,c.End_Date,
											ROUND(p.currentprice,2) as currentp,p.bids,
											IF(p.project_id > 0, c.relist_count+1, 0) as listed_count, c.pcgs as coin_pcgs  
									FROM " . DB_PREFIX . "coins c
									left join " . DB_PREFIX . "projects p on p.project_id=c.coin_id
									WHERE c.user_id = '".$filtervalue_consign."' 
									and((p.winner_user_id=0 and p.filtered_auctiontype='regular')  or p.project_id is null or (p.buynow>0 and p.filtered_auctiontype='fixed' and p.hasbuynowwinner ='0'))
									ORDER BY c.coin_id  ASC			
									".$sql_pagination_k."");

									
		if($ilance->db->num_rows($select_all_consign_coins_pg) > 0)
		{
			$row_con_list = 0;
								
			$select_all_consign_coins = $ilance->db->query("
									SELECT c.coin_id,c.Title,ROUND(c.Minimum_bid,2) as minc,
											ROUND(c.Buy_it_now,2) as buyc,c.Create_Date,c.End_Date,
											ROUND(p.currentprice,2) as currentp,p.bids
									FROM " . DB_PREFIX . "coins c
									left join " . DB_PREFIX . "projects p on p.project_id=c.coin_id
									left join " . DB_PREFIX . "ebay_listing_rows e ON e.coin_id = c.coin_id
									WHERE c.user_id = '".$filtervalue_consign."' 
									and((p.winner_user_id=0 and p.filtered_auctiontype='regular') or p.project_id is null or (p.buynow>0 and p.filtered_auctiontype='fixed' and p.hasbuynowwinner ='0' ))
									AND e.coin_id IS NULL
									ORDER BY c.coin_id  ASC			
									 "); 

			$consing_number = (int)$ilance->db->num_rows($select_all_consign_coins); 

			while($row_list = $ilance->db->fetch_array($select_all_consign_coins_pg))
			{	
				$row_list['checkval'] = '<input type="checkbox" name="val[]" id="my" 
						value="'.$row_list['coin_id'].'" onclick="return myself(this.value);" >';     

				$row_list['coin_id'] ;
				$row_list['coin_pcgs'] ;
				$row_list['minc'] ; 
				$row_list['buyc'] ; 
				$row_list['Title'] ; 
				if($row_list['currentp'] == null)
				{
					$row_list['currentp'] = 0;
				}
				else
				{
					$row_list['currentp'];
				}
				
				if($row_list['bids'] == null)
				{
					$row_list['bids'] = 0;
				}
				else
				{
					$row_list['bids'] ;
				}
				
				$row_list['Create_Date'] ; 	
				$row_list['End_Date']; 	
					
				$all_consing_coins_list[] = $row_list;					
				$row_con_list++;	
			}
				//print_r($all_consing_coins_list);	
								
		}

		$listing_pagnation = print_pagnation($consing_number, 100, $ilance->GPC['page'], $counter_k, $scriptpageprevnext_k,'page');									

// currently consinged coins & return consignment end


		//pending invoice starts
		
		$custmr_uid = $ilance->GPC['id'];			
		$ilance->tax = construct_object('api.tax');	
		$sql_regardlist = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . $ilance->GPC['id']."'
			AND status = 'unpaid'	and not combine_project
			AND isfvf != 1
			AND isif != 1 
			AND isbuyerfee != 1 
			AND isenhancementfee != 1		
		");
		
		if($ilance->db->num_rows($sql_regardlist) > 0)
		{
			
			while($res_regardlist = $ilance->db->fetch_array($sql_regardlist))
			{
				$invid[] = $res_regardlist['invoiceid'];
				
				$show['invoicecancelled'] = 0;
		
				$area_title = $phrase['_invoice_payment_menu'] . ' #' . $txn;
				$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu'];
	
				$navcrumb = array();
				$navcrumb["$ilpage[accounting]?cmd=com-transactions"] = $phrase['_accounting'];
				$navcrumb[""] = $phrase['_transaction'] . ' #' . $txn;
				
				$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders
											WHERE invoiceid = '".$res_regardlist['invoiceid']."'
											AND buyer_id = '".$ilance->GPC['id']."'");
				if($ilance->db->num_rows($buy)>0)
				{
					$resbuy = $ilance->db->fetch_array($buy);
				$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
											WHERE coin_id = '".$res_regardlist['projectid']."'");
								$temp=$ilance->db->fetch_array($bids);						
					
					$coin_no_in_set=empty($temp['nocoin'])?1:intval($temp['nocoin']);
					$res_regardlist['qty'] = $resbuy['qty'];
					 $totqty[] = $res_regardlist['qty']*$coin_no_in_set;
				}
				else
				{
				//check 	nocoin  in ilance_coins for each coins
				$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
											WHERE coin_id = '".$res_regardlist['projectid']."'");
								$temp=$ilance->db->fetch_array($bids);		
								
					$res_regardlist['qty'] = 1;
					 
					$totqty[] = empty($temp['nocoin'])?1:intval($temp['nocoin']);
				}
				
		
	
				$id = $res_regardlist['invoiceid'];
				$txn = $res_regardlist['transactionid'];
				$securekey_hidden .= '<input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="txn" value="' . $txn . '" />';
	
				($apihook = $ilance->api('invoicepayment_transaction_view_condition_end')) ? eval($apihook) : false;

		$amountpaid =  $ilance->currency->format(0);
		// invoice creation date
				$createdate = print_date($res_regardlist['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$show['miscamount']=false;
				 $res_regardlist['miscamount'];
				if($res_regardlist['miscamount']>0)
				{
				$show['miscamount']=true;
				$miscamount =  $ilance->currency->format($res_regardlist['miscamount']);
				}
		// invoice due date
		if ($res_regardlist['duedate'] == "0000-00-00 00:00:00")
		{
			$duedate = '--';		
		}
		else
		{
			$duedate = print_date($res_regardlist['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		}
		// invoice paid date
		if ($res_regardlist['paiddate'] == "0000-00-00 00:00:00")
		{
			$paiddate = '--';
		}
		else
		{
			$paiddate = print_date($res_regardlist['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		}
		
		// invoice identifier
		$invoiceid = $id;
		
		$show['ispaid'] = $show['isunpaid'] = $show['isscheduled'] = $show['iscomplete'] = $show['iscancelled'] = 0;
		
		if ($res_regardlist['status'] == 'paid')
		{
			$show['ispaid'] = 1;
		}
		if ($res_regardlist['status'] == 'unpaid')
		{
			$show['isunpaid'] = 1;
		}
		if ($res_regardlist['status'] == 'scheduled')
		{
			$show['isscheduled'] = 1;
		}
		if ($res_regardlist['status'] == 'complete')
		{
			$show['iscomplete'] = 1;
		}
		if ($res_regardlist['status'] == 'cancelled')
		{
			$show['iscancelled'] = 1;
		}			
		if ($res_regardlist['invoicetype'] == 'subscription')
		{
			$show['subscriptionpayment'] = true;
		}
		else
		{
			$show['subscriptionpayment'] = false;
		}
		
		
				
				if ($res_regardlist['status'] == 'unpaid' OR $res_regardlist['status'] == 'scheduled')
				{
					if ($res_regardlist['p2b_user_id'] == $ilance->GPC['id'])
					{
						$show['paymentpulldown'] = 0;
						$cmd = '_do-invoice-action';
					}
					else if ($res_regardlist['user_id'] == $ilance->GPC['id'])
					{
						$show['paymentpulldown'] = 1;
						$cmd = '_do-invoice-preview';
					}
				}
				else if ($res_regardlist['status'] == 'cancelled')
				{
					$show['invoicecancelled'] = 1;
				}
				else
				{
					$show['paymentpulldown'] = 0;
					$cmd = '_do-invoice-action';
				}
				
				
				$show['listing'] = 0;
				$project_id = 0;
				if ($res_regardlist['projectid'] > 0)
				{
				$show['listing'] = 1;
				$listing = fetch_coin_table('Title',$res_regardlist['projectid']);
				$haswinner = fetch_auction('haswinner', $res_regardlist['projectid']);			
				$project_id = $res_regardlist['projectid'];
				$projects[] = $res_regardlist['projectid'];
				}
				// tax check 
				$taxdetails = $res_regardlist['istaxable'];
				$show['buyer'] = 0;
				$buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
												WHERE projectid = '".$res_regardlist['projectid']."'
												AND user_id = '".$ilance->GPC['id']."'
												AND isbuyerfee = '1'");
												
					if($ilance->db->num_rows($buyfee_inv) > 0)
					{
						$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
						$totalamountlist = $ilance->currency->format(($res_regardlist['amount'] + $res_buyfee['amount'] ), $res_regardlist['currency_id']);
						$buyerfee =  $ilance->currency->format($res_buyfee['amount'], $res_regardlist['currency_id']);
						$buyerfee1 = $res_buyfee['amount'];
						$totalamountlist1 = $res_regardlist['amount'] + $res_buyfee['amount'] ;
						$show['buyer'] = 1;
					}
					else
					{
						$totalamountlist = $ilance->currency->format(($res_regardlist['amount'] ), $res_regardlist['currency_id']);
						$buyerfee =  $ilance->currency->format(0, $res_regardlist['currency_id']);
						$buyerfee1 = 0;
						$totalamountlist1 = $res_regardlist['amount'];
						$show['buyer'] = 1;
					}
										
					$paymethod = ucwords($res_regardlist['paymethod']);
					$paystatus = ucwords($res_regardlist['status']);
					$providername = $phrase['_billing_and_payments'];
					$provider = SITE_NAME;
					$providerinfo = SITE_ADDRESS;
					
					$show['viewingasprovider'] = $show['escrowblock'] = false;
					if ($res_regardlist['invoicetype'] == 'escrow')
					{
			// escrow handling
			$show['providerblock'] = true;
			$show['escrowblock'] = true;
			
			$ilance->auction = construct_object('api.auction');
			$ilance->escrow = construct_object('api.escrow');
			
			
				$customer = fetch_user('username', $res_regardlist['user_id']);
				$customeremail = fetch_user('email', $res_regardlist['user_id']);
				$customerinfo = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
				$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));	
					
			// display invoice type on invoice payment form
			$invoicetype = print_transaction_type($res_regardlist['invoicetype']);
		}
		
					if ($res_regardlist['invoicetype'] == 'debit')
					{
			// escrow handling
			$show['providerblock'] = true;
			$show['escrowblock'] = true;
				
			$ilance->auction = construct_object('api.auction');
			$ilance->escrow = construct_object('api.escrow');
			
			
				$customer = fetch_user('username', $res_regardlist['user_id']);
				$customeremail = fetch_user('email', $res_regardlist['user_id']);
				$customerinfo = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
				$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));	
					
			// display invoice type on invoice payment form
			$invoicetype = print_transaction_type($res_regardlist['invoicetype']);
		}
					
					else if ($res_regardlist['invoicetype'] == 'buynow')
					{
						$show['providerblock'] = true;
						$customer = fetch_user('username', $res_regardlist['user_id']);
						$customeremail = fetch_user('email', $res_regardlist['user_id']);						
						$invoicetype = print_transaction_type($res_regardlist['invoicetype']);
						$customerinfo = print_shipping_address_text($res_regardlist['user_id']) . fetch_business_numbers($res_regardlist['user_id']);						
						$customername = fetch_user('fullname', $res_regardlist['user_id']);
						
					}
				$description .= stripslashes($res_regardlist['description']).'<br>';
				$amountcal[] = $res_regardlist['amount'];
				$taxinfolist = $res_regardlist['taxinfo'];
				$invoicetype = $res_regardlist['invoicetype'];
				$buyerfeecal[] = $buyerfee1;
				$totalamountlistcal[] = $totalamountlist1;
			
				
				$res_regardlist['item_id'] 	 = 	$res_regardlist['projectid'];
				$res_regardlist1['itemtitle'] = fetch_coin_table('Title',$res_regardlist['projectid']);
				/*======vijay bug id:4714 start=====*/
				if($res_regardlist['Site_Id'] >0)
				{
				$res_regard='eBay';
				}
				else
				{
				$res_regard='GC';
				}
				$res_regardlist['Site_Id'] 	 =$res_regard;
				/*======vijay bug id:4714 end=====*/
				
				if ($ilconfig['globalauctionsettings_seourls'])
				{
				
					$res_regardlist['item_id']='<a href="'.HTTPS_SERVER.'Coin/'.$res_regardlist['projectid'].'/'.construct_seo_url_name($res_regardlist1['itemtitle']).'"> '.$res_regardlist['item_id'].'</a>';
					$res_regardlist['itemtitle'] ='<a href="'.HTTPS_SERVER.'Coin/'.$res_regardlist['projectid'].'/'.construct_seo_url_name($res_regardlist1['itemtitle']).'"> '.$res_regardlist1['itemtitle'].'</a>';
					
				}
				else
				{
					$res_regardlist['item_id']='<a href="merch.php?id='.$res_regardlist['projectid'].'">'.$res_regardlist1['item_id'].'</a>';
					$res_regardlist['itemtitle']='<a href="merch.php?id='.$res_regardlist['projectid'].'">'.$res_regardlist1['itemtitle'].'</a>';
				}
						
					
				//$res_regardlist['itemtitle'] = fetch_auction('project_title', $res_regardlist['projectid']);
				$res_regardlist['finalprice'] = $ilance->currency->format($res_regardlist['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
				$res_regardlist['buyerfees'] = $buyerfee;
				$res_regardlist['totallistamount'] =  $totalamountlist;
			  	$regardlist[] = $res_regardlist;
				
			}
			$show['pending_invoice'] = true;
			$show['taxes'] = 0;
			
			$qtyhidden = '<input type = "hidden" name="qtyhidden" id="qtyhidden" value="'.array_sum($totqty).'">';
			$invidim = implode(',',$invid);
			$invidhidden = '<input type = "hidden" name="invhidden" id="invhidden" value="'.$invidim.'">';
			$amounttotal = array_sum($totalamountlistcal);
			$amount = $ilance->currency->format(array_sum($totalamountlistcal),$ilconfig['globalserverlocale_defaultcurrency']);
			//karthik start Apr 15
			 $_SESSION['ilancedata']['user']['totalamount']=array_sum($totalamountlistcal);
			// end
			
			//karthik on sep06 for sales tax reseller
			 $sales_tax_reseller = fetch_user('issalestaxreseller',$ilance->GPC['id']);	
			 
			if ($ilance->tax->is_taxable($ilance->GPC['id'], $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] >= $amounttotal AND $sales_tax_reseller!='1')
             {			 	
				$state = fetch_user('state',$ilance->GPC['id']);			
				 $taxinfo = 'Sales Tax ('.$ilance->tax->fetch_taxdetails($ilance->GPC['id'], $amounttotal, 'buynow', 0).'%, '.$state.')';
				 
				 //new changes apr22 hiddeen taxinfo variable
				 
				$taxinfonew = $ilance->tax->fetch_taxdetails($ilance->GPC['id'], $amounttotal, 'buynow', 0);
				$taxamount1 = $ilance->tax->fetch_amount($ilance->GPC['id'], $amounttotal, 'buynow', 0);
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				
				$show['taxes'] = 1;
			}
			else if ($ilance->tax->is_taxable($ilance->GPC['id'], $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] <= $amounttotal)
			{						
				 $taxinfo = 'Sales Tax Not Applicable ('.$state.'over $1,500)';
				$taxamount1 = $ilance->tax->fetch_amount($ilance->GPC['id'], 0, 'buynow', 0);
				
				$taxinfonew = 0.00;
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				$show['taxes'] = 1;
			}
			else 
			{						
				 $taxinfo = 'Sales Tax Not Applicable (Out of State)';
				$taxamount1 = $ilance->tax->fetch_amount($ilance->GPC['id'], 0, 'buynow', 0);
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				$show['taxes'] = 1;
				
				$taxinfonew = 0.00;
			}
			$buyerfe = array_sum($buyerfeecal);
			$buyerfee = $ilance->currency->format($buyerfe,$ilconfig['globalserverlocale_defaultcurrency']);
			//suku1
			$taxamount1=empty($taxamount1)?"0":$taxamount1;
			$taxamounthidden = '<input type = "hidden" name="taxhidden" id="taxhidden" value="'.$taxamount1.'">
			                    <input type = "hidden" name="taxinfonew" id="taxinfonew" value="'.$taxinfonew.'">
								<input type = "hidden" name="taxhidden1" id="taxhidden1" value="'.$taxamount1.'">
								<input type = "hidden" name="taxshipcal" id="taxshipcal" value="0">';
		
		// murugan changes on feb 28	
		//if ($taxdetails)
		if ($taxamount1 > 0)
		{			
			$totalamount = $ilance->currency->format(($amounttotal + $taxamount1), $ilconfig['globalserverlocale_defaultcurrency']);
			$totalamountnew = $amounttotal + $taxamount1;
			//suku
			$totalhidden = '<input type = "hidden" name="totalhidden" id="totalhidden" value="'.$totalamountnew.'">
			                <input type = "hidden" name="taxhiddenyes" id="taxhiddenyes" value="1">';			
			$show['taxes'] = 1;
		}
		else
		{
			
			$totalamount = $ilance->currency->format($amounttotal, $ilconfig['globalserverlocale_defaultcurrency']);
			$totalamountnew = $amounttotal;
			//suku1
			$totalhidden = '<input type = "hidden" name="totalhidden" id="totalhidden"  value="'.$totalamountnew.'">
			                <input type = "hidden" name="taxhiddenyes" id="taxhiddenyes" value="0">';			
		}
		
		$totalhidden.= '<input type = "hidden" name="totalhidden_base" id="totalhidden_base"  value="'.$totalamountnew.'">';



//bug # 4514 kumaravel start

		//vijay work start for bug id #4409  - Payment Restrictions Not Working
	if (!empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] >0)
	{
	
		$payment_method_pulldown = print_paymethod_pulldown('invoicepayment', 'account_id', $ilance->GPC['id'],'','staff');
		
	}
	else
	{
		
		if( $_SESSION['ilancedata']['user']['totalamount'] < 10000)
		{	
			$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
										<optgroup label="Online Payment">';
			//exit;
			$user_paymethods_sql=$ilance->db->query("select allowed_paymethods 
													from ".DB_PREFIX."users 
													where user_id =".$ilance->GPC['id']);
			$user_paymethods=$ilance->db->fetch_array($user_paymethods_sql);
					//	exit;
			$paymethod_sql=$ilance->db->query("	select * 
												from ".DB_PREFIX."payment_methods 
												where id in (".$user_paymethods['allowed_paymethods'].") 
												order by sort");
			while($allowed_methods=$ilance->db->fetch_array($paymethod_sql))
			{
				$payment_method_pulldown.='<option value="'.$allowed_methods['value'].'">'.$phrase[$allowed_methods['title']].'</option>';
			}
				$payment_method_pulldown.='</optgroup></select>';		
		}	
		else
		{
			$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
										<optgroup label="Online Payment">';
			//exit;
			$user_paymethods_sql=$ilance->db->query("select allowed_paymethods 
													from ".DB_PREFIX."users 
													where user_id =".$ilance->GPC['id']);
			$user_paymethods=$ilance->db->fetch_array($user_paymethods_sql);
			//exit;
			// bug # 4654 - kumaravel 			
			$paymethod_sql=$ilance->db->query("	select * 
												from ".DB_PREFIX."payment_methods 
												where id in (".$user_paymethods['allowed_paymethods'].") 
												and id NOT IN (6,10)
												order by sort");
			while($allowed_methods=$ilance->db->fetch_array($paymethod_sql))
			{
				$payment_method_pulldown.='<option value="'.$allowed_methods['value'].'">'.$phrase[$allowed_methods['title']].'</option>';
			}
				$payment_method_pulldown.='</optgroup></select>';		
		}

	}
				//vijay work end for bug id #4409  - Payment Restrictions Not Working
				

	if($ilance->GPC['id']==82)
	{
		$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
									<optgroup label="Online Payment">';
		
		$user_paymethods_sql=$ilance->db->query("	select allowed_paymethods 
													from ".DB_PREFIX."users 
													where user_id =".$ilance->GPC['id']);
		$user_paymethods=$ilance->db->fetch_array($user_paymethods_sql);
        // bug # 4654 - kumaravel 
		if( $_SESSION['ilancedata']['user']['totalamount'] < 10000)
		{
			$paymethod_sql=$ilance->db->query("	select * 
											from ".DB_PREFIX."payment_methods 
											where id in (".$user_paymethods['allowed_paymethods'].") 
											order by sort"); 			
		}
		else
		{
			$paymethod_sql=$ilance->db->query("	select * 
											from ".DB_PREFIX."payment_methods 
											where id in (".$user_paymethods['allowed_paymethods'].")
											and id NOT IN (6,10)
											order by sort"); 			
		}									
		while($allowed_methods=$ilance->db->fetch_array($paymethod_sql))
		{
			$payment_method_pulldown.='<option value="'.$allowed_methods['value'].'">'.$phrase[$allowed_methods['title']].'</option>';
		}
			$payment_method_pulldown.='</optgroup></select>';
	}
    


//bug # 4514 kumaravel end



	
		$shippment_nethod_pulldown = print_shippment_nethod_pulldown($projects,$selected=0,'shipper_id','return change_shipper();',array_sum($totqty));
		
	
		$shipper_drop_down = $shippment_nethod_pulldown['html'];
		//suku
		$headinclude.='<script>
		function change_shipper()
		{
          
		var shippers_base_cost=new Array(); 
		var shippers_added_cost=new Array();
		var international_extra_morethen_n_coins=0;
		
		'.$shippment_nethod_pulldown['script'].'
		var shipper=document.getElementById("shipper_id").value;
		
		// karthik start apr 16
		var taxamt = document.getElementById("taxhidden").value;
		

		var taxpresent = document.getElementById("taxhiddenyes").value;
		

		var taxinfonew = document.getElementById("taxinfonew").value;
		

		
		if(shipper == 26 && shippers_base_cost[shipper] == 0)
		
		{
		document.getElementById("free_announce").innerHTML ="<span class=\"green\">Standard shipping is free for your first auction purchase (U.S. only)</span>";
		
		}
		
		else
		
		{
		
         
		  
		}
		
		  //end
		//var totalproject = document.getElementById("total_val").value;
		if(shipper>0)
		{
			//document.getElementById("sub").disabled = false;
			
			invhidden=document.getElementById("invhidden").value;
			qtyhiddennew=document.getElementById("qtyhidden").value;
			projectlist=invhidden.split(",");
			
			//var txt = parseFloat(projectlist.length) - parseFloat(totalproject);
			var txt = parseFloat(projectlist.length);
			// muruagn changes on apr 17 for qty
			//var no_item=txt;
			var no_item=qtyhiddennew;
					 
			if(projectlist.length > 0)
		    {
			var shipping_total=(no_item)*shippers_added_cost[shipper]+shippers_base_cost[shipper];
		    }
			else
			{
			
			}
			var shipping_total=(no_item)*shippers_added_cost[shipper]+shippers_base_cost[shipper];
			 
			shipping_total=shipping_total+international_extra_morethen_n_coins;
			shipping_cost=shipping_total.toFixed(2);
 //new change calculating  tax amount for shipping
			
			var taxcount = (taxinfonew *  shipping_cost) / 100;
			
			document.getElementById("taxshipcal").value = taxcount;
						
			var taxadd = parseFloat(document.getElementById("taxhidden1").value) + parseFloat(taxcount);
			
			newtaxadd = taxadd.toFixed(2);
		   
		    document.getElementById("taxhidden").value = newtaxadd;
			
			//end
			document.getElementById("shipping_cost").value=shipping_cost;
			calculate_total();
		}else
		{
		
		//document.getElementById("sub").disabled = true;
		document.getElementById("shipping_cost").value="0";
		calculate_total();
		}
 	  return false;
		}
 
function promocodecheck(val,user_id)
{
 if (window.XMLHttpRequest) { // Mozilla & other compliant browsers
		request = new XMLHttpRequest();
	} else if (window.ActiveXObject) { // Internet Explorer
		request = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	request.onreadystatechange = function ajaxResponse(){
		if (request.readyState==4){
		returned=request.responseText;
			result=returned.split("|");
			if(result[1]=="$" || result[1]=="%")
			{
			var discount=parseFloat(result[0]);
			var temp22=discount.toFixed(2);
			document.getElementById("disount_val").value=temp22;
			if(result[1]=="$")
			discount_str="US$"+temp22+" from total amount";
			if(result[1]=="%")
			discount_str=discount+" % from total amount";
			document.getElementById("promodiv").innerHTML= "You have saved "+discount_str;
			calculate_total();
			}else
			{
			document.getElementById("promodiv").innerHTML= returned;
			document.getElementById("disount_val").value=0;
			calculate_total();
			}
		}else
		{
			document.getElementById("promodiv").innerHTML= "<img src=\"images/default/working.gif\"/>";	
		}
	}
	url ="ajax.php?promocodeauction=" +val+"&projectid="+user_id;
	request.open("GET", url,true);
	request.send(null);
}
function calculate_total()
{
totalhidden_base=parseFloat(document.getElementById("totalhidden_base").value);
disount_val=parseFloat(document.getElementById("disount_val").value);
shipping_cost=parseFloat(document.getElementById("shipping_cost").value);
//new changes apr22
tax_cost=parseFloat(document.getElementById("taxhidden").value);
tax_cost_inship = parseFloat(document.getElementById("taxshipcal").value);
totalhidden=totalhidden_base-disount_val+shipping_cost+tax_cost_inship;
document.getElementById("totalhidden").value=totalhidden;
disount_val_text=disount_val.toFixed(2);
shipping_cost_text=shipping_cost.toFixed(2);
totalhidden_text=totalhidden.toFixed(2);
//apr22
document.getElementById("sales_tax_div").innerHTML="US$"+tax_cost.toFixed(2)+"";
document.getElementById("dicount_amount_div").innerHTML="(US$"+disount_val_text+")";
document.getElementById("ship_cost_div").innerHTML="US$"+shipping_cost_text;
document.getElementById("totalamount_area").innerHTML="US$"+totalhidden_text;
//oct-31

document.getElementById("totalamt_area").innerHTML="US$"+totalhidden_text;
}
</script>
		';
		
		
		$onload = 'javascript:document.invoicepayment.reset();change_shipper();';
		$user_id=$ilance->GPC['id'];

		}
		else
		{
			$show['invoice_not_found'] = true;
		}
	
		
		//pending invoice ends	
		
		// vijay work for split the invoice start//
		
			$giv_user_id=(isset($ilance->GPC['id'])) ? $ilance->GPC['id'] : '';
		
		
			
			$ifcoins=0;

			
			$ilance->tax = construct_object('api.tax');	
			// Murugan Code On Feb 11 Starts Here for Changes of invoice payment
				$sql_regardlist_split = $ilance->db->query("
				SELECT i.*,u.country
				FROM " . DB_PREFIX . "invoices i
				join " . DB_PREFIX . "users  u on u.user_id ='" . $giv_user_id."' and u.status = 'active'
				WHERE i.user_id = '" . $giv_user_id."'
				AND i.status = 'unpaid'	and not i.combine_project
				AND i.isfvf != 1
				AND i.isif != 1 
				AND i.isbuyerfee != 1 
				AND i.isenhancementfee != 1
						
				");

				
				$invcount = $ilance->db->num_rows($sql_regardlist_split);

				if($invcount > 0)
				{
					$ifcoins=1;
					
					while($res_regardlist_split = $ilance->db->fetch_array($sql_regardlist_split))
					{
						
						// echo '<pre>';
						// print_r($res_regardlist_split);
						
						$invid_split[] = $res_regardlist_split['invoiceid'];
						$giv_countryid=$res_regardlist_split['country'];
						$show['invoicecancelled_split'] = 0;
						$showchkbox_split=0;
						if($invcount > 1)
						{
						$showchkbox_split=1;
						$res_regardlist_split['action'] = '<input checked type="checkbox" id="checkbox-1" name="select_invoiceid_split[]" value="'. $res_regardlist_split['invoiceid'].'" />';
						
						}
						else{
						$res_regardlist_single_proj_split = '<input type="hidden"  name="select_invoiceid_split[]" value="'.$res_regardlist_split['invoiceid'].'" />';
						}

						
						$buy_split = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders
													WHERE invoiceid = '".$res_regardlist_split['invoiceid']."'
													AND buyer_id = '".$giv_user_id."'");
						if($ilance->db->num_rows($buy_split)>0)
						{
							$resbuy_split = $ilance->db->fetch_array($buy_split);
							$bids_split = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
													WHERE coin_id = '".$res_regardlist_split['projectid']."'");
										$temp_split=$ilance->db->fetch_array($bids_split);						
							
							$coin_no_in_set_split=empty($temp_split['nocoin'])?1:intval($temp_split['nocoin']);
							$res_regardlist_split['qty'] = $resbuy_split['qty'];
							$totqty_split[] = $res_regardlist_split['qty']*$coin_no_in_set_split;
						}
						else
						{
						//check 	nocoin  in ilance_coins for each coins
						$bids_split = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
													WHERE coin_id = '".$res_regardlist_split['projectid']."'");
										$temp_split=$ilance->db->fetch_array($bids_split);		
										
							$res_regardlist_split['qty'] = 1;
							 
							$totqty_split[] = empty($temp_split['nocoin'])?1:intval($temp_split['nocoin']);
						}
						

				//$res_invoice = $ilance->db->fetch_array($sql_invoice);
						$id_split = $res_regardlist_split['invoiceid'];
						$txn_split = $res_regardlist_split['transactionid'];
						$securekey_hidden_split .= '<input type="hidden" name="id" value="' . $id_split . '" /><input type="hidden" name="txn" value="' . $txn_split . '" />';

						($apihook = $ilance->api('invoicepayment_transaction_view_condition_end')) ? eval($apihook) : false;
					
				// total amount paid for this invoice
						//$amountpaid = $ilance->currency->format($res_regardlist_split['paid'], $res_regardlist_split['currency_id']);
				$amountpaid_split =  $ilance->currency->format(0);
				// invoice creation date
						$createdate_split = print_date($res_regardlist_split['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$show['miscamount_split']=false;
						 $res_regardlist_split['miscamount'];
						if($res_regardlist_split['miscamount']>0)
						{
						$show['miscamount_split']=true;
						$miscamount_split =  $ilance->currency->format($res_regardlist_split['miscamount']);
						}
				// invoice due date
				if ($res_regardlist_split['duedate'] == "0000-00-00 00:00:00")
				{
					$duedate_split = '--';		
				}
				else
				{
					$duedate_split = print_date($res_regardlist_split['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
				// invoice paid date
				if ($res_regardlist_split['paiddate'] == "0000-00-00 00:00:00")
				{
					$paiddate_split = '--';
				}
				else
				{
					$paiddate_split = print_date($res_regardlist_split['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}

				// invoice identifier
				$invoiceid_split = $id_split;

				$show['ispaid_split'] = $show['isunpaid_split'] = $show['isscheduled_split'] = $show['iscomplete_split'] = $show['iscancelled_split'] = 0;

				if ($res_regardlist_split['status'] == 'paid')
				{
					$show['ispaid_split'] = 1;
				}
				if ($res_regardlist_split['status'] == 'unpaid')
				{
					$show['isunpaid_split'] = 1;
				}
				if ($res_regardlist_split['status'] == 'scheduled')
				{
					$show['isscheduled_split'] = 1;
				}
				if ($res_regardlist_split['status'] == 'complete')
				{
					$show['iscomplete_split'] = 1;
				}
				if ($res_regardlist_split['status'] == 'cancelled')
				{
					$show['iscancelled_split'] = 1;
				}			
				if ($res_regardlist_split['invoicetype'] == 'subscription')
				{
					$show['subscriptionpayment_split'] = true;
				}
				else
				{
					$show['subscriptionpayment_split'] = false;
				}


						
						if ($res_regardlist_split['status'] == 'unpaid' OR $res_regardlist_split['status'] == 'scheduled')
						{
							if ($res_regardlist_split['p2b_user_id'] == $giv_user_id)
							{
								$show['paymentpulldown_split'] = 0;
								$cmd_split = '_do-invoice-action';
							}
							else if ($res_regardlist_split['user_id'] == $giv_user_id)
							{
								$show['paymentpulldown_split'] = 1;
								$cmd_split = '_do-invoice-preview';
							}
						}
						else if ($res_regardlist_split['status'] == 'cancelled')
						{
							$show['invoicecancelled_split'] = 1;
						}
						else
						{
							$show['paymentpulldown_split'] = 0;
							$cmd_split = '_do-invoice-action';
						}
						
						
						$show['listing_split'] = 0;
						$project_id_split = 0;
						if ($res_regardlist_split['projectid'] > 0)
						{
						$show['listing_split'] = 1;
						$listing_split = fetch_coin_table('Title',$res_regardlist_split['projectid']);
						$haswinner_split = fetch_auction('haswinner', $res_regardlist_split['projectid']);			
						$project_id_split = $res_regardlist_split['projectid'];
						$projects_split[] = $res_regardlist_split['projectid'];
						}
						// tax check 
						$taxdetails_split = $res_regardlist_split['istaxable'];
						$show['buyer_split'] = 0;
						$buyfee_inv_split = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
														WHERE projectid = '".$res_regardlist_split['projectid']."'
														AND user_id = '".$giv_user_id."'
														AND isbuyerfee = '1'");
							if($ilance->db->num_rows($buyfee_inv_split) > 0)
							{
								$res_buyfee_split = $ilance->db->fetch_array($buyfee_inv_split);
								$totalamountlist_split = $ilance->currency->format(($res_regardlist_split['amount'] + $res_buyfee_split['amount'] ), $res_regardlist_split['currency_id']);
								$buyerfee_split =  $ilance->currency->format($res_buyfee_split['amount'], $res_regardlist_split['currency_id']);
								$buyerfee1_split = $res_buyfee_split['amount'];
								$totalamountlist1_split = $res_regardlist_split['amount'] + $res_buyfee_split['amount'] ;
								$show['buyer_split'] = 1;
							}
							else
							{
								$totalamountlist_split = $ilance->currency->format(($res_regardlist_split['amount'] ), $res_regardlist_split['currency_id']);
								$buyerfee_split =  $ilance->currency->format(0, $res_regardlist_split['currency_id']);
								$buyerfee1_split = 0;
								$totalamountlist1_split = $res_regardlist_split['amount'];
								$show['buyer_split'] = 1;
							}
							
							
							$paymethod_split = ucwords($res_regardlist_split['paymethod']);
							$paystatus_split = ucwords($res_regardlist_split['status']);
							$providername_split = $phrase['_billing_and_payments'];
							$provider_split = SITE_NAME;
							$providerinfo_split = SITE_ADDRESS;
							
							$show['viewingasprovider_split'] = $show['escrowblock_split'] = false;
							if ($res_regardlist_split['invoicetype'] == 'escrow')
							{
							// escrow handling
							$show['providerblock_split'] = true;
							$show['escrowblock_split'] = true;


							$ilance->auction = construct_object('api.auction');
							$ilance->escrow = construct_object('api.escrow');


							$customer_split = fetch_user('username', $res_regardlist_split['user_id']);
							$customeremail_split = fetch_user('email', $res_regardlist_split['user_id']);
							$customerinfo_split = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
							$customername_split = fetch_user('fullname', fetch_user('user_id', '', $customer));	

							// display invoice type on invoice payment form
							$invoicetype_split = print_transaction_type($res_regardlist_split['invoicetype']);
							}

							if ($res_regardlist_split['invoicetype'] == 'debit')
							{
							// escrow handling
							$show['providerblock_split'] = true;
							$show['escrowblock_split'] = true;


							$ilance->auction = construct_object('api.auction');
							$ilance->escrow = construct_object('api.escrow');


							$customer_split = fetch_user('username', $res_regardlist_split['user_id']);
							$customeremail_split = fetch_user('email', $res_regardlist_split['user_id']);
							$customerinfo_split = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
							$customername_split = fetch_user('fullname', fetch_user('user_id', '', $customer));	

							// display invoice type on invoice payment form
							$invoicetype_split = print_transaction_type($res_regardlist_split['invoicetype']);
							}
							
							else if ($res_regardlist_split['invoicetype'] == 'buynow')
							{
								$show['providerblock_split'] = true;
								$customer_split = fetch_user('username', $res_regardlist_split['user_id']);
								$customeremail_split = fetch_user('email', $res_regardlist_split['user_id']);						
								$invoicetype_split = print_transaction_type($res_regardlist_split['invoicetype']);
								$customerinfo_split = print_shipping_address_text($res_regardlist_split['user_id']) . fetch_business_numbers($res_regardlist_split['user_id']);						
								$customername_split = fetch_user('fullname', $res_regardlist_split['user_id']);
								
							}
						$description_split .= stripslashes($res_regardlist_split['description']).'<br>';
						$amountcal_split[] = $res_regardlist_split['amount'];
						$taxinfolist_split = $res_regardlist_split['taxinfo'];
						$invoicetype_split = $res_regardlist_split['invoicetype'];
						$buyerfeecal_split[] = $buyerfee1_split;
						$totalamountlistcal_split[] = $totalamountlist1_split;
					
						
						$res_regardlist_split['item_id'] 	 = 	$res_regardlist_split['projectid'];
						$res_regardlist_split['itemtitle'] = fetch_coin_table('Title',$res_regardlist_split['projectid']);
						/*======vijay bug id:4714 start=====*/
						if($res_regardlist_split['Site_Id'] >0)
						{
						$res_regard_split='eBay';
						}
						else
						{
						$res_regard_split='GC';
						}
						$res_regardlist_split['Site_Id'] 	 =$res_regard_split;
						/*======vijay bug id:4714 end=====*/
						
						if ($ilconfig['globalauctionsettings_seourls'])
						{
						
							$res_regardlist_split['item_id']='<a href="Coin/'.$res_regardlist_split['projectid'].'/'.construct_seo_url_name($res_regardlist_split['itemtitle']).'"> '.$res_regardlist_split['item_id'].'</a>';
							$res_regardlist_split['itemtitle'] ='<a href="Coin/'.$res_regardlist_split['projectid'].'/'.construct_seo_url_name($res_regardlist_split['itemtitle']).'"> '.$res_regardlist_split['itemtitle'].'</a>';
							
						}
						else
						{
							$res_regardlist_split['item_id']='<a href="merch.php?id='.$res_regardlist_split['projectid'].'">'.$res_regardlist_split['item_id'].'</a>';
							$res_regardlist_split['itemtitle']='<a href="merch.php?id='.$res_regardlist_split['projectid'].'">'.$res_regardlist_split['itemtitle'].'</a>';
						}
								
							
						//$res_regardlist_split['itemtitle'] = fetch_auction('project_title', $res_regardlist_split['projectid']);
						$res_regardlist_split['finalprice'] = $ilance->currency->format($res_regardlist_split['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
						$res_regardlist_split['buyerfees'] = $buyerfee_split;
						$res_regardlist_split['totallistamount'] =  $totalamountlist_split;
						$regardlist_split[] = $res_regardlist_split;
						
										
					}

					$show['taxes_split'] = 0;
					
					$qtyhidden_split = '<input type = "hidden" name="qtyhidden" id="qtyhidden" value="'.array_sum($totqty_split).'">';
					$invidim_split = implode(',',$invid_split);
					$invidhidden_split = '<input type = "hidden" name="invhidden" id="invhidden" value="'.$invidim_split.'">';
					$amounttotal_split = array_sum($totalamountlistcal_split);
					$amount_split = $ilance->currency->format(array_sum($totalamountlistcal_split),$ilconfig['globalserverlocale_defaultcurrency']);
					//karthik start Apr 15
					 $totalamount_pending_in_split=array_sum($totalamountlistcal_split);
					// end
					
					//karthik on sep06 for sales tax reseller
					 $sales_tax_reseller_split = fetch_user('issalestaxreseller',$giv_user_id);	
					 
					if ($ilance->tax->is_taxable($giv_user_id, $invoicetype_split) AND $ilconfig['staffsettings_max_tax_limit'] >= $amounttotal_split AND $sales_tax_reseller_split!='1')
					 {			 	
						$state_split = fetch_user('state',$giv_user_id);			
						 $taxinfo_split = 'Sales Tax ('.$ilance->tax->fetch_taxdetails($giv_user_id, $amounttotal_split, 'buynow', 0).'%, '.$state_split.')';
						 
						 //new changes apr22 hiddeen taxinfo variable
						 
						$taxinfonew_split = $ilance->tax->fetch_taxdetails($giv_user_id, $amounttotal_split, 'buynow', 0);
						$taxamount1_split = $ilance->tax->fetch_amount($giv_user_id, $amounttotal_split, 'buynow', 0);
						$taxamount_split =  $ilance->currency->format($taxamount1_split,$ilconfig['globalserverlocale_defaultcurrency']);
						
						$show['taxes_split'] = 1;
					}
					else if ($ilance->tax->is_taxable($giv_user_id, $invoicetype_split) AND $ilconfig['staffsettings_max_tax_limit'] <= $amounttotal_split)
					{						
						 $taxinfo_split = 'Sales Tax Not Applicable ('.$state_split.'over $1,500)';
						$taxamount1_split = $ilance->tax->fetch_amount($giv_user_id, 0, 'buynow', 0);
						
						$taxinfonew_split = 0.00;
						$taxamount_split =  $ilance->currency->format($taxamount1_split,$ilconfig['globalserverlocale_defaultcurrency']);
						$show['taxes_split'] = 1;
					}
					else 
					{						
						 $taxinfo_split = 'Sales Tax Not Applicable (Out of State)';
						$taxamount1_split = $ilance->tax->fetch_amount($giv_user_id, 0, 'buynow', 0);
						$taxamount_split =  $ilance->currency->format($taxamount1_split,$ilconfig['globalserverlocale_defaultcurrency']);
						$show['taxes_split'] = 1;
						
						$taxinfonew_split = 0.00;
					}
					$buyerfe_split = array_sum($buyerfeecal_split);
					$buyerfee = $ilance->currency->format($buyerfe_split,$ilconfig['globalserverlocale_defaultcurrency']);
					//suku1
					$taxamount1_split=empty($taxamount1_split)?"0":$taxamount1_split;
					$taxamounthidden_split = '<input type = "hidden" name="taxhidden" id="taxhidden" value="'.$taxamount1_split.'">
										<input type = "hidden" name="taxinfonew" id="taxinfonew" value="'.$taxinfonew_split.'">
										<input type = "hidden" name="taxhidden1" id="taxhidden1" value="'.$taxamount1_split.'">
										<input type = "hidden" name="taxshipcal" id="taxshipcal" value="0">';

				// murugan changes on feb 28	
				//if ($taxdetails)
				if ($taxamount1_split > 0)
				{			
					$totalamount_split = $ilance->currency->format(($amounttotal_split + $taxamount1_split), $ilconfig['globalserverlocale_defaultcurrency']);
					$totalamountnew_split = $amounttotal_split + $taxamount1_split;
					//suku
					$totalhidden_split = '<input type = "hidden" name="totalhidden" id="totalhidden" value="'.$totalamountnew_split.'">
									<input type = "hidden" name="taxhiddenyes" id="taxhiddenyes" value="1">';			
					$show['taxes_split'] = 1;
				}
				else
				{
					
					$totalamount_split = $ilance->currency->format($amounttotal_split, $ilconfig['globalserverlocale_defaultcurrency']);
					$totalamountnew_split = $amounttotal_split;
					//suku1
					$totalhidden_split = '<input type = "hidden" name="totalhidden" id="totalhidden"  value="'.$totalamountnew_split.'">
									<input type = "hidden" name="taxhiddenyes" id="taxhiddenyes" value="0">';			
				}

				$totalhidden_split.= '<input type = "hidden" name="totalhidden_base" id="totalhidden_base"  value="'.$totalamountnew_split.'">';



				
				
				if( $totalamount_pending_in_split < 10000)
				{	
					$payment_method_pulldown_split='<select name="account_id" style="font-family: verdana">
												<optgroup label="Online Payment">';
					
					$user_paymethods_sql_split=$ilance->db->query("select allowed_paymethods 
															from ".DB_PREFIX."users 
															where user_id =".$giv_user_id);
					$user_paymethods_split=$ilance->db->fetch_array($user_paymethods_sql_split);
								
					$paymethod_sql_split=$ilance->db->query("	select * 
														from ".DB_PREFIX."payment_methods 
														where id in (".$user_paymethods_split['allowed_paymethods'].") 
														order by sort");
					while($allowed_methods_split=$ilance->db->fetch_array($paymethod_sql_split))
					{
						$payment_method_pulldown_split.='<option value="'.$allowed_methods_split['value'].'">'.$phrase[$allowed_methods_split['title']].'</option>';
					}
						$payment_method_pulldown_split.='</optgroup></select>';		
				}	
				else
				{
					$payment_method_pulldown_split='<select name="account_id" style="font-family: verdana">
												<optgroup label="Online Payment">';
					
					$user_paymethods_sql_split=$ilance->db->query("select allowed_paymethods 
															from ".DB_PREFIX."users 
															where user_id =".$giv_user_id);
					$user_paymethods_split=$ilance->db->fetch_array($user_paymethods_sql_split);
					
					// bug # 4654 - kumaravel 			
					$paymethod_sql_split=$ilance->db->query("	select * 
														from ".DB_PREFIX."payment_methods 
														where id in (".$user_paymethods_split['allowed_paymethods'].") 
														and id NOT IN (6,10)
														order by sort");
					while($allowed_methods_split=$ilance->db->fetch_array($paymethod_sql_split))
					{
						$payment_method_pulldown_split.='<option value="'.$allowed_methods_split['value'].'">'.$phrase[$allowed_methods_split['title']].'</option>';
					}
						$payment_method_pulldown_split.='</optgroup></select>';		
				}


				

						$shippment_nethod_pulldown_split = print_shippment_cost($projects_split,$selected_split=0,'shipper_id',array_sum($totqty_split),$giv_user_id,$giv_countryid,$totalamount_pending_in_split);

						$shipper_drop_down_split = $shippment_nethod_pulldown_split['html_split'];

						$itemcount_split = array_sum($totqty_split);

						$totalamount_split=$totalamountnew_split+$shipper_drop_down_split;

						$shipping_cost_amount_split=$ilance->currency->format($shipper_drop_down_split,$ilconfig['globalserverlocale_defaultcurrency']);

						$totalamount_split=$ilance->currency->format($totalamount_split,$ilconfig['globalserverlocale_defaultcurrency']);

						$user_id_split=$giv_user_id;
				
				
				}


		
		
		
		//vijay work for split the invoice end //
		
		
		//returned consignments starts
			$retnd_uid = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
			$retnd_username = fetch_user('username', intval($ilance->GPC['id']));		
			
			$retnd_query=$ilance->db->query("SELECT return_id,user_id, return_date, count(coin_id) as coin_count  FROM " . DB_PREFIX . "coin_return WHERE user_id = '".$retnd_uid."' GROUP BY return_date ORDER BY return_date DESC", 0, null, __FILE__, __LINE__) ;
			
		
			if($ilance->db->num_rows($retnd_query) > 0)
			{
			   while($res = $ilance->db->fetch_array($retnd_query))
			   {
					$returned_list[] = $res;
			   }
			   $show['returned_result'] = true;
			}
			else
			{
				$show['returned_result_no'] = true;
			}
			
			// echo fetch_user('username', intval($ilance->GPC['id']));
			// echo $retnd_query;
			// exit;
		
		//returned consignments ends
		
			// for bug #5204 ends
						
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
				    $filterby1 = (isset($ilance->GPC['filterby2']) AND !empty($ilance->GPC['filterby2'])) ? $ilance->GPC['filterby2'] : '';
                    $filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->db->escape_string($ilance->GPC['filtervalue']) : '';							
				    $filtervalue1 = (isset($ilance->GPC['filtervalue2']) AND !empty($ilance->GPC['filtervalue2'])) ? $ilance->db->escape_string($ilance->GPC['filtervalue2']) : '';
						
					$get_filtervalue =  $ilance->GPC['filtervalue'];
					$get_filtervalue1 = $ilance->GPC['filtervalue2'];
						
						if(isset($ilance->GPC['filterby2']) AND !empty($ilance->GPC['filterby2']))
						{
						 $cond = (isset($ilance->GPC['cond']) AND !empty($ilance->GPC['cond'])) ? $ilance->GPC['cond'] : 'OR';
						}
						
                        $orderby = (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby'])) ? $ilance->GPC['orderby'] : 'desc';
                       $orderlimit = ' ORDER BY ' . $filterby . ' ' . mb_strtoupper($orderby) . ' LIMIT ' . (($ilance->GPC['page']-1)*50).','.'50';
                            
                        // searching via specific user status only
                        $where = "WHERE user_id != '' ";
                         if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']))
                        {
						      $status_in_array = array('active'=>"AND status = 'active'",
							                            'inactive'=>"AND status in('suspended','cancelled','unverified','banned','moderated')",
														'all'=>''
							                           );
						      $where.=$status_in_array[$ilance->GPC['status']];
							
                        }
						// murugan changes on jan 22
						 
						if($filtervalue{0} == '*' AND !empty($filtervalue) AND !empty($filterby))
						{						 	
                                $test = explode('*',$filtervalue);
								$filtervalue = $test['1'];
								$where .= "AND " . $filterby . " LIKE '%" . $filtervalue . "'";                       		
						}
						else
						{
						    if ($filtervalue{strlen($filtervalue)-1} == '*')
                     	   	{
                                $test = explode('*',$filtervalue);
								$filtervalue = $test['0'];
								$where .= "AND " . $filterby . " LIKE '" . $filtervalue . "%'";
                       	 	}
							else
							{
							    if(!empty($filterby1) OR !empty($filtervalue))
							    {
							    	if($filterby == 'zip_code')
							    	{
                                        $where .= "AND " . $filterby . " LIKE '%".$filtervalue."%'";
							    	}
							    	else
							    	{
							    		$where .= "AND " . $filterby . " = '" . $filtervalue . "'";
							    	}
								
								}
							}
						}
						
						//
						if(!empty($ilance->GPC['filterby2'])){
						if($filtervalue1{0} == '*' AND !empty($filtervalue1) AND !empty($filterby1))
						{						 	
                                $test = explode('*',$filtervalue1);
								$filtervalue1 = $test['1'];
								$where .= " ".$cond." " . $filterby1 . " LIKE '%" . $filtervalue1 . "'";                       		
						}
						else
						{
						    if ($filtervalue1{strlen($filtervalue1)-1} == '*')
                     	   	{
                                $test = explode('*',$filtervalue1);
								$filtervalue1 = $test['0'];
								$where .= " ".$cond." " . $filterby1 . " LIKE '" . $filtervalue1 . "%'";
                       	 	}
							else
							{
								if(!empty($filterby1))
								$where .= " ".$cond." " . $filterby1 . " = '" . $filtervalue1 . "'";
								
								
							}
						}		
						
                      }
                       
						/*if (!empty($filtervalue1) AND !empty($filterby1))
                        {
                                $where .= " ".$cond." " . $filterby1 . " = '" . $filtervalue1 . "'";
                        }*/
                        // murugan end
                        $scriptpage = $ilpage['users'] . "?cmd=search";
                        foreach ($ilance->GPC AS $cmd => $value)
                        {
                                if (!empty($cmd) AND !empty($value) AND $cmd != 'submit' AND $cmd != 'cmd' AND $cmd != 'page')
                                {
                                        $scriptpage .= '&amp;' . $cmd . '=' . $value;
                                }
                        }
                        $sqlsearchcustomers = $ilance->db->query("
                                SELECT user_id, username, first_name, last_name,Check_Payable, email, phone, city, state, zip_code, status, available_balance, total_balance, isadmin, permissions,ir_managed,notifyauction,access_bb,enable_batch_bid,enable_bid
                                FROM " . DB_PREFIX . "users
                                $where
                                $orderlimit                           
                        ");
                      
                        $sqlsearchcustomers2 = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "users
                                $where
                        ");
                        $number = (int)$ilance->db->num_rows($sqlsearchcustomers2);
                        if ($ilance->db->num_rows($sqlsearchcustomers) > 0)
                        {
                                $row_count = 0;
                                while ($res = $ilance->db->fetch_array($sqlsearchcustomers, DB_ASSOC))
                                {
                                        $res['edit'] = '<a href="users.php?subcmd=_update-customer&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
                                        $res['remove'] = '<a href="users.php?subcmd=deleteuser&amp;id=' . $res['user_id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
                                        $res['balance'] = $ilance->currency->format($res['available_balance']);
										$res['Check_Payable'] = $res['Check_Payable'];
                                        $res['subscription'] = $ilance->subscription->fetch_subscription_plan($res['user_id']);
                                        $res['role'] = print_role(fetch_user_roleid($res['user_id']));
                                        $res['action'] = '<input type="checkbox" name="user_id[]" value="' . $res['user_id'] . '" id="members_' . $res['user_id'] . '" />';
                                        $res['isadmin'] = ($res['isadmin']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="'.$phrase['_yes'].'" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="'.$phrase['_no'].'" />';
                                        $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                        $res['status'] = ($res['status'] == 'active') ? '<a href="users.php?subcmd=suspenduser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to suspend customer (cannot log-in)" border="0"></a>' : '<a href="users.php?subcmd=activateuser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to re-activate customer (can log in)" border="0"></a>';
										
					// vijay nov 18 
					
					
					$res['enable_batch_bid'] = ($res['enable_batch_bid'] == '1') ? '<a href="users.php?subcmd=enable_batch_bid_deactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to stop Access Batch Biddings" border="0"></a>' : '<a href="users.php?subcmd=enable_batch_bid_activate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to  enable Access Batch Biddings" border="0"></a>';

					//sakthi sep 01
					$res['enable_bid'] = ($res['enable_bid'] == '1') ? '<a href="users.php?subcmd=enable_bid_deactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to stop Access Biddings" border="0"></a>' : '<a href="users.php?subcmd=enable_bid_activate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to  enable Access Biddings" border="0"></a>';


					//bbcode starts
					$res['access_bb'] = ($res['access_bb'] == '1') ? '<a href="users.php?subcmd=access_bbdeactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to stop Access BBcode" border="0"></a>' : '<a href="users.php?subcmd=access_bbactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to  enable Access BBcode" border="0"></a>';
					
					
					// vijay end
										
										
												//dec 02 for bug id 1077	
					$res['ir_managed'] = ($res['ir_managed'] == '1') ? '<a href="users.php?subcmd=irmanageddeactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to stop irmanaged Auction win admin " border="0"></a>' : '<a href="users.php?subcmd=irmanagedactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to irmanaged auction win admin " border="0"></a>';
					
					// murugan added on sep 7 for notify the auction win
										$res['notifyauction'] = ($res['notifyauction'] == '1') ? '<a href="users.php?subcmd=suspendauction&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to stop Notify Auction win admin " border="0"></a>' : '<a href="users.php?subcmd=activateauction&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to notify auction win admin " border="0"></a>';
					
					//dec finished 02 for bug id 1077
					
					
					
					// quick view of items bought and sold in marketplace
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$res['bought'] = '<div class="smaller gray"><span class="black">' . fetch_bought_count($res['user_id'], 'product') . '</span> ' . $phrase['_items_lower'] . '</div>';
					}
					
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$res['sold'] = '<div class="smaller gray"><span class="black">' . fetch_sold_count($res['user_id'], 'product') . '</span> ' . $phrase['_items_lower'] . '</div>';
					}					
					
					$res['login'] = '<a href="users.php?subcmd=switchuser&amp;id=' . $res['user_id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/picture_blue.gif" alt="' . "Switch To Another User". '" border="0"></a>';
                                        $searchcustomers[] = $res;
                                        $row_count++;
                                }
                                
                                $show['no_customers'] = true;
                                
                                $searchprevnext = print_pagnation($number, 50, $ilance->GPC['page'], $counter, $scriptpage);
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
                        $displayorder = '&amp;displayorder=desc';
                        $realdisplayorder = $displayorder;
                        $displayordersql = 'DESC';
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
                        
                        $scriptpage = 'users.php?cmd=listing' . $displayorder . $orderby;
                        $scriptpageprevnext = 'users.php?cmd=listing' . $realdisplayorder . $orderby;
                        $show['showsearch'] = false;
                
                        $sql = $ilance->db->query("
                                SELECT user_id, username, first_name, last_name, email, phone, city, state, zip_code, status, available_balance, total_balance, isadmin, permissions,ir_managed,notifyauction,house_account,access_bb,enable_batch_bid,enable_bid
                                FROM " . DB_PREFIX . "users
                                ORDER BY $orderbysql
                                $displayordersql
                                LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']
                        );
                        
                        $sql2 = $ilance->db->query("
                                SELECT user_id, username, first_name, last_name, email, phone, city, state, zip_code, status, available_balance, total_balance, isadmin, permissions,ir_managed,notifyauction,access_bb,enable_batch_bid,enable_bid
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
                                                $res['status'] = '<a href="users.php?subcmd=suspenduser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to suspend customer (cannot log-in)" border="0"></a>';
                                        }
                                        else
                                        {
                                                $res['status'] = '<a href="users.php?subcmd=activateuser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to re-activate customer (can log in)" border="0"></a>';
                                        }
					
                                        if ($res['isadmin'])
                                        {
                                                    $res['isadmin'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="'.$phrase['_yes'].'" />';
                                        }
                                        else
                                        {
                                                    $res['isadmin'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="'.$phrase['_no'].'" />';
                                        }
					
                                        $res['edit'] = '<a href="users.php?subcmd=_update-customer&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
                                        $res['remove'] = '<a href="users.php?subcmd=deleteuser&amp;id=' . $res['user_id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
					
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
					// quick view of items bought and sold in marketplace
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$res['bought'] = '<div class="smaller gray"><span class="black">' . fetch_bought_count($res['user_id'], 'product') . '</span> ' . $phrase['_items_lower'] . '</div>';
					}
					
					//vijay nov 18
					

					if ($res['enable_batch_bid'])
                                        {
										
                                                $res['enable_batch_bid'] = '<a href="users.php?subcmd=enable_batch_bid_deactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to  stop Access Batch Biddings" border="0"></a>';
                                        }
                                        else
                                        {
										
                                                $res['enable_batch_bid'] = '<a href="users.php?subcmd=enable_batch_bid_activate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to  enable Access Batch Biddings" border="0"></a>';
                                        }

					if ($res['enable_bid'])
					{
					$res['enable_bid'] = '<a href="users.php?subcmd=enable_bid_deactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to  stop Access Biddings" border="0"></a>';
					}
					else
					{
					$res['enable_bid'] = '<a href="users.php?subcmd=enable_bid_activate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to  enable Access  Biddings" border="0"></a>';
					}

					//bbcode starts
					if ($res['access_bb'])
                                        {
										
                                                $res['access_bb'] = '<a href="users.php?subcmd=access_bbdeactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to  stop Access BBcode" border="0"></a>';
                                        }
                                        else
                                        {
										
                                                $res['access_bb'] = '<a href="users.php?subcmd=access_bbactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to  enable Access BBcode" border="0"></a>';
                                        }
										
					//vijya nov 18 end 
					//dec 2 for bug id 1077 irmanaged
										
									if ($res['ir_managed'])
                                        {
										
                                                $res['ir_managed'] = '<a href="users.php?subcmd=irmanageddeactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to stop Irmanaged Auction win admin" border="0"></a>';
                                        }
                                        else
                                        {
										
                                                $res['ir_managed'] = '<a href="users.php?subcmd=irmanagedactivate&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to Irmanaged Auction win admin (can log in)" border="0"></a>';
                                        }
										
										//dec 2 finished id 1077
										
										// murugan sep 7 for auction win notification
										
										 if ($res['notifyauction'])
                                        {
                                                $res['notifyauction'] = '<a href="users.php?subcmd=suspendauction&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to stop Notify Auction win admin" border="0"></a>';
                                        }
                                        else
                                        {
                                                $res['notifyauction'] = '<a href="users.php?subcmd=activateauction&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to Notify Auction win admin (can log in)" border="0"></a>';
                                        }
					
					// murugan changes on jan 20
					//<div class="smaller gray"><span class="black">' . fetch_sold_count($res['user_id'], 'product') . '</span> ' . $phrase['_items_lower'] . '</div>
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$res['sold'] =  '<div class="smaller gray"><span class="black">' . fetch_sold_count($res['user_id'], 'product') . '</span> ' . $phrase['_items_lower'] . '</div>';
					}					
					
					$res['login'] = '<a href="users.php?subcmd=switchuser&amp;id=' . $res['user_id'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/picture_blue.gif" alt="' . "Switch To Another User". '" border="0"></a>';
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
        // vijay work start for spliting invoice 
		if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='sltd_proj_split_invoices')
		{
		
			require_once(DIR_CORE . 'functions_shipping.php');
			$ilance->tax = construct_object('api.tax');
			$ilance->subscription = construct_object('api.subscription');
			
				$selectedinv_ar = array();
				$giv_user_id=$ilance->GPC['select_usrid'];
				$invoiceid_split=$ilance->GPC['select_invoiceid_split'];
				$giv_countryid=$ilance->GPC['giv_countryid'];
				
				/* echo '<pre>';
				print_r($ilance->GPC);
				exit; */
				
				foreach($invoiceid_split as $invoice_split)
				{
					$selectedinv_ar[]=$invoice_split;				
					
				}
				$numberofinv=count($selectedinv_ar);
				
				
			if(isset($numberofinv) and $numberofinv > 0)
			{
				$selectedinv = implode(',',$selectedinv_ar);
				
				
				$sql_regardlist = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE invoiceid in(".$selectedinv.")
				AND status = 'unpaid'	and not combine_project
				AND isfvf != 1
				AND isif != 1 
				AND isbuyerfee != 1 
				AND isenhancementfee != 1		
				");
				
				$invcount = $ilance->db->num_rows($sql_regardlist);
				
				if($invcount > 0)
				{
					$ifcoins=1;
					
					while($res_regardlist_seltd = $ilance->db->fetch_array($sql_regardlist))
					{
						
						 // echo '<pre>';
						 // print_r($res_regardlist_seltd);
						
						$invid[] = $res_regardlist_seltd['invoiceid'];
						
						$show['invoicecancelled'] = 0;
						
						
						$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders
													WHERE invoiceid = '".$res_regardlist_seltd['invoiceid']."'
													AND buyer_id = '".$giv_user_id."'");
						if($ilance->db->num_rows($buy)>0)
						{
							$resbuy = $ilance->db->fetch_array($buy);
						$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
													WHERE coin_id = '".$res_regardlist_seltd['projectid']."'");
										$temp=$ilance->db->fetch_array($bids);						
							
							$coin_no_in_set=empty($temp['nocoin'])?1:intval($temp['nocoin']);
							$res_regardlist_seltd['qty'] = $resbuy['qty'];
							 $totqty[] = $res_regardlist_seltd['qty']*$coin_no_in_set;
						}
						else
						{
						//check 	nocoin  in ilance_coins for each coins
						$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
													WHERE coin_id = '".$res_regardlist_seltd['projectid']."'");
										$temp=$ilance->db->fetch_array($bids);		
										
							$res_regardlist_seltd['qty'] = 1;
							 
							$totqty[] = empty($temp['nocoin'])?1:intval($temp['nocoin']);
						}
						

				//$res_invoice = $ilance->db->fetch_array($sql_invoice);
						$id = $res_regardlist_seltd['invoiceid'];
						$txn = $res_regardlist_seltd['transactionid'];
						$securekey_hidden .= '<input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="txn" value="' . $txn . '" />';

						($apihook = $ilance->api('invoicepayment_transaction_view_condition_end')) ? eval($apihook) : false;
					
				// total amount paid for this invoice
						//$amountpaid = $ilance->currency->format($res_regardlist_seltd['paid'], $res_regardlist_seltd['currency_id']);
				$amountpaid =  $ilance->currency->format(0);
				// invoice creation date
						$createdate = print_date($res_regardlist_seltd['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$show['miscamount']=false;
						 $res_regardlist_seltd['miscamount'];
						if($res_regardlist_seltd['miscamount']>0)
						{
						$show['miscamount']=true;
						$miscamount =  $ilance->currency->format($res_regardlist_seltd['miscamount']);
						}
				// invoice due date
				if ($res_regardlist_seltd['duedate'] == "0000-00-00 00:00:00")
				{
					$duedate = '--';		
				}
				else
				{
					$duedate = print_date($res_regardlist_seltd['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
				// invoice paid date
				if ($res_regardlist_seltd['paiddate'] == "0000-00-00 00:00:00")
				{
					$paiddate = '--';
				}
				else
				{
					$paiddate = print_date($res_regardlist_seltd['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}

				// invoice identifier
				$invoiceid = $id;

				$show['ispaid'] = $show['isunpaid'] = $show['isscheduled'] = $show['iscomplete'] = $show['iscancelled'] = 0;

				if ($res_regardlist_seltd['status'] == 'paid')
				{
					$show['ispaid'] = 1;
				}
				if ($res_regardlist_seltd['status'] == 'unpaid')
				{
					$show['isunpaid'] = 1;
				}
				if ($res_regardlist_seltd['status'] == 'scheduled')
				{
					$show['isscheduled'] = 1;
				}
				if ($res_regardlist_seltd['status'] == 'complete')
				{
					$show['iscomplete'] = 1;
				}
				if ($res_regardlist_seltd['status'] == 'cancelled')
				{
					$show['iscancelled'] = 1;
				}			
				if ($res_regardlist_seltd['invoicetype'] == 'subscription')
				{
					$show['subscriptionpayment'] = true;
				}
				else
				{
					$show['subscriptionpayment'] = false;
				}


						
						if ($res_regardlist_seltd['status'] == 'unpaid' OR $res_regardlist_seltd['status'] == 'scheduled')
						{
							if ($res_regardlist_seltd['p2b_user_id'] == $giv_user_id)
							{
								$show['paymentpulldown'] = 0;
								$cmd = '_do-invoice-action';
							}
							else if ($res_regardlist_seltd['user_id'] == $giv_user_id)
							{
								$show['paymentpulldown'] = 1;
								$cmd = '_do-invoice-preview';
							}
						}
						else if ($res_regardlist_seltd['status'] == 'cancelled')
						{
							$show['invoicecancelled'] = 1;
						}
						else
						{
							$show['paymentpulldown'] = 0;
							$cmd = '_do-invoice-action';
						}
						
						
						$show['listing'] = 0;
						$project_id = 0;
						if ($res_regardlist_seltd['projectid'] > 0)
						{
						$show['listing'] = 1;
						$listing = fetch_coin_table('Title',$res_regardlist_seltd['projectid']);
						$haswinner = fetch_auction('haswinner', $res_regardlist_seltd['projectid']);			
						$project_id = $res_regardlist_seltd['projectid'];
						$projects[] = $res_regardlist_seltd['projectid'];
						}
						// tax check 
						$taxdetails = $res_regardlist_seltd['istaxable'];
						$show['buyer'] = 0;
						$buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
														WHERE projectid = '".$res_regardlist_seltd['projectid']."'
														AND user_id = '".$giv_user_id."'
														AND isbuyerfee = '1'");
							if($ilance->db->num_rows($buyfee_inv) > 0)
							{
								$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
								$totalamountlist = $ilance->currency->format(($res_regardlist_seltd['amount'] + $res_buyfee['amount'] ), $res_regardlist_seltd['currency_id']);
								$buyerfee =  $ilance->currency->format($res_buyfee['amount'], $res_regardlist_seltd['currency_id']);
								$buyerfee1 = $res_buyfee['amount'];
								$totalamountlist1 = $res_regardlist_seltd['amount'] + $res_buyfee['amount'] ;
								$show['buyer'] = 1;
							}
							else
							{
								$totalamountlist = $ilance->currency->format(($res_regardlist_seltd['amount'] ), $res_regardlist_seltd['currency_id']);
								$buyerfee =  $ilance->currency->format(0, $res_regardlist_seltd['currency_id']);
								$buyerfee1 = 0;
								$totalamountlist1 = $res_regardlist_seltd['amount'];
								$show['buyer'] = 1;
							}
							
							
							$paymethod = ucwords($res_regardlist_seltd['paymethod']);
							$paystatus = ucwords($res_regardlist_seltd['status']);
							$providername = $phrase['_billing_and_payments'];
							$provider = SITE_NAME;
							$providerinfo = SITE_ADDRESS;
							
							$show['viewingasprovider'] = $show['escrowblock'] = false;
							if ($res_regardlist_seltd['invoicetype'] == 'escrow')
							{
					// escrow handling
					$show['providerblock'] = true;
					$show['escrowblock'] = true;
					
								
					$ilance->auction = construct_object('api.auction');
					$ilance->escrow = construct_object('api.escrow');
					
					
						$customer = fetch_user('username', $res_regardlist_seltd['user_id']);
						$customeremail = fetch_user('email', $res_regardlist_seltd['user_id']);
						$customerinfo = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
						$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));	
							
					// display invoice type on invoice payment form
					$invoicetype = print_transaction_type($res_regardlist_seltd['invoicetype']);
				}

							if ($res_regardlist_seltd['invoicetype'] == 'debit')
							{
					// escrow handling
					$show['providerblock'] = true;
					$show['escrowblock'] = true;
					
								
					$ilance->auction = construct_object('api.auction');
					$ilance->escrow = construct_object('api.escrow');
					
					
						$customer = fetch_user('username', $res_regardlist_seltd['user_id']);
						$customeremail = fetch_user('email', $res_regardlist_seltd['user_id']);
						$customerinfo = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
						$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));	
							
					// display invoice type on invoice payment form
					$invoicetype = print_transaction_type($res_regardlist_seltd['invoicetype']);
				}
							
							else if ($res_regardlist_seltd['invoicetype'] == 'buynow')
							{
								$show['providerblock'] = true;
								$customer = fetch_user('username', $res_regardlist_seltd['user_id']);
								$customeremail = fetch_user('email', $res_regardlist_seltd['user_id']);						
								$invoicetype = print_transaction_type($res_regardlist_seltd['invoicetype']);
								$customerinfo = print_shipping_address_text($res_regardlist_seltd['user_id']) . fetch_business_numbers($res_regardlist_seltd['user_id']);						
								$customername = fetch_user('fullname', $res_regardlist_seltd['user_id']);
								
							}
						$description .= stripslashes($res_regardlist_seltd['description']).'<br>';
						$amountcal[] = $res_regardlist_seltd['amount'];
						$taxinfolist = $res_regardlist_seltd['taxinfo'];
						$invoicetype = $res_regardlist_seltd['invoicetype'];
						$buyerfeecal[] = $buyerfee1;
						$totalamountlistcal[] = $totalamountlist1;
					
						
						$res_regardlist_seltd['item_id'] 	 = 	$res_regardlist_seltd['projectid'];
						$res_regardlist_seltd1['itemtitle'] = fetch_coin_table('Title',$res_regardlist_seltd['projectid']);
						/*======vijay bug id:4714 start=====*/
						if($res_regardlist_seltd['Site_Id'] >0)
						{
						$res_regard='eBay';
						}
						else
						{
						$res_regard='GC';
						}
						$res_regardlist_seltd['Site_Id'] 	 =$res_regard;
						/*======vijay bug id:4714 end=====*/
						
						if ($ilconfig['globalauctionsettings_seourls'])
						{
						
							$res_regardlist_seltd['item_id']='<a href="Coin/'.$res_regardlist_seltd['projectid'].'/'.construct_seo_url_name($res_regardlist_seltd1['itemtitle']).'"> '.$res_regardlist_seltd['item_id'].'</a>';
							$res_regardlist_seltd['itemtitle'] ='<a href="Coin/'.$res_regardlist_seltd['projectid'].'/'.construct_seo_url_name($res_regardlist_seltd1['itemtitle']).'"> '.$res_regardlist_seltd1['itemtitle'].'</a>';
							
						}
						else
						{
							$res_regardlist_seltd['item_id']='<a href="merch.php?id='.$res_regardlist_seltd['projectid'].'">'.$res_regardlist_seltd1['item_id'].'</a>';
							$res_regardlist_seltd['itemtitle']='<a href="merch.php?id='.$res_regardlist_seltd['projectid'].'">'.$res_regardlist_seltd1['itemtitle'].'</a>';
						}
								
							
						//$res_regardlist_seltd['itemtitle'] = fetch_auction('project_title', $res_regardlist_seltd['projectid']);
						$res_regardlist_seltd['finalprice'] = $ilance->currency->format($res_regardlist_seltd['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
						$res_regardlist_seltd['buyerfees'] = $buyerfee;
						$res_regardlist_seltd['totallistamount'] =  $totalamountlist;
						$regardlist[] = $res_regardlist_seltd;
						 		
						
										
					}
					
					
					$show['taxes'] = 0;
					
					$qtyhidden = '<input type = "hidden" name="qtyhidden" id="qtyhidden" value="'.array_sum($totqty).'">';
					$invidim = implode(',',$invid);
					$invidhidden = '<input type = "hidden" name="invhidden" id="invhidden" value="'.$invidim.'">';
					$amounttotal = array_sum($totalamountlistcal);
					$amount = $ilance->currency->format(array_sum($totalamountlistcal),$ilconfig['globalserverlocale_defaultcurrency']);
					//karthik start Apr 15
					 $totalamount_pending_in =array_sum($totalamountlistcal);
					// end
					
					//karthik on sep06 for sales tax reseller
					 $sales_tax_reseller = fetch_user('issalestaxreseller',$giv_user_id);	
					
					if ($ilance->tax->is_taxable($giv_user_id, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] >= $amounttotal AND $sales_tax_reseller!='1')
					 {	
					   	$state = fetch_user('state',$giv_user_id);			
						 $taxinfo = 'Sales Tax ('.$ilance->tax->fetch_taxdetails($giv_user_id, $amounttotal, 'buynow', 0).'%, '.$state.')';
						 
						 //new changes apr22 hiddeen taxinfo variable
						 
						$taxinfonew = $ilance->tax->fetch_taxdetails($giv_user_id, $amounttotal, 'buynow', 0);
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, $amounttotal, 'buynow', 0);
						$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
						
						$show['taxes'] = 1;
					}
					else if ($ilance->tax->is_taxable($giv_user_id, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] <= $amounttotal)
					{		
		
						 $taxinfo = 'Sales Tax Not Applicable ('.$state.'over $1,500)';
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, 0, 'buynow', 0);
						
						$taxinfonew = 0.00;
						$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
						$show['taxes'] = 1;
					}
					else 
					{		
						 $taxinfo = 'Sales Tax Not Applicable (Out of State)';
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, 0, 'buynow', 0);
						$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
						$show['taxes'] = 1;
						
						$taxinfonew = 0.00;
					}
					$buyerfe = array_sum($buyerfeecal);
					$buyerfee = $ilance->currency->format($buyerfe,$ilconfig['globalserverlocale_defaultcurrency']);
					//suku1
					$taxamount1=empty($taxamount1)?"0":$taxamount1;
					$taxamounthidden = '<input type = "hidden" name="taxhidden" id="taxhidden" value="'.$taxamount1.'">
										<input type = "hidden" name="taxinfonew" id="taxinfonew" value="'.$taxinfonew.'">
										<input type = "hidden" name="taxhidden1" id="taxhidden1" value="'.$taxamount1.'">
										<input type = "hidden" name="taxshipcal" id="taxshipcal" value="0">';

				// murugan changes on feb 28	
				//if ($taxdetails)
					
				
				if ($taxamount1 > 0)
				{			
					$totalamount = $ilance->currency->format(($amounttotal + $taxamount1), $ilconfig['globalserverlocale_defaultcurrency']);
					$totalamountnew = $amounttotal + $taxamount1;
					//suku
					$totalhidden = '<input type = "hidden" name="totalhidden" id="totalhidden" value="'.$totalamountnew.'">
									<input type = "hidden" name="taxhiddenyes" id="taxhiddenyes" value="1">';			
					$show['taxes'] = 1;
				}
				else
				{
					
					$totalamount = $ilance->currency->format($amounttotal, $ilconfig['globalserverlocale_defaultcurrency']);
					$totalamountnew = $amounttotal;
					//suku1
					$totalhidden = '<input type = "hidden" name="totalhidden" id="totalhidden"  value="'.$totalamountnew.'">
									<input type = "hidden" name="taxhiddenyes" id="taxhiddenyes" value="0">';			
				}

				$totalhidden.= '<input type = "hidden" name="totalhidden_base" id="totalhidden_base"  value="'.$totalamountnew.'">';

				

				if( $totalamount_pending_in < 10000)
				{	
					$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
												<optgroup label="Online Payment">';
					
					$user_paymethods_sql=$ilance->db->query("select allowed_paymethods 
															from ".DB_PREFIX."users 
															where user_id =".$giv_user_id);
					$user_paymethods=$ilance->db->fetch_array($user_paymethods_sql);
								
					$paymethod_sql=$ilance->db->query("	select * 
														from ".DB_PREFIX."payment_methods 
														where id in (".$user_paymethods['allowed_paymethods'].") 
														order by sort");
					while($allowed_methods=$ilance->db->fetch_array($paymethod_sql))
					{
						$payment_method_pulldown.='<option value="'.$allowed_methods['value'].'">'.$phrase[$allowed_methods['title']].'</option>';
					}
						$payment_method_pulldown.='</optgroup></select>';		
				}	
				else
				{
					$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
												<optgroup label="Online Payment">';
					
					$user_paymethods_sql=$ilance->db->query("select allowed_paymethods 
															from ".DB_PREFIX."users 
															where user_id =".$giv_user_id);
					$user_paymethods=$ilance->db->fetch_array($user_paymethods_sql);
					
					// bug # 4654 - kumaravel 			
					$paymethod_sql=$ilance->db->query("	select * 
														from ".DB_PREFIX."payment_methods 
														where id in (".$user_paymethods['allowed_paymethods'].") 
														and id NOT IN (6,10)
														order by sort");
					while($allowed_methods=$ilance->db->fetch_array($paymethod_sql))
					{
						$payment_method_pulldown.='<option value="'.$allowed_methods['value'].'">'.$phrase[$allowed_methods['title']].'</option>';
					}
						$payment_method_pulldown.='</optgroup></select>';		
				}


				$shippment_nethod_pulldown = split_print_shippment_nethod_pulldown($projects,$selected=0,'shipper_id','return change_shipper();',array_sum($totqty),$giv_user_id,$giv_countryid,$totalamount_pending_in);


				$shipper_drop_down = $shippment_nethod_pulldown['html'];
				//suku
				$headinclude.='<script>
				function change_shipper()
				{
				  
				var shippers_base_cost=new Array(); 
				var shippers_added_cost=new Array();
				var international_extra_morethen_n_coins=0;

				'.$shippment_nethod_pulldown['script'].'
				var shipper=document.getElementById("shipper_id").value;

				// karthik start apr 16
				var taxamt = document.getElementById("taxhidden").value;


				var taxpresent = document.getElementById("taxhiddenyes").value;


				var taxinfonew = document.getElementById("taxinfonew").value;



				if(shipper == 26 && shippers_base_cost[shipper] == 0)

				{
				document.getElementById("free_announce").innerHTML ="<span class=\"green\">Standard shipping is free for your first auction purchase (U.S. only)</span>";

				}

				else

				{

				 
				  
				}

				  //end
				//var totalproject = document.getElementById("total_val").value;
				if(shipper>0)
				{
					//document.getElementById("sub").disabled = false;
					
					invhidden=document.getElementById("invhidden").value;
					qtyhiddennew=document.getElementById("qtyhidden").value;
					projectlist=invhidden.split(",");
					
					//var txt = parseFloat(projectlist.length) - parseFloat(totalproject);
					var txt = parseFloat(projectlist.length);
					// muruagn changes on apr 17 for qty
					//var no_item=txt;
					var no_item=qtyhiddennew;
							 
					if(projectlist.length > 0)
					{
					var shipping_total=(no_item)*shippers_added_cost[shipper]+shippers_base_cost[shipper];
					}
					else
					{
					
					}
					var shipping_total=(no_item)*shippers_added_cost[shipper]+shippers_base_cost[shipper];
					 
					shipping_total=shipping_total+international_extra_morethen_n_coins;
					shipping_cost=shipping_total.toFixed(2);
				//new change calculating  tax amount for shipping
					
					var taxcount = (taxinfonew *  shipping_cost) / 100;
					
					document.getElementById("taxshipcal").value = taxcount;
								
					var taxadd = parseFloat(document.getElementById("taxhidden1").value) + parseFloat(taxcount);
					
					newtaxadd = taxadd.toFixed(2);
				   
					document.getElementById("taxhidden").value = newtaxadd;
					
					//end
					document.getElementById("shipping_cost").value=shipping_cost;
					calculate_total();
				}else
				{

				//document.getElementById("sub").disabled = true;
				document.getElementById("shipping_cost").value="0";
				calculate_total();
				}
				return false;
				}
				


				function calculate_total()
				{
				totalhidden_base=parseFloat(document.getElementById("totalhidden_base").value);
				disount_val=parseFloat(document.getElementById("disount_val").value);
				shipping_cost=parseFloat(document.getElementById("shipping_cost").value);
				//new changes apr22
				tax_cost=parseFloat(document.getElementById("taxhidden").value);
				tax_cost_inship = parseFloat(document.getElementById("taxshipcal").value);
				totalhidden=totalhidden_base-disount_val+shipping_cost+tax_cost_inship;
				document.getElementById("totalhidden").value=totalhidden;
				disount_val_text=disount_val.toFixed(2);
				shipping_cost_text=shipping_cost.toFixed(2);
				totalhidden_text=totalhidden.toFixed(2);
				//apr22
				document.getElementById("sales_tax_div").innerHTML="US$"+tax_cost.toFixed(2)+"";
				document.getElementById("dicount_amount_div").innerHTML="(US$"+disount_val_text+")";
				document.getElementById("ship_cost_div").innerHTML="US$"+shipping_cost_text;
				document.getElementById("totalamount_area").innerHTML="US$"+totalhidden_text;
				//oct-31

				document.getElementById("totalamt_area").innerHTML="US$"+totalhidden_text;
				}
				</script>
				';

				//new change apr28
				
				
				
				$onload = 'javascript:document.invoicepayment.reset();change_shipper();';
				$user_id=$giv_user_id;
				$pprint_array = array('qtyhidden','giv_user_id','ifcoins','shipper_drop_down','taxamounthidden','totalhidden','invidhidden','project_id','customeremail','buyerfee','project_id','paystatus','markedascancelledurl','markedaspaidurl','markedasunpaidurl','paymethod','listing','headtitle','headmessage','cmd','customername','providername','customerinfo','providerinfo','totalamount','taxinfo','taxamount','transactionid','comments','provider','customer','payment_method_pulldown','invoiceid','invoicetype','description','amount','miscamount','amountpaid','createdate','duedate','paiddate','custommessage','securekey_hidden','countdrafts','countarchived','rfpescrow','rfpvisible','countdelisted','prevnext','input_style','redirect','referer','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;


				$ilance->template->fetch('main', 'users_split_invoices.html',2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', array('regardlist'));
				$ilance->template->pprint('main', $pprint_array);
				exit();



				}
			}
			else{
				
				print_action_failed('Please the select aleast one checkbox to split invoice', "split_invoices.php");
				exit();
				
			}
		}

		// vijay work end for spliting invoice 
		
                
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
                                                $res['user'] = '<a href="users.php?subcmd=_update-customer&id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
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
                        
                        $prevnext = print_pagnation($ilance->GPC['totalcount'], $ilance->GPC['pp'], $ilance->GPC['page'], $ilance->GPC['counter'], 'users.php?cmd=auditlog&amp;do=view&amp;script='.$ilance->GPC['script'].'&amp;admin_id='.$ilance->GPC['admin_id'].'&amp;user_id='.$ilance->GPC['user_id'].'&amp;orderby='.$ilance->GPC['orderby'].'&amp;order='.$ilance->GPC['order']);
                }
                
   // print_r($all_consing_coins_list);
        
                $profilequestions = $phrase['_not_available_at_this_time'];

                $sql = $ilance->db->query("SELECT listing_fee, final_value_fee FROM " . DB_PREFIX . "users WHERE user_id = '" . $id . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);

					if ($res['listing_fee'])
					{
						$listing_fee = getif_drop_down('listing_fee','listing_fee',$res['listing_fee']);
					}
					else
					{
						$listing_fee = getif_drop_down('listing_fee','listing_fee','');
					}
					if ($res['final_value_fee'])
					{
						$fvffee = getfvf_drop_down('final_value_fee','final_value_fee',$res['final_value_fee']);
					}
					else
					{
						$fvffee = getfvf_drop_down('final_value_fee','final_value_fee','');
					}
				}
				else
				{
					$listing_fee = getif_drop_down('listing_fee','listing_fee','');
					$fvffee = getfvf_drop_down('final_value_fee','final_value_fee','');
				}
				
				 
	
          $pprint_array = array('customername_split','customer_split','customeremail_split','customerinfo_split','securekey_hidden_split','res_regardlist_single_proj_split','invidhidden_split','qtyhidden_split','totalhidden_split','taxamounthidden_split','amount_split','shipping_cost_amount_split','amountpaid_split','totalamount_split','shipping_cost_amount','giv_countryid','giv_user_id','res_regardlist_single_proj','ifcoins','taxamount_split','taxinfo_split','total_owing_amount','lower_minimum_bid_percentage','lower_minimum_bid','custmr_uid','qtyhidden','shipper_drop_down','taxamounthidden','totalhidden','invidhidden','project_id','customeremail','buyerfee','paystatus','markedascancelledurl','markedaspaidurl','markedasunpaidurl','paymethod','listing','headtitle','headmessage','cmd','customername','providername','customerinfo','providerinfo','totalamount_split','totalamount','taxinfo','taxamount','transactionid','comments','provider','customer','payment_method_pulldown','invoiceid','invoicetype','description','amount','miscamount','amountpaid_split','amountpaid','createdate_split','createdate','duedate','paiddate_split','paiddate','custommessage','securekey_hidden','countdrafts','countarchived','rfpescrow','rfpvisible','countdelisted','input_style','redirect','referer','login_include','headinclude_split','headinclude','onload','area_title','search_results_table','prevnext_ended','prevnext_active','count','number_search','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','retnd_username','scriptpageprevnext_invoices','scriptpageprevnext_all_invoices','invoice_type_drop_down','consing_number','series_prevnext','date_down','consignor_paginationn','paymethod_html','house_account','yearlist','monthlist','daylist','city_js_pulldown','scriptpage','profilequestions','customquestions','admins_pulldown','members_pulldown','scripts_pulldown','rolepulldown','subscription_role_pulldown','dynamic_js_bodyend2','country_js_pulldown','state_js_pulldown','role_pulldown','subscription_permissions_pulldown','register_questions','reportrange','transactionsprevnext','id','customername','userid','currency','subscription_plan_pulldown','dynamic_js_bodyend','searchprevnext','number','get_filtervalue','get_filtervalue1','phrases_selectlist','keyword','base_language_pulldown','limit_pulldown','language_pulldown','input_style','remote_addr','rid','referfrom','login_include','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','listing_pagnation','user_id','returnurl','distance','subcategory_name','text','prevnext','prevnext2','default_exchange_rate','onload','popup_new','HTTPS_SERVER_ADMIN','buildversion','ilanceversion','login_include_admin','listing_fee','fvffee');
        
         ($apihook = $ilance->api('admincp_subscribers_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'users.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main', array('invoicelist','all_consing_coins_list','customers','ebay_seller_percentage','searchcustomers','registration_questions','profile','profileaccount','transaction_rows','subscription_rows','creditcards','bankaccounts','exemptions','auditlog','advance','notes','returned_list','favorites','active_summary','ended_summary','regardlist','regardlist_split'));
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
	
	
	}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
function getif_drop_down($htmlname,$htmlid,$selected_id=0)
{
	global $ilance, $ilconfig, $phrase, $ilpage;

	$sql = $ilance->db->query("SELECT g.*  FROM " . DB_PREFIX . "insertion_groups g 
		LEFT JOIN " . DB_PREFIX . "insertion_fees f ON f.groupid = g.groupid 
		WHERE g.state = 'product' AND f.insertionid > 0 GROUP BY g.sort
	", 0, null, __FILE__, __LINE__);

	if($ilance->db->num_rows($sql) > 0)
	{
		$html = '<select name="'.$htmlname.'" id="'.$htmlid.'" style="width: 200px">';
		$html .= '<option value="">Please select a listing fee</option>';
		while($line = $ilance->db->fetch_array($sql))
		{
			if($line['groupname'] == $selected_id)
			{
				$html .= '<option value="'.$line['groupname'].'" selected="selected">'.$line['groupname'].'&nbsp;['.$line['description'].']'.'</option>';
			}
			else
			{	
				$html .= '<option value="'.$line['groupname'].'">'.$line['groupname'].'&nbsp;['.$line['description'].']'.'</option>';
			}					 
		}
		$html .= '</select>';
	}
	return $html;
}
function getfvf_drop_down($htmlname,$htmlid,$selected_id=0)
{
	global $ilance;

	$sql = $ilance->db->query("SELECT g.*  FROM " . DB_PREFIX . "finalvalue_groups g 
		LEFT JOIN " . DB_PREFIX . "finalvalue f on f.groupid = g.groupid 
		WHERE g.state = 'product' AND f.tierid > 0 GROUP BY g.groupid ORDER BY g.sort
	", 0, null, __FILE__, __LINE__);

	if($ilance->db->num_rows($sql) > 0)
	{
		$html = '<select name="'.$htmlname.'" id="'.$htmlid.'" style="width: 200px">';
		$html .= '<option value="">Please select a Final value fee</option>';
		while($line = $ilance->db->fetch_array($sql))
		{
			if($line['groupid'] == $selected_id)
			{
				$html .= '<option value="'.$line['groupid'].'" selected="selected">'.$line['groupname'].'&nbsp;['.$line['description'].']'.'</option>';
			}
			else
			{	
				$html .= '<option value="'.$line['groupid'].'">'.$line['groupname'].'&nbsp;['.$line['description'].']'.'</option>';
			}					 
		}
		$html .= '</select>';
	}
	return $html;
}

function statement_coin_count($user_details,$start,$stmt_date)
{
global $ilance;
$coin_count = 0;

$query1="select count(c.coin_id) as coin_count from  ".DB_PREFIX."coins c 
			left join ".DB_PREFIX."ebay_listing e on e.coin_id=c.coin_id
			left join ".DB_PREFIX."projects p on c.coin_id=p.project_id and p.user_id='".$user_details['user_id']."' and DATEDIFF(date_end,date_starts )>1 
			left join 
				(select coin_id,user_id,count(id) as no_relist_b4_statement,max(actual_end_date) as last_listed_time from ".DB_PREFIX."coin_relist 
				where date(actual_end_date)<='".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 group by coin_id) r on r.coin_id=c.coin_id
			where  (c.user_id=".$user_details['user_id']." and (
			(date(r.last_listed_time)>='".$start."' and date(r.last_listed_time)<='".$stmt_date."') or 
			(date(c.End_Date)>='".$start."' and date(c.End_Date)<='".$stmt_date."' and (c.project_id>0 or c.relist_count>0 )) or
			(date(p.date_end)>='".$start."' and date(p.date_end)<='".$stmt_date."') or
			(date(e.end_date)>='".$start."' and date(e.end_date)<='".$stmt_date."')
			)) GROUP BY c.coin_id";
			$result1=$ilance->db->query($query1);
	
	
			if($ilance->db->num_rows($result1))
			{
				$coin_count=$ilance->db->num_rows($result1);
				// while($line1=$ilance->db->fetch_array($result1))
				// {
				// $coin_count=$line1['coin_count'];
				// }
			}
			return $coin_count;
}

function last_monday($anydate)
{
	list($y,$m,$d)=explode("-",$anydate);
	$h = mktime(0, 0, 0, $m, $d, $y);
	$w= date("w", $h) ;
	$rest_sec=6*24*60*60;
	$last_monday=date("Y-m-d",$h-$rest_sec);
	return $last_monday;
}
 

function print_shippment_cost($projects_split,$selected_split,$name_split,$totqty_split=0,$giv_user_id,$giv_countryid,$totalamount_pending_in_split)
	{
	global $ilance,$ilconfig;
	$first_shipment_split=false;
	$only_buynow_split=true;
	
	
	$sql_split=$ilance->db->query("select * from ".DB_PREFIX."projects where project_id in (".implode(",",$projects_split).") and filtered_auctiontype='regular'");
	if($ilance->db->num_rows($sql_split)>0)
	$only_buynow_split=false;
	//echo '<!--'.$totqty.'-->';
	if($ilconfig['staffsettings_free_first_shipping'] ==1 AND $giv_countryid==500 and !$only_buynow_split)
	{
	
	
	$sql_split=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$giv_user_id."'AND status IN('unpaid','paid')");
	if($ilance->db->num_rows($sql_split)<1 AND $ilance->db->num_rows($sql_split) ==0)
	{
	$first_shipment_split=true;
	}
	}
	

	
	//shipping for INTERNATIONAL CLIENTS 
	
	
	
	if($giv_countryid!=500)
	{			
	///invoice  over $10,000	
	if( $totalamount_pending_in_split >= '5000.00')
	{
		$sql_split=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='23' and visible=1");
		if($ilance->db->num_rows($sql_split))
		{
		
			while($line_split=$ilance->db->fetch_array($sql_split))
			{
			
				if($totqty_split>$line_split['maxitem_count'])
				{
				$international_extra_morethen_n_coins_split=$line_split['addedfee_above_maxitem_count'];
				}
				$html_split_split.=$line_split['basefee']+ ($line_split['addedfee'] *$totqty_split) +$international_extra_morethen_n_coins_split;

			}
		 
		}
	}
	
	else if( $totalamount_pending_in_split >= '1000.00')
	{
		$sql_split=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid in('22','23') and visible=1");
		if($ilance->db->num_rows($sql_split))
		{
		
					while($line_split=$ilance->db->fetch_array($sql_split))
					{
					
					// oct-31
					$selected_split='22';
					if($line_split['shipperid']==$selected)
					{
					if($totqty_split>$line_split['maxitem_count'])
					{						
					$international_extra_morethen_n_coins_text_split=$line['addedfee_above_maxitem_count'];
					}
					$html_split.=$line_split['basefee']+($line_split['addedfee'] *$totqty_split)+ $international_extra_morethen_n_coins_text_split;
					
					
					}
					
					}
		  
		 }
	}
	
	else
	{	
	$sql_split=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='0' and visible=1 order by carrier desc");
		if($ilance->db->num_rows($sql_split))
		{

			while($line_split=$ilance->db->fetch_array($sql_split))
			{
				
				
				$selected_split='22';
				if($line_split['shipperid']==$selected_split)
				{
				if($totqty_split>$line_split['maxitem_count'])
				{
					$international_extra_morethen_n_coins_split=$line_split['addedfee_above_maxitem_count'];
				}
				$html_split.=$line_split['basefee']+ ($line_split['addedfee'] *$totqty_split) +$international_extra_morethen_n_coins_split;
				}

			}
		}
	}
	
	}
	//end of shipping for INTERNATIONAL CLIENTS 
	
	//shipping method for US Clients
	
	else
	{
       //invoice  over $10,000	
	   if( $totalamount_pending_in_split > '10000.00')
	   {
		  $sql_split=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid='25' and visible=1");
		  if($ilance->db->num_rows($sql_split))
		  {
				
				while($line_split=$ilance->db->fetch_array($sql_split))
				{
					 $html_split.=$line_split['basefee']+ ($line_split['addedfee'] * $totqty_split);
	             }
		        
		     }
		  }  
	       //invoice  over $2,000,
		   else if( $totalamount_pending_in_split > '1000.00')
	      {
		       //may2 new change add order by basefee asc
			    $sql_split=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid in('25','27') and visible=1 order by basefee asc");
				if($ilance->db->num_rows($sql_split))
				{
					while($line_split=$ilance->db->fetch_array($sql_split))
					{
					  					   
						$selected_split='27';
						
						if($first_shipment_split and $line_split['shipperid']=='27')
						{
						$html_split.='0.00';	
						}else
						{	
							if($line_split['shipperid']==$selected_split)
							{						
							 $html_split.=$line_split['basefee']+ ($line_split['addedfee'] *$totqty_split);
							}	
							
						}
												
							
					}
				    
				}
		}  
		
		else
		{	
            //new change apr19  order by carrier to basefee asc
			$sql_split=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='1' and visible=1 order by basefee asc");
			if($ilance->db->num_rows($sql_split))
			{
			
			while($line_split=$ilance->db->fetch_array($sql_split))
				{
				
				//	Shipping is free for your first auction purchase (U.S. only)
			   
				$selected_split='26';
				
					if($first_shipment_split AND $line_split['shipperid']=='26' AND !$only_buynow_split)
					{
						 $html_split.='0.00';	
					}

					else
					{
					if($line_split['shipperid']==$selected_split){
						 $html_split.=$line_split['basefee']+ ($line_split['addedfee'] *$totqty_split);
					}
					}
					
				}
				
			}
			}
			if($first_shipment_split)
			$free_announce_split='0.00';	
		}

	$html_split.=$free_announce_split;
	$shipping_add_split['html_split']=$html_split;
	
	
	return $shipping_add_split;
	}
function print_shippment_nethod_pulldown($projects,$selected,$name,$onchage_script,$totqty=0)
	{
	global $ilance,$ilconfig;
	$first_shipment=false;
	$only_buynow=true;
	$sql=$ilance->db->query("select * from ".DB_PREFIX."projects where project_id in (".implode(",",$projects).") and filtered_auctiontype='regular'");
	if($ilance->db->num_rows($sql)>0)
	$only_buynow=false;

	$countryid = fetch_user('country', $ilance->GPC['id']);

	//echo '<!--'.$totqty.'-->';
	if($ilconfig['staffsettings_free_first_shipping'] ==1 AND $countryid==500 and !$only_buynow)
	{
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$ilance->GPC['id']."' AND status='paid'");
	if($ilance->db->num_rows($sql)==0)
	{
	$first_shipment=true;
	}
	}
	
	//karthik start on Apr 12
	
	//shipping for INTERNATIONAL CLIENTS 
				
	if($countryid!=500)
	{			
	///invoice  over $5,000	
	if( $_SESSION['ilancedata']['user']['totalamount'] >= '5000.00')
	{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='23' and visible=1");
		if($ilance->db->num_rows($sql))
		{
		$html='';
		$script='';
		$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
		while($line=$ilance->db->fetch_array($sql))
		{
	      /* if($line['shipperid']==$selected)
	     {*/
			if($totqty>$line['maxitem_count'])
			{
				$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
				$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
			}
			$html.='<option value="'.$line['shipperid'].'" selected="selected">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
			 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';'; 
			/*}
			else
			{
			
			$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
			}*/
	      }
		  $html.='</select>';
		 }
	}
	
	
	else if( $_SESSION['ilancedata']['user']['totalamount'] >= '1000.00')
	{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='22' and visible=1");
		if($ilance->db->num_rows($sql))
		{
		$html='';
		$script='';
		$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
					while($line=$ilance->db->fetch_array($sql))
					{
					if($line['domestic'] == 1)
					{
						//echo $test = $line['title'];
					}
					if($line['international'] == 1)
					{
						//echo 'inter';
						//echo $raga = $line['title'];
					}
					// oct-31
					$selected='22';
					if($line['shipperid']==$selected)
					{
					if($totqty>$line['maxitem_count'])
					{
						$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
						$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
					}
					$html.='<option value="'.$line['shipperid'].'" selected="selected">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
					 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
					 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
					
					}
					else
					{
					if($totqty>$line['maxitem_count'])
					{
						$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
						$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
						
					}
					$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
					$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
					$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';

					
					}
					}
		  $html.='</select>';
		 }
	}
		 
	else
	{	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='0' and visible=1 order by basefee asc");
	if($ilance->db->num_rows($sql))
	{
	$html='';
	$script='';
	$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
	while($line=$ilance->db->fetch_array($sql))
	{
	$selected='21';
	if($line['shipperid']==$selected)
	{
	if($totqty>$line['maxitem_count'])
	{
		$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
		$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
	}
	$html.='<option value="'.$line['shipperid'].'" selected="selected">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
	 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
	 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
	
	}
	else
	{
	if($totqty>$line['maxitem_count'])
	{
		$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
		$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
		
	}
	$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
	$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
	$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';

	
	}
	}
	}
	}
	$html.='</select>';
	}
	//end of shipping for INTERNATIONAL CLIENTS 
	
	//shipping method for US Clients
	
	else
	{
       //invoice  over $10,000	
	   if( $_SESSION['ilancedata']['user']['totalamount'] > '10000.00')
	   {
		  $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid='25' and visible=1");
		  if($ilance->db->num_rows($sql))
		  {
				$html='';
				$script='';
				$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
				while($line=$ilance->db->fetch_array($sql))
				{
				   
					
					//$selected_text='';
					//if($line['shipperid']==$selected)
						$selected_text='selected="selected"';
						$html.='<option value="'.$line['shipperid'].'" '.$selected_text.' >'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
							if($first_shipment)
							{
							$script.='shippers_base_cost['.$line['shipperid'].']=0;';
							$script.='shippers_added_cost['.$line['shipperid'].']=0;';		
							}else
							{
							$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
							$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
							}
	             }
		        $html.='</select>';
		     }
		  }  
	       //invoice  over $2,000,
		   else if( $_SESSION['ilancedata']['user']['totalamount'] > '1000.00')
	      {
		       //may2 new change add order by basefee asc
			    $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid in('25','27') and visible=1 order by basefee asc");
				if($ilance->db->num_rows($sql))
				{
					$html='';
					$script='';
					$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
					while($line=$ilance->db->fetch_array($sql))
					{
					   
					   
						$selected_text='';
                       
					    //oct-31
						$selected='27';
						if($line['shipperid']==$selected)
						$selected_text='selected="selected"';
						$html.='<option value="'.$line['shipperid'].'" '.$selected_text.' >'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
							if($first_shipment and $line['shipperid']=='27')
							{
							$script.='shippers_base_cost['.$line['shipperid'].']=0;';
							$script.='shippers_added_cost['.$line['shipperid'].']=0;';		
							}else
							{
							$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
							$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
							}
							
							
					  }
				     $html.='</select>';
				  }
		       }  
		
		else
		{	
            //new change apr19  order by carrier to basefee asc
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='1' and visible=1 order by basefee asc");
			if($ilance->db->num_rows($sql))
			{
			$html='';
			$script='';
			$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
			while($line=$ilance->db->fetch_array($sql))
			{
			
			//	Shipping is free for your first auction purchase (U.S. only)
           //oct-31
			$selected='26';
			if($line['shipperid']==$selected)
		      $selected_text='selected="selected"';
			 else
			    $selected_text='';
	      if($first_shipment AND $line['shipperid']=='26' AND !$only_buynow)
	      {
		  
			$html.='<option value="'.$line['shipperid'].'" '.$selected_text.'>'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			 $script.='shippers_base_cost['.$line['shipperid'].']=0;';
		     $script.='shippers_added_cost['.$line['shipperid'].']=0;';		
			}
						
			else
			{
			 
			$html.='<option value="'.$line['shipperid'].'" '.$selected_text.'>'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
			}
				
			}
			
			}
			}
			if($first_shipment)
			$free_announce='<div id="free_announce"><span class="green">Standard shipping is free for your first auction purchase (U.S. only)</span></div>';
			$html.='</select>';
		}
		//karthik end
/*	if($first_shipment)
	{
		for($j=0;$j<count($projects);$j++)
		{
			if(fetch_auction('filtered_auctiontype',$projects[$j]) == 'regular')
			{
			$myself[] = $projects[$j].'<br>';
			}
			
	     }
	
	//$line['addedfee']=0;
	}
	$count_project=count($myself);
	
	/*$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$ilance->GPC['uid']."'");
	if($ilance->db->num_rows($sql)>0)
	{
	$html.='<input type="hidden" value="0" id="total_val">';
	}
	
	if($first_shipment == 'true')
	{
	$html.='<input type="hidden" value="'.$count_project.'" id="total_val"><br><span class="green">Shipping is free for your first auction purchase (U.S. only)</span>';
	}
	else
	{
	$html.='<input type="hidden" value="0" id="total_val">';
	}
	$html.='</select>
$free_announce="<div id="free_announce"><span class=\"green\">First class shipping is free for your first auction purchase (U.S. only)</span></div>";
	<div id="free_announce"></div>';
*/
$html.=$free_announce;
	$pulldown['html']=$html;
	$pulldown['script']=$script;
	
	return $pulldown;
	}

	
function split_print_shippment_nethod_pulldown($projects,$selected,$name,$onchage_script,$totqty=0,$giv_user_id,$giv_countryid,$totalamount_pending_in)
	{
	global $ilance,$ilconfig;
	$first_shipment=false;
	$only_buynow=true;
	
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."projects where project_id in (".implode(",",$projects).") and filtered_auctiontype='regular'");
	if($ilance->db->num_rows($sql)>0)
	$only_buynow=false;



	//echo '<!--'.$totqty.'-->';
	if($ilconfig['staffsettings_free_first_shipping'] ==1 AND $giv_countryid==500 and !$only_buynow)
	{
	
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$giv_user_id."'AND status IN('unpaid','paid')");
	if($ilance->db->num_rows($sql)<1 AND $ilance->db->num_rows($sql) ==0)
	{
	$first_shipment=true;
	}
	}
	
	//karthik start on Apr 12
	
	//shipping for INTERNATIONAL CLIENTS 
	
	
	

	if($giv_countryid!=500)
	{			
	///invoice  over $10,000	
	if( $totalamount_pending_in >= '10000.00')
	{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='23' and visible=1");
		if($ilance->db->num_rows($sql))
		{
		$html='';
		$script='';
		$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
		while($line=$ilance->db->fetch_array($sql))
		{
	      /* if($line['shipperid']==$selected)
	     {*/
			if($totqty>$line['maxitem_count'])
			{
				$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
				$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
			}
			$html.='<option value="'.$line['shipperid'].'" selected="selected">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
			 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';'; 
			/*}
			else
			{
			
			$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
			}*/
	      }
		  $html.='</select>';
		 }
		}






















































		 
	else
	{	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='0' and visible=1 order by carrier desc");
	if($ilance->db->num_rows($sql))
	{
	$html='';
	$script='';
	$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
	while($line=$ilance->db->fetch_array($sql))
	{
	if($line['domestic'] == 1)
	{
		//echo $test = $line['title'];
	}
	if($line['international'] == 1)
	{
		//echo 'inter';
		//echo $raga = $line['title'];
	}
    // oct-31
	$selected='22';
	if($line['shipperid']==$selected)
	{
	if($totqty>$line['maxitem_count'])
	{
		$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
		$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
	}
	$html.='<option value="'.$line['shipperid'].'" selected="selected">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
	 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
	 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
	
	}
	else
	{
	if($totqty>$line['maxitem_count'])
	{
		$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
		$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
		
	}
	$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
	$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
	$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';

	
	}
	}
	}
	}
	$html.='</select>';
	}
	//end of shipping for INTERNATIONAL CLIENTS 
	
	//shipping method for US Clients
	
	else
	{
       //invoice  over $10,000	
	   if( $totalamount_pending_in > '10000.00')
	   {
		  $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid='25' and visible=1");
		  if($ilance->db->num_rows($sql))
		  {
				$html='';
				$script='';
				$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
				while($line=$ilance->db->fetch_array($sql))
				{
				   
					
					//$selected_text='';
					//if($line['shipperid']==$selected)
						$selected_text='selected="selected"';
						$html.='<option value="'.$line['shipperid'].'" '.$selected_text.' >'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
							if($first_shipment)
							{
							$script.='shippers_base_cost['.$line['shipperid'].']=0;';
							$script.='shippers_added_cost['.$line['shipperid'].']=0;';		
							}else
							{
							$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
							$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
							}
	             }
		        $html.='</select>';
		     }
		  }  
	       //invoice  over $2,000,
		   else if( $totalamount_pending_in > '1000.00')
	      {
		       //may2 new change add order by basefee asc
			    $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid in('25','27') and visible=1 order by basefee asc");
				if($ilance->db->num_rows($sql))
				{
					$html='';
					$script='';
					$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
					while($line=$ilance->db->fetch_array($sql))
					{
					   
					   
						$selected_text='';
                       
					    //oct-31
						$selected='27';
						if($line['shipperid']==$selected)
						$selected_text='selected="selected"';
						$html.='<option value="'.$line['shipperid'].'" '.$selected_text.' >'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
							if($first_shipment and $line['shipperid']=='27')
							{
							$script.='shippers_base_cost['.$line['shipperid'].']=0;';
							$script.='shippers_added_cost['.$line['shipperid'].']=0;';		
							}else
							{
							$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
							$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
							}
							
							
					  }
				     $html.='</select>';
				  }
		       }  
		
		else
		{	
            //new change apr19  order by carrier to basefee asc
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='1' and visible=1 order by basefee asc");
			if($ilance->db->num_rows($sql))
			{
			$html='';
			$script='';
			$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
			while($line=$ilance->db->fetch_array($sql))
			{
			
			//	Shipping is free for your first auction purchase (U.S. only)
           //oct-31
			$selected='26';
			if($line['shipperid']==$selected)
		      $selected_text='selected="selected"';
			 else
			    $selected_text='';
	      if($first_shipment AND $line['shipperid']=='26' AND !$only_buynow)
	      {
		  
			$html.='<option value="'.$line['shipperid'].'" '.$selected_text.'>'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			 $script.='shippers_base_cost['.$line['shipperid'].']=0;';
		     $script.='shippers_added_cost['.$line['shipperid'].']=0;';		
			}
						
			else
			{
			 
			$html.='<option value="'.$line['shipperid'].'" '.$selected_text.'>'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
			}
				
			}
			
			}
			}
			if($first_shipment)
			$free_announce='<div id="free_announce"><span class="green">Standard shipping is free for your first auction purchase (U.S. only)</span></div>';
			$html.='</select>';
		}



































$html.=$free_announce;
	$pulldown['html']=$html;
	$pulldown['script']=$script;
	
	return $pulldown;
	}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
