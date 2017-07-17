<?php 

class lowering_coin_amount
{
	function lowering_coins()
	{
	global $ilance,$ilconfig;

		   
		   $live_date=0;
		   $no_items_seleted=0;
		   $operator='-';

		  $loweringsql="SELECT * FROM " . DB_PREFIX . "users WHERE is_auto_lower_min_bid =1";				
					
		   $loweringrslt = $ilance->db->query($loweringsql);
								
		   if($ilance->db->num_rows($loweringrslt) > 0)
		   {				
				
			while($loweringrsl=$ilance->db->fetch_array($loweringrslt))
			{
			$if_coins_selected_email=0;	
            $html=''; 
			$username =fetch_user('username', $loweringrsl['user_id']);

			$lowering_coinssql="SELECT p.project_id as proj_id,c.coin_id,p.user_id,c.Minimum_bid,c.Buy_it_now,p.project_title,p.currentprice,p.buynow_price,p.currencyid
				FROM " . DB_PREFIX . "projects p
				LEFT JOIN " . DB_PREFIX . "coins c ON p.project_id=c.coin_id AND c.pending = 0 AND c.site_id ='0'
				WHERE DATE(p.date_end) = (select date (subdate(now(), INTERVAL (weekday(now())+1) DAY)))
				AND p.status = 'expired'
				AND c.coin_id IS NOT NULL 
				AND p.user_id = '".$loweringrsl['user_id']."'
				AND ((p.filtered_auctiontype = 'regular' AND p.winner_user_id  = '0') OR (p.buynow = '1' AND p.filtered_auctiontype = 'fixed'  AND p.buynow_qty > '0'))";							
						
					
		   $lowering_rst = $ilance->db->query($lowering_coinssql);
								
		   if($ilance->db->num_rows($lowering_rst) > 0)
		   {				
				
			while($lowering_ress=$ilance->db->fetch_array($lowering_rst))
			{
						$if_coins_selected_email=1;
						$currencyid=$lowering_ress['currencyid'];
						$sqlquery = $ilance->db->query("select * from ".DB_PREFIX."projects where project_id='".$lowering_ress['proj_id']."'");
						if($ilance->db->num_rows($sqlquery) > 0)
						{
						
									
							while($lowering_r=$ilance->db->fetch_array($sqlquery))
							{
										
								if($lowering_r['filtered_auctiontype']='regular'){
								
									$sqlquery1 = $ilance->db->query("select project_id,FLOOR((startprice) ".$operator." ( startprice * ( ".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) ) as changed_minimum_bid,startprice from ".DB_PREFIX."projects where project_id='".$lowering_ress['proj_id']."' and bids=0 AND filtered_auctiontype = 'regular'");							
								}
								if($lowering_r['filtered_auctiontype']='fixed'){
									$sqlquery_fixed = $ilance->db->query("select project_id,FLOOR((buynow_price) ".$operator." ( buynow_price * ( ".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) ) as changed_buynow_price,buynow_price from ".DB_PREFIX."projects where project_id='".$lowering_ress['proj_id']."' and hasbuynowwinner =0 AND filtered_auctiontype =  'fixed'");
								}
								
							}	
								if($ilance->db->num_rows($sqlquery1) > 0)
								{
									//No bids	
									
								
									$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
																				SET  Minimum_bid=FLOOR((Minimum_bid) ".$operator." ( Minimum_bid * ( ".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) )  
																					WHERE  coin_id = '".$lowering_ress['proj_id']."'");	
																					
								
																									
									
								
								
									 $ilance->db->query("UPDATE  " . DB_PREFIX . "projects
																				SET  startprice=FLOOR((startprice) ".$operator." ( startprice  * (".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) ),
																				currentprice=FLOOR((currentprice) ".$operator." ( currentprice * ( ".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) )
																						WHERE  project_id = '".$lowering_ress['proj_id']."'");	
								
							while($line_item=$ilance->db->fetch_array($sqlquery1))
							{
									
								
									$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
											(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) value
												(
												'".$lowering_ress['proj_id']."',
												'1',
												'".$line_item['startprice']."',
												'".$line_item['changed_minimum_bid']."',
												'".$_SESSION['ilancedata']['user']['userid']."',
												'".DATETIME24H."'
												)");
																			
									

								
									$html.='Item ID: ';	
									$html.=$lowering_ress['proj_id'].",";
									$html.='Old Min Bid: ';
									$html.=$ilance->currency->format($line_item['startprice'], $currencyid).",";
									$html.=' New Min Bid: ';
									$html.=$ilance->currency->format($line_item['changed_minimum_bid'], $currencyid)."\n\n";

								$changed[]=$lowering_ress['proj_id'];
							}
								}
								elseif($ilance->db->num_rows($sqlquery_fixed) > 0){
								
									$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
																					SET  Buy_it_Now=FLOOR((Buy_it_Now) ".$operator." ( Buy_it_Now * ( ".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) )  
																					WHERE  coin_id = '".$lowering_ress['proj_id']."'");	
									
									
								
									
									$ilance->db->query("UPDATE  " . DB_PREFIX . "projects
																			SET  buynow_price=FLOOR((buynow_price) ".$operator." ( buynow_price  * (".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) ),
																					currentprice=FLOOR((currentprice) ".$operator." ( currentprice * ( ".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) )
																					WHERE  project_id = '".$lowering_ress['proj_id']."'");	
								
																$changed[]=$lowering_ress['proj_id'];
													while($line_item=$ilance->db->fetch_array($sqlquery_fixed))
													{
															
																$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
																		(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) value
																			(
																			'".$lowering_ress['proj_id']."',
																			'1',
																			'".$line_item['buynow_price']."',
																			'".$line_item['changed_buynow_price']."',
																			'".$_SESSION['ilancedata']['user']['userid']."',
																			'".DATETIME24H."'
																			)");
								
																			$html.='Item ID: ';	
																			$html.=$lowering_ress['proj_id'].",";
																			$html.='Old Buy Now: ';
																			$html.=$ilance->currency->format($line_item['buynow_price'], $currencyid).",";
																			$html.=' New Buy Now: ';
																			$html.=$ilance->currency->format($line_item['changed_buynow_price'], $currencyid)."\n\n";
													}
								}
								else
								{
									//With bids
									$not_changed[]=$lowering_ress['proj_id'];
								}
							
						
						}
						else
						{
							$sqlquery2 = $ilance->db->query("select Minimum_bid,Buy_it_Now,
													FLOOR((Minimum_bid) ".$operator." ( Minimum_bid * ( ".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) )  as changed_minimum_bid,
													FLOOR((Buy_it_Now) ".$operator." ( Buy_it_Now * ( ".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) )  as changed_Buy_it_Now
													 from ".DB_PREFIX."coins where coin_id='".$lowering_ress['proj_id']."'");
							if($ilance->db->num_rows($sqlquery2) > 0)
							{
								
								$res_coin=$ilance->db->fetch_array($sqlquery2);
								if($res_coin['Minimum_bid']>0)
															{
									$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
																			SET  Minimum_bid=FLOOR((Minimum_bid) ".$operator." ( Minimum_bid * ( ".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) )  
																			WHERE  coin_id = '".$lowering_ress['proj_id']."'");  
																						
																		$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
																	(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) value
																		(
																		'".$lowering_ress['proj_id']."',
																		'0',
																		'".$res_coin['Minimum_bid']."',
																		'".$res_coin['changed_minimum_bid']."',
																		'".$_SESSION['ilancedata']['user']['userid']."',
																		'".DATETIME24H."'
																		)");
																		
																		$html.='Item ID: ';	
																		$html.=$lowering_ress['proj_id'].",";
																		$html.='Old Min Bid: ';
																		$html.=$ilance->currency->format($res_coin['Minimum_bid'], $currencyid).",";
																		$html.=' New Min Bid: ';
																		$html.=$ilance->currency->format($res_coin['changed_minimum_bid'], $currencyid)."\n\n";
								
								}
								if($res_coin['Buy_it_Now']>0){
									
															$ilance->db->query("UPDATE  " . DB_PREFIX . "coins SET  Buy_it_Now=FLOOR((Buy_it_Now) ".$operator." ( Buy_it_Now * ( ".$loweringrsl['auto_min_bid_lower_prec']." /100 ) ) )  WHERE  coin_id = '".$lowering_ress['proj_id']."'");
															         $ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
															        (project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) value
															            (
															            '".$lowering_ress['proj_id']."',
															            '0',
															            '".$res_coin['Buy_it_Now']."',
															            '".$res_coin['changed_Buy_it_Now']."',
															            '".$_SESSION['ilancedata']['user']['userid']."',
															            '".DATETIME24H."'
															            )");
															
															
															$html.='Item ID: ';	
															$html.=$lowering_ress['proj_id'].",";
															$html.='Old Buy Now: ';
															$html.=$ilance->currency->format($res_coin['Buy_it_Now'], $currencyid).",";
															$html.=' New Buy Now: ';
															$html.=$ilance->currency->format($res_coin['changed_Buy_it_Now'], $currencyid)."\n\n";
								}
								
								$changed_only_coin[]=$lowering_ress['proj_id'];
							}
							else
							{			
								$donot_exist[]=$lowering_ress['proj_id'];
								
							}
							
						}	
				
			
			}

		   }

		  
			if($if_coins_selected_email==1)
			{
			$ilance->email = construct_dm_object('email', $ilance);
			$ilance->email->logtype = 'lowering automation';
			$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('admin_lowering_min_buy');		
			$ilance->email->set(array('{{username}}' => 'admin','{{details}}'=>$html));
			$ilance->email->send();
			
			 // email admin
			$ilance->email->logtype = 'lowering automation';
			$ilance->email->mail = $ilconfig['globalserversettings_testemail'];    
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('admin_lowering_min_buy');		
			$ilance->email->set(array('{{username}}' => $username,'{{details}}'=>$html));
			$ilance->email->send();


			 // email admin
			$ilance->email->logtype = 'lowering automation';
			$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];    
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('admin_lowering_min_buy');		
			$ilance->email->set(array('{{username}}' => $username,'{{details}}'=>$html));
			$ilance->email->send();
			}
			
		    }
			
			return 1;
		   }
		 
		 
	}
}
?>