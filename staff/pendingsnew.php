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

				

				// $consignid  = $ilance->GPC['consignid'];

				 $coin_id     = $ilance->GPC['coin_id'];

				 

			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers order by basefee asc", 0, null, __FILE__, __LINE__);

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

									AND coin_listed = 'c'

									AND (End_Date = '0000-00-00' OR pending = '1')	

									AND project_id  = '0'

									AND status = '0'


									", 0, null, __FILE__, __LINE__);
							
							
						
						
				while($row_coin_list = $ilance->db->fetch_array($coin_listing_co))
					
				{				 

			     $coinid_list[]=$row_coin_list['coin_id'];	

			     }
				 $coinid=implode(",",$coinid_list);


			

			

			 //list of return
			 
			 /* vijay work for pagination starts on 14.11.14 */
					$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];

					$scriptpageprevnext = 'pendingsnew.php?cmd=list_return_user';

					if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
					{
					$ilance->GPC['page'] = 1;
					}
					else
					{
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
					}
					
					
				$con_listing1 = $ilance->db->query("SELECT charges,shipping_fees,user_id,coin_id,consign_id,return_date FROM " . DB_PREFIX . "coin_return
				LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
				");		   
				if($ilance->db->num_rows($con_listing1) > 0)
				{
				 /* vijay work for pagination ends on 14.11.14 */
				$con_listing = $ilance->db->query("

										SELECT *

										FROM " . DB_PREFIX . "coin_return 


										", 0, null, __FILE__, __LINE__);

							            $newnumber = (int)$ilance->db->num_rows($con_listing);

							            if($ilance->db->num_rows($con_listing1) > 0)

										{

										$row_con_list = 0;

										while($row_list = $ilance->db->fetch_array($con_listing1))

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
				}
				
				else

				{				

				$show['no'] = 'return_list';

				}
				
				
				$return_pagination = print_pagnation($newnumber, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
				
				 /* vijay work for pagination ends on 14.11.14 */
				
				 if (isset($ilance->GPC['type']))
				 $show['return']='all_coin';
				 else
				 $show['return']='';
				 
				 $form_action='pendingsnew.php';

     $pprint_array = array('newnumber','html','coin_id','consignid','seller_name','user_id','seller_id','seller_name','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action','coinid','return_pagination');

			

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

									AND coin_listed = 'c'

									AND (End_Date = '0000-00-00' OR pending = '1')	

									AND project_id  = '0'

									AND status = '0'


									", 0, null, __FILE__, __LINE__);
							
							
						
						
				while($row_coin_list = $ilance->db->fetch_array($coin_listing_co))
					
				{				 

					$con  = $ilance->db->query("SELECT basefee

					FROM " . DB_PREFIX . "shippers WHERE shipperid='".$ilance->GPC['shipper']."'", 0, null, __FILE__, __LINE__);

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

					FROM " . DB_PREFIX . "invoices ORDER BY invoiceid DESC", 0, null, __FILE__, __LINE__);

					$roww = $ilance->db->fetch_array($conw);

					

					$invocie_id = $roww['invoiceid'];				    

					

					

					 //insert cancel value 

					 $con_insert = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_return

					 (coin_id, consign_id, user_id, shipper_id, shipping_fees, charges, return_date, invoiceid, notes, return_opt)

					 VALUES (

						   '".$row_coin_list['coin_id']."',

						   '".$row_coin_list['consignid']."',

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

					$con_insert1 = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coins_retruned SELECT * FROM ilance_coins where coin_id='".$row_coin_list['coin_id']."'");

					

					$con_insert2 = $ilance->db->query("delete FROM " . DB_PREFIX . "coins where coin_id='".$row_coin_list['coin_id']."'");
					
					
					$con_delete = $ilance->db->query("delete FROM " . DB_PREFIX . "projects where project_id='".$row_coin_list['coin_id']."'");
					
					

				}	

					

					print_action_success('Your Coin Details Returned successfully', 'pendingsnew.php');

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
													AND coin_listed = 'c'
													AND (End_Date = '0000-00-00' OR pending = '1')
													AND project_id  = '0'
													AND status = '0'

													", 0, null, __FILE__, __LINE__);
													 
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

								$restotal=$ilance->db->query("select * from " . DB_PREFIX . "coins WHERE date(End_Date) = '".$date_coin."'", 0, null, __FILE__, __LINE__);

			

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
							if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'autobuild_submit')
							{
							
							     if($ilance->GPC['checked_users'] != '' && $ilance->GPC['datevalnew'] != '')
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
												$checked_users = $ilance->GPC['checked_users'];
$SQL= get_autobuild_sql(implode(",",$checked_users),$ilance->GPC['autobuild_option']);
$result=$ilance->db->query($SQL);
$num_rows=$ilance->db->num_rows($result);
if($ilance->GPC['total_number_autobuild']==$num_rows)
{
while($line=$ilance->db->fetch_array($result))
{
$sql="UPDATE  " . DB_PREFIX . "coins
SET  End_Date = '" . $ilance->GPC['datevalnew'] . "',
pending  = '0'
WHERE  coin_id = '".$line['coin_id']."'";
$ilance->db->query($sql);
}
}
print_action_success("End Date for pending sucessfully updated for ".$num_rows." coins", "pendingsnew.php");
exit();
									 }	
									 else
									 {
									    print_action_success("Please Check your End Date Field,it may be past", "pendingsnew.php");
							
							            exit();
									 }	
								 
								 }
								 else
								 {
								   if($ilance->GPC['incheckdate'] == '')
								   print_action_success("Please select checkbox for the users to set enddate, atleast one check box have to be selected", "pendingsnew.php");
									else if($ilance->GPC['datevalnew'] == '')
									print_action_success("Please select an end date, that field cannot be empty", "pendingsnew.php");
							       exit();
								 }
							
							
							
							}
							
							
							
							//new end date for pending through save
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
												$check_count = $ilance->GPC['incheckdate'];
												
												for($k=0;$k<count($check_count);$k++)
												{
												
														$con_insert_cointable = $ilance->db->query("
														UPDATE  " . DB_PREFIX . "coins
														SET  End_Date = '" . $ilance->GPC['datevalnew'] . "',
															 pending  = '0'
															 WHERE  coin_id = '".$check_count[$k]."'
														");
														/*
														$pcgsc = fetch_coin_consignid('pcgs',$check_count[$k]);
														
														$restotal=$ilance->db->query("select * from " . DB_PREFIX . "coins WHERE date(End_Date) = '".$ilance->GPC['datevalnew']."'");
														
														if($ilance->db->num_rows($restotal) > 0)
						
														{
														   //$my_var_in = fetch_date_time_coin($date_coin);
														   $my_var_in = fetch_date_time_coin($ilance->GPC['datevalnew'],$pcgsc,$check_count[$k]);
						
														}
														*/
												
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
								   if($ilance->GPC['incheckdate'] == '')
								   print_action_success("Please select checkbox for the users to set enddate, atleast one check box have to be selected", "pendingsnew.php");
									else if($ilance->GPC['datevalnew'] == '')
									print_action_success("Please select an end date, that field cannot be empty", "pendingsnew.php");
							       exit();
								 }
							
							
							
							}
							
								//date end update in consignor check box
							// bug id 1078 works on dec 15
								
			if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'consignor_date_upadete')
				{
							
					 if($ilance->GPC['date_consignor'] != '' && $ilance->GPC['user_check'] != '')
						{
							  $dateexplode = explode('-',$ilance->GPC['date_consignor']);
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
									
									
												 $check_count = $ilance->GPC['user_check'];
												
												for($k=0;$k<count($check_count);$k++)
												{
												
												$restotal=$ilance->db->query("select coin_id from " . DB_PREFIX . "coins 
												WHERE user_id = '".$check_count[$k]."' 
												and (End_Date = '0000-00-00' OR pending = '1')
												and coin_listed = 'c'
												and project_id  = '0'

												", 0, null, __FILE__, __LINE__);
                                                 while($coin_fetch = $ilance->db->fetch_array($restotal))
												  {
												  
												   
						                      $con_insert_cointable = $ilance->db->query("
														UPDATE  " . DB_PREFIX . "coins
														SET  End_Date = '" . $ilance->GPC['date_consignor'] . "',
															 pending  = '0'
															 WHERE  coin_id = '".$coin_fetch['coin_id']."'
														");
												   
												  }
												  
												 
												}
												
											print_action_success("End Date for consignor coins sucessfully update", "pendings.php");
										
												exit();		
									
									 }
							      else
									 {
									    print_action_success("Please Check your End Date Field,it may be past", "pendingsnew.php");
							
							            exit();
									 }
				}
							
				 else
					{
								   
					 print_action_success("Please select checkbox OR  end date field empty", "pendingsnew.php");
							
						exit();
					}
							
							
		}
							
							//date end update finished in consignor check box
							// bug id 1078 works on dec 15 finished
							
							
							
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
												SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id= '".$ilance->GPC['coin_id']."'", 0, null, __FILE__, __LINE__);
												
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

									", 0, null, __FILE__, __LINE__);
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
							GROUP BY user_id 

							", 0, null, __FILE__, __LINE__);
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
													AND coin_listed = 'c'
													AND (End_Date = '0000-00-00' OR pending = '1')	
													AND project_id  = '0'
													AND status = '0'

														 ", 0, null, __FILE__, __LINE__);
													 
										 while($row_list_co = $ilance->db->fetch_array($con_listing_co))
										{
								        $total_value = $row_list_co['total'];
										}
								 									
										$row_list['username'] = fetch_user('username', $row_list['user_id']);
										
										$row_list['posted']    = $total_value;
										
									    $row_list['View'] = '<span style="cursor:pointer;" onclick="checkpending1('.$row_list['user_id'].','.$row_list['consignid'].');">Click</span>'; 
										
										$row_list['return_con'] = '<span class="blue"><a href="pendingsnew.php?cmd=list_return_user&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'&type=all_coin_return">Return</a></span>';
										
										$row_list['form_action'] = '<form method="post" action="pendingsnew.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				<input type="hidden" name="cmd" value="insert_return_user" />
				<input type="hidden" name="return" value="pendingsnew.php" />';
										
										
										$pending_list_search[] = $row_list;
										$row_con_search++;
										
										}
										
						                }
										
										else
										{				
										$show['no'] = 'list_search';
								     	}
					 
					       } 	
						   
						   
						   
						     //dec 07 for bug id 1100 for sorting username 
						   
					
            
	       
						   
						   
      switch($ilance->GPC['orderby'])
	  {	
	  
	     case 'username':
		 
		              if($ilance->GPC['sort'] =='11' && $ilance->GPC['sort']!='')
						{
						  $usernamesort =  '<td><a href="pendingsnew.php?sort=12&orderby=username">Username</a></td>'; 
						  $emailsort =  '<td width="12%"><a href="pendingsnew.php?sort=21&orderby=email">Email</a></td>';
						 }
						 
						 else
						 {
						  $usernamesort =  '<td><a href="pendingsnew.php?sort=11&orderby=username">Username</a></td>'; 
						  $emailsort =  '<td width="12%"><a href="pendingsnew.php?sort=21&orderby=email">Email</a></td>';
						 }  
						 
						break;
						
		        case 'email':
					   if($ilance->GPC['sort'] =='21' && $ilance->GPC['sort']!='')
						{
						  $emailsort =  '<td><a href="pendingsnew.php?sort=22&orderby=email">Email</a></td>'; 
						  $usernamesort =  '<td width="12%" align="left"><a href="pendingsnew.php?sort=11&orderby=username">Username</a></td>';
						 }
						 
						 else
						 {
						  $emailsort =  '<td><a href="pendingsnew.php?sort=21&orderby=email">Email</a></td>';
						  $usernamesort =  '<td width="12%" align="left"><a href="pendingsnew.php?sort=11&orderby=username">Username</a></td>'; 
						 }  
						 
						break;	
						
						
						
							default:		
						  $usernamesort =  '<td width="12%" align="left"><a href="pendingsnew.php?sort=11&orderby=username">Username</a></td>';	
						  $emailsort =  '<td width="12%"><a href="pendingsnew.php?sort=21&orderby=email">Email</a></td>';								
                                          
			
	  }	
	  
	  
      $orderby_array = array('21'=>'ORDER BY u.email DESC',
					         '22'=>'ORDER BY u.email ASC',
					         '11'=>'ORDER BY u.username DESC',
					         '12'=>'ORDER BY u.username ASC'
					        );
					 
	if($ilance->GPC['sort']!='')				   
	  $orderby = $orderby_array[$ilance->GPC['sort']];
	else
	  $orderby ="ORDER BY u.username ASC";   
	
	 //list for pending

                     $con_listing = $ilance->db->query("SELECT c.user_id,c.consignid,u.username,u.email,u.ir_managed,COUNT(*) AS total
														FROM " . DB_PREFIX . "coins c," . DB_PREFIX . "users u
														WHERE c.coin_listed = 'c'
														AND (c.End_Date = '0000-00-00' OR c.pending = '1')							
														AND c.project_id  = '0'
														AND c.status = '0'
														AND c.user_id = u.user_id
														GROUP BY c.user_id 
														$orderby  

														", 0, null, __FILE__, __LINE__);
												
				      $number = (int)$ilance->db->num_rows($con_listing);
					  if($ilance->db->num_rows($con_listing) > 0)
					  {
							$row_con_list = 0;
					        while($row_list = $ilance->db->fetch_array($con_listing))
							{
							     //IR Managed Pendings
								 if($row_list['ir_managed']=='1')
								 {
										$row_list['username'] = $row_list['username'];
										$row_list['posted']    = $row_list['total'];
									    $row_list['View'] = '<span style="cursor:pointer;" onclick="checkpending3('.$row_list['user_id'].','.$row_list['consignid'].');">Click</span>'; 
										$row_list['return_con'] = '<span class="blue"><a href="pendingsnew.php?cmd=list_return_user&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'&type=all_coin_return">Return</a></span>';
										$row_list['form_action'] = '<form method="post" action="pendingsnew.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				<input type="hidden" name="cmd" value="insert_return_user" />
				<input type="hidden" name="return" value="pendingsnew.php" />';
										$pending_list_managed[] = $row_list;
										$row_con_list++;
								 }
								 //IR Non-Managed Pendings
								 else 
								 {
										$row_list['username'] = $row_list['username'];
										$row_list['posted']    = $row_list['total'];
									    $row_list['View'] = '<span style="cursor:pointer;" onclick="checkpending2('.$row_list['user_id'].','.$row_list['consignid'].');">Click</span>'; 
										$row_list['return_con'] = '<span class="blue"><a href="pendingsnew.php?cmd=list_return_user&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'&type=all_coin_return">Return</a></span>';
										$row_list['form_action'] = '<form method="post" action="pendingsnew.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				<input type="hidden" name="cmd" value="insert_return_user" />
				<input type="hidden" name="return" value="pendingsnew.php" />';
										$pending_list_unmanaged[] = $row_list;
										$row_con_list++;
								 }
								 //All List of Pendings
										$row_list['username'] =  $row_list['username'];
										$row_list['email'] =  $row_list['email'];
										$row_list['posted']    = $row_list['total'];
									    $row_list['View'] = '<span style="cursor:pointer;" onclick="checkpending1('.$row_list['user_id'].','.$row_list['consignid'].');">Click</span>'; 
										$row_list['return_con'] = '<span class="blue"><a href="pendingsnew.php?cmd=list_return_user&user_id='.$row_list['user_id'].'&consignid='.$row_list['consignid'].'&type=all_coin_return">Return</a></span>';
										$row_list['form_action'] = '<form method="post" action="pendingsnew.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				<input type="hidden" name="cmd" value="insert_return_user" />
				<input type="hidden" name="return" value="pendingsnew.php" />';
										$row_list['checkbox']='<input type="checkbox" name="user_check[]"  id="user_consignor" value="'.$row_list['user_id'].'"  />';
										$pending_list[] = $row_list;
										$row_con_list++;
							}
						 }
						else
						{				
							$show['no'] = 'pending_list';
						}
						
				//Search list for IR Non-Managed Pendings
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list_unmanaged')
                 {
						$show['search_list1'] = 'search_list_pend_unmanaged';
					    $sql2_search2 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users
															WHERE user_id = '".$ilance->GPC['filtervalue']."'
															OR    username = '".$ilance->GPC['filtervalue']."'
															OR    email    = '".$ilance->GPC['filtervalue']."'
															OR    zip_code  = '".$ilance->GPC['filtervalue']."'

															", 0, null, __FILE__, __LINE__);
						 $res_list_sec2 = $ilance->db->fetch_array($sql2_search2);
								
						 $con_listing2 = $ilance->db->query("SELECT c.user_id,c.consignid,u.username,u.email,u.ir_managed,COUNT(*) AS total
						                                    FROM " . DB_PREFIX . "coins c," . DB_PREFIX . "users u 
															WHERE u.user_id = '".$res_list_sec2['user_id']."'
															AND coin_listed = 'c'
															AND (c.End_Date = '0000-00-00' OR c.pending = '1')	
															AND c.project_id  = '0'
															AND u.ir_managed = '0'
															AND c.status = '0'
															AND c.user_id = u.user_id
															GROUP BY u.user_id 

															", 0, null, __FILE__, __LINE__);
						$numbers = (int)$ilance->db->num_rows($con_listing2);
					    if($ilance->db->num_rows($con_listing2) > 0)
						{
							$row_con_search = 0;
							while($row_list2 = $ilance->db->fetch_array($con_listing2))
							{
							    $row_list2['username'] = $row_list2['username'];
								$row_list2['posted']    = $row_list2['total'];
								$row_list2['View'] = '<span style="cursor:pointer;" onclick="checkpending2('.$row_list2['user_id'].','.$row_list2['consignid'].');">Click</span>'; 
								$row_list2['return_con'] = '<span class="blue"><a href="pendingsnew.php?cmd=list_return_user&user_id='.$row_list2['user_id'].'&consignid='.$row_list2['consignid'].'&type=all_coin_return">Return</a></span>';
								$row_list2['form_action'] = '<form method="post" action="pendingsnew.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				<input type="hidden" name="cmd" value="insert_return_user" />
				<input type="hidden" name="return" value="pendingsnew.php" />';
								$pending_list_search_unmanage[] = $row_list2;
								$row_con_search++;
							}
						}
						else
						{				
							$show['no'] = 'list_search';
						}
			  } 	
						   
		
			 //Search list for IR Managed Pendings
			 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list_managed')
             {
							$show['search_list1'] = 'search_list_pend_managed';
						    $sql2_search2 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users
																WHERE user_id = '".$ilance->GPC['filtervalue']."'
																OR    username = '".$ilance->GPC['filtervalue']."'
																OR    email    = '".$ilance->GPC['filtervalue']."'
																OR    zip_code  = '".$ilance->GPC['filtervalue']."'

																", 0, null, __FILE__, __LINE__);
									
						    $res_list_sec2 = $ilance->db->fetch_array($sql2_search2);
							$con_listing2 = $ilance->db->query("SELECT c.user_id,c.consignid,u.username,u.email,u.ir_managed,COUNT(*) AS total
																FROM " . DB_PREFIX . "coins c," . DB_PREFIX . "users u 
																WHERE u.user_id = '".$res_list_sec2['user_id']."'
																AND coin_listed = 'c'
																AND (c.End_Date = '0000-00-00' OR c.pending = '1')	
																AND c.project_id  = '0'
																AND u.ir_managed = '1'
																AND c.status = '0'
																AND c.user_id = u.user_id
																GROUP BY u.user_id 

																", 0, null, __FILE__, __LINE__);
						 $numbers = (int)$ilance->db->num_rows($con_listing2);
					     if($ilance->db->num_rows($con_listing2) > 0)
						 {
							  $row_con_search = 0;
							  while($row_list2 = $ilance->db->fetch_array($con_listing2))
							  {
								$row_list2['username'] = $row_list2['username'];
								$row_list2['posted']    = $row_list2['total'];
							    $row_list2['View'] = '<span style="cursor:pointer;" onclick="checkpending3('.$row_list2['user_id'].','.$row_list2['consignid'].');">Click</span>'; 
								$row_list2['return_con'] = '<span class="blue"><a href="pendingsnew.php?cmd=list_return_user&user_id='.$row_list2['user_id'].'&consignid='.$row_list2['consignid'].'&type=all_coin_return">Return</a></span>';
								$row_list2['form_action'] = '<form method="post" action="pendingsnew.php" accept-charset="UTF-8" name="ilform" style="margin: 0px;">
				<input type="hidden" name="cmd" value="insert_return_user" />
				<input type="hidden" name="return" value="pendingsnew.php" />';
								$pending_list_search_manage[] = $row_list2;
								$row_con_search++;
							  }
						 }
						 else
						 {				
							$show['no'] = 'list_search';
						 }
			    } 	
						   
								
	$pprint_array = array('numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','usernamesort','emailsort');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'pending_listnew.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('pending_list','pending_list_search','pending_list_unmanaged','pending_list_search_unmanage','pending_list_search_manage','pending_list_managed'));
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

function get_autobuild_sql($users,$autobuild_option)
{
global $ilance;

switch($autobuild_option)
		{
		case 'autobuild_all':
		$SQL="	SELECT *
					FROM " . DB_PREFIX . "coins
					WHERE user_id in(".$users.")
					AND coin_listed = 'c' AND (End_Date = '0000-00-00' OR pending = '1') AND project_id  = '0' AND status = '0'
					group by user_id,Title,pcgs,Grade,nocoin order by pcgs desc,Grade desc;
					";

		break;
		case 'autobuild_imaged':
		$SQL="	SELECT c.user_id,c.coin_id 
					FROM " . DB_PREFIX . "coins c
					join " . DB_PREFIX . "attachment a on a.project_id=c.coin_id
					WHERE c.user_id in(".$users.")
					AND c.coin_listed = 'c' AND (c.End_Date = '0000-00-00' OR c.pending = '1') AND c.project_id  = '0' AND c.status = '0'
					group by c.user_id,c.Title,c.pcgs,c.Grade,c.nocoin order by c.pcgs desc,c.Grade desc;
					";
		break;
		case 'autobuild_buynow':
		$SQL="	SELECT *
					FROM " . DB_PREFIX . "coins c
					WHERE c.user_id in(".$users.")
					AND c.coin_listed = 'c' AND (c.End_Date = '0000-00-00' OR c.pending = '1') AND c.project_id  = '0' AND c.status = '0'
					AND c.Buy_it_now!='' AND c.Minimum_bid ='' 
					group by c.user_id,c.Title,c.pcgs,c.Grade,c.nocoin order by c.pcgs desc,c.Grade desc;
					";
		break;

		}
		
return $SQL;		
}

?>