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
error_reporting(E_ALL);
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{	
/*  sekar works on check box on aug 4*/
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'check')  
    {
	          $checkbox=$ilance->GPC['documents'];
		       $document=implode(',',$checkbox);
			   
			   if(count($checkbox) > 0)
			   {
				$con_listing = $ilance->db->query("
				select c.user_id,c.coin_id,c.consignid from  " . DB_PREFIX . "ebay_listing el 
				left join  " . DB_PREFIX . "coins c 
				on c.coin_id=el.coin_id 
				where el.status ='expired'  and el.relisted=0
				and el.user_id in (".$document.")
				group by c.coin_id");
				   
					if($ilance->db->num_rows($con_listing) > 0)
					{
						while($row_list = $ilance->db->fetch_array($con_listing))
						{
						relist_ebay_coin($row_list['coin_id']);
						}
					}
			   }
			   else
			   {
				print_action_failed("Please Select the Checkbox for Relist.", 'rependings_ebay.php');
				exit();
			   }
	} 

						   
   if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'relist' AND isset($ilance->GPC['id']) and $ilance->GPC['id']>0)
   {
   	relist_ebay_coin($ilance->GPC['id']);
   } 	
						    

							$user_listing = $ilance->db->query("
							SELECT  distinct el.user_id 
							FROM    " . DB_PREFIX . "ebay_listing el
							LEFT JOIN " . DB_PREFIX . "coins c ON el.coin_id =  c.coin_id
							WHERE   NOT EXISTS
							(
							    SELECT  NULL
							    FROM    " . DB_PREFIX . "ebay_listing r
							    WHERE   r.coin_id = el.coin_id AND r.status IN('listed','sold')
							    GROUP BY r.user_id
							)
							GROUP BY el.coin_id					
							");

							

							$numbers = (int)$ilance->db->num_rows($user_listing);
							if($ilance->db->num_rows($user_listing) > 0)
							{
								$row_user_list = 0;
								while($row_list = $ilance->db->fetch_array($user_listing))
								{
									
										$row_list['username'] = fetch_user('username', $row_list['user_id']);
										
										$ebaycoins_list = $ilance->db->query("
											SELECT el.coin_id
											FROM    " . DB_PREFIX . "ebay_listing el
											LEFT JOIN " . DB_PREFIX . "coins c ON el.coin_id =  c.coin_id
											WHERE   NOT EXISTS
											(
											    SELECT  NULL
											    FROM    " . DB_PREFIX . "ebay_listing r
											    WHERE   r.coin_id = el.coin_id AND r.status IN('listed','sold')
											    GROUP BY r.user_id
											)
											AND el.user_id = '".$row_list['user_id']."'
											GROUP BY el.coin_id					
											");


										$row_list['posted']    = (int)$ilance->db->num_rows($ebaycoins_list);
										
									    $row_list['action'] = '<a href=rependings_ebay1.php?subcmd=list&amp;user_id='.$row_list['user_id'].'>Click</a>'; 
										
										/*sekar working on checkbox on july 19*/
										
										$row_list['checkbox']='<input type="checkbox" name="documents[]" value="'.$row_list['user_id'].'">';
										
										
										$row_list['return_con'] = '<span class="blue"><a href="rependings_ebay.php?cmd=list_return_user&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'&type=all_coin_return">Return</a></span>';
											
											$row_list['form_action']='<form method="post" action="rependings_ebay1.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				<input type="hidden" name="cmd" value="insert_return_user" />
				<input type="hidden" name="return" value="rependings_ebay.php" />';
										
										$user_list[] = $row_list;
										$row_user_list++;
								}
							}
							$con_listing ='';
							if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'list')
							{
								if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
								{
								$ilance->GPC['page'] = 1;
								}
								else
								{
								$ilance->GPC['page'] = intval($ilance->GPC['page']);
								}

								$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
								$scriptpageprevnext = $ilpage['rependings']. '?subcmd=list&amp;user_id='.$ilance->GPC['user_id'].'';
								
								$con_listing = $ilance->db->query(
								"SELECT  *
								FROM  " . DB_PREFIX . "ebay_listing el
								LEFT JOIN " . DB_PREFIX . "coins c ON el.coin_id =  c.coin_id
								WHERE   NOT EXISTS
								(
								    SELECT  NULL
								    FROM    " . DB_PREFIX . "ebay_listing r
								    WHERE   r.coin_id = el.coin_id AND r.status IN('listed','sold')
								)
								AND el.user_id = '".$ilance->GPC['user_id']."'
								GROUP BY el.coin_id 
								asc LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . 
								$ilconfig['globalfilters_maxrowsdisplaysubscribers']."");

							}
							else
							{
						
							 //counter for page 
		  
		  //karthik start apr 21
				$ilance->GPC['page']=isset($ilance->GPC['page'])?$ilance->GPC['page']:1;
				$ilpage['rependings']=HTTP_SERVER.'staff/rependings_ebay.php';
                 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
				 $scriptpageprevnext = $ilpage['rependings']. '?cmd=list';
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
				
				
			
								$con_listing = $ilance->db->query("
									SELECT *
									FROM    " . DB_PREFIX . "ebay_listing el
									LEFT JOIN " . DB_PREFIX . "coins c ON el.coin_id =  c.coin_id
									WHERE   NOT EXISTS
									(
									    SELECT  NULL
									    FROM    " . DB_PREFIX . "ebay_listing r
									    WHERE   r.coin_id = el.coin_id AND r.status IN('listed','sold')
									    GROUP BY r.user_id
									)
									GROUP BY el.coin_id		
									asc LIMIT " . (($ilance->GPC['page'] - 1) * 
									$ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . 
									$ilconfig['globalfilters_maxrowsdisplaysubscribers'].""
								);
							
								}
							           // $number = (int)$ilance->db->num_rows($con_listing);
										if(isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0)
											$user_name = '<a href="rependings_ebay.php?subcmd=all_relist&amp;user_id='.$ilance->GPC['user_id'].'">'.fetch_user('username',$ilance->GPC['user_id']).' (relist all)</a>';
										else
											$user_name = 'All';
											
											
							            if($ilance->db->num_rows($con_listing) > 0)
										{
											if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'list')
											{
												$con_listing1 = $ilance->db->query("
													SELECT *
													FROM    " . DB_PREFIX . "ebay_listing el
													LEFT JOIN " . DB_PREFIX . "coins c ON el.coin_id =  c.coin_id
													WHERE   NOT EXISTS
													(
													    SELECT  NULL
													    FROM    " . DB_PREFIX . "ebay_listing r
													    WHERE   r.coin_id = el.coin_id AND r.status IN('listed','sold')
													    GROUP BY r.user_id
													)
													AND el.user_id = '".$ilance->GPC['user_id']."'
													GROUP BY el.coin_id		 		
											   ");		
											}
											else
											{
												$con_listing1 = $ilance->db->query("
													SELECT *
													FROM    " . DB_PREFIX . "ebay_listing el
													LEFT JOIN " . DB_PREFIX . "coins c ON el.coin_id =  c.coin_id
													WHERE   NOT EXISTS
													(
													    SELECT  NULL
													    FROM    " . DB_PREFIX . "ebay_listing r
													    WHERE   r.coin_id = el.coin_id AND r.status IN('listed','sold')
													    GROUP BY r.user_id
													)
													GROUP BY el.coin_id		 				
												");
											}
								//$row_list = $ilance->db->fetch_array($con_listing);
                                            $number = (int)$ilance->db->num_rows($con_listing1);
											$row_con_list = 0;
											while($row_list = $ilance->db->fetch_array($con_listing))
											{
												$con_listing_co = $ilance->db->query("
														SELECT * 
														FROM " . DB_PREFIX . "coins 
														WHERE user_id ='".$row_list['user_id']."'
														AND coin_id = '".$row_list['coin_id']."'
															 ");
														 
												if($ilance->db->num_rows($con_listing_co) > 0)
												{
												$row_list_co = $ilance->db->fetch_array($con_listing_co);
																						
												$row_list_co['user_id'] = fetch_user('username', $row_list_co['user_id']);	
												$row_list_co['action'] = '<a href="rependings_ebay.php?cmd=relist&amp;id='.$row_list_co['coin_id'].'">Relist</a>';
												$row_list_co['return_con'] = '<span class="blue"><a href="listings.php?cmd=list_return_user&consignid='.$row_list_co['consignid'].'&user_id='.$row_list['user_id'].'&coin_id='.$row_list_co['coin_id'].'">Return</a></span>';
												$pending_listnew[] = $row_list_co;
												$row_con_list++;
												}
										
											 }
										
						                }
										
										else
										{				
										$show['no'] = 'pending_list';
								     	}
				$pagnation = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);						
										
	$pprint_array = array('pagnation','user_name','numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','checkbox');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'repending.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('pending_listnew','pending_list_search','user_list'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

function relist_ebay_coin($coin_id)
{
global $ilance;

  $query="SELECT * FROM " . DB_PREFIX . "coins WHERE status=1 and pending=0 and site_id=1 and coin_id = ".intval($coin_id);
	$result=$ilance->db->query($query);
	if($ilance->db->num_rows($result))
	{
		while($line=$ilance->db->fetch_array($result))
		{
		if($line['Site_Id']==1)
		{
		 $query1="SELECT * FROM " . DB_PREFIX . "ebay_listing WHERE coin_id = ".intval($coin_id);
			$result1=$ilance->db->query($query1);
			if($ilance->db->num_rows($result1))
			{
				while($line1=$ilance->db->fetch_array($result1))
				{
				if($line1['status']=='expired')
				{
				$qty=$line1['quantity']-$line1['sld_quantity'];
				 $coin_sql="UPDATE " . DB_PREFIX . "coins SET project_id = '0', status=0 , pending=1  , End_Date = '0000-00-00 00:00:00', Quantity = '".$qty."', relist_count =relist_count+1 WHERE coin_id = '".intval($coin_id)."'";
					$ilance->db->query($coin_sql);
				 $relist_sql="insert into " . DB_PREFIX . "coin_relist (coin_id,enddate,startbydate,user_id,actual_end_date,filtered_auctiontype) values ('".intval($coin_id)."','0000-00-00 00:00:00','0000-00-00 00:00:00','".$line1['user_id']."','".$line1['end_date']."','".$line1['item_type']."') ";
					$ilance->db->query($relist_sql);
				 $ebay_query1="UPDATE " . DB_PREFIX . "ebay_listing set relisted=1 WHERE id = ".$line1['id'];
					$ilance->db->query($ebay_query1);
					  
				}else
				{
					echo intval($coin_id).' hadnt expired on ebay current status <b>'.$line1['status'].'</b>';
				}
				}
			}else
			{
			
			}
		}else
		{
		echo 'Not an ebay coin'.$line['Site_Id'];
		exit;
		}
		}
	}	 
	
	
}
							
							
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>