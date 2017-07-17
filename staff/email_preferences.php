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
	
);
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
	'jquery_custom_ui',
	'modal',
	'yahoo-jar',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['consignment'] => $ilcrumbs[$ilpage['consignment']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	

			if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'email_list_edit')
			{
					if($ilance->GPC['id'] == '')
					{
					 print_action_failed("We're sorry. Click the Valid Option for Email Preferences", $_SERVER['PHP_SELF']);
					 exit();
					}
					else
					{					 

		$area_title = $phrase['_email_preference_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_email_preference_menu'];
                
                $topnavlink = array(
                        'mycp',
                        'preferencesemail'
                );
				         if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_do-email-change')
        {
		
	 
 
			$email_pre = $ilance->db->query("SELECT * FROM  " . DB_PREFIX . "email_preference
								WHERE user_id = '" . $ilance->GPC['id'] . "'
								");
			if($ilance->db->num_rows($email_pre) > 0)
			{
			
			
			$ilance->db->query("
			UPDATE " . DB_PREFIX . "email_preference
			SET related = '" . $ilance->GPC['related_items']. "',
			outbid = '" . $ilance->GPC['outbid']. "',
			wantlist = '" . $ilance->GPC['wantlist']. "',
			recommend = '" . $ilance->GPC['recommeded']. "',
			gccollection = '" . $ilance->GPC['gccollection']. "',
			itemtracked = '" . $ilance->GPC['itemtracked']. "',
			gcoffer = '" . $ilance->GPC['gcoffer']. "',
			bidconfirm = '" . $ilance->GPC['bidconfirm']. "',
			dailydeal = '" . $ilance->GPC['dailydeal']. "'
			WHERE user_id = '" . $ilance->GPC['id'] . "'
			
		");
		                $notify = isset($ilance->GPC['notify']) ? intval($ilance->GPC['notify']) : 0;

		    $ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET emailnotify = '" . $notify . "'
			WHERE user_id = '" .$ilance->GPC['id'] . "'
		");
		
					print_action_success("You have successfully changed Customer Email Preferences", 'email_preferences.php?cmd=email_list_edit&id='.$ilance->GPC['id'].'');
		exit();		        
 
			}
			
	 
			
		}
				
		//#############  EMAIL PREFERENCE	############################
		################################################################
		######  	EMAIL PREFERENCE						############
		######  Herakle Murugan Coding Nov 02 Starts Here 	############
		################################################################
				////id, related, outbid, wantlist, recommend, gccollection, itemtracked, gcoffer, user_id
		$email_pre = $ilance->db->query("SELECT * FROM  " . DB_PREFIX . "email_preference
								WHERE user_id = '".$ilance->GPC['id']."'
								");
		$email_prefer = $ilance->db->fetch_array($email_pre);
		
		$related = '<input type="checkbox"   style="color:#000;" name="related_items" value="1" ';
		if($email_prefer['related'] == '1')
		{
			$related .= 'checked="checked"';
		}
		$related .= ' />Consignment Related, Items received';
		
		$outbid = '<input type="checkbox"   name="outbid" value="1"';
		if($email_prefer['outbid'] == '1')
		{
			$outbid .= 'checked="checked"';
		}
		$outbid .= ' />Outbid Notices';
	
		$wantlist = '<input type="checkbox"   name="wantlist" value="1"';
		if($email_prefer['wantlist'] == '1')
		{
			$wantlist .= 'checked="checked"';
		}
		$wantlist .= ' />Saved Search Reminders';
		 
		$recommend = '<input type="checkbox"   name="recommeded" value="1"';
		if($email_prefer['recommend'] == '1')
		{
			$recommend .= 'checked="checked"';
		}
		$recommend .= ' />Recommendations';
		
		$gccollection = '<input type="checkbox"   name="gccollection" value="1"';
		if($email_prefer['gccollection'] == '1')
		{
			$gccollection .= 'checked="checked"';
		}
		$gccollection .= ' />My GreatCollections';
	
		$itemtracked = '<input type="checkbox"   name="itemtracked" value="1"';
		if($email_prefer['itemtracked'] == '1')
		{
			$itemtracked .= 'checked="checked"';
		}
		$itemtracked .= ' />Watchlist Reminders';
		 
		$gcoffer = '<input type="checkbox"   name="gcoffer" value="1"';
		if($email_prefer['gcoffer'] == '1')
		{
			$gcoffer .= 'checked="checked"';
		}
		$gcoffer .= ' />Special Offers from GreatCollections';
		
		$bidconfirm = '<input type="checkbox"   name="bidconfirm" value="1"';
		if($email_prefer['bidconfirm'] == '1')
		{
			$bidconfirm .= 'checked="checked"';
		}
		$bidconfirm .= ' />Bid Confirmation Notices';
		
		$dailydeal = '<input type="checkbox"   name="dailydeal" value="1"';
		if($email_prefer['dailydeal'] == '1')
		{
			$dailydeal .= 'checked="checked"';
		}
		$dailydeal .= ' />24-Hour Deal Notification';
		
		//#############  EMAIL PREFERENCE	############################
		################################################################
		######  	EMAIL PREFERENCE						############
		######  Herakle Murugan Coding Nov 02 End Here	 	############
		################################################################

		$emailnotify = fetch_user('emailnotify', $ilance->GPC['id']);
		
		$email_pulldown  = '<select name="notify"   style="font-family: verdana"><option value="1"';
                if ($emailnotify == '1')
		{ 
			$email_pulldown .= ' selected="selected"';
		}
		$email_pulldown .= '>' . $phrase['_yes'] . '</option><option value="0"';
                if ($emailnotify == '0')
		{ 
			$email_pulldown .= ' selected="selected"';
		}
		$email_pulldown .= '>' . $phrase['_no'] . '</option></select>';
		
		$user_sql = $ilance->db->query("select *
													from " . DB_PREFIX . "users
													where user_id =" . $ilance->GPC['id']);
		$user_det = $ilance->db->fetch_array($user_sql);
		
		$username_de = $user_det['username'];
		$user_id_de  = $user_det['user_id'];
		$email_de    = $user_det['email'];
		
		
		
		
		$show['EM_edit'] = true;
		$back = '<a href="email_preferences.php" style="font-weight:bold; color:blue;">Back to Email Preferences List</a>';
				$view = '<a href="email_preferences.php?cmd=email_list&id='.$user_det['user_id'].'" style="font-weight:bold; color:blue;">View to Email Preferences</a>';                
				
				

	 
		$pprint_array = array('dailydeal','bidconfirm','gcoffer','itemtracked','gccollection','recommend','wantlist','outbid','related','session_email','email_pulldown','username_de','user_id_de','email_de','back','view','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','login_include_admin','ilanceversion');
		
						
					($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
					
					$ilance->template->fetch('main', 'email_preferences_view_listing.html', 2);
					$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
					$ilance->template->parse_if_blocks('main');
					$ilance->template->pprint('main', $pprint_array);
		
					exit();
					}
			}




 
	   if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list')
       {
					
					$show['search_list'] = 'search_list';
					
					 $filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : '';
                        $filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->GPC['filtervalue'] : '';
						 

			 
			if (!empty($filtervalue) AND !empty($filterby))
			{
				if($filterby == 'user_id' || $filterby == 'username' || $filterby == 'first_name' || $filterby == 'last_name' || $filterby == 'phone' || $filterby == 'city' || $filterby == 'zip_code' || $filterby == 'email')
				{
					  $where = $filterby . " = '" . $filtervalue . "'";
					  
					  $get_filtervalue = $ilance->GPC['filtervalue'];  

						$consign = $ilance->db->query("SELECT *	FROM " . DB_PREFIX . "users
						                                WHERE $where 
														ORDER BY user_id desc", 0, null, __FILE__, __LINE__);
						
						$number_search = (int)$ilance->db->num_rows($consign);
					
						$consign1 = $ilance->db->query("SELECT * FROM ".DB_PREFIX."users 
							   WHERE $where
							   ORDER BY user_id desc", 0, null, __FILE__, __LINE__);

					
						$number = (int)$ilance->db->num_rows($consign1);
						

						if($ilance->db->num_rows($consign) > 0)
						{
								$row_con_list=0;
								$row_list = array();	
								while($resconsign_user = $ilance->db->fetch_array($consign))
								{
								
									
				  $row_list['user_id'] = $resconsign_user['user_id'];
				  $row_list['username'] = $resconsign_user['username'];
				  $row_list['email'] = $resconsign_user['email'];
				  $row_list['edit'] = '<a href="email_preferences.php?cmd=email_list_edit&amp;id=' . $resconsign_user['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" title="Edit" alt="" border="0"></a>';
 						   				  $email_preferences_search[] = $row_list;	
										  $row_con_list++; 
									
								}	
						}
						else
						{				
							$show['no'] = 'list_search';
						}	
					
					 							
				}
				else
				{				
					$show['no'] = 'list_search';
				}
				 
			}
			else
			{				
				$show['no'] = 'list_search';
			}
			 		
						
						
						

		} 	
	              								
		            								
		 //contactus listing
		 
		   $counter = ($ilance->GPC['page'] - 1) * 50;
				 $scriptpageprevnext = 'email_preferences.php?cmd=listing';
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
				 
		
		
		$consign_user = $ilance->db->query("SELECT user_id, username, first_name, last_name, email, phone, city, zip_code
                                FROM " . DB_PREFIX . "users ORDER BY user_id desc LIMIT " . (($ilance->GPC['page'] - 1) * 50) . "," . '50'."", 0, null, __FILE__, __LINE__);
		
	
		$consign1_user = $ilance->db->query("SELECT * FROM ".DB_PREFIX."users ORDER BY user_id desc", 0, null, __FILE__, __LINE__);

		$row_con_list=0;	
		$number = (int)$ilance->db->num_rows($consign1_user);
		$row_list = array();
		while($resconsign_user = $ilance->db->fetch_array($consign_user))
		{
		
			
			      $row_list['user_id'] = $resconsign_user['user_id'];
				  $row_list['username'] = $resconsign_user['username'];
				  $row_list['email'] = $resconsign_user['email'];
				  $row_list['edit'] = '<a href="email_preferences.php?cmd=email_list_edit&amp;id=' . $resconsign_user['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" title="Edit" alt="" border="0"></a>';

  

   				  $email_preferences_listing[] = $row_list;	
				  $row_con_list++; 
			
		}			
		
		
    $listing_pagnation = print_pagnation($number, 50, $ilance->GPC['page'], $counter, $scriptpageprevnext);


 	$pprint_array = array('number_search','number','listing_pagnation', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer','login_include_admin','ilanceversion','get_filtervalue');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'email_preferences.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('email_preferences_listing','email_preferences_search'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
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