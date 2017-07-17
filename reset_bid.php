<?php
require_once('./functions/config.php');

//select coins bidded after n time bidded by a user
//find if there is a bid placed before that time
//set that price as current price, delete bid and proxy bid


$date_limit='curdate() - INTERVAL 1 week';
$user_id=isset($_GET['user_id'])?$_GET['user_id']:28;
//check if this user placed any bid with in this week
$sql="SELECT * FROM " . DB_PREFIX . "project_bids WHERE user_id='".$user_id."' and date(date_added) > ".$date_limit;
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
	while($line= $ilance->db->fetch_array($result))
	{
		//reset the current price to starting price of the coin, and bid count to 0
		echo $sql2="update " . DB_PREFIX . "projects set currentprice=startprice, bids=0 WHERE project_id = '" . $line['project_id'] . "'";
		$result2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
		echo '<br>';
		//delete all the bids in this coin, even if placed by any user, any date
		echo $sql3="delete from " . DB_PREFIX . "project_bids  WHERE project_id = '" . $line['project_id'] . "'";
		$result3 = $ilance->db->query($sql3, 0, null, __FILE__, __LINE__);
		echo '<br>';
		//delete all the proxy bids on this coin, even if placed by any user, any date
		echo $sql3="delete from " . DB_PREFIX . "proxybid WHERE project_id = '" . $line['project_id'] . "'";
		$result3 = $ilance->db->query($sql3, 0, null, __FILE__, __LINE__);
		echo '<br>';
		
	}
}else
echo 'no items bidded';
?>
