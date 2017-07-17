<?php
require_once('../functions/config.php');
error_reporting(E_ALL);

$pjt = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects");
		
	if($ilance->db->num_rows($pjt) > 0)
	{	
	// statement_id, item_id, item_title,  relist_count, start_date, end_date, sale_date, seller_id, buyer_id, consignment_id, filtered_auctiontype, buynow_price, auction_price, sold_price, insertion_fee, featured_fee, highlite_fee, bold_fee, seller_fee, relist_fee, qty,status, bids
		while($res = $ilance->db->fetch_array($pjt))
		{
			$coin = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id = '".$res['project_id']."'");
			if($ilance->db->num_rows($coin) > 0)
			{
				if($res['winner_user_id'] > 0)
				{
					$buyer_id = $res['winner_user_id'];
					$sale_date = date('Y-m-d',strtotime($res['date_end']));
				}
				else
				{
					$buyer_id = 0;
					$sale_date = '0000-00-00';
				}
				if($res['featured'] > 0)
				{
					$featured_fee = $ilconfig['productupsell_featuredfee'];
				}
				else
				{
					$featured_fee = 0;
				}
				if($res['highlite'] > 0)
				{
					$highlite_fee = $ilconfig['productupsell_highlightfee'];
				}
				else
				{
					$highlite_fee = 0;
				}
				if($res['bold'] > 0)
				{
					$bold_fee = $ilconfig['productupsell_boldfee'];
				}
				else
				{
					$bold_fee = 0;
				}
				if($res['status'] == 'open')
				{
					$status = 'open';
				}
				else
				{
					$status = 'ended';
				}
				$coinres = $ilance->db->fetch_array($coin);
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "consign_statement
					(item_id, item_title,relist_count,start_date,end_date,sale_date,seller_id,buyer_id,consignment_id,
					filtered_auctiontype,buynow_price,auction_price,sold_price,insertion_fee, featured_fee, highlite_fee, bold_fee, seller_fee, relist_fee, qty,status, bids)
					VALUES (
					'".$coinres['coin_id']."',					
					'".mysql_escape_string($res['project_title'])."',
					'".$coinres['relist_count']."',
					'".date('Y-m-d',strtotime($res['date_starts']))."',
					'".date('Y-m-d',strtotime($res['date_end']))."',
					'".$sale_date."',
					'".$res['user_id']."',
					'".$buyer_id."',
					'".$coinres['consignid']."',
					'".$res['filtered_auctiontype']."',
					'".$res['buynow_price']."',
					'".$res['startprice']."',
					'".$res['currentprice']."',
					'".$res['insertionfee']."',
					'".$featured_fee."',
					'".$highlite_fee."',
					'".$bold_fee."',
					'".$res['fvf']."',
					'0',
					'".$res['buynow_qty']."',
					'".$status."',
					'".$res['bids']."'	)									
					");
				echo '<br>yes'.$res['project_id'];
			}
			else
			{
				echo '<br>not'.$res['project_id'];
			}
		}
	}

?>