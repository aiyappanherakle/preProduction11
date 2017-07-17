<?php
require_once('../functions/config.php');
error_reporting(E_ALL);

$pjt = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "buynow_orders");
		
	if($ilance->db->num_rows($pjt) > 0)
	{	
	// statement_id, item_id, item_title,  relist_count, start_date, end_date, sale_date, seller_id, buyer_id, consignment_id, filtered_auctiontype, buynow_price, auction_price, sold_price, insertion_fee, featured_fee, highlite_fee, bold_fee, seller_fee, relist_fee, qty,status, bids
		while($res = $ilance->db->fetch_array($pjt))
		{
			$coin = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id = '".$res['project_id']."'");
			if($ilance->db->num_rows($coin) > 0)
			{
				
							
				
				if(fetch_auction('featured',$res['project_id']) > 0)
				{
					$featured_fee = $ilconfig['productupsell_featuredfee'];
				}
				else
				{
					$featured_fee = 0;
				}
				if(fetch_auction('highlite',$res['project_id']) > 0)
				{
					$highlite_fee = $ilconfig['productupsell_highlightfee'];
				}
				else
				{
					$highlite_fee = 0;
				}
				if(fetch_auction('bold',$res['project_id']) > 0)
				{
					$bold_fee = $ilconfig['productupsell_boldfee'];
				}
				else
				{
					$bold_fee = 0;
				}
				
				
				$insertionfee = fetch_auction('insertionfee',$res['project_id']);
				$buynow_price = fetch_auction('buynow_price',$res['project_id']);
				$date_starts = date('Y-m-d',strtotime(fetch_auction('date_starts',$res['project_id'])));
				$sale_date = date('Y-m-d',strtotime($res['orderdate']));
				$date_end = date('Y-m-d',strtotime(fetch_auction('date_end',$res['project_id'])));
				$title = fetch_auction('project_title',$res['project_id']);
				
				$coinres = $ilance->db->fetch_array($coin);
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "consign_statement
					(item_id, item_title,relist_count,start_date,end_date,sale_date,seller_id,buyer_id,consignment_id,
					filtered_auctiontype,buynow_price,auction_price,sold_price,insertion_fee, featured_fee, highlite_fee, bold_fee, seller_fee, relist_fee, qty,status, bids)
					VALUES (
					'".$coinres['coin_id']."',					
					'".mysql_escape_string($title)."',
					'".$coinres['relist_count']."',
					'".$date_starts."',
					'".$date_end."',
					'".$sale_date."',
					'".$res['owner_id']."',
					'".$res['buyer_id']."',
					'".$coinres['consignid']."',
					'fixed',
					'".$buynow_price."',
					'0',
					'".$res['amount']."',
					'".$insertionfee."',
					'".$featured_fee."',
					'".$highlite_fee."',
					'".$bold_fee."',
					'".$res['fvf']."',
					'0',
					'".$res['qty']."',
					'ended',
					'0'	)									
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