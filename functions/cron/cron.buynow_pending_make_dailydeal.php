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

//Tamil for Bug 3066

// if (!isset($GLOBALS['ilance']->db))
// {
	// die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
// }


//($apihook = $ilance->api('cron_dailydeal_start')) ? eval($apihook) : false;

require_once('../config.php');

$date= new DateTime(date('Y-m-d'));
$date->modify('+2 day');			
$live_date = $date->format('Y-m-d');		
$deal_end_date = $date->format('Y-m-d').' 23:59:00';
$deal_name=$date->format('md').'deal';			
$offertype="dollar";
$discount_range_1=explode("-",'50-100-5');
$discount_range_2=explode("-",'101-200-10');
$discount_range_3=explode("-",'201-400-20');

$denomination_unique_numbers_sql= $ilance->db->query("SELECT denomination_unique_no FROM " . DB_PREFIX . "catalog_toplevel GROUP BY denomination_unique_no");

while($denomination_unique_numbers_res=$ilance->db->fetch_array($denomination_unique_numbers_sql)){
	
	$buynow_listing = $ilance->db->query("SELECT * FROM buynow_pending
					WHERE 
					denomination_unique_no='".$denomination_unique_numbers_res['denomination_unique_no']."'
					ORDER BY RAND()
					LIMIT 2
					");
	if($ilance->db->num_rows($buynow_listing) > 0){
		
		while($buynow_res=$ilance->db->fetch_array($buynow_listing)){
			echo 'T';
			$product=$buynow_res['coin_id'];
			$act_amount = $ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '$product'", "Buy_it_now");
			$act_end_date = $ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '$product'", "End_Date");
	
			switch($buynow_res['Buy_it_now']){
				case ($buynow_res['Buy_it_now'] >= 0 && $buynow_res['Buy_it_now'] <= $discount_range_1[1]) :
					$after_discount= $buynow_res['Buy_it_now'] - $discount_range_1[2];
					$offer_amount=$discount_range_1[2];
					break;
				
				case ($buynow_res['Buy_it_now'] >= $discount_range_2[0] && $buynow_res['Buy_it_now'] <= $discount_range_2[1]) :
					$after_discount= $buynow_res['Buy_it_now'] - $discount_range_2[2];
					$offer_amount=$discount_range_2[2];
					break;
				
				case ($buynow_res['Buy_it_now'] >= $discount_range_3[0] ) :
					$after_discount= $buynow_res['Buy_it_now'] - $discount_range_3[2];
					$offer_amount=$discount_range_3[2];
					break;
				default:
					$after_discount= $buynow_res['Buy_it_now'];
					$offer_amount=0;
			}
		
		}

		echo "
					INSERT INTO " . DB_PREFIX . "dailydeal
					(deal_name, offer_type, offer_amt, live_date, coin_id, notes, act_amount, enddate)
					VALUES (
					'" . $ilance->db->escape_string($deal_name) . "',
					'" . $ilance->db->escape_string($offertype) . "',
					'" . $ilance->db->escape_string($offeramount) . "',								
					'".$ilance->db->escape_string($live_date)."',		
					'" . $product. "',													
					'',
					'" . $act_amount . "',
					'" . $act_end_date . "'
					)
			";
			
		// $ilance->db->query("
					// INSERT INTO " . DB_PREFIX . "dailydeal
					// (deal_name, offer_type, offer_amt, live_date, coin_id, notes, act_amount, enddate)
					// VALUES (
					// '" . $ilance->db->escape_string($deal_name . "',
					// '" . $ilance->db->escape_string($offertype) . "',
					// '" . $ilance->db->escape_string($offeramount) . "',								
					// '".$ilance->db->escape_string($live_date)."',		
					// '" . $product. "',													
					// '',
					// '" . $act_amount . "',
					// '" . $act_end_date . "'
					// )
			// ");
		echo '<br>';
		echo "
					UPDATE  " . DB_PREFIX . "coins
					SET End_Date='".$deal_end_date."',
						 pending=0								 
					WHERE coin_id 	  =  '" .$product. "'
					AND project_id = '0'
					";
		// $con_insert_cointable = $ilance->db->query("
					// UPDATE  " . DB_PREFIX . "coins
					// SET End_Date='".$deal_end_date."',
						 // pending=0								 
					// WHERE coin_id 	  =  '" .$product. "'
					// AND project_id = '0'
					// ");
	
		echo '<br>';
	
	}
	
}


//Tamil for Bug 3066
?>