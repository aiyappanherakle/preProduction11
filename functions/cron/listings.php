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
	'flashfix',
	'jquery',
     'modal',
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
require_once('../ebay/AddItem/AddItem.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
   
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'holder_area')
   {
   
      if($ilance->GPC['val'] == '' && $ilance->GPC['date_down'] =='')
	  {
	   print_action_failed('sorry atleast select one checkbox or dropdown val', $ilpage['listings']);
										exit();
	  }
	  else
	  {
		  if($ilance->GPC['val'] != '' && $ilance->GPC['date_down'] !='')
		  {
		  
		  print_action_failed('sorry your selected both checkbox and dropdown check it please and select atleast one', $ilpage['listings']);
										exit();
		  }
	  
	      else
	      {
	      
	         if($ilance->GPC['val'] == '')
		     {
		    //end date bulk
			$insert_value = $ilance->db->query("
			SELECT  *
			FROM " . DB_PREFIX . "coins 
			WHERE coin_listed = 'c'
			AND date(End_Date)  = '".$ilance->GPC['date_down']."'
			AND project_id = '0'
			AND Site_Id = '0'
			AND pending !='1'
			", 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($insert_value) > 0)
			{
				while($row_value = $ilance->db->fetch_array($insert_value))
				{
				
				// Murugan Changes On Dec 18 For Category questions
				$custom=array();
				
				
				$custom['1']['Cac'] = $row_value['Cac'];
				$custom['2']['Star'] = $row_value['Star'];
				$custom['3']['Plus'] = $row_value['Plus'];
				$custom['4']['Grading_Service'][] = $row_value['Grading_Service'];
				$custom['5']['Grade'][] = $row_value['Grade'];											
				$custom['6']['Condition_Attribute'][] = $row_value['Condition_Attribute'];
				$custom['7']['Coin_Series'] = $row_value['Coin_Series'];
				$custom['8']['Pedigee'][] = $row_value['Pedigee'];
				$custom['9']['Alternate_inventory_No'] = $row_value['Alternate_inventory_No'];
				$custom['10']['Certification_No'] = $row_value['Certification_No'];				
				$custom['11']['Other_information'][] = $row_value['Other_information'];
				$custom['12']['Variety'] = $row_value['Variety'];
				$kill=array('custom'=>$custom);
				// Murugan Changes On Dec 18 For Category questions End 
				$pro_count = $ilance->db->query("SELECT COUNT(*) AS project_count FROM " . DB_PREFIX . "projects", 0, null, __FILE__, __LINE__);
				$pro_count_res = $ilance->db->fetch_array($pro_count);	
				$count_set = $pro_count_res['project_count'];		 
				$insert_count = $count_set+1;
				$dataexplode = explode('-', $row_value['End_Date']);
				$date_coin = $dataexplode['0'] .'-'.$dataexplode['1'].'-'.$dataexplode['2'];
				//insert project	
				if($row_value['Buy_it_now'] == '' || $row_value['Buy_it_now'] == '0')
				{
					$autiontype = 'regular';
					$amo_my = $row_value['Minimum_bid'];
					$buynow = '0';
				}
				else if($row_value['Minimum_bid'] == '' || $row_value['Minimum_bid'] == '0')
				{
					$autiontype = 'fixed';
					$amo_my = $row_value['Buy_it_now'];
					$buynow = '1';
				}
				else
				{
					$autiontype = 'regular';
					$amo_my = $row_value['Minimum_bid'];
					$buynow = '1';
				}
				
				
				if($row_value['Reserve_Price'] == '' || $row_value['Reserve_Price'] == '0')
				{
				$resx_pr = '0.00';
				$resx_pr1 = '0';
				
				}
				else
				{
				$resx_pr =  $row_value['Reserve_Price'];
				$resx_pr1 = '1';
				}
				
				$sql2="SELECT Orderno,coin_series_denomination_no,coin_series_unique_no,coin_detail_year,coin_detail_mintmark FROM " . DB_PREFIX . "catalog_coin WHERE PCGS = '" . $row_value['pcgs'] . "'";
				$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($res2)>0)
				{





					while($line2=$ilance->db->fetch_array($res2))
					{
					$order_nos = $line2['Orderno']; 
					$denom_series = $line2['coin_series_denomination_no'];
					$denom_uniqueno = $line2['coin_series_unique_no'];
					$coin_detail_res=$line2;
					}
				}

				// murugan changes on Jan 19
				$checkin = $ilance->db->query("SELECT project_id,date_end FROM " . DB_PREFIX . "projects WHERE project_id = '".$row_value['coin_id']."'", 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($checkin) > 0)
				{
				$relistend = $ilance->db->fetch_array($checkin);
											/**/
						$ilance->db->query("update " . DB_PREFIX . "coin_relist set  
											enddate ='".$date_coin."',
											startbydate ='".DATETIME24H."'
											where user_id='".$row_value['user_id']."' and coin_id='".$row_value['coin_id']."' and date(actual_end_date)=date('".$relistend['date_end']."')", 0, null, __FILE__, __LINE__);
										if($ilance->db->affected_rows() == 0)
										{
											$ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_relist
															(id, coin_id, enddate, startbydate, user_id, actual_end_date, filtered_auctiontype)
															VALUES (
															NULL,
															'".$row_value['coin_id']."',
															'".$date_coin."',
															'".DATETIME24H."',
															'".$row_value['user_id']."',
															'".$relistend['date_end']."',
															'".$autiontype."'
															)", 0, null, __FILE__, __LINE__);
										}																			
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects
												   WHERE project_id = '" . intval($row_value['coin_id']) . "'", 0, null, __FILE__, __LINE__);
				}
				
				/*Tamil for bug 2489 * Starts */
				$coin_detail_sql=$ilance->db->query("SELECT coin_detail_year,coin_detail_mintmark FROM " . DB_PREFIX . "catalog_coin
																		   WHERE PCGS = '" . $row_value['pcgs'] . "'", 0, null, __FILE__, __LINE__);
				$coin_detail_res = $ilance->db->fetch_array($coin_detail_sql);
				
				/*Tamil for bug 3360 * Starts */
				
				$ilance->db->query("INSERT INTO " . DB_PREFIX . "projects
											(id, project_id, cid, description, date_added, date_starts, date_end, user_id, visible, project_title, status, project_details, project_type, project_state, buynow, buynow_price, startprice, reserve_price, buynow_qty, buynow_purchases,filtered_auctiontype,currentprice,max_qty,alt_no,filter_escrow,reserve,Orderno,coin_series_denomination_no,coin_series_unique_no,norder,bold,highlite,featured,pcgs,Grade,Grading_Service,coin_detail_year,mintmark,Cac,Variety)
											VALUES (
											NULL,
											'".$row_value['coin_id']."',
											'".$row_value['Category']."',
											'".$ilance->db->escape_string($row_value['Description'])."',
											'".DATETIME24H."',
											'".DATETIME24H."',
											'".$date_coin."',
											'".$row_value['user_id']."',
											'1',
											'".$ilance->db->escape_string($row_value['Title'])."',
											'open',
											'public',
											'forward',
											'product',
											'".$buynow."',
											'".$row_value['Buy_it_now']."',
											'".$row_value['Minimum_bid']."',
											'".$resx_pr."',
											'".$row_value['Quantity']."',
											'".$row_value['Max_Quantity_Purchase']."',
											'".$autiontype."',
											'".$amo_my."',
											'".$row_value['Max_Quantity_Purchase']."',
											'".$row_value['Alternate_inventory_NO']."',
											'1',
											'".$resx_pr1."',
											'".$order_nos."',
											'".$denom_series."',
											'".$denom_uniqueno."',
											'".$row_value['norder']."',
											'".$row_value['bold']."',
											'".$row_value['highlite']."',
											'".$row_value['featured']."',
											'".$row_value['pcgs']."',
											'".$row_value['Grade']."',
											'".$row_value['Grading_Service']."',
											'".$coin_detail_res['coin_detail_year']."',
											'".$coin_detail_res['coin_detail_mintmark']."',
											'".$row_value['Cac']."',
											'".$row_value['Variety']."'											
											)", 0, null, __FILE__, __LINE__);
				/*Tamil for bug 3360 * Ends */
				
				/*Tamil for bug 2489 * Starts */
				
										// murugan added here on jan 20	
								$ilance->db->query("INSERT INTO " . DB_PREFIX . "projects_log
																		(id, project_id, cid, description, date_added, date_starts, date_end, user_id, visible, project_title, status, project_details, project_type, project_state, buynow, buynow_price, startprice, reserve_price, buynow_qty, buynow_purchases,filtered_auctiontype,currentprice,max_qty,alt_no,filter_escrow,reserve,Orderno,coin_series_denomination_no,coin_series_unique_no,norder,bold,highlite,featured)
																		VALUES (
																		NULL,
																		'".$row_value['coin_id']."',
																		'".$row_value['Category']."',
																		'".$ilance->db->escape_string($row_value['Description'])."',
																		'".DATETIME24H."',
																		'".DATETIME24H."',
																		'".$date_coin."',
																		'".$row_value['user_id']."',
																		'1',
																		'".$ilance->db->escape_string($row_value['Title'])."',
																		'open',
																		'public',
																		'forward',
																		'product',
																		'".$buynow."',
																		'".$row_value['Buy_it_now']."',
																		'".$row_value['Minimum_bid']."',
																		'".$resx_pr."',
																		'".$row_value['Quantity']."',
																		'".$row_value['Max_Quantity_Purchase']."',
																		'".$autiontype."',
																		'".$amo_my."',
																		'".$row_value['Max_Quantity_Purchase']."',
																		'".$row_value['Alternate_inventory_NO']."',
																		'1',
																		'".$resx_pr1."',
																		'".$order_nos."',
																		'".$denom_series."',
																		'".$denom_uniqueno."',
																		'".$row_value['norder']."',
																		'".$row_value['bold']."',
																		'".$row_value['highlite']."',
																		'".$row_value['featured']."'
																		)", 0, null, __FILE__, __LINE__);
					
					//caterory count
					//$cat_count = build_category_count($row_value['Category'], 'add', "insert_product_auction(): adding increment count category id $cid");											
						
					//category count
                                        if($denom_uniqueno>0)
					$ilance->db->query("update " . DB_PREFIX . "catalog_toplevel set auction_count=auction_count+1 where denomination_unique_no=".$denom_uniqueno, 0, null, __FILE__, __LINE__);
                                        if($denom_series>0)
                                         $ilance->db->query("update " . DB_PREFIX . "catalog_second_level set auction_count=auction_count+1 where coin_series_unique_no=".$denom_series, 0, null, __FILE__, __LINE__);
					
					
					//update coin table						
					$con_insert_cointable = $ilance->db->query("
					UPDATE  " . DB_PREFIX . "coins
					SET  project_id = '".$row_value['coin_id']."',
						 status = '0'
					WHERE coin_id 	  =  '" . $row_value['coin_id'] . "'
					AND project_id = '0'
					");
					
					// murugan changes on may 24 for relisting not calculate the listing fee
					if($row_value['relist_count'] == 0)
					{
					//invoice and listing fees
					$my_var_in = insertion_fee_transaction_new($row_value['listing_fee'], 'product', $amo_my, $row_value['coin_id'], $row_value['user_id']);                     }
					else
					{
						// here relist fees will added
					}                                   
					
				}
				print_action_success('Item to List on Great Collection', $ilpage['listings']);
								exit();
			} 
			
								  
		 
		    }
		     else
		     {
		      //single checkbox and others        
		   
	 
			  $check_value = $ilance->GPC['val'];
			  $drop_value = $ilance->GPC['site_id'];
			  //get id from site name 
			  $con_listing = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "affiliate_listing 
									WHERE id = '".abs($drop_value)."'", 0, null, __FILE__, __LINE__);
			  $row_list = $ilance->db->fetch_array($con_listing);
			 
			  
			 if($drop_value == '0')
			 {
			            for($g=0;$g<count($check_value); $g++)
						{
								//val array set for checkbox
								$checkcount_gc[] = $check_value[$g];
								$con_listifng_gc = $ilance->db->query("
								SELECT  *
								FROM " . DB_PREFIX . "coins 
								WHERE coin_listed = 'c'
								AND coin_id  = '".$check_value[$g]."'
								AND Site_Id = '0'
								", 0, null, __FILE__, __LINE__);
								if($ilance->db->num_rows($con_listifng_gc) > 0)
								{
								
									while($row_lisst_gc = $ilance->db->fetch_array($con_listifng_gc))
									{
									
									$coincount_gc[] = $row_lisst_gc['coin_id'];
									
									}
								} 
						}
						
						//count set
					    $coin_count_gc = count($coincount_gc);
						
						$check_count_gc =  count($checkcount_gc);
						
						//count check for insert project
						if($check_count_gc == $coin_count_gc)
						{
								for($r=0;$r<count($coincount_gc);$r++)
								{
										$insert_value = $ilance->db->query("
										SELECT  *
										FROM " . DB_PREFIX . "coins 
										WHERE coin_listed = 'c'
										AND coin_id  = '".$coincount_gc[$r]."'
										AND Site_Id = '0'
										", 0, null, __FILE__, __LINE__);
										if($ilance->db->num_rows($insert_value) > 0)
										{
											while($row_value = $ilance->db->fetch_array($insert_value))
											{
											// Murugan Changes On Dec 18 For Category questions
											$custom=array();
											
											
											$custom['1']['Cac'] = $row_value['Cac'];
											$custom['2']['Star'] = $row_value['Star'];
											$custom['3']['Plus'] = $row_value['Plus'];
											$custom['4']['Grading_Service'][] = $row_value['Grading_Service'];
											$custom['5']['Grade'][] = $row_value['Grade'];											
											$custom['6']['Condition_Attribute'][] = $row_value['Condition_Attribute'];
											$custom['7']['Coin_Series'] = $row_value['Coin_Series'];
											$custom['8']['Pedigee'][] = $row_value['Pedigee'];
											$custom['9']['Alternate_inventory_No'] = $row_value['Alternate_inventory_No'];
											$custom['10']['Certification_No'] = $row_value['Certification_No'];				
											$custom['11']['Other_information'][] = $row_value['Other_information'];
											$custom['12']['Variety'] = $row_value['Variety'];
											$kill=array('custom'=>$custom);
											// Murugan Changes On Dec 18 For Category questions End 
											$pro_count = $ilance->db->query("SELECT COUNT(*) AS project_count FROM " . DB_PREFIX . "projects", 0, null, __FILE__, __LINE__);
											$pro_count_res = $ilance->db->fetch_array($pro_count);	
											$count_set = $pro_count_res['project_count'];		 
											$insert_count = $count_set+1;
											$dataexplode = explode('-', $row_value['End_Date']);
											$date_coin = $dataexplode['0'] .'-'.$dataexplode['1'].'-'.$dataexplode['2'];
											//insert project	
											if($row_value['Buy_it_now'] == '' || $row_value['Buy_it_now'] == '0')
											{
												$autiontype = 'regular';
												$amo_my = $row_value['Minimum_bid'];
												$buynow = '0';
											}
											else if($row_value['Minimum_bid'] == '' || $row_value['Minimum_bid'] == '0')
											{
												$autiontype = 'fixed';
												$amo_my = $row_value['Buy_it_now'];
												$buynow = '1';
											}
											else
											{
											 	$autiontype = 'regular';
												$amo_my = $row_value['Minimum_bid'];
												$buynow = '1';
											}
											
											
											if($row_value['Reserve_Price'] == '' || $row_value['Reserve_Price'] == '0')
											{
											$resx_pr = '0.00';
											$resx_pr1 = '0';
											
											}
											else
											{
											$resx_pr =  $row_value['Reserve_Price'];
											$resx_pr1 = '1';
											}
											

										    
											$sql2="SELECT Orderno,coin_series_denomination_no,coin_series_unique_no,coin_detail_year,coin_detail_mintmark FROM " . DB_PREFIX . "catalog_coin WHERE PCGS = '" . $row_value['pcgs'] . "'";
											$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
											if($ilance->db->num_rows($res2)>0)
											{





												while($line2=$ilance->db->fetch_array($res2))
												{
												$order_nos = $line2['Orderno']; 
												$denom_series = $line2['coin_series_denomination_no'];
												$denom_uniqueno = $line2['coin_series_unique_no'];
												$coin_detail_res=$line2;
												}
											}
											// murugan changes on Jan 19
									$checkin = $ilance->db->query("SELECT project_id, date_end FROM " . DB_PREFIX . "projects WHERE project_id = '".$row_value['coin_id']."'", 0, null, __FILE__, __LINE__);
										if($ilance->db->num_rows($checkin) > 0)
										{
											$relistend = $ilance->db->fetch_array($checkin);
										
										$ilance->db->query("update " . DB_PREFIX . "coin_relist set  
																enddate ='".$date_coin."',
																startbydate ='".DATETIME24H."'
																where user_id='".$row_value['user_id']."' and coin_id='".$row_value['coin_id']."' and date(actual_end_date)=date('".$relistend['date_end']."')", 0, null, __FILE__, __LINE__);
										if($ilance->db->affected_rows() == 0)
										{
											$ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_relist
																		(id, coin_id, enddate, startbydate, user_id, actual_end_date, filtered_auctiontype)
																		VALUES (
																		NULL,
																		'".$row_value['coin_id']."',
																		'".$date_coin."',
																		'".DATETIME24H."',
																		'".$row_value['user_id']."',
																		'".$relistend['date_end']."',
																		'".$autiontype."'
																		)", 0, null, __FILE__, __LINE__);
										}																
											$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects
																		   WHERE project_id = '" . intval($row_value['coin_id']) . "'", 0, null, __FILE__, __LINE__);
										}
										
										/*Tamil for bug 2489 * Starts */
										$coin_detail_sql=$ilance->db->query("SELECT coin_detail_year,coin_detail_mintmark FROM " . DB_PREFIX . "catalog_coin
																		   WHERE PCGS = '" . $row_value['pcgs'] . "'", 0, null, __FILE__, __LINE__);
										$coin_detail_res = $ilance->db->fetch_array($coin_detail_sql);
										
											/* Tamil for bug 3208 * Starts */
											$ilance->db->query("INSERT INTO " . DB_PREFIX . "projects
																		(id, project_id, cid, description, date_added, date_starts, date_end, user_id, visible, project_title, status, project_details, project_type, project_state, buynow, buynow_price, startprice, reserve_price, buynow_qty, buynow_purchases,filtered_auctiontype,currentprice,max_qty,alt_no,filter_escrow,reserve,Orderno,coin_series_denomination_no,coin_series_unique_no,norder,bold,highlite,featured,pcgs,Grade,Grading_Service,coin_detail_year,mintmark,Cac,Variety)
																		VALUES (
																		NULL,
																		'".$row_value['coin_id']."',
																		'".$row_value['Category']."',
																		'".$ilance->db->escape_string($row_value['Description'])."',
																		'".DATETIME24H."',
																		'".DATETIME24H."',
																		'".$date_coin."',
																		'".$row_value['user_id']."',
																		'1',
																		'".$ilance->db->escape_string($row_value['Title'])."',
																		'open',
																		'public',
																		'forward',
																		'product',
																		'".$buynow."',
																		'".$row_value['Buy_it_now']."',
																		'".$row_value['Minimum_bid']."',
																		'".$resx_pr."',
																		'".$row_value['Quantity']."',
																		'".$row_value['Max_Quantity_Purchase']."',
																		'".$autiontype."',
																		'".$amo_my."',
																		'".$row_value['Max_Quantity_Purchase']."',
																		'".$row_value['Alternate_inventory_NO']."',
																		'1',
																		'".$resx_pr1."',
																		'".$order_nos."',
																		'".$denom_series."',
																		'".$denom_uniqueno."',
																		'".$row_value['norder']."',
																		'".$row_value['bold']."',
																		'".$row_value['highlite']."',
																		'".$row_value['featured']."',
																		'".$row_value['pcgs']."',
																		'".$row_value['Grade']."',
																		'".$row_value['Grading_Service']."',
																		'".$coin_detail_res['coin_detail_year']."',
																		'".$coin_detail_res['coin_detail_mintmark']."',
																		'".$row_value['Cac']."',
																		'".$row_value['Variety']."'																		
																		)", 0, null, __FILE__, __LINE__);
												/* Tamil for bug 3208 * Ends */
												// murugan added on jan 20						
												$ilance->db->query("INSERT INTO " . DB_PREFIX . "projects_log
																		(id, project_id, cid, description, date_added, date_starts, date_end, user_id, visible, project_title, status, project_details, project_type, project_state, buynow, buynow_price, startprice, reserve_price, buynow_qty, buynow_purchases,filtered_auctiontype,currentprice,max_qty,alt_no,filter_escrow,reserve,Orderno,coin_series_denomination_no,coin_series_unique_no,norder,bold,highlite,featured)
																		VALUES (
																		NULL,
																		'".$row_value['coin_id']."',
																		'".$row_value['Category']."',
																		'".$ilance->db->escape_string($row_value['Description'])."',
																		'".DATETIME24H."',
																		'".DATETIME24H."',
																		'".$date_coin."',
																		'".$row_value['user_id']."',
																		'1',
																		'".$ilance->db->escape_string($row_value['Title'])."',
																		'open',
																		'public',
																		'forward',
																		'product',
																		'".$buynow."',
																		'".$row_value['Buy_it_now']."',
																		'".$row_value['Minimum_bid']."',
																		'".$resx_pr."',
																		'".$row_value['Quantity']."',
																		'".$row_value['Max_Quantity_Purchase']."',
																		'".$autiontype."',
																		'".$amo_my."',
																		'".$row_value['Max_Quantity_Purchase']."',
																		'".$row_value['Alternate_inventory_NO']."',
																		'1',
																		'".$resx_pr1."',
																		'".$order_nos."',
																		'".$denom_series."',
																		'".$denom_uniqueno."',
																		'".$row_value['norder']."',
																		'".$row_value['bold']."',
																		'".$row_value['highlite']."',
																		'".$row_value['featured']."'
																		)", 0, null, __FILE__, __LINE__);
												
												
												//caterory count
												//$cat_count = build_category_count($row_value['Category'], 'add', "insert_product_auction(): adding increment count category id $cid");																
												$ilance->db->query("update " . DB_PREFIX . "catalog_toplevel set auction_count=auction_count+1 where denomination_unique_no=".$denom_uniqueno, 0, null, __FILE__, __LINE__);
												$ilance->db->query("update " . DB_PREFIX . "catalog_second_level set auction_count=auction_count+1 where coin_series_unique_no=".$denom_series, 0, null, __FILE__, __LINE__);
												//update coin table						
												$con_insert_cointable = $ilance->db->query("
												UPDATE  " . DB_PREFIX . "coins
												SET  project_id = '".$row_value['coin_id']."',
												     status = '0'
												WHERE coin_id 	  =  '" . $coincount_gc[$r] . "'
												AND project_id = '0'
												", 0, null, __FILE__, __LINE__);
												
												// murugan changes on may 24 for relising not having the listing fees 
												if($row_value['relist_count'] == 0)
												{
													//invoice and listing fees
													$my_var_in = insertion_fee_transaction_new($row_value['listing_fee'], 'product', $amo_my, $row_value['coin_id'], $row_value['user_id']);                     							}
												else
												{
												// here relist fees will added
												}   ;                                 
												
				
											}
										} 
								  }
								
													
								print_action_success(''.$check_count_gc.'Item to List on Great Collection', $ilpage['listings']);
								exit();
						 
						}
						else
						{
								print_action_failed('Please check the checkbox you selected some other one', $ilpage['listings']);
								exit();
						}
			 }
			 
			 else if($drop_value < 0)
			 {
			          for($g=0;$g<count($check_value); $g++)
						{
								//val array set for checkbox
								$checkcount_gc[] = $check_value[$g];
								$con_listifng_gc = $ilance->db->query("
								SELECT  *
								FROM " . DB_PREFIX . "coins 
								WHERE coin_listed = 'c'
								AND coin_id  = '".$check_value[$g]."'
								AND Site_Id = '".abs($drop_value)."'
								", 0, null, __FILE__, __LINE__);
								if($ilance->db->num_rows($con_listifng_gc) > 0)
								{
								
									while($row_lisst_gc = $ilance->db->fetch_array($con_listifng_gc))
									{
									
									$coincount_gc[] = $row_lisst_gc['coin_id'];
									
									}
								} 
						}
						
						//count set
					    $coin_count_gc = count($coincount_gc);
						
						$check_count_gc =  count($checkcount_gc);
						if($check_count_gc == $coin_count_gc)
						{
		                 //coinid in loop
						for($d=0;$d<count($check_value);$d++)
						{
						$array_selected_coins[$d]=$check_value[$d];
						//update 
						
						$con_insert_cointable = $ilance->db->query("
									    UPDATE  " . DB_PREFIX . "coins
										SET export = '1'
										    
										WHERE coin_id='".$check_value[$d]."'", 0, null, __FILE__, __LINE__);
						}
						
						$list_selected_coins=implode(",",$array_selected_coins);
						//field set			                          
                        $fields =  array(
                                        array('Title', 'Title'),
                                        array('Category', 'Category 1'),
                                        array('Quantity', 'Quantity'),
                                        array('Minimum_bid', 'Starting Price'),
                                        array('Reserve_Price', 'Reserve Price'),
                                        array('Description', 'Description'),
										 );
                                
						foreach ($fields as $column)
						{
						   $fieldsToGenerate[] = $column[0];
						   $headings[] = $column[1];
						}                             
                              
							
						$sql = "SELECT *  FROM " . DB_PREFIX . "coins WHERE coin_id in (".$list_selected_coins.") AND Site_Id='".$row_list['id']."' AND coin_listed = 'c'";
						
						//data send for fetch	
						switch ($drop_value)
									{
											case $action:
											{
												$data = $ilance->admincp->fetch_reporting_fields($sql, $fieldsToGenerate);
												 break;
											}
										   
									}
						
						//array set value			
						$reportoutput[] = $ilance->admincp->construct_csv_data($data, $headings);
							   
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "reports-$timeStamp";
						//file download in csv
						if ($drop_value)
						{
							header("Pragma: cache");
							header("Content-Type: text/comma-separated-values");
							header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
							for($h=0;$h<count($reportoutput);$h++)
							{
								echo $reportoutput[$h];
								
							}
							 
							  die();
						}
						}
						else
						{
						print_action_failed('Please check the checkbox you selected some other one', $ilpage['listings']);
								exit();
						}
						
	  
	    
			  
			 }
			 else
			 {
			  			 
								
					
											

								$show['holding_first_page']= '0';
								for($i=0;$i<count($check_value); $i++)
											
								{	    //val array set for checkbox
										$checkcount[] = $check_value[$i];
										
										$listed_query = $ilance->db->query("
										SELECT  *
										FROM " . DB_PREFIX . "coins 
										WHERE coin_listed = 'c'
										AND coin_id  = '".$check_value[$i]."'
										AND Site_Id = '".$row_list['id']."'
										", 0, null, __FILE__, __LINE__); 
									
								 
										if($ilance->db->num_rows($listed_query) > 0)
										{
											while($check_row = $ilance->db->fetch_array($listed_query))
											{
										
											// echo '<pre>';
											// print_r($check_row);
										
											$ebaycoincount['coin_id'] = $check_row['coin_id'];
											$ebaycoincount['ebay_coin_id'] = '<input type="hidden"  name="set_ebay_coins['.$check_row['coin_id'].']" value="'.$check_row['coin_id'].'"/>';
											$ebaycoincount['cointitle'] = $check_row['Title'];
											if(empty($check_row['ebay_title']))
											{
											$ebaycoincount['ebay_title'] = '<input style="padding: 6px;height: 29px;width: 516px;" type="text" maxlength=80 name="set_ebay_coin_id['.$check_row['coin_id'].'][Title]" value="'.$check_row['Title'].'"/>';
											}
											else
											{
											$ebaycoincount['ebay_title'] = '<input style="padding: 6px;height: 29px;width: 516px;" type="text" maxlength=80 name="set_ebay_coin_id['.$check_row['coin_id'].'][Title]" value="'.$check_row['ebay_title'].'"/>';
											}
											
											$ebaycoincount['ebay_subtitle']='<input style="padding: 6px;height: 29px;width: 516px;" type="text" maxlength=55 name="set_ebay_coin_id['.$check_row['coin_id'].'][ebay_subtitle]" value="'.$check_row['ebay_subtitle'].'"/>';
											$ebaycoincount['ebay_Categories_id']='<input style="padding: 6px;height: 29px;width: 516px;" type="text" maxlength=10 name="set_ebay_coin_id['.$check_row['coin_id'].'][ebay_Categories_id]" value="'.$check_row['ebay_Categories_id'].'"/>';
											
											$ebaycoincount['End_Date'] ='<strong>' . date("F d,Y",strtotime($check_row['End_Date'])) . '</strong>';
											$ebaycoincount['pcgsno'] = $check_row['pcgs']; 
											if($check_row['Site_Id'] > 0)
											$check_row['Site_Id'] ='<strong>Ebay</strong>';
											else
											$check_row['Site_Id'] ='<strong>GC</strong>';
											$ebaycoincount['Site_Id'] = $check_row['Site_Id'];
											$coincount[] = $check_row['coin_id'];
												

											$update_ebayquery = $ilance->db->query("
											SELECT s.coin_series_unique_no,s.coin_series_denomination_no,s.coin_series_name,s.ebay_Categories_id,s.ebay_Categories_Names
											FROM " . DB_PREFIX . "catalog_second_level s
											left join " . DB_PREFIX . "catalog_coin c on s.coin_series_unique_no = c.coin_series_unique_no
											WHERE PCGS = '".$check_row['Category']."'
											
										
											", 0, null, __FILE__, __LINE__); 


											// if($ilance->db->num_rows($update_ebayquery) > 0)
											// {
											// while($update_ebay = $ilance->db->fetch_array($update_ebayquery))
											// {
											
									
											
											// if($update_ebay['ebay_Categories_id'] >'0')
											// {
												// $con_update_cointable = $ilance->db->query("
												// UPDATE  " . DB_PREFIX . "coins SET  ebay_Categories_id ='".$update_ebay['ebay_Categories_id']."'
												// WHERE coin_id ='" . $check_row['coin_id'] . "'
												// ", 0, null, __FILE__, __LINE__);
												
											// }
											// else 
											// {
												// print_action_failed('Please check the coins that you selected are not having the ebay_Categories_id,Kindly update in catalog_second_level table ', $ilpage['listings']);
												// exit();
											// }
											// }
											
											// }
											// else 
											// {
												// print_action_failed('Please check the coins that you selected are not having the ebay_Categories_id,Kindly update in catalog_second_level table ', $ilpage['listings']);
												// exit();
											// }
											}
											
												
											$ebaycoincot[]=	$ebaycoincount;
										}
										
								}				
								//exit;
								$pprint_array = array('show','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action');

								($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

								$ilance->template->fetch('main', 'holding_area.html', 2);
								$ilance->template->parse_hash('main', array('ilpage' => $ilpage));	
								$ilance->template->parse_loop('main', array('ebaycoincot'));
								$ilance->template->parse_if_blocks('main');
								$ilance->template->pprint('main', $pprint_array);
								exit();	
								
								
			
	//if closed
			}
		             
		 }
	  
	      }
	  }  
          
    }
	//error_reporting(E_ALL);
	if(isset($ilance->GPC['Submit_ebay']) AND $ilance->GPC['Submit_ebay'] == 'Submitebay' )
	{

	global $ilance;
	 // echo '<pre>';
	 // print_r($ilance->GPC);
	 // exit; 
	$ebay_titlepro	=	array();
	$ebay_projt_id=$ilance->GPC['set_ebay_coin_id'];
	$set_ebay_coins = $ilance->GPC['set_ebay_coins'];
	
	foreach($ebay_projt_id as $key => $ebay_project_id){

	

	$sql2="update  " . DB_PREFIX . "coins set ebay_title ='".$ebay_project_id['Title']."',ebay_subtitle ='".$ebay_project_id['ebay_subtitle']."',ebay_Categories_id ='".$ebay_project_id['ebay_Categories_id']."' WHERE  coin_id='".$key."'"; 
	$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);

	$ebay_titlepro[]	=	$ebay_projt_id;
	}
	

	$set_ebay_coin= array();
	foreach($set_ebay_coins	 as $ebay_coins)
	{
	if(!fetch_image_check($ebay_coins, 'itemphoto'))

	{
	$set_ebay_coin[]	=	$ebay_coins;

	}
	}
	
	 // echo '<pre>';
	 // print_r($set_ebay_coins);
	 // exit; 
	
	if(count($set_ebay_coin)>0){


	print_action_failed('Following Coins not having image,<b>'.implode(',',$set_ebay_coins).'</b>', $ilpage['listings']);
	exit();

	}else
	{



	
		$setcoins= array();
		foreach ($set_ebay_coins as $vcolumn)
		{
		


		 // echo '<pre>';
		 // print_r($vcolumn);
		 // exit; 
		 

		$setcoins[]= $vcolumn;
		post_images_to_ebay($vcolumn);
		

		$imageuplchk = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "ebay_images WHERE coin_id = '".$vcolumn."' group by attach_id", 0, null, __FILE__, __LINE__);

		}
		


		if ($ilance->db->num_rows($imageuplchk) > 0)
		{	


			$ebay_item_id=Add_bulk_item_to_ebay($setcoins);
			// echo '<pre>';
			// print_r($ebay_item_id);
			// exit;
			if($ebay_item_id['success']==1)
			{
			print_action_success('The Coins are Sucessfully Uploaded to EBay Live', $ilpage['listings']);
			exit();		
			}
			else
			{
			
			print_action_success('The Coin are Sucessfully Uploaded to EBay Live,<br/><br/>Sorry the follwing coins was already live,please left it & Reselect to listing<br/><br/>
			<b>coin id: '.implode(',',$ebay_item_id['coins']).'</b>', $ilpage['listings']);

			exit();
			}
		}

		else
		{

		print_action_failed('sorry your selected coins images are not uploaded to live,please Reselect to listing', $ilpage['listings']);
		exit();
		}

	}

	}

							
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'all_listing')
   {
	  
	  
     

	   $query10="SELECT concat('http://cgi.ebay.com/ws/eBayISAPI.dll?ViewItem&item=',e.ebay_id) as link,e.ebay_id,e.listedon,e.end_date,e.status,c.consignid,c.title,c.coin_id,c.user_id,c.pcgs,u.username FROM " . DB_PREFIX . "ebay_listing e left join " . DB_PREFIX . "coins c on c.coin_id=e.coin_id left join " . DB_PREFIX . "users u on u.user_id=c.user_id order by e.id desc";
	   $sql10 = $ilance->db->query($query10, 0, null, __FILE__, __LINE__);
	   if($ilance->db->num_rows($sql10))
	   {
		   $i=0;
	   	while($line10 = $ilance->db->fetch_array($sql10))
	   	{
			$line10['link']='<a href="'.$line10['link'].'">'.$line10['ebay_id'].'</a>';
	   	 $ebay_list[$i++]=$line10;
	   	}

	   }

	$pprint_array = array('site_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'affiliate_listed.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('ebay_list'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	}	
	
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'affiliate_update')
   { 
                 
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_affiliate_update')
			{
			             if($_POST['allready'] != '1')
						 {
								$sqlusercheck_my = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "users
								WHERE username = '" . $_POST['username'] . "'
								or   email    = '" . $_POST['email'] . "'
								", 0, null, __FILE__, __LINE__);
					  
							 if ($ilance->common->is_email_valid(trim($_POST['email'])) == false)
							  {
										  echo '<Script language = JavaScript>
					
												   alert("Enter valid emai address ")
					
												   window.history.go(-1)
					
											  </Script>';
											
											
							   }
					  
							  else if ($ilance->db->num_rows($sqlusercheck_my) > 0)
							  {
							
							echo '<Script language = JavaScript>
				
								   alert("your username or emai address already exit")
				
								   window.history.go(-1)
				
							  </Script>';
							
							}
											
							  else
							  {
									// custom registration questions [page 1]
									$ilance->registration_questions = construct_object('api.registration_questions');
									$customquestions = $ilance->registration_questions->construct_register_questions(1, 'input', 0);
									
									// set desired language for new user we'll be inserting into db as
									if (empty($ilance->GPC['username']))
									{
									$area_title = $phrase['_your_session_has_expired_please_login'];
									$page_title = SITE_NAME . ' - ' . $phrase['_your_session_has_expired_please_login'];
									
									$navcrumb = array("$ilpage[main]" => $phrase['_your_session_has_expired_please_login']);
									print_notice($phrase['_your_session_has_expired_please_login'], $phrase['_either_your_session_has_expired_or_you_are_a_guest_attempting_to_access_a_member_resource'], $ilpage['registration']."?cmd=register&amp;step=1", $phrase['_register_to_login_here']);
									exit();
									}
							
									
													
									// build subscription plan session
									if ($ilance->GPC['subscriptionid'] > 0)
									{
											$_SESSION['ilancedata']['subscription']['subscriptionid'] = intval($ilance->GPC['subscriptionid']);
											$_SESSION['ilancedata']['subscription']['subscriptionpaymethod'] = mb_strtolower(trim($ilance->GPC['paymethod']));
									}
									else
									{
											$_SESSION['ilancedata']['subscription']['subscriptionid'] = '1';
											$_SESSION['ilancedata']['subscription']['subscriptionpaymethod'] = 'account';
									}       
							
									// support promotional code feature
									if (!empty($ilance->GPC['promocode']))
									{
											$_SESSION['ilancedata']['subscription']['promocode'] = handle_input_keywords(trim($ilance->GPC['promocode']));
									}
									else
									{
											$_SESSION['ilancedata']['subscription']['promocode'] = '';
									}
							
									$navcrumb = array();
									$navcrumb["$ilpage[registration]"] = $phrase['_registration'];
									$navcrumb[""] = $phrase['_message'];
									
									// find out if we had any questions to answer
									if (empty($_SESSION['ilancedata']['questions']))
									{
											$_SESSION['ilancedata']['questions'] = array();    
									}       
									 
							
									// notes: you may send 3 custom arguments:
									// return_userid        : returns only the new user ID
									// return_userstatus    : returns the new users status (login status, active, unverified, etc)
									// return_userarray     : returns the full user array of the created member
									$dowhat = 'return_userarray';
							
							
									$ilance->registration = construct_object('api.registration');
									//user saved
									$final = $ilance->registration->build_user_datastore_buyer($_POST, $_SESSION['ilancedata']['preferences'], $_SESSION['ilancedata']['subscription'], $_SESSION['ilancedata']['questions'], $dowhat);
									
									$ilance->email = construct_dm_object('email', $ilance);
									$ilance->email->mail = $final['email'];
									$ilance->email->slng = $final['slng'];
									$ilance->email->get('buyer_new_user');		
									$ilance->email->set(array(
											'{{username}}' => $final['username'],
											'{{password}}' =>  $ilance->GPC['password'],
											'{{first_name}}' => $final['firstname'],
											'{{last_name}}' => $final['lastname'],
											'{{staff}}' => SITE_EMAIL,
											'{{links}}' => HTTP_SERVER . $ilpage['login']
											
									));
									$ilance->email->send();
								
				}	
			
			             }
						
					
					if($_POST['allready'] != '1')
					{
					$user_open = $final['userid'];
					}
					else
					{
					$user_open = $ilance->GPC['buyer_id'];
					}			
						
						$con_insert = $ilance->db->query("
						INSERT INTO " . DB_PREFIX . "affiliate_buyer
						(id, amount, ebay_number, lvf, fvf, pay_email, how_to_pay, coin_id, Site_Id, seller_id, buyer_id)
						VALUES (
							   NULL,
							   '".$ilance->GPC['Amount']."',
							   '".$ilance->GPC['enumber']."',
							   '".$ilance->GPC['lvf']."',
							   '".$ilance->GPC['fvf']."',
							   NULL,
							   NULL,
							   '".$ilance->GPC['coin_id']."',
							   '".$ilance->GPC['site_id']."',
							   '".$ilance->GPC['seller_id']."',
							   '".$user_open."'
						)", 0, null, __FILE__, __LINE__);	
				$ilance->accounting = construct_object('api.accounting');
					
					//amount update for seller and invoice for that 		
					$amount_self =	$ilance->GPC['Amount'] - ($ilance->GPC['lvf'] + $ilance->GPC['fvf']);	
							
					$site_val_name = fetch_user_siteid('site_name',$ilance->GPC['site_id']);
							
					$invoiceid_sel = $ilance->accounting->insert_transaction(
						0,
						0,
						0,
						$ilance->GPC['seller_id'],
						0,
						0,
						0,
						'Your requested  for credit in '.$site_val_name.' listed',
						sprintf("%01.2f", $amount_self),
						sprintf("%01.2f", $amount_self),
						'paid',
						'credit',
						'account',
						DATETIME24H,
						DATETIME24H,
						DATETIME24H,
						'6',
						0,
						0,
						1,
						'',
						0,
						0
					);	
			
				$text  = insert_income_reported($ilance->GPC['seller_id'], sprintf("%01.2f", $amount_self), 'credit');
				
				$user_amount = fetch_user('total_balance',$ilance->GPC['seller_id']);
				$inner_val = $user_amount + $amount_self;
				$con_insert_cointable = $ilance->db->query("
				UPDATE  " . DB_PREFIX . "users
				SET  available_balance = '".$inner_val."',
					 total_balance     = '".$inner_val."'
					 
				WHERE user_id 	  =  '" . $ilance->GPC['seller_id'] . "'
				
				", 0, null, __FILE__, __LINE__); 
				
				echo '<script type="text/javascript">
				self.close();
				window.opener.location.reload();
				</script>';
										
			
			}	    
					
					        //list of all certified coin 
	     
		                    $con_listing = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "coins 
							WHERE coin_listed = 'c'
							AND   project_id  = '0'
							", 0, null, __FILE__, __LINE__);
							            $number = (int)$ilance->db->num_rows($con_listing);
							            if($ilance->db->num_rows($con_listing) > 0)
										{
										$row_con_list = 0;
										while($row_list = $ilance->db->fetch_array($con_listing))
										{	
												
										$date_coin = explode('-',$row_list['End_Date']);	
									    $month_name = date( 'M', strtotime(date("Y-m-d", strtotime($row_list['End_Date'])) ));	
										$row_list['checkval'] = '<input type="checkbox" name="val[]" id="valid" value="'.$row_list['coin_id'].'" onclick="return myself();">';     
										$row_list['coin_id'] = $row_list['coin_id'];
										$row_list['consignid'] = $row_list['consignid']; 
										$row_list['userid'] = $row_list['user_id']; 
										$row_list['user'] = fetch_user('username', $row_list['user_id']); 
										$row_list['date_sent'] = $date_coin[1].'-'.$month_name.'-'.$date_coin[0]; 
										$row_list['cointitle'] = $row_list['Title'];
										$row_list['pcgsno'] = $row_list['pcgs'];  
										$row_list['listin'] = fetch_user_siteid('site_name',$row_list['Site_Id']);
										$holding_area_list[] = $row_list;
										$row_con_list++;
										
										}
										
						                }
										
										else
										{				
										$show['no'] = 'holding';
								     	}
										
       if(isset($ilance->GPC['coin_id']))
	   {
	       $con = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "coins
								WHERE coin_id = '".$ilance->GPC['coin_id']."'
								", 0, null, __FILE__, __LINE__);
		   $row_con = $ilance->db->fetch_array($con);	
		   //coin_id, user_id, pcgs, Title, Description, Grading_Service, Grade, Quantity, Max_Quantity_Purchase, Certification_No, Condition_Attribute, Cac, Star, Plus, Coin_Series, Pedigee, Site_Id, Minimum_bid, Reserve_Price, Buy_it_now, End_Date, Alternate_inventory_No, Category, Other_information, consignid, coin_listed, Service_Level, final_fee_percentage, final_fee_min, listing_fee, referal_id, project_id, status, export
		   $lvf = $row_con['listing_fee'];	
		   $fvf = $row_con['final_fee_percentage'];	
		   $coin_id = $ilance->GPC['coin_id'];
		   $site_id	= $row_con['Site_Id'];
		   $seller_id = $ilance->GPC['seller_id'];	
		   $jscity = $ilconfig['registrationdisplay_defaultcity'];
									$formid = 'forms[' . SEARCHBOXHEADER . ']';
									//country and state value
									$countryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
									$country_js_pulldown = construct_country_pulldown($countryid, $ilconfig['registrationdisplay_defaultcountry'], 'country', false, 'state');
									$state_js_pulldown = '<div id="stateid" style="height:20px">' . construct_state_pulldown($countryid, $ilconfig['registrationdisplay_defaultstate'], 'state') . '</div>';
		   $ilance->subscription = construct_object('api.subscription');
		   $subscription_plan_pulldown = $ilance->subscription->plans_pulldown();
	   }
	 
	$pprint_array = array('seller_id','subscription_plan_pulldown','state_js_pulldown','country_js_pulldown','coin_id','lvf','fvf','site_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'holding_affiliate_update.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('holding_area_list'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
 }
 
   //insert cancel list 
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'insert_canel')
   {
        			$con_seller_id = $ilance->db->query("SELECT *
					FROM " . DB_PREFIX . "users WHERE user_id='".$_POST['seller_id']."'", 0, null, __FILE__, __LINE__);
					$row_list_sid = $ilance->db->fetch_array($con_seller_id);
					
					$sell_amount = $row_list_sid['total_balance'] - $_POST['amount_val'];
					
					$con_buyer_id  = $ilance->db->query("SELECT *
					FROM " . DB_PREFIX . "users WHERE user_id='".$_POST['buyer_id']."'", 0, null, __FILE__, __LINE__);
					$row_list_bid = $ilance->db->fetch_array($con_buyer_id);
					
					$buy_amount = $row_list_bid['total_balance'] + ($_POST['amount_val'] - $_POST['feesc']);
					
					$amount_sell = $_POST['amount_val'];
					$amount_buy = $_POST['amount_val'] - $_POST['feesc'];
					$date_coin = explode('-',$ilance->GPC['datesold']);	
				    
					
					$ilance->accounting = construct_object('api.accounting');
					//buyer invoice create 
					$invoiceid = $ilance->accounting->insert_transaction(
						0,
						$ilance->GPC['item_id'],
						0,
						$_POST['buyer_id'],
						0,
						0,
						0,
						'Your requested  for canceling your purchase message',
						sprintf("%01.2f", $amount_buy),
						sprintf("%01.2f", $amount_buy),
						'paid',
						'debit',
						'account',
						DATETIME24H,
						DATETIME24H,
						DATETIME24H,
						'6',
						0,
						0,
						1,
						'',
						0,
						0
					);	
									    
					$date_coin_op = $date_coin[2].'-'.$date_coin[0].'-'.$date_coin[1]; 
					$text1 = insert_income_spent($_POST['buyer_id'], sprintf("%01.2f", $amount_buy), 'debit');
					
					//buyer email
					$buyer_email =  fetch_user('email', $_POST['buyer_id']);
					$buyer_name  =  fetch_user('username', $_POST['buyer_id']);
					$pro_tit     =  fetch_auction('project_title', $ilance->GPC['item_id']);
					
					$ilance->email = construct_dm_object('email', $ilance);
					
					$ilance->email->slng = fetch_user_slng($_POST['buyer_id']);
					$ilance->email->mail = $buyer_email;
						
					$ilance->email->get('Buyer_cancel');		
					$ilance->email->set(array(
							'{{buyer_name}}' => $buyer_name,
							'{{project_tit}}' => $pro_tit,
							'{{amount_value}}' => 'US$'.$amount_buy,
					));
					
					$ilance->email->send();
					
					
					 //insert cancel value 
					 $con_insert = $ilance->db->query(" INSERT INTO " . DB_PREFIX . "cancel_sale
					 (cancel_id, buyer_id, seller_id, amount, project_id, c_fees, notes, sold_date, gc)
					 VALUES (
						   NULL,
						   '".$ilance->GPC['buyer_id']."',
						   '".$ilance->GPC['seller_id']."',
						   '".$ilance->GPC['amount_val']."',
						   '".$ilance->GPC['item_id']."',
						   '".$ilance->GPC['feesc']."',
						   '".$ilance->GPC['notes']."',
						   '".$date_coin_op."',
						   '".$ilance->GPC['gcyes']."'
						   
					)", 0, null, __FILE__, __LINE__);
					//update user amount
							
					$con_insert_cointable = $ilance->db->query("
					UPDATE  " . DB_PREFIX . "users
					SET  available_balance = '".$buy_amount."',
						 total_balance     = '".$buy_amount."'
						 
					WHERE user_id 	  =  '" . $_POST['buyer_id'] . "'
					
					", 0, null, __FILE__, __LINE__); 
					if($_POST['gcyes'] != '1')
					{
					//seller  invoice create 
					$invoiceid_sel = $ilance->accounting->insert_transaction(
						0,
						$ilance->GPC['item_id'],
						0,
						$_POST['seller_id'],
						0,
						0,
						0,
						'Your requested  for canceling your purchase',
						sprintf("%01.2f", $amount_sell),
						sprintf("%01.2f", $amount_sell),
						'paid',
						'debit',
						'account',
						DATETIME24H,
						DATETIME24H,
						DATETIME24H,
						'6',
						0,
						0,
						1,
						'',
						0,
						0
					);	
					
					
					
					$text  = insert_income_reported($_POST['seller_id'], sprintf("%01.2f", $amount_sell), 'debit');
					
					$con_insert_cointable_new = $ilance->db->query("
					UPDATE  " . DB_PREFIX . "users
					SET  available_balance = '".$sell_amount."',
						 total_balance     = '".$sell_amount."'
						 
					WHERE user_id 	  =  '" . $_POST['seller_id'] . "'
					
					", 0, null, __FILE__, __LINE__); 
					
					//seller email 
					
					$seller_email =  fetch_user('email', $_POST['seller_id']);
					$seller_name  =  fetch_user('username', $_POST['seller_id']);
					$pro_tit     =  fetch_auction('project_title', $ilance->GPC['item_id']);
					
					$ilance->email = construct_dm_object('email', $ilance);
					
					$ilance->email->slng = fetch_user_slng($_POST['seller_id']);
					$ilance->email->mail = $seller_email;
						
					$ilance->email->get('Seller_cancel');		
					$ilance->email->set(array(
							'{{seller_name}}' => $seller_name,
							'{{project_tit}}' => $pro_tit,
							'{{amount_value}}' => 'US$'.$amount_sell,
					));
					
					$ilance->email->send();
					}
														
										
					print_action_success('Your Coin Details Canceled successfully', $ilpage['listings']);
					exit();
   }
   //canel list  
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'list_canel')
   {
                 //get list
				 $buyer_id    = $ilance->GPC['buyer_id'];
				 $buyer_name  = fetch_user('username',$ilance->GPC['buyer_id']);
				 $seller_id   = $ilance->GPC['seller_id'];
				 $seller_name = fetch_user('username',$ilance->GPC['seller_id']);
				 $datesold    = $ilance->GPC['datesale'];
				 $amount_val  = $ilance->GPC['amount'];
				 $item_id     = $ilance->GPC['item_id'];
				 
	            //list by buyer cancelled
				$con_listing = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "cancel_sale 
										WHERE buyer_id = '".$ilance->GPC['buyer_id']."'
										
										", 0, null, __FILE__, __LINE__);
							            $number = (int)$ilance->db->num_rows($con_listing);
							            if($ilance->db->num_rows($con_listing) > 0)
										{
										$row_con_list = 0;
										while($row_list = $ilance->db->fetch_array($con_listing))
										{	
												
										$date_coin = explode('-',$row_list['sold_date']);	
										$dt = explode(' ',$date_coin['2']);	
									    $month_name = date( 'M', strtotime(date("Y-m-d", strtotime($row_list['sold_date']))));	
										$row_list['date_sent'] = $dt[0].'-'.$month_name.'-'.$date_coin[0]; 
										$row_list['sel'] =  $seller_name = fetch_user('username',$row_list['seller_id']);
										
										$cancel_sale_list[] = $row_list;
										$row_con_list++;
										
										}
										
						                }
										
										else
										{				
										$show['no'] = 'cancel_list';
								     	}
     $pprint_array = array('number','item_id','amount_val','datesold','seller_id','seller_name','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'cancel_sale.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('item_won_list','cancel_sale_list'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
   }
   
     //return insert  may4  
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'insert_return_user')
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
						$_POST['user_id'],
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
					 (coin_id, consign_id, user_id, shipper_id, shipping_fees, charges, return_date, invoiceid,notes,return_opt)
					 VALUES (
						   '".$ilance->GPC['coin_id']."',
						   '".$ilance->GPC['consignid']."',
						   '".$ilance->GPC['user_id']."',
						   '".$ilance->GPC['shipper']."',
						   '".$row['basefee']."',
						   '".$ilance->GPC['feesc']."',
						   '".DATETODAY."',
						   '".$invocie_id."',
						    '".$ilance->GPC['notes']."',
						   '".$ilance->GPC['return_opt']."'
					  
					)", 0, null, __FILE__, __LINE__);
					//suku corrected
					$con_insert1 = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coins_retruned SELECT * FROM " . DB_PREFIX . "coins where coin_id='".$ilance->GPC['coin_id']."'", 0, null, __FILE__, __LINE__);
					
					$con_insert2 = $ilance->db->query("delete FROM " . DB_PREFIX . "coins where coin_id='".$ilance->GPC['coin_id']."'", 0, null, __FILE__, __LINE__);
					
					
					print_action_success('Your Coin Details Returned successfully', $ilpage['listings']);
					exit();
   }
   //return select  may4  
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'list_return_user')
   {
                 //get list
				 $user_id   = $ilance->GPC['user_id'];
				 $seller_name = fetch_user('username',$ilance->GPC['user_id']);
				
				 $consignid  = $ilance->GPC['consignid'];
				 $coin_id     = $ilance->GPC['coin_id'];
				 
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers order by basefee asc", 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($sql))
			{
			$html='';
			
			$html.='<select name="shipper" id="shipper">
			<option value="-1"  >Select Shipper</option>
			<option value="0"  >Free Shipping</option>';
				while($line=$ilance->db->fetch_array($sql))
				{
				
				
				$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'</option>';
			}
			}
			
			
			
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
										
										$form_action=$ilpage['listedarea'];
     $pprint_array = array('newnumber','html','coin_id','consignid','seller_name','user_id','seller_id','seller_name','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action');
			
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'return_coin.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('return_sale_list','cancel_sale_list'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
   }
   
  //update live listing details page
   if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'update_live')
   {
                         
						if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_live_coin')
                        {  	
						     if(isset($ilance->GPC['bidacu']))
							 {   
							     if($ilance->GPC['bidacu'] == '1')
								 {
								        //acution expired current award or project expired
								   
								        $sqlusercheck_my = $ilance->db->query("
										UPDATE  " . DB_PREFIX . "projects 
										
										SET status = 'open',
										
										date_end = '".listaction."'
										
										WHERE project_id = '".$ilance->GPC['proid']."'
										
										", 0, null, __FILE__, __LINE__);
										
										$ilance->db->query("
										UPDATE  " . DB_PREFIX . "coins 
										
										SET End_Date = '".listaction."'
										
										WHERE coin_id = '".$ilance->GPC['proid']."'
										
										", 0, null, __FILE__, __LINE__);
								   
								 }
								 else
								 {								       
									   //placed bid delete project										
										$sqlcat_user_detail = $ilance->db->query("
										SELECT *		
										FROM " . DB_PREFIX . "project_bids		
										WHERE project_id = '".$ilance->GPC['proid']."'										
										");
								        if($ilance->db->num_rows($sqlcat_user_detail) > 0)
										{									
											$sqlusercheck = $ilance->db->query("
											DELETE FROM  " . DB_PREFIX . "project_bids 
											WHERE project_id = '".$ilance->GPC['proid']."'											
											");										
										
										}
										$sqlcat_user_detail1 = $ilance->db->query("
										SELECT *		
										FROM " . DB_PREFIX . "proxybid		
										WHERE project_id = '".$ilance->GPC['proid']."'										
										", 0, null, __FILE__, __LINE__);
								        if($ilance->db->num_rows($sqlcat_user_detail1) > 0)
										{	
											
											$sqlusercheck1 = $ilance->db->query("
											DELETE FROM  " . DB_PREFIX . "proxybid 
											WHERE project_id = '".$ilance->GPC['proid']."'											
											", 0, null, __FILE__, __LINE__);
										}
									    //acution closed 								   
								        $sqlusercheck_my = $ilance->db->query("
										UPDATE  " . DB_PREFIX . "projects										
										SET bids = '0',										
										date_end = '".DATETIME24H."',
										
										status = 'expired',
										haswinner = '0',
										winner_user_id = '0'										
										WHERE project_id = '".$ilance->GPC['proid']."'										
										", 0, null, __FILE__, __LINE__);		
										
										$ilance->db->query("
										UPDATE  " . DB_PREFIX . "coins 
										
										SET End_Date = '".DATETIME24H."'
										
										WHERE coin_id = '".$ilance->GPC['proid']."'
										
										", 0, null, __FILE__, __LINE__);								
								 }
							     
							 }
							 else
							 { 
							 
							    if($ilance->GPC['title'] == '')
								{
							    $tit = '';
								$coin_title='';
								}
								else
								{
								$tit = ",project_title = '".$ilance->GPC['title']."'";
								$coin_title = ",Title = '".$ilance->GPC['title']."'";
								}
							    $date_coin = $_POST['year'] .'-'.$_POST['month'].'-'.$_POST['day'].' '.$_POST['endtime']; 
							    
								
							    //project update 
								$sqlusercheck = $ilance->db->query("
	
								UPDATE  " . DB_PREFIX . "projects 
								
								SET cid = '".$ilance->GPC['pcgs']."',
								
								currentprice = '".$ilance->GPC['current']."',
								
								date_end     = '".$date_coin."'
								
								".$tit."
								
								WHERE project_id = '".$ilance->GPC['proid']."'
								
								");
								
								//coin table update
								$ilance->db->query("
	
								UPDATE  " . DB_PREFIX . "coins 
								
								SET pcgs = '".$ilance->GPC['pcgs']."',
								
								End_Date = '".$date_coin."'
								
								".$coin_title."
								
								WHERE coin_id = '".$ilance->GPC['proid']."'
								
								", 0, null, __FILE__, __LINE__);
							 
							 }
						
						//print_r($_POST);
						
						print_action_success('Successfully Updated Live Listing', $ilpage['listedarea'].'?cmd=search_coin');
                        exit();
						
						}
						 
						//update listed details 
						// bug # 4519 kumaravel start
						  $coin_id = $ilance->GPC['project_id'];
						  
						  $con_insert = $ilance->db->query("SELECT currentprice,project_title,project_id,buynow,cid,
																	buynow_price,startprice,date_end,bids 
															FROM " . DB_PREFIX . "projects 
															WHERE project_id= '".$ilance->GPC['project_id']."'");
						$rescat = $ilance->db->fetch_array($con_insert);
	  
						  //date
							$dataexplode = explode('-', $rescat['date_end']);
						
							$daylist_r ='<select name="day" id="day"><option value="">DATE</option>';
						
						
						for($i=1; $i<=31; $i++)
						if($dataexplode[2] == $i)
						$daylist_r .= "<option value='$i' selected>$i</option>";
						else
						$daylist_r .= "<option value='$i'>$i</option>";
						
						$daylist_r .='</select>';
						
						$monthlist_r ='<select name="month" id="month"><option value="">MONTH</option>';
						
						
						for($j=1; $j<=12; $j++)
						
						if($dataexplode[1] == $j)
						$monthlist_r .= "<option value='$j' selected>$j</option>";
						else
						$monthlist_r .= "<option value='$j'>$j</option>";
						
						
						$monthlist_r .= '</select>';
						
						$yearlist_r = '<select name="year" id="year"><option value="">YEAR</option>';
						
					
						for($k=date("Y"); $k<=date("Y")+5; $k++)
						if($dataexplode[0] == $k)
						$yearlist_r .= "<option value='$k' selected>$k</option>";
						else
						$yearlist_r .= "<option value='$k'>$k</option>";
						
						$yearlist_r .='</select>';
						  
						  $pend = $rescat['buynow'];
						  if($pend == '1')
						  $checkl = $rescat['currentprice'];
						  else
						  $checkl = '';
						  
						  $Title =  $rescat['project_title'];
						  $project_id =  $rescat['project_id'];
						  $pcgs =  $rescat['cid'];
						  $buy =  $rescat['buynow_price'];
						  $start =  $rescat['startprice'];
						  $current = $rescat['currentprice'];
						  $bids = $rescat['bids'];
						 
						 $timeexplode = explode(' ', $rescat['date_end']);
						 $endtime = $timeexplode[1];
   
   
   $pprint_array = array('endtime','yearlist_r','monthlist_r','daylist_r','project_id','Title','pcgs','buy','start','current','bids','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','coin_id1','consign_id1','$seller_name1','$title1','$pcgs_no1','dt1','amount1');
			
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'update_live_coin.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('item_won_aff_list','search_coin'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
   }
   
   // bug # 4519 kumaravel start
   
   //all listed item in gc
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search_coin')
   {            
   
									 $counter = ($ilance->GPC['page'] - 1) * 10;
									 
									 $scriptpageprevnext = $ilpage['listings'] . '?cmd=search_coin';
									 
									 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
									 {
										$ilance->GPC['page'] = 1;
									 }
									 else
									 {
										$ilance->GPC['page'] = intval($ilance->GPC['page']);
									 }
				 
										if(isset($ilance->GPC['cmd1']) AND $ilance->GPC['cmd1'] == 'search_coin_search')
										{
										$filter=$ilance->GPC["filterby"];
										$filtervalue=$ilance->GPC["filtervalue"];		

										
										if($filtervalue)
										{	
										if($filter == 'seller_username')
										{
									         $seller_id=fetch_user('user_id','',$filtervalue);
										 $con = "AND p.user_id='".$seller_id."'";
										}
										 else if($filter == 'seller_email')
										{
									     $seller_id=fetch_user('user_id','','',$filtervalue);
										 $con = "AND p.user_id='".$seller_id."'";
										}
										else if($filter == 'winningbidder_email')
										{
									         $bidder_id=fetch_user('user_id','','',$filtervalue);
										 $con = "AND p.winner_user_id='".$bidder_id."'";
										}
										else{
										$con = "AND ".$filter."='".$filtervalue."'";
										}									
										
										$scriptpageprevnext = $ilpage['listings'] . '?cmd=search_coin&cmd1=search_coin_search&filterby='.$filter.'&filtervalue='. $filtervalue;
										//$con = "AND ".$filter."='".$filtervalue."'";
										echo $SQL="
										SELECT *
										FROM " . DB_PREFIX . "projects p
										WHERE p.status = 'open' 
										$con
										GROUP BY p.project_id 
										LIMIT " . (($ilance->GPC['page'] - 1) * 10) . ",10
										";
										$coin_list1=$ilance->db->query($SQL , 0, null, __FILE__, __LINE__ );
										 }
										 else
										 {
										 $con ='';
										 
										 $coin_list1=$ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "projects p
										WHERE p.status = 'open'  GROUP BY p.project_id
										LIMIT " . (($ilance->GPC['page'] - 1) * 10) . ",10
										"  );
										 }
										}
										else
										{
									    $con ='';
										$coin_list1=$ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "projects p
										WHERE p.status = 'open'
										GROUP BY p.project_id
										LIMIT " . (($ilance->GPC['page'] - 1) * 10) . ",10
										", 0, null, __FILE__, __LINE__  );
										
										
										
										}
										
										 $coin_list=$ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "projects p
										WHERE p.status = 'open'
										$con
										GROUP BY p.project_id
										"  );
										
										 
										$number = (int)$ilance->db->num_rows($coin_list);
										if($ilance->db->num_rows($coin_list1) > 0)
										{
										while($row_list=$ilance->db->fetch_array($coin_list1))
										{
										
											$row_list['pc'] = '<span class="blue"><a href="catalog.php?cmd=update-catalog&subcmd=update-coinlist&id='.fetch_cat('id',$row_list['cid']).'">'.$row_list['cid'].'</a></span>';	
											
														
$row_list['coin_id1'] = '<span class="blue"><a href="consignments.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$row_list['user_id'].'&consignid='.fetch_coin_consignid('consignid',$row_list['project_id']).'&pro='.fetch_user_attach('project_id',$row_list['project_id']).'&coin_id='.$row_list['project_id'].'">'.$row_list['project_id'].'</a></span>';	
									    $row_list['title1'] = 	'<span class="blue"><a href="consignments.php?cmd=add_single_coin&subcmd=update_edit&user_id='.$row_list['user_id'].'&consignid='.fetch_coin_consignid('consignid',$row_list['project_id']).'&pro='.fetch_user_attach('project_id',$row_list['project_id']).'&coin_id='.$row_list['project_id'].'">'.$row_list['project_title'].'</a></span>';
										
										
										$row_list['upda'] = '<span class="blue"><a href="listings.php?cmd=update_live&project_id='.$row_list['project_id'].'">Update Live</a></span>';				
										
										$row_list['conid'] = fetch_coin_consignid('consignid',$row_list['project_id']);				
										
										$row_list['seller_name1']=fetch_user('username',$row_list['user_id']);
										
										if($row_list['filtered_auctiontype'] == 'fixed')
										$row_list['deld'] = '<span class="blue"><a href="settings.php?cmd=dailydeals&coin_id='.$row_list['project_id'].'">DailyDeal</a></span>';
										else
										$row_list['deld'] = '<span class="blue">No Deal</span>';
										
										
										
										$date_coin = explode('-',$row_list['date_end']);
										$dt = explode(' ',$date_coin['2']);	
									    $month_name = date( 'M', strtotime(date("Y-m-d", strtotime($row_list['date_end'])) ));
										$row_list['dt1'] = $dt[0].'-'.$month_name.'-'.$date_coin[0]; 
										
										$item_won_aff_list[] = $row_list;
										
										}
										}
										else
										{
										$show['no'] = 'won_aff_list';
										}
						$filter=isset($ilance->GPC["filterby"])?$ilance->GPC["filterby"]:'';				
						$filtervalue=isset($ilance->GPC["filtervalue"])?$ilance->GPC["filtervalue"]:'';				
						$prof2 = print_pagnation($number, 10, $ilance->GPC['page'], $counter, $scriptpageprevnext);
										
     $pprint_array = array('filtervalue','number','item_id','amount_val','datesold','seller_id','seller_name','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','coin_id1','consign_id1','$seller_name1','$title1','$pcgs_no1','dt1','amount1','prof2');
			
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'search_coin.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('item_won_aff_list','search_coin'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
		}	
		
// sekar works on items highlite listed on may10
		
 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'itembold_list')
 {
           
			switch($ilance->GPC['action'])
				  {
				/*  case 'current_bid':
				  
                 
					if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
						{
						  $listing =  '<td width="12%"><a href="listings.php?cmd=itembold_list&sort=12&action=current_bid" title="Sort by Current Bids" style="text-decoration:none">Current Price</a></td> 
						  <td width="12%"><a href="listings.php?cmd=itembold_list&sort=22&action=bid" title="Sort by Bids" style="text-decoration:none">Bids</a></td>
						 ';
						 }
						 
						 else
						 {
						 $listing =  '<td width="12%"><a href="listings.php?cmd=itembold_list&sort=11&action=current_bid" title="Sort by Current Bids" style="text-decoration:none">Current Price</a></td> 
						  <td width="12%"><a href="listings.php?cmd=itembold_list&sort=22&action=bid" title="Sort by Bids" style="text-decoration:none">Bids</a></td>
						 ';
						 }  
						 
						break;*/
						
						case 'bid':
				  
                 
					if($ilance->GPC['sort']!='22' && $ilance->GPC['sort']!='')
						{
						  $listing =  '
						  <td width="12%"><a href="listings.php?cmd=itembold_list&sort=22&action=bid" title="Sort by Bids" style="text-decoration:none">Bids</a></td>
						 ';
						 }
						 
						 else
						 {
						 $listing =  '
						  <td width="12%"><a href="listings.php?cmd=itembold_list&sort=21&action=bid" title="Sort by Bids" style="text-decoration:none">Bids</a></td>
						 ';
						 }  
						 
						break;
						
						default:
						
						  $listing =  ' 
						  <td width="12%"> <a href="listings.php?cmd=itembold_list&sort=22&action=bid" title="Sort by Bids" style="text-decoration:none">Bids</a></td>
						 ';
						 }
/*if ($ilance->GPC['sort']=='12') 
{
$order ="ORDER BY bidamount  DESC";
}
else if ($ilance->GPC['sort']=='11') 
{
$order ="ORDER BY bidamount ASC";
}  */
 if ($ilance->GPC['sort']=='22') 
{
$orderby ="ORDER BY bids  DESC";
}
else
{
$orderby ="ORDER BY bids ASC";
}  
		   $bold_list=$ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "projects p 
										left join  " . DB_PREFIX . "users u on  u.user_id=p.user_id and u.status='active'
									    WHERE  p.status = 'open' 
										
										$orderby
										"  );
		   
		   
		     
			 
			                        if($ilance->db->num_rows($bold_list) > 0)
										{
										     while($values_list=$ilance->db->fetch_array($bold_list))
										       {
											     $project_list['itemid'] = $values_list['project_id'];
											    
												 $project_list['seller_name']=fetch_user('username',$values_list['user_id']);
												
												 $project_list['title'] = $values_list['project_title'];
												 
												  $project_list['bold'] = $values_list['bold'];
												  
												  $project_list['highlite'] = $values_list['highlite'];
												  $project_list['featured'] = $values_list['featured'];
												   $project_list['hotlists'] = $values_list['hotlists'];
												  
												 
												 $date_coin = explode('-',$values_list['date_end']);
										         $dt = explode(' ',$date_coin['2']);	
									             $month_name = date( 'M', strtotime(date("Y-m-d", strtotime($values_list['date_end']))) );
										         $project_list['dt1'] = $dt[0].'-'.$month_name.'-'.$date_coin[0]; 
												 
												 $project_list['noofbids'] = $values_list['bids'];
								 
									
									   $price_list=$ilance->db->query("
										SELECT MAX(bidamount) AS maxbidamount
										FROM " . DB_PREFIX . "project_bids 
										WHERE project_id = '".$values_list['project_id']."' 
										
										GROUP BY project_id
										" , 0, null, __FILE__, __LINE__ );	
												
												
										   $curent_list=$ilance->db->fetch_array($price_list);	
										   
										    $currentprice = $curent_list['maxbidamount'];
											 	$project_list['currentprice'] = $ilance->currency->format($currentprice); 
												
												
												if ($project_list['bold'])
												{
												$project_list['bold'] = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:underline"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="favourite_'.$project_list['itemid'].'" onclick="showfav_bold('.$project_list['itemid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
												<span>
												<img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif" />
												<strong>Click to Remove Bold</strong> 
												</span>
												</a>';
												}
												else
												{
												$project_list['bold'] = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:underline"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="favourite_'.$project_list['itemid'].'" onclick="showfav_bold('.$project_list['itemid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
                                             <span>
                                                <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
                                                <strong>Click to Add Bold</strong> 
                                             </span>
                                            </a>';


												}	



												if ($project_list['highlite'])
												{
												$project_list['highlite'] = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:underline"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="highlite_'.$project_list['itemid'].'" onclick="show_highlite('.$project_list['itemid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
												<span>
												<img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif" />
												<strong>Click to Remove Highlite</strong>';
												}
												else
												{
												$project_list['highlite'] = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:underline"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="highlite_'.$project_list['itemid'].'" onclick="show_highlite('.$project_list['itemid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
                                             <span>
                                                <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
                                                <strong>Click to Add Highlite</strong> 
                                             </span>
                                            </a>';
												}	



												if ($project_list['featured'])
												{
												$project_list['featured'] = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:underline"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="featured_'.$project_list['itemid'].'" onclick="show_featured('.$project_list['itemid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
												<span>
												<img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif" />
												<strong>Click to Remove Featured</strong>';
												}
												else
												{
												$project_list['featured'] = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:underline"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="featured_'.$project_list['itemid'].'" onclick="show_featured('.$project_list['itemid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
                                             <span>
                                                <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
                                                <strong>Click to Add Featured</strong> 
                                             </span>
                                            </a>';
												}	

												if ($project_list['hotlists'] >0)
												{
												$project_list['hotlists'] = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:underline"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="hotlist_'.$project_list['itemid'].'" onclick="show_hotlist('.$project_list['itemid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
												<span>
												<img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif" />
												<strong>Click to Remove Hot list</strong>';
												}
												else
												{
												$project_list['hotlists'] = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:underline"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="hotlist_'.$project_list['itemid'].'" onclick="show_hotlist('.$project_list['itemid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
                                             <span>
                                                <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
                                                <strong>Click to Add Hot list</strong> 
                                             </span>
                                            </a>';
												}							
									
												
												 $project_list['check'] = '<input type="checkbox" name="check_enhance[]" value="'.$project_list['itemid'].'"/>';	
										  $drop_bold= '<select name="accList[]" multiple="multiple">
							    <option value="1">bold</option>
								<option value="2">Highlight</option>
								<option value="3">featured</option>
								<option value="4">hotlists</option>
							   </select>';	
												    $highlitedlist[] = $project_list;
												
											   }
										}	   
		   
		   
		   
		   
		   
		   
		   
 
      $pprint_array = array('itemid','seller_name','title','dt1','noofbids','currentprice','bold','highlite','listing','drop_bold','ilanceversion','login_include_admin');
			
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'boldlist.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('item_won_aff_list','highlitedlist'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
 }		
		
//sekar finished on may10				
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bold_hit')
 {
		  
		 $listdrop = $ilance->GPC['accList'];
		$ccc = count($listdrop);
		 //print_r($listdrop);
		  
		$id_con=$ilance->GPC['check_enhance'];
		$id_consign = count($id_con);
		 
		 
		  for($i=0; $i<($id_consign); $i++)
		  {
		          for($j=0; $j<$ccc; $j++)
                   {
				   $bol =$listdrop[$j];
			
			  if($bol == '1')
			    {
				
			     $upd = $ilance->db->query("update " . DB_PREFIX . "projects SET bold = '1'	
										    
										WHERE project_id = '".$id_con[$i]."'");
			    }
		     if($bol == '2')
			    {
				
			     $upd = $ilance->db->query("update " . DB_PREFIX . "projects SET highlite = '1'	
										    
										WHERE project_id = '".$id_con[$i]."'");
			    }
		
			
			  if($bol == '3')
			    {
				
			     $upd = $ilance->db->query("update " . DB_PREFIX . "projects SET featured = '1'	
										    
										WHERE project_id = '".$id_con[$i]."'", 0, null, __FILE__, __LINE__);
			    }

			    if($bol == '4')
			    {

			     $upd = $ilance->db->query("update " . DB_PREFIX . "projects SET hotlists = '1'	
										    
										WHERE project_id = '".$id_con[$i]."'", 0, null, __FILE__, __LINE__);
			    }
		   }
			
		  }
		  print_action_success('updated successfuly','listings.php?cmd=itembold_list');
								exit();
		  
}  
   //item won listing
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'item_won')
   {	
   	$counter = ($ilance->GPC['page'] - 1) * 10;
							
							 $scriptpageprevnext = $ilpage['listings']. '?cmd=item_won';
							 
							 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
							 {
								$ilance->GPC['page'] = 1;
							 }
							 else
							 {
								$ilance->GPC['page'] = intval($ilance->GPC['page']);
							 }
		$query_solds = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects
										WHERE haswinner OR hasbuynowwinner
										GROUP BY project_id asc LIMIT " . (($ilance->GPC['page'] - 1) * 10) . "," .'10'."", 0, null, __FILE__, __LINE__);			   
							   if($ilance->db->num_rows($query_solds) > 0)
								{
								$query_solds_count = 0;
								
								$query_solds1 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects
										WHERE haswinner OR hasbuynowwinner
										");	
										
										 $number = (int)$ilance->db->num_rows($query_solds1);
								
								while($row_list_sold = $ilance->db->fetch_array($query_solds))
								{
							 
								$row_list_sold['Seller'] = fetch_user('username',$row_list_sold['user_id']);
								
								if($row_list_sold['winner_user_id'] != '0')
								{
									$selectbid = $ilance->db->query("SELECT bidamount FROM " . DB_PREFIX . "project_bids
																	WHERE project_id = '".$row_list_sold['project_id']."'
																	AND user_id = '".$row_list_sold['winner_user_id']."'
																	AND project_user_id = '".$row_list_sold['user_id']."'
																	AND bidstatus = 'awarded' ", 0, null, __FILE__, __LINE__);
									if($ilance->db->num_rows($selectbid) > 0)
									{
										$resbid = $ilance->db->fetch_array($selectbid);
										$bidamount = $resbid['bidamount'];
									}
								$row_list_sold['Buyer']  = fetch_user('username',$row_list_sold['winner_user_id']);
								$s_user = $row_list_sold['winner_user_id'];
								$row_list_sold['Amount'] = $ilance->currency->format($bidamount + $row_list_sold['buyershipcost']);								
								$row_list_sold['SoldDate'] = date('m-d-Y',strtotime($row_list_sold['date_end']));
								}
								else
								{
									$selectbuy = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "buynow_orders
																	WHERE project_id = '".$row_list_sold['project_id']."'
																	");
									if($ilance->db->num_rows($selectbuy) > 0)
									{
										$resbin = $ilance->db->fetch_array($selectbuy);										
										$buyer = $resbin['buyer_id'];
									}
								$s_user = $row_list_sold['buyer_id'];
								$row_list_sold['Buyer']  = fetch_user('username',$buyer);
								$row_list_sold['Amount'] = $ilance->currency->format($row_list_sold['buynow_price'] + $row_list_sold['buyershipcost']);	
								$row_list_sold['SoldDate'] = date('m-d-Y',strtotime($resbin['orderdate']));							
								}
										
								//$ship_id_link = fetch_user_shipment_new('project_id',$row_list_sold['user_id']);
								$ship_id_link=$row_list_sold['project_id'];
								
								 $ship_id_pro  =  fetch_user_shipment_click('item_id',$row_list_sold['project_id']);
							 
								 if($row_list_sold['project_id'] == $ship_id_pro)
								 {
								 $row_list_sold['ship_id_link'] = '<strong>'.fetch_user_shipment_click('ship_id',$row_list_sold['project_id']).'<strong>';
								 }
								 else
								 {
								 $row_list_sold['ship_id_link'] = '<span class="blue" style="cursor:pointer;"><a href="shipping.php?item_id='.$ship_id_link.'&buyer_id='.$s_user.'&seller_id='.$row_list_sold['user_id'].'" >Create  Shipment</a></span>';
								 }
								 			
								$row_list_sold['consignlist'] = fetch_user_consign('consignid',$row_list_sold['user_id']);
								$row_list_sold['CancelSale'] = '<span class="blue" style="cursor:pointer;">
								<a href="listings.php?cmd=list_canel&item_id='.$row_list_sold['project_id'].'&buyer_id='.$s_user.'&seller_id='.$row_list_sold['user_id'].'&datesale='.$row_list_sold['SoldDate'].'&amount='.$row_list_sold['Amount'].'">Cancel</a></span>';
								
							 
								$item_won_list[] = $row_list_sold;
								
								
								$query_solds_count++;
								}
								}
								else
								{				
								$show['no'] = 'won_list';
								}
								
								
							    //affiliate listing all 
								$aff_query_solds = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "affiliate_buyer", 0, null, __FILE__, __LINE__);
											   
							    if($ilance->db->num_rows($aff_query_solds) > 0)
								{
								$aff_query_solds_count = 0;
								while($aff_row_list_sold = $ilance->db->fetch_array($aff_query_solds))
								{
							    $aff_row_list_sold['sel_aff']  =  fetch_user('username',$aff_row_list_sold['seller_id']);
							    $aff_row_list_sold['buy_aff']  =  fetch_user('username',$aff_row_list_sold['buyer_id']);
								$aff_row_list_sold['sit_aff']  =  fetch_user_siteid('site_name',$aff_row_list_sold['Site_Id']);
								$aff_row_list_sold['con_aff']  =  fetch_user_consign('consignid',$aff_row_list_sold['seller_id']);
								
								$ship_id_pro_aff  =  fetch_user_shipment_click_aff('coin_id',$aff_row_list_sold['coin_id']);
							 
								 if($aff_row_list_sold['coin_id'] == $ship_id_pro_aff)
								 {
								 $aff_row_list_sold['ship_id_link_aff'] = '<strong>'.fetch_user_shipment_click_aff('ship_id',$aff_row_list_sold['coin_id']).'<strong>';
								 }
								 else
								 {
								 $aff_row_list_sold['ship_id_link_aff'] = '<span class="blue" style="cursor:pointer;"><a href="shipping.php?coin_id='.$aff_row_list_sold['coin_id'].'&buyer_id='.$aff_row_list_sold['buyer_id'].'&seller_id='.$aff_row_list_sold['seller_id'].'" >Create  Shipment</a></span>';
								 }
								 
								$item_won_aff_list[] = $aff_row_list_sold;
								
								
								$aff_query_solds_count++;
								}
								}
								else
								{				
								$show['no'] = 'won_aff_list';
								}		
									
	$item_won_pagnation = print_pagnation($number, 10, $ilance->GPC['page'], $counter, $scriptpageprevnext);
	
		  $pprint_array = array('site_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','item_won_pagnation');
			
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'item_won.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('item_won_list','item_won_aff_list'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	//image list
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'image_list')
   {
        //deleted image 
		 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'image_list_deleted')
         {
		 
		
		 
		  if($ilance->GPC['attach'] == '')
	      {
	      print_action_success('sorry atleast select one checkbox', $ilpage['listings'].'?cmd=image_list');
										exit();
	      }
		  
		  else
		  {
		      $att_id = $ilance->GPC['attach'];
			  
			  for($k=0;$k<count($att_id);$k++)
			  {
			  
			/*echo  "
														
														DELETE FROM " . DB_PREFIX . "attachment
				                                        WHERE attachid = '" . $att_id[$k] . "'
														
														";*/
			   $sqlusercheck_delete = $ilance->db->query("
														
														DELETE FROM " . DB_PREFIX . "attachment
				                                        WHERE attachid = '" . $att_id[$k] . "'
														
														", 0, null, __FILE__, __LINE__);
			     
			  
			  }
			  
		      print_action_success('Successfully deleted Images', $ilpage['listedarea'].'?cmd=image_list');
                        exit();
		  }
		
		 
		 
		 }
       
                 //counter for page 
                 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
				 $scriptpageprevnext = $ilpage['listedarea'] . '?cmd=image_list';
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
				 
				 
			    $image_sql = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "attachment
				GROUP BY coin_id ORDER BY coin_id desc LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']."");
				
				$image_sql2 = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "attachment
				GROUP BY coin_id", 0, null, __FILE__, __LINE__);
				
				//table start
				$table_row = '<table width="100%" cellspacing="0" cellpadding="6" border="0" class=""><tr>';
				
				if($ilance->db->num_rows($image_sql) > 0)
				{
				     $number = (int)$ilance->db->num_rows($image_sql2);
					 while ($res_atc1 = $ilance->db->fetch_array($image_sql))
					 {
					    
							$image_sql1 = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "attachment
							WHERE coin_id='".$res_atc1['coin_id']."'
							 ORDER BY coin_id,cast(SUBSTR(filename from LOCATE('-',filename)+1 for LOCATE('.',filename)-LOCATE('-',filename)-1) as UNSIGNED) 
							");
						
						    $image_atc_total = $ilance->db->query("
							 SELECT count(coin_id) as end
							 FROM " . DB_PREFIX . "attachment 
							 WHERE coin_id='".$res_atc1['coin_id']."'
							 ORDER BY coin_id,cast(SUBSTR(filename from LOCATE('-',filename)+1 for LOCATE('.',filename)-LOCATE('-',filename)-1) as UNSIGNED)
							 ");
							 
						    $res_total= $ilance->db->fetch_array($image_atc_total);
						
						    //count by divide
						    $count_tol = $res_total['end'];
						
						    $cou = 1;		    
						 while ($res_atc = $ilance->db->fetch_array($image_sql1))
					     {	
							
					          $table_row.= '<tr><td nowrap="nowrap"><input type="checkbox" value="'.$res_atc['attachid'].'" name="attach[]"> '.$res_atc['filename'].' -- '.$res_atc['attachtype'].'</td></tr>';
							  
							  $txt =  (fetch_coin_table('project_id',$res_atc['coin_id']) == '0') ? 'Holdings' : 'Listings';
					   
					          if($cou == $count_tol)
							   {
							   $table_row.= '<tr><td nowrap="nowrap"><strong>Coin ID : </strong>'.$res_atc['coin_id'].'</td><td nowrap="nowrap"><strong>Consignor Name :</strong> '.fetch_user('username',fetch_coin_table('user_id',$res_atc['coin_id'])).'</td></tr><tr><td nowrap="nowrap"><strong>Title :</strong> '.fetch_coin_table('Title',$res_atc['coin_id']).'</td></tr><tr><td nowrap="nowrap"><strong>Min Bid/Buy Now :</strong> '.$ilance->currency->format(fetch_coin_table('Minimum_bid',$res_atc['coin_id'])).' / '.$ilance->currency->format(fetch_coin_table('Buy_it_now',$res_atc['coin_id'])).'</td></tr><tr><td nowrap="nowrap"><strong>Live on website : </strong>'.$txt.'</td></tr><tr class="alt1" valign="top"><td nowrap="nowrap"></td><td nowrap="nowrap"></td></tr>';
							   }
								     
							
									
								
								$cou++;
						 }
							
					}
						
					
				}
				else
				{
				 $table_row = '<tr><td nowrap="nowrap">No Result Found</td></tr>';
				}
				
				$prof = '';
				$table_row.= '</table>';
				
				//pagination
				$prof = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
			
	   $pprint_array = array('prof','table_row','date_down','site_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'image_area.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('image_list_loop'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
   
   }	
   
   //all cancel list
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'all_list_can')
   {            //list by buyer cancelled
				$con_listing = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "cancel_sale 
										");
							            $number = (int)$ilance->db->num_rows($con_listing);
							            if($ilance->db->num_rows($con_listing) > 0)
										{
										$row_con_list = 0;
										while($row_list = $ilance->db->fetch_array($con_listing))
										{	
												
										$date_coin = explode('-',$row_list['sold_date']);
										$dt = explode(' ',$date_coin['2']);	
									    $month_name = date( 'M', strtotime(date("Y-m-d", strtotime($row_list['sold_date'])) ));
										$row_list['date_sent'] = $dt[0].'-'.$month_name.'-'.$date_coin[0]; 
										$row_list['sel'] =  fetch_user('username',$row_list['seller_id']);
										$row_list['buy'] =   fetch_user('username',$row_list['buyer_id']);
										if($row_list['gc'] == '0')
										{
										$row_list['tover'] = 'Buyer'; 
										}
										else
										{
										$row_list['tover'] = 'GC';
										}
										$cancel_sale_list[] = $row_list;
										$row_con_list++;
										
										}
										
						                }
										
										else
										{				
										$show['no'] = 'cancel_list';
								     	}
     $pprint_array = array('number','item_id','amount_val','datesold','seller_id','seller_name','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'all_canel_list.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('item_won_list','cancel_sale_list'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();}	
   else 
   { 
                            //list of all certified coin 
	      //coin_id, user_id, pcgs, Title, Description, Grading_Service, Grade, Quantity, Max_Quantity_Purchase, Certification_No, Condition_Attribute, Cac, Star, Plus, Coin_Series, Pedigee, Site_Id, Minimum_bid, Reserve_Price, Buy_it_now, End_Date, Alternate_inventory_No, Category, Other_information, consignid, coin_listed, Service_Level, final_fee_percentage, final_fee_min, listing_fee, referal_id, project_id, status, export
		  
		  //counter for page 
		  
		  //karthik start apr 20
				
                 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
				 $scriptpageprevnext = $ilpage['listedarea']. '?cmd=holding_area_list';
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
				 
				 
										$con_listing = $ilance->db->query("
										SELECT c.*,at.attachid,u.username,a.site_name,c.coin_id,c.Site_Id,ea.attach_id as ebayimg,at.attachid as gcimg
										FROM " . DB_PREFIX . "coins c 
										left join " . DB_PREFIX . "users u on u.user_id=c.user_id 
										left join " . DB_PREFIX . "attachment at on at.project_id=c.coin_id and at.attachtype ='itemphoto'
										left join " . DB_PREFIX . "ebay_images ea on ea.coin_id=c.coin_id and ea.attach_id = at.attachid
										left join " . DB_PREFIX . "affiliate_listing a on a.id=c.Site_Id WHERE c.coin_listed = 'c' AND c.End_Date != '0000-00-00' AND c.pending !='1' AND c.project_id = '0' AND c.status = '0' 
										GROUP BY c.coin_id  asc LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."", 0, null, __FILE__, __LINE__);
							            
							            if($ilance->db->num_rows($con_listing) > 0)
										{
										$row_con_list = 0;
										$con_listing1 = $ilance->db->query("
										SELECT c.*,u.username,a.site_name
										FROM " . DB_PREFIX . "coins c
										left join " . DB_PREFIX . "users u on u.user_id=c.user_id
										left join " . DB_PREFIX . "affiliate_listing a on a.id=c.Site_Id
										WHERE c.coin_listed = 'c'
										AND c.End_Date != '0000-00-00'
										AND c.pending !='1'
										AND c.project_id  = '0'
										AND c.status = '0' 
										", 0, null, __FILE__, __LINE__);
										$number = (int)$ilance->db->num_rows($con_listing1);
										while($row_list = $ilance->db->fetch_array($con_listing))
										{	
												
										$date_coin = explode('-',$row_list['End_Date']);
										
										 $date_day = explode(' ',$date_coin[2]);
									     $month_name = date( 'M', strtotime(date("Y-m-d", strtotime($row_list['End_Date']))) );
										
										$row_list['checkval'] = '<input type="checkbox" name="val[]" id="my" value="'.$row_list['coin_id'].'" onclick="return myself(this.value);" >';     
										$row_list['coin_id'] = $row_list['coin_id'];
										if(($row_list['Site_Id']) == '1' )
										{
										if($row_list['ebayimg'] != '')
										{
										$row_list['gcimg'] = 'YES LIVE IN EBAY';
										}
										else
										{
										$row_list['gcimg'] = 'NO';
										}
										}
										else
										{
										if($row_list['gcimg'] != '')
										{
										$row_list['gcimg'] = 'YES';
										}
										else
										{
										$row_list['gcimg'] = 'NO';
										}
										}
										$row_list['consignid'] = $row_list['consignid']; 
										$row_list['userid'] = $row_list['user_id']; 
										$row_list['user'] = $row_list['username']; 
										$row_list['date_sent'] = $date_day[0].'-'.$month_name.'-'.$date_coin[0]; 
										$row_list['cointitle'] = $row_list['Title'];
										$row_list['pcgsno'] = $row_list['pcgs'];  
										$row_list['listin'] = $row_list['site_name'];
										$row_list['return_con'] = '<span class="blue"><a href="listings.php?cmd=list_return_user&consignid='.$row_list['consignid'].'&user_id='.$row_list['user_id'].'&coin_id='.$row_list['coin_id'].'">Return</a></span>';
										
										if($row_list['Minimum_bid'] == '')
										$row_list['deld'] = '<span class="blue"><a href="settings.php?cmd=dailydeals&coin_id='.$row_list['coin_id'].'">DailyDeal</a></span>';
										else
										$row_list['deld'] = '<span class="blue">No Deal</span>';
										
										$holding_area_list[] = $row_list;
										$row_con_list++;
										
										}
										
						                }
										
										else
										{				
										$show['no'] = 'holding';
								     	}
										
										//end date dropdown list
										$date_down = '<select name="date_down" id="date_down" >
								              <option value="" selected="selected">Select</option>';
											  
										$con_date = $ilance->db->query("
										SELECT COUNT(*) AS endcount,date(End_Date) as End_Date
										FROM " . DB_PREFIX . "coins 
										WHERE coin_listed = 'c'
										AND End_Date != '0000-00-00'
										AND pending !='1'
										AND project_id  = '0'
										AND status = '0' 
										GROUP BY date(End_Date)
										", 0, null, __FILE__, __LINE__);
							            
							            if($ilance->db->num_rows($con_date) > 0)
										{
										        while($res_date = $ilance->db->fetch_array($con_date))
												{
												
												  $date_coin = explode('-',$res_date['End_Date']);
										          $date_day = explode(' ',$date_coin[2]);
									              $month_name = $date_day[0].'-'.$date_coin[1].'-'.$date_coin[0]; 
												  $month_namev = $date_coin[0].'-'.$date_coin[1].'-'.$date_day[0];
												  $item_count = $res_date['endcount'];
												  $date_down.='<option value="'.$month_namev.'">'.$month_name.' <b>('.$item_count.' items)</b></option>';
												}
										}
										
										$date_down.='</select>';
										
								// site id
								$site_id = '<select name="site_id" id="site_id" onchange="set_ebay_titles();">
								<option value="0" selected="selected">Listed in GC</option>';
						  
									 $sqlcat_siteid = $ilance->db->query("
						                                            SELECT *
						                                            FROM " . DB_PREFIX . "affiliate_listing
						                                            ", 0, null, __FILE__, __LINE__);
										 if ($ilance->db->num_rows($sqlcat_siteid) > 0)
										 {         
											
												while ($rescat_sid = $ilance->db->fetch_array($sqlcat_siteid))
												{
												$site_id.='<option value="'.$rescat_sid['id'].'">Listed in '.$rescat_sid['site_name'].'</option>';	
												$site_id.='<option value="-'.$rescat_sid['id'].'">Export to '.$rescat_sid['site_name'].'</option>';
												}
												
										}
					
						      $site_id.='</select>';	
				
				
							  $show['holding_first_page']= '1';
							  $prof1 = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);	
     
	$pprint_array = array('prof1','date_down','site_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'holding_area.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('holding_area_list'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
 }
 
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}


//vijay work for multiple image upload 

function post_images_to_ebay($coin_id)
{
global $ilance;
$con_listifng_gc = $ilance->db->query("
								SELECT  *
								FROM " . DB_PREFIX . "coins 
								WHERE coin_listed = 'c'
								AND coin_id  = '".$coin_id."'
								AND Site_Id = '1'
								", 0, null, __FILE__, __LINE__);
								if($ilance->db->num_rows($con_listifng_gc) > 0)
								{
								
									while($row_lisst_gc = $ilance->db->fetch_array($con_listifng_gc))
									{
									
								
									
										$query1 = $ilance->db->query("SELECT attachid,project_id,filehash,filename,attachtype FROM " . DB_PREFIX . "attachment  WHERE project_id = '".$coin_id."'", 0, null, __FILE__, __LINE__);
										if ($ilance->db->num_rows($query1) > 0)
										{    

										//$ebayimageflag =false;
										while ($res = $ilance->db->fetch_array($query1, DB_ASSOC))
										{
										$project_id= $res['project_id'];
										$filehash= $res['filehash'];
										$filename=$res['filename'];
										$DIR_AUCTION_ATTACHMENT= '/home/gc/public_html/uploads/attachments/auctions/';
										$file_path=$DIR_AUCTION_ATTACHMENT.floor($project_id/100).'00/'.  $project_id . '/' . $filehash . '.attach';

										if(file_exists($file_path))
										{
										$project_id= $res['project_id'];
										$filehash= $res['filehash'];
										$filename=$res['filename'];


										 // $imageuplchk = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "ebay_images WHERE coin_id = '".$coin_id."' group by attach_id", 0, null, __FILE__, __LINE__);

										// if ($ilance->db->num_rows($imageuplchk) == 0)
										// {
										//$full_url=post_image_ebay($file_path,$filename);

										// $sql="INSERT INTO " . DB_PREFIX . "ebay_images (coin_id, attach_id, filename, ebay_url, ebay_id,attachtype,upload_date) VALUES ('".$project_id."', '".$res['attachid']."', '".$filename."', '".$full_url."','1','".$res['attachtype']."',NOW())";

										// $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
										// //	$ebayimageflag =true;
										//	}

										}
										else
										{
										print_action_failed('file Not Found !,<b>'.$file_path.'</b>', $ilpage['listings']);
										exit();

										return false;
										}
										}
										// if($ebayimageflag == false)
										// {
										// //echo '<strong>Ebay Image is ALready Exists</strong></br><br>';
										// }

										return true;
										}else
										{
										return false;
										}
									
									}
									
								}
							

//ilance_ebay_images
}

function post_image_ebay($file_path,$filename)
{		
	$devID = '0ad39d96-573f-47e7-9345-1cffd412549e';   // these prod keys are different from sandbox keys
    $appID = 'herakle26-0679-4e6f-9155-39794874fde';
    $certID = 'd32b96ca-b01e-407a-8575-7b0e44d76dc1';
    //the token representing the eBay user to assign the call with
    $userToken = 'AgAAAA**AQAAAA**aAAAAA**wNuAUw**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wJkoKjCpmKpA+dj6x9nY+seQ**1OwBAA**AAMAAA**4bmRLHRSN7fp+zQHHvTGptnBWwI1Stnwv2mqCFLMSKSwuoiTxr0KrpUPXIY5nPomb/5VcltYsN8jPQg5+CX9kiJNt0aRLXLpn0F2wZbI95FQtMBchNXNpHCp31OfzZJMNA1U3NNcyNwXJsHRdHPlWnUnUkaA+JlulxhWxe2sCrS8f0yJpLpE+FWFFFXUYKb3lkvoIGwbuuAH7gnlwrgAFHJoGmNjI/YFPkVFwbBNW5zp9ytza+tDs6gBWndel8C1aG3glY735k+obFfyXFV67ZMPyS/NjUPJ4Hr/ioTnE5liyD0g6473NBH7zNJKfcgzjcteQNlJa6Q5QUE0IhHqVw3KT7I94HptJjTOQEp7Z8A5w3u0mZJUCfk3yL4DwlTh/r65d+hnmYoJez/YH1nxpK3AOcNyRw4fr/o0Zze/eNHGAzjW6HA/wxw8lhO1pRm2jQXXH9tMerhwMwI9bI/ipscRNSyP5gxkgEYlFAxNi+6xYBSNKNMXAI9Re/KpLoB5XKvXm/hfCnwcIYNfhvDesYckq3fhbA7SO8lTuaOCT1QxwhZ6yQDXDYjHVpwKTQ1JwYAVSSZSh6JLULLvi7n6/CB91SyzeOEMV/kC9bQw/NgV+2dRTJ0jXtlj6GmsH+K81DMhRRX/7LuDglp7+LwB6df481oq1TI6ZKgnMnCnfwsF8iIQgrxZzlvCIj+TxchS7a/yjdtIi7Jhz7s+YIV1tUtJ/3EAp9FWEjQgcH2ylc166d4qTS2XyWNl/Lh/8dIY';                 

		
   
    $siteID  = 0;                            // siteID needed in request - US=0, UK=3, DE=77...
    $verb    = 'UploadSiteHostedPictures';   // the call being made:
    $version = 517;                          // eBay API version
    
	$file=$file_path;

    $picNameIn = $filename;
    $handle = fopen($file,'r');         // do a binary read of image
    $multiPartImageData = fread($handle,filesize($file));
    fclose($handle);

    ///Build the request XML request which is first part of multi-part POST
    $xmlReq = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    $xmlReq .= '<' . $verb . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
    $xmlReq .= "<Version>$version</Version>\n";
    $xmlReq .= "<PictureName>$picNameIn</PictureName>\n";    
    $xmlReq .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>\n";
    $xmlReq .= '</' . $verb . 'Request>';

    $boundary = "MIME_boundary";
    $CRLF = "\r\n";
    
    // The complete POST consists of an XML request plus the binary image separated by boundaries
    $firstPart   = '';
    $firstPart  .= "--" . $boundary . $CRLF;
    $firstPart  .= 'Content-Disposition: form-data; name="XML Payload"' . $CRLF;
    $firstPart  .= 'Content-Type: text/xml;charset=utf-8' . $CRLF . $CRLF;
    $firstPart  .= $xmlReq;
    $firstPart  .= $CRLF;
    
    $secondPart = "--" . $boundary . $CRLF;
    $secondPart .= 'Content-Disposition: form-data; name="dummy"; filename="dummy"' . $CRLF;
    $secondPart .= "Content-Transfer-Encoding: binary" . $CRLF;
    $secondPart .= "Content-Type: application/octet-stream" . $CRLF . $CRLF;
    $secondPart .= $multiPartImageData;
    $secondPart .= $CRLF;
    $secondPart .= "--" . $boundary . "--" . $CRLF;
    
    $fullPost = $firstPart . $secondPart;
    
    // Create a new eBay session (defined below) 
    $session = new eBaySession1($userToken, $devID, $appID, $certID, false, $version, $siteID, $verb, $boundary);

    $respXmlStr = $session->sendHttpRequest($fullPost);   // send multi-part request and get string XML response
    
    if(stristr($respXmlStr, 'HTTP 404') || $respXmlStr == '')
        die('<P>Error sending request');
        
    $respXmlObj = simplexml_load_string($respXmlStr);    
	$ack        = $respXmlObj->Ack;
    $picNameOut = $respXmlObj->SiteHostedPictureDetails->PictureName;
    $picURL     = $respXmlObj->SiteHostedPictureDetails->FullURL;
	$PictureSetMember=$respXmlObj->SiteHostedPictureDetails->PictureSetMember;
	/*
	//$hosted_image['PictureSetMember']=$PictureSetMember;
	print_r($respXmlObj);
	print "<P>Picture Upload Outcome : $ack </P>\n";
			print "<P>picNameOut = $picNameOut </P>\n";
			print "<P>picURL = $picURL</P>\n";
			print "<IMG SRC=\"$picURL\">";*/
			return $picURL;

	//return $hosted_image;

}
	
	class eBaySession1
{
	private $requestToken;
	private $devID;
	private $appID;
	private $certID;
	private $serverUrl;
	private $compatLevel;
	private $siteID;
	private $verb;
    private $boundary;

	public function __construct($userRequestToken, $developerID, $applicationID, $certificateID, $useTestServer,
								$compatabilityLevel, $siteToUseID, $callName, $boundary)
	{
	    $this->requestToken = $userRequestToken;
	    $this->devID = $developerID;
            $this->appID = $applicationID;
	    $this->certID = $certificateID;
	    $this->compatLevel = $compatabilityLevel;
	    $this->siteID = $siteToUseID;
	    $this->verb = $callName;
            $this->boundary = $boundary;
	    if(!$useTestServer)
		$this->serverUrl = 'https://api.ebay.com/ws/api.dll';
	    else
	        $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
	}
	
	/**	sendHttpRequest
		Sends a HTTP request to the server for this session
		Input:	$requestBody
		Output:	The HTTP Response as a String
	*/
	public function sendHttpRequest($requestBody)
	{        
        $headers = array (
            'Content-Type: multipart/form-data; boundary=' . $this->boundary,
            'Content-Length: ' . strlen($requestBody),
	    'X-EBAY-API-COMPATIBILITY-LEVEL: 517' . $this->compatLevel,  // API version
		
			
	    'X-EBAY-API-DEV-NAME: ' . $this->devID,     //set the keys
	    'X-EBAY-API-APP-NAME: ' . $this->appID,
	    'X-EBAY-API-CERT-NAME: ' . $this->certID,

            'X-EBAY-API-CALL-NAME: ' . $this->verb,		// call to make	
	    'X-EBAY-API-SITEID: ' . $this->siteID,      // US = 0, DE = 77...
        );
	//initialize a CURL session - need CURL library enabled
	$connection = curl_init();
	curl_setopt($connection, CURLOPT_URL, $this->serverUrl);
        curl_setopt($connection, CURLOPT_TIMEOUT, 30 );
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($connection, CURLOPT_POST, 1);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($connection, CURLOPT_FAILONERROR, 0 );
        curl_setopt($connection, CURLOPT_FOLLOWLOCATION, 1 );
        //curl_setopt($connection, CURLOPT_HEADER, 1 );           // Uncomment these for debugging
        //curl_setopt($connection, CURLOPT_VERBOSE, true);        // Display communication with serve
        curl_setopt($connection, CURLOPT_USERAGENT, 'ebatns;xmlstyle;1.0' );
        curl_setopt($connection, CURLOPT_HTTP_VERSION, 1 );       // HTTP version must be 1.0
	$response = curl_exec($connection);
        
        if ( !$response ) {
            print "curl error " . curl_errno($connection ) . "\n";
        }
	curl_close($connection);
	return $response;
    } // function sendHttpRequest
}  // class eBaySession
 

function fetch_image_check($project_id, $type)
	{
		
		global $ilance;
		$sql2="SELECT filehash  FROM " . DB_PREFIX . "attachment WHERE  project_id='".$project_id."' and attachtype='".$type."'"; //itemphoto
		$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($res2)>0)
		{
			while($line2=$ilance->db->fetch_array($res2))
			{
				 return true;
			}
		}else
		{
			return false;
		}
	}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>

