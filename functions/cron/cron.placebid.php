<?php
//include '../config.php';
//error_reporting(E_ALL);
global $ilance, $myapi, $ilpage, $phrase, $ilconfig;
$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_product = construct_object('api.bid_product');
$query1 = "SELECT c.*,p.currentprice as current_project_price,p.bids as bidcount,p.startprice 
FROM " . DB_PREFIX . "cron_placebid c left join " . DB_PREFIX . "projects p on p.project_id=c.project_id  
where c.Bid_Placed='No' and p.status='open' order by id asc limit 2";
$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql1)) {
	while ($line1 = $ilance->db->fetch_array($sql1)) {
		if ($line1['project_id'] > 0) {
			
			$project_id = $line1['project_id'];
			$project_user_id = $line1['user_id'];
			$bidamount = $line1['next_bid'];
			$bidder_id = $line1['bidder_id'];
			$minimum = $line1['currentprice'];			
			$update_date     = DATETIME24H;
			if ($bidamount == 0) {
				if ($line1['bidcount'] > 0) {
					
						$high_bid = $ilance->bid->fetch_highest_bid($project_id);
					
					if ($high_bid <= 0) {
						$high_bid = $line1['currentprice'];
					}
						$bid_increment = get_increment($high_bid);
						$bidamount = $high_bid + $bid_increment;
					
					
				} else {
						$bidamount = $line1['startprice'];
				}
				
				$sucess=$ilance->bid_product->placebid(0, 0, 0, intval($project_id), $project_user_id, $bidamount, 1, $bidder_id, 1, $minimum, 0, false, 0, 0);
				if($sucess == 1)
				{
						$ilance->db->query("UPDATE " . DB_PREFIX . "cron_placebid SET Bid_Placed ='yes',place_bid_date_updated = '" . $ilance->db->escape_string($update_date) . "' WHERE id = '" . intval($line1['id']) . "'");
				}
				else
				{
						$ilance->db->query("UPDATE " . DB_PREFIX . "cron_placebid SET Bid_Placed ='cant' ,place_bid_date_updated = '" . $ilance->db->escape_string($update_date) . "' WHERE id = '" . intval($line1['id']) . "'");
				}
				
			} elseif ($line1['current_project_price'] <= $bidamount) {
						$ilance->bid_product->placebid(0, 0, 0, intval($project_id), $project_user_id, $bidamount, 1, $bidder_id, 1, $minimum, 0, false, 0, 0);
						$ilance->db->query("UPDATE " . DB_PREFIX . "cron_placebid SET Bid_Placed ='yes' ,place_bid_date_updated = '" . $ilance->db->escape_string($update_date) . "' WHERE id = '" . intval($line1['id']) . "'");

			} else {
				$ilance->db->query("UPDATE " . DB_PREFIX . "cron_placebid SET Bid_Placed ='cant' ,place_bid_date_updated = '" . $ilance->db->escape_string($update_date) . "' WHERE id = '" . intval($line1['id']) . "'");
			}
			sleep(rand(10, 40));
		} else {
			$ilance->db->query("UPDATE " . DB_PREFIX . "cron_placebid SET Bid_Placed ='cant' ,place_bid_date_updated = '" . $ilance->db->escape_string($update_date) . "' WHERE id = '" . intval($line1['id']) . "'");
		}
		log_cron_action('The place bid tasks were successfully executed: ' . $project_id . $cronlog, $nextitem);

	}
} else {
	log_cron_action('The place bid tasks were successfully executed' . $cronlog,'No Coins');
}

function get_increment($cbid) {
	global $ilance;
	$incrementgroup = 'default';
	$sqlincrements = $ilance->db->query("
            SELECT amount
            FROM " . DB_PREFIX . "increments
            WHERE ((increment_from <= $cbid AND increment_to >= $cbid)
                            OR (increment_from < $cbid AND increment_to < $cbid))
                    AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
            ORDER BY amount DESC
    ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sqlincrements) > 0) {
		$resincrement = $ilance->db->fetch_array($sqlincrements, DB_ASSOC);
		return $resincrement['amount'];
	}
}
?>
