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
	'administration',
	
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'search',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'modal',
	'yahoo-jar',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['consignment'] => $ilcrumbs[$ilpage['consignment']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'changed_value')
    {
 		if (isset($ilance->GPC['coin_list']) AND is_array($ilance->GPC['coin_list'])  AND count($ilance->GPC['coin_list'])>0 ) 
		{
			$count=0;
			$changed='';
			$changed_only_coin='';
			$not_changed='';
			$p_changed_to_auction_qty='';
			$p_changed_to_auction='';
			$p_changed_to_buynow='';
		    $c_changed_to_auction_qty='';
			$c_changed_to_auction='';
			$c_changed_to_buynow='';
			$donot_exist='';
			if((isset($ilance->GPC['submit']) && $ilance->GPC['submit']=='Change')){
			$item_arr=$ilance->GPC['coin_list'];
			$item_price_arr=$ilance->GPC['price_list'];	
			$change_to_auction=$ilance->GPC['changed_to_auction'];
			$changed_to_buynow=$ilance->GPC['changed_to_buynow'];		
				if(count($item_arr) == count($item_price_arr)){
					for($i=0;$i< count($item_arr); $i++){
						$entire_item_price_arr[$item_arr[$i]] = $item_price_arr[$i];
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
								if (in_array($id, $changed_to_buynow)) 
								{	

									$ilance->db->query("
									UPDATE " . DB_PREFIX . "coins
									SET 
									Buy_it_now = '" .$bid. "',
									Minimum_bid = ''
									WHERE coin_id = '" .$id. "'");

									$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects 
									SET filtered_auctiontype = 'fixed',
									buynow_price = '" . $bid. "',
									currentprice = '" .$bid. "',
									buynow_purchases = '0',
									buynow = '1',
									startprice = '0.00'
									WHERE project_id = '" .$id. "'");

									$line_item=$ilance->db->fetch_array($sqlquery1);
									$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
									(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) values('".$id."',
									'1',
									'".$line_item['startprice']."',
									'".$bid."',
									'".$_SESSION['ilancedata']['user']['userid']."',
									'".DATETIME24H."')");									

                                    $p_changed_to_buynow[]=$id;
								}
								else
								{
									$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
									SET  Minimum_bid = ".$bid."   
									WHERE  coin_id = '".$id."'");	

									$ilance->db->query("UPDATE  " . DB_PREFIX . "projects
									SET  startprice = ".$bid.",
									currentprice=".$bid."
									WHERE  project_id = '".$id."'");

									$line_item=$ilance->db->fetch_array($sqlquery1);
									$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
									(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) values('".$id."',
									'1',
									'".$line_item['startprice']."',
									'".$bid."',
									'".$_SESSION['ilancedata']['user']['userid']."',
									'".DATETIME24H."')");
									$changed[]=$id;								
								}


							}
							elseif($ilance->db->num_rows($sqlquery_fixed) > 0)
							{
								//No buynow winner	
								if (in_array($id, $change_to_auction)) 
								{
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
									'".DATETIME24H."')");	
									$changed[]=$id;
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
								if($res_coin['Minimum_bid'] != '')
								{	
									if (in_array($id, $changed_to_buynow)) 
									{
										$ilance->db->query("
										UPDATE " . DB_PREFIX . "coins
										SET 
										Buy_it_now = '" .$bid. "',
										Minimum_bid = ''
										WHERE coin_id = '" .$id. "'");

										$ilance->db->query("insert into  " . DB_PREFIX . "minimum_bid_log 
										(project_id, both_table, original_minimum_bid, changed_minimum_bid, user_id, changed_on) values
										(
										'".$id."',
										'0',
										'".$res_coin['Minimum_bid']."',
										'".$bid."',
										'".$_SESSION['ilancedata']['user']['userid']."',
										'".DATETIME24H."')");			

										$c_changed_to_buynow[] = $id;
									}
									else
									{
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
										'".DATETIME24H."')");
										$changed_only_coin[]=$id;
									}

								}
								if($res_coin['Buy_it_Now'] != '')
								{
									if (in_array($id, $change_to_auction)) 
									{
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
									else
									{
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
										'".DATETIME24H."')");
										$changed_only_coin[]=$id;
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
					print_action_failed('Please give valid input details',$_SERVER['PHP_SELF'],'Retry');
					exit();  
				}
			}
     	}				
		else
		{
			print_action_failed("We're sorry. Please give valid input details", $_SERVER['PHP_SELF']);
			exit();
		}
 		$p_changed_to_auction=empty($p_changed_to_auction)?0:implode(",",$p_changed_to_auction);
		$p_changed_to_auction_qty=empty($p_changed_to_auction_qty)?0:implode(",",$p_changed_to_auction_qty);
		$c_changed_to_auction=empty($c_changed_to_auction)?0:implode(",",$c_changed_to_auction);
		$c_changed_to_auction_qty=empty($c_changed_to_auction_qty)?0:implode(",",$c_changed_to_auction_qty);

		$p_changed_to_buynow=empty($p_changed_to_buynow)?0:implode(",",$p_changed_to_buynow);
		$c_changed_to_buynow=empty($c_changed_to_buynow)?0:implode(",",$c_changed_to_buynow);

		$changed=empty($changed)?0:implode(",",$changed);
		$changed_only_coin=empty($changed_only_coin)?0:implode(",",$changed_only_coin);
		$not_changed=empty($not_changed)?0:implode(",",$not_changed);
		$donot_exist=empty($donot_exist)?0:implode(",",$donot_exist);
		$html='</br>';
		$html.='<strong>Minimum Bid Change Report</strong>';
		$html.='</br></br>';
		$html.='Total Item Count- '.$count;
		$html.='</br></br>';
		$html.='Price Modified (Live & Re-List)<br>(Updated in Coin & Project Table) - '.$changed;
		$html.='</br></br>';
		$html.='Price Modified (Pending)<br>(Updated in Coin): - '.$changed_only_coin;
		$html.='</br></br>';
		$html.='Modified to Auction (Live & Re-List)<br>(Updated in Coin & Project Table) - '.$p_changed_to_auction;
		$html.='</br></br>';
		$html.='Modified to Auction (Pending)<br>(Updated in Coin) - '.$c_changed_to_auction;
		$html.='</br></br>';
		$html.='Modified to Buynow (Live & Re-List)<br>(Updated in Coin & Project Table) - '.$p_changed_to_buynow;
		$html.='</br></br>';
		$html.='Modified to Buynow (Pending)<br>(Updated in Coin) - '.$c_changed_to_buynow;
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
		print_action_success($html, $_SERVER['PHP_SELF'], 'Change Minimum bid for more coins');
		exit();		
	}

    if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list')
    {
		$show['search_list'] = 'search_list';
		$filename=$_FILES["file"]["tmp_name"];
		$_FILES["file"]["type"];	
        $mimes = array('text/csv');
		if(!in_array($_FILES['file']['type'],$mimes)){
		print_action_failed("Please Upload valid CSV File", $_SERVER['PHP_SELF']);exit();
		}
		if($_FILES["file"]["size"] > 0)
		{
			$file = $_FILES["file"]["tmp_name"];   
			$handle = fopen($file,"r"); 
			$name = $_FILES['file']['name'];
			$sold_coin = array();
			$not_list_coin = array();
			$unique_list_coin = array();
			$row_con_list=0;
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
			{					 
				if($data[0] == "" OR $data[1] == "" ){
                    $sold_coin[] = $data[0];
                    print_action_failed("Please check the Coin Id or Price in CSV column is empty, Please give valid input details", $_SERVER['PHP_SELF']);exit();
				}
				if (!preg_match('/^[0-9]*$/', $data[0])){
                    $sold_coin[] = $data[0];
                    print_action_failed("Please check the CSV Column, Please give valid Coin Id details", $_SERVER['PHP_SELF']);exit();				 
				}
				if (!preg_match('/^[0-9]+(\\.[0-9]+)?$/', $data[1])) {
                    $sold_coin[] = $data[0];
                    print_action_failed("Please check the CSV Column, Please give valid Price details", $_SERVER['PHP_SELF']);exit();				 
				}
				if($data[0] != "" OR $data[1] != "" ){					 
				$sql_seller = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id = ".$data[0], 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($sql_seller) > 0)
				{
					$res_seller = $ilance->db->fetch_array($sql_seller);
					$row_list['Item_id'] = $res_seller['coin_id'];
					$row_list['Title'] =  $res_seller['Title'];
					if(!empty($res_seller['Buy_it_now'])){
						$row_list['BuyNow'] = $res_seller['Buy_it_now'];
						$row_list['changed_to_buynow'] ='-';
					}
					else{
						$row_list['BuyNow'] = '-';
						$row_list['changed_to_buynow'] = '<input type="checkbox" name="changed_to_buynow[]" class="checkbox2" value ="'.$res_seller['coin_id'].'" />';
					}
					if(!empty($res_seller['Minimum_bid'])){
						$row_list['MinBid'] = $res_seller['Minimum_bid'];
						$row_list['changed_to_auction'] ='-';

					}
					else{
						$row_list['MinBid'] = '-';
						$row_list['changed_to_auction'] = '<input type="checkbox" name="changed_to_auction[]" class="checkbox1" value ="'.$res_seller['coin_id'].'" />';
					} 
					$row_list['ChangedMinBid'] =  $data[1];
					$pending_listing_search[] = $row_list;
					$row_con_list++;
					$unique_list_coin[] = $data[0]; 
	            }
	            else
	            {
	            	$not_list_coin[] = $data[0];
	            }
	            }
			}
			$result_Not_list    = implode(",",$not_list_coin);
            $tot_Not_list       = count($not_list_coin);
			$result_sold        = implode(",",$sold_coin);
            $tot_sold_coins     = count($sold_coin);
			if($tot_sold_coins > 0){		
			print_action_failed("Please give valid input details", $_SERVER['PHP_SELF']);exit();	 	 
			}
			if($tot_Not_list > 0){		
			print_action_failed("The coins are not listed in projects or coins table   - Kindly check following list:".$result_Not_list."", $_SERVER['PHP_SELF']);exit();	 	 
			}
			$unique_elements = array_unique($unique_list_coin);
			$totalUniqueElements = count($unique_elements); 
 			if ($row_con_list != $totalUniqueElements){
			print_action_failed("Please Check valid Unique Coin Id input details", $_SERVER['PHP_SELF']);exit();	  
			}
		}
		else
		{				
			$show['no'] = 'list_search';
		}
	} 	             								
 	$pprint_array = array('number_search','number','login_include_admin','ilanceversion','user_id','username','filtervalue','invoices_count','buildversion','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','listing_pagnation');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'change_value_auction_buynow.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('contactus_listing','pending_listing_search'));
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