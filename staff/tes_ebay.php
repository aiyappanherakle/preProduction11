<?php 
require_once('../functions/config.php');

$query="SELECT * FROM " . DB_PREFIX . "ebay_listing_rows";
	$result=$ilance->db->query($query);
	if($ilance->db->num_rows($result))
	{
		while($line=$ilance->db->fetch_array($result))
		{
				echo $query1="update ilance_invoices set  createdate = '".$line['enddate']."' WHERE user_id = ".$line['seller_id']." and projectid = ".$line['coin_id']." and isfvf=1 and createdate> '".$line['enddate']."';";
				echo '<br>';
		}
	}
?>