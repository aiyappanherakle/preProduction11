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
 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'list_return_user')
   {
                 //get list
				
				
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
			
			//new change on 12jan04
			  $coin_listing_co = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins 
									WHERE user_id ='".$ilance->GPC['user_id']."'
									AND consignid = '".$ilance->GPC['consignid']."'
									AND coin_listed = 'c'
									AND (End_Date = '0000-00-00' OR pending = '1')	
									AND project_id  = '0'
									AND status = '0'
										 ");
							
							
						
						
				while($row_coin_list = $ilance->db->fetch_array($coin_listing_co))
					
				{				 
			     $coinid_list[]=$row_coin_list['coin_id'];	
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
				 
				 $form_action=$ilpage['pendings'];
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
   
	  			  $coin_listing_co = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "coins 
									WHERE user_id ='".$ilance->GPC['user_id']."'
									AND consignid = '".$ilance->GPC['consignid']."'
									AND coin_listed = 'c'
									AND (End_Date = '0000-00-00' OR pending = '1')	
									AND project_id  = '0'
									AND status = '0'
										 ");
							
							
						
						
				while($row_coin_list = $ilance->db->fetch_array($coin_listing_co))
					
				{				 
					$con  = $ilance->db->query("SELECT basefee
					FROM " . DB_PREFIX . "shippers WHERE shipperid='".$ilance->GPC['shipper']."'");
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
						'Your requested  for return coin',
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
					
					//invocie id last
					$conw  = $ilance->db->query("SELECT invoiceid
					FROM " . DB_PREFIX . "invoices ORDER BY invoiceid DESC");
					$roww = $ilance->db->fetch_array($conw);
					
					$invocie_id = $roww['invoiceid'];				    
					
					
					 //insert cancel value 
					 $con_insert = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_return
					 (coin_id, consign_id, user_id, shipper_id, shipping_fees, charges, return_date, invoiceid, notes, return_opt)
					 VALUES (
						   '".$row_coin_list['coin_id']."',
						   '".$ilance->GPC['consignid']."',
						   '".$ilance->GPC['user_id']."',
						   '".$ilance->GPC['shipper']."',
						   '".$row['basefee']."',
						   '".$ilance->GPC['feesc']."',
						   '".DATETODAY."',
						   '".$invocie_id."',
						   
						   '".$ilance->GPC['notes']."',
						   
						   '".$ilance->GPC['return_opt']."'
					  
					)");
					//suku corrected
					$con_insert1 = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coins_retruned SELECT * FROM  " . DB_PREFIX . "coins where coin_id='".$row_coin_list['coin_id']."'");
					
					$con_insert2 = $ilance->db->query("delete FROM " . DB_PREFIX . "coins where coin_id='".$row_coin_list['coin_id']."'");
					
					
					$con_delete = $ilance->db->query("delete FROM " . DB_PREFIX . "projects where project_id='".$row_coin_list['coin_id']."'");
					
					
				}	
					
					print_action_success('Your Coin Details Returned successfully', 'pendings.php');
					exit();
   }
                            //Set end date for consignment
							if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'allcoin_update')
							{
							       $date_coin = $_POST['year'] .'-'.$_POST['month'].'-'.$_POST['day'];
							       $con_listing_co = $ilance->db->query("
													SELECT *
													FROM " . DB_PREFIX . "coins 
													WHERE user_id ='".$_POST['user_id']."'
													AND consignid = '".$_POST['consignid']."'
													AND coin_listed = 'c'
													AND (End_Date = '0000-00-00' OR pending = '1')
													AND project_id  = '0'
													AND status = '0'
														 ");
													 
										 while($row_list_co = $ilance->db->fetch_array($con_listing_co))
										{
										    $con_insert_cointable = $ilance->db->query("
											UPDATE  " . DB_PREFIX . "coins
											SET  End_Date = '" . $date_coin . "',
											     pending  = '0'
											
												 WHERE  coin_id = '".$row_list_co['coin_id']."'
											
											");
											
											 $coin_id_last = $row_list_co['coin_id'];
											
							if($ilance->GPC['site_id'] == '0')
							{
								$restotal=$ilance->db->query("select * from " . DB_PREFIX . "coins WHERE date(End_Date) = '".$date_coin."'");
			
								if($ilance->db->num_rows($restotal) > 0)
								{	
								//$my_var_in = fetch_date_time_coin($date_coin);
								$my_var_in = fetch_date_time_coin($date_coin,$row_list_co['pcgs'],$coin_id_last);
								}
							}
											
											echo '<script type="text/javascript">
												  self.close();
												  window.opener.location.reload();
												  </script>';
									  }
							}
							//Set end date for coin
							if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'onecoin_update')
							{
							
						
							    $date_coin = $_POST['year'] .'-'.$_POST['month'].'-'.$_POST['day'];
								$con_insert_cointable = $ilance->db->query("
									UPDATE  " . DB_PREFIX . "coins
									SET  End_Date = '" . $date_coin . "',
									     Site_Id  = '".$_POST['site_id']."',
										 pending  = '0'
										 WHERE  coin_id = '".$_POST['coin_id']."'
									
									");
									
									 $coin_id_last = $_POST['coin_id'];
									 
									  $pcgsc = fetch_coin_consignid('pcgs',$coin_id_last);
									 //for end date coin function   
							if($ilance->GPC['site_id'] == '0')
							{
								$restotal=$ilance->db->query("select * from " . DB_PREFIX . "coins WHERE date(End_Date) = '".$date_coin."'");
			
								if($ilance->db->num_rows($restotal) > 0)
								{	
								//$my_var_in = fetch_date_time_coin($date_coin);
								$my_var_in = fetch_date_time_coin($date_coin,$pcgsc,$coin_id_last);
								}
							}
									
									echo '<script type="text/javascript">
									      self.close();
										  window.opener.location.reload();
										  </script>';
							}
							
							//new end date for pending through save
						/*	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'allcheck_update_date')
							{
							
							
							
							     if($ilance->GPC['incheckdate'] != '' && $ilance->GPC['datevalnew'] != '')
								 {
								    $dateexplode = explode('-',$ilance->GPC['datevalnew']);
									$check = mktime(0, 0, 0, $dateexplode[1], $dateexplode[2], $dateexplode[0]);
									$today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		
									if ($check > $today) {
									    $result_date =  1;
									} else if ($check == $today) {
									    $result_date =  1;
									} else {
									    $result_date =  0;
									} 
							
							 
							        if($result_date != '0')
							        { 
												$check_count = $ilance->GPC['incheckdate'];
												
												for($k=0;$k<count($check_count);$k++)
												{
												
														$con_insert_cointable = $ilance->db->query("
														UPDATE  " . DB_PREFIX . "coins
														SET  End_Date = '" . $ilance->GPC['datevalnew'] . "',
															 pending  = '0'
															 WHERE  coin_id = '".$check_count[$k]."'
														");
														
														$pcgsc = fetch_coin_consignid('pcgs',$check_count[$k]);
														
														$restotal=$ilance->db->query("select * from " . DB_PREFIX . "coins WHERE date(End_Date) = '".$ilance->GPC['datevalnew']."'");
														
														if($ilance->db->num_rows($restotal) > 0)
						
														{
														   //$my_var_in = fetch_date_time_coin($date_coin);
														   $my_var_in = fetch_date_time_coin($ilance->GPC['datevalnew'],$pcgsc,$check_count[$k]);
						
														}
												
												}
												
												
												print_action_success("End Date for pending sucessfully update", "pendings.php");
										
												exit();
									 }	
									 else
									 {
									    print_action_success("Please Check your End Date Field,it may be past", "pendings.php");
							
							            exit();
									 }	
								 
								 }
								 else
								 {
								   
								   print_action_success("Please select checkbox OR  end date field empty", "pendings.php");
							
							       exit();
								 }
							
							
							
							}*/
							
							if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'allcheck_update_date')
                            {
							  
								if($ilance->GPC['incheckdate'] != '' && $ilance->GPC['datevalnew'] != '')
								{
									$dateexplode = explode('-',$ilance->GPC['datevalnew']);
									$check = mktime(0, 0, 0, $dateexplode[1], $dateexplode[2], $dateexplode[0]);
									$today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
										if ($check > $today) {
											$result_date =  1;
										} else if ($check == $today) {
											$result_date =  1;
										} else {
											$result_date =  0;
										} 
								if($result_date != '0')
								{
									
									$selected_coins = $ilance->GPC['incheckdate'];
									$new_end_date = $ilance->GPC['datevalnew'];
									$site_id=isset($ilance->GPC['site_id'])?$ilance->GPC['site_id']:0;
									$flag=pending_set_end_date($selected_coins,$new_end_date,$site_id);
									if($flag)
									{
									print_action_success('End time for '.count($selected_coins).' Coins where updated', 'pendings.php');
									exit();
									}else
									{
									print_action_success('No coins have been Updated, you would have missed to check the coins or the left the End date column blank ', 'pendings.php');
									exit();	
									}
								exit();
								}	
								else
								{
								print_action_success("Please Check your End Date Field,it may be past", "pendings.php");
								exit();
								}	
								}
								else
								{
								print_action_success("Please select checkbox OR  end date field empty", "pendings.php");
								exit();
								}
                            }

                           //all update pending
							if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'all_update')
							{  
							   //allpending 
							   if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'allcoin')
							   {
							   
							     $form_input = '<input type="hidden" name="subcmd" value="allcoin_update" />
								                <input type="hidden" name="user_id" value="'.$ilance->GPC['user_id'].'" />
												<input type="hidden" name="consignid" value="'.$ilance->GPC['consignid'].'" />';
								//date function
								$daylist = '';
								$monthlist = '';
								$yearlist = '';
								$daylist .='<select name="day" id="day"><option value="">DATE</option>';
								
								$day = date('d');
								for($i=1; $i<=31; $i++)
								if($day == $i)
								$daylist .= "<option value='$i' selected=selected>$i</option>";
								else
								$daylist .= "<option value='$i'>$i</option>";
								
								$daylist .='</select>';
								
								$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
								
								$month = date('m');
								for($j=1; $j<=12; $j++)
								if($month == $j)
								$monthlist .= "<option value='$j' selected=selected>$j</option>";
								else
								$monthlist .= "<option value='$j'>$j</option>";
								
								
								$monthlist .= '</select>';
								
								$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
								
								$year = date('Y');;
								for($k=date("Y"); $k<=date("Y")+5; $k++)
								if($year == $k)
								$yearlist .= "<option value='$k' selected=selected>$k</option>";
								else
								$yearlist .= "<option value='$k'>$k</option>";
								
								$yearlist .='</select>';
								
								// site id
								$site_id = '<select name="site_id" id="site_id" >
								<option value="0" selected="selected">Listed in GC</option>';
					  
								$sqlcat_siteid = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "affiliate_listing
								");
								 if ($ilance->db->num_rows($sqlcat_siteid) > 0)
								 {         
									
										while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
										{
													
													
											$site_id.='<option value="'.$rescat_sid['id'].'" >'.$rescat_sid['site_name'].'</option>';
																	
													
										}
										
								}
							
								$site_id.='</select>';
							   }	
							   //single pending
							   if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'onecoin')
							   {
							   
							    $form_input = '<input type="hidden" name="subcmd" value="onecoin_update" />
								                <input type="hidden" name="coin_id" value="'.$ilance->GPC['coin_id'].'" />
												';
								//date function
								$daylist = '';
								$monthlist = '';
								$yearlist = '';
								$daylist .='<select name="day" id="day"><option value="">DATE</option>';
								
								$day = date('d');
								for($i=1; $i<=31; $i++)
								if($day == $i)
								$daylist .= "<option value='$i' selected=selected>$i</option>";
								else
								$daylist .= "<option value='$i'>$i</option>";
								
								$daylist .='</select>';
								
								$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
								
								$month = date('m');
								for($j=1; $j<=12; $j++)
								if($month == $j)
								$monthlist .= "<option value='$j' selected=selected>$j</option>";
								else
								$monthlist .= "<option value='$j'>$j</option>";
								
								
								$monthlist .= '</select>';
								
								$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
								
								$year = date('Y');;
								for($k=date("Y"); $k<=date("Y")+5; $k++)
								if($year == $k)
								$yearlist .= "<option value='$k' selected=selected>$k</option>";
								else
								$yearlist .= "<option value='$k'>$k</option>";
								
								$yearlist .='</select>';
								
								// site id
										$con_insert = $ilance->db->query("
												SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id= '".$ilance->GPC['coin_id']."'");
												
						  $rescat = $ilance->db->fetch_array($con_insert);
					$site_id = '<select name="site_id" id="site_id" >';
					$sqlcat_siteid = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "affiliate_listing
					");
					 if ($ilance->db->num_rows($sqlcat_siteid) > 0)
					 {         
							if($rescat['Site_Id'] == '0')
							{
							$site_id.='<option value="0" selected="selected">Listed in GC</option>';
							while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
							{                       
							 
							$site_id.='<option value="'.$rescat_sid['id'].'">'.$rescat_sid['site_name'].'</option>';	
													
										
							}
							}
							else
							{
							$site_id.='<option value="0" >Listed in GC</option>';
							while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
							{  
								if($rescat_sid['id'] == $rescat['Site_Id'])
								$site_id.='<option value="'.$rescat_sid['id'].'" selected="selected">'.$rescat_sid['site_name'].'</option>';
								else
								$site_id.='<option value="'.$rescat_sid['id'].'">'.$rescat_sid['site_name'].'</option>';	
													
										
							}
							}
							
					}
				
					$site_id.='</select>';	
							   }	
								
								$pprint_array = array('form_input','site_id','daylist','monthlist','yearlist','numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'pending_date_update.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('pending_list','pending_list_search'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
								
							}
							//search list
							if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list')
                            {
					
									$show['search_list'] = 'search_list_pend';
								    $sql2_search = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "users
									WHERE user_id = '".$ilance->GPC['filtervalue']."'
									OR    username = '".$ilance->GPC['filtervalue']."'
									OR    email    = '".$ilance->GPC['filtervalue']."'
									OR    zip_code  = '".$ilance->GPC['filtervalue']."'
							        ");
									 if ($ilance->db->num_rows($sql2_search) > 0)
									{
											
										   
										   $res_list_sec = $ilance->db->fetch_array($sql2_search);
											
									}
									
									$con_listing = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "coins 
							WHERE user_id = '".$res_list_sec['user_id']."'
							AND coin_listed = 'c'
							AND (End_Date = '0000-00-00' OR pending = '1')	
							AND project_id  = '0'
							AND status = '0'
							GROUP BY consignid 
							");
							            $numbers = (int)$ilance->db->num_rows($con_listing);
							            if($ilance->db->num_rows($con_listing) > 0)
										{
										$row_con_search = 0;
										while($row_list = $ilance->db->fetch_array($con_listing))
										{
											
											$con_listing_co = $ilance->db->query("
													SELECT COUNT(*) AS total
													FROM " . DB_PREFIX . "coins 
													WHERE user_id ='".$row_list['user_id']."'
													AND consignid = '".$row_list['consignid']."'
													AND coin_listed = 'c'
													AND (End_Date = '0000-00-00' OR pending = '1')	
													AND project_id  = '0'
													AND status = '0'
														 ");
													 
										 while($row_list_co = $ilance->db->fetch_array($con_listing_co))
										{
								        $total_value = $row_list_co['total'];
										}
								 									
										$row_list['username'] = fetch_user('username', $row_list['user_id']);
										
										$row_list['posted']    = $total_value;
										
									    $row_list['View'] = '<span style="cursor:pointer;" onclick="checkpending('.$row_list['user_id'].','.$row_list['consignid'].');">Click</span>'; 
										
										$row_list['return_con'] = '<span class="blue"><a href="pendings.php?cmd=list_return_user&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'&type=all_coin_return">Return</a></span>';
										
										
										$row_list['form_action'] = '<form method="post" action="pendings.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				<input type="hidden" name="cmd" value="insert_return_user" />
				<input type="hidden" name="return" value="pendings.php" />';
										
										$pending_list_search[] = $row_list;
										$row_con_search++;
										
										}
										
						                }
										
										else
										{				
										$show['no'] = 'list_search';
								     	}
					 
					       } 	
						   
						   //list for pending
						   
						   
						   
							$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
							
							 $scriptpageprevnext = $ilpage['pendings']. '?cmd=listing';
							 
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
							FROM " . DB_PREFIX . "coins 
							WHERE coin_listed = 'c'
							AND (End_Date = '0000-00-00' OR pending = '1')							
							AND project_id  = '0'
							AND status = '0'
							GROUP BY consignid asc LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," .$ilconfig['globalfilters_maxrowsdisplaysubscribers']."");
							
							           // $number = (int)$ilance->db->num_rows($con_listing);
							            if($ilance->db->num_rows($con_listing) > 0)
										{
										$row_con_list = 0;
										
										 $con_listing1 = $ilance->db->query("
														SELECT *
														FROM " . DB_PREFIX . "coins 
														WHERE coin_listed = 'c'
														AND (End_Date = '0000-00-00' OR pending = '1')							
														AND project_id  = '0'
														AND status = '0'
														GROUP BY consignid ");
														
						               $number = (int)$ilance->db->num_rows($con_listing1);
										while($row_list = $ilance->db->fetch_array($con_listing))
										{
											
											$con_listing_co = $ilance->db->query("
													SELECT COUNT(*) AS total
													FROM " . DB_PREFIX . "coins 
													WHERE user_id ='".$row_list['user_id']."'
													AND consignid = '".$row_list['consignid']."'
													AND coin_listed = 'c'
													AND (End_Date = '0000-00-00' OR pending = '1')
													AND project_id  = '0'
													AND status = '0'
														 ");
													 
										 while($row_list_co = $ilance->db->fetch_array($con_listing_co))
										{
								        $total_value = $row_list_co['total'];
										}
								 									
										$row_list['username'] = fetch_user('username', $row_list['user_id']);
										
										$row_list['posted']    = $total_value;
										
									    $row_list['View'] = '<span style="cursor:pointer;" onclick="checkpending('.$row_list['user_id'].','.$row_list['consignid'].');">Click</span>'; 
										
										$row_list['return_con'] = '<span class="blue"><a href="pendings.php?cmd=list_return_user&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'&type=all_coin_return">Return</a></span>';
										
										
										$row_list['form_action'] = '<form method="post" action="pendings.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				<input type="hidden" name="cmd" value="insert_return_user" />
				<input type="hidden" name="return" value="pendings.php" />';
										
										$pending_list[] = $row_list;
										$row_con_list++;
										
										}
										
										/*while($row_list = $ilance->db->fetch_array($con_listing))
										{	
												
										$date_coin = explode('-',$row_list['End_Date']);	
									    $month_name = date( 'M', mktime(0, 0, 0, $date_coin[1]) );	
										//coin_id, user_id, pcgs, Title, Description, Grading_Service, Grade, Quantity, Max_Quantity_Purchase, Certification_No, Condition_Attribute, Cac, Star, Plus, Coin_Series, Pedigee, Site_Id, Minimum_bid, Reserve_Price, Buy_it_now, End_Date, Alternate_inventory_No, Category, Other_information, consignid, coin_listed, Service_Level, final_fee_percentage, final_fee_min, listing_fee, referal_id, project_id, status, export
										$row_list['coin_id'] = $row_list['coin_id'];
										$row_list['pcgs'] = $row_list['pcgs']; 
										$row_list['Description'] = $row_list['Description']; 
										$row_list['Minimum_bid'] = $row_list['Minimum_bid']; 
										$row_list['date_sent'] = $date_coin[1].'-'.$month_name.'-'.$date_coin[0]; 
										$row_list['Reserve_Price'] = $row_list['Reserve_Price'];
										$row_list['Buy_it_now'] = $row_list['Buy_it_now'];  
										$row_list['edit'] = 'Edit';
										$pending_list[] = $row_list;
										$row_con_list++;
										
										}*/
										
						                }
										
										else
										{				
										$show['no'] = 'pending_list';
								     	}
	$listing_pagnation = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);									
										
	$pprint_array = array('numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','listing_pagnation');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'pending_list.html', 2);
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

    function pending_set_end_date($coin_list,$new_end_date,$site_id=0)
	{
	global $ilconfig,$ilance;
	
	foreach($coin_list as $selected_coin)
	{
	echo $sqlo="UPDATE " . DB_PREFIX . "coins SET site_id='".$site_id."' where coin_id='".$selected_coin."'";
	$ilance->db->query($sqlo);
	}
	//get all coins from the end date theat has been selected
	$query=$ilance->db->query("select c.coin_id,c.pcgs,cd.denomination_sort,cs.coin_series_sort,cc.coin_detail_year from 
		" . DB_PREFIX . "coins c, 
		" . DB_PREFIX . "catalog_coin cc, 
		" . DB_PREFIX . "catalog_second_level cs, 
		" . DB_PREFIX . "catalog_toplevel cd 
		where (date(c.End_Date)='".$new_end_date."' or c.coin_id in(".implode(",",$coin_list).")) and
		c.coin_listed = 'c' and
		c.pcgs=cc.PCGS and
		cc.coin_series_unique_no=cs.coin_series_unique_no and
		cc.coin_series_denomination_no=cd.denomination_unique_no
		group by c.coin_id
		order by cd.denomination_sort,
		cs.coin_series_sort,
		cc.coin_detail_year");
		$count_coins_ending_new_end_date=$ilance->db->num_rows($query);
		
		list($y,$m,$d)=explode('-',$new_end_date);
		list($start_h,$start_i,$start_s)=explode(':',$ilconfig['projectstarttime']);
		list($end_h,$end_i,$end_s)=explode(':',$ilconfig['projectendtime']);
		
		$config_start_time =  mktime($start_h,$start_i,$start_s,$m,$d,$y);
		$config_end_time =  mktime($end_h,$end_i,$end_s,$m,$d,$y);
		//in seconds
		$total_auction_window = abs($config_end_time-$config_start_time);
		$each_auction_gap=$total_auction_window/$count_coins_ending_new_end_date;
		
		
		
		$i=0;
		
		if($count_coins_ending_new_end_date>0)
		{
		while($coin=$ilance->db->fetch_array($query))
		{
		$end_date_increment_stamp=$config_start_time+($i*$each_auction_gap);
		$end_date_increment=date("Y-m-d H:i:s",$end_date_increment_stamp);
		
		$update=$ilance->db->query("UPDATE " . DB_PREFIX . "coins SET End_Date = '".$end_date_increment."', pending  = '0' where coin_id='".$coin['coin_id']."'");
		
		$i++;
		
		
		}
		if($i>0)
		{
			return true;
		}
		
		}else
		{
			return false;
		}
	
	}
	
?>