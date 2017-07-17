<?php 
require_once('../../functions/config.php');

$query1="SELECT * FROM " . DB_PREFIX . "ebay_listing_rows";
$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{
echo 'ebay items having more then one invoices<br>';
	while($line1 = $ilance->db->fetch_array($sql1))
	{
	 $project_id=$line1['coin_id'];
	 $query2="SELECT * FROM " . DB_PREFIX . "invoices  where projectid='".$project_id."' and combine_project='' and isfvf=0 and isbuyerfee=0 and isenhancementfee=0";
	 $sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
	 if($ilance->db->num_rows($sql2)>1)
	 {
		 while($line2 = $ilance->db->fetch_array($sql2))
		{
	 	echo $line1['coin_id'].'- invoiceid'.$line2['invoiceid'].'- type'.$line1['type'].'- description'.$line2['description'];
		echo '<br>';
		}
	 }
	}
}
echo '<hr>';
$query1="SELECT * FROM " . DB_PREFIX . "ebay_listing where status='listed'";
$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{
	while($line1 = $ilance->db->fetch_array($sql1))
	{
	 $project_id=$line1['coin_id'];
	 $query2="SELECT * FROM " . DB_PREFIX . "invoices  where projectid='".$project_id."' and combine_project='' and isfvf=0 and isbuyerfee=0 and isenhancementfee=0";
	// $query2="SELECT * FROM " . DB_PREFIX . "invoices  where projectid='".$project_id."' ";
	 $sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
	 if($ilance->db->num_rows($sql2)>0)
	 {
	 	echo $line1['coin_id'].'|'.$line1['end_date'];
		echo '<br>';
	 
	 }
	}
}