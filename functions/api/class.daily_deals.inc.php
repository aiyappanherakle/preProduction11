<?php 

class daily_deals
{
function list_daily_deal()
{
global $ilance,$ilconfig;

$deal_sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "dailydeal WHERE date(live_date) = '" . DATETODAY . "'");

if ($ilance->db->num_rows($deal_sql) != 0)
{
	
	$deal_end_date = DATETODAY.' 23:59:00';
	$message_not_arr	=array();
	
	while($deal=$ilance->db->fetch_array($deal_sql)){
	
		$message_item_arr[]=$deal['coin_id'];
		$product= $deal['coin_id'];
		
		 $deal_start_date = $deal['live_date'].' 00:00:00';
	
		$sel_pjt = $ilance->db->query("SELECT *,p.project_id FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id WHERE p.project_id = '".$product."' and p.status='open'");
		
		if($ilance->db->num_rows($sel_pjt) > 0)
		{
			
			$row_value = $ilance->db->fetch_array($sel_pjt);
			// Minimum offer buynow
			$minimum_buynowoffer= 5;
			if ($row_value['Buy_it_now'] > $minimum_buynowoffer)
			{
						
			if($deal['offer_type'] == 'dollar')
			{
				 $buynow =  $row_value['Buy_it_now'] - $deal['offer_amt'];
			}
                        
			
			$ilance->db->query("UPDATE " . DB_PREFIX . "projects 
								SET buynow_price = '".$buynow."',currentprice = '".$buynow."',
								date_end = '".$ilance->db->escape_string($deal_end_date)."',
								status='open'
								WHERE project_id = '".$row_value['project_id']."'");
			
			$update_deal = $ilance->db->query("UPDATE  " . DB_PREFIX . "dailydeal SET  project_id = '".$row_value['project_id']."'	
                        WHERE coin_id 	  =  '" .$product. "'");
			}
			else
			{
			$message_not_arr[]=$row_value['project_id'];
			}
		}
		/*else
		{
			//update item quantity to coin table
			
			
			$insert_value = $ilance->db->query("
					SELECT  *
					FROM " . DB_PREFIX . "coins 
					WHERE coin_listed = 'c'
					AND coin_id  = '".$product."'
					AND Site_Id = '0' 
					AND Quantity>0
					");
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
					$kill=array('custom'=>$custom);
					// Murugan Changes On Dec 18 For Category questions End 
					$pro_count = $ilance->db->query("SELECT COUNT(*) AS project_count FROM " . DB_PREFIX . "projects");
					$pro_count_res = $ilance->db->fetch_array($pro_count);	
					$count_set = $pro_count_res['project_count'];		 
					$insert_count = $count_set+1;
					$dataexplode = explode('-', $row_value['End_Date']);
					$date_coin = $dataexplode['0'] .'-'.$dataexplode['1'].'-'.$dataexplode['2'];
					//insert project	
					// murugan changes on jan 25
					$cal = $ilance->db->query("SELECT offer_type, offer_amt FROM ".DB_PREFIX."dailydeal
												WHERE coin_id = '".$product."' ");
						
					$calamt = $ilance->db->fetch_array($cal);
					if($deal['offer_type'] == 'dollar')
					{
						$buynow = $row_value['Buy_it_now'] - $deal['offer_amt'] ;
					}
				
					
					//murugan changes on jan 19
					// #### SHIPPING INFORMATION ###################################
					$shipping1 = array(
						'ship_method' => 'flatrate',
						'ship_packagetype' =>  '',
						'ship_length' => '12',
						'ship_width' =>  '12',
						'ship_height' => '12',
						'ship_weightlbs' =>  '1',
						'ship_weightoz' =>  '0',
						'ship_handlingtime' =>  '3',
						'ship_handlingfee' =>  '0.00'
					);
					
					for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
					{
						$shipping2['ship_options_' . $i] =  '';
						$shipping2['ship_service_' . $i] =  '';
						$shipping2['ship_fee_' . $i] =  '0.00';
						$shipping2['freeshipping_' . $i] =  '0';
						$shipping2['ship_options_custom_region_' . $i] =  array();
					}
					
					$shipping = array_merge($shipping1, $shipping2);
					
					unset($shipping1, $shipping2);								
					
					$checkin = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE project_id = '".$row_value['coin_id']."'");
					if($ilance->db->num_rows($checkin) > 0)
					{
						$relistend = $ilance->db->fetch_array($checkin);
						$ilance->db->query("update " . DB_PREFIX . "coin_relist set  
											enddate ='".$date_coin."',
											startbydate ='".DATETIME24H."'
											where user_id='".$row_value['user_id']."' and coin_id='".$row_value['coin_id']."' and date(actual_end_date)=date('".$relistend['date_end']."')");
						if($ilance->db->affected_rows() == 0)
						{
							
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
										
							$ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_relist
															(id, coin_id, enddate, startbydate, user_id, actual_end_date, filtered_auctiontype)
															VALUES (
															NULL,
															'".$row_value['coin_id']."',
															'".$row_value['End_Date']."',
															'".DATETIME24H."',
															'".$row_value['user_id']."',
															'".$relistend['date_end']."',
															'".$autiontype."'
															)");
							$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects
																					   WHERE project_id = '" . intval($row_value['coin_id']) . "'");
						}
					}
					
					//$order_nos = fetch_cat('Orderno',$row_value['pcgs']); 
				
					$sql2="SELECT Orderno,coin_series_denomination_no,coin_series_unique_no,coin_detail_year,coin_detail_mintmark FROM " . DB_PREFIX . "catalog_coin WHERE PCGS = '" . $row_value['pcgs'] . "'";
					$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
					if($ilance->db->num_rows($res2)>0)
					{

						while($line2=$ilance->db->fetch_array($res2))
						{
							$order_nos = $line2['Orderno']; 
							$denom_no = $line2['coin_series_denomination_no'];
							$series_no = $line2['coin_series_unique_no'];	
							$coin_year =$line2['coin_detail_year'];
							$coin_mintmark =$line2['coin_detail_mintmark'];
						}
					}
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects
                                                                               WHERE project_id = '" . intval($row_value['coin_id']) . "'");
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "projects
												(id, project_id, cid, description, date_added, date_starts, date_end, user_id, visible, project_title, status, project_details, project_type, project_state, buynow, buynow_price, buynow_qty, buynow_purchases,filtered_auctiontype,currentprice,max_qty,alt_no,filter_escrow,Orderno,bold,highlite,featured,coin_series_denomination_no,coin_series_unique_no,norder,pcgs,Grade,Grading_Service,coin_detail_year,mintmark)
												VALUES (
												NULL,
												'".$row_value['coin_id']."',
												'".$row_value['Category']."',
												'".$row_value['Description']."',
												'".DATETIME24H."',
												'".$ilance->db->escape_string($deal_start_date)."',
												'".$ilance->db->escape_string($deal_end_date)."',
												'".$row_value['user_id']."',
												'1',
												'".$row_value['Title']."',
												'open',
												'public',
												'forward',
												'product',
												'1',
												'".$buynow."',
												'".$row_value['Quantity']."',
												'".$row_value['Max_Quantity_Purchase']."',
												'fixed',
												'".$buynow."',
												'".$row_value['Max_Quantity_Purchase']."',
												'".$row_value['Alternate_inventory_No']."',
												'1',
												'".$order_nos."',
												'".$row_value['bold']."',
												'".$row_value['highlite']."',
												'".$row_value['featured']."',
												'".$denom_no."',
												'".$series_no."',
												'".$row_value['norder']."',
												'".$row_value['pcgs']."',
												'".$row_value['Grade']."',
												'".$row_value['Grading_Service']."',
												'".$coin_year."',
												'".$coin_mintmark."'
												)");
						
					//project log table insertion
					
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "projects_log
															(id, project_id, cid, description, date_added, date_starts, date_end, user_id, visible, project_title, status, project_details, project_type, project_state, buynow, buynow_price, buynow_qty, buynow_purchases,filtered_auctiontype,currentprice,max_qty,alt_no,filter_escrow,Orderno,coin_series_denomination_no,coin_series_unique_no,norder,bold,highlite,featured)
															VALUES (
															NULL,
															'".$row_value['coin_id']."',
															'".$row_value['Category']."',
															'".$row_value['Description']."',
															'".DATETIME24H."',
															'".$ilance->db->escape_string($deal_start_date)."',
															'".$ilance->db->escape_string($deal_end_date)."',
															'".$row_value['user_id']."',
															'1',
															'".$row_value['Title']."',
															'open',
															'public',
															'forward',
															'product',
															'1',
															'".$buynow."',
															'".$row_value['Quantity']."',
															'".$row_value['Max_Quantity_Purchase']."',
															'fixed',
															'".$buynow."',
															'".$row_value['Max_Quantity_Purchase']."',
															'".$row_value['Alternate_inventory_No']."',
															'1',
															'".$order_nos."',
															'".$denom_no."',
															'".$series_no."',
															'".$row_value['norder']."',
															'".$row_value['bold']."',
															'".$row_value['highlite']."',
															'".$row_value['featured']."'																		
															)");
					$ilance->db->query("update " . DB_PREFIX . "catalog_toplevel set auction_count=auction_count+1 where denomination_unique_no=".$denom_no);
					$ilance->db->query("update " . DB_PREFIX . "catalog_second_level set auction_count=auction_count+1 where coin_series_unique_no=".$series_no);
					
					if (isset($shipping) AND is_array($shipping))
					{
					$ilance->auction = construct_object('api.auction');
					$ilance->auction_rfp = construct_object('api.auction_rfp');
					$ilance->auction_rfp->save_item_shipping_logic($insert_count, $shipping);
					
					}										
					
					if (isset($custom) AND is_array($custom))
					{
						 $ilance->auction = construct_object('api.auction');
						 $ilance->auction_post = construct_object('api.auction_post');
					 // process our answer input and store them into the datastore
						 $ilance->auction_post->process_custom_questions1($custom, $row_value['coin_id'], 'product');
					}
					
				
					build_category_count($row_value['Category'], 'add', "insert_product_auction(): adding increment count category id '".$row_value['Category']."'");
					
					
					//update coin table						
					$con_insert_cointable = $ilance->db->query("
					UPDATE  " . DB_PREFIX . "coins
					SET  project_id = '".$row_value['coin_id']."',
						 status = '0',									 
						 End_Date='".$ilance->db->escape_string($deal_end_date)."',
						 pending=0								 
					WHERE coin_id 	  =  '" .$product. "'
					AND project_id = '0'
					");
					
					$update_deal = $ilance->db->query("
					UPDATE  " . DB_PREFIX . "dailydeal
					SET  project_id = '".$row_value['coin_id']."'												     
					WHERE coin_id 	  =  '" .$product. "'												
					");
					
					//invoice and listing fees
					$my_var_in = insertion_fee_transaction_new($row_value['listing_fee'], 'product', $buynow, $row_value['coin_id'], $row_value['user_id']); 
					
					//update attachment userid and catergoryid
					$attach_concumer_sql = $ilance->db->query("SELECT *
							FROM " . DB_PREFIX . "attachment
							WHERE coin_id = '".$product."'

							");
					while($row_value_new = $ilance->db->fetch_array($attach_concumer_sql))
					{

						$attach_concumer = $ilance->db->query("UPDATE " . DB_PREFIX . "attachment
											  SET project_id     = '".$row_value['coin_id']."'
												  
											  WHERE coin_id = '".$product."'
											  ");
					}			
						

				}
			}
		
		
		}*/
	   
	
	}
	
	$message_dailydeal ='The items '.implode(",",$message_item_arr) . ' has been listed in DailyDeals';
	$ilance->email = construct_dm_object('email', $ilance);
	
	$ilance->email->logtype = 'dailydeals';
	$ilance->email->mail = $ilconfig['globalserversettings_testemail'];
	$ilance->email->slng = fetch_site_slng();	
	$ilance->email->get('daily_deal_listing');	
	$ilance->email->set(array(
			'{{message}}' => $message_dailydeal
	));
	$ilance->email->send();
	
	//email admin
	$ilance->email->logtype = 'dailydeals';
	$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
	$ilance->email->slng = fetch_site_slng();	
	$ilance->email->get('daily_deal_listing');	
	$ilance->email->set(array(
			'{{message}}' => $message_dailydeal
	));
	$ilance->email->send();
	
	
	//email admin ian
	$ilance->email->logtype = 'dailydeals';
	$ilance->email->mail = $ilconfig['globalserversettings_adminemail']; 
	$ilance->email->slng = fetch_site_slng();	
	$ilance->email->get('daily_deal_listing');	
	$ilance->email->set(array(
			'{{message}}' => $message_dailydeal
	));
	$ilance->email->send();
	
	 
	if(count($message_not_arr) >0)
	{
	
	$message_dailydeals ='The items '.implode(",",$message_not_arr) . ' has not listed in DailyDeals because of less than Minimum offer price.';
				$ilance->email = construct_dm_object('email', $ilance);

				$ilance->email->logtype = 'Not listed in DailyDeals';
				$ilance->email->mail = $ilconfig['globalserversettings_testemail'];
				$ilance->email->slng = fetch_site_slng();	
				$ilance->email->get('daily_deal_listing');	
				$ilance->email->set(array(
				'{{message}}' => $message_dailydeals
				));
				$ilance->email->send();

				//email admin
				$ilance->email->logtype = 'Not listed in DailyDeals';
				$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
				$ilance->email->slng = fetch_site_slng();	
				$ilance->email->get('daily_deal_listing');	
				$ilance->email->set(array(
				'{{message}}' => $message_dailydeals
				));
				$ilance->email->send();
				
				//email admin ian
				$ilance->email->logtype = 'Not listed in DailyDeals';
				$ilance->email->mail = $ilconfig['globalserversettings_adminemail']; 
				$ilance->email->slng = fetch_site_slng();	
				$ilance->email->get('daily_deal_listing');	
				$ilance->email->set(array(
				'{{message}}' => $message_dailydeal
				));
				$ilance->email->send();
				
				
				
				
	}
}


define('DATETOMORROW_MOD',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) + 1,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);

		define('DATEMINUSONEDAY',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 1,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		define('DATEMINUSTWODAY',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 2,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		define('DATEMINUSTHREEDAY',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 3,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		define('DATEMINUSFOURDAY',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 4,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		define('DATEMINUSFIVEDAY',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 5,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		define('DATEMINUSSIXDAY',
		gmdate(
			'Y-m-d',
			mktime(0,0,0,
			gmdate(
				'm',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])),
			gmdate(
				'd',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])) - 6,
			gmdate(
				'Y',
				time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst']))))
		);
		
		
	    $nameofthedeal="Deal".DATETOMORROW_MOD;
	    $sql="SELECT * FROM ".DB_PREFIX."dailydeal WHERE live_date = '".DATETOMORROW_MOD."' ";

	   $checkpromo = $ilance->db->query($sql);
						 
	   if($ilance->db->num_rows($checkpromo) > 0 )
	   {
	       echo $msg= " Daily Deal Is Already Added ";
	    	return $msg;
	   	exit();
	   }
	   $html=''; 
	   $live_date=0;
	   $no_items_seleted=0;
	   $minimum_buynowoffer=20;

	   $sql1="SELECT DISTINCT(p.coin_series_denomination_no) AS denomination_unique_num 
					FROM " . DB_PREFIX . "projects p
					LEFT JOIN ". DB_PREFIX ."coins c ON p.project_id=c.coin_id
	   				LEFT JOIN " . DB_PREFIX . "attachment a ON a.project_id=p.project_id and a.attachtype='itemphoto' 
	   				WHERE p.filtered_auctiontype = 'fixed' 
	   				AND p.project_state = 'product' 
	   				AND p.status = 'open' 
	   				AND p.buynow_qty > '0' 
	   				AND p.buynow_price != ''
					AND p.date_end != '".DATETODAY." 23:59:00'
					AND p.date_end != '".DATEMINUSONEDAY." 23:59:00'
					AND p.date_end != '".DATEMINUSTWODAY." 23:59:00'
					AND p.date_end != '".DATEMINUSTHREEDAY." 23:59:00'
					AND p.date_end != '".DATEMINUSFOURDAY." 23:59:00'
					AND p.date_end != '".DATEMINUSFIVEDAY." 23:59:00'
					AND p.date_end != '".DATEMINUSSIXDAY." 23:59:00'
					AND c.Quantity  > '0'
					AND c.isdaily_deal = 0
					AND a.attachtype IS NOT NULL
                    AND c.Buy_it_now != '' 
	   				AND (p.user_id = 101 OR p.user_id = 13115) 
	   				group by p.coin_series_denomination_no 
	   				ORDER BY p.coin_series_denomination_no ASC";							
							
				
	   $buynow_listing = $ilance->db->query($sql1);
							
	   if($ilance->db->num_rows($buynow_listing) > 0)
	   {				
				
	   	while($buynow_ress=$ilance->db->fetch_array($buynow_listing))
	   	{
	               if($buynow_ress['denomination_unique_num']>0)
	               {
			
		
	    $list_sql="SELECT p.project_id,c.coin_id,c.Buy_it_now,p.project_title,p.buynow_price,p.coin_series_denomination_no
	   							FROM " . DB_PREFIX . "projects p	
								LEFT JOIN ". DB_PREFIX ."coins c ON p.project_id=c.coin_id
	   							LEFT JOIN " . DB_PREFIX . "attachment a ON a.project_id=p.project_id and a.attachtype='itemphoto' 
	   							WHERE p.filtered_auctiontype = 'fixed' 
	   							AND p.project_state = 'product' 
	   							AND p.status = 'open' 
	   							AND p.buynow_qty > '0' 
	   							AND p.buynow_price != '' 
								AND c.Quantity  > '0'
								AND c.isdaily_deal = 0
								AND a.attachtype IS NOT NULL
								AND p.date_end != '".DATETODAY." 23:59:00'
								AND p.date_end != '".DATEMINUSONEDAY." 23:59:00'
								AND p.date_end != '".DATEMINUSTWODAY." 23:59:00'
								AND p.date_end != '".DATEMINUSTHREEDAY." 23:59:00'
								AND p.date_end != '".DATEMINUSFOURDAY." 23:59:00'
								AND p.date_end != '".DATEMINUSFIVEDAY." 23:59:00'
								AND p.date_end != '".DATEMINUSSIXDAY." 23:59:00'
                                AND c.Buy_it_now >= ".$minimum_buynowoffer." 
	   							AND p.buynow_price < '400' 
								AND (p.user_id = 101 OR p.user_id = 13115) 
	   							AND p.coin_series_denomination_no=".$buynow_ress['denomination_unique_num']."
	   							ORDER BY RAND() limit 1";														
                                                        
	   		$buynow_listing_inner_sql = $ilance->db->query($list_sql);
	   		while($buynow_res=$ilance->db->fetch_array($buynow_listing_inner_sql)){
		
	   			$discount_range_1=explode("-",'50-100-5');
	   			$discount_range_2=explode("-",'101-200-10');
	   			$discount_range_3=explode("-",'201-400-20');

	   			switch($buynow_res['buynow_price']){
	   				case ($buynow_res['buynow_price'] >= 0 && $buynow_res['buynow_price'] <= $discount_range_1[1]) :
	   					$buynow_res['offer_price']= $buynow_res['buynow_price'] - $discount_range_1[2];
	   					$offer_amount=$discount_range_1[2];
	   					break;
				
	   				case ($buynow_res['buynow_price'] >= $discount_range_2[0] && $buynow_res['buynow_price'] <= $discount_range_2[1]) :
	   					$buynow_res['offer_price']= $buynow_res['buynow_price'] - $discount_range_2[2];
	   					$offer_amount=$discount_range_2[2];
	   					break;
				
	   				case ($buynow_res['buynow_price'] >= $discount_range_3[0] ) :
	   					$buynow_res['offer_price']= $buynow_res['buynow_price'] - $discount_range_3[2];
	   					$offer_amount=$discount_range_3[2];
	   					break;
					
	   				default:
	   					$buynow_res['offer_price']= $buynow_res['buynow_price'];
	   					$offer_amount=0;
	   			}
			
	   			$buynow_res['offer_price']=$ilance->currency->format($buynow_res['offer_price']);
		
	   			$date= new DateTime(date('Y-m-d'));
	   			$date->modify('+1 day');			
	   			$live_date = $date->format('Y-m-d');		
	   			$deal_end_date = $date->format('Y-m-d').' 23:59:00';			
	   			$offertype="dollar";			
			
	   			$product_list['CoinId'] =$buynow_res['project_id'];
	               $product_list['Title'] =$buynow_res['project_title'];
	               $product_list['Original_Buynow'] =$buynow_res['Buy_it_now'];
	               $product_list['Offer'] =$offer_amount;
	   			$product_list['Final_Price'] =$buynow_res['offer_price'];
                        
	   			foreach($product_list as $key=>$value)
	               {
	               $html.=$key." : ".$value."\n";
	               }
			
	   			$act_amount = $ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '".$buynow_res['project_id']."'", "Buy_it_now");
	   			$act_end_date = $ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '".$buynow_res['project_id']."'", "End_Date");				
	            	$insert_sql="INSERT INTO " . DB_PREFIX . "dailydeal
	   						(deal_name, offer_type, offer_amt, live_date, coin_id, notes, act_amount, enddate)
	   						VALUES (
	   						'" . $ilance->db->escape_string($nameofthedeal) . "',
	   						'" . $ilance->db->escape_string($offertype) . "',
	   						'" . $ilance->db->escape_string($offer_amount) . "',								
	   						'".$ilance->db->escape_string($live_date)."',		
	   						'" . $buynow_res['project_id']. "',													
	   						'',
	   						'" . $act_amount . "',
	   						'" . $act_end_date . "'
	   						)";
                                                
	   			$ilance->db->query($insert_sql);
				
				/*  vijay 26.6.14 starts */
				$insertid = $ilance->db->insert_id();
				
				$insert_log="INSERT INTO " . DB_PREFIX . "dailydeal_log
	   						(dailydeal_id, deal_name, offer_type, offer_amt, live_date, coin_id, notes, act_amount, enddate)
	   						VALUES (
							'" . intval($insertid) . "',
	   						'" . $ilance->db->escape_string($nameofthedeal) . "',
	   						'" . $ilance->db->escape_string($offertype) . "',
	   						'" . $ilance->db->escape_string($offer_amount) . "',								
	   						'".$ilance->db->escape_string($live_date)."',		
	   						'" . $buynow_res['project_id']. "',													
	   						'',
	   						'" . $act_amount . "',
	   						'" . $act_end_date . "'
	   						)";
                                                
	   			$ilance->db->query($insert_log);
				
				/*  vijay 26.6.14 ends */
				/*
	   			$con_insert_cointable = $ilance->db->query("
	   						UPDATE  " . DB_PREFIX . "coins
	   						SET End_Date='".$deal_end_date."',
	   							 pending=0								 
	   						WHERE coin_id 	  =  '" .$buynow_res['coin_id']. "'
	   						AND project_id = '0'
	   						");
	   			*/	
	   			$result_arr[]=$buynow_res['project_id'];				
	   			$no_items_seleted++;
		
		
	   		}
	   	}
	   	}
		
	 $no_items_seleted;
        if($no_items_seleted>0)
        {
        $ilance->email = construct_dm_object('email', $ilance);
        
        $ilance->email->logtype = 'dailydeals';
        $ilance->email->mail = SITE_EMAIL;
        $ilance->email->mail = $ilconfig['globalserversettings_testemail'];
        $ilance->email->slng = fetch_site_slng();
        $ilance->email->get('admin_daily_deals');		
        $ilance->email->set(array('{{username}}' => 'admin','{{deal_date}}'=>$live_date,'{{details}}'=>$html));

        $ilance->email->send();
		
		 // email admin
		$ilance->email->logtype = 'dailydeals';
		$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
        $ilance->email->slng = fetch_site_slng();
        $ilance->email->get('admin_daily_deals');		
        $ilance->email->set(array('{{username}}' => 'admin','{{deal_date}}'=>$live_date,'{{details}}'=>$html));
        $ilance->email->send();
				
		// email admin ian
		$ilance->email->logtype = 'dailydeals';
		$ilance->email->mail = $ilconfig['globalserversettings_adminemail']; 
        $ilance->email->slng = fetch_site_slng();
        $ilance->email->get('admin_daily_deals');		
        $ilance->email->set(array('{{username}}' => 'admin','{{deal_date}}'=>$live_date,'{{details}}'=>$html));
        $ilance->email->send();
		
        }else
        {
            echo "no item seleted";
        }
        return $html;
		
		
	   }
									
}
}
?>
