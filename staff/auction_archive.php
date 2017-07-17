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
	'jquery',
	'jquery_custom_ui',
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


if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{	

    // Update visible
	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_visible')
	{
			$check_count = $ilance->GPC['incheckdate'];
				$ilance->db->query("UPDATE  " . DB_PREFIX . "projects
									SET  visible = '1'
									WHERE  user_id = '".$ilance->GPC['user_id']."'
									AND (status ='expired'
										OR status ='closed')
									");
							
				for($k=0;$k<count($check_count);$k++)
				{
					$con_insert_cointable = $ilance->db->query("UPDATE  " . DB_PREFIX . "projects
									                            SET  visible = '3'
																WHERE  project_id = '".$check_count[$k]."'
																AND (status ='expired'
																	OR status ='closed')
																");
								
				}
												
												
				print_action_success("Updated Successfully", "auction_archive.php");
										
				exit();
						
		}
                         
		//search list
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list')
        {
					
			$show['search_list'] = 'search_list_pend';
			
			 $sql2_search = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users
												WHERE user_id = '".$ilance->GPC['filtervalue']."'
												OR    username = '".$ilance->GPC['filtervalue']."'
												OR    email    = '".$ilance->GPC['filtervalue']."'
												OR    zip_code  = '".$ilance->GPC['filtervalue']."'
												");
			if ($ilance->db->num_rows($sql2_search) > 0)
		    {
			    $res_list_sec = $ilance->db->fetch_array($sql2_search);
											
			}
									
		   $con_listing = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects 
											WHERE user_id = '".$res_list_sec['user_id']."'  
											AND (status =  'expired'
												OR status =  'closed')
											GROUP BY user_id
							              ");
						
		   $numbers = (int)$ilance->db->num_rows($con_listing);
			if($ilance->db->num_rows($con_listing) > 0)
			{
			$row_con_search = 0;
			while($row_list = $ilance->db->fetch_array($con_listing))
			{
											
			    $con_listing_co = $ilance->db->query("SELECT COUNT(*) AS total
														FROM " . DB_PREFIX . "projects 
														WHERE user_id ='".$row_list['user_id']."'
														AND (status =  'expired'
														 OR status =  'closed')
														 ");
													 
				 while($row_list_co = $ilance->db->fetch_array($con_listing_co))
				{
				$total_value = $row_list_co['total'];
				}
											
				$row_list['username'] = fetch_user('username', $row_list['user_id']);
				
				$row_list['posted']    = $total_value;
				
				$row_list['View'] = '<span style="cursor:pointer;" onclick="checkended('.$row_list['user_id'].');">Click</span>'; 
				
				
				$row_list['form_action'] = '<form method="post" action="auction_archive.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
<input type="hidden" name="cmd" value="insert_return_user" />
<input type="hidden" name="return" value="auction_archive.php" />';
				
				$pending_list_search[] = $row_list;
				$row_con_search++;
										
			}
										
		 }
		
		else
		{				
		$show['no'] = 'list_search';
		}
					 
	} 	
						   
	  
	  //list for Auction archive
				   
		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
		
		 $scriptpageprevnext ='auction_archive.php?';
		 
		 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
		 {
			$ilance->GPC['page'] = 1;
		 }
		 else
		 {
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		 }

             $con_listing = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects 
							                    WHERE  status =  'expired'
                                                 OR status =  'closed'
							                   GROUP BY user_id asc LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," .$ilconfig['globalfilters_maxrowsdisplaysubscribers']."");
							
			if($ilance->db->num_rows($con_listing) > 0)
			{
			   $row_con_list = 0;
										
			    $con_listing1 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects 
														WHERE  status =  'expired'
                                                            OR status =  'closed'
														GROUP BY user_id ");
														
				   $number = (int)$ilance->db->num_rows($con_listing1);

					while($row_list = $ilance->db->fetch_array($con_listing))
					{
						
						$con_listing_co = $ilance->db->query("
								SELECT COUNT(*) AS total
								FROM " . DB_PREFIX . "projects 
								WHERE user_id ='".$row_list['user_id']."'
								AND (status =  'expired'
								 OR status =  'closed')
									 ");
								 
					 while($row_list_co = $ilance->db->fetch_array($con_listing_co))
					{
					$total_value = $row_list_co['total'];
					}
												
					$row_list['username'] = fetch_user('username', $row_list['user_id']);
					
					$row_list['posted']    = $total_value;
					
					$row_list['View'] = '<span style="cursor:pointer;" onclick="checkended('.$row_list['user_id'].');">Click</span>'; 
					
					$row_list['return_con'] = '<input type="checkbox" name="block" value="'.$row_list['project_id'].'">';
					
					
					$row_list['form_action'] = '<form method="post" action="auction_archive.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">';
					
					$pending_list[] = $row_list;
					$row_con_list++;
					
					}
			
					
					}
					
					else
					{				
					$show['no'] = 'pending_list';
					}
	$listing_pagnation = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);									
										
	$pprint_array = array('numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','listing_pagnation');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'auction_archive.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('pending_list','pending_list_search'));
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