<?php

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
 
$query1="SELECT *,DAYOFWEEK(actual_end_date) as onday FROM " . DB_PREFIX . "coin_relist  where hour(actual_end_date)='23' and enddate>0 and filtered_auctiontype='fixed' order by actual_end_date desc";
//approx 2511
$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
echo $ilance->db->num_rows($sql1).'<br>';
if($ilance->db->num_rows($sql1))
{
	while($line1 = $ilance->db->fetch_array($sql1))
	{
	 $line1['coin_id'];
		 $query2="SELECT * FROM " . DB_PREFIX . "coin_relist where coin_id='".$line['coin_id']."' and date('".$line1['actual_end_date']."')=date(enddate)";
		 $sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
		 if($ilance->db->num_rows($sql2))
		 {
		 	while($line2 = $ilance->db->fetch_array($sql2))
		 	{
		 	//echo $line2['coin_id'];
		 	}
		 }else
		 {
			 echo $line1['coin_id'].','.$line1['onday'].'<br>';
		 }
	}
}


/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>