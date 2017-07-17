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
//error_reporting(E_ALL);
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	/* vijay  for bug 3313 * start 20.12.13 */	
	$show_coin_search = $showlist = false;
	$coins_searched = array();
	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='coins_search')
	{
		$show_coin_search = true;
		if(isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id']>0)
		{
			$project_id = $ilance->GPC['project_id'];
			
			$coins_search_sql=" SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id = '".$project_id."' ";
			
			$coins_search = $ilance->db->query($coins_search_sql);
			
			if($ilance->db->num_rows($coins_search) > 0)
			{
				$showlist = true;
				while($coins_detail = $ilance->db->fetch_array($coins_search))
				{ 
					$coin_id=$coins_detail['coin_id'];
					$seller_id = $coins_detail['user_id'];
					
					$prjct_sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE project_id = '".$coin_id."' ");
					
					if($ilance->db->num_rows($prjct_sql) > 0) //projects listing
					{
						while($project_detail = $ilance->db->fetch_array($prjct_sql))
						{
							
							if($project_detail['filtered_auctiontype'] == 'regular') //GC Bid
							{	
							
								$gc_coins = set_array($coin_id);
								$gc_coins['listed_at'] = 'GC';
								
								if(!empty($project_detail['winner_user_id']) AND $project_detail['winner_user_id'] > 0)
								{
								
									$buyer_id = $project_detail['winner_user_id']; //Sold
									$gc_coins['buyer_name'] = fetch_user('username', $buyer_id);
									$gc_coins['buyer_email'] = fetch_user('username', $buyer_id); 
									
									$buyer_fees = get_buyer_fees($coin_id, $buyer_id, $seller_id); 
									$gc_coins['buyer_fee'] = $ilance->currency->format($buyer_fees['buyer_fee']);
									$gc_coins['hammerprice'] = $ilance->currency->format($buyer_fees['sold_price']);
									$gc_coins['currentprice'] = $ilance->currency->format($buyer_fees['buyer_fee']+$buyer_fees['sold_price']);
									$gc_coins['invoice_no'] = $buyer_fees['invoice_no'];
									$gc_coins['paid_status'] = $buyer_fees['paid_status'];
									$gc_coins['sold_date'] = $buyer_fees['sold_date'];
									
									$seller_fees = get_seller_fees($coin_id, $seller_id);
									$gc_coins['listing_fee'] = $ilance->currency->format($seller_fees['insert_fee']);
									$gc_coins['fvf_fee'] = $ilance->currency->format($seller_fees['fvf_fee']);
									
									$check_ship = shipped_or_not($coin_id, $buyer_id,0);

									
									$gc_coins['shipped'] = $check_ship['Shipped'];
									$gc_coins['shipped_date'] = $check_ship['Shipped_Date'];
									$gc_coins['track_no'] = $check_ship['track_no'];

								 }
								else //Unsold
								{
									$gc_coins['price'] = $ilance->currency->format($project_detail['currentprice']);
									$gc_coins['hammerprice'] = 'Un Sold';
									$chek_returned_or_not = check_returned_or_not($coin_id);
									$gc_coins['returned'] = $chek_returned_or_not['returned'];
									$gc_coins['returned_date'] = $chek_returned_or_not['returned_date'];
								}
								$coins_searched[] = $gc_coins;
							}
							else //buynow
							{
								$buynow_sql = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "buynow_orders WHERE project_id = '".$coin_id."' ");
								
								if($ilance->db->num_rows($buynow_sql) > 0)
								{
									while($buynow_detail = $ilance->db->fetch_array($buynow_sql))
									{
										$gc_coins = set_array($coin_id);
										$gc_coins['listed_at'] = 'GC';
										//echo '<pre>';
										$gc_coins['quantity'] = 'Qty: '.$buynow_detail['qty'];
										$buyer_id = $buynow_detail['buyer_id']; //Sold
										$gc_coins['buyer_name'] = fetch_user('username', $buyer_id);
										$gc_coins['buyer_email'] = fetch_user('username', $buyer_id); 
										
										$buyer_fees = get_buyer_fees($coin_id, $buyer_id, $seller_id); 
									//	$gc_coins['buyer_fee'] = $buyer_fees['buyer_fee'];
										$gc_coins['hammerprice'] = $ilance->currency->format($buyer_fees['sold_price']);
										$gc_coins['currentprice'] = $ilance->currency->format($buyer_fees['sold_price']);
										$gc_coins['invoice_no'] = $buyer_fees['invoice_no'];
										$gc_coins['paid_status'] = $buyer_fees['paid_status'];
										$gc_coins['sold_date'] = $buyer_fees['sold_date'];
										
										$seller_fees = get_seller_fees($coin_id, $seller_id);
										$gc_coins['listing_fee'] = $ilance->currency->format($seller_fees['insert_fee']);
										$gc_coins['fvf_fee'] = $ilance->currency->format($seller_fees['fvf_fee']);
										
										$check_ship = shipped_or_not($coin_id, $buyer_id,0);
										
										$gc_coins['shipped'] = $check_ship['Shipped'];
										$gc_coins['shipped_date'] = $check_ship['Shipped_Date'];
										$gc_coins['track_no'] = $check_ship['track_no'];
										$coins_searched[] = $gc_coins;

									}
								}
								else
								{
									$gc_coins = set_array($coin_id);
									$gc_coins['listed_at'] = 'GC';
										
									$gc_coins['price'] = $ilance->currency->format($project_detail['currentprice']);
									$gc_coins['hammerprice'] = 'Un Sold';
									$chek_returned_or_not = check_returned_or_not($coin_id);
									$gc_coins['returned'] = $chek_returned_or_not['returned'];
									$gc_coins['returned_date'] = $chek_returned_or_not['returned_date'];
									
									$coins_searched[] = $gc_coins;
								}
								
							}
							
						}
						
					}
					
					
					$ebay_sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "ebay_listing WHERE coin_id = '".$coin_id."' ");
					
					if($ilance->db->num_rows($ebay_sql) > 0) //ebay listing
					{
						$showlist = true;
						while($ebay_detail = $ilance->db->fetch_array($ebay_sql))
						{
							
							if($ebay_detail['item_type'] == 'regular') //Ebay regular
							{	
								$ebay_coins = set_array($coin_id);
								$ebay_coins['listed_at'] = 'ebay';
								
								if($coins_detail['ebay_title'] != '')  //ebay title 
									$ebay_coins['Title'] = $coins_detail['ebay_title']; 
								
								$ebay_row_sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "ebay_listing_rows WHERE coin_id = '".$coin_id."' AND ebay_listing_id = '".$ebay_detail['id']."' ");
								if($ilance->db->num_rows($ebay_row_sql) > 0)
								{
									while($ebay_row_detail = $ilance->db->fetch_array($ebay_row_sql))
									{
										$ebay_buyer_id = $ebay_row_detail['buyer_id']; //Sold
										$seller_id = $ebay_row_detail['seller_id'];
										$ebay_coins['buyer_name'] = fetch_user('username', $ebay_buyer_id);
										$ebay_coins['buyer_email'] = fetch_user('username', $ebay_buyer_id); 
										
										$ebay_buyer_fees = get_buyer_fees($coin_id, $ebay_buyer_id, $seller_id); 
										$ebay_coins['buyer_fee'] = $ilance->currency->format($ebay_buyer_fees['buyer_fee']);
										$ebay_coins['hammerprice'] = $ilance->currency->format($ebay_buyer_fees['sold_price']);
										$ebay_coins['currentprice'] = $ilance->currency->format($ebay_buyer_fees['sold_price']+$ebay_buyer_fees['buyer_fee']);
										$ebay_coins['invoice_no'] = $ebay_buyer_fees['invoice_no'];
										$ebay_coins['paid_status'] = $ebay_buyer_fees['paid_status'];
										$ebay_coins['sold_date'] = $ebay_buyer_fees['sold_date'];
										
										$ebay_seller_fees = get_seller_fees($coin_id, $seller_id);
										$ebay_coins['listing_fee'] = $ilance->currency->format($ebay_seller_fees['insert_fee']);
										$ebay_coins['fvf_fee'] = $ilance->currency->format($ebay_seller_fees['fvf_fee']);
										
										$ebay_check_ship = shipped_or_not($coin_id, $ebay_buyer_id,0);
										
										$ebay_coins['shipped'] = $ebay_check_ship['Shipped'];
										$ebay_coins['shipped_date'] = $ebay_check_ship['Shipped_Date'];
										$ebay_coins['track_no'] = $ebay_check_ship['track_no'];
									}
								}
								else //Ebay Unsold
								{
									$ebay_coins['price'] = $ilance->currency->format($coins_detail['currentprice']);
									$chek_returned_or_not = check_returned_or_not($coin_id);
									$ebay_coins['returned'] = $chek_returned_or_not['returned'];
									$ebay_coins['returned_date'] = $chek_returned_or_not['returned_date'];
								}
								$coins_searched[] = $ebay_coins;
							}
							else //Ebay fixed
							{
								
								$ebay_row_sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "ebay_listing_rows WHERE coin_id = '".$coin_id."' AND ebay_listing_id = '".$ebay_detail['id']."' ");
								if($ilance->db->num_rows($ebay_row_sql) > 0)
								{
									while($ebay_row_detail = $ilance->db->fetch_array($ebay_row_sql))
									{
										$ebay_coins = set_array($coin_id);
										$ebay_coins['listed_at'] = 'ebay';
										
										$ebay_buyer_id = $ebay_row_detail['buyer_id']; //Sold
										$seller_id = $ebay_row_detail['seller_id'];
										$ebay_coins['buyer_name'] = fetch_user('username', $ebay_buyer_id);
										$ebay_coins['buyer_email'] = fetch_user('username', $ebay_buyer_id); 
										
										$ebay_buyer_fees = get_buyer_fees($coin_id, $ebay_buyer_id, $seller_id); 
									//	$ebay_coins['buyer_fee'] = $ebay_buyer_fees['buyer_fee'];
										$ebay_coins['hammerprice'] = $ilance->currency->format($ebay_buyer_fees['sold_price']);
										$ebay_coins['currentprice'] = $ilance->currency->format($ebay_buyer_fees['sold_price']);
										$ebay_coins['invoice_no'] = $ebay_buyer_fees['invoice_no'];
										$ebay_coins['paid_status'] = $ebay_buyer_fees['paid_status'];
										$ebay_coins['sold_date'] = $ebay_buyer_fees['sold_date'];
										
										$ebay_seller_fees = get_seller_fees($coin_id, $seller_id);
										$ebay_coins['listing_fee'] = $ilance->currency->format($ebay_seller_fees['insert_fee']);
										$ebay_coins['fvf_fee'] = $ilance->currency->format($ebay_seller_fees['fvf_fee']);
										
										$ebay_check_ship = shipped_or_not($coin_id, $ebay_buyer_id,0);
										
										$ebay_coins['shipped'] = $ebay_check_ship['Shipped'];
										$ebay_coins['shipped_date'] = $ebay_check_ship['Shipped_Date'];
										$ebay_coins['track_no'] = $ebay_check_ship['track_no'];
										$coins_searched[] = $ebay_coins;
									}
									
								}
								else //Ebay Unsold
								{
									$ebay_coins = set_array($coin_id);
									$ebay_coins['listed_at'] = 'ebay';
										
									$ebay_coins['price'] = $ilance->currency->format($coins_detail['currentprice']);
									$chek_returned_or_not = check_returned_or_not($coin_id);
									$ebay_coins['returned'] = $chek_returned_or_not['returned'];
									$ebay_coins['returned_date'] = $chek_returned_or_not['returned_date'];
									$coins_searched[] = $ebay_coins;
								}
								
							}
								
							
						}
						
						
					}
					
				}
			}
		}
		
	
	}
	
		
	$pprint_array = array('showlist','show_coin_search','filtervalue','projec_id','number','item_id','amount_val','datesold','seller_id','seller_name','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','coin_id1','consign_id1','$seller_name1','$title1','$pcgs_no1','dt1','amount1','prof2');
	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	$ilance->template->fetch('main', 'coins_search_5267.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('coins_searched'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	/* vijay  for bug 3313 * ends 3.12.13 */
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}


function shipped_or_not($coiniid, $buyerid=0, $sellerid=0)
{
	global $ilance;
	$ship_det = array();
	$ship_det['Shipped']= $ship_det['Shipped_Date']= $ship_det['track_no']='--';
	
	$condtn = '';
	if($buyerid>0)
		$condtn = "AND buyer_id = '".$buyerid."' ";
		
	if($sellerid>0)
		$condtn = "AND cust_id = '".$sellerid."' ";	
		
	$shsql="SELECT * FROM " . DB_PREFIX . "shippnig_details WHERE coin_id = '".$coiniid."' ".$condtn." "; //AND track_no!='' 
										//echo $shsql;exit;
	$shippng = $ilance->db->query($shsql);
	if($ilance->db->num_rows($shippng) > 0)
	{	
		while($shpng_det = $ilance->db->fetch_array($shippng))
		{
			$ship_det['Shipped']='<strong>'. $shpng_det['email'].'</strong>';
			$ship_det['Shipped_Date']= check_date_format($shpng_det['shipment_date']);
			$ship_det['track_no']=$shpng_det['track_no'];
		}
	}
		
	return $ship_det; 
}

function check_date_format($date_time)
{
	$error_formats = array('0000-00-00 00:00:00','0000-00-00','-','--');
	if(!empty($date_time) AND !in_array($date_time, $error_formats))
	{
		$date_time = date("F d,Y",strtotime($date_time));
	}
	else
	{
		$date_time = '--';
	}
	
	return $date_time;
}

function get_seller_fees($coinid, $sellerid)
{
	global $ilance;
	$data = array('insert_fee'=>'0','fvf_fee'=> '0');
	$sql = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "invoices WHERE projectid = '".$coinid."' AND user_id = '".$sellerid."' AND (isif = 1 OR isfvf=1) ");
		if($ilance->db->num_rows($sql) > 0)
		{
			while($fees = $ilance->db->fetch_array($sql))
			{
				if($fees['isif']==1)
				{
					$data['insert_fee'] = $fees['amount'];
				}
				
				if($fees['isfvf']==1)
				{
					$data['fvf_fee'] = $fees['amount'];
				}
			}
		}
	return $data;	
}

function get_buyer_fees($coin_id, $buyer_id, $seller_id)
{
	global $ilance;
	$data = array('buyer_fee'=>'-','sold_price'=> '-','invoice_no'=>'-','paid_status'=>'-','sold_date'=>'-');
	
	$sql = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "invoices WHERE projectid = '".$coin_id."' AND user_id = '".$buyer_id."' AND isif=0 AND isfvf=0 ");
//	echo "SELECT *  FROM " . DB_PREFIX . "invoices WHERE projectid = '".$coin_id."' AND user_id = '".$buyer_id."' AND isif=0 AND isfvf=0 ";
		if($ilance->db->num_rows($sql) > 0)
		{
			while($bfees = $ilance->db->fetch_array($sql))
			{
				if($bfees['p2b_user_id'] > 0 AND $bfees['p2b_user_id'] == $seller_id AND $bfees['isbuyerfee'] == 0)
				{
					$data['sold_price'] = $bfees['amount'];
					$invoice_id = $bfees['invoiceid'];
					$sql1 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices WHERE user_id = '".$buyer_id."' AND combine_project LIKE '%".$invoice_id."%' ");
					//echo "<br/>SELECT * FROM " . DB_PREFIX . "invoices WHERE user_id = '".$buyer_id."' AND combine_project LIKE '%".$invoice_id."%'";
					if($ilance->db->num_rows($sql1) > 0)
					{
						while($invoice = $ilance->db->fetch_array($sql1))
						{
							$data['paid_status'] = $invoice['status'];
							$data['invoice_no'] = $invoice['invoiceid'];
							$data['sold_date'] = check_date_format($invoice['paiddate']);
						}
					}
				}
				else
				{
					$data['buyer_fee'] = $bfees['amount'];
				}
			}
		}
		
	return $data;		
}

function check_returned_or_not($coin_id)
{
	global $ilance;
	$data =  array();
	$sql = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "coin_return WHERE coin_id = '".$coin_id."'");
	if($ilance->db->num_rows($sql) > 0)
	{	
		while($returned = $ilance->db->fetch_array($sql))
		{
			$data['returned']='YES';
			$data['returned_date']=check_date_format($returned['return_date']); 
		}	
	}
	else
	{
		$data['returned']='NO';
		$data['returned_date']='--';
	}
	return $data;
}

function set_array($coinid=0)
{
	global $ilance;
	$data = array();
	if($coinid > 0)
	{
		$sql = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "coins WHERE coin_id = '".$coinid."' ");
		if($ilance->db->num_rows($sql) > 0)
		{
			while($coins = $ilance->db->fetch_array($sql))
			{ 
				$data['item_id'] = $coins['coin_id'];
				$data['Certification_No'] = $coins['Certification_No'];
				$data['Alternate_inventory_No'] = $coins['Alternate_inventory_No'];
				$data['pcgs'] = $coins['pcgs'];
				$data['consignid'] = $coins['consignid'];
				$data['Title'] = $coins['Title'];
				$data['seller_name'] = fetch_user('username', $coins['user_id']);
				$data['seller_email'] = fetch_user('email', $coins['user_id']);
				$data['relist_count'] = $coins['relist_count'];
				$data['Create_Date'] = check_date_format($coins['Create_Date']);
				$data['End_Date'] = check_date_format($coins['End_Date']);
				$data['listed_at'] = 'Pending';
				
				if(!empty($coins['Minimum_bid']) AND $coins['Minimum_bid'] > 0) 
				{
					$data['item_type'] = 'Bid';
					$data['mibid_or_buynow'] = $ilance->currency->format($coins['Minimum_bid']).'/'.$ilance->currency->format(0);
					$data['currentprice'] = $ilance->currency->format($coins['Minimum_bid']);
				}
				else
				{
					$data['item_type'] = 'Buynow';
					$data['mibid_or_buynow'] = $ilance->currency->format(0).'/'.$ilance->currency->format($coins['Buy_it_now']);
					$data['currentprice'] = $ilance->currency->format($coins['Buy_it_now']);
				}
								
			}
			
			$data['listing_fee'] = $ilance->currency->format(0);
			$data['fvf_fee'] = $ilance->currency->format(0);
			$data['hammerprice'] = '--' ;
			$data['buyer_name'] = '--' ;
			$data['buyer_email'] = '--' ;
			$data['paid_date'] = '--' ;
			$data['buyer_fee'] = $ilance->currency->format(0);
			$data['invoice_no'] = '-' ;
			$data['paid_status'] = '-' ;
			$data['shipped'] = '--' ;
			$data['shipped_date'] = '--' ;
			$data['track_no'] = '--' ;
			$data['returned'] = '--' ;
			$data['returned_date'] = '--' ;
			$data['sold_date'] = '-';
			$data['quantity'] = '';
			
		}	
	}
	
	return $data;

}




/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>