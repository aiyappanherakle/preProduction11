<?php
require_once('./../functions/config.php');
error_reporting(E_ALL);

$selcoin = $ilance->db->query("SELECT * FROM ".DB_PREFIX."coins WHERE relist_count > 0 AND coin_id >= 6000");
while($row_value = $ilance->db->fetch_array($selcoin))
{
	$te = $ilance->db->query("SELECT date_added,date_starts,date_end FROM ".DB_PREFIX."projects WHERE project_id = '".$row_value['coin_id']."'");
	if($ilance->db->num_rows($te)>0)
	{
		$re = $ilance->db->fetch_array($te);
		$date_added = $re['date_added'];
		$date_starts = $re['date_starts'];
		$date_coin = $re['date_end'];
	}
	else
	{
		$date_added = DATETIME24H;
		$date_starts = DATETIME24H;
		$dataexplode = explode('-', $row_value['End_Date']);
		$date_coin = $dataexplode['0'] .'-'.$dataexplode['1'].'-'.$dataexplode['2'];
	}
	for($i=0;$i < $row_value['relist_count'];$i++)
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
				
				$order_nos = fetch_cat('Orderno',$row_value['pcgs']); 
				$denom_series = fetch_cat('coin_series_denomination_no',$row_value['pcgs']);
				$denom_uniqueno = fetch_cat('coin_series_unique_no',$row_value['pcgs']);
				
				
		
		$ilance->db->query("INSERT INTO " . DB_PREFIX . "projects_log
																		(id, project_id, cid, description, date_added, date_starts, date_end, user_id, visible, project_title, status, project_details, project_type, project_state, buynow, buynow_price, startprice, reserve_price, buynow_qty, buynow_purchases,filtered_auctiontype,currentprice,max_qty,alt_no,filter_escrow,reserve,Orderno,coin_series_denomination_no,coin_series_unique_no)
																		VALUES (
																		NULL,
																		'".$row_value['coin_id']."',
																		'".$row_value['Category']."',
																		'".$row_value['Description']."',
																		'".$re['date_added']."',
																		'".$re['date_starts']."',
																		'".$re['date_end']."',
																		'".$row_value['user_id']."',
																		'1',
																		'".$row_value['Title']."',
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
																		'".$denom_uniqueno."'
																		)");
													echo '<br>'.$row_value['coin_id'];
	}
	
}

?>