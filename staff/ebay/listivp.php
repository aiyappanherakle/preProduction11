<?php 
require_once('../../functions/config.php');
error_reporting(E_ALL);
$query1="SELECT * FROM `ilance_ebay_listing_rows` WHERE `invoice_status` = 'unpaid'";
 $sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{
	while($line1 = $ilance->db->fetch_array($sql1))
	{
	$query2="SELECT * FROM `ilance_invoice_projects` WHERE `invoice_id` =".$line1['invoice_id']."";
	$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($sql2)==0)
	{
		echo $line1['invoice_id'].'<br>';
	}
	}
}