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
//karthik on july20 for return
 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'list_return_user')
   {
  
				 $user_id   = $ilance->GPC['user_id'];
				 $seller_name = fetch_user('username',$ilance->GPC['user_id']);
				
				 $consignid  = $ilance->GPC['consignid'];
				 $coin_id     = $ilance->GPC['coin_id'];
				 
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers order by basefee asc");
			if($ilance->db->num_rows($sql))
			{
			$html='';
			
			$html.='<select name="shipper" id="shipper">
			<option value="-1"  >Select Shipper</option>';
				while($line=$ilance->db->fetch_array($sql))
				{
				
				
				$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'</option>';
			}
			}
			
			/* $coin_listing_co = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects
													WHERE status ='expired'
													 AND haswinner = '0'
													 AND buynow_qty > '0'
													 AND user_id = '".$ilance->GPC['user_id']."'
										 ");
				*/			
			 $coin_listing_co = $ilance->db->query("SELECT *
							FROM 
							 " . DB_PREFIX . "projects p,
							 ".DB_PREFIX."coins c
							WHERE (p.status ='expired' OR p.status = 'closed')
							AND p.haswinner = '0'
							AND p.buynow_qty > '0'
							AND p.project_id = c.coin_id
							AND c.project_id != 0
							AND p.user_id = '".$ilance->GPC['user_id']."' 
							order by c.coin_id");
						
				while($row_coin_list = $ilance->db->fetch_array($coin_listing_co))
					
				{				 
			     $coinid_list[]=$row_coin_list['project_id'];	
			     }
				$coinid=implode(",",$coinid_list);
			
			 //list of return
				$con_listing = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "coin_return 
										");
							            $newnumber = (int)$ilance->db->num_rows($con_listing);
							            if($ilance->db->num_rows($con_listing) > 0)
										{
										$row_con_list = 0;
										while($row_list = $ilance->db->fetch_array($con_listing))
										{	
												
										$row_list['chargeamt'] = $ilance->currency->format($row_list['charges']); 
										$row_list['shipamt'] = $ilance->currency->format($row_list['shipping_fees']); 
										$row_list['sel'] =  fetch_user('username',$row_list['user_id']);
									
										$return_sale_list[] = $row_list;
										$row_con_list++;
										
										}
										
						                }
										
										else
										{				
										$show['no'] = 'return_list';
								     	}
				 if (isset($ilance->GPC['type']))
				 $show['return']='all_coin';
				 else
				 $show['return']='';
				 
				  $form_action=$ilpage['rependings'];
     $pprint_array = array('newnumber','html','coin_id','consignid','seller_name','user_id','seller_id','seller_name','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action','coinid');
			
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'return_coin.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('return_sale_list','cancel_sale_list'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
   }
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'insert_return_user')
   {
   
	  			/*  $coin_listing_co = $ilance->db->query("
							SELECT p.user_id,c.consignid,c.coin_id
							FROM 
							 " . DB_PREFIX . "projects p ,
							  " . DB_PREFIX . "coins c
							WHERE p.status ='expired'
							AND p.user_id='".$ilance->GPC['user_id']."'
							AND p.haswinner = '0'
							AND p.buynow_qty > '0'
							AND c.project_id=p.project_id
												
							");
							
				$count= 0;
				
				$total_coins = 	(int)$ilance->db->num_rows($coin_listing_co);		
							
						
				while($row_coin_list = $ilance->db->fetch_array($coin_listing_co))
					
				{				 */
				
				   
				  $coinid=array_filter(explode(",",$ilance->GPC['coin_list']));
				  
				  $total_coins = count($coinid);
				  
				  if($total_coins > 0)
					
				  {	
				  
				     $count= 0; 
	
				     for($i=0;$i<$total_coins;$i++)
				  
				     {
							$con  = $ilance->db->query("SELECT basefee FROM " . DB_PREFIX . "shippers 
							                             WHERE shipperid='".$ilance->GPC['shipper']."'
													 ");
		
							$row = $ilance->db->fetch_array($con);
		
							
		
							$amount_buy = $ilance->GPC['feesc'] + $row['basefee'];
		
		
							$ilance->accounting = construct_object('api.accounting');
		
								
		
							//seller invoice create 
		
							$invoiceid = $ilance->accounting->insert_transaction(
		
								0,
		
								0,
		
								0,
		
								$ilance->GPC['user_id'],
		
								0,
		
								0,
		
								0,
		
								'Your requested  for return coin, Shipping fees = $'.$row['basefee'].' + Charge fees = $'.$ilance->GPC['feesc'],
		
								sprintf("%01.2f", $amount_buy),
		
								sprintf("%01.2f", $amount_buy),
		
								'paid',
		
								'debit',
		
								'account',
		
								DATETIME24H,
		
								DATETIME24H,
		
								DATETIME24H,
		
								'Return coin',
		
								0,
		
								0,
		
								1,
		
								'',
		
								0,
		
								0
		
							);
		
							
		
							
		/*
							$conw  = $ilance->db->query("SELECT invoiceid
		
							FROM " . DB_PREFIX . "invoices ORDER BY invoiceid DESC");
		
							$roww = $ilance->db->fetch_array($conw);
		
							
		
							$invocie_id = $roww['invoiceid'];				    
		*/
							
							
							 //invocie id last
							 
							 // $invocie_id = $invoiceid;
		
							 //insert cancel value 
		
							 $con_insert = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_return
		
							 (coin_id, consign_id, user_id, shipper_id, shipping_fees, charges, return_date, invoiceid, notes, return_opt)
		
							 VALUES (
		
								    '".$coinid[$i]."',
		
								   '".$ilance->GPC['consignid']."',
		
								   '".$ilance->GPC['user_id']."',
		
								   '".$ilance->GPC['shipper']."',
		
								   '".$row['basefee']."',
		
								   '".$ilance->GPC['feesc']."',
		
								   '".DATETODAY."',
		
								   '".$invoiceid."',
						   
						           '".$ilance->GPC['notes']."',
						   
						           '".$ilance->GPC['return_opt']."'
		
							  
		
							)");
		
							//suku corrected
		
							$con_insert1 = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coins_retruned SELECT * FROM ilance_coins where coin_id='".$coinid[$i]."'");
		
		
							$con_insert2 = $ilance->db->query("delete FROM " . DB_PREFIX . "coins where coin_id='".$coinid[$i]."'");
							
							
							$con_delete = $ilance->db->query("delete FROM " . DB_PREFIX . "projects where project_id='".$coinid[$i]."'");
							
							
							$count++;
				     }	
					if($count==$total_coins)
					{
					print_action_success('Your Coin Details Returned successfully', 'rependings.php');
					exit();
					
					}
					else
					{
					
					 print_action_failed('Your Coin Details havent Returned successfully', 'rependings.php');
					 exit();
					
					}
					
				}
				else
				{
					
				   print_action_failed('Please enter coin_id for return', 'rependings.php');
					exit();
					
				}	
   }
/*  sekar works on check box on aug 4*/
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'check')  
    {
	          $checkbox=$ilance->GPC['documents'];
		       $document=implode(',',$checkbox);
			   
			   if(count($checkbox) > 0)
			   {
							$con_listing = $ilance->db->query("
							SELECT *
							FROM 
							 " . DB_PREFIX . "projects p,
							 ".DB_PREFIX."coins c
							WHERE (p.status ='expired' OR p.status = 'closed')
							AND p.haswinner = '0'
							AND p.buynow_qty > '0'
							AND p.project_id = c.coin_id
							AND c.project_id != 0
							AND p.user_id in (".$document.")
							
							
							");
							   
								if($ilance->db->num_rows($con_listing) > 0)
								{
									
									while($row_list = $ilance->db->fetch_array($con_listing))
									{
									$relist = $ilance->db->query("SELECT relist_count FROM " . DB_PREFIX . "coins WHERE coin_id = '".$row_list['project_id']."'");
									$resrelist = $ilance->db->fetch_array($relist);
									$newcount = $resrelist['relist_count'] + 1;	
										//insert into coin_relist
											  $relist_sql="insert into " . DB_PREFIX . "coin_relist (coin_id,enddate,startbydate,user_id,actual_end_date,filtered_auctiontype) values 
											('".$row_list['project_id']."','0000-00-00 00:00:00','0000-00-00 00:00:00','".$row_list['user_id']."','".$row_list['date_end']."','".$row_list['filtered_auctiontype']."') ";
											 ;
										$ilance->db->query($relist_sql);
										$sqsl = $ilance->db->query("UPDATE " . DB_PREFIX . "coins SET project_id = '0' , End_Date = '0000-00-00 00:00:00', Quantity = '".$row_list['buynow_qty']."', relist_count = '".$newcount."' WHERE coin_id = '".$row_list['project_id']."'");
										
										/*$sql2 = $ilance->db->query("
										   DELETE FROM " . DB_PREFIX . "projects
										   WHERE project_id = '" . intval($row_list['project_id']) . "'
										    ");	*/
											
										//karthik on jun01 for daily deal
								          $sql3 = $ilance->db->query("
												   DELETE FROM " . DB_PREFIX . "dailydeal
												   WHERE project_id = '" . intval($row_list['project_id']) . "'");											
									}
								print_action_success("Coin Relisted Successfully, This Item Will Loaded in pending area. After updating end date its will posted as live", $ilpage['pending']);
                				exit();
								}
									
                   }
				   
				   else
				   
				   {
				   
				    print_action_failed("Please Select the Checkbox for Relist.", 'rependings.php');
					
                	exit();
				   
				   }
							
		  
		  
	} 
/*  sekar finished  works on check box on aug 4*/
                            //Set end date for consignment
							if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'all_relist' AND !empty($ilance->GPC['user_id']))
							{
							      
							$con_listing = $ilance->db->query("
							SELECT *
							FROM 
							 " . DB_PREFIX . "projects p,
							 ".DB_PREFIX."coins c
							WHERE (p.status ='expired' OR p.status = 'closed')
							AND p.haswinner = '0'
							AND p.buynow_qty > '0'
							AND p.project_id = c.coin_id
							AND c.project_id != 0
							AND p.user_id = '".$ilance->GPC['user_id']."'
							
							
							");
							            
								if($ilance->db->num_rows($con_listing) > 0)
								{
									
									while($row_list = $ilance->db->fetch_array($con_listing))
									{
									$relist = $ilance->db->query("SELECT relist_count FROM " . DB_PREFIX . "coins WHERE coin_id = '".$row_list['project_id']."'");
									$resrelist = $ilance->db->fetch_array($relist);
									$newcount = $resrelist['relist_count'] + 1;		
										//insert into coin_relist
											  $relist_sql="insert into " . DB_PREFIX . "coin_relist (coin_id,enddate,startbydate,user_id,actual_end_date,filtered_auctiontype) values 
											('".$row_list['project_id']."','0000-00-00 00:00:00','0000-00-00 00:00:00','".$row_list['user_id']."','".$row_list['date_end']."','".$row_list['filtered_auctiontype']."') ";
											 ;
											$ilance->db->query($relist_sql);
										$sqsl = $ilance->db->query("UPDATE " . DB_PREFIX . "coins SET project_id = '0' , End_Date = '0000-00-00 00:00:00', Quantity = '".$row_list['buynow_qty']."', relist_count = '".$newcount."' WHERE coin_id = '".$row_list['project_id']."'");
										
										/*$sql2 = $ilance->db->query("
										   DELETE FROM " . DB_PREFIX . "projects
										   WHERE project_id = '" . intval($row_list['project_id']) . "'
										    ");	*/	
											
									 //karthik june1
									 $sql3 = $ilance->db->query("
										   DELETE FROM " . DB_PREFIX . "dailydeal
										   WHERE project_id = '" . intval($row_list['project_id']) . "'
										    ");										
									}
								print_action_success("Coin Relisted Successfully, This Item Will Loaded in pending area. After updating end date its will posted as live", $ilpage['pending']);
                				exit();
								}
									
							}
							
                           
						   
						   if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'relist')
						   {
						   
						  
						   $coin_and_project="SELECT c.user_id, c.End_Date, c.coin_id, c.relist_count, p.buynow_qty, p.filtered_auctiontype, p.date_end
						FROM " . DB_PREFIX . "coins c, " . DB_PREFIX . "projects p WHERE c.coin_id = '".intval($ilance->GPC['id'])."' AND c.coin_id = p.project_id";
						 
						   		$relist = $ilance->db->query($coin_and_project);
								$resrelist = $ilance->db->fetch_array($relist);
								$newcount = $resrelist['relist_count'] + 1;
								$qty = $resrelist['buynow_qty'];
								$filtered_auctiontype = $resrelist['filtered_auctiontype'];								
								 
								//insert into coin_relist
								  $relist_sql="insert into " . DB_PREFIX . "coin_relist (coin_id,enddate,startbydate,user_id,actual_end_date,filtered_auctiontype) values 
								('".intval($ilance->GPC['id'])."','0000-00-00 00:00:00','0000-00-00 00:00:00','".$resrelist['user_id']."','".$resrelist['date_end']."','".$filtered_auctiontype."') ";
							 
								$ilance->db->query($relist_sql);
								$sqsl = $ilance->db->query("UPDATE " . DB_PREFIX . "coins SET project_id = '0' , End_Date = '0000-00-00 00:00:00', Quantity = '".$qty."', relist_count = '".$newcount."' WHERE coin_id = '".intval($ilance->GPC['id'])."'");
								
								/*$sql2 = $ilance->db->query("
												   DELETE FROM " . DB_PREFIX . "projects
												   WHERE project_id = '" . intval($ilance->GPC['id']) . "'");*/
												   
												   
								//karthik june1
									 $sql3 = $ilance->db->query("
										   DELETE FROM " . DB_PREFIX . "dailydeal
										   WHERE project_id = '" . intval($ilance->GPC['id']) . "'
										    ");	 
								print_action_success("Coin Relisted Successfully, This Item Will Loaded in pending area. After updating end date its will posted as live", $ilpage['pending']);
                				exit(); 
						   } 	
						   
						   //list for pending
                            /*$con_listing = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "coins 
							WHERE coin_listed = 'c'
							AND (End_Date = '0000-00-00' OR pending = '1')							
							AND project_id  = '0'
							AND status = '0'
							GROUP BY consignid 
							");*/
							$user_listing = $ilance->db->query("
							SELECT *
							FROM 
							 " . DB_PREFIX . "projects p,
							 ".DB_PREFIX."coins c
							WHERE (p.status ='expired' OR p.status = 'closed')
							AND p.haswinner = '0'
							AND p.buynow_qty > '0'
							AND p.project_id = c.coin_id
							AND c.project_id != 0
							GROUP BY p.user_id							
							");
							$numbers = (int)$ilance->db->num_rows($user_listing);
							if($ilance->db->num_rows($user_listing) > 0)
							{
								$row_user_list = 0;
								while($row_list = $ilance->db->fetch_array($user_listing))
								{
									$user_listing_count = $ilance->db->query("
													SELECT COUNT(p.project_id) AS total
													FROM " . DB_PREFIX . "projects p,
													".DB_PREFIX."coins c 
													WHERE p.user_id ='".$row_list['user_id']."'
													AND p.haswinner = '0'
													AND p.buynow_qty > '0'
													AND p.project_id = c.coin_id
													AND c.project_id != 0
													AND (p.status ='expired' OR p.status = 'closed')
												");
										$user_count = $ilance->db->fetch_array($user_listing_count);
										$row_list['username'] = fetch_user('username', $row_list['user_id']);
										
										$row_list['posted']    = $user_count['total'];
										
									    $row_list['action'] = '<a href=rependings.php?subcmd=list&amp;user_id='.$row_list['user_id'].'>Click</a>'; 
										
										/*sekar working on checkbox on july 19*/
										
										$row_list['checkbox']='<input type="checkbox" name="documents[]" value="'.$row_list['user_id'].'">';
										
										
										$row_list['return_con'] = '<span class="blue"><a href="rependings.php?cmd=list_return_user&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'&type=all_coin_return">Return</a></span>';
											
											$row_list['form_action']='<form method="post" action="rependings.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				<input type="hidden" name="cmd" value="insert_return_user" />
				<input type="hidden" name="return" value="rependings.php" />';
										
										$user_list[] = $row_list;
										$row_user_list++;
								}
							}
							if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'list')
							{
							 //counter for page 
		  
		  //karthik start apr 21
				
                 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
				 $scriptpageprevnext = $ilpage['rependings']. '?subcmd=list&amp;user_id='.$ilance->GPC['user_id'].'';
				 
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
							FROM 
							 " . DB_PREFIX . "projects p,
							 ".DB_PREFIX."coins c
							WHERE (p.status ='expired' OR p.status = 'closed')
							AND p.haswinner = '0'
							AND p.buynow_qty > '0'
							AND p.project_id = c.coin_id
							AND c.project_id != 0
							AND p.user_id = '".$ilance->GPC['user_id']."'
							
						GROUP BY p.project_id asc LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."");
							}
							else
							{
							
							 //counter for page 
		  
		  //karthik start apr 21
				
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
							FROM 
							 " . DB_PREFIX . "projects p,
							 ".DB_PREFIX."coins c
							WHERE (p.status ='expired' OR p.status = 'closed')
							AND p.haswinner = '0'
							AND p.buynow_qty > '0'
							AND p.project_id = c.coin_id
							AND c.project_id != 0						
								GROUP BY p.project_id asc LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."");
							}
							           // $number = (int)$ilance->db->num_rows($con_listing);
										if($ilance->GPC['user_id'] > 0)
											$user_name = '<a href="rependings.php?subcmd=all_relist&amp;user_id='.$ilance->GPC['user_id'].'">'.fetch_user('username',$ilance->GPC['user_id']).' (relist all)</a>';
										else
											$user_name = 'All';
							            if($ilance->db->num_rows($con_listing) > 0)
										{
										if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'list')
							{
										
								$con_listing1 = $ilance->db->query("
							SELECT *
							FROM 
							 " . DB_PREFIX . "projects p,
							 ".DB_PREFIX."coins c
							WHERE (p.status ='expired' OR p.status = 'closed')
							AND p.haswinner = '0'
							AND p.buynow_qty > '0'
							AND p.project_id = c.coin_id
							AND c.project_id != 0
							AND p.user_id = '".$ilance->GPC['user_id']."'
							   ");		
								}
								else
								{
								
								$con_listing1 = $ilance->db->query("
								SELECT *
							FROM 
							 " . DB_PREFIX . "projects p,
							 ".DB_PREFIX."coins c
							WHERE (p.status ='expired' OR p.status = 'closed')
							AND p.haswinner = '0'
							AND p.buynow_qty > '0'
							AND p.project_id = c.coin_id
							AND c.project_id != 0					
								");
								}
                                            $number = (int)$ilance->db->num_rows($con_listing1);
											$row_con_list = 0;
											while($row_list = $ilance->db->fetch_array($con_listing))
											{											
												$con_listing_co = $ilance->db->query("
														SELECT * 
														FROM " . DB_PREFIX . "coins 
														WHERE user_id ='".$row_list['user_id']."'
														AND coin_id = '".$row_list['project_id']."'
															 ");
														 
												if($ilance->db->num_rows($con_listing_co) > 0)
												{
												$row_list_co = $ilance->db->fetch_array($con_listing_co);
																												
												$row_list_co['user_id'] = fetch_user('username', $row_list_co['user_id']);	
												$row_list_co['action'] = '<a href="rependings.php?cmd=relist&amp;id='.$row_list_co['coin_id'].'">Relist</a>';
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
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>