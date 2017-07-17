<?php 
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
$sql="SELECT *  FROM " . DB_PREFIX . "buynow_orders WHERE item_end_date =0 order by orderid";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
		 $sql1="SELECT *  FROM " . DB_PREFIX . "coin_relist WHERE  coin_id='".$line['project_id']."' and enddate>'".$line['orderdate']."' limit 1";
		 $res1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
		 if($ilance->db->num_rows($res1)>0)
		 {
			 while($line1=$ilance->db->fetch_array($res1))
			 {
				 //$html.= $line['orderid'].','.$line['project_id'].','.$line['orderdate'].','.$line1['enddate'].'<br>';
				 echo $sql3="update " . DB_PREFIX . "buynow_orders set item_end_date='".$line1['enddate']."' WHERE  orderid='".$line['orderid']."'";
				 $res3 = $ilance->db->query($sql3, 0, null, __FILE__, __LINE__);
				 echo '<br>';
				 
			 }
		 }else
		 {
		 //echo $line['orderid'].','.$line['project_id'].','.$line['orderdate'].','.$line1['enddate'].'<br>';
		 //search project table
		 $sql2="SELECT *  FROM " . DB_PREFIX . "projects WHERE  project_id='".$line['project_id']."'";
		 $res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
		 if($ilance->db->num_rows($res2)>0)
		 {
			 while($line2=$ilance->db->fetch_array($res2))
			 {
				echo $sql3="update " . DB_PREFIX . "buynow_orders set item_end_date='".$line2['date_end']."' WHERE  orderid='".$line['orderid']."'";
				 $res3 = $ilance->db->query($sql3, 0, null, __FILE__, __LINE__);
				 echo '<br>';				
				echo $line2['date_end'];
			 }
		 }else
		 {
		 
		 }
		 }
	}
}
echo $html;
?>