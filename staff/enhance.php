<?php

require_once('./../functions/config.php');
$query="SELECT *  FROM `ilance_invoices` WHERE `isenhancementfee` = 1 group by projectid having count(projectid)>1 ORDER BY `ilance_invoices`.`invoiceid`  DESC";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result))
{
while($line=$ilance->db->fetch_array($result))
{
	$query1="SELECT *  FROM ilance_projects_log WHERE project_id = ".$line['projectid']."";
	$result1=$ilance->db->query($query1);
	if($ilance->db->num_rows($result1))
	{
	while($line1=$ilance->db->fetch_array($result1))
	{
	$ip_add='';
	$query2="SELECT *  FROM `ilance_users` WHERE `user_id` = '".$line['user_id']."'";
		$result2=$ilance->db->query($query2);
		if($ilance->db->num_rows($result2))
		{
		while($line2=$ilance->db->fetch_array($result2))
		{
		$ip_add=$line2['ipaddress'];
		}
		}
	
	//any enhancement invoice between $line1['date_added'] and $line1['date_end']
	  $query2="SELECT *,TIMEDIFF('".$line1['date_added']."',createdate) as diff  FROM `ilance_invoices` WHERE `isenhancementfee` = 1  and createdate>='".$line1['date_added']."' and createdate<='".$line1['date_end']."' and projectid='".$line['projectid']."' ";
	$result2=$ilance->db->query($query2);
	if($ilance->db->num_rows($result2))
	{
	while($line2=$ilance->db->fetch_array($result2))
	{
		echo $line1['project_id'].','.$line1['user_id'].','.$line1['date_added'].','.$line2['createdate'].','.$line2['amount'].','.$line2['diff'].','.$line2['ipaddress']
		.','.$ip_add;
		echo '<br>';
	 
	}
	}
	
	}
	}
}
}

?>
