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
		$not_changed='';
		$donot_exist='';
		
		if((isset($ilance->GPC['submit']) && $ilance->GPC['submit']=='Change')){
		
			$item_arr=explode("\r\n",rtrim($ilance->GPC['coin_list']));
			$item_price_arr=explode("\r\n",rtrim($ilance->GPC['price_list']));			
			
			// if($ilance->GPC['coin_list'] == "" AND $ilance->GPC['price_list'] == "" )
			// {
			// 	print_action_failed('Please give valid input details','minbids_value_change.php','Retry');
			// 	exit();
			// }
		 //    if (!preg_match('/^[0-9]*$/', $ilance->GPC['coin_list'])) {
			//   print_action_failed('Please give valid input details','minbids_value_change.php','Retry');
			//   exit();
		 //    }
		 //    if (!preg_match('/^[0-9]+(\\.[0-9]+)?$/', $ilance->GPC['price_list'])) {
			//   print_action_failed('Please give valid input details','minbids_value_change.php','Retry');
			//   exit();
		 //    } 
  
						
			if(count($item_arr) == count($item_price_arr)){
				
				for($i=0;$i< count($item_arr); $i++){
				
					$entire_item_price_arr[$item_arr[$i]] = $item_price_arr[$i];
				}
				
				foreach($entire_item_price_arr as $id=>$bid)
				{
					
					$count++;					
					$sqlquery = $ilance->db->query("select * from ".DB_PREFIX."projects where project_id=$id ");
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
			  
							$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
                                                                             SET  Buy_it_Now = ".$bid."   
                                                                             WHERE  coin_id = '".$id."'");	
							
							
							$ilance->db->query("UPDATE  " . DB_PREFIX . "projects
                                                                             SET  buynow_price = ".$bid.",
                                                                                      currentprice=".$bid."
                                                                                      WHERE  project_id = '".$id."'");
                                                                                      
							$line_item=$ilance->db->fetch_array($sqlquery_fixed); 
							$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
							(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) values
												(
												'".$id."',
												'1',
												'".$line_item['buynow_price']."',
												'".$bid."',
												'".$_SESSION['ilancedata']['user']['userid']."',
												'".DATETIME24H."'
												)");	

							$changed[]=$id;
							
							
							
						}
						else
						{
							//With bids
							$not_changed[]=$id;
						}
						
					}
					else
					{
						
						$sqlquery2 = $ilance->db->query("select Minimum_bid,Buy_it_Now from ".DB_PREFIX."coins where coin_id=$id");
						if($ilance->db->num_rows($sqlquery2) > 0)
						{
							$res_coin=$ilance->db->fetch_array($sqlquery2);
							if($res_coin['Minimum_bid']>0){
							
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

							}
							if($res_coin['Buy_it_Now']){
								
                                                                $ilance->db->query("UPDATE  " . DB_PREFIX . "coins
                                                                                     SET  Buy_it_Now = ".$bid."   
                                                                                     WHERE  coin_id = '".$id."'"); 
							           
								$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
								(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) values
											(
											'".$id."',
											'0',
											'".$res_coin['Buy_it_Now']."',
											'".$bid."',
											'".$_SESSION['ilancedata']['user']['userid']."',
											'".DATETIME24H."'
											)");
							}							
							
							$changed_only_coin[]=$id;
						}
						else
						{			
							$donot_exist[]=$id;
							
						}
						
					}	
					

				}
				
			}
			else{
				
				print_action_failed('Please give valid input details','minbids_value_change.php','Retry');
				exit();  
			
			}
			
			
		}
		
		if((isset($ilance->GPC['submit_percent']) && $ilance->GPC['submit_percent']=='Change')){
		
			$percent_item_arr=explode("\r\n",rtrim($ilance->GPC['coin_list_percent']));
			$percentile=intval(rtrim($ilance->GPC['percentile']));
			$operator=($ilance->GPC['change_by'] == 1)?'+':'-';

			if($ilance->GPC['coin_list_percent'] == "")
			{
				print_action_failed('Please give valid input details','minbids_change.php','Retry');
				exit();
			}

			  
		    if (!preg_match('/^[0-9]*$/', $ilance->GPC['coin_list_percent'])) {
			  print_action_failed('Please give valid input details','minbids_change.php','Retry');
			  exit();
		    }  
			
			if(count($percent_item_arr) > 0 && !empty($percentile)){
				
				foreach($percent_item_arr as $id)
				{

					$count++;
					$sqlquery = $ilance->db->query("select * from ".DB_PREFIX."projects where project_id=$id ");
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
						
							$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
                                                                            SET  Buy_it_Now=ROUND((Buy_it_Now) ".$operator." ( Buy_it_Now * ( ".$percentile." /100 ) ) , 0 )  
                                                                            WHERE  coin_id = '".$id."'");	
							
							
						
							
							$ilance->db->query("UPDATE  " . DB_PREFIX . "projects
                                                                    SET  buynow_price=ROUND((buynow_price) ".$operator." ( buynow_price  * (".$percentile." /100 ) ) , 0 ),
                                                                            currentprice=ROUND((currentprice) ".$operator." ( currentprice * ( ".$percentile." /100 ) ) , 0 )
                                                                            WHERE  project_id = '".$id."'");	
							$changed[]=$id;
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
                                                ROUND((Buy_it_Now) ".$operator." ( Buy_it_Now * ( ".$percentile." /100 ) ) , 0 )  as changed_Buy_it_Now
                                                 from ".DB_PREFIX."coins where coin_id=$id");
						if($ilance->db->num_rows($sqlquery2) > 0)
						{
							
							$res_coin=$ilance->db->fetch_array($sqlquery2);
							if($res_coin['Minimum_bid']>0)
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
							
							}
							if($res_coin['Buy_it_Now']>0){
								
                                                        $ilance->db->query("UPDATE  " . DB_PREFIX . "coins SET  Buy_it_Now=ROUND((Buy_it_Now) ".$operator." ( Buy_it_Now * ( ".$percentile." /100 ) ) , 0 )  WHERE  coin_id = '".$id."'");
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
							
							}
							
							$changed_only_coin[]=$id;
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
		$html='</br>';
		$html.='<strong>hi-Minimum Bid Change Report</strong>';
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
		
			
		print_action_success($html, 'minbids_value_change.php', 'Change Minimum bid for more coins');
		exit();
		
		
	}
	else{
	
		
        
		$show['change_bid_form'] = 'holding';
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
		$ilance->template->fetch('main', 'minbids_value_change.html', 2);
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
	
		
	print_action_success($html, 'minbids_value_change.php', 'Change Minimum bid for more coins');
	exit();
}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*  Tamil */


?>