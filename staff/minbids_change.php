<?php 
/* Tamil */
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
require_once('./../functions/config.php');
error_reporting(e_all);
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{	


	if((isset($ilance->GPC['submit']) && $ilance->GPC['submit']=='Change') || (isset($ilance->GPC['submit_percent']) && $ilance->GPC['submit_percent']=='Change') ){
		
		$count=0;
		$changed='';
		$changed_only_coin='';
		$p_changed_to_auction_qty='';
		$c_changed_to_auction_qty='';
		$c_changed_to_auction='';
		$p_changed_to_auction='';
		$not_changed='';
		$donot_exist='';
		$sold_coin = array();
		$not_list_coin = array();
		$unique_list_coin = array();
		$row_con_list=0;
		
		if((isset($ilance->GPC['submit']) && $ilance->GPC['submit']=='Change')){
		
			$item_arr=explode("\r\n",rtrim($ilance->GPC['coin_list']));
			$item_price_arr=explode("\r\n",rtrim($ilance->GPC['price_list']));	  
						
			if(count($item_arr) == count($item_price_arr)){
				
				for($i=0;$i< count($item_arr); $i++){
				
					$entire_item_price_arr[$item_arr[$i]] = $item_price_arr[$i];
				}



				foreach($entire_item_price_arr as $id=>$bid)
				{
					if($id == "" OR $bid == "" ){
	                    $sold_coin[] = $id;
	                    print_action_failed("Please check the Coin Id or Price column is empty, Please give valid input details", $_SERVER['PHP_SELF']);exit();
					}
					if (!preg_match('/^[0-9]*$/', $id)){
	                    $sold_coin[] = $id;
	                    print_action_failed("Please give valid Coin Id details", $_SERVER['PHP_SELF']);exit();				 
					}
					if (!preg_match('/^[0-9]+(\\.[0-9]+)?$/', $bid)) {
	                    $sold_coin[] = $id;
	                    print_action_failed("Please give valid Price details", $_SERVER['PHP_SELF']);exit();				 
					}
 

				}

				$result_sold        = implode(",",$sold_coin);
	            $tot_sold_coins     = count($sold_coin);
				if($tot_sold_coins > 0){		
				print_action_failed("Please give valid input details", $_SERVER['PHP_SELF']);exit();	 	 
				}


				foreach($item_arr as $id)
				{

					$sql_seller = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id = ".$id, 0, null, __FILE__, __LINE__);
					if($ilance->db->num_rows($sql_seller) > 0)
					{
						$unique_list_coin[] = $id; 
		            }
		            else
		            {
		            	$not_list_coin[] = $id;
		            }
		            $row_con_list++;

				}
				$result_Not_list    = implode(",",$not_list_coin);
	            $tot_Not_list       = count($not_list_coin);
	            if($tot_Not_list > 0){		
				print_action_failed("The coins are not listed in projects or coins table   - Kindly check following list: ".$result_Not_list."", $_SERVER['PHP_SELF']);exit();	 	 
				}
				$unique_elements = array_unique($unique_list_coin);
				$totalUniqueElements = count($unique_elements); 
	 			if ($row_con_list != $totalUniqueElements){
				print_action_failed("Please Check valid Unique Coin Id input details", $_SERVER['PHP_SELF']);exit();	  
				}
				
				foreach($entire_item_price_arr as $id=>$bid)
				{
					
					$count++;					
					$sqlquery = $ilance->db->query("select * from ".DB_PREFIX."projects where project_id=$id ");
					$project_res_seller = $ilance->db->fetch_array($sqlquery);
					if($ilance->db->num_rows($sqlquery) > 0)
					{	
						
						$auction_type=fetch_auction('filtered_auctiontype',$id);
						if($auction_type='regular'){
							$sqlquery1 = $ilance->db->query("select project_id,startprice from ".DB_PREFIX."projects where project_id=$id and bids=0 AND filtered_auctiontype =  'regular'");
						}
						if($auction_type='fixed'){
							$sqlquery_fixed = $ilance->db->query("select project_id,buynow_price from ".DB_PREFIX."projects where project_id=$id and hasbuynowwinner =0 AND filtered_auctiontype =  'fixed'");
						}
						if($ilance->db->num_rows($sqlquery1) > 0)
						{
							//No bids								
                                                  
							$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
                                                                             SET  Minimum_bid = ".$bid."   
                                                                             WHERE  coin_id = '".$id."'															
                                                                             ");	

							
							$ilance->db->query("UPDATE  " . DB_PREFIX . "projects
                                                                             SET  startprice = ".$bid.",
                                                                                     currentprice=".$bid."
                                                                                      WHERE  project_id = '".$id."'															 
                                                                             ");
																			 
																			
				
							$line_item=$ilance->db->fetch_array($sqlquery1);
							$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
							(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) values('".$id."',
												'1',
												'".$line_item['startprice']."',
												'".$bid."',
												'".$_SESSION['ilancedata']['user']['userid']."',
												'".DATETIME24H."'
												)");	
												
							$changed[]=$id;
							
							
						
						}
						elseif($ilance->db->num_rows($sqlquery_fixed) > 0){
							
							//No buynow winner								
			  
                                 	if($project_res_seller['buynow_qty'] == 1)
                                	{
 								        $ilance->db->query("
										UPDATE " . DB_PREFIX . "coins
										SET 
										Buy_it_now = '',
										Minimum_bid = '". $bid ."'
										WHERE coin_id = '" .$id. "'");

 										$ilance->db->query("
										UPDATE " . DB_PREFIX . "projects As p
										SET filtered_auctiontype = 'regular',
										startprice = '" . $bid. "',
										currentprice = '" .$bid . "',
										buynow_purchases = '1',
										buynow = '0',
										buynow_price = '0.00'
										WHERE project_id = '" .$id. "'
										AND p.filtered_auctiontype = 'fixed'
										AND p.buynow='1'");		

										$line_item=$ilance->db->fetch_array($sqlquery_fixed); 
										$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
										(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) values
										(
										'".$id."',
										'1',
										'".$line_item['buynow_price']."',
										'".$bid."',
										'".$_SESSION['ilancedata']['user']['userid']."',
										'".DATETIME24H."')");	

                                		$p_changed_to_auction[]=$id;
                                	}
                                	else
                                	{
                                        $p_changed_to_auction_qty[]=$id;
                                	}
						}
						else
						{
							//With bids
							$not_changed[]=$id;
						}
						
					}
					else
					{
						
						$sqlquery2 = $ilance->db->query("select Minimum_bid,Buy_it_Now,Quantity from ".DB_PREFIX."coins where coin_id=$id");
						if($ilance->db->num_rows($sqlquery2) > 0)
						{
							$res_coin=$ilance->db->fetch_array($sqlquery2);
							if($res_coin['Minimum_bid'] != ''){
							
								$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
                                                                                     SET  Minimum_bid = ".$bid."   
                                                                                     WHERE  coin_id = '".$id."'");  
                                                                                     
								$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
								(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) values
											(
											'".$id."',
											'0',
											'".$res_coin['Minimum_bid']."',
											'".$bid."',
											'".$_SESSION['ilancedata']['user']['userid']."',
											'".DATETIME24H."'
											)");
								$changed_only_coin[]=$id;

							}
							if($res_coin['Buy_it_Now'] != ''){
								
	                                 	if($res_coin['Quantity'] == 1)
	                                	{
									     	$ilance->db->query("
											UPDATE " . DB_PREFIX . "coins
											SET 
											Buy_it_now = '',
											Minimum_bid = '". $bid ."'
											WHERE coin_id = '" .$id. "'");

											$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
											(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) values
											(
											'".$id."',
											'0',
											'".$res_coin['Buy_it_Now']."',
											'".$bid."',
											'".$_SESSION['ilancedata']['user']['userid']."',
											'".DATETIME24H."')");	                                		
											$c_changed_to_auction[]=$id;
	                                	}
	                                	else
	                                	{
	                                        $c_changed_to_auction_qty[]=$id;
	                                	}
							}							
							
						}
						else
						{			
							$donot_exist[]=$id;
							
						}
						
					}	
					

				}
				
			}
			else{
				
				print_action_failed('Please give valid input details','minbids_change.php','Retry');
				exit();  
			
			}
			
			
		}
		
		if((isset($ilance->GPC['submit_percent']) && $ilance->GPC['submit_percent']=='Change')){
		
			$percent_item_arr=explode("\r\n",rtrim($ilance->GPC['coin_list_percent']));
			$percentile=intval(rtrim($ilance->GPC['percentile']));
			$operator=($ilance->GPC['change_by'] == 1)?'+':'-';
			if($operator == '-')
			{ 
				if($percentile > '99')
				{
					print_action_failed('Please give 99 and below percentile input details','minbids_change.php','Retry');
					exit();
				}
			}
			$not_list_coin = array();
			$unique_list_coin = array();
			$row_con_list=0;
 

			  


	        foreach($percent_item_arr as $ids)
			{
				if($ids == "")
				{
					print_action_failed('Please give Coin Id input details','minbids_change.php','Retry');
					exit();
				}
				if (!preg_match('/^[0-9]*$/', $ids)) {
					print_action_failed('Please give valid Coin Id input details','minbids_change.php','Retry');
					exit();
				} 
				$sql_seller = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id = ".$ids, 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($sql_seller) > 0)
				{
					$unique_list_coin[] = $ids; 
	            }
	            else
	            {
	            	$not_list_coin[] = $ids;
	            }
	            $row_con_list++;

			}
			$result_Not_list    = implode(",",$not_list_coin);
            $tot_Not_list       = count($not_list_coin);
            if($tot_Not_list > 0){		
			print_action_failed("The coins are not listed in projects or coins table   - Kindly check following list: ".$result_Not_list."", $_SERVER['PHP_SELF']);exit();	 	 
			}
			$unique_elements = array_unique($unique_list_coin);
			$totalUniqueElements = count($unique_elements); 
 			if ($row_con_list != $totalUniqueElements){
			print_action_failed("Please Check valid Unique Coin Id input details", $_SERVER['PHP_SELF']);exit();	  
			}
			
			if(count($percent_item_arr) > 0 && !empty($percentile)){
				
				foreach($percent_item_arr as $id)
				{
					$count++;
					$sqlquery = $ilance->db->query("select * from ".DB_PREFIX."projects where project_id=$id ");
					$project_res_seller = $ilance->db->fetch_array($sqlquery);

					if($ilance->db->num_rows($sqlquery) > 0)
					{
						
						$auction_type=fetch_auction('filtered_auctiontype',$id);
						if($auction_type='regular'){
							$sqlquery1 = $ilance->db->query("select project_id,ROUND((startprice) ".$operator." ( startprice * ( ".$percentile." /100 ) ) , 0 ) as changed_minimum_bid,startprice from ".DB_PREFIX."projects where project_id=$id and bids=0 AND filtered_auctiontype =  'regular'");
						}
						if($auction_type='fixed'){
							$sqlquery_fixed = $ilance->db->query("select project_id,ROUND((buynow_price) ".$operator." ( buynow_price * ( ".$percentile." /100 ) ) , 0 ) as changed_buynow_price,buynow_price from ".DB_PREFIX."projects where project_id=$id and hasbuynowwinner =0 AND filtered_auctiontype =  'fixed'");
						}						
						
						if($ilance->db->num_rows($sqlquery1) > 0)
						{
							//No bids	
							
			  
							$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
                                                                        SET  Minimum_bid=ROUND((Minimum_bid) ".$operator." ( Minimum_bid * ( ".$percentile." /100 ) ) , 0 )  
                                                                            WHERE  coin_id = '".$id."'");	
                                                         $line_item=$ilance->db->fetch_array($sqlquery1);
                                                        	
							
						
							
							 $ilance->db->query("UPDATE  " . DB_PREFIX . "projects
                                                                        SET  startprice=ROUND((startprice) ".$operator." ( startprice  * (".$percentile." /100 ) ) , 0 ),
                                                                        currentprice=ROUND((currentprice) ".$operator." ( currentprice * ( ".$percentile." /100 ) ) , 0 )
                                                                                WHERE  project_id = '".$id."'");	
						
							$line_item=$ilance->db->fetch_array($sqlquery1);
							$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
									(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) value
										(
										'".$id."',
										'1', 
										'".$line_item['startprice']."',
										'".$line_item['changed_minimum_bid']."',
										'".$_SESSION['ilancedata']['user']['userid']."',
										'".DATETIME24H."'
										)");
																	
							$changed[]=$id;
						
						}
						elseif($ilance->db->num_rows($sqlquery_fixed) > 0){
						
                                 	if($project_res_seller['buynow_qty'] == 1)
                                	{

 
 								        $ilance->db->query("
										UPDATE " . DB_PREFIX . "coins
										SET 
										Buy_it_now = '',
										Minimum_bid=ROUND((".$project_res_seller['buynow_price'].") ".$operator." ( ".$project_res_seller['buynow_price']." * ( ".$percentile." /100 ) ) , 0 ) 
										WHERE coin_id = '" .$id. "'");

 										$ilance->db->query("
										UPDATE " . DB_PREFIX . "projects As p
										SET filtered_auctiontype = 'regular',
										startprice=ROUND((".$project_res_seller['buynow_price'].") ".$operator." ( ".$project_res_seller['buynow_price']."  * (".$percentile." /100 ) ) , 0 ),
                                        currentprice=ROUND((".$project_res_seller['buynow_price'].") ".$operator." ( ".$project_res_seller['buynow_price']." * ( ".$percentile." /100 ) ) , 0 ),
										buynow_purchases = '1',
										buynow = '0',
										buynow_price = '0.00'
										WHERE project_id = '" .$id. "'
										AND p.filtered_auctiontype = 'fixed'
										AND p.buynow='1'");		

										$line_item=$ilance->db->fetch_array($sqlquery_fixed);
										$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
										(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) value
										(
										'".$id."',
										'1',
										'".$line_item['buynow_price']."',
										'".$line_item['changed_buynow_price']."',
										'".$_SESSION['ilancedata']['user']['userid']."',
										'".DATETIME24H."'
										)");	

                                		$p_changed_to_auction[]=$id;
                                	}
                                	else
                                	{
                                        $p_changed_to_auction_qty[]=$id;
                                	}
						
						}
						else
						{
							//With bids
							$not_changed[]=$id;
						}
						
					}
					else
					{
						$sqlquery2 = $ilance->db->query("select Minimum_bid,Buy_it_Now,
                                                ROUND((Minimum_bid) ".$operator." ( Minimum_bid * ( ".$percentile." /100 ) ) , 0 )  as changed_minimum_bid,
                                                ROUND((Buy_it_Now) ".$operator." ( Buy_it_Now * ( ".$percentile." /100 ) ) , 0 )  as changed_Buy_it_Now,Quantity
                                                 from ".DB_PREFIX."coins where coin_id=$id");
						if($ilance->db->num_rows($sqlquery2) > 0)
						{
							
							$res_coin=$ilance->db->fetch_array($sqlquery2);
							if($res_coin['Minimum_bid'] != '')
                                                        {

								$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
                                                                        SET  Minimum_bid=ROUND((Minimum_bid) ".$operator." ( Minimum_bid * ( ".$percentile." /100 ) ) , 0 )  
                                                                        WHERE  coin_id = '".$id."'");  
                                                                                    
                                                                    $ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
                                                                (project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) value
                                                                    (
                                                                    '".$id."',
                                                                    '0',
                                                                    '".$res_coin['Minimum_bid']."',
                                                                    '".$res_coin['changed_minimum_bid']."',
                                                                    '".$_SESSION['ilancedata']['user']['userid']."',
                                                                    '".DATETIME24H."'
                                                                    )");
                                                                    $changed_only_coin[]=$id;
							
							}
							if($res_coin['Buy_it_Now'] != ''){
								
	                                 	if($res_coin['Quantity'] == 1)
	                                	{

									     	$ilance->db->query("
											UPDATE " . DB_PREFIX . "coins
											SET 
											Buy_it_now = '',
											Minimum_bid=ROUND((".$res_coin['Buy_it_Now'].") ".$operator." ( ".$res_coin['Buy_it_Now']." * ( ".$percentile." /100 ) ) , 0 ) 
											WHERE coin_id = '" .$id. "'");

											$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
											(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) value
											(
											'".$id."',
											'0',
											'".$res_coin['Buy_it_Now']."',
											'".$res_coin['changed_Buy_it_Now']."',
											'".$_SESSION['ilancedata']['user']['userid']."',
											'".DATETIME24H."'
											)");
								                                		
											$c_changed_to_auction[]=$id;
	                                	}
	                                	else
	                                	{
	                                        $c_changed_to_auction_qty[]=$id;
	                                	}
							
							}
							
							//$changed_only_coin[]=$id;
						}
						else
						{			
							$donot_exist[]=$id;
							
						}
						
					}	
					

				}			
				
			}
			else{
				
				print_action_failed('Please give valid input details','minbids_change.php','Retry');
				exit();  
			
			}
			
			
		}

		
		$changed=empty($changed)?0:implode(",",$changed);
		$changed_only_coin=empty($changed_only_coin)?0:implode(",",$changed_only_coin);
		$not_changed=empty($not_changed)?0:implode(",",$not_changed);
		$donot_exist=empty($donot_exist)?0:implode(",",$donot_exist);
		$c_changed_to_auction_qty=empty($c_changed_to_auction_qty)?0:implode(",",$c_changed_to_auction_qty);
		$p_changed_to_auction_qty=empty($p_changed_to_auction_qty)?0:implode(",",$p_changed_to_auction_qty);
 		$p_changed_to_auction=empty($p_changed_to_auction)?0:implode(",",$p_changed_to_auction);
		$c_changed_to_auction=empty($c_changed_to_auction)?0:implode(",",$c_changed_to_auction);


		$html='</br>';
		$html.='<strong>Minimum Bid Change Report</strong>';
		$html.='</br></br>';
		$html.='Total count of items- '.$count;
		$html.='</br></br>';
		$html.='Changed in both projects & coins table - '.$changed;
		$html.='</br></br>';
		$html.='Changed only in coins table - '.$changed_only_coin;
		$html.='</br></br>';
		$html.='Modified to Auction (Live & Re-List)<br>(Updated in Coin & Project Table) - '.$p_changed_to_auction;
		$html.='</br></br>';
		$html.='Modified to Auction (Pending)<br>(Updated in Coin) - '.$c_changed_to_auction;
		$html.='</br></br>';
		$html.='Not Modified to Auction (Live & Re-List)<br>(Buy Coins having Multiple Qty) - '.$p_changed_to_auction_qty;
		$html.='</br></br>';
		$html.='Not Modified to Auction (Pending)<br>(Buy Coins having Multiple Qty) - '.$c_changed_to_auction_qty;
		$html.='</br></br>';
		$html.='Not changed items(i.e)With bids - '.$not_changed;
		$html.='</br></br>';
		$html.='Do not exist - '.$donot_exist;
		$html.='</br>';
		
		$show['change_bid_result'] = 'show';
		
			
		print_action_success($html, 'minbids_change.php', 'Change Minimum bid for more coins');
		exit();
		
		
	}
	else{
	
		
        
		$show['change_bid_form'] = 'holding';
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
		$ilance->template->fetch('main', 'minbids_change.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));	
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	$changed=empty($changed)?0:implode(",",$changed);
	$changed_only_coin=empty($changed_only_coin)?0:implode(",",$changed_only_coin);
	$not_changed=empty($not_changed)?0:implode(",",$not_changed);
	$donot_exist=empty($donot_exist)?0:implode(",",$donot_exist);
	$html='</br>';
	$html.='<strong>Minimum Bid Change Report</strong>';
	$html.='</br></br>';
	$html.='Total count of items- '.$count;
	$html.='</br></br>';
	$html.='Changed in both projects & coins table - '.$changed;
	$html.='</br></br>';
	$html.='Changed only in coins table - '.$changed_only_coin;
	$html.='</br></br>';
	$html.='Not changed items(i.e)With bids - '.$not_changed;
	$html.='</br></br>';
	$html.='Do not exist - '.$donot_exist;
	$html.='</br>';
	
	$show['change_bid_result'] = 'show';
	
		
	print_action_success($html, 'minbids_change.php', 'Change Minimum bid for more coins');
	exit();
}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*  Tamil */


?>