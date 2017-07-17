<?php
 
class not_list_placebid
{
	function not_listed_coins()
	{
	    global $ilance,$ilconfig;
		$query1="SELECT * FROM " . DB_PREFIX . "cron_not_placebid c 
		left join " . DB_PREFIX . "projects p on p.project_id = c.project_id
		left join " . DB_PREFIX . "users u on p.user_id = u.user_id
		where c.Bid_listed='No' AND c.status='bid' and u.status='active' and p.date_end>'".DATETIME24H."' and p.status='open' and p.filtered_auctiontype='regular'
		";
 		$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($sql1))
		{
			$total_coin_list  = array();
			$total_candu_list = array();
			while($line1 = $ilance->db->fetch_array($sql1))
			{
				$project_id=$line1['project_id'];
				if ($project_id > 0) 
				{
					$total_coin_list[] = intval($project_id);
					$total_candu_list[intval($project_id)] = "28";
					$ilance->db->query("UPDATE " .DB_PREFIX . "cron_not_placebid SET Bid_listed ='Yes' WHERE status='bid' AND  project_id = '".intval($project_id)."'");
				}
			}
		}

		$coins_list=$total_coin_list;
		$bids_list=$total_candu_list;
		$bidamount=$ilance->GPC['price_fixed'];
 		$total_coin_lists=array();
		foreach($coins_list as $coin_id)
		{
			if ($coin_id > 0) 
			{
				$bidder_id=$bids_list[$coin_id];
				$sql="SELECT p.user_id,p.project_id,bids.user_id as maxbidder_id,proxy.maxamount as bidders_maxamount,p.project_title,p.bids,p.startprice,p.currentprice,p.date_end,p.filtered_auctiontype FROM " . DB_PREFIX . "projects p 
				left join (select user_id,bidamount,project_id  from " . DB_PREFIX . "project_bids where project_id = '".$coin_id."' ORDER BY bidamount DESC, date_added ASC LIMIT 1)as bids on bids.project_id=p.project_id 
				left join (SELECT user_id,maxamount,project_id FROM " . DB_PREFIX . "proxybid  WHERE user_id = '" . $bidder_id . "' AND project_id = '" . $coin_id . "' limit 1) as proxy on proxy.project_id=p.project_id
				WHERE p.project_id = '".$coin_id."' and p.status='open' and p.filtered_auctiontype='regular'";
				$result=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($result) > 0)
				{
					while($line = $ilance->db->fetch_array($result))
					{
						
						$minimum_bid=0;
						$list[]=$line['project_id'];
						$cbid=$line['currentprice'];
						$sql1="SELECT amount+".$cbid." as minimum_bid FROM " . DB_PREFIX . "increments WHERE ((increment_from <= ".$cbid." AND increment_to >= ".$cbid.")  OR (increment_from < ".$cbid." AND increment_to < ".$cbid.")) AND groupname = 'default' ORDER BY amount DESC ";
						$result1=$ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($result1) > 0)
						{
							while($line1 = $ilance->db->fetch_array($result1))
							{
								$minimum_bid=$line1['minimum_bid'];
							}
						}
						if($bidamount>0 and $minimum_bid<=$bidamount)
						{
							//place bid on the mentioned bid amount 
							//insert into cron_placebid
	 
						}else if($bidamount>0 and $minimum_bid>$bidamount)
						{
							//dont have to insert into cron bid table.
						}
						else
						{
							//insert into cron_placebid
							//insert 0 so that it will bid in bext bid amount
							$ipaddress      = IPADDRESS;
							$createdate     = DATETIME24H;
							
							$insert_sql="INSERT INTO " . DB_PREFIX . "cron_placebid
										(id, project_id, user_id, project_title, description, bids, filtered_auctiontype, startprice, currentprice, next_bid, date_end, bidder_id, Bid_Placed, place_bid_createdate,place_bid_date_updated,ipaddress)
										VALUES(
										NULL,
										'" . intval($line['project_id']). "',     
										'" . intval($line['user_id']). "',
										'". $ilance->db->escape_string($line['project_title'])."',
										'place next bid ',
										'" . intval($line['bids']). "',
										'" . $line['filtered_auctiontype']. "',
										'" . $line['startprice'] . "',
										'" . $line['currentprice'] . "',
										'0',
										'" . $ilance->db->escape_string($line['date_end']). "',
										'" . intval($bidder_id). "',
										'No',
										'" . $ilance->db->escape_string($createdate) . "',
										'0000-00-00 00:00:00',										
										'" . $ilance->db->escape_string($ipaddress) . "') ";
							$ilance->db->query($insert_sql, 0, null, __FILE__, __LINE__);
							$total_coin_lists[]=$line['project_id'];
							
						}
					}
				}
		    }
			
		}	 
    }
}

?>
